-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Mar 19, 2026 at 05:03 AM
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
-- Database: `subdivision_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(100) DEFAULT NULL,
  `authority_level` enum('Master','Admin') DEFAULT 'Admin',
  `auth_key` varchar(255) NOT NULL,
  `admin_status` varchar(20) NOT NULL DEFAULT 'Active',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE admin_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NULL,
    action_type VARCHAR(50),
    details TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `admin_name`, `authority_level`, `auth_key`, `admin_status`, `updated_at`) VALUES
(1, 'Kervie Balolong', 'Master', 'delete123', 'Active', '2026-03-09 03:58:47');

-- --------------------------------------------------------

--
-- Table structure for table `residents`
--

CREATE TABLE `residents` (
  `resident_id` int(11) NOT NULL,
  `subdivision_id` int(11) DEFAULT NULL,
  `phase` varchar(50) DEFAULT NULL,
  `block_no` varchar(50) DEFAULT NULL,
  `lot_no` varchar(50) DEFAULT NULL,
  `tct_no` varchar(100) DEFAULT NULL,
  `buyer_name` varchar(255) DEFAULT NULL,
  `new_buyer_assumed` varchar(255) DEFAULT NULL,
  `buyer_representative` varchar(255) DEFAULT NULL,
  `contact_no` varchar(50) DEFAULT NULL,
  `social_media` varchar(255) DEFAULT NULL,
  `email_address` varchar(255) DEFAULT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `account_address` text DEFAULT NULL,
  `resident_status` enum('Active','Inactive','Moved Out') DEFAULT 'Active',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`resident_id`, `subdivision_id`, `phase`, `block_no`, `lot_no`, `tct_no`, `buyer_name`, `new_buyer_assumed`, `buyer_representative`, `contact_no`, `social_media`, `email_address`, `account_number`, `account_address`, `resident_status`, `remarks`, `created_at`) VALUES
(2, 2, 'Phase 1', '18', '63', 'TCT-TEST-001', 'Sample Resident', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Active', NULL, '2026-03-18 02:52:59'),
(3, 2, 'phase 1', '18', '62', '', 'Andress Jose Bonifacio', '', '', '0917-123-4567', '', 'andressjose@example.com', 'ACC-IM-2026-001', '', 'Inactive', '', '2026-03-17 16:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `subdivisions`
--

CREATE TABLE `subdivisions` (
  `subdivision_id` int(11) NOT NULL,
  `project_name` varchar(100) NOT NULL,
  `map_path` varchar(255) DEFAULT NULL,
  `map_width` int(11) DEFAULT 2000,
  `map_height` int(11) DEFAULT 1500
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subdivisions`
--

INSERT INTO `subdivisions` (`subdivision_id`, `project_name`, `map_path`, `map_width`, `map_height`) VALUES
(2, 'PADRE GARCIA', 'subdivision1.png', 2000, 1500),
(3, 'VIA VERDE STO. TOMAS BATANGAS', 'VVST3-SDP_page-0001.jpg', 2000, 1500),
(4, 'Imperial Meadows', 'ISM SITE MAP.jpg', 2000, 1500),
(5, 'Brgy. Tartaria', 'Silang Cavite.jpg', 2000, 1500),
(6, 'Rancho Imperial', 'Rancho imperial de Silang-Model with color.jpg', 2000, 1500),
(7, 'Tagaytay Meridien', 'Tagaytay Meridien map 1.jpg', 2000, 1500),
(8, 'The Venetto Heights', 'The-Venetto-Heights-Updated-2014-Model.jpg', 2000, 1500),
(9, 'Trece Martires', 'W-Trece Martires.jpg', 2000, 1500),
(11, 'Priya Meridian', 'Priya Meridian.jpg', 2000, 1500),
(12, 'Brgy. STO.Domingo', 'BrgySTO.Domingo,IrigaCity.jpg', 2000, 1500),
(13, 'Brgy. Estanza', 'BRGY. ESTANZA LEGAZPI CITY.jpg', 2000, 1500),
(14, 'Homapon Legazpi City', 'HOMAPON LEGAZPI CITY.jpg', 2000, 1500),
(15, 'VHS PH 2', 'VHS PH 2.JPG', 2000, 1500),
(16, 'Sorsogon', 'Sorsogon - with alteration_page-0001.jpg', 2000, 1500),
(17, 'Buragwis', 'Buragwis_page-0001.jpg', 2000, 1500),
(18, 'Estanza PH 1 & 2', 'Estanza ph 1 & 2_page-0001.jpg', 2000, 1500),
(19, 'Estanza Phase 1', 'Estanza Phase 1_page-0001.jpg', 2000, 1500),
(20, 'Iriga Phase 1', 'Iriga Phase 1_page-0001.jpg', 2000, 1500),
(21, 'Labo', 'Labo_page-0001.jpg', 2000, 1500),
(22, 'LeGrand 1 & 2', 'LeGrand 1 & 2_page-0001.jpg', 2000, 1500),
(23, 'OLV Buragwis', 'OLV Buragwis_page-0001.jpg', 2000, 1500),
(24, 'Polangui', 'Polangui_page-0001.jpg', 2000, 1500),
(25, 'San Fernando', 'San Fernando_page-0001.jpg', 2000, 1500);

-- --------------------------------------------------------

--
-- Table structure for table `utility_bills`
--

CREATE TABLE `utility_bills` (
  `bill_id` int(11) NOT NULL,
  `resident_id` int(11) DEFAULT NULL,
  `electric_provider` varchar(100) DEFAULT NULL,
  `water_provider` varchar(100) DEFAULT NULL,
  `bill_date` date DEFAULT NULL,
  `billing_period` varchar(100) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `prev_reading` decimal(10,2) DEFAULT 0.00,
  `present_reading` decimal(10,2) DEFAULT 0.00,
  `consumption` decimal(10,2) DEFAULT 0.00,
  `cost_per_cubic_meter` decimal(10,2) DEFAULT 0.00,
  `current_bill` decimal(10,2) DEFAULT 0.00,
  `previous_bill_balance` decimal(10,2) DEFAULT 0.00,
  `total_bill` decimal(10,2) DEFAULT 0.00,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `remaining_balance` decimal(10,2) DEFAULT 0.00,
  `bill_status` enum('Paid','Unpaid','Overdue','Pending') DEFAULT 'Unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `admin_id` (`admin_id`);

--
-- Indexes for table `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`resident_id`);

--
-- Indexes for table `subdivisions`
--
ALTER TABLE `subdivisions`
  ADD PRIMARY KEY (`subdivision_id`);

--
-- Indexes for table `utility_bills`
--
ALTER TABLE `utility_bills`
  ADD PRIMARY KEY (`bill_id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `resident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subdivisions`
--
ALTER TABLE `subdivisions`
  MODIFY `subdivision_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `utility_bills`
--
ALTER TABLE `utility_bills`
  MODIFY `bill_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `utility_bills`
--
ALTER TABLE `utility_bills`
  ADD CONSTRAINT `utility_bills_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

