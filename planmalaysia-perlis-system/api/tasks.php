<?php
/**
 * ========================================================================
 * RESTFUL API TUGASAN KAKITANGAN (tasks.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Akses tidak dibenarkan.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $task = getTaskById($id);
            if ($task) {
                echo json_encode(['status' => 'success', 'data' => $task]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Tugasan tidak dijumpai.']);
            }
        } else {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $priority = $_GET['priority'] ?? '';
            $projectId = !empty($_GET['project_id']) ? (int)$_GET['project_id'] : null;
            $list = getAllTasks($search, $status, $priority, $projectId);
            echo json_encode(['status' => 'success', 'count' => count($list), 'data' => $list]);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Kaedah HTTP tidak dibenarkan.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
