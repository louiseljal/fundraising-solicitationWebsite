-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 25, 2026 at 06:52 PM
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
(1, 'Typhoon Relief Drive 2026', 'typhoon-relief-2026', 'Providing food and shelter packs to displaced families.', 500000.00, 23000.00, 'Active', 'Disaster Relief', '2026-05-01', '2026-06-01', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(2, 'Juan’s Medical & Chemotherapy Fund', 'juans-medical-fund', 'Helping Juan battle stage 3 lung cancer.', 300000.00, 12500.00, 'Active', 'Medical', '2026-04-15', '2026-07-15', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(3, 'Public School Books & Laptops Project', 'school-books-laptops', 'Sponsoring tech upgrades for remote public schools.', 150000.00, 500.00, 'Active', 'Education', '2026-05-10', '2026-08-10', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(4, 'Salamat Paw-Pals Animal Shelter Expansion', 'animal-shelter-expansion', 'Building extra cages and securing kibble for rescued dogs.', 80000.00, 0.00, 'Active', 'Animal Welfare', '2026-05-20', '2026-06-20', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(5, 'Community Kitchen Clean Water Project', 'clean-water-project', 'Installing high-grade water filters in local districts.', 100000.00, 0.00, 'Draft', 'Community', '2026-06-01', '2026-09-01', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(6, 'Reforestation in Sierra Madre', 'reforestation-sierra-madre', 'Planting 10,000 native trees to combat landslides.', 120000.00, 0.00, 'Paused', 'Environment', '2026-03-01', '2026-09-01', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(7, 'Surgical Fund for Baby Neo', 'surgical-fund-baby-neo', 'Urgent congenital heart disease operation.', 400000.00, 0.00, 'Active', 'Medical', '2026-05-22', '2026-06-22', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(8, 'Scholars Across Borders 2026', 'scholars-across-borders-2026', 'College tuition assistance for underprivileged students.', 250000.00, 0.00, 'Active', 'Education', '2026-05-01', '2026-12-31', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(9, 'Artists Support Group Grant', 'artists-support-grant', 'Micro-grants for local street muralists.', 50000.00, 0.00, 'Completed', 'Arts & Culture', '2026-01-01', '2026-04-01', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10'),
(10, 'Bike-for-a-Cause Metro Manila', 'bike-for-a-cause-manila', 'Purchasing commuter bikes for working-class citizens.', 90000.00, 0.00, 'Cancelled', 'Community', '2026-02-01', '2026-03-01', 0, '2026-05-25 07:13:10', '2026-05-25 07:13:10');

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
  `payment_method` enum('Credit_Card','PayPal','G_Cash','Bank_Transfer') NOT NULL,
  `transaction_reference` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`donation_id`, `user_id`, `campaign_id`, `amount`, `currency`, `payment_status`, `payment_method`, `transaction_reference`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 5000.00, 'PHP', 'Completed', 'G_Cash', 'TXN-20260525-001', '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(2, 2, 2, 1500.00, 'PHP', 'Completed', 'G_Cash', 'TXN-20260525-002', '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(3, 3, 1, 1500.00, 'PHP', 'Completed', 'Credit_Card', 'TXN-20260525-003', '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(4, 5, 1, 12000.00, 'PHP', 'Completed', 'Bank_Transfer', 'TXN-20260525-004', '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(5, 6, 3, 500.00, 'PHP', 'Completed', 'PayPal', 'TXN-20260525-005', '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(6, 7, 2, 2500.00, 'PHP', 'Completed', 'G_Cash', 'TXN-20260525-006', '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(7, 9, 1, 4500.00, 'PHP', 'Completed', 'Bank_Transfer', 'TXN-20260525-007', '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(8, 10, 2, 8500.00, 'PHP', 'Completed', 'Credit_Card', 'TXN-20260525-008', '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(9, 8, 1, 2000.00, 'PHP', 'Pending', 'G_Cash', 'TXN-20260525-009', '2026-05-25 07:13:23', '2026-05-25 07:13:23'),
(10, 3, 4, 1000.00, 'PHP', 'Failed', 'Credit_Card', 'TXN-20260525-010', '2026-05-25 07:13:23', '2026-05-25 07:13:23');

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
(1, 'admin_miko', 'miko.admin@hopefund.org', '$2y$10$e0MYzXy...', 'Admin', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51'),
(2, 'juan_delacruz', 'juan.dc@gmail.com', '$2y$10$p7R9xWq...', 'Donor', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51'),
(3, 'maria_santos', 'maria.s@yahoo.com', '$2y$10$k3F2vBn...', 'Donor', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51'),
(4, 'tech_support', 'it.admin@hopefund.org', '$2y$10$v9L1pZm...', 'Admin', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51'),
(5, 'elena_reyes', 'elena.reyes@outlook.com', '$2y$10$m6P3xRt...', 'Donor', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51'),
(6, 'brian_tan', 'btan.dev@gmail.com', '$2y$10$a1S5dFg...', 'Donor', 'Suspended', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51'),
(7, 'ana_gomez', 'ana.gomez@gmail.com', '$2y$10$h9J2kLl...', 'Donor', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51'),
(8, 'david_lim', 'dlim99@yahoo.com', '$2y$10$q2W4eRt...', 'Donor', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51'),
(9, 'grace_pua', 'grace.p@outlook.com', '$2y$10$z7X8cCv...', 'Donor', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51'),
(10, 'rachel_uy', 'rachel.uy@gmail.com', '$2y$10$u3I4oPp...', 'Donor', 'Active', 0, 1, '2026-05-25 07:12:51', '2026-05-25 07:12:51');

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
(10, 10, 'Rachel', 'Uy', '09263331119', 'avatar10.png', 'PH', 'NCR', 8500.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`campaign_id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_campaigns_status_dates` (`campaign_status`,`start_date`,`end_date`),
  ADD KEY `idx_campaigns_etl` (`updated_at`);

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
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `campaign_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `donation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`campaign_id`);

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
