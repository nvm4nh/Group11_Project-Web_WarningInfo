<?php
/**
 * Bàn làm việc của cán bộ xử lý sự cố (TV4 – Milestone 3)
 *
 *   GET officer                 – danh sách sự cố được phân công (?tab=active|done|all)
 *   GET officer/report/{id}     – chi tiết nhiệm vụ: nội dung sự cố, ảnh, liên hệ người gửi, lịch sử
 *   POST officer/accept/{id}    – nhận việc (sự cố chuyển sang "Đang xử lý")
 *   POST officer/update/{id}    – cập nhật tiến độ: đang xử lý / cần bổ sung thông tin / đã khắc phục
 *   POST officer/note/{id}      – ghi chú nội bộ (người dân không thấy)
 *
 * Mỗi lần đổi trạng thái: ghi report_status_history + gửi thông báo cho người gửi báo cáo;
 * khi nhận việc / hoàn thành thì báo cho quản trị viên đã giao việc.
 *
 * Cán bộ chỉ xem được sự cố được giao cho chính mình (kiểm tra qua bảng assignments).
 */
class OfficerController extends Controller {
    private $assignmentModel;
    private $reportModel;
    private $notificationModel;

    const TABS = ['active', 'done', 'all'];
    // Trạng thái cán bộ được phép chuyển sự cố sang (các trạng thái khác do quản trị viên quyết định)
    const OFFICER_STATUSES = ['in_progress', 'need_more_info', 'resolved'];

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

    /** Nhận việc: phân công → đang xử lý, sự cố → "Đang xử lý" */
    public function accept($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('officer');
        [$report, $a] = $this->loadOwnReport($id);

        if ($a->assignment_status !== 'assigned') {
            setFlash('info', 'Bạn đã nhận việc này rồi.');
            redirect('officer/report/' . (int)$report->id);
        }

        $name = $_SESSION['full_name'] ?? 'Cán bộ';
        $this->assignmentModel->setStatus($a->id, 'in_progress');
        $this->changeStatus($report, 'in_progress', $name . ' đã nhận việc và bắt đầu xử lý.');
        $this->notifyAssigner($a, $report, 'Cán bộ đã nhận việc: ' . $report->report_code,
            "{$name} đã nhận xử lý sự cố \"{$report->title}\".");

        setFlash('success', 'Đã nhận việc. Sự cố chuyển sang trạng thái "Đang xử lý".');
        redirect('officer/report/' . (int)$report->id);
    }

    /** Cập nhật tiến độ xử lý */
    public function update($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('officer');
        [$report, $a] = $this->loadOwnReport($id);
        $back = 'officer/report/' . (int)$report->id;

        if (!in_array($a->assignment_status, AssignmentModel::ACTIVE, true)) {
            setFlash('warning', 'Nhiệm vụ này đã kết thúc, không cập nhật được nữa.');
            redirect($back);
        }

        $status = $_POST['status'] ?? '';
        $note   = mb_substr(trim($_POST['note'] ?? ''), 0, 1000);
        if (!in_array($status, self::OFFICER_STATUSES, true)) {
            setFlash('danger', 'Trạng thái không hợp lệ.');
            redirect($back);
        }
        if ($note === '' && $status !== 'in_progress') {
            setFlash('danger', $status === 'resolved'
                ? 'Vui lòng ghi kết quả xử lý trước khi báo hoàn thành.'
                : 'Vui lòng ghi rõ thông tin người dân cần bổ sung.');
            redirect($back);
        }

        $this->changeStatus($report, $status, $note !== '' ? $note : 'Cán bộ cập nhật tiến độ xử lý.');
        $this->assignmentModel->setStatus($a->id, $status === 'resolved' ? 'completed' : 'in_progress');

        if ($status === 'resolved') {
            $this->notifyAssigner($a, $report, 'Cán bộ đã xử lý xong: ' . $report->report_code,
                ($_SESSION['full_name'] ?? 'Cán bộ') . " báo đã khắc phục sự cố \"{$report->title}\". Kết quả: {$note}");
            setFlash('success', 'Đã báo hoàn thành. Người gửi và quản trị viên đã nhận được thông báo.');
        } else {
            setFlash('success', 'Đã cập nhật tiến độ: ' . NotificationModel::statusLabel($status) . '.');
        }
        redirect($back);
    }

    /** Ghi chú nội bộ – chỉ cán bộ / quản trị viên thấy */
    public function note($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('officer');
        [$report] = $this->loadOwnReport($id);

        $content = mb_substr(trim($_POST['content'] ?? ''), 0, 2000);
        if ($content === '') {
            setFlash('danger', 'Vui lòng nhập nội dung ghi chú.');
        } else {
            $this->reportModel->addComment($report->id, $_SESSION['user_id'], $content, 1);
            setFlash('success', 'Đã lưu ghi chú nội bộ.');
        }
        redirect('officer/report/' . (int)$report->id);
    }

    /* ---------- Hàm phụ ---------- */

    /** Đổi trạng thái sự cố (ghi lịch sử) + báo cho người gửi nếu họ có tài khoản */
    protected function changeStatus($report, $status, $note) {
        $this->reportModel->updateStatus($report->id, $status, $_SESSION['user_id'], $note);
        if (!empty($report->user_id)) {
            $msg = "Báo cáo \"{$report->title}\" đã chuyển sang trạng thái: " . NotificationModel::statusLabel($status) . '.';
            if ($note !== '') $msg .= ' Ghi chú của cán bộ: ' . $note;
            $this->notificationModel->create($report->user_id, 'Cập nhật trạng thái: ' . $report->report_code,
                $msg, (int)$report->id, 'report_status');
        }
    }

    /** Báo cho quản trị viên đã giao việc (nếu khác người đang thao tác) */
    protected function notifyAssigner($a, $report, $title, $message) {
        if (!empty($a->assigned_by) && (int)$a->assigned_by !== (int)$_SESSION['user_id']) {
            $this->notificationModel->create($a->assigned_by, $title, $message, (int)$report->id, 'officer_update');
        }
    }

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
