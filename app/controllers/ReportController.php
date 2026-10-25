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
            echo json_encode([
                'success' => false,
                'message' => 'Chỉ hỗ trợ phương thức GET'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $priority = $_GET['severity'] ?? $_GET['priority'] ?? '';
        $status = $_GET['status'] ?? '';
        $categoryInput = trim((string)($_GET['category_id'] ?? ''));
        $categoryIds = [];

        if ($categoryInput !== '') {
            foreach (explode(',', $categoryInput) as $id) {
                $id = trim($id);

                if ($id === '' || !ctype_digit($id) || (int)$id < 1) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'category_id không hợp lệ'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                $categoryIds[] = (int)$id;
            }

            $categoryIds = array_values(array_unique($categoryIds));
        }
        $keyword = trim((string)($_GET['keyword'] ?? $_GET['q'] ?? ''));
        $fromDate = trim((string)($_GET['from_date'] ?? ''));
        $toDate = trim((string)($_GET['to_date'] ?? ''));

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(1, min(100, (int)($_GET['limit'] ?? 10)));

        $validDate = static function ($date) {
            $d = DateTime::createFromFormat('!Y-m-d', $date);
            return $d && $d->format('Y-m-d') === $date;
        };

        if (($fromDate !== '' && !$validDate($fromDate))
            || ($toDate !== '' && !$validDate($toDate))
            || ($fromDate !== '' && $toDate !== '' && $fromDate > $toDate)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Ngày không hợp lệ. Dùng định dạng YYYY-MM-DD.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($priority !== '' && !in_array($priority, ReportModel::PRIORITIES, true)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'severity không hợp lệ'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($status !== '' && !in_array($status, ReportModel::STATUSES, true)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'status không hợp lệ'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $filters = [
            'public_only' => true,
            'category_ids' => $categoryIds ?: null,
            'priority' => $priority !== '' ? $priority : null,
            'status' => $status !== '' ? $status : null,
            'keyword' => $keyword,
            'from_date' => $fromDate !== '' ? $fromDate : null,
            'to_date' => $toDate !== '' ? $toDate : null,
            'page' => $page,
            'limit' => $limit
        ];

        require_once APPROOT . '/app/models/NotificationModel.php';

        $reports = $this->reportModel->getAll($filters);
        $data = [];

        foreach ($reports as $r) {
            $data[] = [
                'id' => (int)$r->id,
                'report_code' => $r->report_code,
                'title' => $r->title,
                'description' => $r->description,
                'category_id' => (int)$r->category_id,
                'category_name' => $r->category_name,
                'priority' => $r->priority,
                'severity' => $r->priority,
                'status' => $r->status,
                'status_label' => NotificationModel::statusLabel($r->status),
                'address' => $r->address,
                'latitude' => $r->latitude !== null ? (float)$r->latitude : null,
                'longitude' => $r->longitude !== null ? (float)$r->longitude : null,
                'upvote_count' => (int)$r->upvote_count,
                'comment_count' => (int)$r->comment_count,
                'thumbnail_url' => $r->thumbnail ? url($r->thumbnail) : null,
                'detail_url' => url('report/detail/' . $r->id),
                'incident_time' => $r->incident_time,
                'created_at' => $r->created_at
            ];
        }

        echo json_encode([
            'success' => true,
            'count' => count($data),
            'page' => $page,
            'limit' => $limit,
            'filters' => [
                'category_id' => $categoryIds ?: null,
                'severity' => $priority ?: null,
                'status' => $status ?: null,
                'keyword' => $keyword ?: null,
                'from_date' => $fromDate ?: null,
                'to_date' => $toDate ?: null
            ],
            'data' => $data
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
    
    public function api_comments($id = null) {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Chỉ hỗ trợ phương thức GET'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $id = (int)$id;

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID báo cáo không hợp lệ'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $report = $this->reportModel->getById($id);

        if (!$report) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy báo cáo'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $comments = [];

        foreach (($report->comments ?? []) as $comment) {
            // Không trả bình luận nội bộ ra API công khai.
            if (!empty($comment->is_internal)) {
                continue;
            }

            $comments[] = [
                'id' => (int)$comment->id,
                'report_id' => (int)$comment->report_id,
                'user_id' => $comment->user_id !== null
                    ? (int)$comment->user_id : null,
                'content' => $comment->content,
                'created_at' => $comment->created_at,
                'user_name' => $comment->full_name ?? 'Người dùng'
            ];
        }

        echo json_encode([
            'success' => true,
            'report_id' => $id,
            'count' => count($comments),
            'data' => $comments
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    
    public function api_add_comment() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Chỉ hỗ trợ phương thức POST'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Hỗ trợ cả form-data và JSON.
        $input = $_POST;

        $rawInput = file_get_contents('php://input');
        $jsonInput = json_decode($rawInput, true);

        if (is_array($jsonInput)) {
            $input = array_merge($input, $jsonInput);
        }

        $reportId = (int)($input['report_id'] ?? 0);
        $content = trim((string)($input['content'] ?? ''));

        if ($reportId <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID báo cáo không hợp lệ'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($content === '') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Nội dung bình luận không được để trống'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (mb_strlen($content, 'UTF-8') > 2000) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Bình luận không được vượt quá 2000 ký tự'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $report = $this->reportModel->getById($reportId);

        if (!$report) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy báo cáo'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $userId = $_SESSION['user_id'] ?? null;

        $saved = $this->reportModel->addComment(
            $reportId,
            $userId,
            $content,
            0
        );

        if (!$saved) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Không thể lưu bình luận'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Đã thêm bình luận thành công',
            'report_id' => $reportId,
            'content' => $content
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
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
    
    public function api_upvote() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Chỉ hỗ trợ phương thức POST'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        
        if (!isLoggedIn()) {
            

            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để bình chọn'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }


        $input = $_POST;
        $jsonInput = json_decode(file_get_contents('php://input'), true);

        if (is_array($jsonInput)) {
            $input = array_merge($input, $jsonInput);
        }

        $reportId = (int)($input['report_id'] ?? 0);

        if ($reportId <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Mã báo cáo không hợp lệ'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $report = $this->reportModel->getById($reportId);

        if (!$report) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy báo cáo'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $result = $this->reportModel->toggleUpvote(
            $reportId,
            $_SESSION['user_id']
        );

        $updatedReport = $this->reportModel->getById($reportId);
        $newUpvotes = (int)($updatedReport->upvote_count ?? 0);

        echo json_encode([
            'success' => true,
            'action' => $result['action'],
            'new_upvotes' => $newUpvotes
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    public function api_xml_feed() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            header('Content-Type: application/xml; charset=utf-8');
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<error>Method Not Allowed</error>';
            exit;
        }

        header('Content-Type: application/xml; charset=utf-8');

        // Lấy danh sách báo cáo
        $reports = $this->reportModel->getAll([
            'public_only' => true
        ]);

        // Tạo XML
        $xml = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?><alerts></alerts>'
        );

        foreach ($reports as $report) {
            $priority = strtolower($report->priority ?? '');

            // Chỉ lấy cảnh báo mức độ cao hoặc khẩn cấp
            if (!in_array($priority, ['high', 'critical'], true)) {
                continue;
            }

            $item = $xml->addChild('alert');

            $item->addChild('id', (string) $report->id);
            $item->addChild(
                'title',
                htmlspecialchars(
                    (string) ($report->title ?? ''),
                    ENT_XML1,
                    'UTF-8'
                )
            );
            $item->addChild(
                'priority',
                htmlspecialchars($priority, ENT_XML1, 'UTF-8')
            );
            $item->addChild(
                'status',
                htmlspecialchars(
                    (string) ($report->status ?? ''),
                    ENT_XML1,
                    'UTF-8'
                )
            );
            $item->addChild(
                'created_at',
                htmlspecialchars(
                    (string) ($report->created_at ?? ''),
                    ENT_XML1,
                    'UTF-8'
                )
            );
        }

        echo $xml->asXML();
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
