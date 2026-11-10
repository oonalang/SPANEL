<?php
include(FCPATH . 'conn.php');
include('mail.php');

// db_query() never throws, so a missing table can't turn this page into a 500.

$sql1 ="select * from onoff where id=1";
$result1 = db_query($conn, $sql1);
$userDetails1 = $result1 ? mysqli_fetch_assoc($result1) : ['status' => 'off', 'myinput' => ''];

$sql2 ="select * from _ftext where id=1";
$result2 = db_query($conn, $sql2);
$userDetails2 = $result2 ? mysqli_fetch_assoc($result2) : ['_status' => 'Safe', '_ftext' => ''];

$sql3 = "SELECT * FROM Feature WHERE id=1";
$result3 = db_query($conn, $sql3);
$ModFeatureStatus = $result3 ? mysqli_fetch_assoc($result3) : [];

// The mod name is in `modname`. Installs from the bundled SQL script may still keep it in `mod_settings`.
$row = null;
$sql_modname = "SELECT modname FROM modname WHERE id=1";
$result_modname = db_query($conn, $sql_modname);
if ($result_modname) {
    $row = mysqli_fetch_assoc($result_modname);
}
if (!$row) {
    $sql_modname = "SELECT modname FROM mod_settings WHERE id=1";
    $result_modname = db_query($conn, $sql_modname);
    if ($result_modname) {
        $row = mysqli_fetch_assoc($result_modname);
    }
}

if (!$row) {
    $row = ['modname' => 'Unknown Mod Name'];
}

// The controller should pass $user. If it doesn't, this falls back to level 1 (owner).
if (!isset($user)) {
    $user = (object)['level' => 1];
}

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
    .form-control, textarea.form-control {
        background: rgba(255,255,255,0.1);
        border: 1.5px solid rgba(255,255,255,0.2);
        color: var(--light-color);
        border-radius: 12px;
        padding: 0.9rem 1.2rem;
        transition: border-color 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
    }
    .form-control::placeholder {
        color: rgba(255,255,255,0.5);
    }
    .form-control:focus, textarea.form-control:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 15px rgba(6,182,212,0.4);
        background: rgba(255,255,255,0.15);
        color: var(--light-color);
        outline: none;
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
    .btn-gradient-danger {
        background: var(--shai-danger, #f43f5e);
        box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
    }
    .btn-gradient-danger:hover {
        background: var(--shai-danger, #f43f5e);
        box-shadow: 0 10px 25px rgba(220, 53, 69, 0.6);
    }
    .btn-gradient-warning {
        background: #fbbf24;
        box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4);
    }
    .btn-gradient-warning:hover {
        background: #fbbf24;
        box-shadow: 0 10px 25px rgba(255, 193, 7, 0.6);
    }
    .btn-gradient-success {
        background: var(--shai-ok, #34d399);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
    }
    .btn-gradient-success:hover {
        background: var(--shai-ok, #34d399);
        box-shadow: 0 10px 25px rgba(40, 167, 69, 0.6);
    }
    .form-group {
        margin-bottom: 2rem;
    }

    .hacks {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        font-size: 1.05rem;
        color: var(--light-color);
        margin-bottom: 15px;
        font-weight: 500;
        cursor: pointer;
    }
    .hacks > span {
        flex-grow: 1;
        margin-right: 15px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .hacks .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
        flex-shrink: 0;
    }
    .hacks .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .hacks .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.15);
        transition: .4s;
        border-radius: 34px;
        border: 1px solid rgba(255, 255, 255, 0.25);
        box-shadow: inset 0 2px 5px rgba(0,0,0,0.2);
    }
    .hacks .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: var(--light-color);
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    .hacks input:checked + .slider {
        background: var(--shai-accent, #7c3aed);
        border-color: var(--accent-color);
        box-shadow: inset 0 2px 5px rgba(0,0,0,0.3), 0 0 15px rgba(6,182,212,0.3);
    }
    .hacks input:focus + .slider {
        box-shadow: 0 0 1px var(--accent-color), 0 0 15px rgba(6,182,212,0.4);
    }
    .hacks input:checked + .slider:before {
        transform: translateX(26px);
        background-color: var(--light-color);
    }
    .info-display {
        font-weight: 500;
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 1rem;
        display: block;
        font-size: 1rem;
    }
    .info-display strong {
        color: var(--accent-color);
        background: rgba(6, 182, 212, 0.15);
        padding: 3px 8px;
        border-radius: 8px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .info-item {
        display: inline-block;
        margin-right: 15px;
        margin-bottom: 5px;
        padding: 3px 0;
    }
    .info-item strong {
        background: rgba(255,255,255,0.1);
        padding: 2px 6px;
        border-radius: 5px;
        font-weight: 600;
        color: var(--light-color);
    }
    .info-item strong.on {
        color: var(--accent-color);
    }
    .info-item strong.off {
        color: var(--primary-color);
    }
</style>

<div class="row">
 <div class="col-lg-12">
 <?= $this->include('Layout/msgStatus') ?>
 </div>
</div>
 <?php if(isset($user) && $user->level != 2) : ?>
 <div class="col-lg-6">
 <div class="card mb-4">
 <div class="card-header">
 <i class="bi bi-gear-fill me-2"></i> Server Based Mod
 </div>
 <div class="card-body">
 <?= form_open() ?>
 <input type="hidden" name="status_form" value="1">
 <div class="form-group">
 <span class="info-display">Current Maintenance Mode: <strong><?php echo $userDetails1['status']; ?></strong></span>
 <label for="radio" class="hacks">
 <span class="hacks-label-text">Maintenance mode</span>
 <div class="switch">
 <input type="checkbox" name="radios" id="radio" value="on" <?php if ($userDetails1['status'] == "on"){?> checked="checked" <?php } ?>>
 <span class="slider round"></span>
 </div>
 </label>
 </div>
 <div class="form-group">
 <span class="info-display">Offline Message: <strong><?php echo $userDetails1['myinput']; ?></strong></span>
 <label for="myInput" class="form-label">Update offline message</label>
 <textarea class="form-control" placeholder="Server is Under Maintenance" name="myInput" id="myInput" rows="2"></textarea>
 <?php if ($validation->hasError('myInput')) : ?>
 <small id="help-myInput" class="text-danger"><?= $validation->getError('myInput') ?></small>
 <?php endif; ?>
 </div>
 <div class="form-group mt-4">
 <button type="submit" class="btn btn-gradient">Update</button>
 </div>
 <?= form_close() ?>
 </div>
 </div>
 </div>
 <?php endif; ?>

 <div class="col-lg-6">
 <div class="card mb-4">
 <div class="card-header">
 <i class="bi bi-controller me-2"></i> Mod Feature Status
 </div>
 <div class="card-body">
 <?= form_open() ?>
 <input type="hidden" name="feature_form" value="1">
 <div class="form-group">
 <div class="info-display d-flex flex-wrap">Current Status: &nbsp;
 <span class="info-item">ESP - <strong class="<?php echo strtolower($ModFeatureStatus['ESP']); ?>"><?php echo $ModFeatureStatus['ESP']; ?></strong></span>
 <span class="info-item">Items - <strong class="<?php echo strtolower($ModFeatureStatus['Item']); ?>"><?php echo $ModFeatureStatus['Item']; ?></strong></span>
 <span class="info-item">AIM - <strong class="<?php echo strtolower($ModFeatureStatus['AIM']); ?>"><?php echo $ModFeatureStatus['AIM']; ?></strong></span>
 <span class="info-item">SilentAim - <strong class="<?php echo strtolower($ModFeatureStatus['SilentAim']); ?>"><?php echo $ModFeatureStatus['SilentAim']; ?></strong></span>
 <span class="info-item">BulletTrack - <strong class="<?php echo strtolower($ModFeatureStatus['BulletTrack']); ?>"><?php echo $ModFeatureStatus['BulletTrack']; ?></strong></span>
 <span class="info-item">Memory - <strong class="<?php echo strtolower($ModFeatureStatus['Memory']); ?>"><?php echo $ModFeatureStatus['Memory']; ?></strong></span>
 <span class="info-item">Floating Texts - <strong class="<?php echo strtolower($ModFeatureStatus['Floating']); ?>"><?php echo $ModFeatureStatus['Floating']; ?></strong></span>
 <span class="info-item">Setting - <strong class="<?php echo strtolower($ModFeatureStatus['Setting']); ?>"><?php echo $ModFeatureStatus['Setting']; ?></strong></span>
 </div>

 <label for="ESP" class="hacks">
 <span class="hacks-label-text">ESP</span>
 <div class="switch">
 <input type="checkbox" name="ESP" id="ESP" value="on" <?php if ($ModFeatureStatus['ESP'] == "on"){?> checked="checked" <?php } ?>>
 <span class="slider round"></span>
 </div>
 </label>
 <label for="Item" class="hacks">
 <span class="hacks-label-text">Items</span>
 <div class="switch">
 <input type="checkbox" name="Item" id="Item" value="on" <?php if ($ModFeatureStatus['Item'] == "on"){?> checked="checked" <?php } ?>>
 <span class="slider round"></span>
 </div>
 </label>
 <label for="AIM" class="hacks">
 <span class="hacks-label-text">Aim-Bot</span>
 <div class="switch">
 <input type="checkbox" name="AIM" id="AIM" value="on" <?php if ($ModFeatureStatus['AIM'] == "on"){?> checked="checked" <?php } ?>>
 <span class="slider round"></span>
 </div>
 </label>
 <label for="SilentAim" class="hacks">
 <span class="hacks-label-text">Silent Aim</span>
 <div class="switch">
 <input type="checkbox" name="SilentAim" id="SilentAim" value="on" <?php if ($ModFeatureStatus['SilentAim'] == "on"){?> checked="checked" <?php } ?>>
 <span class="slider round"></span>
 </div>
 </label>
 <label for="BulletTrack" class="hacks">
 <span class="hacks-label-text">Bullet Track</span>
 <div class="switch">
 <input type="checkbox" name="BulletTrack" id="BulletTrack" value="on" <?php if ($ModFeatureStatus['BulletTrack'] == "on"){?> checked="checked" <?php } ?>>
 <span class="slider round"></span>
 </div>
 </label>
 <label for="Memory" class="hacks">
 <span class="hacks-label-text">Memory</span>
 <div class="switch">
 <input type="checkbox" name="Memory" id="Memory" value="on" <?php if ($ModFeatureStatus['Memory'] == "on"){?> checked="checked" <?php } ?>>
 <span class="slider round"></span>
 </div>
 </label>
 <label for="Floating" class="hacks">
 <span class="hacks-label-text">Floating texts</span>
 <div class="switch">
 <input type="checkbox" name="Floating" id="Floating" value="on" <?php if ($ModFeatureStatus['Floating'] == "on"){?> checked="checked" <?php } ?>>
 <span class="slider round"></span>
 </div>
 </label>
 <label for="Setting" class="hacks">
 <span class="hacks-label-text">Settings</span>
 <div class="switch">
 <input type="checkbox" name="Setting" id="Setting" value="on" <?php if ($ModFeatureStatus['Setting'] == "on"){?> checked="checked" <?php } ?>>
 <span class="slider round"></span>
 </div>
 </label>
 </div>
 <div class="form-group mt-4">
 <button type="submit" class="btn btn-gradient btn-gradient-danger">Update</button>
 </div>
 <?= form_close() ?>
 </div>
 </div>
 </div>

 <div class="col-lg-6">
 <div class="card mb-4">
 <div class="card-header">
 <i class="bi bi-card-heading me-2"></i> Change Mod Name
 </div>
 <div class="card-body">
 <?= form_open() ?>
 <input type="hidden" name="modname_form" value="1">
 <div class="form-group">
 <span class="info-display">Current Mod Name: <strong><?php echo $row['modname']; ?></strong></span>
 <label for="modname_input" class="form-label">New mod name</label>
 <input type="text" name="modname" id="modname_input" class="form-control" placeholder="Enter Your New Mod Name" aria-describedby="help-modname" REQUIRED>
 <?php if ($validation->hasError('modname')) : ?>
 <small id="help-modname" class="text-danger"><?= $validation->getError('modname') ?></small>
 <?php endif; ?>
 </div>
 <div class="form-group mt-4">
 <button type="submit" class="btn btn-gradient btn-gradient-warning">Update</button>
 </div>
 <?= form_close() ?>
 </div>
 </div>
 </div>

 <div class="col-lg-6">
 <div class="card mb-4">
 <div class="card-header">
 <i class="bi bi-chat-text me-2"></i> Change Floating Text
 </div>
 <div class="card-body">
 <?= form_open() ?>
 <input type="hidden" name="_ftext" value="1">
 <div class="form-group">
 <span class="info-display">Current Mod Status: <strong><?php echo $userDetails2['_status']; ?></strong></span>
 <label for="_ftextr" class="hacks">
 <span class="hacks-label-text">Safe mode</span>
 <div class="switch">
 <input type="checkbox" name="_ftextr" id="_ftextr" value="Safe" <?php if ($userDetails2['_status'] == "Safe"){?> checked="checked" <?php } ?>>
 <span class="slider round"></span>
 </div>
 </label>
 </div>
 <div class="form-group">
 <span class="info-display">Current Floating Text: <strong><?php echo $userDetails2['_ftext']; ?></strong></span>
 <label for="_ftext_input" class="form-label">New floating text</label>
 <input type="text" name="_ftext" id="_ftext_input" class="form-control" placeholder="Give Feedback Else Key Removed!" aria-describedby="help-_ftext" REQUIRED>
 <?php if ($validation->hasError('_ftext')) : ?>
 <small id="help-_ftext" class="text-danger"><?= $validation->getError('_ftext') ?></small>
 <?php endif; ?>
 </div>
 <div class="form-group mt-4">
 <button type="submit" class="btn btn-gradient btn-gradient-success">Update</button>
 </div>
 <?= form_close() ?>
 </div>
 </div>
 </div>
</div>
</br>
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