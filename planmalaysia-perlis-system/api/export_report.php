<?php
/**
 * ========================================================================
 * SKRIP EKSPORT LAPORAN KE FORMAT CSV / EXCEL (export_report.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$filename = "Laporan_PLANMalaysia_Perlis_" . date('Ymd_His') . ".csv";

// Tetapkan tajuk HTTP muat turun
header('Content-Type: text/csv; charset=UTF-8');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// Masukkan UTF-8 Byte Order Mark (BOM) untuk memastikan keserasian penuh dengan Microsoft Excel pada Windows
fputs($output, "\xEF\xBB\xBF");

// Tajuk Dokumen
fputcsv($output, ['JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)']);
fputcsv($output, ['SISTEM PENGURUSAN TUGASAN & PROJEK PERANCANGAN NEGERI']);
fputcsv($output, ['Tarikh Laporan Dijana', date('d/m/Y H:i:s')]);
fputcsv($output, ['Dijana Oleh', $_SESSION['user_name'] . ' (' . $_SESSION['user_role'] . ')']);
fputcsv($output, []); // Baris kosong

// Bahagian 1: Senarai Projek
fputcsv($output, ['=== BAHAGIAN 1: SENARAI PROJEK PERANCANGAN ===']);
fputcsv($output, [
    'Bil',
    'Kod Projek',
    'Tajuk Projek',
    'Kategori',
    'Status',
    'Peratus Kemajuan (%)',
    'Tarikh Mula',
    'Tarikh Sasaran Selesai',
    'Peruntukan (RM)',
    'Pegawai Penyelaras'
]);

$projects = getAllProjects('', '', 500);
$i = 1;
foreach ($projects as $p) {
    fputcsv($output, [
        $i++,
        $p['code'],
        $p['title'],
        $p['category'],
        $p['status'],
        $p['progress'] . '%',
        $p['start_date'] ?: '-',
        $p['end_date'] ?: '-',
        number_format($p['budget'], 2, '.', ''),
        $p['lead_name'] ?: 'Belum Ditugaskan'
    ]);
}

fputcsv($output, []); // Baris kosong
fputcsv($output, []); 

// Bahagian 2: Senarai Tugasan
fputcsv($output, ['=== BAHAGIAN 2: DAFTAR TUGASAN KAKITANGAN ===']);
fputcsv($output, [
    'Bil',
    'Tajuk Tugasan',
    'Projek Berkaitan',
    'Ditugaskan Kepada',
    'Keutamaan',
    'Tarikh Sasaran',
    'Status Tugasan'
]);

$tasks = getAllTasks('', '', '', null, null);
$j = 1;
foreach ($tasks as $t) {
    fputcsv($output, [
        $j++,
        $t['title'],
        $t['project_title'] ?: 'Tugasan Am',
        $t['assigned_name'] ?: 'Semua Kakitangan',
        $t['priority'],
        $t['due_date'] ?: '-',
        $t['status']
    ]);
}

fclose($output);
exit;
