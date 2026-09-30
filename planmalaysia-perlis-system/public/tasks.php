<?php
/**
 * ========================================================================
 * MODUL PENGURUSAN TUGASAN KAKITANGAN (tasks.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$users = getAllUsers();
$projects = getAllProjects('', '', 100);

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$priorityFilter = trim($_GET['priority'] ?? '');
$projectFilter = !empty($_GET['project_id']) ? (int)$_GET['project_id'] : null;

// PROSES TINDAKAN BORANG CRUD TUGASAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        setFlash('danger', 'Pengesahan token keselamatan CSRF gagal. Sila cuba lagi.');
        header('Location: tasks.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // 1. TAMBAH TUGASAN BARU
    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        if (empty($title)) {
            setFlash('danger', 'Tajuk tugasan adalah medan mandatori.');
        } else {
            $data = [
                'project_id'       => !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null,
                'title'            => $title,
                'description'      => trim($_POST['description'] ?? ''),
                'priority'         => trim($_POST['priority'] ?? 'Sederhana'),
                'status'           => trim($_POST['status'] ?? 'Dalam Tindakan'),
                'due_date'         => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
                'assigned_user_id' => !empty($_POST['assigned_user_id']) ? (int)$_POST['assigned_user_id'] : null
            ];
            if (createTask($data, (int)$currentUser['id'])) {
                setFlash('success', "Tugasan '{$title}' telah berjaya didaftarkan.");
            } else {
                setFlash('danger', 'Gagal mendaftar tugasan baru.');
            }
        }
        header('Location: tasks.php');
        exit;
    }

    // 2. KEMASKINI TUGASAN
    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        if ($id <= 0 || empty($title)) {
            setFlash('danger', 'Maklumat tugasan tidak lengkap.');
        } else {
            $data = [
                'project_id'       => !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null,
                'title'            => $title,
                'description'      => trim($_POST['description'] ?? ''),
                'priority'         => trim($_POST['priority'] ?? 'Sederhana'),
                'status'           => trim($_POST['status'] ?? 'Dalam Tindakan'),
                'due_date'         => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
                'assigned_user_id' => !empty($_POST['assigned_user_id']) ? (int)$_POST['assigned_user_id'] : null
            ];
            if (updateTask($id, $data)) {
                setFlash('success', "Tugasan '{$title}' telah berjaya dikemaskini.");
            } else {
                setFlash('danger', 'Gagal mengemaskini tugasan.');
            }
        }
        header('Location: tasks.php');
        exit;
    }

    // 3. TANDAKAN SELESAI PANTAS
    if ($action === 'mark_complete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $task = getTaskById($id);
            if ($task) {
                $task['status'] = 'Selesai';
                updateTask($id, $task);
                setFlash('success', "Tugasan '{$task['title']}' telah ditandakan sebagai SELESAI.");
            }
        }
        header('Location: tasks.php');
        exit;
    }

    // 4. PADAM TUGASAN
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0 && deleteTask($id)) {
            setFlash('success', 'Tugasan telah berjaya dipadam.');
        } else {
            setFlash('danger', 'Gagal memadam tugasan.');
        }
        header('Location: tasks.php');
        exit;
    }
}

$tasks = getAllTasks($search, $statusFilter, $priorityFilter, $projectFilter);
$pageTitle = 'Senarai Tugasan Kakitangan';
include __DIR__ . '/../includes/header.php';
?>

<!-- KEPALA HALAMAN -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Papan Pemuka</a></li>
                <li class="breadcrumb-item active" aria-current="page">Senarai Tugasan</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-navy mb-0">Pengurusan Tugasan & Sasaran Kerja</h3>
    </div>
    <div>
        <button type="button" class="btn btn-navy shadow-sm" data-bs-toggle="modal" data-bs-target="#createTaskModal">
            <i class="bi bi-plus-circle me-2"></i>Tambah Tugasan Baru
        </button>
    </div>
</div>

<!-- BAR PENAPISAN TUGASAN -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="tasks.php" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama tugasan..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="project_id" class="form-select">
                    <option value="">Semua Projek Berkaitan</option>
                    <?php foreach ($projects as $pr): ?>
                        <option value="<?= $pr['id'] ?>" <?= ($projectFilter === (int)$pr['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pr['code']) ?> - <?= htmlspecialchars($pr['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="Dalam Tindakan" <?= ($statusFilter === 'Dalam Tindakan') ? 'selected' : '' ?>>Dalam Tindakan</option>
                    <option value="Pending" <?= ($statusFilter === 'Pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="Dalam Semakan" <?= ($statusFilter === 'Dalam Semakan') ? 'selected' : '' ?>>Dalam Semakan</option>
                    <option value="Selesai" <?= ($statusFilter === 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select">
                    <option value="">Semua Keutamaan</option>
                    <option value="Tinggi" <?= ($priorityFilter === 'Tinggi') ? 'selected' : '' ?>>Tinggi</option>
                    <option value="Sederhana" <?= ($priorityFilter === 'Sederhana') ? 'selected' : '' ?>>Sederhana</option>
                    <option value="Rendah" <?= ($priorityFilter === 'Rendah') ? 'selected' : '' ?>>Rendah</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-filter me-1"></i>Tapis</button>
                <?php if (!empty($search) || !empty($statusFilter) || !empty($priorityFilter) || !empty($projectFilter)): ?>
                    <a href="tasks.php" class="btn btn-outline-secondary" title="Set Semula"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- JADUAL SENARAI TUGASAN -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="fw-bold text-navy mb-0">Senarai Tugasan Aktif & Arkib (<?= count($tasks) ?>)</h5>
        <span class="badge bg-success-subtle text-success">Kemas Kini Automatik</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($tasks)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-clipboard-check fs-1 text-secondary d-block mb-2"></i>
                <p class="mb-0">Tiada tugasan dijumpai berdasarkan tapisan carian.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-secondary">
                        <tr>
                            <th class="ps-4">Tugasan</th>
                            <th>Projek Berkaitan</th>
                            <th>Ditugaskan Kepada</th>
                            <th>Keutamaan</th>
                            <th>Tarikh Sasaran</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $row): 
                            $isOverdue = (!empty($row['due_date']) && strtotime($row['due_date']) < strtotime(date('Y-m-d')) && $row['status'] !== 'Selesai');
                        ?>
                            <tr class="<?= $isOverdue ? 'table-danger-subtle' : '' ?>">
                                <td class="ps-4">
                                    <div class="fw-bold text-navy"><?= htmlspecialchars($row['title']) ?></div>
                                    <small class="text-muted text-truncate d-block" style="max-width: 250px;">
                                        <?= htmlspecialchars($row['description'] ?: 'Tiada catatan tambahan') ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if (!empty($row['project_title'])): ?>
                                        <span class="badge bg-light text-navy border small text-truncate" style="max-width: 180px;">
                                            <?= htmlspecialchars($row['project_title']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">Tugasan Am Jabatan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:26px; height:26px; font-size:0.75rem;">
                                            <?= strtoupper(substr($row['assigned_name'] ?? 'U', 0, 1)) ?>
                                        </div>
                                        <span class="small fw-semibold"><?= htmlspecialchars($row['assigned_name'] ?? 'Belum Ditugaskan') ?></span>
                                    </div>
                                </td>
                                <td><?= renderPriorityBadge($row['priority']) ?></td>
                                <td>
                                    <span class="small <?= $isOverdue ? 'text-danger fw-bold' : 'text-dark' ?>">
                                        <?= formatTarikh($row['due_date']) ?>
                                        <?php if ($isOverdue): ?>
                                            <i class="bi bi-exclamation-circle-fill text-danger ms-1" title="Melepasi Tarikh Sasaran!"></i>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td><?= renderStatusBadge($row['status']) ?></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($row['status'] !== 'Selesai'): ?>
                                            <form action="tasks.php" method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                                <input type="hidden" name="action" value="mark_complete">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn btn-outline-success" title="Tandakan Selesai">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-outline-primary btn-edit-task"
                                            data-task='<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                            title="Kemaskini Tugasan">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-delete-task"
                                            data-id="<?= $row['id'] ?>"
                                            data-title="<?= htmlspecialchars($row['title']) ?>"
                                            title="Padam Tugasan">
                                            <i class="bi bi-trash"></i>
                                        </button>
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

<!-- MODAL 1: TAMBAH TUGASAN -->
<div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="tasks.php" method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="create">

                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title fw-bold" id="createTaskModalLabel"><i class="bi bi-plus-circle me-2 text-warning"></i>Tambah Tugasan Kerja Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold">Tajuk Tugasan <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required placeholder="cth: Mengadakan Sesi Libat Urus Awam RT Kangar Fasa 1">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Di Bawah Projek Perancangan</label>
                            <select name="project_id" class="form-select">
                                <option value="">-- Tiada (Tugasan Am / Rutin) --</option>
                                <?php foreach ($projects as $pr): ?>
                                    <option value="<?= $pr['id'] ?>" <?= ($projectFilter === (int)$pr['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($pr['code']) ?> - <?= htmlspecialchars($pr['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Pegawai Bertanggungjawab</label>
                            <select name="assigned_user_id" class="form-select">
                                <option value="">-- Pilih Pegawai Pelaksana --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['unit'] ?? $u['role']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Catatan / Arahan Kerja</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Sertakan maklumat deliverables, dokumen rujukan atau panduan pelaksanaan..."></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tahap Keutamaan</label>
                            <select name="priority" class="form-select">
                                <option value="Sederhana">Sederhana</option>
                                <option value="Tinggi">Tinggi</option>
                                <option value="Rendah">Rendah</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Status Awal</label>
                            <select name="status" class="form-select">
                                <option value="Dalam Tindakan">Dalam Tindakan</option>
                                <option value="Pending">Pending</option>
                                <option value="Dalam Semakan">Dalam Semakan</option>
                                <option value="Selesai">Selesai</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tarikh Akhir Sasaran</label>
                            <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-navy"><i class="bi bi-save me-1"></i>Daftar Tugasan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: KEMASKINI TUGASAN -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="tasks.php" method="POST" autocomplete="off" id="editTaskForm">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_task_id">

                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-warning"></i>Kemaskini Tugasan Kerja</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold">Tajuk Tugasan <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="edit_task_title" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Projek Berkaitan</label>
                            <select name="project_id" id="edit_task_project_id" class="form-select">
                                <option value="">-- Tiada (Tugasan Am) --</option>
                                <?php foreach ($projects as $pr): ?>
                                    <option value="<?= $pr['id'] ?>"><?= htmlspecialchars($pr['code']) ?> - <?= htmlspecialchars($pr['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Pegawai Bertanggungjawab</label>
                            <select name="assigned_user_id" id="edit_task_assigned_user_id" class="form-select">
                                <option value="">-- Pilih Pegawai --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['unit'] ?? $u['role']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Deskripsi Arahan</label>
                            <textarea name="description" id="edit_task_description" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tahap Keutamaan</label>
                            <select name="priority" id="edit_task_priority" class="form-select">
                                <option value="Sederhana">Sederhana</option>
                                <option value="Tinggi">Tinggi</option>
                                <option value="Rendah">Rendah</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Status Tugasan</label>
                            <select name="status" id="edit_task_status" class="form-select">
                                <option value="Dalam Tindakan">Dalam Tindakan</option>
                                <option value="Pending">Pending</option>
                                <option value="Dalam Semakan">Dalam Semakan</option>
                                <option value="Selesai">Selesai</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tarikh Akhir Sasaran</label>
                            <input type="date" name="due_date" id="edit_task_due_date" class="form-control">
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

<!-- MODAL 3: PADAM TUGASAN -->
<div class="modal fade" id="deleteTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="tasks.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_task_id">

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-trash-fill me-2"></i>Padam Tugasan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p>Adakah anda pasti ingin memadam tugasan ini secara kekal:</p>
                    <h6 class="fw-bold text-danger" id="delete_task_title"></h6>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Ya, Padam</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
