-- ============================================================
-- Database: warninginfo_db
-- Hệ thống Báo Cáo & Cảnh Báo Vấn Đề Môi Trường (WarningInfo)
-- File CSDL chuẩn hóa 10 bảng: Kết hợp nghiệp vụ chuyên sâu & Tương thích chuẩn OOP MVC
-- ============================================================

CREATE DATABASE IF NOT EXISTS `warninginfo_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `warninginfo_db`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. Bảng tài khoản & phân quyền người dùng (users)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(190) NOT NULL UNIQUE,
  `phone` VARCHAR(30) NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'officer', 'user') NOT NULL DEFAULT 'user',
  `account_status` ENUM('active', 'inactive', 'locked') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_role_status` (`role`, `account_status`)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2. Bảng danh mục sự cố môi trường (categories)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL UNIQUE,
  `description` VARCHAR(500) NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. Bảng báo cáo sự cố (reports)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `report_code` VARCHAR(30) NOT NULL UNIQUE,
  `user_id` INT NULL,
  `category_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `incident_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `address` VARCHAR(500) NULL,
  `latitude` DECIMAL(10, 7) NULL,
  `longitude` DECIMAL(10, 7) NULL,
  `priority` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
  `status` ENUM(
    'new', 'received', 'need_more_info', 'verifying',
    'verified', 'assigned', 'in_progress', 'resolved', 'closed', 'rejected'
  ) NOT NULL DEFAULT 'new',
  `is_public` BOOLEAN NOT NULL DEFAULT TRUE,
  `contact_name` VARCHAR(150) NULL,
  `contact_email` VARCHAR(190) NULL,
  `contact_phone` VARCHAR(30) NULL,
  `resolution_note` TEXT NULL,
  `resolved_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reports_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_reports_category` FOREIGN KEY (`category_id`)
    REFERENCES `categories`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX `idx_reports_status_created` (`status`, `created_at`),
  INDEX `idx_reports_category_created` (`category_id`, `created_at`),
  INDEX `idx_reports_priority_status` (`priority`, `status`),
  INDEX `idx_reports_location` (`latitude`, `longitude`)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. Bảng ảnh & video minh chứng sự cố (report_images)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `report_images`;
CREATE TABLE `report_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `report_id` INT NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `original_file_name` VARCHAR(255) NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `file_size_bytes` BIGINT UNSIGNED NULL,
  `uploaded_by` INT NULL,
  `is_public` BOOLEAN NOT NULL DEFAULT TRUE,
  `uploaded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_report_images_report` FOREIGN KEY (`report_id`)
    REFERENCES `reports`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_report_images_uploader` FOREIGN KEY (`uploaded_by`)
    REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_report_images_report` (`report_id`)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. Bảng lịch sử thay đổi trạng thái (report_status_history)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `report_status_history`;
CREATE TABLE `report_status_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `report_id` INT NOT NULL,
  `old_status` VARCHAR(40) NULL,
  `new_status` VARCHAR(40) NOT NULL,
  `changed_by` INT NULL,
  `note` TEXT NULL,
  `changed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_status_history_report` FOREIGN KEY (`report_id`)
    REFERENCES `reports`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_status_history_user` FOREIGN KEY (`changed_by`)
    REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_status_history_report_time` (`report_id`, `changed_at`)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 6. Bảng phân công cán bộ xử lý (assignments)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `assignments`;
CREATE TABLE `assignments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `report_id` INT NOT NULL,
  `officer_id` INT NOT NULL,
  `assigned_by` INT NULL,
  `assignment_note` TEXT NULL,
  `assignment_status` ENUM('assigned', 'accepted', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'assigned',
  `assigned_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` DATETIME NULL,
  CONSTRAINT `fk_assignments_report` FOREIGN KEY (`report_id`)
    REFERENCES `reports`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_assignments_officer` FOREIGN KEY (`officer_id`)
    REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_assignments_assigner` FOREIGN KEY (`assigned_by`)
    REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_assignments_officer_status` (`officer_id`, `assignment_status`)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7. Bảng thông báo người dùng & cán bộ (notifications)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `report_id` INT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `notification_type` VARCHAR(50) NOT NULL DEFAULT 'general',
  `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` DATETIME NULL,
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notifications_report` FOREIGN KEY (`report_id`)
    REFERENCES `reports`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_notifications_user_read` (`user_id`, `is_read`, `created_at`)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 8. Bảng bình luận & trao đổi thông tin (comments)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `report_id` INT NOT NULL,
  `user_id` INT NULL,
  `content` TEXT NOT NULL,
  `is_internal` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_comments_report` FOREIGN KEY (`report_id`)
    REFERENCES `reports`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_comments_report_time` (`report_id`, `created_at`)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 9. Bảng nhật ký thao tác quản trị (admin_logs)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `admin_logs`;
CREATE TABLE `admin_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT NULL,
  `action` VARCHAR(120) NOT NULL,
  `target_type` VARCHAR(80) NULL,
  `target_id` INT NULL,
  `details` JSON NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(500) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_admin_logs_admin` FOREIGN KEY (`admin_id`)
    REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_admin_logs_admin_time` (`admin_id`, `created_at`)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 10. Bảng lượt quan tâm / ủng hộ báo cáo (upvotes)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `upvotes`;
CREATE TABLE `upvotes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `report_id` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_upvotes_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_upvotes_report` FOREIGN KEY (`report_id`)
    REFERENCES `reports`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `uq_upvote_user_report` UNIQUE (`user_id`, `report_id`),
  INDEX `idx_upvotes_report` (`report_id`)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DỮ LIỆU KHỞI TẠO MẪU (DEMO SEED DATA)
-- Mật khẩu mặc định: 'password' (đã băm bằng password_hash bcrypt)
-- ============================================================

-- 1. Tài khoản mẫu
INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `role`, `account_status`) VALUES
(1, 'Quản trị viên Hệ thống', 'admin@warninginfo.com', '0900000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
(2, 'Cán bộ Môi trường', 'officer@warninginfo.com', '0900000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'officer', 'active'),
(3, 'Nguyễn Minh An', 'an.demo@warninginfo.local', '0900000003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active'),
(4, 'Trần Thu Bình', 'binh.demo@warninginfo.local', '0900000004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active'),
(5, 'Lê Quốc Cường', 'cuong.demo@warninginfo.local', '0900000005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active');

-- 2. Danh mục mẫu (7 danh mục khớp hoàn toàn thanh menu giao diện)
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Ô nhiễm rác thải', 'Rác thải sinh hoạt, công nghiệp đổ bừa bãi sai quy định'),
(2, 'Ô nhiễm nguồn nước', 'Nước có màu, mùi bất thường hoặc nghi bị xả thải độc hại'),
(3, 'Ô nhiễm không khí', 'Khói, bụi hoặc khí thải công nghiệp gây khó thở'),
(4, 'Ngập úng', 'Tắc nghẽn cống rãnh, ngập lụt cục bộ sau mưa lớn'),
(5, 'Cháy rừng', 'Sự cố cháy thực bì, cháy rừng phòng hộ hoặc rừng tự nhiên'),
(6, 'Ô nhiễm tiếng ồn', 'Tiếng ồn công trình, sản xuất vượt mức quy định vào giờ nghỉ'),
(7, 'Sự cố hoá chất', 'Rò rỉ hoá chất, khí độc hoặc sự cố tràn dầu');

-- 3. Báo cáo sự cố mẫu
INSERT INTO `reports`
(`id`, `report_code`, `user_id`, `category_id`, `title`, `description`, `incident_time`, `address`, `latitude`, `longitude`, `priority`, `status`, `is_public`, `contact_name`, `contact_email`, `contact_phone`)
VALUES
(1, 'WI-20261001-0001', 3, 2, 'Nước kênh đổi màu bất thường', 'Người dân ghi nhận nước kênh Nhiêu Lộc chuyển màu đen và có mùi tanh lạ.', '2026-10-01 07:30:00', 'Quận 3, TP. Hồ Chí Minh', 10.7765000, 106.7009000, 'high', 'received', TRUE, 'Nguyễn Minh An', 'an.demo@warninginfo.local', '0900000003'),
(2, 'WI-20261002-0002', 4, 3, 'Khói đen phát ra từ khu xưởng', 'Khói đen dày phát ra liên tục trong hơn 2 giờ từ ống khói nhà máy.', '2026-10-02 16:15:00', 'KCN Tân Bình, Tân Phú, TP. Hồ Chí Minh', 10.8231000, 106.6297000, 'critical', 'verifying', TRUE, 'Trần Thu Bình', 'binh.demo@warninginfo.local', '0900000004'),
(3, 'WI-20261003-0003', 5, 1, 'Bãi rác tự phát lấn chiếm lòng lề đường', 'Nhiều bao rác và xà bần bị đổ trộm chắn lối đi của người dân.', '2026-10-03 09:00:00', 'Đường số 3, TP. Thủ Đức, TP. Hồ Chí Minh', 10.8506000, 106.7719000, 'medium', 'new', TRUE, 'Lê Quốc Cường', 'cuong.demo@warninginfo.local', '0900000005'),
(4, 'WI-20261004-0004', 3, 6, 'Tiếng ồn công trình ban đêm', 'Công trình thi công đóng cọc xuyên đêm từ 23h đến 3h sáng gây mất ngủ.', '2026-10-04 22:10:00', 'Quận 10, TP. Hồ Chí Minh', 10.7626000, 106.6602000, 'medium', 'in_progress', TRUE, 'Nguyễn Minh An', 'an.demo@warninginfo.local', '0900000003'),
(5, 'WI-20261005-0005', 4, 7, 'Mùi hóa chất lạ bốc lên từ cống', 'Mùi nồng nặc nghi rò rỉ hóa chất tẩy rửa công nghiệp bốc lên nồng nặc.', '2026-10-05 11:45:00', 'Bình Thạnh, TP. Hồ Chí Minh', 10.7900000, 106.6800000, 'high', 'resolved', TRUE, 'Trần Thu Bình', 'binh.demo@warninginfo.local', '0900000004');

-- 4. Phân công mẫu
INSERT INTO `assignments` (`report_id`, `officer_id`, `assigned_by`, `assignment_note`, `assignment_status`) VALUES
(2, 2, 1, 'Đề nghị cán bộ kiểm tra nồng độ khí thải tại hiện trường', 'assigned'),
(4, 2, 1, 'Đo đạc mức độ decibel tiếng ồn ban đêm và lập biên bản nếu vi phạm', 'in_progress'),
(5, 2, 1, 'Đã phối hợp cùng lực lượng PCCC kiểm tra và trung hòa nguồn phát sinh', 'completed');

-- 5. Lịch sử trạng thái mẫu
INSERT INTO `report_status_history` (`report_id`, `old_status`, `new_status`, `changed_by`, `note`) VALUES
(1, NULL, 'new', 3, 'Người dân gửi báo cáo ban đầu'),
(1, 'new', 'received', 1, 'Ban quản trị đã tiếp nhận và đang xác minh'),
(2, NULL, 'verifying', 1, 'Đang điều phối cán bộ chuyên trách xuống hiện trường'),
(5, 'in_progress', 'resolved', 2, 'Đã xử lý xong điểm nóng mùi hóa chất');

-- 6. Bình luận mẫu
INSERT INTO `comments` (`report_id`, `user_id`, `content`, `is_internal`) VALUES
(1, 4, 'Tôi sống ở gần đây và xác nhận nước kênh hôm nay đen hơn hẳn ngày thường.', FALSE),
(2, 2, 'Ghi nhận: Nhà xưởng đã ngắt lò đốt tạm thời, đang lập biên bản xử phạt.', TRUE);

-- 7. Lượt ủng hộ mẫu
INSERT INTO `upvotes` (`user_id`, `report_id`) VALUES
(3, 2),
(4, 1),
(4, 2),
(5, 1),
(5, 4);

-- 8. Thông báo mẫu
INSERT INTO `notifications` (`user_id`, `report_id`, `title`, `message`, `notification_type`) VALUES
(3, 1, 'Báo cáo đã được tiếp nhận', 'Báo cáo WI-20261001-0001 của bạn đã được chuyển cho bộ phận kiểm tra.', 'report_status'),
(4, 2, 'Tiến độ xử lý', 'Cán bộ xử lý đã được điều động đến KCN Tân Bình.', 'report_status');
