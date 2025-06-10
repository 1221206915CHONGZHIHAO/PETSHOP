-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2025 at 12:25 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `petshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `Admin_Username` varchar(50) NOT NULL,
  `Admin_Email` varchar(50) NOT NULL,
  `Admin_Password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`Admin_Username`, `Admin_Email`, `Admin_Password`) VALUES
('ADMIN', 'admin@petshop.com', 'PETSHOP1');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `Cart_ID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Price` float NOT NULL,
  `Quantity` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`Cart_ID`, `Customer_ID`, `Product_ID`, `Price`, `Quantity`) VALUES
(1, 8, 9, 4.5, 2),
(8, 6, 15, 19.9, 1),
(9, 6, 9, 4.5, 4),
(10, 6, 11, 3, 3),
(11, 6, 12, 189.9, 2),
(12, 6, 10, 175, 1),
(13, 6, 13, 162.5, 1),
(58, 7, 15, 19.9, 1);

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `Customer_id` int(11) NOT NULL,
  `Customer_name` varchar(50) NOT NULL,
  `Customer_email` varchar(50) NOT NULL,
  `Customer_password` varchar(50) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`Customer_id`, `Customer_name`, `Customer_email`, `Customer_password`, `profile_image`, `reset_token`, `reset_token_expires`, `is_active`) VALUES
(1, 'Daniel', 'sss@gmail.com', '12345', NULL, NULL, NULL, 1),
(2, 'Amin', 'amin@mail.com', 'qwer', NULL, NULL, NULL, 1),
(3, 'ggg', 'd@gmail.com', '11', NULL, NULL, NULL, 1),
(7, 'DAVID321', 'zheya1@gmail.com', '1234', 'profile_7_1746592288.jpeg', NULL, NULL, 1),
(13, 'David', 'zheya1810@gmail.com', '12345678', NULL, NULL, NULL, 1),
(15, 'D', 'zheya@gmail.com', 'D1234567#', NULL, NULL, NULL, 1),
(17, 'hao', '1221209151@student.mmu.edu.my', 'Gou1111@', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `customer_address`
--

CREATE TABLE `customer_address` (
  `Address_ID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Address_Label` varchar(50) NOT NULL,
  `Full_Name` varchar(100) NOT NULL,
  `Phone_Number` varchar(20) NOT NULL,
  `Address_Line1` varchar(100) NOT NULL,
  `Address_Line2` varchar(100) DEFAULT NULL,
  `City` varchar(50) NOT NULL,
  `State` varchar(50) DEFAULT NULL,
  `Postal_Code` varchar(20) NOT NULL,
  `Country` varchar(50) NOT NULL,
  `Is_Default` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_address`
--

INSERT INTO `customer_address` (`Address_ID`, `Customer_ID`, `Address_Label`, `Full_Name`, `Phone_Number`, `Address_Line1`, `Address_Line2`, `City`, `State`, `Postal_Code`, `Country`, `Is_Default`) VALUES
(1, 7, 'Home', 'DAVID HI ZHE YA', '0123456789', 'Ixora Apartment, Jalan D1, 75450 Malacca', '', 'Melaka', 'Melaka', '75450', 'Malaysia', 1),
(5, 10, 'HOME', 'Zhi Hao Chong', '+1 (555) 123-4567', '123, jalan ixora, taman ixora,  81300 austin, johor.', '', 'johor', 'jb', '81300', 'africa', 1),
(6, 0, 'HOME', 'Zhi Hao Chong', '0189655809', '123, jalan ixora, taman ixora,  81300 austin, johor.', '', 'johor', 'jb', '81300', 'Malaysia', 1),
(7, 1, 'HOME', 'Zhi Hao ', '+1 (555) 123-4567', '128, jalan ixora 1, taman ixora 12,  81300 austin, johor.', '', 'johor', 'jb', '81300', 'Malaysia', 1);

-- --------------------------------------------------------

--
-- Table structure for table `customer_login_logs`
--

CREATE TABLE `customer_login_logs` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` varchar(10) NOT NULL COMMENT 'login/logout/failed',
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_login_logs`
--

INSERT INTO `customer_login_logs` (`id`, `username`, `email`, `status`, `timestamp`) VALUES
(1, 'Q', 'zhihao0113@gmail.com', 'login', '2025-04-28 19:38:16'),
(2, 'Q', 'zhihao0113@gmail.com', 'logout', '2025-04-28 19:38:27'),
(3, 'Q', 'zhihao0113@gmail.com', 'login', '2025-04-28 20:39:47'),
(4, 'Q', 'zhihao0113@gmail.com', 'logout', '2025-04-28 20:40:15'),
(5, 'Q', 'zhihao0113@gmail.com', 'login', '2025-04-28 20:40:42'),
(6, 'Q', 'zhihao0113@gmail.com', 'logout', '2025-04-28 20:40:56'),
(7, '', 'Q', 'failed', '2025-04-28 20:41:41'),
(8, 'Q', 'zhihao0113@gmail.com', 'login', '2025-04-28 20:41:49'),
(9, 'Q', 'zhihao0113@gmail.com', 'logout', '2025-04-28 20:41:55'),
(10, 'Q', 'zhihao0113@gmail.com', 'login', '2025-04-28 20:42:19'),
(11, 'Q', 'zhihao0113@gmail.com', 'logout', '2025-04-28 20:45:11'),
(12, '', 'Q', 'failed', '2025-04-29 14:35:50'),
(13, 'aa', 'zhihao013@gmail.com', 'login', '2025-04-29 14:36:09'),
(14, 'aa', 'zhihao013@gmail.com', 'logout', '2025-04-29 14:37:32'),
(15, 'aa', 'zhihao013@gmail.com', 'login', '2025-04-29 14:40:24'),
(16, 'aa', 'zhihao013@gmail.com', 'logout', '2025-04-29 15:04:20'),
(17, 'aa', 'zhihao013@gmail.com', 'login', '2025-04-29 15:37:51'),
(18, 'aa', 'zhihao013@gmail.com', 'logout', '2025-04-29 15:37:55'),
(19, 'aa', 'zhihao013@gmail.com', 'login', '2025-04-29 16:48:18'),
(20, 'aa', 'zhihao013@gmail.com', 'logout', '2025-04-29 16:48:43'),
(21, 'aa', 'zhihao013@gmail.com', 'login', '2025-04-29 20:58:48'),
(22, 'aa', 'zhihao013@gmail.com', 'logout', '2025-04-29 20:58:51'),
(23, 'aa', 'zhihao013@gmail.com', 'login', '2025-04-29 21:05:43'),
(24, 'aa', 'zhihao013@gmail.com', 'logout', '2025-04-29 21:06:01'),
(25, 'aa', 'zhihao013@gmail.com', 'login', '2025-04-29 21:06:17'),
(26, 'aa', 'zhihao013@gmail.com', 'login', '2025-04-30 08:38:54'),
(27, 'aa', 'zhihao013@gmail.com', 'logout', '2025-04-30 08:39:13'),
(28, 'aa', 'zhihao013@gmail.com', 'login', '2025-04-30 08:55:26'),
(29, 'aa', 'zhihao013@gmail.com', 'logout', '2025-04-30 08:56:18'),
(30, 'aa', 'zhihao013@gmail.com', 'login', '2025-04-30 09:03:40'),
(31, 'aa', 'zhihao013@gmail.com', 'logout', '2025-04-30 09:04:00'),
(32, '', 'aa', 'failed', '2025-04-30 09:08:12'),
(33, 'aa', 'zhihao013@gmail.com', 'login', '2025-04-30 09:08:19'),
(34, 'aa', 'zhihao013@gmail.com', 'logout', '2025-04-30 09:11:12'),
(35, 'aa', 'zhihao013@gmail.com', 'login', '2025-04-30 11:12:03'),
(36, 'aa', 'zhihao013@gmail.com', 'login', '2025-04-30 11:36:53'),
(37, 'aa', 'zhihao013@gmail.com', 'logout', '2025-04-30 11:44:10'),
(38, 'aa', 'zhihao013@gmail.com', 'login', '2025-04-30 11:45:32'),
(39, 'aa', 'zhihao013@gmail.com', 'logout', '2025-04-30 11:59:21'),
(40, '', 'aa', 'failed', '2025-05-05 16:04:41'),
(41, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-05 16:05:30'),
(42, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-05 17:40:46'),
(43, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-05 18:42:56'),
(44, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-05 18:45:06'),
(45, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-05 18:48:01'),
(46, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-05 19:00:12'),
(47, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-05 19:00:29'),
(48, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-05 19:00:32'),
(49, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-05 19:01:07'),
(50, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-05 19:01:35'),
(51, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-05 19:04:09'),
(52, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-05 19:04:12'),
(53, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-05 19:11:49'),
(54, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-05 19:11:53'),
(55, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-06 14:18:03'),
(56, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-06 14:21:32'),
(57, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-06 14:30:12'),
(58, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-06 14:30:44'),
(59, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-06 16:24:11'),
(60, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-06 16:27:32'),
(61, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-06 16:27:39'),
(62, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-06 16:27:51'),
(63, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-06 16:27:59'),
(64, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-06 16:29:02'),
(65, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-06 16:29:16'),
(66, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-06 20:11:40'),
(67, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-06 20:35:02'),
(68, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-06 20:35:09'),
(69, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-06 20:35:23'),
(70, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-06 20:35:30'),
(71, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-06 20:35:52'),
(72, 'zz', 'ss@gmail.com', 'login', '2025-05-06 21:39:43'),
(73, 'zz', 'ss@gmail.com', 'login', '2025-05-06 21:45:07'),
(74, 'zz', 'ss@gmail.com', 'login', '2025-05-06 21:49:14'),
(75, 'zz', 'ss@gmail.com', 'login', '2025-05-06 21:56:04'),
(76, '', 'aa', 'failed', '2025-05-06 21:56:45'),
(77, '', 'aa', 'failed', '2025-05-06 21:56:54'),
(78, '', 'ggg', 'failed', '2025-05-06 21:57:56'),
(79, '', 'ggg', 'failed', '2025-05-06 21:58:07'),
(80, '', 'Q', 'failed', '2025-05-06 21:58:23'),
(81, '', 'zhihao12', 'failed', '2025-05-06 21:59:29'),
(82, '', 'hao', 'failed', '2025-05-06 22:03:01'),
(83, '', 'hao1', 'failed', '2025-05-06 22:05:31'),
(84, 'hao1', 'zhihao013@gmail.com', 'login', '2025-05-06 22:13:24'),
(85, 'hao1', 'zhihao013@gmail.com', 'login', '2025-05-06 22:14:23'),
(86, 'hao1', 'zhihao013@gmail.com', 'login', '2025-05-06 22:14:39'),
(87, 'hao1', 'zhihao013@gmail.com', 'login', '2025-05-06 22:18:36'),
(88, 'hao', 'zhihao013@gmail.com', 'login', '2025-05-06 22:19:34'),
(89, 'hao', 'admin@petshop.com', 'logout', '2025-05-06 22:20:40'),
(90, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-06 22:26:12'),
(91, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-06 22:26:16'),
(92, '', 'aa', 'failed', '2025-05-07 11:18:30'),
(93, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-07 11:20:30'),
(94, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-07 11:20:39'),
(95, 'Daniel', 'daniel@gmail.com', 'login', '2025-05-07 11:21:05'),
(96, 'Daniel', 'daniel@gmail.com', 'logout', '2025-05-07 11:21:37'),
(97, '', 'aa', 'failed', '2025-05-07 11:21:44'),
(98, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-07 11:21:53'),
(99, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-07 11:23:25'),
(100, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-07 11:39:14'),
(101, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-07 11:41:34'),
(102, 'Daniel', 'daniel@gmail.com', 'login', '2025-05-07 11:41:44'),
(103, 'Daniel', 'daniel@gmail.com', 'logout', '2025-05-07 11:41:49'),
(104, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-07 11:42:00'),
(105, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-07 11:43:45'),
(106, '', 'aa', 'failed', '2025-05-07 12:17:43'),
(107, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-07 12:17:58'),
(108, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-07 12:18:09'),
(109, '', 'aa', 'failed', '2025-05-07 12:22:20'),
(110, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-07 12:22:29'),
(111, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-07 12:23:28'),
(112, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-07 12:23:34'),
(113, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-07 12:24:06'),
(114, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-07 12:24:13'),
(115, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-07 12:25:43'),
(116, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-14 08:18:17'),
(117, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-14 08:19:49'),
(118, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-14 08:24:20'),
(119, 'aa', 'zhihao013@gmail.com', 'logout', '2025-05-14 08:27:59'),
(120, '', 'zz', 'failed', '2025-05-14 16:06:02'),
(121, 'hao', 'ss@gmail.com', 'login', '2025-05-14 16:06:33'),
(122, 'hao', 'ss@gmail.com', 'logout', '2025-05-14 18:16:48'),
(123, 'aa', 'zhihao013@gmail.com', 'login', '2025-05-17 18:22:45'),
(124, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-20 20:40:22'),
(125, 'zhihao', 'zhihao013@gmail.com', 'logout', '2025-05-20 20:40:43'),
(126, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-21 09:32:31'),
(127, 'zhihao', 'zhihao013@gmail.com', 'logout', '2025-05-21 09:38:24'),
(128, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-22 12:01:39'),
(129, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-22 12:28:50'),
(130, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-22 14:21:45'),
(131, 'zhihao', 'zhihao013@gmail.com', 'logout', '2025-05-22 14:22:27'),
(132, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-24 22:38:45'),
(133, 'zhihao', 'zhihao013@gmail.com', 'logout', '2025-05-24 22:39:07'),
(134, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-24 22:39:36'),
(135, 'zhihao', 'zhihao013@gmail.com', 'logout', '2025-05-24 22:41:56'),
(136, 'Daniel', 'sss@gmail.com', 'login', '2025-05-24 22:42:14'),
(137, 'Daniel', 'sss@gmail.com', 'logout', '2025-05-24 22:43:17'),
(138, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-24 22:43:31'),
(139, 'zhihao', 'zhihao013@gmail.com', 'logout', '2025-05-24 22:46:30'),
(140, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-24 22:53:46'),
(141, 'zhihao', 'zhihao013@gmail.com', 'logout', '2025-05-24 22:54:19'),
(142, 'Daniel', 'sss@gmail.com', 'login', '2025-05-24 22:54:38'),
(143, 'Daniel', 'sss@gmail.com', 'logout', '2025-05-24 22:55:21'),
(144, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-24 22:55:43'),
(145, 'zhihao', 'zhihao013@gmail.com', 'logout', '2025-05-24 23:03:44'),
(146, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-25 21:08:22'),
(147, 'zhihao', 'zhihao013@gmail.com', 'logout', '2025-05-25 21:08:42'),
(148, 'Daniel', 'sss@gmail.com', 'login', '2025-05-25 21:08:58'),
(149, 'Daniel', 'sss@gmail.com', 'logout', '2025-05-25 21:10:09'),
(150, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-25 21:10:23'),
(151, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-25 23:12:32'),
(152, 'zhihao', 'zhihao013@gmail.com', 'logout', '2025-05-25 23:13:45'),
(153, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-25 23:15:06'),
(154, 'zhihao', 'zhihao013@gmail.com', 'logout', '2025-05-25 23:15:50'),
(155, 'Daniel', 'sss@gmail.com', 'login', '2025-05-25 23:16:01'),
(156, 'Daniel', 'sss@gmail.com', 'logout', '2025-05-25 23:16:28'),
(157, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-25 23:16:41'),
(158, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-26 14:14:15'),
(159, 'zhihao', 'zhihao013@gmail.com', 'logout', '2025-05-26 14:29:07'),
(160, '', 'zhihao', 'failed', '2025-05-26 14:57:36'),
(161, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-26 14:57:52'),
(162, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-26 15:08:21'),
(163, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-26 15:16:56'),
(164, 'zhihao', 'zhihao013@gmail.com', 'logout', '2025-05-26 15:17:02'),
(165, '', 'zhihao', 'failed', '2025-05-26 15:18:09'),
(166, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-26 15:18:20'),
(167, 'zhihao', 'ww@gmail.com', 'logout', '2025-05-26 18:32:44'),
(168, 'zhihao', 'zhihao013@gmail.com', 'login', '2025-05-26 21:15:16'),
(169, 'zhihao', 'zhihao013@gmail.com', 'logout', '2025-05-26 21:17:27'),
(170, 'TT', 'zhihao013@gmail.com', 'login', '2025-05-27 13:55:41'),
(171, 'TT', 'zhihao013@gmail.com', 'logout', '2025-05-27 13:55:49'),
(172, '', 'zhihao', 'failed', '2025-05-27 15:43:29'),
(173, '', '1221206915@STUDENT.MMU.EDU.MY', 'failed', '2025-05-27 15:43:54'),
(174, 'qq', '1221206915@student.mmu.edu.my', 'login', '2025-05-27 15:50:35'),
(175, '', '1221206915@student.mmu.edu.my', 'failed', '2025-05-27 15:51:19'),
(176, 'qq', '1221206915@student.mmu.edu.my', 'login', '2025-05-27 15:51:59'),
(177, 'qq', '1221206915@student.mmu.edu.my', 'logout', '2025-05-27 15:52:04'),
(178, '', '1221206915@student.mmu.edu.my', 'failed', '2025-06-03 10:00:02'),
(179, 'qq', '1221206915@student.mmu.edu.my', 'login', '2025-06-03 10:00:30'),
(180, 'qq', '1221206915@student.mmu.edu.my', 'logout', '2025-06-03 10:00:37'),
(181, 'qq', '1221206915@student.mmu.edu.my', 'login', '2025-06-03 10:03:13'),
(182, 'qq', '1221206915@student.mmu.edu.my', 'logout', '2025-06-03 10:03:19'),
(183, 'Daniel', 'sss@gmail.com', 'login', '2025-06-03 20:19:19'),
(184, 'Daniel', 'sss@gmail.com', 'logout', '2025-06-03 20:19:39'),
(185, 'Daniel', 'sss@gmail.com', 'login', '2025-06-05 23:52:33'),
(186, 'ggh', '', 'failed', '2025-06-06 20:46:33'),
(187, 'Daniel', 'sss@gmail.com', 'login', '2025-06-07 13:28:42'),
(188, 'Daniel', 'sss@gmail.com', 'login', '2025-06-08 21:59:34'),
(189, 'Daniel', 'admin@petshop.com', 'logout', '2025-06-08 21:59:59'),
(190, 'Daniel', 'sss@gmail.com', 'login', '2025-06-08 22:10:52'),
(191, 'Daniel', 'sss@gmail.com', 'login', '2025-06-08 22:18:52'),
(192, 'Daniel', 'sss@gmail.com', 'login', '2025-06-08 22:29:00'),
(193, 'Daniel', 'sss@gmail.com', 'logout', '2025-06-08 22:29:04'),
(194, 'Daniel', 'sss@gmail.com', 'login', '2025-06-08 22:32:56'),
(195, 'Daniel', 'admin@petshop.com', 'logout', '2025-06-08 22:33:09'),
(196, 'Daniel', 'sss@gmail.com', 'login', '2025-06-08 22:40:05'),
(197, 'Daniel', 'sss@gmail.com', 'login', '2025-06-08 22:44:42'),
(198, 'Daniel', 'sss@gmail.com', 'login', '2025-06-08 22:46:00'),
(199, 'Daniel', 'sss@gmail.com', 'logout', '2025-06-08 22:46:10'),
(200, 'Daniel', 'sss@gmail.com', 'login', '2025-06-08 22:46:50'),
(201, 'Daniel', 'admin@petshop.com', 'logout', '2025-06-08 22:47:11'),
(202, 'Daniel', 'sss@gmail.com', 'login', '2025-06-08 22:48:10'),
(203, 'Daniel', 'sss@gmail.com', 'logout', '2025-06-08 22:48:18'),
(204, 'Daniel', 'sss@gmail.com', 'login', '2025-06-08 22:48:26'),
(205, 'Daniel', 'sss@gmail.com', 'logout', '2025-06-08 22:48:48'),
(206, 'Daniel', 'sss@gmail.com', 'logout', '2025-06-08 22:49:20'),
(207, 'Daniel', 'sss@gmail.com', 'logout', '2025-06-08 22:55:33'),
(208, 'Daniel', 'sss@gmail.com', 'login', '2025-06-08 22:55:44'),
(209, 'Daniel', 'sss@gmail.com', 'logout', '2025-06-08 22:55:48'),
(210, 'Daniel', 'sss@gmail.com', 'login', '2025-06-08 22:57:25'),
(211, 'Daniel', 'admin@petshop.com', 'logout', '2025-06-08 22:57:47'),
(212, 'Daniel', 'sss@gmail.com', 'login', '2025-06-09 00:03:43'),
(213, 'Daniel', 'sss@gmail.com', 'logout', '2025-06-09 00:03:47'),
(214, 'Daniel', 'sss@gmail.com', 'login', '2025-06-09 00:03:59'),
(215, 'Daniel', 'sss@gmail.com', 'logout', '2025-06-09 00:04:06'),
(216, 'Daniel', 'sss@gmail.com', 'login', '2025-06-09 06:28:42');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `Order_ID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `PaymentMethod` varchar(255) NOT NULL,
  `Total` int(11) NOT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`Order_ID`, `Customer_ID`, `Address`, `PaymentMethod`, `Total`, `order_date`, `status`) VALUES
(10, 0, 'Recipient: Zhi Hao Chong\r\nAddress Line 1: 123, jalan ixora, taman ixora,  81300 austin, johor.\r\nCity: johor\r\nState: jb\r\nPostal Code: 81300\r\nCountry: Malaysia\r\nPhone: 0189655809', 'Credit Card', 20, '2025-05-21 09:33:28', 'Pending'),
(11, 1, 'Recipient: Zhi Hao \r\nAddress Line 1: 128, jalan ixora 1, taman ixora 12,  81300 austin, johor.\r\nCity: johor\r\nState: jb\r\nPostal Code: 81300\r\nCountry: Malaysia\r\nPhone: +1 (555) 123-4567', 'Credit Card', 40, '2025-05-24 22:43:11', 'Pending'),
(12, 0, 'Recipient: Zhi Hao Chong\r\nAddress Line 1: 123, jalan ixora, taman ixora,  81300 austin, johor.\r\nCity: johor\r\nState: jb\r\nPostal Code: 81300\r\nCountry: Malaysia\r\nPhone: 0189655809', 'Credit Card', 24, '2025-05-24 22:44:05', 'Pending'),
(13, 1, 'Recipient: Zhi Hao \r\nAddress Line 1: 128, jalan ixora 1, taman ixora 12,  81300 austin, johor.\r\nCity: johor\r\nState: jb\r\nPostal Code: 81300\r\nCountry: Malaysia\r\nPhone: +1 (555) 123-4567', 'Credit Card', 20, '2025-05-24 22:55:12', 'Pending'),
(14, 0, 'Recipient: Zhi Hao Chong\r\nAddress Line 1: 123, jalan ixora, taman ixora,  81300 austin, johor.\r\nCity: johor\r\nState: jb\r\nPostal Code: 81300\r\nCountry: Malaysia\r\nPhone: 0189655809', 'Credit Card', 20, '2025-05-24 22:56:17', 'Completed'),
(15, 1, 'Recipient: Zhi Hao \r\nAddress Line 1: 128, jalan ixora 1, taman ixora 12,  81300 austin, johor.\r\nCity: johor\r\nState: jb\r\nPostal Code: 81300\r\nCountry: Malaysia\r\nPhone: +1 (555) 123-4567', 'Credit Card', 8546, '2025-05-25 23:16:26', 'Pending'),
(16, 1, 'Recipient: Zhi Hao \r\nAddress Line 1: 128, jalan ixora 1, taman ixora 12,  81300 austin, johor.\r\nCity: johor\r\nState: jb\r\nPostal Code: 81300\r\nCountry: Malaysia\r\nPhone: +1 (555) 123-4567', 'Credit Card', 4, '2025-06-05 23:52:58', 'Disabled'),
(17, 1, 'Recipient: Zhi Hao \r\nAddress Line 1: 128, jalan ixora 1, taman ixora 12,  81300 austin, johor.\r\nCity: johor\r\nState: jb\r\nPostal Code: 81300\r\nCountry: Malaysia\r\nPhone: +1 (555) 123-4567', 'Debit Card', 9, '2025-06-07 13:29:28', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 2, 15, 1, 19.90, 19.90),
(2, 3, 9, 1, 4.50, 4.50),
(3, 4, 9, 1, 4.50, 4.50),
(4, 5, 10, 1, 175.00, 175.00),
(6, 7, 16, 1, 19.90, 19.90),
(7, 8, 16, 1, 19.90, 19.90),
(8, 9, 16, 1, 19.90, 19.90),
(9, 10, 16, 1, 19.90, 19.90),
(10, 11, 16, 2, 19.90, 39.80),
(11, 12, 9, 1, 4.50, 4.50),
(12, 12, 16, 1, 19.90, 19.90),
(13, 13, 16, 1, 19.90, 19.90),
(14, 14, 16, 1, 19.90, 19.90),
(15, 15, 12, 45, 189.90, 8545.50),
(16, 16, 9, 1, 4.50, 4.50),
(17, 17, 9, 1, 4.50, 4.50);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_status` varchar(20) NOT NULL DEFAULT 'Pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pet_categories`
--

CREATE TABLE `pet_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `Category` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `description`, `price`, `image_url`, `stock_quantity`, `Category`, `created_at`, `updated_at`) VALUES
(9, 'Probalance Pouch Tender Lamb 100g', 'Nutritious pouch food for dogs with tender lamb pieces', 4.50, 'ProBalance_tenderlamb.png', 97, 'Dogs', '2025-04-11 22:28:19', '2025-06-10 07:11:42'),
(10, 'Pedigree Complete Nutrition Roasted Chicken', 'Complete and balanced nutrition for adult dogs', 175.00, 'dog_product.png', 50, 'Dogs', '2025-04-11 22:28:19', '2025-04-28 04:34:46'),
(11, 'Pedigree Pouch 130g', 'Delicious wet food pouch for dogs', 3.00, 'Pedigree_pouch.png', 200, 'Dogs', '2025-04-11 22:28:19', '2025-04-28 04:34:50'),
(12, 'Royal Canin Medium Adult Dry Dog Food', 'Specially formulated for medium-sized adult dogs', 189.90, 'RoyalCanin.png', 0, 'Dogs', '2025-04-09 22:28:19', '2025-05-25 15:16:26'),
(13, 'Purina Pro Plan Puppy Food', 'Complete nutrition for growing puppies', 162.50, 'Purina.png', 60, 'Dogs', '2025-04-08 22:28:19', '2025-04-28 04:35:01'),
(14, 'Vitality Freeze-Dried Dog Treats', 'Premium freeze-dried meat treats', 25.00, 'Vital.png', 80, 'Dogs', '2025-04-06 22:28:19', '2025-04-28 04:35:06'),
(15, 'Dentastix Fresh Breath Dog Treats', 'Dental care treats that reduce tartar build-up', 19.90, 'Dentastix.png', 0, 'Dogs', '2025-04-01 22:28:19', '2025-05-07 04:32:11'),
(16, 'Whiskas Ocean Fish Flavour', 'Whiskas Adult 1+ Years Ocean Fish Flavour is a 100% nutritionally complete and balanced meal that has been carefully formulated to cater to the requirements of an adult cat\'s need. It contains tasty filled pocket kibbles, paired with quality poulty ingredients and loads of other essential nutrients that will help your cat lead a healthy, active and long life.', 19.90, 'uploads/whiskas-3d-1-2kg-fop-adult-oceanfish-2_1737115178558.png', 0, 'Cats', '2025-04-28 03:58:55', '2025-05-25 15:14:48');

-- --------------------------------------------------------

--
-- Table structure for table `shipping`
--

CREATE TABLE `shipping` (
  `shipping_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `shipping_method` varchar(50) NOT NULL,
  `shipping_cost` decimal(10,2) NOT NULL,
  `shipping_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `estimated_delivery` date DEFAULT NULL,
  `actual_delivery` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_settings`
--

CREATE TABLE `shop_settings` (
  `id` int(11) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `phone_number` varchar(50) NOT NULL,
  `opening_hours` varchar(255) NOT NULL,
  `address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shop_settings`
--

INSERT INTO `shop_settings` (`id`, `contact_email`, `phone_number`, `opening_hours`, `address`) VALUES
(1, 'zhihao013@gmail.com', '0189655809', 'Monday-Friday: 9AM-6PM', '123 Pet Street');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `Staff_ID` int(11) NOT NULL,
  `Staff_name` varchar(50) NOT NULL,
  `Staff_Username` varchar(50) NOT NULL,
  `Staff_Password` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `position` varchar(255) NOT NULL,
  `Staff_Email` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_failed_login` datetime DEFAULT NULL,
  `img_URL` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`Staff_ID`, `Staff_name`, `Staff_Username`, `Staff_Password`, `created_at`, `position`, `Staff_Email`, `status`, `reset_token`, `reset_token_expires`, `password_reset_token`, `token_expiry`, `login_attempts`, `last_failed_login`, `img_URL`) VALUES
(1, 'ggg', 'aa', '111111', '2025-04-05 06:23:09', '', 'ww@gmail.com', '', NULL, NULL, NULL, NULL, 0, NULL, NULL),
(2, 'q', 'qq', '22222222', '2025-04-05 08:29:59', 'Manager', 'ss@gmail.com', 'Inactive', NULL, NULL, NULL, NULL, 0, NULL, 'staff_avatars/2.png'),
(3, 'ggh', 'John', 'Hao1208@', '2025-04-16 01:07:24', 'Inventory Specialist', '1221206915@student.mmu.edu.my', 'Active', NULL, NULL, NULL, NULL, 0, NULL, 'staff_avatars/3_6841a27591241.png');

-- --------------------------------------------------------

--
-- Table structure for table `staff_login_logs`
--

CREATE TABLE `staff_login_logs` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('login','logout','failed') NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_login_logs`
--

INSERT INTO `staff_login_logs` (`id`, `staff_id`, `username`, `email`, `status`, `timestamp`, `ip_address`) VALUES
(1, 2, 'zz', 'ss@gmail.com', 'login', '2025-05-06 13:39:43', NULL),
(2, 2, 'zz', 'ss@gmail.com', 'login', '2025-05-06 13:45:07', NULL),
(3, 2, 'zz', 'ss@gmail.com', 'login', '2025-05-06 13:49:14', NULL),
(4, 2, 'zz', 'ss@gmail.com', 'logout', '2025-05-06 13:49:17', NULL),
(5, 2, 'zz', 'ss@gmail.com', 'login', '2025-05-06 13:56:04', NULL),
(6, 2, 'zz', 'ss@gmail.com', 'logout', '2025-05-06 13:56:13', NULL),
(7, 2, 'zz', 'ss@gmail.com', 'login', '2025-05-06 14:14:10', NULL),
(8, 2, 'zz', 'ss@gmail.com', 'logout', '2025-05-06 14:20:40', NULL),
(9, 2, 'zz', 'ss@gmail.com', 'login', '2025-05-06 14:28:52', NULL),
(10, 2, 'zz', 'ss@gmail.com', 'login', '2025-05-07 00:19:45', NULL),
(11, 2, 'zz', 'ss@gmail.com', 'login', '2025-05-07 00:59:46', NULL),
(12, 2, 'qq', 'ss@gmail.com', 'logout', '2025-05-07 00:59:51', NULL),
(13, 2, 'zz', 'ss@gmail.com', 'login', '2025-05-07 01:01:04', NULL),
(14, 2, 'qq', 'ss@gmail.com', 'logout', '2025-05-07 01:05:56', NULL),
(15, 2, 'zz', 'ss@gmail.com', 'login', '2025-05-07 01:08:02', NULL),
(16, 2, 'q', 'ss@gmail.com', 'logout', '2025-05-07 01:08:50', NULL),
(17, 2, 'zz', 'ss@gmail.com', 'login', '2025-05-07 01:08:56', NULL),
(18, 2, 'qqq', 'ss@gmail.com', 'login', '2025-05-07 01:42:27', NULL),
(19, 2, 'qqq', 'ss@gmail.com', 'login', '2025-05-07 02:15:38', NULL),
(20, 2, 'q', 'ss@gmail.com', 'logout', '2025-05-07 02:23:27', NULL),
(21, 2, 'qqq', 'ss@gmail.com', 'login', '2025-05-07 02:23:35', NULL),
(22, 2, 'q', 'ss@gmail.com', 'logout', '2025-05-07 02:57:35', NULL),
(23, 2, 'qq', 'ss@gmail.com', 'login', '2025-05-07 02:57:41', NULL),
(24, 2, 'q', 'ss@gmail.com', 'logout', '2025-05-07 03:18:20', NULL),
(25, 2, 'qq', 'ss@gmail.com', 'login', '2025-05-07 03:18:52', NULL),
(26, 2, 'q', 'ss@gmail.com', 'logout', '2025-05-07 03:20:25', NULL),
(27, 2, 'qq', 'ss@gmail.com', 'login', '2025-05-07 03:23:35', NULL),
(28, 2, 'q', 'ss@gmail.com', 'logout', '2025-05-07 03:39:00', NULL),
(29, 2, 'qq', 'ss@gmail.com', 'login', '2025-05-07 03:44:03', NULL),
(30, 2, 'q', 'ss@gmail.com', 'logout', '2025-05-07 04:15:17', NULL),
(31, 2, 'qq', 'ss@gmail.com', 'login', '2025-05-07 04:15:24', NULL),
(32, 2, 'q', 'ss@gmail.com', 'logout', '2025-05-07 04:16:34', NULL),
(33, 2, 'qq', 'ss@gmail.com', 'login', '2025-05-07 04:19:56', NULL),
(34, 2, 'q', 'ss@gmail.com', 'logout', '2025-05-07 04:20:07', NULL),
(35, 2, 'qq', 'ss@gmail.com', 'login', '2025-05-07 04:26:35', NULL),
(36, 2, 'q', 'ss@gmail.com', 'logout', '2025-05-07 04:32:29', NULL),
(37, 2, 'qq', 'ss@gmail.com', 'login', '2025-05-07 04:32:34', NULL),
(38, 2, 'q', 'ss@gmail.com', 'logout', '2025-05-07 04:40:51', NULL),
(39, 2, 'qq', 'ss@gmail.com', 'login', '2025-05-07 04:41:02', NULL),
(40, 2, 'q', 'ss@gmail.com', 'logout', '2025-05-07 04:51:07', NULL),
(41, 2, 'qq', 'ss@gmail.com', 'login', '2025-05-07 04:51:17', NULL),
(42, 2, 'qq', 'ss@gmail.com', 'login', '2025-05-14 01:44:30', NULL),
(43, 2, 'qq', 'ss@gmail.com', 'login', '2025-05-14 04:50:37', NULL),
(44, 2, 'q', 'ss@gmail.com', 'logout', '2025-05-14 08:05:30', NULL),
(45, 2, 'qq', 'ss@gmail.com', 'login', '2025-05-21 00:17:59', NULL),
(46, 2, 'q', 'ss@gmail.com', 'logout', '2025-05-21 01:32:00', NULL),
(47, 1, 'aa', 'ww@gmail.com', 'login', '2025-05-26 06:57:01', NULL),
(48, 1, 'ggg', 'ww@gmail.com', 'logout', '2025-05-26 07:17:02', NULL),
(49, 1, 'aa', 'ww@gmail.com', 'login', '2025-05-26 07:17:54', NULL),
(50, 1, 'aa', 'ww@gmail.com', 'login', '2025-05-26 07:18:57', NULL),
(51, 1, 'ggg', 'ww@gmail.com', 'logout', '2025-05-26 10:32:44', NULL),
(52, 3, 'ggh', 'you@gmail.com', 'login', '2025-06-03 01:49:16', NULL),
(53, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-03 01:59:46', NULL),
(54, 3, 'ggh', '1221206915@student.mmu.edu.my', 'login', '2025-06-03 02:00:56', NULL),
(55, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-03 02:01:01', NULL),
(56, 3, 'ggh', '1221206915@student.mmu.edu.my', 'failed', '2025-06-03 02:03:00', '::1'),
(57, 3, 'ggh', '1221206915@student.mmu.edu.my', 'failed', '2025-06-03 04:08:24', '::1'),
(58, 3, 'ggh', '1221206915@student.mmu.edu.my', 'failed', '2025-06-03 04:08:37', '::1'),
(59, 3, 'ggh', '1221206915@student.mmu.edu.my', 'login', '2025-06-03 04:08:49', NULL),
(60, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-03 04:10:37', NULL),
(61, 3, 'ggh', '1221206915@student.mmu.edu.my', 'login', '2025-06-03 12:31:57', NULL),
(62, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-03 12:32:01', NULL),
(63, 3, 'ggh', '1221206915@student.mmu.edu.my', 'failed', '2025-06-05 13:44:08', '::1'),
(64, 3, 'ggh', '1221206915@student.mmu.edu.my', 'login', '2025-06-05 13:45:07', NULL),
(65, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-05 14:18:08', NULL),
(66, 3, 'ggh', '1221206915@student.mmu.edu.my', 'failed', '2025-06-05 14:18:19', '::1'),
(67, 3, 'ggh', '1221206915@student.mmu.edu.my', 'login', '2025-06-05 14:18:34', NULL),
(68, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-05 14:40:12', NULL),
(69, 3, 'ggh', '1221206915@student.mmu.edu.my', 'login', '2025-06-05 15:15:43', NULL),
(70, 3, 'ggh', '1221206915@student.mmu.edu.my', 'login', '2025-06-06 12:48:23', NULL),
(71, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-06 12:48:29', NULL),
(72, 3, 'ggh', '1221206915@student.mmu.edu.my', 'login', '2025-06-06 12:48:41', NULL),
(73, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-06 13:11:26', NULL),
(74, 3, 'ggh', '1221206915@student.mmu.edu.my', 'login', '2025-06-06 13:11:42', NULL),
(75, 3, 'ggh', '1221206915@student.mmu.edu.my', 'login', '2025-06-06 13:12:31', NULL),
(76, 3, 'ggh', '1221206915@student.mmu.edu.my', 'login', '2025-06-06 13:31:05', NULL),
(77, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-06 13:31:16', NULL),
(78, 3, 'ggh', '1221206915@student.mmu.edu.my', 'login', '2025-06-06 13:31:28', NULL),
(79, 3, 'ggh', '1221206915@student.mmu.edu.my', 'login', '2025-06-06 13:31:39', NULL),
(80, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-06 13:38:54', NULL),
(81, 3, 'ggh', '1221206915@student.mmu.edu.my', 'login', '2025-06-06 13:39:03', NULL),
(82, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-06 13:39:31', NULL),
(83, 3, 'ggh', '1221206915@student.mmu.edu.my', 'login', '2025-06-06 13:39:39', NULL),
(84, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-06 13:39:42', NULL),
(85, 3, 'ggh', '1221206915@student.mmu.edu.my', 'login', '2025-06-07 03:38:07', NULL),
(86, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 03:38:22', NULL),
(88, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:12', NULL),
(89, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:12', NULL),
(90, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:13', NULL),
(91, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:13', NULL),
(92, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:14', NULL),
(93, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:14', NULL),
(94, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:14', NULL),
(95, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:14', NULL),
(96, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:14', NULL),
(97, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:15', NULL),
(98, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:15', NULL),
(99, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:15', NULL),
(100, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:15', NULL),
(101, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:15', NULL),
(102, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:16', NULL),
(103, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:16', NULL),
(104, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:16', NULL),
(105, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:16', NULL),
(106, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:16', NULL),
(107, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:02:55', NULL),
(109, 3, 'John', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:06:29', NULL),
(110, 3, 'John', '1221206915@student.mmu.edu.my', 'login', '2025-06-07 04:07:09', NULL),
(111, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-07 04:07:17', NULL),
(112, 3, 'John', '1221206915@student.mmu.edu.my', 'login', '2025-06-08 21:55:25', NULL),
(113, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-08 21:57:42', '::1'),
(114, 3, 'John', '1221206915@student.mmu.edu.my', 'login', '2025-06-08 23:13:24', NULL),
(115, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-09 02:57:21', '::1'),
(116, 3, 'John', '1221206915@student.mmu.edu.my', 'login', '2025-06-09 03:26:49', NULL),
(117, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-09 03:40:09', '::1'),
(118, 3, 'John', '1221206915@student.mmu.edu.my', 'login', '2025-06-09 04:09:16', NULL),
(119, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-09 04:09:29', '::1'),
(120, 3, 'John', '1221206915@student.mmu.edu.my', 'login', '2025-06-10 07:26:53', NULL),
(121, 3, 'ggh', '1221206915@student.mmu.edu.my', 'logout', '2025-06-10 08:02:45', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Admin_Username`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`Cart_ID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`Customer_id`);

--
-- Indexes for table `customer_address`
--
ALTER TABLE `customer_address`
  ADD PRIMARY KEY (`Address_ID`),
  ADD KEY `Customer_ID` (`Customer_ID`);

--
-- Indexes for table `customer_login_logs`
--
ALTER TABLE `customer_login_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`Order_ID`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `pet_categories`
--
ALTER TABLE `pet_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `shipping`
--
ALTER TABLE `shipping`
  ADD PRIMARY KEY (`shipping_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `shop_settings`
--
ALTER TABLE `shop_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`Staff_ID`);

--
-- Indexes for table `staff_login_logs`
--
ALTER TABLE `staff_login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD KEY `Customer_ID` (`Customer_ID`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `Customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `customer_address`
--
ALTER TABLE `customer_address`
  MODIFY `Address_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `customer_login_logs`
--
ALTER TABLE `customer_login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=218;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `Order_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `pet_categories`
--
ALTER TABLE `pet_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping`
--
ALTER TABLE `shipping`
  MODIFY `shipping_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shop_settings`
--
ALTER TABLE `shop_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `Staff_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `staff_login_logs`
--
ALTER TABLE `staff_login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `staff_login_logs`
--
ALTER TABLE `staff_login_logs`
  ADD CONSTRAINT `staff_login_logs_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`Staff_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
