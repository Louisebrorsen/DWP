<?php

function db(): PDO {
  static $pdo = null; // Huskes mellem kald (så der kun laves én forbindelse)
  if ($pdo) return $pdo; // Hvis forbindelsen allerede findes, brug den

  // Henter konstanter fra config.php
  $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

  // Indstillinger for PDO
  $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Smid fejl som exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Returner rækker som associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Brug rigtige prepared statements
  ];

  try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    return $pdo;
  } catch (PDOException $e) {
    // I udvikling kan du vise detaljer – i produktion bør du logge det i stedet
    http_response_code(500);
    exit('Database connection failed: ' . $e->getMessage());
  }
}