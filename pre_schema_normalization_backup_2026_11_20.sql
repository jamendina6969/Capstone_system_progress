-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: hydrotech_inventory
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
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `actor_name` varchar(255) DEFAULT NULL,
  `actor_role` varchar(255) DEFAULT NULL,
  `event` varchar(255) NOT NULL,
  `auditable_type` varchar(255) DEFAULT NULL,
  `auditable_id` bigint(20) unsigned DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,NULL,'System / Guest',NULL,'created','App\\Models\\User',1,NULL,'{\"email\":\"stevenhydrotech@gmail.com\",\"firstname\":\"Admin\",\"lastname\":\"Ako\",\"username\":\"admin\",\"password\":\"$2y$12$oaWW7SlLk.udCTtG0ROT\\/ePwxfGUUXAla27zywFnxqmkmGyu8R0Eq\",\"role\":\"admin\",\"is_active\":true,\"theme\":\"light\",\"updated_at\":\"2026-11-18 20:03:04\",\"created_at\":\"2026-11-18 20:03:04\",\"id\":1}','127.0.0.1','Symfony','2026-11-18 12:03:04','2026-11-18 12:03:04'),(2,1,'Admin Ako','admin','login','App\\Models\\User',1,NULL,'{\"email\":\"stevenhydrotech@gmail.com\",\"role\":\"admin\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 12:03:11','2026-11-18 12:03:11'),(3,1,'Admin Ako','admin','login','App\\Models\\User',1,NULL,'{\"email\":\"stevenhydrotech@gmail.com\",\"role\":\"admin\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 19:59:26','2026-11-18 19:59:26'),(4,1,'Admin Ako','admin','created','App\\Models\\Category',1,NULL,'{\"name\":\"Uncategorized\",\"parent_id\":null,\"type\":\"product\",\"code\":\"UNCAT\",\"updated_at\":\"2026-11-19 04:25:44\",\"created_at\":\"2026-11-19 04:25:44\",\"id\":1}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:44','2026-11-18 20:25:44'),(5,1,'Admin Ako','admin','created','App\\Models\\Category',2,NULL,'{\"name\":\"Filter\",\"parent_id\":null,\"type\":\"product\",\"code\":\"D7778D\",\"updated_at\":\"2026-11-19 04:25:44\",\"created_at\":\"2026-11-19 04:25:44\",\"id\":2}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:44','2026-11-18 20:25:44'),(6,1,'Admin Ako','admin','created','App\\Models\\Category',3,NULL,'{\"name\":\"Uncategorized\",\"parent_id\":2,\"type\":\"product\",\"code\":\"705667\",\"updated_at\":\"2026-11-19 04:25:44\",\"created_at\":\"2026-11-19 04:25:44\",\"id\":3}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:44','2026-11-18 20:25:44'),(7,1,'Admin Ako','admin','created','App\\Models\\Product',1,NULL,'{\"product_sku\":\"MIC-640E24\",\"product_name\":\"Micron #5\",\"product_description\":\"Auto-created from sales import on Nov 19, 2026\",\"purchase_cost\":50,\"selling_price\":80,\"store_quantity\":0,\"warehouse_quantity\":0,\"category_id\":3,\"updated_at\":\"2026-11-19 04:25:44\",\"created_at\":\"2026-11-19 04:25:44\",\"id\":1}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:44','2026-11-18 20:25:44'),(8,1,'Admin Ako','admin','created','App\\Models\\Transaction',1,NULL,'{\"user_id\":1,\"cashier_name\":\"Admin Ako\",\"transaction_id\":\"IMP-20260818-6afe09c8511e0\",\"transaction_type\":1,\"total_amount\":6400,\"amount_paid\":6400,\"amount_change\":0,\"created_at\":\"2026-08-18 04:25:44\",\"updated_at\":\"2026-08-18 04:25:44\",\"id\":1}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:44','2026-11-18 20:25:44'),(9,1,'Admin Ako','admin','created','App\\Models\\TransactionItem',1,NULL,'{\"transaction_id\":1,\"product_id\":1,\"product_sku\":\"MIC-640E24\",\"item_sku\":\"MIC-640E24-IMP-6afe09c851da0\",\"product_name\":\"Micron #5\",\"main_category_name\":\"Filter\",\"brand_category_name\":\"\",\"selling_price\":80,\"quantity\":80,\"subtotal\":6400,\"updated_at\":\"2026-11-19 04:25:44\",\"created_at\":\"2026-11-19 04:25:44\",\"id\":1}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:44','2026-11-18 20:25:44'),(10,1,'Admin Ako','admin','created','App\\Models\\Product',2,NULL,'{\"product_sku\":\"MIC-EA2E3D\",\"product_name\":\"Micron #1\",\"product_description\":\"Auto-created from sales import on Nov 19, 2026\",\"purchase_cost\":50,\"selling_price\":80,\"store_quantity\":0,\"warehouse_quantity\":0,\"category_id\":3,\"updated_at\":\"2026-11-19 04:25:44\",\"created_at\":\"2026-11-19 04:25:44\",\"id\":2}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:44','2026-11-18 20:25:44'),(11,1,'Admin Ako','admin','created','App\\Models\\Transaction',2,NULL,'{\"user_id\":1,\"cashier_name\":\"Admin Ako\",\"transaction_id\":\"IMP-20260819-6afe09c853669\",\"transaction_type\":1,\"total_amount\":6400,\"amount_paid\":6400,\"amount_change\":0,\"created_at\":\"2026-08-19 04:25:44\",\"updated_at\":\"2026-08-19 04:25:44\",\"id\":2}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:44','2026-11-18 20:25:44'),(12,1,'Admin Ako','admin','created','App\\Models\\TransactionItem',2,NULL,'{\"transaction_id\":2,\"product_id\":2,\"product_sku\":\"MIC-EA2E3D\",\"item_sku\":\"MIC-EA2E3D-IMP-6afe09c854421\",\"product_name\":\"Micron #1\",\"main_category_name\":\"Filter\",\"brand_category_name\":\"\",\"selling_price\":80,\"quantity\":80,\"subtotal\":6400,\"updated_at\":\"2026-11-19 04:25:44\",\"created_at\":\"2026-11-19 04:25:44\",\"id\":2}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:44','2026-11-18 20:25:44'),(13,1,'Admin Ako','admin','created','App\\Models\\Category',4,NULL,'{\"name\":\"Sealer\",\"parent_id\":null,\"type\":\"product\",\"code\":\"6EB5BC\",\"updated_at\":\"2026-11-19 04:25:44\",\"created_at\":\"2026-11-19 04:25:44\",\"id\":4}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:44','2026-11-18 20:25:44'),(14,1,'Admin Ako','admin','created','App\\Models\\Category',5,NULL,'{\"name\":\"Uncategorized\",\"parent_id\":4,\"type\":\"product\",\"code\":\"A5EF68\",\"updated_at\":\"2026-11-19 04:25:44\",\"created_at\":\"2026-11-19 04:25:44\",\"id\":5}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:44','2026-11-18 20:25:44'),(15,1,'Admin Ako','admin','created','App\\Models\\Product',3,NULL,'{\"product_sku\":\"FAU-297C37\",\"product_name\":\"Faucet Seal\",\"product_description\":\"Auto-created from sales import on Nov 19, 2026\",\"purchase_cost\":105,\"selling_price\":125,\"store_quantity\":0,\"warehouse_quantity\":0,\"category_id\":5,\"updated_at\":\"2026-11-19 04:25:44\",\"created_at\":\"2026-11-19 04:25:44\",\"id\":3}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:44','2026-11-18 20:25:44'),(16,1,'Admin Ako','admin','created','App\\Models\\Transaction',3,NULL,'{\"user_id\":1,\"cashier_name\":\"Admin Ako\",\"transaction_id\":\"IMP-20260821-6afe09c8565ab\",\"transaction_type\":1,\"total_amount\":52500,\"amount_paid\":52500,\"amount_change\":0,\"created_at\":\"2026-08-21 04:25:44\",\"updated_at\":\"2026-08-21 04:25:44\",\"id\":3}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:44','2026-11-18 20:25:44'),(17,1,'Admin Ako','admin','created','App\\Models\\TransactionItem',3,NULL,'{\"transaction_id\":3,\"product_id\":3,\"product_sku\":\"FAU-297C37\",\"item_sku\":\"FAU-297C37-IMP-6afe09c8569e6\",\"product_name\":\"Faucet Seal\",\"main_category_name\":\"Sealer\",\"brand_category_name\":\"\",\"selling_price\":125,\"quantity\":420,\"subtotal\":52500,\"updated_at\":\"2026-11-19 04:25:44\",\"created_at\":\"2026-11-19 04:25:44\",\"id\":3}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:44','2026-11-18 20:25:44'),(18,1,'Admin Ako','admin','created','App\\Models\\Transaction',4,NULL,'{\"user_id\":1,\"cashier_name\":\"Admin Ako\",\"transaction_id\":\"IMP-20260918-6afe09cfce983\",\"transaction_type\":1,\"total_amount\":6400,\"amount_paid\":6400,\"amount_change\":0,\"created_at\":\"2026-09-18 04:25:51\",\"updated_at\":\"2026-09-18 04:25:51\",\"id\":4}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:51','2026-11-18 20:25:51'),(19,1,'Admin Ako','admin','created','App\\Models\\TransactionItem',4,NULL,'{\"transaction_id\":4,\"product_id\":1,\"product_sku\":\"MIC-640E24\",\"item_sku\":\"MIC-640E24-IMP-6afe09cfcfeb2\",\"product_name\":\"Micron #5\",\"main_category_name\":\"Filter\",\"brand_category_name\":\"\",\"selling_price\":80,\"quantity\":80,\"subtotal\":6400,\"updated_at\":\"2026-11-19 04:25:51\",\"created_at\":\"2026-11-19 04:25:51\",\"id\":4}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:51','2026-11-18 20:25:51'),(20,1,'Admin Ako','admin','created','App\\Models\\Transaction',5,NULL,'{\"user_id\":1,\"cashier_name\":\"Admin Ako\",\"transaction_id\":\"IMP-20260919-6afe09cfd07ac\",\"transaction_type\":1,\"total_amount\":6400,\"amount_paid\":6400,\"amount_change\":0,\"created_at\":\"2026-09-19 04:25:51\",\"updated_at\":\"2026-09-19 04:25:51\",\"id\":5}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:51','2026-11-18 20:25:51'),(21,1,'Admin Ako','admin','created','App\\Models\\TransactionItem',5,NULL,'{\"transaction_id\":5,\"product_id\":2,\"product_sku\":\"MIC-EA2E3D\",\"item_sku\":\"MIC-EA2E3D-IMP-6afe09cfd0ca2\",\"product_name\":\"Micron #1\",\"main_category_name\":\"Filter\",\"brand_category_name\":\"\",\"selling_price\":80,\"quantity\":80,\"subtotal\":6400,\"updated_at\":\"2026-11-19 04:25:51\",\"created_at\":\"2026-11-19 04:25:51\",\"id\":5}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:51','2026-11-18 20:25:51'),(22,1,'Admin Ako','admin','created','App\\Models\\Transaction',6,NULL,'{\"user_id\":1,\"cashier_name\":\"Admin Ako\",\"transaction_id\":\"IMP-20260921-6afe09cfd14df\",\"transaction_type\":1,\"total_amount\":175000,\"amount_paid\":175000,\"amount_change\":0,\"created_at\":\"2026-09-21 04:25:51\",\"updated_at\":\"2026-09-21 04:25:51\",\"id\":6}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:51','2026-11-18 20:25:51'),(23,1,'Admin Ako','admin','created','App\\Models\\TransactionItem',6,NULL,'{\"transaction_id\":6,\"product_id\":3,\"product_sku\":\"FAU-297C37\",\"item_sku\":\"FAU-297C37-IMP-6afe09cfd18ac\",\"product_name\":\"Faucet Seal\",\"main_category_name\":\"Sealer\",\"brand_category_name\":\"\",\"selling_price\":125,\"quantity\":1400,\"subtotal\":175000,\"updated_at\":\"2026-11-19 04:25:51\",\"created_at\":\"2026-11-19 04:25:51\",\"id\":6}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:51','2026-11-18 20:25:51'),(24,1,'Admin Ako','admin','created','App\\Models\\Product',4,NULL,'{\"product_sku\":\"SMA-FAE299\",\"product_name\":\"Small Cap Seal\",\"product_description\":\"Auto-created from sales import on Nov 19, 2026\",\"purchase_cost\":125,\"selling_price\":105,\"store_quantity\":0,\"warehouse_quantity\":0,\"category_id\":5,\"updated_at\":\"2026-11-19 04:25:51\",\"created_at\":\"2026-11-19 04:25:51\",\"id\":4}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:51','2026-11-18 20:25:51'),(25,1,'Admin Ako','admin','created','App\\Models\\Transaction',7,NULL,'{\"user_id\":1,\"cashier_name\":\"Admin Ako\",\"transaction_id\":\"IMP-20260922-6afe09cfd2fd0\",\"transaction_type\":1,\"total_amount\":126525,\"amount_paid\":126525,\"amount_change\":0,\"created_at\":\"2026-09-22 04:25:51\",\"updated_at\":\"2026-09-22 04:25:51\",\"id\":7}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:51','2026-11-18 20:25:51'),(26,1,'Admin Ako','admin','created','App\\Models\\TransactionItem',7,NULL,'{\"transaction_id\":7,\"product_id\":4,\"product_sku\":\"SMA-FAE299\",\"item_sku\":\"SMA-FAE299-IMP-6afe09cfd343b\",\"product_name\":\"Small Cap Seal\",\"main_category_name\":\"Sealer\",\"brand_category_name\":\"\",\"selling_price\":105,\"quantity\":1205,\"subtotal\":126525,\"updated_at\":\"2026-11-19 04:25:51\",\"created_at\":\"2026-11-19 04:25:51\",\"id\":7}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:51','2026-11-18 20:25:51'),(27,1,'Admin Ako','admin','created','App\\Models\\Transaction',8,NULL,'{\"user_id\":1,\"cashier_name\":\"Admin Ako\",\"transaction_id\":\"IMP-20261018-6afe09d782b1c\",\"transaction_type\":1,\"total_amount\":25600,\"amount_paid\":25600,\"amount_change\":0,\"created_at\":\"2026-10-18 04:25:59\",\"updated_at\":\"2026-10-18 04:25:59\",\"id\":8}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:59','2026-11-18 20:25:59'),(28,1,'Admin Ako','admin','created','App\\Models\\TransactionItem',8,NULL,'{\"transaction_id\":8,\"product_id\":1,\"product_sku\":\"MIC-640E24\",\"item_sku\":\"MIC-640E24-IMP-6afe09d783da4\",\"product_name\":\"Micron #5\",\"main_category_name\":\"Filter\",\"brand_category_name\":\"\",\"selling_price\":80,\"quantity\":320,\"subtotal\":25600,\"updated_at\":\"2026-11-19 04:25:59\",\"created_at\":\"2026-11-19 04:25:59\",\"id\":8}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:59','2026-11-18 20:25:59'),(29,1,'Admin Ako','admin','created','App\\Models\\Transaction',9,NULL,'{\"user_id\":1,\"cashier_name\":\"Admin Ako\",\"transaction_id\":\"IMP-20261019-6afe09d784756\",\"transaction_type\":1,\"total_amount\":19200,\"amount_paid\":19200,\"amount_change\":0,\"created_at\":\"2026-10-19 04:25:59\",\"updated_at\":\"2026-10-19 04:25:59\",\"id\":9}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:59','2026-11-18 20:25:59'),(30,1,'Admin Ako','admin','created','App\\Models\\TransactionItem',9,NULL,'{\"transaction_id\":9,\"product_id\":2,\"product_sku\":\"MIC-EA2E3D\",\"item_sku\":\"MIC-EA2E3D-IMP-6afe09d784b9e\",\"product_name\":\"Micron #1\",\"main_category_name\":\"Filter\",\"brand_category_name\":\"\",\"selling_price\":80,\"quantity\":240,\"subtotal\":19200,\"updated_at\":\"2026-11-19 04:25:59\",\"created_at\":\"2026-11-19 04:25:59\",\"id\":9}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:59','2026-11-18 20:25:59'),(31,1,'Admin Ako','admin','created','App\\Models\\Transaction',10,NULL,'{\"user_id\":1,\"cashier_name\":\"Admin Ako\",\"transaction_id\":\"IMP-20261021-6afe09d785531\",\"transaction_type\":1,\"total_amount\":175000,\"amount_paid\":175000,\"amount_change\":0,\"created_at\":\"2026-10-21 04:25:59\",\"updated_at\":\"2026-10-21 04:25:59\",\"id\":10}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:59','2026-11-18 20:25:59'),(32,1,'Admin Ako','admin','created','App\\Models\\TransactionItem',10,NULL,'{\"transaction_id\":10,\"product_id\":3,\"product_sku\":\"FAU-297C37\",\"item_sku\":\"FAU-297C37-IMP-6afe09d7858e3\",\"product_name\":\"Faucet Seal\",\"main_category_name\":\"Sealer\",\"brand_category_name\":\"\",\"selling_price\":125,\"quantity\":1400,\"subtotal\":175000,\"updated_at\":\"2026-11-19 04:25:59\",\"created_at\":\"2026-11-19 04:25:59\",\"id\":10}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:59','2026-11-18 20:25:59'),(33,1,'Admin Ako','admin','created','App\\Models\\Transaction',11,NULL,'{\"user_id\":1,\"cashier_name\":\"Admin Ako\",\"transaction_id\":\"IMP-20261022-6afe09d786091\",\"transaction_type\":1,\"total_amount\":22050,\"amount_paid\":22050,\"amount_change\":0,\"created_at\":\"2026-10-22 04:25:59\",\"updated_at\":\"2026-10-22 04:25:59\",\"id\":11}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:59','2026-11-18 20:25:59'),(34,1,'Admin Ako','admin','created','App\\Models\\TransactionItem',11,NULL,'{\"transaction_id\":11,\"product_id\":4,\"product_sku\":\"SMA-FAE299\",\"item_sku\":\"SMA-FAE299-IMP-6afe09d786ca9\",\"product_name\":\"Small Cap Seal\",\"main_category_name\":\"Sealer\",\"brand_category_name\":\"\",\"selling_price\":105,\"quantity\":210,\"subtotal\":22050,\"updated_at\":\"2026-11-19 04:25:59\",\"created_at\":\"2026-11-19 04:25:59\",\"id\":11}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:25:59','2026-11-18 20:25:59'),(35,1,'Admin Ako','admin','created','App\\Models\\Category',6,NULL,'{\"name\":\"Uncategorized\",\"parent_id\":1,\"type\":\"product\",\"code\":\"UNCUNC\",\"description\":\"Default fallback subcategory for unassigned products.\",\"updated_at\":\"2026-11-19 04:26:12\",\"created_at\":\"2026-11-19 04:26:12\",\"id\":6}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:26:12','2026-11-18 20:26:12'),(36,1,'Admin Ako','admin','updated','App\\Models\\Product',2,'{\"id\":2,\"product_sku\":\"MIC-EA2E3D\",\"product_name\":\"Micron #1\",\"product_description\":\"Auto-created from sales import on Nov 19, 2026\",\"purchase_cost\":\"50.00\",\"selling_price\":\"80.00\",\"store_quantity\":0,\"warehouse_quantity\":0,\"reorder_point\":null,\"product_image\":null,\"category_id\":3,\"approx_volume\":null,\"storage_volume_cbm\":null,\"created_at\":\"2026-11-18T20:25:44.000000Z\",\"updated_at\":\"2026-11-18T20:25:44.000000Z\"}','{\"reorder_point\":0,\"category_id\":\"5\",\"approx_volume\":\"\",\"updated_at\":\"2026-11-19 04:50:42\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:50:42','2026-11-18 20:50:42'),(37,1,'Admin Ako','admin','updated','App\\Models\\Product',2,'{\"id\":2,\"product_sku\":\"MIC-EA2E3D\",\"product_name\":\"Micron #1\",\"product_description\":\"Auto-created from sales import on Nov 19, 2026\",\"purchase_cost\":\"50.00\",\"selling_price\":\"80.00\",\"store_quantity\":0,\"warehouse_quantity\":0,\"reorder_point\":0,\"product_image\":null,\"category_id\":5,\"approx_volume\":\"\",\"storage_volume_cbm\":null,\"created_at\":\"2026-11-18T20:25:44.000000Z\",\"updated_at\":\"2026-11-18T20:50:42.000000Z\"}','{\"category_id\":\"3\",\"updated_at\":\"2026-11-19 04:51:04\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:51:04','2026-11-18 20:51:04'),(38,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',1,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 04:59:55\",\"created_at\":\"2026-11-19 04:59:55\",\"id\":1}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:59:55','2026-11-18 20:59:55'),(39,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',2,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 04:59:55\",\"created_at\":\"2026-11-19 04:59:55\",\"id\":2}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:59:55','2026-11-18 20:59:55'),(40,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',3,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 04:59:55\",\"created_at\":\"2026-11-19 04:59:55\",\"id\":3}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:59:55','2026-11-18 20:59:55'),(41,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',4,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 04:59:55\",\"created_at\":\"2026-11-19 04:59:55\",\"id\":4}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 20:59:55','2026-11-18 20:59:55'),(42,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',5,NULL,'{\"product_id\":\"4\",\"ordering_cost\":null,\"holding_cost_override\":\"17\",\"set_by\":1,\"updated_at\":\"2026-11-19 05:00:00\",\"created_at\":\"2026-11-19 05:00:00\",\"id\":5}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 21:00:00','2026-11-18 21:00:00'),(43,1,'Admin Ako','admin','updated','App\\Models\\Product',1,'{\"id\":1,\"product_sku\":\"MIC-640E24\",\"product_name\":\"Micron #5\",\"product_description\":\"Auto-created from sales import on Nov 19, 2026\",\"purchase_cost\":\"50.00\",\"selling_price\":\"80.00\",\"store_quantity\":0,\"warehouse_quantity\":0,\"reorder_point\":null,\"product_image\":null,\"category_id\":3,\"approx_volume\":null,\"storage_volume_cbm\":null,\"created_at\":\"2026-11-18T20:25:44.000000Z\",\"updated_at\":\"2026-11-18T20:25:44.000000Z\"}','{\"store_quantity\":10}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 21:11:25','2026-11-18 21:11:25'),(44,1,'Admin Ako','admin','created','App\\Models\\StockMovement',1,NULL,'{\"batch_id\":\"RST-20261119051125-510F\",\"product_id\":1,\"type\":\"restock_store\",\"quantity\":10,\"quantity_before\":0,\"quantity_after\":10,\"status\":\"approved\",\"delivery_status\":\"delivered\",\"requested_by\":1,\"requester_name\":\"Admin Ako\",\"approved_by\":1,\"approver_name\":\"Admin Ako\",\"approved_at\":\"2026-11-19 05:11:25\",\"note\":null,\"updated_at\":\"2026-11-19 05:11:25\",\"created_at\":\"2026-11-19 05:11:25\",\"id\":1}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 21:11:25','2026-11-18 21:11:25'),(45,1,'Admin Ako','admin','updated','App\\Models\\Product',1,'{\"id\":1,\"product_sku\":\"MIC-640E24\",\"product_name\":\"Micron #5\",\"product_description\":\"Auto-created from sales import on Nov 19, 2026\",\"purchase_cost\":\"50.00\",\"selling_price\":\"80.00\",\"store_quantity\":10,\"warehouse_quantity\":0,\"reorder_point\":null,\"product_image\":null,\"category_id\":3,\"approx_volume\":null,\"storage_volume_cbm\":null,\"created_at\":\"2026-11-18T20:25:44.000000Z\",\"updated_at\":\"2026-11-18T21:11:25.000000Z\"}','{\"warehouse_quantity\":10}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 21:11:39','2026-11-18 21:11:39'),(46,1,'Admin Ako','admin','created','App\\Models\\StockMovement',2,NULL,'{\"batch_id\":\"RST-20261119051139-71D5\",\"product_id\":1,\"type\":\"restock_warehouse\",\"quantity\":10,\"quantity_before\":0,\"quantity_after\":10,\"status\":\"approved\",\"delivery_status\":\"delivered\",\"requested_by\":1,\"requester_name\":\"Admin Ako\",\"approved_by\":1,\"approver_name\":\"Admin Ako\",\"approved_at\":\"2026-11-19 05:11:39\",\"note\":null,\"updated_at\":\"2026-11-19 05:11:39\",\"created_at\":\"2026-11-19 05:11:39\",\"id\":2}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 21:11:39','2026-11-18 21:11:39'),(47,1,'Admin Ako','admin','updated','App\\Models\\Product',2,'{\"id\":2,\"product_sku\":\"MIC-EA2E3D\",\"product_name\":\"Micron #1\",\"product_description\":\"Auto-created from sales import on Nov 19, 2026\",\"purchase_cost\":\"50.00\",\"selling_price\":\"80.00\",\"store_quantity\":0,\"warehouse_quantity\":0,\"reorder_point\":0,\"product_image\":null,\"category_id\":3,\"approx_volume\":\"\",\"storage_volume_cbm\":null,\"created_at\":\"2026-11-18T20:25:44.000000Z\",\"updated_at\":\"2026-11-18T20:51:04.000000Z\"}','{\"store_quantity\":5}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 21:15:59','2026-11-18 21:15:59'),(48,1,'Admin Ako','admin','created','App\\Models\\StockMovement',3,NULL,'{\"batch_id\":\"RST-20261119051559-B221\",\"product_id\":2,\"type\":\"restock_store\",\"quantity\":5,\"quantity_before\":0,\"quantity_after\":5,\"status\":\"approved\",\"delivery_status\":\"delivered\",\"requested_by\":1,\"requester_name\":\"Admin Ako\",\"approved_by\":1,\"approver_name\":\"Admin Ako\",\"approved_at\":\"2026-11-19 05:15:59\",\"note\":null,\"updated_at\":\"2026-11-19 05:15:59\",\"created_at\":\"2026-11-19 05:15:59\",\"id\":3}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 21:15:59','2026-11-18 21:15:59'),(49,1,'Admin Ako','admin','updated','App\\Models\\Product',2,'{\"id\":2,\"product_sku\":\"MIC-EA2E3D\",\"product_name\":\"Micron #1\",\"product_description\":\"Auto-created from sales import on Nov 19, 2026\",\"purchase_cost\":\"50.00\",\"selling_price\":\"80.00\",\"store_quantity\":5,\"warehouse_quantity\":0,\"reorder_point\":0,\"product_image\":null,\"category_id\":3,\"approx_volume\":\"\",\"storage_volume_cbm\":null,\"created_at\":\"2026-11-18T20:25:44.000000Z\",\"updated_at\":\"2026-11-18T21:15:59.000000Z\"}','{\"warehouse_quantity\":3}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 21:16:06','2026-11-18 21:16:06'),(50,1,'Admin Ako','admin','created','App\\Models\\StockMovement',4,NULL,'{\"batch_id\":\"RST-20261119051606-6F73\",\"product_id\":2,\"type\":\"restock_warehouse\",\"quantity\":3,\"quantity_before\":0,\"quantity_after\":3,\"status\":\"approved\",\"delivery_status\":\"delivered\",\"requested_by\":1,\"requester_name\":\"Admin Ako\",\"approved_by\":1,\"approver_name\":\"Admin Ako\",\"approved_at\":\"2026-11-19 05:16:06\",\"note\":null,\"updated_at\":\"2026-11-19 05:16:06\",\"created_at\":\"2026-11-19 05:16:06\",\"id\":4}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 21:16:06','2026-11-18 21:16:06'),(51,1,'Admin Ako','admin','updated','App\\Models\\Product',1,'{\"id\":1,\"product_sku\":\"MIC-640E24\",\"product_name\":\"Micron #5\",\"product_description\":\"Auto-created from sales import on Nov 19, 2026\",\"purchase_cost\":\"50.00\",\"selling_price\":\"80.00\",\"store_quantity\":10,\"warehouse_quantity\":10,\"reorder_point\":null,\"product_image\":null,\"category_id\":3,\"approx_volume\":null,\"storage_volume_cbm\":null,\"created_at\":\"2026-11-18T20:25:44.000000Z\",\"updated_at\":\"2026-11-18T21:11:39.000000Z\"}','{\"reorder_point\":\"10\",\"approx_volume\":\"\",\"updated_at\":\"2026-11-19 05:16:47\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 21:16:47','2026-11-18 21:16:47'),(52,1,'Admin Ako','admin','updated','App\\Models\\Product',2,'{\"id\":2,\"product_sku\":\"MIC-EA2E3D\",\"product_name\":\"Micron #1\",\"product_description\":\"Auto-created from sales import on Nov 19, 2026\",\"purchase_cost\":\"50.00\",\"selling_price\":\"80.00\",\"store_quantity\":5,\"warehouse_quantity\":3,\"reorder_point\":0,\"product_image\":null,\"category_id\":3,\"approx_volume\":\"\",\"storage_volume_cbm\":null,\"created_at\":\"2026-11-18T20:25:44.000000Z\",\"updated_at\":\"2026-11-18T21:16:06.000000Z\"}','{\"reorder_point\":\"10\",\"updated_at\":\"2026-11-19 05:16:59\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-18 21:16:59','2026-11-18 21:16:59'),(53,1,'Admin Ako','admin','login','App\\Models\\User',1,NULL,'{\"email\":\"stevenhydrotech@gmail.com\",\"role\":\"admin\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 06:34:35','2026-11-19 06:34:35'),(54,1,'Admin Ako','admin','login','App\\Models\\User',1,NULL,'{\"email\":\"stevenhydrotech@gmail.com\",\"role\":\"admin\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 11:55:59','2026-11-19 11:55:59'),(55,1,'Admin Ako','admin','created','App\\Models\\Product',5,NULL,'{\"product_sku\":\"PRD-566E2Q\",\"product_name\":\"Carbon Black\",\"product_description\":\"\",\"approx_volume\":\"\",\"purchase_cost\":\"180\",\"selling_price\":\"450\",\"store_quantity\":\"0\",\"warehouse_quantity\":\"0\",\"reorder_point\":0,\"category_id\":1,\"updated_at\":\"2026-11-19 19:56:47\",\"created_at\":\"2026-11-19 19:56:47\",\"id\":5}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 11:56:47','2026-11-19 11:56:47'),(56,1,'Admin Ako','admin','updated','App\\Models\\Product',5,'{\"id\":5,\"product_sku\":\"PRD-566E2Q\",\"product_name\":\"Carbon Black\",\"product_description\":\"\",\"purchase_cost\":\"180.00\",\"selling_price\":\"450.00\",\"store_quantity\":0,\"warehouse_quantity\":0,\"reorder_point\":0,\"product_image\":null,\"category_id\":1,\"approx_volume\":\"\",\"storage_volume_cbm\":null,\"created_at\":\"2026-11-19T11:56:47.000000Z\",\"updated_at\":\"2026-11-19T11:56:47.000000Z\"}','{\"store_quantity\":\"22\",\"updated_at\":\"2026-11-19 19:57:11\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 11:57:11','2026-11-19 11:57:11'),(57,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',6,NULL,'{\"product_id\":\"5\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:20:55\",\"created_at\":\"2026-11-19 22:20:55\",\"id\":6}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:20:55','2026-11-19 14:20:55'),(58,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',7,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:20:55\",\"created_at\":\"2026-11-19 22:20:55\",\"id\":7}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:20:55','2026-11-19 14:20:55'),(59,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',8,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:20:56\",\"created_at\":\"2026-11-19 22:20:56\",\"id\":8}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:20:56','2026-11-19 14:20:56'),(60,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',9,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:20:56\",\"created_at\":\"2026-11-19 22:20:56\",\"id\":9}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:20:56','2026-11-19 14:20:56'),(61,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',10,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:20:56\",\"created_at\":\"2026-11-19 22:20:56\",\"id\":10}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:20:56','2026-11-19 14:20:56'),(62,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',11,NULL,'{\"product_id\":\"5\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:21:17\",\"created_at\":\"2026-11-19 22:21:17\",\"id\":11}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:21:17','2026-11-19 14:21:17'),(63,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',12,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:21:17\",\"created_at\":\"2026-11-19 22:21:17\",\"id\":12}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:21:17','2026-11-19 14:21:17'),(64,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',13,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:21:17\",\"created_at\":\"2026-11-19 22:21:17\",\"id\":13}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:21:17','2026-11-19 14:21:17'),(65,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',14,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:21:17\",\"created_at\":\"2026-11-19 22:21:17\",\"id\":14}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:21:17','2026-11-19 14:21:17'),(66,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',15,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:21:17\",\"created_at\":\"2026-11-19 22:21:17\",\"id\":15}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:21:17','2026-11-19 14:21:17'),(67,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',16,NULL,'{\"product_id\":\"5\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:21:25\",\"created_at\":\"2026-11-19 22:21:25\",\"id\":16}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:21:25','2026-11-19 14:21:25'),(68,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',17,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:21:25\",\"created_at\":\"2026-11-19 22:21:25\",\"id\":17}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:21:25','2026-11-19 14:21:25'),(69,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',18,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:21:25\",\"created_at\":\"2026-11-19 22:21:25\",\"id\":18}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:21:25','2026-11-19 14:21:25'),(70,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',19,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:21:25\",\"created_at\":\"2026-11-19 22:21:25\",\"id\":19}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:21:25','2026-11-19 14:21:25'),(71,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',20,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:21:25\",\"created_at\":\"2026-11-19 22:21:25\",\"id\":20}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:21:25','2026-11-19 14:21:25'),(72,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',21,NULL,'{\"product_id\":\"5\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:21:38\",\"created_at\":\"2026-11-19 22:21:38\",\"id\":21}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:21:38','2026-11-19 14:21:38'),(73,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',22,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:21:38\",\"created_at\":\"2026-11-19 22:21:38\",\"id\":22}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:21:38','2026-11-19 14:21:38'),(74,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',23,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:21:38\",\"created_at\":\"2026-11-19 22:21:38\",\"id\":23}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:21:38','2026-11-19 14:21:38'),(75,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',24,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:21:38\",\"created_at\":\"2026-11-19 22:21:38\",\"id\":24}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:21:38','2026-11-19 14:21:38'),(76,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',25,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:21:38\",\"created_at\":\"2026-11-19 22:21:38\",\"id\":25}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:21:38','2026-11-19 14:21:38'),(77,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',26,NULL,'{\"product_id\":\"5\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:22:28\",\"created_at\":\"2026-11-19 22:22:28\",\"id\":26}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:22:28','2026-11-19 14:22:28'),(78,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',27,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:22:28\",\"created_at\":\"2026-11-19 22:22:28\",\"id\":27}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:22:28','2026-11-19 14:22:28'),(79,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',28,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:22:28\",\"created_at\":\"2026-11-19 22:22:28\",\"id\":28}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:22:28','2026-11-19 14:22:28'),(80,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',29,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:22:28\",\"created_at\":\"2026-11-19 22:22:28\",\"id\":29}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:22:28','2026-11-19 14:22:28'),(81,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',30,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:22:28\",\"created_at\":\"2026-11-19 22:22:28\",\"id\":30}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:22:28','2026-11-19 14:22:28'),(82,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',31,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"12.50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:22:48\",\"created_at\":\"2026-11-19 22:22:48\",\"id\":31}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:22:48','2026-11-19 14:22:48'),(83,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',32,NULL,'{\"product_id\":\"4\",\"ordering_cost\":null,\"holding_cost_override\":\"17\",\"set_by\":1,\"updated_at\":\"2026-11-19 22:22:57\",\"created_at\":\"2026-11-19 22:22:57\",\"id\":32}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:22:57','2026-11-19 14:22:57'),(84,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',33,NULL,'{\"product_id\":\"5\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:23:47\",\"created_at\":\"2026-11-19 22:23:47\",\"id\":33}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:23:47','2026-11-19 14:23:47'),(85,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',34,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:23:47\",\"created_at\":\"2026-11-19 22:23:47\",\"id\":34}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:23:47','2026-11-19 14:23:47'),(86,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',35,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:23:47\",\"created_at\":\"2026-11-19 22:23:47\",\"id\":35}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:23:47','2026-11-19 14:23:47'),(87,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',36,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:23:47\",\"created_at\":\"2026-11-19 22:23:47\",\"id\":36}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:23:47','2026-11-19 14:23:47'),(88,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',37,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:23:47\",\"created_at\":\"2026-11-19 22:23:47\",\"id\":37}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:23:47','2026-11-19 14:23:47'),(89,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',38,NULL,'{\"product_id\":\"5\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:23:47\",\"created_at\":\"2026-11-19 22:23:47\",\"id\":38}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:23:47','2026-11-19 14:23:47'),(90,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',39,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:23:47\",\"created_at\":\"2026-11-19 22:23:47\",\"id\":39}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:23:47','2026-11-19 14:23:47'),(91,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',40,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:23:47\",\"created_at\":\"2026-11-19 22:23:47\",\"id\":40}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:23:47','2026-11-19 14:23:47'),(92,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',41,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:23:47\",\"created_at\":\"2026-11-19 22:23:47\",\"id\":41}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:23:47','2026-11-19 14:23:47'),(93,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',42,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:23:47\",\"created_at\":\"2026-11-19 22:23:47\",\"id\":42}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:23:47','2026-11-19 14:23:47'),(94,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',43,NULL,'{\"product_id\":\"5\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:37:20\",\"created_at\":\"2026-11-19 22:37:20\",\"id\":43}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:37:20','2026-11-19 14:37:20'),(95,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',44,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:37:20\",\"created_at\":\"2026-11-19 22:37:20\",\"id\":44}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:37:20','2026-11-19 14:37:20'),(96,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',45,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:37:20\",\"created_at\":\"2026-11-19 22:37:20\",\"id\":45}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:37:20','2026-11-19 14:37:20'),(97,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',46,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:37:20\",\"created_at\":\"2026-11-19 22:37:20\",\"id\":46}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:37:20','2026-11-19 14:37:20'),(98,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',47,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:37:20\",\"created_at\":\"2026-11-19 22:37:20\",\"id\":47}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:37:20','2026-11-19 14:37:20'),(99,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',48,NULL,'{\"product_id\":\"5\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:38:10\",\"created_at\":\"2026-11-19 22:38:10\",\"id\":48}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:38:10','2026-11-19 14:38:10'),(100,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',49,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:38:10\",\"created_at\":\"2026-11-19 22:38:10\",\"id\":49}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:38:10','2026-11-19 14:38:10'),(101,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',50,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:38:10\",\"created_at\":\"2026-11-19 22:38:10\",\"id\":50}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:38:10','2026-11-19 14:38:10'),(102,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',51,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:38:10\",\"created_at\":\"2026-11-19 22:38:10\",\"id\":51}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:38:10','2026-11-19 14:38:10'),(103,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',52,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:38:10\",\"created_at\":\"2026-11-19 22:38:10\",\"id\":52}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:38:10','2026-11-19 14:38:10'),(104,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',53,NULL,'{\"product_id\":\"5\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:45:13\",\"created_at\":\"2026-11-19 22:45:13\",\"id\":53}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:45:13','2026-11-19 14:45:13'),(105,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',54,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:45:13\",\"created_at\":\"2026-11-19 22:45:13\",\"id\":54}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:45:13','2026-11-19 14:45:13'),(106,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',55,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:45:13\",\"created_at\":\"2026-11-19 22:45:13\",\"id\":55}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:45:13','2026-11-19 14:45:13'),(107,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',56,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:45:13\",\"created_at\":\"2026-11-19 22:45:13\",\"id\":56}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:45:13','2026-11-19 14:45:13'),(108,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',57,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 22:45:13\",\"created_at\":\"2026-11-19 22:45:13\",\"id\":57}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 14:45:13','2026-11-19 14:45:13'),(109,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',58,NULL,'{\"product_id\":\"5\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:10:08\",\"created_at\":\"2026-11-19 23:10:08\",\"id\":58}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:10:08','2026-11-19 15:10:08'),(110,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',59,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:10:08\",\"created_at\":\"2026-11-19 23:10:08\",\"id\":59}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:10:08','2026-11-19 15:10:08'),(111,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',60,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:10:08\",\"created_at\":\"2026-11-19 23:10:08\",\"id\":60}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:10:08','2026-11-19 15:10:08'),(112,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',61,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:10:08\",\"created_at\":\"2026-11-19 23:10:08\",\"id\":61}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:10:08','2026-11-19 15:10:08'),(113,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',62,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:10:08\",\"created_at\":\"2026-11-19 23:10:08\",\"id\":62}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:10:08','2026-11-19 15:10:08'),(114,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',63,NULL,'{\"product_id\":\"4\",\"ordering_cost\":null,\"holding_cost_override\":\"17\",\"set_by\":1,\"updated_at\":\"2026-11-19 23:10:24\",\"created_at\":\"2026-11-19 23:10:24\",\"id\":63}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:10:24','2026-11-19 15:10:24'),(115,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',64,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"50\",\"holding_cost_override\":\"17\",\"set_by\":1,\"updated_at\":\"2026-11-19 23:10:36\",\"created_at\":\"2026-11-19 23:10:36\",\"id\":64}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:10:36','2026-11-19 15:10:36'),(116,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',65,NULL,'{\"product_id\":\"5\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:11:27\",\"created_at\":\"2026-11-19 23:11:27\",\"id\":65}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:11:27','2026-11-19 15:11:27'),(117,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',66,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:11:27\",\"created_at\":\"2026-11-19 23:11:27\",\"id\":66}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:11:27','2026-11-19 15:11:27'),(118,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',67,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:11:27\",\"created_at\":\"2026-11-19 23:11:27\",\"id\":67}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:11:27','2026-11-19 15:11:27'),(119,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',68,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:11:27\",\"created_at\":\"2026-11-19 23:11:27\",\"id\":68}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:11:27','2026-11-19 15:11:27'),(120,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',69,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:11:27\",\"created_at\":\"2026-11-19 23:11:27\",\"id\":69}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:11:27','2026-11-19 15:11:27'),(121,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',70,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"12.50\",\"holding_cost_override\":\"17\",\"set_by\":1,\"updated_at\":\"2026-11-19 23:12:40\",\"created_at\":\"2026-11-19 23:12:40\",\"id\":70}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:12:40','2026-11-19 15:12:40'),(122,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',71,NULL,'{\"product_id\":\"5\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:14:24\",\"created_at\":\"2026-11-19 23:14:24\",\"id\":71}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:14:24','2026-11-19 15:14:24'),(123,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',72,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:14:24\",\"created_at\":\"2026-11-19 23:14:24\",\"id\":72}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:14:24','2026-11-19 15:14:24'),(124,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',73,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:14:24\",\"created_at\":\"2026-11-19 23:14:24\",\"id\":73}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:14:24','2026-11-19 15:14:24'),(125,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',74,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:14:24\",\"created_at\":\"2026-11-19 23:14:24\",\"id\":74}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:14:24','2026-11-19 15:14:24'),(126,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',75,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"100\",\"holding_cost_override\":\"17\",\"set_by\":1,\"updated_at\":\"2026-11-19 23:15:12\",\"created_at\":\"2026-11-19 23:15:12\",\"id\":75}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:15:12','2026-11-19 15:15:12'),(127,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',76,NULL,'{\"product_id\":\"5\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:15:25\",\"created_at\":\"2026-11-19 23:15:25\",\"id\":76}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:15:25','2026-11-19 15:15:25'),(128,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',77,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:15:25\",\"created_at\":\"2026-11-19 23:15:25\",\"id\":77}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:15:25','2026-11-19 15:15:25'),(129,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',78,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:15:25\",\"created_at\":\"2026-11-19 23:15:25\",\"id\":78}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:15:25','2026-11-19 15:15:25'),(130,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',79,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:15:25\",\"created_at\":\"2026-11-19 23:15:25\",\"id\":79}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:15:25','2026-11-19 15:15:25'),(131,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',80,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"100\",\"holding_cost_override\":\"17\",\"set_by\":1,\"updated_at\":\"2026-11-19 23:16:33\",\"created_at\":\"2026-11-19 23:16:33\",\"id\":80}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:16:33','2026-11-19 15:16:33'),(132,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',81,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"100\",\"holding_cost_override\":\"17.6\",\"set_by\":1,\"updated_at\":\"2026-11-19 23:17:31\",\"created_at\":\"2026-11-19 23:17:31\",\"id\":81}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:17:31','2026-11-19 15:17:31'),(133,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',82,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"50\",\"holding_cost_override\":\"17.6\",\"set_by\":1,\"updated_at\":\"2026-11-19 23:18:09\",\"created_at\":\"2026-11-19 23:18:09\",\"id\":82}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:18:09','2026-11-19 15:18:09'),(134,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',83,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:18:32\",\"created_at\":\"2026-11-19 23:18:32\",\"id\":83}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:18:32','2026-11-19 15:18:32'),(135,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',84,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"10\",\"holding_cost_override\":\"17.6\",\"set_by\":1,\"updated_at\":\"2026-11-19 23:18:44\",\"created_at\":\"2026-11-19 23:18:44\",\"id\":84}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:18:44','2026-11-19 15:18:44'),(136,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',85,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"12.50\",\"holding_cost_override\":\"17.6\",\"set_by\":1,\"updated_at\":\"2026-11-19 23:19:29\",\"created_at\":\"2026-11-19 23:19:29\",\"id\":85}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:19:29','2026-11-19 15:19:29'),(137,1,'Admin Ako','admin','updated','App\\Models\\User',1,'{\"id\":1,\"username\":\"admin\",\"email\":\"stevenhydrotech@gmail.com\",\"password\":\"$2y$12$oaWW7SlLk.udCTtG0ROT\\/ePwxfGUUXAla27zywFnxqmkmGyu8R0Eq\",\"action_password\":null,\"firstname\":\"Admin\",\"lastname\":\"Ako\",\"suffix\":null,\"role\":\"admin\",\"is_active\":true,\"theme\":\"light\",\"email_verified_at\":null,\"remember_token\":null,\"created_at\":\"2026-11-18T12:03:04.000000Z\",\"updated_at\":\"2026-11-18T12:03:04.000000Z\"}','{\"theme\":\"dark\",\"updated_at\":\"2026-11-19 23:24:43\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:24:43','2026-11-19 15:24:43'),(138,1,'Admin Ako','admin','logout','App\\Models\\User',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:25:21','2026-11-19 15:25:21'),(139,1,'Admin Ako','admin','login','App\\Models\\User',1,NULL,'{\"email\":\"stevenhydrotech@gmail.com\",\"role\":\"admin\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:27:08','2026-11-19 15:27:08'),(140,1,'Admin Ako','admin','updated','App\\Models\\User',1,'{\"id\":1,\"username\":\"admin\",\"email\":\"stevenhydrotech@gmail.com\",\"password\":\"$2y$12$oaWW7SlLk.udCTtG0ROT\\/ePwxfGUUXAla27zywFnxqmkmGyu8R0Eq\",\"action_password\":null,\"firstname\":\"Admin\",\"lastname\":\"Ako\",\"suffix\":null,\"role\":\"admin\",\"is_active\":true,\"theme\":\"dark\",\"email_verified_at\":null,\"remember_token\":null,\"created_at\":\"2026-11-18T12:03:04.000000Z\",\"updated_at\":\"2026-11-19T15:24:43.000000Z\"}','{\"theme\":\"light\",\"updated_at\":\"2026-11-19 23:27:10\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:27:10','2026-11-19 15:27:10'),(141,1,'Admin Ako','admin','updated','App\\Models\\Product',4,'{\"id\":4,\"product_sku\":\"SMA-FAE299\",\"product_name\":\"Small Cap Seal\",\"product_description\":\"Auto-created from sales import on Nov 19, 2026\",\"purchase_cost\":\"125.00\",\"selling_price\":\"105.00\",\"store_quantity\":0,\"warehouse_quantity\":0,\"reorder_point\":null,\"product_image\":null,\"category_id\":5,\"approx_volume\":null,\"storage_volume_cbm\":null,\"created_at\":\"2026-11-18T20:25:51.000000Z\",\"updated_at\":\"2026-11-18T20:25:51.000000Z\"}','{\"purchase_cost\":\"88.00\",\"reorder_point\":0,\"approx_volume\":\"\",\"updated_at\":\"2026-11-19 23:28:52\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:28:52','2026-11-19 15:28:52'),(142,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',86,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:29:11\",\"created_at\":\"2026-11-19 23:29:11\",\"id\":86}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:29:11','2026-11-19 15:29:11'),(143,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',87,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"12.50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:29:38\",\"created_at\":\"2026-11-19 23:29:38\",\"id\":87}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:29:38','2026-11-19 15:29:38'),(144,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',88,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:29:55\",\"created_at\":\"2026-11-19 23:29:55\",\"id\":88}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:29:55','2026-11-19 15:29:55'),(145,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',89,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"100\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:30:15\",\"created_at\":\"2026-11-19 23:30:15\",\"id\":89}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:30:15','2026-11-19 15:30:15'),(146,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',90,NULL,'{\"product_id\":\"5\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:30:46\",\"created_at\":\"2026-11-19 23:30:46\",\"id\":90}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:30:46','2026-11-19 15:30:46'),(147,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',91,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:30:46\",\"created_at\":\"2026-11-19 23:30:46\",\"id\":91}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:30:46','2026-11-19 15:30:46'),(148,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',92,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:30:46\",\"created_at\":\"2026-11-19 23:30:46\",\"id\":92}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:30:46','2026-11-19 15:30:46'),(149,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',93,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:30:46\",\"created_at\":\"2026-11-19 23:30:46\",\"id\":93}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:30:46','2026-11-19 15:30:46'),(150,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',94,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:30:46\",\"created_at\":\"2026-11-19 23:30:46\",\"id\":94}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:30:46','2026-11-19 15:30:46'),(151,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',95,NULL,'{\"product_id\":\"5\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:31:02\",\"created_at\":\"2026-11-19 23:31:02\",\"id\":95}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:31:02','2026-11-19 15:31:02'),(152,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',96,NULL,'{\"product_id\":\"3\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:31:02\",\"created_at\":\"2026-11-19 23:31:02\",\"id\":96}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:31:02','2026-11-19 15:31:02'),(153,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',97,NULL,'{\"product_id\":\"2\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:31:02\",\"created_at\":\"2026-11-19 23:31:02\",\"id\":97}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:31:02','2026-11-19 15:31:02'),(154,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',98,NULL,'{\"product_id\":\"1\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:31:02\",\"created_at\":\"2026-11-19 23:31:02\",\"id\":98}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:31:02','2026-11-19 15:31:02'),(155,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',99,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"10\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:31:02\",\"created_at\":\"2026-11-19 23:31:02\",\"id\":99}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:31:02','2026-11-19 15:31:02'),(156,1,'Admin Ako','admin','created','App\\Models\\ProductOrderingCost',100,NULL,'{\"product_id\":\"4\",\"ordering_cost\":\"12.50\",\"holding_cost_override\":null,\"set_by\":1,\"updated_at\":\"2026-11-19 23:31:19\",\"created_at\":\"2026-11-19 23:31:19\",\"id\":100}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 15:31:19','2026-11-19 15:31:19'),(157,1,'Admin Ako','admin','created','App\\Models\\StockMovement',5,NULL,'{\"batch_id\":\"0cb1b8e1-40ad-477e-a7ae-8c51415274a5\",\"product_id\":3,\"requested_by\":1,\"requester_name\":\"Admin Ako\",\"approved_by\":1,\"approver_name\":\"Admin Ako\",\"type\":\"restock_store\",\"status\":\"approved\",\"quantity_before\":0,\"quantity_after\":5,\"quantity\":5,\"note\":null,\"approved_at\":\"2026-11-20 02:32:39\",\"updated_at\":\"2026-11-20 02:32:39\",\"created_at\":\"2026-11-20 02:32:39\",\"id\":5}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 18:32:39','2026-11-19 18:32:39'),(158,1,'Admin Ako','admin','updated','App\\Models\\Product',3,'{\"id\":3,\"product_sku\":\"FAU-297C37\",\"product_name\":\"Faucet Seal\",\"product_description\":\"Auto-created from sales import on Nov 19, 2026\",\"purchase_cost\":\"105.00\",\"selling_price\":\"125.00\",\"store_quantity\":0,\"warehouse_quantity\":0,\"reorder_point\":null,\"product_image\":null,\"category_id\":5,\"approx_volume\":null,\"storage_volume_cbm\":null,\"created_at\":\"2026-11-18T20:25:44.000000Z\",\"updated_at\":\"2026-11-18T20:25:44.000000Z\"}','{\"store_quantity\":5}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 18:32:39','2026-11-19 18:32:39'),(159,1,'Admin Ako','admin','logout','App\\Models\\User',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 18:34:33','2026-11-19 18:34:33'),(160,1,'Admin Ako','admin','login','App\\Models\\User',1,NULL,'{\"email\":\"stevenhydrotech@gmail.com\",\"role\":\"admin\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 18:34:43','2026-11-19 18:34:43'),(161,1,'Admin Ako','admin','created','App\\Models\\User',2,NULL,'{\"firstname\":\"manager\",\"lastname\":\"germany\",\"suffix\":null,\"username\":\"manager\",\"email\":\"manager@hydrotech.com\",\"password\":\"$2y$12$z1S7JKm\\/3cMhhkjIXH2aCu74akSEOXtme6WPsKub.Il0NvaSVmCjC\",\"role\":\"manager\",\"is_active\":true,\"theme\":\"light\",\"action_password\":\"$2y$12$xO6rEbEX88H\\/X\\/CazI60QO\\/9IGtd3OLMQyY8L1RsA\\/SkrY9JY1BVy\",\"updated_at\":\"2026-11-20 02:35:15\",\"created_at\":\"2026-11-20 02:35:15\",\"id\":2}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 18:35:15','2026-11-19 18:35:15'),(162,1,'Admin Ako','admin','created','App\\Models\\User',2,NULL,'{\"firstname\":\"manager\",\"lastname\":\"germany\",\"username\":\"manager\",\"email\":\"manager@hydrotech.com\",\"role\":\"manager\",\"is_active\":true}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 18:35:15','2026-11-19 18:35:15'),(163,1,'Admin Ako','admin','logout','App\\Models\\User',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 18:35:17','2026-11-19 18:35:17'),(164,2,'manager germany','manager','login','App\\Models\\User',2,NULL,'{\"email\":\"manager@hydrotech.com\",\"role\":\"manager\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 18:35:21','2026-11-19 18:35:21'),(165,2,'manager germany','manager','logout','App\\Models\\User',2,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 18:35:50','2026-11-19 18:35:50'),(166,1,'Admin Ako','admin','login','App\\Models\\User',1,NULL,'{\"email\":\"stevenhydrotech@gmail.com\",\"role\":\"admin\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 18:35:54','2026-11-19 18:35:54'),(167,1,'Admin Ako','admin','created','App\\Models\\StockMovement',6,NULL,'{\"batch_id\":\"20aaf08e-77f9-4bcf-855a-f1eade05be78\",\"product_id\":4,\"requested_by\":1,\"requester_name\":\"Admin Ako\",\"approved_by\":1,\"approver_name\":\"Admin Ako\",\"type\":\"restock_store\",\"status\":\"approved\",\"delivery_status\":\"delivered\",\"quantity_before\":0,\"quantity_after\":5,\"quantity\":5,\"note\":null,\"approved_at\":\"2026-11-20 02:44:41\",\"updated_at\":\"2026-11-20 02:44:41\",\"created_at\":\"2026-11-20 02:44:41\",\"id\":6}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 18:44:41','2026-11-19 18:44:41'),(168,1,'Admin Ako','admin','updated','App\\Models\\Product',4,'{\"id\":4,\"product_sku\":\"SMA-FAE299\",\"product_name\":\"Small Cap Seal\",\"product_description\":\"Auto-created from sales import on Nov 19, 2026\",\"purchase_cost\":\"88.00\",\"selling_price\":\"105.00\",\"store_quantity\":0,\"warehouse_quantity\":0,\"reorder_point\":0,\"product_image\":null,\"category_id\":5,\"approx_volume\":\"\",\"storage_volume_cbm\":null,\"created_at\":\"2026-11-18T20:25:51.000000Z\",\"updated_at\":\"2026-11-19T15:28:52.000000Z\"}','{\"store_quantity\":5}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 18:44:41','2026-11-19 18:44:41'),(169,1,'Admin Ako','admin','created','App\\Models\\User',3,NULL,'{\"firstname\":\"staff\",\"lastname\":\"seargent\",\"suffix\":null,\"username\":\"staff\",\"email\":\"staff@hydrotech.com\",\"password\":\"$2y$12$SHhjb59S\\/ygIa3VkoMzf8euDPOZ1DGfQAodDwgc7LJSwJHbrl25rW\",\"role\":\"staff\",\"is_active\":true,\"theme\":\"light\",\"updated_at\":\"2026-11-20 02:45:46\",\"created_at\":\"2026-11-20 02:45:46\",\"id\":3}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 18:45:46','2026-11-19 18:45:46'),(170,1,'Admin Ako','admin','created','App\\Models\\User',3,NULL,'{\"firstname\":\"staff\",\"lastname\":\"seargent\",\"username\":\"staff\",\"email\":\"staff@hydrotech.com\",\"role\":\"staff\",\"is_active\":true}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 18:45:46','2026-11-19 18:45:46'),(171,1,'Admin Ako','admin','created','App\\Models\\User',4,NULL,'{\"firstname\":\"warehouse\",\"lastname\":\"staff\",\"suffix\":null,\"username\":\"warehouse_staff\",\"email\":\"warehouse@hydrotech.com\",\"password\":\"$2y$12$Aa5BEkWtiGWvy66Z6zeiwOujgm.q3MezGLnhX0cJTzXbac5QxGh9m\",\"role\":\"warehouse_staff\",\"is_active\":true,\"theme\":\"light\",\"updated_at\":\"2026-11-20 02:46:12\",\"created_at\":\"2026-11-20 02:46:12\",\"id\":4}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 18:46:12','2026-11-19 18:46:12'),(172,1,'Admin Ako','admin','created','App\\Models\\User',4,NULL,'{\"firstname\":\"warehouse\",\"lastname\":\"staff\",\"username\":\"warehouse_staff\",\"email\":\"warehouse@hydrotech.com\",\"role\":\"warehouse_staff\",\"is_active\":true}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 18:46:12','2026-11-19 18:46:12'),(173,1,'Admin Ako','admin','logout','App\\Models\\User',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:00:17','2026-11-19 19:00:17'),(174,4,'warehouse staff','warehouse_staff','login','App\\Models\\User',4,NULL,'{\"email\":\"warehouse@hydrotech.com\",\"role\":\"warehouse_staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:00:31','2026-11-19 19:00:31'),(175,4,'warehouse staff','warehouse_staff','created','App\\Models\\StockMovement',7,NULL,'{\"batch_id\":\"b7536a98-3b2e-4673-87f9-5853599e3906\",\"product_id\":3,\"requested_by\":4,\"requester_name\":\"warehouse staff\",\"approved_by\":4,\"approver_name\":\"warehouse staff\",\"type\":\"restock_warehouse\",\"status\":\"approved\",\"delivery_status\":\"delivered\",\"quantity_before\":0,\"quantity_after\":5,\"quantity\":5,\"note\":null,\"approved_at\":\"2026-11-20 03:01:34\",\"updated_at\":\"2026-11-20 03:01:34\",\"created_at\":\"2026-11-20 03:01:34\",\"id\":7}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:01:34','2026-11-19 19:01:34'),(176,4,'warehouse staff','warehouse_staff','updated','App\\Models\\Product',3,'{\"id\":3,\"product_sku\":\"FAU-297C37\",\"product_name\":\"Faucet Seal\",\"product_description\":\"Auto-created from sales import on Nov 19, 2026\",\"purchase_cost\":\"105.00\",\"selling_price\":\"125.00\",\"store_quantity\":5,\"warehouse_quantity\":0,\"reorder_point\":null,\"product_image\":null,\"category_id\":5,\"approx_volume\":null,\"storage_volume_cbm\":null,\"created_at\":\"2026-11-18T20:25:44.000000Z\",\"updated_at\":\"2026-11-19T18:32:39.000000Z\"}','{\"warehouse_quantity\":5}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:01:34','2026-11-19 19:01:34'),(177,4,'warehouse staff','warehouse_staff','logout','App\\Models\\User',4,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:02:16','2026-11-19 19:02:16'),(178,1,'Admin Ako','admin','login','App\\Models\\User',1,NULL,'{\"email\":\"stevenhydrotech@gmail.com\",\"role\":\"admin\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:02:20','2026-11-19 19:02:20'),(179,1,'Admin Ako','admin','logout','App\\Models\\User',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:02:52','2026-11-19 19:02:52'),(180,4,'warehouse staff','warehouse_staff','login','App\\Models\\User',4,NULL,'{\"email\":\"warehouse@hydrotech.com\",\"role\":\"warehouse_staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:02:56','2026-11-19 19:02:56'),(181,4,'warehouse staff','warehouse_staff','logout','App\\Models\\User',4,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:09:48','2026-11-19 19:09:48'),(182,1,'Admin Ako','admin','login','App\\Models\\User',1,NULL,'{\"email\":\"stevenhydrotech@gmail.com\",\"role\":\"admin\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:10:00','2026-11-19 19:10:00'),(183,1,'Admin Ako','admin','logout','App\\Models\\User',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:13:09','2026-11-19 19:13:09'),(184,2,'manager germany','manager','login','App\\Models\\User',2,NULL,'{\"email\":\"manager@hydrotech.com\",\"role\":\"manager\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:13:16','2026-11-19 19:13:16'),(185,2,'manager germany','manager','logout','App\\Models\\User',2,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:13:46','2026-11-19 19:13:46'),(186,1,'Admin Ako','admin','login','App\\Models\\User',1,NULL,'{\"email\":\"stevenhydrotech@gmail.com\",\"role\":\"admin\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:13:51','2026-11-19 19:13:51'),(187,1,'Admin Ako','admin','logout','App\\Models\\User',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:18:28','2026-11-19 19:18:28'),(188,4,'warehouse staff','warehouse_staff','login','App\\Models\\User',4,NULL,'{\"email\":\"warehouse@hydrotech.com\",\"role\":\"warehouse_staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:18:31','2026-11-19 19:18:31'),(189,4,'warehouse staff','warehouse_staff','logout','App\\Models\\User',4,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:19:10','2026-11-19 19:19:10'),(190,1,'Admin Ako','admin','login','App\\Models\\User',1,NULL,'{\"email\":\"stevenhydrotech@gmail.com\",\"role\":\"admin\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:19:13','2026-11-19 19:19:13'),(191,1,'Admin Ako','admin','logout','App\\Models\\User',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:20:27','2026-11-19 19:20:27'),(192,1,'Admin Ako','admin','login','App\\Models\\User',1,NULL,'{\"email\":\"stevenhydrotech@gmail.com\",\"role\":\"admin\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:20:30','2026-11-19 19:20:30'),(193,1,'Admin Ako','admin','logout','App\\Models\\User',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:20:32','2026-11-19 19:20:32'),(194,4,'warehouse staff','warehouse_staff','login','App\\Models\\User',4,NULL,'{\"email\":\"warehouse@hydrotech.com\",\"role\":\"warehouse_staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:20:37','2026-11-19 19:20:37'),(195,4,'warehouse staff','warehouse_staff','logout','App\\Models\\User',4,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:20:40','2026-11-19 19:20:40'),(196,1,'Admin Ako','admin','login','App\\Models\\User',1,NULL,'{\"email\":\"stevenhydrotech@gmail.com\",\"role\":\"admin\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:24:31','2026-11-19 19:24:31'),(197,1,'Admin Ako','admin','logout','App\\Models\\User',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:24:32','2026-11-19 19:24:32'),(198,4,'warehouse staff','warehouse_staff','login','App\\Models\\User',4,NULL,'{\"email\":\"warehouse@hydrotech.com\",\"role\":\"warehouse_staff\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:24:35','2026-11-19 19:24:35'),(199,4,'warehouse staff','warehouse_staff','logout','App\\Models\\User',4,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:24:37','2026-11-19 19:24:37'),(200,1,'Admin Ako','admin','login','App\\Models\\User',1,NULL,'{\"email\":\"stevenhydrotech@gmail.com\",\"role\":\"admin\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','2026-11-19 19:54:11','2026-11-19 19:54:11');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-topbar_notifications_1','a:4:{s:7:\"prCount\";i:0;s:13:\"approvalCount\";i:0;s:9:\"recentPRs\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:15:\"recentApprovals\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}',1795118872),('laravel-cache-topbar_notifications_2','a:4:{s:7:\"prCount\";i:0;s:13:\"approvalCount\";i:0;s:9:\"recentPRs\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:15:\"recentApprovals\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}',1795115896),('laravel-cache-topbar_notifications_4','a:4:{s:7:\"prCount\";i:0;s:13:\"approvalCount\";i:0;s:9:\"recentPRs\";O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:15:\"recentApprovals\";O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}',1795116576);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('product','service') NOT NULL DEFAULT 'product',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Uncategorized','UNCAT',NULL,NULL,'product','2026-11-18 20:25:44','2026-11-18 20:25:44'),(2,'Filter','D7778D',NULL,NULL,'product','2026-11-18 20:25:44','2026-11-18 20:25:44'),(3,'Uncategorized','705667',NULL,2,'product','2026-11-18 20:25:44','2026-11-18 20:25:44'),(4,'Sealer','6EB5BC',NULL,NULL,'product','2026-11-18 20:25:44','2026-11-18 20:25:44'),(5,'Uncategorized','A5EF68',NULL,4,'product','2026-11-18 20:25:44','2026-11-18 20:25:44'),(6,'Uncategorized','UNCUNC','Default fallback subcategory for unassigned products.',1,'product','2026-11-18 20:26:12','2026-11-18 20:26:12');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_return_items`
--

DROP TABLE IF EXISTS `customer_return_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_return_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_return_id` bigint(20) unsigned NOT NULL,
  `transaction_item_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_refund_amount` decimal(15,2) NOT NULL,
  `refund_amount` decimal(15,2) NOT NULL,
  `restock` tinyint(1) NOT NULL DEFAULT 1,
  `source` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_return_items_customer_return_id_foreign` (`customer_return_id`),
  KEY `customer_return_items_product_id_foreign` (`product_id`),
  KEY `customer_return_items_transaction_item_id_index` (`transaction_item_id`),
  CONSTRAINT `customer_return_items_customer_return_id_foreign` FOREIGN KEY (`customer_return_id`) REFERENCES `customer_returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_return_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `customer_return_items_transaction_item_id_foreign` FOREIGN KEY (`transaction_item_id`) REFERENCES `transaction_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_return_items`
--

LOCK TABLES `customer_return_items` WRITE;
/*!40000 ALTER TABLE `customer_return_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_return_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_returns`
--

DROP TABLE IF EXISTS `customer_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_returns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `refund_transaction_id` bigint(20) unsigned DEFAULT NULL,
  `return_number` varchar(255) NOT NULL,
  `refund_amount` decimal(15,2) NOT NULL,
  `restock_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `refund_method` varchar(255) NOT NULL DEFAULT 'cash',
  `reason` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'completed',
  `voided_at` timestamp NULL DEFAULT NULL,
  `voided_by` bigint(20) unsigned DEFAULT NULL,
  `client_request_id` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_returns_return_number_unique` (`return_number`),
  UNIQUE KEY `customer_returns_client_request_id_unique` (`client_request_id`),
  KEY `customer_returns_user_id_foreign` (`user_id`),
  KEY `customer_returns_refund_transaction_id_foreign` (`refund_transaction_id`),
  KEY `customer_returns_voided_by_foreign` (`voided_by`),
  KEY `customer_returns_transaction_id_status_index` (`transaction_id`,`status`),
  CONSTRAINT `customer_returns_refund_transaction_id_foreign` FOREIGN KEY (`refund_transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_returns_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`),
  CONSTRAINT `customer_returns_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `customer_returns_voided_by_foreign` FOREIGN KEY (`voided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_returns`
--

LOCK TABLES `customer_returns` WRITE;
/*!40000 ALTER TABLE `customer_returns` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `demand_records`
--

DROP TABLE IF EXISTS `demand_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `demand_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `transaction_id` bigint(20) unsigned DEFAULT NULL,
  `record_date` date NOT NULL,
  `quantity` int(11) NOT NULL,
  `source` varchar(255) NOT NULL DEFAULT 'sale',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `demand_records_product_id_record_date_index` (`product_id`,`record_date`),
  KEY `fk_demand_records_transaction_id` (`transaction_id`),
  CONSTRAINT `fk_demand_records_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_demand_records_transaction_id` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `demand_records`
--

LOCK TABLES `demand_records` WRITE;
/*!40000 ALTER TABLE `demand_records` DISABLE KEYS */;
INSERT INTO `demand_records` VALUES (1,1,NULL,'2026-08-18',80,'sale','2026-11-18 20:25:44','2026-11-18 20:25:44'),(2,2,NULL,'2026-08-19',80,'sale','2026-11-18 20:25:44','2026-11-18 20:25:44'),(3,3,NULL,'2026-08-21',420,'sale','2026-11-18 20:25:44','2026-11-18 20:25:44'),(4,1,NULL,'2026-09-18',80,'sale','2026-11-18 20:25:51','2026-11-18 20:25:51'),(5,2,NULL,'2026-09-19',80,'sale','2026-11-18 20:25:51','2026-11-18 20:25:51'),(6,3,NULL,'2026-09-21',1400,'sale','2026-11-18 20:25:51','2026-11-18 20:25:51'),(7,4,NULL,'2026-09-22',1205,'sale','2026-11-18 20:25:51','2026-11-18 20:25:51'),(8,1,NULL,'2026-10-18',320,'sale','2026-11-18 20:25:59','2026-11-18 20:25:59'),(9,2,NULL,'2026-10-19',240,'sale','2026-11-18 20:25:59','2026-11-18 20:25:59'),(10,3,NULL,'2026-10-21',1400,'sale','2026-11-18 20:25:59','2026-11-18 20:25:59'),(11,4,NULL,'2026-10-22',210,'sale','2026-11-18 20:25:59','2026-11-18 20:25:59');
/*!40000 ALTER TABLE `demand_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eoq_settings`
--

DROP TABLE IF EXISTS `eoq_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eoq_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `default_ordering_cost` decimal(10,2) DEFAULT NULL,
  `ordering_cost_percentage` decimal(5,2) NOT NULL DEFAULT 10.00,
  `holding_cost_percentage` decimal(5,2) NOT NULL DEFAULT 20.00,
  `holding_cost_per_cbm` decimal(10,2) NOT NULL DEFAULT 0.00,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eoq_settings_updated_by_foreign` (`updated_by`),
  CONSTRAINT `eoq_settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eoq_settings`
--

LOCK TABLES `eoq_settings` WRITE;
/*!40000 ALTER TABLE `eoq_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `eoq_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forecast_settings`
--

DROP TABLE IF EXISTS `forecast_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forecast_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scope` enum('global','category','product') NOT NULL DEFAULT 'global',
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `sma_window_days` int(10) unsigned NOT NULL DEFAULT 30,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `forecast_settings_scope_category_id_product_id_unique` (`scope`,`category_id`,`product_id`),
  KEY `forecast_settings_category_id_foreign` (`category_id`),
  KEY `forecast_settings_product_id_foreign` (`product_id`),
  KEY `forecast_settings_updated_by_foreign` (`updated_by`),
  CONSTRAINT `forecast_settings_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `forecast_settings_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `forecast_settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forecast_settings`
--

LOCK TABLES `forecast_settings` WRITE;
/*!40000 ALTER TABLE `forecast_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `forecast_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_08_02_203922_create_categories_table',1),(5,'2026_08_03_063938_create_products_table',1),(6,'2026_10_03_150953_create_transactions_table',1),(7,'2026_10_03_151015_create_transaction_items_table',1),(8,'2026_10_03_152307_create_demand_records_table',1),(9,'2026_10_04_092343_create_stock_movements_table',1),(10,'2026_10_07_065028_create_forecast_settings_table',1),(11,'2026_10_08_165928_add_transaction_id_to_demand_records_table',1),(12,'2026_10_11_162009_create_eoq_settings_table',1),(13,'2026_10_11_162055_create_product_ordering_costs_table',1),(14,'2026_11_02_124545_add_discount_columns_to_pos_tables',1),(15,'2026_11_03_082849_create_audit_logs_table',1),(16,'2026_11_03_132505_add_category_fields_to_categories_table',1),(17,'2026_11_03_164930_add_sku_to_products_table',1),(18,'2026_11_04_103632_add_skus_to_transaction_items_table',1),(19,'2026_11_05_013635_create_suppliers_tables',1),(20,'2026_11_05_015254_remove_balance_from_suppliers_table',1),(21,'2026_11_05_015910_add_delivery_fields_to_purchase_orders_table',1),(22,'2026_11_05_015911_create_purchase_requests_tables',1),(23,'2026_11_05_015912_create_supplier_ratings_table',1),(24,'2026_11_05_021638_create_supplier_returns_table',1),(25,'2026_11_05_034100_increase_type_length_on_stock_movements_table',1),(26,'2026_11_05_041537_add_received_quantity_to_purchase_order_items_table',1),(27,'2026_11_05_044701_fix_eoq_settings_table',1),(28,'2026_11_05_060840_add_action_password_to_users_table',1),(29,'2026_11_09_104814_add_category_snapshots_to_transaction_items_table',1),(30,'2026_11_10_193347_add_status_to_purchase_request_items_table',1),(31,'2026_11_11_114757_add_receipt_path_to_supplier_ledgers_table',1),(32,'2026_11_11_135524_add_change_amount_to_supplier_ledgers_table',1),(33,'2026_11_11_145651_add_tax_and_freight_to_purchase_orders_table',1),(34,'2026_11_12_095103_add_user_snapshots_to_logs',1),(35,'2026_11_12_104948_make_remaining_user_foreign_keys_nullable',1),(36,'2026_11_12_105310_fix_transactions_user_id_foreign_key',1),(37,'2026_11_12_111217_add_requester_name_to_purchase_tables',1),(38,'2026_11_12_120519_create_sales_records_table',1),(39,'2026_11_12_121300_create_demand_records_table',1),(40,'2026_11_12_121712_add_transaction_id_to_demand_records_table',1),(41,'2026_11_15_202513_add_project_columns_to_transaction_items_table',1),(42,'2026_11_16_212005_create_customer_returns_table',1),(43,'2026_11_16_212006_create_customer_return_items_table',1),(44,'2026_11_16_224936_add_customer_return_id_to_transactions_table',1),(45,'2026_11_16_225639_fix_batch_id_on_stock_movements',1),(46,'2026_11_17_011809_create_transaction_item_materials_table',1),(47,'2026_11_17_092855_add_skus_to_transaction_item_materials_table',1),(48,'2026_11_18_110554_add_address_fields_to_suppliers_table',1),(49,'2026_11_19_040823_add_delivery_status_to_stock_movements_table',2),(50,'2026_11_20_033600_add_total_quantity_and_indexes_to_products_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_ordering_costs`
--

DROP TABLE IF EXISTS `product_ordering_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_ordering_costs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `ordering_cost` decimal(10,2) DEFAULT NULL,
  `holding_cost_override` decimal(10,2) DEFAULT NULL,
  `set_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_ordering_costs_product_id_foreign` (`product_id`),
  KEY `product_ordering_costs_set_by_foreign` (`set_by`),
  CONSTRAINT `product_ordering_costs_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_ordering_costs_set_by_foreign` FOREIGN KEY (`set_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_ordering_costs`
--

LOCK TABLES `product_ordering_costs` WRITE;
/*!40000 ALTER TABLE `product_ordering_costs` DISABLE KEYS */;
INSERT INTO `product_ordering_costs` VALUES (1,3,10.00,NULL,1,'2026-11-18 20:59:55','2026-11-18 20:59:55'),(2,2,10.00,NULL,1,'2026-11-18 20:59:55','2026-11-18 20:59:55'),(3,1,10.00,NULL,1,'2026-11-18 20:59:55','2026-11-18 20:59:55'),(4,4,10.00,NULL,1,'2026-11-18 20:59:55','2026-11-18 20:59:55'),(5,4,NULL,17.00,1,'2026-11-18 21:00:00','2026-11-18 21:00:00'),(6,5,100.00,NULL,1,'2026-11-19 14:20:55','2026-11-19 14:20:55'),(7,3,100.00,NULL,1,'2026-11-19 14:20:55','2026-11-19 14:20:55'),(8,2,100.00,NULL,1,'2026-11-19 14:20:56','2026-11-19 14:20:56'),(9,1,100.00,NULL,1,'2026-11-19 14:20:56','2026-11-19 14:20:56'),(10,4,100.00,NULL,1,'2026-11-19 14:20:56','2026-11-19 14:20:56'),(11,5,10.00,NULL,1,'2026-11-19 14:21:17','2026-11-19 14:21:17'),(12,3,10.00,NULL,1,'2026-11-19 14:21:17','2026-11-19 14:21:17'),(13,2,10.00,NULL,1,'2026-11-19 14:21:17','2026-11-19 14:21:17'),(14,1,10.00,NULL,1,'2026-11-19 14:21:17','2026-11-19 14:21:17'),(15,4,10.00,NULL,1,'2026-11-19 14:21:17','2026-11-19 14:21:17'),(16,5,50.00,NULL,1,'2026-11-19 14:21:25','2026-11-19 14:21:25'),(17,3,50.00,NULL,1,'2026-11-19 14:21:25','2026-11-19 14:21:25'),(18,2,50.00,NULL,1,'2026-11-19 14:21:25','2026-11-19 14:21:25'),(19,1,50.00,NULL,1,'2026-11-19 14:21:25','2026-11-19 14:21:25'),(20,4,50.00,NULL,1,'2026-11-19 14:21:25','2026-11-19 14:21:25'),(21,5,100.00,NULL,1,'2026-11-19 14:21:38','2026-11-19 14:21:38'),(22,3,100.00,NULL,1,'2026-11-19 14:21:38','2026-11-19 14:21:38'),(23,2,100.00,NULL,1,'2026-11-19 14:21:38','2026-11-19 14:21:38'),(24,1,100.00,NULL,1,'2026-11-19 14:21:38','2026-11-19 14:21:38'),(25,4,100.00,NULL,1,'2026-11-19 14:21:38','2026-11-19 14:21:38'),(26,5,10.00,NULL,1,'2026-11-19 14:22:28','2026-11-19 14:22:28'),(27,3,10.00,NULL,1,'2026-11-19 14:22:28','2026-11-19 14:22:28'),(28,2,10.00,NULL,1,'2026-11-19 14:22:28','2026-11-19 14:22:28'),(29,1,10.00,NULL,1,'2026-11-19 14:22:28','2026-11-19 14:22:28'),(30,4,10.00,NULL,1,'2026-11-19 14:22:28','2026-11-19 14:22:28'),(31,4,12.50,NULL,1,'2026-11-19 14:22:48','2026-11-19 14:22:48'),(32,4,NULL,17.00,1,'2026-11-19 14:22:57','2026-11-19 14:22:57'),(33,5,50.00,NULL,1,'2026-11-19 14:23:47','2026-11-19 14:23:47'),(34,3,50.00,NULL,1,'2026-11-19 14:23:47','2026-11-19 14:23:47'),(35,2,50.00,NULL,1,'2026-11-19 14:23:47','2026-11-19 14:23:47'),(36,1,50.00,NULL,1,'2026-11-19 14:23:47','2026-11-19 14:23:47'),(37,4,50.00,NULL,1,'2026-11-19 14:23:47','2026-11-19 14:23:47'),(38,5,50.00,NULL,1,'2026-11-19 14:23:47','2026-11-19 14:23:47'),(39,3,50.00,NULL,1,'2026-11-19 14:23:47','2026-11-19 14:23:47'),(40,2,50.00,NULL,1,'2026-11-19 14:23:47','2026-11-19 14:23:47'),(41,1,50.00,NULL,1,'2026-11-19 14:23:47','2026-11-19 14:23:47'),(42,4,50.00,NULL,1,'2026-11-19 14:23:47','2026-11-19 14:23:47'),(43,5,50.00,NULL,1,'2026-11-19 14:37:20','2026-11-19 14:37:20'),(44,3,50.00,NULL,1,'2026-11-19 14:37:20','2026-11-19 14:37:20'),(45,2,50.00,NULL,1,'2026-11-19 14:37:20','2026-11-19 14:37:20'),(46,1,50.00,NULL,1,'2026-11-19 14:37:20','2026-11-19 14:37:20'),(47,4,50.00,NULL,1,'2026-11-19 14:37:20','2026-11-19 14:37:20'),(48,5,100.00,NULL,1,'2026-11-19 14:38:10','2026-11-19 14:38:10'),(49,3,100.00,NULL,1,'2026-11-19 14:38:10','2026-11-19 14:38:10'),(50,2,100.00,NULL,1,'2026-11-19 14:38:10','2026-11-19 14:38:10'),(51,1,100.00,NULL,1,'2026-11-19 14:38:10','2026-11-19 14:38:10'),(52,4,100.00,NULL,1,'2026-11-19 14:38:10','2026-11-19 14:38:10'),(53,5,10.00,NULL,1,'2026-11-19 14:45:13','2026-11-19 14:45:13'),(54,3,10.00,NULL,1,'2026-11-19 14:45:13','2026-11-19 14:45:13'),(55,2,10.00,NULL,1,'2026-11-19 14:45:13','2026-11-19 14:45:13'),(56,1,10.00,NULL,1,'2026-11-19 14:45:13','2026-11-19 14:45:13'),(57,4,10.00,NULL,1,'2026-11-19 14:45:13','2026-11-19 14:45:13'),(58,5,50.00,NULL,1,'2026-11-19 15:10:08','2026-11-19 15:10:08'),(59,3,50.00,NULL,1,'2026-11-19 15:10:08','2026-11-19 15:10:08'),(60,2,50.00,NULL,1,'2026-11-19 15:10:08','2026-11-19 15:10:08'),(61,1,50.00,NULL,1,'2026-11-19 15:10:08','2026-11-19 15:10:08'),(62,4,50.00,NULL,1,'2026-11-19 15:10:08','2026-11-19 15:10:08'),(63,4,NULL,17.00,1,'2026-11-19 15:10:24','2026-11-19 15:10:24'),(64,4,50.00,17.00,1,'2026-11-19 15:10:36','2026-11-19 15:10:36'),(65,5,100.00,NULL,1,'2026-11-19 15:11:27','2026-11-19 15:11:27'),(66,3,100.00,NULL,1,'2026-11-19 15:11:27','2026-11-19 15:11:27'),(67,2,100.00,NULL,1,'2026-11-19 15:11:27','2026-11-19 15:11:27'),(68,1,100.00,NULL,1,'2026-11-19 15:11:27','2026-11-19 15:11:27'),(69,4,100.00,NULL,1,'2026-11-19 15:11:27','2026-11-19 15:11:27'),(70,4,12.50,17.00,1,'2026-11-19 15:12:40','2026-11-19 15:12:40'),(71,5,10.00,NULL,1,'2026-11-19 15:14:24','2026-11-19 15:14:24'),(72,3,10.00,NULL,1,'2026-11-19 15:14:24','2026-11-19 15:14:24'),(73,2,10.00,NULL,1,'2026-11-19 15:14:24','2026-11-19 15:14:24'),(74,1,10.00,NULL,1,'2026-11-19 15:14:24','2026-11-19 15:14:24'),(75,4,100.00,17.00,1,'2026-11-19 15:15:12','2026-11-19 15:15:12'),(76,5,100.00,NULL,1,'2026-11-19 15:15:25','2026-11-19 15:15:25'),(77,3,100.00,NULL,1,'2026-11-19 15:15:25','2026-11-19 15:15:25'),(78,2,100.00,NULL,1,'2026-11-19 15:15:25','2026-11-19 15:15:25'),(79,1,100.00,NULL,1,'2026-11-19 15:15:25','2026-11-19 15:15:25'),(80,4,100.00,17.00,1,'2026-11-19 15:16:33','2026-11-19 15:16:33'),(81,4,100.00,17.60,1,'2026-11-19 15:17:31','2026-11-19 15:17:31'),(82,4,50.00,17.60,1,'2026-11-19 15:18:09','2026-11-19 15:18:09'),(83,4,10.00,NULL,1,'2026-11-19 15:18:32','2026-11-19 15:18:32'),(84,4,10.00,17.60,1,'2026-11-19 15:18:44','2026-11-19 15:18:44'),(85,4,12.50,17.60,1,'2026-11-19 15:19:29','2026-11-19 15:19:29'),(86,4,10.00,NULL,1,'2026-11-19 15:29:11','2026-11-19 15:29:11'),(87,4,12.50,NULL,1,'2026-11-19 15:29:38','2026-11-19 15:29:38'),(88,4,50.00,NULL,1,'2026-11-19 15:29:55','2026-11-19 15:29:55'),(89,4,100.00,NULL,1,'2026-11-19 15:30:15','2026-11-19 15:30:15'),(90,5,50.00,NULL,1,'2026-11-19 15:30:46','2026-11-19 15:30:46'),(91,3,50.00,NULL,1,'2026-11-19 15:30:46','2026-11-19 15:30:46'),(92,2,50.00,NULL,1,'2026-11-19 15:30:46','2026-11-19 15:30:46'),(93,1,50.00,NULL,1,'2026-11-19 15:30:46','2026-11-19 15:30:46'),(94,4,50.00,NULL,1,'2026-11-19 15:30:46','2026-11-19 15:30:46'),(95,5,10.00,NULL,1,'2026-11-19 15:31:02','2026-11-19 15:31:02'),(96,3,10.00,NULL,1,'2026-11-19 15:31:02','2026-11-19 15:31:02'),(97,2,10.00,NULL,1,'2026-11-19 15:31:02','2026-11-19 15:31:02'),(98,1,10.00,NULL,1,'2026-11-19 15:31:02','2026-11-19 15:31:02'),(99,4,10.00,NULL,1,'2026-11-19 15:31:02','2026-11-19 15:31:02'),(100,4,12.50,NULL,1,'2026-11-19 15:31:19','2026-11-19 15:31:19');
/*!40000 ALTER TABLE `product_ordering_costs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_sku` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_description` longtext NOT NULL,
  `purchase_cost` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `store_quantity` int(11) NOT NULL DEFAULT 0,
  `warehouse_quantity` int(11) NOT NULL DEFAULT 0,
  `total_quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_point` int(11) DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `approx_volume` varchar(255) DEFAULT NULL,
  `storage_volume_cbm` decimal(8,4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_product_sku_unique` (`product_sku`),
  KEY `products_total_quantity_index` (`total_quantity`),
  KEY `products_store_quantity_index` (`store_quantity`),
  KEY `products_warehouse_quantity_index` (`warehouse_quantity`),
  KEY `products_reorder_point_index` (`reorder_point`),
  KEY `products_category_id_product_name_index` (`category_id`,`product_name`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'MIC-640E24','Micron #5','Auto-created from sales import on Nov 19, 2026',50.00,80.00,10,10,20,10,NULL,3,'',NULL,'2026-11-18 20:25:44','2026-11-18 21:16:47'),(2,'MIC-EA2E3D','Micron #1','Auto-created from sales import on Nov 19, 2026',50.00,80.00,5,3,8,10,NULL,3,'',NULL,'2026-11-18 20:25:44','2026-11-18 21:16:59'),(3,'FAU-297C37','Faucet Seal','Auto-created from sales import on Nov 19, 2026',105.00,125.00,5,5,10,NULL,NULL,5,NULL,NULL,'2026-11-18 20:25:44','2026-11-19 19:01:34'),(4,'SMA-FAE299','Small Cap Seal','Auto-created from sales import on Nov 19, 2026',88.00,105.00,5,0,5,0,NULL,5,'',NULL,'2026-11-18 20:25:51','2026-11-19 18:44:41'),(5,'PRD-566E2Q','Carbon Black','',180.00,450.00,22,0,22,0,NULL,1,'',NULL,'2026-11-19 11:56:47','2026-11-19 11:57:11');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `received_quantity` int(11) DEFAULT NULL,
  `unit_cost` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_order_items_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `purchase_order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `purchase_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_order_items_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_items`
--

LOCK TABLES `purchase_order_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `po_number` varchar(255) NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `creator_name` varchar(255) DEFAULT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `freight_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `received_quantity` int(11) DEFAULT NULL,
  `discrepancy_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `grn_notes` text DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_orders_po_number_unique` (`po_number`),
  KEY `purchase_orders_supplier_id_foreign` (`supplier_id`),
  KEY `purchase_orders_user_id_foreign` (`user_id`),
  CONSTRAINT `purchase_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_request_items`
--

DROP TABLE IF EXISTS `purchase_request_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_request_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_request_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_request_items_purchase_request_id_foreign` (`purchase_request_id`),
  KEY `purchase_request_items_product_id_foreign` (`product_id`),
  CONSTRAINT `purchase_request_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_request_items_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_request_items`
--

LOCK TABLES `purchase_request_items` WRITE;
/*!40000 ALTER TABLE `purchase_request_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_request_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_requests`
--

DROP TABLE IF EXISTS `purchase_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pr_number` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `requester_name` varchar(255) DEFAULT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `reason` text DEFAULT NULL,
  `manager_notes` text DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_requests_pr_number_unique` (`pr_number`),
  KEY `purchase_requests_supplier_id_foreign` (`supplier_id`),
  KEY `purchase_requests_approved_by_foreign` (`approved_by`),
  KEY `purchase_requests_user_id_foreign` (`user_id`),
  CONSTRAINT `purchase_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_requests_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_requests`
--

LOCK TABLES `purchase_requests` WRITE;
/*!40000 ALTER TABLE `purchase_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_records`
--

DROP TABLE IF EXISTS `sales_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `transaction_id` bigint(20) unsigned DEFAULT NULL,
  `record_date` date NOT NULL,
  `quantity` int(11) NOT NULL,
  `source` varchar(255) NOT NULL DEFAULT 'pos',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_records_transaction_id_foreign` (`transaction_id`),
  KEY `sales_records_product_id_record_date_index` (`product_id`,`record_date`),
  CONSTRAINT `sales_records_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_records_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_records`
--

LOCK TABLES `sales_records` WRITE;
/*!40000 ALTER TABLE `sales_records` DISABLE KEYS */;
INSERT INTO `sales_records` VALUES (1,1,1,'2026-08-18',80,'imported','2026-11-18 20:25:44','2026-11-18 20:25:44'),(2,2,2,'2026-08-19',80,'imported','2026-11-18 20:25:44','2026-11-18 20:25:44'),(3,3,3,'2026-08-21',420,'imported','2026-11-18 20:25:44','2026-11-18 20:25:44'),(4,1,4,'2026-09-18',80,'imported','2026-11-18 20:25:51','2026-11-18 20:25:51'),(5,2,5,'2026-09-19',80,'imported','2026-11-18 20:25:51','2026-11-18 20:25:51'),(6,3,6,'2026-09-21',1400,'imported','2026-11-18 20:25:51','2026-11-18 20:25:51'),(7,4,7,'2026-09-22',1205,'imported','2026-11-18 20:25:51','2026-11-18 20:25:51'),(8,1,8,'2026-10-18',320,'imported','2026-11-18 20:25:59','2026-11-18 20:25:59'),(9,2,9,'2026-10-19',240,'imported','2026-11-18 20:25:59','2026-11-18 20:25:59'),(10,3,10,'2026-10-21',1400,'imported','2026-11-18 20:25:59','2026-11-18 20:25:59'),(11,4,11,'2026-10-22',210,'imported','2026-11-18 20:25:59','2026-11-18 20:25:59');
/*!40000 ALTER TABLE `sales_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('5o6MZ4N38hmYe55uccLYFy4dyASLxm43fl1tGgeP',NULL,'127.0.0.1','Symfony','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMkJRb05ubHdlbWJZN3R0WnhRSWcyUlk3VUtZajBRUUpxZXRLYnJERiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjM6Imh0dHA6Ly9sb2NhbGhvc3QvbG9nb3V0IjtzOjU6InJvdXRlIjtzOjY6ImxvZ291dCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1795116226),('9DR6Yrd37aR0IXyKXDgocUuZ79PKeqrBF56JoATy',4,'127.0.0.1','Symfony','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNHRvUmNWSE9mY3JCZTVsOXZtbjVaaVI5bTE2NXdtWm4xN0tRZVJPMSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3QvaW52ZW50b3J5L2V4cG9ydCI7czo1OiJyb3V0ZSI7czoxNjoiaW52ZW50b3J5LmV4cG9ydCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1795115873),('fsDgLbvpcwrMNEi5n7ncLueVocbJqHjCkWvB6oWJ',NULL,'127.0.0.1','Symfony','YToyOntzOjY6Il90b2tlbiI7czo0MDoiSjgxUWN4eHdsSDJNeE9DbzVtajJGb21IMzRDcGhvM1JUbzdKRTF6SyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1795116226),('GMr4hpu0Kya8m3bozH8byQKKpA3OmYWZJ1OWdxG8',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiNEJtelVpMExZQmZ0NGZ3WGNVSXpDNGhtWHRPblEzVFdjeHBnV1pIaiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjQ6ImNhcnQiO2E6NTp7aTozO2E6MTI6e3M6NDoidHlwZSI7czo3OiJwcm9kdWN0IjtzOjEyOiJwcm9kdWN0X25hbWUiO3M6MTE6IkZhdWNldCBTZWFsIjtzOjExOiJwcm9kdWN0X3NrdSI7czoxMDoiRkFVLTI5N0MzNyI7czo4OiJpdGVtX3NrdSI7czoxNjoiRkFVLTI5N0MzNy02ODkzRCI7czoxMzoic2VsbGluZ19wcmljZSI7czo2OiIxMjUuMDAiO3M6ODoicXVhbnRpdHkiO2k6MTtzOjE4OiJpdGVtX2Rpc2NvdW50X3R5cGUiO3M6NDoibm9uZSI7czoxOToiaXRlbV9kaXNjb3VudF92YWx1ZSI7aTowO3M6MTk6Iml0ZW1fZGlzY291bnRfc2NvcGUiO3M6NToid2hvbGUiO3M6MjA6Iml0ZW1fZGlzY291bnRfYW1vdW50IjtpOjA7czo4OiJzdWJ0b3RhbCI7ZDoxMjU7czo2OiJzb3VyY2UiO3M6NToic3RvcmUiO31pOjI7YToxMjp7czo0OiJ0eXBlIjtzOjc6InByb2R1Y3QiO3M6MTI6InByb2R1Y3RfbmFtZSI7czo5OiJNaWNyb24gIzEiO3M6MTE6InByb2R1Y3Rfc2t1IjtzOjEwOiJNSUMtRUEyRTNEIjtzOjg6Iml0ZW1fc2t1IjtzOjE2OiJNSUMtRUEyRTNELUE4RTYwIjtzOjEzOiJzZWxsaW5nX3ByaWNlIjtzOjU6IjgwLjAwIjtzOjg6InF1YW50aXR5IjtpOjE7czoxODoiaXRlbV9kaXNjb3VudF90eXBlIjtzOjQ6Im5vbmUiO3M6MTk6Iml0ZW1fZGlzY291bnRfdmFsdWUiO2k6MDtzOjE5OiJpdGVtX2Rpc2NvdW50X3Njb3BlIjtzOjU6Indob2xlIjtzOjIwOiJpdGVtX2Rpc2NvdW50X2Ftb3VudCI7aTowO3M6ODoic3VidG90YWwiO2Q6ODA7czo2OiJzb3VyY2UiO3M6NToic3RvcmUiO31pOjU7YToxMjp7czo0OiJ0eXBlIjtzOjc6InByb2R1Y3QiO3M6MTI6InByb2R1Y3RfbmFtZSI7czoxMjoiQ2FyYm9uIEJsYWNrIjtzOjExOiJwcm9kdWN0X3NrdSI7czoxMDoiUFJELTU2NkUyUSI7czo4OiJpdGVtX3NrdSI7czoxNjoiUFJELTU2NkUyUS0xQ0Q0OCI7czoxMzoic2VsbGluZ19wcmljZSI7czo2OiI0NTAuMDAiO3M6ODoicXVhbnRpdHkiO2k6MjtzOjE4OiJpdGVtX2Rpc2NvdW50X3R5cGUiO3M6NDoibm9uZSI7czoxOToiaXRlbV9kaXNjb3VudF92YWx1ZSI7aTowO3M6MTk6Iml0ZW1fZGlzY291bnRfc2NvcGUiO3M6NToid2hvbGUiO3M6MjA6Iml0ZW1fZGlzY291bnRfYW1vdW50IjtpOjA7czo4OiJzdWJ0b3RhbCI7ZDo5MDA7czo2OiJzb3VyY2UiO3M6NToic3RvcmUiO31pOjE7YToxMjp7czo0OiJ0eXBlIjtzOjc6InByb2R1Y3QiO3M6MTI6InByb2R1Y3RfbmFtZSI7czo5OiJNaWNyb24gIzUiO3M6MTE6InByb2R1Y3Rfc2t1IjtzOjEwOiJNSUMtNjQwRTI0IjtzOjg6Iml0ZW1fc2t1IjtzOjE2OiJNSUMtNjQwRTI0LUU2M0MxIjtzOjEzOiJzZWxsaW5nX3ByaWNlIjtzOjU6IjgwLjAwIjtzOjg6InF1YW50aXR5IjtpOjE7czoxODoiaXRlbV9kaXNjb3VudF90eXBlIjtzOjQ6Im5vbmUiO3M6MTk6Iml0ZW1fZGlzY291bnRfdmFsdWUiO2k6MDtzOjE5OiJpdGVtX2Rpc2NvdW50X3Njb3BlIjtzOjU6Indob2xlIjtzOjIwOiJpdGVtX2Rpc2NvdW50X2Ftb3VudCI7aTowO3M6ODoic3VidG90YWwiO2Q6ODA7czo2OiJzb3VyY2UiO3M6NToic3RvcmUiO31pOjQ7YToxMjp7czo0OiJ0eXBlIjtzOjc6InByb2R1Y3QiO3M6MTI6InByb2R1Y3RfbmFtZSI7czoxNDoiU21hbGwgQ2FwIFNlYWwiO3M6MTE6InByb2R1Y3Rfc2t1IjtzOjEwOiJTTUEtRkFFMjk5IjtzOjg6Iml0ZW1fc2t1IjtzOjE2OiJTTUEtRkFFMjk5LUVGOTA2IjtzOjEzOiJzZWxsaW5nX3ByaWNlIjtzOjY6IjEwNS4wMCI7czo4OiJxdWFudGl0eSI7aToxO3M6MTg6Iml0ZW1fZGlzY291bnRfdHlwZSI7czo0OiJub25lIjtzOjE5OiJpdGVtX2Rpc2NvdW50X3ZhbHVlIjtpOjA7czoxOToiaXRlbV9kaXNjb3VudF9zY29wZSI7czo1OiJ3aG9sZSI7czoyMDoiaXRlbV9kaXNjb3VudF9hbW91bnQiO2k6MDtzOjg6InN1YnRvdGFsIjtkOjEwNTtzOjY6InNvdXJjZSI7czo1OiJzdG9yZSI7fX19',1795119580),('SUKrgjb49lxwWnIQ6kBsRoEWlHZs7t6G6k5vlipT',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','YToyOntzOjY6Il90b2tlbiI7czo0MDoiWXBJZGlsZ25MeENtcmxrWG1xUE42Z3NPc09Da2VHS0JBQ0VhMTJCeSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1795116040);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` varchar(255) DEFAULT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `requested_by` bigint(20) unsigned DEFAULT NULL,
  `requester_name` varchar(255) DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approver_name` varchar(255) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `delivery_status` varchar(255) NOT NULL DEFAULT 'pending',
  `quantity_before` int(11) NOT NULL DEFAULT 0,
  `quantity_after` int(11) NOT NULL DEFAULT 0,
  `quantity` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_requested_by_foreign` (`requested_by`),
  KEY `stock_movements_approved_by_foreign` (`approved_by`),
  KEY `stock_movements_product_id_created_at_index` (`product_id`,`created_at`),
  KEY `stock_movements_status_index` (`status`),
  KEY `stock_movements_batch_id_index` (`batch_id`),
  CONSTRAINT `stock_movements_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_movements_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES (1,'RST-20261119051125-510F',1,1,'Admin Ako',1,'Admin Ako','restock_store','approved','delivered',0,10,10,NULL,NULL,'2026-11-18 21:11:25','2026-11-18 21:11:25','2026-11-18 21:11:25'),(2,'RST-20261119051139-71D5',1,1,'Admin Ako',1,'Admin Ako','restock_warehouse','approved','delivered',0,10,10,NULL,NULL,'2026-11-18 21:11:39','2026-11-18 21:11:39','2026-11-18 21:11:39'),(3,'RST-20261119051559-B221',2,1,'Admin Ako',1,'Admin Ako','restock_store','approved','delivered',0,5,5,NULL,NULL,'2026-11-18 21:15:59','2026-11-18 21:15:59','2026-11-18 21:15:59'),(4,'RST-20261119051606-6F73',2,1,'Admin Ako',1,'Admin Ako','restock_warehouse','approved','delivered',0,3,3,NULL,NULL,'2026-11-18 21:16:06','2026-11-18 21:16:06','2026-11-18 21:16:06'),(5,'0cb1b8e1-40ad-477e-a7ae-8c51415274a5',3,1,'Admin Ako',1,'Admin Ako','restock_store','approved','delivered',0,5,5,NULL,NULL,'2026-11-19 18:32:39','2026-11-19 18:32:39','2026-11-19 18:42:44'),(6,'20aaf08e-77f9-4bcf-855a-f1eade05be78',4,1,'Admin Ako',1,'Admin Ako','restock_store','approved','delivered',0,5,5,NULL,NULL,'2026-11-19 18:44:41','2026-11-19 18:44:41','2026-11-19 18:44:41'),(7,'b7536a98-3b2e-4673-87f9-5853599e3906',3,4,'warehouse staff',4,'warehouse staff','restock_warehouse','approved','delivered',0,5,5,NULL,NULL,'2026-11-19 19:01:34','2026-11-19 19:01:34','2026-11-19 19:01:34');
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_ledgers`
--

DROP TABLE IF EXISTS `supplier_ledgers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_ledgers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `purchase_order_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_type` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `change_amount` decimal(12,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_ledgers_supplier_id_foreign` (`supplier_id`),
  KEY `supplier_ledgers_purchase_order_id_foreign` (`purchase_order_id`),
  CONSTRAINT `supplier_ledgers_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supplier_ledgers_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_ledgers`
--

LOCK TABLES `supplier_ledgers` WRITE;
/*!40000 ALTER TABLE `supplier_ledgers` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_ledgers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_ratings`
--

DROP TABLE IF EXISTS `supplier_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_ratings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `purchase_order_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `score` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_ratings_supplier_id_foreign` (`supplier_id`),
  KEY `supplier_ratings_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `supplier_ratings_user_id_foreign` (`user_id`),
  CONSTRAINT `supplier_ratings_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supplier_ratings_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_ratings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_ratings`
--

LOCK TABLES `supplier_ratings` WRITE;
/*!40000 ALTER TABLE `supplier_ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_return_items`
--

DROP TABLE IF EXISTS `supplier_return_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_return_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_return_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_cost` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_return_items_supplier_return_id_foreign` (`supplier_return_id`),
  KEY `supplier_return_items_product_id_foreign` (`product_id`),
  CONSTRAINT `supplier_return_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_return_items_supplier_return_id_foreign` FOREIGN KEY (`supplier_return_id`) REFERENCES `supplier_returns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_return_items`
--

LOCK TABLES `supplier_return_items` WRITE;
/*!40000 ALTER TABLE `supplier_return_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_return_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_returns`
--

DROP TABLE IF EXISTS `supplier_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_returns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `return_number` varchar(255) NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `purchase_order_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `reason` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplier_returns_return_number_unique` (`return_number`),
  KEY `supplier_returns_supplier_id_foreign` (`supplier_id`),
  KEY `supplier_returns_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `supplier_returns_user_id_foreign` (`user_id`),
  CONSTRAINT `supplier_returns_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supplier_returns_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_returns_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_returns`
--

LOCK TABLES `supplier_returns` WRITE;
/*!40000 ALTER TABLE `supplier_returns` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `rating` int(11) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_item_materials`
--

DROP TABLE IF EXISTS `transaction_item_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction_item_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_item_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `product_sku` varchar(255) DEFAULT NULL,
  `item_sku` varchar(255) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `source` varchar(255) NOT NULL DEFAULT 'store',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_item_materials_transaction_item_id_foreign` (`transaction_item_id`),
  KEY `transaction_item_materials_product_id_foreign` (`product_id`),
  KEY `transaction_item_materials_product_sku_index` (`product_sku`),
  KEY `transaction_item_materials_item_sku_index` (`item_sku`),
  CONSTRAINT `transaction_item_materials_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transaction_item_materials_transaction_item_id_foreign` FOREIGN KEY (`transaction_item_id`) REFERENCES `transaction_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_item_materials`
--

LOCK TABLES `transaction_item_materials` WRITE;
/*!40000 ALTER TABLE `transaction_item_materials` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaction_item_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_items`
--

DROP TABLE IF EXISTS `transaction_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint(20) unsigned NOT NULL,
  `item_type` varchar(255) NOT NULL DEFAULT 'product',
  `project_image` varchar(255) DEFAULT NULL,
  `project_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`project_breakdown`)),
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `product_sku` varchar(255) DEFAULT NULL,
  `item_sku` varchar(255) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `main_category_name` varchar(255) DEFAULT NULL,
  `brand_category_name` varchar(255) DEFAULT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `item_discount_type` varchar(255) DEFAULT NULL,
  `item_discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `item_discount_scope` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_items_transaction_id_foreign` (`transaction_id`),
  KEY `transaction_items_product_id_foreign` (`product_id`),
  CONSTRAINT `transaction_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transaction_items_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_items`
--

LOCK TABLES `transaction_items` WRITE;
/*!40000 ALTER TABLE `transaction_items` DISABLE KEYS */;
INSERT INTO `transaction_items` VALUES (1,1,'product',NULL,NULL,1,'MIC-640E24','MIC-640E24-IMP-6afe09c851da0','Micron #5','Filter','',80.00,80,6400.00,'2026-11-18 20:25:44','2026-11-18 20:25:44',NULL,0.00,NULL),(2,2,'product',NULL,NULL,2,'MIC-EA2E3D','MIC-EA2E3D-IMP-6afe09c854421','Micron #1','Filter','',80.00,80,6400.00,'2026-11-18 20:25:44','2026-11-18 20:25:44',NULL,0.00,NULL),(3,3,'product',NULL,NULL,3,'FAU-297C37','FAU-297C37-IMP-6afe09c8569e6','Faucet Seal','Sealer','',125.00,420,52500.00,'2026-11-18 20:25:44','2026-11-18 20:25:44',NULL,0.00,NULL),(4,4,'product',NULL,NULL,1,'MIC-640E24','MIC-640E24-IMP-6afe09cfcfeb2','Micron #5','Filter','',80.00,80,6400.00,'2026-11-18 20:25:51','2026-11-18 20:25:51',NULL,0.00,NULL),(5,5,'product',NULL,NULL,2,'MIC-EA2E3D','MIC-EA2E3D-IMP-6afe09cfd0ca2','Micron #1','Filter','',80.00,80,6400.00,'2026-11-18 20:25:51','2026-11-18 20:25:51',NULL,0.00,NULL),(6,6,'product',NULL,NULL,3,'FAU-297C37','FAU-297C37-IMP-6afe09cfd18ac','Faucet Seal','Sealer','',125.00,1400,175000.00,'2026-11-18 20:25:51','2026-11-18 20:25:51',NULL,0.00,NULL),(7,7,'product',NULL,NULL,4,'SMA-FAE299','SMA-FAE299-IMP-6afe09cfd343b','Small Cap Seal','Sealer','',105.00,1205,126525.00,'2026-11-18 20:25:51','2026-11-18 20:25:51',NULL,0.00,NULL),(8,8,'product',NULL,NULL,1,'MIC-640E24','MIC-640E24-IMP-6afe09d783da4','Micron #5','Filter','',80.00,320,25600.00,'2026-11-18 20:25:59','2026-11-18 20:25:59',NULL,0.00,NULL),(9,9,'product',NULL,NULL,2,'MIC-EA2E3D','MIC-EA2E3D-IMP-6afe09d784b9e','Micron #1','Filter','',80.00,240,19200.00,'2026-11-18 20:25:59','2026-11-18 20:25:59',NULL,0.00,NULL),(10,10,'product',NULL,NULL,3,'FAU-297C37','FAU-297C37-IMP-6afe09d7858e3','Faucet Seal','Sealer','',125.00,1400,175000.00,'2026-11-18 20:25:59','2026-11-18 20:25:59',NULL,0.00,NULL),(11,11,'product',NULL,NULL,4,'SMA-FAE299','SMA-FAE299-IMP-6afe09d786ca9','Small Cap Seal','Sealer','',105.00,210,22050.00,'2026-11-18 20:25:59','2026-11-18 20:25:59',NULL,0.00,NULL);
/*!40000 ALTER TABLE `transaction_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `cashier_name` varchar(255) DEFAULT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `transaction_type` tinyint(4) NOT NULL DEFAULT 0,
  `total_amount` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `amount_change` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cart_discount_type` varchar(255) DEFAULT NULL,
  `cart_discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_return_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transactions_transaction_id_unique` (`transaction_id`),
  KEY `transactions_user_id_foreign` (`user_id`),
  KEY `transactions_customer_return_id_foreign` (`customer_return_id`),
  CONSTRAINT `transactions_customer_return_id_foreign` FOREIGN KEY (`customer_return_id`) REFERENCES `customer_returns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,1,'Admin Ako','IMP-20260818-6afe09c8511e0',1,6400.00,6400.00,0.00,'2026-08-17 20:25:44','2026-08-17 20:25:44',NULL,0.00,NULL,NULL),(2,1,'Admin Ako','IMP-20260819-6afe09c853669',1,6400.00,6400.00,0.00,'2026-08-18 20:25:44','2026-08-18 20:25:44',NULL,0.00,NULL,NULL),(3,1,'Admin Ako','IMP-20260821-6afe09c8565ab',1,52500.00,52500.00,0.00,'2026-08-20 20:25:44','2026-08-20 20:25:44',NULL,0.00,NULL,NULL),(4,1,'Admin Ako','IMP-20260918-6afe09cfce983',1,6400.00,6400.00,0.00,'2026-09-17 20:25:51','2026-09-17 20:25:51',NULL,0.00,NULL,NULL),(5,1,'Admin Ako','IMP-20260919-6afe09cfd07ac',1,6400.00,6400.00,0.00,'2026-09-18 20:25:51','2026-09-18 20:25:51',NULL,0.00,NULL,NULL),(6,1,'Admin Ako','IMP-20260921-6afe09cfd14df',1,175000.00,175000.00,0.00,'2026-09-20 20:25:51','2026-09-20 20:25:51',NULL,0.00,NULL,NULL),(7,1,'Admin Ako','IMP-20260922-6afe09cfd2fd0',1,126525.00,126525.00,0.00,'2026-09-21 20:25:51','2026-09-21 20:25:51',NULL,0.00,NULL,NULL),(8,1,'Admin Ako','IMP-20261018-6afe09d782b1c',1,25600.00,25600.00,0.00,'2026-10-17 20:25:59','2026-10-17 20:25:59',NULL,0.00,NULL,NULL),(9,1,'Admin Ako','IMP-20261019-6afe09d784756',1,19200.00,19200.00,0.00,'2026-10-18 20:25:59','2026-10-18 20:25:59',NULL,0.00,NULL,NULL),(10,1,'Admin Ako','IMP-20261021-6afe09d785531',1,175000.00,175000.00,0.00,'2026-10-20 20:25:59','2026-10-20 20:25:59',NULL,0.00,NULL,NULL),(11,1,'Admin Ako','IMP-20261022-6afe09d786091',1,22050.00,22050.00,0.00,'2026-10-21 20:25:59','2026-10-21 20:25:59',NULL,0.00,NULL,NULL);
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `action_password` varchar(255) DEFAULT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `suffix` varchar(255) DEFAULT NULL,
  `role` enum('admin','manager','staff','warehouse_staff') NOT NULL DEFAULT 'staff',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `theme` enum('light','dark') NOT NULL DEFAULT 'light',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','stevenhydrotech@gmail.com','$2y$12$oaWW7SlLk.udCTtG0ROT/ePwxfGUUXAla27zywFnxqmkmGyu8R0Eq',NULL,'Admin','Ako',NULL,'admin',1,'light',NULL,NULL,'2026-11-18 12:03:04','2026-11-19 15:27:10'),(2,'manager','manager@hydrotech.com','$2y$12$z1S7JKm/3cMhhkjIXH2aCu74akSEOXtme6WPsKub.Il0NvaSVmCjC','$2y$12$xO6rEbEX88H/X/CazI60QO/9IGtd3OLMQyY8L1RsA/SkrY9JY1BVy','manager','germany',NULL,'manager',1,'light',NULL,NULL,'2026-11-19 18:35:15','2026-11-19 18:35:15'),(3,'staff','staff@hydrotech.com','$2y$12$SHhjb59S/ygIa3VkoMzf8euDPOZ1DGfQAodDwgc7LJSwJHbrl25rW',NULL,'staff','seargent',NULL,'staff',1,'light',NULL,NULL,'2026-11-19 18:45:46','2026-11-19 18:45:46'),(4,'warehouse_staff','warehouse@hydrotech.com','$2y$12$Aa5BEkWtiGWvy66Z6zeiwOujgm.q3MezGLnhX0cJTzXbac5QxGh9m',NULL,'warehouse','staff',NULL,'warehouse_staff',1,'light',NULL,NULL,'2026-11-19 18:46:12','2026-11-19 18:46:12');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'hydrotech_inventory'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-20  4:20:33
