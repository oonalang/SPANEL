<?php

function color($value) {
if($value == 1) {
return "#0000FF";
} else {
return "#FF0000";
}
}
?>


<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>
<div class="row">
 <div class="col-lg-12">
 <div class="alert alert-dark" role="alert">
 <strong>INFO :</strong> Search specify user by their (username, fullname, saldo or uplink).
 </div>
 <div class="card shadow-sm">
 <div class="card-header text-white bg-dark">
 <h2>Manage users</h2>
 </div>
 <div class="card-body">
 <?php if ($user_list) : ?>

 <div class="table-responsive">
 <table class="table table-bordered table-hover text-center" style="width:100%">
 <thead>
 <tr>
 <th scope="row">ID</th>
 <th>Username</th>
 <th>Fullname</th>
 <th>Level</th>
 <th>Saldo</th>
 <th>Status</th>
 <th>Uplink</th>
 <th>Expiration</th>
 <th>Action</th>
 </tr>
 </thead>
 <tbody>
 <?php foreach ($user_list as $u) : ?>
 <tr>
 <td><?= $u->id_users ?></td>
 <td><?= $u->username ?></td>
 <td><?= $u->fullname ?></td>
 <td style="color: <?= color($u->level) ?>;">
 <?php if($u->level == 1) : ?>
                                     Owner
 <?php elseif($u->level == 2) : ?>
                                     Admin
 <?php else : ?>
                                     Reseller
 <?php endif; ?>
 </td>
 <td style="color: <?= color($u->level) ?>;">
 <?php if($u->level == 1) : ?>
                                     &mstpos;
 <?php else : ?>
 <?= $u->saldo ?>
 <?php endif; ?>
 </td>
 <td style="color: <?= color($u->status) ?>;">
 <?php if($u->status == 1) : ?>
                                      Active
 <?php elseif($u->status == 2) : ?>
                                      Banned/Blocked
 <?php else : ?>
                                      Expired
 <?php endif; ?>
 </td>
 <td><?= $u->uplink ?></td>
 <td><?= $u->expiration_date ?></td>
 <td>
 <a href="user/<?php echo $u->id_users ?>" class="btn btn-dark btn-sm"><i class="bi bi-pencil-square"></i></a><br></br>
 </td>
 </tr>
 <?php endforeach; ?>
 <tbody>
 </table>
 </div>
 <?php endif; ?>

 </div>
 </div>
 </div>
</div>
</br></br></br>
<?= $this->endSection() ?>

<?= $this->section('css') ?>
<?= link_tag("https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css") ?>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<?= script_tag("https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js") ?>

<?= script_tag("https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js") ?>

<?= $this->endSection() ?>