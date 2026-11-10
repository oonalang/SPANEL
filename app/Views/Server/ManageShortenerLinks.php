<?php
include(FCPATH . 'conn.php');

    $URL = "https://chat.aalyan.shop/";
    $BPShortener = $URL . "NULL";
    $CODMShortener = $URL . "CODM-DOSiyXCY0u";
    $FL84Shortener = $URL . "FL84-DOSiyXCY0u";
    $MLBBShortener = $URL . "MLBB-DOSiyXCY0u";
    $PUBGShortener = $URL . "NULL";
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
    .input-group .form-control {
        background: rgba(255,255,255,0.1);
        border: 1.5px solid rgba(255,255,255,0.2);
        color: var(--light-color);
        border-radius: 12px;
        padding: 0.9rem 1.2rem;
        transition: border-color 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
    }
    .input-group .form-control:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 15px rgba(6,182,212,0.4);
        background: rgba(255,255,255,0.15);
        color: var(--light-color);
        outline: none;
    }
    .btn-dark {
        background: var(--primary-color);
        border: none;
        border-radius: 12px;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
        color: #fff;
    }
    .btn-dark:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);
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
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_processing,
    .dataTables_wrapper .dataTables_paginate {
        color: var(--light-color) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        color: var(--light-color) !important;
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        border-radius: 8px;
        margin: 0 2px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--primary-color) !important;
        border: none !important;
    }
    .dataTables_wrapper .dataTables_filter input {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: var(--light-color);
        border-radius: 8px;
        padding: 0.5rem 1rem;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        outline: none;
        border-color: var(--accent-color);
        box-shadow: 0 0 0 2px rgba(6, 182, 212, 0.2);
    }
</style>

<div id="particles-js"></div>

<div class="col-lg-6">
    <div class="card mb-3">
        <div class="card-header">
            <i class="bi bi-link-45deg me-2"></i> Shortener Links (No ads)
        </div>
        <div class="card-body">
            <div class="input-group mb-3">
                <input type="text" id="8BP" class="form-control" value="<?= $BPShortener; ?>" readonly>
                <button class="btn btn-dark" type="button" onclick="copy8BP()"><i class="bi bi-clipboard"></i></button>
            </div>
            <div class="input-group mb-3">
                <input type="text" id="CODM" class="form-control" value="<?= $CODMShortener; ?>" readonly>
                <button class="btn btn-dark" type="button" onclick="copyCODM()"><i class="bi bi-clipboard"></i></button>
            </div>
            <div class="input-group mb-3">
                <input type="text" id="FL84" class="form-control" value="<?= $FL84Shortener; ?>" readonly>
                <button class="btn btn-dark" type="button" onclick="copyFL84()"><i class="bi bi-clipboard"></i></button>
            </div>
            <div class="input-group mb-3">
                <input type="text" id="MLBB" class="form-control" value="<?= $MLBBShortener; ?>" readonly>
                <button class="btn btn-dark" type="button" onclick="copyMLBB()"><i class="bi bi-clipboard"></i></button>
            </div>
            <div class="input-group mb-3">
                <input type="text" id="PUBG" class="form-control" value="<?= $PUBGShortener; ?>" readonly>
                <button class="btn btn-dark" type="button" onclick="copyPUBG()"><i class="bi bi-clipboard"></i></button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <i class="bi bi-table me-2"></i> Manage Shortener Links
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable" class="table table-hover text-center" style="width:100%">
                        <thead>
                            <tr>
                                <th scope="row">#</th>
                                <th>8BP</th>
                                <th>CODM</th>
                                <th>FL84</th>
                                <th>MLBB</th>
                                <th>PUBG</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $fetchqry = "SELECT * FROM `Keygen_Links`";
                            $result=mysqli_query($conn,$fetchqry);
                            $num=mysqli_num_rows($result);

                            if($num > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    echo '<tr>';
                                    echo '<td>' . $row['id'] . '</td>';
                                    echo '<td>' . $row['8BP_Link'] . '</td>';
                                    echo '<td>' . $row['CODM_Link'] . '</td>';
                                    echo '<td>' . $row['FL84_Link'] . '</td>';
                                    echo '<td>' . $row['MLBB_Link'] . '</td>';
                                    echo '<td>' . $row['PUBG_Link'] . '</td>';
                                    echo '<td><a class="btn btn-dark btn-sm" href="Links">Edit</a></td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.1.0.min.js"></script>
<script>
function copy8BP() {
    var UsernameText = document.getElementById("8BP");
    UsernameText.select();
    UsernameText.setSelectionRange(0, 99999);
    document.execCommand("copy");
}
function copyCODM() {
    var UsernameText = document.getElementById("CODM");
    UsernameText.select();
    UsernameText.setSelectionRange(0, 99999);
    document.execCommand("copy");
}
function copyFL84() {
    var UsernameText = document.getElementById("FL84");
    UsernameText.select();
    UsernameText.setSelectionRange(0, 99999);
    document.execCommand("copy");
}
function copyMLBB() {
    var UsernameText = document.getElementById("MLBB");
    UsernameText.select();
    UsernameText.setSelectionRange(0, 99999);
    document.execCommand("copy");
}
function copyPUBG() {
    var UsernameText = document.getElementById("PUBG");
    UsernameText.select();
    UsernameText.setSelectionRange(0, 99999);
    document.execCommand("copy");
}
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