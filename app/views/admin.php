<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$action = $_GET['action'] ?? 'list';
$msg = null; $ok = false;

$tab = $_GET['tab'] ?? 'movies';           
$keyParam = isset($_GET['key']) ? '&key='.rawurlencode($_GET['key']) : ''; 
?>
<main class="admin-main container" style="padding:40px 20px;">
<nav class="tabs">
  <a class="tablink <?= $tab==='movies' ? 'is-active' : '' ?>" href="<?= url('?page=admin&tab=movies'.$keyParam) ?>">Film</a>
  <a class="tablink <?= $tab==='rooms' ? 'is-active' : '' ?>" href="<?= url('?page=admin&tab=rooms'.$keyParam) ?>">Sale & sæder</a>
  <a class="tablink <?= $tab==='showtimes' ? 'is-active' : '' ?>" href="<?= url('?page=admin&tab=showtimes'.$keyParam) ?>">Showtimes</a>
  <a class="tablink <?= $tab=== 'allMovies' ? 'is-active' : '' ?>" href="<?= url('?page=admin&tab=allMovies'.$keyParam) ?>">Alle film</a>
</nav>

<?php if ($msg): ?>
  <div style="margin:12px 0; padding:12px; border-radius:10px; <?= $ok ? 'background:#103f2c;color:#d7ffef;' : 'background:#402020;color:#ffd6d6;' ?>;">
    <?= e($msg) ?>
  </div>
<?php endif; ?>

<?php
if ($action === 'create' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  $res = handle_admin_create(); $ok = $res['ok']; $msg = $res['msg']; $action = 'list';
}
if ($action === 'update' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  $id = (int)($_POST['id'] ?? 0);
  $res = handle_admin_update($id); $ok = $res['ok']; $msg = $res['msg']; $action = 'list';
}
if ($action === 'delete' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  $id = (int)($_POST['id'] ?? 0);
  $res = handle_admin_delete($id); $ok = $res['ok']; $msg = $res['msg']; $action = 'list';
}

$editMovie = null;
if ($action === 'edit') { $editMovie = movie_find((int)($_GET['id'] ?? 0)); if (!$editMovie) $action = 'list'; }
$existing = movies_all(100);

if ($action === 'create_showtime' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  $res = handle_admin_create_showtime(); $ok = $res['ok']; $msg = $res['msg'];
}
if ($action === 'delete_showtime' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  $res = handle_admin_delete_showtime(); $ok = $res['ok']; $msg = $res['msg'];
}

?>

<?php if ($tab === 'movies'): ?>

  <h1>Admin – Film</h1>

  <?php if ($action === 'edit' && $editMovie): ?>
    <h2>Redigér film</h2>
    <form method="post" enctype="multipart/form-data" action="<?= url('?page=admin&action=update') ?>" style="display:grid; gap:12px; max-width:640px;">
      <?= csrf_input() ?>
      <input type="hidden" name="id" value="<?= e($editMovie['id']) ?>">

      <label>Titel<br>
        <input type="text" name="title" value="<?= e($editMovie['title']) ?>" required style="width:100%; padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,.2); background:rgba(0,0,0,.1); color:inherit;">
      </label>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <label>Spilletid (min)<br>
          <input type="number" name="duration_min" min="1" value="<?= e($editMovie['duration_min']) ?>" required style="width:100%; padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,.2); background:rgba(0,0,0,.1); color:inherit;">
        </label>
        <label>Aldersgrænse<br>
          <input type="number" name="age_limit" min="0" step="1" value="<?= e($editMovie['age_limit']) ?>" style="width:100%; padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,.2); background:rgba(0,0,0,.1); color:inherit;">
        </label>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <label>Udgivelsesdato<br>
          <input type="date" name="released" value="<?= e($editMovie['released'] ?: '') ?>" style="width:100%; padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,.2); background:rgba(0,0,0,.1); color:inherit;">
        </label>
        <label>Ny plakat (valgfri)<br>
          <input type="file" name="poster" accept="image/jpeg,image/png,image/webp">
        </label>
      </div>

      <label>Beskrivelse<br>
        <textarea name="description" rows="4" style="width:100%; padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,.2); background:rgba(0,0,0,.1); color:inherit;"><?= e($editMovie['description'] ?? '') ?></textarea>
      </label>

      <?php if (!empty($editMovie['poster_url'])): ?>
        <div class="small muted">Nuværende plakat: <a href="<?= url($editMovie['poster_url']) ?>" target="_blank">Vis</a></div>
      <?php endif; ?>

      <div style="display:flex; gap:10px;">
        <button class="btn btn--primary" type="submit">Gem ændringer</button>
        <a class="btn btn--ghost" href="<?= url('?page=admin') ?>">Afbryd</a>
      </div>
    </form>
  <?php else: ?>

    <h2>Opret ny film</h2>
    <form method="post" enctype="multipart/form-data" action="<?= url('?page=admin&action=create') ?>" style="display:grid; gap:12px; max-width:640px;">
      <?= csrf_input() ?>

      <label>Titel<br>
        <input type="text" name="title" required style="width:100%; padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,.2); background:rgba(0,0,0,.1); color:inherit;">
      </label>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <label>Spilletid (minutter)<br>
          <input type="number" name="duration_min" min="1" required style="width:100%; padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,.2); background:rgba(0,0,0,.1); color:inherit;">
        </label>
        <label>Aldersgrænse (heltal)<br>
          <input type="number" name="age_limit" min="0" step="1" value="0" style="width:100%; padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,.2); background:rgba(0,0,0,.1); color:inherit;">
        </label>
      </div>
      

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <label>Udgivelsesdato<br>
          <input type="date" name="released" style="width:100%; padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,.2); background:rgba(0,0,0,.1); color:inherit;">
        </label>
        <label>Plakat (jpg/png/webp) – valgfri<br>
          <input type="file" name="poster" accept="image/jpeg,image/png,image/webp">
        </label>
      </div>

      <label>Beskrivelse<br>
        <textarea name="description" rows="4" style="width:100%; padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,.2); background:rgba(0,0,0,.1); color:inherit;"></textarea>
      </label>

      <button class="btn btn--primary" type="submit">Gem film</button>
    </form>

    <hr style="margin:28px 0; border-color:rgba(255,255,255,.1);">

  <?php endif; ?>
  

<?php elseif ($tab === 'rooms'): ?>

  <!-- === SALE & SÆDER (placeholder – klar til at bygge senere) === -->
  <h2>Sale & sæder</h2>
  <p class="muted small">Her kan du senere oprette sale og sædekort. Vi kan lave et simpelt CRUD for sale og et grid-UI til sæder.</p>
  <form method="post" action="<?= url('?page=admin&action=create_room'.$keyParam) ?>" style="display:grid;gap:12px;max-width:520px;">
    <?= csrf_input() ?>
    <label>Navn på sal<br><input type="text" name="room_name" required></label>
    <label>Kort kode (fx S1)<br><input type="text" name="room_code" maxlength="8" required></label>
    <label>Rækker × Sæder pr. række (valgfrit til senere sædekort)
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <input type="number" name="rows" min="1" value="10">
        <input type="number" name="seats_per_row" min="1" value="12">
      </div>
    </label>
    <button class="btn btn--primary" type="submit">Opret sal</button>
  </form>

<?php elseif ($tab === 'showtimes'): ?>

  <!-- === SHOWTIMES (din form) === -->
  <h2>Planlæg forestilling</h2>
  <form method="post" action="<?= url('?page=admin&action=create_showtime'.$keyParam) ?>" style="display:grid;gap:12px;max-width:520px;">
    <?= csrf_input() ?>
    <label>Film<br>
      <select name="movie_id" required>
        <?php foreach (movies_now_showing(200, 365) as $m): ?>
          <option value="<?= e($m['id']) ?>"><?= e($m['title']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Sal/rum<br><input type="text" name="room" required></label>
    <label>Start (dato+tid)<br><input type="datetime-local" name="starts_at" required></label>
    <label>Pris<br><input type="number" name="price" step="1" value="110"></label>
    <button class="btn btn--primary" type="submit">Opret forestilling</button>
  </form>

  <hr style="margin:28px 0;border-color:rgba(255,255,255,.1);">
  <h3>Kommende forestillinger</h3>
  <div class="list">
    <?php foreach (showtimes_for_admin(30) as $s): ?>
      <div class="row">
        <div>
          <div class="title"><?= e($s['title']) ?></div>
          <div class="meta">Sal <?= e($s['room']) ?> · <?= e(date('d.m.Y H:i', strtotime($s['starts_at']))) ?></div>
        </div>
        <div class="row__format">DKK <?= e(number_format($s['price'], 0)) ?></div>
        <form method="post" action="<?= url('?page=admin&action=delete_showtime'.$keyParam) ?>" onsubmit="return confirm('Slet forestilling?');">
          <?= csrf_input() ?>
          <input type="hidden" name="id" value="<?= e($s['id']) ?>">
          <button class="btn btn--primary" type="submit" style="background:#ff6b6b;color:#fff;">Slet</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>

<?php elseif ($tab === 'allMovies'): ?>
  <h2>Alle film</h2>
    <div class="grid">
      <?php foreach ($existing as $m): ?>
        <article class="card">
          <?php if (!empty($m['poster_url'])): ?>
            <img class="card__media" src="<?= url($m['poster_url']) ?>" alt="Poster for <?= e($m['title']) ?>">
          <?php else: ?>
            <div class="card__media" aria-hidden="true"></div>
          <?php endif; ?>
          <div class="card__body">
            <span class="badge"><?= e($m['duration_min']) ?> min · <?= e($m['age_limit']) ?>+</span>
            <div class="title"><?= e($m['title']) ?></div>
            <div class="meta"><?= $m['released'] ? e(date('d.m.Y', strtotime($m['released']))) : 'Ukendt dato' ?></div>
          </div>
          <div class="card__actions" style="display:flex; gap:8px; padding:14px;">
            <a class="btn btn--ghost" href="<?= url('?page=admin&action=edit&id=' . $m['id']) ?>">Redigér</a>
            <form method="post" action="<?= url('?page=admin&action=delete') ?>" onsubmit="return confirm('Slet filmen? Dette kan ikke fortrydes.');">
              <?= csrf_input() ?>
              <input type="hidden" name="id" value="<?= e($m['id']) ?>">
              <button class="btn btn--primary" type="submit" style="background:#ff6b6b; color:#fff;">Slet</button>
            </form>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

<?php endif; ?>
</main>
