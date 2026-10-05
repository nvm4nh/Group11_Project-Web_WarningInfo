# Sơ đồ Cơ Sở Dữ Liệu (ERD) - WarningInfo

Dưới đây là sơ đồ quan hệ thực thể (ERD) cho hệ thống báo cáo sự cố môi trường.

```mermaid
erDiagram
    users {
        int id PK
        varchar full_name
        varchar email
        varchar password
        enum role "admin, user"
        timestamp created_at
    }

    categories {
        int id PK
        varchar name
        text description
        timestamp created_at
    }

    reports {
        int id PK
        int user_id FK
        int category_id FK
        varchar title
        text description
        decimal latitude
        decimal longitude
        varchar address
        varchar image_url
        enum severity "low, medium, high, critical"
        enum status "pending, processing, resolved, rejected"
        timestamp created_at
        timestamp updated_at
    }

    comments {
        int id PK
        int report_id FK
        int user_id FK
        text content
        timestamp created_at
    }

    upvotes {
        int user_id PK, FK
        int report_id PK, FK
        timestamp created_at
    }

    users ||--o{ reports : "tạo"
    categories ||--o{ reports : "phân loại"
    reports ||--o{ comments : "có"
    users ||--o{ comments : "viết"
    users ||--o{ upvotes : "thích"
    reports ||--o{ upvotes : "được thích"
```

## Các bảng chính:
1. **users:** Lưu thông tin người dùng và phân quyền (role admin/user).
2. **categories:** Lưu các danh mục ô nhiễm (Rác thải, Khói bụi, Nước...).
3. **reports:** Bảng quan trọng nhất, lưu chi tiết một báo cáo sự cố kèm tọa độ (latitude, longitude) và hình ảnh.
4. **comments:** Bình luận của người dùng về một sự cố.
5. **upvotes:** Lưu lượt "Tôi cũng thấy vấn đề này" (Upvote) của cộng đồng.
