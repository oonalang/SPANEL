<?= $this->extend('Layout/Starter') ?>

/*
Justine Hacks VIP Panel
*/

<?= $this->section('content') ?>

<div class="col-lg-6">
    <div class="card mb-3">
        <div class="card-header h6 p-3 bg-dark text-white">
            Extend Key Duration
        </div>
        <div class="card-body">
            <form action="EXTEND_KEYS_DURATIONS.php" method="post">
                <div class="form-group mb-3">
                    <label for="addDays">Extend Key Duration:</label>
                    <input type="number" name="addDays" id="addDays" min="1" max="5000" class="form-control mt-2" placeholder="Enter Extend Days">
                </div>
                <div class="form-group my-2">
                    <label for="action">Select action:</label>
                    <select name="action" class="form-select">
                        <option value="extend_keygen">Extend all Keygen Keys</option>
                        <option value="extend_not_keygen">Extend all Keys Not Included Keygen key</option>
                        <option value="extend_all_keys">Extend all keys</option>                       
                    </select>
                </div>
                <div class="form-group my-2">
                    <label for="game">Game:</label>
                    <select name="game" class="form-select">
                        <option value="EV">Easy Victory</option>
                    </select>
                </div>
                <div class="form-group my-2">
                    <button type="submit" class="btn btn-dark">Update</button>
                </div>
            </form>
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

