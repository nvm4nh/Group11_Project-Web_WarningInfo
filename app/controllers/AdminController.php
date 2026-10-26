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
        
        echo json_encode([
            'status' => 'success',
            'data' => [
                'categories' => $this->reportModel->getStatsByCategory(),
                'statuses'   => $this->reportModel->getStatsByStatus(),
                'months'     => $this->reportModel->getStatsByMonth()
            ]
        ]);
        exit;
    }

    public function export_csv() {
        $reports = $this->reportModel->getAll();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="bao_cao_su_co_' . date('Ymd_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Thêm BOM UTF-8 để Excel không lỗi font tiếng Việt
        fputs($output, "\xEF\xBB\xBF");
        
        fputcsv($output, ['Mã BC', 'Tiêu đề', 'Danh mục', 'Mức độ', 'Trạng thái', 'Địa chỉ', 'Ngày gửi']);
        
        foreach ($reports as $r) {
            fputcsv($output, [
                $r->report_code,
                $r->title,
                $r->category_name ?? 'N/A',
                $r->priority,
                $r->status,
                $r->address,
                date('d/m/Y H:i', strtotime($r->created_at))
            ]);
        }
        
        fclose($output);
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
            'title'       => 'Quản lý sự cố môi trường',
            'reports'     => $reports,
            'images'      => $this->reportModel->getImagesForReports($ids),
            'assignments' => $this->reportModel->getAssignmentsForReports($ids),
            'officers'    => $this->userModel->getOfficers(), // SỬA Ở ĐÂY: Gọi hàm getOfficers() thay vì getByRole()
            'statuses'    => $statuses
        ]);
    }

    public function assignOfficer($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) redirect('admin/reports');
        
        $officerId = (int)($_POST['officer_id'] ?? 0);
        $deadline  = $_POST['deadline'] ?? null;

        // Validate deadline: Không được để trống và không được ở quá khứ
        if (empty($deadline) || strtotime($deadline) < strtotime(date('Y-m-d'))) {
            setFlash('danger', 'Hạn chót không hợp lệ hoặc đã ở trong quá khứ. Vui lòng chọn lại.');
            redirect('admin/reports');
        }

        // Hủy phân công cũ
        $this->reportModel->cancelAssignment($id);
        
        // Thêm phân công mới
        $this->reportModel->assignOfficer($id, $officerId, $deadline);
        
        // Cập nhật trạng thái báo cáo
        $this->reportModel->updateStatus($id, 'assigned', $_SESSION['user_id'] ?? 1, 'Đã phân công cán bộ xử lý');
        
        setFlash('success', 'Đã phân công cán bộ thành công.');
        redirect('admin/reports');
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
                $this->notificationModel->create($report->user_id, $title, $msg, (int)$id, 'report_status');
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