-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: db_tewuneed2
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `db_tewuneed2`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `db_tewuneed2` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `db_tewuneed2`;

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` enum('admin','user') DEFAULT 'user',
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','staff') DEFAULT 'admin',
  `avatar` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (1,'admin','admin@tewuneed.com','$2y$12$HqvoEtHbf9yg5yF9b4ryLOIHGHwfoGixz4.gq6kz.ng.LByxy20o6','Administrator','super_admin',NULL,'active',NULL,'2026-10-24 08:28:34','2026-10-24 09:13:37');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Electronics','electronics','Latest gadgets and electronic devices for your modern lifestyle.',NULL,NULL,0,'active','2026-10-24 08:28:34','2026-10-24 08:28:34'),(2,'Cosmetics','cosmetics','Natural and organic skincare products for your daily beauty routine.',NULL,NULL,0,'active','2026-10-24 08:28:34','2026-10-24 08:28:34'),(3,'Sports','sports','High-quality sports equipment and accessories for your active lifestyle.',NULL,NULL,0,'active','2026-10-24 08:28:34','2026-10-24 08:28:34'),(4,'Food & Snacks','food-snacks','From farm-fresh milk to artisanal cheeses and creamy yoghurts.',NULL,NULL,0,'active','2026-10-24 08:28:34','2026-10-24 08:28:34'),(5,'Health & Medicine','health-medicine','Trusted pharmaceutical products for your health and wellness needs.',NULL,NULL,0,'active','2026-10-24 08:28:34','2026-10-24 08:28:34'),(6,'Vegetables','vegetables','Fresh, organic vegetables sourced directly from local farmers.',NULL,NULL,0,'active','2026-10-24 08:28:34','2026-10-24 08:28:34');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupon_usage`
--

DROP TABLE IF EXISTS `coupon_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `discount_amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `coupon_id` (`coupon_id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `coupon_usage_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupon_usage`
--

LOCK TABLES `coupon_usage` WRITE;
/*!40000 ALTER TABLE `coupon_usage` DISABLE KEYS */;
INSERT INTO `coupon_usage` VALUES (1,1,1,1,22500.00,'2026-10-24 08:59:24');
/*!40000 ALTER TABLE `coupon_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `type` enum('percentage','fixed') DEFAULT 'percentage',
  `value` decimal(15,2) NOT NULL,
  `min_amount` decimal(15,2) DEFAULT 0.00,
  `max_uses` int(11) DEFAULT 0,
  `used_count` int(11) DEFAULT 0,
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
INSERT INTO `coupons` VALUES (1,'WELCOME15','percentage',15.00,50000.00,1000,1,NULL,NULL,'active','2026-10-24 08:28:35','2026-10-24 08:59:24'),(2,'DISKON10K','fixed',10000.00,100000.00,500,0,NULL,NULL,'active','2026-10-24 08:28:35','2026-10-24 08:28:35');
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,13,2,75000.00,150000.00,'2026-10-24 08:59:24');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_status_history`
--

DROP TABLE IF EXISTS `order_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `order_status_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status_history`
--

LOCK TABLES `order_status_history` WRITE;
/*!40000 ALTER TABLE `order_status_history` DISABLE KEYS */;
INSERT INTO `order_status_history` VALUES (1,1,'pending','Order placed',NULL,'2026-10-24 08:59:24');
/*!40000 ALTER TABLE `order_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','completed','cancelled','refunded') DEFAULT 'pending',
  `total_amount` decimal(15,2) NOT NULL,
  `shipping_amount` decimal(15,2) DEFAULT 0.00,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `discount_amount` decimal(15,2) DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_date` datetime DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `billing_address` text DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'TWN-20261024-CB1C17',1,'pending',157500.00,15000.00,15000.00,22500.00,'Transfer Bank BCA','pending',NULL,'Budi Pratama\nJl. Kebon Jeruk No. 45\nJakarta Barat, DKI Jakarta 11530\nIndonesia\nTelp: 08123456789','Budi Pratama\nJl. Kebon Jeruk No. 45\nJakarta Barat, DKI Jakarta 11530\nIndonesia\nTelp: 08123456789',NULL,'','2026-10-24 08:59:24','2026-10-24 08:59:24');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('bank_transfer','e_wallet','credit_card','cod') DEFAULT 'bank_transfer',
  `description` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `fee_percentage` decimal(5,2) DEFAULT 0.00,
  `fee_fixed` decimal(15,2) DEFAULT 0.00,
  `status` enum('active','inactive') DEFAULT 'active',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_methods`
--

LOCK TABLES `payment_methods` WRITE;
/*!40000 ALTER TABLE `payment_methods` DISABLE KEYS */;
INSERT INTO `payment_methods` VALUES (1,'Transfer Bank BCA','bank_transfer',NULL,NULL,0.00,0.00,'active',1,'2026-10-24 08:28:35'),(2,'Transfer Bank Mandiri','bank_transfer',NULL,NULL,0.00,0.00,'active',2,'2026-10-24 08:28:35'),(3,'Transfer Bank BNI','bank_transfer',NULL,NULL,0.00,0.00,'active',3,'2026-10-24 08:28:35'),(4,'OVO','e_wallet',NULL,NULL,0.00,0.00,'active',4,'2026-10-24 08:28:35'),(5,'GoPay','e_wallet',NULL,NULL,0.00,0.00,'active',5,'2026-10-24 08:28:35'),(6,'DANA','e_wallet',NULL,NULL,0.00,0.00,'active',6,'2026-10-24 08:28:35'),(7,'COD (Bayar di Tempat)','cod',NULL,NULL,0.00,0.00,'active',7,'2026-10-24 08:28:35');
/*!40000 ALTER TABLE `payment_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_reviews`
--

DROP TABLE IF EXISTS `product_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` tinyint(1) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `review_text` text DEFAULT NULL,
  `verified_purchase` tinyint(1) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_reviews`
--

LOCK TABLES `product_reviews` WRITE;
/*!40000 ALTER TABLE `product_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `sale_price` decimal(15,2) DEFAULT NULL,
  `cost_price` decimal(15,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `min_order_quantity` int(11) DEFAULT 1,
  `weight` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','draft') DEFAULT 'active',
  `views` int(11) DEFAULT 0,
  `sold_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`),
  KEY `featured` (`featured`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,4,'Pensil 2B Joyko','pensil-2b-joyko',NULL,NULL,NULL,3500.00,NULL,NULL,50,1,NULL,'home gambar/pensil 2B joyko.jpeg',NULL,1,'active',0,0,'2026-10-24 08:28:34','2026-10-24 08:36:08'),(2,4,'Teh Botol Sosro','teh-botol-sosro',NULL,NULL,NULL,6000.00,NULL,NULL,100,1,NULL,'home gambar/teh botol sosro.jpeg',NULL,1,'active',0,0,'2026-10-24 08:28:34','2026-10-24 08:36:08'),(3,4,'Buku Sidu A4 70gr','buku-sidu-a4-70gr',NULL,NULL,NULL,35000.00,NULL,NULL,30,1,NULL,'home gambar/Buku Sidu A4 70gr.jpeg',NULL,1,'active',0,0,'2026-10-24 08:28:34','2026-10-24 08:36:08'),(4,4,'Kopi Kapal Api','kopi-kapal-api',NULL,NULL,NULL,12000.00,NULL,NULL,75,1,NULL,'home gambar/Kopi Kapal Api.jpeg',NULL,1,'active',0,0,'2026-10-24 08:28:34','2026-10-24 08:36:08'),(5,4,'Stabilo Boss','stabilo-boss',NULL,NULL,NULL,15000.00,NULL,NULL,40,1,NULL,'home gambar/Stabilo Boss.jpeg',NULL,1,'active',0,0,'2026-10-24 08:28:34','2026-10-24 08:36:08'),(6,4,'Beng-Beng Drink','beng-beng-drink',NULL,NULL,NULL,7500.00,NULL,NULL,60,1,NULL,'home gambar/Beng-Beng Drink.jpeg',NULL,1,'active',0,0,'2026-10-24 08:28:34','2026-10-24 08:36:08'),(7,4,'Pensil Warna FC','pensil-warna-fc',NULL,NULL,NULL,45000.00,NULL,NULL,25,1,NULL,'home gambar/Pensil Warna FC.jpeg',NULL,1,'active',0,0,'2026-10-24 08:28:34','2026-10-24 08:36:08'),(8,4,'Teh Pucuk Harum','teh-pucuk-harum',NULL,NULL,NULL,5500.00,NULL,NULL,80,1,NULL,'home gambar/Teh Pucuk Harum.jpeg',NULL,1,'active',0,0,'2026-10-24 08:28:34','2026-10-24 08:36:08'),(9,4,'Binder Clip Kenko','binder-clip-kenko',NULL,NULL,NULL,12000.00,NULL,NULL,45,1,NULL,'home gambar/Binder Clip Kenko.jpeg',NULL,1,'active',0,0,'2026-10-24 08:28:34','2026-10-24 08:36:08'),(10,4,'Kopi ABC Susu','kopi-abc-susu',NULL,NULL,NULL,2500.00,NULL,NULL,120,1,NULL,'home gambar/Kopi ABC Susu.jpeg',NULL,1,'active',0,0,'2026-10-24 08:28:34','2026-10-24 08:36:08'),(11,4,'Penggaris Butterfly','penggaris-butterfly',NULL,NULL,NULL,8000.00,NULL,NULL,35,1,NULL,'home gambar/Penggaris Butterfly.png',NULL,1,'active',0,0,'2026-10-24 08:28:34','2026-10-24 08:36:08'),(12,4,'Good Day Freeze','good-day-freeze',NULL,NULL,NULL,10000.00,NULL,NULL,65,1,NULL,'home gambar/Good Day Freeze.jpeg',NULL,1,'active',0,0,'2026-10-24 08:28:34','2026-10-24 08:36:08'),(13,2,'Natural Glow Serum Skincare','natural-glow-serum-skincare',NULL,'Serum pencerah kulit alami dengan ekstrak herbal.','Serum wajah berkualitas tinggi yang menutrisi kulit secara mendalam, mencerahkan dan menjaga kelembaban alami sepanjang hari.',85000.00,75000.00,NULL,43,1,NULL,'Cosmetics/cosmetics1.jpg',NULL,1,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:59:24'),(14,2,'Hydrating Face Cream Day & Night','hydrating-face-cream-day-night',NULL,'Krim pelembab wajah 24 jam non-komedogenik.','Krim pelembab lembut dengan formula ringan cepat meresap untuk perlindungan maksimal kulit wajah dari radikal bebas.',95000.00,82000.00,NULL,30,1,NULL,'Cosmetics/cosmetics2.jpg',NULL,1,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08'),(15,2,'Gentle Facial Wash Foam','gentle-facial-wash-foam',NULL,'Pembersih wajah busa lembut pH seimbang.','Membersihkan kotoran dan minyak berlebih tanpa membuat kulit kering. Cocok untuk semua jenis kulit.',48000.00,NULL,NULL,50,1,NULL,'Cosmetics/cosmetics3.jpg',NULL,0,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08'),(16,2,'Matte Velvet Lip Cream Shade 04','matte-velvet-lip-cream-shade-04',NULL,'Lip cream tahan lama dengan hasil akhir lembut.','Formula tahan air dan transfer-proof dengan warna cerah natural yang menawan seharian.',62000.00,52000.00,NULL,40,1,NULL,'Cosmetics/cosmetics14.jpg',NULL,0,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08'),(17,6,'Wortel Segar Organik 500g','wortel-segar-organik-500g',NULL,'Wortel manis segar langsung dari petani lokal.','Wortel pilihan berkualitas prima kaya vitamin A, dipetik segar setiap pagi dari perkebunan organik terpercaya.',14000.00,12000.00,NULL,80,1,NULL,'Vegetables/veg1.jpeg',NULL,1,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08'),(18,6,'Brokoli Hijau Segar 250g','brokoli-hijau-segar-250g',NULL,'Brokoli hijau segar renyah bebas pestisida.','Kaya antioksidan dan serat. Sempurna untuk salad sehat, tumisan lezat, atau sup keluarga.',18000.00,NULL,NULL,40,1,NULL,'Vegetables/veg3.jpg',NULL,1,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08'),(19,6,'Tomat Merah Segar Pilihan 1kg','tomat-merah-segar-pilihan-1kg',NULL,'Tomat merah segar juicy kaya vitamin C.','Tomat kualitas terbaik cocok untuk aneka masakan, jus segar, atau sambal rumahan istimewa.',16000.00,14500.00,NULL,60,1,NULL,'Vegetables/veg7.jpg',NULL,0,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08'),(20,6,'Paprika Hijau Segar 300g','paprika-hijau-segar-300g',NULL,'Paprika segar tebal dan renyah.','Paprika hijau segar dengan cita rasa renyah manis alami, pas untuk tumis sapi atau hidangan pasta.',22000.00,NULL,NULL,35,1,NULL,'Vegetables/veg8.jpg',NULL,0,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08'),(21,4,'Indomie Goreng Spesial (Dus 40 pcs)','indomie-goreng-spesial-dus',NULL,'Mie instan legendaris rasa original kebanggaan Indonesia.','Indomie Goreng dengan bumbu gurih komplit, bawang goreng renyah, dan kecap manis asli.',118000.00,108000.00,NULL,70,1,NULL,'products/Indomie Goreng.jpeg',NULL,1,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08'),(22,4,'Susu Segar Fresh Milk Dairy 1L','susu-segar-fresh-milk-dairy-1l',NULL,'Susu sapi murni pasteurisasi kaya kalsium.','Diolah dari susu sapi pilihan dengan standar higienis tinggi untuk menjaga nutrisi alami keluarga.',24000.00,NULL,NULL,50,1,NULL,'Dairy/milk1.jpeg',NULL,1,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08'),(23,4,'Keju Cheddar Olahan Serbaguna 170g','keju-cheddar-olahan-serbaguna-170g',NULL,'Keju cheddar gurih lembut mudah diparut.','Cocok untuk taburan martabak, roti bakar, kue kering, atau campuran masakan favorit Anda.',21000.00,19000.00,NULL,65,1,NULL,'Dairy/cheese1.jpg',NULL,0,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08'),(24,4,'Chitato Sapi Panggang 68g','chitato-sapi-panggang-68g',NULL,'Keripik kentang bergelombang rasa sapi panggang.','Renyahnya kentang asli dipadu dengan bumbu daging sapi panggang yang kaya rasa.',11500.00,NULL,NULL,90,1,NULL,'products/Chitato.jpeg',NULL,0,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08'),(25,1,'Electric Kettle Pemanas Air 1.8L Stainless','electric-kettle-pemanas-air-1-8l',NULL,'Ketel listrik stainless steel cepat panas hemat daya.','Dilengkapi fitur auto off saat mendidih, bahan food grade 304, dan kabel putar 360 derajat.',145000.00,125000.00,NULL,25,1,NULL,'Elektronik.jpg',NULL,1,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08'),(26,1,'Smart Hand Blender & Chopper 4-in-1','smart-hand-blender-chopper-4-in-1',NULL,'Blender tangan multifungsi untuk bumbu dan jus.','Motor bertenaga tinggi dengan pisau titanium tahan karat. Mudah dibersihkan dan praktis disimpan.',235000.00,199000.00,NULL,20,1,NULL,'Elektronik.jpg',NULL,0,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08'),(27,3,'Matras Yoga Anti-Slip Premium TPE 6mm','matras-yoga-anti-slip-premium-tpe-6mm',NULL,'Matras senam yoga lentur anti slip ramah lingkungan.','Ketebalan ideal 6mm melindungi sendi saat berolahraga. Dilengkapi tas jaring praktis untuk dibawa.',110000.00,95000.00,NULL,35,1,NULL,'olahraga.jpg',NULL,1,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08'),(28,3,'Dumbbell Set Karet 5kg (Sepasang)','dumbbell-set-karet-5kg-sepasang',NULL,'Barbell dumbbell lapis karet nyaman digenggam.','Besi solid dengan lapisan neoprene anti slip yang aman bagi lantai rumah.',165000.00,145000.00,NULL,28,1,NULL,'olahraga.jpg',NULL,0,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08'),(29,5,'Vitamin C 1000mg Effervescent Strip','vitamin-c-1000mg-effervescent-strip',NULL,'Suplemen vitamin C + Zinc untuk daya tahan tubuh harian.','Membantu memelihara imunitas tubuh dan menjaga kebugaran stamina saat beraktivitas padat.',38000.00,32000.00,NULL,85,1,NULL,'Obat.jpg',NULL,1,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08'),(30,5,'Minyak Kayu Putih Alami 120ml','minyak-kayu-putih-alami-120ml',NULL,'Minyak kayu putih murni meredakan masuk angin & gatal.','Hangat alami menyegarkan, meredakan perut kembung, pegal-pegal, dan gigitan serangga.',45000.00,NULL,NULL,60,1,NULL,'Obat.jpg',NULL,0,'active',0,0,'2026-10-24 08:36:08','2026-10-24 08:36:08');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(50) DEFAULT 'text',
  `group` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_name','TeWuNeed','text','general','2026-10-24 08:28:35','2026-10-24 08:28:35'),(2,'site_email','info@tewuneed.com','text','general','2026-10-24 08:28:35','2026-10-24 08:28:35'),(3,'site_phone','+62 21 1234 5678','text','general','2026-10-24 08:28:35','2026-10-24 08:28:35'),(4,'site_address','Jl. Contoh No. 123, Jakarta, Indonesia','text','general','2026-10-24 08:28:35','2026-10-24 08:28:35'),(5,'currency','IDR','text','general','2026-10-24 08:28:35','2026-10-24 08:28:35'),(6,'tax_rate','10','text','general','2026-10-24 08:28:35','2026-10-24 08:28:35'),(7,'free_shipping_min','100000','text','shipping','2026-10-24 08:28:35','2026-10-24 08:28:35');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_methods`
--

DROP TABLE IF EXISTS `shipping_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `estimated_days` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_methods`
--

LOCK TABLES `shipping_methods` WRITE;
/*!40000 ALTER TABLE `shipping_methods` DISABLE KEYS */;
INSERT INTO `shipping_methods` VALUES (1,'Regular (JNE REG)',NULL,15000.00,'3-5 hari kerja','active','2026-10-24 08:28:35'),(2,'Express (JNE YES)',NULL,25000.00,'1-2 hari kerja','active','2026-10-24 08:28:35'),(3,'Same Day (GoSend)',NULL,35000.00,'Hari ini','active','2026-10-24 08:28:35'),(4,'Gratis Ongkir',NULL,0.00,'5-7 hari kerja','active','2026-10-24 08:28:35');
/*!40000 ALTER TABLE `shipping_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_addresses`
--

DROP TABLE IF EXISTS `user_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('home','work','other') DEFAULT 'home',
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `company` varchar(100) DEFAULT NULL,
  `address_line_1` varchar(255) NOT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) DEFAULT 'Indonesia',
  `phone` varchar(20) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_addresses`
--

LOCK TABLES `user_addresses` WRITE;
/*!40000 ALTER TABLE `user_addresses` DISABLE KEYS */;
INSERT INTO `user_addresses` VALUES (1,1,'home','Budi','Pratama',NULL,'Jl. Kebon Jeruk No. 45',NULL,'Jakarta Barat','DKI Jakarta','11530','Indonesia','08123456789',0,'2026-10-24 08:58:17','2026-10-24 08:58:17');
/*!40000 ALTER TABLE `user_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(100) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'budi.pratama@test.com','$2y$10$V8SyVibxX6bsQceW0.5N/.aTJa31yFaAnslBIe32D3D659wi1.pre','Budi','Pratama','08123456789',NULL,0,NULL,NULL,NULL,'active',NULL,'2026-10-24 08:54:39','2026-10-24 08:54:39');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlist`
--

LOCK TABLES `wishlist` WRITE;
/*!40000 ALTER TABLE `wishlist` DISABLE KEYS */;
/*!40000 ALTER TABLE `wishlist` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-24 16:13:43
