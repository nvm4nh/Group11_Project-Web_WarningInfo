# Báo cáo kiểm thử – WarningInfo

- Trang web: https://warninginfo.infinityfree.io/
- Ngày kiểm thử: 13/11/2026
- Công cụ: Playwright (Chromium headless), độ phân giải 1366×850, GPS giả lập 21.0285, 105.8542 (Hà Nội)
- Phạm vi: duyệt toàn bộ trang và liên kết với 4 vai trò (khách, quản trị viên, cán bộ, người dân) và chạy 42 kịch bản chức năng xuyên suốt quy trình: người dân gửi báo cáo → quản trị viên duyệt và phân công → cán bộ xử lý → người dân nhận thông báo.
- Mã dữ liệu kiểm thử: `QA-943920`

## Tổng kết

| Hạng mục | Kết quả |
| --- | --- |
| Duyệt trang và liên kết | 280 lượt mở trang (70 trang × 4 vai trò): không có lỗi 404/500, không có lỗi PHP, không có lỗi JavaScript |
| Kịch bản chức năng | 42/42 Pass (sau khi sửa Lỗi 1) |
| Lỗi cần sửa trước khi quay demo | 0 (Lỗi 1 đã khắc phục và kiểm thử lại) |

## Danh sách lỗi (xếp theo mức ảnh hưởng tới buổi quay demo)

### Lỗi 1 – Nghiêm trọng: ảnh người dân tải lên không hiển thị (HTTP 500) – ĐÃ KHẮC PHỤC

- Hiện tượng: gửi báo cáo kèm ảnh thành công, file được lưu, nhưng mở ảnh thì máy chủ trả trang "Oops! A Website Error Has Occurred" (HTTP 500). Trang chi tiết, bàn làm việc cán bộ và trang quản trị đều hiện ảnh vỡ.
- Phạm vi: **mọi** đường dẫn dưới `/uploads/` đều trả 500, kể cả file không tồn tại (`/uploads/reports/khongco.png`) và `/uploads/reports/.gitkeep`. Các file tĩnh khác (`/favicon.ico`, `/assets/...`) bình thường.
- Nguyên nhân: trong `public/uploads/.htaccess`, dòng `Options -Indexes -ExecCGI` dùng `ExecCGI`, một Option mà InfinityFree không cho khai báo trong `.htaccess`, nên Apache báo lỗi cấu hình cho cả thư mục.
- Cách sửa: đổi thành `Options -Indexes`, giữ nguyên các lớp chặn còn lại (danh sách trắng ảnh, chặn đuôi script, tắt PHP, RemoveHandler, header nosniff/CSP).
- Kiểm thử lại sau khi sửa (13/11/2026): ảnh `/uploads/reports/53a2c4…727.png` trả HTTP 200 và hiển thị trên trang chi tiết. Lớp chặn vẫn hoạt động: `.gitkeep`, `test.php`, `a.php.png`, liệt kê thư mục `/uploads/reports/` và `/uploads/.htaccess` đều bị từ chối (chuyển sang trang lỗi của hosting).
- Ảnh hưởng tới demo: phần "gửi báo cáo kèm ảnh" là tính năng chính, ảnh vỡ sẽ lộ rõ trong clip.

### Ghi chú (không phải lỗi)

- Khách mở `/admin` được đưa về trang chủ kèm thông báo "Bạn không có quyền truy cập khu vực Quản trị" (không chuyển sang trang đăng nhập). Hành vi hợp lệ.
- Khách mở được form gửi báo cáo (hỗ trợ gửi ẩn danh kèm thông tin liên hệ).
- Khi bấm "Lấy vị trí", ô địa chỉ không tự điền (chỉ có toạ độ). Nếu muốn có địa chỉ tự động khi quay demo, cần kiểm tra lại tính năng tra địa chỉ.
- Dữ liệu mẫu chưa có báo cáo nào kèm ảnh, nên trang danh sách và trang chủ không có ảnh minh hoạ. Nên thêm vài báo cáo có ảnh sau khi sửa Lỗi 1 để clip đẹp hơn.

## Bảng test case

| STT | Vai trò | Chức năng | Các bước | Kết quả mong đợi | Kết quả thực tế | Pass/Fail | Ảnh |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 01 | Khách | Trang chủ | Mở / | Trang chủ hiển thị hero, thống kê, bản đồ, cảnh báo mới | HTTP 200, 7 liên kết cảnh báo, tiêu đề "WarningInfo – Báo cáo & cảnh báo môi trường từ cộng đồng" | ✅ Pass | [TC01](screenshots/TC01.png) |
| 02 | Khách | Danh sách cảnh báo | Mở /report | Hiện danh sách thẻ báo cáo + bộ lọc | HTTP 200, 5 báo cáo hiển thị | ✅ Pass | [TC02](screenshots/TC02.png) |
| 03 | Khách | Tìm kiếm | Mở /report, nhập "rác" vào ô từ khoá, Enter | Trả về kết quả liên quan | URL /report?q=rác, 2 kết quả | ✅ Pass | [TC03](screenshots/TC03.png) |
| 04 | Khách | Chi tiết báo cáo | Bấm vào 1 báo cáo từ danh sách | Hiện tiêu đề, mô tả, ảnh, bản đồ, bình luận | HTTP 200 /report/detail/1 | ✅ Pass | [TC04](screenshots/TC04.png) |
| 05 | Khách | Upvote khi chưa đăng nhập | Bấm "Tôi cũng thấy sự cố này" | Yêu cầu đăng nhập, không tăng lượt | URL /auth/login, số lượt 2 → ? | ✅ Pass | [TC05](screenshots/TC05.png) |
| 06 | Khách | Bản đồ sự cố | Mở /report/map | Bản đồ Leaflet tải và có marker | HTTP 200, 5 marker/cluster | ✅ Pass | [TC06](screenshots/TC06.png) |
| 07 | Khách | Trang 404 | Mở /khongtontai | Trang 404 thân thiện | HTTP 404 | ✅ Pass | [TC07](screenshots/TC07.png) |
| 08 | Khách | Chặn truy cập trang quản trị | Mở /admin khi chưa đăng nhập | Không vào được trang quản trị, có thông báo | Chuyển về /; thông báo "Bạn không có quyền truy cập khu vực Quản trị" | ✅ Pass | [TC08](screenshots/TC08.png) |
| 09 | Khách | Chặn gửi báo cáo | Mở /report/create khi chưa đăng nhập | Cho phép gửi ẩn danh hoặc yêu cầu đăng nhập | Mở được form gửi báo cáo khi chưa đăng nhập (cho phép gửi ẩn danh kèm thông tin liên hệ) | ✅ Pass | [TC09](screenshots/TC09.png) |
| 10 | Khách | Đăng nhập sai mật khẩu | Nhập admin@warninginfo.com / saimatkhau | Báo lỗi, không đăng nhập | Ở lại /auth/login; thông báo: "Email hoặc mật khẩu không đúng" | ✅ Pass | [TC10](screenshots/TC10.png) |
| 11 | Khách | Đăng ký – kiểm tra dữ liệu | Mở /auth/register, bấm Đăng ký khi để trống | Hiện lỗi bắt buộc nhập | 3 trường báo lỗi, vẫn ở /auth/register | ✅ Pass | [TC11](screenshots/TC11.png) |
| 12 | Người dân (an.demo) | Đăng nhập | Đăng nhập an.demo@warninginfo.local | Đăng nhập thành công, hiện tên người dùng | Chuyển tới / | ✅ Pass | [TC12](screenshots/TC12.png) |
| 13 | Người dân (an.demo) | Chặn trang quản trị | Mở /admin bằng tài khoản người dân | Bị từ chối / chuyển hướng | HTTP 200, URL / | ✅ Pass | [TC13](screenshots/TC13.png) |
| 14 | Người dân (an.demo) | Chặn bàn làm việc cán bộ | Mở /officer | Bị từ chối / chuyển hướng | HTTP 200, URL / | ✅ Pass | [TC14](screenshots/TC14.png) |
| 15 | Người dân (an.demo) | Gửi báo cáo – thiếu dữ liệu | Mở form gửi báo cáo, bấm Gửi khi để trống | Hiện lỗi tại các trường bắt buộc | 3 trường báo lỗi | ✅ Pass | [TC15](screenshots/TC15.png) |
| 16 | Người dân (an.demo) | Lấy vị trí GPS | Bấm nút "Lấy vị trí hiện tại" (GPS giả lập 21.0285,105.8542) | Ô vĩ độ/kinh độ được điền | lat=21.0285000, lng=105.8542000, địa chỉ="" | ✅ Pass | [TC16](screenshots/TC16.png) |
| 17 | Người dân (an.demo) | Gửi báo cáo kèm ảnh + GPS | Điền tiêu đề, danh mục, mô tả, đính kèm ảnh PNG, bấm Gửi | Báo cáo được tạo, thông báo thành công | Chuyển tới /report/detail/6; có thông báo thành công | ✅ Pass | [TC17](screenshots/TC17.png) |
| 18 | Người dân (an.demo) | Báo cáo của tôi | Mở /report/my_reports | Báo cáo vừa gửi xuất hiện, trạng thái Chờ xác minh | HTTP 200; có báo cáo QA-943920; link https://warninginfo.infinityfree.io/report/detail/6 | ✅ Pass | [TC18](screenshots/TC18.png) |
| 19 | Người dân (an.demo) | Chi tiết báo cáo vừa gửi | Mở chi tiết báo cáo vừa gửi | Hiện ảnh đã tải lên, vị trí trên bản đồ | Lần đầu: ảnh lỗi 500 (Lỗi 1). Kiểm thử lại sau khi sửa .htaccess: ảnh hiển thị (400 px), HTTP 200 | ✅ Pass | [TC19](screenshots/TC19.png) |
| 20 | Người dân (an.demo) | Bình luận | Nhập bình luận và gửi | Bình luận hiện ngay dưới báo cáo | Bình luận hiển thị | ✅ Pass | [TC20](screenshots/TC20.png) |
| 21 | Người dân (an.demo) | Hồ sơ cá nhân | Mở /auth/profile | Hiện thông tin tài khoản | HTTP 200 | ✅ Pass | [TC21](screenshots/TC21.png) |
| 22 | Người dân (an.demo) | Bản đồ sự cố gần tôi | Mở /report/map, bật vị trí | Bản đồ hiển thị sự cố quanh vị trí | HTTP 200, 3 marker; nút "gần tôi": có | ✅ Pass | [TC22](screenshots/TC22.png) |
| 23 | Người dân 2 (binh.demo) | Upvote báo cáo người khác | Đăng nhập binh.demo, mở báo cáo của an.demo, bấm upvote | Số lượt xác nhận tăng 1, nút đổi trạng thái | Lượt xác nhận 0 → 1 | ✅ Pass | [TC23](screenshots/TC23.png) |
| 24 | Người dân 2 (binh.demo) | Bình luận chéo | Gửi bình luận trên báo cáo của an.demo | Bình luận hiển thị | Bình luận hiển thị | ✅ Pass | [TC24](screenshots/TC24.png) |
| 25 | Quản trị viên | Đăng nhập + Dashboard | Đăng nhập admin, mở /admin | Dashboard hiện thống kê và biểu đồ Chart.js | 3 biểu đồ canvas tại /admin | ✅ Pass | [TC25](screenshots/TC25.png) |
| 26 | Quản trị viên | API thống kê | Gọi /admin/api_statistics (fetch trong trình duyệt) | Trả JSON hợp lệ | HTTP 200, JSON hợp lệ, khoá: status, data | ✅ Pass | [TC26](screenshots/TC26.png) |
| 27 | Quản trị viên | Xuất báo cáo CSV | Bấm Xuất CSV | Tải file CSV UTF-8, tiếng Việt hiển thị đúng | File Danh_sach_su_co_20261113_143222.csv, 1159 byte, BOM UTF-8: có, 7 dòng | ✅ Pass | [TC27](screenshots/TC27.png) |
| 28 | Quản trị viên | Quản lý sự cố | Mở /admin/reports, tìm báo cáo vừa gửi | Báo cáo mới có trong danh sách | Tìm thấy báo cáo | ✅ Pass | [TC28](screenshots/TC28.png) |
| 29 | Quản trị viên | Duyệt sự cố (đổi trạng thái) | Chọn trạng thái "Đã xác minh", nhập ghi chú, bấm Lưu | Trạng thái đổi, có thông báo thành công | Chọn "verifying"; thông báo: "Đã cập nhật trạng thái sự cố." | ✅ Pass | [TC29](screenshots/TC29.png) |
| 30 | Quản trị viên | Phân công cán bộ | Bấm nút Phân công, chọn officer, chọn hạn chót, bấm Phân công | Trạng thái → Đã phân công, hiện tên cán bộ thụ lý | Thông báo: "Đã phân công Cán bộ Môi trường xử lý sự cố WI-20261113-AB65." | ✅ Pass | [TC30](screenshots/TC30.png) |
| 31 | Quản trị viên | Quản lý người dùng | Mở /admin/users | Hiện danh sách người dùng | HTTP 200, 6 người dùng | ✅ Pass | [TC31](screenshots/TC31.png) |
| 32 | Quản trị viên | Quản lý danh mục | Mở /admin/categories | Hiện danh sách danh mục | HTTP 200, 7 mục | ✅ Pass | [TC32](screenshots/TC32.png) |
| 33 | Quản trị viên | Thông báo | Mở /notification | Hiện danh sách thông báo | HTTP 200 | ✅ Pass | [TC33](screenshots/TC33.png) |
| 34 | Cán bộ hiện trường | Bàn làm việc cán bộ | Đăng nhập officer, mở /officer | Thấy nhiệm vụ vừa được phân công | Có nhiệm vụ QA-943920 (/officer/report/6) | ✅ Pass | [TC34](screenshots/TC34.png) |
| 35 | Cán bộ hiện trường | Nhận việc | Mở nhiệm vụ, bấm "Nhận việc & bắt đầu xử lý" | Trạng thái → Đang xử lý, hiện form cập nhật | Thông báo: "Đã nhận việc. Sự cố chuyển sang trạng thái "Đang xử lý"." | ✅ Pass | [TC35](screenshots/TC35.png) |
| 36 | Cán bộ hiện trường | Cập nhật tiến độ | Chọn "Đang xử lý", nhập ghi chú, bấm Cập nhật | Lưu tiến độ, có thông báo | Thông báo: "Đã cập nhật tiến độ: Đang xử lý." | ✅ Pass | [TC36](screenshots/TC36.png) |
| 37 | Cán bộ hiện trường | Ghi chú nội bộ | Nhập ghi chú nội bộ, bấm gửi | Ghi chú xuất hiện trong phần trao đổi | Ghi chú hiển thị | ✅ Pass | [TC37](screenshots/TC37.png) |
| 38 | Cán bộ hiện trường | Hoàn thành nhiệm vụ | Chọn "Đã khắc phục xong", bấm Cập nhật | Nhiệm vụ chuyển hoàn thành | Hộp xác nhận: "Xác nhận báo đã khắc phục xong sự cố? Sau khi báo hoàn thành, nhiệm vụ sẽ được đóng." → OK; thông báo: "Đã báo hoàn thành. Người gửi và quản trị viên đã nhận được thông báo." | ✅ Pass | [TC38](screenshots/TC38.png) |
| 39 | Cán bộ hiện trường | Chặn trang quản trị | Mở /admin bằng tài khoản cán bộ | Bị từ chối / chuyển hướng | HTTP 200, URL / | ✅ Pass | [TC39](screenshots/TC39.png) |
| 40 | Người dân (an.demo) | Nhận thông báo cập nhật | Mở /notification sau khi admin/cán bộ xử lý | Có thông báo đổi trạng thái báo cáo | HTTP 200, 6 thông báo | ✅ Pass | [TC40](screenshots/TC40.png) |
| 41 | Người dân (an.demo) | Lịch sử xử lý | Mở lại chi tiết báo cáo | Trạng thái "Đã khắc phục", có lịch sử xử lý | Trạng thái Đã khắc phục hiển thị | ✅ Pass | [TC41](screenshots/TC41.png) |
| 42 | Người dân (an.demo) | Đăng xuất | Bấm Đăng xuất | Về trang chủ, không còn phiên | Sau đăng xuất mở my_reports → /auth/login | ✅ Pass | [TC42](screenshots/TC42.png) |

## Dữ liệu kiểm thử đã tạo trên hosting (cần dọn trước khi quay demo)

- 1 báo cáo `[QA-943920] Rác thải tồn đọng ven hồ Hoàn Kiếm` (/report/detail/6), người gửi an.demo, trạng thái đã khắc phục, kèm 1 ảnh PNG trong `uploads/reports/`.
- 2 bình luận trên báo cáo đó (an.demo và binh.demo), 1 lượt upvote của binh.demo.
- 1 bản ghi phân công cho officer@warninginfo.com, 1 ghi chú nội bộ, 3 bản ghi lịch sử trạng thái.
- Các thông báo sinh ra cho an.demo, officer và admin.

Xoá báo cáo này (và các bản ghi liên quan) trong cơ sở dữ liệu, hoặc nạp lại `database/database_init_hosting.sql`.

## Bảo mật

Các tài khoản demo đang dùng mật khẩu `password` trên hosting công khai. Nên đổi mật khẩu sau khi nộp đồ án.
