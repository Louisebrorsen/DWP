<?php
// HTML escape
define('ENT_FLAGS', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
function e(string $str): string { return htmlspecialchars($str, ENT_FLAGS, 'UTF-8'); }

// URL helpers
function url(string $path = ''): string { return rtrim(BASE_URL . '/' . ltrim($path, '/'), '/'); }
function asset($path) {
    // Sørg for at alle assets hentes fra /assets/
    $base = rtrim(BASE_URL, '/');
    return $base . '/assets/' . ltrim($path, '/');
}
// Simple active‑link helper
function nav_active(string $hashId): string {
  $q = $_GET['page'] ?? 'home';
  if ($hashId === 'home' && ($q === 'home' || $q === '')) return 'aria-current="page"';
  return $q === $hashId ? 'aria-current="page"' : '';
}