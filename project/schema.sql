-- 1. Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `event`;
USE `event`;

-- 2. Create the `event` table (singular name & singular column names matching index.php & register.php)
CREATE TABLE IF NOT EXISTS `event` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `total_seat` INT NOT NULL DEFAULT 50,
  `booked_seat` INT NOT NULL DEFAULT 0
);

-- 3. Create the `registrations` table
CREATE TABLE IF NOT EXISTS `registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `college_id` VARCHAR(50) NOT NULL,
  `branch` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `event_id` INT NOT NULL,
  `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`event_id`) REFERENCES `event`(`id`) ON DELETE CASCADE
);

-- 4. Insert initial event data matching $eventDetails in index.php
INSERT INTO `event` (`name`, `total_seat`, `booked_seat`) VALUES
('Tech Talk', 100, 0),
('Cyber Security Workshop', 50, 0),
('Mystery Seekers', 40, 0),
('Silent DJ', 80, 0),
('Sprint Brawls', 60, 0),
('Fun Games', 50, 0),
('Celebrity Night', 200, 0);