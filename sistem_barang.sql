-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 11 Feb 2026 pada 03.56
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistem_barang`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `location_type` varchar(255) NOT NULL DEFAULT 'dalam_kota',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `branches`
--

INSERT INTO `branches` (`id`, `name`, `address`, `location_type`, `created_at`, `updated_at`) VALUES
(1, 'Jakarta Pusat', 'Jl. Jakarta Pusat No. 1', 'dalam_kota', '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(2, 'Bandung', 'Jl. Bandung No. 1', 'dalam_kota', '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(3, 'Surabaya', 'Jl. Surabaya No. 1', 'dalam_kota', '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(4, 'Medan', 'Jl. Medan No. 1', 'dalam_kota', '2026-02-10 03:24:05', '2026-02-10 03:24:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `items`
--

CREATE TABLE `items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `unit` varchar(255) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `items`
--

INSERT INTO `items` (`id`, `name`, `category`, `unit`, `stock`, `created_at`, `updated_at`) VALUES
(1, 'Kertas A4', 'Alat Tulis Kantor', 'Rim', 100, '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(2, 'Pulpen Hitam', 'Alat Tulis Kantor', 'Box', 50, '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(3, 'Laptop', 'Elektronik', 'Unit', 10, '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(4, 'Pulpen Biru', 'Stationery', 'Box', 50, '2026-02-10 03:24:07', '2026-02-10 03:24:07'),
(5, 'Spidol Board', 'Stationery', 'Pcs', 20, '2026-02-10 03:24:07', '2026-02-10 03:24:07'),
(6, 'Buku Tulis', 'Stationery', 'Pcs', 200, '2026-02-10 03:24:07', '2026-02-10 03:24:07'),
(7, 'Lakban Bening', 'Stationery', 'Roll', 30, '2026-02-10 03:24:07', '2026-02-10 03:24:07'),
(8, 'Laptop Dell', 'Electronics', 'Unit', 5, '2026-02-10 03:24:07', '2026-02-10 03:24:07'),
(9, 'Mouse Wireless', 'Electronics', 'Unit', 15, '2026-02-10 03:24:07', '2026-02-10 03:24:07'),
(10, 'Keyboard Mechanical', 'Electronics', 'Unit', 10, '2026-02-10 03:24:07', '2026-02-10 03:24:07'),
(11, 'Monitor 24\"', 'Electronics', 'Unit', 8, '2026-02-10 03:24:07', '2026-02-10 03:24:07'),
(12, 'Kabel HDMI', 'Electronics', 'Pcs', 25, '2026-02-10 03:24:07', '2026-02-10 03:24:07'),
(13, 'Kursi Kantor', 'Furniture', 'Unit', 10, '2026-02-10 03:24:07', '2026-02-10 03:24:07'),
(14, 'Meja Kerja', 'Furniture', 'Unit', 5, '2026-02-10 03:24:07', '2026-02-10 03:24:07'),
(15, 'Lemari Arsip', 'Furniture', 'Unit', 3, '2026-02-10 03:24:07', '2026-02-10 03:24:07'),
(16, 'Sabun Cuci Tangan', 'Cleaning', 'Botol', 40, '2026-02-10 03:24:07', '2026-02-10 03:24:07'),
(17, 'Tisu Wajah', 'Cleaning', 'Box', 100, '2026-02-10 03:24:07', '2026-02-10 03:24:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2026_01_14_031237_create_permission_tables', 1),
(6, '2026_01_14_031257_create_branches_table', 1),
(7, '2026_01_14_031300_create_items_table', 1),
(8, '2026_01_14_031301_create_requests_table', 1),
(9, '2026_01_14_031304_create_request_items_table', 1),
(10, '2026_01_14_031306_create_request_approvals_table', 1),
(11, '2026_01_14_031307_add_role_and_branch_to_users_table', 1),
(12, '2026_01_14_041209_create_user_role_logs_table', 1),
(13, '2026_01_14_042842_add_due_date_to_request_items_table', 1),
(14, '2026_01_14_063910_add_nip_to_users_table', 1),
(15, '2026_01_15_020712_modify_request_items_table_for_custom_items', 1),
(16, '2026_01_19_023725_create_notifications_table', 1),
(17, '2026_01_19_024709_add_company_to_users_table', 1),
(18, '2026_01_21_101231_add_category_to_items_table', 1),
(19, '2026_01_21_101235_add_location_type_to_branches_table', 1),
(20, '2026_02_04_085801_add_login_logout_timestamps_to_users_table', 1),
(21, '2026_02_09_090716_add_previous_login_at_to_users_table', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 4),
(2, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 2),
(4, 'App\\Models\\User', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('6592f2a6-30cb-4e99-815a-25e1b155dbdf', 'App\\Notifications\\RequestStatusChanged', 'App\\Models\\User', 3, '{\"request_id\":7,\"request_code\":\"REQ-20260210-3738\",\"status\":\"rejected\",\"rejected_by\":\"ka\",\"reason\":\"stok masih banyak\",\"message\":\"Request REQ-20260210-3738 has been rejected by ka: stok masih banyak\"}', NULL, '2026-02-11 01:52:31', '2026-02-11 01:52:31'),
('87c3ce72-9aa8-4dce-9ccd-e891c39cb7f6', 'App\\Notifications\\RequestStatusChanged', 'App\\Models\\User', 4, '{\"request_id\":7,\"request_code\":\"REQ-20260210-3738\",\"status\":\"rejected\",\"rejected_by\":\"ka\",\"reason\":\"stok masih banyak\",\"message\":\"Request REQ-20260210-3738 has been rejected by ka: stok masih banyak\"}', NULL, '2026-02-11 01:52:31', '2026-02-11 01:52:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'create_requests', 'web', '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(2, 'view_own_requests', 'web', '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(3, 'view_branch_requests', 'web', '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(4, 'approve_spv', 'web', '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(5, 'view_area_requests', 'web', '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(6, 'approve_ka', 'web', '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(7, 'view_all_requests', 'web', '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(8, 'approve_ga', 'web', '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(9, 'manage_master_data', 'web', '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(10, 'export_reports', 'web', '2026-02-10 03:24:05', '2026-02-10 03:24:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `requests`
--

CREATE TABLE `requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `request_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `requests`
--

INSERT INTO `requests` (`id`, `code`, `user_id`, `branch_id`, `request_date`, `status`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(3, 'REQ-20260210-1242', 4, 1, '2026-01-31', 'pending_spv', NULL, '2026-01-31 03:24:07', '2026-01-31 03:24:07'),
(7, 'REQ-20260210-3738', 4, 1, '2026-02-07', 'rejected', 'stok masih banyak', '2026-02-07 03:24:07', '2026-02-11 01:52:30'),
(8, 'REQ-20260210-5017', 4, 1, '2026-01-23', 'pending_ka', NULL, '2026-01-23 03:24:08', '2026-01-23 03:24:08'),
(13, 'REQ-20260210-1706', 4, 1, '2026-01-24', 'pending_ga', NULL, '2026-01-24 03:24:08', '2026-01-24 03:24:08'),
(14, 'REQ-20260210-8553', 4, 1, '2026-01-22', 'pending_ga', NULL, '2026-01-22 03:24:08', '2026-01-22 03:24:08'),
(16, 'REQ-20260210-9850', 4, 1, '2026-01-15', 'approved', NULL, '2026-01-15 03:24:08', '2026-01-15 03:24:08'),
(17, 'REQ-20260210-5881', 4, 1, '2026-01-14', 'approved', NULL, '2026-01-14 03:24:08', '2026-01-14 03:24:08'),
(18, 'REQ-20260210-5070', 4, 1, '2026-02-09', 'approved', NULL, '2026-02-09 03:24:08', '2026-02-09 03:24:08'),
(19, 'REQ-20260210-2861', 4, 1, '2026-01-15', 'rejected', 'Stok tidak tersedia', '2026-01-15 03:24:08', '2026-01-15 03:24:08'),
(20, 'REQ-20260210-6248', 4, 1, '2026-02-09', 'rejected', 'Stok tidak tersedia', '2026-02-09 03:24:08', '2026-02-09 03:24:08'),
(21, 'REQ-202602-0004', 4, 1, '2026-02-11', 'pending_ga', NULL, '2026-02-11 01:49:43', '2026-02-11 01:51:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `request_approvals`
--

CREATE TABLE `request_approvals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `approver_id` bigint(20) UNSIGNED NOT NULL,
  `stage` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `signed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `request_approvals`
--

INSERT INTO `request_approvals` (`id`, `request_id`, `approver_id`, `stage`, `status`, `signed_at`, `created_at`, `updated_at`) VALUES
(2, 7, 3, 'spv', 'approved', '2026-02-07 04:24:07', '2026-02-07 04:24:07', '2026-02-10 03:24:08'),
(3, 8, 3, 'spv', 'approved', '2026-01-23 04:24:08', '2026-01-23 04:24:08', '2026-02-10 03:24:08'),
(10, 13, 3, 'spv', 'approved', '2026-01-24 04:24:08', '2026-01-24 04:24:08', '2026-02-10 03:24:08'),
(11, 13, 2, 'ka', 'approved', '2026-01-24 05:24:08', '2026-01-24 05:24:08', '2026-02-10 03:24:08'),
(12, 14, 3, 'spv', 'approved', '2026-01-22 04:24:08', '2026-01-22 04:24:08', '2026-02-10 03:24:08'),
(13, 14, 2, 'ka', 'approved', '2026-01-22 05:24:08', '2026-01-22 05:24:08', '2026-02-10 03:24:08'),
(16, 16, 3, 'spv', 'approved', '2026-01-15 04:24:08', '2026-01-15 04:24:08', '2026-02-10 03:24:08'),
(17, 16, 2, 'ka', 'approved', '2026-01-15 05:24:08', '2026-01-15 05:24:08', '2026-02-10 03:24:08'),
(18, 16, 1, 'ga', 'approved', '2026-01-15 06:24:08', '2026-01-15 06:24:08', '2026-02-10 03:24:08'),
(19, 17, 3, 'spv', 'approved', '2026-01-14 04:24:08', '2026-01-14 04:24:08', '2026-02-10 03:24:08'),
(20, 17, 2, 'ka', 'approved', '2026-01-14 05:24:08', '2026-01-14 05:24:08', '2026-02-10 03:24:08'),
(21, 17, 1, 'ga', 'approved', '2026-01-14 06:24:08', '2026-01-14 06:24:08', '2026-02-10 03:24:08'),
(22, 18, 3, 'spv', 'approved', '2026-02-09 04:24:08', '2026-02-09 04:24:08', '2026-02-10 03:24:08'),
(23, 18, 2, 'ka', 'approved', '2026-02-09 05:24:08', '2026-02-09 05:24:08', '2026-02-10 03:24:08'),
(24, 18, 1, 'ga', 'approved', '2026-02-09 06:24:08', '2026-02-09 06:24:08', '2026-02-10 03:24:08'),
(25, 19, 3, 'spv', 'approved', '2026-01-15 04:24:08', '2026-01-15 04:24:08', '2026-02-10 03:24:08'),
(26, 19, 2, 'ka', 'rejected', '2026-01-15 05:24:08', '2026-01-15 05:24:08', '2026-02-10 03:24:08'),
(27, 20, 3, 'spv', 'approved', '2026-02-09 04:24:08', '2026-02-09 04:24:08', '2026-02-10 03:24:08'),
(28, 20, 2, 'ka', 'rejected', '2026-02-09 05:24:08', '2026-02-09 05:24:08', '2026-02-10 03:24:08'),
(29, 21, 3, 'spv', 'approved', '2026-02-11 01:51:03', '2026-02-11 01:51:03', '2026-02-11 01:51:03'),
(30, 21, 2, 'ka', 'approved', '2026-02-11 01:51:55', '2026-02-11 01:51:55', '2026-02-11 01:51:55'),
(31, 7, 2, 'ka', 'rejected', '2026-02-11 01:52:30', '2026-02-11 01:52:30', '2026-02-11 01:52:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `request_items`
--

CREATE TABLE `request_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `due_date` date DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `request_items`
--

INSERT INTO `request_items` (`id`, `request_id`, `item_id`, `item_name`, `quantity`, `due_date`, `notes`, `created_at`, `updated_at`) VALUES
(26, 7, 15, 'Lemari Arsip', 5, '2026-02-14', 'Kebutuhan demo', '2026-02-10 03:24:07', '2026-02-10 03:24:07'),
(27, 7, 16, 'Sabun Cuci Tangan', 3, '2026-02-14', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(28, 8, 2, 'Pulpen Hitam', 2, '2026-01-30', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(29, 8, 3, 'Laptop', 5, '2026-01-30', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(45, 13, 2, 'Pulpen Hitam', 4, '2026-01-31', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(46, 13, 9, 'Mouse Wireless', 3, '2026-01-31', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(47, 13, 12, 'Kabel HDMI', 5, '2026-01-31', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(48, 13, 13, 'Kursi Kantor', 1, '2026-01-31', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(49, 13, 17, 'Tisu Wajah', 4, '2026-01-31', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(50, 14, 4, 'Pulpen Biru', 5, '2026-01-29', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(51, 14, 12, 'Kabel HDMI', 1, '2026-01-29', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(57, 16, 6, 'Buku Tulis', 1, '2026-01-22', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(58, 16, 8, 'Laptop Dell', 1, '2026-01-22', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(59, 16, 9, 'Mouse Wireless', 3, '2026-01-22', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(60, 16, 13, 'Kursi Kantor', 2, '2026-01-22', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(61, 16, 14, 'Meja Kerja', 4, '2026-01-22', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(62, 17, 6, 'Buku Tulis', 3, '2026-01-21', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(63, 17, 15, 'Lemari Arsip', 5, '2026-01-21', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(64, 18, 3, 'Laptop', 4, '2026-02-16', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(65, 18, 4, 'Pulpen Biru', 1, '2026-02-16', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(66, 18, 17, 'Tisu Wajah', 5, '2026-02-16', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(67, 19, 5, 'Spidol Board', 4, '2026-01-22', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(68, 19, 6, 'Buku Tulis', 2, '2026-01-22', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(69, 19, 8, 'Laptop Dell', 3, '2026-01-22', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(70, 20, 4, 'Pulpen Biru', 5, '2026-02-16', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(71, 20, 9, 'Mouse Wireless', 1, '2026-02-16', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(72, 20, 15, 'Lemari Arsip', 1, '2026-02-16', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(73, 20, 16, 'Sabun Cuci Tangan', 5, '2026-02-16', 'Kebutuhan demo', '2026-02-10 03:24:08', '2026-02-10 03:24:08'),
(76, 3, 13, 'Kursi Kantor', 3, '2026-02-07', 'Kebutuhan demo', '2026-02-10 03:32:58', '2026-02-10 03:32:58'),
(77, 3, 16, 'Sabun Cuci Tangan', 1, '2026-02-07', 'Kebutuhan demo', '2026-02-10 03:32:58', '2026-02-10 03:32:58'),
(80, 21, 1, 'Kertas A4', 4, '2026-02-13', 'test', '2026-02-11 01:50:55', '2026-02-11 01:50:55'),
(81, 21, 13, 'Kursi Kantor', 3, '2026-02-16', 'test', '2026-02-11 01:50:55', '2026-02-11 01:50:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'user', 'web', '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(2, 'admin_1', 'web', '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(3, 'admin_2', 'web', '2026-02-10 03:24:05', '2026-02-10 03:24:05'),
(4, 'super_admin', 'web', '2026-02-10 03:24:05', '2026-02-10 03:24:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 2),
(4, 2),
(5, 3),
(6, 3),
(7, 4),
(8, 4),
(9, 4),
(10, 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `nip` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `previous_login_at` timestamp NULL DEFAULT NULL,
  `last_logout_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `nip`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `last_login_at`, `previous_login_at`, `last_logout_at`, `branch_id`, `company`, `job_title`) VALUES
(1, 'Procurement/General Affair', '123456', 'ga@example.com', NULL, '$2y$12$Ms.ianRUQoYqZDcMWCONVe7K8mC16Big.8SU0XzZEHDcix.NMGe5u', NULL, '2026-02-10 03:24:05', '2026-02-11 01:53:08', '2026-02-11 01:53:08', '2026-02-11 01:43:19', '2026-02-11 01:43:39', NULL, NULL, 'Procurement'),
(2, 'Kepala Area 1', '8888002', 'ka@example.com', NULL, '$2y$12$SDYtwUZUI.i0LDR26nMpge6exRv.4JqnS2xm8SEV3VVExF7WLy4V2', NULL, '2026-02-10 03:24:06', '2026-02-11 01:52:56', '2026-02-11 01:51:35', NULL, '2026-02-11 01:52:56', NULL, NULL, 'Kepala Area'),
(3, 'SPV Jakarta', '8888003', 'spv@example.com', NULL, '$2y$12$hOEWkA8u2iF7v78Fa7922eEpZPYOJwiXoUvADnestGqx.hoWLzXuy', NULL, '2026-02-10 03:24:06', '2026-02-11 01:51:22', '2026-02-11 01:50:26', '2026-02-11 01:43:52', '2026-02-11 01:51:22', 1, NULL, 'Supervisor'),
(4, 'Staff Jakarta', '8888004', 'staff@example.com', NULL, '$2y$12$SpW1Ha8DuYVurxJFdzFchuD10g0Vr2Gauiwv3raM.MohwoaztWYlm', NULL, '2026-02-10 03:24:06', '2026-02-11 01:50:01', '2026-02-11 01:48:26', '2026-02-10 03:29:42', '2026-02-11 01:50:01', 1, NULL, 'Staff Logistik');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_role_logs`
--

CREATE TABLE `user_role_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `old_role` varchar(255) DEFAULT NULL,
  `new_role` varchar(255) NOT NULL,
  `changed_by` bigint(20) UNSIGNED NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indeks untuk tabel `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indeks untuk tabel `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `requests_code_unique` (`code`),
  ADD KEY `requests_user_id_foreign` (`user_id`),
  ADD KEY `requests_branch_id_foreign` (`branch_id`);

--
-- Indeks untuk tabel `request_approvals`
--
ALTER TABLE `request_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_approvals_request_id_foreign` (`request_id`),
  ADD KEY `request_approvals_approver_id_foreign` (`approver_id`);

--
-- Indeks untuk tabel `request_items`
--
ALTER TABLE `request_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_items_request_id_foreign` (`request_id`),
  ADD KEY `request_items_item_id_foreign` (`item_id`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indeks untuk tabel `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_nip_unique` (`nip`),
  ADD KEY `users_branch_id_foreign` (`branch_id`);

--
-- Indeks untuk tabel `user_role_logs`
--
ALTER TABLE `user_role_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_role_logs_user_id_foreign` (`user_id`),
  ADD KEY `user_role_logs_changed_by_foreign` (`changed_by`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `requests`
--
ALTER TABLE `requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `request_approvals`
--
ALTER TABLE `request_approvals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `request_items`
--
ALTER TABLE `request_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `user_role_logs`
--
ALTER TABLE `user_role_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `request_approvals`
--
ALTER TABLE `request_approvals`
  ADD CONSTRAINT `request_approvals_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `request_approvals_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `request_items`
--
ALTER TABLE `request_items`
  ADD CONSTRAINT `request_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `request_items_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Ketidakleluasaan untuk tabel `user_role_logs`
--
ALTER TABLE `user_role_logs`
  ADD CONSTRAINT `user_role_logs_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_role_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
