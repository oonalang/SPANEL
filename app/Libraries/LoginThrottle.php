<?php

namespace App\Libraries;

/**
 * File-based brute-force guard, no extra database table. Failures are counted
 * per IP+username and per IP alone. Five misses on one name, or twenty from one
 * address, lock further attempts for a while.
 */
final class LoginThrottle
{
    private const PER_NAME_LIMIT = 5;
    private const PER_IP_LIMIT   = 20;
    private const WINDOW         = 900;   // seconds a failure is remembered
    private const LOCK           = 900;   // seconds the lock lasts

    private static function file(string $key): string
    {
        $dir = WRITEPATH . 'cache';
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return $dir . DIRECTORY_SEPARATOR . 'throttle_' . hash('sha256', $key) . '.json';
    }

    private static function read(string $key): array
    {
        $raw  = @file_get_contents(self::file($key));
        $data = $raw ? json_decode($raw, true) : null;

        if (! is_array($data) || ($data['until'] ?? 0) < time() && ($data['first'] ?? 0) < time() - self::WINDOW) {
            return ['count' => 0, 'first' => time(), 'until' => 0];
        }

        return $data + ['count' => 0, 'first' => time(), 'until' => 0];
    }

    private static function write(string $key, array $data): void
    {
        @file_put_contents(self::file($key), json_encode($data), LOCK_EX);
    }

    /** @return int seconds left on a lock, 0 when attempts are allowed */
    public static function lockedFor(string $ip, string $name): int
    {
        $now  = time();
        $left = 0;

        foreach ([self::keyName($ip, $name), self::keyIp($ip)] as $key) {
            $row = self::read($key);
            if (($row['until'] ?? 0) > $now) {
                $left = max($left, (int) $row['until'] - $now);
            }
        }

        return $left;
    }

    public static function fail(string $ip, string $name): void
    {
        foreach ([[self::keyName($ip, $name), self::PER_NAME_LIMIT], [self::keyIp($ip), self::PER_IP_LIMIT]] as [$key, $limit]) {
            $row = self::read($key);
            if ($row['first'] < time() - self::WINDOW) {
                $row = ['count' => 0, 'first' => time(), 'until' => 0];
            }
            $row['count']++;
            if ($row['count'] >= $limit) {
                $row['until'] = time() + self::LOCK;
                $row['count'] = 0;
                $row['first'] = time();
            }
            self::write($key, $row);
        }
    }

    public static function clear(string $ip, string $name): void
    {
        @unlink(self::file(self::keyName($ip, $name)));
    }

    private static function keyName(string $ip, string $name): string
    {
        return 'n|' . $ip . '|' . strtolower($name);
    }

    private static function keyIp(string $ip): string
    {
        return 'i|' . $ip;
    }
}
