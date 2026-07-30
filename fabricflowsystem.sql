-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 05, 2024 at 09:40 AM
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
-- Database: `fabricflowwsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent','On Leave') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `employee_id`, `date`, `status`) VALUES
(7, 8, '2024-12-03', 'Present');

-- --------------------------------------------------------

--
-- Table structure for table `carriers`
--

CREATE TABLE `carriers` (
  `carrier_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `company` varchar(100) DEFAULT NULL,
  `date_of_registration` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carriers`
--

INSERT INTO `carriers` (`carrier_id`, `name`, `phone_number`, `email`, `company`, `date_of_registration`) VALUES
(6, 'Nadia Khan', '+1-202-555-0101', 'nadia.carrier@example.com', 'SwiftRoute Logistics', '2024-12-06'),
(7, 'Omar Reed', '+1-202-555-0102', 'omar.carrier@example.com', 'Northstar Freight', '2024-12-05');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `registration_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `name`, `email`, `phone_number`, `address`, `registration_date`) VALUES
(7, 'Avery Morgan', 'avery.customer@example.com', '+1-202-555-0111', '100 Demo Street', '2024-12-05'),
(8, 'Jordan Lee', 'jordan.customer@example.com', '+1-202-555-0112', '200 Sample Avenue', '2024-12-05');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL,
  `date_of_joining` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `name`, `email`, `role`, `date_of_joining`) VALUES
(8, 'Taylor Brooks', 'taylor.employee@example.com', 'Sewing Operator', '2024-12-06'),
(9, 'Casey Patel', 'casey.employee@example.com', 'Engineer', '2024-12-05');

-- --------------------------------------------------------

--
-- Table structure for table `fabrics`
--

CREATE TABLE `fabrics` (
  `fabric_id` int(11) NOT NULL,
  `fabric_name` varchar(255) NOT NULL,
  `fabric_type` varchar(255) NOT NULL,
  `price_per_meter` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fabrics`
--

INSERT INTO `fabrics` (`fabric_id`, `fabric_name`, `fabric_type`, `price_per_meter`, `stock_quantity`) VALUES
(7, 'Cotton', 'Synthetic', 0.09, 14),
(8, 'Denim', 'Natural', 10.00, 997);

-- --------------------------------------------------------

--
-- Table structure for table `machines`
--

CREATE TABLE `machines` (
  `machine_id` int(11) NOT NULL,
  `machine_name` varchar(255) NOT NULL,
  `machine_type` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `machines`
--

INSERT INTO `machines` (`machine_id`, `machine_name`, `machine_type`, `status`) VALUES
(6, 'Machine A', 'Swing', 'Active'),
(7, 'Machine B', 'Swing', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `machine_assignments`
--

CREATE TABLE `machine_assignments` (
  `assignment_id` int(11) NOT NULL,
  `machine_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `assigned_date` date DEFAULT (curdate())
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `machine_assignments`
--

INSERT INTO `machine_assignments` (`assignment_id`, `machine_id`, `employee_id`, `assigned_date`) VALUES
(5, 6, 8, '2024-12-05');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `carrier_id` int(11) DEFAULT NULL,
  `order_date` date NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `total_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `carrier_id`, `order_date`, `status`, `total_amount`) VALUES
(15, 7, 6, '2024-12-06', 'Processing', 0.36),
(17, 8, 7, '2024-12-05', 'pending', 20.09),
(19, 7, 6, '2024-12-06', 'pending', 10.09);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `fabric_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `fabric_id`, `quantity`) VALUES
(13, 15, 7, 4),
(14, 17, 7, 1),
(15, 17, 8, 2),
(16, 19, 7, 1),
(17, 19, 8, 1);

UPDATE `order_items` oi
JOIN `fabrics` f ON f.fabric_id = oi.fabric_id
SET oi.unit_price = f.price_per_meter;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `attendance_employee_date_unique` (`employee_id`, `date`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `carriers`
--
ALTER TABLE `carriers`
  ADD PRIMARY KEY (`carrier_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `fabrics`
--
ALTER TABLE `fabrics`
  ADD PRIMARY KEY (`fabric_id`);

--
-- Indexes for table `machines`
--
ALTER TABLE `machines`
  ADD PRIMARY KEY (`machine_id`);

--
-- Indexes for table `machine_assignments`
--
ALTER TABLE `machine_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD UNIQUE KEY `machine_employee_unique` (`machine_id`, `employee_id`),
  ADD KEY `machine_id` (`machine_id`),
  ADD KEY `machine_assignments_ibfk_2` (`employee_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `carrier_id` (`carrier_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `fabric_id` (`fabric_id`);

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
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `carriers`
--
ALTER TABLE `carriers`
  MODIFY `carrier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `fabrics`
--
ALTER TABLE `fabrics`
  MODIFY `fabric_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `machines`
--
ALTER TABLE `machines`
  MODIFY `machine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `machine_assignments`
--
ALTER TABLE `machine_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `machine_assignments`
--
ALTER TABLE `machine_assignments`
  ADD CONSTRAINT `machine_assignments_ibfk_1` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`machine_id`),
  ADD CONSTRAINT `machine_assignments_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`carrier_id`) REFERENCES `carriers` (`carrier_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`fabric_id`) REFERENCES `fabrics` (`fabric_id`) ON DELETE RESTRICT;

ALTER TABLE `fabrics`
  ADD CONSTRAINT `fabrics_price_nonnegative` CHECK (`price_per_meter` >= 0),
  ADD CONSTRAINT `fabrics_stock_nonnegative` CHECK (`stock_quantity` >= 0);

ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_quantity_positive` CHECK (`quantity` > 0),
  ADD CONSTRAINT `order_items_price_nonnegative` CHECK (`unit_price` >= 0);

UPDATE `orders`
SET `status` = CASE LOWER(`status`)
  WHEN 'pending' THEN 'Pending'
  WHEN 'processing' THEN 'Processing'
  WHEN 'completed' THEN 'Completed'
  WHEN 'cancelled' THEN 'Cancelled'
  ELSE 'Pending'
END;

ALTER TABLE `orders`
  MODIFY `status` enum('Pending','Processing','Completed','Cancelled') NOT NULL DEFAULT 'Pending';

ALTER TABLE `users` ADD `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `customers` ADD `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `carriers` ADD `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `employees` ADD `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `fabrics` ADD `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `machines` ADD `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `orders` ADD `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `attendance` ADD `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `machine_assignments` ADD `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
