-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 15, 2024 at 03:22 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `loginsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `registration_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `registration_date`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$/PY0OD1MQGI/HGAHzd710.jpek4TzPK9NIX4MEmU/3MGBfeqM1zSK', '2024-07-13 12:32:12'),
(4, 'pratik', 'pratusyaharsora@gmail.com', '$2y$10$nCviBPQMN8u55EbeHGHa2OsHQSYfo.W8MgBvS6nV4iNrGcTROqb96', '2024-07-13 12:36:25'),
(7, 'yash', 'yash@gmail.com', '$2y$10$BWZrNLXz3D5LKCViQqmTDuZoXiUNEMkorupq25Th4B7t4wJYphdMm', '2024-07-13 12:38:30');

-- --------------------------------------------------------

--
-- Table structure for table `admin_logins`
--

CREATE TABLE `admin_logins` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `login_time` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_logins`
--

INSERT INTO `admin_logins` (`id`, `admin_id`, `login_time`, `ip_address`) VALUES
(1, 4, '2024-07-13 12:36:34', '::1'),
(2, 1, '2024-07-13 15:04:28', '::1'),
(3, 1, '2024-07-13 17:07:41', '::1'),
(4, 1, '2024-07-15 15:58:50', '::1'),
(5, 1, '2024-07-15 18:46:26', '::1'),
(6, 1, '2024-07-15 18:49:36', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `typing_history`
--

CREATE TABLE `typing_history` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `wpm` int(11) NOT NULL,
  `cpm` int(11) NOT NULL,
  `accuracy` float NOT NULL,
  `errors` int(11) NOT NULL,
  `backspaces` int(11) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `typing_history`
--

INSERT INTO `typing_history` (`id`, `username`, `wpm`, `cpm`, `accuracy`, `errors`, `backspaces`, `timestamp`) VALUES
(16, 'yash', 353, 1766, 2, 441, 0, '2024-07-15 15:21:29'),
(17, 'yash', 353, 1766, 2, 441, 0, '2024-07-15 15:21:30');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `create_datetime` datetime NOT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `registration_date` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `otp` varchar(6) DEFAULT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `paid` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `phone`, `create_datetime`, `reset_token`, `reset_token_expires`, `registration_date`, `last_login`, `otp`, `verified`, `paid`) VALUES
(90, 'dhruvil', '$2y$10$scojttlJXQW5pwWprBP9jewfqoIYZvnAKiEYvk2vFI1Adu/B66MZ.', 'pratusyaharsora@gmail.com', '7096441340', '0000-00-00 00:00:00', '8b5be7c4d05969a66ba78729cd37fc1b5c8244dc602901e121cb8b8b89254c64db12985a837570184bae562cb9a994395d8e', '2024-07-15 19:49:44', '2024-07-15 18:22:27', NULL, NULL, 1, 0),
(91, 'pratik', '$2y$10$kNp3NdUVpGWH14tPyXK50ekOOaiebMVJ6ePD1J8y8RBS7W5dX4xpO', 'pratikharsora41@gmail.com', '7096441340', '0000-00-00 00:00:00', NULL, NULL, '2024-07-15 18:47:14', NULL, NULL, 1, 0),
(92, 'yash', '$2y$10$fkvyB/4SNS5Bkft/2L3Ch.NUi3dr5eiGQNzBL4z86fdedDxOWdh1S', 'yashgohel395@gmail.com', '7046547026', '0000-00-00 00:00:00', NULL, NULL, '2024-07-15 18:50:49', NULL, NULL, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_logins`
--

CREATE TABLE `user_logins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_logins`
--
ALTER TABLE `admin_logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `typing_history`
--
ALTER TABLE `typing_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_logins`
--
ALTER TABLE `user_logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `admin_logins`
--
ALTER TABLE `admin_logins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `typing_history`
--
ALTER TABLE `typing_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `user_logins`
--
ALTER TABLE `user_logins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logins`
--
ALTER TABLE `admin_logins`
  ADD CONSTRAINT `admin_logins_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`);

--
-- Constraints for table `user_logins`
--
ALTER TABLE `user_logins`
  ADD CONSTRAINT `user_logins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
