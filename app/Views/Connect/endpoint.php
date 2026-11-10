<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center endpoint-layout">
    <div class="col-12 col-xl-9">
        <div class="endpoint-card">
            <div class="endpoint-head">
                <span class="shai-wordmark">SHAI<em>.</em>ENDPOINT</span>
                <span class="endpoint-badge"><?= $maintenance ? 'MAINTENANCE' : 'ONLINE' ?></span>
            </div>

            <div class="endpoint-body">
                <h1 class="endpoint-title">API Endpoint</h1>
                <p class="endpoint-lead">
                    License verification endpoint used by the client application to validate keys,
                    register devices and pull module settings.
                </p>

                <div class="endpoint-row">
                    <span class="endpoint-method">POST</span>
                    <span class="endpoint-note">only POST requests are accepted</span>
                </div>

                <div class="endpoint-grid">
                    <div class="endpoint-item">
                        <span class="endpoint-key">client</span>
                        <span class="endpoint-val"><?= esc($info['web_info']['_client']) ?></span>
                    </div>
                    <div class="endpoint-item">
                        <span class="endpoint-key">version</span>
                        <span class="endpoint-val"><?= esc($info['web_info']['version']) ?></span>
                    </div>
                    <div class="endpoint-item">
                        <span class="endpoint-key">license</span>
                        <span class="endpoint-val"><?= esc(trim($info['web_info']['license'])) ?></span>
                    </div>
                    <div class="endpoint-item">
                        <span class="endpoint-key">status</span>
                        <span class="endpoint-val"><?= $maintenance ? 'maintenance' : 'operational' ?></span>
                    </div>
                </div>

                <h2 class="endpoint-sub">Request parameters</h2>
                <div class="endpoint-code mono">
                    game=MLBB<br>
                    user_key=YOUR_KEY<br>
                    serial=DEVICE_SERIAL
                </div>

                <h2 class="endpoint-sub">Example</h2>
                <div class="endpoint-code mono">
                    curl -X POST <?= esc(site_url('connect')) ?> \<br>
                    &nbsp;&nbsp;-d 'game=MLBB&amp;user_key=YOUR_KEY&amp;serial=DEVICE_SERIAL'
                </div>

                <h2 class="endpoint-sub">Developers</h2>
                <div class="endpoint-links">
                    <a class="endpoint-link" href="https://t.me/Ascension_Shy" target="_blank" rel="noopener">
                        <i class="bi bi-telegram"></i> Telegram
                    </a>
                    <a class="endpoint-link" href="<?= site_url('login') ?>">
                        <i class="bi bi-box-arrow-in-right"></i> Panel login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .endpoint-card {
        background: var(--shai-surface);
        border: 1.5px solid var(--shai-line);
    }
    .endpoint-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1.5px solid var(--shai-line);
    }
    .endpoint-head .shai-wordmark { font-size: .95rem; }
    .endpoint-badge {
        font-family: 'JetBrains Mono', monospace;
        font-size: .68rem;
        letter-spacing: .14em;
        padding: 3px 8px;
        border: 1px solid var(--shai-line-2);
        color: var(--shai-ok);
    }
    .endpoint-body { padding: 26px 22px 30px; }
    .endpoint-title { font-size: 1.5rem; margin: 0 0 10px; }
    .endpoint-lead { color: var(--shai-dim); font-size: .92rem; line-height: 1.6; margin: 0 0 22px; }
    .endpoint-row { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
    .endpoint-method {
        font-family: 'JetBrains Mono', monospace;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .1em;
        padding: 5px 12px;
        color: #12121a;
        background: var(--shai-ok);
    }
    .endpoint-note { color: var(--shai-dim); font-size: .85rem; }
    .endpoint-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1.5px;
        background: var(--shai-line);
        border: 1.5px solid var(--shai-line);
        margin-bottom: 26px;
    }
    .endpoint-item { background: var(--shai-surface); padding: 14px; }
    .endpoint-key {
        display: block;
        font-size: .68rem;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--shai-dim);
        margin-bottom: 6px;
    }
    .endpoint-val { font-family: 'JetBrains Mono', monospace; font-size: .85rem; }
    .endpoint-sub {
        font-size: .72rem;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--shai-dim);
        margin: 0 0 10px;
    }
    .endpoint-code {
        background: var(--shai-bg);
        border: 1.5px solid var(--shai-line);
        padding: 14px;
        font-size: .8rem;
        line-height: 1.7;
        margin-bottom: 26px;
        overflow-x: auto;
    }
    .endpoint-links { display: flex; flex-wrap: wrap; gap: 10px; }
    .endpoint-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border: 1.5px solid var(--shai-line-2);
        font-size: .85rem;
        font-weight: 600;
        color: var(--shai-text) !important;
        transition: border-color .15s ease, color .15s ease, transform .1s ease;
    }
    .endpoint-link:hover {
        border-color: var(--shai-accent);
        color: var(--shai-accent-2) !important;
        transform: translateY(-1px);
    }

    .endpoint-layout { padding-top:clamp(16px,4vw,48px); padding-bottom:48px; }
    .endpoint-card { border-radius:24px; overflow:hidden; background:#222429; border:1px solid #41434a; box-shadow:12px 12px 26px rgba(0,0,0,.4),-5px -5px 18px rgba(255,255,255,.035); }
    .endpoint-head { background:#25272c; padding:20px 24px; }
    .endpoint-head .shai-wordmark { font-size:clamp(.95rem,2vw,1.15rem); font-weight:750; color:#f6f6f6; letter-spacing:.04em; }
    .endpoint-badge { border-radius:999px; color:#fff; border-color:#62656b; padding:7px 12px; letter-spacing:.06em; }
    .endpoint-body { padding:clamp(20px,4vw,42px); }
    .endpoint-title { font-size:clamp(1.65rem,4vw,2.3rem); font-weight:700; color:#fff; }
    .endpoint-lead { font-size:clamp(.94rem,2vw,1.05rem); color:#c3c5c9; max-width:65ch; }
    .endpoint-method { background:#f5f5f5; color:#151515; border-radius:9px; }
    .endpoint-note { color:#bcbec3; }
    .endpoint-grid { background:transparent; border:0; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
    .endpoint-item { min-width:0; background:#25272c; border:1px solid #41434a; border-radius:16px; padding:18px; box-shadow:inset 3px 3px 8px rgba(0,0,0,.26),inset -2px -2px 7px rgba(255,255,255,.03); }
    .endpoint-key { color:#bfc1c7; }
    .endpoint-val { color:#fff; overflow-wrap:anywhere; }
    .endpoint-sub { font-size:.85rem; letter-spacing:.04em; color:#d7d8db; font-weight:650; }
    .endpoint-code { background:#18191d; color:#f0f0f1; border:1px solid #41434a; border-radius:14px; padding:18px; white-space:normal; overflow-wrap:anywhere; font-size:clamp(.8rem,1.8vw,.95rem); }
    .endpoint-link { border-radius:13px; padding:13px 17px; background:#292b30; border-color:#484a50; box-shadow:4px 4px 10px rgba(0,0,0,.3),-2px -2px 7px rgba(255,255,255,.035); }
    .endpoint-link:hover { border-color:#fff; color:#fff !important; }
    @media(max-width:480px) { .endpoint-grid { gap:9px; } .endpoint-item { padding:13px; } .endpoint-head { padding:16px; } .endpoint-body { padding:18px; } .endpoint-row { align-items:flex-start; } }
</style>
<?= $this->endSection() ?>
