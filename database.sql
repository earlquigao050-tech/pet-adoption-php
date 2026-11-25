-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: pet_adoption
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

CREATE DATABASE IF NOT EXISTS `pet_adoption`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `pet_adoption`;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `adoption_requests`
--

DROP TABLE IF EXISTS `adoption_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adoption_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `pet_id` int DEFAULT NULL,
  `message` text,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `pet_id` (`pet_id`),
  CONSTRAINT `adoption_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `adoption_requests_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adoption_requests`
--

LOCK TABLES `adoption_requests` WRITE;
/*!40000 ALTER TABLE `adoption_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `adoption_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pets`
--

DROP TABLE IF EXISTS `pets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `breed` varchar(100) DEFAULT NULL,
  `species` enum('Dog','Cat','Bird','Rabbit','Other') DEFAULT 'Dog',
  `age` int DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT 'Male',
  `size` enum('Small','Medium','Large') DEFAULT 'Medium',
  `color` varchar(50) DEFAULT NULL,
  `description` text,
  `image` varchar(255) DEFAULT 'default.jpg',
  `status` enum('Available','Pending','Adopted') DEFAULT 'Available',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pets`
--

LOCK TABLES `pets` WRITE;
/*!40000 ALTER TABLE `pets` DISABLE KEYS */;
INSERT INTO `pets` VALUES (1,'Bruno','Labrador Retriever','Dog',2,'Male','Large','Golden','Bruno is a very affectionate and friendly dog who gets along wonderfully with children.','dog1.jpg','Available','2026-04-22 18:14:15'),(2,'Moti','Indian Pariah','Dog',3,'Male','Medium','Brown','Moti is a loyal and intelligent Indian Pariah dog.','dog2.jpg','Available','2026-04-22 18:14:15'),(3,'Kitty','Persian','Cat',1,'Female','Small','White','Kitty is a beautiful Persian cat who enjoys spending time alone at home.','cat1.jpg','Available','2026-04-22 18:14:15'),(4,'Tommy','German Shepherd','Dog',4,'Male','Large','Black & Tan','Tommy is a trained German Shepherd and an excellent guard dog.','dog3.jpg','Available','2026-04-22 18:14:15'),(5,'Bella','Beagle','Dog',2,'Female','Small','Tricolor','Bella is a playful Beagle who loves to exercise.','dog4.jpg','Available','2026-04-22 18:14:15'),(6,'Mittens','Tabby','Cat',2,'Female','Small','Orange','Mittens is a very affectionate lap cat.','cat2.jpg','Available','2026-04-22 18:14:15'),(7,'Rocky','Rottweiler','Dog',3,'Male','Large','Black','Rocky is a strong, protective dog best suited to experienced owners.','dog5.jpg','Available','2026-04-22 18:14:15'),(8,'Coco','Pomeranian','Dog',1,'Female','Small','White','Coco is a cute, fluffy Pomeranian puppy, perfect for apartment living.','dog6.jpg','Available','2026-04-22 18:14:15'),(9,'Shadow','Black Cat','Cat',3,'Male','Medium','Black','Shadow is a mysterious, independent cat who is easy to care for.','cat3.jpg','Available','2026-04-22 18:14:15'),(10,'Goldie','Goldfish','Other',1,'Female','Small','Orange','Goldie is a beautiful goldfish and a perfect pet for beginners.','fish1.jpg','Available','2026-04-22 18:14:15'),(11,'Tweety','Budgerigar','Bird',1,'Male','Small','Yellow','Tweety is a talkative budgie and a very entertaining pet.','bird1.jpg','Available','2026-04-22 18:14:15'),(12,'Max','Golden Retriever','Dog',5,'Male','Large','Golden','Max is a gentle giant and an excellent choice for a family dog.','dog7.jpg','Available','2026-04-22 18:14:15'),(13,'Luna','Siamese','Cat',2,'Female','Medium','Cream','Luna is a vocal and affectionate Siamese cat.','cat4.jpg','Available','2026-04-22 18:14:15'),(14,'Bunny','Dutch Rabbit','Rabbit',1,'Female','Small','White & Black','Bunny is an adorable Dutch rabbit who is great with children.','rabbit1.jpg','Available','2026-04-22 18:14:15'),(15,'Rex','Doberman','Dog',2,'Male','Large','Black & Rust','Rex is an intelligent and loyal Doberman, well suited for home security.','dog8.jpg','Available','2026-04-22 18:14:15'),(16,'Bruno','Labrador Retriever','Dog',2,'Male','Large','Golden','Bruno is a very affectionate and friendly dog who gets along wonderfully with children.','dog1.jpg','Available','2026-04-22 18:15:05'),(17,'Moti','Indian Pariah','Dog',3,'Male','Medium','Brown','Moti is a loyal and intelligent Indian Pariah dog.','dog2.jpg','Available','2026-04-22 18:15:05'),(18,'Kitty','Persian','Cat',1,'Female','Small','White','Kitty is a beautiful Persian cat who enjoys spending time alone at home.','cat1.jpg','Available','2026-04-22 18:15:05'),(19,'Tommy','German Shepherd','Dog',4,'Male','Large','Black & Tan','Tommy is a trained German Shepherd and an excellent guard dog.','dog3.jpg','Available','2026-04-22 18:15:05'),(20,'Bella','Beagle','Dog',2,'Female','Small','Tricolor','Bella is a playful Beagle who loves to exercise.','dog4.jpg','Available','2026-04-22 18:15:05'),(21,'Mittens','Tabby','Cat',2,'Female','Small','Orange','Mittens is a very affectionate lap cat.','cat2.jpg','Available','2026-04-22 18:15:05'),(22,'Rocky','Rottweiler','Dog',3,'Male','Large','Black','Rocky is a strong, protective dog best suited to experienced owners.','dog5.jpg','Available','2026-04-22 18:15:05'),(23,'Coco','Pomeranian','Dog',1,'Female','Small','White','Coco is a cute, fluffy Pomeranian puppy, perfect for apartment living.','dog6.jpg','Available','2026-04-22 18:15:05'),(24,'Shadow','Black Cat','Cat',3,'Male','Medium','Black','Shadow is a mysterious, independent cat who is easy to care for.','cat3.jpg','Available','2026-04-22 18:15:05'),(25,'Goldie','Goldfish','Other',1,'Female','Small','Orange','Goldie is a beautiful goldfish and a perfect pet for beginners.','fish1.jpg','Available','2026-04-22 18:15:05'),(26,'Tweety','Budgerigar','Bird',1,'Male','Small','Yellow','Tweety is a talkative budgie and a very entertaining pet.','bird1.jpg','Available','2026-04-22 18:15:05'),(27,'Max','Golden Retriever','Dog',5,'Male','Large','Golden','Max is a gentle giant and an excellent choice for a family dog.','dog7.jpg','Available','2026-04-22 18:15:05'),(28,'Luna','Siamese','Cat',2,'Female','Medium','Cream','Luna is a vocal and affectionate Siamese cat.','cat4.jpg','Available','2026-04-22 18:15:05'),(29,'Bunny','Dutch Rabbit','Rabbit',1,'Female','Small','White & Black','Bunny is an adorable Dutch rabbit who is great with children.','rabbit1.jpg','Available','2026-04-22 18:15:05'),(30,'Rex','Doberman','Dog',2,'Male','Large','Black & Rust','Rex is an intelligent and loyal Doberman, well suited for home security.','dog8.jpg','Available','2026-04-22 18:15:05');
/*!40000 ALTER TABLE `pets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','admin@petadopt.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','2026-04-22 18:14:15'),(3,'Aditya Satyadeep Ghodke','ghodkeaditya07@gmail.com','$2y$10$Gdet7qU2Hppsm2wtAvfhfOLH8Y35d11RelXnay8YM/8dbLuMWEKZ6','user','2026-04-24 07:48:33');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-25 12:07:05

CREATE TABLE IF NOT EXISTS `api_tokens` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `api_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
