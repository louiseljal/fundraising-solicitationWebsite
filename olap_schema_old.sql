-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 14, 2026 at 04:44 PM
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
-- Database: `olap_schema`
--

-- --------------------------------------------------------

--
-- Table structure for table `dim_campaign`
--

CREATE TABLE `dim_campaign` (
  `campaign_sk` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `category` varchar(100) NOT NULL,
  `goal_amount` decimal(12,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dim_campaign`
--

INSERT INTO `dim_campaign` (`campaign_sk`, `campaign_id`, `title`, `category`, `goal_amount`, `start_date`, `end_date`, `status`) VALUES
(1, 1, 'Typhoon Relief Drive 2026', 'Disaster Relief', 500000.00, '2026-05-01', '2026-06-01', 'Active'),
(2, 2, 'Juan’s Medical & Chemotherapy Fund', 'Medical', 300000.00, '2026-04-15', '2026-07-15', 'Active'),
(3, 3, 'Public School Books & Laptops Project', 'Education', 150000.00, '2026-05-10', '2026-08-10', 'Active'),
(4, 4, 'Salamat Paw-Pals Animal Shelter Expansion', 'Animal Welfare', 80000.00, '2026-05-20', '2026-06-20', 'Active'),
(5, 5, 'Community Kitchen Clean Water Project', 'Community', 100000.00, '2026-06-01', '2026-09-01', 'Draft'),
(6, 6, 'Reforestation in Sierra Madre', 'Environment', 120000.00, '2026-03-01', '2026-09-01', 'Paused'),
(7, 7, 'Surgical Fund for Baby Neo', 'Medical', 400000.00, '2026-05-22', '2026-06-22', 'Active'),
(8, 8, 'Scholars Across Borders 2026', 'Education', 250000.00, '2026-05-01', '2026-12-31', 'Active'),
(9, 9, 'Artists Support Group Grant', 'Arts & Culture', 50000.00, '2026-01-01', '2026-04-01', 'Completed'),
(10, 10, 'Bike-for-a-Cause Metro Manila', 'Community', 90000.00, '2026-02-01', '2026-03-01', 'Cancelled'),
(11, 11, 'Mangrove Planting Drive Bulacan', 'Environment', 75000.00, '2026-06-01', '2026-08-01', 'Active'),
(12, 12, 'Scholarship Fund for Tech Students', 'Education', 200000.00, '2026-05-15', '2026-09-15', 'Active'),
(13, 13, 'Barangay Health Center Renovation', 'Medical', 180000.00, '2026-07-01', '2026-10-01', 'Active'),
(14, 14, 'Sulu Community Library Project', 'Education', 120000.00, '2026-01-10', '2026-05-10', 'Active'),
(15, 15, 'Marikina Flood Relief Phase 2', 'Disaster Relief', 150000.00, '2026-02-15', '2026-03-15', 'Cancelled'),
(16, 16, 'Indigenous Weavers Preservation Grant', 'Arts & Culture', 95000.00, '2026-03-01', '2026-09-01', 'Paused'),
(17, 17, 'Solar Panels for Remote Villages', 'Community', 350000.00, '2026-06-05', '2026-11-05', 'Active'),
(18, 18, 'Youth Sports Equipment Drive', 'Community', 40000.00, '2026-03-15', '2026-05-15', 'Active'),
(19, 19, 'Stray Cat Trap-Neuter-Return Programwwww', 'Arts &amp; Culture', 60000.00, '2026-06-11', '2026-08-11', 'Paused'),
(20, 20, 'Local Music Festival & Arts Relief', 'Arts & Culture', 110000.00, '2026-04-01', '2026-08-01', 'Active'),
(21, 21, 'Scholarship Fund 2026', 'Education', 5000.00, '2026-06-15', '2026-12-15', 'Active'),
(22, 22, 'Clean Water Initiative', 'Community', 8000.00, '2026-06-16', '2026-12-16', 'Active'),
(23, 23, 'Urban Gardening Project', 'Environment', 2000.00, '2026-06-17', '2026-12-17', 'Active'),
(24, 24, 'Tech for Students', 'Education', 4500.00, '2026-06-18', '2026-12-18', 'Active'),
(25, 25, 'Local Library Renovation', 'Community', 3000.00, '2026-06-19', '2026-12-19', 'Active'),
(26, 26, 'Art supplies for kids', 'Arts & Culture', 1500.00, '2026-06-20', '2026-12-20', 'Active'),
(27, 27, 'Shelter Expansion', 'Community', 12000.00, '2026-06-21', '2026-12-21', 'Active'),
(28, 28, 'Disaster Relief Fund', 'Disaster Relief', 15000.00, '2026-06-22', '2026-12-22', 'Active'),
(29, 29, 'Medical Aid for Elders', 'Medical', 6000.00, '2026-06-23', '2026-12-23', 'Active'),
(30, 30, 'Community Park Cleanup', 'Environment', 1000.00, '2026-06-24', '2026-12-24', 'Active'),
(31, 31, 'Coding Bootcamp', 'Education', 3500.00, '2026-06-25', '2026-12-25', 'Active'),
(32, 32, 'Youth Sports League', 'Community', 2500.00, '2026-06-26', '2026-12-26', 'Active'),
(33, 33, 'Animal Rescue Shelter', 'Animal Welfare', 4000.00, '2026-06-27', '2026-12-27', 'Active'),
(34, 34, 'Senior Nutrition Program', 'Community', 5500.00, '2026-06-28', '2026-12-28', 'Active'),
(35, 35, 'Winter Clothing Drive', 'Disaster Relief', 2000.00, '2026-06-29', '2026-12-29', 'Active'),
(36, 36, 'Upcoming Health Seminar', 'Medical', 500.00, '2026-07-01', '2026-07-02', 'Draft'),
(37, 37, 'New Science Lab Build', 'Education', 9000.00, '2026-07-03', '2026-07-04', 'Active'),
(38, 38, 'Historical Archival Project', 'Arts & Culture', 3000.00, '2026-07-05', '2026-07-06', 'Cancelled'),
(39, 39, 'Community Solar Project', 'Environment', 7500.00, '2026-07-08', '2026-07-09', 'Active'),
(40, 40, 'Holiday Food Bank', 'Community', 4000.00, '2026-07-10', '2026-07-11', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `dim_donor`
--

CREATE TABLE `dim_donor` (
  `donor_sk` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `region_state` varchar(100) DEFAULT NULL,
  `user_role` varchar(50) DEFAULT NULL,
  `joined_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dim_donor`
--

INSERT INTO `dim_donor` (`donor_sk`, `user_id`, `username`, `full_name`, `region_state`, `user_role`, `joined_date`) VALUES
(1, 1, 'admin_miko', 'Miko Alvarez', 'NCR', 'Admin', '2026-05-25'),
(2, 2, 'juan_delacruz', 'Juan Dela Cruz', 'Calabarzon', 'Admin', '2026-05-25'),
(3, 3, 'maria_santos', 'Maria Santos', 'NCR', 'Donor', '2026-05-25'),
(4, 4, 'tech_support', 'Alex Tech', 'Central Luzon', 'Admin', '2026-05-25'),
(5, 5, 'elena_reyes', 'Elena Reyes', 'Central Visayas', 'Donor', '2026-05-25'),
(6, 6, 'brian_tan', 'Brian Tan', 'NCR', 'Donor', '2026-05-25'),
(7, 7, 'ana_gomez', 'Ana Gomez', 'Davao Region', 'Donor', '2026-05-25'),
(8, 8, 'david_lim', 'David Lim', 'Calabarzon', 'Donor', '2026-05-25'),
(9, 9, 'grace_pua', 'Grace Pua', 'Western Visayas', 'Donor', '2026-05-25'),
(10, 10, 'rachel_uy', 'Rachel Uy', 'NCR', 'Donor', '2026-05-25'),
(11, 11, 'louiseledesma', 'louise ledesma', NULL, 'Admin', '2026-06-12'),
(12, 12, 'dennis_103', 'dennis dennis', NULL, 'Admin', '2026-06-13'),
(13, 13, 'dennisuser', 'dennisuser userrr', NULL, 'Donor', '2026-06-13'),
(14, 14, 'mikeross', 'Mike Ross', NULL, 'Donor', '2026-06-13');

-- --------------------------------------------------------

--
-- Table structure for table `dim_payment_method`
--

CREATE TABLE `dim_payment_method` (
  `payment_method_id` int(11) NOT NULL,
  `method_name` varchar(50) NOT NULL,
  `method_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dim_payment_method`
--

INSERT INTO `dim_payment_method` (`payment_method_id`, `method_name`, `method_type`) VALUES
(1, 'Credit_Card', 'Online'),
(2, 'PayPal', 'Online'),
(3, 'G_Cash', 'Online'),
(4, 'Bank_Transfer', 'Online'),
(5, 'Cash', 'Offline'),
(6, 'Check', 'Offline'),
(7, 'Manual', 'Offline');

-- --------------------------------------------------------

--
-- Table structure for table `dim_time`
--

CREATE TABLE `dim_time` (
  `time_id` int(11) NOT NULL,
  `full_date` date NOT NULL,
  `day_of_month` tinyint(4) NOT NULL,
  `day_name` varchar(10) NOT NULL,
  `month_num` tinyint(4) NOT NULL,
  `month_name` varchar(10) NOT NULL,
  `quarter` tinyint(4) NOT NULL,
  `year` year(4) NOT NULL,
  `is_weekend` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dim_time`
--

INSERT INTO `dim_time` (`time_id`, `full_date`, `day_of_month`, `day_name`, `month_num`, `month_name`, `quarter`, `year`, `is_weekend`) VALUES
(1, '2026-05-25', 25, 'Monday', 5, 'May', 2, '2026', 0),
(2, '2026-06-12', 12, 'Friday', 6, 'June', 2, '2026', 0),
(3, '2026-06-01', 1, 'Monday', 6, 'June', 2, '2026', 0),
(4, '2026-06-02', 2, 'Tuesday', 6, 'June', 2, '2026', 0),
(5, '2026-06-05', 5, 'Friday', 6, 'June', 2, '2026', 0),
(6, '2026-06-07', 7, 'Sunday', 6, 'June', 2, '2026', 1),
(7, '2026-06-08', 8, 'Monday', 6, 'June', 2, '2026', 0),
(8, '2026-06-10', 10, 'Wednesday', 6, 'June', 2, '2026', 0),
(9, '2026-06-11', 11, 'Thursday', 6, 'June', 2, '2026', 0),
(10, '2026-06-13', 13, 'Saturday', 6, 'June', 2, '2026', 1),
(11, '2025-11-12', 12, 'Wednesday', 11, 'November', 4, '2025', 0),
(12, '2025-12-25', 25, 'Thursday', 12, 'December', 4, '2025', 0),
(13, '2026-01-05', 5, 'Monday', 1, 'January', 1, '2026', 0),
(14, '2026-02-14', 14, 'Saturday', 2, 'February', 1, '2026', 1),
(15, '2026-03-20', 20, 'Friday', 3, 'March', 1, '2026', 0),
(16, '2026-04-01', 1, 'Wednesday', 4, 'April', 2, '2026', 0),
(17, '2026-05-18', 18, 'Monday', 5, 'May', 2, '2026', 0),
(18, '2026-06-15', 15, 'Monday', 6, 'June', 2, '2026', 0),
(19, '2026-06-16', 16, 'Tuesday', 6, 'June', 2, '2026', 0),
(20, '2026-06-17', 17, 'Wednesday', 6, 'June', 2, '2026', 0),
(21, '2026-06-18', 18, 'Thursday', 6, 'June', 2, '2026', 0),
(22, '2026-06-19', 19, 'Friday', 6, 'June', 2, '2026', 0),
(23, '2026-06-20', 20, 'Saturday', 6, 'June', 2, '2026', 1),
(24, '2026-06-21', 21, 'Sunday', 6, 'June', 2, '2026', 1),
(25, '2026-06-22', 22, 'Monday', 6, 'June', 2, '2026', 0),
(26, '2026-06-23', 23, 'Tuesday', 6, 'June', 2, '2026', 0),
(27, '2026-06-24', 24, 'Wednesday', 6, 'June', 2, '2026', 0),
(28, '2026-06-25', 25, 'Thursday', 6, 'June', 2, '2026', 0),
(29, '2026-06-26', 26, 'Friday', 6, 'June', 2, '2026', 0),
(30, '2026-06-27', 27, 'Saturday', 6, 'June', 2, '2026', 1),
(31, '2026-06-28', 28, 'Sunday', 6, 'June', 2, '2026', 1),
(32, '2026-06-29', 29, 'Monday', 6, 'June', 2, '2026', 0);

-- --------------------------------------------------------

--
-- Table structure for table `fact_campaign_performance`
--

CREATE TABLE `fact_campaign_performance` (
  `perf_id` int(11) NOT NULL,
  `time_id` int(11) NOT NULL,
  `campaign_sk` int(11) NOT NULL,
  `total_raised` decimal(12,2) DEFAULT 0.00,
  `donor_count` int(11) DEFAULT 0,
  `donation_count` int(11) DEFAULT 0,
  `avg_donation` decimal(12,2) DEFAULT 0.00,
  `goal_amount` decimal(12,2) DEFAULT 0.00,
  `progress_pct` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fact_campaign_performance`
--

INSERT INTO `fact_campaign_performance` (`perf_id`, `time_id`, `campaign_sk`, `total_raised`, `donor_count`, `donation_count`, `avg_donation`, `goal_amount`, `progress_pct`) VALUES
(64, 1, 1, 25000.00, 5, 5, 5000.00, 500000.00, 5.00),
(65, 1, 2, 12500.00, 3, 3, 4166.67, 300000.00, 4.17),
(66, 1, 3, 500.00, 1, 1, 500.00, 150000.00, 0.33),
(67, 2, 1, 10000.00, 1, 1, 10000.00, 500000.00, 2.00),
(68, 2, 2, 8000.00, 1, 1, 8000.00, 300000.00, 2.67),
(69, 2, 3, 12500.00, 1, 1, 12500.00, 150000.00, 8.33),
(70, 2, 7, 14000.00, 1, 1, 14000.00, 400000.00, 3.50),
(71, 2, 8, 350.00, 1, 1, 350.00, 250000.00, 0.14),
(72, 3, 1, 3500.00, 1, 1, 3500.00, 500000.00, 0.70),
(73, 3, 7, 2000.00, 1, 1, 2000.00, 400000.00, 0.50),
(74, 4, 3, 5000.00, 1, 1, 5000.00, 150000.00, 3.33),
(75, 5, 7, 15000.00, 1, 1, 15000.00, 400000.00, 3.75),
(76, 8, 1, 2500.00, 1, 1, 2500.00, 500000.00, 0.50),
(77, 8, 3, 10000.00, 1, 1, 10000.00, 150000.00, 6.67),
(78, 10, 7, 1000.00, 1, 1, 1000.00, 400000.00, 0.25),
(79, 10, 8, 6000.00, 1, 1, 6000.00, 250000.00, 2.40),
(80, 11, 1, 2500.00, 1, 1, 2500.00, 500000.00, 0.50),
(81, 12, 2, 1500.00, 1, 1, 1500.00, 300000.00, 0.50),
(82, 13, 3, 5000.00, 1, 1, 5000.00, 150000.00, 3.33),
(83, 15, 8, 12000.00, 1, 1, 12000.00, 250000.00, 4.80),
(84, 17, 2, 4500.00, 1, 1, 4500.00, 300000.00, 1.50),
(85, 18, 1, 100.00, 1, 1, 100.00, 500000.00, 0.02),
(86, 19, 2, 250.00, 1, 1, 250.00, 300000.00, 0.08),
(87, 20, 3, 50.00, 1, 1, 50.00, 150000.00, 0.03),
(88, 21, 4, 300.00, 1, 1, 300.00, 80000.00, 0.38),
(89, 22, 5, 75.00, 1, 1, 75.00, 100000.00, 0.08),
(90, 23, 6, 20.00, 1, 1, 20.00, 120000.00, 0.02),
(91, 24, 7, 500.00, 1, 1, 500.00, 400000.00, 0.13),
(92, 25, 8, 1000.00, 1, 1, 1000.00, 250000.00, 0.40),
(93, 26, 9, 200.00, 1, 1, 200.00, 50000.00, 0.40),
(94, 27, 10, 30.00, 1, 1, 30.00, 90000.00, 0.03),
(95, 32, 15, 60.00, 1, 1, 60.00, 150000.00, 0.04);

-- --------------------------------------------------------

--
-- Table structure for table `fact_donations`
--

CREATE TABLE `fact_donations` (
  `fact_id` int(11) NOT NULL,
  `time_id` int(11) NOT NULL,
  `campaign_sk` int(11) NOT NULL,
  `donor_sk` int(11) NOT NULL,
  `payment_method_id` int(11) NOT NULL,
  `donation_amount` decimal(12,2) NOT NULL,
  `transaction_reference` varchar(50) DEFAULT NULL,
  `donation_id` int(11) NOT NULL,
  `loaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fact_donations`
--

INSERT INTO `fact_donations` (`fact_id`, `time_id`, `campaign_sk`, `donor_sk`, `payment_method_id`, `donation_amount`, `transaction_reference`, `donation_id`, `loaded_at`) VALUES
(1, 1, 1, 2, 3, 5000.00, 'TXN-20260525-001', 1, '2026-06-14 14:39:27'),
(2, 1, 2, 2, 3, 1500.00, 'TXN-20260525-002', 2, '2026-06-14 14:39:27'),
(3, 1, 1, 3, 1, 1500.00, 'TXN-20260525-003', 3, '2026-06-14 14:39:27'),
(4, 1, 1, 5, 4, 12000.00, 'TXN-20260525-004', 4, '2026-06-14 14:39:27'),
(5, 1, 3, 6, 2, 500.00, 'TXN-20260525-005', 5, '2026-06-14 14:39:27'),
(6, 1, 2, 7, 3, 2500.00, 'TXN-20260525-006', 6, '2026-06-14 14:39:27'),
(7, 1, 1, 9, 4, 4500.00, 'TXN-20260525-007', 7, '2026-06-14 14:39:27'),
(8, 1, 2, 10, 1, 8500.00, 'TXN-20260525-008', 8, '2026-06-14 14:39:27'),
(9, 1, 1, 8, 3, 2000.00, 'TXN-20260525-009', 9, '2026-06-14 14:39:27'),
(10, 2, 1, 11, 7, 10000.00, 'TXN-20260612-063418-3788', 11, '2026-06-14 14:39:27'),
(11, 2, 3, 11, 7, 12500.00, 'TXN-20260612-123427-5887', 12, '2026-06-14 14:39:27'),
(12, 2, 7, 11, 7, 14000.00, 'TXN-20260612-160215-7831', 14, '2026-06-14 14:39:27'),
(13, 3, 1, 3, 3, 3500.00, 'TXN-20260601-9921', 15, '2026-06-14 14:39:27'),
(14, 4, 3, 5, 1, 5000.00, 'TXN-20260602-4810', 16, '2026-06-14 14:39:27'),
(15, 5, 7, 2, 4, 15000.00, 'TXN-20260605-1192', 17, '2026-06-14 14:39:27'),
(16, 8, 1, 9, 7, 2500.00, 'TXN-20260610-0912', 20, '2026-06-14 14:39:27'),
(17, 2, 2, 11, 3, 8000.00, 'TXN-20260612-5541', 22, '2026-06-14 14:39:27'),
(18, 10, 7, 6, 4, 1000.00, 'TXN-20260613-0012', 23, '2026-06-14 14:39:27'),
(19, 10, 8, 2, 2, 6000.00, 'TXN-20260613-0450', 24, '2026-06-14 14:39:27'),
(20, 11, 1, 2, 2, 2500.00, 'TXN-20251112-0041', 25, '2026-06-14 14:39:27'),
(21, 12, 2, 3, 3, 1500.00, 'TXN-20251225-1225', 26, '2026-06-14 14:39:27'),
(22, 13, 3, 5, 1, 5000.00, 'TXN-20260105-9912', 27, '2026-06-14 14:39:27'),
(23, 15, 8, 7, 4, 12000.00, 'TXN-20260320-4412', 29, '2026-06-14 14:39:27'),
(24, 17, 2, 9, 2, 4500.00, 'TXN-20260518-8831', 31, '2026-06-14 14:39:27'),
(25, 3, 7, 10, 7, 2000.00, 'TXN-20260601-5011', 32, '2026-06-14 14:39:27'),
(26, 8, 3, 11, 3, 10000.00, 'TXN-20260610-1092', 33, '2026-06-14 14:39:27'),
(27, 2, 8, 2, 1, 350.00, 'TXN-20260612-7410', 34, '2026-06-14 14:39:27'),
(28, 18, 1, 2, 3, 100.00, 'TXN-101', 35, '2026-06-14 14:39:27'),
(29, 19, 2, 2, 3, 250.00, 'TXN-102', 36, '2026-06-14 14:39:27'),
(30, 20, 3, 2, 3, 50.00, 'TXN-103', 37, '2026-06-14 14:39:27'),
(31, 21, 4, 3, 1, 300.00, 'TXN-104', 38, '2026-06-14 14:39:27'),
(32, 22, 5, 3, 1, 75.00, 'TXN-105', 39, '2026-06-14 14:39:27'),
(33, 23, 6, 5, 4, 20.00, 'TXN-106', 40, '2026-06-14 14:39:27'),
(34, 24, 7, 5, 4, 500.00, 'TXN-107', 41, '2026-06-14 14:39:27'),
(35, 25, 8, 6, 2, 1000.00, 'TXN-108', 42, '2026-06-14 14:39:27'),
(36, 26, 9, 6, 2, 200.00, 'TXN-109', 43, '2026-06-14 14:39:27'),
(37, 27, 10, 7, 3, 30.00, 'TXN-110', 44, '2026-06-14 14:39:27'),
(38, 32, 15, 11, 7, 60.00, 'TXN-115', 49, '2026-06-14 14:39:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dim_campaign`
--
ALTER TABLE `dim_campaign`
  ADD PRIMARY KEY (`campaign_sk`);

--
-- Indexes for table `dim_donor`
--
ALTER TABLE `dim_donor`
  ADD PRIMARY KEY (`donor_sk`);

--
-- Indexes for table `dim_payment_method`
--
ALTER TABLE `dim_payment_method`
  ADD PRIMARY KEY (`payment_method_id`),
  ADD UNIQUE KEY `method_name` (`method_name`);

--
-- Indexes for table `dim_time`
--
ALTER TABLE `dim_time`
  ADD PRIMARY KEY (`time_id`),
  ADD UNIQUE KEY `full_date` (`full_date`);

--
-- Indexes for table `fact_campaign_performance`
--
ALTER TABLE `fact_campaign_performance`
  ADD PRIMARY KEY (`perf_id`),
  ADD UNIQUE KEY `time_id` (`time_id`,`campaign_sk`),
  ADD KEY `campaign_sk` (`campaign_sk`);

--
-- Indexes for table `fact_donations`
--
ALTER TABLE `fact_donations`
  ADD PRIMARY KEY (`fact_id`),
  ADD KEY `time_id` (`time_id`),
  ADD KEY `campaign_sk` (`campaign_sk`),
  ADD KEY `donor_sk` (`donor_sk`),
  ADD KEY `payment_method_id` (`payment_method_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dim_campaign`
--
ALTER TABLE `dim_campaign`
  MODIFY `campaign_sk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `dim_donor`
--
ALTER TABLE `dim_donor`
  MODIFY `donor_sk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `dim_payment_method`
--
ALTER TABLE `dim_payment_method`
  MODIFY `payment_method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `dim_time`
--
ALTER TABLE `dim_time`
  MODIFY `time_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `fact_campaign_performance`
--
ALTER TABLE `fact_campaign_performance`
  MODIFY `perf_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `fact_donations`
--
ALTER TABLE `fact_donations`
  MODIFY `fact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `fact_campaign_performance`
--
ALTER TABLE `fact_campaign_performance`
  ADD CONSTRAINT `fact_campaign_performance_ibfk_1` FOREIGN KEY (`time_id`) REFERENCES `dim_time` (`time_id`),
  ADD CONSTRAINT `fact_campaign_performance_ibfk_2` FOREIGN KEY (`campaign_sk`) REFERENCES `dim_campaign` (`campaign_sk`);

--
-- Constraints for table `fact_donations`
--
ALTER TABLE `fact_donations`
  ADD CONSTRAINT `fact_donations_ibfk_1` FOREIGN KEY (`time_id`) REFERENCES `dim_time` (`time_id`),
  ADD CONSTRAINT `fact_donations_ibfk_2` FOREIGN KEY (`campaign_sk`) REFERENCES `dim_campaign` (`campaign_sk`),
  ADD CONSTRAINT `fact_donations_ibfk_3` FOREIGN KEY (`donor_sk`) REFERENCES `dim_donor` (`donor_sk`),
  ADD CONSTRAINT `fact_donations_ibfk_4` FOREIGN KEY (`payment_method_id`) REFERENCES `dim_payment_method` (`payment_method_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
