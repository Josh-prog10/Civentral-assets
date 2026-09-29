-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 15, 2026 at 02:44 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `civentral_local`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `plate_number` varchar(50) DEFAULT NULL,
  `location_text` varchar(255) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `condition` enum('good','fair','poor','damaged','under_repair') DEFAULT 'good',
  `acquisition_date` date DEFAULT NULL,
  `lifecycle_status` enum('active','retired','disposed') DEFAULT 'active',
  `last_maintenance_date` date DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL,
  `maintenance_interval_months` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL COMMENT 'ID from remote employee API',
  `updated_by` int(11) NOT NULL COMMENT 'ID from remote employee API',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `category_id`, `name`, `description`, `plate_number`, `location_text`, `latitude`, `longitude`, `condition`, `acquisition_date`, `lifecycle_status`, `last_maintenance_date`, `next_maintenance_date`, `maintenance_interval_months`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 2, 'Dell Latitude 5420 #001', 'Office laptop assigned to the Administrative Office', '', 'City Hall, Administrative Office, Room 201', 14.65070000, 120.98300000, 'good', '2024-06-15', 'active', '2026-06-10', '2026-12-10', 6, 'Assigned to the Administrative Office for daily office operations.', 23, 23, '2026-09-01 12:07:56', '2026-09-01 12:07:56'),
(2, 5, 'Caloocan City Hall – South', 'Government administrative building used for city government offices and public services', '', 'Caloocan City Hall, South Caloocan', 14.65070000, 120.98300000, 'good', '2018-01-15', 'active', '2026-06-15', '2026-12-15', 6, 'Main government facility serving administrative and public-service functions.', 23, 23, '2026-09-01 12:38:28', '2026-09-10 10:38:25'),
(3, 2, 'Dell OptiPlex Desktop – IT-001', 'Desktop computer used for administrative and records processing', '', 'Caloocan City Hall – Administrative Office', 14.65070000, 120.98300000, 'good', '2024-06-15', 'active', '2026-06-15', '2026-12-15', 6, 'Assigned to city government personnel for daily office operations.', 23, 23, '2026-09-01 12:41:57', '2026-09-01 12:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `asset_categories`
--

CREATE TABLE `asset_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fa-cube',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `field_config` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asset_categories`
--

INSERT INTO `asset_categories` (`id`, `name`, `description`, `icon`, `created_at`, `updated_at`, `field_config`) VALUES
(1, 'Vehicles', 'Municipal vehicles, trucks, service vans, etc.', 'fa-solid fa-truck', '2026-09-01 11:32:57', '2026-09-13 13:28:15', '{\"plate_number\":true,\"coordinates\":true,\"gis_map\":true,\"maintenance\":true,\"acquisition_date\":true,\"notes\":true}'),
(2, 'IT Equipment', 'Computers, servers, printers, networking devices, etc.', 'fa-laptop', '2026-09-01 11:32:57', '2026-09-01 11:32:57', NULL),
(3, 'Office Furniture', 'Desks, chairs, filing cabinets, bookshelves, etc.', 'fa-chair', '2026-09-01 11:32:57', '2026-09-01 11:32:57', NULL),
(4, 'Tools & Equipment', 'Hand tools, power tools, maintenance equipment, etc.', 'fa-tools', '2026-09-01 11:32:57', '2026-09-01 11:32:57', NULL),
(5, 'Building & Infrastructure', 'Buildings, pavements, street lighting, etc.', 'fa-solid fa-building', '2026-09-01 11:32:57', '2026-09-13 13:27:19', '{\"plate_number\":false,\"coordinates\":true,\"gis_map\":true,\"maintenance\":true,\"acquisition_date\":true,\"notes\":true}');

-- --------------------------------------------------------

--
-- Table structure for table `asset_maintenance_logs`
--

CREATE TABLE `asset_maintenance_logs` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `maintenance_date` date NOT NULL,
  `description` text NOT NULL,
  `performed_by` varchar(255) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cemeteries`
--

CREATE TABLE `cemeteries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cemeteries`
--

INSERT INTO `cemeteries` (`id`, `name`, `description`, `address`, `latitude`, `longitude`, `created_at`) VALUES
(2, 'Sangandaan Public Cemetery', 'Government-managed public cemetery serving residents of South Caloocan and providing burial and interment facilities.', 'Sangandaan, Brgy. 4, South Caloocan', 14.65954000, 120.96986000, '2026-09-01 11:43:23'),
(3, 'Bagbaguin Cemetery', 'Government-managed public cemetery serving the northern portion of Caloocan City and providing burial and interment facilities.', 'Bagbaguin, Brgy. 166, North Caloocan', 14.71879000, 121.00249000, '2026-09-01 11:47:47'),
(4, 'Bagong Silang Cemetery / Tala Cemetery', 'Government-managed public cemetery serving residents of North Caloocan, particularly the Bagong Silang area.', 'Bagong Silang, Brgy. 176, North Caloocan', 14.77621000, 121.05560000, '2026-09-01 11:50:29');

-- --------------------------------------------------------

--
-- Table structure for table `cemetery_burials`
--

CREATE TABLE `cemetery_burials` (
  `id` int(11) NOT NULL,
  `lot_id` int(11) NOT NULL,
  `deceased_id` int(11) NOT NULL,
  `burial_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cemetery_burials`
--

INSERT INTO `cemetery_burials` (`id`, `lot_id`, `deceased_id`, `burial_date`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-09-22', '', '2026-09-15 06:39:49', '2026-09-15 06:39:49');

-- --------------------------------------------------------

--
-- Table structure for table `cemetery_deceased`
--

CREATE TABLE `cemetery_deceased` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `date_of_death` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cemetery_deceased`
--

INSERT INTO `cemetery_deceased` (`id`, `first_name`, `last_name`, `date_of_birth`, `date_of_death`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Alexander', 'David', '1979-03-15', '2026-09-11', '', '2026-09-15 06:37:45', '2026-09-15 06:37:45');

-- --------------------------------------------------------

--
-- Table structure for table `cemetery_lots`
--

CREATE TABLE `cemetery_lots` (
  `id` int(11) NOT NULL,
  `section` varchar(50) NOT NULL,
  `row_num` varchar(50) NOT NULL,
  `lot_number` varchar(50) NOT NULL,
  `status` enum('available','reserved','occupied') DEFAULT 'available',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cemetery_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cemetery_lots`
--

INSERT INTO `cemetery_lots` (`id`, `section`, `row_num`, `lot_number`, `status`, `notes`, `created_at`, `updated_at`, `cemetery_id`) VALUES
(1, 'A', '01', 'A-01-001', 'occupied', '', '2026-09-15 05:23:38', '2026-09-15 06:39:49', 4);

-- --------------------------------------------------------

--
-- Table structure for table `lgu_facilities`
--

CREATE TABLE `lgu_facilities` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('covered_court','function_hall','barangay_hall','conference_room','auditorium','other') DEFAULT 'other',
  `description` text DEFAULT NULL,
  `capacity` int(11) DEFAULT 0,
  `location` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lgu_facilities`
--

INSERT INTO `lgu_facilities` (`id`, `name`, `type`, `description`, `capacity`, `location`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Barangay 176 Covered Court', 'covered_court', 'Multipurpose covered court used for sports activities, community programs, and barangay events.', 500, 'Barangay 176, Bagong Silang, Caloocan City', 'active', '2026-09-01 13:34:34', '2026-09-01 13:34:34');

-- --------------------------------------------------------

--
-- Table structure for table `lgu_reservations`
--

CREATE TABLE `lgu_reservations` (
  `id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID from remote employee API',
  `purpose` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('pending','approved','rejected','cancelled','completed') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_notes` text DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `cancelled_reason` enum('manual','expired') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_name` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lgu_reservations`
--

INSERT INTO `lgu_reservations` (`id`, `facility_id`, `user_id`, `purpose`, `description`, `start_datetime`, `end_datetime`, `status`, `payment_status`, `payment_amount`, `payment_method`, `payment_reference`, `payment_notes`, `receipt_number`, `paid_at`, `cancelled_reason`, `created_at`, `updated_at`, `user_name`) VALUES
(11, 1, 23, 'Birthday', 'asdsadasdsadadasd', '2026-09-15 13:20:00', '2026-09-15 13:25:00', 'completed', 'paid', 1200.00, 'cash', '', NULL, 'RCPT-20260915-C61B65', '2026-09-15 13:18:28', NULL, '2026-09-15 05:16:29', '2026-09-15 12:41:20', 'Renz Dela Cruz'),
(12, 1, 23, 'Meeting', '', '2026-09-15 13:26:00', '2026-09-15 13:30:00', 'cancelled', 'unpaid', 0.00, NULL, NULL, NULL, NULL, NULL, 'expired', '2026-09-15 05:17:23', '2026-09-15 12:41:20', 'Renz Dela Cruz');

-- --------------------------------------------------------

--
-- Table structure for table `park_facilities`
--

CREATE TABLE `park_facilities` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `capacity` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `park_facilities`
--

INSERT INTO `park_facilities` (`id`, `name`, `description`, `capacity`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Caloocan People\'s Park', 'Public park with playground, open spaces, and interactive fountain', 500, 'active', '2026-09-15 06:44:58', '2026-09-15 06:44:58');

-- --------------------------------------------------------

--
-- Table structure for table `park_reservations`
--

CREATE TABLE `park_reservations` (
  `id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID from remote employee API',
  `user_name` varchar(255) NOT NULL DEFAULT '',
  `event_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('pending','approved','cancelled','rejected','completed') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_notes` text DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `cancelled_reason` enum('manual','expired') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `park_reservations`
--

INSERT INTO `park_reservations` (`id`, `facility_id`, `user_id`, `user_name`, `event_name`, `description`, `start_datetime`, `end_datetime`, `status`, `payment_status`, `payment_amount`, `payment_method`, `payment_reference`, `payment_notes`, `receipt_number`, `paid_at`, `cancelled_reason`, `created_at`, `updated_at`) VALUES
(1, 1, 23, 'Renz Dela Cruz', 'Kasalang Bayan', '', '2026-09-15 14:50:00', '2026-09-15 14:55:00', 'completed', 'paid', 1200.00, 'cash', '', NULL, 'PRCPT-20260915-F6AA31', '2026-09-15 14:48:50', NULL, '2026-09-15 06:47:37', '2026-09-15 12:39:22');

-- --------------------------------------------------------

--
-- Table structure for table `wd_requests`
--

CREATE TABLE `wd_requests` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID from remote employee API',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location_text` varchar(255) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('pending','assigned','in_progress','resolved','rejected') DEFAULT 'pending',
  `assigned_to` int(11) DEFAULT NULL COMMENT 'ID from remote employee API',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wd_requests`
--

INSERT INTO `wd_requests` (`id`, `category_id`, `user_id`, `title`, `description`, `location_text`, `latitude`, `longitude`, `status`, `assigned_to`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 11, 23, 'adasdasd', 'asdsadsadasda', '8th Street corner 8th Avenue, South Caloocan City', 14.59878389, 120.98472834, 'pending', NULL, NULL, '2026-09-01 13:19:23', '2026-09-01 13:19:23'),
(2, 2, 23, 'asdasdsadsa', 'dsadsadsadasd', '8th Street corner 8th Avenue, South Caloocan City', 14.59950000, 120.98420000, 'pending', NULL, NULL, '2026-09-01 13:23:03', '2026-09-01 13:23:03');

-- --------------------------------------------------------

--
-- Table structure for table `wd_request_attachments`
--

CREATE TABLE `wd_request_attachments` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size` int(11) NOT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wd_request_attachments`
--

INSERT INTO `wd_request_attachments` (`id`, `request_id`, `filename`, `original_name`, `file_path`, `mime_type`, `size`, `uploaded_at`) VALUES
(1, 2, '05183e70dbba9c76d97621f115c50f4d.jpg', 'download (1).jpg', 'uploads/water/05183e70dbba9c76d97621f115c50f4d.jpg', 'image/jpeg', 126515, '2026-09-01 21:23:03');

-- --------------------------------------------------------

--
-- Table structure for table `wd_request_categories`
--

CREATE TABLE `wd_request_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wd_request_categories`
--

INSERT INTO `wd_request_categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Water Supply Issue', 'Problems with water pressure, no water supply, broken pipes, etc.', '2026-09-01 11:32:57', '2026-09-01 11:32:57'),
(2, 'Drainage Clog', 'Clogged drains, flooding, sewage backup, etc.', '2026-09-01 11:32:57', '2026-09-01 11:32:57'),
(3, 'Water Quality Concern', 'Discolored water, foul odor, or contamination reports.', '2026-09-01 11:32:57', '2026-09-01 11:32:57'),
(4, 'Irrigation System', 'Issues with public irrigation or watering systems.', '2026-09-01 11:32:57', '2026-09-01 11:32:57'),
(5, 'Water Leakage', '', '2026-09-01 13:06:38', '2026-09-01 13:06:38'),
(6, 'Low Water Pressure', '', '2026-09-01 13:06:38', '2026-09-01 13:06:38'),
(7, 'Water Connection Request', '', '2026-09-01 13:06:38', '2026-09-01 13:06:38'),
(8, 'Water Connection Repair', '', '2026-09-01 13:06:38', '2026-09-01 13:06:38'),
(9, 'Drainage Damage', '', '2026-09-01 13:06:38', '2026-09-01 13:06:38'),
(10, 'Drainage Overflow', '', '2026-09-01 13:06:38', '2026-09-01 13:06:38'),
(11, 'Flooding / Water Accumulation', '', '2026-09-01 13:06:38', '2026-09-01 13:06:38'),
(12, 'Sewerage / Wastewater Concern', '', '2026-09-01 13:06:38', '2026-09-01 13:06:38'),
(13, 'Drainage Maintenance', '', '2026-09-01 13:06:38', '2026-09-01 13:06:38'),
(14, 'Water Infrastructure Damage', '', '2026-09-01 13:06:38', '2026-09-01 13:06:38'),
(15, 'Other Water/Drainage Concern', '', '2026-09-01 13:06:38', '2026-09-01 13:06:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_updated_by` (`updated_by`);

--
-- Indexes for table `asset_categories`
--
ALTER TABLE `asset_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `asset_maintenance_logs`
--
ALTER TABLE `asset_maintenance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_id` (`asset_id`);

--
-- Indexes for table `cemeteries`
--
ALTER TABLE `cemeteries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cemetery_burials`
--
ALTER TABLE `cemetery_burials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lot_id` (`lot_id`),
  ADD KEY `deceased_id` (`deceased_id`);

--
-- Indexes for table `cemetery_deceased`
--
ALTER TABLE `cemetery_deceased`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cemetery_lots`
--
ALTER TABLE `cemetery_lots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_lot` (`section`,`row_num`,`lot_number`),
  ADD KEY `cemetery_id` (`cemetery_id`);

--
-- Indexes for table `lgu_facilities`
--
ALTER TABLE `lgu_facilities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lgu_reservations`
--
ALTER TABLE `lgu_reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_receipt_number` (`receipt_number`),
  ADD KEY `facility_id` (`facility_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status_start` (`status`,`start_datetime`);

--
-- Indexes for table `park_facilities`
--
ALTER TABLE `park_facilities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `park_reservations`
--
ALTER TABLE `park_reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_park_receipt_number` (`receipt_number`),
  ADD KEY `facility_id` (`facility_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status_start` (`status`,`start_datetime`);

--
-- Indexes for table `wd_requests`
--
ALTER TABLE `wd_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_assigned_to` (`assigned_to`);

--
-- Indexes for table `wd_request_attachments`
--
ALTER TABLE `wd_request_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `wd_request_categories`
--
ALTER TABLE `wd_request_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `asset_categories`
--
ALTER TABLE `asset_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `asset_maintenance_logs`
--
ALTER TABLE `asset_maintenance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cemeteries`
--
ALTER TABLE `cemeteries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cemetery_burials`
--
ALTER TABLE `cemetery_burials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cemetery_deceased`
--
ALTER TABLE `cemetery_deceased`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cemetery_lots`
--
ALTER TABLE `cemetery_lots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lgu_facilities`
--
ALTER TABLE `lgu_facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lgu_reservations`
--
ALTER TABLE `lgu_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `park_facilities`
--
ALTER TABLE `park_facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `park_reservations`
--
ALTER TABLE `park_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wd_requests`
--
ALTER TABLE `wd_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wd_request_attachments`
--
ALTER TABLE `wd_request_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wd_request_categories`
--
ALTER TABLE `wd_request_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `asset_maintenance_logs`
--
ALTER TABLE `asset_maintenance_logs`
  ADD CONSTRAINT `asset_maintenance_logs_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cemetery_burials`
--
ALTER TABLE `cemetery_burials`
  ADD CONSTRAINT `cemetery_burials_ibfk_1` FOREIGN KEY (`lot_id`) REFERENCES `cemetery_lots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cemetery_burials_ibfk_2` FOREIGN KEY (`deceased_id`) REFERENCES `cemetery_deceased` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cemetery_lots`
--
ALTER TABLE `cemetery_lots`
  ADD CONSTRAINT `cemetery_lots_ibfk_1` FOREIGN KEY (`cemetery_id`) REFERENCES `cemeteries` (`id`);

--
-- Constraints for table `lgu_reservations`
--
ALTER TABLE `lgu_reservations`
  ADD CONSTRAINT `lgu_reservations_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `lgu_facilities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `park_reservations`
--
ALTER TABLE `park_reservations`
  ADD CONSTRAINT `park_reservations_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `park_facilities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wd_requests`
--
ALTER TABLE `wd_requests`
  ADD CONSTRAINT `wd_requests_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `wd_request_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wd_request_attachments`
--
ALTER TABLE `wd_request_attachments`
  ADD CONSTRAINT `wd_request_attachments_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `wd_requests` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
