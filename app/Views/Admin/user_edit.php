<?php
include('mail.php');
?>

<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>
<div id="particles-js"></div>
<style>
    :root {
        --primary-color: #a78bfa;
        --secondary-color: #a78bfa;
        --accent-color: #a78bfa;
        --dark-color: #0b0b0f;
        --light-color: #f8fafc;
        --glass-background: rgba(255, 255, 255, 0.08);
        --glass-border: 1px solid rgba(255, 255, 255, 0.18);
        --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        --glass-blur: blur(10px);
    }
    #particles-js {
        position: fixed;
        width: 100%;
        height: 100%;
        background-color: var(--dark-color);
        background-image: url('');
        background-repeat: no-repeat;
        background-size: cover;
        background-position: 50% 50%;
        z-index: -1;
        top: 0;
        left: 0;
    }
    body {
        font-family: 'Chakra Petch', 'Segoe UI', sans-serif;
        background-color: var(--dark-color);
        color: var(--light-color);
        overflow-x: hidden;
        min-height: 100vh;
    }
    .card {
        background: var(--glass-background);
        border-radius: 20px;
        box-shadow: var(--glass-shadow);
        border: var(--glass-border);
        backdrop-filter: var(--glass-blur);
        -webkit-backdrop-filter: var(--glass-blur);
        margin-top: 30px;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        overflow: hidden;
    }
    .card:hover {
        transform: translateY(-8px) scale(1.005);
        box-shadow: 0 16px 48px 0 rgba(0, 0, 0, 0.5);
    }
    .card-header {
        background: var(--primary-color) !important;
        color: var(--light-color) !important;
        font-weight: 700;
        border-radius: 20px 20px 0 0;
        font-size: 1.4rem;
        letter-spacing: 1.2px;
        padding: 1.8rem 2.2rem;
        display: flex;
        align-items: center;
        text-shadow: 0 2px 5px rgba(0,0,0,0.2);
        position: relative;
        overflow: hidden;
    }
    .card-header::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.1);
        mix-blend-mode: overlay;
        opacity: 0.5;
        z-index: 0;
    }
    .card-header * {
        position: relative;
        z-index: 1;
    }
    .card-header i {
        margin-right: 15px;
        font-size: 1.8rem;
        color: var(--light-color);
        transition: transform 0.3s ease;
    }
    .card-header:hover i {
        transform: rotate(5deg) scale(1.1);
    }
    .form-label {
        color: var(--accent-color);
        font-weight: 600;
        margin-bottom: 0.7rem;
        font-size: 1.05rem;
        transition: color 0.2s ease;
    }
    .form-control, .form-select {
        background: rgba(255,255,255,0.1);
        border: 1.5px solid rgba(255,255,255,0.2);
        color: var(--light-color);
        border-radius: 12px;
        padding: 0.9rem 1.2rem;
        transition: border-color 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
    }
    .form-control::placeholder, .form-select option {
        color: rgba(255,255,255,0.5);
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 15px rgba(6,182,212,0.4);
        background: rgba(255,255,255,0.15);
        color: var(--light-color);
        outline: none;
    }
    .form-select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
    }
    .form-select option {
        background-color: var(--dark-color);
        color: var(--light-color);
    }
    .input-group-text {
        background: var(--primary-color);
        color: var(--light-color);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px 0 0 12px;
        padding: 0.9rem 1.2rem;
        font-weight: 600;
    }
    .input-group .form-control {
        border-radius: 0 12px 12px 0;
    }
    .text-danger {
        color: #ff6b6b !important;
        font-size: 0.9rem;
        margin-top: 0.5rem;
        display: block;
        animation: fadeInError 0.3s ease-out;
    }
    @keyframes fadeInError {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .btn-gradient {
        background: var(--primary-color);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        padding: 1rem 2.5rem;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    .btn-gradient::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: var(--primary-color);
        transition: all 0.4s ease;
        z-index: -1;
    }
    .btn-gradient:hover::before {
        left: 0;
    }
    .btn-gradient:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.6);
    }
    .btn-back {
        background: rgba(255, 255, 255, 0.15) !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        color: var(--light-color) !important;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        margin-left: 1rem;
    }
    .btn-back i {
        margin-right: 8px;
        font-size: 1rem;
    }
    .btn-back:hover {
        background: rgba(255, 255, 255, 0.25) !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
</style>

<div class="row justify-content-center pt-3">
    <div class="col-lg-8">
        <?= $this->include('Layout/msgStatus') ?>
    </div>
    <div class="col-lg-8 mb-3">
        <div class="card mb-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Acc Info &middot; <?= getName($target) ?></span>
                <a class="btn btn-back" href="<?= site_url('admin/manage-users') ?>"><i class="bi bi-arrow-left"></i> Back</a>
            </div>
            <div class="card-body">
                <?= form_open() ?>
                <input type="hidden" name="user_id" value="<?= $target->id_users ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" id="username" class="form-control" placeholder="" aria-describedby="help-username" value="<?= old('username') ?: $target->username ?>">
                        <?php if ($validation->hasError('username')) : ?>
                            <small id="help-username" class="form-text text-danger"><?= $validation->getError('username') ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fullname" class="form-label">Fullname</label>
                        <input type="text" name="fullname" id="fullname" class="form-control" placeholder="" aria-describedby="help-fullname" value="<?= old('fullname') ?: $target->fullname ?>">
                        <?php if ($validation->hasError('fullname')) : ?>
                            <small id="help-fullname" class="form-text text-danger"><?= $validation->getError('fullname') ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="level" class="form-label">Roles</label>
                        <?php $sel_level = ['' => 'Select role', '1' => 'Owner', '2' => 'Admin', '3' => 'Reseller']; ?>
                        <?= form_dropdown(['class' => 'form-select', 'name' => 'level', 'id' => 'level'], $sel_level, $target->level) ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <?php $sel_status = ['' => 'Select status', '2' => 'Banned/Block', '1' => 'Active', '3' => 'Expired',]; ?>
                        <?= form_dropdown(['class' => 'form-select', 'name' => 'status', 'id' => 'status'], $sel_status, $target->status) ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="saldo" class="form-label">Saldo</label>
                        <input type="number" name="saldo" id="saldo" class="form-control" placeholder="" aria-describedby="help-saldo" value="<?= old('saldo') ?: $target->saldo ?>">
                        <?php if ($validation->hasError('saldo')) : ?>
                            <small id="help-saldo" class="form-text text-danger"><?= $validation->getError('saldo') ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="uplink" class="form-label">Uplink</label>
                        <input type="text" name="uplink" id="uplink" class="form-control" placeholder="" aria-describedby="help-uplink" value="<?= old('uplink') ?: $target->uplink ?>">
                        <?php if ($validation->hasError('uplink')) : ?>
                            <small id="help-uplink" class="form-text text-danger"><?= $validation->getError('uplink') ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="expiration" class="form-label">Expiration</label>
                        <input type="text" name="expiration" id="expiration" class="form-control" placeholder="" aria-describedby="help-expiration" value="<?= old('expiration') ?: $target->expiration_date ?>">
                        <?php if ($validation->hasError('expiration')) : ?>
                            <small id="help-expiration" class="form-text text-danger"><?= $validation->getError('expiration') ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-12 mt-3">
                        <button type="submit" class="btn btn-gradient">Update account information</button>
                    </div>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<script>
    particlesJS('particles-js', {
        "particles": {
            "number": {
                "value": 80,
                "density": {
                    "enable": true,
                    "value_area": 800
                }
            },
            "color": {
                "value": "#ffffff"
            },
            "shape": {
                "type": "circle",
                "stroke": {
                    "width": 0,
                    "color": "#000000"
                }
            },
            "opacity": {
                "value": 0.5,
                "random": false,
                "anim": {
                    "enable": false,
                    "speed": 1,
                    "opacity_min": 0.1,
                    "sync": false
                }
            },
            "size": {
                "value": 3,
                "random": true,
                "anim": {
                    "enable": false,
                    "speed": 40,
                    "size_min": 0.1,
                    "sync": false
                }
            },
            "line_linked": {
                "enable": true,
                "distance": 150,
                "color": "#ffffff",
                "opacity": 0.4,
                "width": 1
            },
            "move": {
                "enable": true,
                "speed": 6,
                "direction": "none",
                "random": false,
                "straight": false,
                "out_mode": "out",
                "bounce": false,
                "attract": {
                    "enable": false,
                    "rotateX": 600,
                    "rotateY": 1200
                }
            }
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": {
                "onhover": {
                    "enable": true,
                    "mode": "repulse"
                },
                "onclick": {
                    "enable": true,
                    "mode": "push"
                },
                "resize": true
            },
            "modes": {
                "grab": {
                    "distance": 400,
                    "line_linked": {
                        "opacity": 1
                    }
                },
                "bubble": {
                    "distance": 400,
                    "size": 40,
                    "duration": 2,
                    "opacity": 8,
                    "speed": 3
                },
                "repulse": {
                    "distance": 200,
                    "duration": 0.4
                },
                "push": {
                    "particles_nb": 4
                },
                "remove": {
                    "particles_nb": 2
                }
            }
        },
        "retina_detect": true
    });
</script>
<?= $this->endSection() ?>