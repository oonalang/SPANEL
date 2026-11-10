<?php

namespace App\Controllers;

use App\Libraries\LoginThrottle;
use App\Models\UserModel;
use CodeIgniter\Config\Services;

/**
 * First-run owner setup.
 *
 * - Works only while the panel has no owner (level 1).
 * - Needs a one-time token that exists only as a file on the server
 *   (writable/.shai_setup_token), so nobody who finds /setup first can take
 *   over the panel. The file is deleted after use.
 * - The owner picks their own username, e-mail and password; nothing is
 *   stored in code or SQL.
 */
class Setup extends BaseController
{
    public static function tokenFile(): string
    {
        return WRITEPATH . '.shai_setup_token';
    }

    public static function needed(): bool
    {
        try {
            return db_connect()->table('users')->where('level', 1)->countAllResults() === 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function token(): string
    {
        $file = self::tokenFile();
        $raw  = is_file($file) ? trim((string) @file_get_contents($file)) : '';

        if ($raw === '') {
            $raw = bin2hex(random_bytes(16));
            @file_put_contents($file, $raw . "\n", LOCK_EX);
            @chmod($file, 0600);
        }

        return $raw;
    }

    private function ip(): string
    {
        return (string) $this->request->getIPAddress();
    }

    public function index()
    {
        if (! self::needed()) {
            return redirect()->to('login');
        }

        $this->token();

        if (strtolower((string) $this->request->getMethod()) === 'post') {
            return $this->create();
        }

        return $this->page();
    }

    private function page()
    {
        return view('Auth/setup', [
            'title'      => 'First-time setup',
            'validation' => Services::validation(),
            'tokenPath'  => 'writable/.shai_setup_token',
        ]);
    }

    private function create()
    {
        $ip = $this->ip();

        if (($wait = LoginThrottle::lockedFor($ip, 'setup')) > 0) {
            return redirect()->to('setup')->withInput()
                ->with('msgDanger', 'Too many attempts. Try again in ' . ceil($wait / 60) . ' minute(s).');
        }

        $rules = [
            'setup_token' => 'required|max_length[64]',
            'username'    => 'required|alpha_numeric|min_length[4]|max_length[25]|is_unique[users.username]',
            'fullname'    => 'required|alpha_numeric_space|min_length[3]|max_length[60]',
            'email'       => 'required|valid_email|max_length[40]|is_unique[users.email]',
            'password'    => 'required|min_length[10]|max_length[45]',
            'password2'   => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('setup')->withInput()
                ->with('msgDanger', implode(' ', array_values($this->validator->getErrors())));
        }

        $given    = (string) $this->request->getPost('setup_token');
        $password = (string) $this->request->getPost('password');

        if (! hash_equals($this->token(), $given)) {
            LoginThrottle::fail($ip, 'setup');

            return redirect()->to('setup')->withInput()
                ->with('msgDanger', 'The setup token is wrong. Copy it from ' . 'writable/.shai_setup_token.');
        }

        if (! preg_match('/[A-Za-z]/', $password) || ! preg_match('/\d/', $password)) {
            return redirect()->to('setup')->withInput()
                ->with('msgDanger', 'Use at least one letter and one number in the password.');
        }

        // Check again inside the request so two parallel submissions can't create two owners.
        if (! self::needed()) {
            return redirect()->to('login');
        }

        $record = [
            'email'            => (string) $this->request->getPost('email'),
            'username'         => (string) $this->request->getPost('username'),
            'fullname'         => (string) $this->request->getPost('fullname'),
            'level'            => 1,
            'status'           => 1,
            'password'         => create_password($password),
            'saldo'            => 2147483647,
            'uplink'           => null,
            'user_ip'          => $ip,
            'expiration_date'  => '2099-12-31 23:59:59',
            'reset_link_token' => '',
            'exp_date'         => '',
            'logged_in'        => 0,
        ];

        try {
            $id = (new UserModel())->insert($record, true);
        } catch (\Throwable $e) {
            log_message('error', 'Owner setup failed: {m}', ['m' => $e->getMessage()]);
            $id = false;
        }

        if (! $id) {
            return redirect()->to('setup')->withInput()
                ->with('msgDanger', 'Could not create the owner account. Check the database and try again.');
        }

        @unlink(self::tokenFile());
        LoginThrottle::clear($ip, 'setup');

        return redirect()->to('login')->with('msgSuccess', 'Owner account created. Sign in with the details you just chose.');
    }
}
