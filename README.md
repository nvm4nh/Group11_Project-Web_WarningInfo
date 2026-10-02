# 🌍 WarningInfo — Hệ Thống Báo Cáo & Cảnh Báo Vấn Đề Môi Trường
  # Tổng quan dự án & Phân công nhiệm vụ 

## 1. Khái quát chung về dự án

**Tên đề tài:** [Điền tên đề tài, VD: Xây dựng Website Thương Mại Điện Tử bán đồ công nghệ / Mạng xã hội / Trang tin tức...]

### 1.1. Mục tiêu dự án
Xây dựng một ứng dụng web hoàn chỉnh áp dụng các kiến thức cốt lõi của môn học, bao gồm thiết kế giao diện với Bootstrap, lập trình Backend Hướng đối tượng (OOP) theo mô hình MVC và ứng dụng AJAX/WebService để tối ưu hóa trải nghiệm người dùng.

### 1.2. Công nghệ sử dụng & Môi trường phát triển
- **Frontend:** HTML5, CSS3, JavaScript, jQuery, Framework Bootstrap 5.
- **Backend:** Ngôn ngữ lập trình PHP (Viết theo Hướng đối tượng - OOP).
- **Cơ sở dữ liệu:** Hệ quản trị CSDL MySQL.
- **Giao tiếp dữ liệu:** AJAX, WebService (truyền tải dữ liệu qua định dạng JSON/XML).
- **Môi trường Local:** XAMPP hoặc Laragon (Apache, MySQL).
- **Công cụ Quản lý & Làm việc nhóm:** Git & GitHub (Quản lý mã nguồn), GitHub Projects / Microsoft Teams (Quản lý tiến độ).
- **Triển khai (Deployment):** Shared Host / Free Hosting.

### 1.3. Kiến trúc hệ thống
- Hệ thống được thiết kế theo mô hình **Client - Server**.
- Backend được xây dựng tuân thủ chặt chẽ mô hình **MVC (Model - View - Controller)**.
  - **Model:** Xử lý các thao tác tương tác với Database (CRUD).
  - **View:** Hiển thị giao diện người dùng, sử dụng Bootstrap.
  - **Controller:** Tiếp nhận Request, gọi Model xử lý dữ liệu và trả về View tương ứng.
- Tích hợp mô hình bất đồng bộ: Client sử dụng **AJAX** để gọi các endpoint API/WebService của hệ thống, xử lý cập nhật giao diện mà không cần tải lại toàn bộ trang.

### 1.4. Các tính năng nghiệp vụ cốt lõi
*(Lưu ý: Bạn có thể điều chỉnh lại danh sách này tùy theo chủ đề bạn chọn là TMĐT, Tin tức, Mạng xã hội hay Diễn đàn)*
- **Dành cho Người dùng (Public/Client):**
  - Xem danh sách, chi tiết thông tin (Sản phẩm / Bài viết).
  - Tìm kiếm và Lọc dữ liệu nâng cao (Sử dụng AJAX).
  - Tương tác: Thêm vào Giỏ hàng / Đăng bình luận / Upvote / Đánh giá (Sử dụng AJAX).
  - Xác thực: Đăng ký, Đăng nhập, Quản lý tài khoản cá nhân.
- **Dành cho Quản trị viên (Admin):**
  - Đăng nhập vào trang Quản trị (Dashboard).
  - Quản lý nội dung: Thêm, sửa, xóa, duyệt (Danh mục, Sản phẩm, Bài viết).
  - Quản lý người dùng và phân quyền.
  - Theo dõi thống kê, đơn hàng hoặc báo cáo.

---

## 2. Bảng phân công vai trò và nhiệm vụ

| STT | Thành viên | Vai trò chính | Trách nhiệm chi tiết |
|:---:|:---|:---|:---|
| **TV1** | **Khiếu Hoàng Nam Anh** | **Team Lead & System Architect** | Quản lý dự án trên GitHub/Teams, thiết kế CSDL (ERD), thiết lập kiến trúc **OOP + MVC** cho Backend cơ bản. Đảm nhiệm việc tạo Shared Host và **Deploy website**. |
| **TV2** | **Trần Yến Phượng** | **Backend Developer (Core & Admin)** | Xử lý logic Backend cốt lõi: Xác thực người dùng (Auth/Login/Register), xây dựng các Controller/Model cho **Phần quản trị (Admin)** (Quản lý danh mục, user, v.v.). |
| **TV3** | **Lê Tô Nguyệt Minh** | **Backend Developer (Features & WebService)** | Xây dựng logic cho các tính năng chính (Sản phẩm/Bài viết, Giỏ hàng/Bình luận). Tạo các **WebService trả về dữ liệu XML/JSON** để phục vụ cho các tính năng gọi bằng AJAX. |
| **TV4** | **Lê Trọng Hiếu** | **Frontend Developer (UI/UX & Public)** | Sử dụng **Bootstrap** cắt HTML/CSS để xây dựng giao diện các trang Public (Trang chủ, Chi tiết, Đăng ký/Đăng nhập...). Đảm bảo giao diện Responsive chuẩn trên mọi thiết bị. |
| **TV5** | **Nguyễn Thế Luân** | **Frontend Admin, AJAX & QA** | Thiết kế giao diện trang Quản trị (Admin). Chịu trách nhiệm viết script gọi **AJAX** (tương tác không tải lại trang) từ Frontend gọi xuống Backend. **Kiểm thử (Testing)** và tổng hợp viết Báo cáo. |

---

## 3. Lộ trình thực hiện chi tiết (Milestones & Issues) trong 10 tuần

Dự án được chia thành 4 Milestones bám sát theo các chương trong mẫu báo cáo đồ án:

### 🎯 Milestone 1: Khảo sát, Thiết kế & Khởi tạo (Tuần 1 – 2)
> *Giai đoạn này phục vụ cho Chương 2 & 4 trong mẫu Báo cáo.*

- [ ] **[TV1]** Khởi tạo Repository trên GitHub, tạo nhánh `main` và `develop`, phân quyền và mời các thành viên. Set up môi trường code MVC ban đầu.
- [ ] **[TV1, TV2]** Phân tích và thiết kế Sơ đồ Cơ sở dữ liệu (ERD) và các Bảng dữ liệu.
- [ ] **[TV4]** Vẽ Wireframe / Thiết kế UI nháp cho các trang chính.
- [ ] **[TV5]** Viết đặc tả yêu cầu, vẽ Sơ đồ Use Case và chuẩn bị cấu trúc file Báo cáo Word.

### 🎯 Milestone 2: Xây dựng Backend Core & Giao diện tĩnh (Tuần 3 – 5)
> *Giai đoạn này phục vụ cho Chương 3 & 5 trong mẫu Báo cáo.*

- [ ] **[TV4]** Hoàn thiện HTML/CSS/Bootstrap cho các trang: Trang chủ, Danh sách, Chi tiết, Giỏ hàng/Tìm kiếm.
- [ ] **[TV5]** Hoàn thiện HTML/CSS/Bootstrap cho Dashboard của trang Quản trị (Admin).
- [ ] **[TV2]** Viết code Backend kết nối CSDL, làm chức năng Đăng nhập/Đăng ký và CRUD danh mục, user trong Admin.
- [ ] **[TV3]** Viết code Backend các chức năng thêm/sửa/xóa Sản phẩm/Bài viết và xử lý Upload hình ảnh.

### 🎯 Milestone 3: Tích hợp, Tính năng nâng cao & AJAX (Tuần 6 – 8)
> *Tiêu chí lấy điểm cao (AJAX/WebService).*

- [ ] **[TV3]** Cấu hình các endpoint WebService xuất dữ liệu ra định dạng JSON/XML.
- [ ] **[TV5]** Viết JavaScript/jQuery sử dụng AJAX để gọi WebService từ TV3 (VD: Lọc sản phẩm không load lại trang, Thêm vào giỏ hàng bằng AJAX, Load thêm bình luận).
- [ ] **[TV4]** Gắn dữ liệu động (PHP/Backend) vào giao diện trang Public, tinh chỉnh hiệu ứng UI, đảm bảo validation form đầy đủ.
- [ ] **[TV2]** Hoàn thiện và phân quyền chặt chẽ trong trang Quản trị (Chỉ Admin mới vào được).

### 🎯 Milestone 4: Kiểm thử, Báo cáo & Triển khai (Tuần 9 – 10)
> *Giai đoạn này phục vụ cho Chương 6 & Phụ lục trong mẫu Báo cáo.*

- [ ] **[TV5 (QA)]** Chạy thử (Testing) toàn bộ luồng chức năng, bắt lỗi (bug) và log lên GitHub Issues.
- [ ] **[TV2, TV3, TV4]** Fix lỗi (Bug fixing) dựa trên báo cáo của QA.
- [ ] **[TV1]** Đăng ký Free Hosting (hoặc Shared Host), import Database và **Deploy website** lên mạng.
- [ ] **[Cả nhóm]** Chụp ảnh minh chứng GitHub (branch, issue), Microsoft Teams, màn hình chức năng để nhúng vào cuốn Báo cáo tổng kết theo chuẩn mẫu của trường.
