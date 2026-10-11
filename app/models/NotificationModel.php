<?php
class NotificationModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /** Tạo 1 thông báo mới */
    public function create($userId, $title, $message, $reportId = null, $type = 'general') {
        $this->db->query("INSERT INTO notifications (user_id, report_id, title, message, notification_type)
                          VALUES (:user_id, :report_id, :title, :message, :type)");
        $this->db->bind(':user_id',   $userId);
        $this->db->bind(':report_id', $reportId);
        $this->db->bind(':title',     $title);
        $this->db->bind(':message',   $message);
        $this->db->bind(':type',      $type);
        return $this->db->execute();
    }

    /** Lấy danh sách thông báo của 1 user */
    public function getByUser($userId, $limit = 20) {
        $this->db->query("SELECT * FROM notifications
                          WHERE user_id = :user_id
                          ORDER BY created_at DESC
                          LIMIT :limit");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit',   (int)$limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /** Đếm số thông báo chưa đọc */
    public function countUnread($userId) {
        $this->db->query("SELECT COUNT(*) AS total FROM notifications
                          WHERE user_id = :user_id AND is_read = 0");
        $this->db->bind(':user_id', $userId);
        $row = $this->db->single();
        return $row ? (int)$row->total : 0;
    }

    /** Đánh dấu 1 thông báo đã đọc */
    public function markAsRead($notificationId, $userId) {
        $this->db->query("UPDATE notifications
                          SET is_read = 1, read_at = NOW()
                          WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':id',      $notificationId);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    /** Đánh dấu tất cả thông báo đã đọc */
    public function markAllAsRead($userId) {
        $this->db->query("UPDATE notifications
                          SET is_read = 1, read_at = NOW()
                          WHERE user_id = :user_id AND is_read = 0");
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    /** Helper: dịch status code sang tiếng Việt */
    public static function statusLabel($status) {
        $map = [
            'new'            => 'Mới tiếp nhận',
            'received'       => 'Đã tiếp nhận',
            'need_more_info' => 'Cần bổ sung thông tin',
            'verifying'      => 'Đang xác minh',
            'verified'       => 'Đã xác minh',
            'assigned'       => 'Đã phân công cán bộ',
            'in_progress'    => 'Đang xử lý',
            'resolved'       => 'Đã khắc phục',
            'closed'         => 'Đã đóng',
            'rejected'       => 'Bị từ chối',
        ];
        return $map[$status] ?? $status;
    }
}