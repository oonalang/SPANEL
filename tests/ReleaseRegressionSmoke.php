<?php
/** Minimal regression checks that read the source files directly. */
$root = dirname(__DIR__);
$read = static fn (string $rel): string => file_get_contents($root . '/' . $rel);
$auth = $read('app/Controllers/Auth.php');
$base = $read('app/Controllers/BaseController.php');
$user = $read('app/Models/UserModel.php');
$view = $read('app/Views/Auth/register.php');
$routes = $read('app/Config/Routes.php');
$assert = static function (bool $ok, string $label): void {
    if (! $ok) { fwrite(STDERR, "FAIL: {$label}\n"); exit(1); }
    echo "PASS: {$label}\n";
};
$assert(str_contains($base, 'public function getUserIP(): string') && str_contains($auth, 'public function getUserIP(): string'), 'Auth IP method visibility');
$assert(str_contains($base, "getIPAddress()") && str_contains($user, "getIPAddress()"), 'Trusted proxy IP consistency');
$assert(! str_contains($view, "include(FCPATH . 'conn.php')"), 'Registration does not load legacy mysqli connection');
$assert(str_contains($view, 'shai-ip-input') && str_contains($view, '</div>'), 'Registration markup retained');
$assert(str_contains($routes, "ENVIRONMENT === 'development'"), 'Debug route limited to development');
$assert(str_contains($routes, "'connect', 'Connect::index'"), 'Existing license API route present');
$assert(str_contains($routes, "'loader/catalog', 'LoaderCatalog::index'"), 'Loader catalog route present');
$assert(! str_contains($read('app/Views/Auth/login.php'), "HTTP_USER_AGENT"), 'Unused raw request header removed from login form');
