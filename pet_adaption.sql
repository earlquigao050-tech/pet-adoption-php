
CREATE DATABASE IF NOT EXISTS `pet_adoption`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `pet_adoption`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `adoption_requests`;
DROP TABLE IF EXISTS `pets`;
DROP TABLE IF EXISTS `users`;

-- ==========================================
-- TABLE: users
-- ==========================================
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLE: pets
-- ==========================================
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
) ENGINE=InnoDB AUTO_INCREMENT=31
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLE: adoption_requests
-- ==========================================
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
  CONSTRAINT `adoption_requests_ibfk_1`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `adoption_requests_ibfk_2`
    FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- INSERT USERS
-- ==========================================
INSERT INTO `users` (`id`,`name`,`email`,`password`,`role`,`created_at`) VALUES
(1,'Admin','admin@petadopt.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','2026-04-22 18:14:15'),
(3,'Aditya Satyadeep Ghodke','ghodkeaditya07@gmail.com','$2y$10$Gdet7qU2Hppsm2wtAvfhfOLH8Y35d11RelXnay8YM/8dbLuMWEKZ6','user','2026-04-24 07:48:33');

-- ==========================================
-- INSERT PETS
-- ==========================================
INSERT INTO `pets` VALUES
(1,'Bruno','Labrador Retriever','Dog',2,'Male','Large','Golden','Bruno is a very affectionate and friendly dog who gets along wonderfully with children.','dog1.jpg','Available','2026-04-22 18:14:15'),
(2,'Moti','Indian Pariah','Dog',3,'Male','Medium','Brown','Moti is a loyal and intelligent Indian Pariah dog.','dog2.jpg','Available','2026-04-22 18:14:15'),
(3,'Kitty','Persian','Cat',1,'Female','Small','White','Kitty is a beautiful Persian cat who enjoys spending time alone at home.','cat1.jpg','Available','2026-04-22 18:14:15'),
(4,'Tommy','German Shepherd','Dog',4,'Male','Large','Black & Tan','Tommy is a trained German Shepherd and an excellent guard dog.','dog3.jpg','Available','2026-04-22 18:14:15'),
(5,'Bella','Beagle','Dog',2,'Female','Small','Tricolor','Bella is a playful Beagle who loves to exercise.','dog4.jpg','Available','2026-04-22 18:14:15'),
(6,'Mittens','Tabby','Cat',2,'Female','Small','Orange','Mittens is a very affectionate lap cat.','cat2.jpg','Available','2026-04-22 18:14:15'),
(7,'Rocky','Rottweiler','Dog',3,'Male','Large','Black','Rocky is a strong, protective dog best suited to experienced owners.','dog5.jpg','Available','2026-04-22 18:14:15'),
(8,'Coco','Pomeranian','Dog',1,'Female','Small','White','Coco is a cute, fluffy Pomeranian puppy, perfect for apartment living.','dog6.jpg','Available','2026-04-22 18:14:15'),
(9,'Shadow','Black Cat','Cat',3,'Male','Medium','Black','Shadow is a mysterious, independent cat who is easy to care for.','cat3.jpg','Available','2026-04-22 18:14:15'),
(10,'Goldie','Goldfish','Other',1,'Female','Small','Orange','Goldie is a beautiful goldfish and a perfect pet for beginners.','fish1.jpg','Available','2026-04-22 18:14:15'),
(11,'Tweety','Budgerigar','Bird',1,'Male','Small','Yellow','Tweety is a talkative budgie and a very entertaining pet.','bird1.jpg','Available','2026-04-22 18:14:15'),
(12,'Max','Golden Retriever','Dog',5,'Male','Large','Golden','Max is a gentle giant and an excellent choice for a family dog.','dog7.jpg','Available','2026-04-22 18:14:15'),
(13,'Luna','Siamese','Cat',2,'Female','Medium','Cream','Luna is a vocal and affectionate Siamese cat.','cat4.jpg','Available','2026-04-22 18:14:15'),
(14,'Bunny','Dutch Rabbit','Rabbit',1,'Female','Small','White & Black','Bunny is an adorable Dutch rabbit who is great with children.','rabbit1.jpg','Available','2026-04-22 18:14:15'),
(15,'Rex','Doberman','Dog',2,'Male','Large','Black & Rust','Rex is an intelligent and loyal Doberman, well suited for home security.','dog8.jpg','Available','2026-04-22 18:14:15'),
(16,'Bruno','Labrador Retriever','Dog',2,'Male','Large','Golden','Bruno is a very affectionate and friendly dog who gets along wonderfully with children.','dog1.jpg','Available','2026-04-22 18:15:05'),
(17,'Moti','Indian Pariah','Dog',3,'Male','Medium','Brown','Moti is a loyal and intelligent Indian Pariah dog.','dog2.jpg','Available','2026-04-22 18:15:05'),
(18,'Kitty','Persian','Cat',1,'Female','Small','White','Kitty is a beautiful Persian cat who enjoys spending time alone at home.','cat1.jpg','Available','2026-04-22 18:15:05'),
(19,'Tommy','German Shepherd','Dog',4,'Male','Large','Black & Tan','Tommy is a trained German Shepherd and an excellent guard dog.','dog3.jpg','Available','2026-04-22 18:15:05'),
(20,'Bella','Beagle','Dog',2,'Female','Small','Tricolor','Bella is a playful Beagle who loves to exercise.','dog4.jpg','Available','2026-04-22 18:15:05'),
(21,'Mittens','Tabby','Cat',2,'Female','Small','Orange','Mittens is a very affectionate lap cat.','cat2.jpg','Available','2026-04-22 18:15:05'),
(22,'Rocky','Rottweiler','Dog',3,'Male','Large','Black','Rocky is a strong, protective dog best suited to experienced owners.','dog5.jpg','Available','2026-04-22 18:15:05'),
(23,'Coco','Pomeranian','Dog',1,'Female','Small','White','Coco is a cute, fluffy Pomeranian puppy, perfect for apartment living.','dog6.jpg','Available','2026-04-22 18:15:05'),
(24,'Shadow','Black Cat','Cat',3,'Male','Medium','Black','Shadow is a mysterious, independent cat who is easy to care for.','cat3.jpg','Available','2026-04-22 18:15:05'),
(25,'Goldie','Goldfish','Other',1,'Female','Small','Orange','Goldie is a beautiful goldfish and a perfect pet for beginners.','fish1.jpg','Available','2026-04-22 18:15:05'),
(26,'Tweety','Budgerigar','Bird',1,'Male','Small','Yellow','Tweety is a talkative budgie and a very entertaining pet.','bird1.jpg','Available','2026-04-22 18:15:05'),
(27,'Max','Golden Retriever','Dog',5,'Male','Large','Golden','Max is a gentle giant and an excellent choice for a family dog.','dog7.jpg','Available','2026-04-22 18:15:05'),
(28,'Luna','Siamese','Cat',2,'Female','Medium','Cream','Luna is a vocal and affectionate Siamese cat.','cat4.jpg','Available','2026-04-22 18:15:05'),
(29,'Bunny','Dutch Rabbit','Rabbit',1,'Female','Small','White & Black','Bunny is an adorable Dutch rabbit who is great with children.','rabbit1.jpg','Available','2026-04-22 18:15:05'),
(30,'Rex','Doberman','Dog',2,'Male','Large','Black & Rust','Rex is an intelligent and loyal Doberman, well suited for home security.','dog8.jpg','Available','2026-04-22 18:15:05');

SET FOREIGN_KEY_CHECKS = 1;
