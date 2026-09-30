<?php
/**
 * ========================================================================
 * MODUL FUNGSI UTILITI, CRUD & DATA (functions.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */

require_once __DIR__ . '/../config/config.php';

// ==========================================
// 1. KESELAMATAN & SANITASI INPUT
// ==========================================

/**
 * Sanitasi string input untuk menghalang XSS & suntikan
 */
function sanitizeInput(?string $data): string {
    if ($data === null) return '';
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Pengesahan Format E-mel
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Format Tarikh Tempatan Malaysia (cth: 30 Sep 2026)
 */
function formatTarikh(?string $dateStr, bool $includeTime = false): string {
    if (empty($dateStr)) return '-';
    $time = strtotime($dateStr);
    if (!$time) return $dateStr;
    $format = $includeTime ? 'd M Y, h:i A' : 'd M Y';
    return date($format, $time);
}

// ==========================================
// 2. SISTEM FLASH MESSAGES (NOTIFIKASI SESI)
// ==========================================

function setFlash(string $type, string $message): void {
    $_SESSION['flash_notification'] = [
        'type'    => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash_notification'])) {
        $flash = $_SESSION['flash_notification'];
        unset($_SESSION['flash_notification']);
        return $flash;
    }
    return null;
}

function displayFlashMessage(): void {
    $flash = getFlash();
    if ($flash) {
        $type = htmlspecialchars($flash['type']);
        $icon = match($type) {
            'success' => 'bi-check-circle-fill',
            'danger'  => 'bi-exclamation-triangle-fill',
            'warning' => 'bi-exclamation-circle-fill',
            default   => 'bi-info-circle-fill'
        };
        echo "<div class='alert alert-{$type} alert-dismissible fade show d-flex align-items-center' role='alert'>
            <i class='bi {$icon} me-2 fs-5'></i>
            <div>{$flash['message']}</div>
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    }
}

// ==========================================
// 3. BADGE STATUS & KEUTAMAAN
// ==========================================

function renderStatusBadge(string $status): string {
    $class = match(trim($status)) {
        'Selesai', 'Diluluskan'         => 'bg-success text-white',
        'Sedang Berjalan', 'Dalam Tindakan' => 'bg-primary text-white',
        'Dalam Semakan', 'Menunggu Maklum Balas' => 'bg-warning text-dark',
        'Dalam Perancangan', 'Belum Mula', 'Pending' => 'bg-secondary text-white',
        'Tertangguh', 'Ditolak'         => 'bg-danger text-white',
        default                         => 'bg-light text-dark border'
    };
    return "<span class='badge {$class} px-2 py-1 fw-semibold'>" . htmlspecialchars($status) . "</span>";
}

function renderPriorityBadge(string $priority): string {
    $class = match(trim($priority)) {
        'Tinggi', 'Kritikal' => 'bg-danger-subtle text-danger border border-danger',
        'Sederhana'          => 'bg-warning-subtle text-warning-emphasis border border-warning',
        'Rendah'             => 'bg-success-subtle text-success border border-success',
        default              => 'bg-secondary-subtle text-secondary'
    };
    return "<span class='badge {$class} px-2 py-1'>" . htmlspecialchars($priority) . "</span>";
}

// ==========================================
// 4. CRUD OPERASI PROJEK (PLANNING PROJECTS)
// ==========================================

function getAllProjects(string $search = '', string $status = '', int $limit = 100): array {
    $pdo = getDatabaseConnection();
    $sql = "SELECT p.*, u.name as lead_name 
            FROM projects p 
            LEFT JOIN users u ON p.lead_user_id = u.id 
            WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (p.title LIKE :search OR p.code LIKE :search OR p.description LIKE :search)";
        $params[':search'] = "%{$search}%";
    }

    if (!empty($status)) {
        $sql .= " AND p.status = :status";
        $params[':status'] = $status;
    }

    $sql .= " ORDER BY p.created_at DESC LIMIT " . (int)$limit;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getProjectById(int $id): ?array {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("SELECT p.*, u.name as lead_name, u.email as lead_email 
                           FROM projects p 
                           LEFT JOIN users u ON p.lead_user_id = u.id 
                           WHERE p.id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $res = $stmt->fetch();
    return $res ?: null;
}

function createProject(array $data): bool {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("INSERT INTO projects 
        (code, title, category, description, status, progress, start_date, end_date, budget, lead_user_id, created_at) 
        VALUES (:code, :title, :category, :description, :status, :progress, :start_date, :end_date, :budget, :lead_user_id, NOW())");
    
    return $stmt->execute([
        ':code'         => $data['code'] ?? ('PRJ-' . strtoupper(substr(uniqid(), 7))),
        ':title'        => $data['title'],
        ':category'     => $data['category'] ?? 'Rancangan Tempatan (RT)',
        ':description'  => $data['description'] ?? '',
        ':status'       => $data['status'] ?? 'Dalam Perancangan',
        ':progress'     => (int)($data['progress'] ?? 0),
        ':start_date'   => !empty($data['start_date']) ? $data['start_date'] : null,
        ':end_date'     => !empty($data['end_date']) ? $data['end_date'] : null,
        ':budget'       => (float)($data['budget'] ?? 0),
        ':lead_user_id' => !empty($data['lead_user_id']) ? (int)$data['lead_user_id'] : null
    ]);
}

function updateProject(int $id, array $data): bool {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("UPDATE projects SET 
        title = :title, 
        category = :category, 
        description = :description, 
        status = :status, 
        progress = :progress, 
        start_date = :start_date, 
        end_date = :end_date, 
        budget = :budget, 
        lead_user_id = :lead_user_id, 
        updated_at = NOW() 
        WHERE id = :id");

    return $stmt->execute([
        ':id'           => $id,
        ':title'        => $data['title'],
        ':category'     => $data['category'],
        ':description'  => $data['description'] ?? '',
        ':status'       => $data['status'],
        ':progress'     => (int)($data['progress'] ?? 0),
        ':start_date'   => !empty($data['start_date']) ? $data['start_date'] : null,
        ':end_date'     => !empty($data['end_date']) ? $data['end_date'] : null,
        ':budget'       => (float)($data['budget'] ?? 0),
        ':lead_user_id' => !empty($data['lead_user_id']) ? (int)$data['lead_user_id'] : null
    ]);
}

function deleteProject(int $id): bool {
    $pdo = getDatabaseConnection();
    // Padam tugasan berkaitan terlebih dahulu
    $pdo->prepare("DELETE FROM tasks WHERE project_id = :id")->execute([':id' => $id]);
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

// ==========================================
// 5. CRUD OPERASI TUGASAN (TASKS)
// ==========================================

function getAllTasks(string $search = '', string $status = '', string $priority = '', ?int $projectId = null, ?int $assignedTo = null): array {
    $pdo = getDatabaseConnection();
    $sql = "SELECT t.*, p.title as project_title, p.code as project_code, u.name as assigned_name, c.name as creator_name 
            FROM tasks t 
            LEFT JOIN projects p ON t.project_id = p.id 
            LEFT JOIN users u ON t.assigned_user_id = u.id 
            LEFT JOIN users c ON t.created_by_user_id = c.id 
            WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (t.title LIKE :search OR t.description LIKE :search)";
        $params[':search'] = "%{$search}%";
    }

    if (!empty($status)) {
        $sql .= " AND t.status = :status";
        $params[':status'] = $status;
    }

    if (!empty($priority)) {
        $sql .= " AND t.priority = :priority";
        $params[':priority'] = $priority;
    }

    if ($projectId !== null) {
        $sql .= " AND t.project_id = :project_id";
        $params[':project_id'] = $projectId;
    }

    if ($assignedTo !== null) {
        $sql .= " AND t.assigned_user_id = :assigned_to";
        $params[':assigned_to'] = $assignedTo;
    }

    $sql .= " ORDER BY t.due_date ASC, t.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getTaskById(int $id): ?array {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("SELECT t.*, p.title as project_title, u.name as assigned_name, u.email as assigned_email 
                           FROM tasks t 
                           LEFT JOIN projects p ON t.project_id = p.id 
                           LEFT JOIN users u ON t.assigned_user_id = u.id 
                           WHERE t.id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $res = $stmt->fetch();
    return $res ?: null;
}

function createTask(array $data, int $creatorId): bool {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("INSERT INTO tasks 
        (project_id, title, description, priority, status, due_date, assigned_user_id, created_by_user_id, created_at) 
        VALUES (:project_id, :title, :description, :priority, :status, :due_date, :assigned_user_id, :created_by_user_id, NOW())");

    return $stmt->execute([
        ':project_id'          => !empty($data['project_id']) ? (int)$data['project_id'] : null,
        ':title'               => $data['title'],
        ':description'         => $data['description'] ?? '',
        ':priority'            => $data['priority'] ?? 'Sederhana',
        ':status'              => $data['status'] ?? 'Dalam Tindakan',
        ':due_date'            => !empty($data['due_date']) ? $data['due_date'] : null,
        ':assigned_user_id'    => !empty($data['assigned_user_id']) ? (int)$data['assigned_user_id'] : null,
        ':created_by_user_id'  => $creatorId
    ]);
}

function updateTask(int $id, array $data): bool {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("UPDATE tasks SET 
        project_id = :project_id, 
        title = :title, 
        description = :description, 
        priority = :priority, 
        status = :status, 
        due_date = :due_date, 
        assigned_user_id = :assigned_user_id, 
        updated_at = NOW() 
        WHERE id = :id");

    return $stmt->execute([
        ':id'               => $id,
        ':project_id'       => !empty($data['project_id']) ? (int)$data['project_id'] : null,
        ':title'            => $data['title'],
        ':description'      => $data['description'] ?? '',
        ':priority'         => $data['priority'],
        ':status'           => $data['status'],
        ':due_date'         => !empty($data['due_date']) ? $data['due_date'] : null,
        ':assigned_user_id' => !empty($data['assigned_user_id']) ? (int)$data['assigned_user_id'] : null
    ]);
}

function deleteTask(int $id): bool {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

// ==========================================
// 6. OPERASI PENGGUNA (USERS & STAFF)
// ==========================================

function getAllUsers(string $role = ''): array {
    $pdo = getDatabaseConnection();
    $sql = "SELECT id, name, email, role, unit, phone, status, last_login, created_at FROM users WHERE 1=1";
    $params = [];
    if (!empty($role)) {
        $sql .= " AND role = :role";
        $params[':role'] = $role;
    }
    $sql .= " ORDER BY name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getUserById(int $id): ?array {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("SELECT id, name, email, role, unit, phone, status, last_login FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $res = $stmt->fetch();
    return $res ?: null;
}

function createUser(array $data): bool {
    $pdo = getDatabaseConnection();
    // Sahkan e-mel belum digunakan
    $check = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $check->execute([':email' => $data['email']]);
    if ($check->fetch()) {
        throw new InvalidArgumentException('E-mel tersebut telah didaftarkan dalam sistem.');
    }

    $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users 
        (name, email, password, role, unit, phone, status, created_at) 
        VALUES (:name, :email, :password, :role, :unit, :phone, :status, NOW())");

    return $stmt->execute([
        ':name'     => $data['name'],
        ':email'    => $data['email'],
        ':password' => $hashed,
        ':role'     => $data['role'] ?? 'Staff',
        ':unit'     => $data['unit'] ?? 'Perancangan Wilayah',
        ':phone'    => $data['phone'] ?? '',
        ':status'   => $data['status'] ?? 'Aktif'
    ]);
}

function updateUser(int $id, array $data): bool {
    $pdo = getDatabaseConnection();
    $params = [
        ':id'    => $id,
        ':name'  => $data['name'],
        ':email' => $data['email'],
        ':role'  => $data['role'],
        ':unit'  => $data['unit'],
        ':phone' => $data['phone'] ?? '',
        ':status'=> $data['status'] ?? 'Aktif'
    ];

    $sql = "UPDATE users SET name = :name, email = :email, role = :role, unit = :unit, phone = :phone, status = :status, updated_at = NOW()";

    if (!empty($data['password'])) {
        $sql .= ", password = :password";
        $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    $sql .= " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function deleteUser(int $id): bool {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

// ==========================================
// 7. STATISTIK & KPI PAPAN PEMUKA
// ==========================================

function getDashboardStats(): array {
    $pdo = getDatabaseConnection();

    // Jumlah Projek
    $pTotal = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
    $pActive = $pdo->query("SELECT COUNT(*) FROM projects WHERE status IN ('Sedang Berjalan', 'Dalam Perancangan')")->fetchColumn();
    $pCompleted = $pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'Selesai'")->fetchColumn();

    // Jumlah Tugasan
    $tTotal = $pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
    $tCompleted = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'Selesai'")->fetchColumn();
    $tPending = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status IN ('Dalam Tindakan', 'Pending')")->fetchColumn();
    $tOverdue = $pdo->query("SELECT COUNT(*) FROM tasks WHERE due_date < CURDATE() AND status != 'Selesai'")->fetchColumn();

    // Jumlah Kakitangan
    $uTotal = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'Aktif'")->fetchColumn();

    return [
        'total_projects'     => (int)$pTotal,
        'active_projects'    => (int)$pActive,
        'completed_projects' => (int)$pCompleted,
        'total_tasks'        => (int)$tTotal,
        'completed_tasks'    => (int)$tCompleted,
        'pending_tasks'      => (int)$tPending,
        'overdue_tasks'      => (int)$tOverdue,
        'total_staff'        => (int)$uTotal,
        'completion_rate'    => $tTotal > 0 ? round(($tCompleted / $tTotal) * 100, 1) : 0
    ];
}
