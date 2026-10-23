<?php
/**
 * Bàn làm việc của cán bộ xử lý sự cố (TV4 – Milestone 3)
 *
 *   GET officer                 – danh sách sự cố được phân công (?tab=active|done|all)
 *   GET officer/report/{id}     – chi tiết nhiệm vụ: nội dung sự cố, ảnh, liên hệ người gửi, lịch sử
 *
 * Cán bộ chỉ xem được sự cố được giao cho chính mình (kiểm tra qua bảng assignments).
 */
class OfficerController extends Controller {
    private $assignmentModel;
    private $reportModel;
    private $notificationModel;

    const TABS = ['active', 'done', 'all'];

    public function __construct() {
        requireOfficer();
        $this->assignmentModel   = $this->model('AssignmentModel');
        $this->reportModel       = $this->model('ReportModel');
        $this->notificationModel = $this->model('NotificationModel');
    }

    public function index() {
        $officerId = $_SESSION['user_id'];
        $tab = in_array($_GET['tab'] ?? '', self::TABS, true) ? $_GET['tab'] : 'active';

        $this->view('officer/index', [
            'title'       => 'Bàn làm việc cán bộ',
            'active'      => 'officer',
            'tab'         => $tab,
            'assignments' => $this->assignmentModel->getForOfficer($officerId, $tab),
            'counts'      => $this->assignmentModel->countsForOfficer($officerId),
        ]);
    }

    public function report($id = null) {
        [$report, $assignment] = $this->loadOwnReport($id);

        $this->view('officer/report', [
            'title'      => 'Xử lý ' . $report->report_code,
            'active'     => 'officer',
            'report'     => $report,
            'assignment' => $assignment,
        ]);
    }

    /* ---------- Hàm phụ ---------- */

    /** Lấy sự cố + phân công của cán bộ đang đăng nhập; không được giao thì quay về bàn làm việc */
    protected function loadOwnReport($id) {
        $assignment = $id ? $this->assignmentModel->findForOfficer($id, $_SESSION['user_id']) : null;
        $report     = $assignment ? $this->reportModel->getById($id) : null;
        if (!$assignment || !$report) {
            setFlash('warning', 'Sự cố này không được phân công cho bạn hoặc đã được chuyển cho cán bộ khác.');
            redirect('officer');
        }
        return [$report, $assignment];
    }
}
