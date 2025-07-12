-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 08, 2025 at 07:53 AM
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
-- Database: `sky_pharmacy`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_recommendations`
--

CREATE TABLE `ai_recommendations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `input_text` text NOT NULL,
  `ai_response` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_recommendations`
--

INSERT INTO `ai_recommendations` (`id`, `user_id`, `input_text`, `ai_response`, `created_at`) VALUES
(1, NULL, 'headech', '{\"success\":true,\"suggestions\":\"Based on your symptoms: \'headech\'\\n\\nRecommended medications:\\n1. Paracetamol\\n2. Ibuprofen\\n3. Vitamin C\\n\\nNote: This is a general recommendation. Please consult with a pharmacist for proper dosage and usage instructions.\",\"source\":\"fallback\"}', '2025-06-27 22:35:50'),
(2, NULL, 'headech', '{\"success\":true,\"suggestions\":\"Based on your symptoms: \'headech\'\\n\\nRecommended medications:\\n1. Paracetamol\\n2. Ibuprofen\\n3. Vitamin C\\n\\nNote: This is a general recommendation. Please consult with a pharmacist for proper dosage and usage instructions.\",\"source\":\"fallback\"}', '2025-06-27 22:37:43'),
(3, NULL, 'headech', '{\"success\":true,\"suggestions\":\"Based on your symptoms: \'headech\'\\n\\nRecommended medications:\\n1. Paracetamol\\n2. Ibuprofen\\n3. Vitamin C\\n\\nNote: This is a general recommendation. Please consult with a pharmacist for proper dosage and usage instructions.\",\"source\":\"fallback\"}', '2025-06-27 22:37:56'),
(4, NULL, 'fever\r\n', '{\"success\":true,\"suggestions\":\"Based on your symptoms: \'fever\\r\\n\'\\n\\nRecommended medications:\\n1. Paracetamol\\n2. Ibuprofen\\n\\nNote: This is a general recommendation. Please consult with a pharmacist for proper dosage and usage instructions.\",\"source\":\"fallback\"}', '2025-06-27 22:38:49'),
(5, NULL, 'fever\r\n', '{\"success\":true,\"suggestions\":\"Based on your symptoms: \'fever\\r\\n\'\\n\\nRecommended medications:\\n1. Paracetamol\\n2. Ibuprofen\\n\\nNote: This is a general recommendation. Please consult with a pharmacist for proper dosage and usage instructions.\",\"source\":\"fallback\"}', '2025-06-27 22:40:55'),
(6, NULL, 'fever\r\n', '{\"success\":true,\"suggestions\":\"Based on your symptoms: \'fever\\r\\n\'\\n\\nRecommended medications:\\n1. Paracetamol\\n2. Ibuprofen\\n\\nNote: This is a general recommendation. Please consult with a pharmacist for proper dosage and usage instructions.\",\"source\":\"fallback\"}', '2025-06-27 22:41:54'),
(7, NULL, 'stomach pean', '{\"success\":true,\"suggestions\":\"Based on your symptoms: \'stomach pean\'\\n\\nRecommended medications:\\n1. Paracetamol\\n2. Ibuprofen\\n3. Vitamin C\\n\\nNote: This is a general recommendation. Please consult with a pharmacist for proper dosage and usage instructions.\",\"source\":\"fallback\"}', '2025-06-27 22:42:34'),
(8, 4, 'stomach pean', '{\"success\":true,\"symptoms\":\"stomach pean\",\"suggestions\":\"For stomach pean, here are some general over-the-counter options:\\n\\n1. Acetaminophen (Tylenol) - 500-1000mg every 4-6 hours\\n   Take with or without food. Do not exceed 4000mg per day.\\n\\n2. Ibuprofen (Advil, Motrin) - 200-400mg every 4-6 hours\\n   Take with food or milk to prevent stomach upset.\\n\\n\\u26a0\\ufe0f IMPORTANT: This is general information only. Always consult a healthcare professional before taking any medication, especially for persistent or severe symptoms.\",\"source\":\"fallback\"}', '2025-06-27 22:49:37'),
(9, 4, 'cough\r\n', '{\"success\":true,\"symptoms\":\"cough\\r\\n\",\"suggestions\":\"Drug suggestions for cough:\\n\\n1. Dextromethorphan (Robitussin)\\n   Dosage: 15-30mg every 4-6 hours\\n   How to take: Take with or without food. Do not exceed 120mg per day.\\n   Precautions: Avoid if you have breathing problems.\\n   Side effects: Drowsiness, dizziness\\n\\n2. Guaifenesin (Mucinex)\\n   Dosage: 200-400mg every 4 hours\\n   How to take: Take with a full glass of water.\\n   Precautions: Stay well hydrated.\\n   Side effects: Nausea, vomiting\\n\\n\\u26a0\\ufe0f IMPORTANT: This is general information only. Always consult a healthcare professional before taking any medication.\",\"source\":\"fallback\"}', '2025-06-27 22:53:16'),
(10, 4, 'headech', '{\"success\":true,\"suggestions\":\"Based on your symptoms: \'headech\'\\n\\nRecommended medications:\\n1. Paracetamol\\n2. Ibuprofen\\n3. Vitamin C\\n\\nNote: This is a general recommendation. Please consult with a pharmacist for proper dosage and usage instructions.\",\"source\":\"fallback\"}', '2025-06-28 12:05:00'),
(11, 4, 'headech', '{\"success\":true,\"suggestions\":\"Based on your symptoms: \'headech\'\\n\\nRecommended medications:\\n1. Paracetamol\\n2. Ibuprofen\\n3. Vitamin C\\n\\nNote: This is a general recommendation. Please consult with a pharmacist for proper dosage and usage instructions.\",\"source\":\"fallback\"}', '2025-06-28 12:05:23'),
(12, 4, 'headech', '{\"success\":true,\"symptoms\":\"headech\",\"suggestions\":\"For headech, here are some general over-the-counter options:\\n\\n1. Acetaminophen (Tylenol) - 500-1000mg every 4-6 hours\\n   Take with or without food. Do not exceed 4000mg per day.\\n\\n2. Ibuprofen (Advil, Motrin) - 200-400mg every 4-6 hours\\n   Take with food or milk to prevent stomach upset.\\n\\n\\u26a0\\ufe0f IMPORTANT: This is general information only. Always consult a healthcare professional before taking any medication, especially for persistent or severe symptoms.\",\"source\":\"fallback\"}', '2025-06-28 12:06:59'),
(13, 4, 'headach', '{\"success\":true,\"symptoms\":\"headach\",\"suggestions\":\"For headach, here are some general over-the-counter options:\\n\\n1. Acetaminophen (Tylenol) - 500-1000mg every 4-6 hours\\n   Take with or without food. Do not exceed 4000mg per day.\\n\\n2. Ibuprofen (Advil, Motrin) - 200-400mg every 4-6 hours\\n   Take with food or milk to prevent stomach upset.\\n\\n\\u26a0\\ufe0f IMPORTANT: This is general information only. Always consult a healthcare professional before taking any medication, especially for persistent or severe symptoms.\",\"source\":\"fallback\"}', '2025-07-01 13:43:30'),
(14, 4, 'headach', '{\"success\":true,\"symptoms\":\"headach\",\"suggestions\":\"For headach, here are some general over-the-counter options:\\n\\n1. Acetaminophen (Tylenol) - 500-1000mg every 4-6 hours\\n   Take with or without food. Do not exceed 4000mg per day.\\n\\n2. Ibuprofen (Advil, Motrin) - 200-400mg every 4-6 hours\\n   Take with food or milk to prevent stomach upset.\\n\\n\\u26a0\\ufe0f IMPORTANT: This is general information only. Always consult a healthcare professional before taking any medication, especially for persistent or severe symptoms.\",\"source\":\"fallback\"}', '2025-07-01 14:10:17'),
(15, 4, 'headach', '{\"success\":true,\"symptoms\":\"headach\",\"suggestions\":\"For headach, here are some general over-the-counter options:\\n\\n1. Acetaminophen (Tylenol) - 500-1000mg every 4-6 hours\\n   Take with or without food. Do not exceed 4000mg per day.\\n\\n2. Ibuprofen (Advil, Motrin) - 200-400mg every 4-6 hours\\n   Take with food or milk to prevent stomach upset.\\n\\n\\u26a0\\ufe0f IMPORTANT: This is general information only. Always consult a healthcare professional before taking any medication, especially for persistent or severe symptoms.\",\"source\":\"fallback\"}', '2025-07-01 14:10:40'),
(16, 4, 'headach', '{\"success\":true,\"symptoms\":\"headach\",\"suggestions\":\"For headach, here are some general over-the-counter options:\\n\\n1. Acetaminophen (Tylenol) - 500-1000mg every 4-6 hours\\n   Take with or without food. Do not exceed 4000mg per day.\\n\\n2. Ibuprofen (Advil, Motrin) - 200-400mg every 4-6 hours\\n   Take with food or milk to prevent stomach upset.\\n\\n\\u26a0\\ufe0f IMPORTANT: This is general information only. Always consult a healthcare professional before taking any medication, especially for persistent or severe symptoms.\",\"source\":\"fallback\"}', '2025-07-01 16:39:12'),
(17, 4, 'stomach\r\n', '{\"success\":true,\"symptoms\":\"stomach\\r\\n\",\"suggestions\":\"For stomach, here are some general over-the-counter options:\\n\\n1. Acetaminophen (Tylenol) - 500-1000mg every 4-6 hours\\n   Take with or without food. Do not exceed 4000mg per day.\\n\\n2. Ibuprofen (Advil, Motrin) - 200-400mg every 4-6 hours\\n   Take with food or milk to prevent stomach upset.\\n\\n\\u26a0\\ufe0f IMPORTANT: This is general information only. Always consult a healthcare professional before taking any medication, especially for persistent or severe symptoms.\",\"source\":\"fallback\"}', '2025-07-01 16:50:04'),
(18, 4, 'stomach\r\n', '{\"success\":true,\"symptoms\":\"stomach\\r\\n\",\"suggestions\":\"For stomach, here are some general over-the-counter options:\\n\\n1. Acetaminophen (Tylenol) - 500-1000mg every 4-6 hours\\n   Take with or without food. Do not exceed 4000mg per day.\\n\\n2. Ibuprofen (Advil, Motrin) - 200-400mg every 4-6 hours\\n   Take with food or milk to prevent stomach upset.\\n\\n\\u26a0\\ufe0f IMPORTANT: This is general information only. Always consult a healthcare professional before taking any medication, especially for persistent or severe symptoms.\",\"source\":\"fallback\"}', '2025-07-01 17:04:47');

-- --------------------------------------------------------

--
-- Table structure for table `drugs`
--

CREATE TABLE `drugs` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `generic_name` varchar(200) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `dosage_form` varchar(50) DEFAULT NULL,
  `strength` varchar(50) DEFAULT NULL,
  `manufacturer` varchar(200) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `reorder_level` int(11) DEFAULT 10,
  `is_prescription_required` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drugs`
--

INSERT INTO `drugs` (`id`, `name`, `generic_name`, `category_id`, `description`, `dosage_form`, `strength`, `manufacturer`, `price`, `cost_price`, `stock_quantity`, `reorder_level`, `is_prescription_required`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Paracetamol', 'Acetaminophen', 1, 'Pain reliever and fever reducer', 'tablet', '500mg', 'Ethiopian Pharmaceutical Corporation', 5.00, 3.50, 997, 10, 0, 1, '2025-06-27 22:20:59', '2025-06-29 18:50:48'),
(2, 'Amoxicillin', 'Amoxicillin', 2, 'Antibiotic for bacterial infections', 'capsule', '500mg', 'Ethiopian Pharmaceutical Corporation', 25.00, 18.00, 499, 10, 1, 1, '2025-06-27 22:20:59', '2025-06-29 18:51:25'),
(3, 'Cetirizine', 'Cetirizine', 3, 'Antihistamine for allergies', 'tablet', '10mg', 'Ethiopian Pharmaceutical Corporation', 8.00, 5.50, 749, 10, 0, 1, '2025-06-27 22:20:59', '2025-06-29 15:02:09'),
(4, 'Ibuprofen', 'Ibuprofen', 1, 'Anti-inflammatory pain reliever', 'tablet', '400mg', 'Ethiopian Pharmaceutical Corporation', 7.00, 4.80, 797, 10, 0, 1, '2025-06-27 22:20:59', '2025-06-29 15:55:28'),
(5, 'Vitamin C', 'Ascorbic Acid', 5, 'Vitamin C supplement', 'tablet', '500mg', 'Ethiopian Pharmaceutical Corporation', 12.00, 8.00, 588, 10, 0, 1, '2025-06-27 22:20:59', '2025-06-29 18:50:48'),
(6, 'Shaine Rios', 'Adam Pratt', 1, 'Aut dolores non dolo', 'Aut labore consequat', 'Pariatur Inventore ', 'Doloremque quae in n', 997.00, 353.00, 709, 73, 1, 0, '2025-06-28 08:44:27', '2025-06-28 08:46:31');

-- --------------------------------------------------------

--
-- Table structure for table `drug_batches`
--

CREATE TABLE `drug_batches` (
  `id` int(11) NOT NULL,
  `drug_id` int(11) NOT NULL,
  `batch_number` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL,
  `expiry_date` date NOT NULL,
  `manufacturing_date` date DEFAULT NULL,
  `supplier` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drug_batches`
--

INSERT INTO `drug_batches` (`id`, `drug_id`, `batch_number`, `quantity`, `expiry_date`, `manufacturing_date`, `supplier`, `created_at`) VALUES
(1, 1, 'PAR001', 500, '2025-12-31', '2024-01-15', 'Ethiopian Pharmaceutical Corporation', '2025-06-27 22:20:59'),
(2, 1, 'PAR002', 500, '2025-12-31', '2024-02-15', 'Ethiopian Pharmaceutical Corporation', '2025-06-27 22:20:59'),
(3, 2, 'AMX001', 250, '2025-06-30', '2024-01-10', 'Ethiopian Pharmaceutical Corporation', '2025-06-27 22:20:59'),
(4, 3, 'CET001', 400, '2025-09-30', '2024-01-20', 'Ethiopian Pharmaceutical Corporation', '2025-06-27 22:20:59');

-- --------------------------------------------------------

--
-- Table structure for table `drug_categories`
--

CREATE TABLE `drug_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drug_categories`
--

INSERT INTO `drug_categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Analgesics', 'Pain relief medications', '2025-06-27 22:20:59'),
(2, 'Antibiotics', 'Medications to treat bacterial infections', '2025-06-27 22:20:59'),
(3, 'Antihistamines', 'Allergy and cold medications', '2025-06-27 22:20:59'),
(4, 'Antipyretics', 'Fever reducing medications', '2025-06-27 22:20:59'),
(5, 'Vitamins & Supplements', 'Nutritional supplements', '2025-06-27 22:20:59'),
(6, 'Cough & Cold', 'Medications for respiratory symptoms', '2025-06-27 22:20:59'),
(7, 'Gastrointestinal', 'Medications for stomach and digestive issues', '2025-06-27 22:20:59');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','processing','ready','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `payment_method` enum('cash','card','mobile_money') DEFAULT 'cash',
  `cashier_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `order_number`, `total_amount`, `status`, `payment_status`, `payment_method`, `cashier_id`, `created_at`, `updated_at`) VALUES
(1, 4, 'ORD-20250629-0817', 20.00, 'completed', 'paid', 'cash', 3, '2025-06-29 15:02:09', '2025-06-29 15:45:55'),
(2, 4, 'ORD-20250629-0614', 151.00, 'completed', 'paid', 'cash', 3, '2025-06-29 15:55:28', '2025-06-29 15:56:07'),
(3, 4, 'ORD-20250629-0115', 17.00, 'completed', 'paid', 'cash', 3, '2025-06-29 18:50:48', '2025-06-29 23:21:03'),
(4, 4, 'ORD-20250629-7185', 25.00, 'completed', 'paid', 'mobile_money', 3, '2025-06-29 18:51:25', '2025-06-29 22:39:41');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `drug_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `drug_id`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 1, 3, 1, 8.00, 8.00),
(2, 1, 5, 1, 12.00, 12.00),
(3, 2, 5, 10, 12.00, 120.00),
(4, 2, 1, 2, 5.00, 10.00),
(5, 2, 4, 3, 7.00, 21.00),
(6, 3, 5, 1, 12.00, 12.00),
(7, 3, 1, 1, 5.00, 5.00),
(8, 4, 2, 1, 25.00, 25.00);

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `pharmacist_id` int(11) DEFAULT NULL,
  `prescription_file` varchar(255) DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','dispensed') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `customer_id`, `pharmacist_id`, `prescription_file`, `symptoms`, `diagnosis`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 4, 2, 'prescription_4_1751064700.PDF', 'Dolore corporis obca', 'Ut sit dolor minus ', 'approved', 'Tenetur voluptatem ', '2025-06-27 22:51:40', '2025-06-29 15:50:59'),
(2, 4, 2, 'prescription_4_1751064765.PDF', 'Dolore corporis obca', 'Nostrum aut quo veli', 'dispensed', 'Repudiandae quae lab', '2025-06-27 22:52:45', '2025-06-29 14:59:13'),
(3, 4, NULL, NULL, 'Quae fugiat animi m', NULL, 'pending', NULL, '2025-06-29 17:33:49', '2025-06-29 17:33:49');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_items`
--

CREATE TABLE `prescription_items` (
  `id` int(11) NOT NULL,
  `prescription_id` int(11) NOT NULL,
  `drug_id` int(11) NOT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `frequency` varchar(100) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `cashier_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `final_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `order_id`, `cashier_id`, `total_amount`, `tax_amount`, `discount_amount`, `final_amount`, `created_at`) VALUES
(1, 1, 3, 20.00, 0.00, 0.00, 20.00, '2025-06-29 15:45:55'),
(2, 2, 3, 151.00, 0.00, 0.00, 151.00, '2025-06-29 15:56:07'),
(3, 4, 3, 25.00, 0.00, 0.00, 25.00, '2025-06-29 22:39:41'),
(4, 3, 3, 17.00, 0.00, 0.00, 17.00, '2025-06-29 23:21:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','pharmacist','cashier','customer') NOT NULL DEFAULT 'customer',
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `phone`, `address`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'admin', 'admin@skypharmacy.com', '$2y$10$Nm8IubqsSIUOgASsL0NiceDuNhSPhCSriXQ.1m7Sy2OnuSwQTcmLm', 'System Administrator', 'admin', '+251911234567', NULL, '2025-06-27 22:20:59', '2025-06-29 14:47:04', 1),
(2, 'pharmacist1', 'pharmacist@skypharmacy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Abebe Kebede', 'pharmacist', '+251922345678', NULL, '2025-06-27 22:20:59', '2025-06-27 22:20:59', 1),
(3, 'cashier1', 'cashier@skypharmacy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tigist Haile', 'cashier', '+251933456789', NULL, '2025-06-27 22:20:59', '2025-06-27 22:20:59', 1),
(4, 'sani', 'cusukob@mailinator.com', '$2y$10$Nm8IubqsSIUOgASsL0NiceDuNhSPhCSriXQ.1m7Sy2OnuSwQTcmLm', 'Iola Juarez', 'customer', '+1 (163) 658-4544', 'Est velit voluptate', '2025-06-27 22:29:51', '2025-06-27 22:29:51', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_recommendations`
--
ALTER TABLE `ai_recommendations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `drugs`
--
ALTER TABLE `drugs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `drug_batches`
--
ALTER TABLE `drug_batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `drug_id` (`drug_id`);

--
-- Indexes for table `drug_categories`
--
ALTER TABLE `drug_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `cashier_id` (`cashier_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `drug_id` (`drug_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `pharmacist_id` (`pharmacist_id`);

--
-- Indexes for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prescription_id` (`prescription_id`),
  ADD KEY `drug_id` (`drug_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `cashier_id` (`cashier_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_recommendations`
--
ALTER TABLE `ai_recommendations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `drugs`
--
ALTER TABLE `drugs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `drug_batches`
--
ALTER TABLE `drug_batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `drug_categories`
--
ALTER TABLE `drug_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `prescription_items`
--
ALTER TABLE `prescription_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_recommendations`
--
ALTER TABLE `ai_recommendations`
  ADD CONSTRAINT `ai_recommendations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `drugs`
--
ALTER TABLE `drugs`
  ADD CONSTRAINT `drugs_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `drug_categories` (`id`);

--
-- Constraints for table `drug_batches`
--
ALTER TABLE `drug_batches`
  ADD CONSTRAINT `drug_batches_ibfk_1` FOREIGN KEY (`drug_id`) REFERENCES `drugs` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`drug_id`) REFERENCES `drugs` (`id`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`pharmacist_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD CONSTRAINT `prescription_items_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`),
  ADD CONSTRAINT `prescription_items_ibfk_2` FOREIGN KEY (`drug_id`) REFERENCES `drugs` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
