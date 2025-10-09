<?php
// includes/auth.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$configPath = __DIR__ . '/../config/config.php';
if (file_exists($configPath)) require_once $configPath;

function _test_db_credentials($user, $pass) {
    if (!defined('DB_HOST') || !defined('DB_NAME')) return false;
    $host = DB_HOST;
    $db   = DB_NAME;

    $mysqli = mysqli_init();
    if (function_exists('mysqli_options')) {
        @mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    }

    $ok = @mysqli_real_connect($mysqli, $host, $user, $pass, $db);
    if ($ok) { @mysqli_close($mysqli); return true; }
    @mysqli_close($mysqli);
    return false;
}

function login_user($username, $password) {
    $username = (string) trim($username);
    $password = (string) $password;
    if ($username === '' || $password === '') return false;

    if (_test_db_credentials($username, $password)) {
        $_SESSION['user'] = ['username' => $username, 'via' => 'db_user'];
        $_SESSION['db_creds'] = ['user' => $username, 'pass' => $password];
        return true;
    }
    return false;
}

function is_logged_in() {
    return !empty($_SESSION['user']) && !empty($_SESSION['user']['username']);
}

function logout_user() {
    if (session_status() !== PHP_SESSION_ACTIVE) return;
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

function require_login($redirectTo = 'auth/login.php') {
    if (is_logged_in()) return;
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    if ($scriptDir === '' || $scriptDir === '/') $base = '';
    else $base = $scriptDir;
    $url = $base . '/' . ltrim($redirectTo, '/');
    header('Location: ' . $url);
    exit;
}
