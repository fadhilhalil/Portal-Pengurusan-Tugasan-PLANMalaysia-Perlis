-- ========================================================================
-- DATA CONTOH / DEMO SEED DATA (sample_data.sql)
-- SISTEM PENGURUSAN TUGASAN & PROJEK PERANCANGAN
-- JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
-- ========================================================================

USE `db_planmalaysia_perlis`;

-- --------------------------------------------------------
-- 1. DATA KAKITANGAN CONTOH
-- Kata laluan lalai untuk semua akaun demo di bawah adalah: "password"
-- Menggunakan hash Bcrypt: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `unit`, `phone`, `status`, `last_login`, `created_at`) VALUES
(1, 'Pentadbir Sistem ICT', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Bahagian Korporat & Kewangan', '04-9761234', 'Aktif', NOW(), NOW()),
(2, 'Dr. Azman bin Yahya', 'pengarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pengarah', 'Pejabat Pengarah Negeri', '04-9765555', 'Aktif', NOW(), NOW()),
(3, 'TPr. Nur Syahirah binti Rosli', 'staff1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff', 'Bahagian Rancangan Pembangunan', '019-4567890', 'Aktif', NOW(), NOW()),
(4, 'TPr. Mohd Hafiz bin Mansor', 'staff2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff', 'Bahagian Kawalan Pemajuan', '012-3456789', 'Aktif', NOW(), NOW()),
(5, 'Siti Aisyah binti Kamaruddin', 'staff3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff', 'Pusat Maklumat Geografi (GIS)', '017-8901234', 'Aktif', NOW(), NOW());

-- --------------------------------------------------------
-- 2. DATA PROJEK PERANCANGAN CONTOH
-- Projek-projek berimpak tinggi mengikut teras pembangunan Negeri Perlis
-- --------------------------------------------------------
INSERT INTO `projects` (`id`, `code`, `title`, `category`, `description`, `status`, `progress`, `start_date`, `end_date`, `budget`, `lead_user_id`, `created_at`) VALUES
(1, 'RT-KANGAR-2035', 'Rancangan Tempatan Majlis Perbandaran Kangar 2035 (Penggantian)', 'Rancangan Tempatan (RT)', 'Kajian menyeluruh perancangan guna tanah, pengangkutan, dan zon komersial bagi seluruh daerah Kangar dan pusat bandar pentadbiran negeri.', 'Sedang Berjalan', 65, '2024-01-15', '2025-12-31', 450000.00, 3, NOW()),
(2, 'RSN-PERLIS-2040', 'Kajian Semakan Rancangan Struktur Negeri Perlis 2040', 'Rancangan Struktur Negeri (RSN)', 'Merangka dasar strategik spatial negeri merangkumi pertumbuhan ekonomi koridor utara, sempadan antarabangsa, dan kelestarian alam sekitar.', 'Dalam Semakan', 80, '2023-06-01', '2025-06-30', 850000.00, 2, NOW()),
(3, 'RKK-PBB-2030', 'Rancangan Kawasan Khas Sempadan Antarabangsa Padang Besar', 'Rancangan Kawasan Khas (RKK)', 'Pelan pemajuan zon bebas cukai, hab logistik darat rentas sempadan (Dry Port), dan peremajaan bandar Padang Besar.', 'Sedang Berjalan', 40, '2024-03-01', '2026-03-31', 320000.00, 4, NOW()),
(4, 'GIS-AGRI-2024', 'Pemetaan Guna Tanah Pertanian Moden & Harumanis Berasaskan GIS', 'Kajian Guna Tanah & GIS', 'Pangkalan data spatial berkomputer untuk pemantauan zon pertanian bernilai tinggi, perlindungan tanah sawah kelas 1 dan dusun Harumanis.', 'Selesai', 100, '2024-01-01', '2024-08-30', 120000.00, 5, NOW()),
(5, 'RKK-KUALAPERLIS', 'Rancangan Kawasan Khas Pesisir Pantai & Pelancongan Kuala Perlis', 'Rancangan Kawasan Khas (RKK)', 'Pembangunan semula jeti penumpang Langkawi, zon pelancongan makanan laut, serta zon penampan paya bakau Kuala Perlis.', 'Dalam Perancangan', 15, '2024-09-01', '2026-12-31', 280000.00, 3, NOW());

-- --------------------------------------------------------
-- 3. DATA TUGASAN KAKITANGAN CONTOH
-- Sasaran kerja harian dan tugasan projek
-- --------------------------------------------------------
INSERT INTO `tasks` (`id`, `project_id`, `title`, `description`, `priority`, `status`, `due_date`, `assigned_user_id`, `created_by_user_id`, `created_at`) VALUES
(1, 1, 'Mengurus Sesi Publisiti & Penyertaan Awam RT Kangar 2035', 'Sediakan banner promosi, risalah maklum balas borang bantahan, dan susun atur ruang pameran di Dewan 2020 Kangar.', 'Tinggi', 'Sedang Berjalan', '2025-02-15', 3, 1, NOW()),
(2, 1, 'Penyediaan Laporan Pemeriksaan (Draft Inception Report)', 'Kompilasikan analisis data demografi, unjuran penduduk 2035, dan corak guna tanah semasa.', 'Sederhana', 'Selesai', '2024-05-30', 3, 2, NOW()),
(3, 2, 'Mesyuarat Jawatankuasa Perancang Negeri (JPN) Bil. 2/2024', 'Sediakan slaid pembentangan eksekutif untuk YAB Menteri Besar dan ahli mesyuarat mengenai status RSN Perlis 2040.', 'Tinggi', 'Dalam Semakan', '2025-01-20', 4, 2, NOW()),
(4, 3, 'Kajian Lapangan & Temubual Agensi Sempadan di Padang Besar', 'Libat urus bersama pihak Kastam Diraja Malaysia, Imigresen, dan KTMB bagi keperluan kawasan pelepas kargo darat.', 'Sederhana', 'Dalam Tindakan', '2025-03-10', 4, 1, NOW()),
(5, 4, 'Penyerahan Data Spatial Geodatabase kepada Pusat GIS Negeri', 'Eksport lapisan Shapefile (SHP) & GeoPackage bagi zon tanaman Harumanis ke pelayan GIS Perlis.', 'Rendah', 'Selesai', '2024-08-25', 5, 2, NOW()),
(6, 5, 'Kajian Impak Trafik & Zon Penjaja Jeti Kuala Perlis', 'Kaji corak kesesakan kenderaan semasa cuti persekolahan dan cadangan tapak parkir bertingkat.', 'Tinggi', 'Dalam Tindakan', '2025-04-30', 3, 1, NOW()),
(7, NULL, 'Audit Keselamatan Siber & Sandaran Pangkalan Data Bulanan', 'Lakukan full backup pangkalan data MySQL dan semak log sistem IIS Windows Server 2019.', 'Tinggi', 'Dalam Tindakan', '2025-01-31', 1, 1, NOW());

-- --------------------------------------------------------
-- 4. DATA LOG AUDIT CONTOH
-- --------------------------------------------------------
INSERT INTO `audit_logs` (`user_id`, `action`, `entity`, `entity_id`, `description`, `ip_address`, `created_at`) VALUES
(1, 'LOGIN', 'users', 1, 'Pentadbir Sistem log masuk ke portal', '127.0.0.1', NOW()),
(1, 'CREATE', 'projects', 1, 'Mendaftar projek RT Kangar 2035', '127.0.0.1', NOW()),
(2, 'LOGIN', 'users', 2, 'Pengarah log masuk dan melihat laporan eksekutif', '192.168.1.50', NOW());
