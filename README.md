# 🌍 WarningInfo – Hệ Thống Báo Cáo & Cảnh Báo Vấn Đề Môi Trường Đô Thị

> **TRƯỜNG ĐẠI HỌC KINH TẾ TP. HỒ CHÍ MINH (UEH)**  
> **TRƯỜNG CÔNG NGHỆ VÀ THIẾT KẾ – KHOA CÔNG NGHỆ THÔNG TIN KINH DOANH**  
> **Học phần:** Phát Triển Ứng Dụng Web  
> **Giảng viên hướng dẫn:** TS. Đặng Ngọc Hoàng Thành  
> **Nhóm thực hiện:** Nhóm 11  
> **GitHub Repository:** [nvm4nh/Group11_Project-Web_WarningInfo](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo)

---

## 📑 MỤC LỤC
1. [Khái quát chung về Dự án](#1-khái-quát-chung-về-dự-án)
2. [Công nghệ sử dụng & Kiến trúc hệ thống](#2-công-nghệ-sử-dụng--kiến-trúc-hệ-thống)
3. [Bảng phân công vai trò Nhóm 11](#3-bảng-phân-công-vai-trò-nhóm-11)
4. [Lộ trình triển khai & Chi tiết Milestone 3](#4-lộ-trình-triển-khai--chi-tiết-milestone-3)
5. [Đặc tả Hệ thống WebService & RESTful API](#5-đặc-tả-hệ-thống-webservice--restful-api)
6. [Hướng dẫn Cài đặt & Chạy ứng dụng](#6-hướng-dẫn-cài-đặt--chạy-ứng-dụng)
7. [Hướng dẫn Trải nghiệm & Kiểm thử tính năng](#7-hướng-dẫn-trải-nghiệm--kiểm-thử-tính-năng)

---

## 1. Khái quát chung về Dự án

### 1.1. Bối cảnh & Mục tiêu đề tài
Trước các thách thức về ô nhiễm môi trường, điểm tập kết rác tự phát, ngập úng và hư hỏng hạ tầng đô thị tại TP.HCM, dự án **WarningInfo** được xây dựng như một nền tảng cầu nối tương tác số giữa **Người dân (Citizen)**, **Cán bộ hiện trường (Officer)** và **Ban quản lý đô thị (Admin)**:
- Cho phép người dân gửi báo cáo phản ánh hiện trường kèm đa ảnh chụp minh chứng và tọa độ GPS định vị tức thời.
- Trực quan hóa dữ liệu sự cố trên **Bản đồ tương tác Leaflet.js (OpenStreetMap)**, hiển thị bán kính sự cố quanh vị trí người dùng.
- Tự động hóa quy trình phân công cán bộ xử lý, cập nhật tiến độ biên bản hiện trường và gửi thông báo real-time qua AJAX.
- Cung cấp hệ thống **WebService RESTful API (JSON/XML)** phục vụ tích hợp liên thông dữ liệu.
- Trang bị bảng điều hành **Admin Dashboard** với biểu đồ phân tích trực quan (**Chart.js**) và chức năng trích xuất dữ liệu (**Export CSV UTF-8**).

---

## 2. Công nghệ sử dụng & Kiến trúc hệ thống

### 2.1. Ngăn xếp Công nghệ (Tech Stack)
- **Frontend:** HTML5, CSS3, JavaScript (ES6+), Bootstrap 5.3, Bootstrap Icons.
- **Thư viện mở rộng:**
  - **Leaflet.js 1.9.4 & OpenStreetMap:** Bản đồ số tương tác, định vị GPS, GeoJSON renderer, công thức Haversine tính bán kính.
  - **Chart.js:** Trực quan hóa dữ liệu thống kê (Biểu đồ tròn Doughnut, Biểu đồ cột Bar chart, Biểu đồ đường Line chart).
- **Backend:** Ngôn ngữ PHP 8.x thuần hướng đối tượng (OOP), tuân thủ chặt chẽ mô hình **MVC (Model - View - Controller)**.
- **Cơ sở dữ liệu:** Hệ quản trị CSDL MySQL / MariaDB (Bộ lưu trữ InnoDB, bảng quan hệ chuẩn hóa 3NF).
- **Giao tiếp bất đồng bộ:** Kỹ thuật **AJAX (Fetch API)** kết hợp **WebService RESTful API** định dạng dữ liệu chuẩn JSON / XML.
- **Bảo mật:**
  - Chống tấn công giả mạo yêu cầu chéo: **CSRF Token** tự động theo phiên cho toàn bộ form POST.
  - Chống tấn công tiêm mã độc: **PDO Prepared Statements** (chống SQL Injection) và bộ lọc `htmlspecialchars()` / XSS Filter.
  - Phân quyền người dùng 3 cấp độ: `user`, `officer`, `admin`.

### 2.2. Cấu trúc Thư mục Mã nguồn (MVC Standard)
```text
Group11_Project-Web_WarningInfo/
├── app/
│   ├── controllers/      # Bộ điều hướng xử lý logic (Report, Admin, Officer, Auth, Home...)
│   ├── models/           # Mô hình thao tác CSDL (Report, User, Category, Notification...)
│   └── views/            # Giao diện hiển thị Bootstrap (home, reports, admin, officer...)
├── config/               # Cấu hình hệ thống & kết nối CSDL (config.php)
├── core/                 # Nhân kiến trúc MVC (App, Controller, Database, CSRF helper...)
├── database/             # Tệp tin lược đồ & dữ liệu mẫu CSDL (.sql)
├── docs/                 # Tài liệu Báo cáo đồ án UEH, Kế hoạch & Ảnh chụp minh chứng
│   ├── screenshots/      # Ảnh chụp giao diện Responsive Mobile / Tablet / Desktop
│   ├── BAO_CAO_DO_AN_WARNINGINFO_MILESTONE_2.md
│   ├── CHUONG_5_BAN_DO_LEAFLET.md
│   └── KE_HOACH_PHAN_CONG_MILESTONE_3.md
├── public/               # Tài nguyên tĩnh và điểm đón yêu cầu (index.php)
│   ├── assets/css/       # Style tùy chỉnh (style.css, map.css)
│   ├── assets/js/        # Kịch bản AJAX & Bản đồ (wi-map.js, gallery.js, main.js...)
│   └── uploads/          # Thư mục lưu trữ hình ảnh minh chứng sự cố
├── README.md             # Tài liệu tổng quan dự án GitHub
└── HANDOVER.md           # Biên bản bàn giao kỹ thuật
```

---

## 3. Bảng phân công vai trò Nhóm 11

| STT | Thành viên | Mã sinh viên | GitHub Account | Vai trò & Trách nhiệm chính |
|:---:|:---|:---:|:---:|:---|
| **TV1** | **Khiếu Hoàng Nam Anh** | *Nhóm trưởng* | [@nvm4nh](https://github.com/nvm4nh) | **Team Lead & System Architect**: Thiết kế kiến trúc MVC, quản trị GitHub/Issues/PRs, phát triển tích hợp Bản đồ số Leaflet.js, API GeoJSON, Review code và QA. |
| **TV2** | **Trần Yến Phụng** | Thành viên | [@phuongtran31241022663](https://github.com/phuongtran31241022663) | **Backend Admin & Workflow**: Xây dựng API thống kê `/admin/api_statistics`, biểu đồ Chart.js, xuất báo cáo CSV UTF-8 và luồng phân công nhiệm vụ có hạn định. |
| **TV3** | **Lê Tố Nguyệt Minh** | Thành viên | [@LeMinh7706](https://github.com/LeMinh7706) | **Backend Features & WebService API**: Xây dựng hệ thống RESTful API cho danh sách sự cố, tương tác Upvote, bình luận động và chuẩn xuất dữ liệu XML Feed. |
| **TV4** | **Lê Trọng Hiếu** | Thành viên | [@hieuhamhoc313-alt](https://github.com/hieuhamhoc313-alt) | **Frontend UI/UX & Security**: Thiết kế giao diện Bản đồ `/report/map`, Marker & Popup Leaflet, giao diện Bàn làm việc cán bộ, cơ chế bảo mật chống tấn công CSRF. |
| **TV5** | **Nguyễn Thế Luân** | Thành viên | [@luannguyen35262020117](https://github.com/luannguyen35262020117) | **Frontend AJAX Integration & QA**: Phát triển script AJAX lọc sự cố không reload, tương tác Upvote/bình luận, đánh dấu thông báo và chủ trì soạn thảo Chương 5 Báo cáo UEH. |

---

## 4. Lộ trình triển khai & Chi tiết Milestone 3

### 4.1. Tổng hợp Tiến độ các Milestones

- ✅ **Milestone 1: Khảo sát, Thiết kế CSDL & Khởi tạo Khung MVC (Tuần 1 – 2)**  
  *Trạng thái: Hoàn thành 100%.* Khởi tạo Repository, thiết kế CSDL ERD, đặc tả Use Case, thiết kế Wireframe UI.
- ✅ **Milestone 2: Xây dựng Backend Core & Giao diện tĩnh (Tuần 3 – 5)**  
  *Trạng thái: Hoàn thành 100%.* Xác thực người dùng, upload ảnh minh chứng an toàn, quy trình duyệt sự cố Admin, lưu lịch sử `report_status_history`, tự động tạo `notifications`.
- 🚀 **Milestone 3: Tích hợp nâng cao, AJAX, WebService & Bản đồ tương tác (Tuần 6 – 8)**  
  *Trạng thái: Đang hoàn thiện các hạng mục tích hợp & kiểm thử.* Tiêu chuẩn hướng đến điểm 9.0 – 10.0 của UEH.
- ⏳ **Milestone 4: Kiểm thử, Hoàn thiện Báo cáo & Triển khai Hosting (Tuần 9 – 10)**  
  *Kế hoạch:* Kiểm thử tổng thể, triển khai Shared Hosting/Cloud và đóng gói báo cáo hoàn chỉnh.

---

### 4.2. Chi tiết phân công nhiệm vụ & GitHub Issues trong Milestone 3

> **Milestone trên GitHub:** [Milestone 3: Advanced Features, AJAX, WebService & Interactive Map](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/milestone/3)

| Issue # | Tiêu đề công việc | Người phụ trách | Nhánh Git | Trạng thái |
|:---:|:---|:---:|:---:|:---:|
| **[#22](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/issues/22)** | **[TV3] Xây dựng bộ WebService RESTful API (JSON/XML) cho sự cố và tương tác** | Lê Tố Nguyệt Minh | `feature/tv3-webservice-rest-api` | 🟡 In Progress |
| **[#23](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/issues/23)** | **[TV5] Tích hợp AJAX toàn diện vào giao diện Public (Lọc không reload, Upvote, Bình luận)** | Nguyễn Thế Luân | `feature/tv5-ajax-core-and-docs` | 🟡 In Progress |
| **[#24](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/issues/24)** | **[TV1 & TV4] Tích hợp Bản đồ số Leaflet.js tương tác hiển thị điểm ghim GPS** | Khiếu Hoàng Nam Anh & Lê Trọng Hiếu | `feature/tv1-system-leaflet-map` | 🟢 **Merged (#21)** |
| **[#25](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/issues/25)** | **[TV2] Xây dựng Biểu đồ thống kê Admin Dashboard (Chart.js) & Xuất dữ liệu CSV** | Trần Yến Phụng | `feature/tv2-admin-statistics-export` | 🟡 Reviewing (#18) |
| **[#26](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/issues/26)** | **[TV4] Cơ chế bảo mật CSRF Token, Hộp thông báo và Bàn làm việc cán bộ** | Lê Trọng Hiếu | `feature/tv4-csrf-notify-officer` | 🟢 **Merged (#19)** |
| **[#27](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/issues/27)** | **[TV5 & TV1] Soạn thảo Chương 5 Báo cáo Đồ án UEH & Kiểm thử hệ thống (QA Testing)** | Nguyễn Thế Luân & Khiếu Hoàng Nam Anh | `feature/tv5-ajax-core-and-docs` | 🟡 In Progress |

---

## 5. Đặc tả Hệ thống WebService & RESTful API

Hệ thống cung cấp danh mục API chuẩn RESTful phục vụ giao tiếp AJAX không tải lại trang và tích hợp bên thứ ba:

### 5.1. Bảng danh mục Endpoints

| STT | Phương thức | URL Endpoint | Định dạng | Mô tả chức năng |
|:---:|:---:|:---|:---:|:---|
| 1 | `GET` | `/report/api_list` | JSON | Lấy danh sách sự cố công khai có phân trang, hỗ trợ bộ lọc đa tiêu chí (danh mục, mức độ, trạng thái, ngày gửi, từ khóa). |
| 2 | `GET` | `/report/api_geojson` | GeoJSON | Xuất tập hợp điểm sự cố chuẩn GeoJSON FeatureCollection cho bản đồ Leaflet.js; hỗ trợ lọc theo bán kính `lat`, `lng`, `radius_km`. |
| 3 | `POST` | `/report/api_upvote` | JSON | Tăng lượt xác nhận độ tin cậy của sự cố; cơ chế chống gian lận/spam theo tài khoản và phiên làm việc. |
| 4 | `GET` | `/report/api_comments/{id}` | JSON | Nạp danh sách bình luận động của sự cố kèm phân trang. |
| 5 | `POST` | `/report/api_add_comment` | JSON | Gửi bình luận mới nhanh chóng qua AJAX không làm reload trang. |
| 6 | `GET` | `/report/api_xml_feed` | XML | WebService cung cấp luồng cấp tin dữ liệu sự cố dạng XML chuẩn RSS/Atom feed. |
| 7 | `GET` | `/admin/api_statistics` | JSON | Dữ liệu thống kê sự cố theo danh mục, trạng thái xử lý và xu hướng tháng phục vụ render biểu đồ Chart.js. |
| 8 | `GET` | `/admin/export_csv` | CSV | Trích xuất toàn bộ dữ liệu sự cố ra tệp CSV chuẩn UTF-8 (tương thích Microsoft Excel không lỗi font tiếng Việt). |
| 9 | `GET` | `/notification/api_unread_count` | JSON | Đếm số lượng thông báo chưa đọc của người dùng hiện tại để hiển thị badge chuông trên thanh điều hướng. |

---

## 6. Hướng dẫn Cài đặt & Chạy ứng dụng

### 6.1. Yêu cầu môi trường
- PHP >= 8.1 (bật extension: `pdo_mysql`, `gd`, `mbstring`, `curl`).
- MySQL / MariaDB >= 10.4.
- Web server Apache (có bật module `mod_rewrite`) thông qua XAMPP, Laragon hoặc Docker.

### 6.2. Các bước cài đặt chi tiết
1. **Clone mã nguồn từ GitHub:**
   ```bash
   git clone https://github.com/nvm4nh/Group11_Project-Web_WarningInfo.git
   cd Group11_Project-Web_WarningInfo
   ```
2. **Thiết lập Cơ sở dữ liệu:**
   - Mở phpMyAdmin hoặc MySQL Workbench, tạo một cơ sở dữ liệu mới có tên `warning_info_db` với bảng mã `utf8mb4_unicode_ci`.
   - Nhập (Import) tệp tin CSDL tại: `database/warning_info_db.sql` hoặc `database/db_warninginfo_full.sql`.
3. **Cấu hình kết nối:**
   - Kiểm tra tệp `config/config.php`:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'warning_info_db');
     define('URLROOT', 'http://localhost/Group11_Project-Web_WarningInfo');
     ```
4. **Phân quyền thư mục tải lên:**
   - Đảm bảo thư mục `public/uploads/` có quyền ghi (`chmod 777` hoặc Write permissions).

---

## 7. Hướng dẫn Trải nghiệm & Kiểm thử tính năng

### 7.1. Danh sách Tài khoản Kiểm thử (Mặc định)
| Loại tài khoản | Tên đăng nhập / Email | Mật khẩu | Quyền hạn |
|:---|:---|:---:|:---|
| **Quản trị viên (Admin)** | `admin@warninginfo.vn` | `123456` | Truy cập Admin Dashboard, duyệt tin, phân công cán bộ, xem biểu đồ, xuất CSV. |
| **Cán bộ hiện trường (Officer)** | `officer@warninginfo.vn` | `123456` | Truy cập Bàn làm việc cán bộ (`/officer`), tiếp nhận sự cố, cập nhật tiến độ xử lý. |
| **Người dân (Citizen)** | `citizen@gmail.com` | `123456` | Gửi phản ánh hiện trường, xem bản đồ số, theo dõi tiến độ sự cố của mình. |

### 7.2. Các đường dẫn trải nghiệm tính năng nổi bật Milestone 3
- 🗺️ **Bản đồ số tương tác:** `http://localhost/Group11_Project-Web_WarningInfo/report/map`
- 📍 **API dữ liệu GeoJSON:** `http://localhost/Group11_Project-Web_WarningInfo/report/api_geojson`
- 📊 **Bảng điều khiển Admin & Biểu đồ Chart.js:** `http://localhost/Group11_Project-Web_WarningInfo/admin`
- 📥 **Xuất dữ liệu sự cố CSV UTF-8:** `http://localhost/Group11_Project-Web_WarningInfo/admin/export_csv`
- 🛡️ **Bàn làm việc Cán bộ thụ lý:** `http://localhost/Group11_Project-Web_WarningInfo/officer`
- 🔔 **Hộp thông báo hệ thống:** `http://localhost/Group11_Project-Web_WarningInfo/notification`

---

## 8. Quy ước Nhánh và Đóng góp Nhóm

- **Nhánh `main`**: Mã nguồn xuất bản ổn định, đã kiểm thử QA và sẵn sàng báo cáo / chấm điểm.
- **Nhánh `develop`**: Nhánh tích hợp trung tâm của các thành viên.
- **Nhánh chức năng**: Quy tắc đặt tên theo mẫu `feature/tv<STT>-<tên-chức-năng>`.
- **Quy tắc Commit chuẩn Conventional Commits**:
  - `feat(map):`: Tính năng bản đồ Leaflet và ghim GPS.
  - `feat(api):`: Các endpoint WebService RESTful API và GeoJSON.
  - `feat(ajax):`: Xử lý tương tác AJAX client-side.
  - `feat(security):`: Bảo mật chống CSRF và phân quyền.
  - `docs(ueh):`: Cập nhật tài liệu và Báo cáo Đồ án UEH.

---
*© 2026 Nhóm 11 – Khoa Công Nghệ Thông Tin Kinh Doanh – Trường Đại học Kinh tế TP. Hồ Chí Minh (UEH).*
