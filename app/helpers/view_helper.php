<?php
/**
 * Hàm tiện ích dùng chung cho View & Controller (TV4 – Lê Trọng Hiếu)
 * Được nạp trong public/index.php.
 */

/* ---------- Bảo mật & đường dẫn ---------- */

// In dữ liệu ra HTML an toàn (chống XSS). LUÔN dùng khi in dữ liệu người dùng nhập.
function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// url('report/detail/5') => http://localhost/DoAnWeb_MVC/report/detail/5
function url($path = '') {
    return rtrim(URLROOT, '/') . '/' . ltrim($path, '/');
}

// asset('css/style.css') => http://localhost/DoAnWeb_MVC/assets/css/style.css
// (.htaccess ở thư mục gốc tự chuyển vào public/, nên KHÔNG ghi /public/ trong link)
function asset($path) {
    return rtrim(URLROOT, '/') . '/assets/' . ltrim($path, '/');
}

// Tạo link giữ nguyên query string: queryUrl('report', ['q' => 'rác', 'page' => 2])
function queryUrl($path, array $params = []) {
    $params = array_filter($params, function ($v) { return $v !== null && $v !== '' && $v !== []; });
    $qs = http_build_query($params);
    return url($path) . ($qs ? '?' . $qs : '');
}

function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

/* ---------- Thời gian tương đối (Milestone 3 – hộp thông báo) ---------- */

// timeAgo(90) => "1 phút trước". Nhận số giây đã trôi qua (tính sẵn trong SQL) và ngày gốc để hiện khi đã lâu.
function timeAgo($seconds, $datetime = null) {
    $s = max(0, (int)$seconds);
    if ($s < 60)        return 'Vừa xong';
    if ($s < 3600)      return floor($s / 60) . ' phút trước';
    if ($s < 86400)     return floor($s / 3600) . ' giờ trước';
    if ($s < 172800)    return 'Hôm qua';
    if ($s < 7 * 86400) return floor($s / 86400) . ' ngày trước';
    return $datetime ? date('d/m/Y', strtotime($datetime)) : floor($s / 86400) . ' ngày trước';
}

/* ---------- Biểu tượng / màu theo danh mục & mức độ (Milestone 4 – trang chủ) ---------- */

// categoryVisual(2) => ['bi-droplet-half', 'cat-water', 'water.svg']; danh mục admin tự thêm dùng kiểu "khác"
function categoryVisual($categoryId) {
    $map = [
        1 => ['bi-trash3',              'cat-waste',  'waste.svg'],
        2 => ['bi-droplet-half',        'cat-water',  'water.svg'],
        3 => ['bi-cloud-haze2',         'cat-air',    'air.svg'],
        4 => ['bi-cloud-lightning-rain','cat-flood',  'flood.svg'],
        5 => ['bi-tree',                'cat-forest', 'forest.svg'],
        6 => ['bi-volume-up',           'cat-noise',  'noise.svg'],
        7 => ['bi-exclamation-octagon', 'cat-chem',   'chem.svg'],
    ];
    return $map[(int)$categoryId] ?? ['bi-exclamation-circle', 'cat-other', 'other.svg'];
}

// severityVisual('high') => ['Cao', 'sev-high', 'bi-exclamation-triangle-fill'] (huy hiệu .badge-severity ở trang chủ)
function severityVisual($priority) {
    $map = [
        'critical' => ['Nghiêm trọng', 'sev-critical', 'bi-exclamation-octagon-fill'],
        'high'     => ['Cao',          'sev-high',     'bi-exclamation-triangle-fill'],
        'medium'   => ['Trung bình',   'sev-medium',   'bi-exclamation-circle-fill'],
        'low'      => ['Thấp',         'sev-low',      'bi-info-circle-fill'],
    ];
    return $map[$priority] ?? $map['medium'];
}

// Đường dẫn ảnh đã upload (file_path trong CSDL có thể có hoặc không có "public/" ở đầu)
function uploadUrl($path) {
    $path = trim((string)$path);
    if ($path === '') return '';
    return preg_match('#^https?://#i', $path) ? $path : url(preg_replace('#^/?(public/)?#', '', $path));
}

/* ---------- SEO: thẻ meta, OpenGraph, Twitter Card (TV4 – Milestone 4) ---------- */

// Cắt đoạn văn còn tối đa $max ký tự, không cắt giữa chữ: seo_excerpt('Nước kênh ...', 160)
function seo_excerpt($text, $max = 160) {
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$text)));
    if (mb_strlen($text) <= $max) return $text;
    $cut = mb_substr($text, 0, $max - 1);
    $space = mb_strrpos($cut, ' ');
    return rtrim($space > $max * 0.6 ? mb_substr($cut, 0, $space) : $cut, " ,.;:–-") . '…';
}

/**
 * Thông tin SEO của trang đang mở – header.php dùng để in <title>, meta description, canonical,
 * robots, OpenGraph (Facebook / Zalo / Messenger hiện ảnh xem trước) và Twitter Card.
 * Controller có thể ghi đè bằng $data['description'], $data['og_image'], $data['noindex'].
 */
function seo_meta(array $data = []) {
    $site  = defined('SITENAME') ? SITENAME : 'WarningInfo';
    $parts = array_values(array_filter(explode('/', trim((string)($_GET['url'] ?? ''), '/')), 'strlen'));
    $route = strtolower(($parts[0] ?? 'home') . '/' . ($parts[1] ?? 'index'));   // "report/detail", "home/index"...

    $intro = 'Người dân gửi phản ánh ô nhiễm, rác thải, ngập úng kèm ảnh và vị trí GPS; theo dõi tiến độ xử lý của cán bộ môi trường tại TP.HCM.';
    $descriptions = [
        'home/index'    => $intro,
        'report/index'  => 'Danh sách cảnh báo môi trường do cộng đồng gửi: lọc theo danh mục, mức độ nghiêm trọng và trạng thái xử lý.',
        'report/map'    => 'Bản đồ sự cố môi trường: xem vị trí các điểm rác thải, ngập úng, ô nhiễm không khí, tiếng ồn quanh bạn.',
        'report/create' => 'Gửi báo cáo sự cố môi trường trong vài bước: chụp ảnh, ghim vị trí GPS và mô tả ngắn – không bắt buộc đăng nhập.',
        'report/store'  => 'Gửi báo cáo sự cố môi trường trong vài bước: chụp ảnh, ghim vị trí GPS và mô tả ngắn – không bắt buộc đăng nhập.',
        'auth/login'    => 'Đăng nhập WarningInfo để theo dõi các báo cáo đã gửi và nhận thông báo khi sự cố được xử lý.',
        'auth/register' => 'Tạo tài khoản WarningInfo miễn phí để gửi, theo dõi báo cáo và nhận thông báo cập nhật từ cán bộ xử lý.',
    ];

    $title  = trim((string)($data['title'] ?? ''));
    $isHome = $route === 'home/index';
    $slogan = $site . ' – Báo cáo & cảnh báo môi trường từ cộng đồng';
    $seo = [
        'title'       => $isHome ? $slogan : ($title !== '' ? $title . ' | ' : '') . $site,
        'og_title'    => $isHome ? $slogan : ($title !== '' ? $title : $site),
        'description' => $descriptions[$route] ?? $intro,
        'type'        => 'website',
        'image'       => asset('img/og-image.jpg'),
        'image_size'  => [1200, 630],
        'image_alt'   => $site . ' – Thấy vấn đề môi trường? Báo ngay để cùng xử lý.',
        'canonical'   => url($isHome ? '' : implode('/', $parts)),
        'published'   => null,
        'robots'      => 'index, follow',
    ];

    // Trang chi tiết sự cố: mô tả + ảnh minh chứng đầu tiên của chính báo cáo
    $r = $data['report'] ?? null;
    if ($route === 'report/detail' && is_object($r)) {
        $place = trim(($r->category_name ?? '') . (!empty($r->address) ? ' · ' . $r->address : ''), ' ·');
        $seo['description'] = seo_excerpt(($place !== '' ? $place . ' – ' : '') . ($r->description ?? ''));
        $seo['type']        = 'article';
        $seo['canonical']   = url('report/detail/' . (int)$r->id);
        $seo['published']   = !empty($r->created_at) ? date('c', strtotime($r->created_at)) : null;
        foreach (($r->images ?? []) as $img) {
            $img = (object)$img;
            if (strpos((string)($img->mime_type ?? 'image/'), 'image/') === 0 && trim((string)($img->file_path ?? '')) !== '') {
                $seo['image']      = uploadUrl($img->file_path);
                $seo['image_size'] = null;                       // ảnh người dân gửi: không biết trước kích thước
                $seo['image_alt']  = 'Ảnh hiện trường: ' . $r->title;
                break;
            }
        }
        if (empty($r->is_public)) $data['noindex'] = true;    // báo cáo chưa công khai: chỉ người gửi / cán bộ xem
    }

    if (!empty($data['description'])) $seo['description'] = seo_excerpt($data['description']);
    if (!empty($data['og_image']))    { $seo['image'] = $data['og_image']; $seo['image_size'] = null; }

    // Không cho công cụ tìm kiếm lập chỉ mục: trang quản trị / cán bộ / cá nhân và mọi trang lỗi (404, 403, 500)
    $private = in_array(strtolower($parts[0] ?? ''), ['admin', 'officer', 'notification'], true)
        || in_array($route, ['auth/profile', 'report/my_reports'], true);
    $status = http_response_code();
    if ($private || !empty($data['noindex']) || (is_int($status) && $status >= 400)) {
        $seo['robots']    = 'noindex, nofollow';
        $seo['canonical'] = null;
    }
    return $seo;
}
