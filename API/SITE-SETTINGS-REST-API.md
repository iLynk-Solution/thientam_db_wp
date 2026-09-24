# 📚 Tài Liệu Hướng Dẫn Tích Hợp WordPress REST API Cho Cài Đặt & Nội Dung Website (Site Settings & Localization)

> **Base URL**: `https://site.thientam68.com/wp-json`  
> **Namespace**: `thientam/v1`  
> **Endpoint**: `/wp-json/thientam/v1/settings`  
> **Phiên bản**: `1.0.0`

---

## 📌 Tổng Quan Hệ Thống Cài Đặt Website (Site Settings)

Hệ thống **Cài đặt Website & Nội dung (Site Settings & Localization)** cho phép Quản trị viên thay đổi toàn bộ nội dung tĩnh của website (tương đương với tệp `vi.json`) trực tiếp trên **WordPress Admin** mà không cần can thiệp mã nguồn:

- **Menu Quản trị**: Cài đặt Website (*Thiên Tâm Options*).
- **Giao diện 7 Tabs trực quan**: Phân loại theo từng nhóm trang (Page) và thành phần giao diện (Component).
- **Tự động Nạp Dữ Liệu Mẫu (Auto Seed)**: Khởi tạo sẵn toàn bộ dữ liệu chuẩn ban đầu khi mới cài đặt.
- **Cơ chế Fallback An toàn Tuyệt đối**: Next.js tự động gộp (deep-merge) nội dung từ WordPress API với `vi.json`. Nếu WordPress offline, website vẫn giữ nguyên 100% nội dung hiển thị mà không bao giờ bị lỗi.
- **Không phụ thuộc plugin**: 100% code Native WordPress Core lưu vào `wp_options`.

---

## 🚀 Danh Sách REST API Endpoints

### 1. Lấy Toàn Bộ Cấu Hình & Nội Dung Website (Site Settings)

Endpoint trả về đầy đủ tất cả các trường dữ liệu nội dung của website, được gộp tự động giữa cài đặt tùy chỉnh trong database và dữ liệu mặc định chuẩn.

#### Yêu Cầu (Request):
- **Phương thức (Method)**: `GET`
- **URL**: `https://site.thientam68.com/wp-json/thientam/v1/settings`
- **Quyền hạn (Authentication)**: Không yêu cầu (`Public API`)
- **Headers**:
  ```http
  Accept: application/json
  ```
- **CORS Headers**: Đã bật sẵn `Access-Control-Allow-Origin: *` cho phép Next.js fetch từ mọi domain.

#### Cấu Trúc Phản Hồi (Response 200 OK):

```json
{
  "success": true,
  "data": {
    "meta": {
      "title": "Minh định tương lai - An trú hiện tại",
      "description": "Thiên Tâm đồng hành cùng Quý vị trên hành trình tìm về sự an yên, thịnh vượng và hạnh phúc bền vững thông qua triết lý phong thủy ứng dụng và chiều sâu tâm trí."
    },
    "company": {
      "name": "Công ty cổ phần dịch vụ Thiên Tâm",
      "titlePrefix": "Công ty cổ phần dịch vụ",
      "titleHighlight": "Thiên Tâm",
      "siteName": "Thiên Tâm",
      "slogan": "Hiểu Mệnh – Hiểu Người – Hiểu Không Gian Sống",
      "tagline": "Tư vấn & đồng hành",
      "desc": "Thiên Tâm là đơn vị chuyên tư vấn phong thủy, tử vi, và các giải pháp cân bằng năng lượng trong đời sống, không gian sống và doanh nghiệp.",
      "taxCode": "5801576524",
      "startDate": "24/06/2026",
      "representative": "Trần Thu Trang",
      "address": "Số 61/2 An Bình, Phường Xuân Hương - Đà Lạt, Tỉnh Lâm Đồng",
      "taxAddress": "Số 61/2 An Bình, Phường Xuân Hương - Đà Lạt, Tỉnh Lâm Đồng, Việt Nam.",
      "internationalName": "THIEN TAM SERVICE JOINT STOCK COMPANY.",
      "shortName": "THIEN TAM SERVICE JSC.",
      "mainIndustry": "Hoạt động tư vấn quản lý (tư vấn định hướng cuộc sống, tư vấn phong thủy ứng dụng trong đời sống, không gian làm việc và doanh nghiệp).",
      "hotline": "0935 425 238",
      "email": "thientamdl68@gmail.com",
      "website": "www.thientam68.com",
      "facebookHandle": "facebook.com/thientam68",
      "facebookHref": "https://facebook.com/thientam68",
      "hours": "Thứ 2 – Chủ Nhật: 08:00 – 21:00"
    },
    "header": {
      "ctaButton": "Đặt lịch tư vấn",
      "hotlineLabel": "Hotline tư vấn",
      "addressLabel": "Địa chỉ văn phòng",
      "hoursLabel": "Giờ mở cửa",
      "servicesSubTitle": "Danh mục dịch vụ",
      "servicesAllLabel": "Xem tất cả dịch vụ",
      "trainingSubTitle": "Chương trình đào tạo",
      "trainingAllLabel": "Xem tất cả khóa học"
    },
    "footer": {
      "ctaBanner": {
        "titlePrefix": "Bạn cần một góc nhìn khách quan để đưa ra",
        "titleHighlight": "lựa chọn phù hợp?",
        "subtitle": "Thiên Tâm luôn sẵn sàng lắng nghe và đồng hành cùng bạn.",
        "button": "Đặt lịch tư vấn ngay"
      },
      "companyName": "Công ty cổ phần dịch vụ Thiên Tâm",
      "description": "Phong thủy Thiên Tâm là một đơn vị chuyên tư vấn, khảo sát và đào tạo trong lĩnh vực cổ học phương Đông...",
      "copyright": "Bản quyền © 2026 iLynk. All rights reserved.",
      "privacy": "Chính sách bảo mật",
      "terms": "Các chính sách"
    },
    "heroHome": {
      "tagline": "THIÊN TÂM • TƯ VẤN & ĐỒNG HÀNH",
      "titleLine1": "Hiểu mệnh – Hiểu người",
      "titleLine2": "Hiểu không gian sống",
      "description": "Thiên Tâm đồng hành cùng Quý vị trên hành trình cân bằng cuộc sống, đưa ra lựa chọn phù hợp và thuận lẽ tự nhiên.",
      "ctaPrimary": "Đặt lịch tư vấn",
      "ctaSecondary": "Xem các dịch vụ",
      "socialProofCount": "1000+ khách hàng",
      "socialProofLabel": "đã tin tưởng và hài lòng",
      "avatars": [
        {
          "image": "https://site.thientam68.com/wp-content/uploads/2026/09/avatar-1.jpg",
          "alt": "Khách hàng Doanh nghiệp"
        }
      ]
    },
    "homeService": {
      "titlePrefix": "Các dịch vụ",
      "titleHighlight": "tư vấn trọng tâm",
      "desc": "Thiên Tâm cung cấp các dịch vụ tư vấn toàn diện từ bản mệnh cá nhân, không gian sống cho tới phong thủy doanh nghiệp và số học.",
      "learnMore": "Tìm hiểu chi tiết"
    },
    "aboutPage": {
      "title": "Giới thiệu",
      "description": "Thiên Tâm là đơn vị tư vấn chuyên sâu về phong thủy, tử vi và đời sống...",
      "coreValues": {
        "titlePrefix": "Giá trị làm nên",
        "titleHighlight": "Thiên Tâm",
        "desc": "Kim chỉ nam dẫn lối mọi hoạt động tư vấn...",
        "items": [
          { "title": "Tận tâm", "desc": "Lắng nghe và thấu hiểu để đưa ra những tư vấn phù hợp nhất." },
          { "title": "Chuyên sâu", "desc": "Nghiên cứu kỹ lưỡng, dựa trên nền tảng học thuật và thực tiễn." },
          { "title": "Chính trực", "desc": "Đặt lợi ích của khách hàng lên hàng đầu với sự minh bạch." },
          { "title": "Đồng hành", "desc": "Không chỉ tư vấn, Thiên Tâm đồng hành cùng bạn." }
        ]
      },
      "quoteBanner": {
        "title": "Thiên Tâm – Hiểu mệnh, hiểu người, hiểu không gian sống",
        "desc": "Để mỗi quyết định của bạn đều đúng thời điểm và đúng hướng đi."
      }
    },
    "servicesPage": { ... },
    "trainingPage": { ... },
    "recruitment": { ... },
    "contactPage": { ... },
    "whyChoose": { ... },
    "process": { ... },
    "consultingProcess": { ... },
    "testimonials": { ... },
    "news": { ... },
    "contactForm": { ... },
    "formSuccessAlert": { ... },
    "notFound": { ... },
    "newsDetail": { ... },
    "newsListing": { ... }
  },
  "timestamp": "2026-09-23 09:40:00"
}
```

---

### 2. Cập Nhật Cấu Hình Website Qua API (Update Site Settings)

Endpoint cho phép ứng dụng bên ngoài hoặc công cụ đồng bộ cập nhật lại cấu hình website.

#### Yêu Cầu (Request):
- **Phương thức (Method)**: `POST`
- **URL**: `https://site.thientam68.com/wp-json/thientam/v1/settings`
- **Quyền hạn (Authentication)**: Yêu cầu quyền Quản trị viên (`manage_options`) qua Cookie/Nonce hoặc Bearer Token.
- **Headers**:
  ```http
  Content-Type: application/json
  X-WP-Nonce: {wp_rest_nonce}
  ```
- **Body (JSON)**:
  ```json
  {
    "company": {
      "hotline": "0935 425 238",
      "slogan": "Hiểu Mệnh – Hiểu Người – Hiểu Không Gian Sống"
    },
    "heroHome": {
      "socialProofCount": "1500+ khách hàng"
    }
  }
  ```

#### Cấu Trúc Phản Hồi (Response 200 OK):

```json
{
  "success": true,
  "message": "Cập nhật cấu hình website thành công.",
  "data": { ... },
  "timestamp": "2026-09-23 09:40:00"
}
```

---

## 📋 Bảng Tra Cứu Các Khóa Dữ Liệu & Vị Trí Hiển Thị

| Khóa JSON | Phân Loại | Component / Page Tương Ứng | Ý Nghĩa / Mục Đích |
| :--- | :--- | :--- | :--- |
| `meta` | **Global** | `src/app/layout.tsx` | Meta Title & Description SEO toàn trang |
| `company` | **Component & Page** | `Header.tsx`, `Footer.tsx`, `AboutCompanyInfoSection.tsx`, `contact/page.tsx` | Tên công ty, MST, Hotline, Email, Địa chỉ, Đại diện PL, Giờ mở cửa |
| `header` | **Component** | `Header.tsx`, `DesktopNavDropdown.tsx`, `MobileNavDrawer.tsx` | Nút CTA Đặt lịch, nhãn Hotline, Giờ mở cửa |
| `footer` | **Component** | `Footer.tsx`, `LandingFooter.tsx` | CTA Banner chân trang, mô tả thương hiệu, liên kết Mạng xã hội, Copyright |
| `heroHome` | **Page** | `HeroSection.tsx` *(Trang Chủ)* | Hero banner: Tagline, Tiêu đề 2 dòng, Mô tả, 2 Nút CTA, Social proof |
| `homeService`| **Page** | `ServicesSection.tsx` *(Trang Chủ)* | Tiêu đề & mô tả khối dịch vụ tư vấn trọng tâm |
| `whyChoose` | **Page / Component**| `WhyChooseSection.tsx` *(Trang Chủ)* | 3 Lý do chọn Thiên Tâm, Triết lý kim chỉ nam, Số liệu thống kê |
| `process` | **Page / Component**| `ProcessSection.tsx` *(Trang Chủ)* | Quy trình 4 bước đồng hành cùng khách hàng |
| `consultingProcess` | **Page / Component**| `ConsultingProcessSection.tsx` *(Trang Chủ)* | Quy trình tư vấn 5 bước |
| `testimonials` | **Page / Component**| `TestimonialsSection.tsx` *(Trang Chủ)* | Tiêu đề khối cảm nhận khách hàng |
| `news` | **Page / Component**| `NewsSection.tsx` *(Trang Chủ)* | Tiêu đề khối tin tức & tri thức trên trang chủ |
| `aboutPage` | **Page** | `about/page.tsx`, `AboutValuesSection.tsx` | Trang Giới thiệu: 4 Giá trị cốt lõi & Khung quote banner |
| `servicesPage` | **Page** | `services/page.tsx`, `ServicesPrivacyCommitment.tsx`, `ServicesFaqSection.tsx` | Intro dịch vụ, 4 bước triết lý, Cam kết bảo mật & Tải PDF điều khoản, FAQ |
| `trainingPage` | **Page** | `training/page.tsx`, `TrainingOverviewSection.tsx`, `TrainingOutcomesSection.tsx` | Hero đào tạo, Điểm nổi bật, Giá trị nhận được, FAQs, CTA cuối trang |
| `recruitment` | **Page** | `career/page.tsx`, `RecruitmentValuesSection.tsx`, `JobApplicationModal.tsx` | Hero tuyển dụng, Giá trị văn hóa, 4 Bước tuyển dụng, Form ứng tuyển, SEO |
| `contactPage` | **Page** | `contact/page.tsx` | Thẻ thông tin liên hệ trực tiếp, form liên hệ, bản đồ |
| `contactForm` | **Component** | `ConsultationModal.tsx`, `ConsultationForm.tsx` | Form modal đặt lịch tư vấn dùng chung |
| `formSuccessAlert` | **Component** | `FormSuccessAlert.tsx` | Thông báo gửi form thành công |
| `notFound` | **Page** | `src/app/not-found.tsx` | Nội dung trang báo lỗi 404 |
| `serviceDetailCommon` | **Layout / Component**| `ServiceDetailHero.tsx`, `ServiceDetailNavTabs.tsx` | 5 Tabs điều hướng chi tiết dịch vụ & Banner CTA |
| `newsDetail` & `newsListing` | **Page & Component** | `src/components/news/*` | TOC bài viết tin tức, tác giả, banner sidebar & bộ lọc tìm kiếm |

---

## 💻 Code Mẫu Tích Hợp Phía Next.js (TypeScript)

### 1. Hàm Gọi API (`src/lib/api.ts`)

```typescript
export const API_ENDPOINTS = {
  // ...
  siteSettings: `${WP_API_URL}/thientam/v1/settings`,
};

export async function getSiteSettings(): Promise<Record<string, any> | null> {
  if (WP_API_URL) {
    try {
      const res = await fetch(API_ENDPOINTS.siteSettings, {
        next: { revalidate: 60 }, // Cache ISR 60 giây
      });
      if (res.ok) {
        const result = await res.json();
        if (result && result.success && result.data) {
          return result.data;
        }
      }
    } catch (err) {
      console.debug("WP API offline for site settings:", err);
    }
  }
  return null;
}
```

### 2. Tự Động Deep Merge với `vi.json` Fallback (`src/lib/content.ts`)

```typescript
import vi from "@/locales/vi.json";
import { getSiteSettings } from "@/lib/api";

export async function getSiteContent() {
  try {
    const wpSettings = await getSiteSettings();
    if (wpSettings && typeof wpSettings === "object") {
      return buildDictionary(wpSettings);
    }
  } catch (err) {
    console.debug("Fallback to default vi.json dictionary:", err);
  }
  return dictionary;
}
```

### 3. Sử Dụng Trong Server Components (Ví Dụ: `page.tsx` hoặc `layout.tsx`)

```typescript
import { getSiteContent } from "@/lib/content";

export default async function HomePage() {
  const content = await getSiteContent();

  return (
### 4. Lệnh Đồng Bộ Nhanh Qua Terminal (CLI Script)

Bạn có thể đẩy trực tiếp toàn bộ dữ liệu từ `src/locales/vi.json` lên WordPress bất kỳ lúc nào bằng lệnh:

```bash
npm run sync:settings
```

Script sẽ tự động đọc `vi.json`, xác thực qua `X-ThienTam-Sync-Key` và cập nhật dữ liệu vào WordPress CMS chỉ sau 1 giây!
