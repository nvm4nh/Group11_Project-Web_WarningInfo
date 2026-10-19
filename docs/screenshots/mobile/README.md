# Ảnh chụp giao diện Mobile – WarningInfo

Người thực hiện: **Lê Trọng Hiếu (TV4 – Frontend UI/UX & Responsive)** · Milestone 2

Ảnh chụp trên trình duyệt Chromium ở 3 kích thước theo yêu cầu: **375px** (iPhone SE/X), **414px** (iPhone Plus/11) và **768px** (iPad dọc).
Tên file: `<chiều rộng>-<số thứ tự>-<trang>.png`. Ảnh chụp toàn trang, riêng menu và khung phóng to ảnh chỉ chụp màn hình đầu.

| Trang | 375px | 414px | 768px |
|---|---|---|---|
| Trang chủ | [375-01](375-01-trang-chu.png) | [414-01](414-01-trang-chu.png) | [768-01](768-01-trang-chu.png) |
| Menu điều hướng (nút ☰) | [375-02](375-02-menu-dieu-huong.png) | | |
| Danh sách cảnh báo | [375-03](375-03-danh-sach-canh-bao.png) | | |
| Chi tiết báo cáo – thư viện ảnh | [375-04](375-04-chi-tiet-thu-vien-anh.png) | [414-04](414-04-chi-tiet-thu-vien-anh.png) | [768-04](768-04-chi-tiet-thu-vien-anh.png) |
| Chi tiết – bấm ảnh để phóng to | [375-05](375-05-chi-tiet-phong-to-anh.png) | | [768-05](768-05-chi-tiet-phong-to-anh.png) |
| Gửi báo cáo – xem trước ảnh + GPS | [375-06](375-06-gui-bao-cao-xem-truoc-anh-gps.png) | [414-06](414-06-gui-bao-cao-xem-truoc-anh-gps.png) | [768-06](768-06-gui-bao-cao-xem-truoc-anh-gps.png) |
| Gửi báo cáo – báo lỗi khi bỏ trống | [375-07](375-07-gui-bao-cao-bao-loi.png) | | |
| Đăng nhập | [375-08](375-08-dang-nhap.png) | | |
| Báo cáo của tôi | [375-09](375-09-bao-cao-cua-toi.png) | | |
| Quản trị – danh sách sự cố | [375-10](375-10-quan-tri-bao-cao.png) | | [768-10](768-10-quan-tri-bao-cao.png) |

## Kết quả kiểm tra responsive

- Không trang nào bị tràn ngang (cuộn ngang toàn trang) ở 375 / 414 / 768px.
- Navbar thu gọn thành nút ☰ dưới 992px, menu mở ra đầy đủ link + ô tìm kiếm + nút Gửi báo cáo.
- Bảng dữ liệu (Báo cáo của tôi, Quản trị) giữ độ rộng tối thiểu và vuốt ngang bên trong khung, chữ không bị bể.
- Badge mức độ "Cao" đã có màu cam (trước đây bị trong suốt do thiếu lớp `.bg-orange`).

> Ảnh minh chứng trong các ảnh chụp là **ảnh minh hoạ** dùng để kiểm thử giao diện thư viện ảnh.
> Ảnh thật sẽ hiển thị khi phần upload của TV3 lưu file vào `public/uploads/reports/` và nạp `$report->images` trong `ReportModel::getById()`.
