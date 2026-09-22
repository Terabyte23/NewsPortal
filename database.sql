-- NewsPortal Database Schema & Seed Data
-- Character Set: utf8mb4, Collation: utf8mb4_unicode_ci

CREATE DATABASE IF NOT EXISTS `newsportal` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `newsportal`;

-- 1. Table `category`
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `category` (`id`, `name`) VALUES
(1, 'Tehnoloogia'),
(2, 'Haridus'),
(3, 'Teadus'),
(4, 'Internet'),
(5, 'Majandus'),
(6, 'Kultuur'),
(7, 'Sport');

-- 2. Table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `job` VARCHAR(100) NULL DEFAULT 'Lugeja',
  `email` VARCHAR(150) NOT NULL,
  `telefon` VARCHAR(50) NULL DEFAULT NULL,
  `login` VARCHAR(100) NOT NULL UNIQUE,
  `parol` VARCHAR(255) NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'user',
  `registratsion_date` DATE NOT NULL,
  `avatar` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial users with bcrypt hashed passwords ('admin123' and 'user123')
INSERT INTO `users` (`id`, `name`, `job`, `email`, `telefon`, `login`, `parol`, `status`, `registratsion_date`) VALUES
(1, 'Peatoimetaja', 'Peatoimetaja / Admin', 'admin@newsportal.ee', '+3725000001', 'admin', '$2y$10$w09ZkMqmUe52v8s7q8iE4e.65lJ7eF4b0w.s12Fp9J5N4rA2N4N4K', 'admin', CURDATE()),
(2, 'Tavaline Lugeja', 'Lugeja', 'user@newsportal.ee', '+3725000002', 'user', '$2y$10$t58XmPqpVe52v8s7q8iE4e.65lJ7eF4b0w.s12Fp9J5N4rA2N4N4K', 'user', CURDATE());

-- 3. Table `news`
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `text` MEDIUMTEXT NULL,
  `picture` VARCHAR(255) NULL DEFAULT '',
  `category_id` INT(11) NULL DEFAULT 1,
  `user_id` INT(11) NULL DEFAULT 1,
  `views` INT(11) NOT NULL DEFAULT 0,
  `likes` INT(11) NOT NULL DEFAULT 0,
  `image_url` VARCHAR(500) NULL DEFAULT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `reactions` TEXT NULL DEFAULT NULL,
  `tags` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Table `comments`
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `news_id` INT(11) NOT NULL,
  `text` TEXT NOT NULL,
  `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` INT(11) NULL DEFAULT NULL,
  `author_name` VARCHAR(100) NULL DEFAULT 'Lugeja',
  PRIMARY KEY (`id`),
  KEY `idx_news_id` (`news_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
