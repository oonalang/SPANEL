<?= $this->extend('Layout/Starter') ?>
<?= $this->section('content') ?>
<style>
.catalog-wrap{max-width:1140px;margin:2rem auto;color:#ecedff}
.catalog-hero{padding:1.7rem 2rem;border:1px solid #38344e;border-radius:16px;background:#201d2c}
.catalog-hero h1{font-size:1.65rem;font-weight:750;letter-spacing:-.025em;margin:0 0 .4rem}
.catalog-sub{color:#bfbcd1;font-size:.96rem;margin-bottom:0}
.catalog-box{background:#211e2e;border:1px solid #38344e;border-radius:16px;padding:1.5rem;margin-top:1.25rem}
.catalog-box h2{font-size:1.16rem;font-weight:700;margin-bottom:1.25rem}
.catalog-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,240px),1fr));gap:1rem}
.catalog-grid .field{min-width:0}
.catalog-grid label{display:block;font-size:.88rem;margin-bottom:.4rem;color:#cecada;font-weight:600}
.catalog-grid input,.catalog-grid select{background:#15131d;border:1px solid #484357;border-radius:9px;color:#faf9ff;padding:.72rem .78rem;width:100%;outline-offset:2px}
.catalog-grid input:focus,.catalog-grid select:focus{border-color:#b19cff;outline:2px solid #55418a}
.catalog-grid select option{background:#15131d}
.catalog-upload-note{font-size:.83rem;line-height:1.45;color:#beb9ce;margin:.4rem 0 0}
.catalog-upload-field{grid-column:1/-1}
.catalog-upload-field input[type=file]{width:100%;cursor:pointer}
.catalog-upload-field input[type=file]::file-selector-button{background:#e5ded0;border:1px solid #918576;border-radius:7px;color:#201c1a;padding:.55rem .8rem;margin-right:.8rem;font-weight:650;cursor:pointer}
.catalog-upload-meter{height:6px;background:#413a42;border-radius:8px;overflow:hidden;margin-top:.85rem}
.catalog-upload-meter-fill{height:100%;width:0;background:#e5ded0;transition:width .12s ease}
.catalog-upload-status{color:#d3c9b7;font-size:.85rem;margin:.45rem 0 0}
.catalog-actions{display:flex;gap:.7rem;align-items:center;flex-wrap:wrap;margin-top:1.25rem}
.catalog-btn{display:inline-flex;align-items:center;justify-content:center;border-radius:9px;padding:.69rem 1rem;font-size:.9rem;font-weight:650;text-decoration:none;cursor:pointer;border:1px solid #61568e;background:#7f6bc4;color:white}
.catalog-btn:hover{background:#9c85ed;color:white}
.catalog-btn.secondary{background:#302b3e;border-color:#514963;color:#f1eefe}
.catalog-btn.danger{background:transparent;color:#ffada9;border-color:#994b51}
.catalog-btn.danger:hover{background:#50252c}
.catalog-table{width:100%;border-collapse:separate;border-spacing:0}
.catalog-table th,.catalog-table td{padding:1rem .75rem;text-align:left;border-bottom:1px solid #413a50;vertical-align:middle}
.catalog-table th{color:#b6b0c9;font-size:.8rem;text-transform:uppercase;letter-spacing:.06em}
.catalog-table td{font-size:.92rem}
.catalog-table .muted{color:#b6b0c9;font-size:.81rem}
.catalog-table code{color:#dac9ff}
.catalog-tag{border:1px solid #5b5572;border-radius:50px;padding:.22rem .55rem;font-size:.77rem}
.catalog-empty{border:1px dashed #554a6d;padding:1.4rem;text-align:center;border-radius:10px;color:#bfbcd1}
.catalog-feedback{margin-top:1rem;border-radius:9px;padding:.85rem 1.1rem;background:#34304c;border:1px solid #7263b2}
.catalog-feedback.error{background:#452a34;border-color:#9d4f67}
@media(max-width:700px){.catalog-hero,.catalog-box{padding:1.2rem}.catalog-table-wrap{overflow:auto}.catalog-table{min-width:650px}}
</style>
<div class="catalog-wrap">
  <div class="catalog-hero">
    <h1>Loader catalog</h1>
    <p class="catalog-sub">Manage the libraries displayed in ASTRAL LOADER. License validation continues through the existing /connect endpoint.</p>
  </div>
  <?php if (session()->getFlashdata('catalog_notice')): ?>
    <div class="catalog-feedback" role="status"><?= esc(session()->getFlashdata('catalog_notice')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('catalog_error')): ?>
    <div class="catalog-feedback error" role="alert"><?= esc(session()->getFlashdata('catalog_error')) ?></div>
  <?php endif; ?>
  <section class="catalog-box">
    <h2><?= $selected ? 'Edit library' : 'Add library' ?></h2>
    <form method="post" enctype="multipart/form-data" action="<?= site_url('admin/loader-catalog/save') ?>" id="catalog-upload-form">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= esc((string) ($selected['id'] ?? 0)) ?>">
      <?php $value = static fn(string $key): string => esc((string) ($selected[$key] ?? ''), 'attr'); ?>
      <div class="catalog-grid">
        <div class="field"><label for="cat-name">Display name</label><input required maxlength="120" name="name" id="cat-name" value="<?= $value('name') ?>" placeholder="Product name"></div>
        <div class="field"><label for="cat-type">License game TYPE</label><select required name="game_type" id="cat-type" class="form-select"><?= \App\Libraries\GameTypes::options($value('game_type'), $licenseGames, true) ?></select></div>
        <div class="field"><label for="cat-package">Android package</label><input required maxlength="190" name="game_package" id="cat-package" value="<?= $value('game_package') ?>" placeholder="com.example.app"></div>
        <div class="field"><label for="cat-arch">Architecture</label><select name="architecture" id="cat-arch"><option value="64bit" <?= ($selected['architecture'] ?? '64bit') === '64bit' ? 'selected' : '' ?>>64bit</option><option value="32bit" <?= ($selected['architecture'] ?? '') === '32bit' ? 'selected' : '' ?>>32bit</option></select></div>
        <div class="field catalog-upload-field"><label for="cat-library-upload">Upload library (.so)</label><input type="file" accept=".so,application/octet-stream" name="catalog_library_file" id="cat-library-upload" aria-describedby="cat-library-help"><p id="cat-library-help" class="catalog-upload-note">Choose a .so file from your device, or paste an HTTPS URL below. A file upload takes priority when both are provided. Uploaded files stay private and use the existing license-protected download system. Maximum 10 MB on free hosting.</p></div>
        <div class="field catalog-upload-field"><label for="cat-download">HTTPS file URL (alternative)</label><input type="url" maxlength="1024" name="download_url" id="cat-download" value="<?= $value('download_url') ?>" placeholder="https://example.org/file.so" aria-describedby="cat-download-help"><p id="cat-download-help" class="catalog-upload-note">Leave blank when uploading a file. Editing without a new upload keeps the URL shown here.</p></div>
        <div class="field"><label for="cat-jni">Library entry key (public metadata)</label><input maxlength="255" name="jni_key" id="cat-jni" value="<?= $value('jni_key') ?>" autocomplete="off" placeholder="Not a private secret"></div>
        <div class="field"><label for="cat-version">Version label</label><input maxlength="50" name="version_name" id="cat-version" value="<?= $value('version_name') ?>" placeholder="1.0"></div>
        <div class="field"><label for="cat-logo">HTTPS image URL (optional)</label><input type="url" maxlength="1024" name="logo_url" id="cat-logo" value="<?= $value('logo_url') ?>" placeholder="https://example.org/image.png"></div>
        <div class="field"><label for="cat-status">Visibility</label><select name="status" id="cat-status">
          <?php foreach (['ACTIVE' => 'Active', 'MAINTENANCE' => 'Maintenance', 'DISABLED' => 'Hidden'] as $status => $text): ?>
          <option value="<?= $status ?>" <?= ($selected['status'] ?? 'ACTIVE') === $status ? 'selected' : '' ?>><?= $text ?></option>
          <?php endforeach; ?>
        </select></div>
        <div class="field"><label for="cat-order">Sort order</label><input type="number" min="0" max="100000" name="sort_order" id="cat-order" value="<?= esc((string) ($selected['sort_order'] ?? 100), 'attr') ?>"></div>
      </div>
      <div class="catalog-actions"><button class="catalog-btn" type="submit" id="catalog-save-button">Save library</button><?php if ($selected): ?><a class="catalog-btn secondary" href="<?= site_url('admin/loader-catalog') ?>">Cancel editing</a><?php endif; ?></div>
      <div id="catalog-upload-feedback" hidden aria-live="polite">
        <div class="catalog-upload-meter" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-label="Library upload progress"><div class="catalog-upload-meter-fill" id="catalog-upload-meter-fill"></div></div>
        <p class="catalog-upload-status" id="catalog-upload-status">Starting upload</p>
      </div>
    </form>
  </section>
  <section class="catalog-box">
    <h2>Library entries (<?= count($entries) ?>)</h2>
    <?php if (! $entries): ?><div class="catalog-empty">No entries yet. Add your first library above, then test the catalog URL.</div>
    <?php else: ?><div class="catalog-table-wrap"><table class="catalog-table"><thead><tr><th>Library</th><th>License type</th><th>Architecture</th><th>Visibility</th><th>Actions</th></tr></thead><tbody>
      <?php foreach ($entries as $entry): ?><tr>
        <td><strong><?= esc($entry['name']) ?></strong><div class="muted"><?= esc($entry['game_package']) ?></div></td>
        <td><code><?= esc($entry['game_type']) ?></code></td>
        <td><?= esc($entry['architecture']) ?></td>
        <td><span class="catalog-tag"><?= esc($entry['status']) ?></span></td>
        <td><div class="catalog-actions" style="margin:0"><a class="catalog-btn secondary" href="<?= site_url('admin/loader-catalog') . '?edit=' . (int) $entry['id'] ?>">Edit</a>
          <form method="post" action="<?= site_url('admin/loader-catalog/remove') ?>" onsubmit="return confirm('Remove this catalog entry?');">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $entry['id'] ?>"><button class="catalog-btn danger" type="submit">Remove</button>
          </form></div></td>
      </tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
    <div class="catalog-actions"><a class="catalog-btn secondary" href="<?= site_url('loader/catalog') ?>" target="_blank" rel="noopener noreferrer">View catalog response</a></div>
  </section>
</div>
<script>
(function () {
  const form = document.getElementById('catalog-upload-form');
  const picker = document.getElementById('cat-library-upload');
  const urlField = document.getElementById('cat-download');
  picker.addEventListener('change', function () {
    urlField.disabled = Boolean(picker.files && picker.files.length);
  });
  const button = document.getElementById('catalog-save-button');
  const feedback = document.getElementById('catalog-upload-feedback');
  const meter = feedback.querySelector('[role="progressbar"]');
  const bar = document.getElementById('catalog-upload-meter-fill');
  const status = document.getElementById('catalog-upload-status');
  let uploading = false;
  form.addEventListener('submit', function (event) {
    if (!picker.files || !picker.files.length || uploading) {
      if (uploading) event.preventDefault();
      return;
    }
    if (!form.reportValidity()) return;
    if (typeof XMLHttpRequest === 'undefined' || typeof FormData === 'undefined') return;
    event.preventDefault();
    uploading = true;
    button.disabled = true;
    feedback.hidden = false;
    meter.setAttribute('aria-valuenow', '0');
    bar.style.width = '0%';
    status.textContent = 'Uploading ' + picker.files[0].name;
    const request = new XMLHttpRequest();
    request.open('POST', form.action, true);
    request.upload.addEventListener('progress', function (progress) {
      if (!progress.lengthComputable) return;
      const percent = Math.min(100, Math.round(progress.loaded / progress.total * 100));
      meter.setAttribute('aria-valuenow', String(percent));
      bar.style.width = percent + '%';
      status.textContent = percent < 100 ? 'Uploading ' + percent + '%' : 'Saving library';
    });
    request.addEventListener('load', function () {
      if (request.status >= 200 && request.status < 400) {
        window.location.assign(request.responseURL || form.action);
      } else {
        uploading = false;
        button.disabled = false;
        status.textContent = 'Upload failed. Please try again.';
      }
    });
    request.addEventListener('error', function () {
      uploading = false;
      button.disabled = false;
      status.textContent = 'Connection interrupted. Please try again.';
    });
    request.send(new FormData(form));
  });
})();
</script>
<?= $this->endSection() ?>
