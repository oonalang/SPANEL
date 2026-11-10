<?php
$db = db_connect();
$owner = $user->username;
$keycount = ['id_keys' => $db->table('keys_code')->where('registrator', $owner)->countAllResults()];
$active = ['devices' => $db->table('keys_code')->where('registrator', $owner)->where('devices IS NOT NULL', null, false)->countAllResults()];
$inactive = ['devices' => $db->table('keys_code')->where('registrator', $owner)->where('devices IS NULL', null, false)->countAllResults()];
$users = ['id_users' => 1];
$period = ['expiration_date' => $user->expiration_date];

function HoursToDays($value)
{
    if($value == 1) {
       return "$value Hour";
    } else if($value >= 2 && $value < 24) {
       return "$value Hours";
    } else if($value == 24) {
       $darkespyt = $value/24;
       return "$darkespyt Day";
    } else if($value > 24) {
       $darkespyt = $value/24;
       return "$darkespyt Days";
    }
}

$expiryValue = trim((string) ($period['expiration_date'] ?? ''));
$expiryTimestamp = $expiryValue !== '' ? strtotime($expiryValue) : false;
$expiryEndMillis = $expiryTimestamp === false ? null : $expiryTimestamp * 1000;
?>

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

    body {
        background: var(--dark-color);
        min-height: 100vh;
        font-family: 'Chakra Petch', 'Segoe UI', sans-serif;
        overflow-x: hidden;
        position: relative;
    }

    #particles-js {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }

    .dashboard-container {
        padding: 20px;
    }

    .card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        transition: all 0.3s ease;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.3);
    }

    .card-header {
        background: var(--primary-color);
        color: var(--light-color);
        font-weight: 600;
        border-bottom: none;
        padding: 1.5rem;
        font-size: 1.2rem;
    }

    .card-body {
        padding: 1.5rem;
    }

    .expiration-timer {
        font-family: 'Nova Mono', monospace;
        font-size: 2.5rem;
        text-align: center;
        color: var(--accent-color);
        text-shadow: 0 0 20px rgba(6, 182, 212, 0.5);
        animation: titlePulse 2s infinite;
    }

    @keyframes titlePulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    .stats-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(79, 70, 229, 0.2);
    }

    .stats-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--accent-color);
        text-shadow: 0 0 10px rgba(6, 182, 212, 0.3);
    }

    .stats-label {
        color: var(--light-color);
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .table {
        color: var(--light-color);
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        overflow: hidden;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        margin-bottom: 0;
    }

    .table thead th {
        background: var(--primary-color);
        color: var(--light-color);
        font-weight: 600;
        border: none !important;
        padding: 1rem;
        outline: none !important;
        box-shadow: none !important;
    }

    .table td,
    .table th {
        border: none !important;
        padding: 1rem;
        vertical-align: middle;
        background-color: transparent !important;
        outline: none !important;
        box-shadow: none !important;
    }

    .table tbody tr {
        background: transparent !important;
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
    }

    .table tbody tr:nth-child(odd),
    .table tbody tr:nth-child(even) {
        background-color: transparent !important;
    }

    .table tbody tr:hover {
        background: rgba(255, 255, 255, 0.05) !important;
    }

    .badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .btn {
        background: var(--primary-color);
        color: var(--light-color);
        border: none;
        border-radius: 16px;
        padding: 0.8rem 1.5rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(79, 70, 229, 0.3);
    }

    .btn-white {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .btn-white:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .chart-container {
        position: relative;
        margin: auto;
        height: 300px;
        padding: 1rem;
    }

</style>

<div id="particles-js"></div>

<div class="dashboard-container">
 <div class="row">
 <div class="col-lg-12">
 <?= $this->include('Layout/msgStatus') ?>
 </div>

 <div class="col-12 mb-4">
 <div class="card">
 <div class="card-header">
 <i class="bi bi-clock"></i> Expiration timer
 </div>
 <div class="card-body">
 <div class="expiration-timer cd" id="exp" role="timer" aria-live="off"></div>
 <div class="cd-note" id="expNote"></div>
 </div>
 </div>
 </div>

 <div class="col-lg-5 mb-4">
 <div class="card h-100">
 <div class="card-header">
 <i class="bi bi-graph-up"></i> Stats overview
 </div>
 <div class="card-body stats-stack">
 <div class="stats-card">
 <div class="stats-value"><?php echo $keycount['id_keys']; ?></div>
 <div class="stats-label">Total keys</div>
 </div>
 <div class="stats-card">
 <div class="stats-value"><?php echo $active['devices']; ?></div>
 <div class="stats-label">Active keys</div>
 </div>
 <div class="stats-card">
 <div class="stats-value"><?php echo $inactive['devices']; ?></div>
 <div class="stats-label">Inactive keys</div>
 </div>
 </div>
 </div>
 </div>

 <div class="col-lg-7 mb-4">
 <div class="card h-100">
 <div class="card-header">
 <i class="bi bi-bar-chart"></i> Key usage analysis
 </div>
 <div class="card-body chart-body">
 <div class="chart-container">
 <canvas id="usageChart"></canvas>
 </div>
 </div>
 </div>
 </div>

 <div class="col-lg-12 mb-4">
 <div class="card">
 <div class="card-header">
 <i class="bi bi-clock-history"></i> History
 </div>
 <div class="card-body">
 <div class="table-responsive">
 <table class="table table-hover text-center">
 <thead>
 <tr>
 <th>#</th>
 <th>Info</th>
 <th>Key</th>
 <th>Days</th>
 <th>Devices</th>
 <th>Time</th>
 </tr>
 </thead>
 <tbody>
 <?php foreach ($history as $h) : ?>
 <?php $in = explode("|", $h->info) ?>
 <tr>
 <td><span class="badge">#3812<?= $h->id_history ?></span></td>
 <td><?= $in[0] ?></td>
 <td><span class="badge"><?= $in[1] ?>**</span></td>
 <td><span class="badge"><?= $in[2] ?> Days</span></td>
 <td><span class="badge"><?= $in[3] ?> Devices</span></td>
 <td><i class="badge"><?= $time::parse($h->created_at)->humanize() ?></i></td>
 </tr>
 <?php endforeach; ?>
 </tbody>
 </table>
 <?php if (($user->level == 2) || ($user->level == 3)) : ?>
 <div class="form-group mb-2 text-center">
 <button type="button" class="btn" onclick="deleteAllUserKeys()">
 <i class="bi bi-trash-fill"></i> Delete All History Records
 </button>
 </div>
 <?php endif; ?>
 <?php if($user->level == 1) : ?>
 <div class="form-group mb-2 text-center">
 <button type="button" class="btn btn-white" onclick="deleteAllUserKeyss()">
 <i class="bi bi-trash-fill"></i> Delete All History Records
 </button>
 </div>
 <?php endif; ?>
 </div>
 </div>
 </div>
 </div>
 </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

    // Expiration timer: four counter tiles, each digit flaps when it changes
    (function () {
        var box = document.getElementById("exp"), note = document.getElementById("expNote");
        if (!box) { return; }
        var end = <?= json_encode($expiryEndMillis) ?>;
        var units = [["days", "Days"], ["hours", "Hours"], ["minutes", "Minutes"], ["seconds", "Seconds"]];
        var cells = {};

        function pad(n, w) { n = String(n); while (n.length < w) { n = "0" + n; } return n; }

        function build(vals) {
            box.innerHTML = "";
            var row = document.createElement("div");
            row.className = "cd-row";
            units.forEach(function (u) {
                var unit = document.createElement("div");
                unit.className = "cd-unit cd-" + u[0];
                var digits = document.createElement("div");
                digits.className = "cd-digits";
                var text = pad(vals[u[0]], u[0] === "days" ? 2 : 2);
                cells[u[0]] = [];
                for (var i = 0; i < text.length; i++) {
                    var d = document.createElement("span");
                    d.className = "cd-digit";
                    d.innerHTML = "<b>" + text.charAt(i) + "</b>";
                    digits.appendChild(d);
                    cells[u[0]].push(d);
                }
                var label = document.createElement("div");
                label.className = "cd-label";
                label.textContent = u[1];
                unit.appendChild(digits);
                unit.appendChild(label);
                row.appendChild(unit);
            });
            box.appendChild(row);
        }

        function paint(name, value) {
            var text = pad(value, 2), list = cells[name];
            if (text.length !== list.length) { return false; } // days gained or lost a digit: rebuild
            for (var i = 0; i < text.length; i++) {
                var b = list[i].firstChild;
                if (b.textContent !== text.charAt(i)) {
                    b.textContent = text.charAt(i);
                    list[i].classList.remove("flap");
                    void list[i].offsetWidth;
                    list[i].classList.add("flap");
                }
            }
            return true;
        }

        function parts(diff) {
            return {
                days: Math.floor(diff / 86400000),
                hours: Math.floor((diff % 86400000) / 3600000),
                minutes: Math.floor((diff % 3600000) / 60000),
                seconds: Math.floor((diff % 60000) / 1000)
            };
        }

        function tick() {
            if (end === null || !Number.isFinite(end)) {
                box.textContent = "Expiration date unavailable";
                if (note) { note.textContent = "Check your account expiration setting."; }
                return false;
            }
            var diff = end - Date.now();
            if (diff <= 0) {
                box.innerHTML = '<div class="cd-expired">Expired</div>';
                return false;
            }
            var v = parts(diff);
            if (!cells.days || !paint("days", v.days)) { build(v); }
            paint("hours", v.hours); paint("minutes", v.minutes); paint("seconds", v.seconds);
            return true;
        }

        if (note && end !== null && Number.isFinite(end)) {
            note.textContent = "Ends " + new Date(end).toLocaleString(undefined, { dateStyle: "medium", timeStyle: "short" });
        }
        if (tick()) {
            var iv = setInterval(function () { if (!tick()) { clearInterval(iv); } }, 1000);
        }
    })();

    // Usage chart: ring with the device total in the middle
    (function () {
        var active = <?= (int) $active['devices'] ?>, inactive = <?= (int) $inactive['devices'] ?>, total = active + inactive;
        var ink = "#ece6da", dim = "#a39b8c";
        var centre = {
            id: "centreText",
            afterDraw: function (chart) {
                var c = chart.ctx, a = chart.chartArea, x = (a.left + a.right) / 2, y = (a.top + a.bottom) / 2;
                c.save();
                c.textAlign = "center"; c.textBaseline = "middle";
                c.fillStyle = ink; c.font = "700 34px Archivo, sans-serif";
                c.fillText(String(total), x, y - 8);
                c.fillStyle = dim; c.font = "600 11px Archivo, sans-serif";
                c.fillText("DEVICES", x, y + 18);
                c.restore();
            }
        };
        new Chart(document.getElementById("usageChart").getContext("2d"), {
            type: "doughnut",
            data: {
                labels: total ? ["Active", "Inactive"] : ["No devices yet"],
                datasets: [{
                    data: total ? [active, inactive] : [1],
                    backgroundColor: total ? ["#f26a1b", "#4a4540"] : ["#2e2b28"],
                    borderColor: "#242220", borderWidth: 4, hoverOffset: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: "70%",
                animation: { duration: 900, easing: "easeOutQuart" },
                plugins: {
                    legend: { position: "bottom", labels: { color: ink, usePointStyle: true, pointStyle: "rectRounded", padding: 18, font: { family: "Archivo, sans-serif", size: 13 } } },
                    tooltip: { backgroundColor: "#131211", borderColor: "#4c4640", borderWidth: 1, titleColor: ink, bodyColor: ink, displayColors: false }
                }
            },
            plugins: [centre]
        });
    })();
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css"/>

<?= $this->endSection() ?>