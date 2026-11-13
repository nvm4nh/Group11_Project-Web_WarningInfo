# TRƯỜNG ĐẠI HỌC KINH TẾ TP. HỒ CHÍ MINH (UEH)
## TRƯỜNG CÔNG NGHỆ VÀ THIẾT KẾ – KHOA CÔNG NGHỆ THÔNG TIN KINH DOANH
### Học phần: Phát Triển Ứng Dụng Web
**Giảng viên hướng dẫn:** TS. Đặng Ngọc Hoàng Thành  
**Đề tài:** WarningInfo – Hệ Thống Báo Cáo & Cảnh Báo Vấn Đề Môi Trường Đô Thị  
**Nhóm thực hiện:** Nhóm 11  
**GitHub Repository:** [nvm4nh/Group11_Project-Web_WarningInfo](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo)  

---

# KẾ HOẠCH TRIỂN KHAI & PHÂN CÔNG NHIỆM VỤ GITHUB
## MILESTONE 1: KHẢO SÁT, THIẾT KẾ CSDL & KHỞI TẠO KHUNG MVC (TUẦN 1 – TUẦN 2)

> **GitHub Issues:** [#53 – #57](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/milestone/1?closed=1) trong Milestone 1.

---

## I. BẢNG PHÂN CÔNG NHIỆM VỤ TỔNG QUAN

| STT | Thành viên | Nhóm công việc | Sản phẩm bàn giao |
|:---:|:---|:---|:---|
| **1** | **Khiếu Hoàng Nam Anh** (TV1 – Team Lead) | Quản lý & khởi tạo môi trường | Repository, `.gitignore`, nhánh `develop`, rule bảo vệ `main`, Kanban Board, nhãn (labels), khung thư mục MVC, tài khoản hosting thử nghiệm |
| **1 & 2** | **Khiếu Hoàng Nam Anh** (TV1) & **Trần Yến Phượng** (TV2) | Thiết kế cơ sở dữ liệu | Danh sách thực thể, sơ đồ ERD, script tạo bảng và khóa, tệp `database/database_init.sql` |
| **3** | **Lê Tố Nguyệt Minh** (TV3) | Chuẩn bị dữ liệu & nghiên cứu WebService | Rà soát ERD, dữ liệu mẫu trong `database_init.sql`, file PHP thử nghiệm trả JSON |
| **4** | **Lê Trọng Hiếu** (TV4) | Thiết kế wireframe & UI nháp | Danh sách trang public, wireframe các trang chính, chốt Bootstrap 5, bảng màu và font |
| **5** | **Nguyễn Thế Luân** (TV5) | Đặc tả hệ thống & báo cáo | File Word báo cáo theo mẫu, sơ đồ Use Case, đặc tả yêu cầu (SRS), bản thảo Chương 2, ảnh minh chứng GitHub |

---

## II. CHI TIẾT NHIỆM VỤ TỪNG THÀNH VIÊN

### 👷‍♂️ Thành viên 1 (TV1 – Team Lead): Quản lý & Khởi tạo Môi trường
- [ ] **Task 1:** Tạo Repository trên GitHub. Thêm file `.gitignore` (bỏ qua các thư mục rác).
- [ ] **Task 2:** Vào *Settings > Branches*, thiết lập rule bảo vệ nhánh `main` (không cho push trực tiếp, bắt buộc phải tạo Pull Request). Tạo thêm nhánh `develop`.
- [ ] **Task 3:** Mời 4 thành viên (TV2, TV3, TV4, TV5) tham gia vào Repository.
- [ ] **Task 4:** Tạo Kanban Board (GitHub Projects) với các cột: *To do, In Progress, Review, Done*. Tạo sẵn các nhãn (Labels) như `frontend`, `backend`, `database`, `docs`.
- [ ] **Task 5:** Tạo cấu trúc thư mục code chuẩn MVC ban đầu (ví dụ: `models/`, `views/`, `controllers/`, `public/`, `assets/`, `config/`). Push đoạn code khung này lên nhánh `develop`.
- [ ] **Task 6:** Tìm hiểu và đăng ký thử 1 tài khoản Free Hosting (như InfinityFree, 000webhost...) hoặc Shared Host để chuẩn bị trước.

### 🗄️ Thành viên 1 & 2 (TV1, TV2): Thiết kế Cơ sở dữ liệu (Database)
- [ ] **Task 1:** Chốt danh sách các thực thể chính dựa trên đề tài.
- [ ] **Task 2:** Vẽ sơ đồ Quan hệ thực thể (ERD) bằng công cụ dbdiagram.io, draw.io hoặc MySQL Workbench. (Lưu hình ảnh độ phân giải cao để TV5 đưa vào Báo cáo.)
- [ ] **Task 3:** Viết script SQL (hoặc dùng phpMyAdmin) để tạo Database, tạo các bảng, thiết lập Khóa chính (Primary Key), Khóa ngoại (Foreign Key).
- [ ] **Task 4:** Xuất CSDL ra file `database_init.sql`, lưu vào thư mục `database/` và push lên GitHub để các bạn khác kéo về máy chạy.

### ⚙️ Thành viên 3 (TV3): Chuẩn bị Dữ liệu & Nghiên cứu WebService
*(Vì TV3 chịu trách nhiệm chính về WebService/AJAX ở Milestone 3, nên tuần 1 – 2 cần chuẩn bị trước.)*
- [ ] **Task 1:** Cùng rà soát lại sơ đồ ERD của TV1 & TV2 xem cấu trúc bảng đã đủ dữ liệu để trả về API/JSON sau này chưa.
- [ ] **Task 2:** Soạn sẵn bộ dữ liệu giả (Dummy Data) khoảng 10 – 20 dòng cho mỗi bảng và thêm vào file `database_init.sql`, giúp cả nhóm có dữ liệu để code ngay khi ráp giao diện.
- [ ] **Task 3:** Nghiên cứu và tạo 1 file code nháp (test) bằng PHP để lấy dữ liệu từ MySQL và in ra màn hình dưới dạng chuẩn JSON.

### 🎨 Thành viên 4 (TV4): Thiết kế Wireframe & UI Nháp
- [ ] **Task 1:** Lên danh sách toàn bộ các trang cần có bên phía giao diện Người dùng (Public).
- [ ] **Task 2:** Sử dụng Figma hoặc Balsamiq vẽ Wireframe (bản phác thảo khung) cho các trang quan trọng nhất: Trang chủ, Chi tiết sự cố, Đăng nhập/Đăng ký.
- [ ] **Task 3:** Chốt thư viện CSS sẽ dùng (Bootstrap 5) và chọn bảng màu chủ đạo, font chữ (Google Fonts).
- [ ] **Task 4:** Xuất (Export) các hình ảnh Wireframe gửi cho TV5 để tổng hợp vào báo cáo.

### 📝 Thành viên 5 (TV5): Đặc tả Hệ thống & Báo cáo
- [ ] **Task 1:** Tạo file Word Báo cáo theo đúng chuẩn mẫu giảng viên cung cấp (làm sẵn trang bìa, mục lục).
- [ ] **Task 2:** Vẽ Sơ đồ Use Case tổng quát cho hệ thống (chia 2 Actor chính là User và Admin).
- [ ] **Task 3:** Liệt kê Đặc tả yêu cầu phần mềm (SRS), mô tả các chức năng phần mềm dưới dạng văn bản.
- [ ] **Task 4:** Thu thập sơ đồ ERD (từ TV1, TV2), Wireframe (từ TV4), sơ đồ Use Case và viết dần vào Chương 2 (Phân tích & Thiết kế hệ thống) của Báo cáo.
- [ ] **Task 5 (Quan trọng):** Chụp màn hình kho GitHub của nhóm, bảng Kanban Board (để minh chứng có dùng công cụ quản lý) và dán vào Báo cáo.

---

## III. KẾT QUẢ BÀN GIAO TRONG REPOSITORY

| Sản phẩm | Vị trí trong repo |
|:---|:---|
| Khung MVC (`core/App.php`, `core/Controller.php`, `core/Database.php`) | `core/`, `app/`, `public/`, `config/` |
| CSDL 10 bảng kèm dữ liệu mẫu (5 tài khoản, 7 danh mục, 5 báo cáo) | `database/database_init.sql` |
| Wireframe các trang chính | `docs/wireframe/` |
| Bản thảo báo cáo (Use Case, ERD, đặc tả) | `docs/BAO_CAO_DO_AN_WARNINGINFO_MILESTONE_2.md` |

---
*Tài liệu được lập bởi Nhóm 11 — Đồ án Phát Triển Ứng Dụng Web — UEH. Nội dung lấy từ trang phân công nhóm trên Google Docs.*
