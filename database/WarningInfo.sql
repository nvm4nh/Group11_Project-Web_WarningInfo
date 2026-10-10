-- ============================================================
-- WarningInfo.sql
-- Database cho website báo cáo các vấn đề môi trường khẩn cấp
-- MySQL 8.0+
-- ============================================================

CREATE DATABASE IF NOT EXISTS WarningInfo
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE WarningInfo;

SET NAMES utf8mb4;

-- Tài khoản người dùng, cán bộ xử lý và quản trị viên
CREATE TABLE IF NOT EXISTS users (
    user_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'officer', 'admin') NOT NULL DEFAULT 'user',
    account_status ENUM('active', 'inactive', 'locked') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role_status (role, account_status)
) ENGINE=InnoDB;

-- Danh mục loại sự cố môi trường
CREATE TABLE IF NOT EXISTS incident_categories (
    category_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(500) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Báo cáo sự cố
CREATE TABLE IF NOT EXISTS reports (
    report_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_code VARCHAR(30) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NULL,
    category_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    incident_time DATETIME NOT NULL,
    address VARCHAR(500) NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    priority ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
    status ENUM(
        'new', 'received', 'need_more_info', 'verifying',
        'verified', 'assigned', 'in_progress', 'resolved', 'closed', 'rejected'
    ) NOT NULL DEFAULT 'new',
    is_public BOOLEAN NOT NULL DEFAULT FALSE,
    contact_name VARCHAR(150) NULL,
    contact_email VARCHAR(190) NULL,
    contact_phone VARCHAR(30) NULL,
    resolution_note TEXT NULL,
    resolved_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reports_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_reports_category FOREIGN KEY (category_id)
        REFERENCES incident_categories(category_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_reports_latitude CHECK (latitude IS NULL OR latitude BETWEEN -90 AND 90),
    CONSTRAINT chk_reports_longitude CHECK (longitude IS NULL OR longitude BETWEEN -180 AND 180),
    INDEX idx_reports_status_created (status, created_at),
    INDEX idx_reports_category_created (category_id, created_at),
    INDEX idx_reports_priority_status (priority, status),
    INDEX idx_reports_location (latitude, longitude),
    INDEX idx_reports_incident_time (incident_time)
) ENGINE=InnoDB;

-- Ảnh/video minh chứng: chỉ lưu đường dẫn tệp, tệp cần được kiểm tra ở tầng ứng dụng
CREATE TABLE IF NOT EXISTS report_images (
    image_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_file_name VARCHAR(255) NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size_bytes BIGINT UNSIGNED NULL,
    uploaded_by BIGINT UNSIGNED NULL,
    is_public BOOLEAN NOT NULL DEFAULT FALSE,
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_report_images_report FOREIGN KEY (report_id)
        REFERENCES reports(report_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_report_images_uploader FOREIGN KEY (uploaded_by)
        REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_report_images_report (report_id)
) ENGINE=InnoDB;

-- Lịch sử thay đổi trạng thái
CREATE TABLE IF NOT EXISTS report_status_history (
    history_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id BIGINT UNSIGNED NOT NULL,
    old_status VARCHAR(40) NULL,
    new_status VARCHAR(40) NOT NULL,
    changed_by BIGINT UNSIGNED NULL,
    note TEXT NULL,
    changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_status_history_report FOREIGN KEY (report_id)
        REFERENCES reports(report_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_status_history_user FOREIGN KEY (changed_by)
        REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_status_history_report_time (report_id, changed_at)
) ENGINE=InnoDB;

-- Phân công cán bộ xử lý báo cáo
CREATE TABLE IF NOT EXISTS assignments (
    assignment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id BIGINT UNSIGNED NOT NULL,
    officer_id BIGINT UNSIGNED NOT NULL,
    assigned_by BIGINT UNSIGNED NULL,
    assignment_note TEXT NULL,
    assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    assignment_status ENUM('assigned', 'accepted', 'in_progress', 'completed', 'cancelled')
        NOT NULL DEFAULT 'assigned',
    CONSTRAINT fk_assignments_report FOREIGN KEY (report_id)
        REFERENCES reports(report_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_assignments_officer FOREIGN KEY (officer_id)
        REFERENCES users(user_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_assignments_assigner FOREIGN KEY (assigned_by)
        REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_assignments_officer_status (officer_id, assignment_status),
    INDEX idx_assignments_report (report_id)
) ENGINE=InnoDB;

-- Thông báo tới người dùng/cán bộ
CREATE TABLE IF NOT EXISTS notifications (
    notification_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    report_id BIGINT UNSIGNED NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    notification_type VARCHAR(50) NOT NULL DEFAULT 'general',
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_notifications_report FOREIGN KEY (report_id)
        REFERENCES reports(report_id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_notifications_user_read_time (user_id, is_read, created_at)
) ENGINE=InnoDB;

-- Bình luận, trao đổi và yêu cầu bổ sung thông tin
CREATE TABLE IF NOT EXISTS comments (
    comment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    comment_text TEXT NOT NULL,
    is_internal BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comments_report FOREIGN KEY (report_id)
        REFERENCES reports(report_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_comments_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_comments_report_time (report_id, created_at)
) ENGINE=InnoDB;

-- Nhật ký thao tác quản trị
CREATE TABLE IF NOT EXISTS admin_logs (
    log_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id BIGINT UNSIGNED NULL,
    action VARCHAR(120) NOT NULL,
    target_type VARCHAR(80) NULL,
    target_id BIGINT UNSIGNED NULL,
    details JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_admin_logs_admin FOREIGN KEY (admin_id)
        REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_admin_logs_admin_time (admin_id, created_at),
    INDEX idx_admin_logs_target (target_type, target_id)
) ENGINE=InnoDB;

-- Dữ liệu danh mục mẫu (không thêm trùng nếu chạy lại script)
INSERT INTO incident_categories (category_name, description) VALUES
('Ô nhiễm nguồn nước', 'Nước có màu, mùi bất thường hoặc nghi bị ô nhiễm'),
('Khói bụi bất thường', 'Khói, bụi hoặc khí thải gây ảnh hưởng môi trường'),
('Đổ rác trái phép', 'Đổ rác hoặc chất thải không đúng nơi quy định'),
('Ô nhiễm tiếng ồn', 'Tiếng ồn vượt mức hoặc kéo dài'),
('Sự cố môi trường khác', 'Các sự cố môi trường chưa thuộc danh mục trên')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- ============================================================
-- TRUY VẤN MẪU
-- ============================================================

-- 1. Liệt kê 20 báo cáo mới nhất
-- SELECT r.report_code, r.title, c.category_name, r.priority, r.status,
--        r.address, r.incident_time, r.created_at
-- FROM reports r
-- JOIN incident_categories c ON c.category_id = r.category_id
-- ORDER BY r.created_at DESC
-- LIMIT 20;

-- 2. Đếm báo cáo theo trạng thái
-- SELECT status, COUNT(*) AS total_reports
-- FROM reports
-- GROUP BY status
-- ORDER BY total_reports DESC;

-- 3. Lọc báo cáo theo danh mục
-- SELECT r.report_code, r.title, r.status, r.created_at
-- FROM reports r
-- JOIN incident_categories c ON c.category_id = r.category_id
-- WHERE c.category_name = 'Ô nhiễm nguồn nước'
-- ORDER BY r.created_at DESC;

-- 4. Thống kê số báo cáo theo khu vực (dựa trên địa chỉ)
-- SELECT address, COUNT(*) AS total_reports
-- FROM reports
-- WHERE address IS NOT NULL AND address <> ''
-- GROUP BY address
-- ORDER BY total_reports DESC;

-- 5. Thống kê báo cáo theo tháng
-- SELECT DATE_FORMAT(created_at, '%Y-%m') AS report_month,
--        COUNT(*) AS total_reports
-- FROM reports
-- GROUP BY DATE_FORMAT(created_at, '%Y-%m')
-- ORDER BY report_month DESC;

-- Lưu ý bảo mật:
-- * Lưu password_hash được tạo bằng password_hash() của PHP, không lưu mật khẩu thuần.
-- * Dùng prepared statements trong PHP để tránh SQL injection.
-- * Kiểm tra quyền truy cập ở phía máy chủ; không dựa vào giao diện để phân quyền.
-- * Kiểm tra MIME type, phần mở rộng, dung lượng và tên tệp khi upload.
-- * Không công khai email/số điện thoại/toạ độ chính xác nếu chưa được phép.
