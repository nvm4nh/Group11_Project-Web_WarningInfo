# Ảnh chụp Milestone 3 – phần của TV4 (Lê Trọng Hiếu)

Ba việc được giao: **CSRF token cho các form**, **hộp thông báo cho người dân và cán bộ**, **giao diện riêng cho cán bộ**.
Chụp ở 375px (điện thoại) và 1280px (máy tính). Ảnh minh chứng trong sự cố là ảnh minh hoạ dùng để kiểm thử.

| # | Nội dung | 375px | 1280px |
|---|---|---|---|
| 01 | Gửi form không có / sai mã CSRF → trang báo lỗi 403 | | [01](01-loi-csrf-403-1280.png) |
| 02 | Hộp thông báo `/notification` (tab Tất cả / Chưa đọc, đánh dấu đã đọc) | [02](02-hop-thong-bao-375.png) | [02](02-hop-thong-bao-1280.png) |
| 03 | Chuông thông báo trên thanh menu (số chưa đọc + 6 thông báo mới, nạp bằng AJAX) | [03](03-chuong-thong-bao-375.png) | [03](03-chuong-thong-bao-1280.png) |
| 04 | Bàn làm việc cán bộ `/officer` (thống kê + danh sách sự cố được giao) | [04](04-ban-lam-viec-can-bo-375.png) | [04](04-ban-lam-viec-can-bo-1280.png) |
| 05 | Trang xử lý sự cố: cập nhật tiến độ, liên hệ người gửi, lịch sử, ghi chú nội bộ | [05](05-xu-ly-su-co-375.png) | [05](05-xu-ly-su-co-1280.png) |
| 06 | Sự cố mới được giao: nút "Nhận việc & bắt đầu xử lý" | [06](06-nhan-viec-375.png) | [06](06-nhan-viec-1280.png) |

## Luồng xử lý đã kiểm thử

1. Admin phân công sự cố → cán bộ nhận thông báo (chuông đỏ) → bấm thông báo mở thẳng trang xử lý.
2. Cán bộ bấm **Nhận việc** → sự cố "Đang xử lý"; người gửi và admin được thông báo.
3. Cán bộ cập nhật tiến độ / báo **Cần bổ sung thông tin** / **Đã khắc phục** (2 trạng thái sau bắt buộc ghi chú) → ghi lịch sử + thông báo cho người gửi.
4. Ghi chú nội bộ của cán bộ chỉ cán bộ và admin thấy, người dân không thấy.
5. Mọi form POST (và AJAX POST) đều phải có mã CSRF hợp lệ, nếu không bị chặn với mã 403.
