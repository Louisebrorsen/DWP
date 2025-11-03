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