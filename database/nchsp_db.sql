-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 14, 2026 at 10:54 AM
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
-- Database: `nchsp_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','medical_officer','doctor') NOT NULL DEFAULT 'doctor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(5, 'admin_user', '$2y$10$Arbq2jmhCJYN9SNkw59SoO6A/eC3QQGLaPij9bzPgDNgyArbOBRHa', 'super_admin', '2026-01-06 08:17:39'),
(6, 'test_doc_1767688446', '$2y$10$jywi/EFVNmX91DZ3UJeq.um7wtfcU2xeX61XfOqIIa/L8a6Afhdyy', 'doctor', '2026-01-06 08:34:06'),
(7, 'khalid_boss', '$2y$10$Zxt/0/9.OAeuYp1FB190AeqCZIKh02JgrsvtsHNkAv5je2TbXwhnm', 'doctor', '2026-01-06 08:36:12'),
(8, '123456', '$2y$10$Xlm2ZBdFbCS8AOhHNBUW/eZxvdON5cpEP5LsauR1hWTm7WXonWVAK', 'doctor', '2026-01-06 08:41:07'),
(9, 'Khalid', '$2y$10$OItNxhgZuB7Q/CDV3UuX1u0Epm3SFtWEDg/3UjwFMVFc./RF3/r0q', 'doctor', '2026-01-11 03:29:23');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` datetime NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `doctor_id`, `appointment_date`, `description`, `status`, `created_at`) VALUES
(1, 1, 1, '2026-01-07 09:34:06', 'Test Checkup', 'pending', '2026-01-06 08:34:06'),
(2, 4, 2, '2026-01-15 02:44:00', 'edxfcghvjk', 'pending', '2026-01-06 08:43:07'),
(3, 4, 3, '2026-01-01 02:44:00', 'dfghjbj', 'completed', '2026-01-06 08:43:43'),
(4, 5, 2, '2026-01-14 12:31:00', 'jbfkhbervnerfjvbhjerbvhqebvkqbefhvewio', 'pending', '2026-01-11 03:24:09'),
(5, 5, 4, '2026-01-11 12:30:00', '', 'completed', '2026-01-11 03:30:07'),
(6, 6, 4, '2026-01-11 00:33:00', '', 'completed', '2026-01-11 03:34:38');

-- --------------------------------------------------------

--
-- Table structure for table `blood_requests`
--

CREATE TABLE `blood_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `blood_group` varchar(5) NOT NULL,
  `units_needed` int(11) NOT NULL,
  `hospital` varchar(150) DEFAULT NULL,
  `contact_person` varchar(100) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `date_needed` date NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','approved','fulfilled','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diagnostics`
--

CREATE TABLE `diagnostics` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `condition_name` varchar(100) NOT NULL,
  `doctor_name` varchar(100) DEFAULT NULL,
  `since_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diagnostics`
--

INSERT INTO `diagnostics` (`id`, `user_id`, `condition_name`, `doctor_name`, `since_date`) VALUES
(1, 1, 'Hypertension Stage 1', 'Dr. Smith', '2023-08-01'),
(2, 1, 'Seasonal Allergies', 'Dr. House', '2022-03-15');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `admin_id`, `name`, `specialization`, `created_at`) VALUES
(1, 6, 'Dr. Test', 'General', '2026-01-06 08:34:06'),
(2, 7, 'Khalid', 'Child', '2026-01-06 08:36:12'),
(3, 8, 'zayed iqbal', 'zs', '2026-01-06 08:41:07'),
(4, 9, 'Dr. S. M. Khalid Mahmud', 'Child', '2026-01-11 03:29:23');

-- --------------------------------------------------------

--
-- Table structure for table `health_camps`
--

CREATE TABLE `health_camps` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `location` varchar(255) NOT NULL,
  `camp_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `google_map_link` text DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_camps`
--

INSERT INTO `health_camps` (`id`, `name`, `location`, `camp_date`, `description`, `google_map_link`, `contact_number`, `created_at`) VALUES
(1, 'Project_DBMS', 'jkt', '2026-01-22', 'asdfgh', 'https://www.google.com/maps/@23.7966476,90.4429568,3209m/data=!3m1!1e3?entry=ttu&g_ep=EgoyMDI1MTIwOS4wIKXMDSoASAFQAw%3D%3D', 'DSD', '2026-01-06 08:21:38'),
(2, 'WCC Health Camp', 'Jhalokathi Govt. High School', '2026-01-10', 'Free health camp, where people can free of cost test their blood pressure, diabetes test and many more.', 'https://maps.app.goo.gl/kTvLdKR1uDMuEvZk9', '01755768595', '2026-01-06 08:48:56');

-- --------------------------------------------------------

--
-- Table structure for table `medications`
--

CREATE TABLE `medications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `dosage` varchar(50) DEFAULT NULL,
  `frequency` varchar(50) DEFAULT NULL,
  `capsules_left` int(11) DEFAULT 0,
  `total_capsules` int(11) DEFAULT 10,
  `color_class` varchar(20) DEFAULT 'blue'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medications`
--

INSERT INTO `medications` (`id`, `user_id`, `name`, `dosage`, `frequency`, `capsules_left`, `total_capsules`, `color_class`) VALUES
(1, 1, 'Amoxicillin', '500mg', 'Daily', 2, 10, 'orange'),
(2, 1, 'Ibuprofen', '200mg', 'As needed', 15, 20, 'blue');

-- --------------------------------------------------------

--
-- Table structure for table `reminders`
--

CREATE TABLE `reminders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `time` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(20) DEFAULT 'general',
  `is_completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reminders`
--

INSERT INTO `reminders` (`id`, `user_id`, `title`, `time`, `description`, `type`, `is_completed`) VALUES
(1, 1, 'Dentist Appointment', '10:00 AM', 'Dr. Strange • Room 302', 'appointment', 0),
(2, 1, 'Take Amoxicillin', '12:30 PM', 'After lunch • 500mg', 'medication', 0),
(3, 1, 'Drink Water', '04:00 PM', 'Target: 2 Liters', 'water', 0);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `test_name` varchar(100) NOT NULL,
  `test_date` date NOT NULL,
  `result_value` varchar(100) DEFAULT NULL,
  `reference_range` varchar(100) DEFAULT NULL,
  `report_file` varchar(255) DEFAULT NULL,
  `doctor_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `user_id`, `test_name`, `test_date`, `result_value`, `reference_range`, `report_file`, `doctor_name`, `created_at`) VALUES
(1, 4, 'cbc ', '2026-01-08', 'wdferg', '4521', 'uploads/1767687553_Gd_35364493064232.pdf', 'sdfee', '2026-01-06 08:19:13'),
(2, 2, 'CBC', '2026-01-10', '9000', '40000-110000', 'uploads/1768101685_Diagnostic_Report_Munna.pdf', 'Dr. Sayem Sayeed', '2026-01-11 03:21:25'),
(3, 5, 'Blood', '2026-01-11', 'Good', '', 'uploads/1768102699_Diagnostic_Report_Munna.pdf', 'sdfee', '2026-01-11 03:38:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `voter_id` varchar(50) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `weight` decimal(5,2) DEFAULT 65.00,
  `height` decimal(5,2) DEFAULT 170.00,
  `blood_type` varchar(5) DEFAULT 'O+',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `dob`, `voter_id`, `mobile`, `password`, `weight`, `height`, `blood_type`, `created_at`) VALUES
(1, 'Sarah Connor', '1985-05-15', 'VID123456789', '01700000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 68.50, 172.00, 'A+', '2026-01-06 07:05:23'),
(2, 'Munna', '2026-01-09', '25144121', '544', '$2y$10$FY6f.aqK9m1AbxH0uNpBzeTKbSXvE3v8vBv72J8ReH7LpVsOgPELO', 65.00, 170.00, 'O+', '2026-01-06 07:20:44'),
(3, 'Zobaer', '2026-01-22', '5456622', '01993192365', '$2y$10$WjgUMLDMDaNRtx5ZWnt9NewJAsOJz.Dlhd2ADp5OY5bpniD16d9PG', 65.00, 170.00, 'O+', '2026-01-06 08:05:39'),
(4, 'zayed iqbal', '2026-01-08', '565542145456124512', '255', '$2y$10$0gi43AQ.brujMfz2gegUqemiewoXYj7glpbqBxeuoMBYHYBk0pTOu', 45.00, 142.00, 'AB+', '2026-01-06 08:10:13'),
(5, 'Mashruba Tammi', '2002-10-24', '656211', '01666666666', '$2y$10$nrggueZ0qShjzgnjwB/lReWv529VyNbCizsUurinFU6aV7lQ8El9K', 55.00, 175.00, 'B+', '2026-01-11 03:23:27'),
(6, 'zayed iqbal', '2026-01-11', '12345', '8rt78r', '$2y$10$h0qgITKTkyYxMJ/A2skkPu3Px0TRNC7nJxPQUWr/jYlSGDzLnA5uy', 123.00, 34.00, 'AB+', '2026-01-11 03:33:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `diagnostics`
--
ALTER TABLE `diagnostics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `health_camps`
--
ALTER TABLE `health_camps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voter_id` (`voter_id`),
  ADD UNIQUE KEY `mobile` (`mobile`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `blood_requests`
--
ALTER TABLE `blood_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diagnostics`
--
ALTER TABLE `diagnostics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `health_camps`
--
ALTER TABLE `health_camps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reminders`
--
ALTER TABLE `reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD CONSTRAINT `blood_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `diagnostics`
--
ALTER TABLE `diagnostics`
  ADD CONSTRAINT `diagnostics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medications`
--
ALTER TABLE `medications`
  ADD CONSTRAINT `medications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reminders`
--
ALTER TABLE `reminders`
  ADD CONSTRAINT `reminders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `assistants`
--

CREATE TABLE `assistants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `assistants_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assistants_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctor_schedules`
--

CREATE TABLE `doctor_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` int(11) NOT NULL,
  `available_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_booked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `doctor_schedules_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `prescription_text` text DEFAULT NULL,
  `prescription_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Updates to existing tables
--

-- Update admins role
ALTER TABLE `admins` MODIFY `role` enum('super_admin','medical_officer','doctor','assistant') NOT NULL DEFAULT 'doctor';

-- Update doctors with bio and profile_picture
ALTER TABLE `doctors` ADD `bio` text DEFAULT NULL AFTER `specialization`;
ALTER TABLE `doctors` ADD `profile_picture` varchar(255) DEFAULT NULL AFTER `bio`;

-- Update appointments with schedule_id
ALTER TABLE `appointments` ADD `schedule_id` int(11) DEFAULT NULL AFTER `doctor_id`;
ALTER TABLE `appointments` ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`schedule_id`) REFERENCES `doctor_schedules` (`id`) ON DELETE SET NULL;

-- Update health_camps with image_path
ALTER TABLE `health_camps` ADD `image_path` varchar(255) DEFAULT NULL;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
