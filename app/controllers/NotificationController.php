<?php
/**
 * Hộp thông báo cho người dân và cán bộ (TV4 – Milestone 3)
 *
 *   GET  notification              – danh sách thông báo (?filter=unread để lọc chưa đọc)
 *   GET  notification/open/{id}    – đánh dấu đã đọc rồi mở trang liên quan (chi tiết sự cố)
 *   GET  notification/latest       – JSON: số chưa đọc + 6 thông báo mới nhất (chuông trên thanh menu)
 *   POST notification/read/{id}    – đánh dấu 1 thông báo đã đọc
 *   POST notification/readAll      – đánh dấu tất cả đã đọc
 *
 * Thông báo được tạo ở AdminController (đổi trạng thái, phân công cán bộ) qua NotificationModel::create().
 */
class NotificationController extends Controller {
    private $notificationModel;

    public function __construct() {
        if (!isLoggedIn() && $this->wantsJson()) {
            $this->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập để xem thông báo.'], 401);
        }
        requireLogin();
        $this->notificationModel = $this->model('NotificationModel');
    }

    public function index() {
        $userId = $_SESSION['user_id'];
        $filter = ($_GET['filter'] ?? '') === 'unread' ? 'unread' : 'all';

        $this->view('notifications/index', [
            'title'         => 'Thông báo của tôi',
            'active'        => 'notification',
            'filter'        => $filter,
            'notifications' => $this->notificationModel->getByUser($userId, 100, $filter === 'unread'),
            'totalCount'    => $this->notificationModel->countAll($userId),
            'unreadCount'   => $this->notificationModel->countUnread($userId),
        ]);
    }

    /** Bấm vào 1 thông báo: đánh dấu đã đọc rồi chuyển tới sự cố liên quan */
    public function open($id = null) {
        $n = $id ? $this->notificationModel->findForUser($id, $_SESSION['user_id']) : null;
        if (!$n) {
            setFlash('warning', 'Không tìm thấy thông báo.');
            redirect('notification');
        }
        if (!$n->is_read) {
            $this->notificationModel->markAsRead($n->id, $_SESSION['user_id']);
        }
        redirect($this->targetPath($n));
    }

    /** GET notification/latest – JSON cho chuông thông báo trên thanh menu (gọi bằng AJAX) */
    public function latest() {
        $userId = $_SESSION['user_id'];
        $items = [];
        foreach ($this->notificationModel->getByUser($userId, 6) as $n) {
            $items[] = [
                'id'      => (int)$n->id,
                'title'   => $n->title,
                'message' => $n->message,
                'type'    => $n->notification_type,
                'is_read' => (bool)$n->is_read,
                'time'    => timeAgo($n->age_seconds, $n->created_at),
                'url'     => url('notification/open/' . (int)$n->id),
            ];
        }
        $this->json([
            'status' => 'success',
            'unread' => $this->notificationModel->countUnread($userId),
            'items'  => $items,
        ]);
    }

    public function read($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) redirect('notification');
        $this->notificationModel->markAsRead((int)$id, $_SESSION['user_id']);
        $this->done('Đã đánh dấu thông báo là đã đọc.');
    }

    public function readAll() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('notification');
        $this->notificationModel->markAllAsRead($_SESSION['user_id']);
        $this->done('Đã đánh dấu tất cả thông báo là đã đọc.');
    }

    /* ---------- Hàm phụ ---------- */

    /** Trang cần mở khi bấm vào thông báo: việc được giao → bàn làm việc cán bộ, còn lại → chi tiết sự cố */
    protected function targetPath($n) {
        if (!$n->report_id) return 'notification';
        if ($n->notification_type === 'assignment' && isOfficer()) {
            return 'officer/report/' . (int)$n->report_id;
        }
        return 'report/detail/' . (int)$n->report_id;
    }

    /** Trả JSON cho AJAX, còn form thường thì quay lại hộp thông báo */
    private function done($message) {
        if ($this->wantsJson()) {
            $this->json([
                'status'  => 'success',
                'message' => $message,
                'unread'  => $this->notificationModel->countUnread($_SESSION['user_id']),
            ]);
        }
        setFlash('success', $message);
        $back = ($_POST['filter'] ?? '') === 'unread' ? 'notification?filter=unread' : 'notification';
        redirect($back);
    }

    private function wantsJson() {
        return stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
            || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    private function json(array $payload, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
