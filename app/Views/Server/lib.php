<?php

include(FCPATH . 'conn.php');
                            $sql21 ="SELECT * FROM lib WHERE id='1'";
                            $result21 = mysqli_query($conn, $sql21);
                            $userDetails21 = mysqli_fetch_assoc($result21);
                           if ($result21) {
                           }
function generateRandomString($length = 7) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
$token = md5(generateRandomString());
$token.= rand(10,999);
$sql1 ="UPDATE lib SET token='$token' where id=1";
$result1 = mysqli_query($conn, $sql1);
if ($result1) {
}
$timestamp = $userDetails21['last_modified'];
$date = new DateTime();
$date->setTimestamp($timestamp);

$last = $date->format('Y-m-d h:i:s a');
date_default_timezone_set("Asia/Kolkata");
$current = date('Y-m-d h:i:s a');
$linkk = $userDetails21['link'];
$js = "window.open('$linkk')";
$path = $userDetails21['path'];
?>

<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>
<div id="particles-js"></div>
<script>
      function openlink(){
window.location = ("<?= site_url($path) ?>");
      }
</script>
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
        justify-content: center;
    }
    .card-header i {
        margin-right: 15px;
        font-size: 1.8rem;
    }
    .card-body p {
        color: rgba(255, 255, 255, 0.9);
    }
    .card-body p b {
        color: var(--accent-color);
    }
    .card-body p a {
        color: var(--light-color) !important;
    }

    .form-control {
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
    .form-control:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 15px rgba(6,182,212,0.4);
        background: rgba(255,255,255,0.15);
        color: var(--light-color);
        outline: none;
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

    .custom-file-button input[type=file] {
        margin-left: -2px !important;
    }
    .custom-file-button input[type=file]::-webkit-file-upload-button {
        display: none;
    }
    .custom-file-button input[type=file]::file-selector-button {
        display: none;
    }
    .custom-file-button:hover label {
        background-color: #dde0e3;
        cursor: pointer;
    }
    .input-group-text {
        background: var(--primary-color);
        color: var(--light-color);
        border: none;
        border-radius: 12px 0 0 12px;
        padding: 0.9rem 1.2rem;
        font-weight: 600;
    }
    .input-group .form-control {
        border-radius: 0 12px 12px 0;
    }
</style>

<div class="row">
    <div class="col-lg-12">
        <?= $this->include('Layout/msgStatus') ?>
    </div>
</div>
     <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header h6 p-3">
                <i class="bi bi-cloud-arrow-down me-2"></i> ONLINE LIB STATUS
            </div>
            <div class="card-body text-center">
                <p><b>CURRENT LIB :</b><span> <?php echo $userDetails21['name']; ?></span></p>
                <p><b>LIB SIZE :</b><span> <?php echo $userDetails21['size']; ?></span></p>
                <p><b>LIB Path :</b><span> <?php echo $userDetails21['path']; ?></span></p>
                <p><b>Last Modified :</b><span> <?php echo $last; ?></span></p>
                <p><b>Current Time :</b><span> <?php echo $current; ?></span></p>
                <p>
                    <button class="btn btn-gradient" onclick="openlink()" style="width: auto;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download me-2" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"></path>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"></path>
                        </svg>
                        Download
                    </button>
                </p>
            </div>
        </div>
    </div>

     <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header h6 p-3">
                <i class="bi bi-cloud-arrow-up me-2"></i> UPLOAD LIB
            </div>
            <div class="card-body text-center">
                <div class="container py-3">
                    <form action="<?php echo site_url('file_upload.php'); ?>" method="post" enctype="multipart/form-data">
                        <div class="input-group custom-file-button mb-3">
                            <label class="input-group-text" for="libfile">Choose lib</label>
                            <input type="file" name="libfile" class="form-control" id="libfile">
                            <input name="token" value="<?php echo $token; ?>" hidden>
                        </div>
                        <p></p>
                        <button type="submit" class="btn btn-gradient">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-upload me-2" viewBox="0 0 16 16">
                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"></path>
                                <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"></path>
                            </svg>
                            Upload
                        </button>
                    </form>
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