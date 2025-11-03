<?php
define('APP_ROOT', dirname(__DIR__)); // Roden af projektet (dwp/)

require APP_ROOT . '/config/config.php';
require APP_ROOT . '/includes/connection.php';
require APP_ROOT . '/includes/helpers.php';
require APP_ROOT . '/includes/security.php';

// Du kan tilføje mere her — fx din movies.php, admin_actions.php osv.
require APP_ROOT . '/includes/movies.php';
require APP_ROOT . '/includes/admin_actions.php';

// Evt. error handling (kan udelades i udvikling)
if (!defined('DEV')) define('DEV', true);

if (DEV === false) {
  set_exception_handler('handle_exception');
  set_error_handler('handle_error');
  register_shutdown_function('handle_shutdown');
}

// Autoload til klasser (hvis du laver dem)
spl_autoload_register(function ($class) {
  $path = APP_ROOT . '/app/Classes/' . $class . '.php';
  if (file_exists($path)) require $path;
});