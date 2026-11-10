<?= $this->extend('Layout/Starter') ?>
<?= $this->section('content') ?>

<div class="shai-auth-wrap pt-5">
    <?= $this->include('Layout/msgStatus') ?>

    <div class="register-container shai-auth-card">
        <div class="shai-auth-head">
            <span class="shai-wordmark">SHAI Panel</span>
        </div>

        <div class="shai-auth-body">
            <div class="shai-switch is-register" role="tablist">
                <a class="shai-switch-item " href="<?= site_url('login') ?>">Sign in</a>
                <a class="shai-switch-item is-active" href="<?= site_url('register') ?>">Register</a>
                <span class="shai-switch-thumb" aria-hidden="true"></span>
            </div>

            <h1 class="shai-title mb-4">Create an account</h1>

                    <?= form_open() ?>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="shai-field">
                            <input type="email" class="form-control" name="email" id="email" placeholder="you@example.com" minlength="13" maxlength="40" value="<?= old('email') ?>" required>
                        </div>
                        <?php if ($validation->hasError('email')) : ?>
                            <div class="error-message"><?= $validation->getError('email') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="shai-field">
                            <input type="text" class="form-control" name="username" id="username" placeholder="min. 4 characters" minlength="4" maxlength="24" value="<?= old('username') ?>" required>
                        </div>
                        <?php if ($validation->hasError('username')) : ?>
                            <div class="error-message"><?= $validation->getError('username') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="fullname">Full name</label>
                        <div class="shai-field">
                            <input type="text" class="form-control" name="fullname" id="fullname" placeholder="Your full name" minlength="4" maxlength="24" value="<?= old('fullname') ?>" required>
                        </div>
                        <?php if ($validation->hasError('fullname')) : ?>
                            <div class="error-message"><?= $validation->getError('fullname') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="shai-field shai-pw">
                            <input type="password" class="form-control" name="password" id="password" placeholder="min. 8 characters" minlength="8" maxlength="24" required>
                            <button type="button" class="shai-pw-toggle" data-target="password" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <?php if ($validation->hasError('password')) : ?>
                            <div class="error-message"><?= $validation->getError('password') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password2">Confirm password</label>
                        <div class="shai-field shai-pw">
                            <input type="password" class="form-control" name="password2" id="password2" placeholder="Repeat password" minlength="8" maxlength="24" required>
                            <button type="button" class="shai-pw-toggle" data-target="password2" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <?php if ($validation->hasError('password2')) : ?>
                            <div class="error-message"><?= $validation->getError('password2') ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-register w-100">
                        <i class="bi bi-person-plus"></i> Create account
                    </button>

                    <a href="<?= site_url('login') ?>" class="shai-alt-link">Already have an account? Sign in</a>
                    <?= form_close() ?>
        </div>
    </div>
</div>

<script>
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
