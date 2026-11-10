<?php

namespace App\Controllers;

use App\Libraries\LoaderCatalogFormatter;

final class LoaderCatalog extends BaseController
{
    public function index()
    {
        try {
            $rows = db_connect()
                ->table('loader_catalog')
                ->select('name, download_url, game_package, game_type, architecture, jni_key, version_name, logo_url, status')
                ->where('owner_user_id IS NULL', null, false)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();
            $encoded = LoaderCatalogFormatter::encode(
                LoaderCatalogFormatter::payload($rows, site_url('connect'))
            );
        } catch (\Throwable $error) {
            log_message('error', 'Unable to serve loader catalog: {error}', [
                'error' => $error->getMessage(),
            ]);
            return $this->response->setStatusCode(503)
                ->setHeader('Cache-Control', 'no-store')
                ->setBody('Catalog unavailable');
        }

        return $this->response->setHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->setHeader('Cache-Control', 'no-store')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($encoded);
    }
    public function assigned()
    {
        $key = trim((string) $this->request->getPost('user_key'));
        $serial = trim((string) $this->request->getPost('serial'));
        $game = trim((string) $this->request->getPost('game'));
        if (! preg_match('/^[A-Za-z0-9_-]{1,190}$/D', $key)
            || ! preg_match('/^[A-Za-z0-9_-]{1,190}$/D', $serial)
            || ! preg_match('/^[A-Za-z0-9_-]{1,32}$/D', $game)) {
            return $this->rejectAssigned('Invalid request');
        }
        try {
            $db = db_connect();
            $license = $db->table('keys_code')
                ->select('id_keys, game, registrator, status, expired_date, devices')
                ->where('user_key', $key)
                ->where('game', $game)
                ->get()->getRowArray();
            if (! $license || (int) ($license['status'] ?? 0) !== 1) {
                return $this->rejectAssigned('License unavailable');
            }
            $expires = strtotime((string) ($license['expired_date'] ?? ''));
            if (! $expires || $expires <= time()) {
                return $this->rejectAssigned('License expired or not activated');
            }
            $registered = array_filter(array_map('trim', explode(',', (string) ($license['devices'] ?? ''))));
            if (! in_array($serial, $registered, true)) {
                return $this->rejectAssigned('Device has not been authorized');
            }
            $owner = $db->table('users')
                ->select('id_users')
                ->where('username', (string) $license['registrator'])
                ->get()->getRowArray();
            if (! $owner) {
                return $this->rejectAssigned('Library owner unavailable');
            }
            $rows = $db->table('loader_catalog')
                ->select('id, name, download_url, game_package, game_type, architecture, jni_key, version_name, logo_url, status')
                ->where('owner_user_id', (int) $owner['id_users'])
                ->where('game_type', $game)
                ->whereIn('status', ['ACTIVE', 'MAINTENANCE'])
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()->getResultArray();
            foreach ($rows as &$entry) {
                $filename = $this->localLibraryName((string) $entry['download_url']);
                if ($filename !== null) {
                    $entry['download_url'] = $this->issueDownload(
                        (int) $owner['id_users'],
                        (int) $entry['id'],
                        (int) $license['id_keys'],
                        $game,
                        $serial,
                        $filename
                    );
                }
            }
            unset($entry);

            return $this->response
                ->setHeader('Cache-Control', 'no-store')
                ->setHeader('X-Content-Type-Options', 'nosniff')
                ->setJSON([
                    'status' => true,
                    'game' => $game,
                    'catalog' => LoaderCatalogFormatter::payload($rows, site_url('connect')),
                ]);
        } catch (\Throwable $error) {
            log_message('error', 'Assigned library catalog error: {error}', [
                'error' => $error->getMessage(),
            ]);
            return $this->response->setStatusCode(503)
                ->setHeader('Cache-Control', 'no-store')
                ->setJSON(['status' => false, 'reason' => 'Catalog unavailable']);
        }
    }

    private function localLibraryName(string $url): ?string
    {
        $target = parse_url($url);
        $expected = parse_url(site_url('lib/download'));
        if (! is_array($target) || ! is_array($expected)) {
            return null;
        }
        if (strtolower((string) ($target['scheme'] ?? '')) !== 'https'
            || strcasecmp((string) ($target['host'] ?? ''), (string) ($expected['host'] ?? '')) !== 0
            || ($target['path'] ?? '') !== ($expected['path'] ?? '')
            || isset($target['user']) || isset($target['pass'])) {
            return null;
        }
        parse_str((string) ($target['query'] ?? ''), $query);
        $filename = $query['file'] ?? null;
        if (! is_string($filename)
            || ! preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]{0,196}\.so\z/iD', $filename)
            || str_contains($filename, '..')) {
            throw new \RuntimeException('Invalid private library URL');
        }
        return $filename;
    }

    private function downloadTokenDirectory(): string
    {
        return WRITEPATH . 'private_libraries' . DIRECTORY_SEPARATOR . '.download_tokens';
    }

    private function issueDownload(int $owner, int $catalogId, int $licenseId, string $game, string $serial, string $filename): string
    {
        $file = WRITEPATH . 'private_libraries' . DIRECTORY_SEPARATOR . $owner . DIRECTORY_SEPARATOR . $filename;
        $record = db_connect()->table('user_library_files')
            ->where('owner_user_id', $owner)
            ->where('client_name', $filename)
            ->get()->getRowArray();
        if (! $record || ! is_file($file) || filesize($file) < 1) {
            throw new \RuntimeException('Private library file is missing');
        }
        $directory = $this->downloadTokenDirectory();
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Private library token storage is unavailable');
        }
        $token = bin2hex(random_bytes(32));
        $grant = json_encode([
            'owner' => $owner,
            'catalog' => $catalogId,
            'license' => $licenseId,
            'game' => $game,
            'serial' => $serial,
            'filename' => $filename,
            'expires' => time() + 3600,
        ], JSON_THROW_ON_ERROR);
        $temporary = $directory . DIRECTORY_SEPARATOR . '.' . $token . '.tmp';
        $destination = $directory . DIRECTORY_SEPARATOR . $token . '.json';
        if (file_put_contents($temporary, $grant, LOCK_EX) === false) {
            throw new \RuntimeException('Unable to store private library grant');
        }
        chmod($temporary, 0600);
        if (! rename($temporary, $destination)) {
            @unlink($temporary);
            throw new \RuntimeException('Unable to publish private library grant');
        }
        if (random_int(0, 15) === 0) {
            foreach (glob($directory . DIRECTORY_SEPARATOR . '*.json') ?: [] as $old) {
                if (filemtime($old) < time() - 7200) {
                    @unlink($old);
                }
            }
        }
        return site_url('loader/catalog/file/' . $token);
    }

    public function file(string $token = '')
    {
        $denied = function () {
            return $this->response->setStatusCode(404)
                ->setHeader('Cache-Control', 'no-store')
                ->setHeader('X-Content-Type-Options', 'nosniff')
                ->setBody('Library unavailable');
        };
        if (! preg_match('/\A[a-f0-9]{64}\z/D', $token)) {
            return $denied();
        }
        try {
            $grantFile = $this->downloadTokenDirectory() . DIRECTORY_SEPARATOR . $token . '.json';
            if (! is_file($grantFile)) {
                return $denied();
            }
            $grant = json_decode((string) file_get_contents($grantFile), true, 16, JSON_THROW_ON_ERROR);
            if (! is_array($grant) || (int) ($grant['expires'] ?? 0) < time()) {
                return $denied();
            }
            $owner = (int) ($grant['owner'] ?? 0);
            $catalogId = (int) ($grant['catalog'] ?? 0);
            $licenseId = (int) ($grant['license'] ?? 0);
            $filename = (string) ($grant['filename'] ?? '');
            $game = (string) ($grant['game'] ?? '');
            $serial = (string) ($grant['serial'] ?? '');
            if ($owner <= 0 || $catalogId <= 0 || $licenseId <= 0
                || ! preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]{0,196}\.so\z/iD', $filename)
                || str_contains($filename, '..')) {
                return $denied();
            }
            $db = db_connect();
            $catalog = $db->table('loader_catalog')
                ->select('owner_user_id, game_type, status, download_url')
                ->where('id', $catalogId)
                ->where('owner_user_id', $owner)
                ->where('game_type', $game)
                ->where('status', 'ACTIVE')
                ->get()->getRowArray();
            if (! $catalog || $this->localLibraryName((string) $catalog['download_url']) !== $filename) {
                return $denied();
            }
            $license = $db->table('keys_code')
                ->select('registrator, status, expired_date, devices')
                ->where('id_keys', $licenseId)
                ->where('game', $game)
                ->get()->getRowArray();
            if (! $license || (int) ($license['status'] ?? 0) !== 1
                || strtotime((string) ($license['expired_date'] ?? '')) <= time()) {
                return $denied();
            }
            $registered = array_filter(array_map('trim', explode(',', (string) ($license['devices'] ?? ''))));
            if (! in_array($serial, $registered, true)) {
                return $denied();
            }
            $user = $db->table('users')->select('username')->where('id_users', $owner)->get()->getRowArray();
            if (! $user || (string) $user['username'] !== (string) $license['registrator']) {
                return $denied();
            }
            $record = $db->table('user_library_files')
                ->where('owner_user_id', $owner)
                ->where('client_name', $filename)
                ->get()->getRowArray();
            $path = WRITEPATH . 'private_libraries' . DIRECTORY_SEPARATOR . $owner . DIRECTORY_SEPARATOR . $filename;
            if (! $record || ! is_file($path) || filesize($path) < 1) {
                return $denied();
            }
            return $this->response->download($path, null)
                ->setFileName($filename)
                ->setHeader('Cache-Control', 'private, no-store, max-age=0')
                ->setHeader('X-Content-Type-Options', 'nosniff')
                ->setHeader('Referrer-Policy', 'no-referrer');
        } catch (\Throwable $error) {
            log_message('error', 'Private library download failed: {error}', [
                'error' => $error->getMessage(),
            ]);
            return $denied();
        }
    }

    private function rejectAssigned(string $reason)
    {
        return $this->response->setStatusCode(403)
            ->setHeader('Cache-Control', 'no-store')
            ->setJSON(['status' => false, 'reason' => $reason]);
    }

}
