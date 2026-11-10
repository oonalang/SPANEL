<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>
<div class="shai-auth-wrap pt-5">
    <?= $this->include('Layout/msgStatus') ?>

    <div class="login-container shai-auth-card">
        <div class="shai-auth-head">
            <span class="shai-wordmark">SHAI Panel</span>
            <span class="shai-subtitle">first run</span>
        </div>

        <div class="shai-auth-body">
            <h1 class="shai-title mb-2">Set up the owner account</h1>
            <p class="text-muted mb-4" style="font-size:.9rem">
                This panel has no owner yet. Pick the username and password you want to use. To show you have access
                to the server, open <code><?= esc($tokenPath) ?></code> in your file manager and paste what is inside it
                below. The file is deleted once the account exists.
            </p>

            <?= form_open('setup', ['autocomplete' => 'off']) ?>
            <div class="form-group">
                <label for="setup_token">Setup token</label>
                <div class="shai-field">
                    <input type="text" class="form-control" name="setup_token" id="setup_token" placeholder="Paste the token from the file" required maxlength="64" autocomplete="off" spellcheck="false" value="<?= esc(old('setup_token') ?? '', 'attr') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="fullname">Display name</label>
                <div class="shai-field">
                    <input type="text" class="form-control" name="fullname" id="fullname" placeholder="Your name" required minlength="3" maxlength="60" value="<?= esc(old('fullname') ?? '', 'attr') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <div class="shai-field">
                    <input type="text" class="form-control" name="username" id="username" placeholder="Letters and numbers, 4-25" required minlength="4" maxlength="25" autocomplete="username" value="<?= esc(old('username') ?? '', 'attr') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <div class="shai-field">
                    <input type="email" class="form-control" name="email" id="email" placeholder="you@example.com" required maxlength="40" autocomplete="email" value="<?= esc(old('email') ?? '', 'attr') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="shai-field shai-pw">
                    <input type="password" class="form-control" name="password" id="password" placeholder="At least 10 characters, letters + numbers" required minlength="10" maxlength="45" autocomplete="new-password">
                    <button type="button" class="shai-pw-toggle" data-target="password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                </div>
                <div class="fx-meter" id="pwMeter" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
            </div>
            <div class="form-group">
                <label for="password2">Repeat password</label>
                <div class="shai-field">
                    <input type="password" class="form-control" name="password2" id="password2" placeholder="Repeat the password" required minlength="10" maxlength="45" autocomplete="new-password">
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-2">Create owner account</button>
            <?= form_close() ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
