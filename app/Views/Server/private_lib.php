<?= $this->extend('Layout/Starter') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <?= $this->include('Layout/msgStatus') ?>
    <div class="card mb-4">
      <div class="card-header"><i class="bi bi-hdd-stack me-2"></i> My private library</div>
      <div class="card-body p-4">
        <p class="text-muted mb-4">Your uploads belong to your account. Other members cannot list or download them.</p>
        <?php if (!empty($libraries)): ?>
          <div class="library-list mb-4">
            <?php foreach ($libraries as $item): ?>
              <?php $downloadUrl = site_url('lib/download') . '?file=' . rawurlencode($item['client_name']); ?>
              <section class="library-item">
                <div class="library-item-head"><strong><?= esc($item['client_name']) ?></strong><span><?= number_format((int) $item['size_bytes'] / 1048576, 2) ?> MB</span></div>
                <p class="library-meta">Updated <?= esc($item['uploaded_at']) ?></p>
                <label class="library-url-label" for="url-<?= md5($item['client_name']) ?>">Full library URL, sign-in required</label>
                <div class="library-url-row">
                  <input id="url-<?= md5($item['client_name']) ?>" class="library-url" type="text" readonly value="<?= esc($downloadUrl, 'attr') ?>">
                  <button type="button" class="library-action library-copy" data-url="<?= esc($downloadUrl, 'attr') ?>">Copy URL</button>
                </div>
                <div class="library-actions">
                  <a href="<?= esc($downloadUrl, 'attr') ?>" class="library-action">Download</a>
                  <?= form_open('lib/delete', ['class' => 'library-delete-form', 'onsubmit' => "return confirm('Delete this library?');"]) ?>
                    <input type="hidden" name="file" value="<?= esc($item['client_name'], 'attr') ?>">
                    <button class="library-action library-danger" type="submit">Delete</button>
                  <?= form_close() ?>
                </div>
              </section>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="mb-4">No libraries uploaded yet.</p>
        <?php endif; ?>
        <?= form_open_multipart('lib/upload', ['id' => 'private-library-upload-form']) ?>
          <label for="libfile" class="form-label">Upload a .so library or replace one with the same filename</label>
          <div class="private-library-picker mb-3">
            <input id="libfile" name="libfile" type="file" class="private-library-input" accept=".so" required aria-describedby="libfile-name">
            <label for="libfile" class="private-library-browse"><i class="bi bi-folder2-open me-2"></i> Choose .so file</label>
            <span id="libfile-name" class="private-library-filename" aria-live="polite">No file selected</span>
          </div>
          <button id="private-library-submit" class="btn btn-primary" type="submit">Save library</button>
          <div id="private-library-progress" class="library-progress" hidden role="status" aria-live="polite">
            <div class="library-progress-heading"><span id="library-progress-status">Preparing upload</span><strong id="library-progress-percent">0%</strong></div>
            <div id="library-progress-track" class="library-progress-track" role="progressbar" aria-label="Library upload" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div id="library-progress-fill" class="library-progress-fill"></div></div>
            <p id="library-progress-detail" class="library-progress-detail">Waiting to send file</p>
            <button id="library-upload-cancel" class="library-cancel" type="button">Cancel upload</button>
          </div>
        <?= form_close() ?>
      </div>
    </div>
  </div>
</div>
<style>
.private-library-picker { display:flex; align-items:center; gap:12px; flex-wrap:wrap; min-width:0; }
.private-library-input { position:absolute; width:1px; height:1px; opacity:0; overflow:hidden; clip-path:inset(50%); }
.private-library-input:focus-visible + .private-library-browse { outline: 2px solid var(--lamp); outline-offset: 3px; }
.private-library-browse { display:inline-flex; align-items:center; justify-content:center; cursor:pointer; min-height:44px; padding:12px 18px; border-radius:var(--r); font-weight:650; color:var(--ink); background:#38342f; border:1px solid var(--line-2); box-shadow:inset 0 1px 0 rgba(255,255,255,.12),0 4px 0 #181614,0 5px 0 var(--wall); transition:background-color .12s,transform .16s var(--press),box-shadow .16s var(--press); }
.private-library-browse:hover { color:#fff; background:#423d37; transform:translateY(-1px); }
.private-library-browse:active { transform:translateY(4px); box-shadow:inset 0 1px 0 rgba(255,255,255,.08),0 1px 0 var(--wall); }
.private-library-browse .bi { color:var(--lamp); }
.private-library-filename { min-width:0; flex:1 1 160px; color:#c7c9cd; font-size:.9rem; overflow-wrap:anywhere; }
.library-progress { margin-top:22px; padding:18px; border:1px solid var(--line); border-radius:var(--r-plate); background:var(--plate-2); box-shadow:inset 0 1px 0 rgba(255,255,255,.06),0 3px 0 var(--wall); }
.library-progress[hidden] { display:none !important; }
.library-progress-heading { display:flex; justify-content:space-between; gap:12px; color:#f6f6f7; font-size:.94rem; margin-bottom:12px; }
.library-progress-track { width:100%; height:12px; overflow:hidden; border-radius:var(--r); background:var(--well); box-shadow:inset 0 2px 0 rgba(0,0,0,.6); }
.library-progress-fill { height:100%; width:0%; background:var(--lamp); border-radius:var(--r); transition:width .15s ease; }
.library-progress-track.is-processing .library-progress-fill { width:40% !important; animation:library-wait 1.25s ease-in-out infinite alternate; }
@keyframes library-wait { from { transform:translateX(0); } to { transform:translateX(150%); } }
.library-progress-detail { margin:12px 0 8px; color:#bcbfc6; font-size:.84rem; overflow-wrap:anywhere; }
.library-cancel { border:1px solid var(--line-2); border-radius:var(--r); background:#38342f; color:var(--ink); padding:8px 15px; }
@media (prefers-reduced-motion:reduce) { .library-progress-track.is-processing .library-progress-fill { animation:none; } }
.library-list { display:grid; gap:16px; }
.library-item { padding:18px; border-radius:var(--r-plate); border:1px solid var(--line); background:var(--plate-2); box-shadow:inset 0 1px 0 rgba(255,255,255,.06),0 3px 0 var(--wall); }
.library-item-head { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:8px; color:#fff; font-size:1rem; overflow-wrap:anywhere; }
.library-item-head span { color:#d3d5d9; font-size:.85rem; }
.library-meta { color:#c3c5cb; font-size:.82rem; margin:8px 0 16px; }
.library-url-label { color:#e4e5e8; font-size:.86rem; margin-bottom:8px; display:block; }
.library-url-row { display:flex; flex-wrap:wrap; gap:8px; }
.library-url { flex:1 1 230px; min-width:0; padding:11px 12px; background:var(--well) !important; border:1px solid var(--line); border-radius:var(--r); color:var(--ink) !important; font-size:.86rem; }
.library-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:14px; align-items:center; }
.library-delete-form { margin:0; }
.library-action, .library-action:link, .library-action:visited { display:inline-flex; align-items:center; justify-content:center; min-height:44px; padding:10px 16px; background:#38342f !important; color:var(--ink) !important; border:1px solid var(--line-2); border-radius:var(--r); font-weight:650; text-decoration:none; cursor:pointer; box-shadow:inset 0 1px 0 rgba(255,255,255,.12),0 3px 0 #181614; }
.library-action:hover { background:#423d37 !important; color:#fff !important; }
.library-danger { border-color:var(--bad); }
#private-library-submit { background:var(--bone) !important; color:var(--bg) !important; border:0 !important; border-radius:var(--r) !important; font-weight:700; min-height:44px; padding:11px 20px; box-shadow:inset 0 1px 0 #fff,0 4px 0 var(--bone-wall),0 5px 0 var(--wall) !important; }
#private-library-submit:hover { background:#f3ecdd !important; color:var(--bg) !important; box-shadow:inset 0 1px 0 #fff,0 5px 0 var(--bone-wall),0 6px 0 var(--wall) !important; }
</style>
<script>
(function () {
    document.querySelectorAll('.library-copy').forEach(function(button) {
        button.addEventListener('click', async function() {
            const value = button.getAttribute('data-url');
            try { await navigator.clipboard.writeText(value); }
            catch (_) { const input = button.parentElement.querySelector('input'); input.select(); document.execCommand('copy'); }
            button.textContent = 'Copied';
            setTimeout(function(){ button.textContent = 'Copy URL'; }, 1800);
        });
    });
    const input = document.getElementById('libfile');
    const label = document.getElementById('libfile-name');
    if (!input || !label) return;
    const form = document.getElementById('private-library-upload-form');
    const progress = document.getElementById('private-library-progress');
    const track = document.getElementById('library-progress-track');
    const fill = document.getElementById('library-progress-fill');
    const percent = document.getElementById('library-progress-percent');
    const status = document.getElementById('library-progress-status');
    const detail = document.getElementById('library-progress-detail');
    const submit = document.getElementById('private-library-submit');
    const cancel = document.getElementById('library-upload-cancel');
    let xhr = null;
    let started = 0;
    const setProgress = (value) => {
        const n = Math.min(100, Math.max(0, Math.round(value)));
        fill.style.width = n + '%';
        percent.textContent = n + '%';
        track.setAttribute('aria-valuenow', String(n));
    };
    const reset = () => { submit.disabled = false; cancel.hidden = true; xhr = null; };
    form.addEventListener('submit', function (event) {
        if (!input.files || !input.files.length) return;
        event.preventDefault();
        if (xhr) return;
        const file = input.files[0];
        const data = new FormData(form);
        xhr = new XMLHttpRequest();
        started = Date.now();
        submit.disabled = true;
        cancel.hidden = false;
        progress.hidden = false;
        track.classList.remove('is-processing');
        setProgress(0);
        status.textContent = 'Uploading library';
        detail.textContent = '0 of ' + (file.size / 1048576).toFixed(2) + ' MB sent';
        xhr.upload.addEventListener('progress', function (e) {
            if (!e.lengthComputable) return;
            const ratio = e.loaded / e.total;
            setProgress(ratio * 100);
            const elapsed = Math.max((Date.now() - started) / 1000, .1);
            const rate = e.loaded / elapsed;
            const remaining = rate > 0 ? Math.ceil((e.total - e.loaded) / rate) : null;
            detail.textContent = (e.loaded / 1048576).toFixed(2) + ' of ' + (e.total / 1048576).toFixed(2) + ' MB sent' + (remaining !== null && ratio < 1 ? ' · about ' + remaining + 's remaining' : '');
        });
        xhr.upload.addEventListener('load', function () {
            status.textContent = 'Processing library';
            detail.textContent = 'Transfer complete. Waiting for server validation and storage.';
            percent.textContent = '100% sent';
            track.classList.add('is-processing');
            cancel.hidden = true;
        });
        xhr.addEventListener('load', function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                status.textContent = 'Refreshing library';
                detail.textContent = 'Checking server result.';
                const finalUrl = xhr.responseURL || '';
                if (/\/login(?:[?#]|$)/i.test(finalUrl)) {
                    track.classList.remove('is-processing');
                    status.textContent = 'Session expired';
                    detail.textContent = 'Your login session expired. Sign in again, then check whether the library was saved.';
                    percent.textContent = 'Sign in';
                    reset();
                    return;
                }
                window.location.assign(finalUrl || form.action);
            } else {
                track.classList.remove('is-processing');
                status.textContent = 'Upload failed';
                percent.textContent = 'Error';
                detail.textContent = 'Server returned HTTP ' + xhr.status + '. Check PHP upload limits and retry.';
                reset();
            }
        });
        xhr.addEventListener('error', function () {
            track.classList.remove('is-processing');
            status.textContent = 'Connection failed';
            detail.textContent = 'Network interrupted. Retry the upload.';
            reset();
        });
        xhr.addEventListener('abort', function () {
            track.classList.remove('is-processing');
            status.textContent = 'Upload cancelled';
            detail.textContent = 'No new file was submitted.';
            reset();
        });
        cancel.onclick = function () { if (xhr) xhr.abort(); };
        xhr.open('POST', form.action);
        xhr.send(data);
    });
    input.addEventListener('change', function () {
        const file = input.files && input.files[0];
        label.textContent = file ? file.name + ' (' + (file.size / 1048576).toFixed(2) + ' MB)' : 'No file selected';
    });
})();
</script>
<?= $this->endSection() ?>
