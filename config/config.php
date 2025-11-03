<?php

// Error reporting (slå ned i produktion)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Stier
define('BASE_PATH', dirname(__DIR__)); // dwp/
define('PUBLIC_PATH', BASE_PATH . '/public');

// BASE_URL – virker lokalt i f.eks. XAMPP (folder i htdocs)
$docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
$publicPath = str_replace('\\', '/', PUBLIC_PATH);
$baseUrl = rtrim(str_replace($docRoot, '', $publicPath), '/');
define('BASE_URL', $baseUrl ?: '');

define('DB_HOST', 'localhost');
define('DB_NAME', 'normanbrorsen_dk_db');  
define('DB_USER', 'louise');
define('DB_PASS', '123456');
define('DB_CHARSET', 'utf8mb4');

$SITE = [
  'name' => 'CineMagic',
  'description' => 'Din lokale biograf – moderne sale og god lyd',
];

