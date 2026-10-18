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
            'public_only' => true,
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

        // Tổng dung lượng gửi lên vượt post_max_size => PHP bỏ trống cả $_POST lẫn $_FILES
        if (empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $this->view('reports/create', [
                'title'      => 'Gửi báo cáo sự cố môi trường',
                'active'     => 'report',
                'categories' => $this->categoryModel->getAll(),
                'errors'     => ['images' => 'Tổng dung lượng gửi lên quá lớn (máy chủ cho phép tối đa ' . ini_get('post_max_size') . '). Vui lòng chọn ảnh nhỏ hơn và nhập lại thông tin.'],
            ]);
            return;
        }

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
        if (!in_array($priority, ReportModel::PRIORITIES, true)) $priority = 'medium';

        // Kiểm tra ảnh minh chứng TRƯỚC khi lưu báo cáo (chưa di chuyển file nào)
        $uploads = $this->collectUploads($errors);

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

            $reportId = $this->reportModel->create($data);
            if ($reportId) {
                $failed = $this->saveUploads($reportId, $uploads);
                if ($failed > 0) {
                    setFlash('warning', 'Báo cáo đã được gửi, nhưng ' . $failed . ' ảnh không lưu được. Vui lòng liên hệ quản trị viên nếu cần bổ sung ảnh.');
                } else {
                    setFlash('success', 'Báo cáo của bạn đã được gửi thành công và đang chờ xác minh!');
                }
                redirect('report/detail/' . $reportId);
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

    /* =========================================================
       UPLOAD ẢNH MINH CHỨNG (TV3)
       - Tối đa 3 ảnh / lần gửi, mỗi ảnh ≤ 5MB, chỉ JPG / PNG / WEBP
       - MIME được xác định từ NỘI DUNG file (finfo), không tin $_FILES[..]['type'] do trình duyệt gửi
       - Tên file trên server sinh ngẫu nhiên, đuôi file lấy theo MIME thật => không thể upload .php
       ========================================================= */
    const UPLOAD_MAX_FILES = 3;
    const UPLOAD_MAX_BYTES = 5 * 1024 * 1024;
    const UPLOAD_DIR       = 'uploads/reports';           // tương đối so với thư mục public/
    const UPLOAD_MIME_EXT  = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Gom và kiểm tra $_FILES['images'].
     * Trả về danh sách file hợp lệ [tmp_name, mime, size, original_name]; lỗi ghi vào $errors['images'].
     */
    private function collectUploads(array &$errors) {
        if (empty($_FILES['images']) || !is_array($_FILES['images']['name'] ?? null)) {
            return [];
        }

        $f = $_FILES['images'];
        $files = [];
        foreach ($f['name'] as $i => $name) {
            $err = $f['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            if ($err === UPLOAD_ERR_NO_FILE) continue;          // ô chọn ảnh để trống
            $files[] = [
                'name'  => (string)$name,
                'tmp'   => (string)($f['tmp_name'][$i] ?? ''),
                'size'  => (int)($f['size'][$i] ?? 0),
                'error' => (int)$err,
            ];
        }

        if (empty($files)) return [];

        if (count($files) > self::UPLOAD_MAX_FILES) {
            $errors['images'] = 'Chỉ được đính kèm tối đa ' . self::UPLOAD_MAX_FILES . ' ảnh cho mỗi báo cáo.';
            return [];
        }

        // finfo đọc "magic bytes" của file; nếu XAMPP chưa bật extension fileinfo thì dùng getimagesize()
        $finfo = class_exists('finfo') ? new finfo(FILEINFO_MIME_TYPE) : null;
        $valid = [];
        foreach ($files as $file) {
            $label = '"' . basename($file['name']) . '"';

            if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
                $errors['images'] = "Ảnh $label vượt quá giới hạn dung lượng của máy chủ (" . ini_get('upload_max_filesize') . ').';
                return [];
            }
            if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp'])) {
                $errors['images'] = "Tải ảnh $label lên không thành công, vui lòng thử lại.";
                return [];
            }
            if ($file['size'] <= 0 || $file['size'] > self::UPLOAD_MAX_BYTES) {
                $errors['images'] = "Ảnh $label vượt quá 5MB.";
                return [];
            }

            $info = @getimagesize($file['tmp']);
            $mime = $finfo ? $finfo->file($file['tmp']) : ($info['mime'] ?? '');
            if ($info === false || !isset(self::UPLOAD_MIME_EXT[$mime])) {
                $errors['images'] = "Tệp $label không phải ảnh JPG, PNG hoặc WEBP hợp lệ.";
                return [];
            }

            $valid[] = [
                'tmp'           => $file['tmp'],
                'mime'          => $mime,
                'size'          => $file['size'],
                'original_name' => basename($file['name']),
            ];
        }
        return $valid;
    }

    /** Di chuyển ảnh đã kiểm tra vào public/uploads/reports/ và ghi bảng report_images. Trả về số ảnh lỗi. */
    private function saveUploads($reportId, array $uploads) {
        if (empty($uploads)) return 0;

        $dir = APPROOT . '/public/' . self::UPLOAD_DIR;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return count($uploads);
        }

        $failed = 0;
        foreach ($uploads as $up) {
            $fileName = md5(uniqid((string)rand(), true)) . '.' . self::UPLOAD_MIME_EXT[$up['mime']];
            if (!move_uploaded_file($up['tmp'], $dir . '/' . $fileName)) {
                $failed++;
                continue;
            }
            $this->reportModel->addImage(
                $reportId,
                self::UPLOAD_DIR . '/' . $fileName,   // ví dụ: uploads/reports/ab12...jpg
                $up['mime'],
                $up['size'],
                $up['original_name'],
                $_SESSION['user_id'] ?? null
            );
        }
        return $failed;
    }

    /* =========================================================
       WEBSERVICE JSON (TV3)
       GET /report/api_list?category_id=&priority=&status=&q=
       Chỉ trả báo cáo công khai; KHÔNG trả thông tin liên hệ (email/SĐT) của người gửi.
       ========================================================= */
    public function api_list() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Chỉ hỗ trợ phương thức GET'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $priority = $_GET['priority'] ?? '';
        $status   = $_GET['status'] ?? '';
        $filters = [
            'public_only' => true,
            'category_id' => (int)($_GET['category_id'] ?? 0) ?: null,
            'priority'    => in_array($priority, ReportModel::PRIORITIES, true) ? $priority : null,
            'status'      => in_array($status, ReportModel::STATUSES, true) ? $status : null,
            'keyword'     => trim((string)($_GET['q'] ?? '')),
        ];

        require_once APPROOT . '/app/models/NotificationModel.php';   // dùng statusLabel()
        $data = [];
        foreach ($this->reportModel->getAll($filters) as $r) {
            $data[] = [
                'id'            => (int)$r->id,
                'report_code'   => $r->report_code,
                'title'         => $r->title,
                'description'   => $r->description,
                'category_id'   => (int)$r->category_id,
                'category_name' => $r->category_name,
                'priority'      => $r->priority,
                'status'        => $r->status,
                'status_label'  => NotificationModel::statusLabel($r->status),
                'address'       => $r->address,
                'latitude'      => $r->latitude !== null ? (float)$r->latitude : null,
                'longitude'     => $r->longitude !== null ? (float)$r->longitude : null,
                'upvote_count'  => (int)$r->upvote_count,
                'comment_count' => (int)$r->comment_count,
                'thumbnail_url' => $r->thumbnail ? url($r->thumbnail) : null,
                'detail_url'    => url('report/detail/' . $r->id),
                'incident_time' => $r->incident_time,
                'created_at'    => $r->created_at,
            ];
        }

        echo json_encode([
            'status'  => 'success',
            'count'   => count($data),
            'filters' => array_filter($filters, function ($v, $k) { return $k !== 'public_only' && $v !== null && $v !== ''; }, ARRAY_FILTER_USE_BOTH),
            'data'    => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
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
