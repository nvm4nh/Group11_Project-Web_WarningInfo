# Kiểm thử tự động WarningInfo (E2E + truy cập WCAG)

Bộ test mở trình duyệt thật (Playwright – Chromium) và đi qua toàn bộ luồng nghiệp vụ, đồng thời đối chiếu dữ liệu trong CSDL.

| Nhóm | Nội dung chính |
|---|---|
| 1. Kiểm tra tĩnh & CSDL | `php -l` mọi file PHP, `node --check` mọi file JS, đủ 10 bảng |
| 2. Trang công khai | Trang chủ, danh sách, bản đồ Leaflet (có marker), chi tiết, lọc AJAX, đường dẫn sai |
| 3. WebService API | `api_list` (kể cả mã 422 khi tham số sai), `api_geojson`, `api_xml_feed`, `api_comments` |
| 4. Bảo mật & phân quyền | CSRF, chặn khách / người dân vào `/admin`, `/officer`, sai mật khẩu |
| 5. Người dân | Đăng ký, gửi báo cáo kèm ảnh + GPS, chống XSS, upvote, bình luận AJAX |
| 6. Admin | Chart.js, xuất CSV UTF-8, đổi trạng thái, phân công cán bộ, CRUD danh mục |
| 7. Cán bộ | Nhận việc, ghi chú, báo hoàn thành, lịch sử trạng thái |
| 8. Thông báo | Người dân / admin nhận thông báo, đánh dấu đã đọc |
| 9. Di động & lỗi JS | iPhone 13 không tràn ngang, không có lỗi JavaScript |
| 10. Sẵn sàng deploy | Chạy ở gốc domain, header bảo mật, cookie HttpOnly/SameSite, múi giờ, khoá tài khoản, không lộ mã nguồn |

## Chuẩn bị (1 lần)

- Node.js 18 trở lên, PHP và `mysql` CLI trong PATH (XAMPP: `C:\xampp\php`, `C:\xampp\mysql\bin`).
- Cài thư viện và trình duyệt cho Playwright:

```bash
cd tests/e2e
npm install
npx playwright install chromium
```

## Chạy

Bộ test **thêm / sửa dữ liệu**, nên luôn nạp lại CSDL mẫu trước mỗi lần chạy (không chạy trên CSDL thật của hosting):

```bash
mysql -u root < database/database_init.sql
```

```bash
cd tests/e2e
npm test
```

- Kết quả từng test in ra màn hình; ảnh chụp từng bước và `results.json` nằm trong `tests/e2e/results/`.
- Xem trình duyệt chạy chậm để theo dõi: `npm run test:watch` (PowerShell: `$env:HEADED=1; $env:SLOW=400; node run.js`).
- Kiểm tra truy cập WCAG 2 AA: `npm run a11y`.

## Biến môi trường

| Biến | Mặc định | Ý nghĩa |
|---|---|---|
| `BASE_URL` | `http://localhost/Group11_Project-Web_WarningInfo` | Địa chỉ web cần test |
| `ROOT_BASE` | `http://localhost:8080` | Bản cài ở **gốc domain** (nhóm 10 kiểm tra link CSS/JS khi không ở thư mục con). Trỏ vào cùng web nếu chỉ có 1 bản |
| `MYSQL_CMD` | `mysql -uroot` | Lệnh mysql, vd `C:/xampp/mysql/bin/mysql.exe -uroot` |
| `DB_NAME` | `warninginfo_db` | Tên CSDL |
| `PHP_BIN` | `php` | Đường dẫn PHP cho bước lint |
| `HEADED`, `SLOW` | tắt, `0` | Hiện trình duyệt, làm chậm mỗi thao tác (ms) |
| `OFFLINE_CDN`, `CDN_DIR` | tắt | Máy không ra được CDN (jsdelivr): đặt `OFFLINE_CDN=1` và `CDN_DIR` trỏ tới thư mục `node_modules` có cài `bootstrap@5.3.3 bootstrap-icons@1.11.3 leaflet@1.9.4 chart.js@4.4.1 jquery@3.7.1` |

Tài khoản dùng trong test là tài khoản mẫu của `database_init.sql` (mật khẩu `password`).
