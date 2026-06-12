-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 11, 2026 at 09:18 AM
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
-- Database: `maru_bake_house`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` int(11) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `category`, `price`, `description`, `image`) VALUES
(1, 'Original', 'Melted Cheese Tart', 50000, 'Our signature melted cheese tart with rich, creamy original cheese filling.', 'img/Original.png'),
(2, 'Double Cheese', 'Melted Cheese Tart', 55000, 'Extra cheese layer for cheese lovers. Double the creaminess, double the taste.', 'img/Double_Cheese.png'),
(3, 'Matcha', 'Melted Cheese Tart', 55000, 'Perfect blend of premium Japanese matcha and our signature smooth melted cheese.', 'img/Matcha_Tart.png'),
(4, 'Chocolate', 'Melted Cheese Tart', 50000, 'Rich Belgian chocolate melted beautifully inside a crispy tart shell.', 'img/Chocolate_Tart.png'),
(5, 'Iceberg Cheese', 'Melted Cheese Tart', 55000, 'Served chilled for a refreshing, ice-cream-like cheese sensation.', 'img/Iceberg_Cheese.png'),
(6, 'Pistachio', 'Melted Cheese Tart', 60000, 'Premium nutty pistachio flavor combined with our rich melted cheese cream.', 'img/Pistachio_Tart.png'),
(7, 'Original Loaf Cheesecake', 'Loaf Cheesecake', 40000, 'Classic smooth and velvety baked loaf cheesecake that melts in your mouth.', 'img/Original_Loaf_Cheesecake.png'),
(8, 'Matcha Loaf Cheesecake', 'Loaf Cheesecake', 50000, 'Infused with high-quality green tea matcha powder for a well-balanced bittersweet flavor.', 'img/Matcha_Loaf.png'),
(9, 'Chocolate Douce Loaf Cheesecake', 'Loaf Cheesecake', 45000, 'Gentle and sweet chocolate loaf cake, perfectly moist and rich.', 'img/Chocolate_Douce.png'),
(10, 'Blueberry Loaf Cheesecake', 'Loaf Cheesecake', 40000, 'Classic loaf cheesecake topped with fresh, tangy sweet blueberry compote.', 'img/Blueberry_Loaf.png'),
(11, 'Original Tiramisu', 'Tiramisu', 38000, 'Traditional Italian dessert made of ladyfingers dipped in coffee, layered with mascarpone.', 'img/Original_Tiramisu.png'),
(12, 'Matcha Tiramisu', 'Tiramisu', 45000, 'A modern twist of tiramisu using Japanese green tea extract instead of traditional coffee.', 'img/Matcha_Tiramisu.png'),
(13, 'Classic Glaze', 'Pumpkin Donut', 15000, 'Soft pumpkin donut coated with our signature sweet translucent sugar glaze.', 'img/Classic_Glaze.png'),
(14, 'Garlic Cheese', 'Pumpkin Donut', 23000, 'Savory pumpkin donut topped with aromatic garlic butter and melted cheese.', 'img/Garlic_Cheese.png'),
(15, 'Creme Brulee', 'Pumpkin Donut', 25000, 'Topped with custard cream and a layer of crispy, hard caramelized sugar.', 'img/Creme_Brulee.png'),
(16, 'Dunkies', 'Pumpkin Donut', 35000, 'Innovative combination of crunchy cookies texture and soft mochi pumpkin donut.', 'img/Dunkies.png'),
(17, 'Cinnamon', 'Pumpkin Donut', 15000, 'Dusted generously with sweet, aromatic cinnamon powder and fine sugar.', 'img/Cinnamon.png'),
(18, 'Beef Maple', 'Pumpkin Donut', 25000, 'Topped with savory, premium Indonesian beef floss (abon) and light mayonnaise.', 'img/Beef_Maple.png'),
(19, 'Ovamaltine Matcha', 'Pumpkin Donut', 28000, 'A unique sweet blast combining chocolate Ovaltine crunch and smooth matcha glaze.', 'img/Ovamaltine_Matcha.png'),
(20, 'Tuna Mayo', 'Pumpkin Donut', 25000, 'Savory donut option stuffed with creamy, seasoned tuna and mayonnaise mixture.', 'img/Tuna_Mayo.png'),
(21, 'Strawberry Cheesecake', 'Pumpkin Donut', 25000, 'Savory donut option stuffed with creamy, seasoned tuna and mayonnaise mixture.', 'img/Strawberry_Cheesecake.png'),
(22, 'Choco Pistachio Dubai Kunafa', 'Pumpkin Donut', 25000, 'Savory donut option stuffed with creamy, seasoned tuna and mayonnaise mixture.', 'img/Choco_Pistachio.png'),
(23, 'Vanilla Puff', 'Puffy Donut', 18000, 'Light and flaky choux pastry filled with smooth, creamy vanilla custard for a classic and timeless treat.', 'img/Vanilla_Puff.png'),
(24, 'Taro Puff', 'Puffy Donut', 18000, 'A crispy puff filled with rich, velvety taro cream, offering a naturally sweet and nutty flavor.', 'img/Taro_Puff.png'),
(25, 'Chocolate Puff', 'Puffy Donut', 18000, 'Golden pastry packed with luscious chocolate cream, delivering a rich and indulgent chocolate experience.', 'img/Chocolate_Puff.png'),
(26, 'Matcha Puff', 'Puffy Donut', 20000, 'Delicate choux pastry filled with premium matcha cream, balancing earthy green tea notes with subtle sweetness.', 'img/Matcha_Puff.png'),
(27, 'Strawberry Yoghurt Puff', 'Puffy Donut', 20000, 'A refreshing combination of sweet strawberry and tangy yoghurt cream inside a light, flaky puff pastry.', 'img/Strawberry_Yoghurt_Puff.png'),
(28, 'Pistachio Puff', 'Puffy Donut', 28000, 'Crispy pastry filled with luxurious pistachio cream, featuring a rich nutty flavor and smooth texture', 'img/Pistachio_Puff.png'),
(29, 'Vanilla Mochi', 'Mochi Donut', 23000, 'Chewy Japanese-style mochi donut glazed with sweet, aromatic vanilla bean paste.', 'img/Vanilla_Mochi.png'),
(30, 'Chocolate Mochi', 'Mochi Donut', 23000, 'Glazed with thick, decadent dark chocolate glaze on top of our signature chewy ring.', 'img/Chocolate_Mochi.png'),
(31, 'Taro Mochi', 'Mochi Donut', 23000, 'Visually beautiful and delicious purple taro root glaze with a sweet earthy flavor.', 'img/Taro_Mochi.png'),
(32, 'Strawberry Yoghurt Mochi', 'Mochi Donut', 25000, 'Perfect balance of sweet strawberry flavor and refreshing tangy yogurt glaze.', 'img/Strawberry_Yoghurt_Mochi.png'),
(33, 'Matcha Mochi', 'Mochi Donut', 25000, 'Authentic, slightly bitter Uji matcha glaze paired perfectly with the sweet chewy donut.', 'img/Matcha_Mochi.png'),
(34, 'Pistachio Mochi', 'Mochi Donut', 33000, 'Premium, rich roasted pistachio nut glaze. Sophisticated and highly addictive.', 'img/Pistachio_Mochi.png');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `review_text` text NOT NULL,
  `review_date` varchar(30) NOT NULL,
  `status` enum('pending','published') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `customer_name`, `review_text`, `review_date`, `status`) VALUES
(30, 'EXMING', 'da', '2026-06-11 12:38:26', 'pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
