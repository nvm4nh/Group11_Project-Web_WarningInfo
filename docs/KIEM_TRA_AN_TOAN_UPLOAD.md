# Kiểm tra an toàn upload tệp trên Hosting – WarningInfo

> **Người thực hiện:** Lê Trọng Hiếu (TV4) · **Milestone 4** – Issue *[TV4] Xây dựng trang xử lý lỗi 404/500 và tinh chỉnh UI/UX hoàn thiện*
> Nhánh: `feature/tv4-ui-polish-404-page`

Người dân gửi báo cáo kèm tối đa 3 ảnh, và đây là chỗ duy nhất người lạ đưa được tệp lên máy chủ. Tài liệu này ghi lại:

- các lớp bảo vệ đang có;
- kết quả kiểm thử trên hai cấu hình máy chủ (giống XAMPP và giống hosting thật);
- những gì đã sửa sau khi kiểm thử;
- cách chạy lại bài kiểm tra trên hosting sau khi TV1 triển khai.

---

## 1. Các lớp bảo vệ

| Lớp | Ở đâu | Làm gì |
|---|---|---|
| ① Kiểm tra ở trình duyệt | `public/assets/js/report-form.js` (TV4) | Chặn ngay khi chọn sai định dạng, quá số ảnh, quá dung lượng, để người dân không phải gửi rồi mới biết lỗi. *Chỉ để tiện cho người dùng, không dùng để bảo mật.* |
| ② Kiểm tra ở máy chủ | `ReportController::collectUploads()` (TV3) | Kiểm tra CSRF token, tối đa 3 ảnh, mỗi ảnh tối đa 5MB. Nhận diện loại tệp từ **nội dung** (`finfo` + `getimagesize`), không tin đuôi tệp hay `Content-Type` do trình duyệt gửi. Chỉ nhận JPG/PNG/WEBP. |
| ③ Lưu tệp | `ReportController::saveUploads()` (TV3) | Đặt **tên ngẫu nhiên 32 ký tự**. Đuôi tệp lấy theo loại thật (`.jpg/.png/.webp`). Tên gốc chỉ lưu vào CSDL sau khi đã `basename()`. |
| ④ Thư mục `public/uploads/` | `public/uploads/.htaccess` (siết lại ở Milestone 4) | Có **danh sách trắng**: chỉ mở cho `tên.jpg/.jpeg/.png/.webp` có đúng một dấu chấm. Chặn đuôi script, kể cả đuôi kép như `anh.php.jpg`. Tắt PHP, bỏ `AddHandler`, không liệt kê thư mục. Thêm header `X-Content-Type-Options: nosniff` và CSP `sandbox`. |
| ⑤ Hiển thị | `reports/detail.php` | Tên tệp gốc luôn được in qua `e()` (chống XSS). |

## 2. Môi trường kiểm thử

| | Môi trường A – giống XAMPP | Môi trường B – giống hosting thật |
|---|---|---|
| Máy chủ web | Apache 2.4.58 + **mod_php** 8.3 | Apache 2.4.58 + **PHP-FPM** 8.3 (`proxy_fcgi`), cách phần lớn hosting cPanel/DirectAdmin chạy PHP |
| Giới hạn PHP | `upload_max_filesize = 40M`, `post_max_size = 40M` (mặc định XAMPP) | `upload_max_filesize = 2M`, `post_max_size = 8M` (mặc định php.ini, nhiều hosting giữ nguyên) |
| Cách chạy | Script tự động gửi form thật qua HTTP rồi mở trực tiếp các tệp trong `uploads/` | Như môi trường A |

## 3. Kết quả

**A = môi trường A, B = môi trường B.** ✅ = đạt.

### 3.1. Ứng dụng kiểm tra tệp gửi lên

| # | Tình huống tấn công / sử dụng | Kết quả mong đợi | A | B |
|---|---|---|---|---|
| 1 | Ảnh JPG hợp lệ | Nhận, lưu thành `<32 ký tự ngẫu nhiên>.jpg` | ✅ | ✅ |
| 2 | Ảnh PNG + WEBP hợp lệ | Nhận cả hai | ✅ | ✅ |
| 3 | Tệp `shell.php` | Từ chối: *"không phải ảnh JPG, PNG hoặc WEBP hợp lệ"* | ✅ | ✅ |
| 4 | Mã PHP đổi đuôi thành `shell.jpg` | Từ chối (nội dung không phải ảnh) | ✅ | ✅ |
| 5 | `shell.php` gửi kèm `Content-Type: image/jpeg` giả | Từ chối, vì máy chủ không tin loại tệp do trình duyệt khai | ✅ | ✅ |
| 6 | `GIF89a` + mã PHP | Từ chối, vì GIF không nằm trong danh sách cho phép | ✅ | ✅ |
| 7 | SVG chứa `<script>` | Từ chối | ✅ | ✅ |
| 8 | Tệp rỗng 0 byte | Từ chối: *"rỗng (0 byte)"* | ✅ (sau khi sửa) | ✅ (sau khi sửa) |
| 9 | Gửi 4 ảnh | Từ chối: *"tối đa 3 ảnh"* | ✅ | ✅ |
| 10 | Ảnh 7MB | A: *"vượt quá 5MB"* | ✅ | – |
| 11 | Ảnh 3MB | A: nhận · B: *"vượt quá giới hạn dung lượng của máy chủ (2M)"* | ✅ | ✅ |
| 12 | Tổng 14MB (quá `post_max_size`) | B: trang báo *"Dữ liệu gửi lên quá lớn (máy chủ cho phép tối đa 8M)"*, không lưu gì | – | ✅ |
| 13 | **Ảnh JPEG thật có gắn mã PHP ở cuối**, đặt tên `poly.php` | Qua được bước kiểm tra ảnh, nhưng bị lưu thành `.jpg`. Mở trực tiếp thì trả `image/jpeg`, **mã PHP không chạy** | ✅ | ✅ |
| 14 | Tên tệp `../../../evil.jpg` | Không thoát ra ngoài thư mục; CSDL chỉ lưu `evil.jpg` | ✅ | ✅ |
| 15 | Tên tệp chứa `"><img src=x onerror=…>` | Trang chi tiết in tên đã mã hoá, không chèn được thẻ | ✅ | ✅ |
| 16 | Gửi form không có CSRF token | 403 | ✅ | ✅ |

### 3.2. Mở trực tiếp tệp trong `uploads/`

Giả sử kẻ xấu đưa được tệp vào thư mục bằng một đường khác (FTP lộ mật khẩu, plugin lỗi…), máy chủ vẫn không được chạy tệp đó.

| # | Đường dẫn | Mong đợi | A | B |
|---|---|---|---|---|
| 17 | `uploads/reports/t.php`, `t.PhP`, `t.phtml`, `t.phar`, `t.php5` | 403, không chạy | ✅ | ✅ |
| 18 | `uploads/reports/t.php.jpg` (đuôi kép) | 403 | ✅ | ✅ |
| 19 | `uploads/reports/x.svg`, `x.html` | 403 | ✅ | ✅ |
| 20 | `uploads/reports/.user.ini`, `uploads/.htaccess` | 403 | ✅ | ✅ |
| 21 | `uploads/reports/` (liệt kê thư mục) | 403 | ✅ | ✅ |
| 22 | Ảnh hợp lệ | 200 `image/jpeg`, có header `nosniff` | ✅ | ✅ |

**Tổng:** 30/30 đạt ở cả hai môi trường.

## 4. Những gì đã sửa sau khi kiểm thử

| Vấn đề phát hiện | Trước | Sau (commit trên nhánh này) |
|---|---|---|
| Hosting chỉ cho 2MB/ảnh nhưng form ghi *"mỗi ảnh không quá 5MB"* | Người dân chọn ảnh điện thoại 3MB và điền hết form. Bấm gửi xong mới bị báo lỗi, phải chọn lại ảnh. | `upload_limits()` lấy mức nhỏ hơn giữa quy định của ứng dụng và `php.ini` của máy chủ. Form ghi đúng con số, JS chặn ngay khi chọn ảnh, chặn cả trường hợp tổng dung lượng vượt `post_max_size`. |
| Tệp 0 byte | Báo nhầm *"vượt quá 5MB"* | Báo *"rỗng (0 byte), vui lòng chọn lại ảnh"* |
| Đuôi kép `anh.php.jpg` trong `uploads/` | Trả **200** (máy chủ trả tệp tĩnh, không chạy). Riêng hosting cấu hình `AddHandler` kiểu cũ có thể chạy tệp này như PHP. | Trả **403**: danh sách trắng chỉ cho tên có đúng một dấu chấm, cộng với `RemoveHandler`. |
| Trình duyệt tự "đoán" loại tệp | Không có header | Thêm `X-Content-Type-Options: nosniff` và `Content-Security-Policy: … sandbox` cho mọi tệp trong `uploads/` |

Kết quả cấu hình `.htaccess` **cũ** trên cùng bộ kiểm thử: 28/30 ở cả hai môi trường. Hai mục trượt là #18 (đuôi kép trả 200) và #22 (thiếu `nosniff`). Cấu hình cũ chưa có lỗ hổng chạy được mã trên hai môi trường này; bản mới thêm lớp phòng thủ cho các hosting cấu hình khác.

## 5. Chạy lại bài kiểm tra trên hosting thật (TV1 / TV5, sau khi triển khai)

Script `tools/kiem_tra_upload_hosting.php` chạy **trên máy của bạn** và trỏ vào địa chỉ website:

```bash
# macOS (XAMPP)
/Applications/XAMPP/xamppfiles/bin/php tools/kiem_tra_upload_hosting.php https://ten-mien-cua-nhom.com/

# Windows (XAMPP)
C:\xampp\php\php.exe tools\kiem_tra_upload_hosting.php https://ten-mien-cua-nhom.com/
```

- **Không tạo dữ liệu rác:** mọi lần gửi đều để trống tiêu đề, nên form luôn bị từ chối và không báo cáo nào được lưu. Script chỉ đọc câu báo lỗi về ảnh.
- **Không cần tài khoản, không ghi tệp nào lên máy chủ.** Để kiểm tra `.htaccess`, script chỉ thử tên tệp *không tồn tại*: Apache/LiteSpeed chặn theo tên trước khi tìm tệp, nên nếu `.htaccess` có tác dụng thì phải nhận **403**.
- Script nằm ngoài `public/` nên không mở được từ trình duyệt; nếu bị gọi qua web, nó trả 404 ngay.

Kết quả chạy thử trên máy local:

| Môi trường | Đạt | Lỗi | Cảnh báo |
|---|---|---|---|
| A (giống XAMPP) | 28 | 0 | 0 |
| B (giống hosting, 2M) | 27 | 0 | 1 – *"Máy chủ cho mỗi ảnh tối đa 2MB…"* |

### Đọc kết quả

| Dòng | Ý nghĩa | Cần làm |
|---|---|---|
| `LỖI … HTTP 404 – .htaccess KHÔNG có tác dụng` | Hosting bỏ qua `.htaccess`: dùng **Nginx**, hoặc `AllowOverride None` | Nhờ nhà cung cấp bật `AllowOverride All`, hoặc với Nginx thêm vào cấu hình site:<br>`location ~* ^/uploads/.*\.(php\d?\|phtml\|phar\|svg\|html?)$ { deny all; }` |
| `CẢNH BÁO Giới hạn ảnh … 2MB` | Hosting để `upload_max_filesize` thấp | Website vẫn chạy đúng (form báo đúng giới hạn). Muốn đủ 5MB/ảnh thì vào cPanel → **Select PHP Version → Options**, đặt `upload_max_filesize = 6M`, `post_max_size = 20M` |
| `CẢNH BÁO … nosniff` | Hosting chưa bật `mod_headers` | Không nguy hiểm, các lớp ①–④ vẫn hoạt động |
| `LỖI Không lộ file: …` | Mã nguồn hoặc cấu hình đọc được từ ngoài | Kiểm tra `.htaccess` ở thư mục gốc, hoặc trỏ *Document Root* vào thư mục `public/` |

## 6. Đề xuất cho bước triển khai (TV1)

1. Quyền thư mục: `public/uploads/` và `public/uploads/reports/` để **755**. Chỉ dùng **775** nếu PHP chạy khác user với chủ thư mục. **Không dùng 777.** Tệp ảnh để 644.
2. Khi upload mã nguồn lên hosting, **giữ nguyên** `public/uploads/.htaccess`. Tệp bắt đầu bằng dấu chấm nên một số trình FTP ẩn đi, cần bật "hiện tệp ẩn" để kiểm tra.
3. Nếu hosting cho chọn *Document Root*, trỏ thẳng vào `public/`.
4. Sau khi website chạy, chạy script ở mục 5 và dán kết quả vào phụ lục biên bản UAT (TV5).

**Chưa làm (ngoài phạm vi Milestone 4):** ảnh chụp bằng điện thoại có thể còn thông tin EXIF (tọa độ GPS, đời máy). Vị trí sự cố vốn đã công khai trên bản đồ, nên rủi ro thấp. Nếu muốn xoá EXIF hoàn toàn thì cần xử lý lại ảnh bằng GD khi lưu.
