<?php
class ReportController extends Controller {
    private $reportModel;
    private $categoryModel;

    public function __construct() {
        $this->reportModel   = $this->model('ReportModel');
        $this->categoryModel = $this->model('CategoryModel');
    }

    public function index() {
        // Tham số lọc luôn đưa về 1 giá trị chuỗi bằng get_param() (link dạng ?status[]=... hay ?status[][]=...
        // trước đây gây lỗi Fatal / Warning)
        $category_id = (int)get_param('category_id', get_param('category')) ?: null;
        $priority    = get_param('priority', get_param('severity'));
        $status      = get_param('status');
        $keyword     = get_param('q');
        $priority    = in_array($priority, ReportModel::PRIORITIES, true) ? $priority : null;
        $status      = in_array($status, ReportModel::STATUSES, true) ? $status : null;

        $filters = [
            'public_only' => true,
            'category_id' => $category_id,
            'priority'    => $priority,
            'status'      => $status,
            'keyword'     => $keyword
        ];

        // Phân trang "Xem thêm" (TV5 – M3): ?page=N hiển thị N trang đầu, để trang vẫn dùng được khi tắt JS.
        // Khi bật JS, ajax-filter.js gọi report/api_list để nạp trang kế tiếp mà không tải lại trang.
        $page = max(1, (int)($_GET['page'] ?? 1));

        $reports    = $this->reportModel->getAll($filters);
        $categories = $this->categoryModel->getAll();
        $total      = count($reports);

        $this->view('reports/index', [
            'title'       => 'Danh sách sự cố & cảnh báo',
            'active'      => 'report',
            'reports'     => array_slice($reports, 0, $page * self::LIST_PAGE_SIZE),
            'total'       => $total,
            'page'        => $page,
            'page_size'   => self::LIST_PAGE_SIZE,
            'categories'  => $categories,
            'filters'     => $filters
        ]);
    }

    const LIST_PAGE_SIZE     = 9;  // thẻ sự cố mỗi lần nạp ở /report (lưới 3 cột)
    const COMMENT_PAGE_SIZE  = 5;  // bình luận mỗi lần nạp ở trang chi tiết

    public function detail($id = null) {
        if (!$id || !ctype_digit((string)$id)) show_404('Đường dẫn sự cố không hợp lệ.');

        $report = $this->reportModel->getById($id);
        // Báo cáo không công khai: chỉ người gửi và cán bộ / quản trị viên được xem (các API đã chặn tương tự)
        $canView = $report && ($report->is_public
            || isOfficer()
            || (isLoggedIn() && (int)$report->user_id === (int)$_SESSION['user_id']));
        if (!$canView) {
            // Trang 404 thay vì quay về danh sách (TV4 – Milestone 4): đúng mã HTTP và người dùng biết lý do
            show_404('Sự cố bạn tìm không tồn tại, đã bị gỡ hoặc chưa được công khai.');
        }

        // Bình luận công khai: trang đầu render sẵn, các trang sau nạp bằng AJAX (api_comments)
        // Ghi chú nội bộ (is_internal = 1) chỉ cán bộ / quản trị viên thấy, hiển thị ở khối riêng
        $internalNotes = isOfficer()
            ? array_values(array_filter($report->comments ?? [], function ($cm) { return (int)$cm->is_internal === 1; }))
            : [];

        $this->view('reports/detail', [
            'title'          => $report->title,
            'active'         => 'report',
            'report'         => $report,
            'comments'       => $this->reportModel->getPublicComments($report->id, self::COMMENT_PAGE_SIZE, 0),
            'comment_total'  => $this->reportModel->countPublicComments($report->id),
            'comment_limit'  => self::COMMENT_PAGE_SIZE,
            'internal_notes' => $internalNotes,
            'has_upvoted'    => isLoggedIn() && $this->reportModel->hasUpvoted($report->id, $_SESSION['user_id']),
            'use_map'        => $report->latitude !== null && $report->longitude !== null,
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
            if ($file['size'] <= 0) {   // TV4 – Milestone 4: file rỗng trước đây bị báo nhầm là "vượt quá 5MB"
                $errors['images'] = "Tệp $label rỗng (0 byte), vui lòng chọn lại ảnh.";
                return [];
            }
            if ($file['size'] > self::UPLOAD_MAX_BYTES) {
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
        $this->jsonError(405, 'Chỉ hỗ trợ phương thức GET');
    }

    $priority = get_param('priority');
    $status = get_param('status');
    $keyword = get_param('keyword', get_param('q'));
    $fromDate = get_param('from_date');
    $toDate = get_param('to_date');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 10)));

    foreach (['from_date' => $fromDate, 'to_date' => $toDate] as $name => $date) {
        if ($date !== '') {
            $parsed = DateTime::createFromFormat('!Y-m-d', $date);
            if (!$parsed || $parsed->format('Y-m-d') !== $date) {
                $this->jsonError(422, "Trường ngày phải có định dạng YYYY-MM-DD");
            }
        }
    }

    if ($fromDate !== '' && $toDate !== '' && $fromDate > $toDate) {
        $this->jsonError(422, 'Ngày bắt đầu không được sau ngày kết thúc');
    }

    if ($priority !== '' && !in_array($priority, ReportModel::PRIORITIES, true)) {
        $this->jsonError(422, 'Mức độ ưu tiên không hợp lệ');
    }

    if ($status !== '' && !in_array($status, ReportModel::STATUSES, true)) {
        $this->jsonError(422, 'Trạng thái không hợp lệ');
    }

    $rawCats = $_GET['category_id'] ?? [];
    $rawCats = is_array($rawCats) ? $rawCats : explode(',', (string)$rawCats);
    $categoryIds = array_values(array_unique(array_filter(array_map('intval', $rawCats), function ($id) {
        return $id > 0;
    })));

    $filters = [
        'public_only' => true,
        'priority' => $priority !== '' ? $priority : null,
        'status' => $status !== '' ? $status : null,
        'keyword' => $keyword,
    ];

    require_once APPROOT . '/app/models/NotificationModel.php';

    $reports = $this->reportModel->getAll($filters);

    $reports = array_values(array_filter($reports, function ($r) use ($categoryIds, $fromDate, $toDate) {
        if ($categoryIds && !in_array((int)$r->category_id, $categoryIds, true)) {
            return false;
        }

        $date = substr((string)($r->incident_time ?? $r->created_at ?? ''), 0, 10);

        if ($fromDate !== '' && ($date === '' || $date < $fromDate)) {
            return false;
        }

        if ($toDate !== '' && ($date === '' || $date > $toDate)) {
            return false;
        }

        return true;
    }));

    $total = count($reports);
    $totalPages = (int)ceil($total / $limit);
    $reports = array_slice($reports, ($page - 1) * $limit, $limit);

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
            'created_at' => $r->created_at,
        ];
    }

    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => $totalPages,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

    /* =========================================================
       BẢN ĐỒ SỐ LEAFLET (TV1 – Milestone 3, Issue #3)
       ========================================================= */
    const MAP_RADIUS_OPTIONS = [2, 5, 10];   // km – lựa chọn "Sự cố gần tôi"
    const MAP_RADIUS_MAX     = 20;
    const PRIORITY_LABELS = [
        'critical' => 'Nghiêm trọng',
        'high'     => 'Cao',
        'medium'   => 'Trung bình',
        'low'      => 'Thấp',
    ];

    /** Trang bản đồ toàn màn hình: GET /report/map */
    public function map() {
        $this->view('reports/map', [
            'title'         => 'Bản đồ sự cố môi trường',
            'active'        => 'map',
            'use_map'       => true,
            'categories'    => $this->categoryModel->getAll(),
            'radiusOptions' => self::MAP_RADIUS_OPTIONS,
        ]);
    }

    /**
     * WEBSERVICE GEOJSON (RFC 7946) cho Leaflet
     * GET /report/api_geojson?category_id=1,2&priority=high&state=active|resolved|all
     *                        &lat=10.77&lng=106.70&radius=5
     * - Toạ độ GeoJSON theo thứ tự [kinh độ, vĩ độ].
     * - Không trả thông tin liên hệ của người gửi.
     */
    public function api_geojson() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonError(405, 'Chỉ hỗ trợ phương thức GET');
        }

        // category_id chấp nhận: 3 | 1,2,3 | category_id[]=1&category_id[]=2
        $rawCats = $_GET['category_id'] ?? [];
        $catList = is_array($rawCats) ? $rawCats : explode(',', (string)$rawCats);
        $priority = get_param('priority');
        $state    = get_param('state', 'all');

        $filters = [
            'category_ids' => array_values(array_filter(array_map('intval', $catList))),
            'priority'     => in_array($priority, ReportModel::PRIORITIES, true) ? $priority : null,
            'state'        => in_array($state, ReportModel::MAP_STATES, true) ? $state : 'all',
        ];

        // Lọc "Sự cố gần tôi": cần đủ lat + lng hợp lệ
        $hasLat = isset($_GET['lat']) && $_GET['lat'] !== '';
        $hasLng = isset($_GET['lng']) && $_GET['lng'] !== '';
        if ($hasLat || $hasLng) {
            $lat = filter_var($_GET['lat'] ?? null, FILTER_VALIDATE_FLOAT);
            $lng = filter_var($_GET['lng'] ?? null, FILTER_VALIDATE_FLOAT);
            if ($lat === false || $lng === false || abs($lat) > 90 || abs($lng) > 180) {
                $this->jsonError(422, 'Toạ độ không hợp lệ (lat ∈ [-90, 90], lng ∈ [-180, 180]).');
            }
            $radius = filter_var($_GET['radius'] ?? 5, FILTER_VALIDATE_FLOAT);
            $filters['lat']       = $lat;
            $filters['lng']       = $lng;
            $filters['radius_km'] = min(max($radius ?: 5, 0.5), self::MAP_RADIUS_MAX);
        }

        require_once APPROOT . '/app/models/NotificationModel.php';   // dùng statusLabel()
        $features = [];
        foreach ($this->reportModel->getMapPoints($filters) as $r) {
            $features[] = [
                'type'     => 'Feature',
                'id'       => (int)$r->id,
                'geometry' => [
                    'type'        => 'Point',
                    'coordinates' => [(float)$r->longitude, (float)$r->latitude],
                ],
                'properties' => [
                    'id'             => (int)$r->id,
                    'report_code'    => $r->report_code,
                    'title'          => $r->title,
                    'address'        => $r->address,
                    'category_id'    => (int)$r->category_id,
                    'category_name'  => $r->category_name,
                    'priority'       => $r->priority,
                    'priority_label' => self::PRIORITY_LABELS[$r->priority] ?? $r->priority,
                    'status'         => $r->status,
                    'status_label'   => NotificationModel::statusLabel($r->status),
                    'level'          => ReportModel::mapLevel($r->priority, $r->status),
                    'upvote_count'   => (int)$r->upvote_count,
                    'thumbnail_url'  => $r->thumbnail ? url($r->thumbnail) : null,
                    'detail_url'     => url('report/detail/' . $r->id),
                    'distance_km'    => $r->distance_km !== null ? round((float)$r->distance_km, 2) : null,
                    'incident_time'  => $r->incident_time,
                ],
            ];
        }

        $meta = array_filter([
            'category_ids' => $filters['category_ids'] ?: null,
            'priority'     => $filters['priority'],
            'state'        => $filters['state'],
            'center'       => isset($filters['lat']) ? [$filters['lng'], $filters['lat']] : null,
            'radius_km'    => $filters['radius_km'] ?? null,
        ], function ($v) { return $v !== null; });

        echo json_encode([
            'type'     => 'FeatureCollection',
            'count'    => count($features),
            'filters'  => $meta,
            'features' => $features,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function jsonError($code, $message) {
        header('Content-Type: application/json; charset=utf-8');
	http_response_code($code);
        echo json_encode(['status' => 'error', 'message' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

public function api_upvote($id = null)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $this->jsonError(405, 'Phương thức không được hỗ trợ');
    }

    if (!isLoggedIn()) {
        return $this->jsonError(401, 'Vui lòng đăng nhập để xác nhận sự cố.');
    }

    $id = (int)($id ?? $_POST['report_id'] ?? 0);
    if ($id <= 0) {
        return $this->jsonError(400, 'Mã sự cố không hợp lệ');
    }

    	$reportModel = new ReportModel();
	$report = $reportModel->getById($id);

	if (!$report || !$report->is_public) {
return $this->jsonError(404, 'Không tìm thấy sự cố');
	}

    $result = $reportModel->toggleUpvote($id, $_SESSION['user_id']);
    $report = $reportModel->getById($id);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'success',
        'action' => $result['action'],
        'upvotes' => (int)$report->upvote_count,
        'user_voted' => $result['action'] === 'upvoted'
    ]);
    exit;
}

public function api_comments($id = null)
{
    $id = (int)($id ?? $_GET['report_id'] ?? 0);
    if ($id <= 0) {
        return $this->jsonError(400, 'Mã sự cố không hợp lệ.');
    }

    $report = $this->reportModel->getById($id);
    if (!$report || !$report->is_public) {
        return $this->jsonError(404, 'Không tìm thấy sự cố.');
    }

    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(50, max(1, (int)($_GET['limit'] ?? 10)));
    $total = $this->reportModel->countPublicComments($id);
    $comments = array_map([$this, 'commentToArray'],
        $this->reportModel->getPublicComments($id, $limit, ($page - 1) * $limit));

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'success',
        'data' => $comments,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => (int)ceil($total / $limit),
            'has_more' => $page * $limit < $total,
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

public function api_add_comment()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $this->jsonError(405, 'Phương thức không được hỗ trợ');
    }

    if (!isLoggedIn()) {
        return $this->jsonError(401, 'Vui lòng đăng nhập để gửi bình luận.');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    $reportId = (int)($input['report_id'] ?? 0);
    $content = is_string($input['content'] ?? null) ? trim($input['content']) : '';   // JSON {"content": [...]} → coi như rỗng

    if ($reportId <= 0 || $content === '') {
        return $this->jsonError(422, 'Vui lòng nhập nội dung bình luận.');
    }

    if (mb_strlen($content) > 5000) {
        return $this->jsonError(422, 'Bình luận tối đa 5000 ký tự.');
    }

    $report = $this->reportModel->getById($reportId);
    if (!$report || !$report->is_public) {
        return $this->jsonError(404, 'Không tìm thấy sự cố.');
    }

    $commentId = $this->reportModel->addComment($reportId, $_SESSION['user_id'], $content, 0);
    if (!$commentId) {
        return $this->jsonError(500, 'Không lưu được bình luận, vui lòng thử lại.');
    }

    // Trả lại bình luận vừa tạo để giao diện chèn ngay vào danh sách (không tải lại trang)
    http_response_code(201);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'success',
        'message' => 'Đã gửi bình luận.',
        'comment' => $this->commentToArray($this->reportModel->getCommentById($commentId)),
        'total' => $this->reportModel->countPublicComments($reportId),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Chuẩn hoá 1 bình luận cho JSON: chỉ các trường giao diện cần, không lộ thông tin khác của người dùng */
private function commentToArray($c) {
    return [
        'id' => (int)$c->id,
        'content' => $c->content,
        'user_name' => $c->user_name ?: 'Ẩn danh',
        'is_staff' => in_array($c->user_role, ['admin', 'officer'], true),
        'created_at' => $c->created_at,
        'created_at_text' => date('H:i d/m/Y', strtotime($c->created_at)),
    ];
}

public function api_xml_feed()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        return $this->jsonError(405, 'Phương thức không được hỗ trợ');
    }

    $reportModel = new ReportModel();
    $reports = $reportModel->getAll(['public_only' => true]);

    $xml = new SimpleXMLElement(
        '<?xml version="1.0" encoding="UTF-8"?><reports></reports>'
    );

    foreach ($reports as $report) {
        $item = $xml->addChild('report');
        $item->addChild('id', (string)$report->id);
        $item->addChild('title', htmlspecialchars(
            (string)$report->title, ENT_XML1, 'UTF-8'
        ));
        $item->addChild('description', htmlspecialchars(
            (string)$report->description, ENT_XML1, 'UTF-8'
        ));
        $item->addChild('status', (string)$report->status);
        $item->addChild('created_at', (string)$report->created_at);
    }

    header('Content-Type: application/xml; charset=utf-8');
    echo $xml->asXML();
    exit;
}



    public function comment($id = null) {
        // Đường dự phòng khi trình duyệt tắt JS (bản AJAX dùng api_add_comment) – cùng quy tắc: phải đăng nhập
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) redirect('report');
        requireLogin();

        // Cùng quy tắc với api_add_comment: chỉ bình luận vào sự cố có thật và đang công khai
        // (trước đây id không tồn tại gây lỗi 500, còn sự cố đã ẩn vẫn nhận bình luận)
        $report = $this->reportModel->getById((int)$id);
        if (!$report || !$report->is_public) {
            setFlash('danger', 'Không tìm thấy sự cố.');
            redirect('report');
        }

        $content = is_string($_POST['content'] ?? null) ? trim($_POST['content']) : '';
        if ($content !== '' && mb_strlen($content) <= 5000) {
            $this->reportModel->addComment($id, $_SESSION['user_id'], $content, 0);
            setFlash('success', 'Đã gửi phản hồi thành công.');
        }
        redirect('report/detail/' . (int)$report->id);
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
