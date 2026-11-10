<?php
// Checks that the controller classes load, without a database or Composer.
// Usage: php tests/check_auth_controller.php /absolute/path/to/panel
namespace CodeIgniter { class Controller {} }
namespace {
    $project = $argv[1] ?? '';
    if (!is_file($project . '/app/Controllers/Auth.php')) {
        fwrite(STDERR, "Missing panel source folder.\n");
        exit(2);
    }
    require $project . '/app/Controllers/BaseController.php';
    require $project . '/app/Controllers/Auth.php';
    $base = new ReflectionMethod(\App\Controllers\BaseController::class, 'getUserIP');
    $auth = new ReflectionMethod(\App\Controllers\Auth::class, 'getUserIP');
    if (! $base->isPublic() || ! $auth->isPublic()) {
        fwrite(STDERR, "getUserIP visibility does not match parent.\n");
        exit(1);
    }
    echo "PASS: Auth controller loads and getUserIP is public.\n";
}
