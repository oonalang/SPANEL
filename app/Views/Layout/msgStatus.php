<style>
    .custom-alert {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: var(--shai-surface, #13131a);
        border: 1.5px solid var(--shai-line, #262630);
        border-left: 3px solid var(--shai-accent, #a78bfa);
        padding: 13px 14px;
        margin-bottom: 16px;
        color: var(--shai-text, #ececf1);
        font-family: 'Inter', sans-serif;
        font-size: .88rem;
        line-height: 1.5;
        animation: msgIn .2s ease both;
    }
    .custom-alert.danger  { border-left-color: var(--shai-danger, #f43f5e); }
    .custom-alert.success { border-left-color: var(--shai-ok, #34d399); }
    .custom-alert.warning { border-left-color: #fbbf24; }
    .custom-alert.secondary { border-left-color: var(--shai-line-2, #33333f); }

    .custom-alert .alert-content { display: flex; align-items: flex-start; gap: 12px; width: 100%; }
    .custom-alert .alert-icon { display: none; }
    .custom-alert .alert-message { flex: 1; }
    .custom-alert .alert-message strong { color: #fff; font-weight: 600; }

    .custom-alert .close-btn {
        background: none;
        border: none;
        color: var(--shai-dim, #8b8b99);
        cursor: pointer;
        padding: 0 2px;
        font-size: 1.05rem;
        line-height: 1;
        transition: color .12s ease;
    }
    .custom-alert .close-btn:hover { color: #fff; }

    @keyframes msgIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }
    @keyframes msgOut { to { opacity: 0; transform: translateY(-6px); } }
    .custom-alert.fade-out { animation: msgOut .25s ease forwards; }
</style>

<?php if (session()->getFlashdata('msgDanger')) : ?>
 <div class="custom-alert danger" role="alert">
 <div class="alert-content">
 <div class="alert-icon"></div>
 <div class="alert-message"><?= session()->getFlashdata('msgDanger') ?></div>
 <button type="button" class="close-btn" onclick="this.parentElement.parentElement.classList.add('fade-out')">×</button>
 </div>
 </div>
<?php elseif (session()->getFlashdata('msgSuccess')) : ?>
 <div class="custom-alert success" role="alert">
 <div class="alert-content">
 <div class="alert-icon"></div>
 <div class="alert-message"><?= session()->getFlashdata('msgSuccess') ?></div>
 <button type="button" class="close-btn" onclick="this.parentElement.parentElement.classList.add('fade-out')">×</button>
 </div>
 </div>
<?php elseif (session()->getFlashdata('msgWarning')) : ?>
 <div class="custom-alert warning" role="alert">
 <div class="alert-content">
 <div class="alert-icon"></div>
 <div class="alert-message"><?= session()->getFlashdata('msgWarning') ?></div>
 <button type="button" class="close-btn" onclick="this.parentElement.parentElement.classList.add('fade-out')">×</button>
 </div>
 </div>
<?php else : ?>
 <?php if (session()->has('userid')) : ?>
 <?php if (isset($messages)) : ?>
 <div class="custom-alert <?= $messages[1] ?>" role="alert">
 <div class="alert-content">
 <div class="alert-icon">
 <?php
                        switch($messages[1]) {
                            case 'success':
                                echo '';
                                break;
                            case 'danger':
                                echo '';
                                break;
                            case 'warning':
                                echo '';
                                break;
                            default:
                                echo 'ℹ';
                        }
                        ?>
 </div>
 <div class="alert-message"><?= $messages[0] ?></div>
 <button type="button" class="close-btn" onclick="this.parentElement.parentElement.classList.add('fade-out')">×</button>
 </div>
 </div>
 <?php else : ?>
 <div class="custom-alert secondary" role="alert">
 <div class="alert-content">
 <div class="alert-icon"></div>
 <div class="alert-message">Welcome <?= getName($user) ?></div>
 <button type="button" class="close-btn" onclick="this.parentElement.parentElement.classList.add('fade-out')">×</button>
 </div>
 </div>
 <?php endif; ?>
 <?php else : ?>
 <?php?>
 <?php?> 
 <?php?>
 <?php?>
 <?php endif; ?>
<?php endif; ?>
<?php // Auto-close and the countdown bar are handled by shai-fx.js.?>
