# SHAI Panel — How It Works

Analysis of `eliwoahzja/Panel2` at commit `2d041fe` (branch `analysis/how-it-works`).

---

## 0. TL;DR

This repository is **two products in one tree**:

1. **SHAI Panel** — a CodeIgniter 4 web app that sells and validates **licence keys for Android
   game cheats** (Call of Duty Mobile, Farlight 84, Mobile Legends, PUBG/Easy Victory). Resellers
   buy credit ("saldo"), generate keys, and the game client phones home to `/connect` to redeem a
   key against a device fingerprint.
2. **SHAI anti-DDoS library** (`src/`) — a standalone, drop-in PHP "JS challenge" library
   rebranded from CleanTalk Anti-DDoS-Lite. It is **not wired into the panel**; it is a separate
   product that happens to live in the same repo (and the `README.md` documents *only* this half).

On top of that sits a **third, older codebase**: ~15 standalone `.php` scripts in the repository
root (`extend.php`, `DELETEREFF.php`, `Edit_Keygen_Links.php`, …) that talk to the database
directly with raw `mysqli`, guarded only by a `Referer` check.

The panel code is functional but has **several serious authorization defects** (a global
"reset every device binding" endpoint, an unauthenticated key lookup/reset endpoint, SQL
injection in the password-reset view, and committed secrets). See
[§10 Findings](#10-findings-bugs) and [§11 Findings (security)](#11-findings-security).

---

## 1. What the product actually does

A user (the "customer") buys a licence key. A *reseller* (level 3), *admin* (level 2) or *owner*
(level 1) runs the panel. The cheat client (`Farlight/libunoimgui.so`, a 32-bit ARM ELF ImGui
library) starts on a phone and POSTs to `/connect` with:

| field      | meaning                                                     |
|------------|-------------------------------------------------------------|
| `game`     | game code, e.g. `FL84`, `CODM`, `MLBB`, `8BP`, `PBS`         |
| `user_key` | the licence key the customer bought                         |
| `serial`   | the device hardware serial — this is the "device binding"    |

The panel answers with a JSON blob containing the mod name, feature flags (ESP / Item / AIM /
SilentAim / BulletTrack / Memory / Setting / Floating), the account expiry, and an MD5 `token`
derived from `game-key-serial-static-secret`. The client uses that token to unlock the overlay.

So the panel is, in effect, a **licence server + key-vending machine** for game mods.

### The role model

`level` on the `users` row (`app/Helpers/nata_helper.php:29` `getLevel()`):

| level | label      | can do                                                        |
|-------|------------|---------------------------------------------------------------|
| 1     | Owner      | everything: all users, all keys, feature flags, mod name, lib uploads |
| 2     | Admin      | own keys, referral codes, mod name/status, floating text      |
| 3     | Reseller   | own keys only, limited bulk sizes                              |

Every "admin" action in `User.php` re-checks `$user->level` inline, e.g. `app/Controllers/User.php:197`.

---

## 2. Repository topology

```
index.php                 front controller (root docroot variant)
public/index.php          front controller (public/ docroot variant)
app/                      CodeIgniter 4 application
  Config/Routes.php       the routing table (single source of truth)
  Controllers/            Auth, User, Keys, Connect, Home, mail, UserMail
  Models/                 UserModel, KeysModel, CodeModel, HistoryModel, Server, Feature, onoff, _ftext
  Filters/                AuthFilter (global), AdminFilter (/admin group)
  Helpers/nata_helper.php hashing, pricing, device list, DataTables server-side JSON
  Views/                  Bootstrap 5 + neo-brutalist "SHAI" theme
src/anti-ddos-lib.php     the SHAI anti-DDoS challenge library (standalone)
vendor/                   CodeIgniter 4.1.9 (committed so the host needs no composer)
project-root/              a stale copy of the CI4 skeleton (87 tracked files, dead weight)
writable/debugbar/*.json  52 committed DebugToolbar dumps — contain live session cookies
Farlight/libunoimgui.so   the compiled mod library that the panel licenses
conn.php                  raw mysqli connection with hardcoded credentials (committed)
*.php (root)              ~15 legacy standalone scripts from the previous version of the product
```

The repo has **two front controllers** because the deployment target (an x10 shared host) uses the
repository root as the document root: `index.php` sets `FCPATH` to the repo root and the root
`.htaccess` rewrites unknown paths into it. `public/index.php` is the conventional CI4 layout and
is equivalent apart from `FCPATH`.

---

## 3. Bootstrap & request lifecycle

1. `index.php` defines `FCPATH`, requires `app/Config/Paths.php`, then the framework
   `system/bootstrap.php`, then `$app->run()`.
2. `Config\Routes` sets `setAutoRoute(false)` — **every URL is explicitly declared**
   (`app/Config/Routes.php:25`). The default controller is `Home`, whose `index()` does
   `echo view('New')` — a view that does not exist, so `/` is effectively a 500 unless
   `Auth::login` intercepts it first (it does: `/` is routed to `Auth::login`).
3. **Filters** run in order: global `csrf` → global `auth` → route-group filters (`admin`).
4. The controller runs; models use the CI4 query builder.
5. Views render Bootstrap 5 markup with a `Layout/Starter` shell and a `Layout/Header` navbar that
   switches on `session()->has('userid')`.

### Routing map (`app/Config/Routes.php`)

| Route                       | Controller                  | Notes                                   |
|-----------------------------|-----------------------------|-----------------------------------------|
| `/`, `login`                | `Auth::login`               | public                                  |
| `register`                  | `Auth::register`            | public                                  |
| `connect`                   | `Connect::index`            | **public + CSRF-exempt** — the game API |
| `member-key`                | `Keys::memberKey`           | **public** (see §11.2)                  |
| `logout`, `dashboard`       | `Auth::logout`, `User::index` | auth required                        |
| `settings`, `verify_pass`, `token`, `Forget`, `update`, `Server` | `User::*` | auth required |
| `keys/*`                    | `Keys::*`                   | auth required                           |
| `admin/*`                   | `User::*`                   | auth + `admin` filter (owner only)      |

---

## 4. The real security perimeter: `Config\Filters`

`app/Config/Filters.php:33-45` is the single most important file in the app:

```php
'globals' => [
    'before' => [
        'csrf' => ['except' => 'connect'],
        'auth' => ['except' => ['/', 'login', 'register', 'connect', 'member-key']],
    ],
],
```

CI4 matches `except` entries as pseudo-regexes anchored `^…$` against the lowercased URI path
(`vendor/codeigniter4/framework/system/Filters/Filters.php:517`), so the exceptions are **exact
paths, not prefixes**:

* `/`, `/login`, `/register`, `/connect`, `/member-key` → **no session required**.
* Everything else (`/keys/*`, `/dashboard`, `/admin/*`, `/settings`, `/verify_pass`) → `AuthFilter`.
* `/connect` additionally skips **CSRF** (correct — the game client cannot hold a token).

### `AuthFilter` (`app/Filters/AuthFilter.php`)

On every guarded request it calls `UserModel::checkAuthFilter()` (`app/Models/UserModel.php:99`),
which enforces three things:

1. **Session not expired** — `time_login` is an absolute timestamp; `stay_log` extends it to
   +10 h, otherwise +120 min (`app/Controllers/Auth.php:100-107`).
2. **IP pinning** — `bind_ip` is compared with `hash_equals()` against the current client IP; a
   mismatch kills the session.
3. **Account still valid** — `level <= 3` and `status == 1`, re-read from the DB on every request
   (so disabling a user in the admin panel takes effect immediately).

`AdminFilter` (`app/Filters/AdminFilter.php`) then allows only `level == 1` into `/admin/*`.

Note the `getUserIP()` helper (`app/Controllers/Auth.php:22`, duplicated in `BaseController.php:52`)
trusts `HTTP_CLIENT_IP` and `HTTP_X_FORWARDED_FOR` **before** `REMOTE_ADDR`. Since `bind_ip` is
compared against this value, a client that can set `X-Forwarded-For` can defeat the IP pinning
(and, worse, impersonate another IP in the audit logs). `Config\App::$proxyIPs` is empty, so CI4's
own proxy handling is not involved — this is bespoke and untrusted.

---

## 5. Data model

The schema is MySQL, database `jqdmcnvd_shaii`. Tables (as used by the code):

| table           | model           | key columns                                                        |
|-----------------|-----------------|--------------------------------------------------------------------|
| `users`         | `UserModel`     | `id_users`, `username`, `fullname`, `email`, `password`, `level`, `status`, `saldo`, `uplink`, `expiration_date`, `reset_link_token`, `exp_date` |
| `keys_code`     | `KeysModel`     | `id_keys`, `game`, `user_key`, `duration`, `expired_date`, `max_devices`, `devices` (CSV), `status`, `registrator` |
| `referral_code` | `CodeModel`     | `id_reff`, `code` (MD5), `Referral` (plaintext), `level`, `set_saldo`, `used_by`, `created_by`, `acc_expiration` |
| `history`       | `HistoryModel`  | `id_history`, `keys_id`, `user_do`, `info`                          |
| `modname`       | `Server`        | `id`, `modname`                                                    |
| `onoff`         | `onoff`         | `id`, `status` (maintenance on/off), `myinput`                     |
| `_ftext`        | `_ftext`        | `id`, `_ftext` (floating credit), `_status`                        |
| `Feature`       | `Feature`       | `id`, `ESP`, `Item`, `AIM`, `SilentAim`, `BulletTrack`, `Memory`, `Setting`, `Floating` |
| `lib`           | –               | uploaded mod binaries (`upload.php`)                                |
| `Keygen_Links`  | –               | per-game keygen download links                                     |
| `visitor_logs`  | –               | hacking-attempt log rows                                           |

`sql/xyto-update.sql` is the only schema script in the repo; it creates `Keygen_Links` and
`mod_settings`, seeds a test referral code (`SHAITEST01`) and a test owner account
(`shaitest` / `ShaiTest123`).

> The single-row config tables (`modname`, `onoff`, `_ftext`, `Feature`) are edited from
> `/Server` in the admin UI — that is where "maintenance mode", the mod's display name and the
> feature-flag switches live. (`sql/xyto-update.sql` creates a `mod_settings` table instead of
> `modname` — see B9.)

---

## 6. Core flows

### 6.1 Registration (referral-gated)

`Auth::register_action()` (`app/Controllers/Auth.php:149`):

1. Validates email/username/fullname/password/referral.
2. `CodeModel::checkCode($referral)` hashes the code with
   `create_password($code, false)` = `md5("XquxmymXDtWRA66D" . $code)` and looks it up in
   `referral_code.code` (`app/Models/CodeModel.php:41`).
3. Reads `acc_expiration` and `level` from the referral row, then inserts the user with that
   level, `saldo = set_saldo`, `uplink = created_by` and the referral's expiry.
4. `useReferral()` stamps `used_by` so the code cannot be reused.

Referral codes are therefore **plaintext in the DB** (`Referral` column) and **MD5 in the `code`
column** — the MD5 is not a secret, it is just a lookup key.

### 6.2 Login

`Auth::login_action()` (`app/Controllers/Auth.php:74`):

* The validation rule is `is_not_unique[users.username]` — **the username must already exist**
  for the form to validate, so a non-existent username returns "check the form" while a wrong
  password returns "Wrong password". That is a username-enumeration oracle.
* Password check: `password_verify(create_password($pass, false), $row->password)`, where
  `create_password` = `password_hash(md5("XquxmymXDtWRA66D".$pass), PASSWORD_DEFAULT, ['cost'=>8])`
  (`app/Helpers/nata_helper.php:10`). bcrypt **cost 8** is the framework's floor, not a sane
  value (10–12 is normal).
* Three raw `mysqli` queries check the account's `expiration_date` and `status`
  (`app/Controllers/Auth.php:115-127`) — the CI4 model is bypassed for no reason.
* On success it sets the session payload (`userid`, `unames`, `time_login`, `time_since`,
  `login_at`, `stay_log`, `bind_ip`) and `include('UserMail.php')` — a **procedural include that
  lives in `app/Controllers/`** (`app/Controllers/UserMail.php`) and sends a "you logged in" email
  to the account's address, leaking the IP, user agent and visited page into the mailbox.

### 6.3 Key generation and billing

`Keys::generate_action()` (`app/Controllers/Keys.php:503`):

```
price  = getPrice($this->price, $drtn, $loopcount, $maxd)   // ← 4 args into a 3-arg function
saldo  = user.saldo - price
if (saldo < 0) → "Insufficient balance"
insert N keys:  {game, user_key, duration, max_devices, registrator, admin_id}
user.saldo = saldo
history rows for each generated key
```

* `getPrice($price, $duration, $device_max)` (`app/Helpers/nata_helper.php:82`) multiplies
  `price[duration] × device_max`. Because the caller passes `$loopcount` (number of keys) as the
  third argument and `$maxd` as an ignored fourth, **the per-key device count is never charged**,
  even though the UI advertises "$1/Device".
* `loopcount` comes straight from `POST` and is **not validated against the per-role
  `$this->bulk_key` allow-list** — the reseller restriction (1/4/8 keys) is UI-only and can be
  bypassed by posting any `loopcount`.
* Key format is `<duration>xCHRIS-<10 random alnum>`, or a custom key when `custominput=custom`
  (validated `alpha_numeric`, 4–16 chars, unique).
* `admin_id` is inserted but is not in `KeysModel::$allowedFields`, so it is silently dropped.

### 6.4 The `/connect` handshake — the heart of the product

`Connect::index_post()` (`app/Controllers/Connect.php:70`):

1. `game`, `user_key`, `serial` are validated (`alpha_dash`).
2. Maintenance flag: `onoff.status == 'on'` short-circuits to a canned message.
3. Look up the key by `user_key` + `game` → `KeysModel::getKeysGame()`.
4. `status != 1` → "Suspended Key".
5. **First activation**: if `expired_date` is NULL, set it to `now + duration` days. This is when
   the licence clock starts — generating a key does not start it.
6. If `expired_date` is in the past → "Expired Key".
7. **Device binding** (`checkDevicesAdd()`, `app/Controllers/Connect.php:130`): if the serial is
   already in the CSV `devices` column it passes; otherwise it is appended while
   `count(devices) < max_devices`; when the cap is reached → "Max Device Reached".
8. On success it returns the payload:

```json
{"status": true, "data": {
  "real": "<game>-<key>-<serial>-Vm8Lk7Uj2JmsjCPVPVjrLa7zgfx3uz9E",
  "token": "<md5(real)>",
  "modname": "...", "mod_status": "...", "credit": "...",
  "ESP": true, "Item": true, "AIM": false, "SilentAim": false,
  "BulletTrack": false, "Floating": false, "Memory": false, "Setting": false,
  "EXP": "<expiry>", "device": <max_devices>, "BHATIA_SLOT": <max_devices>, "rng": <unix ts>
}}
```

The `staticWords` secret (`app/Controllers/Connect.php:28`) is **hardcoded in the controller**, so
it is in the repository and in every client build. The `token` is therefore not a secret
challenge — it is a checksum the client can compute itself. It only proves "the client knows the
algorithm", which it always does.

### 6.4.1 The client contract — and why a *valid* key could still be rejected

The Android client that redeems the licence is `oonalang/VARIANT4`
(`src/main/jni/Includes/Jni_Stuff.h`, function `Login()`). It is the consumer of `/connect`,
and it is **strict about JSON types**:

```cpp
sprintf(data, "game=CODMGR&user_key=%s&serial=%s", user_key, UUID.c_str());
curl_easy_setopt(curl, CURLOPT_POSTFIELDS, data);
...
json result = json::parse(chunk.memory);
if (result["status"] == true) {
    std::string token = result["data"]["token"].get<std::string>();   // must be a string
    time_t       rng   = result["data"]["rng"].get<time_t>();         // must be a number
    EXP                = result["data"]["EXP"].get<std::string>();   // must be a string
    if (rng + 30 > time(0)) {
        auth = "CODMGR-" + user_key + "-" + UUID + "-Vm8Lk7Uj2JmsjCPVPVjrLa7zgfx3uz9E";
        bValid = (token == CalcMD5(auth));
    }
} else {
    errMsg = result["reason"].get<std::string>();                     // must be a string
}
```

* the endpoint URL is **hardcoded and obfuscated** (`oxorany("https://natachizz.x10.mx/connect")`) —
  pointing the client at another panel means editing that string and rebuilding the APK;
* `nlohmann::json` throws on a type mismatch, and the client catches it with
  `catch (json::exception &e)`, so the user is shown the **raw response body** as the error text;
* the token is `md5("<game>-<key>-<serial>-Vm8Lk7Uj2JmsjCPVPVjrLa7zgfx3uz9E")` — the same
  secret string is hardcoded in the panel (`$staticWords`) and in every client build.

**Three independent defects made a perfectly valid key fail**, all of them in `/connect`:

1. **HTTP 500 on every successful handshake.** `Connect::index_post()` read the mod name from
   the `modname` table, but the panel's own schema (`sql/xyto-update.sql`) creates
   **`mod_settings`** — and CI4's MySQLi driver sets
   `mysqli_report(MYSQLI_REPORT_ALL & ~MYSQLI_REPORT_INDEX)`
   (`vendor/codeigniter4/framework/system/Database/MySQLi/Connection.php:95`) for the whole
   request as soon as it opens its own connection. The raw `mysqli_query()` for the missing
   table therefore **throws a `mysqli_sql_exception`**, the request dies with a 500 and a JSON
   error object, the client finds neither `status` nor `reason`, nlohmann throws, and the login
   fails. A missing *optional* table rejected a valid licence.
2. **`instanceof mysqli` never matched inside a namespace.** In `namespace App\Controllers` an
   unqualified class name does **not** fall back to the global namespace (functions do, classes
   do not), so `$conn instanceof mysqli` was always `false` and the controller answered
   "Panel database unavailable" with a perfectly healthy database. The same trap applies to the
   `?mysqli` parameter type hints.
3. **Maintenance mode returned `status: true` with no `data` block.** The client reads
   `result["data"]["token"]` as soon as `status == true`, so it died with a nlohmann type error
   instead of showing the operator's maintenance message.

On top of that the payload itself was not type-safe: `EXP` came from a query that returns
`null` when the row is missing, and `modname` / `mod_status` / `credit` could be `null` as well —
all fatal for the client.

**The fix (this branch)**

| file | change |
|------|--------|
| `app/Common.php` | new `db_query()` helper — runs a query and returns `false` instead of letting a mysqli error escape |
| `app/Controllers/Connect.php` | connection resolved from `ROOTPATH . 'conn.php'` (works from the repo root *and* from `public/`); every lookup goes through `fetchRow()` → `db_query()`, so a missing table degrades to a default instead of a 500; mod name read from `modname` with a `mod_settings` fallback and a `BASE_NAME_FULL` last resort; `token` always a string, `rng` always an int, `EXP` always a string; failures answer `{"status": false, "reason": "…", "data": {…}}` so a client that reads `data` unconditionally never hits a type error; maintenance answers `status: false` + the operator's message; the model calls are wrapped so a missing `keys_code` table answers "Panel database unavailable" instead of a 500; a new `databasesMatch()` guard reports a configuration mismatch instead of a bogus "Incorrect Key" |
| `app/Views/Server/Server.php` | the settings page uses `db_query()` as well, so a missing `modname`/`mod_settings` table can no longer 500 the admin screen |
| `sql/xyto-update.sql` | creates `modname` *and* keeps `mod_settings` in sync; converts `keys_code.id_keys` to `AUTO_INCREMENT` when an upstream dump left it as a plain INT (CodeIgniter reads the id of a new key from `insert_id()`; without it every generated key collides on id 0) |
| `public/conn.php` | shim so `FCPATH . 'conn.php'` resolves when the document root is `public/` |
| `tools/connect_client_sim.py` | simulator of the client's `Login()` that reproduces nlohmann's strictness, used to verify the endpoint |
| `tools/connect_scenarios.sh` | the 14 scenarios as a re-runnable harness (`PANEL_URL=… bash tools/connect_scenarios.sh`) |

#### The panel has two database configurations

`conn.php` (raw mysqli) and the CodeIgniter group in `.env`
(`database.default.*`, used by every model) are two independent copies of the same
credentials. When they drift apart — which happens as soon as only one of the two is
edited after a host migration — the licence is looked up in the `.env` database while
the mod name, the feature switches and the floating text are read from the `conn.php`
database. The result is a *valid* key answered with **"Incorrect Key"**, which is the
single most misleading failure this panel can produce.

`Connect::databasesMatch()` compares `select database()` on the raw connection with
the CodeIgniter database name and answers

```
"Panel database configuration mismatch: conn.php and .env point at different databases."
```

instead of pretending the key is wrong.

#### The chr15 panel (`chrispanelsrc`) is not a different endpoint

`eliwoahzja/chrispanelsrc` is the panel this one was derived from. Its
`app/Controllers/Connect.php` is the *same* code with the `@Christdmm` branding: it
queries the same `modname` table, uses the same `$staticWords` secret and answers the
same fields, so the client contract is identical. Switching panels would therefore
**not** have fixed a "the key is right but it does not work" report.

The only relevant difference is the schema: `chris.sql` (the chr15 dump) creates a
`modname` table and gives `keys_code.id_keys` an `AUTO_INCREMENT`, while this panel's
`sql/xyto-update.sql` had renamed the table to `mod_settings` — that rename is what
made the endpoint 500. The fixed panel was verified against **both** schemas.

**Verified** with the simulator against both deployment layouts (document root = repo root and
document root = `public/`):

| scenario | result |
|----------|--------|
| valid key, device already bound | `LOGIN OK`, token matches |
| valid key, second device | `LOGIN OK`, serial appended to `devices` |
| fresh key (`expired_date` NULL) | `LOGIN OK`, expiry written |
| same key again (expiry set) | `LOGIN OK` |
| expired / unknown / suspended key | `LOGIN FAILED` with a readable reason |
| device slots full | `LOGIN FAILED` — "Max Device Reached" |
| maintenance mode on | `LOGIN FAILED` — the operator's message, no crash |
| `modname` + `mod_settings` missing | `LOGIN OK` (no 500) |
| `Feature` / `_ftext` missing | `LOGIN OK` (no 500) |
| `keys_code` missing | `LOGIN FAILED` — "Panel database unavailable", no 500 |
| `conn.php` and `.env` point at different databases | `LOGIN FAILED` — "Panel database configuration mismatch", not a bogus "Incorrect Key" |

The panel was additionally verified against the **chr15 schema** (`chris.sql` imported
into a fresh database): with both database configurations pointing at it, the same
scenarios pass.

### 6.5 Admin surfaces

* `/keys` — server-side DataTable. `KeysModel::API_getKeys()` builds the JSON with the helper
  `datatables_json()` (`app/Helpers/nata_helper.php:99`), which implements DataTables'
  `draw`/`start`/`length`/`search`/`order` protocol against the CI4 query builder. Non-owners are
  scoped with `WHERE registrator = :username`.
* `/admin/manage-users`, `/admin/user/{id}`, `/admin/create-referral`, `/admin/api/users` — owner
  only.
* `/Server` — mod name, maintenance switch, floating credit, and the per-feature on/off switches.
* `/keys/{id}` — edit a key (owner: all fields; admin: all fields; reseller: `status` only).
* `/keys/reset` — device/expiry reset for one key, with an ownership check.

---

## 7. Frontend

* Bootstrap 5 + Bootstrap Icons, served from the panel's own `assets/` folder.
* A "neo-brutalist / gaming" theme: `assets/css/shai.css`, purple accent `#a78bfa`, Orbitron /
  Chakra Petch display fonts, glassmorphism cards, particles.js background.
* The DataTables integration is client-side glue calling `site_url('keys/api')` with
  `serverSide: true` and rendering `user_key` blurred by default (an "eye" button unblurs it).
* `app/Views/Layout/Header.php` renders the role-aware navbar (owner sees Manage Users / Create
  Referral / Manage Hacking Attempt / Private Dashboard).
* CDN dependencies: `cdn.datatables.net`, `cdn.jsdelivr.net` (particles.js), Bootstrap, SweetAlert2.

---

## 8. The anti-DDoS library (`src/`) — standalone, not wired in

`src/anti-ddos-lib.php` implements the design described in `README.md`:

1. Reject non-GET/HEAD methods with **405**.
2. Derive `secure_key = sha256(ip + ':' + salt)`.
3. Maintain a **file-backed IP score** (`anti_ddos_ip_scores.dat`, serialized PHP array in the
   library directory). Score ≥ 3 → block.
4. Skip known-good visitors: re-entry via the `secure_label` cookie (compared with
   `hash_equals`), trusted ASNs via GeoIP, and trusted user agents from `src/not_rated_ua.php`.
5. Detect headless/automation clients (UA markers `headlesschrome`, `puppeteer`, `playwright`,
   `selenium`, `cypress`, `webdriver`, … plus empty/short UA).
6. Otherwise serve the block page `src/anti-ddos.html` with **403** and `X-Content-Type-Options`,
   `X-Frame-Options: DENY`, `Referrer-Policy: no-referrer`, plus a one-time nonce.

**It is not referenced anywhere in the panel.** `grep -rn "anti-ddos-lib" app/` returns nothing;
the library is meant to be `require`d as the very first statement of some *other* application.
`src/anti-ddos.html` and the score file live next to the library in the web root, which means the
score file is writable by the web user and the block page is publicly fetchable — acceptable for
the intended use, but it is dead weight inside this repo (and the README, which documents only
this half, is what a new contributor reads first).

---

## 9. The legacy root scripts — a second, older codebase

These sit in the document root and are still tracked:

| file                        | purpose                                    | protection                        |
|-----------------------------|--------------------------------------------|-----------------------------------|
| `upload.php`                | upload the mod `.so` into `Farlight/`     | `Referer` check only              |
| `extend.php`                | extend a key's duration                    | `Referer` check only              |
| `EXTEND_KEYS_DURATIONS.php` | bulk-extend keys by game                   | `Referer` check only              |
| `DELETEREFF.php`            | delete a referral code                     | `Referer` check only              |
| `Edit_Keygen_Links.php`     | edit keygen download links                 | `Referer` check only              |
| `DeleteHackingAttempt.php`  | delete a visitor log row                   | `Referer` check only              |
| `anti ddos v1.php` etc.     | pre-2021 CleanTalk protection snippets    | none                              |
| `Protection.php`            | leftover Imunify360 web-shell artifact     | obfuscated; payload removed       |

Every one of these authenticates by **comparing `$_SERVER['HTTP_REFERER']` to an allow-list**.
A `Referer` header is attacker-controlled — `curl -H 'Referer: https://host/keys'` satisfies it
in one line. They then run raw SQL with interpolated request values (`extend.php`,
`DELETEREFF.php`, `Edit_Keygen_Links.php`, `EXTEND_KEYS_DURATIONS.php`). Several of them also
phone home to a third-party URL (`http://destroyer.cfd/sent-data-api.php?param=…`) on every
successful action.

---

## 10. Findings (bugs)

These are correctness defects confirmed by reading the code and the vendored CI4 4.1.9 source.

### B1 — `/keys/resetAll` wipes the device list of **every key in the database**

`app/Controllers/Keys.php:96-100`:

```php
public function resetDeviceKey()
{
    $model = $this->model;
    $model->set('devices', NULL)->update();   // ← no where()
    return redirect()->to('keys');
}
```

CI4's `Model::update()` with a null id and no prior `where()` compiles
`UPDATE keys_code SET devices = NULL, updated_at = '…'` with **no WHERE clause**
(`vendor/…/Database/BaseBuilder.php:1886` → `_update()` at 1931; `validateUpdate()` at 1952 only
checks that a SET exists). Every key in the table loses its device bindings.

Worse, the **"delete key" button in the UI calls this endpoint**: `resetUserKey1()` in
`app/Views/Keys/list.php` posts to `${origin}/keys/resetAll`. So clicking the trash icon on one
row silently resets device bindings for the whole panel.

`/keys/reset` (→ `Keys::api_key_reset`) is the correctly scoped endpoint; `resetAll` is the
mis-wired one.

### B2 — The price never accounts for devices

`app/Controllers/Keys.php:510` calls `getPrice($this->price, $drtn, $loopcount, $maxd)` but
`getPrice($price, $duration, $device_max)` takes three parameters (`app/Helpers/nata_helper.php:82`).
`$maxd` (devices per key) is dropped, so a 10-device key costs the same as a 1-device key while
the form advertises "$X/Device".

### B3 — `loopcount` is not validated server-side

The bulk-size allow-list (`$this->bulk_key`, 1/4/8 for resellers, up to 1000 for the owner) is
only used to render the `<select>`. `generate_action()` reads `loopcount` straight from POST
(`app/Controllers/Keys.php:509`) and only validates `game`, `duration`, `max_devices`. A reseller
can post `loopcount=500`.

### B4 — `setDevice()` can produce one device too many, and can fatal

`app/Helpers/nata_helper.php:65-80`: the loop keeps index `k` while `k <= $max`, so indices
`0..max` are kept → `max + 1` devices, one over the advertised cap. If every posted line is
filtered out (e.g. only punctuation), `$result` is never initialised and
`implode(",", array_unique($result))` raises a **TypeError on PHP 8** (`array_unique(null)`).

### B5 — `api_key_delete` is an unauthenticated-ownership delete (currently dead code)

`app/Controllers/Keys.php:255` deletes any key by `?userkey=` with **no ownership check**. It is
currently unreachable: `keys/delete` is registered twice (`app/Config/Routes.php`), and the second
registration (`Keys::deleteKeys`) overwrites the first, so the live route is "delete all *my*
keys". Re-exposing that method would be an IDOR.

### B6 — Two menu links point at routes that do not exist

`app/Views/Keys/list.php` links to `site_url('keys/deleteExp')` and `site_url('keys/deleteUnused')`;
neither is registered (the real routes are `DelEXPKeys` / `Un-UsedKeygen`) → 404 on click.

### B7 — `useReferral()` writes the wrong "used by" value

`app/Models/CodeModel.php:20` calls `useReferral($referral)` with the default `$username = true`,
so `used_by` is set to `1` rather than the username that consumed it. The registration flow still
works (it reads `used_by` *before* consuming), but the audit trail is useless and the
"code has been used · 1" message leaks a boolean instead of a user.

### B8 — `User::passwd_act()` sends the new password hash by e-mail and applies it anyway

`app/Controllers/User.php:682-770` changes the password in the DB **and** mails a link
`/verify_pass?key=<new bcrypt hash>&mail=<email>&token=<token>` containing the hash in the URL.
The reset view (`app/Views/User/verify_pass.php`) then writes that hash straight into the
`password` column. The whole token dance is redundant with the immediate update.

### B9 — The SQL seed script does not match the schema the app expects

`sql/xyto-update.sql` creates `mod_settings` (with a `modname` column), but the app reads and
writes the single-row config table **`modname`** (`app/Models/Server.php:11`,
`select * from modname where id=1` in `app/Controllers/Connect.php:182`). The script therefore
creates a table nothing reads and does not create the one everything reads — so a fresh install
from the script alone has a broken `/Server` screen and a broken `/connect` payload.

**Fixed** on `fix/connect-endpoint`: the script now creates `modname` and keeps `mod_settings` in
sync, and the controller reads either table (see §6.4.1).

### B10 — `Home::index()` renders a non-existent view

`app/Controllers/Home.php:18` → `view('New')`; there is no `app/Views/New.php`. The route
`/New` → `Home::index` therefore 500s. (`/` is routed to `Auth::login`, so the default controller
is only reachable via `/New`.)

### B11 — `Auth::index()` dumps the session

`app/Controllers/Auth.php:38` → `dd($a, session())`. It is wired to `/dbg`
(`app/Config/Routes.php:29`). The global `auth` filter does apply, so it needs a session, but
dumping the session payload (`bind_ip`, `time_login`, …) to the browser is not something a
production panel should do.

### B12 — `app/Models/Upload.php` is a CodeIgniter 3 leftover

The file is named `Upload.php` but declares `class User extends CI_Model` (CI3 API) and inserts
into the **`users`** table with a `created`/`modified` column pair that does not exist there.
CI4's autoloader maps `App\Models\Upload`, so this file is never loaded — but it is a live
landmine sitting next to the real `UserModel`.

---

## 11. Findings (security)

### S1 — Unauthenticated key lookup **and** reset at `/member-key`

`app/Config/Filters.php:41` lists `member-key` in the auth filter's `except` list, and
`Keys::memberKey()` (`app/Controllers/Keys.php:687`) does:

```php
$key = $this->model->getKeys($searched);              // any key, no owner check
if ($doReset) {                                        // action=reset
    $this->model->set('devices', NULL)->where('user_key', $searched)->update();
    $this->model->set('expired_date', NULL)->where('user_key', $searched)->update();
}
```

So **anyone, without a session**, can (a) look up any licence key's game, duration, device count,
max devices, expiry and status, and (b) **reset that key's device list and expiry**, which
effectively un-binds the customer's phone and restarts their licence clock. This is the most
serious functional finding: it is both an information leak and a mutation on production data.

### S2 — SQL injection in the password-reset view

`app/Views/User/verify_pass.php` interpolates `$_GET['token']` and `$_GET['mail']` into raw SQL:

```php
mysqli_query($conn, "SELECT * FROM `users` WHERE `reset_link_token`='".$token."'");
```

`/verify_pass` is *not* in the auth `except` list, so this requires a session — but any
authenticated account (including a level-3 reseller) can inject. The same view also writes
`$_GET['key']` directly into the `password` column.

### S3 — The password-reset token is guessable

`app/Controllers/User.php:739` builds the token as `md5($emailId) . rand(10, 9999)` and the
e-mail address is handed to the client in the same URL (`?mail=…`). An attacker who knows a
target's e-mail can compute `md5(email)` and brute-force the ~9 990 possible suffixes to forge a
valid reset link and set an arbitrary password — within the 10-minute window in which a token
row exists. Rotate to `random_bytes()` and never put the secret in the URL.

### S4 — Committed secrets

* `conn.php` (and the duplicate `app/Controllers/conn.php`) hardcode the MySQL host, database,
  user and password in the repository — reachable by anyone who can clone the repo.
* `.env` and `uploads/.env` are **tracked in git** (`git ls-files .env` → `.env`). `.env` contains
  `database.default.hostname` and friends.
* The `.htaccess` guard for `.env` uses Apache **2.2 syntax** (`Order allow,deny` /
  `Deny from all`) inside a `<FilesMatch>` block; on Apache 2.4 without `mod_access_compat` those
  directives are a configuration error, so the protection is not guaranteed.

### S5 — 52 committed DebugToolbar dumps leak live session material

`writable/debugbar/*.json` are tracked (only `writable/session/*` and `writable/logs/*` are
ignored). The dumps contain the **`ci_session` cookie value, the CSRF cookie**, the full request
headers and the executed SQL — for example
`writable/debugbar/debugbar_1654279542.json` contains
`"ci_session": "ebb2rbm38gg7gctsginphbhkhr0jahpb"` and
`SELECT * FROM users WHERE id_users = '10'`. With the repository root as document root, those
JSON files are directly fetchable.

### S6 — The legacy root scripts authenticate by `Referer`

See §9. `Referer` is client-controlled, so `upload.php` (writes a `.so` into `Farlight/`),
`extend.php`, `DELETEREFF.php`, `Edit_Keygen_Links.php`, `EXTEND_KEYS_DURATIONS.php` and
`DeleteHackingAttempt.php` are reachable by anyone who sets one header — and they interpolate
request values into SQL.

### S7 — Untrusted client IP is used for session pinning and audit trails

`getUserIP()` (`app/Controllers/Auth.php:22`) prefers `HTTP_CLIENT_IP`, then `HTTP_X_FORWARDED_FOR`,
then `REMOTE_ADDR`, with no proxy allow-list (`Config\App::$proxyIPs` is empty). The same value
is stored in `users.user_ip` and mailed in the login notification. Anyone can forge it.

### S8 — `keys/reset` has a `sleep(1)` and no rate limiting

`app/Controllers/Keys.php:224` — a deliberate slowdown, but nothing stops a logged-in user from
enumerating keys, and the `login` form has no throttling either.

### S9 — The `/connect` token secret is public

`$staticWords` lives in the controller source, so `token = md5(game-key-serial-secret)` proves
nothing to an attacker who has the client. The real protection is the licence check itself, so
this is not exploitable on its own, but it should not be presented as a secret.

### S10 — `writable/` is web-reachable and the docroot is the repo root

With the root as document root, `/writable/debugbar/*.json`, `/uploads/.env`, `/new.txt`,
`/Newkeys.txt` and `/Farlight/libunoimgui.so` are all public URLs. `app/.htaccess` denies
`/app/`, but nothing denies `/writable/` or `/uploads/`.

---

## 12. Repository hygiene

* `project-root/` — 87 tracked files: a stale copy of the CI4 skeleton (its own `app/`,
  `writable/`, `tests/`). Dead weight; nothing references it.
* `writable/debugbar/*.json` — 52 tracked runtime dumps (see S5).
* `Farlight/libunoimgui.so` — a 3.2 MB stripped 32-bit ARM ELF binary tracked in git. It is the
  product being licensed, so it belongs in a release artifact rather than the source tree.
* `Protection.php` — a leftover Imunify360 web-shell artifact. The obfuscated base64 scaffolding
  is still there; the executing statement was stripped (per the file's own comment). Inert as it
  stands, but it should not be in the repository.
* `Newkeys.txt` / `new.txt` — key dumps written by `Keys::download_all_Keys()` into the current
  working directory (the document root) and then committed.
* `.travis.yml` + `.github/workflows/reviewNotice.yml` — a Travis badge from the CleanTalk
  upstream, and a workflow that posts a Telegram message when a PR is approved (it uses the
  deprecated `::set-output` and `appleboy/telegram-action@master`).

---

## 13. Running it locally

```bash
# PHP 7.3+ (or 8.x) with ext-mysqli, mbstring, intl, curl, json
composer install                 # vendor/ is already committed, so this is optional
cp .env .env.local               # then set:
#   CI_ENVIRONMENT = development
#   app.baseURL    = http://localhost:8080/
#   database.default.hostname / username / password / database

mysql -u root -p <db> < sql/xyto-update.sql   # creates the config tables + test account

php -S localhost:8080 -t public               # public/ layout
# or point the vhost at the repo root to mirror the production layout
```

Test credentials from the SQL seed: owner `shaitest` / `ShaiTest123`, referral code `SHAITEST01`.

Health check without a browser:

```bash
curl -s http://localhost:8080/connect                      # JSON endpoint info
curl -s -X POST http://localhost:8080/connect \
     -d 'game=FL84&user_key=SHAITEST01&serial=ABC123'     # licence handshake
curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8080/dashboard   # expect 302 → /login
```

---

## 14. Fix priority

| # | Fix                                              | Where                          |
|---|--------------------------------------------------|--------------------------------|
| 1 | Remove `member-key` from the auth `except` list; add an owner/registrator check | `app/Config/Filters.php:41`, `app/Controllers/Keys.php:687` |
| 2 | Give `resetDeviceKey()` a `where()` (or delete the method and re-point the UI to `/keys/reset`) | `app/Controllers/Keys.php:96` |
| 3 | Parameterise the reset-view SQL and stop accepting a password hash from the URL | `app/Views/User/verify_pass.php` |
| 4 | Replace the reset token with `random_bytes()`, remove the password from the URL, drop the redundant e-mail flow | `app/Controllers/User.php:682` |
| 5 | Rotate the DB credentials; untrack `.env`, `uploads/.env`, `conn.php`; add them to `.gitignore` | repo root |
| 6 | Delete the tracked `writable/debugbar/*.json` and ignore `writable/debugbar/` | repo root |
| 7 | Delete or port the legacy root scripts into authenticated controllers; they are `Referer`-guarded and SQL-injectable | repo root |
| 8 | Use `Config\App::$proxyIPs` + CI4's `getIPAddress()` instead of the bespoke `getUserIP()` | `app/Controllers/Auth.php:22`, `app/Models/UserModel.php:75` |
| 9 | Fix `getPrice` to multiply by devices; validate `loopcount` against the role allow-list | `app/Helpers/nata_helper.php:82`, `app/Controllers/Keys.php:503` |
| 10 | bcrypt cost 8 → 12; drop the MD5 pre-hash                       | `app/Helpers/nata_helper.php:10` |
| 11 | Remove `writable/`, `uploads/`, `project-root/`, `Protection.php` from the tracked tree; move `libunoimgui.so` to a release artifact | repo root |
| 12 | Fix `sql/xyto-update.sql` to create `modname` (not `mod_settings`) and ship a real schema dump — **done** (see §6.4.1) | `sql/xyto-update.sql` |
| 13 | Make `/connect` answer the client's JSON contract: no 500s, no `null` where a string is expected, `modname`/`mod_settings` fallback — **done** (see §6.4.1) | `app/Controllers/Connect.php`, `app/Common.php` |
