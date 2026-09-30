<?php
/**
 * ========================================================================
 * TITIK MASUK UTAMA APLIKASI (index.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Jika pengguna sudah log masuk, bawa ke Papan Pemuka; jika tidak, bawa ke Laman Log Masuk
if (isLoggedIn()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
