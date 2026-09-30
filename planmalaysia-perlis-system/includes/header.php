<?php
/**
 * ========================================================================
 * TEMPLAT KEPALA LAMAN (header.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$currentUser = getCurrentUser();
$pageTitle = $pageTitle ?? 'Portal Pengurusan Tugasan';
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= htmlspecialchars($pageTitle) ?> | PLANMalaysia Perlis</title>
    
    <!-- Favicon & Ikon -->
    <link rel="icon" type="image/svg+xml" href="../assets/images/logo.svg">

    <!-- Bootstrap 5.3 CSS & Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts (Inter & Poppins untuk gaya Sistem Maklumat Kerajaan) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Gaya Kustom PLANMalaysia Perlis -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

<?php if (isLoggedIn()): ?>
<!-- BAR NAVIGASI ATAS RASMI (TOPBAR KERAJAAN) -->
<header class="navbar navbar-expand-lg navbar-dark bg-navy sticky-top shadow-sm py-2">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Butang Togol Sidebar untuk Skrin Kecil/Telefon -->
        <button class="btn btn-outline-light d-lg-none me-2" type="button" id="sidebarToggle" aria-label="Buka Menu">
            <i class="bi bi-list fs-5"></i>
        </button>

        <!-- Jenama & Logo Rasmi Jabatan -->
        <a class="navbar-brand d-flex align-items-center gap-2 m-0" href="dashboard.php">
            <div class="logo-box bg-white p-1 rounded shadow-sm d-flex align-items-center justify-content-center">
                <img src="../assets/images/logo.svg" alt="PLANMalaysia Perlis" height="34" class="logo-img">
            </div>
            <div class="d-none d-sm-block lh-1 text-start">
                <span class="d-block fw-bold fs-6 text-white tracking-wide">PLANMalaysia @ PERLIS</span>
                <small class="text-warning text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Jabatan Perancangan Bandar & Desa</small>
            </div>
        </a>

        <!-- Maklumat Kanan: Tarikh, Profil & Notifikasi -->
        <div class="d-flex align-items-center gap-3 ms-auto">
            <!-- Waktu & Tarikh Rasmi -->
            <div class="d-none d-md-flex align-items-center text-light small border-end pe-3 border-secondary">
                <i class="bi bi-calendar3 me-2 text-warning"></i>
                <span id="liveDateTime"><?= date('d F Y, l') ?></span>
            </div>

            <!-- Menu Profil Pengguna -->
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center gap-2 py-1 px-2 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="avatar-circle bg-warning text-dark fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px; font-size:0.85rem;">
                        <?= strtoupper(substr($currentUser['name'] ?? 'P', 0, 1)) ?>
                    </div>
                    <div class="text-start d-none d-md-block lh-1 text-white">
                        <div class="fw-semibold text-truncate" style="max-width: 140px;"><?= htmlspecialchars($currentUser['name'] ?? 'Pengguna') ?></div>
                        <small class="text-warning-emphasis" style="font-size:0.7rem;"><?= htmlspecialchars($currentUser['role'] ?? 'Staff') ?></small>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                    <li class="dropdown-header text-truncate">
                        <strong><?= htmlspecialchars($currentUser['name'] ?? '') ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($currentUser['email'] ?? '') ?></small><br>
                        <span class="badge bg-primary-subtle text-primary mt-1"><?= htmlspecialchars($currentUser['unit'] ?? '') ?></span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-speedometer2 me-2 text-primary"></i>Papan Pemuka</a></li>
                    <li><a class="dropdown-item" href="projects.php"><i class="bi bi-folder-fill me-2 text-warning"></i>Projek Perancangan</a></li>
                    <li><a class="dropdown-item" href="tasks.php"><i class="bi bi-check2-square me-2 text-success"></i>Senarai Tugasan</a></li>
                    <?php if (hasRole(['Admin', 'Pengarah'])): ?>
                    <li><a class="dropdown-item" href="users.php"><i class="bi bi-people-fill me-2 text-info"></i>Kakitangan & Akses</a></li>
                    <li><a class="dropdown-item" href="reports.php"><i class="bi bi-file-earmark-bar-graph me-2 text-secondary"></i>Laporan Prestasi</a></li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger fw-semibold" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Log Keluar</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>

<!-- PEMBUNGKUS UTAMA APLIKASI (SIDEBAR + KANDUNGAN) -->
<div class="d-flex" id="wrapper">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="page-content flex-grow-1 p-3 p-md-4">
        <div class="container-fluid">
            <?php displayFlashMessage(); ?>
<?php else: ?>
<!-- Paparan Luar / Login (Tanpa Sidebar) -->
<main class="login-wrapper">
<?php endif; ?>
