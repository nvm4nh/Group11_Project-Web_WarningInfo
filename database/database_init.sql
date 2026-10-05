CREATE DATABASE IF NOT EXISTS warninginfo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE warninginfo_db;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'user') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `latitude` DECIMAL(10, 8) NOT NULL,
  `longitude` DECIMAL(11, 8) NOT NULL,
  `address` VARCHAR(255),
  `image_url` VARCHAR(255),
  `severity` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
  `status` ENUM('pending', 'processing', 'resolved', 'rejected') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
);

CREATE TABLE `comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `report_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `content` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`report_id`) REFERENCES `reports`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE `upvotes` (
  `user_id` INT NOT NULL,
  `report_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `report_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`report_id`) REFERENCES `reports`(`id`) ON DELETE CASCADE
);

-- Dummy Data
INSERT INTO `users` (`full_name`, `email`, `password`, `role`) VALUES
('Admin User', 'admin@warninginfo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Nguyễn Văn A', 'nva@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

INSERT INTO `categories` (`name`, `description`) VALUES
('Ô nhiễm rác thải', 'Rác thải sinh hoạt, công nghiệp đổ sai quy định'),
('Ô nhiễm nguồn nước', 'Nước thải đen, bốc mùi hôi thối'),
('Ô nhiễm không khí', 'Khói bụi, khí thải từ nhà máy, xe cộ'),
('Ngập úng', 'Tắc nghẽn cống rãnh, ngập lụt sau mưa');

INSERT INTO `reports` (`user_id`, `category_id`, `title`, `description`, `latitude`, `longitude`, `address`, `severity`, `status`) VALUES
(2, 1, 'Bãi rác tự phát', 'Rất nhiều rác thải sinh hoạt đổ trộm tại hẻm.', 10.762622, 106.660172, 'Quận 10, TP.HCM', 'medium', 'pending'),
(2, 2, 'Cống xả nước đen ngòm', 'Nước xả trực tiếp ra kênh, mùi rất hôi.', 10.776889, 106.700806, 'Quận 1, TP.HCM', 'high', 'processing');

INSERT INTO `comments` (`report_id`, `user_id`, `content`) VALUES
(1, 1, 'Chính quyền địa phương đã tiếp nhận thông tin.');
