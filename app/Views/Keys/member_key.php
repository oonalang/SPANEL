<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center pt-5">
    <div class="col-lg-7">
        <div class="mk-card">
            <div class="mk-head">
                <span class="shai-wordmark">MEMBER<em>.</em>KEY</span>
                <span class="mk-badge">SELF-SERVICE</span>
            </div>

            <div class="mk-body">
                <h1 class="mk-title">Member key checker</h1>
                <p class="mk-lead">
                    Enter a member key to view its status or reset bound devices.
                </p>

                <?= form_open('member-key', ['class' => 'mk-form']) ?>
                    <div class="mk-input-group">
                        <input type="text" name="member_key" class="mk-input mono" placeholder="Member key"
                               value="<?= esc($searched) ?>" autocomplete="off" spellcheck="false" required>
                        <button type="submit" class="mk-btn">Check</button>
                    </div>
                <?= form_close() ?>

                <?php if ($result !== null && $result['found']) : ?>
                    <?php if ($result['reset']) : ?>
                        <div class="mk-note mk-note-ok">
                            <i class="bi bi-check2-circle"></i> Devices were reset. Connect again from your app to bind again.
                        </div>
                    <?php endif; ?>

                    <?php if (!$result['status']) : ?>
                        <div class="mk-note mk-note-bad">
                            <i class="bi bi-exclamation-triangle"></i> This key is currently blocked.
                        </div>
                    <?php endif; ?>

                    <div class="mk-grid">
                        <div class="mk-item">
                            <span class="mk-key">game</span>
                            <span class="mk-val"><?= esc($result['game']) ?></span>
                        </div>
                        <div class="mk-item">
                            <span class="mk-key">duration</span>
                            <span class="mk-val"><?= $result['duration'] == 239976 ? 'Unlimited' : (int) $result['duration'] . ' day' . ((int) $result['duration'] > 1 ? 's' : '') ?></span>
                        </div>
                        <div class="mk-item">
                            <span class="mk-key">devices</span>
                            <span class="mk-val"><?= (int) $result['devices'] ?> / <?= (int) $result['max_devices'] ?></span>
                        </div>
                        <div class="mk-item">
                            <span class="mk-key">expires</span>
                            <span class="mk-val"><?= $result['expired'] ? esc($result['expired']) : 'not started yet' ?></span>
                        </div>
                    </div>

                    <?php if ($result['devices'] > 0) : ?>
                        <form method="post" action="<?= site_url('member-key') ?>" class="mk-reset-form"
                              onsubmit="return confirm('Reset the bound devices for this key?');">
                            <?= form_hidden('member_key', $searched) ?>
                            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                            <button type="submit" name="action" value="reset" class="mk-btn mk-btn-reset">
                                <i class="bi bi-bootstrap-reboot"></i> Reset devices
                            </button>
                        </form>
                    <?php endif; ?>

                <?php elseif ($result !== null) : ?>
                    <div class="mk-note mk-note-bad">
                        <i class="bi bi-x-octagon"></i> No key found. Double-check the key and try again.
                    </div>
                <?php endif; ?>

                <div class="mk-links">
                    <a class="mk-link" href="https://t.me/Ascension_Shy" target="_blank" rel="noopener">
                        <i class="bi bi-telegram"></i> Support on Telegram
                    </a>
                    <a class="mk-link" href="<?= site_url('login') ?>">
                        <i class="bi bi-box-arrow-in-right"></i> Panel login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .mk-card {
        background: var(--shai-surface);
        border: 1.5px solid var(--shai-line);
    }
    .mk-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1.5px solid var(--shai-line);
    }
    .mk-head .shai-wordmark { font-size: .95rem; }
    .mk-badge {
        font-family: 'JetBrains Mono', monospace;
        font-size: .68rem;
        letter-spacing: .14em;
        padding: 3px 8px;
        border: 1px solid var(--shai-line-2);
        color: var(--shai-accent-2);
    }
    .mk-body { padding: 26px 22px 30px; }
    .mk-title { font-size: 1.5rem; margin: 0 0 10px; }
    .mk-lead { color: var(--shai-dim); font-size: .92rem; line-height: 1.6; margin: 0 0 22px; }

    .mk-input-group {
        display: flex;
        gap: 10px;
        margin-bottom: 24px;
    }
    .mk-input {
        flex: 1;
        min-width: 0;
        background: var(--shai-raised);
        border: 1.5px solid var(--shai-line);
        color: var(--shai-text);
        padding: .8rem 1rem;
        font-size: .92rem;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .mk-input:focus {
        outline: none;
        border-color: var(--shai-accent);
        box-shadow: 3px 3px 0 0 var(--shai-accent);
    }
    .mk-input::placeholder { color: var(--shai-dim); }

    .mk-btn {
        background: var(--shai-accent);
        border: 1.5px solid var(--shai-accent);
        color: #fff;
        padding: .8rem 1.4rem;
        font-size: .88rem;
        font-weight: 600;
        white-space: nowrap;
        transition: transform .12s ease, box-shadow .12s ease, background .15s ease;
    }
    .mk-btn:hover {
        background: var(--shai-accent-2);
        border-color: var(--shai-accent-2);
        color: #12121a;
        transform: translate(-1px, -1px);
        box-shadow: 3px 3px 0 0 var(--shai-accent);
    }
    .mk-btn:active { transform: none; box-shadow: none; }

    .mk-note {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: .85rem;
        padding: 12px 14px;
        border: 1.5px solid var(--shai-line);
        margin-bottom: 18px;
    }
    .mk-note-ok { color: var(--shai-ok); border-color: rgba(52, 211, 153, .35); }
    .mk-note-bad { color: var(--shai-danger); border-color: rgba(244, 63, 94, .35); }

    .mk-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1.5px;
        background: var(--shai-line);
        border: 1.5px solid var(--shai-line);
        margin-bottom: 22px;
    }
    .mk-item { background: var(--shai-surface); padding: 14px; }
    .mk-key {
        display: block;
        font-size: .68rem;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--shai-dim);
        margin-bottom: 6px;
    }
    .mk-val { font-size: .95rem; word-break: break-word; }

    .mk-reset-form { margin-bottom: 22px; }
    .mk-btn-reset {
        background: transparent;
        border-color: var(--shai-danger);
        color: var(--shai-danger);
    }
    .mk-btn-reset:hover {
        background: var(--shai-danger);
        border-color: var(--shai-danger);
        color: #fff;
        box-shadow: 3px 3px 0 0 rgba(244, 63, 94, .45);
    }

    .mk-links {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        padding-top: 4px;
    }
    .mk-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--shai-dim);
        font-size: .82rem;
        text-decoration: none;
        transition: color .15s ease;
    }
    .mk-link:hover { color: var(--shai-accent-2); }

    @media (max-width: 575.98px) {
        .mk-input-group { flex-direction: column; }
        .mk-btn { width: 100%; }
    }
</style>

<?= $this->endSection() ?>
