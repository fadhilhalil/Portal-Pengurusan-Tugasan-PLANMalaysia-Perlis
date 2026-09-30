<?php
/**
 * ========================================================================
 * TEMPLAT MENU SISI (sidebar.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar bg-white border-end shadow-sm" id="sidebarNav">
    <div class="sidebar-header p-3 border-bottom d-flex align-items-center justify-content-between">
        <span class="text-uppercase fw-bold text-muted small tracking-wide">Menu Utama Sistem</span>
        <button class="btn-close d-lg-none" type="button" id="sidebarCloseBtn" aria-label="Tutup"></button>
    </div>

    <nav class="sidebar-nav p-2">
        <ul class="nav flex-column gap-1">
            
            <!-- 1. Papan Pemuka -->
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage === 'dashboard.php') ? 'active' : '' ?>" href="dashboard.php">
                    <i class="bi bi-grid-1x2-fill me-2 text-primary"></i>
                    <span>Papan Pemuka</span>
                </a>
            </li>

            <!-- 2. Projek Perancangan -->
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage === 'projects.php') ? 'active' : '' ?>" href="projects.php">
                    <i class="bi bi-folder-fill me-2 text-warning"></i>
                    <span>Projek Perancangan</span>
                </a>
            </li>

            <!-- 3. Senarai Tugasan -->
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage === 'tasks.php') ? 'active' : '' ?>" href="tasks.php">
                    <i class="bi bi-check2-square me-2 text-success"></i>
                    <span>Senarai Tugasan</span>
                </a>
            </li>

            <!-- Bahagian Pengurusan Pentadbiran -->
            <?php if (hasRole(['Admin', 'Pengarah'])): ?>
            <li class="nav-heading mt-3 px-3 py-1 text-uppercase text-muted" style="font-size:0.7rem; font-weight:700; letter-spacing:0.8px;">
                Pengurusan & Analitik
            </li>

            <!-- 4. Laporan & Statistik -->
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage === 'reports.php') ? 'active' : '' ?>" href="reports.php">
                    <i class="bi bi-file-earmark-bar-graph-fill me-2 text-info"></i>
                    <span>Laporan & Analitik</span>
                </a>
            </li>

            <!-- 5. Kakitangan & Akses (Admin Sahaja) -->
            <?php if (hasRole('Admin')): ?>
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage === 'users.php') ? 'active' : '' ?>" href="users.php">
                    <i class="bi bi-people-fill me-2 text-secondary"></i>
                    <span>Kakitangan & Akses</span>
                </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <li class="nav-heading mt-3 px-3 py-1 text-uppercase text-muted" style="font-size:0.7rem; font-weight:700; letter-spacing:0.8px;">
                Sokongan Sistem
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#aboutModal">
                    <i class="bi bi-info-circle-fill me-2 text-secondary"></i>
                    <span>Mengenai Sistem</span>
                </a>
            </li>

            <li class="nav-item mt-4 border-top pt-2">
                <a class="nav-link text-danger fw-semibold" href="logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    <span>Log Keluar</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Info Bahagian Bawah Sidebar -->
    <div class="sidebar-footer p-3 mt-auto border-top bg-light-subtle small text-muted">
        <div class="d-flex align-items-center gap-2">
            <span class="status-indicator bg-success rounded-circle" style="width:8px; height:8px; display:inline-block;"></span>
            <span>Pelayan IIS: <strong>Aktif</strong></span>
        </div>
        <div class="text-truncate mt-1" style="font-size:0.7rem;">Versi Sistem: <?= APP_VERSION ?></div>
    </div>
</aside>
