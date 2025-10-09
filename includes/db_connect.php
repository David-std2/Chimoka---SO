<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) session_start();

$configCandidates = [__DIR__ . '/../config/config.php',];
foreach ($configCandidates as $cfg) {
    if (file_exists($cfg)) {
        require_once $cfg;
        break;
    }
}

$DB_HOST = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
$DB_NAME = defined('DB_NAME') ? DB_NAME : '';
$DB_USER_DEF = defined('DB_USER') ? DB_USER : '';
$DB_PASS_DEF = defined('DB_PASS') ? DB_PASS : '';

$sessionCreds = $_SESSION['db_creds'] ?? null;
$DB_USER = $sessionCreds['user'] ?? $DB_USER_DEF;
$DB_PASS = $sessionCreds['pass'] ?? $DB_PASS_DEF;

// mysqli
$conexion = mysqli_init();
if (function_exists('mysqli_options')) @mysqli_options($conexion, MYSQLI_OPT_CONNECT_TIMEOUT, 5);

// conectar
$connected = false;
if ($DB_NAME !== '')$connected = @mysqli_real_connect($conexion, $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($connected) mysqli_set_charset($conexion, 'utf8mb4');
else error_log('DB connection failed: ' . mysqli_connect_error());


$GLOBALS['conexion'] = $conexion;
return $conexion;
