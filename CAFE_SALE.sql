-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 29, 2025 at 09:22 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `CAFE_SALE`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `name`, `phone`, `email`, `created_at`) VALUES
(1, 'Aakash Kumar', '9123456789', 'aryan.kumar@gmail.com', '2025-04-24 18:04:51'),
(2, 'Lokesh Singh', '8123456780', 'anuragh.singh@hotmail.com', '2025-04-24 18:04:51'),
(3, 'Simran Kaur', '7123456781', 'simran.kaur@yahoo.com', '2025-04-24 18:04:51'),
(4, 'Preeti Sharma', '9234567892', 'preeti.sharma@gmail.com', '2025-04-24 18:04:51'),
(5, 'Rohit Verma', '9876543210', 'rohit.verma@gmail.com', '2025-04-24 18:04:51'),
(6, 'Bibek Thapa', '9123987654', 'bibek.thapa@yahoo.com', '2025-04-24 18:04:51'),
(7, 'Neeti Joshi', '9988776655', 'neeti.joshi@rocketmail.com', '2025-04-24 18:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `name`, `description`, `price`, `category`, `is_available`) VALUES
(1, 'Cappuccino', 'Espresso with steamed milk foam.', 120.00, 'Beverage', 1),
(2, 'Latte', 'Smooth espresso with steamed milk.', 130.00, 'Beverage', 1),
(3, 'Tea', 'Hot brewed tea.', 50.00, 'Beverage', 1),
(4, 'Coke', 'Chilled Coca-Cola.', 60.00, 'Beverage', 1),
(5, 'Muffin', 'Freshly baked muffin.', 70.00, 'Snack', 1),
(6, 'Pastry', 'Assorted sweet pastry.', 80.00, 'Snack', 1),
(7, 'Cheese Cake', 'Creamy cheesecake slice.', 150.00, 'Dessert', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_method` enum('Cash','Card','UPI') DEFAULT 'Cash',
  `status` enum('Pending','Paid','Cancelled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `user_id`, `order_date`, `total_amount`, `payment_method`, `status`) VALUES
(1, 1, 1, '2025-04-24 23:34:51', 190.00, 'Cash', 'Paid'),
(2, 2, 2, '2025-04-24 23:34:51', 190.00, 'UPI', 'Paid'),
(3, 3, 3, '2025-04-24 23:34:51', 180.00, 'Cash', 'Pending'),
(4, 4, 4, '2025-04-24 23:34:51', 210.00, 'UPI', 'Paid'),
(5, 5, 1, '2025-04-24 23:34:51', 130.00, 'Cash', 'Paid'),
(6, 6, 2, '2025-04-24 23:34:51', 200.00, 'UPI', 'Paid'),
(7, 7, 3, '2025-04-24 23:34:51', 120.00, 'Cash', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 120.00),
(2, 1, 5, 1, 70.00),
(3, 2, 2, 1, 130.00),
(4, 2, 4, 1, 60.00),
(5, 3, 3, 2, 50.00),
(6, 4, 7, 1, 150.00),
(7, 4, 6, 1, 80.00),
(8, 5, 3, 1, 50.00),
(9, 6, 2, 1, 130.00),
(10, 7, 1, 1, 120.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `email`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin', 'administrator', 'admin@gmail.com', '2025-04-24 23:34:51', '2025-04-29 18:38:35'),
(2, 'aryan', 'aryan123', 'staff', 'aryan@yahoo.com', '2025-04-24 23:34:51', '2025-04-29 18:38:42'),
(3, 'staff', 'staff', 'staff', 'staff@gmail.com', '2025-04-24 23:34:51', '2025-04-30 00:51:26'),
(4, 'manager', 'manager', 'manager', 'manager@hotmail.com', '2025-04-24 23:34:51', '2025-04-30 00:51:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `item_id` (`item_id`);

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
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
