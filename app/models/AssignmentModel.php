<?php
/**
 * Phân công xử lý sự cố – phía cán bộ (TV4 – Milestone 3)
 * Bảng assignments: report_id, officer_id, assigned_by, assignment_note,
 *                   assignment_status (assigned | accepted | in_progress | completed | cancelled), assigned_at, completed_at
 * (Admin tạo phân công qua ReportModel::assignOfficer(); model này dùng cho bàn làm việc của cán bộ.)
 */
class AssignmentModel {
    private $db;

    const ACTIVE = ['assigned', 'accepted', 'in_progress'];

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Danh sách việc được giao cho 1 cán bộ.
     * $tab: 'active' (đang thụ lý) | 'done' (đã hoàn thành) | 'all'
     * Sắp xếp: mức độ nghiêm trọng trước, rồi việc giao gần nhất.
     */
    public function getForOfficer($officerId, $tab = 'active') {
        $where = "a.assignment_status <> 'cancelled'";
        if ($tab === 'active') $where = "a.assignment_status IN ('assigned', 'accepted', 'in_progress')";
        if ($tab === 'done')   $where = "a.assignment_status = 'completed'";

        $this->db->query("SELECT a.*, r.report_code, r.title, r.status AS report_status, r.priority, r.address,
                                 c.name AS category_name, u.full_name AS assigner_name,
                                 TIMESTAMPDIFF(SECOND, a.assigned_at, NOW()) AS age_seconds,
                                 (SELECT file_path FROM report_images img
                                   WHERE img.report_id = r.id ORDER BY img.id ASC LIMIT 1) AS thumbnail
                          FROM assignments a
                          JOIN reports r       ON r.id = a.report_id
                          LEFT JOIN categories c ON c.id = r.category_id
                          LEFT JOIN users u      ON u.id = a.assigned_by
                          WHERE a.officer_id = :oid AND $where
                          ORDER BY FIELD(r.priority, 'critical', 'high', 'medium', 'low'), a.assigned_at DESC, a.id DESC");
        $this->db->bind(':oid', (int)$officerId);
        return $this->db->resultSet();
    }

    /** Số liệu cho các ô thống kê trên bàn làm việc */
    public function countsForOfficer($officerId) {
        $this->db->query("SELECT
                SUM(a.assignment_status = 'assigned')                                   AS waiting,
                SUM(a.assignment_status IN ('accepted', 'in_progress'))                 AS doing,
                SUM(a.assignment_status = 'completed')                                  AS done,
                SUM(a.assignment_status IN ('assigned', 'accepted', 'in_progress')
                    AND r.priority IN ('high', 'critical'))                             AS urgent
            FROM assignments a
            JOIN reports r ON r.id = a.report_id
            WHERE a.officer_id = :oid");
        $this->db->bind(':oid', (int)$officerId);
        $row = $this->db->single();
        return [
            'waiting' => (int)($row->waiting ?? 0),
            'doing'   => (int)($row->doing ?? 0),
            'done'    => (int)($row->done ?? 0),
            'urgent'  => (int)($row->urgent ?? 0),
        ];
    }

    /**
     * Phân công (chưa bị huỷ) mới nhất của cán bộ cho 1 sự cố, kèm tên người giao.
     * Trả về null nếu sự cố không giao cho cán bộ này → cán bộ không xem/xử lý được.
     */
    public function findForOfficer($reportId, $officerId) {
        $this->db->query("SELECT a.*, u.full_name AS assigner_name
                          FROM assignments a
                          LEFT JOIN users u ON u.id = a.assigned_by
                          WHERE a.report_id = :rid AND a.officer_id = :oid AND a.assignment_status <> 'cancelled'
                          ORDER BY a.assigned_at DESC, a.id DESC
                          LIMIT 1");
        $this->db->bind(':rid', (int)$reportId);
        $this->db->bind(':oid', (int)$officerId);
        return $this->db->single() ?: null;
    }

    /** Đổi trạng thái phân công; khi hoàn thành thì ghi thời điểm completed_at */
    public function setStatus($assignmentId, $status) {
        $this->db->query("UPDATE assignments
                          SET assignment_status = :status,
                              completed_at = CASE WHEN :status2 = 'completed' THEN NOW() ELSE NULL END
                          WHERE id = :id");
        $this->db->bind(':status',  $status);
        $this->db->bind(':status2', $status);
        $this->db->bind(':id',      (int)$assignmentId);
        return $this->db->execute();
    }
}
