<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>

<style>
    :root {
        --primary-color: #a78bfa;
        --secondary-color: #a78bfa;
        --accent-color: #a78bfa;
        --dark-color: #0b0b0f;
        --light-color: #ffffff;
        --glass-background: rgba(255, 255, 255, 0.05);
        --glass-border: 1px solid rgba(255, 255, 255, 0.1);
        --glass-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        --glass-blur: blur(20px);
    }
    body {
        background: var(--dark-color);
        font-family: 'Chakra Petch', 'Segoe UI', sans-serif;
        overflow-x: hidden;
        position: relative;
        min-height: 100vh;
    }
    #particles-js {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }
    .card {
        background: var(--glass-background);
        border-radius: 24px;
        box-shadow: var(--glass-shadow);
        border: var(--glass-border);
        backdrop-filter: var(--glass-blur);
        margin-top: 30px;
        transition: all 0.3s ease-in-out;
    }
    .card:hover {
        transform: translateY(-5px) scale(1.005);
        box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.3);
    }
    .card-header {
        background: var(--primary-color);
        color: var(--light-color);
        font-weight: 600;
        border-radius: 24px 24px 0 0;
        font-size: 1.5rem;
        letter-spacing: 1px;
        padding: 1.5rem 2rem;
    }
    .form-label {
        color: var(--light-color);
        font-weight: 500;
        margin-bottom: 0.8rem;
        font-size: 1.1rem;
    }
    .form-control, .form-select {
        background: rgba(255,255,255,0.1);
        border: 1.5px solid rgba(255,255,255,0.15);
        color: var(--light-color);
        border-radius: 12px;
        padding: 0.85rem 1.2rem;
        transition: border 0.3s, background 0.3s, box-shadow 0.3s;
        font-size: 1rem;
    }
    .form-control::placeholder, .form-select::placeholder {
        color: rgba(255,255,255,0.5);
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 15px rgba(6, 182, 212, 0.4);
        background: rgba(255,255,255,0.15);
        color: var(--light-color);
    }
    .text-danger {
        color: #ef4444 !important;
        font-size: 0.9rem;
        margin-top: 0.5rem;
        display: block;
        text-shadow: 0 0 5px rgba(239, 68, 68, 0.3);
    }
    .btn-outline-dark {
        background: var(--primary-color);
        color: #fff;
        border: none;
        border-radius: 16px;
        font-weight: 600;
        padding: 0.9rem 2rem;
        transition: all 0.3s ease;
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 1.05rem;
    }
    .btn-outline-dark:hover {
        background: var(--primary-color);
        color: #fff;
        transform: translateY(-3px) scale(1.03);
        box-shadow: 0 12px 25px rgba(79, 70, 229, 0.4);
    }
    .key-popup-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0; top: 0; width: 100vw; height: 100vh;
        background: rgba(26,26,46,0.7);
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.4s;
    }
    .key-popup-content {
        background: rgba(255,255,255,0.05);
        border-radius: 24px;
        padding: 2.5rem 3rem;
        text-align: center;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(20px);
        animation: popIn 0.5s;
    }
    .key-popup-content h4 {
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        font-size: 1.8rem;
    }
    .key-popup-content .key-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--accent-color);
        background: rgba(0,0,0,0.12);
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        word-break: break-all;
        letter-spacing: 1px;
    }
    .key-popup-content .btn {
        margin-top: 1rem;
        background: var(--primary-color);
        color: #fff;
        border: none;
        border-radius: 16px;
        font-weight: 600;
        padding: 0.9rem 2rem;
        transition: all 0.3s ease;
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 1.05rem;
    }
    .key-popup-content .btn:hover {
        transform: translateY(-3px) scale(1.03);
        box-shadow: 0 12px 25px rgba(79, 70, 229, 0.4);
    }
    .key-popup-content .btn-secondary {
        background-color: rgba(255, 255, 255, 0.15) !important;
        border-color: rgba(255, 255, 255, 0.2) !important;
        color: var(--light-color) !important;
        transition: all 0.2s ease;
        margin-left: 10px;
    }
    .key-popup-content .btn-secondary:hover {
        background-color: rgba(255, 255, 255, 0.25) !important;
        transform: translateY(-1px);
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes popIn {
        0% { transform: scale(0.7); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>

<div id="particles-js"></div>

<div class="row justify-content-center">
 <div class="col-lg-6">
 <?= $this->include('Layout/msgStatus') ?>
 <div class="card">
 <div class="card-header">
 <div class="row">
 <div class="col pt-1">
 <i class="bi bi-key"></i> Create License
 </div>
 <div class="col text-end">
 <a class="btn btn-secondary btn-sm" href="<?= site_url('keys') ?>"><i class="bi bi-people"></i> View keys</a>
 </div>
 </div>
 </div>
 <div class="card-body">
 <?= form_open() ?>
 <div class="row">
 <div class="form-group col-lg-6 mb-3">
 <label for="game" class="form-label">Game</label>
 <?= form_dropdown(['class' => 'form-select', 'name' => 'game', 'id' => 'game'], $game, old('game') ?: '') ?>
 <?php if ($validation->hasError('game')) : ?>
 <small id="help-game" class="text-danger"><?= $validation->getError('game') ?></small>
 <?php endif; ?>
 </div>
 <div class="form-group col-lg-6 mb-3">
 <label for="max_devices" class="form-label">Max devices</label>
 <input type="number" name="max_devices" id="max_devices" class="form-control" placeholder="1" value="<?= old('max_devices') ?: 1 ?>">
 <?php if ($validation->hasError('max_devices')) : ?>
 <small id="help-max_devices" class="text-danger"><?= $validation->getError('max_devices') ?></small>
 <?php endif; ?>
 </div>
 </div>
 <div class="form-group mb-3">
 <label for="duration" class="form-label">Duration</label>
 <?= form_dropdown(['class' => 'form-select', 'name' => 'duration', 'id' => 'duration'], $duration, old('duration') ?: '') ?>
 <?php if ($validation->hasError('duration')) : ?>
 <small id="help-duration" class="text-danger"><?= $validation->getError('duration') ?></small>
 <?php endif; ?>
 </div>
 <div class="form-group mb-3">
 <div class="form-check">
 <input class="form-check-input" type="checkbox" minlength="4" maxlength="16" value="" name="check" onchange="fupi(this)" id="check">
 <label class="form-check-label" for="check">Custom key</label>
 </div>
 </div>
 
 <div class="form-group mb-3" id="custom-key-group" style="display:none;">
 <label for="custom" id="cuslabel" class="form-label">Your key</label>
 <input type="text" minlength="4" maxlength="16" name="cuslicense" class="form-control" id="custom" autocomplete="off">
 </div>

 <div class="form-group mb-3" id="bulk-keys-group">
 <label for="hulala" id="labula" class="form-label">Bulk keys</label>
 <select class="form-select" aria-label="Default select example" id="hulala" name="loopcount">
 <option value="1">1 Key</option>
 <option value="5">5 Keys</option>
 <option value="10">10 Keys</option>
 <option value="25">25 Keys</option>
 <option value="50">50 Keys</option>
 <option value="100">100 Keys</option>
 </select>
 </div>
 
 <input type="hidden" id="textinput" name="custominput">

 <div class="form-group mb-3">
 <label for="estimation" class="form-label">Estimation</label>
 <input type="text" id="estimation" class="form-control" placeholder="Your order will total" readonly>
 </div>
 <div class="form-group">
 <button type="submit" class="btn btn-outline-dark">Generate</button>
 </div>
 <?= form_close() ?>
 </div>
 </div>
 </div>
</div>

<?php if (session()->getFlashdata('user_key')) : ?>
<div class="key-popup-modal" id="keyPopup" style="display:flex;">
 <div class="key-popup-content">
 <h4> Key Generated!</h4>
 <div class="key-value" id="generatedKey"><?= session()->getFlashdata('user_key') ?></div>
 <button class="btn btn-outline-dark" onclick="copyGeneratedKey()">Copy key</button>
 <button class="btn btn-secondary" onclick="closeKeyPopup()">Close</button>
 <div id="copyMsg" style="margin-top:10px;color:var(--accent-color);font-weight:600;"></div>
 </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    particlesJS('particles-js', {
        particles: {
            number: { value: 80, density: { enable: true, value_area: 800 } },
            color: { value: '#ffffff' },
            shape: { type: 'circle' },
            opacity: {
                value: 0.5,
                random: false,
                anim: { enable: false }
            },
            size: {
                value: 3,
                random: true,
                anim: { enable: false }
            },
            line_linked: {
                enable: true,
                distance: 150,
                color: '#ffffff',
                opacity: 0.4,
                width: 1
            },
            move: {
                enable: true,
                speed: 2,
                direction: 'none',
                random: false,
                straight: false,
                out_mode: 'out',
                bounce: false
            }
        },
        interactivity: {
            detect_on: 'canvas',
            events: {
                onhover: { enable: true, mode: 'repulse' },
                onclick: { enable: true, mode: 'push' },
                resize: true
            }
        },
        retina_detect: true
    });

    function fupi(obj) {
        if($(obj).is(':checked')){
            document.getElementById("custom-key-group").style.display = "block";
            document.getElementById("bulk-keys-group").style.display = "none";
            $('#hulala option').prop('selected', function() {
                return this.defaultSelected;
            });
            document.getElementById("textinput").value = "custom";
            const input = document.getElementById('custom');
            input.setAttribute('required', 'required');
            input.value = '';
        } else {
            document.getElementById("custom-key-group").style.display = "none";
            document.getElementById("bulk-keys-group").style.display = "block";
            document.getElementById("textinput").value = "auto";
            const input = document.getElementById('custom');
            input.removeAttribute('required');
            input.value = '';
        }
    }

    $(document).ready(function() {
        var price = JSON.parse('<?= $price ?>');
        getPrice(price);
        $("#max_devices, #duration, #game, #hulala").change(function() {
            getPrice(price);
        });
        function getPrice(price) {
            var device = $("#max_devices").val();
            var durate = $("#duration").val();
            var loopCount = $("#hulala").val();
            var gprice = price[durate];
            if (gprice != NaN) {
                var result = (device * gprice);
                if ($("#check").is(':checked')) {
                    $("#estimation").val(result + " (for 1 key)");
                } else {
                    $("#estimation").val(result * loopCount + " (for " + loopCount + " keys)");
                }
            } else {
                $("#estimation").val('Estimation error');
            }
        }
    });

    function copyGeneratedKey() {
        var key = document.getElementById("generatedKey").innerText;
        navigator.clipboard.writeText(key).then(function() {
            document.getElementById("copyMsg").innerText = "Copied!";
        }, function() {
            document.getElementById("copyMsg").innerText = "Copy failed!";
        });
    }
    function closeKeyPopup() {
        document.getElementById("keyPopup").style.display = "none";
    }
</script>
<?= $this->endSection() ?>