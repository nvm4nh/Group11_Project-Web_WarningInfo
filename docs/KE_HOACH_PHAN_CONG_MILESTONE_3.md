# TRƯỜNG ĐẠI HỌC KINH TẾ TP. HỒ CHÍ MINH (UEH)
## TRƯỜNG CÔNG NGHỆ VÀ THIẾT KẾ – KHOA CÔNG NGHỆ THÔNG TIN KINH DOANH
### Học phần: Phát Triển Ứng Dụng Web
**Giảng viên hướng dẫn:** TS. Đặng Ngọc Hoàng Thành  
**Đề tài:** WarningInfo – Hệ Thống Báo Cáo & Cảnh Báo Vấn Đề Môi Trường Đô Thị  
**Nhóm thực hiện:** Nhóm 11  
**GitHub Repository:** [nvm4nh/Group11_Project-Web_WarningInfo](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo)  

---

# KẾ HOẠCH TRIỂN KHAI & PHÂN CÔNG NHIỆM VỤ GITHUB
## MILESTONE 3: TÍCH HỢP NÂNG CAO, AJAX, WEBSERVICE & BẢN ĐỒ TƯƠNG TÁC (TUẦN 6 – TUẦN 8)

---

## I. ĐÁNH GIÁ TỔNG QUAN & MỤC TIÊU CỐT LÕI

### 1. Hiện trạng sau Milestone 2
- Đã hoàn thành 100% cốt lõi nghiệp vụ MVP (Upload đa ảnh hiện trường an toàn, định vị GPS sơ bộ, quản lý hồ sơ cá nhân và theo dõi trang "Báo cáo của tôi").
- Đã hoàn thành quy trình duyệt bài và thay đổi trạng thái sự cố của Admin kèm lý do ghi chú, tự động kích hoạt lưu lịch sử (`report_status_history`) và tạo thông báo (`notifications`).
- Toàn bộ mã nguồn đã được rà soát và merge sạch sẽ vào nhánh `develop`.

### 2. Mục tiêu trọng tâm của Milestone 3 (Tiêu chuẩn điểm 9.0 – 10.0 của UEH)
1. **Hệ thống WebService RESTful API (JSON/XML):** Xây dựng các endpoint cung cấp dữ liệu phục vụ các tác vụ bất đồng bộ và khả năng tích hợp mở rộng ngoại vi.
2. **Kỹ thuật AJAX toàn diện:** Loại bỏ hoàn toàn reload trang ở các tác vụ: Bộ lọc sự cố động đa tiêu chí, nút Upvote tin cậy, nạp bình luận động (Pagination / Load More) và đánh dấu đã đọc thông báo.
3. **Bản đồ số tương tác (Interactive Map - Leaflet.js & OpenStreetMap):** Trực quan hóa các điểm nóng sự cố theo tọa độ GPS kèm bộ lọc bán kính xung quanh vị trí người dùng (công thức Haversine).
4. **Trung tâm điều hành Quản trị thông minh:** Trực quan hóa số liệu thống kê bằng biểu đồ tương tác (**Chart.js**) và hỗ trợ xuất dữ liệu báo cáo ra file Excel/CSV chuẩn UTF-8.
5. **Gia cố Bảo mật & Phân hệ Cán bộ:** Cơ chế chống tấn công CSRF Token toàn diện và bàn làm việc phân công cán bộ hiện trường (`/officer`).
6. **Học thuật & Quản trị dự án:** Hoàn thiện bản thảo Chương 5 Báo cáo Đồ án UEH và quản lý tiến độ minh bạch qua GitHub Issues, Pull Requests.

---

## II. BẢNG PHÂN CÔNG NHIỆM VỤ TỔNG QUAN (NHÓM 11)

| STT | Thành viên | Vai trò chính | Nhánh Git | Module phụ trách | Thời hạn | Sản phẩm bàn giao | Trạng thái |
|:---:|:---|:---|:---|:---|:---:|:---|:---:|
| **1** | **Khiếu Hoàng Nam Anh** (TV1) | **Team Lead & System Architect** | `feature/tv1-system-leaflet-map` | • Kiến trúc Bản đồ số Leaflet.js<br>• API GeoJSON bản đồ<br>• Quản trị GitHub & Code Review | Tuần 8 | • Map component tích hợp Leaflet.js<br>• Endpoint GeoJSON sự cố<br>• Quản trị Milestone 3 trên GitHub | 🟢 **Merged (#21)** |
| **2** | **Trần Yến Phụng** (TV2) | **Backend Admin & Workflow** | `feature/tv2-admin-statistics-export` | • API thống kê Chart.js<br>• Phân công cán bộ xử lý<br>• Chức năng Xuất báo cáo (CSV) | Tuần 8 | • Endpoint /admin/api_statistics<br>• Biểu đồ Chart.js trong Admin<br>• Nút Export CSV chuẩn UTF-8 | 🟡 Reviewing (#18) |
| **3** | **Lê Tố Nguyệt Minh** (TV3) | **Backend Features & WebService API** | `feature/tv3-webservice-rest-api` | • WebService RESTful API chuẩn<br>• API Lọc sự cố & Tương tác<br>• Hỗ trợ định dạng kép JSON/XML | Tuần 8 | • Bộ controller API đầy đủ<br>• Tài liệu đặc tả API chuẩn REST<br>• WebService XML Feed | 🟡 In Progress |
| **4** | **Lê Trọng Hiếu** (TV4) | **Frontend UI/UX & Security** | `feature/tv4-ui-map-and-filters`<br>`feature/tv4-csrf-notify-officer` | • Giao diện Bản đồ tương tác<br>• Bảo mật CSRF Token<br>• Hộp thông báo & Bàn làm việc cán bộ | Tuần 8 | • Màn hình bản đồ (/report/map)<br>• Token CSRF trong mọi Form POST<br>• Giao diện /officer và /notification | 🟢 **Merged (#19)** |
| **5** | **Nguyễn Thế Luân** (TV5) | **Frontend AJAX Integration & QA** | `feature/tv5-ajax-core-and-docs` | • Lập trình kịch bản AJAX Client<br>• Tương tác Upvote & Bình luận<br>• Soạn Chương 5 Báo cáo UEH | Tuần 8 | • public/assets/js/ajax-filter.js<br>• public/assets/js/ajax-interaction.js<br>• Bản thảo Chương 5 Báo cáo | 🟡 In Progress |

---

## III. DANH SÁCH CHI TIẾT CÁC GITHUB ISSUES CHO MILESTONE 3

### 📌 Issue #22: [TV3] Xây dựng bộ WebService RESTful API (JSON/XML) cho sự cố và tương tác
* **Assignee:** Lê Tố Nguyệt Minh (TV3) - [@LeMinh7706](https://github.com/LeMinh7706)
* **Milestone:** [Milestone 3: Advanced Features, AJAX, WebService & Interactive Map](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/milestone/3)
* **Branch:** `feature/tv3-webservice-rest-api`
* **Labels:** `backend`, `webservice`, `enhancement`
* **Mục tiêu:** Cung cấp các endpoint phục vụ Client gọi AJAX bất đồng bộ và trao đổi dữ liệu với hệ thống ngoài.
* **Danh sách công việc:**
  - [ ] Nâng cấp `ReportController::api_list()`: Hỗ trợ lọc theo `category_id`, `priority`, `status`, `from_date`, `to_date`, `keyword`, `page`, `limit`.
  - [ ] Xây dựng endpoint `POST /report/api_upvote`: Tăng độ tin cậy sự cố, kiểm tra chống spam theo user/session, trả về JSON kết quả.
  - [ ] Xây dựng endpoint `GET /report/api_comments/{id}` & `POST /report/api_add_comment`: Nạp và gửi bình luận nhanh không reload.
  - [ ] Xây dựng WebService xuất dữ liệu XML: Hỗ trợ endpoint `/report/api_xml_feed` phục vụ tiêu chí điểm cao môn học.

---

### 📌 Issue #23: [TV5] Tích hợp AJAX toàn diện vào giao diện Public (Lọc không reload, Upvote, Bình luận)
* **Assignee:** Nguyễn Thế Luân (TV5) - [@luannguyen35262020117](https://github.com/luannguyen35262020117)
* **Milestone:** [Milestone 3: Advanced Features, AJAX, WebService & Interactive Map](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/milestone/3)
* **Branch:** `feature/tv5-ajax-core-and-docs`
* **Labels:** `frontend`, `ajax`, `high-priority`
* **Mục tiêu:** Ứng dụng AJAX để mang lại trải nghiệm tương tác liền mạch, mượt mà và không gián đoạn giao diện.
* **Danh sách công việc:**
  - [ ] Viết `public/assets/js/ajax-filter.js`: Lắng nghe sự kiện filter/search (debounce 300ms), gọi API và render động danh sách Card sự cố.
  - [ ] Viết `public/assets/js/ajax-interaction.js`: Xử lý nút Upvote kèm micro-animation, xử lý phân trang "Tải thêm bình luận".
  - [ ] Tích hợp AJAX cho Thông báo (Notifications): Đánh dấu đã đọc tức thì khi người dùng bấm xem thông báo trên Header Navbar.
  - [ ] Hiển thị thông báo Toast / Loading Skeleton khi đang tải dữ liệu từ server.

---

### 📌 Issue #24: [TV1 & TV4] Tích hợp Bản đồ số Leaflet.js hiển thị điểm ghim sự cố theo tọa độ GPS
* **Assignee:** Khiếu Hoàng Nam Anh (TV1) & Lê Trọng Hiếu (TV4)
* **Milestone:** [Milestone 3: Advanced Features, AJAX, WebService & Interactive Map](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/milestone/3)
* **Branch:** `feature/tv1-system-leaflet-map`
* **Pull Request:** [PR #21 (Merged)](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/pull/21)
* **Labels:** `frontend`, `map`, `feature`
* **Trạng thái:** ✅ **ĐÃ HOÀN THÀNH VÀ MERGE VÀO DEVELOP**
* **Chi tiết bàn giao:**
  - [x] Tích hợp thư viện Leaflet.js 1.9.4 tải tối ưu có điều kiện trên các view cần bản đồ.
  - [x] Xây dựng component bản đồ tương tác `wi-map.js` và style `map.css`.
  - [x] Endpoint `GET /report/api_geojson` xuất chuẩn GeoJSON FeatureCollection.
  - [x] Marker phân cấp màu sắc theo độ nghiêm trọng (Đỏ, Vàng, Xanh) và Popup xem nhanh.
  - [x] Tính năng "Sự cố trong bán kính gần tôi" dựa trên Geolocation và công thức Haversine.

---

### 📌 Issue #25: [TV2] Xây dựng Biểu đồ thống kê Admin Dashboard (Chart.js) & Xuất dữ liệu báo cáo CSV
* **Assignee:** Trần Yến Phụng (TV2) - [@phuongtran31241022663](https://github.com/phuongtran31241022663)
* **Milestone:** [Milestone 3: Advanced Features, AJAX, WebService & Interactive Map](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/milestone/3)
* **Branch:** `feature/tv2-admin-statistics-export`
* **Pull Request:** [PR #18](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/pull/18)
* **Labels:** `admin`, `backend`, `enhancement`
* **Mục tiêu:** Cung cấp bảng điều hành phân tích số liệu trực quan cho ban quản trị và trích xuất dữ liệu phục vụ lưu trữ.
* **Danh sách công việc:**
  - [ ] Endpoint `GET /admin/api_statistics`: Trả về dữ liệu thống kê tổng hợp (tỷ lệ danh mục, xu hướng theo tháng, trạng thái xử lý).
  - [ ] Tích hợp Chart.js vào Admin Dashboard: Doughnut chart danh mục, Bar chart trạng thái, Line chart xu hướng tháng.
  - [ ] Hoàn thiện luồng Phân công cán bộ phụ trách xử lý từng sự cố cụ thể có deadline.
  - [ ] Chức năng Export danh sách sự cố ra file CSV hỗ trợ đầy đủ font UTF-8 tiếng Việt.

---

### 📌 Issue #26: [TV4] Cơ chế bảo mật phòng chống CSRF Token, Hộp thông báo và Bàn làm việc cán bộ xử lý
* **Assignee:** Lê Trọng Hiếu (TV4) - [@hieuhamhoc313-alt](https://github.com/hieuhamhoc313-alt)
* **Milestone:** [Milestone 3: Advanced Features, AJAX, WebService & Interactive Map](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/milestone/3)
* **Branch:** `feature/tv4-csrf-notify-officer`
* **Pull Request:** [PR #19 (Merged)](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/pull/19)
* **Labels:** `security`, `frontend`, `backend`
* **Trạng thái:** ✅ **ĐÃ HOÀN THÀNH VÀ MERGE VÀO DEVELOP**
* **Chi tiết bàn giao:**
  - [x] Cơ chế chống tấn công CSRF theo phiên, kiểm tra token bắt buộc cho mọi yêu cầu POST.
  - [x] Trang Hộp thông báo (`/notification`) và badge chuông thông báo trên thanh Navbar.
  - [x] Phân hệ Bàn làm việc Cán bộ (`/officer`), cập nhật tiến độ và ghi chép biên bản xử lý hiện trường.

---

### 📌 Issue #27: [TV5 & TV1] Soạn thảo Chương 5 Báo cáo Đồ án UEH & Kiểm thử hệ thống (QA Testing)
* **Assignee:** Nguyễn Thế Luân (TV5) & Khiếu Hoàng Nam Anh (TV1)
* **Milestone:** [Milestone 3: Advanced Features, AJAX, WebService & Interactive Map](https://github.com/nvm4nh/Group11_Project-Web_WarningInfo/milestone/3)
* **Branch:** `feature/tv5-ajax-core-and-docs`
* **Labels:** `documentation`, `ueh-report`
* **Mục tiêu:** Hoàn thiện tài liệu học thuật theo đúng mẫu quy định của UEH và bảo đảm chất lượng phần mềm.
* **Danh sách công việc:**
  - [ ] Soạn thảo **Chương 5: Hiện thực hóa & Thử nghiệm hệ thống**:
    - Mô tả chi tiết kiến trúc WebService và đặc tả các API endpoints (URL, HTTP Method, Parameters, JSON Structure).
    - Vẽ Sequence Diagram cho luồng gọi AJAX giữa Client - Dispatcher - Controller - Model - Database.
    - Đưa hình ảnh minh chứng màn hình Bản đồ Leaflet và bộ lọc động vào báo cáo.
  - [ ] Tổ chức kiểm thử chéo toàn diện luồng nghiệp vụ trên cả Desktop và thiết bị di động.
  - [ ] Log các lỗi (bugs) lên GitHub Issues để các thành viên khắc phục kịp thời.

---

## IV. QUY TRÌNH PHỐI HỢP & QUY TẮC LÀM VIỆC NHÓM TRÊN GITHUB

1. **Khởi tạo Milestone:**
   - Đã tạo Milestone mới trên GitHub Repository:  
     **Milestone 3: Advanced Features, AJAX, WebService & Interactive Map (Tuần 6 – 8)**
2. **Quy tắc tạo nhánh:**
   - Luôn cập nhật mã nguồn mới nhất từ nhánh `develop` trước khi tạo nhánh chức năng:
     ```bash
     git checkout develop
     git pull origin develop
     git checkout -b feature/tv...
     ```
3. **Quy chuẩn Commit:**
   - `feat(api):`: thêm endpoint geojson và bộ lọc webservice
   - `feat(ajax):`: tích hợp fetch api cho bộ lọc sự cố không reload
   - `feat(map):`: nhúng bản đồ leaflet và load marker động
   - `docs(report):`: cập nhật chương 5 đặc tả webservice ueh
4. **Quy trình Pull Request & Merge:**
   - Mỗi thành viên hoàn thành nhánh của mình, đẩy lên GitHub và mở Pull Request hướng về nhánh `develop`.
   - Ghi rõ `Closes #<issue_id>` trong mô tả Pull Request để GitHub tự động đóng Issue khi merge.
   - Nhóm trưởng (TV1) thực hiện review code, kiểm tra không phát sinh xung đột trước khi merge.
