<?php
/**
 * ========================================================================
 * PAPAN PEMUKA UTAMA (dashboard.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$stats = getDashboardStats();
$recentProjects = getAllProjects('', '', 5);
$recentTasks = getAllTasks('', '', '', null, null);
// Ambil 6 tugasan terkini
$recentTasks = array_slice($recentTasks, 0, 6);

$pageTitle = 'Papan Pemuka Eksekutif';
include __DIR__ . '/../includes/header.php';
?>

<!-- BANNER ALU-ALUAN RASMI -->
<div class="welcome-banner bg-white rounded-3 shadow-sm p-4 mb-4 border-start border-4 border-primary">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-navy text-white px-2 py-1 small"><?= htmlspecialchars($currentUser['role']) ?></span>
                <span class="text-muted small">• <?= htmlspecialchars($currentUser['unit']) ?></span>
            </div>
            <h3 class="fw-bold text-navy mb-1">Selamat Datang, <?= htmlspecialchars($currentUser['name']) ?></h3>
            <p class="text-secondary small mb-0">
                Sistem Pengurusan Tugasan & Projek Rasmi Jabatan Perancangan Bandar dan Desa Negeri Perlis. Pantau kemajuan perancangan negeri secara masa nyata.
            </p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                <a href="tasks.php" class="btn btn-sm btn-navy">
                    <i class="bi bi-plus-circle me-1"></i>Tugasan Baru
                </a>
                <a href="projects.php" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-folder-plus me-1"></i>Projek Baru
                </a>
                <a href="reports.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i>Laporan
                </a>
            </div>
        </div>
    </div>
</div>

<!-- KAD-KAD KPI & STATISTIK UTAMA -->
<div class="row g-3 mb-4">
    <!-- 1. Jumlah Projek -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm rounded-3 h-100 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Jumlah Projek</span>
                    <h2 class="fw-bold text-navy my-1"><?= $stats['total_projects'] ?></h2>
                    <span class="badge bg-primary-subtle text-primary small">
                        <i class="bi bi-arrow-up-right me-1"></i><?= $stats['active_projects'] ?> Sedang Aktif
                    </span>
                </div>
                <div class="stat-icon bg-primary-subtle text-primary rounded-3 p-3">
                    <i class="bi bi-folder2-open fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Tugasan Aktif / Tindakan -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm rounded-3 h-100 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Tugasan Tindakan</span>
                    <h2 class="fw-bold text-warning my-1"><?= $stats['pending_tasks'] ?></h2>
                    <span class="badge bg-warning-subtle text-warning-emphasis small">
                        Menunggu Tindakan Staf
                    </span>
                </div>
                <div class="stat-icon bg-warning-subtle text-warning rounded-3 p-3">
                    <i class="bi bi-hourglass-split fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Tugasan Selesai -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm rounded-3 h-100 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Tugasan Selesai</span>
                    <h2 class="fw-bold text-success my-1"><?= $stats['completed_tasks'] ?></h2>
                    <span class="badge bg-success-subtle text-success small">
                        Kadar Capaian: <?= $stats['completion_rate'] ?>%
                    </span>
                </div>
                <div class="stat-icon bg-success-subtle text-success rounded-3 p-3">
                    <i class="bi bi-check2-all fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Tugasan Melepasi Tarikh (Overdue) -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm rounded-3 h-100 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Perlu Perhatian</span>
                    <h2 class="fw-bold text-danger my-1"><?= $stats['overdue_tasks'] ?></h2>
                    <span class="badge bg-danger-subtle text-danger small">
                        Lewat Tarikh Sasaran
                    </span>
                </div>
                <div class="stat-icon bg-danger-subtle text-danger rounded-3 p-3">
                    <i class="bi bi-exclamation-octagon fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KANDUNGAN UTAMA: PROJEK TERKINI & TUGASAN TINDAKAN -->
<div class="row g-4">
    <!-- Bahagian Kiri: Projek Perancangan Aktif -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-kanban-fill text-primary fs-5"></i>
                    <h5 class="fw-bold text-navy mb-0">Status Projek Perancangan Terkini</h5>
                </div>
                <a href="projects.php" class="btn btn-sm btn-link text-decoration-none fw-semibold">Lihat Semua &rarr;</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentProjects)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                        Tiada projek didaftarkan lagi.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase text-secondary">
                                <tr>
                                    <th class="ps-4">Kod & Tajuk Projek</th>
                                    <th>Kategori</th>
                                    <th>Kemajuan</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentProjects as $p): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-navy text-truncate" style="max-width: 220px;"><?= htmlspecialchars($p['title']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($p['code']) ?> • Pegawai: <?= htmlspecialchars($p['lead_name'] ?? 'Belum Ditugaskan') ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border small"><?= htmlspecialchars($p['category']) ?></span>
                                        </td>
                                        <td style="min-width: 120px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $p['progress'] ?>%;" aria-valuenow="<?= $p['progress'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="small fw-semibold text-secondary"><?= $p['progress'] ?>%</span>
                                            </div>
                                        </td>
                                        <td><?= renderStatusBadge($p['status']) ?></td>
                                        <td class="text-end pe-4">
                                            <a href="projects.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Perincian Projek">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bahagian Kanan: Senarai Tugasan Terkini -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-list-check text-success fs-5"></i>
                    <h5 class="fw-bold text-navy mb-0">Tugasan Perlu Tindakan</h5>
                </div>
                <a href="tasks.php" class="btn btn-sm btn-link text-decoration-none fw-semibold">Lihat Semua &rarr;</a>
            </div>
            <div class="card-body p-3">
                <?php if (empty($recentTasks)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-check2-circle fs-1 d-block mb-2 text-success"></i>
                        Semua tugasan selesai atau tiada tugasan aktif.
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentTasks as $t): ?>
                            <div class="list-group-item px-2 py-3 border-bottom d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <?= renderPriorityBadge($t['priority']) ?>
                                        <small class="text-muted"><i class="bi bi-calendar-event me-1"></i><?= formatTarikh($t['due_date']) ?></small>
                                    </div>
                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 250px;">
                                        <?= htmlspecialchars($t['title']) ?>
                                    </div>
                                    <small class="text-muted d-block text-truncate" style="max-width: 250px;">
                                        <?= htmlspecialchars($t['project_title'] ?? 'Tugasan Umum') ?> • Ditugaskan: <?= htmlspecialchars($t['assigned_name'] ?? 'Semua Staf') ?>
                                    </small>
                                </div>
                                <div>
                                    <?= renderStatusBadge($t['status']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
