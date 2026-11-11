<?php
/**
 * KIỂM TRA AN TOÀN UPLOAD TRÊN HOSTING (TV4 – Lê Trọng Hiếu, Milestone 4)
 * -----------------------------------------------------------------------
 * Chạy TỪ MÁY CỦA BẠN (không chạy trên hosting), trỏ vào địa chỉ website đã triển khai:
 *
 *   macOS (XAMPP):  /Applications/XAMPP/xamppfiles/bin/php tools/kiem_tra_upload_hosting.php https://ten-mien-cua-nhom.com/
 *   Windows (XAMPP): C:\xampp\php\php.exe tools\kiem_tra_upload_hosting.php https://ten-mien-cua-nhom.com/
 *   Máy local:       php tools/kiem_tra_upload_hosting.php http://localhost/Group11_Project-Web_WarningInfo/
 *
 * Script KHÔNG tạo báo cáo nào trên website: mọi lần gửi đều để trống tiêu đề nên form luôn bị từ chối,
 * chỉ đọc thông báo lỗi về ảnh. Không cần tài khoản. Không ghi file nào lên máy chủ.
 * Kết quả và ý nghĩa từng dòng: docs/KIEM_TRA_AN_TOAN_UPLOAD.md
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }       // không bao giờ chạy qua trình duyệt
if (!function_exists('curl_init')) { fwrite(STDERR, "Cần bật extension curl của PHP.\n"); exit(2); }

$base = rtrim($argv[1] ?? '', '/') . '/';
if (!preg_match('#^https?://#i', $base)) {
    fwrite(STDERR, "Cách dùng: php tools/kiem_tra_upload_hosting.php https://ten-mien-cua-nhom.com/\n");
    exit(2);
}

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_COOKIEFILE => '', CURLOPT_TIMEOUT => 30, CURLOPT_USERAGENT => 'WarningInfo-UploadCheck/1.0',
]);

/** Gửi request, trả [mã HTTP, header (chữ thường), nội dung] */
function http($ch, $url, $post = null) {
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    if ($post !== null) { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, $post); }
    $raw = curl_exec($ch);
    if ($raw === false) return [0, '', curl_error($ch)];
    $size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    return [(int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE), strtolower(substr($raw, 0, $size)), substr($raw, $size)];
}

/** Ảnh PNG 1x1 hợp lệ, tạo bằng PHP thuần (không cần GD) */
function png1x1() {
    $chunk = function ($type, $data) { return pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data)); };
    return "\x89PNG\r\n\x1a\n" . $chunk('IHDR', pack('NNCCCCC', 1, 1, 8, 2, 0, 0, 0))
         . $chunk('IDAT', gzcompress("\x00\x0f\x76\x6e")) . $chunk('IEND', '');
}

$tmp = [];
function tmpfile_with($content) { global $tmp; $f = tempnam(sys_get_temp_dir(), 'wi'); file_put_contents($f, $content); $tmp[] = $f; return $f; }
register_shutdown_function(function () use (&$tmp) { foreach ($tmp as $f) @unlink($f); });

$pass = 0; $fail = 0; $warn = 0;
function result($ok, $name, $detail = '', $level = 'fail') {
    global $pass, $fail, $warn;
    if ($ok) { $pass++; $mark = '  ĐẠT  '; } elseif ($level === 'warn') { $warn++; $mark = 'CẢNH BÁO'; } else { $fail++; $mark = '  LỖI  '; }
    echo "[$mark] $name" . ($detail !== '' ? "\n           → $detail" : '') . "\n";
}

/** Gửi form báo cáo (tiêu đề TRỐNG → không bao giờ lưu) kèm các file, trả câu báo lỗi về ảnh */
function send_files($ch, $base, array $files, $withToken = true) {
    [$code, , $html] = http($ch, $base . 'report/create');
    if ($code !== 200 || !preg_match('/name="csrf-token" content="([^"]+)"/', $html, $m)) return [$code, 'Không mở được trang report/create'];
    $post = ['title' => '', 'category_id' => '0', 'description' => '', 'priority' => 'low'];
    if ($withToken) $post['csrf_token'] = $m[1];
    $i = 0;
    foreach ($files as $name => $f) {
        $post['images[' . $i++ . ']'] = new CURLFile(tmpfile_with($f['content']), $f['type'], preg_replace('/#\d+$/', '', $name));
    }
    [$code, , $html] = http($ch, $base . 'report/store', $post);
    $msg = preg_match('/id="uploadError">([^<]*)</', $html, $mm) ? html_entity_decode(trim($mm[1]), ENT_QUOTES, 'UTF-8') : '';
    return [$code, $msg];
}

echo "\n== KIỂM TRA AN TOÀN UPLOAD: $base\n\n";

/* ---- 1. Trang gửi báo cáo + giới hạn dung lượng thực tế của máy chủ ---- */
[$code, $hdr, $html] = http($ch, $base . 'report/create');
result($code === 200, 'Mở được trang gửi báo cáo (report/create)', "HTTP $code");
if ($code !== 200) { echo "\nKhông mở được website – kiểm tra lại địa chỉ.\n"; exit(1); }
if (preg_match('/data-max-bytes="(\d+)"/', $html, $m1) && preg_match('/data-max-total="(\d+)"/', $html, $m2)) {
    $mb = function ($b) { return round($b / 1048576, 1) . 'MB'; };
    $ok = (int)$m1[1] >= 5 * 1048576;
    result($ok, 'Giới hạn ảnh của máy chủ đủ 5MB/ảnh như thiết kế',
        'Máy chủ cho mỗi ảnh tối đa ' . $mb($m1[1]) . ', tổng ' . $mb($m2[1])
        . ($ok ? '' : ' – nên nhờ TV1 tăng upload_max_filesize ≥ 6M, post_max_size ≥ 20M (cPanel > Select PHP Version > Options). Form vẫn báo đúng con số này nên không lỗi.'), 'warn');
}

/* ---- 2. Ứng dụng phải từ chối file độc hại ---- */
$php = "<?php echo 'PWNED-'.(6*7); ?>";
$png = png1x1();
$cases = [
    'File PHP (shell.php)'                       => [['shell.php' => ['content' => $php, 'type' => 'application/x-php']], 'không phải ảnh'],
    'Mã PHP đổi đuôi .jpg (shell.jpg)'           => [['shell.jpg' => ['content' => $php, 'type' => 'image/jpeg']], 'không phải ảnh'],
    'GIF89a + mã PHP (anh.gif)'                  => [['anh.gif' => ['content' => 'GIF89a' . $php, 'type' => 'image/gif']], 'không phải ảnh'],
    'SVG chứa <script> (logo.svg)'               => [['logo.svg' => ['content' => '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>', 'type' => 'image/svg+xml']], 'không phải ảnh'],
    'File rỗng 0 byte'                           => [['rong.jpg' => ['content' => '', 'type' => 'image/jpeg']], 'rỗng'],
    'Gửi 4 ảnh (vượt quá 3 ảnh/báo cáo)'         => [['a.png#1' => ['content' => $png, 'type' => 'image/png'], 'a.png#2' => ['content' => $png, 'type' => 'image/png'],
                                                     'a.png#3' => ['content' => $png, 'type' => 'image/png'], 'a.png#4' => ['content' => $png, 'type' => 'image/png']], 'tối đa 3'],
];
foreach ($cases as $name => [$files, $expect]) {
    [$code, $msg] = send_files($ch, $base, $files);
    result($code === 200 && mb_stripos($msg, $expect) !== false, "Từ chối: $name", $msg !== '' ? "\"$msg\"" : "HTTP $code, không thấy thông báo lỗi ảnh");
}
[$code, $msg] = send_files($ch, $base, ['ok.png' => ['content' => $png, 'type' => 'image/png']]);
result($code === 200 && $msg === '', 'Ảnh PNG hợp lệ KHÔNG bị báo lỗi (form chỉ báo thiếu tiêu đề, không lưu gì)', $msg !== '' ? "\"$msg\"" : '');
[$code] = send_files($ch, $base, ['ok.png' => ['content' => $png, 'type' => 'image/png']], false);
result($code === 403, 'Gửi form không có CSRF token bị chặn (403)', "HTTP $code");

/* ---- 3. .htaccess của thư mục uploads có hiệu lực trên hosting không? ----
   Apache/LiteSpeed chặn theo TÊN file trước khi tìm file, nên chỉ cần thử tên không tồn tại:
   bị chặn → 403; nếu ra 404 nghĩa là hosting BỎ QUA .htaccess (Nginx, hoặc AllowOverride None). */
$blocked = ['uploads/reports/thu.php', 'uploads/reports/thu.PHP', 'uploads/reports/thu.phtml', 'uploads/reports/thu.phar',
            'uploads/reports/thu.php.jpg', 'uploads/reports/thu.svg', 'uploads/reports/thu.html', 'uploads/reports/.user.ini',
            'uploads/.htaccess', 'uploads/reports/'];
foreach ($blocked as $path) {
    [$code] = http($ch, $base . $path);
    result($code === 403, "Chặn truy cập trực tiếp: $path", "HTTP $code" . ($code === 404 && $path !== 'uploads/reports/' ? ' – .htaccess KHÔNG có tác dụng trên hosting này!' : ''));
}
[$code] = http($ch, $base . 'uploads/reports/khong-ton-tai.jpg');
result($code === 404, 'Tên ảnh hợp lệ không bị chặn nhầm (ảnh không tồn tại → 404)', "HTTP $code");
// Lấy 1 ảnh thật đang hiển thị ở trang chủ (mục "Cảnh báo mới nhất") để kiểm tra ảnh vẫn xem được + header nosniff
[, , $list] = http($ch, $base);
if (preg_match('#uploads/reports/[0-9a-f]{32}\.(?:jpg|png|webp)#', $list, $img)) {
    [$code, $hdr] = http($ch, $base . $img[0]);
    result($code === 200 && preg_match('#content-type: image/#', $hdr), 'Ảnh người dân gửi vẫn xem được bình thường', "HTTP $code · {$img[0]}");
    result(strpos($hdr, 'x-content-type-options: nosniff') !== false, 'Header X-Content-Type-Options: nosniff cho ảnh trong uploads',
        strpos($hdr, 'x-content-type-options') !== false ? '' : 'Hosting chưa bật mod_headers – không nguy hiểm vì các lớp chặn khác vẫn hoạt động', 'warn');
} else {
    echo "[ BỎ QUA ] Trang chủ chưa có ảnh sự cố nào để kiểm tra header – gửi 1 báo cáo có ảnh, duyệt công khai rồi chạy lại\n";
}

/* ---- 4. Không lộ mã nguồn / cấu hình ---- */
foreach (['config/config.php', '.git/config', 'database/database_init.sql', 'app/helpers/view_helper.php', '.env'] as $path) {
    [$code, , $body] = http($ch, $base . $path);
    $leak = $code === 200 && preg_match('/DB_PASS|\[core\]|CREATE TABLE|function e\(|DB_HOST/i', $body);
    result(!$leak, "Không lộ file: $path", "HTTP $code" . ($leak ? ' – NỘI DUNG FILE BỊ LỘ!' : ''));
}

echo "\n== TỔNG KẾT: $pass đạt, $fail lỗi, $warn cảnh báo\n";
exit($fail > 0 ? 1 : 0);
