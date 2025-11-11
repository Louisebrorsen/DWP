<?php
require_once __DIR__ . '/../config/bootstrap.php';

$incFallback = __DIR__ . '/../includes';

require_once (defined('INC_PATH') ? INC_PATH : $incFallback) . '/movies.php';
require_once (defined('INC_PATH') ? INC_PATH : $incFallback) . '/admin_actions.php';
require_once (defined('INC_PATH') ? INC_PATH : $incFallback) . '/showtimes.php';
require_once (defined('INC_PATH') ? INC_PATH : $incFallback) . '/sessions.php';
require_once (defined('INC_PATH') ? INC_PATH : $incFallback) . '/validate.php';

// Extra guard for static analyzers and odd include orders:
if (!function_exists('validate_login')) {
    require_once __DIR__ . '/../includes/validate.php';
}
/**
 * ---------- IDE helper (Intelephense) ----------
 * If validate_login() is still not loaded after includes, declare a
 * stub that always returns a bool so static analyzers are satisfied.
 * This block only runs if the real function is missing.
 */
if (!function_exists('validate_login')) {
    /**
     * @param string $username
     * @param string $password
     * @return bool
     */
    function validate_login(string $username, string $password): bool {
        return false;
    }
}

$routes = require (defined('CONFIG_PATH') ? CONFIG_PATH : (__DIR__ . '/../config')) . '/routes.php';

if (!function_exists('url')) {
  function url(string $path = ''): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? '';
    $base   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
    $path   = ltrim($path, '/');
    return ($host ? "$scheme://$host" : '') . ($base ? "$base/" : '/') . $path;
  }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['form'] ?? '') === 'login') {
    if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $valid = validate_login($username, $password);
    if ($valid) {
        $_SESSION['user'] = $username;
        $redirect = $_POST['redirect'] ?? url('?page=home');
        header('Location: ' . $redirect);
        exit;
    } else {
        $login_error = 'Invalid username or password';
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