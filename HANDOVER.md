# TÀI LIỆU BÀN GIAO DỰ ÁN (HANDOVER) — DỰ ÁN WEB WARNINGINFO
> **Môn học:** Lập trình Web  
> **Đơn vị đào tạo:** Trường Công nghệ và Thiết kế — Đại học Kinh tế TP. Hồ Chí Minh (UEH) — Khóa K50  
> **Giảng viên hướng dẫn:** TS. Đặng Ngọc Hoàng Thành  
> **Nhóm thực hiện:** Nhóm 11  
> **Người bàn giao:** Khiếu Hoàng Nam Anh (TV1 — Nhóm trưởng & Kiến trúc sư hệ thống)  
> **Ngày cập nhật:** 17/10/2026  
> **Mục đích tài liệu:** Cung cấp bức tranh toàn diện, chi tiết và chính xác nhất về dự án để bất kỳ kỹ sư hoặc AI trợ lý nào có thể tiếp quản, tiếp tục phát triển, gỡ lỗi và hoàn thiện đồ án mà không bị đứt gãy thông tin.

---

## 1. YÊU CẦU ĐỀ BÀI CỦA MÔN HỌC & MỤC TIÊU DỰ ÁN

### 1.1. Yêu cầu học phần & Barem chấm điểm UEH
* **Mục tiêu môn học:** Xây dựng một ứng dụng web hoàn chỉnh từ đầu, vận dụng kiến thức nền tảng về thiết kế giao diện Responsive với Bootstrap 5, lập trình Backend hướng đối tượng (OOP) theo mô hình chuẩn **MVC (Model - View - Controller)** không phụ thuộc vào full-stack framework (như Laravel), kết hợp cơ sở dữ liệu quan hệ MySQL và kỹ thuật bất đồng bộ **AJAX / WebService (JSON/XML)** để tối ưu hóa trải nghiệm người dùng.
* **Quy định đánh giá cá nhân:** Giảng viên yêu cầu tính độc lập và minh bạch trong đóng góp của từng thành viên. Toàn bộ tiến độ phải được thể hiện trực quan qua **GitHub Projects**, từng thành viên phải có Issue, Branch riêng (`feature/...`) và Pull Request độc lập để chụp ảnh minh chứng đưa vào Phụ lục Báo cáo môn học.

### 1.2. Đề tài: Website Cảnh báo & Tiếp nhận Thông tin sự cố (WarningInfo)
* **Bài toán thực tế:** Cung cấp nền tảng số hóa cho phép người dân nhanh chóng gửi phản ánh, báo cáo các sự cố đô thị (ngập lụt, sạt lở, tai nạn, hỏng hạ tầng, ô nhiễm) kèm định vị GPS và hình ảnh hiện trường; đồng thời giúp cơ quan quản lý (Admin/Officer) tiếp nhận, phân loại, phân công cán bộ xử lý, theo dõi lịch sử trạng thái và phản hồi minh bạch đến người dân.
* **Đối tượng sử dụng:**
  1. **Người dân (Public/Citizen):** Xem bản tin cảnh báo, tìm kiếm/lọc sự cố theo danh mục, gửi báo cáo kèm ảnh & tọa độ, theo dõi tiến độ xử lý tại trang cá nhân (*"Báo cáo của tôi"*), nhận thông báo khi có phản hồi.
  2. **Cán bộ quản trị (Admin/Officer):** Đăng nhập trang quản trị (Dashboard), kiểm duyệt báo cáo, thay đổi trạng thái sự cố có ghi chú lý do, phân công cán bộ phụ trách, quản lý danh mục và tài khoản người dùng.

---

## 2. CÔNG NGHỆ, KIẾN TRÚC & CẤU TRÚC THƯ MỤC

### 2.1. Công nghệ sử dụng
* **Frontend:** HTML5, CSS3, JavaScript ES6, thư viện jQuery, UI Framework **Bootstrap 5.3**.
* **Backend:** Ngôn ngữ lập trình **PHP (>= 8.0)**, thuần hướng đối tượng (OOP), tuân thủ mô hình **MVC thuần**.
* **Cơ sở dữ liệu:** Hệ quản trị CSDL **MySQL**, kết nối và thao tác an toàn thông qua **PDO** (Prepared Statements) chống SQL Injection.
* **Giao tiếp dữ liệu:** AJAX (Fetch API / jQuery AJAX), WebService RESTful trả về định dạng **JSON** (phục vụ lọc động và nạp dữ liệu không tải lại trang).
* **Môi trường cục bộ:** XAMPP hoặc Laragon (Apache Web Server, MySQL).

### 2.2. Kiến trúc hệ thống
* **Mô hình MVC (Model - View - Controller):**
  * **Routing tập trung (Front Controller):** Mọi request HTTP đều được điều hướng qua file `public/index.php` thông qua Apache `.htaccess`.
  * **Core Dispatcher (`app/App.php`):** Phân tích URL theo quy tắc `/{controller}/{action}/{params}`, tự động khởi tạo Controller và gọi Action tương ứng; xử lý ngoại lệ 404 khi không tìm thấy route.
  * **Base Controller (`app/Controller.php`):** Cung cấp các hàm dùng chung: `model()`, `view()`, `jsonResponse()`, `redirect()`, kiểm tra middleware `requireLogin()`, `requireAdmin()`.
  * **Database Connection (`app/Database.php`):** Áp dụng mẫu thiết kế Singleton hoặc khởi tạo PDO tĩnh, đọc cấu hình kết nối an toàn.
  * **Model:** Chứa logic nghiệp vụ và truy vấn CSDL (`User`, `Report`, `Category`, `Notification`, v.v.).
  * **View:** Giao diện phân tách dạng module (`views/layouts/`, `views/auth/`, `views/admin/`, `views/report/`).

### 2.3. Cấu trúc thư mục chi tiết
```
Group11_Project-Web_WarningInfo/
├── app/
│   ├── App.php                     # Bộ định tuyến cốt lõi (Router & Request Dispatcher)
│   ├── Controller.php              # Base Controller (quản lý session, render view, JSON)
│   ├── Database.php                # Kết nối PDO MySQL bảo mật
│   ├── controllers/
│   │   ├── AdminController.php     # Xử lý quy trình duyệt bài, phân công cán bộ
│   │   ├── AuthController.php      # Đăng ký, đăng nhập, hồ sơ cá nhân (profile)
│   │   ├── HomeController.php      # Trang chủ, hiển thị danh sách cảnh báo công khai
│   │   └── ReportController.php    # Gửi báo cáo, Upload ảnh, danh sách "Báo cáo của tôi"
│   ├── models/
│   │   ├── Category.php            # Danh mục sự cố
│   │   ├── Notification.php        # Thông báo gửi đến người dân
│   │   ├── Report.php              # Nghiệp vụ sự cố và chi tiết ảnh đính kèm
│   │   └── User.php                # Tài khoản và phân quyền
│   └── views/
│       ├── layouts/                # Header, footer, navbar dùng chung
│       ├── auth/                   # Form đăng nhập, đăng ký, trang đổi mật khẩu/profile
│       ├── admin/                  # Dashboard quản trị, bảng duyệt sự cố
│       └── report/                 # Form báo cáo sự cố (GPS + preview ảnh), "Báo cáo của tôi"
├── database/
│   ├── database_init.sql           # Script khởi tạo cấu trúc CSDL và dữ liệu mẫu
│   └── ERD.md                      # Thiết kế lược đồ thực thể quan hệ
├── docs/
│   ├── KE_HOACH_PHAN_CONG_MILESTONE_2.md # Kế hoạch phân công chi tiết & tiêu chí nghiệm thu
│   ├── screenshots/                # Ảnh chụp minh chứng responsive (375px, 414px, 768px)
│   └── wireframe/                  # Thiết kế bản vẽ giao diện nháp
├── public/
│   ├── .htaccess                   # Rewrite engine trỏ về index.php
│   ├── index.php                   # Điểm vào duy nhất (Entry point)
│   ├── assets/
│   │   ├── css/                    # Custom CSS ghi đè Bootstrap
│   │   └── js/                     # AJAX scripts, preview ảnh, lấy tọa độ GPS
│   └── uploads/
│       └── reports/                # Thư mục lưu trữ hình ảnh sự cố thực tế
└── README.md                       # Giới thiệu đồ án, bảng phân công 5 thành viên
```

---

## 3. CÁC QUYẾT ĐỊNH QUAN TRỌNG VÀ LÝ DO (DESIGN DECISIONS)

1. **Tự xây dựng Mini-MVC thuần thay vì dùng Framework có sẵn:**
   * *Lý do:* Thầy Thành yêu cầu sinh viên phải hiểu bản chất của OOP trong PHP, cách hoạt động của Front Controller, Session, PDO và Request Lifecycle. Sử dụng Laravel sẽ vi phạm barem và bị trừ điểm đồ án cơ sở.
2. **Ghi vết lịch sử trạng thái bằng bảng riêng (`report_status_history`):**
   * *Lý do:* Không chỉ đơn thuần cập nhật cột `status` trong bảng `reports`. Cần lưu lại: ai đổi, đổi từ trạng thái nào sang trạng thái nào, vào thời điểm nào, và ghi chú lý do (`resolution_note`). Điều này giúp minh bạch hóa quy trình công quyền và phục vụ tính năng gửi thông báo tự động (`notifications`).
3. **Cơ chế tải ảnh và định vị GPS tại Form gửi báo cáo:**
   * *Lý do:* Khi người dân gửi sự cố, ảnh chụp là bằng chứng cốt lõi. Client sử dụng JavaScript File API để xem trước ảnh (Image preview) trước khi upload. Tọa độ được lấy trực tiếp thông qua HTML5 Geolocation API (`navigator.geolocation`) để giảm thiểu sai sót do người dùng nhập tay kinh độ/vĩ độ.
4. **Phân quyền và bảo mật Session:**
   * *Lý do:* Kiểm tra phân quyền đa tầng tại Controller. Các action nhạy cảm trong `AdminController` bắt buộc phải chạy qua middleware `requireAdmin()`. Mật khẩu được mã hóa an toàn bằng thuật toán `password_hash()` của PHP (BCrypt).

---

## 4. NHỮNG GÌ ĐÃ XONG, ĐANG LÀM DỞ, CÒN LỖI GÌ

### 4.1. Những gì đã hoàn thành (Milestones 1 & 2)
* **Hạ tầng lõi & CSDL:**
  * Bộ khung MVC hướng đối tượng hoạt động mượt mà, cấu hình VirtualHost hoặc `htdocs` ổn định.
  * CSDL `warning_info` hoàn chỉnh các bảng: `users`, `categories`, `reports`, `report_images`, `report_status_history`, `notifications`, `assignments`.
* **Phân hệ Người dùng & Xác thực (TV1 - Nam Anh phụ trách):**
  * Đăng ký, đăng nhập bảo mật, lưu session an toàn.
  * Trang Quản lý thông tin cá nhân (`/auth/profile`): Cập nhật họ tên, số điện thoại, đổi mật khẩu có kiểm tra mật khẩu cũ.
  * Trang Báo cáo của tôi (`/report/my_reports`): Hiển thị bảng danh sách các sự cố do chính người dùng gửi, kèm thanh trạng thái tiến trình xử lý trực quan (*Mới tiếp nhận $\rightarrow$ Đang xác minh $\rightarrow$ Đang xử lý $\rightarrow$ Đã khắc phục / Từ chối*).
* **Phân hệ Giao diện & Form báo cáo (TV4 - Trọng Hiếu phụ trách):**
  * Giao diện Responsive chuẩn trên Mobile (375px, 414px) và Tablet/Desktop (768px+).
  * Form gửi báo cáo tích hợp nút *"Lấy tọa độ GPS hiện tại"* và khung xem trước ảnh chụp trước khi bấm gửi.
* **Phân hệ Admin & Kiểm duyệt (TV2 - Yến Phụng & TV5 - Thọ Luân phụ trách):**
  * Dashboard hiển thị thống kê tổng quan.
  * Modal xem chi tiết sự cố, duyệt sự cố, chuyển trạng thái kèm ghi chú và kích hoạt ghi lịch sử + bắn thông báo.
* **Xử lý nhánh Git:** Đã hòa giải xung đột thành công nhánh của Trọng Hiếu (`feature/tv4-ui-report-form`) và Thọ Luân (`feature/tv5-admin-docs`), code đã được kiểm tra và merge sạch vào nhánh `develop` (commit `32e254b`).

### 4.2. Những gì đang làm dở & Cần tiếp tục (Milestone 3)
* **WebService RESTful API (`ReportController::api_list`):**
  * Đã có khung sơ bộ nhưng chưa hoàn thiện đầy đủ bộ lọc kết hợp (lọc theo khoảng ngày, lọc nhiều danh mục cùng lúc). Cần hoàn thiện endpoint JSON chuẩn.
* **Tích hợp AJAX hoàn chỉnh ở trang Public (TV5 & TV3):**
  * Cần viết script jQuery AJAX gọi về endpoint trên để lọc danh sách sự cố ngoài trang chủ không tải lại trang.
* **Hiển thị bản đồ trực quan (Interactive Map):**
  * Hiện mới lưu kinh độ / vĩ độ dạng số thập phân (`latitude`, `longitude`). Cần nhúng thư viện bản đồ mã nguồn mở **Leaflet.js** (OpenStreetMap) để hiển thị các điểm ghim sự cố trực quan ngoài trang chủ.

### 4.3. Các lỗi / lưu ý kỹ thuật cần để tâm
* Thư mục `public/uploads/reports/` trên môi trường Linux/Shared Hosting cần được cấp quyền ghi (`chmod 775` hoặc `777`), nếu không thao tác tải ảnh sẽ phát sinh lỗi.
* Khi chạy trên một số cấu hình XAMPP cũ, hàm kiểm tra route trong `.htaccess` có thể cần thêm `RewriteBase /`.

---

## 5. CÁC BƯỚC TIẾP THEO ĐÃ THỐNG NHẤT CHO MILESTONE 3 & 4

1. **Tuần 6 – 7 (Milestone 3: AJAX & WebService):**
   * Hoàn thiện WebService trả về JSON chuẩn cho bộ lọc sự cố.
   * Viết script AJAX cho bộ lọc ngoài trang chủ và nạp thêm bình luận/phản hồi.
   * Gắn bản đồ Leaflet.js hiển thị vị trí sự cố trên trang chi tiết và trang chủ.
2. **Tuần 8 – 9 (Milestone 4: Kiểm thử, Báo cáo & Triển khai):**
   * TV5 (QA) thực hiện kiểm thử chéo toàn diện, ghi nhận issue bug lên GitHub.
   * Đóng gói bản thảo Báo cáo Word (Chương 1 đến Chương 6) theo đúng mẫu biểu quy định của UEH.
   * TV1 (Nam Anh) đăng ký Free Hosting / Shared Hosting, cấu hình CSDL và đưa website chạy trực tuyến (live demo).
3. **Tuần 10: Tổng duyệt & Chuẩn bị Slide thuyết trình:**
   * Chụp ảnh minh chứng GitHub, lập bảng phân công nhiệm vụ có chữ ký xác nhận của các thành viên.

---

## 6. NHỮNG HƯỚNG ĐÃ THỬ NHƯNG BỎ & LÝ DO

* **Tích hợp Google Maps API ngay từ đầu:** Đã thử nghiệm nhưng phải bỏ vì Google Maps yêu cầu gắn thẻ tín dụng để kích hoạt Billing Account. Đã quyết định chuyển sang sử dụng tọa độ Geolocation của trình duyệt và chuẩn bị tích hợp Leaflet.js (miễn phí, không cần API Key phức tạp).
* **Sử dụng AJAX cho toàn bộ luồng đăng ký / đăng nhập:** Ban đầu định làm SPA (Single Page Application) cho auth, nhưng sau đó chuyển về mô hình Form Submit truyền thống có kiểm tra Session để đảm bảo tính tương thích và đơn giản hóa việc nộp bài cho sinh viên trong nhóm.
* **Duyệt bài 1 chạm không ghi chú:** Đã từng có ý tưởng chỉ cần bấm nút Duyệt là xong, nhưng đã bác bỏ vì không đáp ứng yêu cầu nghiệp vụ thực tế (phải giải trình vì sao từ chối sự cố của người dân).

---

## 7. CÁCH CHẠY DỰ ÁN (SETUP & RUN)

### 7.1. Yêu cầu môi trường
* XAMPP (hoặc Laragon / MAMP) chạy PHP phiên bản **8.0, 8.1 hoặc 8.2**.
* Hệ quản trị MySQL / MariaDB (đi kèm trong XAMPP).

### 7.2. Các bước cài đặt
1. **Đặt mã nguồn vào Web Server:**
   * Đặt thư mục `Group11_Project-Web_WarningInfo` vào thư mục `C:\xampp\htdocs\` (hoặc thư mục tương đương của Laragon).
2. **Khởi tạo Cơ sở dữ liệu:**
   * Mở trình duyệt truy cập `http://localhost/phpmyadmin`.
   * Tạo cơ sở dữ liệu mới có tên: `warning_info` (Collation: `utf8mb4_unicode_ci`).
   * Chọn tab **Import** $\rightarrow$ Chọn file `database/database_init.sql` và nhấn **Go**.
3. **Cấu hình kết nối CSDL:**
   * Mở file [Database.php](file:///C:/Users/Admin/Documents/GitHub/Group11_Project-Web_WarningInfo/app/Database.php).
   * Kiểm tra và điều chỉnh thông số (nếu khác mặc định):
     ```php
     private $host = "localhost";
     private $db_name = "warning_info";
     private $username = "root";
     private $password = "";
     ```
4. **Khởi chạy ứng dụng:**
   * Khởi động Apache và MySQL trên XAMPP Control Panel.
   * Mở trình duyệt và truy cập: `http://localhost/Group11_Project-Web_WarningInfo/public/`.
   * Tài khoản thử nghiệm:
     * **Admin:** `admin@warninginfo.vn` / Mật khẩu: `admin123` (hoặc tài khoản trong `database_init.sql`).
     * **Người dân:** Đăng ký trực tiếp trên giao diện hoặc dùng tài khoản mẫu.

---

## 8. YÊU CẦU VÀ PHONG CÁCH LÀM VIỆC CỦA TÔI (NAM ANH)

* **Chuẩn chỉ & Kỷ luật Git:**
  * Tuyệt đối không commit trực tiếp mã nguồn chưa qua kiểm thử lên nhánh `main`.
  * Mọi công việc phải xuất phát từ GitHub Issue, tạo branch chuẩn cú pháp (`feature/tv...`), tạo Pull Request và yêu cầu review trước khi merge vào `develop`.
* **Bám sát Barem UEH:**
  * Luôn đối chiếu code với các mục trong đề cương báo cáo của thầy Thành. Mọi tính năng kỹ thuật viết ra đều phải phục vụ cho việc giải trình trong cuốn báo cáo hoặc minh chứng phân công.
* **Thực tế & Trách nhiệm:**
  * Giữ vai trò Trưởng nhóm và Kiến trúc sư, tôi ưu tiên sự mạch lạc, phân định công việc rõ ràng cho 5 bạn. Khi một AI hỗ trợ tôi, hãy giải thích nguyên nhân gốc rễ (root cause) khi có lỗi, đưa ra giải pháp ngắn gọn, sạch sẽ, không code dư thừa và tuân thủ chặt chẽ kiến trúc MVC OOP đã định hình.
