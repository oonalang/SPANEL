<?php
/** CLI only. Makes no network calls and prints no secrets. Run: php tools/preflight.php */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$project = dirname(__DIR__);
$failures = 0;
$assertCheck = static function (bool $success, string $label) use (&$failures): void {
    echo ($success ? '[PASS] ' : '[BLOCKED] ') . $label . PHP_EOL;
    if (! $success) { $failures++; }
};
$assertCheck(PHP_VERSION_ID >= 80200, 'PHP 8.2+ (' . PHP_VERSION . ')');
foreach (['intl', 'mbstring', 'mysqli', 'curl'] as $ext) {
    $assertCheck(extension_loaded($ext), "PHP extension: {$ext}");
}
$assertCheck(is_file($project . '/vendor/codeigniter4/framework/system/Boot.php'), 'CodeIgniter vendor dependencies');
$assertCheck(is_dir($project . '/writable') && is_writable($project . '/writable'), 'Writable folder permissions');
$assertCheck(is_file($project . '/.env'), 'Private .env configuration');
exit($failures === 0 ? 0 : 1);
