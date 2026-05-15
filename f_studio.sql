-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2026 at 08:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `f_studio`
--

-- --------------------------------------------------------

--
-- Table structure for table `app_notifications`
--

CREATE TABLE `app_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(100) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_notifications`
--

INSERT INTO `app_notifications` (`id`, `user_id`, `type`, `title`, `message`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 2, 'loan_created', 'Peminjaman Diajukan', 'Peminjaman peralatan dengan kode LN-20260516-0001 berhasil diajukan.', '{\"loan_id\":1}', NULL, '2026-05-15 19:26:26', '2026-05-15 19:26:26'),
(2, 2, 'booking_created', 'Pemesanan Diajukan', 'Pemesanan ruang Studio Foto Utama dengan kode BK-20260516-0001 berhasil diajukan.', '{\"booking_id\":1}', NULL, '2026-05-15 19:29:43', '2026-05-15 19:29:43'),
(3, 2, 'booking_approved', 'Pemesanan Disetujui', 'Pemesanan BK-20260516-0001 telah disetujui.', '{\"booking_id\":1}', NULL, '2026-05-15 19:30:03', '2026-05-15 19:30:03'),
(4, 2, 'loan_approved', 'Peminjaman Disetujui', 'Peminjaman LN-20260516-0001 telah disetujui.', '{\"loan_id\":1}', NULL, '2026-05-15 19:30:17', '2026-05-15 19:30:17'),
(5, 2, 'loan_returned', 'Peralatan Dikembalikan', 'Peminjaman LN-20260516-0001 selesai. Peralatan telah dikembalikan dan stok diperbarui.', '{\"loan_id\":1}', NULL, '2026-05-15 19:40:59', '2026-05-15 19:40:59'),
(6, 2, 'loan_created', 'Peminjaman Diajukan', 'Peminjaman peralatan dengan kode LN-20260516-0002 berhasil diajukan.', '{\"loan_id\":2}', NULL, '2026-05-15 19:47:03', '2026-05-15 19:47:03'),
(7, 2, 'loan_approved', 'Peminjaman Disetujui', 'Peminjaman LN-20260516-0002 telah disetujui.', '{\"loan_id\":2}', NULL, '2026-05-15 19:47:33', '2026-05-15 19:47:33');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `room_id` bigint(20) UNSIGNED NOT NULL,
  `booking_code` varchar(30) NOT NULL,
  `customer_name` varchar(200) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('pending','approved','rejected','cancelled','completed') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `room_id`, `booking_code`, `customer_name`, `customer_phone`, `title`, `start_datetime`, `end_datetime`, `status`, `notes`, `admin_notes`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'BK-20260516-0001', 'Rivaldi', '3435345344', 'Keperluan Foto Keluarga', '2026-05-17 13:30:00', '2026-05-17 14:30:00', 'approved', NULL, NULL, 1, '2026-05-15 19:30:03', '2026-05-15 19:29:43', '2026-05-15 19:30:03');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:1;', 1778873174),
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1778873174;', 1778873174);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Kamera', 'Kamera foto dan video digital, DSLR, mirrorless.', '2026-05-15 19:22:52', '2026-05-15 19:22:52'),
(2, 'Pencahayaan', 'Lighting kit, softbox, reflektor, dan lampu studio.', '2026-05-15 19:22:52', '2026-05-15 19:22:52'),
(3, 'Audio', 'Mikrofon, audio interface, dan aksesori rekaman.', '2026-05-15 19:22:52', '2026-05-15 19:22:52'),
(4, 'Tripod & Stabilizer', 'Tripod, monopod, gimbal, dan alat stabilisasi.', '2026-05-15 19:22:52', '2026-05-15 19:22:52'),
(5, 'Lensa', 'Lensa berbagai jenis untuk kamera mirrorless dan DSLR.', '2026-05-15 19:22:52', '2026-05-15 19:22:52'),
(6, 'Aksesori Studio', 'Backdrop, stand, clamp, dan perlengkapan studio lainnya.', '2026-05-15 19:22:52', '2026-05-15 19:22:52');

-- --------------------------------------------------------

--
-- Table structure for table `check_ins`
--

CREATE TABLE `check_ins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `equipment_loan_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `action` enum('check_in','check_out') NOT NULL,
  `checked_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity_total` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `quantity_available` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `location` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `category_id`, `name`, `code`, `description`, `quantity_total`, `quantity_available`, `location`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Sony A7 III', 'CAM-001', NULL, 2, 2, 'Rak A-1', NULL, 0, '2026-05-15 19:22:52', '2026-05-15 19:46:08'),
(2, 1, 'Canon EOS R5', 'CAM-002', NULL, 1, 0, 'Rak A-1', NULL, 1, '2026-05-15 19:22:52', '2026-05-15 19:47:33'),
(3, 2, 'Godox SL-200W', 'LGT-001', NULL, 4, 4, 'Rak B-1', NULL, 1, '2026-05-15 19:22:52', '2026-05-15 19:22:52'),
(4, 2, 'Softbox 90x90cm', 'LGT-002', NULL, 4, 4, 'Rak B-2', NULL, 1, '2026-05-15 19:22:52', '2026-05-15 19:22:52'),
(5, 3, 'Rode NTG5 Shotgun', 'AUD-001', NULL, 3, 3, 'Rak C-1', NULL, 1, '2026-05-15 19:22:52', '2026-05-15 19:22:52'),
(6, 3, 'Focusrite Scarlett 2i2', 'AUD-002', NULL, 2, 2, 'Rak C-1', NULL, 1, '2026-05-15 19:22:52', '2026-05-15 19:22:52'),
(7, 4, 'Manfrotto MT055', 'TRP-001', NULL, 3, 3, 'Rak D-1', NULL, 1, '2026-05-15 19:22:52', '2026-05-15 19:22:52'),
(8, 4, 'DJI Ronin RS3', 'GIM-001', NULL, 2, 2, 'Rak D-2', NULL, 1, '2026-05-15 19:22:52', '2026-05-15 19:40:59'),
(9, 5, 'Sony FE 24-70mm f/2.8', 'LNS-001', NULL, 2, 0, 'Rak A-2', NULL, 1, '2026-05-15 19:22:52', '2026-05-15 19:47:33'),
(10, 6, 'Backdrop Muslin 3x6m', 'ACC-001', NULL, 3, 3, 'Gudang', NULL, 1, '2026-05-15 19:22:52', '2026-05-15 19:22:52');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_loans`
--

CREATE TABLE `equipment_loans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED DEFAULT NULL,
  `loan_date` date DEFAULT NULL,
  `loan_code` varchar(30) NOT NULL,
  `customer_name` varchar(200) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `purpose` text NOT NULL,
  `status` enum('pending','approved','rejected','active','returned','overdue') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `due_date` date NOT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equipment_loans`
--

INSERT INTO `equipment_loans` (`id`, `user_id`, `booking_id`, `loan_date`, `loan_code`, `customer_name`, `customer_phone`, `purpose`, `status`, `notes`, `admin_notes`, `approved_by`, `approved_at`, `due_date`, `returned_at`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, '2026-05-20', 'LN-20260516-0001', 'Andre', '0475374857', 'Peminjaman Camera Sony Keperluan Pemotretan Preweeding', 'returned', NULL, NULL, 1, '2026-05-15 19:30:17', '2026-05-21', '2026-05-15 19:40:59', '2026-05-15 19:26:26', '2026-05-15 19:40:59'),
(2, 2, NULL, '2026-05-16', 'LN-20260516-0002', 'Rizky Fadhillah', '0475374857', 'Pemotretan Alam', 'approved', NULL, NULL, 1, '2026-05-15 19:47:33', '2026-05-17', NULL, '2026-05-15 19:47:03', '2026-05-15 19:47:33');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_loan_items`
--

CREATE TABLE `equipment_loan_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `equipment_loan_id` bigint(20) UNSIGNED NOT NULL,
  `equipment_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `check_in_at` timestamp NULL DEFAULT NULL,
  `check_out_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equipment_loan_items`
--

INSERT INTO `equipment_loan_items` (`id`, `equipment_loan_id`, `equipment_id`, `quantity`, `check_in_at`, `check_out_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, NULL, '2026-05-15 19:40:59', '', '2026-05-15 19:26:26', '2026-05-15 19:40:59'),
(2, 1, 8, 2, NULL, '2026-05-15 19:40:59', '', '2026-05-15 19:26:26', '2026-05-15 19:40:59'),
(3, 2, 2, 1, NULL, NULL, NULL, '2026-05-15 19:47:03', '2026-05-15 19:47:03'),
(4, 2, 9, 2, NULL, NULL, NULL, '2026-05-15 19:47:03', '2026-05-15 19:47:03');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_01_000002_create_categories_table', 1),
(5, '2026_01_01_000003_create_equipment_table', 1),
(6, '2026_01_01_000004_create_rooms_table', 1),
(7, '2026_01_01_000005_create_bookings_table', 1),
(8, '2026_01_01_000006_create_equipment_loans_table', 1),
(9, '2026_01_01_000007_create_equipment_loan_items_table', 1),
(10, '2026_01_01_000008_create_check_ins_table', 1),
(11, '2026_01_01_000009_create_notifications_table', 1),
(12, '2026_05_15_000001_add_receptionist_fields_to_bookings', 1),
(13, '2026_05_15_000002_add_receptionist_role_to_users', 1),
(14, '2026_05_15_135055_create_personal_access_tokens_table', 1),
(15, '2026_05_16_000002_add_loan_date_to_equipment_loans', 1),
(16, '2026_05_16_024318_add_condition_return_breakdown_to_equipment_loan_items', 2),
(17, '2026_05_16_025232_drop_condition_columns_from_equipment_tables', 3);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `capacity` int(10) UNSIGNED NOT NULL,
  `facilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`facilities`)),
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `code`, `description`, `capacity`, `facilities`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Studio Foto Utama', 'STD-01', 'Studio foto profesional dengan cyclorama putih, dilengkapi lighting kit lengkap.', 8, '[\"Cyclorama\",\"Lighting Kit\",\"AC\",\"WiFi\",\"Backdrop Stand\"]', 'rooms/5wU1WdBtKHsveewIN7BMoejIIbGNKtrpH7DB4iOz.jpg', 1, '2026-05-15 19:22:52', '2026-05-15 19:24:05'),
(2, 'Studio Video A', 'STD-02', 'Studio produksi video dengan akustik treatment dan green screen.', 6, '[\"Green Screen\",\"Akustik Panel\",\"AC\",\"WiFi\",\"Monitor 4K\"]', 'rooms/uifCVfZDvnJWXyIPgS7Mrjp5kdX4SFkuHUDFi0t4.jpg', 1, '2026-05-15 19:22:52', '2026-05-15 19:24:12'),
(3, 'Ruang Meeting Kreatif', 'MTG-01', 'Ruang diskusi dan brainstorming untuk tim kreatif.', 12, '[\"Proyektor\",\"Whiteboard\",\"AC\",\"WiFi\",\"TV 65 inch\"]', 'rooms/Qqxh5hewTpcQGpuhTZKnYait4jzlpcysIs15kcZA.jpg', 1, '2026-05-15 19:22:52', '2026-05-15 19:24:22'),
(4, 'Ruang Edit', 'EDT-01', 'Ruang editing dengan workstation high-spec untuk pasca-produksi.', 4, '[\"Workstation Mac\",\"Monitor Color Grading\",\"AC\",\"WiFi\"]', 'rooms/LsaHy2xgV1L0tG2g7ngmizeWywBv1jJGyCmh86e0.jpg', 1, '2026-05-15 19:22:52', '2026-05-15 19:24:32');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('gvzH9LXcb24oTIuAYBNf0YJgOk1wgWphZ2aZRg5V', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'eyJfdG9rZW4iOiJybU02U0ZaSHpjRzZpczhVa0hvemdVYnJQaUVuYUF4OUtBV2RVb1ZVIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2Jvb2tpbmdzP2JwYWdlPTEifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9lcXVpcG1lbnRcL2NyZWF0ZSIsInJvdXRlIjoiZXF1aXBtZW50LmNyZWF0ZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxfQ==', 1778875244),
('TQeGuSfTtkCDd9VtjrzS41uW1g8WIazlpIfF8H54', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJITVBQcEg4YWdhVWF3bzhBMW94SWNXa0pqVmVNZUZQUUpxWjczeGI1IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJ1cmwiOnsiaW50ZW5kZWQiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvcmVjZXB0aW9uaXN0In0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvcmVjZXB0aW9uaXN0XC9sb2FucyIsInJvdXRlIjoicmVjZXB0aW9uaXN0LmxvYW5zIn0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoyfQ==', 1778874918);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','member','receptionist') NOT NULL DEFAULT 'member',
  `phone` varchar(20) DEFAULT NULL,
  `member_id` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `member_id`, `avatar`, `is_active`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrator F-Studio', 'admin@fstudio.id', '$2y$12$JWt/70MmIPoaODCtqdas.uLaVBUrXgfYlo0x94n/wxxxEU7BIPjBO', 'admin', '081234567890', NULL, NULL, 1, NULL, NULL, '2026-05-15 19:22:52', '2026-05-15 19:22:52'),
(2, 'Rizky', 'rizky@fstudio.id', '$2y$12$rc4T1hhzgcdU/m1Pzl3tXu9hm.KJRf/FGkTELhHGLyWmdeC38OOe6', 'receptionist', '081298765432', NULL, NULL, 1, NULL, NULL, '2026-05-15 19:22:52', '2026-05-15 19:22:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_notifications`
--
ALTER TABLE `app_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `app_notifications_user_id_read_at_index` (`user_id`,`read_at`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bookings_booking_code_unique` (`booking_code`),
  ADD KEY `bookings_approved_by_foreign` (`approved_by`),
  ADD KEY `bookings_user_id_index` (`user_id`),
  ADD KEY `bookings_room_id_index` (`room_id`),
  ADD KEY `bookings_status_index` (`status`),
  ADD KEY `bookings_room_id_start_datetime_end_datetime_index` (`room_id`,`start_datetime`,`end_datetime`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_name_unique` (`name`);

--
-- Indexes for table `check_ins`
--
ALTER TABLE `check_ins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `check_ins_user_id_foreign` (`user_id`),
  ADD KEY `check_ins_equipment_loan_id_action_index` (`equipment_loan_id`,`action`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equipment_code_unique` (`code`),
  ADD KEY `equipment_category_id_index` (`category_id`),
  ADD KEY `equipment_condition_is_active_index` (`is_active`);

--
-- Indexes for table `equipment_loans`
--
ALTER TABLE `equipment_loans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equipment_loans_loan_code_unique` (`loan_code`),
  ADD KEY `equipment_loans_booking_id_foreign` (`booking_id`),
  ADD KEY `equipment_loans_approved_by_foreign` (`approved_by`),
  ADD KEY `equipment_loans_user_id_index` (`user_id`),
  ADD KEY `equipment_loans_status_index` (`status`),
  ADD KEY `equipment_loans_due_date_index` (`due_date`);

--
-- Indexes for table `equipment_loan_items`
--
ALTER TABLE `equipment_loan_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_loan_items_equipment_loan_id_index` (`equipment_loan_id`),
  ADD KEY `equipment_loan_items_equipment_id_index` (`equipment_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rooms_code_unique` (`code`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_member_id_unique` (`member_id`),
  ADD KEY `users_role_index` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `app_notifications`
--
ALTER TABLE `app_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `check_ins`
--
ALTER TABLE `check_ins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `equipment_loans`
--
ALTER TABLE `equipment_loans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `equipment_loan_items`
--
ALTER TABLE `equipment_loan_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `app_notifications`
--
ALTER TABLE `app_notifications`
  ADD CONSTRAINT `app_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `check_ins`
--
ALTER TABLE `check_ins`
  ADD CONSTRAINT `check_ins_equipment_loan_id_foreign` FOREIGN KEY (`equipment_loan_id`) REFERENCES `equipment_loans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `check_ins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `equipment_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `equipment_loans`
--
ALTER TABLE `equipment_loans`
  ADD CONSTRAINT `equipment_loans_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_loans_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_loans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `equipment_loan_items`
--
ALTER TABLE `equipment_loan_items`
  ADD CONSTRAINT `equipment_loan_items_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_loan_items_equipment_loan_id_foreign` FOREIGN KEY (`equipment_loan_id`) REFERENCES `equipment_loans` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
