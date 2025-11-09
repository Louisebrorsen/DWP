<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once INC_PATH . '/movies.php';
require_once INC_PATH . '/admin_actions.php';
require_once INC_PATH . '/showtimes.php';
require_once INC_PATH . '/sessions.php';
require_once INC_PATH . '/validate.php';
$routes = require CONFIG_PATH . '/routes.php';

if (!function_exists('url')) {
  function url(string $path = ''): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? '';
    $base   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
    $path   = ltrim($path, '/');
    return ($host ? "$scheme://$host" : '') . ($base ? "$base/" : '/') . $path;
  }
}

// Vælg side
$page = $_GET['page'] ?? 'home';
$page = preg_replace('/[^a-z0-9_-]/i', '', $page); // simpel sanitizing
// Handle logout early (no output yet)
if ($page === 'logout') {
  if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
  // Clear session array
  $_SESSION = [];
  // Remove session cookie
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }
  // Destroy session
  session_destroy();
  // Redirect to home
  $dest = function_exists('url') ? url('?page=home') : '/';
  header('Location: ' . $dest);
  exit;
}

$view = $routes[$page] ?? ($page === 'home' || $page === '' ? 'home' : '404');

// Render – brug BASE_PATH konsekvent
$viewFile = BASE_PATH . '/app/views/' . $view . '.php';
$header   = BASE_PATH . '/app/views/partials/header.php';
$footer   = BASE_PATH . '/app/views/partials/footer.php';

// Header
if (is_readable($header)) {
    include $header;
}

// View
if (is_readable($viewFile)) {
    include $viewFile;
} else {
    // Fallback og lille debug, så vi kan se den sti PHP forsøger
    echo '<p style="padding:1rem">View mangler: ' . htmlspecialchars($viewFile, ENT_QUOTES, 'UTF-8') . '</p>';
    $fallback404 = BASE_PATH . '/app/views/404.php';
    if (is_readable($fallback404)) {
        include $fallback404;
    }
}

// Footer
if (is_readable($footer)) {
    include $footer;
}