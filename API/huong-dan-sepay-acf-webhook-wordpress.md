# Tích hợp SePay Sandbox với WordPress và ACF

Tài liệu này áp dụng cho website WordPress tự xây dựng bằng ACF, không sử dụng WooCommerce. Luồng thanh toán:

```text
Khách gửi form → WordPress tạo đơn Pending → hiển thị VietQR
→ SePay gửi webhook → WordPress kiểm tra giao dịch → cập nhật đơn Paid
```

## 1. Yêu cầu

- WordPress có HTTPS và truy cập được từ Internet.
- ACF Pro nếu muốn dùng Options Page.
- Tài khoản SePay đã bật **Test mode**.
- Một tài khoản ngân hàng thử nghiệm trong SePay Test mode.
- PHP 7.4 trở lên.

Nếu đang chạy WordPress tại localhost, cần dùng ngrok hoặc Cloudflare Tunnel để SePay gọi được webhook.

## 2. Cấu trúc dữ liệu ACF

### 2.1. Options Page: Cấu hình SePay

Tạo Options Page có slug `sepay-settings`, sau đó tạo field group với các trường:

| Label | Field name | Type | Ví dụ |
| --- | --- | --- | --- |
| Ngân hàng | `bank_name` | Text | `MBBank` |
| Số tài khoản | `account_number` | Text | Số tài khoản test |
| Chủ tài khoản | `account_holder` | Text | `NGUYEN VAN A` |
| Tiền tố thanh toán | `payment_prefix` | Text | `DH` |

Trong Sandbox, nhập số tài khoản test. Khi chuyển sang Live, thay bằng tài khoản thật đã liên kết với SePay.

### 2.2. CPT đơn thanh toán

Plugin mẫu bên dưới tự đăng ký CPT `sepay_order`. Tạo ACF Field Group gắn với:

```text
Post Type is equal to SePay Orders
```

Các field cần tạo:

| Label | Field name | Type | Giá trị |
| --- | --- | --- | --- |
| Khách hàng | `customer_name` | Text | Tên khách hàng |
| Số điện thoại | `customer_phone` | Text | Số điện thoại |
| Email | `customer_email` | Email | Email khách hàng |
| Sản phẩm | `product_id` | Post Object | ID sản phẩm |
| Số tiền | `amount` | Number | Số nguyên VND |
| Mã thanh toán | `payment_code` | Text | `DH123` |
| Trạng thái | `payment_status` | Select | `pending`, `paid`, `cancelled` |
| Mã giao dịch SePay | `sepay_transaction_id` | Text | ID từ webhook |
| Mã tham chiếu | `sepay_reference_code` | Text | `referenceCode` |
| Thời gian thanh toán | `paid_at` | Date Time Picker | Ngày thanh toán |
| Payload webhook | `raw_payload` | Textarea | JSON gốc |

Thiết lập field `payment_status`:

```text
pending : Chờ thanh toán
paid : Đã thanh toán
cancelled : Đã hủy
```

### 2.3. Giá sản phẩm

Ví dụ sản phẩm của website có ACF field:

| Label | Field name | Type |
| --- | --- | --- |
| Giá bán | `price` | Number |

Backend phải lấy giá từ `price` trong WordPress. Không sử dụng số tiền do JavaScript hoặc người dùng gửi lên.

## 3. Khai báo khóa webhook

Thêm vào `wp-config.php`, đặt trước dòng `/* That's all, stop editing! */`:

```php
define('SEPAY_WEBHOOK_KEY', 'thay-bang-api-key-sandbox-cua-ban');
```

Không đưa API Key vào JavaScript, ACF hoặc Git repository.

## 4. Tạo custom plugin

Tạo thư mục:

```text
wp-content/plugins/custom-sepay-acf/
```

Tạo file:

```text
wp-content/plugins/custom-sepay-acf/custom-sepay-acf.php
```

Dán toàn bộ mã sau:

```php
<?php
/**
 * Plugin Name: Custom SePay ACF
 * Description: Tạo đơn ACF, VietQR và nhận webhook SePay.
 * Version: 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Đăng ký CPT lưu đơn thanh toán.
 */
add_action('init', function () {
    register_post_type('sepay_order', [
        'labels' => [
            'name'          => 'Đơn SePay',
            'singular_name' => 'Đơn SePay',
            'add_new_item'  => 'Thêm đơn SePay',
            'edit_item'     => 'Chỉnh sửa đơn SePay',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'supports'     => ['title'],
        'menu_icon'    => 'dashicons-money-alt',
    ]);
});

/**
 * Tạo Options Page nếu ACF Pro đang hoạt động.
 */
add_action('acf/init', function () {
    if (!function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page([
        'page_title' => 'Cấu hình SePay',
        'menu_title' => 'Cấu hình SePay',
        'menu_slug'  => 'sepay-settings',
        'capability' => 'manage_options',
        'redirect'   => false,
        'icon_url'   => 'dashicons-bank',
    ]);
});

/**
 * Đăng ký REST API.
 */
add_action('rest_api_init', function () {
    register_rest_route('custom-sepay/v1', '/orders', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'custom_sepay_create_order',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('custom-sepay/v1', '/orders/(?P<token>[a-f0-9]{64})/status', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'custom_sepay_get_order_status',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('custom-sepay/v1', '/webhook', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'custom_sepay_webhook',
        'permission_callback' => '__return_true',
    ]);
});

/**
 * Chuẩn hóa số tiền VND thành số nguyên dương.
 */
function custom_sepay_normalize_amount($value): int
{
    if (!is_numeric($value)) {
        return 0;
    }

    return max(0, (int) round((float) $value));
}

/**
 * Sinh URL VietQR.
 */
function custom_sepay_build_qr_url(int $amount, string $payment_code): string
{
    $bank           = sanitize_text_field((string) get_field('bank_name', 'option'));
    $account_number = preg_replace('/\s+/', '', (string) get_field('account_number', 'option'));
    $account_holder = sanitize_text_field((string) get_field('account_holder', 'option'));

    return add_query_arg([
        'acc'      => $account_number,
        'bank'     => $bank,
        'amount'   => $amount,
        'des'      => $payment_code,
        'template' => 'compact',
        'holder'   => $account_holder,
    ], 'https://qr.sepay.vn/img');
}

/**
 * Tạo đơn thanh toán.
 *
 * Request JSON:
 * {
 *   "product_id": 123,
 *   "customer_name": "Nguyễn Văn A",
 *   "customer_phone": "0900000000",
 *   "customer_email": "a@example.com"
 * }
 */
function custom_sepay_create_order(WP_REST_Request $request)
{
    if (!function_exists('get_field') || !function_exists('update_field')) {
        return new WP_Error('acf_required', 'ACF chưa được kích hoạt.', ['status' => 500]);
    }

    $product_id    = absint($request->get_param('product_id'));
    $customer_name = sanitize_text_field((string) $request->get_param('customer_name'));
    $customer_phone = sanitize_text_field((string) $request->get_param('customer_phone'));
    $customer_email = sanitize_email((string) $request->get_param('customer_email'));

    if (!$product_id || get_post_status($product_id) !== 'publish') {
        return new WP_Error('invalid_product', 'Sản phẩm không hợp lệ.', ['status' => 422]);
    }

    if ($customer_name === '' || $customer_phone === '') {
        return new WP_Error('missing_customer', 'Vui lòng nhập tên và số điện thoại.', ['status' => 422]);
    }

    $amount = custom_sepay_normalize_amount(get_field('price', $product_id));

    if ($amount <= 0) {
        return new WP_Error('invalid_amount', 'Giá sản phẩm không hợp lệ.', ['status' => 422]);
    }

    $order_id = wp_insert_post([
        'post_type'   => 'sepay_order',
        'post_status' => 'publish',
        'post_title'  => 'Đơn SePay - ' . current_time('Y-m-d H:i:s'),
    ], true);

    if (is_wp_error($order_id)) {
        return $order_id;
    }

    $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) get_field('payment_prefix', 'option')));
    $prefix = $prefix !== '' ? $prefix : 'DH';

    $payment_code = $prefix . $order_id;
    $public_token = bin2hex(random_bytes(32));

    update_field('customer_name', $customer_name, $order_id);
    update_field('customer_phone', $customer_phone, $order_id);
    update_field('customer_email', $customer_email, $order_id);
    update_field('product_id', $product_id, $order_id);
    update_field('amount', $amount, $order_id);
    update_field('payment_code', $payment_code, $order_id);
    update_field('payment_status', 'pending', $order_id);
    update_post_meta($order_id, '_sepay_public_token', $public_token);

    wp_update_post([
        'ID'         => $order_id,
        'post_title' => sprintf('Đơn %s - %s', $payment_code, $customer_name),
    ]);

    return new WP_REST_Response([
        'success'      => true,
        'order_id'     => $order_id,
        'payment_code' => $payment_code,
        'amount'       => $amount,
        'qr_url'       => custom_sepay_build_qr_url($amount, $payment_code),
        'status'       => 'pending',
        'status_url'   => rest_url('custom-sepay/v1/orders/' . $public_token . '/status'),
    ], 201);
}

/**
 * API để frontend kiểm tra trạng thái thanh toán.
 */
function custom_sepay_get_order_status(WP_REST_Request $request)
{
    $token = sanitize_text_field((string) $request['token']);

    $orders = get_posts([
        'post_type'      => 'sepay_order',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_sepay_public_token',
        'meta_value'     => $token,
    ]);

    if (!$orders) {
        return new WP_Error('order_not_found', 'Không tìm thấy đơn hàng.', ['status' => 404]);
    }

    $order_id = (int) $orders[0];

    return new WP_REST_Response([
        'success'      => true,
        'payment_code' => (string) get_field('payment_code', $order_id),
        'status'       => (string) get_field('payment_status', $order_id),
        'paid_at'      => (string) get_field('paid_at', $order_id),
    ], 200);
}

/**
 * Lấy mã thanh toán từ trường code hoặc nội dung chuyển khoản.
 */
function custom_sepay_extract_payment_code(array $payload): string
{
    $code = strtoupper(sanitize_text_field((string) ($payload['code'] ?? '')));

    if ($code !== '') {
        return $code;
    }

    $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) get_field('payment_prefix', 'option')));
    $prefix = $prefix !== '' ? $prefix : 'DH';
    $content = strtoupper(sanitize_text_field((string) ($payload['content'] ?? '')));

    if (preg_match('/\b' . preg_quote($prefix, '/') . '\d+\b/', $content, $matches)) {
        return $matches[0];
    }

    return '';
}

/**
 * Nhận webhook SePay.
 */
function custom_sepay_webhook(WP_REST_Request $request)
{
    if (!defined('SEPAY_WEBHOOK_KEY') || SEPAY_WEBHOOK_KEY === '') {
        return new WP_Error('missing_webhook_key', 'Webhook key chưa được cấu hình.', ['status' => 500]);
    }

    $authorization = trim((string) $request->get_header('authorization'));
    $expected       = 'Apikey ' . SEPAY_WEBHOOK_KEY;

    if ($authorization === '' || !hash_equals($expected, $authorization)) {
        return new WP_Error('unauthorized', 'Webhook không hợp lệ.', ['status' => 401]);
    }

    $payload = $request->get_json_params();

    if (!is_array($payload)) {
        return new WP_Error('invalid_payload', 'Payload không hợp lệ.', ['status' => 400]);
    }

    // Chỉ xử lý giao dịch tiền vào.
    if (($payload['transferType'] ?? '') !== 'in') {
        return new WP_REST_Response(['success' => true], 200);
    }

    $transaction_id = sanitize_text_field((string) ($payload['id'] ?? ''));
    $reference_code = sanitize_text_field((string) ($payload['referenceCode'] ?? ''));
    $payment_code   = custom_sepay_extract_payment_code($payload);
    $received_amount = custom_sepay_normalize_amount($payload['transferAmount'] ?? 0);
    $received_account = preg_replace('/\s+/', '', (string) ($payload['accountNumber'] ?? ''));
    $expected_account = preg_replace('/\s+/', '', (string) get_field('account_number', 'option'));

    if ($transaction_id === '' || $payment_code === '' || $received_amount <= 0) {
        return new WP_Error('missing_transaction_data', 'Thiếu dữ liệu giao dịch.', ['status' => 422]);
    }

    // Bỏ qua webhook đã xử lý trước đó.
    $duplicate = get_posts([
        'post_type'      => 'sepay_order',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => 'sepay_transaction_id',
        'meta_value'     => $transaction_id,
    ]);

    if ($duplicate) {
        return new WP_REST_Response(['success' => true, 'duplicate' => true], 200);
    }

    $orders = get_posts([
        'post_type'      => 'sepay_order',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => 'payment_code',
        'meta_value'     => $payment_code,
    ]);

    if (!$orders) {
        return new WP_Error('order_not_found', 'Không tìm thấy đơn hàng.', ['status' => 404]);
    }

    $order_id        = (int) $orders[0];
    $expected_amount = custom_sepay_normalize_amount(get_field('amount', $order_id));
    $current_status  = (string) get_field('payment_status', $order_id);

    if ($expected_account !== '' && $received_account !== $expected_account) {
        return new WP_Error('account_mismatch', 'Sai tài khoản nhận.', ['status' => 422]);
    }

    if ($received_amount !== $expected_amount) {
        return new WP_Error('amount_mismatch', 'Số tiền thanh toán không khớp.', ['status' => 422]);
    }

    if ($current_status === 'paid') {
        return new WP_REST_Response(['success' => true, 'already_paid' => true], 200);
    }

    update_field('payment_status', 'paid', $order_id);
    update_field('sepay_transaction_id', $transaction_id, $order_id);
    update_field('sepay_reference_code', $reference_code, $order_id);
    update_field('paid_at', current_time('Y-m-d H:i:s'), $order_id);
    update_field(
        'raw_payload',
        wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        $order_id
    );

    do_action('custom_sepay_payment_completed', $order_id, $payload);

    return new WP_REST_Response([
        'success'  => true,
        'order_id' => $order_id,
    ], 200);
}
```

Sau đó vào **Plugins → Installed Plugins** và kích hoạt **Custom SePay ACF**.

## 5. Gọi API tạo đơn từ frontend

Ví dụ JavaScript:

```js
const response = await fetch('/wp-json/custom-sepay/v1/orders', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    product_id: 123,
    customer_name: 'Nguyễn Văn A',
    customer_phone: '0900000000',
    customer_email: 'a@example.com',
  }),
});

const order = await response.json();

if (!response.ok) {
  throw new Error(order.message || 'Không thể tạo đơn');
}

document.querySelector('#payment-qr').src = order.qr_url;
document.querySelector('#payment-code').textContent = order.payment_code;
document.querySelector('#payment-amount').textContent =
  new Intl.NumberFormat('vi-VN').format(order.amount) + 'đ';

const timer = setInterval(async () => {
  const statusResponse = await fetch(order.status_url, { cache: 'no-store' });
  const statusData = await statusResponse.json();

  if (statusData.status === 'paid') {
    clearInterval(timer);
    document.querySelector('#payment-status').textContent = 'Thanh toán thành công';
  }
}, 3000);
```

HTML mẫu:

```html
<div class="payment-box">
  <img id="payment-qr" src="" alt="Mã QR thanh toán">
  <p>Số tiền: <strong id="payment-amount"></strong></p>
  <p>Nội dung: <strong id="payment-code"></strong></p>
  <p id="payment-status">Đang chờ thanh toán...</p>
</div>
```

## 6. Cấu hình webhook trong SePay Test mode

Đăng nhập `my.sepay.vn` và thực hiện:

1. Bật **Test mode**.
2. Vào **Bank Accounts → Add New** và tạo tài khoản test.
3. Vào **Webhooks → Add Webhook**.
4. Chọn giao dịch **Tiền vào**.
5. Nhập webhook URL:

```text
https://ten-mien-test.com/wp-json/custom-sepay/v1/webhook
```

6. Chọn phương thức xác thực **API Key**.
7. Nhập đúng giá trị đã khai báo trong `SEPAY_WEBHOOK_KEY`.
8. Nếu dùng bộ lọc mã thanh toán, nhập tiền tố `DH`.
9. Kích hoạt webhook.

## 7. Test giao dịch không dùng tiền thật

### Bước 1: Tạo đơn

Gửi form trên website. Ví dụ hệ thống trả về:

```json
{
  "payment_code": "DH123",
  "amount": 100000,
  "status": "pending"
}
```

### Bước 2: Mô phỏng giao dịch

Trong SePay Test mode, vào **Transactions → Simulate** và nhập:

```text
Account: tài khoản test đã cấu hình trong ACF
Type: In
Amount: 100000
Content: DH123
```

### Bước 3: Kiểm tra kết quả

- SePay Webhook Delivery Log trả về HTTP `200`.
- Response có `{"success":true}`.
- ACF `payment_status` chuyển từ `pending` sang `paid`.
- `sepay_transaction_id`, `sepay_reference_code`, `paid_at` và `raw_payload` được lưu.
- Frontend hiển thị **Thanh toán thành công** sau lần polling tiếp theo.

## 8. Lỗi thường gặp

### Webhook trả về 401

- API Key trên SePay không giống `SEPAY_WEBHOOK_KEY`.
- Header phải có dạng:

```text
Authorization: Apikey YOUR_API_KEY
```

### Webhook trả về 404

- Mã trong giao dịch không trùng ACF `payment_code`.
- Kiểm tra tiền tố `DH`.
- Vào **Settings → Permalinks** và nhấn **Save Changes** một lần.

### Webhook trả về 422 Amount mismatch

Số tiền mô phỏng phải bằng chính xác ACF `amount` của đơn.

### Localhost không nhận webhook

SePay cần URL truy cập được từ Internet. Sử dụng ngrok hoặc Cloudflare Tunnel và cập nhật lại webhook URL.

### Frontend không đổi trạng thái

- Kiểm tra `status_url` từ API tạo đơn.
- Tắt cache cho REST API trạng thái.
- Không cache trang thanh toán hoặc response `/wp-json/custom-sepay/`.

## 9. Checklist trước khi chạy thật

- Đổi ACF sang tài khoản ngân hàng Live.
- Tạo lại webhook trong môi trường Live.
- Đổi `SEPAY_WEBHOOK_KEY` sang khóa Live đủ mạnh.
- Kiểm tra đúng `accountNumber`, `payment_code` và `transferAmount`.
- Không đánh dấu Paid nếu số tiền không khớp.
- Không xử lý lại cùng `transaction_id`.
- Bật HTTPS và giới hạn request vào webhook.
- Lưu payload để đối soát.
- Thêm cron đối soát giao dịch định kỳ.
- Thử một giao dịch thật có giá trị nhỏ trước khi mở thanh toán chính thức.

> Lưu ý production: truy vấn post meta chỉ phù hợp với hệ thống nhỏ. Nếu có nhiều giao dịch hoặc cần chống xử lý đồng thời tuyệt đối, nên tạo bảng giao dịch riêng với cột `transaction_id` có UNIQUE INDEX.

## 10. Tài liệu SePay

- [SePay Test mode](https://developer.sepay.vn/en/sepay-webhooks/test-mode/bat-dau-nhanh)
- [Xác thực webhook](https://developer.sepay.vn/en/sepay-webhooks/xac-thuc)
- [Tích hợp webhook](https://developer.sepay.vn/en/sepay-webhooks/tich-hop-webhook)
- [Bảo mật webhook](https://developer.sepay.vn/en/sepay-webhooks/bao-mat)
- [Tạo VietQR](https://developer.sepay.vn/en/tien-ich-khac/tao-qr-code)
