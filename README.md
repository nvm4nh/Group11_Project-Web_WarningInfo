# 🌍 WarningInfo — Hệ Thống Báo Cáo & Cảnh Báo Vấn Đề Môi Trường

[![Course](https://img.shields.io/badge/Course-Web%20Development-blue.svg)]()
[![Frontend](https://img.shields.io/badge/Frontend-ReactJS%20%7C%20TailwindCSS-cyan.svg)](https://react.dev)
[![Backend](https://img.shields.io/badge/Backend-Node.js%20(Express)-green.svg)](https://nodejs.org)
[![Maps](https://img.shields.io/badge/Maps-Leaflet%20%2F%20Mapbox-yellow.svg)]()
[![Database](https://img.shields.io/badge/Database-MongoDB%20%2F%20PostgreSQL-lightgrey.svg)]()

> **WarningInfo** là ứng dụng web hỗ trợ cộng đồng báo cáo, theo dõi và trực quan hóa các vấn đề môi trường xung quanh (rác thải tự phát, xả thải nguồn nước, ô nhiễm khói bụi, ngập úng, cây đổ,...). Dự án được thực hiện trong khuôn khổ đồ án môn học **Phát triển Web**.

---

## 📌 Mục lục
1. [Tính năng chính](#-1-tính-năng-chính)
2. [Công nghệ sử dụng](#-2-công-nghệ-sử-dụng-dự-kiến)
3. [Thành viên & Phân công vai trò](#-3-thành-viên--phân-công-vai-trò)
4. [Lộ trình thực hiện (Milestones & Issues)](#-4-lộ-trình-thực-hiện-milestones--issues)
5. [Quy trình làm việc trên GitHub (Git Workflow)](#-5-quy-trình-làm-việc-trên-github-git-workflow)
6. [Mẫu tạo Issue chuẩn (Issue Template)](#-6-mẫu-tạo-issue-chuẩn-issue-template)
7. [Hướng dẫn cài đặt & Chạy dự án](#-7-hướng-dẫn-cài-đặt--chạy-dự-án)
8. [Giấy phép & Liên hệ](#-giấy-phép--liên-hệ)

---

## 🚀 1. Tính năng chính

* **Gửi báo cáo sự cố nhanh:** Người dùng tải lên hình ảnh/video minh chứng, chọn danh mục ô nhiễm, mô tả mức độ nghiêm trọng và ghim tọa độ GPS trực tiếp trên bản đồ số.
* **Bản đồ cảnh báo trực quan:** Hiển thị các điểm nóng môi trường theo màu sắc phản ánh mức độ nghiêm trọng:
  * 🟢 **Xanh lá:** Đã xử lý / Đã khắc phục.
  * 🟡 **Vàng:** Đang trong quá trình xử lý.
  * 🔴 **Đỏ:** Mức độ nghiêm trọng / Mới phát sinh cần giải quyết gấp.
* **Tương tác cộng đồng:** Người dùng ở khu vực lân cận có thể xác nhận sự cố (tính năng Upvote — *"Tôi cũng thấy vấn đề này"*) và để lại bình luận cập nhật tình hình thực tế tại hiện trường.
* **Hệ thống Quản trị (Admin / Moderator):** Kiểm duyệt báo cáo hợp lệ, lọc các báo cáo rác/spam và cập nhật tiến độ xử lý qua các trạng thái: `Chờ duyệt` $\rightarrow$ `Đang xử lý` $\rightarrow$ `Đã khắc phục`.

---

## 🛠 2. Công nghệ sử dụng (Dự kiến)

* **Frontend:** HTML5, CSS3 / Tailwind CSS, JavaScript (ReactJS / VueJS / Next.js)
* **Bản đồ số:** Leaflet.js / Mapbox / Google Maps API
* **Backend:** Node.js (Express) / PHP (Laravel) / Python (Django)
* **Cơ sở dữ liệu:** MongoDB / PostgreSQL / MySQL
* **Lưu trữ Media:** Cloudinary / AWS S3 / Firebase Storage
* **Quản lý dự án & Mã nguồn:** Git, GitHub Projects, Figma, Postman

---

## 👥 3. Thành viên & Phân công vai trò

| STT | Thành viên | Vai trò chính | Trách nhiệm chi tiết |
|:---:|:---|:---|:---|
| 1 | **Thành viên 1 (TV1)** | Team Lead & Backend Core | Quản lý GitHub Project, duyệt PR; thiết kế lược đồ ERD CSDL; xây dựng module xác thực (Auth/JWT) và phân quyền người dùng. |
| 2 | **Thành viên 2 (TV2)** | Backend API & Media/Geo | Xây dựng RESTful API cho nghiệp vụ báo cáo sự cố; tích hợp upload ảnh/video minh chứng; lưu trữ tọa độ GPS và viết API bộ lọc. |
| 3 | **Thành viên 3 (TV3)** | Frontend Core & Flow | Thiết kế UI/UX trên Figma; xây dựng trang chủ, luồng gửi báo cáo, trang chi tiết sự cố và các tính năng tương tác cộng đồng (Upvote, Comment). |
| 4 | **Thành viên 4 (TV4)** | Frontend Map, Admin & QA | Tích hợp bản đồ số trực quan điểm nóng; xây dựng giao diện Dashboard Admin duyệt bài; thực hiện kiểm thử (Testing) và deploy hệ thống. |

---

## 🗓 4. Lộ trình thực hiện (Milestones & Issues)

Dự án được chia thành **4 Milestones** kéo dài trong 10 tuần:

### 🎯 Milestone 1: Khảo sát & Thiết kế hệ thống (Tuần 1 – 2)
* [ ] `[Docs]` Viết đặc tả yêu cầu phần mềm (SRS) và sơ đồ Use Case cho WarningInfo *(Cả nhóm)*
* [ ] `[Design]` Thiết kế sơ đồ CSDL (ERD): `Users`, `Reports`, `Categories`, `Locations`, `Comments`, `StatusLogs` *(TV1, TV2)*
* [ ] `[Design]` Thiết kế Wireframe & UI Prototype trên Figma *(TV3, TV4)*
* [ ] `[Setup]` Khởi tạo Repository, cấu hình cấu trúc thư mục Frontend/Backend và file `.gitignore` *(TV1)*

### 🎯 Milestone 2: Xây dựng MVP - Chức năng cốt lõi (Tuần 3 – 5)
* [ ] `[BE]` Xây dựng API Đăng ký, Đăng nhập, phân quyền người dùng và quản trị viên *(TV1)*
* [ ] `[BE]` Xây dựng API CRUD Báo cáo sự cố môi trường kèm chức năng upload hình ảnh minh chứng *(TV2)*
* [ ] `[FE]` Xây dựng giao diện Đăng nhập/Đăng ký và Trang chủ hiển thị danh sách báo cáo mới nhất *(TV3)*
* [ ] `[FE]` Xây dựng Form gửi báo cáo sự cố (chọn danh mục, tải ảnh, lấy tọa độ GPS hoặc chấm điểm trên bản đồ) *(TV4)*

### 🎯 Milestone 3: Bản đồ, Tương tác & Quản trị (Tuần 6 – 8)
* [ ] `[FE]` Tích hợp Bản đồ trực quan hóa các điểm cảnh báo theo mức độ nghiêm trọng *(TV4)*
* [ ] `[FE/BE]` Phát triển tính năng tương tác cộng đồng: Bình luận, Upvote xác nhận sự cố *(TV3, TV1)*
* [ ] `[FE/BE]` Xây dựng Dashboard Admin: Duyệt báo cáo, ẩn báo cáo rác, cập nhật trạng thái xử lý *(TV4, TV2)*
* [ ] `[BE]` Xây dựng API bộ lọc nâng cao (theo khu vực, loại ô nhiễm, trạng thái, thời gian) *(TV2)*

### 🎯 Milestone 4: Kiểm thử, Deploy & Báo cáo (Tuần 9 – 10)
* [ ] `[QA]` Kiểm thử chéo toàn bộ luồng người dùng và tối ưu giao diện Responsive trên thiết bị di động *(TV3, TV4)*
* [ ] `[Deploy]` Triển khai ứng dụng lên Cloud (Vercel/Netlify cho FE, Render/Railway cho BE & DB) *(TV1, TV4)*
* [ ] `[Docs]` Hoàn thiện cuốn báo cáo tổng kết, slide thuyết trình và video demo *(Cả nhóm)*

---

## 🌿 5. Quy trình làm việc trên GitHub (Git Workflow)

### 5.1. Chiến lược phân nhánh (Branching Strategy)
* **`main`**: Nhánh chứa mã nguồn ổn định nhất, dùng để demo và nộp đồ án.
* **`develop`**: Nhánh tích hợp code từ các thành viên sau khi đã review.
* **`feature/<ten-tinh-nang>`**: Nhánh làm việc cá nhân (ví dụ: `feature/report-submission-form`, `feature/interactive-map`).
* **`fix/<ten-loi>`**: Nhánh sửa lỗi phát sinh (ví dụ: `fix/upload-image-error`).

> ⛔ **Quy tắc Merge:** Không push trực tiếp lên `main` hoặc `develop`. Mọi thay đổi phải tạo **Pull Request (PR)** vào nhánh `develop` và cần ít nhất **1 thành viên khác review** trước khi merge.

### 5.2. Bảng Kanban (GitHub Projects)
Các thẻ công việc (Issues) được quản lý qua 5 cột trạng thái:
1. **Backlog:** Toàn bộ tính năng và tài liệu cần làm của đồ án.
2. **Todo:** Các Issue cần hoàn thành trong tuần hiện tại.
3. **In Progress:** Các Issue đang được thực hiện (tối đa 2 Issue/người cùng lúc).
4. **In Review (PR Open):** Đã tạo Pull Request, đang chờ kiểm thử và duyệt code.
5. **Done:** Đã merge vào develop và hoạt động ổn định.

### 5.3. Hệ thống nhãn (GitHub Labels)
* **Phân vùng:** `area: frontend`, `area: backend`, `area: database`, `area: docs`
* **Loại công việc:** `type: feature`, `type: bug`, `type: enhancement`
* **Độ ưu tiên:** `priority: high`, `priority: medium`, `priority: low`

---

## 📝 6. Mẫu tạo Issue chuẩn (Issue Template)

Khi tạo Issue mới trên GitHub, các thành viên sử dụng cấu trúc sau (lưu tại `.github/ISSUE_TEMPLATE/feature_request.md`):

```markdown
---
name: Tính năng mới (Feature Task)
about: Mẫu tạo công việc cho thành viên nhóm WarningInfo
title: "[FE/BE] Tên tính năng ngắn gọn"
labels: "type: feature"
---

### 1. Mô tả công việc
Mô tả ngắn gọn chức năng cần xây dựng (ví dụ: Form gửi báo cáo ô nhiễm kèm tọa độ GPS).

### 2. Các đầu việc chi tiết (Checklist)
- [ ] Thiết kế giao diện / Cấu trúc dữ liệu API
- [ ] Xử lý logic và kiểm tra dữ liệu đầu vào (Validation)
- [ ] Kết nối API và xử lý thông báo lỗi
- [ ] Tự kiểm thử trên máy cá nhân

### 3. Tiêu chí hoàn thành (Acceptance Criteria)
- Người dùng gửi được báo cáo kèm tối đa 3 ảnh và vị trí hợp lệ.
- Không phát sinh lỗi trên Console và hiển thị tốt trên cả máy tính lẫn điện thoại.
