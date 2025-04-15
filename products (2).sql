-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 15, 2025 at 11:30 AM
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
(9, 'Probalance Pouch Tender Lamb 100g', 'Nutritious pouch food for dogs with tender lamb pieces', 4.50, 'ProBalance_tenderlamb.png', 100, 'Dog > Wet Food', '2025-04-12 06:28:19', '2025-04-12 06:50:05'),
(10, 'Pedigree Complete Nutrition Roasted Chicken', 'Complete and balanced nutrition for adult dogs', 175.00, 'dog_product.png', 50, 'Dog > Dry Food', '2025-04-12 06:28:19', '2025-04-12 06:28:19'),
(11, 'Pedigree Pouch 130g', 'Delicious wet food pouch for dogs', 3.00, 'Pedigree_pouch.png', 200, 'Dog > Wet Food', '2025-04-12 06:28:19', '2025-04-12 06:28:19'),
(12, 'Royal Canin Medium Adult Dry Dog Food', 'Specially formulated for medium-sized adult dogs', 189.90, 'RoyalCanin.png', 45, 'Dog > Dry Food', '2025-04-10 06:28:19', '2025-04-12 06:31:10'),
(13, 'Purina Pro Plan Puppy Food', 'Complete nutrition for growing puppies', 162.50, 'Purina.png', 60, 'Dog > Dry Food', '2025-04-09 06:28:19', '2025-04-12 06:32:34'),
(14, 'Vitality Freeze-Dried Dog Treats', 'Premium freeze-dried meat treats', 25.00, 'Vital.png', 80, 'Dog > Treats', '2025-04-07 06:28:19', '2025-04-12 06:33:24'),
(15, 'Dentastix Fresh Breath Dog Treats', 'Dental care treats that reduce tartar build-up', 19.90, 'Dentastix.png', 120, 'Dog > Treats', '2025-04-02 06:28:19', '2025-04-12 06:34:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
