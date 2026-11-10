<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>

<div id="shaiSplash" class="shai-splash">
    <span class="shai-wordmark">SHAI Panel</span>
    <div class="shai-bar"><div></div></div>
</div>
<script>
    setTimeout(function () {
        var s = document.getElementById('shaiSplash');
        if (s) { s.classList.add('shai-done'); setTimeout(function () { if (s.parentNode) s.parentNode.removeChild(s); }, 400); }
    }, 700);
</script>

<div class="shai-auth-wrap pt-5">
    <?= $this->include('Layout/msgStatus') ?>

    <div class="login-container shai-auth-card">
        <div class="shai-auth-head">
            <span class="shai-wordmark">SHAI Panel</span>
        </div>

        <div class="shai-auth-body">
            <div class="shai-switch " role="tablist">
                <a class="shai-switch-item is-active" href="<?= site_url('login') ?>">Sign in</a>
                <a class="shai-switch-item " href="<?= site_url('register') ?>">Register</a>
                <span class="shai-switch-thumb" aria-hidden="true"></span>
            </div>

            <h1 class="shai-title mb-4">Sign in</h1>

            <?= form_open() ?>
            <div class="form-group">
                <label for="username">Username</label>
                <div class="shai-field">
                    <input type="text" class="form-control" name="username" id="username" placeholder="Your username" required minlength="4">
                </div>
                <?php if ($validation->hasError('username')) : ?>
                    <div class="error-message"><?= $validation->getError('username') ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="shai-field shai-pw">
                    <input type="password" class="form-control" name="password" id="password" placeholder="Your password" required minlength="6">
                    <button type="button" class="shai-pw-toggle" data-target="password" aria-label="Show password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <?php if ($validation->hasError('password')) : ?>
                    <div class="error-message"><?= $validation->getError('password') ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="current_ip">Your IP address</label>
                <div class="shai-ip-row shai-field">
                    <input type="text" class="form-control shai-ip-input" id="current_ip"
                           value="<?= isset($user_ip) ? esc($user_ip) : '' ?>" readonly
                           data-ip="<?= isset($user_ip) ? esc($user_ip) : '' ?>">
                    <button type="button" class="btn shai-ip-toggle" id="ipToggle" aria-label="Hide your IP address">
                        <i class="bi bi-eye" id="ipToggleIcon"></i>
                    </button>
                </div>
                <div class="shai-ip-hint">Your session is locked to this IP address.</div>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="stay_log" id="stay_log" value="yes">
                <label class="form-check-label" for="stay_log" data-bs-toggle="tooltip" data-bs-placement="top" title="Keep me signed in for up to 10 hours">
                    Stay signed in <span class="shai-stay-tag">10H</span>
                </label>
            </div>

            <button type="submit" class="btn btn-login w-100">
                <i class="bi bi-box-arrow-in-right"></i> Sign in
            </button>

            <a href="<?= site_url('register') ?>" class="shai-alt-link">No account yet? Create one</a>
            <?= form_close() ?>
        </div>
    </div>
</div>

<script>
    (function () {
        var ipInput = document.getElementById('current_ip');
        var toggle  = document.getElementById('ipToggle');
        var icon    = document.getElementById('ipToggleIcon');
        if (!ipInput || !toggle) { return; }

        var ip = ipInput.getAttribute('data-ip') || '';
        var masked = false;
        var dots = function () { return new Array(ip.length + 1).join('\u2022'); };

        toggle.addEventListener('click', function () {
            masked = !masked;
            ipInput.value = masked ? dots() : ip;
            ipInput.classList.toggle('shai-masked', masked);
            if (icon) { icon.className = masked ? 'bi bi-eye-slash' : 'bi bi-eye'; }
            toggle.setAttribute('aria-label', masked ? 'Show your IP address' : 'Hide your IP address');
        });
    })();

    document.querySelectorAll('.shai-pw-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.getAttribute('data-target'));
            if (!input) { return; }
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.classList.toggle('is-on', show);
            btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });
</script>

<script>
    (function () {
        var card = document.querySelector('.shai-auth-card');
        if (!card) { return; }
        document.querySelectorAll('.shai-switch-item, .shai-alt-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                if (e.metaKey || e.ctrlKey || e.shiftKey || link.target === '_blank') { return; }
                e.preventDefault();
                var goesRight = link.getAttribute('href').indexOf('register') !== -1;
                card.classList.add(goesRight ? 'is-leaving-right' : 'is-leaving-left');
                setTimeout(function () { window.location.href = link.getAttribute('href'); }, 190);
            });
        });
    })();
</script>

<?= $this->endSection() ?>
