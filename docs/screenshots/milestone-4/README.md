# Ảnh chụp Milestone 4 – phần của TV4 (Lê Trọng Hiếu)

Issue **[TV4] Xây dựng trang xử lý lỗi 404/500 và tinh chỉnh UI/UX hoàn thiện** – nhánh `feature/tv4-ui-polish-404-page`.
Chụp ở 375px (điện thoại) và 1280px (máy tính).

| # | Nội dung | 375px | 1280px |
|---|---|---|---|
| 01 | Trang 404 – mở sự cố không tồn tại `/report/detail/9999` (mã HTTP 404) | [01](01-loi-404-375.png) | [01](01-loi-404-1280.png) |
| 02 | Trang 500 – khi CSDL mất kết nối (bản trên hosting: chỉ hiện mã tra cứu, không lộ chi tiết kỹ thuật) | [02](02-loi-500-375.png) | [02](02-loi-500-1280.png) |
| 03 | Trang chủ dùng dữ liệu thật: "Đang diễn ra", cảnh báo mới nhất, số báo cáo theo danh mục | [03](03-trang-chu-du-lieu-that-375.png) | [03](03-trang-chu-du-lieu-that-1280.png) |
| 04 | Thanh menu không còn tràn: khách 992px, admin 1280px, cán bộ 1440px | | [992](04-thanh-menu-khach-992.png) · [1280](04-thanh-menu-admin-1280.png) · [1440](04-thanh-menu-can-bo-1440.png) |
| 05 | Đăng ký (gợi ý mật khẩu) và đăng nhập (báo lỗi dưới ô) dạng thẻ | [ĐK](05-dang-ky-375.png) · [ĐN](05-dang-nhap-bao-loi-375.png) | [ĐK](05-dang-ky-1280.png) · [ĐN](05-dang-nhap-bao-loi-1280.png) |
| 06 | "Báo cáo của tôi" xếp thành thẻ trên điện thoại | [06](06-bao-cao-cua-toi-the-375.png) | |
| 07 | **OpenGraph**: khung xem trước khi chia sẻ link (trang chủ dùng ảnh `og-image.jpg`, sự cố dùng ảnh hiện trường đầu tiên) | | [07](07-xem-truoc-chia-se-og.png) |
| 08 | **Favicon** trên tab trình duyệt + bộ icon màn hình chính (iPhone / Android) | | [08](08-favicon-icon.png) |
| 09 | Form gửi báo cáo trên máy chủ giống hosting (PHP-FPM, 2MB/ảnh): form ghi đúng giới hạn và chặn ngay ảnh 3MB | [09](09-upload-gioi-han-hosting-375.png) | |
| 10 | Kết quả script `tools/kiem_tra_upload_hosting.php` (chi tiết: [KIEM_TRA_AN_TOAN_UPLOAD.md](../../KIEM_TRA_AN_TOAN_UPLOAD.md)) | | [10](10-script-kiem-tra-upload.png) |
| 11 | Dùng phím Tab: thẻ danh mục có viền focus và nổi lên giống khi rê chuột | | [11](11-focus-ban-phim-1280.png) |

## Cách xử lý lỗi

- **404**: `core/App.php` trả 404 khi controller hoặc action không tồn tại, kể cả khi gọi hàm private, `view()`, `model()` qua URL. Controller gọi `show_404('lời nhắn')` khi không có dữ liệu (vd sự cố không tồn tại hoặc chưa công khai). API / AJAX nhận JSON `{"status":"error"}` với mã 404.
- **500**: `register_error_pages()` trong `public/index.php` bắt mọi exception và lỗi Fatal, rồi hiện trang 500 độc lập (không phụ thuộc header/footer/CSDL). Phần trang đang in dở bị xoá. Lỗi được ghi vào `error_log` kèm **mã tra cứu** (vd `E261110-083343-7E51`).
- Chi tiết kỹ thuật chỉ hiện khi mở web ngay trên máy chạy XAMPP. Muốn ép tắt hoặc bật thì thêm `define('APP_DEBUG', false);` (hoặc `true`) vào `config/config.php`.

## SEO, OpenGraph, favicon

- `seo_meta()` trong `app/helpers/view_helper.php` tạo cho mỗi trang: tiêu đề, mô tả riêng, `canonical`, `og:*`, `twitter:*`.
- Trang chi tiết sự cố lấy mô tả và ảnh minh chứng đầu tiên của chính báo cáo.
- Gắn `noindex` cho: trang quản trị, trang cán bộ, trang cá nhân (thông báo, hồ sơ, báo cáo của tôi), báo cáo chưa công khai và mọi trang lỗi.
- Tệp mới: `favicon.ico` (16/32/48), `assets/img/icons/` (apple-touch-icon 180, icon 192/512), `site.webmanifest`, `robots.txt`, ảnh chia sẻ `assets/img/og-image.jpg` (1200×630).

## Rà soát giao diện toàn hệ thống

- 17 trang (khách, người dân, cán bộ, quản trị) × 4 độ rộng 375 / 414 / 768 / 1280px: không trang nào tràn ngang, không lỗi JavaScript, không lỗi PHP.
- Kiểm thử hồi quy 17/17: gửi báo cáo kèm ảnh + GPS, upvote, bình luận AJAX, thông báo, admin đổi trạng thái và phân công, cán bộ nhận việc và hoàn thành. Bộ này chạy được cả trên Apache + mod_php (XAMPP) lẫn Apache + PHP-FPM (giống hosting).
- Hover và focus thống nhất: mọi liên kết có viền focus khi dùng phím Tab. Trên màn hình cảm ứng, thẻ không còn bị "kẹt" ở trạng thái nổi. Hiệu ứng chuyển động tắt khi người dùng bật "giảm chuyển động".
