# SHAI Panel — Security Policy

## Overview

SHAI Panel is a hardened anti-DDoS / anti-crawler protection layer for PHP web applications.
It blocks scripted and automated traffic before it can reach the application, while allowing
legitimate visitors to pass after a one-time JavaScript challenge.

## Security model (hardened)

Compared with the original Anti-DDoS-Lite implementation, SHAI Panel tightens several areas:

- **Key derivation** — secure cookies are derived with `hash('sha256', ip . ':' . salt)` instead of
  the broken MD5 used by the legacy version. Do not reuse old MD5-based salts without regenerating them.
- **Timing-safe comparisons** — cookie re-entry checks use `hash_equals()` to avoid timing side-channels.
- **Cookie flags** — pass cookies are set with `Secure`, `HttpOnly`, and `SameSite=Strict` by default.
- **Request method gating** — only `GET` and `HEAD` are allowed through the protection layer; other
  methods are rejected early with `405`.
- **Stronger bot signals** — empty or very short User-Agent strings, automation-related UA substrings
  (headlesschrome, playwright, puppeteer, selenium, webdriver, etc.), and missing Accept-Language
  are evaluated as part of the block decision.
- **One-time nonce challenge** — the block page issues a nonce that the JavaScript challenge must echo
  back. A bare reload without the nonce receipt does not pass the challenge.
- **IP scoring / rate limiting** — repeated block-page hits from the same IP increment a score; once the
  score crosses the threshold the IP is blocked more aggressively. Passing the challenge clears the score.
- **Security headers on the block page** — `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`,
  and `Referrer-Policy: no-referrer` are emitted on the challenge page.
- **No inline marketing/obfuscation script** — the legacy CleanTalk obfuscation/marketing script has been
  removed from the block page.

## Salt management

The `anti_ddos_salt` value must be a strong random secret. Generate a new one with:

```php
// Generate once, store securely (not committed to the repo).
$salt = \SHAI\SHAI_generateSalt(32);
```

Do **not** use the examples in `Newkeys.txt` or `public/new.txt` as production salts.
Rotating the salt will invalidate existing pass cookies, which is expected when rotating secrets.

## Supported versions

| Version | Supported          |
| ------- | ------------------ |
| SHAI Panel (current) | :white_check_mark: |

## Reporting a vulnerability

Please report security issues to **welcome@cleantalk.org**.
When reporting, include the SHAI Panel version and a description of the bypass or weakness you found.

## Integration guidance

Include the protection **as early as possible** in the request lifecycle, before any application logic
or verbose error output. The block page is served with HTTP `403` and must be shown to visitors that
fail the challenge.
