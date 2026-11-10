<?php

declare(strict_types=1);

/**
 * First-time, additive database install. Runs before CodeIgniter handles the
 * request and cannot be called from outside.
 */
function shaiAutoSetup(string $root): void
{
    $flag = $root . '/writable/.shai_schema_ready';
    if (is_file($flag)) {
        return;
    }

    if (!extension_loaded('mysqli')) {
        shaiSetupUnavailable('The PHP mysqli extension is required.');
    }

    $directory = $root . '/writable';
    if (!is_dir($directory) || !is_writable($directory)) {
        shaiSetupUnavailable('Make the writable directory writable for the PHP process.');
    }

    $lock = @fopen($directory . '/.shai_setup.lock', 'c');
    if ($lock === false || !flock($lock, LOCK_EX)) {
        shaiSetupUnavailable('The database installer is busy or the writable directory is locked.');
    }
    try {
        if (is_file($flag)) {
            return;
        }
        $config = shaiSetupReadEnv($root . '/.env');
        $host = $config['database.default.hostname'] ?? '';
        $user = $config['database.default.username'] ?? '';
        $password = $config['database.default.password'] ?? '';
        $database = $config['database.default.database'] ?? '';
        $port = (int) ($config['database.default.port'] ?? '3306');
        if ($host === '' || $user === '' || $password === '' || $database === '') {
            shaiSetupUnavailable('Database connection settings are missing.');
        }

        mysqli_report(MYSQLI_REPORT_OFF);
        $connection = @new mysqli($host, $user, $password, $database, $port);
        if ($connection->connect_errno !== 0) {
            shaiSetupUnavailable('The database is unavailable. Check its credentials or hosting permissions.');
        }
        if (!$connection->set_charset('utf8mb4')) {
            shaiSetupUnavailable('The database character set is unavailable.');
        }

        // One idempotent schema script, safe on empty and existing databases.
        $sqlFile = $root . '/sql/panel_fresh_install.sql';
        $sql = @file_get_contents($sqlFile);
        if ($sql === false || trim($sql) === '') {
            shaiSetupUnavailable('The SQL schema file is missing.');
        }

        if (!$connection->multi_query($sql)) {
            shaiSetupUnavailable('Automatic database setup failed. See x10Hosting database permissions.');
        }
        do {
            $result = $connection->store_result();
            if ($result instanceof mysqli_result) {
                $result->free();
            }
            if (!$connection->more_results()) {
                break;
            }
            if (!$connection->next_result()) {
                shaiSetupUnavailable('Automatic database setup could not finish.');
            }
        } while (true);

        foreach (['users', 'keys_code', 'history', 'loader_catalog', 'user_libraries'] as $table) {
            $escaped = $connection->real_escape_string($table);
            $result = $connection->query("SHOW TABLES LIKE '" . $escaped . "'");
            if (!$result || $result->num_rows !== 1) {
                shaiSetupUnavailable('Database validation failed during first-time installation.');
            }
            $result->free();
        }
        if (@file_put_contents($flag, "SHAI schema installed\n", LOCK_EX) === false) {
            shaiSetupUnavailable('The database was initialized, but writable setup status could not be saved.');
        }
        @chmod($flag, 0600);
        $connection->close();
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function shaiSetupReadEnv(string $file): array
{
    if (!is_file($file) || !is_readable($file)) {
        shaiSetupUnavailable('The configuration file is missing.');
    }
    $values = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $value = trim($value);
        if (strlen($value) >= 2 && ($value[0] === "'" || $value[0] === '"') && substr($value, -1) === $value[0]) {
            $value = substr($value, 1, -1);
        }
        $values[trim($key)] = $value;
    }
    return $values;
}

function shaiSetupUnavailable(string $message): never
{
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    exit('SHAI setup: ' . $message);
}
