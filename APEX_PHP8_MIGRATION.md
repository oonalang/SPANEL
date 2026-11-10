# SHAI Panel: PHP 8 migration, behavior preservation

Status: **implementation provided, runtime verification incomplete**.

## Scope and compatibility

The original upload used PHP 7.3-era CodeIgniter 4.1.9. This migration targets PHP 8.2+ with CodeIgniter 4.7.5, and recommends a maintained PHP 8.4 environment if supported by your hosting account. The original database table names, `/connect` license-validation handler, `app/Config/Routes.php`, feature flags, key status checks, expiry behavior, device-binding behavior, and expected JSON keys were retained. The existing account login, referral and administrative workflows remain in place; authentication and connection configuration received focused changes.

The loader catalog at `https://4amvip.x10.mx/Loader/index.php` is **not supplied by this panel archive**. This migration does not silently replace the loader catalog or point the Android client at a different server. The `/connect` license endpoint is retained, but whether the Android client reaches it depends on the remote catalog's `URL` field and your hosting configuration.

## Changes

- Updated Composer constraints to PHP ^8.2 and CodeIgniter ^4.7.5. Removed bundled outdated `vendor/` and its incompatible old lock file. Composer must resolve/install the new dependencies before deployment.
- Updated `index.php`, `public/index.php` and `spark` to CodeIgniter 4.7's `Boot::bootWeb` / `Boot::bootSpark` API.
- Updated `Config/Filters.php` to its modern parent class while retaining `auth`, `admin`, and `csrf` filters and the license API's CSRF exception.
- Preserved existing login checks for account state and expiration, while eliminating interpolated login/referral SQL, rotating session IDs on login, and using framework IP detection. Account login email notices require a configured sender, and provider errors no longer abort valid logins.
- Prevented partially expired sessions from passing the auth filter and hardened the admin filter when a user is missing or logged out.
- Consolidated MySQL credentials into a private `.env`, used by framework connections and old standalone connection helpers. Never copy the original hardcoded credentials back into source.
- Prevented a password hash being inserted into a password-change email URL. Existing password-change action semantics remain otherwise unchanged.
- Added Apache restrictions for nonpublic folders and source/config files in the root-document-root fallback configuration.
- Excluded sensitive `.env` files, `Newkeys.txt`, pre-existing runtime logs/debugbar state, old `vendor/`, a redundant `project-root/` nested app copy, and the malformed, obfuscated, unreferenced `Protection.php` from the new deployment archive. Also removed private `users` and `referral_code` seed rows from bundled SQL. The **original archive remains the backup**.

## Safe deployment to shaii.x10.mx

1. Back up the existing deployed files and export the live MySQL database before replacing files. Do **not** import the included SQL over your live data: existing users, referral codes, license keys and device bindings should stay in your current database.
2. In the x10hosting control panel, confirm the PHP version and required extensions. PHP 8.2+ is mandatory; use PHP 8.4 if available. Required extensions for this project include `intl`, `mbstring`, `mysqli`, and `curl`.
3. On a computer with PHP 8.2+ and Composer, inside the upgraded project folder run `composer update --no-dev --prefer-dist --optimize-autoloader` once to generate new `composer.lock` and `vendor/`. Then upload those resolved dependencies along with the application source. Do not reuse the old vendor directory.
4. Copy `.env.example` to `.env` **outside the public web root** when hosting permits. Reenter the *current* live database name, user and password; generate a new random encryption key, and set `CI_ENVIRONMENT = production`. Set `email.fromEmail` if account login mail is required. Protect `.env` from web requests.
5. Prefer pointing the site document root to `public/` after moving any directly served assets into it; this is the framework's secure default. If your shared host forces the original project-root webroot, retain the included root `.htaccess`. Apache overrides must be enabled for it to protect private folders.
6. Run `php spark routes`, then manually test account login, register/referral, dashboard, key generation, edit/delete, key expiry, device limits, `/connect` failure/success JSON, and logout on a staging deployment. Check x10 error logs for new compatibility errors before touching the live instance.
7. Rotate database passwords and any other secrets included in the original source ZIP. Do not reuse the exposed old credentials.

## Verification performed

- PHP CLI source syntax checks, excluding removed old dependencies.
- Static assertions for declared dependencies, route names, license API payload fields and release secret hygiene.
- Exact bytewise comparison against the original `/connect` handler and route definitions.

No successful installation, framework boot, MySQL query, Apache request, live login, license check, hosting PHP-version confirmation, or end-to-end test is claimed. The build environment in this session lacks Composer and the PHP `intl`, `mbstring`, `mysqli` and `curl` extensions. Those checks are **BLOCKED**, not passed.

## Remaining risks and deliberate non-changes

- A CodeIgniter 4.1.9 to 4.7.5 upgrade spans several framework releases. Other application config files and old view templates might need adjustments identified by staging runtime tests.
- Several legacy root-level PHP scripts and administrative GET requests require a dedicated authorization/CSRF review. They were not rewritten blindly because their current frontend interactions are unknown.
- The existing license validator uses a legacy MD5-style token protocol, which this patch did not alter to avoid breaking clients. It should be redesigned and deployed together with compatible clients in a later release.
- Saved password formats and the existing database schema were intentionally preserved. A future password-migration plan should be staged and tested against existing hashes.
- The backend `lib` page does not on its own establish compatibility with the separate Android loader catalog format. Test with a real response from your server before switching clients.
