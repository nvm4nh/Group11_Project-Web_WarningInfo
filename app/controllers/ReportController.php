<?php
class ReportController extends Controller {
    private $reportModel;
    private $categoryModel;

    public function __construct() {
        $this->reportModel   = $this->model('ReportModel');
        $this->categoryModel = $this->model('CategoryModel');
    }

    public function index() {
        $category_id = $_GET['category_id'] ?? ($_GET['category'][0] ?? null);
        $priority    = $_GET['priority'] ?? ($_GET['severity'][0] ?? null);
        $status      = $_GET['status'] ?? null;
        $keyword     = trim($_GET['q'] ?? '');

        $filters = [
            'category_id' => $category_id,
            'priority'    => $priority,
            'status'      => $status,
            'keyword'     => $keyword
        ];

        $reports    = $this->reportModel->getAll($filters);
        $categories = $this->categoryModel->getAll();

        $this->view('reports/index', [
            'title'       => 'Danh sách sự cố & cảnh báo',
            'active'      => 'report',
            'reports'     => $reports,
            'categories'  => $categories,
            'filters'     => $filters
        ]);
    }

    public function detail($id = null) {
        if (!$id) redirect('report');

        $report = $this->reportModel->getById($id);
        if (!$report) {
            setFlash('danger', 'Không tìm thấy sự cố này.');
            redirect('report');
        }

        $this->view('reports/detail', [
            'title'  => $report->title,
            'active' => 'report',
            'report' => $report
        ]);
    }

    public function create() {
        $categories = $this->categoryModel->getAll();
        $this->view('reports/create', [
            'title'      => 'Gửi báo cáo sự cố môi trường',
            'active'     => 'report',
            'categories' => $categories,
            'errors'     => []
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('report/create');

        $errors = [];
        $title       = trim($_POST['title'] ?? '');
        $categoryId  = (int)($_POST['category_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $address     = trim($_POST['address'] ?? '');
        $latitude    = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
        $longitude   = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
        $priority    = $_POST['priority'] ?? 'medium';
        $contactName = trim($_POST['contact_name'] ?? '');
        $contactPhone= trim($_POST['contact_phone'] ?? '');
        $contactEmail= trim($_POST['contact_email'] ?? '');

        if ($title === '')       $errors['title']       = 'Vui lòng nhập tiêu đề sự cố';
        if ($categoryId <= 0)    $errors['category_id'] = 'Vui lòng chọn danh mục phù hợp';
        if ($description === '') $errors['description'] = 'Vui lòng mô tả chi tiết sự cố';

        if (empty($errors)) {
            $data = [
                'user_id'       => $_SESSION['user_id'] ?? null,
                'category_id'   => $categoryId,
                'title'         => $title,
                'description'   => $description,
                'incident_time' => date('Y-m-d H:i:s'),
                'address'       => $address,
                'latitude'      => $latitude,
                'longitude'     => $longitude,
                'priority'      => $priority,
                'contact_name'  => $contactName ?: ($_SESSION['full_name'] ?? ''),
                'contact_email' => $contactEmail ?: ($_SESSION['email'] ?? ''),
                'contact_phone' => $contactPhone
            ];

            $ok = $this->reportModel->create($data);
            if ($ok) {
                setFlash('success', 'Báo cáo của bạn đã được gửi thành công và đang chờ xác minh!');
                redirect('report');
            } else {
                $errors['general'] = 'Có lỗi xảy ra khi lưu báo cáo, vui lòng thử lại.';
            }
        }

        $categories = $this->categoryModel->getAll();
        $this->view('reports/create', [
            'title'      => 'Gửi báo cáo sự cố môi trường',
            'active'     => 'report',
            'categories' => $categories,
            'errors'     => $errors,
            'old'        => $_POST
        ]);
    }

    public function comment($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) redirect('report');

        $content = trim($_POST['content'] ?? '');
        if ($content !== '') {
            $userId = $_SESSION['user_id'] ?? null;
            $this->reportModel->addComment($id, $userId, $content, 0);
            setFlash('success', 'Đã gửi phản hồi thành công.');
        }
        redirect('report/detail/' . $id);
    }

    public function upvote($id = null) {
        header('Content-Type: application/json');
        if (!isLoggedIn()) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập để ủng hộ báo cáo này.']);
            exit;
        }

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Mã báo cáo không hợp lệ.']);
            exit;
        }

        $res = $this->reportModel->toggleUpvote($id, $_SESSION['user_id']);
        $report = $this->reportModel->getById($id);
        echo json_encode([
            'status' => 'success',
            'action' => $res['action'],
            'count'  => $report ? $report->upvote_count : 0
        ]);
        exit;
    }

    public function my_reports() {
        requireLogin();
        $userId = $_SESSION['user_id'];
        $reports = $this->reportModel->getByUserId($userId);
        $stats   = $this->reportModel->getUserStats($userId);

        $this->view('reports/my_reports', [
            'title'   => 'Báo cáo của tôi',
            'active'  => 'report',
            'reports' => $reports,
            'stats'   => $stats,
        ]);
    }

    public function myReports() {
        $this->my_reports();
    }
}
