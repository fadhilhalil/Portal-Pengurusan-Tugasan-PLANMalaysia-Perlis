<?php
/**
 * ========================================================================
 * MODUL LAPORAN PRESTASI & ANALITIK (reports.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$stats = getDashboardStats();
$projects = getAllProjects('', '', 100);
$tasks = getAllTasks('', '', '', null, null);

$pageTitle = 'Laporan Prestasi & Kemajuan';
include __DIR__ . '/../includes/header.php';
?>

<!-- KEPALA LAPORAN -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 no-print">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Papan Pemuka</a></li>
                <li class="breadcrumb-item active" aria-current="page">Laporan Prestasi</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-navy mb-0">Laporan Kemajuan Projek & Tugasan Jabatan</h3>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer-fill me-1"></i>Cetak Laporan
        </button>
        <a href="../api/export_report.php" class="btn btn-success">
            <i class="bi bi-file-earmark-excel-fill me-1"></i>Eksport CSV / Excel
        </a>
    </div>
</div>

<!-- KEPALA CETAKAN KHAS KERAJAAN (Hanya dipaparkan semasa cetakan) -->
<div class="print-header d-none d-print-block text-center pb-3 mb-4 border-bottom border-2 border-dark">
    <h4 class="fw-bold mb-0 text-uppercase">JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS</h4>
    <p class="mb-1 small">Tingkat 2, Bangunan Dato' Mahmud, 01000 Kangar, Perlis Indera Kayangan</p>
    <h5 class="fw-bold mt-2 text-decoration-underline">LAPORAN PRESTASI & KEMAJUAN PROJEK PERANCANGAN</h5>
    <small>Tarikh Cetakan: <?= date('d F Y, h:i A') ?> | Dijana oleh: <?= htmlspecialchars($currentUser['name']) ?> (<?= htmlspecialchars($currentUser['role']) ?>)</small>
</div>

<!-- RINGKASAN METRIK LAPORAN -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border p-3 rounded-3 bg-white">
            <small class="text-muted text-uppercase fw-semibold">Jumlah Projek Berdaftar</small>
            <h3 class="fw-bold text-navy mb-0"><?= $stats['total_projects'] ?></h3>
            <small class="text-success"><?= $stats['completed_projects'] ?> telah disiapkan</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border p-3 rounded-3 bg-white">
            <small class="text-muted text-uppercase fw-semibold">Jumlah Keseluruhan Tugasan</small>
            <h3 class="fw-bold text-primary mb-0"><?= $stats['total_tasks'] ?></h3>
            <small class="text-muted"><?= $stats['completed_tasks'] ?> tugasan berjaya</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border p-3 rounded-3 bg-white">
            <small class="text-muted text-uppercase fw-semibold">Kadar Kejayaan Jabatan</small>
            <h3 class="fw-bold text-success mb-0"><?= $stats['completion_rate'] ?>%</h3>
            <div class="progress mt-2" style="height: 5px;">
                <div class="progress-bar bg-success" style="width: <?= $stats['completion_rate'] ?>%"></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border p-3 rounded-3 bg-white">
            <small class="text-muted text-uppercase fw-semibold">Tugasan Melepasi Tarikh</small>
            <h3 class="fw-bold text-danger mb-0"><?= $stats['overdue_tasks'] ?></h3>
            <small class="text-danger">Perlu intervensi pengurusan</small>
        </div>
    </div>
</div>

<!-- JADUAL PERINCIAN LAPORAN PROJEK -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 px-4 border-bottom">
        <h5 class="fw-bold text-navy mb-0"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Status Terperinci Projek Perancangan Negeri</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th style="width: 50px;">Bil</th>
                        <th>Kod</th>
                        <th>Tajuk Projek</th>
                        <th>Kategori</th>
                        <th>Pegawai Lead</th>
                        <th>Peruntukan</th>
                        <th>Kemajuan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($projects)): ?>
                        <tr><td colspan="8" class="text-center py-3 text-muted">Tiada projek direkodkan.</td></tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($projects as $p): ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $i++ ?></td>
                                <td><span class="badge bg-navy text-white"><?= htmlspecialchars($p['code']) ?></span></td>
                                <td class="fw-semibold text-navy"><?= htmlspecialchars($p['title']) ?></td>
                                <td><?= htmlspecialchars($p['category']) ?></td>
                                <td><?= htmlspecialchars($p['lead_name'] ?? 'Tiada') ?></td>
                                <td>RM <?= number_format($p['budget'], 2) ?></td>
                                <td style="width: 140px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" style="width: <?= $p['progress'] ?>%;"></div>
                                        </div>
                                        <span class="small fw-bold"><?= $p['progress'] ?>%</span>
                                    </div>
                                </td>
                                <td><?= renderStatusBadge($p['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JADUAL PERINCIAN TUGASAN KRITIKAL & SELESAI -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 px-4 border-bottom">
        <h5 class="fw-bold text-navy mb-0"><i class="bi bi-list-stars me-2 text-success"></i>Daftar Tugasan & Sasaran Kerja</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th style="width: 50px;">Bil</th>
                        <th>Tugasan</th>
                        <th>Projek Berkaitan</th>
                        <th>Pegawai Pelaksana</th>
                        <th>Keutamaan</th>
                        <th>Tarikh Sasaran</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tasks)): ?>
                        <tr><td colspan="7" class="text-center py-3 text-muted">Tiada tugasan aktif.</td></tr>
                    <?php else: ?>
                        <?php $j = 1; foreach ($tasks as $t): ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $j++ ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($t['title']) ?></td>
                                <td><?= htmlspecialchars($t['project_title'] ?? 'Tugasan Am') ?></td>
                                <td><?= htmlspecialchars($t['assigned_name'] ?? 'Semua') ?></td>
                                <td><?= renderPriorityBadge($t['priority']) ?></td>
                                <td><?= formatTarikh($t['due_date']) ?></td>
                                <td><?= renderStatusBadge($t['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- KAKI CETAKAN RASMI -->
<div class="d-none d-print-block mt-5 pt-4 text-center">
    <div class="row">
        <div class="col-6 text-start ps-5">
            <p class="mb-5">Disediakan Oleh:</p>
            <p class="fw-bold mb-0">___________________________</p>
            <p class="small mb-0"><?= htmlspecialchars($currentUser['name']) ?></p>
            <p class="small text-muted"><?= htmlspecialchars($currentUser['role']) ?></p>
        </div>
        <div class="col-6 text-end pe-5">
            <p class="mb-5">Disahkan Oleh:</p>
            <p class="fw-bold mb-0">___________________________</p>
            <p class="small mb-0">Pengarah</p>
            <p class="small text-muted">PLANMalaysia Negeri Perlis</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
