=== iLynk SePay VietQR Payment Gateway ===
Contributors: ilynksolution
Tags: sepay, vietqr, payment, checkout, gateway, thientam
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Cổng thanh toán tự động VietQR qua SePay Sandbox & Live, quản lý đơn hàng dịch vụ, hỗ trợ trang checkout độc lập Next.js và webhook đối soát thời gian thực. Phát triển bởi iLynk.

== Description ==

Plugin **iLynk SePay VietQR Payment Gateway** cung cấp giải pháp toàn diện cho việc nhận thanh toán chuyển khoản ngân hàng tự động qua SePay VietQR:

* **Tự động sinh mã VietQR**: Chuẩn Napas 24/7 với đầy đủ Số tài khoản, Số tiền, và Nội dung chuyển khoản theo tiền tố đơn hàng.
* **Hỗ trợ Trang Checkout Độc Lập**: Tích hợp đường dẫn checkout Next.js (`/order/[token]`) bảo mật bằng chuỗi 64 ký tự.
* **Webhook Đối Soát Thời Gian Thực**: Tự động khớp số tiền, tài khoản nhận, gạch nợ đơn hàng ngay tức thì khi ngân hàng báo biến động số dư.
* **Hỗ Trợ SePay Sandbox & Live**: Dễ dàng chuyển đổi giữa môi trường thử nghiệm và sản xuất trực tiếp trong trang quản trị.
* **Quản trị Đơn hàng Trực quan**: Cột mở nhanh link checkout cho khách, thanh sao chép link, và chi tiết giao dịch (Transaction ID, Reference Code, Thời gian thanh toán).

*Phát triển bởi **iLynk Solution** (https://ilynk.vn).*

== Installation ==

1. Tải file `ilynk-sepay-vietqr.zip` hoặc tải thư mục `ilynk-sepay-vietqr` lên thư mục `/wp-content/plugins/`.
2. Vào **WordPress Admin > Plugins (Gói mở rộng)** và kích hoạt **iLynk SePay VietQR Payment Gateway**.
3. Truy cập **Đơn SePay > Cài đặt SePay** để thiết lập:
   * Ngân hàng & Số tài khoản nhận tiền
   * Tiền tố mã đơn (ví dụ: `TT`)
   * Webhook API Key (tạo từ SePay.vn)
   * Frontend Base URL (ví dụ: `https://thientam68.com`)
4. Sao chép URL Webhook và dán vào phần cài đặt Webhook trên trang quản trị SePay.

== Changelog ==

= 1.0.0 =
* Phiên bản phát hành đầu tiên: Tách module SePay từ theme thành plugin độc lập, hỗ trợ trang checkout Next.js, polling thời gian thực và quản lý đơn hàng. Phát triển bởi iLynk.
