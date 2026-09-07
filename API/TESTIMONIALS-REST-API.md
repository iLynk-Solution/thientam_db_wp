# 📚 Tài Liệu Hướng Dẫn Tích Hợp WordPress REST API Cho Cảm Nhận Khách Hàng (Testimonials)

> **Base URL**: `https://site.thientam68.com/wp-json`  
> **Namespace**: `thientam/v1`  
> **Endpoint**: `/wp-json/thientam/v1/testimonials`  
> **Phiên bản**: `1.0.0`

---

## 📌 Tổng Quan Post Type `testimonial`

- **Tên Post Type**: `testimonial` (Hiển thị trong Admin: **Cảm nhận KH**)
- **Không tạo Single Page riêng**: Được cấu hình `publicly_queryable => false`, `has_archive => false`, `rewrite => false` (truy cập link trực tiếp sẽ tự động 301 chuyển hướng về trang chủ).
- **Thứ tự lấy dữ liệu**: Lấy theo bài viết **Mới nhất** (`date` giảm dần / `DESC`).
- **Ký tự Initials & Màu sắc thẻ**: Được giao diện Frontend Next.js tự động tạo từ Họ Tên và phân phối màu sắc xen kẽ.
- **Không phụ thuộc plugin**: 100% code Native thuần của WordPress Core.

---

## 🚀 REST API Endpoint

### 1. Lấy Danh Sách Cảm Nhận Khách Hàng (Testimonials)

Endpoint này trả về danh sách tất cả các đánh giá/cảm nhận đã xuất bản, được sắp xếp từ mới nhất trở về trước.

#### Yêu cầu (Request):
- **Phương thức (Method)**: `GET`
- **URL**: `https://site.thientam68.com/wp-json/thientam/v1/testimonials`
- **Header**: Không yêu cầu xác thực (`Public API`)
- **Cache**: Tự động vô hiệu hóa cache LiteSpeed/Redis qua tham số `_t={timestamp}` khi gọi từ client.

#### Cấu trúc Phản hồi (Response 200 OK):

```json
{
  "success": true,
  "count": 7,
  "data": [
    {
      "id": 3001,
      "name": "Nguyễn Văn Minh",
      "role": "Giám đốc Doanh nghiệp BĐS",
      "quote": "Sau khi được Thiên Tâm tư vấn cải tạo lại hướng bàn làm việc và bố trí phòng khách, công việc kinh doanh của công ty tôi khởi sắc rõ rệt. Cảm ơn sự tận tâm của đội ngũ!",
      "rating": 5,
      "avatar": ""
    },
    {
      "id": 3002,
      "name": "Trần Thu Hà",
      "role": "Gia chủ tại Hà Nội",
      "quote": "Gia đình tôi từng gặp nhiều xáo trộn không rõ nguyên do. Nhờ Thiên Tâm tư vấn phong thủy nhà ở và hướng dẫn bài trí phù hợp bản mệnh, không khí gia đình rộn rã tiếng cười trở lại.",
      "rating": 5,
      "avatar": "https://site.thientam68.com/wp-content/uploads/2026/08/avatar-tran-thu-ha.jpg"
    }
  ]
}
```

---

## 📋 Chi Tiết Các Trường Dữ Liệu (Field Specifications)

| Tên trường | Kiểu dữ liệu | Mô tả |
| :--- | :--- | :--- |
| `id` | `number` | ID của bài viết trong WordPress. |
| `name` | `string` | Họ tên khách hàng (*Tiêu đề bài viết - post_title*). |
| `role` | `string` | Chức vụ / Nghề nghiệp / Địa phương (*meta: `_testimonial_role`*). |
| `quote` | `string` | Nội dung lời cảm nhận, đánh giá (*meta: `_testimonial_quote`*). |
| `rating` | `number` | Số sao đánh giá từ 1 đến 5 (*meta: `_testimonial_rating`*). |
| `avatar` | `string` | URL ảnh đại diện (*Featured Image*). Trả về chuỗi rỗng `""` nếu không chọn ảnh. |

---

## 💻 Code Mẫu Tích Hợp Phía Next.js (TypeScript)

```typescript
export interface TestimonialItem {
  id?: number | string;
  name: string;
  role: string;
  quote: string;
  rating?: number;
  avatar?: string;
  initials?: string;
}

// Helper tự sinh Initials từ Tên khách hàng phía UI
export function getInitials(name: string): string {
  if (!name) return "TT";
  const words = name.trim().split(/\s+/);
  if (words.length >= 2) {
    return (words[0][0] + words[words.length - 1][0]).toUpperCase();
  }
  return name.slice(0, 2).toUpperCase();
}
```
