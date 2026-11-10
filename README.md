# SHAI Panel

[![Build Status](https://travis-ci.com/CleanTalk/anti-ddos-lite.svg?branch=master)](https://travis-ci.org/CleanTalk/anti-ddos-lite)

**SHAI Panel** — a hardened PHP anti-DDoS / anti-crawler protection layer.

It is a security-hardened rebrand and rewrite of CleanTalk Anti-DDoS-Lite, styled as a
**neo-brutalism red gaming UI** and tightened against common bypass techniques.

## What SHAI Panel does

During the opening of a page, the application performs a JavaScript challenge. Real browsers
execute the challenge and pass through after one interaction. Automated traffic, headless browsers,
and simple bots typically fail the challenge and are shown the SHAI Panel block page instead of
the real content.

As a result:

- Bots are denied the original page and receive only the stop page.
- Server load from bad traffic is reduced before it reaches the application.
- Legitimate visitors see the block page only once — on their first visit — and then pass
  automatically on subsequent requests.

## Design

The block page uses a **neo-brutalism red gaming aesthetic**:

- Black backgrounds with sharp red borders and heavy shadows
- Bold, skewed "SHAI" logotype with a red glow
- Scanline overlay and red grid background for a gaming/terminal feel
- Glitch-animated accent line and pulsing status dot
- Brutalist challenge button with press-state stacking shadow
- Countdown timer and shrinking progress bar before auto-redirect

## Quick start

### A. Include the library as the very first step

```php
<?php
// This file must be included before any other application output.
require __DIR__ . '/src/anti-ddos-lib.php';

use function SHAI\antiDdosProtectionMain;

$shai = [
    'remote_ip'         => '',                // auto-detected if empty
    'anti_ddos_salt'    => 'REPLACE_WITH_STRONG_SALT',
    'secure_label'      => 'shai_pass',
    'redirect_delay'    => 8,                 // seconds before auto-redirect
    'secure_cookie_days'=> 7,
    'server_url'        => 'https://example.com/',
    'anti_ddos_debug'   => false,
    'test_headless'     => false,
    'skip_not_rated_ua' => true,
];

antiDdosProtectionMain($shai);

// ... rest of your application below ...
```

### B. Regenerate the salt

Never ship the example salt in production. Generate a strong one once and store it securely
(outside the repository).

```php
$salt = SHAI_generateSalt(32);
```

## Trusted networks and user agents

### Skip trusted Autonomous Systems (GeoIP)

Install the [GeoIP](https://www.php.net/manual/en/book.geoip.php) extension and list excluded
AS numbers:

```php
$not_rated_as = '';
```

### Skip trusted user agents

Edit `src/not_rated_ua.php` to allow legitimate bots (search engines, uptime monitors, social
previews). Regular expressions are supported:

```php
$not_rated_ua = array(
    'Googlebot',
    'Bingbot',
    'UptimeRobot',
);
```

## Security notes

- Keep the salt secret and rotate it if it is ever exposed.
- Include SHAI Panel **before** any application logic or error output.
- The library now uses SHA-256 key derivation, timing-safe cookie comparisons, Secure+HttpOnly+
  SameSite=Strict cookies, request-method gating, a one-time nonce challenge, and IP scoring.
- The legacy CleanTalk marketing/obfuscation script on the block page has been removed.

## Changelog

### SHAI Panel (current)
- Rebranded to SHAI Panel with neo-brutalism red gaming UI.
- Replaced MD5 key derivation with SHA-256 + salt.
- Cookie comparison hardened with `hash_equals()`.
- Cookies set with Secure + HttpOnly + SameSite=Strict by default.
- Request method allowlist (GET/HEAD only; others rejected with 405).
- Stronger headless/automation detection (UA markers, empty UA, Accept-Language hint).
- One-time nonce challenge on the block page to defeat naive auto-reload bots.
- IP scoring / rate-limiting ring (file-backed) with score reset on pass.
- Security headers on the block page (`X-Content-Type-Options`, `X-Frame-Options`,
  `Referrer-Policy`).
- Removed legacy inline obfuscation/marketing script.

### Anti-DDoS-Lite 2.0.2
- Fixed a `document.write` vulnerability.
- UA list converted to regular expressions.
- UptimeRobot UA handled.
- Trusted UA skip handled correctly.

### Anti-DDoS-Lite 2.0.1
- Added headless mode detection.
- Visitors without JS are blocked correctly.
- Refactoring and minor fixes.

## Contacts

Email: welcome@cleantalk.org
