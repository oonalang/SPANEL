<?php

namespace App\Libraries;

final class CatalogLibraryUpload
{
    private const MAX_BYTES = 10 * 1048576;

    public static function validate($file, string $architecture): int
    {
        if (! $file->isValid()) {
            $error = (int) $file->getError();
            if (in_array($error, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                throw new \InvalidArgumentException('The selected library exceeds the PHP upload limit (' . ini_get('upload_max_filesize') . ').');
            }
            throw new \InvalidArgumentException('Upload failed. Choose the .so file again.');
        }
        if ($file->hasMoved()) {
            throw new \InvalidArgumentException('The selected library was already uploaded. Choose the file again.');
        }
        $name = basename(str_replace('\\', '/', (string) $file->getClientName()));
        if (! preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]{0,196}\.so\z/iD', $name) || str_contains($name, '..')) {
            throw new \InvalidArgumentException('Choose a .so file with a valid filename.');
        }
        $size = (int) $file->getSize();
        if ($size < 1 || $size > self::MAX_BYTES) {
            throw new \InvalidArgumentException('Choose a nonempty .so file no larger than 10 MB. Your hosting PHP limit may be lower.');
        }
        $stream = @fopen((string) $file->getTempName(), 'rb');
        if (! $stream) {
            throw new \InvalidArgumentException('The selected file could not be read.');
        }
        try {
            $header = fread($stream, 5);
        } finally {
            fclose($stream);
        }
        $expectedClass = $architecture === '32bit' ? 1 : ($architecture === '64bit' ? 2 : 0);
        if (strlen($header) !== 5 || substr($header, 0, 4) !== "\x7fELF" || ord($header[4]) !== $expectedClass) {
            throw new \InvalidArgumentException('This file must be a valid ' . $architecture . ' Android .so library. Check the architecture selection.');
        }
        return $size;
    }

    public static function store($file, int $owner, string $architecture): array
    {
        if ($owner < 1) {
            throw new \InvalidArgumentException('Sign in to upload a library.');
        }
        $size = self::validate($file, $architecture);
        $original = basename(str_replace('\\', '/', (string) $file->getClientName()));
        $base = substr($original, 0, -3);
        $base = substr($base, 0, 130);
        $name = 'catalog-' . bin2hex(random_bytes(12)) . '-' . $base . '.so';
        $directory = WRITEPATH . 'private_libraries' . DIRECTORY_SEPARATOR . $owner;
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Private library storage is unavailable');
        }
        $path = $directory . DIRECTORY_SEPARATOR . $name;
        $upload = ['owner' => $owner, 'name' => $name, 'path' => $path, 'url' => site_url('lib/download') . '?file=' . rawurlencode($name)];
        try {
            $file->move($directory, $name);
            if (! is_file($path) || filesize($path) !== $size) {
                throw new \RuntimeException('The library could not be saved');
            }
            chmod($path, 0600);
            $db = db_connect();
            $db->query('CREATE TABLE IF NOT EXISTS `user_library_files` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `owner_user_id` BIGINT NOT NULL,
                `client_name` VARCHAR(200) NOT NULL,
                `size_bytes` BIGINT UNSIGNED NOT NULL,
                `sha256` CHAR(64) NOT NULL,
                `uploaded_at` DATETIME NOT NULL,
                UNIQUE KEY `owner_filename` (`owner_user_id`, `client_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
            $saved = $db->table('user_library_files')->insert([
                'owner_user_id' => $owner,
                'client_name' => $name,
                'size_bytes' => $size,
                'sha256' => hash_file('sha256', $path),
                'uploaded_at' => date('Y-m-d H:i:s'),
            ]);
            if ($saved === false) {
                throw new \RuntimeException('Library record could not be saved');
            }
            return $upload;
        } catch (\Throwable $error) {
            self::discard($upload);
            throw $error;
        }
    }

    public static function discard(array $upload): void
    {
        $name = (string) ($upload['name'] ?? '');
        $owner = (int) ($upload['owner'] ?? 0);
        if ($owner < 1 || ! preg_match('/\Acatalog-[a-f0-9]{24}-[A-Za-z0-9][A-Za-z0-9._-]{0,130}\.so\z/D', $name)) {
            return;
        }
        $path = WRITEPATH . 'private_libraries' . DIRECTORY_SEPARATOR . $owner . DIRECTORY_SEPARATOR . $name;
        if (is_file($path)) {
            @unlink($path);
        }
        try {
            db_connect()->table('user_library_files')->where('owner_user_id', $owner)->where('client_name', $name)->delete();
        } catch (\Throwable $ignored) {
        }
    }
}
