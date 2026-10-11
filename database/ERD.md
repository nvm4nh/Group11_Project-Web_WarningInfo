# Sơ đồ Cơ Sở Dữ Liệu (ERD) Chuẩn Hóa 10 Bảng - WarningInfo

Dưới đây là sơ đồ quan hệ thực thể (ERD) phiên bản chuẩn hóa cho hệ thống Báo cáo & Cảnh báo Sự cố Môi trường **WarningInfo**, tích hợp đầy đủ 10 bảng nghiệp vụ và tương thích hoàn toàn với kiến trúc Backend PHP OOP MVC.

```mermaid
erDiagram
    users {
        int id PK
        varchar full_name
        varchar email UK
        varchar phone
        varchar password
        enum role "admin, officer, user"
        enum account_status "active, inactive, locked"
        timestamp created_at
        timestamp updated_at
    }

    categories {
        int id PK
        varchar name UK
        varchar description
        boolean is_active
        timestamp created_at
    }

    reports {
        int id PK
        varchar report_code UK
        int user_id FK
        int category_id FK
        varchar title
        text description
        datetime incident_time
        varchar address
        decimal latitude
        decimal longitude
        enum priority "low, medium, high, critical"
        enum status "new, received, need_more_info, verifying, verified, assigned, in_progress, resolved, closed, rejected"
        boolean is_public
        varchar contact_name
        varchar contact_email
        varchar contact_phone
        text resolution_note
        datetime resolved_at
        timestamp created_at
        timestamp updated_at
    }

    report_images {
        int id PK
        int report_id FK
        varchar file_path
        varchar original_file_name
        varchar mime_type
        bigint file_size_bytes
        int uploaded_by FK
        boolean is_public
        timestamp uploaded_at
    }

    report_status_history {
        int id PK
        int report_id FK
        varchar old_status
        varchar new_status
        int changed_by FK
        text note
        timestamp changed_at
    }

    assignments {
        int id PK
        int report_id FK
        int officer_id FK
        int assigned_by FK
        text assignment_note
        enum assignment_status "assigned, accepted, in_progress, completed, cancelled"
        timestamp assigned_at
        datetime completed_at
    }

    notifications {
        int id PK
        int user_id FK
        int report_id FK
        varchar title
        text message
        varchar notification_type
        boolean is_read
        timestamp created_at
        datetime read_at
    }

    comments {
        int id PK
        int report_id FK
        int user_id FK
        text content
        boolean is_internal
        timestamp created_at
    }

    admin_logs {
        int id PK
        int admin_id FK
        varchar action
        varchar target_type
        int target_id
        json details
        varchar ip_address
        varchar user_agent
        timestamp created_at
    }

    upvotes {
        int id PK
        int user_id FK
        int report_id FK
        timestamp created_at
    }

    users ||--o{ reports : "gửi báo cáo"
    categories ||--o{ reports : "phân loại"
    reports ||--o{ report_images : "chứa hình ảnh"
    users ||--o{ report_images : "tải lên"
    reports ||--o{ report_status_history : "lịch sử xử lý"
    users ||--o{ report_status_history : "cập nhật bởi"
    reports ||--o{ assignments : "được phân công"
    users ||--o{ assignments : "cán bộ thụ lý"
    reports ||--o{ comments : "có trao đổi"
    users ||--o{ comments : "bình luận"
    users ||--o{ notifications : "nhận thông báo"
    reports ||--o{ notifications : "liên quan sự cố"
    users ||--o{ upvotes : "quan tâm"
    reports ||--o{ upvotes : "được ủng hộ"
    users ||--o{ admin_logs : "thực hiện tác vụ"
```

---

## Danh mục 10 bảng nghiệp vụ:
1. **users:** Lưu tài khoản, phân quyền 3 cấp (`admin`, `officer`, `user`), trạng thái tài khoản.
2. **categories:** 7 danh mục sự cố môi trường chuẩn hóa theo thanh menu giao diện.
3. **reports:** Bảng sự cố trọng tâm, mã sự cố `report_code`, toạ độ GPS, 10 trạng thái và 4 mức ưu tiên.
4. **report_images:** Quản lý tệp ảnh/video minh chứng tải lên.
5. **report_status_history:** Ghi lại tiến trình thay đổi trạng thái từ lúc tiếp nhận đến khi xử lý xong.
6. **assignments:** Phân công trách nhiệm cho cán bộ môi trường thụ lý sự cố.
7. **notifications:** Gửi thông báo tự động cho người dân khi trạng thái báo cáo thay đổi.
8. **comments:** Trao đổi giữa người dân và cán bộ (hỗ trợ cờ nội bộ `is_internal`).
9. **admin_logs:** Nhật ký bảo mật lưu vết mọi tác vụ quản trị kèm dữ liệu JSON.
10. **upvotes:** Lượt xác nhận và quan tâm của cộng đồng (chống spam vote bằng UNIQUE).
