# SHAI PANEL | Complete PHP 8.4 compatible source (registration hotfix integrated)

This archive is the **complete updated SHAI Panel application source**, not a two-file hotfix.
The included files cover the controllers, models, views, assets, routes, loader catalog integration, SQL migrations, and tests.

**Do not overwrite an existing database. Do not upload the PHP 7.3-era vendor folder.**

## The registration HTTP 500 fix

- Fixed the PHP visibility fatal: `Auth::getUserIP()` is now `public`, matching `BaseController`.
- Removed the unnecessary `conn.php` include from the registration view and repaired its IP-field markup.
- Standardized authentication IP handling through CodeIgniter's trusted-proxy-aware `getIPAddress()`.
- The session debug dump route only exists in development.
- Fixed unescaped, unused `HTTP_USER_AGENT` injection in the login form.
- Front controllers return a clear HTTP 503 if Composer dependencies are absent, instead of crashing with HTTP 500.

## Requirements

- PHP **8.2 or newer**, recommended 8.4; extensions `intl`, `mbstring`, `mysqli`, `curl`.
- MySQL-compatible database with your existing panel tables.
- Composer 2 on a local machine or hosting shell **to install supported CodeIgniter 4.7.x dependencies**.
- HTTPS, an Apache-compatible web server with rewrite support, and a writable `writable/` directory.

## To deploy the full codebase on x10hosting

1. **Back up** the existing server files and export the live database.
2. Extract the entire project. Don't merge old vendor libraries over this release.
3. In the extracted project root, using a computer with PHP 8.2+ and Composer 2, run:

   ```sh
   composer update --no-dev --prefer-dist --optimize-autoloader
   php tools/preflight.php
   ```

   This prepares `vendor/` and `composer.lock`. Upload both together with this project.
   The project cannot start until compatible dependencies are installed.
4. Copy `.env.example` to `.env` and configure **your current live database values**, HTTPS baseURL, and a fresh encryption key. Protect `.env` from the web. Do not upload your configuration or credentials publicly.
5. Prefer pointing the x10 website document root to the `public/` folder. If you must deploy with the project root as the public document root, retain the included root `.htaccess` and ensure Apache honors overrides. Never serve app/, vendor/, writable/, .env or SQL publicly.
6. Check `https://shaii.x10.mx/register` and `/login`, test both form submissions with your own referral code, check `/loader/catalog`, and then test `/connect` with a valid test license.
7. Import `sql/loader_catalog_migration.sql` **only if loader catalog tables are missing**. Never re-import `sql/panel_fresh_install.sql` onto an existing live database.

## Tests and reproducibility

From the project root with PHP CLI:

```sh
php tests/check_auth_controller.php "$PWD"
php tests/ReleaseRegressionSmoke.php
php tests/LoaderCatalogFormatterSmoke.php
php tools/preflight.php
php spark routes
```

`php spark routes`, live HTTP checks, and database integration require vendor dependencies and access to the host environment. Source syntax validation alone does not establish runtime success.

If HTTP 500 persists, get the **first** error line from x10hosting's PHP error log or `writable/logs/` and check the installed `vendor/`, `.env`, required extensions, and writable permissions. Never enable public debug stack traces on production.

**This archive does not modify the Android APK.** The SHAI LOADER's previously configured `/loader/catalog` and `/connect` routes remain in this PHP panel release.

## Legacy standalone scripts

This release also removes three outbound HTTP calls to an unrelated domain from `EXTEND_KEYS_DURATIONS.php`, `Edit_Keygen_Links.php`, and `extend.php`. The existing business logic in those scripts is otherwise retained. Their historical `HTTP_REFERER`-only access checks are **not a secure authorization method**, so treat legacy administrative actions as unverified and migrate them to authenticated CodeIgniter POST actions in a separate rollout. The recommended `public/` document root avoids exposing root PHP scripts directly.

The full code has been packaged, **but vendor is excluded** because the correct PHP 8.4 dependency tree could not be installed within this build environment. Importing the SQL or copying outdated CodeIgniter vendor files will not fix missing dependencies.
