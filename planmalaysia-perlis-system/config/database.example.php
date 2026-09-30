<?php
/**
 * ========================================================================
 * CONTOH KONFIGURASI PENGKALAN DATA (database.example.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 * 
 * ARAHAN:
 * 1. Salin fail ini kepada 'database.php' di dalam direktori 'config/'.
 * 2. Fail 'database.php' telah diabaikan (ignored) oleh .gitignore demi keselamatan.
 * 3. Tetapkan pembolehubah persekitaran (Environment Variables) atau kemaskini
 *    nilai di bawah mengikut tetapan pelayan MySQL anda.
 */

// Menghalang capaian terus dari pelayar web
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// 1. Fungsi ringkas untuk membaca fail .env jika wujud
if (!function_exists('loadEnvVariables')) {
    function loadEnvVariables($envPath) {
        if (!file_exists($envPath)) {
            return;
        }
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            // Abaikan komen
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
}

// Muat fail .env dari punca aplikasi
loadEnvVariables(APP_ROOT . '/.env');

// 2. Pembolehubah sambungan pangkalan data (Keutamaan: getenv -> Nilai Contoh)
$dbHost     = getenv('DB_HOST')     ?: '127.0.0.1';
$dbPort     = getenv('DB_PORT')     ?: '3306';
$dbName     = getenv('DB_DATABASE') ?: 'db_planmalaysia_perlis';
$dbUser     = getenv('DB_USERNAME') ?: 'root';
$dbPassword = getenv('DB_PASSWORD') ?: '';
$dbCharset  = getenv('DB_CHARSET')  ?: 'utf8mb4';

/**
 * Fungsi untuk mendapatkan objek sambungan PDO MySQL yang selamat
 *
 * @return PDO
 * @throws PDOException
 */
function getDatabaseConnection() {
    global $dbHost, $dbPort, $dbName, $dbUser, $dbPassword, $dbCharset;
    static $pdoInstance = null;

    if ($pdoInstance !== null) {
        return $pdoInstance;
    }

    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$dbCharset}";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // Mengaktifkan persediaan kenyataan sebenar di peringkat MySQL
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbCharset} COLLATE utf8mb4_unicode_ci"
    ];

    try {
        $pdoInstance = new PDO($dsn, $dbUser, $dbPassword, $options);
        return $pdoInstance;
    } catch (PDOException $e) {
        // Log ralat ke fail log tanpa mendedahkan kata laluan kepada pengguna
        error_log("[RALAT DB][" . date('Y-m-d H:i:s') . "] Sambungan gagal: " . $e->getMessage());

        $debugMode = getenv('APP_DEBUG') === 'true';
        if ($debugMode) {
            die("<div style='background:#f8d7da;color:#721c24;padding:15px;border-radius:6px;font-family:sans-serif;'>
                <h3>Ralat Sambungan Pangkalan Data</h3>
                <p>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>
            </div>");
        } else {
            die("<div style='background:#f8d7da;color:#721c24;padding:20px;border-radius:6px;font-family:sans-serif;max-width:600px;margin:50px auto;text-align:center;'>
                <h2>Harap Maaf</h2>
                <p>Sistem tidak dapat berhubung dengan pelayan pangkalan data pada masa ini. Sila hubungi Pentadbir Sistem ICT PLANMalaysia Perlis.</p>
            </div>");
        }
    }
}
