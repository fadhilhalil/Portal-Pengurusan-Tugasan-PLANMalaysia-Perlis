<?php
/**
 * ========================================================================
 * API STATISTIK PAPAN PEMUKA (get_stats.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Sila log masuk untuk mengakses data API ini.']);
    exit;
}

try {
    $stats = getDashboardStats();
    echo json_encode([
        'status'    => 'success',
        'timestamp' => date('c'),
        'data'      => $stats
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Gagal mendapatkan statistik: ' . $e->getMessage()
    ]);
}
