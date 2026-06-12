-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2026 at 01:32 PM
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
  MODIFY `campaign_sk` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dim_donor`
--
ALTER TABLE `dim_donor`
  MODIFY `donor_sk` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dim_payment_method`
--
ALTER TABLE `dim_payment_method`
  MODIFY `payment_method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `dim_time`
--
ALTER TABLE `dim_time`
  MODIFY `time_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fact_campaign_performance`
--
ALTER TABLE `fact_campaign_performance`
  MODIFY `perf_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fact_donations`
--
ALTER TABLE `fact_donations`
  MODIFY `fact_id` int(11) NOT NULL AUTO_INCREMENT;

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
