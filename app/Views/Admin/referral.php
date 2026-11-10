<?php
include(FCPATH . 'conn.php');

$sqli = "SELECT * FROM referral_code
ORDER BY id_reff DESC
LIMIT 1;";
$result = mysqli_query($conn, $sqli);
$id_reff = mysqli_fetch_assoc($result);

$sql = "SELECT Referral FROM referral_code";
$result = mysqli_query($conn, $sql);
$refcode = mysqli_fetch_assoc($result);
$row = $refcode;

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
        --light-color: #ffffff;
        --glass-background: rgba(255, 255, 255, 0.08);
        --glass-border: 1px solid rgba(255, 255, 255, 0.18);
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        --glass-blur: blur(10px);
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
        background: var(--glass-background);
        border-radius: 20px;
        box-shadow: var(--glass-shadow);
        border: var(--glass-border);
        backdrop-filter: var(--glass-blur);
        margin-top: 30px;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        color: var(--light-color);
    }
    .card:hover {
        transform: translateY(-8px) scale(1.005);
        box-shadow: 0 16px 48px 0 rgba(31, 38, 135, 0.5);
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
    .form-group {
        margin-bottom: 2rem;
    }
    .table {
        color: var(--light-color);
        background: transparent;
    }

    .table thead th {
        background: rgba(255, 255, 255, 0.1);
        color: var(--accent-color);
        font-weight: 600;
        border: none;
        padding: 1rem;
    }

    .table tbody td {
        border-color: rgba(255, 255, 255, 0.1);
        padding: 1rem;
    }

    .table tbody tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }
</style>

<div class="row">
    <div class="col-lg-12">
        <?= $this->include('Layout/msgStatus') ?>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card">
          <div class="card-header">
                <i class="bi bi-journal-plus me-2"></i> Generate <?= $title ?>
            </div>
            <div class="card-body">
                <?= form_open() ?>

                <div class="form-group mb-3">
                    <label for="set_saldo">You can set with multiple saldo</label>
                    <div class="input-group mt-2">
                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                        <input type="number" class="form-control" name="set_saldo" id="set_saldo" minlength="1" maxlength="11" value="5">
                    </div>
                    <?php if ($validation->hasError('set_saldo')) : ?>
                        <small id="help-saldo" class="text-danger"><?= $validation->getError('set_saldo') ?></small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group mb-3">
                    <label for="accExpire" class="form-label">Account expiration</label>
                    <?= form_dropdown(['class' => 'form-select', 'name' => 'accExpire', 'id' => 'accExpire'], $accExpire, old('accExpire') ?: '') ?>
                    <?php if ($validation->hasError('accExpire')) : ?>
                        <small id="help-accExpire" class="text-danger"><?= $validation->getError('accExpire') ?></small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group mb-3">
                    <label for="accLevel" class="form-label">Account level</label>
                    <?= form_dropdown(['class' => 'form-select', 'name' => 'accLevel', 'id' => 'accLevel'], $accLevel, old('accLevel') ?: '') ?>
                    <?php if ($validation->hasError('accLevel')) : ?>
                        <small id="help-accLevel" class="text-danger"><?= $validation->getError('accLevel') ?></small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-gradient">Create code</button>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <?php if ($code) : ?>
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history me-2"></i> History Generate - Total <?= $total_code ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover text-center" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Referral</th>
                                    <th>Hashed</th>
                                    <th>Saldo</th>
                                    <th>Level</th>
                                    <th>Expiration</th>
                                    <th>Used by</th>
                                    <th>Create by</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($code as $c) : ?>
                                    <tr>
                                        <td><?= $c->id_reff ?></td>
                                        <td><?= $c->Referral ?></td>
                                        <td><?= substr($c->code, 1, 15) ?></td>
                                        <td>₹<?= $c->set_saldo ?></td>
                                        <td><?= $c->level ?></td>
                                        <td><?= $c->acc_expiration ?></td>
                                        <td><?= $c->used_by ?></td>
                                        <td><?= $c->created_by ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<br><br>
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