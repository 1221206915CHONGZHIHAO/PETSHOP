-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 05, 2025 at 05:38 AM
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
-- Database: `petshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--
-- Error reading structure for table petshop.admin: #1932 - Table &#039;petshop.admin&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.admin: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`admin`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `admin_settings`
--
-- Error reading structure for table petshop.admin_settings: #1932 - Table &#039;petshop.admin_settings&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.admin_settings: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`admin_settings`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--
-- Error reading structure for table petshop.audit_logs: #1932 - Table &#039;petshop.audit_logs&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.audit_logs: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`audit_logs`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--
-- Error reading structure for table petshop.cart: #1932 - Table &#039;petshop.cart&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.cart: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`cart`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--
-- Error reading structure for table petshop.customer: #1932 - Table &#039;petshop.customer&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.customer: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`customer`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `customer_address`
--
-- Error reading structure for table petshop.customer_address: #1932 - Table &#039;petshop.customer_address&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.customer_address: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`customer_address`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `customer_login_logs`
--
-- Error reading structure for table petshop.customer_login_logs: #1932 - Table &#039;petshop.customer_login_logs&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.customer_login_logs: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`customer_login_logs`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--
-- Error reading structure for table petshop.inventory: #1932 - Table &#039;petshop.inventory&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.inventory: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`inventory`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--
-- Error reading structure for table petshop.orders: #1932 - Table &#039;petshop.orders&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.orders: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`orders`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--
-- Error reading structure for table petshop.order_items: #1932 - Table &#039;petshop.order_items&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.order_items: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`order_items`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--
-- Error reading structure for table petshop.payments: #1932 - Table &#039;petshop.payments&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.payments: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`payments`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `pet_categories`
--
-- Error reading structure for table petshop.pet_categories: #1932 - Table &#039;petshop.pet_categories&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.pet_categories: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`pet_categories`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `products`
--
-- Error reading structure for table petshop.products: #1932 - Table &#039;petshop.products&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.products: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`products`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `promotion`
--
-- Error reading structure for table petshop.promotion: #1932 - Table &#039;petshop.promotion&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.promotion: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`promotion`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--
-- Error reading structure for table petshop.reviews: #1932 - Table &#039;petshop.reviews&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.reviews: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`reviews`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `shipping`
--
-- Error reading structure for table petshop.shipping: #1932 - Table &#039;petshop.shipping&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.shipping: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`shipping`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `shop_settings`
--
-- Error reading structure for table petshop.shop_settings: #1932 - Table &#039;petshop.shop_settings&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.shop_settings: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`shop_settings`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--
-- Error reading structure for table petshop.staff: #1932 - Table &#039;petshop.staff&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.staff: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`staff`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `staff_login_logs`
--
-- Error reading structure for table petshop.staff_login_logs: #1932 - Table &#039;petshop.staff_login_logs&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.staff_login_logs: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`staff_login_logs`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `staff_permissions`
--
-- Error reading structure for table petshop.staff_permissions: #1932 - Table &#039;petshop.staff_permissions&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.staff_permissions: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`staff_permissions`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--
-- Error reading structure for table petshop.wishlist: #1932 - Table &#039;petshop.wishlist&#039; doesn&#039;t exist in engine
-- Error reading data for table petshop.wishlist: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `petshop`.`wishlist`&#039; at line 1
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
