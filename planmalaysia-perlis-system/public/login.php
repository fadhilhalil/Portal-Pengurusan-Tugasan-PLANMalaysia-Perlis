<?php
/**
 * ========================================================================
 * LAMAN LOG MASUK RASMI (login.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Jika sudah log masuk, lencong terus ke dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errorMessage = '';
$successMessage = '';

if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $successMessage = 'Anda telah berjaya log keluar dari sistem.';
}

// Proses Penghantaran Borang Log Masuk
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        $errorMessage = 'Sesi pengesahan keselamatan CSRF telah tamat tempoh. Sila muat semula halaman ini.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $result = attemptLogin($email, $password);
        if ($result['success']) {
            setFlash('success', $result['message']);
            header('Location: dashboard.php');
            exit;
        } else {
            $errorMessage = $result['message'];
        }
    }
}

$pageTitle = 'Log Masuk Pengguna';
include __DIR__ . '/../includes/header.php';
?>

<div class="login-page d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5">
                
                <!-- KAD LOG MASUK RASMI KERAJAAN -->
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <!-- Kepala Kad Rasmi -->
                    <div class="card-header bg-navy text-white text-center py-4 px-4 position-relative border-0">
                        <div class="badge-ribbon mb-2">
                            <span class="badge bg-warning text-dark text-uppercase px-3 py-1 fw-bold tracking-wide" style="font-size: 0.72rem;">Sistem Rasmi Kerajaan</span>
                        </div>
                        <div class="logo-circle bg-white p-2 rounded-circle mx-auto mb-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <img src="../assets/images/logo.svg" alt="Logo PLANMalaysia Perlis" height="54">
                        </div>
                        <h4 class="fw-bold mb-1 tracking-wide">PLANMalaysia @ PERLIS</h4>
                        <p class="text-white-50 small mb-0">Jabatan Perancangan Bandar dan Desa Negeri Perlis</p>
                    </div>

                    <!-- Badan Borang Log Masuk -->
                    <div class="card-body p-4 p-md-5 bg-white">
                        <h5 class="fw-bold text-navy text-center mb-3">Log Masuk Kakitangan</h5>
                        <p class="text-muted small text-center mb-4">Sila masukkan e-mel rasmi dan kata laluan untuk mengakses portal pengurusan tugas & projek.</p>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center small py-2" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                <div><?= htmlspecialchars($errorMessage) ?></div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center small py-2" role="alert">
                                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                <div><?= htmlspecialchars($successMessage) ?></div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST" autocomplete="off" id="loginForm">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                            <!-- E-mel Rasmi -->
                            <div class="mb-3">
                                <label for="email" class="form-label small fw-semibold text-secondary">E-mel Rasmi Jabatan</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope-fill"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" required placeholder="nama@perlis.gov.my" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- Kata Laluan -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="password" class="form-label small fw-semibold text-secondary mb-0">Kata Laluan</label>
                                </div>
                                <div class="input-group mt-1">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0" id="password" name="password" required placeholder="••••••••">
                                    <button class="btn btn-outline-light border text-muted" type="button" id="togglePasswordBtn">
                                        <i class="bi bi-eye-fill" id="togglePasswordIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Butang Log Masuk -->
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-navy text-white fw-bold py-2 shadow-sm">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Log Masuk ke Portal
                                </button>
                            </div>
                        </form>

                        <!-- Pilihan Akaun Demo untuk Pengujian Cepat -->
                        <div class="demo-box mt-4 p-3 bg-light rounded border text-start">
                            <div class="fw-bold small text-navy mb-2"><i class="bi bi-key-fill text-warning me-1"></i>Akaun Demo Pengujian:</div>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-xs btn-outline-primary demo-fill-btn" data-email="admin@example.com" data-pass="password">
                                    <i class="bi bi-shield-lock me-1"></i>Admin
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-warning text-dark demo-fill-btn" data-email="pengarah@example.com" data-pass="password">
                                    <i class="bi bi-award me-1"></i>Pengarah
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-success demo-fill-btn" data-email="staff1@example.com" data-pass="password">
                                    <i class="bi bi-person me-1"></i>Pegawai 1
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Kaki Kad -->
                    <div class="card-footer bg-light-subtle text-center py-3 border-top small text-muted">
                        Hak Cipta Terpelihara &copy; <?= date('Y') ?> PLANMalaysia Perlis.<br>
                        <span class="text-secondary" style="font-size:0.75rem;">Sistem Beroperasi di bawah IIS & Windows Server 2019</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
