# TRƯỜNG ĐẠI HỌC KINH TẾ TP. HỒ CHÍ MINH (UEH)
## TRƯỜNG CÔNG NGHỆ VÀ THIẾT KẾ — KHOA CÔNG NGHỆ THÔNG TIN KINH DOANH
### Học phần: Phát Triển Ứng Dụng Web
**Giảng viên hướng dẫn:** TS. Đặng Ngọc Hoàng Thành  
**Đề tài:** WarningInfo — Hệ Thống Báo Cáo & Cảnh Báo Vấn Đề Môi Trường  
**Nhóm thực hiện:** Nhóm 11  

---

# KẾ HOẠCH ĐIỀU CHỈNH TIẾN ĐỘ & PHÂN CÔNG NHIỆM VỤ CHI TIẾT
## MILESTONE 2: HOÀN THIỆN MVP & CỐT LÕI NGHIỆP VỤ (TUẦN 3 – TUẦN 5)

---

## I. ĐÁNH GIÁ TỔNG QUAN HIỆN TRẠNG (CODEBASE AUDIT)

Sau khi đối chiếu mã nguồn thực tế tại kho lưu trữ với các mốc Milestone ban đầu và yêu cầu đồ án môn học, nhóm ghi nhận tình hình thực hiện như sau:

### 1. Những phần việc đã hoàn thành xuất sắc
- **Kiến trúc hệ thống:** Xây dựng hoàn chỉnh kiến trúc MVC theo hướng đối tượng (OOP) bằng PHP thuần (`core/App.php`, `core/Controller.php`, `core/Database.php`), kết nối PDO chuẩn mực.
- **Cơ sở dữ liệu:** Thiết kế và chuẩn hóa 10 bảng CSDL quan hệ (`database_init.sql`), hỗ trợ trọn vẹn bảng mã `utf8mb4` cho tiếng Việt, có sơ đồ quan hệ thực thể ([ERD.md](file:///C:/Users/Admin/.gemini/antigravity-ide/brain/801fcc77-5bb4-4a16-ab23-ad6f96d0b17d/Group11_Project-Web_WarningInfo/database/ERD.md)).
- **Giao diện công cộng (Public):**
  - Trang chủ (`/`): Đầy đủ banner Hero, khối thống kê số liệu động, 7 danh mục sự cố, danh sách sự cố mới nhất, quy trình 4 bước gửi báo cáo.
  - Trang danh sách cảnh báo (`/report`): Hỗ trợ tìm kiếm từ khóa, lọc theo danh mục, lọc theo mức độ nghiêm trọng; hiển thị thẻ card kèm huy hiệu trạng thái.
  - Trang chi tiết sự cố (`/report/detail/{id}`): Hiển thị chi tiết nội dung, nút Upvote AJAX và khu vực bình luận tương tác.
- **Xác thực & Quản trị cơ bản:**
  - Đăng ký và đăng nhập bảo mật với băm mật khẩu `password_hash` (`bcrypt`), quản lý phiên (`$_SESSION`).
  - Trang quản trị Admin (`/admin`): Dashboard thống kê, danh sách sự cố, đổi trạng thái sự cố, quản lý người dùng và danh mục sự cố.

### 2. Các điểm lệch tiến độ & Thiếu sót cần khắc phục ngay trong Milestone 2
- **Chưa có tính năng Upload ảnh minh chứng thực tế:** Form gửi báo cáo hiện tại chưa xử lý lưu file ảnh tải lên máy chủ và chưa ghi vào bảng `report_images`.
- **Chưa có cơ chế định vị GPS / Bản đồ tương tác:** Form gửi báo cáo mới có 2 trường nhập tọa độ thủ công, chưa có nút bấm lấy tọa độ tự động qua GPS của trình duyệt.
- **Thiếu phân hệ "Sự cố của tôi" (User Dashboard):** Người dân sau khi gửi báo cáo chưa có trang để theo dõi lại danh sách các sự cố do mình đã phản ánh và tiến độ xử lý của cơ quan chức năng.
- **Thiếu tài liệu báo cáo học thuật theo chuẩn mẫu UEH:** Cần tài liệu hóa các chương 1, 2, 3, 4, 5 theo đề cương của thầy TS. Đặng Ngọc Hoàng Thành (Use Case, Sơ đồ lớp, quy trình thực thi).
- **Phân định công việc trên GitHub:** Cần phân chia nhiệm vụ rành mạch cho từng cá nhân thông qua GitHub Issues và nhánh riêng (`feature/...`), tránh làm chung chung.

---

## II. MỤC TIÊU CỐT LÕI CỦA MILESTONE 2 (TUẦN 3 – TUẦN 5)

1. **Về sản phẩm (Product):** Hoàn thiện 100% luồng nghiệp vụ tương tác của hệ thống (Người dân gửi báo cáo kèm ảnh thật + vị trí -> Admin tiếp nhận duyệt bài -> Cập nhật trạng thái kèm thông báo -> Người dân theo dõi phản hồi).
2. **Về kỹ thuật (Technical):** Đảm bảo chuẩn MVC OOP, kiểm soát dữ liệu đầu vào (Validation), xử lý upload file an toàn.
3. **Về báo cáo môn học (Report):** Hoàn thiện bản thảo Chương 1, Chương 2, Chương 3 và Chương 4 của cuốn Báo cáo đồ án.
4. **Về quản lý dự án (Project Management):** Toàn bộ 5 thành viên đều có Issue, Branch và Pull Request độc lập trên GitHub để chụp ảnh làm minh chứng phụ lục.

---

## III. BẢNG TỔNG HỢP PHÂN CÔNG NHIỆM VỤ MILESTONE 2

| STT | Thành viên | Vai trò | Nhánh Git | Module phụ trách | Thời hạn | Sản phẩm bàn giao |
|:---:|:---|:---|:---|:---|:---:|:---|
| **1** | **Khiếu Hoàng Nam Anh** (TV1) | **Team Lead & Backend Core** | `feature/tv1-auth-profile` | • User Profile & Báo cáo của tôi<br>• Phân quyền Session Middleware<br>• Quản trị dự án & Review PR | Tuần 5 | • Trang `/auth/profile`<br>• Trang `/report/my_reports`<br>• Bảng GitHub Project Milestone 2 |
| **2** | **Trần Yến Phượng** (TV2) | **Backend Admin & Workflow** | `feature/tv2-admin-workflow` | • Luồng duyệt báo cáo nâng cao<br>• Lịch sử trạng thái sự cố<br>• Cơ chế tạo thông báo tự động | Tuần 5 | • Action duyệt sự cố trong Admin<br>• Tự động ghi `report_status_history`<br>• Tạo thông báo `notifications` |
| **3** | **Lê Tô Nguyệt Minh** (TV3) | **Backend Features & Media** | `feature/tv3-upload-api` | • Xử lý Upload file ảnh minh chứng<br>• Tương tác bảng `report_images`<br>• API JSON WebService ban đầu | Tuần 5 | • Thư mục lưu trữ `public/uploads/reports/`<br>• Code upload an toàn trong Controller<br>• Endpoint `GET /report/api_list` |
| **4** | **Lê Trọng Hiếu** (TV4) | **Frontend UI/UX & Responsive** | `feature/tv4-ui-report-form` | • Form gửi báo cáo (Xem trước ảnh + GPS)<br>• Hiển thị gallery ảnh tại trang chi tiết<br>• Tối ưu giao diện Mobile | Tuần 5 | • UI xem trước ảnh (Image preview)<br>• Nút "Lấy GPS hiện tại"<br>• Bộ ảnh chụp màn hình Mobile |
| **5** | **Nguyễn Thế Luân** (TV5) | **Frontend Admin, QA & Docs** | `feature/tv5-admin-docs` | • Modal xem chi tiết sự cố trong Admin<br>• Soạn thảo Báo cáo đồ án UEH (Chương 1-4)<br>• Kiểm thử chéo hệ thống (QA) | Tuần 5 | • Giao diện modal duyệt bài Admin<br>• File Word bản thảo Báo cáo Đồ án<br>• Danh sách Issue bug trên GitHub |

---

## IV. BẢNG MÔ TẢ CHI TIẾT NHIỆM VỤ TỪNG THÀNH VIÊN

### 1. Thành viên 1: Khiếu Hoàng Nam Anh (TV1 — Team Lead & Backend Core)
- **Nhiệm vụ chi tiết:**
  1. **Khởi tạo và điều phối GitHub Project:** Tạo 5 thẻ Issue trên GitHub Projects tương ứng với công việc của từng bạn, thiết lập nhãn (`area:backend`, `area:frontend`, `area:docs`, `milestone:milestone-2`).
  2. **Trang Quản lý thông tin cá nhân (`/auth/profile`):**
     - Cho phép người dùng cập nhật thông tin: Họ và tên, số điện thoại liên hệ.
     - Cho phép đổi mật khẩu (nhập mật khẩu hiện tại, mật khẩu mới, xác nhận mật khẩu mới có kiểm tra bảo mật).
  3. **Trang Báo cáo của tôi (`/report/my_reports`):**
     - Hiển thị bảng danh sách toàn bộ các sự cố do tài khoản hiện tại đã gửi.
     - Hiển thị rõ trạng thái xử lý từng bước: *Mới tiếp nhận ➔ Đang xác minh ➔ Đang xử lý ➔ Đã khắc phục (hoặc Từ chối)*.
  4. **Bảo mật và phân quyền:**
     - Hoàn thiện hàm kiểm tra quyền hạn (`requireLogin()`, `requireAdmin()`) để ngăn chặn triệt để người dùng chưa đăng nhập hoặc không phải admin truy cập trái phép.
  5. **Review mã nguồn:** Kiểm tra và hợp nhất (merge) các Pull Request từ TV2, TV3, TV4, TV5 vào nhánh `develop`.
- **Tiêu chí nghiệm thu (Acceptance Criteria):**
  - Người dùng đăng nhập vào xem và cập nhật được thông tin cá nhân.
  - Người dùng xem được danh sách lịch sử phản ánh của chính mình.
  - Bảng GitHub Projects hiển thị đầy đủ tiến độ minh chứng.

---

### 2. Thành viên 2: Trần Yến Phượng (TV2 — Backend Developer Admin & Workflow)
- **Nhiệm vụ chi tiết:**
  1. **Nâng cấp luồng duyệt sự cố tại `AdminController`:**
     - Xây dựng chức năng chuyển đổi trạng thái sự cố có ghi chú chi tiết (`resolution_note`).
     - Tự động ghi bản ghi vào bảng `report_status_history` gồm: `report_id`, `old_status`, `new_status`, `changed_by`, `note`, `changed_at`.
     - Xử lý phân loại sự cố hợp lệ và từ chối báo cáo spam/sai sự thật (`rejected`).
  2. **Cơ chế thông báo tự động (`notifications`):**
     - Mỗi khi sự cố chuyển trạng thái (ví dụ từ *Mới tiếp nhận* sang *Đang xử lý* hoặc *Đã giải quyết*), tự động chèn 1 bản ghi thông báo gửi đến `user_id` của người gửi báo cáo.
  3. **Phân công cán bộ xử lý (`assignments`):**
     - Cho phép admin chọn một cán bộ (tài khoản role `officer` hoặc `admin`) phụ trách giải quyết sự cố, lưu vào bảng `assignments`.
- **Tiêu chí nghiệm thu (Acceptance Criteria):**
  - Mọi thao tác thay đổi trạng thái sự cố đều được lưu vết đầy đủ trong bảng `report_status_history`.
  - Người gửi nhận được thông báo trạng thái mới trong CSDL.

---

### 3. Thành viên 3: Lê Tô Nguyệt Minh (TV3 — Backend Developer Media & WebService)
- **Nhiệm vụ chi tiết:**
  1. **Xây dựng Module Upload file ảnh/video:**
     - Tạo thư mục `public/uploads/reports/` và cấp quyền ghi.
     - Xây dựng hàm kiểm tra an toàn: Cho phép các đuôi tệp `.jpg`, `.jpeg`, `.png`, `.webp`; kích thước tối đa 5MB/ảnh; số lượng tối đa 3 ảnh/lần gửi.
     - Sinh tên file ngẫu nhiên chống trùng lặp: `md5(uniqid()) . '.' . $ext`.
     - Lưu thông tin tệp vào bảng `report_images` (`report_id`, `file_path`, `mime_type`, `file_size_bytes`).
  2. **Cập nhật hiển thị ảnh trong `ReportModel`:**
     - Cập nhật hàm `getById($id)` để truy vấn danh sách ảnh đính kèm từ bảng `report_images`.
     - Cập nhật hàm `getAll()` để lấy ảnh đại diện đầu tiên của mỗi báo cáo.
  3. **Xây dựng API WebService cơ sở:**
     - Viết endpoint `GET /report/api_list` trả về dữ liệu danh sách sự cố dưới định dạng chuẩn JSON (phục vụ lấy điểm phần WebService/AJAX cho Milestone 3).
- **Tiêu chí nghiệm thu (Acceptance Criteria):**
  - Upload được ảnh thật khi gửi báo cáo, file được lưu vào thư mục `public/uploads/reports/`.
  - Dữ liệu ảnh liên kết chính xác trong CSDL và hiển thị được trên web.
  - Endpoint API trả về JSON hợp lệ với header `Content-Type: application/json`.

---

### 4. Thành viên 4: Lê Trọng Hiếu (TV4 — Frontend Developer UI/UX & Responsive)
- **Nhiệm vụ chi tiết:**
  1. **Nâng cấp giao diện Form gửi báo cáo (`reports/create.php`):**
     - Thêm trường input upload ảnh có chức năng **Xem trước hình ảnh (Image Preview)** bằng JavaScript trước khi bấm gửi.
     - Thêm nút bấm **"📍 Lấy tọa độ GPS tự động"**: Sử dụng API `navigator.geolocation` của trình duyệt để tự động lấy và điền chính xác vĩ độ (`latitude`) và kinh độ (`longitude`).
  2. **Nâng cấp giao diện chi tiết sự cố (`reports/detail.php`):**
     - Thiết kế khu vực hiển thị thư viện ảnh (Photo Gallery) chuyên nghiệp dạng lưới (grid) hoặc carousel kèm hiệu ứng phóng to ảnh khi click.
  3. **Tối ưu hóa Responsive trên Mobile (Theo chuẩn Chương 3 Báo cáo UEH):**
     - Kiểm tra và tinh chỉnh giao diện trên các kích thước màn hình điện thoại (375px, 414px, 768px).
     - Đảm bảo thanh điều hướng (navbar toggler), bảng dữ liệu và thẻ card hiển thị đẹp mắt, không bị tràn khung hay bể chữ.
     - Chụp ảnh màn hình các trang trên giao diện Mobile để lưu vào thư mục `docs/screenshots/mobile/`.
- **Tiêu chí nghiệm thu (Acceptance Criteria):**
  - Người dùng chọn ảnh là thấy ảnh xem trước ngay lập tức.
  - Nút lấy GPS hoạt động chính xác khi người dùng cấp quyền vị trí.
  - Giao diện đạt chuẩn Responsive 100% trên thiết bị di động.

---

### 5. Thành viên 5: Nguyễn Thế Luân (TV5 — Frontend Admin, QA & Tài liệu Báo cáo)
- **Nhiệm vụ chi tiết:**
  1. **Nâng cấp giao diện trang Admin (`admin/reports/index.php`):**
     - Thiết kế Modal Bootstrap xem nhanh chi tiết sự cố và bộ ảnh minh chứng ngay trên bảng quản lý mà không cần chuyển trang.
     - Tích hợp form cập nhật trạng thái có ô nhập lý do/ghi chú xử lý trực quan.
  2. **Biên soạn Báo cáo Đồ án theo mẫu chuẩn của UEH:**
     - Thiết lập file Báo cáo Word theo chuẩn mục lục 6 chương của TS. Đặng Ngọc Hoàng Thành.
     - Soạn thảo **Chương 1 (Tổng quan)**: Giới thiệu tính cấp thiết của đề tài bảo vệ môi trường, phân tích các công nghệ đã áp dụng (HTML5, CSS3, Bootstrap 5, PHP OOP, MySQL, Web Server Apache).
     - Soạn thảo **Chương 2 (Phân tích & Thiết kế hệ thống)**: Vẽ sơ đồ công việc (Workflow Diagram), sơ đồ phân rã chức năng (Business Functional Diagram) và sơ đồ Use Case.
     - Soạn thảo **Chương 4 (Xây dựng CSDL)**: Đưa cấu trúc 10 bảng dữ liệu, sơ đồ quan hệ ERD và các câu lệnh truy vấn mẫu vào báo cáo.
  3. **Kiểm thử chất lượng (QA Testing):**
     - Đóng vai trò người dùng thực hiện toàn bộ quy trình: Đăng ký ➔ Đăng nhập ➔ Gửi báo cáo kèm ảnh ➔ Upvote ➔ Bình luận.
     - Kiểm thử tài khoản admin: Đăng nhập admin ➔ Duyệt bài ➔ Đổi trạng thái ➔ Quản lý danh mục.
     - Ghi nhận các lỗi phát sinh (nếu có) lên mục Issues của GitHub.
- **Tiêu chí nghiệm thu (Acceptance Criteria):**
  - Giao diện Admin có modal duyệt sự cố tiện lợi.
  - Bản thảo Báo cáo Đồ án hoàn thành Chương 1, 2, 4 đúng quy chuẩn hình thức của UEH.

---

## V. QUY TRÌNH PHỐI HỢP & QUẢN LÝ MÃ NGUỒN TRÊN GITHUB

Để đảm bảo minh chứng làm việc nhóm đạt điểm tối đa theo barem đánh giá của giảng viên:

1. **Chiến lược phân nhánh (Branching Strategy):**
   - Không được push trực tiếp lên nhánh `main` hoặc `develop`.
   - Mỗi bạn tạo nhánh từ `develop` theo cú pháp: `feature/<ma-thanh-vien>-<ten-tinh-nang>`.
     - Ví dụ: `feature/tv1-auth-profile`, `feature/tv2-admin-workflow`, `feature/tv3-upload-api`, v.v.
2. **Quy chuẩn ghi chú Commit (Conventional Commits):**
   - `feat(...)`: Thêm tính năng mới (VD: `feat(upload): xu ly upload anh minh chung cho bao cao`).
   - `fix(...)`: Sửa lỗi phát sinh (VD: `fix(auth): sua loi xac thuc doi mat khau`).
   - `docs(...)`: Cập nhật tài liệu báo cáo (VD: `docs(report): bo sung chuong 1 va 2 vao bao cao`).
3. **Quy trình duyệt Pull Request (PR):**
   - Khi hoàn thành tính năng, tạo PR hướng về nhánh `develop`.
   - Gán Team Lead (TV1) làm Reviewer. Sau khi test hoạt động ổn định trên máy mới tiến hành merge vào `develop`.
4. **Lưu trữ minh chứng đồ án:**
   - Định kỳ chụp ảnh màn hình hoạt động thảo luận nhóm (Zalo/Teams/Meet), lịch sử commit GitHub và bảng Kanban GitHub Projects để đưa vào phần **Phụ lục** của cuốn Báo cáo đồ án.

---
*Tài liệu được lập và phê duyệt bởi Nhóm 11 — Đồ án Phát Triển Ứng Dụng Web — UEH.*  
*Ngày lập: Tháng 10/2026.*
