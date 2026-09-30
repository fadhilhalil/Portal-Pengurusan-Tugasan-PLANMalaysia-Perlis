<?php
/**
 * ========================================================================
 * MODUL PENGURUSAN KAKITANGAN & PERANAN PENGGUNA (users.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Hanya Admin dan Pengarah dibenarkan
requireRole(['Admin', 'Pengarah']);

$roleFilter = trim($_GET['role'] ?? '');

// PROSES TINDAKAN CRUD KAKITANGAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pengarah hanya view, hanya Admin boleh buat perubahan
    if (!hasRole('Admin')) {
        setFlash('danger', 'Hanya Pentadbir Sistem ICT (Admin) mempunyai kuasa untuk mengubah data kakitangan.');
        header('Location: users.php');
        exit;
    }

    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        setFlash('danger', 'Pengesahan token keselamatan CSRF gagal. Sila cuba lagi.');
        header('Location: users.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // 1. TAMBAH KAKITANGAN BARU
    if ($action === 'create') {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = trim($_POST['role'] ?? 'Staff');
        $unit     = trim($_POST['unit'] ?? 'Perancangan Wilayah');
        $phone    = trim($_POST['phone'] ?? '');

        if (empty($name) || empty($email) || empty($password)) {
            setFlash('danger', 'Nama, e-mel dan kata laluan adalah medan wajib diisi.');
        } elseif (!isValidEmail($email)) {
            setFlash('danger', 'Format alamat e-mel tidak sah.');
        } else {
            try {
                createUser([
                    'name'     => $name,
                    'email'    => $email,
                    'password' => $password,
                    'role'     => $role,
                    'unit'     => $unit,
                    'phone'    => $phone,
                    'status'   => 'Aktif'
                ]);
                setFlash('success', "Akaun kakitangan '{$name}' telah berjaya didaftarkan.");
            } catch (InvalidArgumentException $ex) {
                setFlash('danger', $ex->getMessage());
            } catch (Exception $e) {
                setFlash('danger', 'Ralat semasa mendaftarkan pengguna baru: ' . $e->getMessage());
            }
        }
        header('Location: users.php');
        exit;
    }

    // 2. KEMASKINI KAKITANGAN
    if ($action === 'update') {
        $id    = (int)($_POST['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role  = trim($_POST['role'] ?? 'Staff');
        $unit  = trim($_POST['unit'] ?? 'Perancangan Wilayah');
        $phone = trim($_POST['phone'] ?? '');
        $status= trim($_POST['status'] ?? 'Aktif');
        $newPass = $_POST['password'] ?? '';

        if ($id <= 0 || empty($name) || empty($email)) {
            setFlash('danger', 'Maklumat pengguna tidak lengkap.');
        } else {
            $updData = [
                'name'   => $name,
                'email'  => $email,
                'role'   => $role,
                'unit'   => $unit,
                'phone'  => $phone,
                'status' => $status
            ];
            if (!empty($newPass)) {
                $updData['password'] = $newPass;
            }

            if (updateUser($id, $updData)) {
                setFlash('success', "Maklumat pengguna '{$name}' telah dikemaskini.");
            } else {
                setFlash('danger', 'Ralat semasa mengemaskini rekod pengguna.');
            }
        }
        header('Location: users.php');
        exit;
    }

    // 3. PADAM KAKITANGAN
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === (int)$currentUser['id']) {
            setFlash('danger', 'Anda tidak dibenarkan memadam akaun yang sedang anda gunakan sendiri.');
        } elseif ($id > 0 && deleteUser($id)) {
            setFlash('success', 'Akaun pengguna telah dipadam.');
        } else {
            setFlash('danger', 'Gagal memadam akaun pengguna.');
        }
        header('Location: users.php');
        exit;
    }
}

$userList = getAllUsers($roleFilter);
$pageTitle = 'Pengurusan Kakitangan & Akses';
include __DIR__ . '/../includes/header.php';
?>

<!-- KEPALA HALAMAN -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Papan Pemuka</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kakitangan & Akses</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-navy mb-0">Direktori Kakitangan & Kawalan Akses (RBAC)</h3>
    </div>
    <?php if (hasRole('Admin')): ?>
    <div>
        <button type="button" class="btn btn-navy shadow-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="bi bi-person-plus-fill me-2"></i>Daftar Kakitangan Baru
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- JADUAL PENGGUNA -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="fw-bold text-navy mb-0">Senarai Kakitangan PLANMalaysia Perlis (<?= count($userList) ?>)</h5>
        <div class="d-flex gap-2">
            <a href="users.php" class="badge <?= empty($roleFilter) ? 'bg-primary' : 'bg-light text-dark border' ?> text-decoration-none p-2">Semua</a>
            <a href="users.php?role=Admin" class="badge <?= ($roleFilter === 'Admin') ? 'bg-primary' : 'bg-light text-dark border' ?> text-decoration-none p-2">Admin</a>
            <a href="users.php?role=Staff" class="badge <?= ($roleFilter === 'Staff') ? 'bg-primary' : 'bg-light text-dark border' ?> text-decoration-none p-2">Staff</a>
            <a href="users.php?role=Pengarah" class="badge <?= ($roleFilter === 'Pengarah') ? 'bg-primary' : 'bg-light text-dark border' ?> text-decoration-none p-2">Pengarah</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-secondary">
                    <tr>
                        <th class="ps-4">Nama Kakitangan</th>
                        <th>Peranan (Role)</th>
                        <th>Unit / Bahagian</th>
                        <th>Telefon</th>
                        <th>Log Masuk Terakhir</th>
                        <th>Status</th>
                        <?php if (hasRole('Admin')): ?>
                        <th class="text-end pe-4">Tindakan</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($userList as $u): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-box bg-navy text-white fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-navy"><?= htmlspecialchars($u['name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($u['role'] === 'Admin'): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger fw-semibold">Admin ICT</span>
                                <?php elseif ($u['role'] === 'Pengarah'): ?>
                                    <span class="badge bg-warning-subtle text-dark border border-warning fw-semibold">Pengarah</span>
                                <?php else: ?>
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info fw-semibold">Pegawai Perancang</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="small fw-semibold text-secondary"><?= htmlspecialchars($u['unit'] ?: 'Perancangan Wilayah') ?></span></td>
                            <td><span class="small"><?= htmlspecialchars($u['phone'] ?: '-') ?></span></td>
                            <td><small class="text-muted"><?= formatTarikh($u['last_login'], true) ?></small></td>
                            <td>
                                <?php if (($u['status'] ?? 'Aktif') === 'Aktif'): ?>
                                    <span class="badge bg-success-subtle text-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger">Disekat</span>
                                <?php endif; ?>
                            </td>
                            <?php if (hasRole('Admin')): ?>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary btn-edit-user"
                                        data-user='<?= json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                        title="Kemaskini Kakitangan">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <?php if ($u['id'] !== $currentUser['id']): ?>
                                    <button type="button" class="btn btn-outline-danger btn-delete-user"
                                        data-id="<?= $u['id'] ?>"
                                        data-name="<?= htmlspecialchars($u['name']) ?>"
                                        title="Padam Kakitangan">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL 1: DAFTAR KAKITANGAN BARU -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="users.php" method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="create">

                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2 text-warning"></i>Daftar Kakitangan Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Penuh <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="cth: Ts. Ahmad Zulkifli bin Ismail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">E-mel Rasmi Jabatan <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="nama@perlis.gov.my" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kata Laluan Awal <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required placeholder="Minimum 8 aksara">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Peranan (Role)</label>
                            <select name="role" class="form-select">
                                <option value="Staff">Staff (Pegawai)</option>
                                <option value="Admin">Admin (Pentadbir ICT)</option>
                                <option value="Pengarah">Pengarah</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">No. Telefon</label>
                            <input type="text" name="phone" class="form-control" placeholder="01X-XXXXXXX">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Bahagian / Unit</label>
                        <select name="unit" class="form-select">
                            <option value="Perancangan Wilayah">Bahagian Rancangan Pembangunan</option>
                            <option value="Kawalan Pemajuan">Bahagian Kawalan Pemajuan</option>
                            <option value="Maklumat Geografi & GIS">Pusat Maklumat Geografi (GIS)</option>
                            <option value="Pentadbiran & Kewangan">Bahagian Korporat & Kewangan</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-navy"><i class="bi bi-save me-1"></i>Daftar Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: KEMASKINI KAKITANGAN -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="users.php" method="POST" autocomplete="off" id="editUserForm">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_user_id">

                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-warning"></i>Kemaskini Rekod Kakitangan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Penuh <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_user_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">E-mel Rasmi <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="edit_user_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tukar Kata Laluan Baru <small class="text-muted">(Kosongkan jika tidak mahu ubah)</small></label>
                        <input type="password" name="password" class="form-control" placeholder="Katalaluan baru (pilihan)">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Peranan (Role)</label>
                            <select name="role" id="edit_user_role" class="form-select">
                                <option value="Staff">Staff (Pegawai)</option>
                                <option value="Admin">Admin (Pentadbir ICT)</option>
                                <option value="Pengarah">Pengarah</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Status Akaun</label>
                            <select name="status" id="edit_user_status" class="form-select">
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Bahagian / Unit</label>
                            <input type="text" name="unit" id="edit_user_unit" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">No Telefon</label>
                            <input type="text" name="phone" id="edit_user_phone" class="form-control">
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

<!-- MODAL 3: PADAM KAKITANGAN -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="users.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_user_id">

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-trash-fill me-2"></i>Padam Akaun Kakitangan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p>Adakah anda pasti ingin memadam akaun kakitangan ini:</p>
                    <h6 class="fw-bold text-danger" id="delete_user_name_text"></h6>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Sahkan Padam</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
