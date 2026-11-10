<?php

include(FCPATH . 'conn.php');

    $keyCountSql = "SELECT COUNT(*) as id_keys FROM keys_code";
    $keyCountResult = mysqli_query($conn, $keyCountSql);
    $keycount = mysqli_fetch_assoc($keyCountResult);
    
    $activeCountSql = "SELECT COUNT(devices) as devices FROM keys_code";
    $activeCountResult = mysqli_query($conn, $activeCountSql);
    $active = mysqli_fetch_assoc($activeCountResult);
    
    $inactiveCountSql = "SELECT COUNT(*) as devices FROM keys_code WHERE devices IS NULL";
    $inactiveCountResult = mysqli_query($conn, $inactiveCountSql);
    $inactive = mysqli_fetch_assoc($inactiveCountResult);
    
    $sql = "SELECT COUNT(*) as id_keys FROM keys_code WHERE expired_date < NOW()";
    $result = mysqli_query($conn, $sql);
    $expkeyCount = mysqli_fetch_assoc($result)['id_keys'];
    
    $sql = "SELECT COUNT(*) as id_keys FROM keys_code WHERE status = '0'";
    $result = mysqli_query($conn, $sql);
    $blockCount = mysqli_fetch_assoc($result)['id_keys'];
            
    $sql = "SELECT COUNT(*) as id_keys FROM keys_code WHERE registrator = 'Keygen' AND expired_date < NOW()";
    $result = mysqli_query($conn, $sql);
    $expkeygenCount = mysqli_fetch_assoc($result)['id_keys'];
    
    $sql = "SELECT COUNT(*) as id_keys FROM keys_code WHERE registrator = 'Keygen'";
    $result = mysqli_query($conn, $sql);
    $keygenCount = mysqli_fetch_assoc($result)['id_keys'];
    
    
    $userCountSql = "SELECT COUNT(*) as id_users FROM users";
    $userCountResult = mysqli_query($conn, $userCountSql);
    $users = mysqli_fetch_assoc($userCountResult);
    
    $sql = "SELECT COUNT(*) as id_users FROM users WHERE status = '3'";
    $result = mysqli_query($conn, $sql);
    $status_expusers = mysqli_fetch_assoc($result)['id_users'];
    
    $sql = "SELECT COUNT(*) as id_users FROM users WHERE status = '2'";
    $result = mysqli_query($conn, $sql);
    $banusers = mysqli_fetch_assoc($result)['id_users'];
    
    $sql = "SELECT COUNT(*) as id_users FROM users WHERE status = '1'";
    $result = mysqli_query($conn, $sql);
    $activeusers = mysqli_fetch_assoc($result)['id_users'];
    
    $sql = "SELECT COUNT(*) as id_users FROM users WHERE expiration_date < NOW()";
    $result = mysqli_query($conn, $sql);
    $expusers = mysqli_fetch_assoc($result)['id_users'];
    
    $sql = "SELECT COUNT(*) as id_users FROM users WHERE level = '1'";
    $result = mysqli_query($conn, $sql);
    $ownerusers = mysqli_fetch_assoc($result)['id_users'];
    
    $sql = "SELECT COUNT(*) as id_users FROM users WHERE level = '2'";
    $result = mysqli_query($conn, $sql);
    $adminusers = mysqli_fetch_assoc($result)['id_users'];
    
    $sql = "SELECT COUNT(*) as id_users FROM users WHERE level = '3'";
    $result = mysqli_query($conn, $sql);
    $resellerusers = mysqli_fetch_assoc($result)['id_users'];
    
    $totalExpiredUsers = $status_expusers + $expusers;
    
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

    .list-group {
        border-radius: 15px;
        overflow: hidden;
        border: none;
    }
    .list-group-item {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--light-color);
        padding: 1rem 1.5rem;
        transition: background 0.3s ease, transform 0.3s ease;
    }
    .list-group-item:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateX(5px);
    }
    .list-group-item:first-child {
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }
    .list-group-item:last-child {
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
        border-bottom: none;
    }
    .list-group-item span.badge {
        background: var(--primary-color);
        color: var(--light-color) !important;
        padding: 0.5em 0.8em;
        border-radius: 8px;
        font-weight: 600;
        min-width: 60px;
        text-align: center;
    }

</style>

<div class="row">
    <div class="col-lg-12">
        <?= $this->include('Layout/msgStatus') ?>
    </div>
</div>
   
<div class="container p-3 py-4 mb-3" id="content">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-key me-2"></i> Keys Details
                </div>
                <div class="card-body">
                    <ul class="list-group list-hover mb-3">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Used Keys
                            <span class="badge"><?= $active['devices']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Un-Used Keys
                            <span class="badge"><?= $inactive['devices']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Blocked Keys
                            <span class="badge"><?= $blockCount ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total All Exp Keys
                            <span class="badge"><?= $expkeyCount ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total All Keys
                            <span class="badge"><?= $keycount['id_keys']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Exp Keygen Keys
                            <span class="badge"><?= $expkeygenCount ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total All Keygen Keys
                            <span class="badge"><?= $keygenCount ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
                    
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-people me-2"></i> Users Details
                </div>
                <div class="card-body">
                    <ul class="list-group list-hover mb-3">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Banned/Block Users
                            <span class="badge"><?= $banusers ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Active Users
                            <span class="badge"><?= $activeusers ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Expired Users
                            <span class="badge"><?= $totalExpiredUsers ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Owner
                            <span class="badge"><?= $ownerusers ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Admin
                            <span class="badge"><?= $adminusers ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Reseller
                            <span class="badge"><?= $resellerusers ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total All Users
                            <span class="badge"><?= $users['id_users']; ?></span>
                        </li>
                    </ul>
                </div>                   
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