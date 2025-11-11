<?php
require_once __DIR__ . '/connection.php';

function movie_find_by_title_released(string $title, ?string $released): ?array {
  $sql = "SELECT movieID AS id, title, poster_url, description, released, duration_min, age_limit
          FROM movie
          WHERE LOWER(TRIM(title)) = LOWER(TRIM(:title))
            AND ((:released IS NULL AND released IS NULL) OR released = :released)
          LIMIT 1";
  $stmt = db()->prepare($sql);
  $stmt->execute([
    ':title'    => $title,
    ':released' => $released,
  ]);
  $row = $stmt->fetch();
  return $row ?: null;
}

function movie_create(array $data): int {
  // Normalize input
  $title    = trim((string)($data['title'] ?? ''));
  $released = $data['released'] !== '' ? ($data['released'] ?? null) : null; // allow null/empty

  if ($title === '') {
    // Title is required
    return 0;
  }

  // If a movie with same normalized title and released date exists, return its id instead of inserting another
  if ($existing = movie_find_by_title_released($title, $released)) {
    $_SESSION['message'] = "Filmen '{$title}' eksisterer allerede og blev ikke oprettet igen.";
    return (int)$existing['id'];
  }

  $sql = "INSERT INTO movie (title, poster_url, description, released, duration_min, age_limit)
          VALUES (:title, :poster, :descr, :released, :duration, :age)";
  $stmt = db()->prepare($sql);
  $stmt->execute([
    ':title'    => $title,
    ':poster'   => $data['poster_url'] ?? null,
    ':descr'    => $data['description'] ?? null,
    ':released' => $released,
    ':duration' => (int)($data['duration_min'] ?? 0),
    ':age'      => (int)($data['age_limit'] ?? 0),
  ]);
  $_SESSION['message'] = "Filmen '{$title}' blev oprettet.";
  return (int)db()->lastInsertId();
}

function movies_all(int $limit = 50): array {
  $sql = "SELECT movieID AS id, title, poster_url, description, released, duration_min, age_limit
          FROM movie ORDER BY movieID DESC LIMIT :lim";
  $stmt = db()->prepare($sql);
  $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->fetchAll();
}

function movie_find(int $id): ?array {
  $stmt = db()->prepare("SELECT movieID AS id, title, poster_url, description, released, duration_min, age_limit FROM movie WHERE movieID = :id");
  $stmt->execute([':id' => $id]);
  $row = $stmt->fetch();
  return $row ?: null;
}

function movie_update(int $id, array $data): bool {
  $sql = "UPDATE movie
          SET title = :title,
              poster_url = :poster,
              description = :descr,
              released = :released,
              duration_min = :duration,
              age_limit = :age
          WHERE movieID = :id";
  $stmt = db()->prepare($sql);
  return $stmt->execute([
    ':id'       => $id,
    ':title'    => $data['title'],
    ':poster'   => $data['poster_url'] ?? null,
    ':descr'    => $data['description'] ?? null,
    ':released' => $data['released'] ?: null,
    ':duration' => (int)($data['duration_min'] ?? 0),
    ':age'      => (int)($data['age_limit'] ?? 0),
  ]);
}

function movie_delete(int $id): bool {
  $stmt = db()->prepare("DELETE FROM movie WHERE movieID = :id");
  return $stmt->execute([':id' => $id]);
}