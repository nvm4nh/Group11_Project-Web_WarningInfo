<?php
class AdminController extends Controller {
    private $userModel;
    private $categoryModel;
    private $reportModel;
    private $notificationModel;

    public function __construct() {
        requireAdmin();
        $this->userModel         = $this->model('UserModel');
        $this->categoryModel     = $this->model('CategoryModel');
        $this->reportModel       = $this->model('ReportModel');
        $this->notificationModel = $this->model('NotificationModel');
    }

    public function index() {
        $stats = $this->reportModel->getStats();
        $this->view('admin/dashboard', [
            'title'           => 'Bảng điều khiển',
            'totalUsers'      => count($this->userModel->getAll()),
            'totalCategories' => count($this->categoryModel->getAll()),
            'totalReports'    => $stats['total_reports'],
            'processing'      => $stats['processing'],
            'resolved'        => $stats['resolved'],
        ]);
    }

    public function api_statistics() {
        header('Content-Type: application/json; charset=utf-8');
        $db = new Database();

        // 1. Thống kê theo danh mục
        $db->query("SELECT c.name, COUNT(r.id) as total FROM categories c LEFT JOIN reports r ON c.id = r.category_id GROUP BY c.id");
        $byCategory = $db->resultSet();

        // 2. Thống kê theo trạng thái
        $db->query("SELECT status, COUNT(*) as total FROM reports GROUP BY status");
        $byStatus = $db->resultSet();

        // 3. Thống kê xu hướng theo tháng
        $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total 
                    FROM reports 
                    GROUP BY month 
                    ORDER BY month ASC");
        $byMonth = $db->resultSet();

        echo json_encode([
            'status' => 'success',
            'data' => [
                'categories' => $byCategory,
                'statuses' => $byStatus,
                'months' => $byMonth
            ]
        ]);
        exit;
    }

    public function reports() {
        $reports = $this->reportModel->getAll();
        $ids = array_map(function ($r) { return $r->id; }, $reports);

        $statuses = [];
        foreach (ReportModel::STATUSES as $code) {
            $statuses[$code] = NotificationModel::statusLabel($code);
        }

        $this->view('admin/reports/index', [
            'title'   => 'Quản lý sự cố môi trường',
            'reports' => $this->reportModel->getAll(),
        ]);
    }

    public function updateReportStatus($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) redirect('admin/reports');

        $status = $_POST['status'] ?? '';
        $note   = trim($_POST['note'] ?? '');
        if (!in_array($status, ReportModel::STATUSES, true)) {
            setFlash('danger', 'Trạng thái không hợp lệ.');
            redirect('admin/reports');
        }

        $report = $this->reportModel->getById($id);
        if (!$report) {
            setFlash('danger', 'Không tìm thấy sự cố.');
            redirect('admin/reports');
        }

        $oldStatus = $report->status;
        $ok = $this->reportModel->updateStatus($id, $status, $_SESSION['user_id'] ?? 1, $note);

        if ($ok) {
            if (!empty($report->user_id) && $oldStatus !== $status) {
                $label = NotificationModel::statusLabel($status);
                $title = 'Cập nhật trạng thái: ' . $report->report_code;
                $msg   = "Báo cáo \"{$report->title}\" đã chuyển sang trạng thái: {$label}.";
                if ($note !== '') {
                    $msg .= " Ghi chú: {$note}";
                }
                $this->notificationModel->create(
                    $report->user_id,
                    $title,
                    $msg,
                    (int)$id,
                    'report_status'
                );
            }
            setFlash('success', 'Đã cập nhật trạng thái sự cố.');
        } else {
            setFlash('danger', 'Có lỗi xảy ra khi cập nhật.');
        }
        redirect('admin/reports');
    }

    public function users() {
        $this->view('admin/users/index', [
            'title' => 'Quản lý người dùng',
            'users' => $this->userModel->getAll(),
        ]);
    }

    public function updateUser($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) redirect('admin/users');
        $role = $_POST['role'] ?? 'user';
        if (!in_array($role, ['user','officer','admin'], true)) $role = 'user';
        $this->userModel->updateRole($id, $role);
        setFlash('success', 'Cập nhật người dùng thành công');
        redirect('admin/users');
    }

    public function deleteUser($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) redirect('admin/users');
        if ((int)$id === (int)($_SESSION['user_id'] ?? 0)) {
            setFlash('danger', 'Không thể xoá chính mình');
            redirect('admin/users');
        }
        try {
            $this->userModel->delete($id);
            setFlash('success', 'Đã xoá người dùng');
        } catch (PDOException $e) {
            setFlash('danger', 'Không thể xoá: người dùng còn dữ liệu liên quan');
        }
        redirect('admin/users');
    }

    public function categories() {
        $this->view('admin/categories/index', [
            'title'      => 'Quản lý danh mục',
            'categories' => $this->categoryModel->getAll(),
        ]);
    }

    public function createCategory() {
        $this->view('admin/categories/form', [
            'title'  => 'Thêm danh mục',
            'mode'   => 'create',
            'errors' => [],
            'item'   => null,
        ]);
    }

    public function storeCategory() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('admin/categories');
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $errors = [];
        if ($name === '')                                $errors['name'] = 'Vui lòng nhập tên danh mục';
        elseif ($this->categoryModel->nameExists($name)) $errors['name'] = 'Tên danh mục đã tồn tại';

        if (empty($errors)) {
            $this->categoryModel->create($name, $desc);
            setFlash('success', 'Thêm danh mục thành công');
            redirect('admin/categories');
        }
        $this->view('admin/categories/form', [
            'title'  => 'Thêm danh mục',
            'mode'   => 'create',
            'errors' => $errors,
            'item'   => (object)['name' => $name, 'description' => $desc],
        ]);
    }

    public function editCategory($id = null) {
        $item = $id ? $this->categoryModel->findById($id) : null;
        if (!$item) {
            setFlash('danger', 'Không tìm thấy danh mục');
            redirect('admin/categories');
        }
        $this->view('admin/categories/form', [
            'title'  => 'Sửa danh mục',
            'mode'   => 'edit',
            'errors' => [],
            'item'   => $item,
        ]);
    }

    public function updateCategory($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) redirect('admin/categories');
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $errors = [];
        if ($name === '')                                     $errors['name'] = 'Vui lòng nhập tên danh mục';
        elseif ($this->categoryModel->nameExists($name, $id)) $errors['name'] = 'Tên danh mục đã tồn tại';

        if (empty($errors)) {
            $this->categoryModel->update($id, $name, $desc);
            setFlash('success', 'Cập nhật danh mục thành công');
            redirect('admin/categories');
        }
        $this->view('admin/categories/form', [
            'title'  => 'Sửa danh mục',
            'mode'   => 'edit',
            'errors' => $errors,
            'item'   => (object)['id' => $id, 'name' => $name, 'description' => $desc],
        ]);
    }

    public function deleteCategory($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) redirect('admin/categories');
        try {
            $this->categoryModel->delete($id);
            setFlash('success', 'Đã xoá danh mục');
        } catch (PDOException $e) {
            setFlash('danger', 'Không thể xoá: danh mục đang được sử dụng');
        }
        redirect('admin/categories');
    }
}
