-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2025 at 05:52 AM
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

CREATE TABLE `admin` (
  `Admin_Username` varchar(50) NOT NULL,
  `Admin_Email` varchar(50) NOT NULL,
  `Admin_Password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('staff','admin') NOT NULL,
  `action` varchar(50) NOT NULL,
  `table_affected` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `Cart_ID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Inventory_ID` int(11) NOT NULL,
  `Price` float NOT NULL,
  `Quantity` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`Cart_ID`, `Customer_ID`, `Inventory_ID`, `Price`, `Quantity`) VALUES
(1, 8, 9, 4.5, 2),
(8, 6, 15, 19.9, 1),
(9, 6, 9, 4.5, 4),
(10, 6, 11, 3, 3),
(11, 6, 12, 189.9, 2),
(12, 6, 10, 175, 1),
(13, 6, 13, 162.5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `Customer_ID` int(11) NOT NULL,
  `Customer_name` varchar(50) NOT NULL,
  `Customer_email` varchar(50) NOT NULL,
  `Customer_password` varchar(50) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`Customer_ID`, `Customer_name`, `Customer_email`, `Customer_password`, `profile_image`, `reset_token`, `reset_token_expires`) VALUES
(1, 'Daniel', 'sss@gmail.com', '12345', NULL, NULL, NULL),
(2, 'Amin', 'amin@mail.com', 'qwer', NULL, NULL, NULL),
(3, 'ggg', 'd@gmail.com', '11', NULL, NULL, NULL),
(6, 'HAO', 'zhihao013@gmail.com', '123456', NULL, '6a1b3bd0900196f668bcaa8e3843baf927a2481f6fe1a83f63000917445f5072', '2025-04-06 13:05:34'),
(7, 'David', 'zheya1810@gmail.com', '1234', '7_1745984877.png', NULL, NULL),
(8, 'hao1', 'zhihao0131@gmail.com', '11223344', NULL, NULL, NULL),
(10, 'aa', 'zhihao013@gmail.com', '123#123', NULL, NULL, NULL),
(11, 'Q', 'zhihao0113@gmail.com', 'ASAS11', NULL, NULL, NULL);

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
(1, 7, 'Home', 'DAVID HI ZHE YA', '0123456789', 'Ixora Apartment, Jalan D1, 75450 Malacca', '', 'Melaka', 'Melaka', '75450', 'Malaysia', 1);

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
(0, 'David', 'zheya1810@gmail.com', 'login', '2025-04-29 16:01:40'),
(0, 'David', 'zheya1810@gmail.com', 'logout', '2025-04-29 16:02:48'),
(0, 'David', 'zheya1810@gmail.com', 'login', '2025-04-29 16:02:55'),
(0, 'David', 'zheya1810@gmail.com', 'logout', '2025-04-29 16:26:24'),
(0, 'David', 'zheya1810@gmail.com', 'login', '2025-04-29 16:26:32'),
(0, 'David', 'zheya1810@gmail.com', 'logout', '2025-04-29 16:30:09'),
(0, 'David', 'zheya1810@gmail.com', 'login', '2025-04-29 16:50:42'),
(0, 'David', 'zheya1810@gmail.com', 'logout', '2025-04-29 19:54:56'),
(0, 'David', 'zheya1810@gmail.com', 'login', '2025-04-29 19:56:53'),
(0, 'David', 'zheya1810@gmail.com', 'login', '2025-04-30 11:44:07');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `Inventory_ID` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `Quantity` double NOT NULL,
  `Price` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `Order_ID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `PaymentMethod` varchar(255) NOT NULL,
  `Total` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(9, 'Probalance Pouch Tender Lamb 100g', 'Nutritious pouch food for dogs with tender lamb pieces', 4.50, 'ProBalance_tenderlamb.png', 100, 'Dogs', '2025-04-12 06:28:19', '2025-04-28 12:34:42'),
(10, 'Pedigree Complete Nutrition Roasted Chicken', 'Complete and balanced nutrition for adult dogs', 175.00, 'dog_product.png', 50, 'Dogs', '2025-04-12 06:28:19', '2025-04-28 12:34:46'),
(11, 'Pedigree Pouch 130g', 'Delicious wet food pouch for dogs', 3.00, 'Pedigree_pouch.png', 200, 'Dogs', '2025-04-12 06:28:19', '2025-04-28 12:34:50'),
(12, 'Royal Canin Medium Adult Dry Dog Food', 'Specially formulated for medium-sized adult dogs', 189.90, 'RoyalCanin.png', 45, 'Dogs', '2025-04-10 06:28:19', '2025-04-28 12:34:55'),
(13, 'Purina Pro Plan Puppy Food', 'Complete nutrition for growing puppies', 162.50, 'Purina.png', 60, 'Dogs', '2025-04-09 06:28:19', '2025-04-28 12:35:01'),
(14, 'Vitality Freeze-Dried Dog Treats', 'Premium freeze-dried meat treats', 25.00, 'Vital.png', 80, 'Dogs', '2025-04-07 06:28:19', '2025-04-28 12:35:06'),
(15, 'Dentastix Fresh Breath Dog Treats', 'Dental care treats that reduce tartar build-up', 19.90, 'Dentastix.png', 120, 'Dogs', '2025-04-02 06:28:19', '2025-04-28 12:35:09'),
(16, 'Whiskas Ocean Fish Flavour', 'Whiskas Adult 1+ Years Ocean Fish Flavour is a 100% nutritionally complete and balanced meal that has been carefully formulated to cater to the requirements of an adult cat\'s need. It contains tasty filled pocket kibbles, paired with quality poulty ingredients and loads of other essential nutrients that will help your cat lead a healthy, active and long life.', 19.90, 'uploads/whiskas-3d-1-2kg-fop-adult-oceanfish-2_1737115178558.png', 20, 'Cats', '2025-04-28 11:58:55', '2025-04-28 11:58:55');

-- --------------------------------------------------------

--
-- Table structure for table `promotion`
--

CREATE TABLE `promotion` (
  `promo_code` varchar(50) NOT NULL,
  `discount` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `usage_limit` int(11) NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `review_text` text DEFAULT NULL,
  `review_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `shop_name` varchar(100) NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `admin_email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shop_settings`
--

INSERT INTO `shop_settings` (`id`, `shop_name`, `contact_email`, `admin_email`) VALUES
(1, 'PetShop', 'contact@petshop.com', 'admin@petshop.com');

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
  `last_failed_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`Staff_ID`, `Staff_name`, `Staff_Username`, `Staff_Password`, `created_at`, `position`, `Staff_Email`, `status`, `reset_token`, `reset_token_expires`, `password_reset_token`, `token_expiry`, `login_attempts`, `last_failed_login`) VALUES
(1, 'ggg', 'aa', '111111', '2025-04-05 06:23:09', 'Manager', 'ww@gmail.com', 'Active', NULL, NULL, NULL, NULL, 0, NULL),
(2, 'zz', 'zz', '11111111', '2025-04-05 08:29:59', 'Manager', 'ss@gmail.com', 'Active', NULL, NULL, NULL, NULL, 0, NULL),
(3, 'ggh', 'ggh', '111111112', '2025-04-15 17:07:24', 'Inventory Specialist', 'you@gmail.com', 'Active', NULL, NULL, NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `staff_permissions`
--

CREATE TABLE `staff_permissions` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `permission` varchar(50) NOT NULL,
  `granted_by` int(11) NOT NULL COMMENT 'Admin who granted this',
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`Cart_ID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`Customer_ID`);

--
-- Indexes for table `customer_address`
--
ALTER TABLE `customer_address`
  ADD PRIMARY KEY (`Address_ID`),
  ADD KEY `Customer_ID` (`Customer_ID`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`Inventory_ID`),
  ADD UNIQUE KEY `product_id` (`product_id`);

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
-- Indexes for table `pet_categories`
--
ALTER TABLE `pet_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `Customer_ID` (`Customer_ID`);

--
-- Indexes for table `shipping`
--
ALTER TABLE `shipping`
  ADD PRIMARY KEY (`shipping_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`Staff_ID`);

--
-- Indexes for table `staff_permissions`
--
ALTER TABLE `staff_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `granted_by` (`granted_by`);

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
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `Cart_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `Customer_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `customer_address`
--
ALTER TABLE `customer_address`
  MODIFY `Address_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `Inventory_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `Order_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pet_categories`
--
ALTER TABLE `pet_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping`
--
ALTER TABLE `shipping`
  MODIFY `shipping_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `Staff_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `staff_permissions`
--
ALTER TABLE `staff_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer_address`
--
ALTER TABLE `customer_address`
  ADD CONSTRAINT `fk_customer_address` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
