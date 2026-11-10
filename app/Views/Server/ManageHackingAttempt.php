<?= $this->extend('Layout/Starter') ?>

<?php include(FCPATH . 'conn.php'); ?>

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

    .btn-dark {
        background: var(--primary-color);
        border: none;
        border-radius: 12px;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
    }

    .btn-dark:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);
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

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: var(--primary-color) !important;
        border: none !important;
    }

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

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-shield-check me-2"></i> Manage <?= $title ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable" class="table table-hover text-center" style="width:100%">
                        <thead>
                            <tr>
                                <th scope="row">#</th>
                                <th>IP Address</th>
                                <th>IP Location</th>
                                <th>Attempt date</th>
                                <th>Target</th>
                                <th>Status</th>
                                <th>VPN</th>
                                <th>Duration</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $fetchqry = "SELECT * FROM `visitor_logs`";
                            $result=mysqli_query($conn,$fetchqry);
                            $num=mysqli_num_rows($result);

                            if($num > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    echo '<tr>';
                                    echo '<td>' . $row['id'] . '</td>';
                                    echo '<td>' . $row['ip_address'] . '</td>';
                                    echo '<td>' . $row['ip_location'] . '</td>';
                                    echo '<td>' . $row['timestamp'] . '</td>';
                                    echo '<td>' . $row['visited_page'] . '</td>';
                                    echo '<td>' . $row['access_status'] . '</td>';
                                    echo '<td>' . $row['vpn_used'] . '</td>';
                                    echo '<td>' . $row['duration'] . '</td>';                                   
                                    echo '<td><a href="DeleteHackingAttempt.php?id=' . $row['id'] . '" class="btn btn-dark btn-sm"><i class="bi bi-trash"></i></a></td>';
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

<?= $this->endSection() ?>

<?= $this->section('css') ?>
<?= link_tag("https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css") ?>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<?= script_tag("https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js") ?>

<?= script_tag("https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js") ?>
<script>
   
</script>
<?= $this->endSection() ?>
