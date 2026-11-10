<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>
<style>
    :root {
        --primary-color: #a78bfa;
        --secondary-color: #a78bfa;
        --accent-color: #a78bfa;
        --dark-color: #0b0b0f;
        --light-color: #ffffff;
    }

    #particles-js {
        position: fixed;
        width: 100%;
        height: 100%;
        background-color: var(--dark-color);
        z-index: -1;
        top: 0;
        left: 0;
    }

    body {
        background: var(--dark-color);
        font-family: 'Chakra Petch', 'Segoe UI', sans-serif;
        color: var(--light-color);
        min-height: 100vh;
    }

    .card {
        background: rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        margin-top: 30px;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    .card:hover {
        transform: translateY(-8px) scale(1.005);
        box-shadow: 0 16px 48px 0 rgba(31, 38, 135, 0.5);
    }

    .card-header {
        background: var(--primary-color);
        color: var(--light-color);
        font-weight: 700;
        border-radius: 20px 20px 0 0;
        font-size: 1.4rem;
        letter-spacing: 1.2px;
        padding: 1.8rem 2.2rem;
        display: flex;
        align-items: center;
        text-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .card-header i {
        margin-right: 15px;
        font-size: 1.8rem;
    }

    .form-label {
        color: var(--accent-color);
        font-weight: 600;
        margin-bottom: 0.7rem;
        font-size: 1.05rem;
        transition: color 0.2s ease;
    }

    .form-control {
        background: rgba(255, 255, 255, 0.1);
        border: 1.5px solid rgba(255, 255, 255, 0.2);
        color: var(--light-color);
        border-radius: 12px;
        padding: 0.9rem 1.2rem;
        transition: all 0.3s ease;
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .form-control:focus {
        background: rgba(255, 255, 255, 0.15);
        border-color: var(--accent-color);
        box-shadow: 0 0 15px rgba(6, 182, 212, 0.4);
        color: var(--light-color);
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

    .btn-gradient-success {
        background: var(--shai-ok, #34d399);
    }

    .btn-gradient-success:hover {
        background: var(--shai-ok, #34d399);
    }

    .form-group {
        margin-bottom: 2rem;
    }

    .card {
        animation: fadeInScale 0.8s ease-out forwards;
    }

    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>

<div id="particles-js"></div>

<div class="row justify-content-center">
    <div class="col-lg-12">
        <?= $this->include('Layout/msgStatus') ?>
    </div>
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-lock"></i> Change password
            </div>
            <div class="card-body">
                <?= form_open() ?>
                <input type="hidden" name="password_form" value="1">
                <div class="form-group">
                    <label for="current" class="form-label">Current password</label>
                    <input type="password" name="current" id="current" class="form-control" placeholder="Enter current password">
                    <?php if ($validation->hasError('current')) : ?>
                        <small id="help-current" class="text-danger"><?= $validation->getError('current') ?></small>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">New password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter new password" aria-describedby="help-password">
                    <?php if ($validation->hasError('password')) : ?>
                        <small id="help-password" class="text-danger"><?= $validation->getError('password') ?></small>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="password2" class="form-label">Confirm password</label>
                    <input type="password" name="password2" id="password2" class="form-control" placeholder="Confirm new password" aria-describedby="help-password2">
                    <?php if ($validation->hasError('password2')) : ?>
                        <small id="help-password2" class="text-danger"><?= $validation->getError('password2') ?></small>
                    <?php endif; ?>
                </div>
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-gradient">Change password</button>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-person-circle"></i> Account Information
            </div>
            <div class="card-body">
                <?= form_open() ?>
                <input type="hidden" name="fullname_form" value="1">
                <div class="form-group">
                    <label for="fullname" class="form-label">Full name</label>
                    <input type="text" name="fullname" id="fullname" class="form-control" placeholder="Your Full Name" aria-describedby="help-fullname" value="<?= old('fullname') ?: ($user->fullname ?: '') ?>">
                    <?php if ($validation->hasError('fullname')) : ?>
                        <small id="help-fullname" class="text-danger"><?= $validation->getError('fullname') ?></small>
                    <?php endif; ?>
                </div>
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-gradient btn-gradient-success">Update account</button>
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