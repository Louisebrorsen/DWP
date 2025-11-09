<?php
// Start session as early as possible to avoid "headers already sent"
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// BASE_PATH peger på roden (én mappe op fra /config)
if (!defined('BASE_PATH')) {
  define('BASE_PATH', dirname(__DIR__));
}

// Smart path resolver – håndterer både /app og /public_html/app osv.
$APP1 = BASE_PATH . '/app';
$APP2 = BASE_PATH . '/public_html/app';
$INC1 = BASE_PATH . '/includes';
$INC2 = BASE_PATH . '/public_html/includes';
$CFG1 = BASE_PATH . '/config';
$CFG2 = BASE_PATH . '/public_html/config';

if (!defined('APP_PATH'))    define('APP_PATH',    is_dir($APP1) ? $APP1 : $APP2);
if (!defined('INC_PATH'))    define('INC_PATH',    is_dir($INC1) ? $INC1 : $INC2);
if (!defined('CONFIG_PATH')) define('CONFIG_PATH', is_dir($CFG1) ? $CFG1 : $CFG2);
if (!defined('VIEWS_PATH'))  define('VIEWS_PATH',  APP_PATH . '/views');

// Indlæs konfiguration og kernefiler
require CONFIG_PATH . '/config.php';
require INC_PATH . '/connection.php';
require INC_PATH . '/helpers.php';
require INC_PATH . '/security.php';
require INC_PATH . '/movies.php';
require INC_PATH . '/admin_actions.php';

// Error handling (kan deaktiveres i udvikling)
if (!defined('DEV')) define('DEV', true);
if (DEV === false) {
  set_exception_handler('handle_exception');
  set_error_handler('handle_error');
  register_shutdown_function('handle_shutdown');
}

// Autoload til klasser
spl_autoload_register(function ($class) {
  $path = APP_PATH . '/Classes/' . $class . '.php';
  if (file_exists($path)) require $path;
});