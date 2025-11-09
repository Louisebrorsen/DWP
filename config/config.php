<?php

// Error reporting (slå ned i produktion)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Paths
 * Resolve dynamically so it virker både lokalt (XAMPP) og på Simply (public_html).
 */

// Projektrod
if (!defined('BASE_PATH')) {
  define('BASE_PATH', dirname(__DIR__)); // fx /var/www/normanbrorsen.dk
}

// Webrodsmappe: foretræk /public_html, ellers /public, ellers BASE_PATH
if (!defined('PUBLIC_PATH')) {
  $publicCandidates = [
    BASE_PATH . '/public_html',
    BASE_PATH . '/public',
  ];
  $resolvedPublic = null;
  foreach ($publicCandidates as $p) {
    if (is_dir($p)) { $resolvedPublic = $p; break; }
  }
  define('PUBLIC_PATH', $resolvedPublic ?: BASE_PATH);
}

// app/, includes/, config/
if (!defined('APP_PATH')) {
  $candidatesApp = [ BASE_PATH . '/public_html/app', BASE_PATH . '/app' ];
  $resolved = null;
  foreach ($candidatesApp as $p) { if (is_dir($p)) { $resolved = $p; break; } }
  define('APP_PATH', $resolved ?: BASE_PATH . '/app');
}

if (!defined('INCLUDES_PATH')) {
  $candidatesInc = [ BASE_PATH . '/public_html/includes', BASE_PATH . '/includes' ];
  $resolved = null;
  foreach ($candidatesInc as $p) { if (is_dir($p)) { $resolved = $p; break; } }
  define('INCLUDES_PATH', $resolved ?: BASE_PATH . '/includes');
}

if (!defined('CONFIG_PATH')) {
  $candidatesCfg = [ BASE_PATH . '/public_html/config', BASE_PATH . '/config' ];
  $resolved = null;
  foreach ($candidatesCfg as $p) { if (is_dir($p)) { $resolved = $p; break; } }
  define('CONFIG_PATH', $resolved ?: BASE_PATH . '/config');
}

// views/ (håndterer både "views" og "Views")
if (!defined('VIEWS_PATH')) {
  $views = APP_PATH . '/views';
  if (!is_dir($views)) {
    $alt = APP_PATH . '/Views';
    if (is_dir($alt)) $views = $alt;
  }
  define('VIEWS_PATH', $views);
}

/**
 * BASE_URL – bruges af fx asset('css/style.css')
 * Finder URL-delen til PUBLIC_PATH relativt til DOCUMENT_ROOT.
 */
$docRoot   = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/') : '';
$publicDir = str_replace('\\', '/', PUBLIC_PATH);
$baseUrl   = $docRoot ? rtrim(str_replace($docRoot, '', $publicDir), '/') : '';
if (!defined('BASE_URL')) {
  define('BASE_URL', $baseUrl ?: '');
}

/**
 * Site-info
 */
$SITE = [
  'name' => 'CineMagic',
  'description' => 'Din lokale biograf – moderne sale og god lyd',
];

/**
 * Database – skift automatisk mellem lokal og live
 */
$isLocal = false;
if (isset($_SERVER['HTTP_HOST'])) {
  $host = $_SERVER['HTTP_HOST'];
  $isLocal = (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false);
}

if ($isLocal) {
  // Lokalt miljø (XAMPP)
  if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
  if (!defined('DB_PORT')) define('DB_PORT', 3306);
  if (!defined('DB_NAME')) define('DB_NAME', 'normanbrorsen_dk_db');
  if (!defined('DB_USER')) define('DB_USER', 'louise');
  if (!defined('DB_PASS')) define('DB_PASS', '123456');
} else {
  // Live på Simply
  if (!defined('DB_HOST')) define('DB_HOST', 'mysql79.unoeuro.com');
  if (!defined('DB_PORT')) define('DB_PORT', 3306);
  if (!defined('DB_NAME')) define('DB_NAME', 'normanbrorsen_dk_db');
  if (!defined('DB_USER')) define('DB_USER', 'normanbrorsen_dk');
  if (!defined('DB_PASS')) define('DB_PASS', 'hrHGwe2bAEzcF4pBkngy');
}

if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');