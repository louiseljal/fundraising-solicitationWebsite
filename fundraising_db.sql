-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
<<<<<<< HEAD
-- Generation Time: Jun 13, 2026 at 08:12 AM
=======
-- Generation Time: Jun 13, 2026 at 01:55 AM
>>>>>>> 4208e3dba672fe1d40c0a6f0d44da2e196c4a190
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
-- Database: `fundraising_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `priority` enum('Normal','Important','Urgent') DEFAULT 'Normal',
  `is_pinned` tinyint(1) DEFAULT 0,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `user_id`, `title`, `content`, `priority`, `is_pinned`, `is_deleted`, `created_at`) VALUES
(1, 11, 'Solicitation Approved: Donations for fire victims', 'this is to support fire victims', 'Normal', 0, 0, '2026-06-12 14:03:02'),
(2, 11, 'Solicitation Approved: Donations for residents harmed by Bagyong Harvey', 'Helping our kababayans to recover', 'Normal', 0, 0, '2026-06-12 23:48:50');

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `campaign_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `goal_amount` decimal(12,2) NOT NULL,
  `current_raised_cache` decimal(12,2) DEFAULT 0.00,
  `campaign_status` enum('Draft','Active','Paused','Completed','Cancelled') DEFAULT 'Draft',
  `category` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campaigns`
--

INSERT INTO `campaigns` (`campaign_id`, `title`, `slug`, `description`, `goal_amount`, `current_raised_cache`, `campaign_status`, `category`, `start_date`, `end_date`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'Typhoon Relief Drive 2026', 'typhoon-relief-2026', 'Providing food and shelter packs to displaced families.', 500000.00, 35000.00, 'Active', 'Disaster Relief', '2026-05-01', '2026-06-01', 0, '2026-05-25 07:13:10', '2026-06-12 04:34:33'),
<<<<<<< HEAD
(2, 'Juan’s Medical & Chemotherapy Fund', 'juans-medical-fund', 'Helping Juan battle stage 3 lung cancer.', 300000.00, 12500.00, 'Active', 'Medical', '2026-04-15', '2026-07-15', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(3, 'Public School Books & Laptops Project', 'school-books-laptops', 'Sponsoring tech upgrades for remote public schools.', 150000.00, 13000.00, 'Active', 'Education', '2026-05-10', '2026-08-10', 0, '2026-05-25 07:13:10', '2026-06-12 20:23:55'),
(4, 'Salamat Paw-Pals Animal Shelter Expansion', 'animal-shelter-expansion', 'Building extra cages and securing kibble for rescued dogs.', 80000.00, 0.00, 'Active', 'Animal Welfare', '2026-05-20', '2026-06-20', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(5, 'Community Kitchen Clean Water Project', 'clean-water-project', 'Installing high-grade water filters in local districts.', 100000.00, 0.00, 'Draft', 'Community', '2026-06-01', '2026-09-01', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(6, 'Reforestation in Sierra Madre', 'reforestation-sierra-madre', 'Planting 10,000 native trees to combat landslides.', 120000.00, 0.00, 'Paused', 'Environment', '2026-03-01', '2026-09-01', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(7, 'Surgical Fund for Baby Neo', 'surgical-fund-baby-neo', 'Urgent congenital heart disease operation.', 400000.00, 16000.00, 'Active', 'Medical', '2026-05-22', '2026-06-22', 0, '2026-05-25 07:13:10', '2026-06-12 21:11:14'),
(8, 'Scholars Across Borders 2026', 'scholars-across-borders-2026', 'College tuition assistance for underprivileged students.', 250000.00, 0.00, 'Active', 'Education', '2026-05-01', '2026-12-31', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(9, 'Artists Support Group Grant', 'artists-support-grant', 'Micro-grants for local street muralists.', 50000.00, 0.00, 'Completed', 'Arts & Culture', '2026-01-01', '2026-04-01', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(10, 'Bike-for-a-Cause Metro Manila', 'bike-for-a-cause-manila', 'Purchasing commuter bikes for working-class citizens.', 90000.00, 0.00, 'Cancelled', 'Community', '2026-02-01', '2026-03-01', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(11, 'Mangrove Planting Drive Bulacan', 'mangrove-planting-bulacan', 'Restoring coastal barriers by planting 5,000 mangrove saplings.', 75000.00, 0.00, 'Active', 'Environment', '2026-06-01', '2026-08-01', 0, '2026-05-31 18:00:00', '2026-05-31 18:00:00'),
(12, 'Scholarship Fund for Tech Students', 'tech-scholarship-2026', 'Providing tuition and laptop support for underprivileged IT students.', 200000.00, 8000.00, 'Active', 'Education', '2026-05-15', '2026-09-15', 0, '2026-05-14 20:30:00', '2026-06-13 03:05:29'),
(13, 'Barangay Health Center Renovation', 'health-center-renovation', 'Upgrading critical diagnostic equipment and maternal care units.', 180000.00, 0.00, 'Active', 'Medical', '2026-07-01', '2026-10-01', 0, '2026-06-10 00:15:00', '2026-06-13 05:41:21'),
(14, 'Sulu Community Library Project', 'sulu-community-library', 'Successfully built a modern reading center stocked with over 2,000 reference books.', 120000.00, 124500.00, 'Active', 'Education', '2026-01-10', '2026-05-10', 0, '2026-01-09 17:00:00', '2026-06-13 03:05:12'),
(15, 'Marikina Flood Relief Phase 2', 'marikina-flood-relief-p2', 'Emergency food provisions and temporary bedding kits for flood survivors.', 150000.00, 60.00, 'Cancelled', 'Disaster Relief', '2026-02-15', '2026-03-15', 0, '2026-02-14 01:00:00', '2026-06-13 05:46:24'),
(16, 'Indigenous Weavers Preservation Grant', 'indigenous-weavers-grant', 'Temporarily paused pending structural logistical adjustments with regional hubs.', 95000.00, 15000.00, 'Paused', 'Arts & Culture', '2026-03-01', '2026-09-01', 0, '2026-02-28 06:00:00', '2026-05-20 08:45:00'),
(17, 'Solar Panels for Remote Villages', 'solar-panels-remote-villages', 'Bringing sustainable grid-tied lighting options to mountain community centers.', 350000.00, 0.00, 'Active', 'Community', '2026-06-05', '2026-11-05', 0, '2026-06-04 19:10:00', '2026-06-04 19:10:00'),
(18, 'Youth Sports Equipment Drive', 'youth-sports-equipment', 'Successfully distributed jerseys, basketballs, and soccer nets across 12 local barangays.', 40000.00, 42000.00, 'Active', 'Community', '2026-03-15', '2026-05-15', 0, '2026-03-14 23:00:00', '2026-06-13 03:05:23'),
(19, 'Stray Cat Trap-Neuter-Return Programwwww', 'stray-cat-tnr-program', 'Managing stray animal populations humanely via local veterinary partnerships.', 60000.00, 1000.00, 'Paused', 'Arts &amp; Culture', '2026-06-11', '2026-08-11', 0, '2026-06-10 21:00:00', '2026-06-13 03:24:13'),
(20, 'Local Music Festival & Arts Relief', 'local-music-festival-relief', 'Providing emergency micro-grants for stage managers and indie performers.', 110000.00, 0.00, 'Active', 'Arts & Culture', '2026-04-01', '2026-08-01', 0, '2026-03-25 02:30:00', '2026-06-13 00:46:56'),
(21, 'Scholarship Fund 2026', 'scholarship-fund-2026', 'Funding for students', 5000.00, 0.00, 'Active', 'Education', '2026-06-15', '2026-12-15', 0, '2026-06-15 02:00:00', '2026-06-13 04:16:28'),
(22, 'Clean Water Initiative', 'clean-water-initiative', 'Providing filters', 8000.00, 0.00, 'Active', 'Community', '2026-06-16', '2026-12-16', 0, '2026-06-16 03:00:00', '2026-06-13 04:16:28'),
(23, 'Urban Gardening Project', 'urban-gardening', 'Green spaces', 2000.00, 0.00, 'Active', 'Environment', '2026-06-17', '2026-12-17', 0, '2026-06-17 01:30:00', '2026-06-13 04:16:28'),
(24, 'Tech for Students', 'tech-for-students', 'Laptop drive', 4500.00, 0.00, 'Active', 'Education', '2026-06-18', '2026-12-18', 0, '2026-06-18 06:00:00', '2026-06-13 04:16:28'),
(25, 'Local Library Renovation', 'library-renovation', 'Book repair', 3000.00, 0.00, 'Active', 'Community', '2026-06-19', '2026-12-19', 0, '2026-06-19 02:15:00', '2026-06-13 04:16:28'),
(26, 'Art supplies for kids', 'art-supplies-kids', 'Art tools', 1500.00, 0.00, 'Active', 'Arts & Culture', '2026-06-20', '2026-12-20', 0, '2026-06-20 08:45:00', '2026-06-13 04:16:28'),
(27, 'Shelter Expansion', 'shelter-expansion', 'New beds', 12000.00, 0.00, 'Active', 'Community', '2026-06-21', '2026-12-21', 0, '2026-06-21 00:00:00', '2026-06-13 04:16:28'),
(28, 'Disaster Relief Fund', 'disaster-relief-fund', 'Emergency aid', 15000.00, 0.00, 'Active', 'Disaster Relief', '2026-06-22', '2026-12-22', 0, '2026-06-22 04:00:00', '2026-06-13 04:16:28'),
(29, 'Medical Aid for Elders', 'medical-aid-elders', 'Health support', 6000.00, 0.00, 'Active', 'Medical', '2026-06-23', '2026-12-23', 0, '2026-06-23 03:20:00', '2026-06-13 04:16:28'),
(30, 'Community Park Cleanup', 'park-cleanup', 'Waste mgmt', 1000.00, 0.00, 'Active', 'Environment', '2026-06-24', '2026-12-24', 0, '2026-06-24 01:00:00', '2026-06-13 04:16:28'),
(31, 'Coding Bootcamp', 'coding-bootcamp', 'Skill building', 3500.00, 0.00, 'Active', 'Education', '2026-06-25', '2026-12-25', 0, '2026-06-25 05:00:00', '2026-06-13 04:16:28'),
(32, 'Youth Sports League', 'youth-sports', 'Gear drive', 2500.00, 0.00, 'Active', 'Community', '2026-06-26', '2026-12-26', 0, '2026-06-26 07:30:00', '2026-06-13 04:16:28'),
(33, 'Animal Rescue Shelter', 'animal-rescue', 'Rescue funds', 4000.00, 0.00, 'Active', 'Animal Welfare', '2026-06-27', '2026-12-27', 0, '2026-06-27 02:00:00', '2026-06-13 04:16:28'),
(34, 'Senior Nutrition Program', 'senior-nutrition', 'Meal plans', 5500.00, 0.00, 'Active', 'Community', '2026-06-28', '2026-12-28', 0, '2026-06-28 01:45:00', '2026-06-13 04:16:28'),
(35, 'Winter Clothing Drive', 'winter-clothing', 'Warm coats', 2000.00, 0.00, 'Active', 'Disaster Relief', '2026-06-29', '2026-12-29', 0, '2026-06-29 06:20:00', '2026-06-13 04:16:28'),
(36, 'Upcoming Health Seminar', 'health-seminar', 'Event funding', 500.00, 0.00, 'Draft', 'Medical', '2026-07-01', '2026-07-02', 0, '2026-06-30 00:00:00', '2026-06-13 04:16:28'),
(37, 'New Science Lab Build', 'science-lab-build', 'Lab equipment', 9000.00, 0.00, 'Active', 'Education', '2026-07-03', '2026-07-04', 0, '2026-06-30 01:00:00', '2026-06-13 05:38:09'),
(38, 'Historical Archival Project', 'archival-project', 'Preservation', 3000.00, 0.00, 'Cancelled', 'Arts & Culture', '2026-07-05', '2026-07-06', 0, '2026-06-30 02:00:00', '2026-06-13 05:37:00'),
(39, 'Community Solar Project', 'solar-project', 'Solar power', 7500.00, 0.00, 'Active', 'Environment', '2026-07-08', '2026-07-09', 0, '2026-06-30 03:00:00', '2026-06-13 04:33:16'),
(40, 'Holiday Food Bank', 'holiday-food-bank', 'Holiday meals', 4000.00, 0.00, 'Active', 'Community', '2026-07-10', '2026-07-11', 0, '2026-06-30 04:00:00', '2026-06-13 04:31:27');
=======
(2, 'Juan’s Medical & Chemotherapy Fund', 'juans-medical-fund', 'Helping Juan battle stage 3 lung cancer.', 300000.00, 612500.00, 'Active', 'Medical', '2026-04-15', '2026-07-15', 0, '2026-05-25 07:13:10', '2026-06-12 23:02:17'),
(3, 'Public School Books & Laptops Project', 'school-books-laptops', 'Sponsoring tech upgrades for remote public schools.', 150000.00, 125500.00, 'Active', 'Education', '2026-05-10', '2026-08-10', 0, '2026-05-25 07:13:10', '2026-06-12 10:35:45'),
(4, 'Salamat Paw-Pals Animal Shelter Expansion', 'animal-shelter-expansion', 'Building extra cages and securing kibble for rescued dogs.', 80000.00, 0.00, 'Active', 'Animal Welfare', '2026-05-20', '2026-06-20', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(5, 'Community Kitchen Clean Water Project', 'clean-water-project', 'Installing high-grade water filters in local districts.', 100000.00, 0.00, 'Draft', 'Community', '2026-06-01', '2026-09-01', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(6, 'Reforestation in Sierra Madre', 'reforestation-sierra-madre', 'Planting 10,000 native trees to combat landslides.', 120000.00, 0.00, 'Paused', 'Environment', '2026-03-01', '2026-09-01', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(7, 'Surgical Fund for Baby Neo', 'surgical-fund-baby-neo', 'Urgent congenital heart disease operation.', 400000.00, 6000500.00, 'Active', 'Medical', '2026-05-22', '2026-06-22', 0, '2026-05-25 07:13:10', '2026-06-12 23:20:28'),
(8, 'Scholars Across Borders 2026', 'scholars-across-borders-2026', 'College tuition assistance for underprivileged students.', 250000.00, 0.00, 'Active', 'Education', '2026-05-01', '2026-12-31', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(9, 'Artists Support Group Grant', 'artists-support-grant', 'Micro-grants for local street muralists.', 50000.00, 0.00, 'Completed', 'Arts & Culture', '2026-01-01', '2026-04-01', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(10, 'Bike-for-a-Cause Metro Manila', 'bike-for-a-cause-manila', 'Purchasing commuter bikes for working-class citizens.', 90000.00, 0.00, 'Cancelled', 'Community', '2026-02-01', '2026-03-01', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(11, 'Running-for-a-Cause Cavite', 'running-for-a-cause-cavite', '', 1000.00, 0.00, 'Active', '', '0000-00-00', '0000-00-00', 0, '2026-06-12 23:35:30', '2026-06-12 23:35:30');
>>>>>>> 4208e3dba672fe1d40c0a6f0d44da2e196c4a190

-- --------------------------------------------------------

--
-- Table structure for table `collections`
--

CREATE TABLE `collections` (
  `collection_id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `collected_by` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `collection_date` date NOT NULL,
  `collection_method` varchar(100) DEFAULT 'Cash',
  `notes` text DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `donation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` char(3) DEFAULT 'PHP',
  `payment_status` enum('Pending','Completed','Failed','Refunded') DEFAULT 'Pending',
  `payment_method` enum('Credit_Card','PayPal','G_Cash','Bank_Transfer','Manual') NOT NULL,
  `transaction_reference` varchar(100) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`donation_id`, `user_id`, `campaign_id`, `amount`, `currency`, `payment_status`, `payment_method`, `transaction_reference`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 5000.00, 'PHP', 'Completed', 'G_Cash', 'TXN-20260525-001', 0, '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(2, 2, 2, 1500.00, 'PHP', 'Completed', 'G_Cash', 'TXN-20260525-002', 0, '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(3, 3, 1, 1500.00, 'PHP', 'Completed', 'Credit_Card', 'TXN-20260525-003', 0, '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(4, 5, 1, 12000.00, 'PHP', 'Completed', 'Bank_Transfer', 'TXN-20260525-004', 0, '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(5, 6, 3, 500.00, 'PHP', 'Completed', 'PayPal', 'TXN-20260525-005', 0, '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(6, 7, 2, 2500.00, 'PHP', 'Completed', 'G_Cash', 'TXN-20260525-006', 0, '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(7, 9, 1, 4500.00, 'PHP', 'Completed', 'Bank_Transfer', 'TXN-20260525-007', 0, '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(8, 10, 2, 8500.00, 'PHP', 'Completed', 'Credit_Card', 'TXN-20260525-008', 0, '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(9, 8, 1, 2000.00, 'PHP', 'Completed', 'G_Cash', 'TXN-20260525-009', 0, '2026-05-25 07:13:23', '2026-06-12 03:04:18'),
(10, 3, 4, 1000.00, 'PHP', 'Failed', 'Credit_Card', 'TXN-20260525-010', 0, '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(11, 11, 1, 10000.00, 'PHP', 'Completed', 'Manual', 'TXN-20260612-063418-3788', 0, '2026-06-12 04:34:18', '2026-06-12 04:34:33'),
<<<<<<< HEAD
(12, 11, 3, 12500.00, 'PHP', 'Completed', 'Manual', 'TXN-20260612-123427-5887', 0, '2026-06-12 10:34:27', '2026-06-12 20:23:55'),
(13, 11, 4, 5000.00, 'PHP', 'Failed', 'Manual', 'TXN-20260612-123515-8257', 0, '2026-06-12 10:35:15', '2026-06-12 20:23:55'),
(14, 11, 7, 14000.00, 'PHP', 'Completed', 'Manual', 'TXN-20260612-160215-7831', 0, '2026-06-12 14:02:15', '2026-06-12 20:23:55'),
(15, 3, 1, 3500.00, 'PHP', 'Completed', 'G_Cash', 'TXN-20260601-9921', 0, '2026-06-01 01:15:00', '2026-06-01 01:15:00'),
(16, 5, 3, 5000.00, 'PHP', 'Completed', 'Credit_Card', 'TXN-20260602-4810', 0, '2026-06-02 06:22:30', '2026-06-02 06:22:30'),
(17, 2, 7, 15000.00, 'PHP', 'Completed', 'Bank_Transfer', 'TXN-20260605-1192', 0, '2026-06-05 10:40:12', '2026-06-05 10:40:12'),
(18, 7, 2, 1200.00, 'PHP', 'Failed', 'PayPal', 'TXN-20260607-3341', 0, '2026-06-07 03:05:22', '2026-06-12 21:15:31'),
(19, 8, 8, 4500.00, 'PHP', 'Failed', 'G_Cash', 'TXN-20260608-8812', 0, '2026-06-08 08:10:45', '2026-06-08 08:10:45'),
(20, 9, 1, 2500.00, 'PHP', 'Completed', 'Manual', 'TXN-20260610-0912', 0, '2026-06-10 05:33:19', '2026-06-10 05:33:19'),
(21, 10, 3, 3000.00, 'PHP', 'Refunded', 'Credit_Card', 'TXN-20260611-7621', 0, '2026-06-11 12:50:00', '2026-06-11 12:50:00'),
(22, 11, 2, 8000.00, 'PHP', 'Completed', 'G_Cash', 'TXN-20260612-5541', 0, '2026-06-12 15:15:00', '2026-06-12 15:15:00'),
(23, 6, 7, 1000.00, 'PHP', 'Completed', 'Bank_Transfer', 'TXN-20260613-0012', 0, '2026-06-12 17:10:00', '2026-06-12 17:10:00'),
(24, 2, 8, 6000.00, 'PHP', 'Completed', 'PayPal', 'TXN-20260613-0450', 0, '2026-06-12 20:05:00', '2026-06-12 20:05:00'),
(25, 2, 1, 2500.00, 'PHP', 'Completed', 'PayPal', 'TXN-20251112-0041', 0, '2025-11-12 02:15:00', '2025-11-12 02:15:00'),
(26, 3, 2, 1500.00, 'PHP', 'Completed', 'G_Cash', 'TXN-20251225-1225', 0, '2025-12-25 00:30:00', '2025-12-25 00:30:00'),
(27, 5, 3, 5000.00, 'PHP', 'Completed', 'Credit_Card', 'TXN-20260105-9912', 0, '2026-01-05 06:22:00', '2026-01-05 06:22:00'),
(28, 6, 7, 750.00, 'PHP', 'Failed', 'G_Cash', 'TXN-20260214-3141', 0, '2026-02-14 11:45:10', '2026-02-14 11:45:10'),
(29, 7, 8, 12000.00, 'PHP', 'Completed', 'Bank_Transfer', 'TXN-20260320-4412', 0, '2026-03-20 03:00:00', '2026-03-20 03:00:00'),
(30, 8, 1, 3000.00, 'PHP', 'Refunded', 'Credit_Card', 'TXN-20260401-0104', 0, '2026-04-01 01:15:30', '2026-04-01 01:15:30'),
(31, 9, 2, 4500.00, 'PHP', 'Completed', 'PayPal', 'TXN-20260518-8831', 0, '2026-05-18 08:40:00', '2026-05-18 08:40:00'),
(32, 10, 7, 2000.00, 'PHP', 'Completed', 'Manual', 'TXN-20260601-5011', 0, '2026-06-01 05:12:00', '2026-06-12 21:11:14'),
(33, 11, 3, 10000.00, 'PHP', 'Completed', 'G_Cash', 'TXN-20260610-1092', 0, '2026-06-10 14:05:00', '2026-06-10 14:05:00'),
(34, 2, 8, 350.00, 'PHP', 'Completed', 'Credit_Card', 'TXN-20260612-7410', 0, '2026-06-12 09:50:00', '2026-06-12 09:50:00'),
(35, 2, 1, 100.00, 'PHP', 'Completed', 'G_Cash', 'TXN-101', 0, '2026-06-15 02:05:00', '2026-06-13 04:16:28'),
(36, 2, 2, 250.00, 'PHP', 'Completed', 'G_Cash', 'TXN-102', 0, '2026-06-16 03:05:00', '2026-06-13 04:16:28'),
(37, 2, 3, 50.00, 'PHP', 'Completed', 'G_Cash', 'TXN-103', 0, '2026-06-17 01:35:00', '2026-06-13 04:16:28'),
(38, 3, 4, 300.00, 'PHP', 'Completed', 'Credit_Card', 'TXN-104', 0, '2026-06-18 06:05:00', '2026-06-13 04:16:28'),
(39, 3, 5, 75.00, 'PHP', 'Completed', 'Credit_Card', 'TXN-105', 0, '2026-06-19 02:20:00', '2026-06-13 04:16:28'),
(40, 5, 6, 20.00, 'PHP', 'Completed', 'Bank_Transfer', 'TXN-106', 0, '2026-06-20 08:50:00', '2026-06-13 04:16:28'),
(41, 5, 7, 500.00, 'PHP', 'Completed', 'Bank_Transfer', 'TXN-107', 0, '2026-06-21 00:05:00', '2026-06-13 04:16:28'),
(42, 6, 8, 1000.00, 'PHP', 'Completed', 'PayPal', 'TXN-108', 0, '2026-06-22 04:05:00', '2026-06-13 04:16:28'),
(43, 6, 9, 200.00, 'PHP', 'Completed', 'PayPal', 'TXN-109', 0, '2026-06-23 03:25:00', '2026-06-13 04:16:28'),
(44, 7, 10, 30.00, 'PHP', 'Completed', 'G_Cash', 'TXN-110', 0, '2026-06-24 01:05:00', '2026-06-13 04:16:28'),
(45, 7, 11, 150.00, 'PHP', 'Pending', 'G_Cash', 'TXN-111', 0, '2026-06-25 05:05:00', '2026-06-13 04:16:28'),
(46, 8, 12, 80.00, 'PHP', 'Pending', 'Credit_Card', 'TXN-112', 0, '2026-06-26 07:35:00', '2026-06-13 04:16:28'),
(47, 9, 13, 120.00, 'PHP', 'Pending', 'Bank_Transfer', 'TXN-113', 0, '2026-06-27 02:05:00', '2026-06-13 04:16:28'),
(48, 10, 14, 90.00, 'PHP', 'Pending', 'PayPal', 'TXN-114', 0, '2026-06-28 01:50:00', '2026-06-13 04:16:28'),
(49, 11, 15, 60.00, 'PHP', 'Completed', 'Manual', 'TXN-115', 0, '2026-06-29 06:25:00', '2026-06-13 05:46:24');
=======
(12, 11, 3, 125000.00, 'PHP', 'Completed', 'Manual', 'TXN-20260612-123427-5887', 0, '2026-06-12 10:34:27', '2026-06-12 10:35:45'),
(13, 11, 4, 500000.00, 'PHP', 'Failed', 'Manual', 'TXN-20260612-123515-8257', 0, '2026-06-12 10:35:15', '2026-06-12 10:35:55'),
(14, 11, 7, 6000000.00, 'PHP', 'Completed', 'Manual', 'TXN-20260612-160215-7831', 0, '2026-06-12 14:02:15', '2026-06-12 14:03:06'),
(15, 11, 2, 600000.00, 'PHP', 'Completed', 'Manual', 'TXN-20260613-010205-9980', 0, '2026-06-12 23:02:05', '2026-06-12 23:02:17'),
(16, 11, 7, 500.00, 'PHP', 'Completed', 'Manual', 'TXN-20260613-011955-9406', 0, '2026-06-12 23:19:55', '2026-06-12 23:20:28');
>>>>>>> 4208e3dba672fe1d40c0a6f0d44da2e196c4a190

-- --------------------------------------------------------

--
-- Table structure for table `solicitations`
--

CREATE TABLE `solicitations` (
  `solicitation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_title` varchar(200) NOT NULL,
  `solicitation_category` varchar(100) NOT NULL,
  `target_amount` decimal(12,2) NOT NULL,
  `campaign_deadline` date NOT NULL,
  `post_description` text NOT NULL,
  `urgency_level` enum('Low','Medium','High') DEFAULT 'Medium',
  `poc_name` varchar(100) DEFAULT NULL,
  `poc_phone` varchar(30) DEFAULT NULL,
  `beneficiary_count` int(11) DEFAULT NULL,
  `allocation_items_json` longtext DEFAULT NULL,
  `attachments_json` longtext DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Completed') DEFAULT 'Pending',
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `solicitations`
--

INSERT INTO `solicitations` (`solicitation_id`, `user_id`, `post_title`, `solicitation_category`, `target_amount`, `campaign_deadline`, `post_description`, `urgency_level`, `poc_name`, `poc_phone`, `beneficiary_count`, `allocation_items_json`, `attachments_json`, `status`, `is_deleted`, `created_at`) VALUES
(1, 11, 'Solicitation for the improvement of computer laboratories', 'Educational Aid', 150000.00, '2026-08-06', 'Helping the students learn through materials with better quality and a much better environment', 'Low', 'Marian Liwayway', '091256783412', 0, '[\"Mouse\",\"Keyboard\",\"Monitor\"]', '[\"1781303914_Untitled-design-8.webp\"]', 'Approved', 0, '2026-06-12 22:38:34'),
(2, 12, 'Donations for residents harmed by Bagyong Harvey', 'Disaster Relief', 400000.00, '2026-07-08', 'Helping our kababayans to recover', 'Medium', 'Michael Ross', '09876543210', 20, '[\"Water\",\"Clothes\",\"First-Aid Kit\"]', '[\"1781308077_storm.jpg\"]', 'Approved', 0, '2026-06-12 23:47:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_role` enum('Admin','Donor') DEFAULT 'Donor',
  `account_status` enum('Active','Suspended') DEFAULT 'Active',
  `is_deleted` tinyint(1) DEFAULT 0,
  `row_version` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `user_role`, `account_status`, `is_deleted`, `row_version`, `created_at`, `updated_at`) VALUES
(1, 'admin_miko', 'miko.admin@hopefund.org', '$2y$10$e0MYzXy...', 'Admin', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-06-12 19:56:01'),
(2, 'juan_delacruz', 'juan.dc@gmail.com', '$2y$10$p7R9xWq...', 'Admin', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-06-12 19:55:55'),
(3, 'maria_santos', 'maria.s@yahoo.com', '$2y$10$k3F2vBn...', 'Donor', 'Suspended', 0, 1, '2026-05-25 07:12:51', '2026-06-13 00:45:02'),
(4, 'tech_support', 'it.admin@hopefund.org', '$2y$10$v9L1pZm...', 'Admin', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51'),
(5, 'elena_reyes', 'elena.reyes@outlook.com', '$2y$10$m6P3xRt...', 'Donor', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51'),
(6, 'brian_tan', 'btan.dev@gmail.com', '$2y$10$a1S5dFg...', 'Donor', 'Suspended', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51'),
(7, 'ana_gomez', 'ana.gomez@gmail.com', '$2y$10$h9J2kLl...', 'Donor', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51'),
(8, 'david_lim', 'dlim99@yahoo.com', '$2y$10$q2W4eRt...', 'Donor', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51'),
(9, 'grace_pua', 'grace.p@outlook.com', '$2y$10$z7X8cCv...', 'Donor', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51'),
(10, 'rachel_uy', 'rachel.uy@gmail.com', '$2y$10$u3I4oPp...', 'Donor', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51'),
(11, 'louiseledesma', 'louise@gmail.com', '$2y$10$OMm1oMLSQRYRmB/E/YGIBO4HujA7VMK1MGNKKq2yhtDwYwIDWiwAa', 'Admin', 'Active', 0, 1, '2026-06-12 03:33:57', '2026-06-12 03:33:57'),
<<<<<<< HEAD
(12, 'dennis_103', 'dennis103@gmail.com', '$2y$10$HJUbg8FuHt71Nhj2LduifuoLaLafNS6L8Nz88W53T0iNV8SeuN6Re', 'Admin', 'Active', 0, 1, '2026-06-13 00:02:34', '2026-06-13 00:03:20'),
(13, 'dennisuser', 'dennisuser@gmail.com', '$2y$10$GiUEaSWKp7K5HwRzgejErOkArduOrQKcfp/E.mD24Q6hb28M7Sds.', 'Donor', 'Active', 0, 1, '2026-06-13 01:07:48', '2026-06-13 01:07:48');
=======
(12, 'mikeross', 'suits@gmail.com', '$2y$10$IWpYBeN7FZA97wDHmaSIfumrBwIxN3XDKd92fWXcfz7BuY8uLP.I2', 'Donor', 'Active', 0, 1, '2026-06-12 23:42:49', '2026-06-12 23:42:49');
>>>>>>> 4208e3dba672fe1d40c0a6f0d44da2e196c4a190

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `country_code` char(2) DEFAULT NULL,
  `region_state` varchar(50) DEFAULT NULL,
  `total_donated_cache` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`profile_id`, `user_id`, `first_name`, `last_name`, `phone_number`, `avatar_url`, `country_code`, `region_state`, `total_donated_cache`) VALUES
(1, 1, 'Miko', 'Alvarez', '09171234567', 'avatar1.png', 'PH', 'NCR', 0.00),
(2, 2, 'Juan', 'Dela Cruz', '09187654321', 'avatar2.png', 'PH', 'Calabarzon', 6500.00),
(3, 3, 'Maria', 'Santos', '09192223334', 'avatar3.png', 'PH', 'NCR', 1500.00),
(4, 4, 'Alex', 'Tech', '09205556667', 'avatar4.png', 'PH', 'Central Luzon', 0.00),
(5, 5, 'Elena', 'Reyes', '09219998887', 'avatar5.png', 'PH', 'Central Visayas', 12000.00),
(6, 6, 'Brian', 'Tan', '09224445556', 'avatar6.png', 'PH', 'NCR', 500.00),
(7, 7, 'Ana', 'Gomez', '09231112223', 'avatar7.png', 'PH', 'Davao Region', 2500.00),
(8, 8, 'David', 'Lim', '09248887776', 'avatar8.png', 'PH', 'Calabarzon', 0.00),
(9, 9, 'Grace', 'Pua', '09256664442', 'avatar9.png', 'PH', 'Western Visayas', 4500.00),
(10, 10, 'Rachel', 'Uy', '09263331119', 'avatar10.png', 'PH', 'NCR', 8500.00),
<<<<<<< HEAD
(11, 11, 'louise', 'ledesma', NULL, NULL, NULL, NULL, 26500.00),
(12, 12, 'dennis', 'dennis', NULL, NULL, NULL, NULL, 0.00),
(13, 13, 'dennisuser', 'userrr', NULL, NULL, NULL, NULL, 0.00);
=======
(11, 11, 'louise', 'ledesma', NULL, NULL, NULL, NULL, 0.00),
(12, 12, 'Mike', 'Ross', NULL, NULL, NULL, NULL, 0.00);
>>>>>>> 4208e3dba672fe1d40c0a6f0d44da2e196c4a190

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `idx_announcements_user` (`user_id`),
  ADD KEY `idx_announcements_pinned` (`is_pinned`,`created_at`);

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`campaign_id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_campaigns_status_dates` (`campaign_status`,`start_date`,`end_date`),
  ADD KEY `idx_campaigns_etl` (`updated_at`);

--
-- Indexes for table `collections`
--
ALTER TABLE `collections`
  ADD PRIMARY KEY (`collection_id`),
  ADD KEY `idx_collections_campaign` (`campaign_id`),
  ADD KEY `idx_collections_collected_by` (`collected_by`),
  ADD KEY `idx_collections_date` (`collection_date`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`donation_id`),
  ADD UNIQUE KEY `transaction_reference` (`transaction_reference`),
  ADD KEY `campaign_id` (`campaign_id`),
  ADD KEY `idx_donations_user_campaign` (`user_id`,`campaign_id`),
  ADD KEY `idx_donations_etl` (`updated_at`,`payment_status`);

--
-- Indexes for table `solicitations`
--
ALTER TABLE `solicitations`
  ADD PRIMARY KEY (`solicitation_id`),
  ADD KEY `solicitations_ibfk_1` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_updated_at` (`updated_at`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_profiles_geo` (`country_code`,`region_state`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
<<<<<<< HEAD
  MODIFY `campaign_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;
=======
  MODIFY `campaign_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
>>>>>>> 4208e3dba672fe1d40c0a6f0d44da2e196c4a190

--
-- AUTO_INCREMENT for table `collections`
--
ALTER TABLE `collections`
  MODIFY `collection_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
<<<<<<< HEAD
  MODIFY `donation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;
=======
  MODIFY `donation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
>>>>>>> 4208e3dba672fe1d40c0a6f0d44da2e196c4a190

--
-- AUTO_INCREMENT for table `solicitations`
--
ALTER TABLE `solicitations`
  MODIFY `solicitation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
<<<<<<< HEAD
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
=======
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
>>>>>>> 4208e3dba672fe1d40c0a6f0d44da2e196c4a190

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
<<<<<<< HEAD
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
=======
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
>>>>>>> 4208e3dba672fe1d40c0a6f0d44da2e196c4a190

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `collections`
--
ALTER TABLE `collections`
  ADD CONSTRAINT `collections_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`campaign_id`),
  ADD CONSTRAINT `collections_ibfk_2` FOREIGN KEY (`collected_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`campaign_id`);

--
-- Constraints for table `solicitations`
--
ALTER TABLE `solicitations`
  ADD CONSTRAINT `solicitations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
