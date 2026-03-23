-- phpMyAdmin SQL Dump
-- version 5.2.2deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 14, 2026 at 06:37 AM
-- Server version: 11.8.3-MariaDB-1build1 from Ubuntu
-- PHP Version: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `conslot`
--

-- --------------------------------------------------------

--
-- Table structure for table `consultation_bookings`
--

CREATE TABLE `consultation_bookings` (
  `id` int(11) NOT NULL,
  `slot_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `booking_status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `consultation_bookings`
--

INSERT INTO `consultation_bookings` (`id`, `slot_id`, `student_id`, `reason`, `booking_status`, `created_at`) VALUES
(1, 1, 3, 'try lng nko ma consult', 'pending', '2026-03-14 06:35:10');

-- --------------------------------------------------------

--
-- Table structure for table `consultation_slots`
--

CREATE TABLE `consultation_slots` (
  `id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `slot_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration_minutes` int(11) DEFAULT 20,
  `max_students` int(11) DEFAULT 1,
  `booked_students` int(11) DEFAULT 0,
  `status` enum('open','full','closed') DEFAULT 'open',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `consultation_slots`
--

INSERT INTO `consultation_slots` (`id`, `instructor_id`, `slot_date`, `start_time`, `end_time`, `duration_minutes`, `max_students`, `booked_students`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 3, '2026-03-14', '13:00:00', '17:00:00', 60, 4, 1, 'open', 'only for cluster A', '2026-03-13 07:44:34', '2026-03-14 06:35:10');

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `google_access_token` text DEFAULT NULL,
  `google_refresh_token` text DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `office_location` varchar(100) DEFAULT NULL,
  `office_hours` varchar(100) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`id`, `first_name`, `middle_name`, `last_name`, `email`, `google_id`, `google_access_token`, `google_refresh_token`, `password`, `phone`, `department`, `specialization`, `employee_id`, `office_location`, `office_hours`, `profile_image`, `bio`, `status`, `email_verified`, `created_at`, `updated_at`, `last_login`) VALUES
(2, 'Charles', 'Simblate', 'Evangelio', 'charles123evangelio@gmail.com', NULL, NULL, NULL, '$2y$12$.hURCfHCCe47EkONE6rG3O4Qy/umv6oJcc3fBKSFLBy2Ze9hwHZTe', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 0, '2026-03-09 21:10:00', '2026-03-09 21:10:00', NULL),
(3, 'Charles', 'S.', 'Evangelio', 'charles123simblante@gmail.com', NULL, NULL, NULL, '$2y$12$g0a3/09UhYcLFW.11pSEl.GdXa5vqUu4runhdrn3ztziBRIcclUv.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 0, '2026-03-09 21:11:56', '2026-03-14 06:06:31', '2026-03-14 06:06:31');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `google_access_token` text DEFAULT NULL,
  `google_refresh_token` text DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `year_level` int(11) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `first_name`, `middle_name`, `last_name`, `email`, `google_id`, `google_access_token`, `google_refresh_token`, `password`, `phone`, `student_id`, `year_level`, `course`, `department`, `profile_image`, `bio`, `status`, `email_verified`, `created_at`, `updated_at`, `last_login`) VALUES
(3, 'Charles', 'Simblate', 'Evangelio', 'charles123simblante@gmail.com', NULL, NULL, NULL, '$2y$12$ET/jKgaIZhA0WiH7w7ewFele6nCndQajVIIgnvdyW2pnG7ud6NPGS', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 0, '2026-03-09 21:08:49', '2026-03-14 06:22:43', '2026-03-14 06:22:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `consultation_bookings`
--
ALTER TABLE `consultation_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `slot_id` (`slot_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `consultation_slots`
--
ALTER TABLE `consultation_slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_slot_date` (`slot_date`),
  ADD KEY `idx_instructor_id` (`instructor_id`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `idx_instructor_email` (`email`),
  ADD KEY `idx_instructor_status` (`status`),
  ADD KEY `idx_instructor_created_at` (`created_at`),
  ADD KEY `idx_instructor_department` (`department`),
  ADD KEY `idx_instructor_employee_id` (`employee_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `idx_student_email` (`email`),
  ADD KEY `idx_student_status` (`status`),
  ADD KEY `idx_student_created_at` (`created_at`),
  ADD KEY `idx_student_student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `consultation_bookings`
--
ALTER TABLE `consultation_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `consultation_slots`
--
ALTER TABLE `consultation_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `consultation_bookings`
--
ALTER TABLE `consultation_bookings`
  ADD CONSTRAINT `consultation_bookings_ibfk_1` FOREIGN KEY (`slot_id`) REFERENCES `consultation_slots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consultation_bookings_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `consultation_slots`
--
ALTER TABLE `consultation_slots`
  ADD CONSTRAINT `fk_slots_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
