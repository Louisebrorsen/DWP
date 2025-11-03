<?php
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/movies.php';

function movies_upcoming(int $limit = 12): array {
  $sql = "SELECT movieID AS id, title, poster_url, released, duration_min, age_limit
          FROM movie
          WHERE released > CURDATE()
          ORDER BY released ASC
          LIMIT :lim";
  $st = db()->prepare($sql);
  $st->bindValue(':lim', $limit, PDO::PARAM_INT);
  $st->execute();
  return $st->fetchAll();
}

function movies_now_showing(int $limit = 12, int $days_horizon = 7): array {
  // Nuværende = udgivet + har showtimes i dag eller de næste X dage
  $sql = "SELECT DISTINCT m.movieID AS id, m.title, m.poster_url, m.released, m.duration_min, m.age_limit
          FROM movie m
          JOIN showtimes s ON s.movie_id = m.movieID
          WHERE m.released <= CURDATE()
            AND s.starts_at BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
          ORDER BY m.released DESC, MIN(s.starts_at) ASC
          LIMIT :lim";
  $st = db()->prepare($sql);
  $st->bindValue(':days', $days_horizon, PDO::PARAM_INT);
  $st->bindValue(':lim', $limit, PDO::PARAM_INT);
  $st->execute();
  return $st->fetchAll();
}

function showtimes_today(): array {
  $sql = "SELECT s.id, s.movie_id, m.title, m.poster_url, s.room, s.starts_at, s.price
          FROM showtimes s
          JOIN movie m ON m.movieID = s.movie_id
          WHERE DATE(s.starts_at) = CURDATE()
          ORDER BY s.starts_at ASC";
  return db()->query($sql)->fetchAll();
}


if (!function_exists('showtime_create')) {
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
}

function showtimes_for_admin(int $limit = 30): array {
  $sql = "SELECT s.id, s.movie_id, m.title, s.room, s.starts_at, s.price
          FROM showtimes s
          JOIN movie m ON m.movieID = s.movie_id
          WHERE s.starts_at >= NOW()
          ORDER BY s.starts_at ASC
          LIMIT :lim";
  $st = db()->prepare($sql);
  $st->bindValue(':lim', $limit, PDO::PARAM_INT);
  $st->execute();
  return $st->fetchAll();
}

function showtime_delete(int $id): bool {
  $st = db()->prepare("DELETE FROM showtimes WHERE id = :id");
  return $st->execute([':id' => $id]);
}