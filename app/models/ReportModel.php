<?php
class ReportModel {
    private $db;

    // Giá trị hợp lệ — khớp ENUM trong database/database_init.sql
    const STATUSES   = ['new', 'received', 'need_more_info', 'verifying', 'verified',
                        'assigned', 'in_progress', 'resolved', 'closed', 'rejected'];
    const PRIORITIES = ['low', 'medium', 'high', 'critical'];

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
                (SELECT COUNT(*) FROM comments cm WHERE cm.report_id = r.id AND cm.is_internal = 0) as comment_count,
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

        if (!empty($filters['public_only'])) {
            $sql .= " AND r.is_public = 1";
        }
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

        // Lấy ảnh minh chứng (gallery của TV4 dùng $r->images)
        $report->images = $this->getImages($id);

        return $report;
    }

    /** Danh sách ảnh của 1 báo cáo */
    public function getImages($reportId) {
        $this->db->query("SELECT id, file_path, mime_type, file_size_bytes, original_file_name
                          FROM report_images
                          WHERE report_id = :id AND is_public = 1
                          ORDER BY id ASC");
        $this->db->bind(':id', $reportId);
        return $this->db->resultSet();
    }

    /** Ảnh của nhiều báo cáo cùng lúc (1 truy vấn), trả về mảng [report_id => [ảnh...]] */
    public function getImagesForReports(array $reportIds) {
        $ids = array_values(array_filter(array_map('intval', $reportIds)));
        if (empty($ids)) return [];

        $placeholders = [];
        foreach ($ids as $i => $id) $placeholders[] = ':id' . $i;
        $this->db->query("SELECT id, report_id, file_path, mime_type, original_file_name
                          FROM report_images
                          WHERE report_id IN (" . implode(',', $placeholders) . ")
                          ORDER BY id ASC");
        foreach ($ids as $i => $id) $this->db->bind(':id' . $i, $id);

        $map = [];
        foreach ($this->db->resultSet() as $img) {
            $map[$img->report_id][] = $img;
        }
        return $map;
    }

    /** Lưu thông tin 1 ảnh minh chứng vào bảng report_images */
    public function addImage($reportId, $filePath, $mimeType, $fileSize, $originalName = null, $uploadedBy = null) {
        $this->db->query("INSERT INTO report_images (report_id, file_path, original_file_name, mime_type, file_size_bytes, uploaded_by)
                          VALUES (:report_id, :file_path, :original_name, :mime_type, :file_size, :uploaded_by)");
        $this->db->bind(':report_id',     (int)$reportId);
        $this->db->bind(':file_path',     $filePath);
        $this->db->bind(':original_name', $originalName !== null ? mb_substr($originalName, 0, 255) : null);
        $this->db->bind(':mime_type',     $mimeType);
        $this->db->bind(':file_size',     (int)$fileSize);
        $this->db->bind(':uploaded_by',   $uploadedBy !== null ? (int)$uploadedBy : null);
        return $this->db->execute();
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

        // Trả về ID báo cáo vừa tạo (để gắn ảnh), false nếu lỗi.
        // Lưu ý: KHÔNG gọi single() sau INSERT — single() chạy execute() lần 2 gây trùng report_code.
        if ($this->db->execute()) {
            return (int)$this->db->lastInsertId();
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

    /* =========================================================
       BẢN ĐỒ SỐ LEAFLET (TV1 – Milestone 3)
       ========================================================= */
    const MAP_STATES     = ['active', 'resolved', 'all'];
    const RESOLVED_STATUSES = ['resolved', 'closed'];
    const EARTH_RADIUS_KM   = 6371;

    /**
     * Sự cố công khai CÓ toạ độ GPS để ghim lên bản đồ (bỏ qua báo cáo bị từ chối).
     * $filters: category_ids (int[]), priority, state (active|resolved|all),
     *           lat, lng, radius_km (lọc "Sự cố gần tôi" theo công thức Haversine).
     * Khi có lọc bán kính, mỗi bản ghi kèm distance_km và kết quả sắp xếp gần → xa.
     */
    public function getMapPoints(array $filters = []) {
        $near = isset($filters['lat'], $filters['lng'], $filters['radius_km']);

        $distanceSql = $near
            ? self::EARTH_RADIUS_KM . " * 2 * ASIN(SQRT(
                    POWER(SIN(RADIANS(r.latitude - :lat) / 2), 2) +
                    COS(RADIANS(:lat2)) * COS(RADIANS(r.latitude)) *
                    POWER(SIN(RADIANS(r.longitude - :lng) / 2), 2)))"
            : "NULL";

        $sql = "SELECT r.id, r.report_code, r.title, r.address, r.latitude, r.longitude,
                       r.priority, r.status, r.category_id, r.incident_time, r.created_at,
                       c.name AS category_name,
                       (SELECT COUNT(*) FROM upvotes u WHERE u.report_id = r.id) AS upvote_count,
                       (SELECT file_path FROM report_images img
                         WHERE img.report_id = r.id AND img.is_public = 1
                         ORDER BY img.id ASC LIMIT 1) AS thumbnail,
                       $distanceSql AS distance_km
                FROM reports r
                LEFT JOIN categories c ON r.category_id = c.id
                WHERE r.is_public = 1
                  AND r.status <> 'rejected'
                  AND r.latitude IS NOT NULL AND r.longitude IS NOT NULL";

        $params = [];

        $categoryIds = array_values(array_filter(array_map('intval', $filters['category_ids'] ?? [])));
        if ($categoryIds) {
            $ph = [];
            foreach ($categoryIds as $i => $cid) {
                $ph[] = ':cat' . $i;
                $params[':cat' . $i] = $cid;
            }
            $sql .= " AND r.category_id IN (" . implode(',', $ph) . ")";
        }

        if (!empty($filters['priority']) && in_array($filters['priority'], self::PRIORITIES, true)) {
            $sql .= " AND r.priority = :priority";
            $params[':priority'] = $filters['priority'];
        }

        $state = $filters['state'] ?? 'all';
        if ($state === 'resolved') {
            $sql .= " AND r.status IN ('resolved', 'closed')";
        } elseif ($state === 'active') {
            $sql .= " AND r.status NOT IN ('resolved', 'closed')";
        }

        if ($near) {
            // Khung chữ nhật bao quanh vòng tròn: lọc thô để tận dụng chỉ mục idx_reports_location
            $lat = (float)$filters['lat'];
            $lng = (float)$filters['lng'];
            $dLat = rad2deg($filters['radius_km'] / self::EARTH_RADIUS_KM);
            $dLng = $dLat / max(cos(deg2rad($lat)), 0.01);
            $sql .= " AND r.latitude BETWEEN :minLat AND :maxLat
                      AND r.longitude BETWEEN :minLng AND :maxLng
                      HAVING distance_km <= :radius
                      ORDER BY distance_km ASC";
            $params += [
                ':lat' => $lat, ':lat2' => $lat, ':lng' => $lng,
                ':minLat' => $lat - $dLat, ':maxLat' => $lat + $dLat,
                ':minLng' => $lng - $dLng, ':maxLng' => $lng + $dLng,
                ':radius' => (float)$filters['radius_km'],
            ];
        } else {
            $sql .= " ORDER BY r.created_at DESC";
        }

        $sql .= " LIMIT :limit";

        $this->db->query($sql);
        foreach ($params as $k => $v) {
            $this->db->bind($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $this->db->bind(':limit', (int)($filters['limit'] ?? 500), PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Nhóm màu Marker trên bản đồ:
     *  resolved = Xanh (đã giải quyết), urgent = Đỏ (nghiêm trọng/cao), medium = Vàng (trung bình/thấp)
     */
    public static function mapLevel($priority, $status) {
        if (in_array($status, self::RESOLVED_STATUSES, true)) return 'resolved';
        return in_array($priority, ['critical', 'high'], true) ? 'urgent' : 'medium';
    }

    public function getByUserId($userId) {
        $this->db->query("SELECT r.*, c.name as category_name,
                          (SELECT COUNT(*) FROM upvotes u WHERE u.report_id = r.id) as upvote_count,
                          (SELECT COUNT(*) FROM comments cm WHERE cm.report_id = r.id AND cm.is_internal = 0) as comment_count
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
