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

    /**
     * Lấy danh sách thông báo của 1 user (mới nhất trước).
     * Kèm mã báo cáo liên quan và số giây đã trôi qua (age_seconds – tính bằng MySQL để không lệch múi giờ).
     */
    public function getByUser($userId, $limit = 20, $unreadOnly = false) {
        $this->db->query("SELECT n.*, r.report_code,
                                 TIMESTAMPDIFF(SECOND, n.created_at, NOW()) AS age_seconds
                          FROM notifications n
                          LEFT JOIN reports r ON r.id = n.report_id
                          WHERE n.user_id = :user_id" . ($unreadOnly ? " AND n.is_read = 0" : "") . "
                          ORDER BY n.created_at DESC, n.id DESC
                          LIMIT :limit");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit',   (int)$limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /** Tổng số thông báo của 1 user */
    public function countAll($userId) {
        $this->db->query("SELECT COUNT(*) AS total FROM notifications WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        $row = $this->db->single();
        return $row ? (int)$row->total : 0;
    }

    /** Lấy 1 thông báo – chỉ khi thông báo đó thuộc về user (không xem trộm được của người khác) */
    public function findForUser($notificationId, $userId) {
        $this->db->query("SELECT * FROM notifications WHERE id = :id AND user_id = :user_id LIMIT 1");
        $this->db->bind(':id',      (int)$notificationId);
        $this->db->bind(':user_id', $userId);
        return $this->db->single() ?: null;
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