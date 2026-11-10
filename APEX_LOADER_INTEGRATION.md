# SHAI Panel + ASTRAL Loader integration

## What is compatible

- Android `GET https://shaii.x10.mx/loader/catalog` receives a plain Base64 string, decoded into JSON `{ "META": {...}, "DATA": [...] }`.
- One catalog item contains `NAME`, `DOWNLOAD`, `URL`, `ARCH`, `KEY`, `GAME`, `TYPE`, `STATUS`, `VERSION`, `LOGO`.
- The public `URL` is always derived from the panel's `app.baseURL` and ends with `/connect`. No per-entry validation URL overrides.
- Loader `POST /connect` submits form `game=TYPE`, `user_key=<license>`, `serial=<Android ID>`. The existing controller still returns JSON `status`, `reason`, and `data`.
- `TYPE` must match existing `keys_code.game` exactly. `ARCH` is `32bit` or `64bit`; these are the only two identifiers handled by the current Android client.
- `ACTIVE` entries are usable, `MAINTENANCE` appears unavailable, `DISABLED` entries do not appear in the catalog.
- `META.APP_VERSION_CODE=1` matches the supplied Android app. Increase only when you publish a corresponding newer app, then add the update URL and coordinated update policy.
- The older `/Loader/index.php` route is supported as a secondary alias on hosts whose rewrites route this path into CodeIgniter; the Android app uses `/loader/catalog` to avoid `.php` rewrite conflicts.

## Database migration

1. Back up the live database. **Do not import the full sample SQL into a live panel**. The supplied `xyto-update.sql` includes a predictable test account and sample referral codes. Remove those credentials before any production use.
2. Import `sql/loader_catalog_migration.sql` through phpMyAdmin; this creates one new table without deleting existing keys or users.
3. Use your existing `keys_code` table and original key administration as before. Entries in the catalog reference a game TYPE such as the exact value stored in `keys_code.game`. No license keys or device IDs are stored in the new table.
4. Log in with a real owner account (`level = 1`) and open `/admin/loader-catalog`. Add a catalog entry with an HTTPS download link and matching license game type.
5. Check `https://shaii.x10.mx/loader/catalog` is Base64 text; decode for a JSON object with `DATA`. Empty `DATA` before you add entries is expected.
6. Deploy the updated Android app source, build your own APK, and test with a non-production key. Existing installed APKs still point to the old host until rebuilt.

## Security + hosting

- Use a supported PHP 8.2+ runtime and fresh Composer-installed CodeIgniter dependencies from the previous migration guide.
- Set `.env` `app.baseURL = 'https://shaii.x10.mx/'` and production database credentials. Require HTTPS for the public catalog, `/connect`, and all file URLs.
- Catalog management is owner-only and all mutations are POST with the existing CodeIgniter CSRF filter. Never publish or bypass admin access.
- Base64 is **encoding, not secrecy**. `KEY` in the catalog is public application metadata and must never contain a server password, database credential, or private signing key.
- Original `lib` upload table/page is separate from `loader_catalog`; its `file`, `file_type`, `file_size`, `time` columns are insufficient for ASTRAL catalog fields. It remains unchanged to avoid breaking legacy workflows.
- The project includes legacy standalone PHP scripts and admin GET actions which still require a separate security audit. This release does not claim full panel hardening.
- The provided Android archive contained a signing key and hardcoded signing passwords. The edited Android source package excludes that private key and reads optional release signing credentials from user-local Gradle properties. Rotate the original signing key if exposed outside trusted storage. An unsigned release or ordinary debug build is possible after configuring the Android toolchain. Existing installed APKs signed with the original key require the original signing identity for in-place updates.

## Validation checklist

- Confirm catalog loads from the actual x10hosting website with a new entry.
- Confirm the entry's `TYPE` matches an existing license's `game`.
- Confirm a valid key returns `status:true` and an invalid key returns `status:false`.
- Confirm maintenance/disabled visibility and owner-only management.
- Confirm client downloads only from sources you control and trust; validate the native file independently before distribution.
- Confirm account login, key generation, expiry, device limits, password reset, and logout after migration.

## Unverified

No live hosting, installed Composer vendor framework, MySQL server or Android SDK runtime has been exercised in this packaging environment. See earlier `APEX_PHP8_MIGRATION.md` for all migration prerequisites.

## Run live smoke test after deployment

`python tools/check_loader_endpoints.py https://shaii.x10.mx` confirms the catalog wire format. For a private staging key, set `PANEL_TEST_KEY` and `PANEL_TEST_GAME` as environment variables, then repeat the command. This performs a real `/connect` call and might register the test serial as a new device on the staging key. Do not run this with production keys.
