<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Adds security headers to panel pages. /connect and /loader/catalog are
 * skipped so the Android loader is not affected.
 */
class SecurityHeaders implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $path = trim($request->getUri()->getPath(), '/');
        if ($path === 'connect' || strpos($path, 'loader/catalog') === 0 || stripos($path, 'Loader/index.php') === 0) {
            return;
        }

        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->setHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->setHeader('Cross-Origin-Opener-Policy', 'same-origin');

        $https = $request->isSecure() || strtolower((string) $request->getHeaderLine('X-Forwarded-Proto')) === 'https';
        if ($https) {
            $response->setHeader('Strict-Transport-Security', 'max-age=15552000');
        }

        // Login, setup and account pages must not be cached or shown from the back button.
        if (in_array($path, ['', 'login', 'register', 'setup', 'dashboard', 'settings'], true)) {
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
            $response->setHeader('Pragma', 'no-cache');
        }
    }
}
