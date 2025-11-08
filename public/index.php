<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/movies.php';
require_once __DIR__ . '/../includes/admin_actions.php';
require_once __DIR__ . '/../includes/showtimes.php'; 
require_once __DIR__ . '/../includes/sessions.php';
require_once __DIR__ . '/../includes/validate.php';

$routes = require __DIR__ . '/../config/routes.php';

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

// Render
$viewFile = BASE_PATH . '/app/Views/' . $view . '.php';
$header = BASE_PATH . '/app/Views/partials/header.php';
$footer = BASE_PATH . '/app/Views/partials/footer.php';

// Header
if (file_exists($header)) { include $header; }

// View
if (file_exists($viewFile)) { include $viewFile; }
else { include BASE_PATH . '/app/Views/404.php'; }

// Footer
if (file_exists($footer)) { include $footer; }