-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 14, 2025 at 03:59 PM
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
(9, 'Probalance Pouch Tender Lamb 100g', 'Nutritious pouch food for dogs with tender lamb pieces', 4.50, 'ProBalance_tenderlamb.png', 90, 'Dogs', '2025-04-11 22:28:19', '2025-06-10 05:04:41'),
(10, 'Pedigree Complete Nutrition Roasted Chicken', 'Complete and balanced nutrition for adult dogs', 175.00, 'dog_product.png', 50, 'Dogs', '2025-04-11 22:28:19', '2025-04-28 04:34:46'),
(11, 'Pedigree Pouch 130g', 'Delicious wet food pouch for dogs', 3.00, 'Pedigree_pouch.png', 200, 'Dogs', '2025-04-11 22:28:19', '2025-04-28 04:34:50'),
(12, 'Royal Canin Medium Adult Dry Dog Food', 'Specially formulated for medium-sized adult dogs', 189.90, 'RoyalCanin.png', 0, 'Dogs', '2025-04-09 22:28:19', '2025-05-25 15:16:26'),
(13, 'Purina Pro Plan Puppy Food', 'Complete nutrition for growing puppies', 162.50, 'Purina.png', 60, 'Dogs', '2025-04-08 22:28:19', '2025-04-28 04:35:01'),
(14, 'Vitality Freeze-Dried Dog Treats', 'Premium freeze-dried meat treats', 25.00, 'Vital.png', 80, 'Dogs', '2025-04-06 22:28:19', '2025-04-28 04:35:06'),
(15, 'Dentastix Fresh Breath Dog Treats', 'Dental care treats that reduce tartar build-up', 19.90, 'Dentastix.png', 0, 'Dogs', '2025-04-01 22:28:19', '2025-05-07 04:32:11'),
(16, 'Whiskas Ocean Fish Flavour', 'Whiskas Adult 1+ Years Ocean Fish Flavour is a 100% nutritionally complete and balanced meal that has been carefully formulated to cater to the requirements of an adult cat\'s need. It contains tasty filled pocket kibbles, paired with quality poulty ingredients and loads of other essential nutrients that will help your cat lead a healthy, active and long life.', 19.90, 'uploads/whiskas-3d-1-2kg-fop-adult-oceanfish-2_1737115178558.png', 0, 'Cats', '2025-04-28 03:58:55', '2025-05-25 15:14:48'),
(17, 'Capuca Bird Travel Cage', 'A lightweight and secure travel cage for small to medium-sized birds. Features easy-to-clean materials, a comfortable perch, and excellent ventilation, ensuring your feathered friend travels in comfort and safety.', 85.50, 'Bird_cage.png', 30, 'Other', '2025-06-14 13:27:45', '2025-06-14 13:29:43'),
(18, 'Aqua Culture 10-Gallon Glass Aquarium Starter Kit', 'The Aqua Culture 10-Gallon Glass Aquarium Starter Kit is perfect for any new hobbyist. This kit includes a 10-gallon scratch-resistant glass aquarium, an energy-efficient LED lighting system, and a powerful filtration system featuring 3-stage filtration for sparkling clear water. It gives fish plenty of room to grow and allows you to stock up to 10 fish with a greater variety.', 159.90, 'fish_tank.png', 15, 'Other', '2025-06-14 13:39:57', '2025-06-14 13:40:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
