<?php
/**
 * ========================================================================
 * MODUL AUTENTIKASI & KESELAMATAN PENGGUNA (auth.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Semak sama ada pengguna telah log masuk ke dalam sesi
 *
 * @return bool
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Dapatkan maklumat pengguna yang sedang log masuk
 *
 * @return array|null
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'       => $_SESSION['user_id'],
        'name'     => $_SESSION['user_name'] ?? 'Pengguna',
        'email'    => $_SESSION['user_email'] ?? '',
        'role'     => $_SESSION['user_role'] ?? 'Staff',
        'unit'     => $_SESSION['user_unit'] ?? 'Perancangan Wilayah',
        'avatar'   => $_SESSION['user_avatar'] ?? null
    ];
}

/**
 * Wajibkan pengguna log masuk sebelum mengakses halaman tertentu
 *
 * @param string $redirectUrl
 * @return void
 */
function requireLogin(string $redirectUrl = 'login.php'): void {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = 'Sila log masuk untuk mengakses halaman ini.';
        header("Location: {$redirectUrl}");
        exit;
    }
}

/**
 * Semak sama ada pengguna mempunyai peranan yang dibenarkan
 *
 * @param string|array $allowedRoles
 * @return bool
 */
function hasRole($allowedRoles): bool {
    if (!isLoggedIn()) {
        return false;
    }
    $userRole = $_SESSION['user_role'] ?? '';
    if (is_array($allowedRoles)) {
        return in_array($userRole, $allowedRoles, true);
    }
    return $userRole === $allowedRoles;
}

/**
 * Sekat akses jika pengguna tidak mempunyai peranan yang ditetapkan
 *
 * @param string|array $allowedRoles
 * @return void
 */
function requireRole($allowedRoles): void {
    requireLogin();
    if (!hasRole($allowedRoles)) {
        http_response_code(403);
        include __DIR__ . '/header.php';
        echo "<div class='container my-5'>
            <div class='alert alert-danger p-4 text-center'>
                <h3 class='fw-bold'>403 - Akses Disekat</h3>
                <p>Harap maaf, akaun anda tidak mempunyai kebenaran untuk membuka modul pentadbiran ini.</p>
                <a href='dashboard.php' class='btn btn-primary mt-2'>Kembali ke Papan Pemuka</a>
            </div>
        </div>";
        include __DIR__ . '/footer.php';
        exit;
    }
}

/**
 * Fungsi Pengesahan Log Masuk Pengguna (Secure Login)
 * Menggunakan Kata Laluan Hash (Bcrypt/Argon2) & Perlindungan Brute-Force
 *
 * @param string $email
 * @param string $password
 * @return array [ 'success' => bool, 'message' => string ]
 */
function attemptLogin(string $email, string $password): array {
    $email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));
    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Sila masukkan e-mel rasmi dan kata laluan anda.'];
    }

    // Semakan had percubaan login (Brute Force Protection)
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();
    }

    if ($_SESSION['login_attempts'] >= 5) {
        $elapsed = time() - $_SESSION['last_attempt_time'];
        if ($elapsed < 900) { // 15 minit sekat
            $remaining = ceil((900 - $elapsed) / 60);
            return [
                'success' => false, 
                'message' => "Akaun dikunci buat sementara kerana 5 percubaan gagal. Sila cuba lagi selepas {$remaining} minit."
            ];
        } else {
            // Tetapkan semula selepas tamat tempoh sekat
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt_time'] = time();
        }
    }

    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, password, role, unit, status FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Semak sama ada akaun aktif
            if (isset($user['status']) && $user['status'] !== 'Aktif') {
                return ['success' => false, 'message' => 'Akaun anda telah dinyahaktifkan. Sila hubungi Pentadbir Sistem ICT PLANMalaysia Perlis.'];
            }

            // Sahkan kata laluan dengan password_verify
            if (password_verify($password, $user['password'])) {
                // Berjaya log masuk - Bersihkan had percubaan
                $_SESSION['login_attempts'] = 0;

                // Cegah Serangan Session Fixation dengan menjana semula Session ID
                session_regenerate_id(true);

                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role']  = $user['role'];
                $_SESSION['user_unit']  = $user['unit'] ?? 'Perancangan Korporat';
                $_SESSION['LAST_ACTIVITY'] = time();

                // Kemaskini masa log masuk terakhir di database
                try {
                    $upd = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                    $upd->execute([':id' => $user['id']]);
                } catch (\Exception $ignore) {}

                return ['success' => true, 'message' => 'Log masuk berjaya. Selamat kembali ke Portal PLANMalaysia Perlis.'];
            }
        }

        // Jika gagal
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        $left = 5 - $_SESSION['login_attempts'];
        $leftMsg = $left > 0 ? " (Baki percubaan: {$left})" : " Akaun disekat 15 minit.";

        return ['success' => false, 'message' => 'E-mel atau kata laluan tidak sah.' . $leftMsg];

    } catch (PDOException $e) {
        error_log("[RALAT AUTH] " . $e->getMessage());
        return ['success' => false, 'message' => 'Ralat pangkalan data berlaku semasa log masuk. Sila cuba sebentar lagi.'];
    }
}

/**
 * Log Keluar Pengguna & Musnahkan Sesi Secara Selamat
 *
 * @param string $redirectUrl
 * @return void
 */
function logoutUser(string $redirectUrl = 'login.php'): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: {$redirectUrl}?logout=success");
    exit;
}

/**
 * Jana Token CSRF untuk keselamatan borang HTML
 *
 * @return string
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Sahkan Token CSRF yang dihantar dari borang POST
 *
 * @param string|null $token
 * @return bool
 */
function validateCsrfToken(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
