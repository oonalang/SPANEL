<?php

namespace App\Controllers;

use App\Models\KeysModel;

/**
 * Licence endpoint for the game client.
 *
 * The client posts `game`, `user_key` and `serial` to /connect and reads these
 * fields from the answer:
 *
 *   result["status"]        boolean true
 *   result["data"]["token"] string
 *   result["data"]["rng"]   number
 *   result["data"]["EXP"]   string
 *
 * It compares the token with md5("<game>-<user_key>-<serial>-<static word>"),
 * where the static word is $this->staticWords below. A missing field, a null or
 * the wrong type makes the client fail the login even for a valid key, so keep
 * this response shape.
 */
class Connect extends BaseController
{
    protected $model, $game, $uKey, $sDev;
    protected $maintenance, $staticWords, $dbReady;

    public function __construct()
    {
        $conn = $this->db();

        $this->dbReady = $conn !== null;
        $this->model   = new KeysModel();
        $userDetails1 = $this->dbReady ? $this->fetchRow('select * from onoff where id=1', $conn) : null;
        $this->maintenance = (strtolower((string) ($userDetails1['status'] ?? '')) === 'on');
        $this->staticWords = 'Vm8Lk7Uj2JmsjCPVPVjrLa7zgfx3uz9E';
    }

    /**
     * Opens the panel's database connection. conn.php is in the project root and
     * resolves whether the panel runs from the root or from /public.
     */
    protected function db(): ?\mysqli
    {
        include(ROOTPATH . 'conn.php');

        return isset($conn) && $conn instanceof \mysqli ? $conn : null;
    }

    /**
     * Runs a query and returns the first row, or null on a missing table, lost
     * connection or bad SQL, so the client always gets JSON instead of an HTML error.
     */
    protected function fetchRow(string $sql, ?\mysqli $conn = null): ?array
    {
        if ($conn === null) {
            $conn = $this->db();
        }

        $result = db_query($conn, $sql);

        if ($result === false) {
            return null;
        }

        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);

        return $row ?: null;
    }

    /**
     * The panel has two database configs: conn.php (raw mysqli) and the
     * CodeIgniter group in .env that the models use. If they point at different
     * databases, the key is looked up in one and the mod name read from the other,
     * and the client is told a valid key is wrong.
     */
    protected function databasesMatch(\mysqli $conn): bool
    {
        $result = db_query($conn, 'select database() as db');
        $row    = $result ? mysqli_fetch_assoc($result) : null;

        if (! $row || empty($row['db'])) {
            // No answer from the connection: don't block the login.
            return true;
        }

        try {
            $ciDatabase = (string) \Config\Database::connect()->getDatabase();
        } catch (\Throwable $e) {
            return true;
        }

        return $ciDatabase === '' || $ciDatabase === $row['db'];
    }

    /** The mod name is in `modname`; older installs may still keep it in `mod_settings`. */
    protected function fetchModName(?\mysqli $conn = null): string
    {
        $row = $this->fetchRow('select modname from modname where id=1', $conn)
            ?? $this->fetchRow('select modname from mod_settings where id=1', $conn);

        $name = trim((string) ($row['modname'] ?? ''));

        return $name !== '' ? $name : BASE_NAME_FULL;
    }

    /**
     * Binds a device to a key, or reports that the device slots are full.
     *
     * @return array|bool ['devices' => 'serial,serial'] for a new device,
     *                     true if the serial is already bound, false if full
     */
    protected function checkDevicesAdd($serial, $devices, $max_dev)
    {
        $lsDevice = array_filter(explode(',', (string) $devices), static function ($d) {
            return $d !== '';
        });
        $cDevices = count($lsDevice);
        $serialOn = in_array($serial, $lsDevice, true);

        if ($serialOn) {
            return true;
        }

        if ($cDevices < $max_dev) {
            $lsDevice[] = $serial;

            return ['devices' => reduce_multiples(implode(',', $lsDevice), ',', true)];
        }

        return false;
    }

    /** Empty payload the client expects next to "status": false, so it can read the fields without a type error. */
    protected function emptyData(): array
    {
        return [
            'real'         => '',
            'token'        => '',
            'modname'      => BASE_NAME_FULL,
            'mod_status'   => '',
            'credit'       => '',
            'ESP'          => false,
            'Item'         => false,
            'AIM'          => false,
            'SilentAim'    => false,
            'BulletTrack'  => false,
            'Floating'     => false,
            'Memory'       => false,
            'Setting'      => false,
            'EXP'          => '',
            'device'       => 0,
            'MOD_NAME'     => BASE_NAME_FULL,
            'MOD_STATUS'   => '',
            'FLOTING_TEST' => '',
            'BHATIA_EXP'   => null,
            'BHATIA_SLOT'  => 0,
            'rng'          => 0,
        ];
    }

    protected function fail(string $reason)
    {
        return $this->response->setJSON([
            'status' => false,
            'reason' => $reason,
            'data'   => $this->emptyData(),
        ]);
    }

    public function index()
    {
        $this->mergeJsonBody();

        if (! $this->request->getPost() && $this->request->getGet('user_key') !== null) {
            foreach (['game', 'user_key', 'serial'] as $f) {
                if ($this->request->getGet($f) !== null) {
                    $_POST[$f] = (string) $this->request->getGet($f);
                }
            }
            $this->request->setGlobal('post', $_POST);
        }

        if ($this->request->getPost()) {
            try {
                return $this->index_post();
            } catch (\Throwable $e) {
                // A fatal in the licence check used to leave the client with an empty body and the
                // error "Value of type java.lang.String cannot be converted to JSONObject".
                log_message('error', 'Connect failed: {message}', ['message' => $e->getMessage()]);

                return $this->fail('Server error, try again later. Contact: @Christdmm');
            }
        }

        $nata = [
            'web_info' => [
                '_client' => BASE_NAME_FULL,
                'license' => "5G7B3F8J2H\n",
                'version' => '3.0.0',
            ],
            'web__dev' => [
                'author'  => 'Zia Dev',
                'Channel' => 'https://t.me/Ascension_Shy',
                'Group'   => 'https://t.me/Ascension_Shy',
            ],
        ];

        if ($this->clientWantsHtml()) {
            $signedInUser = null;
            if (session()->has('userid')) {
                try {
                    $signedInUser = (new \App\Models\UserModel())->getUser((int) session('userid'));
                } catch (\Throwable $e) {
                    log_message('error', 'Connect header user lookup failed: {message}', ['message' => $e->getMessage()]);
                }
            }
            return view('Connect/endpoint', [
                'info'        => $nata,
                'maintenance' => $this->maintenance,
                'user'        => $signedInUser,
            ]);
        }

        return $this->response->setJSON($nata);
    }

    /** Some loaders send the three fields as a JSON body. Copy them into POST so both styles work. */
    protected function mergeJsonBody(): void
    {
        if ($this->request->getPost()) {
            return;
        }

        $raw = trim((string) $this->request->getBody());

        if ($raw === '' || $raw[0] !== '{') {
            return;
        }

        $json = json_decode($raw, true);

        if (! is_array($json)) {
            return;
        }

        foreach (['game', 'user_key', 'serial'] as $field) {
            if (isset($json[$field]) && is_scalar($json[$field])) {
                $_POST[$field] = (string) $json[$field];
            }
        }

        $this->request->setGlobal('post', $_POST);
    }

    protected function clientWantsHtml()
    {
        // Loader apps (Dalvik/okhttp) and any request with licence fields always get JSON.
        // Only a real browser gets the status page.
        foreach (['game', 'user_key', 'serial'] as $field) {
            if ($this->request->getPost($field) !== null || $this->request->getGet($field) !== null) {
                return false;
            }
        }

        $agent  = (string) $this->request->getHeaderLine('User-Agent');
        $accept = (string) $this->request->getHeaderLine('Accept');

        if (stripos($agent, 'Mozilla/') !== 0 || stripos($agent, 'Dalvik') !== false) {
            return false;
        }

        return strpos($accept, 'text/html') !== false && strpos($accept, 'application/json') === false;
    }

    public function index_post()
    {
        $conn = $this->db();

        if ($conn === null) {
            return $this->fail('Panel database unavailable, try again later. Contact: @Christdmm');
        }

        if (! $this->databasesMatch($conn)) {
            return $this->fail('Panel database configuration mismatch: conn.php and .env point at different databases. Contact: @Christdmm');
        }

        $isMT = $this->maintenance;
        $game = $this->request->getPost('game');
        $uKey = $this->request->getPost('user_key');
        $sDev = $this->request->getPost('serial');

        $form_rules = [
            'game'     => 'required|alpha_dash',
            'user_key' => 'required|alpha_dash',
            'serial'   => 'required|alpha_dash',
        ];

        if (! $this->validate($form_rules)) {
            return $this->fail('Bad Parameter, Contact: @Christdmm');
        }

        if ($isMT) {
            $userDetails1 = $this->fetchRow('select * from onoff where id=1', $conn) ?? [];

            return $this->fail((string) ($userDetails1['myinput'] ?? '') ?: 'Panel is under maintenance, try again later.');
        }

        if (! $game or ! $uKey or ! $sDev) {
            return $this->fail('Invalid Parameter, Contact: @Christdmm');
        }

        $time   = new \CodeIgniter\I18n\Time;
        $model  = $this->model;

        try {
            $findKey = $model->getKeysGame(['user_key' => $uKey, 'game' => $game]);
        } catch (\Throwable $e) {
            // The MySQLi driver throws a raw mysqli_sql_exception, not a CodeIgniter one,
            // and the client can't read a 500 page.
            return $this->fail('Panel database unavailable, try again later. Contact: @Christdmm');
        }

        if (! $findKey) {
            return $this->fail('Incorrect Key, Contact: @Christdmm');
        }

        if ($findKey->status != 1) {
            return $this->fail('Suspended Key, Contact: @Christdmm');
        }

        $id_keys  = $findKey->id_keys;
        $duration = (int) $findKey->duration;
        $expired  = $findKey->expired_date;
        $max_dev  = (int) $findKey->max_devices;
        $devices  = $findKey->devices;

        if ($duration < 1) {
            $duration = 1;
        }

        if ($max_dev < 1) {
            $max_dev = 1;
        }

        // A key that was never used has no expiry yet: start the clock now.
        $expiry = $expired;

        if (! $expiry) {
            $setExpired = $time::now()->addDays($duration);

            try {
                $model->update($id_keys, ['expired_date' => $setExpired]);
            } catch (\Throwable $e) {
                return $this->fail('Panel database unavailable, try again later. Contact: @Christdmm');
            }

            $expiry = $setExpired->toDateTimeString();
        } elseif (! $time::now()->isBefore($expiry)) {
            return $this->fail('Expired Key, Contact: @Christdmm');
        }

        $modName        = $this->fetchModName($conn);
        $userDetails3   = $this->fetchRow('select * from _ftext where id=1', $conn) ?? [];
        $safeKey        = mysqli_real_escape_string($conn, $uKey);
        $userDetails4   = $this->fetchRow("select expired_date from keys_code where user_key='{$safeKey}'", $conn) ?? [];
        $ModFeatureStatus = $this->fetchRow('select * from Feature where id=1', $conn) ?? [];
        $rngcnt         = $time->getTimestamp();
        $devicesAdd     = $this->checkDevicesAdd($sDev, $devices, $max_dev);

        if ($devicesAdd === false) {
            return $this->fail('Max Device Reached, Contact: @Christdmm');
        }

        if (is_array($devicesAdd)) {
            try {
                $model->update($id_keys, $devicesAdd);
            } catch (\Throwable $e) {
                return $this->fail('Panel database unavailable, try again later. Contact: @Christdmm');
            }
        }

        $real = "$game-$uKey-$sDev-$this->staticWords";

        $data = [
            'status' => true,
            'data'   => [
                'real'         => $real,
                'token'        => md5($real),
                'modname'      => $modName,
                'mod_status'   => (string) ($userDetails3['_status'] ?? ''),
                'credit'       => (string) ($userDetails3['_ftext'] ?? ''),
                'ESP'          => (($ModFeatureStatus['ESP'] ?? 'false') == 'true') ? true : false,
                'Item'         => (($ModFeatureStatus['Item'] ?? 'false') == 'true') ? true : false,
                'AIM'          => (($ModFeatureStatus['AIM'] ?? 'false') == 'true') ? true : false,
                'SilentAim'    => (($ModFeatureStatus['SilentAim'] ?? 'false') == 'true') ? true : false,
                'BulletTrack'  => (($ModFeatureStatus['BulletTrack'] ?? 'false') == 'true') ? true : false,
                'Floating'     => (($ModFeatureStatus['Floating'] ?? 'false') == 'true') ? true : false,
                'Memory'       => (($ModFeatureStatus['Memory'] ?? 'false') == 'true') ? true : false,
                'Setting'      => (($ModFeatureStatus['Setting'] ?? 'false') == 'true') ? true : false,

                'EXP'          => (string) ($userDetails4['expired_date'] ?? $expiry),
                'device'       => $max_dev,
                'MOD_NAME'     => $modName,
                'MOD_STATUS'   => (string) ($userDetails3['_status'] ?? ''),
                'FLOTING_TEST' => (string) ($userDetails3['_ftext'] ?? ''),
                'BHATIA_EXP'   => $expired,
                'BHATIA_SLOT'  => $max_dev,
                'rng'          => $rngcnt,
            ],
        ];

        return $this->response->setJSON($data);
    }
}
