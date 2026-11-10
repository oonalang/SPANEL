<?php

namespace App\Libraries;

/**
 * Builds the loader catalog in the Base64(JSON) format the ASTRAL loader expects.
 * It is not an authorization token: every field in the response is public.
 */
final class LoaderCatalogFormatter
{
    public static function payload(array $rows, string $connectUrl): array
    {
        $entries = [];
        foreach ($rows as $row) {
            $status = strtoupper((string) ($row['status'] ?? 'DISABLED'));
            if (! in_array($status, ['ACTIVE', 'MAINTENANCE'], true)) {
                continue;
            }

            $entries[] = [
                'NAME' => (string) $row['name'],
                'DOWNLOAD' => (string) $row['download_url'],
                'URL' => $connectUrl,
                'ARCH' => (string) $row['architecture'],
                'KEY' => (string) $row['jni_key'],
                'GAME' => (string) $row['game_package'],
                'TYPE' => (string) $row['game_type'],
                'STATUS' => $status,
                'VERSION' => (string) ($row['version_name'] ?? ''),
                'LOGO' => (string) ($row['logo_url'] ?? ''),
            ];
        }

        // Must match the Android app's versionCode (currently 1). Raising it needs a
        // matching app release and update URL.
        return [
            'META' => ['APP_VERSION_CODE' => 1],
            'DATA' => $entries,
        ];
    }

    public static function encode(array $payload): string
    {
        return base64_encode(json_encode(
            $payload,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ));
    }
}
