<style>
    .nb-panel {
        background: var(--shai-bg, #0b0b0f);
        border-bottom: 1.5px solid var(--shai-line, #262630);
        padding: 10px 0;
        z-index: 1040;
    }

    .nb-brand {
        font-family: 'Orbitron', 'Inter', sans-serif;
        font-weight: 900;
        font-size: .95rem;
        letter-spacing: .12em;
        color: var(--shai-text, #ececf1) !important;
        border: 1.5px solid var(--shai-line-2, #33333f);
        padding: 7px 12px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: border-color .12s ease, color .12s ease;
    }
    .nb-brand em { font-style: normal; color: var(--shai-accent-2, #a78bfa); }
    .nb-brand i { color: var(--shai-accent-2, #a78bfa); font-size: 1rem; }
    .nb-brand:hover {
        border-color: var(--shai-accent, #a78bfa);
        color: #fff !important;
    }

    .nb-panel .nav-link {
        font-family: 'Inter', sans-serif;
        font-weight: 500;
        font-size: .88rem;
        color: var(--shai-dim, #8b8b99) !important;
        padding: 7px 12px;
        margin: 0 2px;
        border: 1.5px solid transparent;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: color .12s ease, border-color .12s ease, background .12s ease;
    }
    .nb-panel .nav-link i { color: inherit; font-size: .95rem; }
    .nb-panel .nav-link:hover {
        color: var(--shai-text, #ececf1) !important;
        background: var(--shai-raised, #1a1a23);
    }
    .nb-panel .nav-link.nb-active,
    .nb-panel .nav-link.active {
        color: var(--shai-text, #ececf1) !important;
        border-color: var(--shai-line-2, #33333f);
        background: var(--shai-raised, #1a1a23);
    }

    .nb-panel .dropdown-menu {
        background: var(--shai-surface, #13131a);
        border: 1.5px solid var(--shai-line, #262630);
        border-radius: 0;
        padding: 6px 0;
        min-width: 220px;
        box-shadow: 6px 6px 0 0 rgba(0, 0, 0, .5);
        animation: nb-drop .16s ease;
        z-index: 1050;
    }
    @keyframes nb-drop { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: none; } }

    .nb-panel .dropdown-item {
        font-family: 'Inter', sans-serif;
        font-size: .87rem;
        font-weight: 500;
        color: var(--shai-text, #ececf1) !important;
        padding: 9px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: background .1s ease, color .1s ease, padding-left .1s ease;
    }
    .nb-panel .dropdown-item i { color: var(--shai-dim, #8b8b99); width: 16px; text-align: center; }
    .nb-panel .dropdown-item:hover {
        background: var(--shai-raised, #1a1a23);
        color: var(--shai-accent-2, #a78bfa) !important;
        padding-left: 20px;
    }
    .nb-panel .dropdown-item:hover i { color: var(--shai-accent-2, #a78bfa); }
    .nb-panel .dropdown-item.text-muted {
        color: var(--shai-dim, #8b8b99) !important;
        font-size: .7rem;
        letter-spacing: .1em;
        text-transform: uppercase;
        padding-top: 12px;
        padding-bottom: 4px;
    }
    .nb-panel .dropdown-item.text-danger { color: var(--shai-danger, #f43f5e) !important; }
    .nb-panel .dropdown-item.text-danger i { color: var(--shai-danger, #f43f5e); }
    .nb-panel .dropdown-item.text-danger:hover { background: rgba(244, 63, 94, .1); }
    .nb-panel .dropdown-divider { border-color: var(--shai-line, #262630); margin: 6px 0; }

    .nb-panel .navbar-toggler {
        border: 1.5px solid var(--shai-line-2, #33333f);
        background: transparent;
        padding: 7px 10px;
        border-radius: 0;
        transition: border-color .12s ease;
    }
    .nb-panel .navbar-toggler:hover { border-color: var(--shai-accent, #a78bfa); }
    .nb-panel .navbar-toggler:focus { box-shadow: none; }
    .nb-panel .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23a78bfa' stroke-width='2.5' stroke-linecap='square' d='M5 8h20M5 15h20M5 22h20'/%3e%3c/svg%3e");
    }

    @media (max-width: 767.98px) {
        .nb-panel .navbar-collapse {
            background: var(--shai-surface, #13131a);
            border: 1.5px solid var(--shai-line, #262630);
            margin-top: 10px;
            padding: 10px;
        }
        .nb-panel .nav-link { width: 100%; margin: 2px 0; }
    }

    @media (prefers-reduced-motion: reduce) {
        .nb-panel * { animation: none !important; transition: none !important; }
    }
</style>

<header>
    <nav class="navbar nb-panel navbar-expand-md navbar-dark py-0">
        <div class="container px-3">
            <a class="navbar-brand nb-brand" href="<?= site_url() ?>">
                SHAI Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <?php if (session()->has('userid') && isset($user) && is_object($user)) : ?>
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('keys') ?>">
                                <i class="bi bi-key"></i> Keys
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('keys/generate') ?>">
                                <i class="bi bi-plus-circle"></i> Generate
                            </a>
                        </li>
                    </ul>
                    <div class="float-right">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle pe-2"></i><?= getName($user) ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li>
                                        <a class="dropdown-item" href="<?= site_url('settings') ?>">
                                            <i class="bi bi-gear"></i> Settings
                                        </a>
                                    </li>
                                   
                                    <?php if($user->level == 1) : ?>
                                        <li>
                                            <a class="dropdown-item" href="<?= site_url('ManageShortenerLinks') ?>">
                                                <i class="bi bi-link-45deg"></i> Manage keygen links
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <li><hr class="dropdown-divider"></li>
                                    
                                    <?php if (($user->level == 1) || ($user->level ==2)) : ?>
                                        <li><a class="dropdown-item text-muted">Admin</a></li>
                                        
                                        <?php if ((int)$user->level === 1) : ?>
                                        <li>
                                            <a class="dropdown-item" href="<?= site_url('Server') ?>">
                                                <i class="bi bi-controller"></i> Online system
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    
                                        <li>
                                            <a class="dropdown-item" href="<?= site_url('lib') ?>">
                                                <i class="bi bi-cloud-upload"></i> Online LIB
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php if (($user->level == 1) || ($user->level == 2)) : ?>
                                        <li>
                                            <a class="dropdown-item" href="<?= site_url('admin/loader-catalog') ?>">
                                                <i class="bi bi-collection"></i> My loader catalog
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php if($user->level == 1) : ?>
                                        <li>
                                            <a class="dropdown-item" href="<?= site_url('ManageHackingAttempt') ?>">
                                                <i class="bi bi-shield-check"></i> Manage hacking attempt
                                            </a>
                                        </li>
                                        
                                        <li>
                                            <a class="dropdown-item" href="<?= site_url('PrivateDashboard') ?>">
                                                <i class="bi bi-speedometer2"></i> Private dashboard
                                            </a>
                                        </li>
                                        
                                        <li>
                                            <a class="dropdown-item" href="<?= site_url('admin/manage-users') ?>">
                                                <i class="bi bi-people"></i> Manage users
                                            </a>
                                        </li>
                                        
                                        
                                        <li><hr class="dropdown-divider"></li>
                                    <?php endif; ?>
                                    
                                    <li>
                                        <a class="dropdown-item" href="https://t.me/Ascension_Shy" target="_blank" rel="noopener">
                                            <i class="bi bi-telegram"></i> Get Support
                                        </a>
                                    </li>
                                    
                                    <li><hr class="dropdown-divider"></li>
                                    
                                    <li>
                                        <a class="dropdown-item text-danger" href="<?= site_url('logout') ?>">
                                            <i class="bi bi-box-arrow-in-left"></i> Logout
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                <?php else : ?>
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('login') ?>">
                                <i class="bi bi-box-arrow-in-right"></i> Sign in
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('register') ?>">
                                <i class="bi bi-person-plus"></i> Register
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<script>
    document.querySelectorAll('.nb-panel .nav-link').forEach(function (link) {
        if (link.href && link.href === window.location.href) {
            link.classList.add('nb-active');
        }
    });
</script>
