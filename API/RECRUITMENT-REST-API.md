# 📚 TÀI LIỆU REST API - TUYỂN DỤNG (RECRUITMENT & CAREERS)

Tài liệu hướng dẫn chi tiết các REST API endpoints cho hệ thống **Tuyển Dụng & Nhân Sự** tại website Phong Thủy Thiên Tâm (`site.thientam68.com`).

> **Base URL**: `https://site.thientam68.com/wp-json`  
> **Namespace**: `thientam/v1`  
> **Định dạng dữ liệu**: `JSON` (UTF-8)  
> **Xác thực**: Không yêu cầu (Public Read API)  
> **Phiên bản**: `1.2.0`

---

## 1. TỔNG QUAN HỆ THỐNG

- **Custom Post Type**: `recruitment` (Vị trí tuyển dụng).
- **Custom Taxonomy**: `recruitment_department` (Phòng ban tuyển dụng).
- **Giao diện nhập liệu**: Native WordPress Meta Box & ACF Field Group (`group_recruitment_settings.json`).
- **Format dữ liệu trả về**: Chuẩn hóa 100% tương thích với Next.js Frontend (`src/components/recruitment/`).
- **Xử lý danh sách**: Các trường nhiều dòng (`responsibilities`, `requirements`, `benefits`) được tự động bóc tách thành mảng `string[]`.

---

## 2. DANH SÁCH ENDPOINTS

| STT | Phương thức | Endpoint | Mô tả |
|---|---|---|---|
| 1 | `GET` | `/wp-json/thientam/v1/recruitment/departments` | Lấy danh sách tất cả các phòng ban kèm số lượng vị trí tuyển dụng |
| 2 | `GET` | `/wp-json/thientam/v1/recruitment` | Lấy danh sách việc làm (hỗ trợ tìm kiếm, lọc theo phòng ban, hình thức, phân trang, sắp xếp) |
| 3 | `GET` | `/wp-json/thientam/v1/recruitment/{slug}` | Lấy thông tin chi tiết một vị trí tuyển dụng theo slug |
| 4 | `POST` | `/wp-json/thientam/v1/submit-form` | Gửi hồ sơ ứng tuyển từ ứng viên về hệ thống |

> [!NOTE]
> Endpoint `/recruitment/departments` được đăng ký trước route wildcard `/recruitment/{slug}` trên WordPress để tránh xung đột slug.

---

## 3. CHI TIẾT ENDPOINTS & LỆNH cURL

### 3.1. Danh Sách Phòng Ban (Departments)

Trả về toàn bộ danh mục phòng ban từ taxonomy `recruitment_department` kèm tổng số bài tuyển dụng đang publish.

- **URL**: `GET https://site.thientam68.com/wp-json/thientam/v1/recruitment/departments`
- **Method**: `GET`
- **Headers**:
  ```http
  Accept: application/json
  ```

#### Các lệnh cURL mẫu:

```bash
# 1. Lấy danh sách tất cả các phòng ban
curl -s -X GET "https://site.thientam68.com/wp-json/thientam/v1/recruitment/departments" \
  -H "Accept: application/json"
```

```bash
# 2. Lấy danh sách phòng ban và format đẹp qua jq
curl -s -X GET "https://site.thientam68.com/wp-json/thientam/v1/recruitment/departments" \
  -H "Accept: application/json" | jq .
```

#### Cấu trúc Phản hồi (Response 200 OK):

```json
{
  "success": true,
  "data": [
    {
      "id": "all",
      "name": "Tất cả vị trí",
      "slug": "all",
      "count": 6
    },
    {
      "id": 12,
      "name": "Tư vấn & Khảo sát",
      "slug": "tu-van",
      "count": 2
    },
    {
      "id": 13,
      "name": "Viện Cổ Học & Đào Tạo",
      "slug": "co-hoc",
      "count": 2
    },
    {
      "id": 14,
      "name": "Không gian & Kiến trúc",
      "slug": "khong-gian",
      "count": 1
    },
    {
      "id": 15,
      "name": "Truyền thông & Thương hiệu",
      "slug": "truyen-thong",
      "count": 1
    },
    {
      "id": 16,
      "name": "Dịch vụ & Chăm sóc khách hàng",
      "slug": "dich-vu",
      "count": 1
    }
  ]
}
```

---

### 3.2. Danh Sách Tuyển Dụng (Recruitment List)

Lấy danh sách các vị trí việc làm đang mở kèm đầy đủ thông tin tóm tắt và phân trang.

- **URL**: `GET https://site.thientam68.com/wp-json/thientam/v1/recruitment`
- **Method**: `GET`
- **Headers**:
  ```http
  Accept: application/json
  ```
- **Tham số Query Parameters**:

| Tham số | Kiểu | Mặc định | Mô tả |
|---|---|---|---|
| `page` | `integer` | `1` | Trang cần lấy |
| `per_page` | `integer` | `9` | Số lượng bài viết trên 1 trang |
| `department` | `string` | `""` | Lọc theo slug phòng ban (`tu-van`, `co-hoc`, `khong-gian`, `truyen-thong`, `dich-vu` hoặc `all`) |
| `type` | `string` | `""` | Lọc theo hình thức (`Toàn thời gian`, `Bán thời gian`, `Cộng tác viên`) |
| `search` | `string` | `""` | Tìm kiếm từ khóa theo tiêu đề hoặc nội dung |
| `sort` | `string` | `"latest"` | Sắp xếp: `latest` (mới nhất theo ngày đăng) hoặc `oldest` (cũ nhất) |
| `_t` | `string` | `""` | Cache buster timestamp (tùy chọn) |

#### Các lệnh cURL mẫu:

```bash
# 1. Lấy danh sách mặc định (9 vị trí mới nhất, trang 1)
curl -s -X GET "https://site.thientam68.com/wp-json/thientam/v1/recruitment" \
  -H "Accept: application/json" | jq .
```

```bash
# 2. Lọc theo phòng ban (Ví dụ: Tư vấn & Khảo sát)
curl -s -X GET "https://site.thientam68.com/wp-json/thientam/v1/recruitment?department=tu-van" \
  -H "Accept: application/json" | jq .
```

```bash
# 3. Lọc theo hình thức làm việc (Toàn thời gian)
curl -s -X GET "https://site.thientam68.com/wp-json/thientam/v1/recruitment?type=To%C3%A0n+th%E1%BB%9Di+gian" \
  -H "Accept: application/json" | jq .
```

```bash
# 4. Tìm kiếm từ khóa (Ví dụ: 'phong thủy')
curl -s -X GET "https://site.thientam68.com/wp-json/thientam/v1/recruitment?search=phong+th%E1%BB%A7y" \
  -H "Accept: application/json" | jq .
```

```bash
# 5. Phân trang (Trang 1, mỗi trang lấy 3 vị trí)
curl -s -X GET "https://site.thientam68.com/wp-json/thientam/v1/recruitment?page=1&per_page=3" \
  -H "Accept: application/json" | jq .
```

```bash
# 6. Sắp xếp tin cũ nhất trước
curl -s -X GET "https://site.thientam68.com/wp-json/thientam/v1/recruitment?sort=oldest" \
  -H "Accept: application/json" | jq .
```

```bash
# 7. Kết hợp nhiều bộ lọc (Phòng ban Tư vấn + Toàn thời gian + Phân trang)
curl -s -X GET "https://site.thientam68.com/wp-json/thientam/v1/recruitment?department=tu-van&type=To%C3%A0n+th%E1%BB%9Di+gian&sort=latest&page=1&per_page=6" \
  -H "Accept: application/json" | jq .
```

#### Cấu trúc Phản hồi (Response 200 OK):

```json
{
  "success": true,
  "count": 1,
  "total": 1,
  "total_pages": 1,
  "data": [
    {
      "id": 1001,
      "slug": "chuyen-vien-tu-van-phong-thuy-ung-dung",
      "title": "Chuyên Viên Tư Vấn Phong Thủy Ứng Dụng",
      "department": "Tư vấn & Khảo sát",
      "departmentSlug": "tu-van",
      "location": "Đà Lạt & TP. Hồ Chí Minh",
      "type": "Toàn thời gian",
      "salary": "18.000.000 - 30.000.000 VNĐ + Thưởng dự án",
      "experience": "Từ 2 năm kinh nghiệm",
      "deadline": "31/10/2026",
      "isFeatured": true,
      "createdAt": "22/09/2026",
      "desc": "Đồng hành trực tiếp cùng khách hàng và gia chủ khảo sát thực địa, phân tích loan đầu - lý khí, tư vấn giải pháp bố trí không gian sống vượng khí, an lành.",
      "overview": "Tại Thiên Tâm, Chuyên viên Tư vấn Phong thủy Ứng dụng không chỉ đơn thuần là người đọc bản đồ hay đo đạc phương vị, mà là cầu nối thấu hiểu nhân duyên giữa con người và không gian sống.",
      "responsibilities": [
        "Khảo sát hiện trạng thực địa công trình (nhà ở, biệt thự, văn phòng, dự án kinh doanh).",
        "Ứng dụng hệ thống kiến thức phong thủy phái Loan Đầu, Bát Trạch, Huyền Không Phi Tinh để phân tích năng lượng.",
        "Làm việc trực tiếp với khách hàng để thấu hiểu nhu cầu, giải tỏa âu lo.",
        "Phối hợp cùng bộ phận Kiến trúc - Thiết kế để hiện thực hóa giải pháp."
      ],
      "requirements": [
        "Tốt nghiệp Cao đẳng/Đại học các chuyên ngành Kiến trúc, Xây dựng, Văn hóa học, Triết học.",
        "Có từ 2 năm kinh nghiệm thực tế trong lĩnh vực phong thủy địa lý.",
        "Tư duy chính trực, điềm đạm, lắng nghe sâu sắc và tôn trọng bảo mật tuyệt đối.",
        "Sẵn sàng di chuyển khảo sát thực địa khi có dự án."
      ],
      "benefits": [
        "Mức thu nhập hấp dẫn từ 18 - 30 triệu đồng/tháng + Thưởng theo từng dự án.",
        "Được trực tiếp hướng dẫn, nâng cao chuyên môn từ Ms. Linda Trần.",
        "Môi trường làm việc an hòa, tôn trọng, giàu tính nhân văn.",
        "Đầy đủ chế độ BHXH, BHYT, nghỉ dưỡng hàng quý."
      ],
      "workLocation": "Văn phòng Đà Lạt (Số 61/2 An Bình, Phường Xuân Hương) & Chi nhánh TP. Hồ Chí Minh",
      "workTime": "Thứ Hai đến Thứ Bảy (08:00 - 17:30)"
    }
  ]
}
```

---

### 3.3. Chi Tiết Một Vị Trí Tuyển Dụng (Recruitment Detail)

Lấy thông tin chi tiết đầy đủ của một vị trí theo `slug`.

- **URL**: `GET https://site.thientam68.com/wp-json/thientam/v1/recruitment/{slug}`
- **Method**: `GET`
- **Headers**:
  ```http
  Accept: application/json
  ```
- **Tham số Đường dẫn (Path Params)**:
  - `slug`: `string` (Slug định danh bài tuyển dụng, VD: `chuyen-vien-tu-van-phong-thuy-ung-dung`)

#### Các lệnh cURL mẫu:

```bash
# 1. Lấy chi tiết bài tuyển dụng theo slug
curl -s -X GET "https://site.thientam68.com/wp-json/thientam/v1/recruitment/chuyen-vien-tu-van-phong-thuy-ung-dung" \
  -H "Accept: application/json" | jq .
```

```bash
# 2. Lấy chi tiết vị trí Nghiên Cứu Bát Tự - Tử Vi
curl -s -X GET "https://site.thientam68.com/wp-json/thientam/v1/recruitment/chuyen-vien-nghien-cuu-luan-giai-bat-tu-tu-vi" \
  -H "Accept: application/json" | jq .
```

```bash
# 3. Thử nghiệm trường hợp slug không tồn tại (Kiểm tra lỗi 404)
curl -s -i -X GET "https://site.thientam68.com/wp-json/thientam/v1/recruitment/vi-tri-khong-ton-tai" \
  -H "Accept: application/json"
```

#### Cấu trúc Phản hồi Thành công (Response 200 OK):

```json
{
  "success": true,
  "data": {
    "id": 1001,
    "slug": "chuyen-vien-tu-van-phong-thuy-ung-dung",
    "title": "Chuyên Viên Tư Vấn Phong Thủy Ứng Dụng",
    "department": "Tư vấn & Khảo sát",
    "departmentSlug": "tu-van",
    "location": "Đà Lạt & TP. Hồ Chí Minh",
    "type": "Toàn thời gian",
    "salary": "18.000.000 - 30.000.000 VNĐ + Thưởng dự án",
    "experience": "Từ 2 năm kinh nghiệm",
    "deadline": "31/10/2026",
    "isFeatured": true,
    "createdAt": "22/09/2026",
    "desc": "Đồng hành trực tiếp cùng khách hàng và gia chủ khảo sát thực địa...",
    "overview": "Tại Thiên Tâm, Chuyên viên Tư vấn Phong thủy Ứng dụng...",
    "responsibilities": [
      "Khảo sát hiện trạng thực địa công trình (nhà ở, biệt thự, văn phòng, dự án kinh doanh).",
      "Ứng dụng hệ thống kiến thức phong thủy phái Loan Đầu, Bát Trạch, Huyền Không Phi Tinh để phân tích năng lượng.",
      "Làm việc trực tiếp với khách hàng để thấu hiểu nhu cầu, giải tỏa âu lo.",
      "Phối hợp cùng bộ phận Kiến trúc - Thiết kế để hiện thực hóa giải pháp."
    ],
    "requirements": [
      "Tốt nghiệp Cao đẳng/Đại học các chuyên ngành Kiến trúc, Xây dựng, Văn hóa học, Triết học.",
      "Có từ 2 năm kinh nghiệm thực tế trong lĩnh vực phong thủy địa lý.",
      "Tư duy chính trực, điềm đạm, lắng nghe sâu sắc và tôn trọng bảo mật tuyệt đối.",
      "Sẵn sàng di chuyển khảo sát thực địa khi có dự án."
    ],
    "benefits": [
      "Mức thu nhập hấp dẫn từ 18 - 30 triệu đồng/tháng + Thưởng theo từng dự án.",
      "Được trực tiếp hướng dẫn, nâng cao chuyên môn từ Ms. Linda Trần.",
      "Môi trường làm việc an hòa, tôn trọng, giàu tính nhân văn.",
      "Đầy đủ chế độ BHXH, BHYT, nghỉ dưỡng hàng quý."
    ],
    "workLocation": "Văn phòng Đà Lạt (Số 61/2 An Bình, Phường Xuân Hương) & Chi nhánh TP. Hồ Chí Minh",
    "workTime": "Thứ Hai đến Thứ Bảy (08:00 - 17:30)"
  }
}
```

#### Cấu trúc Phản hồi Thất bại (Response 404 Not Found):

```json
{
  "success": false,
  "message": "Không tìm thấy vị trí tuyển dụng"
}
```

---

### 3.4. Nộp Hồ Sơ Ứng Tuyển (Submit Application)

Endpoint tiếp nhận thông tin ứng tuyển từ ứng viên (được gửi từ modal ứng tuyển `JobApplicationModal` / `ConsultationForm`).

- **URL**: `POST https://site.thientam68.com/wp-json/thientam/v1/submit-form`
- **Method**: `POST`
- **Headers**:
  ```http
  Content-Type: application/json
  Accept: application/json
  ```

#### Lệnh cURL mẫu:

```bash
# Nộp hồ sơ ứng tuyển vị trí chuyên viên tư vấn
curl -s -X POST "https://site.thientam68.com/wp-json/thientam/v1/submit-form" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "form_id": "recruitment_application",
    "form_title": "Ứng tuyển: Chuyên Viên Tư Vấn Phong Thủy Ứng Dụng",
    "name": "Nguyễn Văn A",
    "phone": "0987654321",
    "email": "nguyenvana@gmail.com",
    "message": "Tôi rất mong muốn được cống hiến tại Phong Thủy Thiên Tâm.",
    "fields": {
      "Họ và tên": "Nguyễn Văn A",
      "Số điện thoại": "0987654321",
      "Email": "nguyenvana@gmail.com",
      "Vị trí ứng tuyển": "Chuyên Viên Tư Vấn Phong Thủy Ứng Dụng",
      "Lời nhắn / Yêu cầu": "Tôi rất mong muốn được cống hiến tại Phong Thủy Thiên Tâm."
    },
    "meta": {
      "page_url": "https://thientam68.com/tuyen-dung/chuyen-vien-tu-van-phong-thuy-ung-dung"
    }
  }' | jq .
```

#### Cấu trúc Phản hồi (Response 200 OK):

```json
{
  "success": true,
  "message": "Gửi yêu cầu thành công!"
}
```

---

## 4. BẢNG ÁNH XẠ TRƯỜNG DỮ LIỆU (SCHEMA MAPPING)

| Khóa JSON trả về | Nguồn WordPress Meta / Core | Kiểu dữ liệu | Mô tả |
|---|---|---|---|
| `id` | `WP_Post->ID` | `number` | ID của bài tuyển dụng |
| `slug` | `WP_Post->post_name` | `string` | Slug đường dẫn URL |
| `title` | `WP_Post->post_title` | `string` | Tên vị trí tuyển dụng |
| `department` | Taxonomy term name / Meta | `string` | Tên phòng ban hiển thị |
| `departmentSlug` | Taxonomy term slug | `string` | Slug phòng ban (dùng để lọc) |
| `location` | Meta `location` | `string` | Địa điểm làm việc tổng quát |
| `type` | Meta `type` | `string` | Hình thức (Toàn thời gian, Bán thời gian, CTV) |
| `salary` | Meta `salary` | `string` | Mức thu nhập / Lương thưởng |
| `experience` | Meta `experience` | `string` | Yêu cầu số năm kinh nghiệm |
| `deadline` | Meta `deadline` | `string` | Hạn nộp hồ sơ (DD/MM/YYYY) |
| `isFeatured` | Meta `is_featured` | `boolean` | Vị trí nổi bật (`true`/`false`) |
| `createdAt` | `WP_Post->post_date` | `string` | Ngày đăng tuyển (`d/m/Y`) |
| `desc` | `WP_Post->post_excerpt` | `string` | Đoạn tóm tắt ngắn vai trò |
| `image` | Featured Image (`get_the_post_thumbnail_url`) | `string` | URL ảnh đại diện mặc định của bài viết trong WordPress |
| `overview` | Meta `overview` / Content | `string` | Đoạn giới thiệu tổng quan |
| `responsibilities` | Meta `responsibilities` | `string[]` | Danh sách nhiệm vụ chính (tách theo từng dòng xuống hàng `\n`) |
| `requirements` | Meta `requirements` | `string[]` | Yêu cầu ứng viên (tách theo từng dòng xuống hàng `\n`) |
| `benefits` | Meta `benefits` | `string[]` | Chế độ đãi ngộ & quyền lợi (tách theo từng dòng xuống hàng `\n`) |
| `workLocation` | Meta `work_location` | `string` | Địa chỉ làm việc cụ thể của văn phòng |
| `workTime` | Meta `work_time` | `string` | Khung thời gian làm việc trong tuần |

---

## 5. XUẤT & NHẬP DỮ LIỆU MẪU BẰNG XML (WXR IMPORT)

Nhằm đảm bảo phòng ban và bài viết tuyển dụng hoàn toàn **động 100%** trong WordPress database mà không gắn cứng (hardcode) trong code PHP:

1. **Xuất file XML bằng công cụ mặc định WordPress**:
   - Truy cập trang quản trị WP Admin &rarr; menu **Công cụ (Tools)** &rarr; chọn **Xuất (Export)**.
   - Chọn mục **Tuyển dụng** &rarr; bấm nút **"Tải tập tin xuất về"**.
2. **Nhập dữ liệu vào website**:
   - Vào menu **Công cụ (Tools)** &rarr; **Nhập (Import)** &rarr; chọn **WordPress** (Cài đặt plugin WordPress Importer nếu chưa có).
   - Chọn file `thientam-recruitment-sample.xml` đã tải về (hoặc lấy tại thư mục gốc dự án).
   - Bấm **Tải lên tập tin và nhập** &rarr; Chọn tài khoản tác giả quản trị &rarr; Hoàn tất.
