-- ========================================================================
-- SKEMA PENGKALAN DATA MYSQL (database.sql)
-- SISTEM PENGURUSAN TUGASAN & PROJEK PERANCANGAN
-- JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
-- ========================================================================

-- Cipta Pangkalan Data (Gunakan utf8mb4_unicode_ci untuk aksara antarabangsa & Melayu)
CREATE DATABASE IF NOT EXISTS `db_planmalaysia_perlis` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `db_planmalaysia_perlis`;

-- --------------------------------------------------------
-- 1. JADUAL PENGGUNA (users)
-- Menyimpan maklumat kakitangan, peranan (RBAC), dan kelayakan log masuk
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('Admin', 'Pengarah', 'Staff') NOT NULL DEFAULT 'Staff',
    `unit` VARCHAR(100) NOT NULL DEFAULT 'Perancangan Wilayah',
    `phone` VARCHAR(25) NULL,
    `avatar` VARCHAR(255) NULL,
    `status` ENUM('Aktif', 'Tidak Aktif') NOT NULL DEFAULT 'Aktif',
    `last_login` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. JADUAL PROJEK PERANCANGAN (projects)
-- Menyimpan senarai kajian, rancangan tempatan, dan pelan pembangunan negeri
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NOT NULL DEFAULT 'Rancangan Tempatan (RT)',
    `description` TEXT NULL,
    `status` ENUM('Dalam Perancangan', 'Sedang Berjalan', 'Dalam Semakan', 'Selesai', 'Tertangguh') NOT NULL DEFAULT 'Dalam Perancangan',
    `progress` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `start_date` DATE NULL,
    `end_date` DATE NULL,
    `budget` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `lead_user_id` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_projects_status` (`status`),
    INDEX `idx_projects_category` (`category`),
    CONSTRAINT `fk_projects_lead_user` FOREIGN KEY (`lead_user_id`) 
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. JADUAL TUGASAN & SASARAN KERJA (tasks)
-- Mengurus tugasan harian kakitangan dan perkaitannya dengan projek perancangan
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT UNSIGNED NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `priority` ENUM('Rendah', 'Sederhana', 'Tinggi') NOT NULL DEFAULT 'Sederhana',
    `status` ENUM('Pending', 'Dalam Tindakan', 'Dalam Semakan', 'Selesai') NOT NULL DEFAULT 'Dalam Tindakan',
    `due_date` DATE NULL,
    `assigned_user_id` INT UNSIGNED NULL,
    `created_by_user_id` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tasks_status` (`status`),
    INDEX `idx_tasks_priority` (`priority`),
    INDEX `idx_tasks_due_date` (`due_date`),
    CONSTRAINT `fk_tasks_project` FOREIGN KEY (`project_id`) 
        REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tasks_assigned_user` FOREIGN KEY (`assigned_user_id`) 
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_tasks_created_by` FOREIGN KEY (`created_by_user_id`) 
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. JADUAL LOG AUDIT KESELAMATAN (audit_logs)
-- Merekodkan aktiviti pengguna untuk pematuhan keselamatan sektor awam
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(50) NOT NULL,
    `entity` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NULL,
    `description` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_action` (`action`),
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
