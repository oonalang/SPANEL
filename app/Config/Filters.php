<?php

namespace Config;

use App\Filters\AdminFilter;
use App\Filters\AuthFilter;
use App\Filters\SecurityHeaders;
use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf' => CSRF::class,
        'toolbar' => DebugToolbar::class,
        'honeypot' => Honeypot::class,
        'invalidchars' => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors' => Cors::class,
        'forcehttps' => ForceHTTPS::class,
        'pagecache' => PageCache::class,
        'performance' => PerformanceMetrics::class,
        'auth' => AuthFilter::class,
        'sechead' => SecurityHeaders::class,
        'admin' => AdminFilter::class,
    ];

    // Required in modern CodeIgniter. Retain its boot and debugging hooks.
    public array $required = [
        'before' => ['forcehttps', 'pagecache'],
        'after' => ['pagecache', 'performance', 'toolbar'],
    ];

    // Preserve existing form CSRF and /connect API's non-browser POST contract.
    public array $globals = [
        'before' => [
            'csrf' => ['except' => ['connect', 'loader/catalog/assigned']],
            'auth' => ['except' => ['/', 'login', 'register', 'setup', 'connect', 'loader/catalog', 'loader/catalog/assigned', 'loader/catalog/file/*', 'Loader/index.php']],
        ],
        'after' => ['sechead'],
    ];
    public array $methods = [];
    public array $filters = [];
}
