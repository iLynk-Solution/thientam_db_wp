# 📚 Tài Liệu Hướng Dẫn Tích Hợp WordPress REST API Cho Dịch Vụ Thiên Tâm

> **Base URL**: `https://site.thientam68.com/wp-json`  
> **Namespace**: `thientam/v1`  
> **Phiên bản**: `1.0.0`

---

## 📌 Danh Sách Endpoints

| STT | Phương thức | Endpoint | Mô tả |
| :--- | :--- | :--- | :--- |
| **1** | `GET` | `/wp-json/thientam/v1/services` | Lấy danh sách rút gọn các dịch vụ (**slug, thumbnail_url, featured_image, mô tả ngắn**) |
| **2** | `GET` | `/wp-json/thientam/v1/services/{slug}` | Lấy toàn bộ dữ liệu chi tiết của 1 dịch vụ (Hero, Overview, Bảng giá, Quyền lợi) |
| **3** | `GET` | `/wp-json/wp/v2/pages?slug={slug}` | API mặc định WordPress (đã mở rộng `thumbnail_url` & `service_details`) |

---

## 1. Lấy Danh Sách Dịch Vụ (Rút gọn)

Endpoint này được tối ưu nhẹ nhất để phục vụ trang danh sách dịch vụ / menu / thẻ dịch vụ ngoài trang chủ.

### Yêu cầu:
- **Phương thức**: `GET`
- **URL**: `https://site.thientam68.com/wp-json/thientam/v1/services`
- **Header**: Không yêu cầu xác thực (`public`)

### Phản hồi mẫu (Response 200 OK):
```json
{
  "success": true,
  "count": 6,
  "data": [
    {
      "id": 2001,
      "slug": "su-vat-su-viec",
      "title": "Sự vật • Sự việc",
      "description": "Thêm một góc nhìn khách quan để xem xét, định hướng và lựa chọn phương án phù hợp.",
      "thumbnail_url": "https://site.thientam68.com/wp-content/uploads/2026/08/banner-service-su-vat-su-viec.png",
      "featured_image": {
        "id": 125,
        "url": "https://site.thientam68.com/wp-content/uploads/2026/08/banner-service-su-vat-su-viec.png",
        "alt": "Sự vật • Sự việc"
      },
      "menu_order": 1
    },
    {
      "id": 2002,
      "slug": "phong-thuy-so",
      "title": "Phong thủy số",
      "description": "Ứng dụng các nguyên lý phong thủy để phân tích và lựa chọn những con số phù hợp cho công việc, tài lộc và cuộc sống.",
      "thumbnail_url": "https://site.thientam68.com/wp-content/uploads/2026/08/banner-service-phong-thuy-so.png",
      "featured_image": {
        "id": 126,
        "url": "https://site.thientam68.com/wp-content/uploads/2026/08/banner-service-phong-thuy-so.png",
        "alt": "Phong thủy số"
      },
      "menu_order": 2
    },
    {
      "id": 2003,
      "slug": "phong-thuy-nha-o-doanh-nghiep",
      "title": "Phong thủy nhà ở & doanh nghiệp",
      "description": "Khảo sát và sắp xếp không gian sống, làm việc dựa trên hiện trạng thực tế và các nguyên lý phong thủy phù hợp.",
      "thumbnail_url": "https://site.thientam68.com/wp-content/uploads/2026/08/banner-service-phong-thuy-nha-o-doanh-nghiep.png",
      "featured_image": {
        "id": 127,
        "url": "https://site.thientam68.com/wp-content/uploads/2026/08/banner-service-phong-thuy-nha-o-doanh-nghiep.png",
        "alt": "Phong thủy nhà ở & doanh nghiệp"
      },
      "menu_order": 3
    }
  ]
}
```

---

## 2. Lấy Chi Tiết Một Dịch Vụ Theo Slug

Endpoint này trả về toàn bộ dữ liệu 4 phần (Hero Banner, Tổng quan Overview cards, Bảng giá Pricing table, Quyền lợi Benefits).

### Danh sách Slug có sẵn:
1. `su-vat-su-viec` — Sự vật • Sự việc
2. `phong-thuy-so` — Phong thủy số
3. `phong-thuy-nha-o-doanh-nghiep` — Phong thủy nhà ở & doanh nghiệp
4. `tu-vi-bat-tu` — Tử vi • Bát tự • Kỳ môn mệnh
5. `tu-van-gia-dao` — Tư vấn gia đạo
6. `goi-doanh-nghiep` — Gói doanh nghiệp

### Yêu cầu:
- **Phương thức**: `GET`
- **URL**: `https://site.thientam68.com/wp-json/thientam/v1/services/{slug}`
- **Ví dụ**: `https://site.thientam68.com/wp-json/thientam/v1/services/phong-thuy-so`

### Phản hồi mẫu (Response 200 OK):
```json
{
  "success": true,
  "data": {
    "id": 2002,
    "slug": "phong-thuy-so",
    "thumbnail_url": "https://site.thientam68.com/wp-content/uploads/2026/08/banner-service-phong-thuy-so.png",
    "featured_image": {
      "id": 126,
      "url": "https://site.thientam68.com/wp-content/uploads/2026/08/banner-service-phong-thuy-so.png",
      "alt": "Phong thủy số"
    },
    "breadcrumb": [
      { "label": "Trang chủ", "href": "/" },
      { "label": "Dịch vụ", "href": "/services" },
      { "label": "Phong thủy số", "href": "/services/phong-thuy-so" }
    ],
    "hero": {
      "eyebrow": "DỊCH VỤ TƯ VẤN CÁ NHÂN",
      "title": "Phong thủy số",
      "subtitle": "Chọn số • Hợp mệnh",
      "description": "Ứng dụng các nguyên lý phong thủy để phân tích và lựa chọn những con số phù hợp cho công việc, tài lộc và cuộc sống.",
      "image": "https://site.thientam68.com/wp-content/uploads/2026/08/banner-service-phong-thuy-so.png",
      "primaryBtn": "Đặt lịch tư vấn",
      "pricingBtn": "Xem bảng giá"
    },
    "navTabs": [
      { "id": "tong-quan", "label": "Tổng quan", "icon": "compass" },
      { "id": "bang-gia", "label": "Bảng giá", "icon": "clipboard" },
      { "id": "quyen-loi", "label": "Quyền lợi", "icon": "shield" },
      { "id": "quy-trinh", "label": "Quy trình", "icon": "user" }
    ],
    "overviewSection": {
      "titlePrefix": "Con số đồng hành",
      "titleHighlight": "cùng hành trình",
      "desc": "Mỗi con số mang một năng lượng riêng và có thể ảnh hưởng đến nhiều khía cạnh trong cuộc sống của bạn.",
      "cards": [
        {
          "icon": "phone",
          "title": "Số đang sử dụng",
          "desc": "Phân tích các con số bạn đang dùng để hiểu được ý nghĩa và tác động hiện tại."
        },
        {
          "icon": "compass",
          "title": "Tìm số phù hợp",
          "desc": "Tìm kiếm những con số phù hợp với mệnh, mục tiêu và mong muốn của bạn."
        },
        {
          "icon": "calendar",
          "title": "Chọn ngày kích hoạt",
          "desc": "Lựa chọn ngày giờ tốt để kích hoạt và phát huy tối đa năng lượng của con số."
        }
      ]
    },
    "pricingSection": {
      "titlePrefix": "Bảng giá",
      "titleHighlight": "Phong thủy số",
      "desc": "Bảng chi phí tư vấn minh bạch, rõ ràng theo từng hạng mục nhằm mang lại giải pháp thấu đáo và thiết thực nhất cho Quý vị.",
      "headers": {
        "stt": "STT",
        "service": "HẠNG MỤC TƯ VẤN",
        "content": "NỘI DUNG",
        "price": "PHÍ TƯ VẤN"
      },
      "items": [
        {
          "stt": "01",
          "service": "Xem các số đang sử dụng",
          "content": "Số điện thoại, số nhà, số xe, tài khoản ngân hàng",
          "price": "1.500.000đ"
        },
        {
          "stt": "02",
          "service": "Tìm số điện thoại phong thủy",
          "content": "Tìm số phù hợp và xem ngày giờ kích hoạt",
          "price": "3.000.000đ"
        },
        {
          "stt": "03",
          "service": "Gói kết hợp",
          "content": "Xem số điện thoại hiện tại + tìm số theo phong thủy",
          "price": "3.800.000đ"
        }
      ],
      "note": "Chi phí dịch vụ không bao gồm chi phí mua SIM hoặc chi phí phát sinh từ bên thứ ba."
    },
    "benefitsSection": {
      "titlePrefix": "Quyền lợi",
      "titleHighlight": "đồng hành",
      "desc": "Thiên Tâm không chỉ dừng lại ở buổi tư vấn mà luôn sẵn sàng đồng hành, hỗ trợ Quý vị thấu đáo trong suốt quá trình áp dụng giải pháp.",
      "content": "Giải đáp kết quả chi tiết, hướng dẫn lựa chọn con số phù hợp theo mệnh, hỗ trợ ứng dụng và kích hoạt số để an tâm phát triển công việc và cuộc sống."
    }
  }
}
```

---

## 3. Mã Nguồn Gọi API Trong Next.js

```typescript
const WP_API_URL = process.env.NEXT_PUBLIC_WP_API_URL || 'https://site.thientam68.com/wp-json';

// 1. Lấy danh sách rút gọn các dịch vụ (slug, thumbnail, description)
export async function getServicesList() {
  try {
    const res = await fetch(`${WP_API_URL}/thientam/v1/services`, {
      next: { revalidate: 60 },
    });
    if (!res.ok) return [];
    const json = await res.json();
    return json.data || [];
  } catch (error) {
    console.error('Error fetching services list:', error);
    return [];
  }
}

// 2. Lấy đầy đủ chi tiết dịch vụ theo Slug
export async function getServiceBySlug(slug: string) {
  try {
    const res = await fetch(`${WP_API_URL}/thientam/v1/services/${slug}`, {
      next: { revalidate: 60 },
    });
    if (!res.ok) return null;
    const json = await res.json();
    return json.data;
  } catch (error) {
    console.error('Error fetching service detail:', error);
    return null;
  }
}
```
