# TÀI LIỆU REST API - GÓI ĐÀO TẠO (TRAINING PACKAGES)

Tài liệu hướng dẫn các REST API endpoints cho **Gói Đào Tạo & Khóa Học Tử Vi - Phong Thủy** tại website Phong Thủy Thiên Tâm (`site.thientam68.com`).

---

## 1. TỔNG QUAN

- **Post Type**: `training`
- **Base URL API**: `/wp-json/thientam/v1`
- **Format dữ liệu**: Chuẩn hóa 100% tương thích với Next.js Frontend (`src/locales/vi.json -> trainingPage.curriculum.packages`)

---

## 2. DANH SÁCH ENDPOINTS

| STT | Phương thức | Endpoint | Mô tả |
|---|---|---|---|
| 1 | `GET` | `/wp-json/thientam/v1/training` | Lấy danh sách tất cả các gói đào tạo |
| 2 | `GET` | `/wp-json/thientam/v1/training/{slug}` | Lấy chi tiết gói đào tạo theo slug |

---

## 3. CHI TIẾT ENDPOINTS

### 3.1. Lấy danh sách tất cả gói đào tạo

- **URL**: `GET /wp-json/thientam/v1/training`
- **Response**:
```json
{
  "success": true,
  "count": 7,
  "data": [
    {
      "id": "tu-vi-nhap-mon",
      "category": "Tử vi nhập môn",
      "subtitle": "Giáo trình chuẩn hóa Cổ học phương Đông & Tử Vi thực chiến",
      "isCategory": true,
      "level": "Cơ bản - Nhập môn",
      "duration": "10 chuyên đề cốt lõi",
      "summary": "Trang bị nền tảng kiến thức chuẩn xác, hệ thống hóa toàn bộ 14 chính tinh...",
      "targetAudience": "Người mới bắt đầu tìm hiểu Cổ học & Tử Vi, muốn tự lập và luận giải lá số...",
      "audienceItems": [
        "Người muốn hiểu sâu về mệnh bàn cá nhân, gia đình và con cái",
        "Người yêu thích Cổ học phương Đông muốn tiếp cận Tử Vi một cách khoa học, bài bản"
      ],
      "benefitItems": [
        {
          "highlight": "Hình thức linh hoạt:",
          "text": " Học trực tiếp hoặc online qua Zoom có ghi hình bài giảng để xem lại"
        },
        {
          "highlight": "Giáo trình độc quyền:",
          "text": " Tài liệu đúc kết thực chiến, bảng tra cứu tinh gọn"
        },
        {
          "highlight": "Đồng hành trọn đời:",
          "text": " Tham gia cộng đồng học viên Thiên Tâm, được giải đáp thắc mắc không giới hạn"
        }
      ],
      "metrics": [
        { "value": "10 Chuyên đề", "desc": "Lộ trình bài bản từ gốc" },
        { "value": "60%+ Thực hành", "desc": "Luận giải lá số thực tế" },
        { "value": "Online & Trực tiếp", "desc": "Linh hoạt thời gian" },
        { "value": "Đồng hành 1:1", "desc": "Hỗ trợ giải đáp trọn đời" }
      ],
      "lessons": [
        "Giới thiệu nhập môn",
        "Ý nghĩa 14 sao chính tinh",
        "Bộ nhóm sao quan trọng trong luận đoán",
        "Ý nghĩa các nhóm sao khi luận đoán",
        "Vị trí các cung sắp xếp trên sơ đồ lá số Tử vi",
        "Cách truy vấn lá số sai, đúng qua cách an sao",
        "Ý nghĩa và luận đoán Vòng Trường sinh",
        "Tầm quan trọng của Vòng Lộc tồn và Thái tuế",
        "12 cách cục lá số Tử Vi",
        "Phương pháp luận đoán tổng quát lá số Tử vi trên 12 cung địa bàn"
      ],
      "image": "https://site.thientam68.com/wp-content/uploads/..."
    }
  ]
}
```

---

### 3.2. Lấy chi tiết gói đào tạo theo slug

- **URL**: `GET /wp-json/thientam/v1/training/tu-vi-nhap-mon`
- **Response**:
```json
{
  "success": true,
  "data": {
    "id": "tu-vi-nhap-mon",
    "category": "Tử vi nhập môn",
    "subtitle": "Giáo trình chuẩn hóa Cổ học phương Đông & Tử Vi thực chiến",
    "isCategory": true,
    "level": "Cơ bản - Nhập môn",
    "duration": "10 chuyên đề cốt lõi",
    "summary": "Trang bị nền tảng kiến thức chuẩn xác, hệ thống hóa toàn bộ 14 chính tinh...",
    "targetAudience": "Người mới bắt đầu tìm hiểu Cổ học & Tử Vi, muốn tự lập và luận giải lá số...",
    "audienceItems": [ ... ],
    "benefitItems": [ ... ],
    "metrics": [ ... ],
    "lessons": [ ... ],
    "image": "https://site.thientam68.com/wp-content/uploads/..."
  }
}
```

---

## 4. TÍNH NĂNG TỰ ĐỘNG NẠP DỮ LIỆU (AUTO-SEEDING)

Hệ thống tự động nạp sẵn 7 gói đào tạo chuẩn từ Thiên Tâm vào cơ sở dữ liệu nếu WordPress chưa có bài viết nào:
1. **Tử vi nhập môn** (`tu-vi-nhap-mon`)
2. **Tử vi nâng cao** (`tu-vi-nang-cao`)
3. **Tử vi chuyên sâu** (`tu-vi-chuyen-sau`)
4. **Tử vi chân truyền** (`tu-vi-chan-truyen`)
5. **Kỳ môn phong thủy - Chiến lược** (`ky-mon-phong-thuy-chien-luoc`)
6. **Kinh dịch phối hợp thực chiến** (`kinh-dich-phoi-hop-thuc-chien`)
7. **Tứ trụ - Bát tự** (`tu-tru-bat-tu`)
