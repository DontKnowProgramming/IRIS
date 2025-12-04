-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 03:30 PM
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
-- Database: `rocelenrj_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@rocelynrj.com', '2025-09-18 16:50:04');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL DEFAULT curdate(),
  `checkin_time` time DEFAULT NULL,
  `checkout_time` time DEFAULT NULL,
  `status` enum('Present','Checked Out','Absent','Not Checked In') DEFAULT 'Not Checked In'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `date`, `checkin_time`, `checkout_time`, `status`) VALUES
(2, 9, '2025-10-21', NULL, NULL, ''),
(4, 11, '2025-10-21', NULL, NULL, ''),
(5, 12, '2025-10-21', NULL, NULL, ''),
(7, 11, '2025-11-26', '02:05:50', '02:05:57', ''),
(8, 9, '2025-11-29', '02:10:53', '02:08:38', 'Present'),
(9, 11, '2025-11-29', '02:08:39', '02:10:56', 'Checked Out'),
(10, 12, '2025-11-29', '02:08:40', '02:10:57', 'Checked Out'),
(13, 9, '2025-11-30', '23:13:56', '23:14:01', 'Checked Out'),
(14, 11, '2025-11-30', '23:13:58', '23:14:02', 'Checked Out'),
(15, 12, '2025-11-30', '23:13:59', '23:23:58', 'Checked Out'),
(18, 9, '2025-12-01', '16:08:24', '16:08:33', 'Checked Out'),
(19, 11, '2025-12-01', '16:08:24', '16:08:33', 'Checked Out'),
(20, 12, '2025-12-01', '16:08:25', '16:08:33', 'Checked Out'),
(22, 16, '2025-12-01', '09:00:42', '16:08:34', 'Checked Out'),
(23, 9, '2025-12-02', '11:26:58', '11:26:45', 'Present'),
(24, 11, '2025-12-02', '11:26:51', '11:26:46', 'Present'),
(25, 12, '2025-12-02', '11:26:57', '11:26:47', 'Present'),
(27, 16, '2025-12-02', NULL, NULL, 'Absent'),
(28, 17, '2025-12-02', '05:17:30', NULL, 'Present'),
(29, 18, '2025-12-02', '05:19:36', NULL, 'Present');

-- --------------------------------------------------------

--
-- Table structure for table `budget`
--

CREATE TABLE `budget` (
  `id` int(11) NOT NULL,
  `project` varchar(100) NOT NULL,
  `total_budget` decimal(18,2) NOT NULL,
  `spent` decimal(18,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'dada', 'dadad@gagaa', 'adada', '2025-09-18 17:08:11'),
(2, 'dadadada', 'dadadada@gagaaga', 'dadadadada', '2025-09-18 17:48:34');

-- --------------------------------------------------------

--
-- Table structure for table `email_settings`
--

CREATE TABLE `email_settings` (
  `id` int(11) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(190) NOT NULL,
  `from_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_settings`
--

INSERT INTO `email_settings` (`id`, `email`, `password`, `from_name`) VALUES
(1, 'solaones13@gmail.com', 'cqfmxepmzlidirzb', 'Rocelyn RJ');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `department` varchar(50) NOT NULL,
  `site` varchar(50) NOT NULL,
  `salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `hire_date` date NOT NULL DEFAULT '2000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `position`, `department`, `site`, `salary`, `status`, `hire_date`) VALUES
(9, 'JP', 'HR Manager', 'Design', 'Cebu', 113131.00, 'Active', '2025-10-13'),
(11, 'FRANCE M. PEREZ', 'HR Manager', 'Construction', 'Manila', 12990.00, 'Active', '2025-10-13'),
(12, 'Lara', 'HR Manager', 'Construction', 'Taguig', 27000.00, 'Active', '2025-10-18'),
(16, 'sample1', 'Administrator', 'Construction', 'Taguig', 99999999.99, 'Active', '2025-12-01'),
(17, 'Adrian Carlo S. Zarate', 'Administrator', 'Management', 'Manila', 99999999.99, 'Active', '2025-12-02'),
(18, 'Paul Mejia', 'TimeKeeper', 'Management', 'Taguig', 50000.00, 'Active', '2025-12-02');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `vendor` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `status` enum('Pending','Approved','Disapproved','Paid') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `category`, `description`, `amount`, `vendor`, `date`, `status`) VALUES
(1, 'dada', 'dada', 123131.00, 'dada', '2025-10-21', 'Paid'),
(2, 'dada', 'dada', 13132132.00, 'dada', '2022-05-08', 'Disapproved'),
(3, 'adad', 'dadadad', 50000.00, 'tae', '2025-11-09', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `employee_id`, `type`, `start_date`, `end_date`, `days`, `status`) VALUES
(28, 9, 'Vacation Leave', '2025-12-02', '2025-12-03', 2, 'Pending'),
(29, 9, 'dfnkjfjf', '2025-12-02', '2025-12-08', 7, 'Rejected'),
(30, 11, 'Vacation', '2025-12-02', '2025-12-31', 30, 'Pending'),
(31, 11, 'Vacation', '2025-12-02', '2025-12-31', 30, 'Pending'),
(32, 11, 'Vacation', '2025-12-02', '2025-12-31', 30, 'Pending'),
(33, 16, 'Maternal Leave', '2025-12-02', '2026-01-05', 35, 'Pending'),
(34, 16, 'Maternal Leave', '2025-12-02', '2026-01-05', 35, 'Pending'),
(35, 17, 'Initial Pending Request', '2025-12-02', '2025-12-02', 1, 'Pending'),
(36, 18, 'Initial Pending Request', '2025-12-02', '2025-12-02', 1, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(100) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `budget` varchar(100) DEFAULT NULL,
  `completion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `description`, `status`, `image`, `created_at`, `category`, `duration`, `budget`, `completion`) VALUES
(5, 'Rocelen RJ', 'Renovation', 'In Progress', 'uploads/project_1760459740_314.png', '2025-10-14 16:35:40', 'Commercial', '6 months', '12 Billion', '2025-10-15'),
(19, 'House For Mr. Perez', 'Modern House', 'In Progress', 'uploads/project_1760462709_450.jpg', '2025-10-14 17:25:09', 'Residential', '6 months', '2 Million', '2026-04-15'),
(20, 'House for Mr. Lara', 'Industrial House Design', 'Planning', 'uploads/project_1760462875_578.jpg', '2025-10-14 17:27:55', 'Residential', '12 months', '1 Million', '2026-10-15');

-- --------------------------------------------------------

--
-- Table structure for table `revenue`
--

CREATE TABLE `revenue` (
  `id` int(11) NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `source` varchar(100) DEFAULT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `position` varchar(120) DEFAULT NULL,
  `username` varchar(120) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `ga_secret` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `employee_id`, `full_name`, `email`, `phone`, `position`, `username`, `password`, `created_at`, `ga_secret`) VALUES
(25, '001', 'John Patrick S. Onrubia', 'solaones13@gmail.com', '09054165198', 'Administrator', 'solaones13', '$2y$10$6k65RZsHRUjyCa0ojFYmlOQb1NCGPgW1B7L0sNZYprRN93gYiQ9dS', '2025-10-26 17:31:03', 'J2ZQ64HHOPOJXRFR'),
(43, '002', 'Tom Lara Kenji', 'jponrubia0720@gmail.com', NULL, 'HR Manager', 'Lara', '$2y$10$G9qvpsCNRojwuH9vRMYMteFh/xV7qRUF34N9bYe.6uHaApqxYeaqi', '2025-11-26 01:58:11', 'BSD7W65VUNONNBFO'),
(46, '003', 'France M. Perez', 'johnonrubia22@gmail.com', NULL, 'Time Keeper', 'France', '$2y$10$QDSZWA6H0RCqRcmZhX5mMOP4D8WiBjyY1d9wBAdm.GQY8fm1mrwXC', '2025-12-01 04:18:06', 'HYMPXPTF52BQ2CJ2');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `budget`
--
ALTER TABLE `budget`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_settings`
--
ALTER TABLE `email_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `revenue`
--
ALTER TABLE `revenue`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `budget`
--
ALTER TABLE `budget`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `email_settings`
--
ALTER TABLE `email_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `revenue`
--
ALTER TABLE `revenue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
