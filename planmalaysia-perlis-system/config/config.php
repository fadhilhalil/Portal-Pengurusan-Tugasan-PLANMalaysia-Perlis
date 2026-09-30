<?php
/**
 * ========================================================================
 * KONFIGURASI UTAMA APLIKASI (config.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */

// Pastikan laluan asas didefinisikan
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Zon Waktu Rasmi Malaysia
date_default_timezone_set('Asia/Kuala_Lumpur');

// Muat pembolehubah .env
if (file_exists(APP_ROOT . '/.env')) {
    $envLines = file(APP_ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) continue;
        if (strpos($line, '=') !== false) {
            list($k, $v) = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v, " \t\n\r\0\x0B\"'");
            if (!isset($_SERVER[$k]) && !isset($_ENV[$k])) {
                putenv("{$k}={$v}");
                $_ENV[$k] = $v;
                $_SERVER[$k] = $v;
            }
        }
    }
}

// Pemalar Sistem
define('APP_NAME', getenv('APP_NAME') ?: 'Sistem Pengurusan Tugasan PLANMalaysia Perlis');
define('APP_VERSION', '2.5.0');
define('APP_DEPT', 'Jabatan Perancangan Bandar dan Desa Negeri Perlis');
define('APP_STATE', 'Negeri Perlis Indera Kayangan');
define('APP_DEBUG', (getenv('APP_DEBUG') === 'true'));

// Konfigurasi Ralat mengikut mod
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', APP_ROOT . '/logs/php_errors.log');
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
}

// Konfigurasi Keselamatan Kuki Sesi
if (session_status() === PHP_SESSION_NONE) {
    $lifetime = (int)(getenv('SESSION_LIFETIME') ?: 7200);
    $secure = (getenv('SESSION_SECURE') === 'true') || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $httpOnly = true;

    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => $httpOnly,
        'samesite' => 'Lax'
    ]);

    session_name('PMSESSION_ID');
    session_start();
}

// Semakan Keselamatan Masa Tamat Sesi (2 Jam ketidakaktifan)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 7200)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['LAST_ACTIVITY'] = time();

// Muat fail sambungan pangkalan data (Keutamaan: database.php, jika tiada fallback ke database.example.php)
if (file_exists(APP_ROOT . '/config/database.php')) {
    require_once APP_ROOT . '/config/database.php';
} elseif (file_exists(APP_ROOT . '/config/database.example.php')) {
    require_once APP_ROOT . '/config/database.example.php';
}
