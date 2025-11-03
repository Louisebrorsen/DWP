<?php
require_once __DIR__ . '/movies.php';
require_once __DIR__ . '/showtimes.php';


function handle_admin_create(): array {
  verify_csrf();
  $title = trim($_POST['title'] ?? '');
  $runtime = (int)($_POST['duration_min'] ?? 0);
  if ($title === '' || $runtime <= 0) return ['ok' => false, 'msg' => 'Udfyld titel og spilletid (>0).'];

  $posterRel = handle_poster_upload($title, $_FILES['poster'] ?? null);

  try {
    movie_create([
      'title'        => $title,
      'poster_url'   => $posterRel,
      'description'  => trim($_POST['description'] ?? ''),
      'released'     => trim($_POST['released'] ?? ''),
      'duration_min' => $runtime,
      'age_limit'    => (int)($_POST['age_limit'] ?? 0),
    ]);
    return ['ok' => true, 'msg' => 'Filmen blev oprettet.'];
  } catch (Throwable $e) {
    return ['ok' => false, 'msg' => 'Kunne ikke gemme: ' . $e->getMessage()];
  }
}

function handle_admin_update(int $id): array {
  verify_csrf();
  $movie = movie_find($id);
  if (!$movie) return ['ok' => false, 'msg' => 'Filmen findes ikke.'];

  $title = trim($_POST['title'] ?? '');
  $runtime = (int)($_POST['duration_min'] ?? 0);
  if ($title === '' || $runtime <= 0) return ['ok' => false, 'msg' => 'Udfyld titel og spilletid (>0).'];

  $posterRel = $movie['poster_url'];
  $new = handle_poster_upload($title, $_FILES['poster'] ?? null);
  if ($new) {
    // valgfrit: slet gammel fil
    if (!empty($posterRel) && is_file(PUBLIC_PATH . '/' . $posterRel)) { @unlink(PUBLIC_PATH . '/' . $posterRel); }
    $posterRel = $new;
  }

  try {
    movie_update($id, [
      'title'        => $title,
      'poster_url'   => $posterRel,
      'description'  => trim($_POST['description'] ?? ''),
      'released'     => trim($_POST['released'] ?? ''),
      'duration_min' => $runtime,
      'age_limit'    => (int)($_POST['age_limit'] ?? 0),
    ]);
    return ['ok' => true, 'msg' => 'Filmen er opdateret.'];
  } catch (Throwable $e) {
    return ['ok' => false, 'msg' => 'Kunne ikke opdatere: ' . $e->getMessage()];
  }
}

function handle_admin_delete(int $id): array {
  verify_csrf();
  $m = movie_find($id);
  if (!$m) return ['ok' => false, 'msg' => 'Filmen findes ikke.'];
  // valgfrit: slet poster fra disk
  if (!empty($m['poster_url']) && is_file(PUBLIC_PATH . '/' . $m['poster_url'])) { @unlink(PUBLIC_PATH . '/' . $m['poster_url']); }
  try {
    movie_delete($id);
    return ['ok' => true, 'msg' => 'Filmen er slettet.'];
  } catch (Throwable $e) {
    return ['ok' => false, 'msg' => 'Kunne ikke slette: ' . $e->getMessage()];
  }
}

function handle_poster_upload(string $title, ?array $file): ?string {
  if (!$file || empty($file['name'])) return null;
  $okTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
  $tmp  = $file['tmp_name'];
  if (!is_uploaded_file($tmp)) return null;
  $type = mime_content_type($tmp) ?: '';
  if (!isset($okTypes[$type])) return null;

  $ext = $okTypes[$type];
  $dir = PUBLIC_PATH . '/uploads/posters';
  if (!is_dir($dir)) { @mkdir($dir, 0775, true); }

  $safe = preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($title));
  $name = $safe . '-' . substr(sha1(uniqid('', true)), 0, 8) . '.' . $ext;
  $dest = $dir . '/' . $name;
  if (!move_uploaded_file($tmp, $dest)) return null;
  return 'uploads/posters/' . $name; // relativ sti
}

function showtime_create(int $movie_id, string $room, string $starts_at, float $price = 110.0): int {
  $sql = "INSERT INTO showtimes (movie_id, room, starts_at, price)
          VALUES (:mid, :room, :starts, :price)";
  $st = db()->prepare($sql);
  $st->execute([
    ':mid'    => $movie_id,
    ':room'   => $room,
    ':starts' => $starts_at, // 'YYYY-MM-DD HH:MM:00'
    ':price'  => $price,
  ]);
  return (int)db()->lastInsertId();
}


function handle_admin_create_showtime(): array {
  verify_csrf();
  $mid = (int)($_POST['movie_id'] ?? 0);
  $room = trim($_POST['room'] ?? '');
  $starts_local = trim($_POST['starts_at'] ?? ''); // 'YYYY-MM-DDTHH:MM'
  $price = (float)($_POST['price'] ?? 110);

  if ($mid <= 0 || $room === '' || $starts_local === '') {
    return ['ok' => false, 'msg' => 'Udfyld alle felter.'];
  }

  // Konverter fra <input type="datetime-local"> til MySQL DATETIME
  $starts_at = str_replace('T', ' ', $starts_local) . ':00';

  try {
    showtime_create($mid, $room, $starts_at, $price);
    return ['ok' => true, 'msg' => 'Forestilling oprettet.'];
  } catch (Throwable $e) {
    return ['ok' => false, 'msg' => 'Kunne ikke oprette: ' . $e->getMessage()];
  }
}

function handle_admin_delete_showtime(): array {
  verify_csrf();
  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) return ['ok' => false, 'msg' => 'Ugyldigt ID.'];

  try {
    showtime_delete($id);
    return ['ok' => true, 'msg' => 'Forestilling slettet.'];
  } catch (Throwable $e) {
    return ['ok' => false, 'msg' => 'Kunne ikke slette: ' . $e->getMessage()];
  }
}