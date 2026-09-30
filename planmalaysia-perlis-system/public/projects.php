<?php
/**
 * ========================================================================
 * MODUL PENGURUSAN PROJEK PERANCANGAN (projects.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$users = getAllUsers();
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

// PROSES BORANG CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        setFlash('danger', 'Pengesahan CSRF gagal atau sesi telah tamat. Sila cuba lagi.');
        header('Location: projects.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // 1. TAMBAH PROJEK
    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        if (empty($title)) {
            setFlash('danger', 'Tajuk projek adalah medan wajib diisi.');
        } else {
            $data = [
                'code'         => trim($_POST['code'] ?? ''),
                'title'        => $title,
                'category'     => trim($_POST['category'] ?? 'Rancangan Tempatan (RT)'),
                'description'  => trim($_POST['description'] ?? ''),
                'status'       => trim($_POST['status'] ?? 'Dalam Perancangan'),
                'progress'     => (int)($_POST['progress'] ?? 0),
                'start_date'   => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
                'end_date'     => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                'budget'       => (float)($_POST['budget'] ?? 0),
                'lead_user_id' => !empty($_POST['lead_user_id']) ? (int)$_POST['lead_user_id'] : null
            ];
            if (createProject($data)) {
                setFlash('success', "Projek '{$title}' berjaya didaftarkan ke dalam sistem.");
            } else {
                setFlash('danger', 'Gagal mendaftarkan projek. Sila semak pangkalan data.');
            }
        }
        header('Location: projects.php');
        exit;
    }

    // 2. KEMASKINI PROJEK
    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        if ($id <= 0 || empty($title)) {
            setFlash('danger', 'ID Projek dan Tajuk Projek diperlukan untuk kemaskini.');
        } else {
            $data = [
                'title'        => $title,
                'category'     => trim($_POST['category'] ?? 'Rancangan Tempatan (RT)'),
                'description'  => trim($_POST['description'] ?? ''),
                'status'       => trim($_POST['status'] ?? 'Dalam Perancangan'),
                'progress'     => (int)($_POST['progress'] ?? 0),
                'start_date'   => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
                'end_date'     => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                'budget'       => (float)($_POST['budget'] ?? 0),
                'lead_user_id' => !empty($_POST['lead_user_id']) ? (int)$_POST['lead_user_id'] : null
            ];
            if (updateProject($id, $data)) {
                setFlash('success', "Projek '{$title}' telah berjaya dikemaskini.");
            } else {
                setFlash('danger', 'Ralat semasa mengemaskini maklumat projek.');
            }
        }
        header('Location: projects.php');
        exit;
    }

    // 3. PADAM PROJEK (ADMIN SAHAJA)
    if ($action === 'delete') {
        if (!hasRole(['Admin', 'Pengarah'])) {
            setFlash('danger', 'Hanya Pentadbir atau Pengarah dibenarkan memadam rekod projek.');
        } else {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0 && deleteProject($id)) {
                setFlash('success', 'Rekod projek berserta tugasan berkaitan telah berjaya dipadam.');
            } else {
                setFlash('danger', 'Gagal memadam projek dari pangkalan data.');
            }
        }
        header('Location: projects.php');
        exit;
    }
}

$projects = getAllProjects($search, $statusFilter);
$pageTitle = 'Pengurusan Projek Perancangan';
include __DIR__ . '/../includes/header.php';
?>

<!-- KEPALA HALAMAN & BUTANG TINDAKAN -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Papan Pemuka</a></li>
                <li class="breadcrumb-item active" aria-current="page">Projek Perancangan</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-navy mb-0">Senarai Projek Perancangan Negeri</h3>
    </div>
    <div>
        <button type="button" class="btn btn-navy shadow-sm" data-bs-toggle="modal" data-bs-target="#createProjectModal">
            <i class="bi bi-folder-plus me-2"></i>Daftar Projek Baru
        </button>
    </div>
</div>

<!-- BAR PENAPISAN & CARIAN -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="projects.php" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari kod projek, tajuk atau deskripsi..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">Semua Status Projek</option>
                    <option value="Dalam Perancangan" <?= ($statusFilter === 'Dalam Perancangan') ? 'selected' : '' ?>>Dalam Perancangan</option>
                    <option value="Sedang Berjalan" <?= ($statusFilter === 'Sedang Berjalan') ? 'selected' : '' ?>>Sedang Berjalan</option>
                    <option value="Dalam Semakan" <?= ($statusFilter === 'Dalam Semakan') ? 'selected' : '' ?>>Dalam Semakan</option>
                    <option value="Selesai" <?= ($statusFilter === 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                    <option value="Tertangguh" <?= ($statusFilter === 'Tertangguh') ? 'selected' : '' ?>>Tertangguh</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-filter me-1"></i>Tapis</button>
                <?php if (!empty($search) || !empty($statusFilter)): ?>
                    <a href="projects.php" class="btn btn-outline-secondary" title="Set Semula"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- JADUAL DATA PROJEK -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="fw-bold text-navy mb-0">Senarai Rekod Projek (<?= count($projects) ?>)</h5>
        <span class="badge bg-secondary-subtle text-secondary">MySQL InnoDB Prepared</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($projects)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-folder-x fs-1 text-secondary d-block mb-2"></i>
                <p class="mb-0">Tiada rekod projek dijumpai sepadan dengan carian anda.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-secondary">
                        <tr>
                            <th class="ps-4">Kod & Tajuk</th>
                            <th>Kategori</th>
                            <th>Pegawai Bertanggungjawab</th>
                            <th>Peruntukan (RM)</th>
                            <th>Kemajuan</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $row): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-navy text-white px-2 py-0 mb-1" style="font-size:0.7rem;"><?= htmlspecialchars($row['code']) ?></span>
                                    <div class="fw-bold text-navy"><?= htmlspecialchars($row['title']) ?></div>
                                    <small class="text-muted text-truncate d-block" style="max-width: 280px;"><?= htmlspecialchars($row['description'] ?: 'Tiada deskripsi tambahan') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($row['category']) ?></span>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark"><?= htmlspecialchars($row['lead_name'] ?? 'Belum Ditugaskan') ?></div>
                                    <small class="text-muted">Tarikh: <?= formatTarikh($row['start_date']) ?> - <?= formatTarikh($row['end_date']) ?></small>
                                </td>
                                <td class="fw-semibold text-secondary">
                                    RM <?= number_format($row['budget'], 2) ?>
                                </td>
                                <td style="min-width: 140px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 7px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $row['progress'] ?>%;"></div>
                                        </div>
                                        <span class="small fw-bold text-dark"><?= $row['progress'] ?>%</span>
                                    </div>
                                </td>
                                <td><?= renderStatusBadge($row['status']) ?></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary btn-edit-project" 
                                            data-project='<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                            title="Kemaskini Projek">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <a href="tasks.php?project_id=<?= $row['id'] ?>" class="btn btn-outline-success" title="Lihat Tugasan Projek">
                                            <i class="bi bi-list-task"></i>
                                        </a>
                                        <?php if (hasRole(['Admin', 'Pengarah'])): ?>
                                        <button type="button" class="btn btn-outline-danger btn-delete-project" 
                                            data-id="<?= $row['id'] ?>" 
                                            data-title="<?= htmlspecialchars($row['title']) ?>"
                                            title="Padam Projek">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL 1: DAFTAR PROJEK BARU -->
<div class="modal fade" id="createProjectModal" tabindex="-1" aria-labelledby="createProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="projects.php" method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="create">

                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title fw-bold" id="createProjectModalLabel"><i class="bi bi-folder-plus me-2 text-warning"></i>Daftar Projek Perancangan Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Kod Projek</label>
                            <input type="text" name="code" class="form-control" placeholder="Contoh: RT-KANGAR-2035" required value="PRJ-<?= strtoupper(substr(uniqid(), 7)) ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Tajuk Rasmi Projek <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="cth: Kajian Rancangan Tempatan Majlis Perbandaran Kangar 2035" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kategori Perancangan</label>
                            <select name="category" class="form-select">
                                <option value="Rancangan Tempatan (RT)">Rancangan Tempatan (RT)</option>
                                <option value="Rancangan Kawasan Khas (RKK)">Rancangan Kawasan Khas (RKK)</option>
                                <option value="Rancangan Struktur Negeri (RSN)">Rancangan Struktur Negeri (RSN)</option>
                                <option value="Pelan Induk & Dasar">Pelan Induk & Dasar</option>
                                <option value="Kajian Guna Tanah & GIS">Kajian Guna Tanah & GIS</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Pegawai Bertanggungjawab (Lead)</label>
                            <select name="lead_user_id" class="form-select">
                                <option value="">-- Pilih Pegawai --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['role']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Deskripsi & Skop Projek</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Maklumat ringkas objektif dan kawasan perancangan..."></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tarikh Mula</label>
                            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tarikh Sasaran Selesai</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Anggaran Peruntukan (RM)</label>
                            <input type="number" step="0.01" name="budget" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Status Awal</label>
                            <select name="status" class="form-select">
                                <option value="Dalam Perancangan">Dalam Perancangan</option>
                                <option value="Sedang Berjalan">Sedang Berjalan</option>
                                <option value="Dalam Semakan">Dalam Semakan</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Tertangguh">Tertangguh</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Peratus Kemajuan Semasa (%)</label>
                            <input type="number" min="0" max="100" name="progress" class="form-control" value="0">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-navy"><i class="bi bi-save me-1"></i>Simpan Projek</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: KEMASKINI PROJEK -->
<div class="modal fade" id="editProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="projects.php" method="POST" autocomplete="off" id="editProjectForm">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_project_id">

                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-warning"></i>Kemaskini Projek Perancangan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Kod Projek</label>
                            <input type="text" id="edit_code" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Tajuk Rasmi Projek <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="edit_title" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kategori Perancangan</label>
                            <select name="category" id="edit_category" class="form-select">
                                <option value="Rancangan Tempatan (RT)">Rancangan Tempatan (RT)</option>
                                <option value="Rancangan Kawasan Khas (RKK)">Rancangan Kawasan Khas (RKK)</option>
                                <option value="Rancangan Struktur Negeri (RSN)">Rancangan Struktur Negeri (RSN)</option>
                                <option value="Pelan Induk & Dasar">Pelan Induk & Dasar</option>
                                <option value="Kajian Guna Tanah & GIS">Kajian Guna Tanah & GIS</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Pegawai Bertanggungjawab</label>
                            <select name="lead_user_id" id="edit_lead_user_id" class="form-select">
                                <option value="">-- Pilih Pegawai --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['role']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Deskripsi & Skop</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tarikh Mula</label>
                            <input type="date" name="start_date" id="edit_start_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tarikh Sasaran Selesai</label>
                            <input type="date" name="end_date" id="edit_end_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Peruntukan (RM)</label>
                            <input type="number" step="0.01" name="budget" id="edit_budget" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Status Semasa</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="Dalam Perancangan">Dalam Perancangan</option>
                                <option value="Sedang Berjalan">Sedang Berjalan</option>
                                <option value="Dalam Semakan">Dalam Semakan</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Tertangguh">Tertangguh</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Peratus Kemajuan (%)</label>
                            <input type="number" min="0" max="100" name="progress" id="edit_progress" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: PENGESAHAN PADAM PROJEK -->
<div class="modal fade" id="deleteProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="projects.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_project_id">

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Sahkan Padam Projek</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p>Adakah anda pasti ingin memadam rekod projek ini:</p>
                    <h5 class="fw-bold text-danger" id="delete_project_title"></h5>
                    <p class="small text-muted mb-0">Tindakan ini juga akan memadam semua senarai tugasan di bawah projek ini secara kekal dari pangkalan data MySQL.</p>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash-fill me-1"></i>Ya, Padam Projek</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
