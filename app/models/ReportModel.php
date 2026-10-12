<?php
class ReportModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getStats() {
        $stats = [
            'total_reports' => 0,
            'processing'    => 0,
            'resolved'      => 0,
            'total_users'   => 0
        ];

        $this->db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status IN ('received', 'verifying', 'assigned', 'in_progress') THEN 1 ELSE 0 END) as processing,
            SUM(CASE WHEN status IN ('resolved', 'closed') THEN 1 ELSE 0 END) as resolved
            FROM reports");
        $reportStats = $this->db->single();
        if ($reportStats) {
            $stats['total_reports'] = (int)($reportStats->total ?? 0);
            $stats['processing']    = (int)($reportStats->processing ?? 0);
            $stats['resolved']      = (int)($reportStats->resolved ?? 0);
        }

        $this->db->query("SELECT COUNT(*) as total FROM users WHERE account_status = 'active'");
        $userStats = $this->db->single();
        if ($userStats) {
            $stats['total_users'] = (int)($userStats->total ?? 0);
        }

        return $stats;
    }

    public function getLatest($limit = 6, $priority = null) {
        $sql = "SELECT r.*, c.name as category_name,
                (SELECT COUNT(*) FROM upvotes u WHERE u.report_id = r.id) as upvote_count,
                (SELECT COUNT(*) FROM comments cm WHERE cm.report_id = r.id) as comment_count,
                (SELECT file_path FROM report_images img WHERE img.report_id = r.id ORDER BY img.id ASC LIMIT 1) as thumbnail
                FROM reports r
                LEFT JOIN categories c ON r.category_id = c.id
                WHERE r.is_public = 1";
        
        if ($priority && in_array($priority, ['critical', 'high', 'medium', 'low'])) {
            $sql .= " AND r.priority = :priority";
        }
        $sql .= " ORDER BY r.created_at DESC LIMIT :limit";

        $this->db->query($sql);
        if ($priority && in_array($priority, ['critical', 'high', 'medium', 'low'])) {
            $this->db->bind(':priority', $priority);
        }
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getAll($filters = []) {
        $sql = "SELECT r.*, c.name as category_name,
                (SELECT COUNT(*) FROM upvotes u WHERE u.report_id = r.id) as upvote_count,
                (SELECT COUNT(*) FROM comments cm WHERE cm.report_id = r.id) as comment_count
                FROM reports r
                LEFT JOIN categories c ON r.category_id = c.id
                WHERE 1=1";
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND r.category_id = :category_id";
        }
        if (!empty($filters['priority'])) {
            $sql .= " AND r.priority = :priority";
        }
        if (!empty($filters['status'])) {
            $sql .= " AND r.status = :status";
        }
        if (!empty($filters['keyword'])) {
            $sql .= " AND (r.title LIKE :kw OR r.description LIKE :kw OR r.address LIKE :kw)";
        }
        $sql .= " ORDER BY r.created_at DESC";

        $this->db->query($sql);
        if (!empty($filters['category_id'])) {
            $this->db->bind(':category_id', (int)$filters['category_id']);
        }
        if (!empty($filters['priority'])) {
            $this->db->bind(':priority', $filters['priority']);
        }
        if (!empty($filters['status'])) {
            $this->db->bind(':status', $filters['status']);
        }
        if (!empty($filters['keyword'])) {
            $this->db->bind(':kw', '%' . $filters['keyword'] . '%');
        }
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query("SELECT r.*, c.name as category_name, u.full_name as author_name,
                (SELECT COUNT(*) FROM upvotes uv WHERE uv.report_id = r.id) as upvote_count
                FROM reports r
                LEFT JOIN categories c ON r.category_id = c.id
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $report = $this->db->single();
        if (!$report) return null;

        // Lấy bình luận
        $this->db->query("SELECT cm.*, u.full_name, u.role
                          FROM comments cm
                          LEFT JOIN users u ON cm.user_id = u.id
                          WHERE cm.report_id = :id ORDER BY cm.created_at ASC");
        $this->db->bind(':id', $id);
        $report->comments = $this->db->resultSet();

        // Lấy lịch sử trạng thái
        $this->db->query("SELECT h.*, u.full_name as changer_name
                          FROM report_status_history h
                          LEFT JOIN users u ON h.changed_by = u.id
                          WHERE h.report_id = :id ORDER BY h.changed_at ASC");
        $this->db->bind(':id', $id);
        $report->history = $this->db->resultSet();

        return $report;
    }

    public function create($data) {
        $reportCode = 'WI-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $this->db->query("INSERT INTO reports 
            (report_code, user_id, category_id, title, description, incident_time, address, latitude, longitude, priority, status, is_public, contact_name, contact_email, contact_phone)
            VALUES 
            (:report_code, :user_id, :category_id, :title, :description, :incident_time, :address, :lat, :lng, :priority, 'new', 1, :c_name, :c_email, :c_phone)");
        
        $this->db->bind(':report_code',   $reportCode);
        $this->db->bind(':user_id',       $data['user_id'] ?? null);
        $this->db->bind(':category_id',   $data['category_id']);
        $this->db->bind(':title',         $data['title']);
        $this->db->bind(':description',   $data['description']);
        $this->db->bind(':incident_time', $data['incident_time'] ?? date('Y-m-d H:i:s'));
        $this->db->bind(':address',       $data['address'] ?? '');
        $this->db->bind(':lat',           $data['latitude'] ?? null);
        $this->db->bind(':lng',           $data['longitude'] ?? null);
        $this->db->bind(':priority',      $data['priority'] ?? 'medium');
        $this->db->bind(':c_name',        $data['contact_name'] ?? '');
        $this->db->bind(':c_email',       $data['contact_email'] ?? '');
        $this->db->bind(':c_phone',       $data['contact_phone'] ?? '');

        if ($this->db->execute()) {
            return $this->db->single() ?: true;
        }
        return false;
    }

    public function addComment($reportId, $userId, $content, $isInternal = 0) {
        $this->db->query("INSERT INTO comments (report_id, user_id, content, is_internal)
                          VALUES (:report_id, :user_id, :content, :is_internal)");
        $this->db->bind(':report_id',   $reportId);
        $this->db->bind(':user_id',     $userId);
        $this->db->bind(':content',     $content);
        $this->db->bind(':is_internal', $isInternal);
        return $this->db->execute();
    }

    public function updateStatus($reportId, $newStatus, $changedBy, $note = '') {
        $report = $this->getById($reportId);
        if (!$report) return false;

        $this->db->query("UPDATE reports SET status = :status WHERE id = :id");
        $this->db->bind(':status', $newStatus);
        $this->db->bind(':id',     $reportId);
        $ok = $this->db->execute();

        if ($ok) {
            $this->db->query("INSERT INTO report_status_history (report_id, old_status, new_status, changed_by, note)
                              VALUES (:report_id, :old_status, :new_status, :changed_by, :note)");
            $this->db->bind(':report_id',   $reportId);
            $this->db->bind(':old_status',  $report->status);
            $this->db->bind(':new_status',  $newStatus);
            $this->db->bind(':changed_by',  $changedBy);
            $this->db->bind(':note',        $note);
            $this->db->execute();
        }
        return $ok;
    }

    public function toggleUpvote($reportId, $userId) {
        $this->db->query("SELECT id FROM upvotes WHERE report_id = :rid AND user_id = :uid LIMIT 1");
        $this->db->bind(':rid', $reportId);
        $this->db->bind(':uid', $userId);
        $existing = $this->db->single();

        if ($existing) {
            $this->db->query("DELETE FROM upvotes WHERE id = :id");
            $this->db->bind(':id', $existing->id);
            $this->db->execute();
            return ['action' => 'unvoted'];
        } else {
            $this->db->query("INSERT INTO upvotes (report_id, user_id) VALUES (:rid, :uid)");
            $this->db->bind(':rid', $reportId);
            $this->db->bind(':uid', $userId);
            $this->db->execute();
            return ['action' => 'upvoted'];
        }
    }

    public function getByUserId($userId) {
        $this->db->query("SELECT r.*, c.name as category_name,
                          (SELECT COUNT(*) FROM upvotes u WHERE u.report_id = r.id) as upvote_count,
                          (SELECT COUNT(*) FROM comments cm WHERE cm.report_id = r.id) as comment_count
                          FROM reports r
                          LEFT JOIN categories c ON r.category_id = c.id
                          WHERE r.user_id = :user_id
                          ORDER BY r.created_at DESC");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    public function getUserStats($userId) {
        $this->db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status IN ('new', 'received', 'verifying', 'assigned', 'in_progress') THEN 1 ELSE 0 END) as processing,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM reports WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        $res = $this->db->single();
        return [
            'total'      => (int)($res->total ?? 0),
            'processing' => (int)($res->processing ?? 0),
            'resolved'   => (int)($res->resolved ?? 0),
            'rejected'   => (int)($res->rejected ?? 0),
        ];
    }
}
