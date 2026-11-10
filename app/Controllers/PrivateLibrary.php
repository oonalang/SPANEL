<?php

namespace App\Controllers;

use App\Models\UserModel;

final class PrivateLibrary extends BaseController
{
    private function ownerId(): int
    {
        return (int) session('userid');
    }

    private function directory(): string
    {
        return WRITEPATH . 'private_libraries' . DIRECTORY_SEPARATOR . $this->ownerId();
    }

    private function table()
    {
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
        return $db->table('user_library_files');
    }

    private function filename(string $value): string
    {
        $name = basename(str_replace(chr(92), '/', $value));
        if (! preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]{0,196}\.so\z/iD', $name) || str_contains($name, '..')) {
            throw new \InvalidArgumentException('Use a .so filename containing letters, numbers, dots, hyphens, or underscores.');
        }
        return $name;
    }

    private function records(): array
    {
        $table = $this->table();
        $rows = $table->where('owner_user_id', $this->ownerId())->orderBy('uploaded_at', 'DESC')->get()->getResultArray();
        if ($rows) return $rows;
        $legacy = db_connect()->table('user_libraries')->where('owner_user_id', $this->ownerId())->get()->getRowArray();
        if (! $legacy) return [];
        $name = $this->filename($legacy['client_name']);
        $old = $this->directory() . DIRECTORY_SEPARATOR . 'lib.so';
        $new = $this->directory() . DIRECTORY_SEPARATOR . $name;
        if (! is_file($old) && ! is_file($new)) return [];
        if (is_file($old) && ! is_file($new) && ! rename($old, $new)) throw new \RuntimeException('Unable to migrate legacy library');
        $row = [
            'owner_user_id' => $this->ownerId(), 'client_name' => $name,
            'size_bytes' => filesize($new), 'sha256' => hash_file('sha256', $new),
            'uploaded_at' => $legacy['uploaded_at'] ?? date('Y-m-d H:i:s'),
        ];
        $this->table()->insert($row);
        return [$row];
    }

    public function index()
    {
        try {
            $libraries = $this->records();
        } catch (\Throwable $e) {
            log_message('error', 'Library listing failed: {message}', ['message' => $e->getMessage()]);
            return redirect()->to('dashboard')->with('msgDanger', 'Unable to load library records. Check database permissions and migrations.');
        }
        return view('Server/private_lib', [
            'title' => 'My Libraries',
            'user' => (new UserModel())->getUser($this->ownerId()),
            'libraries' => $libraries,
        ]);
    }

    public function upload()
    {
        try {
            $file = $this->request->getFile('libfile');
            if (! $file) return redirect()->to('lib')->with('msgDanger', 'Select a .so file.');
            if (! $file->isValid()) {
                $error = $file->getError();
                $message = in_array($error, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                    ? 'PHP upload limit is ' . ini_get('upload_max_filesize') . '. Update your hosting PHP options.'
                    : 'Upload failed with PHP error ' . (int) $error . '.';
                return redirect()->to('lib')->with('msgDanger', $message);
            }
            if ($file->hasMoved() || $file->getSize() < 1 || $file->getSize() > 50 * 1048576) {
                return redirect()->to('lib')->with('msgDanger', 'File must be nonempty and no larger than 50 MB.');
            }
            $name = $this->filename($file->getClientName());
            $directory = $this->directory();
            if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
                throw new \RuntimeException('Storage unavailable');
            }
            $this->records();
            $path = $directory . DIRECTORY_SEPARATOR . $name;
            $tempName = '.upload-' . bin2hex(random_bytes(12)) . '.tmp';
            $file->move($directory, $tempName);
            $temp = $directory . DIRECTORY_SEPARATOR . $tempName;
            $backup = null;
            try {
                if (is_file($path)) {
                    $backup = $directory . DIRECTORY_SEPARATOR . '.backup-' . bin2hex(random_bytes(12));
                    if (! rename($path, $backup)) throw new \RuntimeException('Unable to stage old library');
                }
                if (! rename($temp, $path)) throw new \RuntimeException('Unable to save library');
                $row = [
                    'owner_user_id' => $this->ownerId(), 'client_name' => $name,
                    'size_bytes' => filesize($path), 'sha256' => hash_file('sha256', $path),
                    'uploaded_at' => date('Y-m-d H:i:s'),
                ];
                $table = $this->table();
                $existing = $table->where('owner_user_id', $this->ownerId())->where('client_name', $name)->get()->getRowArray();
                if ($existing) {
                    $table->where('id', $existing['id'])->update($row);
                } else {
                    $table->insert($row);
                }
                if ($backup && is_file($backup)) unlink($backup);
            } catch (\Throwable $e) {
                if (is_file($path)) unlink($path);
                if ($backup && is_file($backup)) rename($backup, $path);
                if (is_file($temp)) unlink($temp);
                throw $e;
            }
            return redirect()->to('lib')->with('msgSuccess', $existing ? 'Library replaced. Existing URL preserved.' : 'Library uploaded.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->to('lib')->with('msgDanger', $e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'Library upload failed: {message}', ['message' => $e->getMessage()]);
            return redirect()->to('lib')->with('msgDanger', 'Upload failed. Check storage and database permissions.');
        }
    }

    public function download()
    {
        try {
            $name = $this->filename((string) $this->request->getGet('file'));
            $row = $this->table()->where('owner_user_id', $this->ownerId())->where('client_name', $name)->get()->getRowArray();
            $path = $this->directory() . DIRECTORY_SEPARATOR . $name;
            if (! $row || ! is_file($path)) throw new \RuntimeException('Not found');
            return $this->response->download($path, null)->setFileName($name)
                ->setHeader('Cache-Control', 'private, no-store')
                ->setHeader('X-Content-Type-Options', 'nosniff');
        } catch (\Throwable $e) {
            return redirect()->to('lib')->with('msgDanger', 'Library unavailable.');
        }
    }

    public function delete()
    {
        try {
            $name = $this->filename((string) $this->request->getPost('file'));
            $table = $this->table();
            $row = $table->where('owner_user_id', $this->ownerId())->where('client_name', $name)->get()->getRowArray();
            if (! $row) throw new \RuntimeException('Not found');
            $path = $this->directory() . DIRECTORY_SEPARATOR . $name;
            if (is_file($path) && ! unlink($path)) throw new \RuntimeException('Unable to remove file');
            $table->where('id', $row['id'])->delete();
            return redirect()->to('lib')->with('msgSuccess', 'Library deleted.');
        } catch (\Throwable $e) {
            return redirect()->to('lib')->with('msgDanger', 'Unable to delete library.');
        }
    }
}
