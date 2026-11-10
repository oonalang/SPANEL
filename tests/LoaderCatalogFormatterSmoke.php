<?php
// Run: php tests/LoaderCatalogFormatterSmoke.php (no Composer vendor required)
require_once __DIR__ . '/../app/Libraries/LoaderCatalogFormatter.php';
use App\Libraries\LoaderCatalogFormatter;

$rows = [
    ['name' => 'Demo', 'download_url' => 'https://example.org/app.so',
     'game_package' => 'com.example.demo', 'game_type' => 'DEMO',
     'architecture' => '64bit', 'jni_key' => 'public-metadata',
     'version_name' => '1.0', 'logo_url' => '', 'status' => 'ACTIVE'],
    ['name' => 'Paused', 'download_url' => 'https://example.org/paused.so',
     'game_package' => 'com.example.paused', 'game_type' => 'DEMO',
     'architecture' => '32bit', 'jni_key' => '',
     'version_name' => '', 'logo_url' => '', 'status' => 'MAINTENANCE'],
    ['name' => 'Hidden', 'download_url' => 'https://example.org/hidden.so',
     'game_package' => 'com.example.hidden', 'game_type' => 'DEMO',
     'architecture' => '64bit', 'jni_key' => '',
     'version_name' => '', 'logo_url' => '', 'status' => 'DISABLED'],
];
$payload = LoaderCatalogFormatter::payload($rows, 'https://shaii.x10.mx/connect');
$encoded = LoaderCatalogFormatter::encode($payload);
$parsed = json_decode(base64_decode($encoded, true), true, 512, JSON_THROW_ON_ERROR);
assert(count($parsed['DATA']) === 2);
assert($parsed['META']['APP_VERSION_CODE'] === 1);
assert($parsed['DATA'][0]['NAME'] === 'Demo');
assert($parsed['DATA'][0]['TYPE'] === 'DEMO');
assert($parsed['DATA'][0]['ARCH'] === '64bit');
assert($parsed['DATA'][0]['URL'] === 'https://shaii.x10.mx/connect');
assert($parsed['DATA'][1]['STATUS'] === 'MAINTENANCE');
assert(count(LoaderCatalogFormatter::payload([], 'https://shaii.x10.mx/connect')['DATA']) === 0);
foreach (['NAME','DOWNLOAD','URL','ARCH','KEY','GAME','TYPE','STATUS'] as $key) {
    assert(array_key_exists($key,$parsed['DATA'][0]));
}
echo "PASS: catalog Base64 JSON contract, 2 visible entries, hidden filtering, metadata, URL and empty-state.\n";
