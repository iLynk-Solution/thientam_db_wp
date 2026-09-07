# 📚 Tài Liệu Hướng Dẫn Tích Hợp WordPress REST API Cho Tin Tức & Bài Viết (Posts)

> **Base URL**: `https://site.thientam68.com/wp-json`  
> **Namespace**: `thientam/v1`  
> **Endpoints**:
> - Danh mục tin tức: `/wp-json/thientam/v1/news/categories` (hoặc `/wp-json/thientam/v1/categories`)
> - Thẻ bài viết (Tags): `/wp-json/thientam/v1/news/tags`
> - Danh sách bài viết: `/wp-json/thientam/v1/news`
> - Bài viết theo chuyên mục: `/wp-json/thientam/v1/news/category/{slug}` (hoặc `/wp-json/thientam/v1/categories/{slug}`)
> - Chi tiết bài viết: `/wp-json/thientam/v1/news/{slug}`
> **Phiên bản**: `1.2.0`

---

## 📌 Tổng Quan

- **Post Type**: Sử dụng trực tiếp post type mặc định của WordPress là **`post`** (**Bài viết / Posts**).
- **Taxonomy**: Phân loại danh mục theo **`category`** (**Chuyên mục** mặc định của WordPress).
- **Trường dữ liệu**: Tiêu đề (*Title*), Soạn thảo nội dung (*Content*), Tóm tắt (*Excerpt*), Ảnh đại diện (*Featured Image*), Tác giả (*Author*), Chuyên mục (*Categories*), Thẻ (*Tags*).
- **Thứ tự lấy dữ liệu**: Lấy theo bài viết **Mới nhất** (`date` giảm dần / `DESC`).
- **Không phụ thuộc plugin**: 100% code Native thuần của WordPress Core.

---

## 🚀 REST API Endpoints

### 1. Lấy Danh Sách Danh Mục Tin Tức (News Categories)

Endpoint này trả về danh sách tất cả các chuyên mục / danh mục bài viết từ mục **Chuyên mục (Categories)** của WordPress (tự động loại bỏ danh mục mặc định *uncategorized* / *chưa phân loại*).

#### Yêu cầu (Request):
- **Phương thức (Method)**: `GET`
- **URL**: `https://site.thientam68.com/wp-json/thientam/v1/news/categories` *(Alias: `/wp-json/thientam/v1/categories`)*
- **Tham số tùy chọn (Query Params)**:
  - `hide_empty`: `true` | `false` (Mặc định `false` - lấy toàn bộ danh mục kể cả chưa có bài viết)
  - `parent`: `number` (Lọc theo ID chuyên mục cha, ví dụ `parent=0` để lấy chuyên mục gốc)
  - `orderby`: `name` | `count` | `id` | `slug` (Mặc định `name`)
  - `order`: `ASC` | `DESC` (Mặc định `ASC`)

#### Cấu trúc Phản hồi (Response 200 OK):

```json
{
  "success": true,
  "count": 4,
  "data": [
    {
      "id": 12,
      "name": "Phong thủy nhà ở",
      "slug": "phong-thuy-nha-o",
      "description": "Kiến thức và tư vấn phong thủy nhà ở, phòng khách, phòng ngủ và nhà bếp",
      "count": 8,
      "parent": 0,
      "parent_name": "",
      "link": "https://site.thientam68.com/category/phong-thuy-nha-o/"
    },
    {
      "id": 15,
      "name": "Tử vi & Bản mệnh",
      "slug": "tu-vi-ban-menh",
      "description": "Luận giải tử vi, bát tự và ngũ hành bản mệnh",
      "count": 5,
      "parent": 0,
      "parent_name": "",
      "link": "https://site.thientam68.com/category/tu-vi-ban-menh/"
    },
    {
      "id": 18,
      "name": "Phong thủy doanh nghiệp",
      "slug": "phong-thuy-doanh-nghiep",
      "description": "Tư vấn phong thủy văn phòng, cửa hàng kinh doanh",
      "count": 4,
      "parent": 0,
      "parent_name": "",
      "link": "https://site.thientam68.com/category/phong-thuy-doanh-nghiep/"
    },
    {
      "id": 20,
      "name": "Vật phẩm phong thủy",
      "slug": "vat-pham-phong-thuy",
      "description": "Cách chọn và khai quang linh vật, vật phẩm may mắn",
      "count": 6,
      "parent": 0,
      "parent_name": "",
      "link": "https://site.thientam68.com/category/vat-pham-phong-thuy/"
    }
  ]
}
```

---

### 2. Lấy Danh Sách Tin Tức / Bài Viết (Posts)

Endpoint này trả về danh sách các bài viết mới nhất từ mục **Bài viết (Posts)**, hỗ trợ lọc theo chuyên mục, tìm kiếm và phân trang.

#### Yêu cầu (Request):
- **Phương thức (Method)**: `GET`
- **URL**: `https://site.thientam68.com/wp-json/thientam/v1/news`
- **Tham số tùy chọn (Query Params)**:
  - `category`: `string` (Slug của chuyên mục, ví dụ: `?category=phong-thuy-nha-o`)
  - `category_id`: `number` (ID của chuyên mục, ví dụ: `?category_id=12`)
  - `per_page`: `number` (Số lượng bài viết trên 1 trang, mặc định `20`)
  - `page`: `number` (Trang hiện tại, mặc định `1`)
  - `search`: `string` (Từ khóa tìm kiếm tiêu đề / nội dung)
- **Header**: Không yêu cầu xác thực (`Public API`)
- **Cache**: Tự động vô hiệu hóa cache LiteSpeed/Redis qua tham số `_t={timestamp}` khi gọi từ client.

#### Cấu trúc Phản hồi (Response 200 OK):

```json
{
  "success": true,
  "count": 2,
  "total": 15,
  "total_pages": 8,
  "data": [
    {
      "id": 4001,
      "slug": "bi-quyet-bo-tri-phong-khach-thu-hut-tai-loc-va-vuong-khi-cho-gia-chu",
      "title": "Bí quyết bố trí phòng khách thu hút tài lộc và vượng khí cho gia chủ",
      "category": "Phong thủy nhà ở",
      "desc": "Khám phá cách sắp đặt vị trí bàn ghế, ánh sáng và màu sắc hợp bản mệnh giúp không gian sinh hoạt luôn tràn đầy sinh khí.",
      "date": "18/08/2026 14:08:00",
      "author": "Chuyên gia Thiên Tâm",
      "image": "https://site.thientam68.com/wp-content/uploads/2026/08/banner-home-1.png"
    },
    {
      "id": 4002,
      "slug": "hieu-dung-ve-ngu-hanh-tuong-sinh-chia-khoa-can-bang-tam-tri-va-gia-dao",
      "title": "Hiểu đúng về ngũ hành tương sinh: Chìa khóa cân bằng tâm trí và gia đạo",
      "category": "Tử vi & Bản mệnh",
      "desc": "Ứng dụng triết lý ngũ hành vào đời sống thường nhật để hóa giải xung khắc và nuôi dưỡng những mối quan hệ bền vững.",
      "date": "15/08/2026 09:08:00",
      "author": "Chuyên gia Thiên Tâm",
      "image": "https://site.thientam68.com/wp-content/uploads/2026/08/brand-showcase.png"
    }
  ]
}
```

---

### 3. Lấy Danh Sách Bài Viết Thuộc Chuyên Mục (Posts by Category)

Endpoint này trả về thông tin chi tiết của chuyên mục cùng danh sách bài viết thuộc chuyên mục đó (bao gồm cả các bài viết trong các chuyên mục con).

#### Yêu cầu (Request):
- **Phương thức (Method)**: `GET`
- **URL**: `https://site.thientam68.com/wp-json/thientam/v1/news/category/{slug}`
  *(Aliases: `/wp-json/thientam/v1/categories/{slug}/news`, `/wp-json/thientam/v1/categories/{slug}`)*
- **Tham số URL (Path Params)**:
  - `slug`: `string` | `number` (Slug hoặc ID của chuyên mục, ví dụ: `phong-thuy-nha-o` hoặc `12`)
- **Tham số tùy chọn (Query Params)**:
  - `sort`: `latest` | `oldest` (Mặc định `latest` - Mới nhất, `oldest` - Cũ nhất)
  - `per_page`: `number` (Số lượng bài viết trên 1 trang, **mặc định `6` bài/trang**)
  - `page`: `number` (Trang hiện tại, mặc định `1`)
  - `tag`: `string` (Lọc theo slug của thẻ bài viết)
  - `search` / `s`: `string` (Tìm kiếm bài viết trong chuyên mục)

#### Ví dụ gọi API:
- Lấy 6 bài mới nhất trang 1:
  `GET https://site.thientam68.com/wp-json/thientam/v1/news/category/phong-thuy-nha-o?page=1&per_page=6&sort=latest`
- Lấy 6 bài cũ nhất trang 2:
  `GET https://site.thientam68.com/wp-json/thientam/v1/news/category/phong-thuy-nha-o?page=2&per_page=6&sort=oldest`
- Lọc theo thẻ `phong-khach` trong chuyên mục:
  `GET https://site.thientam68.com/wp-json/thientam/v1/news/category/phong-thuy-nha-o?tag=phong-khach&page=1&per_page=6`

#### Cấu trúc Phản hồi (Response 200 OK):

```json
{
  "success": true,
  "category": {
    "id": 12,
    "name": "Phong thủy nhà ở",
    "slug": "phong-thuy-nha-o",
    "description": "Tổng hợp kiến thức giúp bạn sắp xếp, cân bằng và nuôi dưỡng năng lượng tích cực trong không gian sống.",
    "count": 18,
    "parent": 0,
    "parent_name": "",
    "link": "https://site.thientam68.com/category/phong-thuy-nha-o/"
  },
  "sub_categories": [
    {
      "id": 13,
      "name": "Phòng khách",
      "slug": "phong-khach",
      "description": "Phong thủy bài trí phòng khách",
      "count": 6
    },
    {
      "id": 14,
      "name": "Phòng ngủ",
      "slug": "phong-ngu",
      "description": "Phong thủy không gian nghỉ ngơi",
      "count": 5
    }
  ],
  "tags": ["Phòng khách", "Phòng ngủ", "Hướng nhà", "Màu sắc", "Cây xanh"],
  "count": 9,
  "total": 18,
  "total_pages": 2,
  "data": [
    {
      "id": 4001,
      "slug": "7-nguyen-tac-phong-thuy-phong-khach-giup-gia-dao-an-yen",
      "title": "7 nguyên tắc phong thủy phòng khách giúp gia đạo an yên",
      "category": "Phòng khách",
      "desc": "Phòng khách là trái tim của ngôi nhà – nơi thu hút năng lượng, kết nối các thành viên và đón tiếp.",
      "date": "24/08/2026 14:08:00",
      "author": "Chuyên gia Thiên Tâm",
      "image": "https://site.thientam68.com/wp-content/uploads/2026/08/banner-home-1.png",
      "tags": ["Phòng khách", "Không gian sống"]
    }
  ]
}
```

---

### 4. Lấy Chi Tiết Bài Viết (Single News Detail)

#### Yêu cầu (Request):
- **Phương thức (Method)**: `GET`
- **URL**: `https://site.thientam68.com/wp-json/thientam/v1/news/{slug}`

#### Cấu trúc Phản hồi (Response 200 OK):

```json
{
  "success": true,
  "data": {
    "id": 4001,
    "slug": "bi-quyet-bo-tri-phong-khach-thu-hut-tai-loc-va-vuong-khi-cho-gia-chu",
    "title": "Bí quyết bố trí phòng khách thu hút tài lộc và vượng khí cho gia chủ",
    "category": "Phong thủy nhà ở",
    "desc": "Khám phá cách sắp đặt vị trí bàn ghế, ánh sáng và màu sắc...",
    "content": "<p>Nội dung chi tiết HTML bài viết...</p>",
    "date": "18/08/2026 14:08:00",
    "author": "Chuyên gia Thiên Tâm",
    "image": "https://site.thientam68.com/wp-content/uploads/2026/08/banner-home-1.png",
    "tags": ["Phong thủy nhà ở", "Phòng khách", "Không gian sống"],
    "related": [
      {
        "id": 4002,
        "slug": "hieu-dung-ve-ngu-hanh-tuong-sinh-chia-khoa-can-bang-tam-tri-va-gia-dao",
        "title": "Hiểu đúng về ngũ hành tương sinh...",
        "category": "Tử vi & Bản mệnh",
        "date": "15/08/2026 09:08:00",
        "image": "https://site.thientam68.com/wp-content/uploads/2026/08/brand-showcase.png"
      }
    ]
  }
}
```

---

## 📋 Chi Tiết Các Trường Dữ Liệu (Field Specifications)

### Danh mục bài viết (News Category)
| Tên trường | Kiểu dữ liệu | Mô tả |
| :--- | :--- | :--- |
| `id` | `number` | ID của danh mục (`term_id`). |
| `name` | `string` | Tên hiển thị của danh mục. |
| `slug` | `string` | Đường dẫn tĩnh (slug) của danh mục. |
| `description` | `string` | Mô tả của danh mục. |
| `count` | `number` | Tổng số bài viết trực thuộc danh mục này. |
| `parent` | `number` | ID danh mục cha (`0` nếu là danh mục gốc). |
| `parent_name` | `string` | Tên danh mục cha nếu có. |
| `link` | `string` | Đường dẫn lưu trữ chuyên mục trên web WordPress. |

### Bài viết (News Item)
| Tên trường | Kiểu dữ liệu | Mô tả |
| :--- | :--- | :--- |
| `id` | `number` | ID của bài viết trong WordPress. |
| `slug` | `string` | Đường dẫn tĩnh (slug) của bài viết. |
| `title` | `string` | Tiêu đề bài viết (*post_title*). |
| `category` | `string` | Tên chuyên mục chính (*Category*). |
| `desc` | `string` | Mô tả ngắn / Tóm tắt (*post_excerpt* hoặc trích đoạn từ nội dung). |
| `date` | `string` | Ngày đăng bài định dạng `dd/mm/yyyy hh:mm:ss`. |
| `author` | `string` | Tác giả bài viết (*Mặc định lấy từ Display Name của tài khoản tác giả WordPress*). |
| `image` | `string` | URL ảnh đại diện (*Featured Image*). |

---

## 💻 Code Mẫu Tích Hợp Phía Next.js (TypeScript)

```typescript
export interface NewsCategory {
  id: number;
  name: string;
  slug: string;
  description?: string;
  count: number;
  parent?: number;
  parent_name?: string;
  link?: string;
}

export interface NewsItem {
  id?: number | string;
  slug: string;
  title: string;
  category: string;
  desc: string;
  date: string;
  author: string;
  image: string;
}

export interface NewsCategoryResponse {
  success: boolean;
  category?: NewsCategory;
  sub_categories?: NewsCategory[];
  tags?: string[];
  count: number;
  total: number;
  total_pages: number;
  data: NewsItem[];
}

const WP_API_URL = process.env.NEXT_PUBLIC_WP_API_URL || "https://site.thientam68.com/wp-json";

// 1. Lấy danh sách danh mục tin tức
export async function getNewsCategories(): Promise<NewsCategory[]> {
  try {
    const res = await fetch(`${WP_API_URL}/thientam/v1/news/categories`, {
      next: { revalidate: 60 },
    });
    if (res.ok) {
      const result = await res.json();
      if (result.success && Array.isArray(result.data)) {
        return result.data as NewsCategory[];
      }
    }
  } catch (error) {
    console.debug("Lỗi kết nối WordPress API cho danh mục tin tức:", error);
  }
  return [];
}

// 2. Lấy danh sách tin tức
export async function getAllNews(): Promise<NewsItem[]> {
  try {
    const res = await fetch(`${WP_API_URL}/thientam/v1/news`, {
      next: { revalidate: 60 },
    });
    if (res.ok) {
      const result = await res.json();
      if (result.success && Array.isArray(result.data)) {
        return result.data as NewsItem[];
      }
    }
  } catch (error) {
    console.debug("Lỗi kết nối WordPress API cho tin tức:", error);
  }
  return [];
}

// 3. Lấy danh sách bài viết thuộc chuyên mục
export async function getNewsByCategory(
  slug: string,
  params?: { page?: number; per_page?: number; tag?: string; search?: string }
): Promise<NewsCategoryResponse | null> {
  try {
    const query = new URLSearchParams();
    if (params?.page) query.set("page", params.page.toString());
    if (params?.per_page) query.set("per_page", params.per_page.toString());
    if (params?.tag) query.set("tag", params.tag);
    if (params?.search) query.set("search", params.search);

    const qs = query.toString();
    const url = `${WP_API_URL}/thientam/v1/news/category/${slug}${qs ? `?${qs}` : ""}`;

    const res = await fetch(url, { next: { revalidate: 60 } });
    if (res.ok) {
      const result = await res.json();
      if (result.success) {
        return result as NewsCategoryResponse;
      }
    }
  } catch (error) {
    console.debug("Lỗi kết nối WordPress API cho bài viết theo chuyên mục:", error);
  }
  return null;
}
```

