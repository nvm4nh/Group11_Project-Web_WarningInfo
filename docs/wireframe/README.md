# Wireframe & quy ước giao diện – WarningInfo (TV4)

Wireframe low-fi các trang chính (Milestone 1). Số màu đỏ trên hình tương ứng với chú thích bên dưới mỗi ảnh.

| File | Trang |
|---|---|
| `01-trang-chu.png` | Trang chủ |
| `02-danh-sach-canh-bao.png` | Danh sách cảnh báo (tìm kiếm, lọc, phân trang) |
| `03-chi-tiet-canh-bao.png` | Chi tiết cảnh báo (xác nhận, tiến trình, bình luận) |
| `04-gui-bao-cao.png` | Form gửi báo cáo |
| `05-dang-nhap-dang-ky.png` | Đăng nhập / Đăng ký |
| `06-tai-khoan.png` | Tài khoản cá nhân |
| `07-mobile.png` | Bố cục trên điện thoại |

## Quy ước giao diện (style guide)

- **Framework:** Bootstrap 5.3 + Bootstrap Icons, responsive theo breakpoint của Bootstrap (≥992px: 2 cột; <768px: 1 cột).
- **Font chữ:** Be Vietnam Pro (Google Fonts) – hiển thị tốt tiếng Việt.
- **Màu chính:** xanh môi trường `#0f766e`; màu nhấn (nút "Gửi báo cáo") `#f59e0b`.
- **Màu mức độ:** Thấp `#16a34a` · Trung bình `#d97706` · Cao `#ea580c` · Nghiêm trọng `#dc2626`.
- **Trạng thái:** Chờ duyệt (xám) · Đang xử lý (tím) · Đã giải quyết (xanh lá) · Bị từ chối (đỏ nhạt).
- **Thẻ cảnh báo** là thành phần dùng lại ở trang chủ, danh sách, tài khoản: ảnh – mức độ – danh mục – trạng thái – tiêu đề – mô tả ngắn – địa chỉ – thời gian – số xác nhận/bình luận.
- **Bo góc** 10–14px, đổ bóng nhẹ; nút chính bo 10px.
