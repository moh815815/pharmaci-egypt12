-- ============================================================
-- Egyptian Pharmacies Directory - Database Schema
-- دليل الصيدليات المصرية - هيكل قاعدة البيانات
-- ============================================================

CREATE DATABASE IF NOT EXISTS `pharmaci_egypt` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pharmaci_egypt`;

-- ============================================================
-- 1. Governorates (المحافظات)
-- ============================================================
CREATE TABLE `governorates` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name_ar` VARCHAR(100) NOT NULL,
  `name_en` VARCHAR(100) DEFAULT NULL,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. Cities / Districts (المدن / المناطق)
-- ============================================================
CREATE TABLE `cities` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `governorate_id` INT UNSIGNED NOT NULL,
  `name_ar` VARCHAR(150) NOT NULL,
  `name_en` VARCHAR(150) DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`governorate_id`) REFERENCES `governorates`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. Main Streets (الشوارع الرئيسية)
-- ============================================================
CREATE TABLE `streets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `city_id` INT UNSIGNED NOT NULL,
  `name_ar` VARCHAR(200) NOT NULL,
  `name_en` VARCHAR(200) DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`city_id`) REFERENCES `cities`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. Pharmacy Tiers (أنواع الاشتراكات / البطاقات)
-- ============================================================
CREATE TABLE `pharmacy_tiers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name_ar` VARCHAR(50) NOT NULL,
  `name_en` VARCHAR(50) DEFAULT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `photos_limit` INT UNSIGNED NOT NULL DEFAULT 0,
  `has_logo` TINYINT(1) NOT NULL DEFAULT 0,
  `has_whatsapp` TINYINT(1) NOT NULL DEFAULT 0,
  `has_map` TINYINT(1) NOT NULL DEFAULT 0,
  `has_license_upload` TINYINT(1) NOT NULL DEFAULT 0,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. Pharmacies (الصيدليات)
-- ============================================================
CREATE TABLE `pharmacies` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tier_id` INT UNSIGNED NOT NULL,
  `governorate_id` INT UNSIGNED NOT NULL,
  `city_id` INT UNSIGNED NOT NULL,
  `street_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `name_ar` VARCHAR(200) NOT NULL,
  `name_en` VARCHAR(200) DEFAULT NULL,
  `owner_name` VARCHAR(200) DEFAULT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `whatsapp` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `building_no` VARCHAR(50) DEFAULT NULL,
  `floor` VARCHAR(50) DEFAULT NULL,
  `landmark` VARCHAR(255) DEFAULT NULL,
  `latitude` DECIMAL(10,8) DEFAULT NULL,
  `longitude` DECIMAL(11,8) DEFAULT NULL,
  `logo` VARCHAR(255) DEFAULT NULL,
  `is_24hours` TINYINT(1) NOT NULL DEFAULT 0,
  `has_delivery` TINYINT(1) NOT NULL DEFAULT 0,
  `has_license` TINYINT(1) NOT NULL DEFAULT 0,
  `license_file` VARCHAR(255) DEFAULT NULL,
  `license_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
  `views_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `featured_until` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`tier_id`) REFERENCES `pharmacy_tiers`(`id`) ON UPDATE CASCADE,
  FOREIGN KEY (`governorate_id`) REFERENCES `governorates`(`id`) ON UPDATE CASCADE,
  FOREIGN KEY (`city_id`) REFERENCES `cities`(`id`) ON UPDATE CASCADE,
  FOREIGN KEY (`street_id`) REFERENCES `streets`(`id`) ON UPDATE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. Pharmacy Photos (صور الصيدليات)
-- ============================================================
CREATE TABLE `pharmacy_photos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `pharmacy_id` INT UNSIGNED NOT NULL,
  `photo` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. Pharmacy Licenses History (سجل التراخيص)
-- ============================================================
CREATE TABLE `pharmacy_licenses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `pharmacy_id` INT UNSIGNED NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(50) DEFAULT NULL,
  `file_size` INT UNSIGNED DEFAULT 0,
  `verified` TINYINT(1) NOT NULL DEFAULT 0,
  `verified_by` INT UNSIGNED DEFAULT NULL,
  `verified_at` DATETIME DEFAULT NULL,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. Users (المستخدمين - أصحاب الصيدليات)
-- ============================================================
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(200) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL UNIQUE,
  `phone` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `whatsapp` VARCHAR(50) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. Admin Users (المشرفين)
-- ============================================================
CREATE TABLE `admin_users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(200) NOT NULL,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(100) DEFAULT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin','admin','moderator') NOT NULL DEFAULT 'moderator',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. Contact Messages (رسائل التواصل)
-- ============================================================
CREATE TABLE `contact_messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(200) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. Pages / CMS (صفحات الموقع)
-- ============================================================
CREATE TABLE `pages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title_ar` VARCHAR(255) NOT NULL,
  `title_en` VARCHAR(255) DEFAULT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `content_ar` LONGTEXT DEFAULT NULL,
  `content_en` LONGTEXT DEFAULT NULL,
  `meta_description` VARCHAR(500) DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. Site Settings (إعدادات الموقع)
-- ============================================================
CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key_name` VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Indexes for Performance
-- ============================================================
CREATE INDEX `idx_pharmacies_status` ON `pharmacies`(`status`);
CREATE INDEX `idx_pharmacies_tier` ON `pharmacies`(`tier_id`);
CREATE INDEX `idx_pharmacies_governorate` ON `pharmacies`(`governorate_id`);
CREATE INDEX `idx_pharmacies_city` ON `pharmacies`(`city_id`);
CREATE INDEX `idx_pharmacies_street` ON `pharmacies`(`street_id`);
CREATE INDEX `idx_pharmacies_24hours` ON `pharmacies`(`is_24hours`);
CREATE INDEX `idx_pharmacies_name` ON `pharmacies`(`name_ar`);
CREATE INDEX `idx_cities_governorate` ON `cities`(`governorate_id`);
CREATE INDEX `idx_streets_city` ON `streets`(`city_id`);
CREATE INDEX `idx_pharmacy_photos_pharmacy` ON `pharmacy_photos`(`pharmacy_id`);

-- ============================================================
-- Seed Data: Governorates of Egypt (محافظات مصر)
-- ============================================================
INSERT INTO `governorates` (`name_ar`, `sort_order`) VALUES
('القاهرة', 1),
('الإسكندرية', 2),
('الجيزة', 3),
('الشرقية', 4),
('الدقهلية', 5),
('البحيرة', 6),
('المنيا', 7),
('القليوبية', 8),
('سوهاج', 9),
('كفر الشيخ', 10),
('الغربية', 11),
('المنوفية', 12),
('بني سويف', 13),
('الفيوم', 14),
('أسوان', 15),
('الأقصر', 16),
('قنا', 17),
('أسوان', 18),
('بورسعيد', 19),
('السويس', 20),
('الإسماعيلية', 21),
('دمياط', 22),
('مطروح', 23),
('الوادي الجديد', 24),
('شمال سيناء', 25),
('جنوب سيناء', 26),
('البحر الأحمر', 27);

-- ============================================================
-- Seed Data: Pharmacy Tiers (أنواع البطاقات)
-- ============================================================
INSERT INTO `pharmacy_tiers` (`name_ar`, `name_en`, `slug`, `price`, `photos_limit`, `has_logo`, `has_whatsapp`, `has_map`, `has_license_upload`, `is_featured`, `sort_order`) VALUES
('البطاقة الأساسية', 'Basic Card', 'basic', 0.00, 0, 0, 0, 0, 0, 0, 1),
('البطاقة الفضية', 'Silver Card', 'medium', 250.00, 2, 1, 0, 0, 1, 0, 2),
('البطاقة الذهبية', 'Gold Card', 'large', 500.00, 5, 1, 1, 1, 1, 1, 3);

-- ============================================================
-- Seed Data: Default Admin
-- ============================================================
INSERT INTO `admin_users` (`name`, `username`, `email`, `password`, `role`) VALUES
('المدير العام', 'admin', 'admin@pharmaci-egypt.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');
-- Default password: password

-- ============================================================
-- Seed Data: Default Settings
-- ============================================================
INSERT INTO `settings` (`key_name`, `value`) VALUES
('site_name', 'دليل الصيدليات المصرية'),
('site_description', 'أكبر دليل صيدليات في مصر - ابحث عن أقرب صيدلية لك'),
('admin_email', 'admin@pharmaci-egypt.com'),
('phone', '01000000000'),
('whatsapp', '01000000000'),
('address', 'القاهرة - مصر'),
('facebook_url', ''),
('twitter_url', ''),
('instagram_url', ''),
('currency', 'جنيه مصري'),
('pharmacies_per_page', '12'),
('maintenance_mode', '0'),
('google_maps_api_key', '');
