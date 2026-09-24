<?php

/**
 * Plugin Name:       iLynk SePay VietQR Payment Gateway
 * Plugin URI:        https://ilynk.vn
 * Description:       Cổng thanh toán tự động VietQR qua SePay Sandbox & Live, quản lý đơn hàng dịch vụ, hỗ trợ trang checkout độc lập Next.js và webhook đối soát thời gian thực. Phát triển bởi iLynk.
 * Version:           1.0.0
 * Author:            iLynk Solution
 * Author URI:        https://ilynk.vn
 * License:           GPL-2.0+
 * Text Domain:       ilynk-sepay
 * Domain Path:       /languages
 */

if (! defined('ABSPATH')) {
    exit;
}

define('ILYNK_SEPAY_VERSION', '1.0.0');
define('ILYNK_SEPAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ILYNK_SEPAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ILYNK_SEPAY_PLUGIN_FILE', __FILE__);

/**
 * Kích hoạt & Hủy kích hoạt Plugin
 */
register_activation_hook(__FILE__, function () {
    custom_sepay_register_order_post_type();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

/**
 * 1. Đăng ký Custom Post Type: sepay_order
 */
add_action('init', 'custom_sepay_register_order_post_type');
function custom_sepay_register_order_post_type()
{
    $labels = [
        'name'               => 'Đơn SePay',
        'singular_name'      => 'Đơn SePay',
        'menu_name'          => 'Đơn SePay',
        'name_admin_bar'     => 'Đơn SePay',
        'add_new_item'       => 'Thêm đơn SePay',
        'edit_item'          => 'Chi tiết đơn SePay',
        'view_item'          => 'Xem đơn SePay',
        'all_items'          => 'Tất cả đơn SePay',
        'search_items'       => 'Tìm kiếm đơn SePay',
        'not_found'          => 'Chưa có đơn SePay nào.',
        'not_found_in_trash' => 'Không có đơn nào trong thùng rác.',
    ];

    register_post_type('sepay_order', [
        'labels'             => $labels,
        'description'        => 'Lưu trữ các đơn thanh toán quét mã VietQR tự động qua SePay - Phát triển bởi iLynk',
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => false,
        'rewrite'            => false,
        'capability_type'    => 'post',
        'capabilities'       => [
            'create_posts' => 'do_not_allow', // Đơn sinh tự động từ API
        ],
        'map_meta_cap'       => true,
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 23,
        'menu_icon'          => 'dashicons-money-alt',
        'supports'           => ['title'],
        'show_in_rest'       => false,
    ]);
}

// Xóa menu "Thêm mới" đơn bằng tay
add_action('admin_menu', function () {
    remove_submenu_page('edit.php?post_type=sepay_order', 'post-new.php?post_type=sepay_order');
}, 999);

/**
 * 2. Cài đặt Cấu hình SePay:
 * 2.1. Hỗ trợ ACF Options Page nếu có ACF Pro
 */
add_action('acf/init', function () {
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title'  => 'Cấu hình SePay',
            'menu_title'  => 'Cấu hình SePay (ACF)',
            'menu_slug'   => 'sepay-settings',
            'capability'  => 'manage_options',
            'redirect'    => false,
            'icon_url'    => 'dashicons-bank',
            'parent_slug' => 'edit.php?post_type=sepay_order',
        ]);
    }
});

/**
 * 2.2. Native Submenu Cài đặt SePay (Hoạt động độc lập không phụ thuộc ACF Pro)
 */
add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=sepay_order',
        'Cài đặt Cổng SePay (iLynk)',
        'Cài đặt SePay',
        'manage_options',
        'custom-sepay-settings',
        'custom_sepay_render_native_settings_page'
    );
});

// Tự động kiểm tra và hủy các đơn hàng pending khi cổng thanh toán trên website bị tắt
add_action('admin_init', function () {
    if (get_option('sepay_enabled', '1') !== '1') {
        $pending_orders = get_posts([
            'post_type'      => 'sepay_order',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_key'       => 'payment_status',
            'meta_value'     => 'pending',
        ]);
        if (!empty($pending_orders)) {
            foreach ($pending_orders as $p_id) {
                update_post_meta($p_id, 'payment_status', 'cancelled');
                update_post_meta($p_id, 'cancelled_reason', 'site_disabled');
                update_post_meta($p_id, 'cancelled_at', current_time('mysql'));
                do_action('custom_sepay_order_cancelled', $p_id, 'site_disabled');
            }
        }
    }
});

function custom_sepay_get_setting(string $key, string $default = ''): string
{
    // Ưu tiên đọc từ ACF option nếu có
    if (function_exists('get_field')) {
        $acf_val = get_field($key, 'option');
        if (! empty($acf_val)) {
            return (string) $acf_val;
        }
    }
    // Fallback sang WP option
    return (string) get_option('sepay_' . $key, $default);
}

/**
 * Lấy Base URL của giao diện Next.js (Frontend)
 */
function custom_sepay_get_frontend_url(): string
{
    $url = trim(custom_sepay_get_setting('frontend_url', ''));
    if (! empty($url)) {
        return untrailingslashit($url);
    }
    // Tự động nhận diện host nếu request đến từ localhost / dev
    if (isset($_SERVER['HTTP_ORIGIN']) && (strpos($_SERVER['HTTP_ORIGIN'], 'localhost') !== false || strpos($_SERVER['HTTP_ORIGIN'], '127.0.0.1') !== false)) {
        return untrailingslashit((string) $_SERVER['HTTP_ORIGIN']);
    }
    if (isset($_SERVER['HTTP_REFERER']) && (strpos($_SERVER['HTTP_REFERER'], 'localhost') !== false || strpos($_SERVER['HTTP_REFERER'], '127.0.0.1') !== false)) {
        $parts = parse_url((string) $_SERVER['HTTP_REFERER']);
        if (! empty($parts['scheme']) && ! empty($parts['host'])) {
            $port = ! empty($parts['port']) ? ':' . $parts['port'] : '';
            return $parts['scheme'] . '://' . $parts['host'] . $port;
        }
    }
    return 'https://thientam68.com';
}

function custom_sepay_render_native_settings_page()
{
    $saved = false;
    if (isset($_POST['custom_sepay_save_nonce']) && wp_verify_nonce($_POST['custom_sepay_save_nonce'], 'custom_sepay_save_action')) {
        update_option('sepay_bank_name', sanitize_text_field($_POST['bank_name'] ?? ''));
        update_option('sepay_account_number', preg_replace('/\s+/', '', sanitize_text_field($_POST['account_number'] ?? '')));
        update_option('sepay_account_holder', sanitize_text_field($_POST['account_holder'] ?? ''));
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', sanitize_text_field($_POST['payment_prefix'] ?? 'TT')));
        update_option('sepay_payment_prefix', $prefix ?: 'TT');
        update_option('sepay_order_timeout', max(1, absint($_POST['order_timeout'] ?? 15)));
        update_option('sepay_webhook_key', sanitize_text_field($_POST['webhook_key'] ?? ''));
        update_option('sepay_frontend_url', esc_url_raw(trim($_POST['frontend_url'] ?? '')));
        $new_enabled = isset($_POST['is_enabled']) ? '1' : '0';
        update_option('sepay_enabled', $new_enabled);
        update_option('sepay_is_sandbox', isset($_POST['is_sandbox']) ? '1' : '0');
        if ($new_enabled === '0') {
            // Tự động hủy tất cả đơn hàng đang chờ thanh toán khi admin tắt cổng thanh toán trên website
            $pending_orders = get_posts([
                'post_type'      => 'sepay_order',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'meta_key'       => 'payment_status',
                'meta_value'     => 'pending',
            ]);
            foreach ($pending_orders as $p_id) {
                update_post_meta($p_id, 'payment_status', 'cancelled');
                update_post_meta($p_id, 'cancelled_reason', 'site_disabled');
                update_post_meta($p_id, 'cancelled_at', current_time('mysql'));
                do_action('custom_sepay_order_cancelled', $p_id, 'site_disabled');
            }
        }
        $saved = true;
    }

    $bank_name      = custom_sepay_get_setting('bank_name', 'MBBank');
    $account_number = custom_sepay_get_setting('account_number', '');
    $account_holder = custom_sepay_get_setting('account_holder', 'PHONG THUY THIEN TAM');
    $payment_prefix = custom_sepay_get_setting('payment_prefix', 'TT');
    $order_timeout  = (int) get_option('sepay_order_timeout', 15);
    $frontend_url   = custom_sepay_get_setting('frontend_url', 'https://thientam68.com');
    $webhook_key    = defined('SEPAY_WEBHOOK_KEY') && SEPAY_WEBHOOK_KEY !== '' ? SEPAY_WEBHOOK_KEY : get_option('sepay_webhook_key', '');
    $is_enabled     = get_option('sepay_enabled', '1') === '1';
    $is_sandbox     = get_option('sepay_is_sandbox', '1') === '1';

    $webhook_url = rest_url('custom-sepay/v1/webhook');
?>
    <style>
        .ilynk-sepay-wrap {
            max-width: 920px;
            margin-top: 20px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }

        .ilynk-sepay-wrap .form-table {
            width: 100%;
            table-layout: fixed;
        }

        .ilynk-sepay-wrap .form-table th {
            font-weight: 600;
            color: #334155;
            padding: 16px 12px 16px 0;
            font-size: 14px;
            vertical-align: top;
            width: 220px;
        }

        .ilynk-sepay-wrap .form-table td {
            padding: 12px 0 12px 10px;
            width: calc(100% - 220px);
        }

        .ilynk-sepay-wrap input[type="text"],
        .ilynk-sepay-wrap input[type="url"],
        .ilynk-sepay-wrap input[type="password"] {
            width: 100% !important;
            max-width: 100% !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            padding: 8px 14px !important;
            font-size: 14px !important;
            color: #1e293b !important;
            background: #ffffff !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
            min-height: 40px !important;
            box-sizing: border-box !important;
        }

        .ilynk-sepay-wrap input[type="text"]:focus,
        .ilynk-sepay-wrap input[type="url"]:focus,
        .ilynk-sepay-wrap input[type="password"]:focus {
            border-color: #0284c7 !important;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15) !important;
            outline: none !important;
        }

        .ilynk-input-group {
            display: flex !important;
            gap: 10px !important;
            align-items: center !important;
            width: 100% !important;
        }

        .ilynk-input-group input {
            flex: 1 1 0% !important;
            min-width: 0 !important;
            width: 100% !important;
            margin: 0 !important;
        }

        .ilynk-sepay-wrap .description {
            color: #64748b;
            font-size: 13px;
            margin-top: 6px;
            line-height: 1.5;
        }

        .ilynk-sepay-wrap .description code {
            background: #f1f5f9;
            color: #0369a1;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
            border: 1px solid #e2e8f0;
        }

        .ilynk-sepay-btn-save {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
            border: none !important;
            border-radius: 8px !important;
            color: #ffffff !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            padding: 0 28px !important;
            height: 44px !important;
            line-height: 1 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35) !important;
            cursor: pointer !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            text-shadow: none !important;
            outline: none !important;
        }

        .ilynk-sepay-btn-save:hover {
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.45) !important;
            color: #ffffff !important;
        }

        .ilynk-sepay-btn-save:active {
            transform: translateY(1px) !important;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.3) !important;
        }

        .ilynk-sepay-btn-secondary {
            background: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            color: #334155 !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            height: 40px !important;
            line-height: 1 !important;
            padding: 0 16px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 7px !important;
            cursor: pointer !important;
            transition: all 0.15s ease !important;
            text-shadow: none !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
        }

        .ilynk-sepay-btn-secondary:hover {
            background: #f1f5f9 !important;
            border-color: #94a3b8 !important;
            color: #0f172a !important;
        }
    </style>
    <div class="wrap ilynk-sepay-wrap">
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 24px 28px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 style="color: #fff; margin: 0 0 6px 0; font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-money-alt" style="font-size: 26px; width: 26px; height: 26px; color: #38bdf8;"></span>
                    iLynk SePay VietQR Payment Gateway
                </h1>
                <p style="margin: 0; color: #94a3b8; font-size: 13px;">
                    Giải pháp tích hợp cổng thanh toán VietQR tự động •
                    <span style="color: #38bdf8; font-weight: 600;">Phát triển bởi iLynk</span>
                </p>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <span style="background: <?php echo $is_enabled ? 'rgba(34, 197, 94, 0.2)' : 'rgba(239, 68, 68, 0.2)'; ?>; color: <?php echo $is_enabled ? '#4ade80' : '#f87171'; ?>; border: 1px solid <?php echo $is_enabled ? '#22c55e' : '#ef4444'; ?>; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                    <?php echo $is_enabled ? '🟢 Đang Bật' : '🔴 Đang Tắt'; ?>
                </span>
                <span style="background: <?php echo $is_sandbox ? 'rgba(234, 88, 12, 0.2)' : 'rgba(34, 197, 94, 0.2)'; ?>; color: <?php echo $is_sandbox ? '#fb923c' : '#4ade80'; ?>; border: 1px solid <?php echo $is_sandbox ? '#ea580c' : '#22c55e'; ?>; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                    <?php echo $is_sandbox ? '🧪 Sandbox Mode' : '🚀 Live Production'; ?>
                </span>
                <span style="background: rgba(255,255,255,0.1); color: #cbd5e1; padding: 4px 10px; border-radius: 20px; font-size: 11px;">
                    v<?php echo esc_html(ILYNK_SEPAY_VERSION); ?>
                </span>
            </div>
        </div>

        <?php if ($saved): ?>
            <div class="notice notice-success is-dismissible" style="border-radius: 8px; border-left-color: #22c55e; padding: 12px 18px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <p style="margin: 0; font-size: 14px;"><strong>✅ Đã lưu thành công cấu hình SePay!</strong></p>
            </div>
        <?php endif; ?>

        <div style="background: #fff; padding: 26px 30px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.04); border: 1px solid #e2e8f0;">
            <form method="POST" action="">
                <?php wp_nonce_field('custom_sepay_save_action', 'custom_sepay_save_nonce'); ?>

                <table class="form-table" role="presentation" style="margin-top: 0;">
                    <tr>
                        <th scope="row">Trạng thái Cổng thanh toán</th>
                        <td>
                            <label style="display: inline-flex; align-items: center; gap: 10px; cursor: pointer; user-select: none;">
                                <input type="checkbox" name="is_enabled" value="1" <?php checked($is_enabled, true); ?> style="width: 18px; height: 18px; cursor: pointer;" />
                                <span style="font-weight: 600; color: <?php echo $is_enabled ? '#16a34a' : '#dc2626'; ?>; font-size: 14px;">
                                    <?php echo $is_enabled ? '🟢 Đang BẬT cổng thanh toán VietQR trên Website' : '🔴 Đang TẮT cổng thanh toán VietQR trên Website'; ?>
                                </span>
                            </label>
                            <p class="description">Khi Tắt: Giao diện Website Next.js sẽ tự động ẩn tùy chọn quét mã VietQR và chỉ nhận thông tin tư vấn.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Chế độ Môi trường</th>
                        <td>
                            <label style="display: inline-flex; align-items: center; gap: 10px; cursor: pointer; user-select: none;">
                                <input type="checkbox" name="is_sandbox" value="1" <?php checked($is_sandbox, true); ?> style="width: 18px; height: 18px; cursor: pointer;" />
                                <span style="font-weight: 600; color: #ea580c; font-size: 14px;">Bật SePay Sandbox (Môi trường Thử nghiệm)</span>
                            </label>
                            <p class="description">Khi bật, các tài khoản và giao dịch thử nghiệm từ SePay Sandbox sẽ được chấp nhận.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Ngân hàng Thụ hưởng</th>
                        <td>
                            <input type="text" name="bank_name" value="<?php echo esc_attr($bank_name); ?>" placeholder="Ví dụ: Vietcombank, MBBank, TPBank..." required />
                            <p class="description">Tên thương hiệu ngân hàng chính xác theo SePay (VD: <code>Vietcombank</code>, <code>MBBank</code>, <code>Techcombank</code>).</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Số tài khoản Ngân hàng</th>
                        <td>
                            <input type="text" name="account_number" value="<?php echo esc_attr($account_number); ?>" placeholder="Ví dụ: 0000000001 (Sandbox) hoặc STK thật" style="font-family: monospace;" required />
                            <p class="description">Số tài khoản đăng ký trên cổng SePay (Trong Sandbox điền <code>0000000001</code>).</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Tên Chủ tài khoản</th>
                        <td>
                            <input type="text" name="account_holder" value="<?php echo esc_attr($account_holder); ?>" placeholder="Ví dụ: PHONG THUY THIEN TAM" required />
                            <p class="description">Tên in hoa không dấu của chủ tài khoản nhận tiền.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Tiền tố mã đơn (Prefix)</th>
                        <td>
                            <input type="text" name="payment_prefix" value="<?php echo esc_attr($payment_prefix); ?>" style="text-transform: uppercase; font-weight: bold;" maxlength="10" required />
                            <p class="description">Tiền tố nhận diện mã đơn hàng khi chuyển khoản (Mặc định: <code>TT</code>, tạo mã như TT2130, TT2131...).</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Thời gian hiệu lực (Timeout)</th>
                        <td>
                            <div style="display: flex; gap: 8px; align-items: center; max-width: 250px;">
                                <input type="number" name="order_timeout" value="<?php echo esc_attr($order_timeout); ?>" min="1" max="1440" style="width: 100px; text-align: center; font-weight: bold;" required />
                                <span style="font-weight: 600; color: #475569;">phút</span>
                            </div>
                            <p class="description">Thời gian tối đa để khách thanh toán VietQR (Mặc định: <code>15</code> phút). Quá thời hạn tính từ ngày tạo order trên WordPress, đơn sẽ tự động chuyển sang Hết hạn (Cancelled).</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Frontend URL (Next.js)</th>
                        <td>
                            <input type="url" name="frontend_url" value="<?php echo esc_attr($frontend_url); ?>" placeholder="https://thientam68.com" required />
                            <p class="description">Địa chỉ website frontend Next.js để gắn vào link checkout <code>{frontend_url}/order/{token}</code>.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Webhook API Key (SePay)</th>
                        <td>
                            <div class="ilynk-input-group">
                                <input type="password" id="sepay_webhook_key_input" name="webhook_key" value="<?php echo esc_attr($webhook_key); ?>" placeholder="Dán API Key tạo từ SePay Webhook Settings" />
                                <button type="button" class="button ilynk-sepay-btn-secondary" onclick="const input = document.getElementById('sepay_webhook_key_input'); input.type = input.type === 'password' ? 'text' : 'password'; this.querySelector('.btn-label').textContent = input.type === 'password' ? 'Hiện' : 'Ẩn';">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; flex-shrink: 0;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    <span class="btn-label">Hiện</span>
                                </button>
                            </div>
                            <p class="description">Chuỗi API Key xác thực Webhook được tạo trên SePay (Header <code>Authorization: Apikey &lt;KEY&gt;</code>).</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">URL Tiếp nhận Webhook</th>
                        <td>
                            <div class="ilynk-input-group">
                                <input type="text" readonly value="<?php echo esc_attr($webhook_url); ?>" id="sepay_webhook_url_box" style="background: #f8fafc; font-family: monospace;" />
                                <button type="button" class="button ilynk-sepay-btn-secondary" onclick="navigator.clipboard.writeText(document.getElementById('sepay_webhook_url_box').value); alert('Đã sao chép Webhook URL vào bộ nhớ tạm!');">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; flex-shrink: 0;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    <span>Sao chép</span>
                                </button>
                            </div>
                            <p class="description">Dán link này vào mục <strong>Webhook URL</strong> trên trang quản trị SePay (Cấu hình Webhook).</p>
                        </td>
                    </tr>
                </table>

                <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                    <button type="submit" class="button ilynk-sepay-btn-save">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; flex-shrink: 0;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Lưu Cấu Hình</span>
                    </button>
                    <span style="font-size: 13px; color: #64748b;">
                        Phát triển bởi <strong style="color: #0284c7;">iLynk Solution</strong>
                    </span>
                </div>
            </form>
        </div>

        <div style="margin-top: 20px; padding: 18px 22px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; font-size: 13px; color: #475569; line-height: 1.6;">
            <strong style="color: #1e293b; font-size: 14px;">💡 Hướng dẫn kiểm tra nhanh:</strong>
            <ul style="margin: 10px 0 0 18px; list-style-type: disc;">
                <li>Trên SePay Webhook Settings: Chọn kiểu chứng thực <code>API Key</code>, điền API Key trùng với khóa trên, và dán URL Webhook.</li>
                <li>Hệ thống tự động ghi nhận và phân tích tiền tố <code><?php echo esc_html($payment_prefix); ?></code> để gạch nợ tự động ngay tức thì.</li>
            </ul>
        </div>    </div>
<?php
}

/**
 * Kiểm tra và tự động chuyển trạng thái đơn hàng sang Hết hạn (Cancelled)
 * Tính theo thời gian tạo thực tế của post type sepay_order trong WordPress
 *
 * @param int $order_id
 * @param int|null $timeout_seconds Nếu null sẽ lấy theo cấu hình sepay_order_timeout (mặc định 15 phút)
 * @return string Trạng thái đơn hàng sau kiểm tra
 */
function custom_sepay_check_order_expiration(int $order_id, ?int $timeout_seconds = null): string
{
    $status = (string) (get_post_meta($order_id, 'payment_status', true) ?: 'pending');

    // Nếu đơn đã thanh toán hoặc thất bại thì không xử lý
    if ($status !== 'pending') {
        return $status;
    }

    // Nếu cổng thanh toán trên Website đã tắt -> tự động hủy đơn với lý do site tắt thanh toán
    if (get_option('sepay_enabled', '1') !== '1') {
        $status = 'cancelled';
        update_post_meta($order_id, 'payment_status', 'cancelled');
        update_post_meta($order_id, 'cancelled_reason', 'site_disabled');
        update_post_meta($order_id, 'cancelled_at', current_time('mysql'));
        do_action('custom_sepay_order_cancelled', $order_id, 'site_disabled');
        return $status;
    }

    if ($timeout_seconds === null) {
        $timeout_minutes = (int) get_option('sepay_order_timeout', 15);
        if ($timeout_minutes < 1) {
            $timeout_minutes = 15;
        }
        $timeout_seconds = $timeout_minutes * 60;
    }

    $timeout_seconds = (int) apply_filters('custom_sepay_order_timeout', $timeout_seconds, $order_id);

    // Lấy thời điểm tạo đơn theo Unix timestamp UTC của WordPress post
    $created_timestamp = get_post_time('U', true, $order_id);
    if (! $created_timestamp) {
        $meta_created = get_post_meta($order_id, 'created_at', true);
        if ($meta_created) {
            $created_timestamp = strtotime($meta_created);
        } else {
            $created_timestamp = time();
        }
    }

    $elapsed = time() - $created_timestamp;

    if ($elapsed >= $timeout_seconds) {
        $status = 'cancelled';
        update_post_meta($order_id, 'payment_status', 'cancelled');
        update_post_meta($order_id, 'cancelled_reason', 'timeout');
        update_post_meta($order_id, 'cancelled_at', current_time('mysql'));
        do_action('custom_sepay_order_cancelled', $order_id, 'timeout');
    }

    return $status;
}

/**
 * 3. Tùy chỉnh Cột hiển thị trong Danh sách Đơn hàng SePay
 */
add_filter('manage_sepay_order_posts_columns', 'custom_sepay_order_columns');
function custom_sepay_order_columns($columns)
{
    $new_cols = [];
    $new_cols['cb']             = $columns['cb'] ?? '<input type="checkbox" />';
    $new_cols['payment_code']   = 'Mã thanh toán';
    $new_cols['customer']       = 'Khách hàng';
    $new_cols['order_source']   = 'Nguồn tạo';
    $new_cols['service']        = 'Dịch vụ / Gói';
    $new_cols['amount']         = 'Số tiền';
    $new_cols['payment_status'] = 'Trạng thái';
    $new_cols['checkout_page']  = 'Trang Checkout';
    $new_cols['paid_at']        = 'Ngày thanh toán';
    $new_cols['date']           = 'Ngày tạo';
    return $new_cols;
}

add_action('manage_sepay_order_posts_custom_column', 'custom_sepay_order_column_content', 10, 2);
function custom_sepay_order_column_content($column, $post_id)
{
    switch ($column) {
        case 'payment_code':
            $code = get_post_meta($post_id, 'payment_code', true);
            echo '<strong>' . esc_html($code ?: 'TT' . $post_id) . '</strong>';
            break;

        case 'customer':
            $name  = get_post_meta($post_id, 'customer_name', true);
            $phone = get_post_meta($post_id, 'customer_phone', true);
            $email = get_post_meta($post_id, 'customer_email', true);

            echo '<div style="line-height:1.4;">';
            echo '<strong>' . esc_html($name ?: 'Chưa đặt tên') . '</strong><br>';
            if ($phone) {
                echo '<span style="font-size:12px;color:#64748b;">📞 ' . esc_html($phone) . '</span><br>';
            }
            if ($email) {
                echo '<span style="font-size:12px;color:#64748b;">✉️ ' . esc_html($email) . '</span>';
            }
            echo '</div>';
            break;

        case 'order_source':
            $source  = get_post_meta($post_id, 'order_source', true);
            $slug    = get_post_meta($post_id, 'landing_slug', true);
            $service = get_post_meta($post_id, 'service_name', true);

            if (empty($source)) {
                $lower = mb_strtolower((string)$service, 'UTF-8');
                if (strpos($lower, 'hiểu mình') !== false || strpos($lower, 'hieu-minh') !== false) {
                    $source = 'landing';
                    $slug   = $slug ?: 'hieu-minh';
                } elseif (strpos($lower, 'hiểu con') !== false || strpos($lower, 'hieu-con') !== false) {
                    $source = 'landing';
                    $slug   = $slug ?: 'hieu-con-de-dong-hanh';
                } else {
                    $source = 'service';
                }
            }

            if ($source === 'landing') {
                $name = ($slug === 'hieu-minh') ? 'Hiểu Mình' : (($slug === 'hieu-con-de-dong-hanh') ? 'Hiểu Con' : $slug);
                echo '<span style="display:inline-block;padding:3px 8px;background:#fdf2f8;color:#be185d;border:1px solid #fbcfe8;border-radius:6px;font-size:11px;font-weight:700;">🚀 Landing: ' . esc_html($name) . '</span>';
            } else {
                echo '<span style="display:inline-block;padding:3px 8px;background:#f0f9ff;color:#0369a1;border:1px solid #bae6fd;border-radius:6px;font-size:11px;font-weight:600;">🌐 Trang thường</span>';
            }
            break;

        case 'service':
            $service = get_post_meta($post_id, 'service_name', true);
            $package = get_post_meta($post_id, 'package_name', true);
            echo esc_html($service ?: 'Dịch vụ Phong Thủy');
            if ($package) {
                echo '<br><span class="badge" style="background:#f1f5f9;color:#475569;padding:2px 6px;border-radius:4px;font-size:11px;">' . esc_html($package) . '</span>';
            }
            break;

        case 'amount':
            $amount = (int) get_post_meta($post_id, 'amount', true);
            echo '<strong style="color:#0f766e;font-size:14px;">' . number_format($amount, 0, ',', '.') . 'đ</strong>';
            break;

        case 'payment_status':
            $status = get_post_meta($post_id, 'payment_status', true) ?: 'pending';
            if ($status === 'pending') {
                $status = custom_sepay_check_order_expiration($post_id);
            }
            if ($status === 'paid') {
                echo '<span style="color:#16a34a;font-weight:600;font-size:13px;display:inline-flex;align-items:center;gap:6px;"><span style="width:7px;height:7px;border-radius:50%;background:#16a34a;display:inline-block;"></span>Đã thanh toán</span>';
            } elseif ($status === 'failed') {
                echo '<span style="color:#dc2626;font-weight:600;font-size:13px;display:inline-flex;align-items:center;gap:6px;"><span style="width:7px;height:7px;border-radius:50%;background:#dc2626;display:inline-block;"></span>Thất bại</span>';
            } elseif ($status === 'cancelled') {
                $reason = get_post_meta($post_id, 'cancelled_reason', true);
                if ($reason === 'site_disabled') {
                    $label = 'Tắt thanh toán';
                } elseif ($reason === 'timeout') {
                    $label = 'Hết hạn';
                } else {
                    $label = 'Đã hủy';
                }
                echo '<span style="color:#dc2626;font-weight:600;font-size:13px;display:inline-flex;align-items:center;gap:6px;"><span style="width:7px;height:7px;border-radius:50%;background:#dc2626;display:inline-block;"></span>' . esc_html($label) . '</span>';
            } else {
                echo '<span style="color:#d97706;font-weight:600;font-size:13px;display:inline-flex;align-items:center;gap:6px;"><span style="width:7px;height:7px;border-radius:50%;background:#d97706;display:inline-block;"></span>Chờ thanh toán</span>';
            }
            break;

        case 'checkout_page':
            $token        = get_post_meta($post_id, '_sepay_public_token', true);
            $checkout_url = get_post_meta($post_id, 'checkout_url', true);
            $slug         = get_post_meta($post_id, 'landing_slug', true);
            $source       = get_post_meta($post_id, 'order_source', true);
            $service      = get_post_meta($post_id, 'service_name', true);

            if (empty($source)) {
                $lower = mb_strtolower((string)$service, 'UTF-8');
                if (strpos($lower, 'hiểu mình') !== false) {
                    $source = 'landing';
                    $slug   = $slug ?: 'hieu-minh';
                } elseif (strpos($lower, 'hiểu con') !== false) {
                    $source = 'landing';
                    $slug   = $slug ?: 'hieu-con-de-dong-hanh';
                }
            }

            if (empty($checkout_url) && ! empty($token)) {
                $frontend_base = custom_sepay_get_frontend_url();
                if ($source === 'landing' && !empty($slug)) {
                    $checkout_url = trailingslashit($frontend_base) . 'order/' . $token . '?from=landing&slug=' . $slug;
                } else {
                    $checkout_url = trailingslashit($frontend_base) . 'order/' . $token;
                }
            } elseif ($source === 'landing' && !empty($slug) && strpos($checkout_url, 'slug=') === false) {
                $checkout_url .= (strpos($checkout_url, '?') !== false ? '&' : '?') . 'from=landing&slug=' . $slug;
            }

            if (!metadata_exists('post', $post_id, 'order_source') || empty(get_post_meta($post_id, 'order_source', true))) {
                update_post_meta($post_id, 'order_source', $source);
                if (!empty($slug)) {
                    update_post_meta($post_id, 'landing_slug', $slug);
                }
                update_post_meta($post_id, 'checkout_url', $checkout_url);
            }
            if (! empty($checkout_url)) {
                echo '<a href="' . esc_url($checkout_url) . '" target="_blank" style="color:#2271b1;font-size:13px;font-weight:500;">Mở trang ↗</a>';
            } else {
                echo '<span style="color:#94a3b8;font-size:13px;">—</span>';
            }
            break;

        case 'paid_at':
            $paid = get_post_meta($post_id, 'paid_at', true);
            echo $paid ? '<span style="color:#15803d;font-size:12px;font-weight:500;">' . esc_html($paid) . '</span>' : '<span style="color:#94a3b8;">—</span>';
            break;
    }
}

/**
 * 4. Thêm Metabox Chi tiết Đơn hàng & Quản trị trong trang Sửa Đơn
 */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'custom_sepay_order_details_mb',
        'Chi tiết Thanh toán SePay VietQR (Phát triển bởi iLynk)',
        'custom_sepay_render_order_details_metabox',
        'sepay_order',
        'normal',
        'high'
    );
});

function custom_sepay_render_order_details_metabox($post)
{
    $post_id         = $post->ID;
    $payment_code    = get_post_meta($post_id, 'payment_code', true) ?: ('TT' . $post_id);
    $amount          = (int) get_post_meta($post_id, 'amount', true);
    $payment_status  = custom_sepay_check_order_expiration($post_id);
    $customer_name   = get_post_meta($post_id, 'customer_name', true);
    $customer_phone  = get_post_meta($post_id, 'customer_phone', true);
    $customer_email  = get_post_meta($post_id, 'customer_email', true);
    $service_name    = get_post_meta($post_id, 'service_name', true);
    $package_name    = get_post_meta($post_id, 'package_name', true);
    $customer_notes  = get_post_meta($post_id, 'customer_notes', true);
    $paid_at         = get_post_meta($post_id, 'paid_at', true);
    $public_token    = get_post_meta($post_id, '_sepay_public_token', true);
    $tx_id           = get_post_meta($post_id, 'sepay_transaction_id', true);
    $ref_code        = get_post_meta($post_id, 'sepay_reference_code', true);
    $webhook_history = get_post_meta($post_id, 'sepay_webhook_history', true);
    $checkout_url    = get_post_meta($post_id, 'checkout_url', true);
    $order_source    = get_post_meta($post_id, 'order_source', true);
    $landing_slug    = get_post_meta($post_id, 'landing_slug', true);

    if (empty($order_source)) {
        $lower = mb_strtolower((string)$service_name, 'UTF-8');
        if (strpos($lower, 'hiểu mình') !== false || strpos($lower, 'hieu-minh') !== false) {
            $order_source = 'landing';
            $landing_slug = $landing_slug ?: 'hieu-minh';
        } elseif (strpos($lower, 'hiểu con') !== false || strpos($lower, 'hieu-con') !== false) {
            $order_source = 'landing';
            $landing_slug = $landing_slug ?: 'hieu-con-de-dong-hanh';
        } else {
            $order_source = 'service';
        }
    }

    if (empty($checkout_url) && ! empty($public_token)) {
        $frontend_base = custom_sepay_get_frontend_url();
        if ($order_source === 'landing' && !empty($landing_slug)) {
            $checkout_url = trailingslashit($frontend_base) . 'order/' . $public_token . '?from=landing&slug=' . $landing_slug;
        } else {
            $checkout_url = trailingslashit($frontend_base) . 'order/' . $public_token;
        }
    } elseif ($order_source === 'landing' && !empty($landing_slug) && strpos($checkout_url, 'slug=') === false) {
        $checkout_url .= (strpos($checkout_url, '?') !== false ? '&' : '?') . 'from=landing&slug=' . $landing_slug;
    }

    if (!metadata_exists('post', $post_id, 'order_source') || empty(get_post_meta($post_id, 'order_source', true))) {
        update_post_meta($post_id, 'order_source', $order_source);
        if (!empty($landing_slug)) {
            update_post_meta($post_id, 'landing_slug', $landing_slug);
        }
        update_post_meta($post_id, 'checkout_url', $checkout_url);
    }

    $qr_url = custom_sepay_build_qr_url($amount, $payment_code);
    wp_nonce_field('custom_sepay_order_save', 'custom_sepay_order_nonce');
?>
    <div style="font-family: inherit;">
        <!-- Thanh Link Checkout riêng cho khách -->
        <?php if (! empty($checkout_url)): ?>
            <div style="margin-bottom: 20px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #86efac; border-radius: 8px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 20px;">🔗</span>
                    <div>
                        <strong style="color: #166534; font-size: 13px; display: block;">Link Trang Checkout Độc Lập Cho Khách Hàng:</strong>
                        <a href="<?php echo esc_url($checkout_url); ?>" target="_blank" style="color: #15803d; font-size: 13px; text-decoration: underline; word-break: break-all;" id="sepay_checkout_link_text">
                            <?php echo esc_html($checkout_url); ?>
                        </a>
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="button" onclick="navigator.clipboard.writeText(document.getElementById('sepay_checkout_link_text').href); alert('Đã sao chép Link Checkout vào bộ nhớ tạm!');" style="font-size: 12px;">
                        Sao chép Link
                    </button>
                    <a href="<?php echo esc_url($checkout_url); ?>" target="_blank" class="button button-primary" style="font-size: 12px; background: #15803d; border-color: #166534;">
                        Mở trang ↗
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 24px; flex-wrap: wrap;">
            <!-- Cột thông tin đơn -->
            <div style="flex: 1; min-width: 280px;">
                <table class="form-table" style="margin: 0;">
                    <tr>
                        <th style="width: 140px; padding: 10px 0;">Nguồn tạo đơn:</th>
                        <td style="padding: 10px 0;">
                            <?php if ($order_source === 'landing'): ?>
                                <span style="display:inline-block;padding:4px 10px;background:#fdf2f8;color:#be185d;border:1px solid #fbcfe8;border-radius:6px;font-size:12px;font-weight:700;">
                                    🚀 Landing Page (<?php echo esc_html($landing_slug === 'hieu-minh' ? 'Hiểu Mình Để Định Hướng' : ($landing_slug === 'hieu-con-de-dong-hanh' ? 'Hiểu Con Để Đồng Hành' : $landing_slug)); ?>)
                                </span>
                            <?php else: ?>
                                <span style="display:inline-block;padding:4px 10px;background:#f0f9ff;color:#0369a1;border:1px solid #bae6fd;border-radius:6px;font-size:12px;font-weight:600;">
                                    🌐 Trang dịch vụ thông thường
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="width: 140px; padding: 10px 0;">Mã thanh toán:</th>
                        <td style="padding: 10px 0;">
                            <input type="text" name="sepay_payment_code" value="<?php echo esc_attr($payment_code); ?>" class="regular-text" style="font-weight: bold; color: #b45309;" />
                            <p class="description">Mã ghi nhận trong nội dung chuyển khoản VietQR.</p>
                        </td>
                    </tr>
                    <tr>
                        <th style="padding: 10px 0;">Số tiền (VND):</th>
                        <td style="padding: 10px 0;">
                            <input type="number" name="sepay_amount" value="<?php echo esc_attr($amount); ?>" class="regular-text" style="font-weight: bold; color: #0f766e;" />
                            <span style="font-weight: 600; margin-left: 6px;"><?php echo number_format($amount, 0, ',', '.'); ?> đ</span>
                        </td>
                    </tr>
                    <tr>
                        <th style="padding: 10px 0;">Trạng thái đơn:</th>
                        <td style="padding: 10px 0;">
                            <select name="sepay_payment_status" style="font-weight: 600;">
                                <option value="pending" <?php selected($payment_status, 'pending'); ?>>⏳ Chờ thanh toán (Pending)</option>
                                <option value="paid" <?php selected($payment_status, 'paid'); ?>>✓ Đã thanh toán (Paid)</option>
                                <option value="failed" <?php selected($payment_status, 'failed'); ?>>✕ Thất bại (Failed)</option>
                                <option value="cancelled" <?php selected($payment_status, 'cancelled'); ?>>Đã hủy (Cancelled)</option>
                            </select>
                            <?php if ($paid_at): ?>
                                <span style="display: block; font-size: 12px; color: #15803d; margin-top: 4px;">Thời gian thanh toán: <?php echo esc_html($paid_at); ?></span>
                            <?php elseif ($payment_status === 'cancelled'): ?>
                                <?php
                                $c_reason = get_post_meta($post_id, 'cancelled_reason', true);
                                $c_at     = get_post_meta($post_id, 'cancelled_at', true);
                                $reason_text = ($c_reason === 'site_disabled')
                                    ? 'Đã hủy tự động do Website tắt cổng thanh toán VietQR'
                                    : (($c_reason === 'timeout') ? 'Đã hủy tự động do hết hạn 15 phút' : 'Đã hủy');
                                ?>
                                <span style="display: block; font-size: 12px; color: #dc2626; font-weight: 600; margin-top: 4px;">
                                    ⚠️ <?php echo esc_html($reason_text); ?> <?php echo $c_at ? '(' . esc_html($c_at) . ')' : ''; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="padding: 10px 0;">Khách hàng:</th>
                        <td style="padding: 10px 0;">
                            <input type="text" name="sepay_customer_name" value="<?php echo esc_attr($customer_name); ?>" class="regular-text" placeholder="Họ và tên" />
                        </td>
                    </tr>
                    <tr>
                        <th style="padding: 10px 0;">Số điện thoại:</th>
                        <td style="padding: 10px 0;">
                            <input type="text" name="sepay_customer_phone" value="<?php echo esc_attr($customer_phone); ?>" class="regular-text" placeholder="Số điện thoại" />
                        </td>
                    </tr>
                    <tr>
                        <th style="padding: 10px 0;">Email:</th>
                        <td style="padding: 10px 0;">
                            <input type="email" name="sepay_customer_email" value="<?php echo esc_attr($customer_email); ?>" class="regular-text" placeholder="Email" />
                        </td>
                    </tr>
                    <tr>
                        <th style="padding: 10px 0;">Dịch vụ & Gói:</th>
                        <td style="padding: 10px 0;">
                            <input type="text" name="sepay_service_name" value="<?php echo esc_attr($service_name); ?>" class="regular-text" placeholder="Tên dịch vụ" style="margin-bottom: 6px;" /><br>
                            <input type="text" name="sepay_package_name" value="<?php echo esc_attr($package_name); ?>" class="regular-text" placeholder="Gói tư vấn (nếu có)" />
                        </td>
                    </tr>
                    <tr>
                        <th style="padding: 10px 0;">Ghi chú từ khách:</th>
                        <td style="padding: 10px 0;">
                            <textarea name="sepay_customer_notes" rows="3" class="large-text"><?php echo esc_textarea($customer_notes); ?></textarea>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Cột mã QR VietQR Demo -->
            <div style="width: 250px; text-align: center; border-left: 1px solid #e2e8f0; padding-left: 20px;">
                <h4 style="margin: 0 0 10px 0; color: #334155;">Mã VietQR Thanh toán</h4>
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; display: inline-block;">
                    <img src="<?php echo esc_url($qr_url); ?>" alt="SePay VietQR" style="max-width: 100%; height: auto; display: block; border-radius: 4px;" />
                </div>
                <div style="font-size: 12px; color: #64748b; margin-top: 8px; text-align: left; line-height: 1.5;">
                    <div><strong>Ngân hàng:</strong> <?php echo esc_html(custom_sepay_get_setting('bank_name', 'MBBank')); ?></div>
                    <div><strong>Số TK:</strong> <?php echo esc_html(custom_sepay_get_setting('account_number', '')); ?></div>
                    <div><strong>Nội dung:</strong> <code style="color: #b45309;"><?php echo esc_html($payment_code); ?></code></div>
                </div>
            </div>
        </div>

        <!-- Khối Thông tin Giao dịch SePay Thực tế -->
        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
            <h4 style="margin: 0 0 12px 0; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <span class="dashicons dashicons-shield" style="color: #0284c7;"></span>
                Thông tin Đối soát Giao dịch SePay (Transaction)
            </h4>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; font-size: 13px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
                    <div>
                        <span style="color: #64748b; font-size: 12px; display: block;">Mã giao dịch SePay (ID):</span>
                        <strong style="color: #0f172a; font-family: monospace;"><?php echo esc_html($tx_id ?: '—'); ?></strong>
                    </div>
                    <div>
                        <span style="color: #64748b; font-size: 12px; display: block;">Mã tham chiếu ngân hàng:</span>
                        <strong style="color: #0f172a; font-family: monospace;"><?php echo esc_html($ref_code ?: '—'); ?></strong>
                    </div>
                    <div>
                        <span style="color: #64748b; font-size: 12px; display: block;">Thời gian khớp tiền:</span>
                        <strong style="color: #15803d;"><?php echo esc_html($paid_at ?: 'Chưa thanh toán'); ?></strong>
                    </div>
                </div>

                <?php if (! empty($webhook_history)): ?>
                    <details style="margin-top: 14px; padding-top: 10px; border-top: 1px dashed #cbd5e1;">
                        <summary style="cursor: pointer; color: #0284c7; font-weight: 600;">Xem Lịch sử Webhook Raw Data</summary>
                        <pre style="background: #1e293b; color: #f8fafc; padding: 12px; border-radius: 6px; overflow-x: auto; font-size: 12px; margin-top: 8px;"><?php echo esc_html(wp_json_encode($webhook_history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                    </details>
                <?php endif; ?>
            </div>
            <div style="margin-top: 10px; font-size: 12px; color: #94a3b8; text-align: right;">
                Hệ thống đối soát thanh toán tự động SePay VietQR • <strong>Phát triển bởi iLynk</strong>
            </div>
        </div>
    </div>
<?php
}

/**
 * 5. Lưu dữ liệu metabox khi Admin bấm Update
 */
add_action('save_post_sepay_order', function ($post_id) {
    if (! isset($_POST['custom_sepay_order_nonce']) || ! wp_verify_nonce($_POST['custom_sepay_order_nonce'], 'custom_sepay_order_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['sepay_payment_code'])) {
        update_post_meta($post_id, 'payment_code', sanitize_text_field($_POST['sepay_payment_code']));
    }
    if (isset($_POST['sepay_amount'])) {
        update_post_meta($post_id, 'amount', (int) $_POST['sepay_amount']);
    }
    if (isset($_POST['sepay_payment_status'])) {
        $status = sanitize_text_field($_POST['sepay_payment_status']);
        update_post_meta($post_id, 'payment_status', $status);
        if ($status === 'paid' && ! get_post_meta($post_id, 'paid_at', true)) {
            update_post_meta($post_id, 'paid_at', current_time('mysql'));
        }
    }
    if (isset($_POST['sepay_customer_name'])) {
        update_post_meta($post_id, 'customer_name', sanitize_text_field($_POST['sepay_customer_name']));
    }
    if (isset($_POST['sepay_customer_phone'])) {
        update_post_meta($post_id, 'customer_phone', sanitize_text_field($_POST['sepay_customer_phone']));
    }
    if (isset($_POST['sepay_customer_email'])) {
        update_post_meta($post_id, 'customer_email', sanitize_email($_POST['sepay_customer_email']));
    }
    if (isset($_POST['sepay_service_name'])) {
        update_post_meta($post_id, 'service_name', sanitize_text_field($_POST['sepay_service_name']));
    }
    if (isset($_POST['sepay_package_name'])) {
        update_post_meta($post_id, 'package_name', sanitize_text_field($_POST['sepay_package_name']));
    }
    if (isset($_POST['sepay_customer_notes'])) {
        update_post_meta($post_id, 'customer_notes', sanitize_textarea_field($_POST['sepay_customer_notes']));
    }
});

/**
 * 6. Helper: Tạo URL ảnh QR SePay
 */
function custom_sepay_build_qr_url(int $amount, string $payment_code): string
{
    $bank_name      = custom_sepay_get_setting('bank_name', 'MBBank');
    $account_number = custom_sepay_get_setting('account_number', '');
    $account_holder = custom_sepay_get_setting('account_holder', 'PHONG THUY THIEN TAM');

    $des = $payment_code;
    if (stripos($bank_name, 'Vietin') !== false) {
        if (strpos($payment_code, 'SEVQR') !== 0) {
            $des = 'SEVQR ' . $payment_code;
        }
    }

    $query = [
        'acc'      => $account_number,
        'bank'     => $bank_name,
        'amount'   => $amount,
        'des'      => $des,
        'template' => 'compact',
    ];

    if (! empty($account_holder)) {
        $query['holder'] = $account_holder;
    }

    return 'https://qr.sepay.vn/img?' . http_build_query($query);
}

/**
 * 7. Helper: Sinh chuỗi bảo mật ngẫu nhiên (Public Token)
 */
function custom_sepay_generate_token(int $length = 32): string
{
    if (function_exists('random_bytes')) {
        try {
            return bin2hex(random_bytes($length));
        } catch (Exception $e) {
            // fallback
        }
    }
    if (function_exists('openssl_random_pseudo_bytes')) {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
    return wp_generate_password($length * 2, false, false);
}

/**
 * 8. Đăng ký REST API Routes cho SePay
 */
add_action('rest_api_init', 'custom_sepay_register_rest_routes');
function custom_sepay_register_rest_routes()
{
    // 8.1. API Tạo đơn thanh toán: POST /wp-json/custom-sepay/v1/orders
    register_rest_route('custom-sepay/v1', '/orders', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'custom_sepay_create_order',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('thientam/v1', '/sepay/orders', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'custom_sepay_create_order',
        'permission_callback' => '__return_true',
    ]);

    // 8.2. API Kiểm tra trạng thái đơn: GET /wp-json/custom-sepay/v1/orders/{token}/status
    register_rest_route('custom-sepay/v1', '/orders/(?P<token>[a-f0-9]{64})/status', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'custom_sepay_get_order_status',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('thientam/v1', '/sepay/orders/(?P<token>[a-f0-9]{64})/status', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'custom_sepay_get_order_status',
        'permission_callback' => '__return_true',
    ]);

    // 8.3. Webhook tiếp nhận từ SePay: POST /wp-json/custom-sepay/v1/webhook (hỗ trợ GET để kiểm tra trạng thái)
    register_rest_route('custom-sepay/v1', '/webhook', [
        'methods'             => ['POST', 'GET'],
        'callback'            => 'custom_sepay_webhook',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('sepay/v1', '/webhook', [
        'methods'             => ['POST', 'GET'],
        'callback'            => 'custom_sepay_webhook',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('thientam/v1', '/sepay/webhook', [
        'methods'             => ['POST', 'GET'],
        'callback'            => 'custom_sepay_webhook',
        'permission_callback' => '__return_true',
    ]);

    // 8.4. Cấu hình public SePay: GET /wp-json/custom-sepay/v1/config
    register_rest_route('custom-sepay/v1', '/config', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'custom_sepay_get_public_config',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('thientam/v1', '/sepay/config', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'custom_sepay_get_public_config',
        'permission_callback' => '__return_true',
    ]);

    // 8.5. Debug helper: GET /wp-json/custom-sepay/v1/debug
    register_rest_route('custom-sepay/v1', '/debug', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'custom_sepay_debug_status',
        'permission_callback' => '__return_true',
    ]);
}

/**
 * 8.4. Callback: Lấy cấu hình public
 */
function custom_sepay_get_public_config()
{
    $is_enabled     = (get_option('sepay_enabled', '1') === '1');
    $bank_name      = custom_sepay_get_setting('bank_name', 'MBBank');
    $account_number = custom_sepay_get_setting('account_number', '');
    $account_holder = custom_sepay_get_setting('account_holder', 'PHONG THUY THIEN TAM');
    $payment_prefix = custom_sepay_get_setting('payment_prefix', 'TT');

    return new WP_REST_Response([
        'success'        => true,
        'enabled'        => $is_enabled,
        'bank_name'      => $bank_name,
        'account_number' => $account_number,
        'account_holder' => $account_holder,
        'payment_prefix' => $payment_prefix,
        'developer'      => 'iLynk Solution',
    ], 200);
}

/**
 * 8.5. Callback: Debug thông tin đơn hàng và webhook logs
 */
function custom_sepay_debug_status(WP_REST_Request $request)
{
    $webhook_key   = defined('SEPAY_WEBHOOK_KEY') && SEPAY_WEBHOOK_KEY !== '' ? SEPAY_WEBHOOK_KEY : get_option('sepay_webhook_key', '');
    $authorization = trim((string) $request->get_header('authorization'));
    $is_authorized = current_user_can('manage_options') ||
        (! empty($webhook_key) && ($authorization === 'Apikey ' . $webhook_key || $authorization === $webhook_key));

    if (! $is_authorized) {
        return new WP_Error('forbidden', 'Không có quyền truy cập thông tin chẩn đoán.', ['status' => 403]);
    }
    $orders = get_posts([
        'post_type'      => 'sepay_order',
        'post_status'    => 'any',
        'posts_per_page' => 10,
        'orderby'        => 'ID',
        'order'          => 'DESC',
    ]);
    $order_list = [];
    foreach ($orders as $o) {
        $order_list[] = [
            'id'             => $o->ID,
            'title'          => $o->post_title,
            'payment_code'   => get_post_meta($o->ID, 'payment_code', true),
            'status'         => get_post_meta($o->ID, 'payment_status', true),
            'amount'         => (int) get_post_meta($o->ID, 'amount', true),
            'paid_at'        => get_post_meta($o->ID, 'paid_at', true),
            'created_at'     => get_post_meta($o->ID, 'created_at', true),
            'webhook_error'  => get_post_meta($o->ID, 'sepay_webhook_last_error', true),
            'history'        => get_post_meta($o->ID, 'sepay_webhook_history', true),
        ];
    }
    return new WP_REST_Response([
        'settings'        => [
            'is_sandbox'     => get_option('sepay_is_sandbox', '1'),
            'account_number' => custom_sepay_get_setting('account_number', ''),
            'bank_name'      => custom_sepay_get_setting('bank_name', 'MBBank'),
            'payment_prefix' => custom_sepay_get_setting('payment_prefix', 'TT'),
            'is_enabled'     => get_option('sepay_enabled', '1'),
            'webhook_key'    => substr(get_option('sepay_webhook_key', ''), 0, 4) . '***',
        ],
        'recent_orders'   => $order_list,
        'recent_webhooks' => get_option('custom_sepay_recent_webhooks', []),
    ], 200);
}

/**
 * 8.1. Callback: Tạo đơn hàng SePay
 */
function custom_sepay_create_order(WP_REST_Request $request)
{
    if (get_option('sepay_enabled', '1') !== '1') {
        return new WP_Error('payment_disabled', 'Cổng thanh toán VietQR hiện đang tạm tắt. Quý khách vui lòng chọn hình thức Tư vấn trước.', ['status' => 403]);
    }

    $params = $request->get_json_params();
    if (empty($params) || ! is_array($params)) {
        $params = $request->get_params();
    }

    $customer_name   = sanitize_text_field((string) ($params['customer_name'] ?? ''));
    $customer_phone  = sanitize_text_field((string) ($params['customer_phone'] ?? ''));
    $customer_email  = sanitize_email((string) ($params['customer_email'] ?? ''));
    $service_name    = sanitize_text_field((string) ($params['service_name'] ?? ''));
    $package_name    = sanitize_text_field((string) ($params['package_name'] ?? ''));
    $customer_notes  = sanitize_textarea_field((string) ($params['customer_notes'] ?? ''));
    $raw_amount      = $params['amount'] ?? 0;
    $amount          = (int) preg_replace('/[^0-9]/', '', (string) $raw_amount);

    if ($amount <= 0) {
        return new WP_Error('invalid_amount', 'Số tiền thanh toán phải lớn hơn 0đ.', ['status' => 400]);
    }

    if (empty($customer_phone)) {
        return new WP_Error('missing_phone', 'Vui lòng cung cấp số điện thoại liên hệ.', ['status' => 400]);
    }

    $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', custom_sepay_get_setting('payment_prefix', 'TT')));
    $prefix = $prefix !== '' ? $prefix : 'TT';

    $order_source = sanitize_text_field((string) ($params['order_source'] ?? ''));
    $landing_slug = sanitize_text_field((string) ($params['landing_slug'] ?? ''));

    if (empty($order_source)) {
        $lower_svc = mb_strtolower($service_name, 'UTF-8');
        if (strpos($lower_svc, 'hiểu mình') !== false || strpos($lower_svc, 'hieu-minh') !== false) {
            $order_source = 'landing';
            $landing_slug = 'hieu-minh';
        } elseif (strpos($lower_svc, 'hiểu con') !== false || strpos($lower_svc, 'hieu-con') !== false) {
            $order_source = 'landing';
            $landing_slug = 'hieu-con-de-dong-hanh';
        } else {
            $order_source = 'service';
        }
    }

    // Tạo đơn nháp trước để nhận Post ID
    $post_id = wp_insert_post([
        'post_type'   => 'sepay_order',
        'post_status' => 'publish',
        'post_title'  => 'Đơn hàng đang khởi tạo...',
    ], true);

    if (is_wp_error($post_id)) {
        return new WP_Error('order_creation_failed', 'Không thể khởi tạo đơn hàng: ' . $post_id->get_error_message(), ['status' => 500]);
    }

    $payment_code = $prefix . $post_id;
    $public_token = custom_sepay_generate_token(32);

    $title_parts = array_filter([$customer_name, $service_name, $package_name]);
    $order_title = '#' . $payment_code . ' - ' . ($title_parts ? implode(' - ', $title_parts) : 'Đơn dịch vụ');

    wp_update_post([
        'ID'         => $post_id,
        'post_title' => $order_title,
    ]);

    // Tạo Checkout URL độc lập cho Next.js Frontend
    $frontend_base = custom_sepay_get_frontend_url();
    if ($order_source === 'landing' && !empty($landing_slug)) {
        $checkout_url = trailingslashit($frontend_base) . 'order/' . $public_token . '?from=landing&slug=' . $landing_slug;
    } else {
        $checkout_url = trailingslashit($frontend_base) . 'order/' . $public_token;
    }

    // Lưu trữ metadata
    update_post_meta($post_id, 'payment_code', $payment_code);
    update_post_meta($post_id, 'amount', $amount);
    update_post_meta($post_id, 'payment_status', 'pending');
    update_post_meta($post_id, 'customer_name', $customer_name);
    update_post_meta($post_id, 'customer_phone', $customer_phone);
    update_post_meta($post_id, 'customer_email', $customer_email);
    update_post_meta($post_id, 'service_name', $service_name);
    update_post_meta($post_id, 'package_name', $package_name);
    update_post_meta($post_id, 'customer_notes', $customer_notes);
    update_post_meta($post_id, '_sepay_public_token', $public_token);
    update_post_meta($post_id, 'order_source', $order_source);
    update_post_meta($post_id, 'landing_slug', $landing_slug);
    update_post_meta($post_id, 'checkout_url', $checkout_url);
    update_post_meta($post_id, 'created_at', current_time('mysql'));

    $qr_url       = custom_sepay_build_qr_url($amount, $payment_code);
    $status_url   = rest_url('custom-sepay/v1/orders/' . $public_token . '/status');
    $bank_name    = custom_sepay_get_setting('bank_name', 'MBBank');
    $payment_memo = (stripos($bank_name, 'Vietin') !== false) ? ('SEVQR ' . $payment_code) : $payment_code;

    return new WP_REST_Response([
        'success'          => true,
        'order_id'         => $post_id,
        'payment_code'     => $payment_code,
        'payment_memo'     => $payment_memo,
        'amount'           => $amount,
        'amount_formatted' => number_format($amount, 0, ',', '.') . 'đ',
        'qr_url'           => $qr_url,
        'checkout_url'     => $checkout_url,
        'status'           => 'pending',
        'token'            => $public_token,
        'status_url'       => $status_url,
        'timeout_seconds'   => (int) apply_filters('custom_sepay_order_timeout', ((int) get_option('sepay_order_timeout', 15)) * 60, $post_id),
        'remaining_seconds' => (int) apply_filters('custom_sepay_order_timeout', ((int) get_option('sepay_order_timeout', 15)) * 60, $post_id),
        'expires_at'        => date('Y-m-d H:i:s', time() + (int) apply_filters('custom_sepay_order_timeout', ((int) get_option('sepay_order_timeout', 15)) * 60, $post_id)),
        'bank_info'        => [
            'bank_name'      => custom_sepay_get_setting('bank_name', 'MBBank'),
            'account_number' => custom_sepay_get_setting('account_number', ''),
            'account_holder' => custom_sepay_get_setting('account_holder', 'PHONG THUY THIEN TAM'),
        ],
        'developer'        => 'iLynk Solution',
    ], 201);
}

/**
 * 8.2. Callback: Tra cứu trạng thái đơn hàng từ Frontend (Hỗ trợ Trang Checkout / Order riêng)
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

    if (! $orders) {
        return new WP_Error('order_not_found', 'Không tìm thấy đơn hàng tương ứng.', ['status' => 404]);
    }

    $order_id = (int) $orders[0];
    $status   = custom_sepay_check_order_expiration($order_id);
    $code     = get_post_meta($order_id, 'payment_code', true);
    $paid_at  = get_post_meta($order_id, 'paid_at', true);
    $amount   = (int) get_post_meta($order_id, 'amount', true);

    $customer_name   = (string) get_post_meta($order_id, 'customer_name', true);
    $customer_phone  = (string) get_post_meta($order_id, 'customer_phone', true);
    $customer_email  = (string) get_post_meta($order_id, 'customer_email', true);
    $service_name    = (string) get_post_meta($order_id, 'service_name', true);
    $package_name    = (string) get_post_meta($order_id, 'package_name', true);
    $customer_notes  = (string) get_post_meta($order_id, 'customer_notes', true);
    $tx_id           = (string) get_post_meta($order_id, 'sepay_transaction_id', true);
    $ref_code        = (string) get_post_meta($order_id, 'sepay_reference_code', true);
    $created_at      = (string) get_post_meta($order_id, 'created_at', true) ?: get_the_date('Y-m-d H:i:s', $order_id);
    $checkout_url    = (string) get_post_meta($order_id, 'checkout_url', true);
    $order_source    = (string) get_post_meta($order_id, 'order_source', true);
    $landing_slug    = (string) get_post_meta($order_id, 'landing_slug', true);

    if (empty($order_source)) {
        $lower_svc = mb_strtolower($service_name, 'UTF-8');
        if (strpos($lower_svc, 'hiểu mình') !== false || strpos($lower_svc, 'hieu-minh') !== false) {
            $order_source = 'landing';
            $landing_slug = $landing_slug ?: 'hieu-minh';
        } elseif (strpos($lower_svc, 'hiểu con') !== false || strpos($lower_svc, 'hieu-con') !== false) {
            $order_source = 'landing';
            $landing_slug = $landing_slug ?: 'hieu-con-de-dong-hanh';
        } else {
            $order_source = 'service';
        }
    }

    if (empty($checkout_url)) {
        $frontend_base = custom_sepay_get_frontend_url();
        if ($order_source === 'landing' && !empty($landing_slug)) {
            $checkout_url = trailingslashit($frontend_base) . 'order/' . $token . '?from=landing&slug=' . $landing_slug;
        } else {
            $checkout_url = trailingslashit($frontend_base) . 'order/' . $token;
        }
    } elseif ($order_source === 'landing' && !empty($landing_slug) && strpos($checkout_url, 'slug=') === false) {
        $checkout_url = trailingslashit(custom_sepay_get_frontend_url()) . 'order/' . $token . '?from=landing&slug=' . $landing_slug;
    }

    if (!metadata_exists('post', $order_id, 'order_source') || empty(get_post_meta($order_id, 'order_source', true))) {
        update_post_meta($order_id, 'order_source', $order_source);
        if (!empty($landing_slug)) {
            update_post_meta($order_id, 'landing_slug', $landing_slug);
        }
        update_post_meta($order_id, 'checkout_url', $checkout_url);
    }

    $timeout_minutes   = (int) get_option('sepay_order_timeout', 15);
    $timeout_seconds   = (int) apply_filters('custom_sepay_order_timeout', $timeout_minutes * 60, $order_id);
    $created_timestamp = get_post_time('U', true, $order_id) ?: strtotime($created_at);
    $elapsed           = time() - $created_timestamp;
    $remaining_seconds = max(0, $timeout_seconds - $elapsed);
    $expires_at        = date('Y-m-d H:i:s', $created_timestamp + $timeout_seconds);

    // Header chống cache để phản hồi trạng thái mới nhất ngay lập tức
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    $bank_name    = custom_sepay_get_setting('bank_name', 'MBBank');
    $payment_memo = (stripos($bank_name, 'Vietin') !== false) ? ('SEVQR ' . $code) : (string) $code;

    return new WP_REST_Response([
        'success'          => true,
        'order_id'         => $order_id,
        'payment_code'     => (string) $code,
        'payment_memo'     => $payment_memo,
        'status'           => (string) $status,
        'cancelled_reason' => (string) get_post_meta($order_id, 'cancelled_reason', true),
        'cancelled_at'     => (string) get_post_meta($order_id, 'cancelled_at', true),
        'paid_at'          => (string) $paid_at,
        'amount'           => $amount,
        'amount_formatted' => number_format($amount, 0, ',', '.') . 'đ',
        'customer_name'    => $customer_name,
        'customer_phone'   => $customer_phone,
        'customer_email'   => $customer_email,
        'service_name'     => $service_name,
        'package_name'     => $package_name,
        'customer_notes'   => $customer_notes,
        'order_source'     => (string) $order_source,
        'landing_slug'     => (string) $landing_slug,
        'qr_url'           => custom_sepay_build_qr_url($amount, (string) $code),
        'checkout_url'     => $checkout_url,
        'timeout_seconds'   => $timeout_seconds,
        'remaining_seconds' => $remaining_seconds,
        'expires_at'        => $expires_at,
        'bank_info'        => [
            'bank_name'      => custom_sepay_get_setting('bank_name', 'MBBank'),
            'account_number' => custom_sepay_get_setting('account_number', ''),
            'account_holder' => custom_sepay_get_setting('account_holder', 'PHONG THUY THIEN TAM'),
        ],
        'transaction'      => [
            'id'             => $tx_id,
            'reference_code' => $ref_code,
            'paid_at'        => $paid_at,
            'amount'         => $amount,
            'status'         => $status,
        ],
        'created_at'       => $created_at,
        'developer'        => 'iLynk Solution',
    ], 200);
}

/**
 * 8.3. Callback: Trích xuất mã thanh toán từ nội dung chuyển khoản
 */
function custom_sepay_extract_payment_code(array $payload): string
{
    $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', custom_sepay_get_setting('payment_prefix', 'TT')));
    $prefix = $prefix !== '' ? $prefix : 'TT';

    $sources = [
        (string) ($payload['code'] ?? ''),
        (string) ($payload['content'] ?? ''),
        (string) ($payload['description'] ?? ''),
    ];

    // 1. Tìm kiếm theo mẫu prefix + số (chấp nhận có hoặc không dấu cách)
    $pattern = '/' . preg_quote($prefix, '/') . '\s*(\d+)/i';
    foreach ($sources as $text) {
        if (!empty($text) && preg_match($pattern, $text, $matches)) {
            return $prefix . $matches[1];
        }
    }

    // 2. Tìm kiếm theo tiền tố TT mặc định nếu prefix cấu hình khác TT
    if ($prefix !== 'TT') {
        foreach ($sources as $text) {
            if (!empty($text) && preg_match('/TT\s*(\d+)/i', $text, $matches)) {
                return 'TT' . $matches[1];
            }
        }
    }

    // 3. Nếu trường code từ SePay có giá trị bất kỳ (không phải test SEVN)
    $code = strtoupper(sanitize_text_field((string) ($payload['code'] ?? '')));
    if ($code !== '' && strpos($code, 'SEVN') !== 0) {
        return $code;
    }

    return '';
}

/**
 * 8.3. Callback: Xử lý Webhook SePay (Đối soát & Cập nhật thanh toán)
 */
function custom_sepay_webhook(WP_REST_Request $request)
{
    // Kiểm tra nhanh qua phương thức GET (trình duyệt hoặc health check)
    if ($request->get_method() === 'GET') {
        $webhook_key = defined('SEPAY_WEBHOOK_KEY') && SEPAY_WEBHOOK_KEY !== '' ? SEPAY_WEBHOOK_KEY : get_option('sepay_webhook_key', '');
        return new WP_REST_Response([
            'success'     => true,
            'status'      => 'active',
            'message'     => 'Cổng Webhook SePay đang hoạt động bình thường và sẵn sàng nhận thông báo giao dịch.',
            'configured'  => ! empty($webhook_key),
            'method'      => 'POST',
            'auth_format' => 'Authorization: Apikey <SEPAY_WEBHOOK_KEY>',
            'server_time' => current_time('mysql'),
            'developer'   => 'iLynk Solution',
        ], 200);
    }

    $webhook_key = defined('SEPAY_WEBHOOK_KEY') && SEPAY_WEBHOOK_KEY !== '' ? SEPAY_WEBHOOK_KEY : get_option('sepay_webhook_key', '');

    if (empty($webhook_key)) {
        return new WP_Error('missing_webhook_key', 'Khóa Webhook SePay chưa được thiết lập trên máy chủ.', ['status' => 500]);
    }

    // 1. Xác thực API Key từ Header Authorization
    $authorization = trim((string) $request->get_header('authorization'));
    $expected_1    = 'Apikey ' . $webhook_key;
    $expected_2    = $webhook_key;

    $is_authorized = false;
    if ($authorization !== '') {
        if (function_exists('hash_equals')) {
            $is_authorized = hash_equals($expected_1, $authorization) || hash_equals($expected_2, $authorization);
        } else {
            $is_authorized = ($authorization === $expected_1 || $authorization === $expected_2);
        }
    }

    if (! $is_authorized) {
        return new WP_Error('unauthorized', 'Xác thực Webhook không hợp lệ.', ['status' => 401]);
    }

    $payload = $request->get_json_params();
    if (empty($payload) || ! is_array($payload)) {
        $payload = $request->get_params();
    }

    // Ghi log toàn bộ webhook nhận được
    $recent_logs = (array) get_option('custom_sepay_recent_webhooks', []);
    $new_log = [
        'time'          => current_time('mysql'),
        'method'        => $request->get_method(),
        'auth_header'   => !empty($request->get_header('authorization')),
        'payload'       => $payload,
    ];
    array_unshift($recent_logs, $new_log);
    $recent_logs = array_slice($recent_logs, 0, 20);
    update_option('custom_sepay_recent_webhooks', $recent_logs, false);

    // Hỗ trợ kiểm tra kiểm thử từ SePay Dashboard ("Gửi thử" / "Test Webhook")
    $content_desc = (string) ($payload['content'] ?? ($payload['description'] ?? ''));
    $payload_code = strtoupper(trim((string) ($payload['code'] ?? '')));
    $ref_code     = trim((string) ($payload['referenceCode'] ?? ''));

    $is_test_ping = (
        stripos($content_desc, 'thu nghiem') !== false ||
        stripos($content_desc, 'Giao dich thu nghiem') !== false ||
        strpos($payload_code, 'SEVN') === 0 ||
        strpos(strtoupper($content_desc), 'SEVN') !== false ||
        $ref_code === 'FT24012345678'
    );

    if ($is_test_ping) {
        return new WP_REST_Response([
            'success'   => true,
            'message'   => 'SePay Dashboard Test Webhook Ping Received Successfully (Phát triển bởi iLynk).',
            'developer' => 'iLynk Solution',
        ], 200);
    }

    $payment_code = custom_sepay_extract_payment_code($payload);
    if (empty($payment_code)) {
        return new WP_REST_Response([
            'success'   => false,
            'message'   => 'Bỏ qua giao dịch: Không tìm thấy mã thanh toán hợp lệ trong nội dung chuyển khoản.',
            'developer' => 'iLynk Solution',
        ], 200);
    }

    // 2. Tìm đơn hàng theo mã payment_code
    $orders = get_posts([
        'post_type'      => 'sepay_order',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => 'payment_code',
        'meta_value'     => $payment_code,
    ]);

    if (! $orders) {
        return new WP_REST_Response([
            'success'   => false,
            'message'   => 'Không tìm thấy đơn hàng với mã thanh toán: ' . $payment_code,
            'developer' => 'iLynk Solution',
        ], 200);
    }

    $order_id       = (int) $orders[0];
    $current_status = get_post_meta($order_id, 'payment_status', true);
    $order_amount   = (int) get_post_meta($order_id, 'amount', true);

    // Bỏ qua nếu đơn đã thanh toán trước đó
    if ($current_status === 'paid') {
        return new WP_REST_Response([
            'success'   => true,
            'message'   => 'Đơn hàng ' . $payment_code . ' đã được thanh toán từ trước.',
            'developer' => 'iLynk Solution',
        ], 200);
    }

    // 3. Đối soát số tiền nhận được
    $transfer_type   = sanitize_text_field((string) ($payload['transferType'] ?? 'in'));
    $transfer_amount = (int) ($payload['transferAmount'] ?? 0);

    if ($transfer_type !== 'in') {
        return new WP_REST_Response(['success' => false, 'message' => 'Giao dịch không phải dòng tiền vào (in).'], 200);
    }

    if ($transfer_amount < $order_amount) {
        update_post_meta($order_id, 'sepay_partial_amount', $transfer_amount);
        update_post_meta($order_id, 'sepay_webhook_last_error', 'Số tiền chuyển (' . $transfer_amount . 'đ) nhỏ hơn số tiền yêu cầu (' . $order_amount . 'đ)');
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Số tiền chuyển không đủ so với giá trị đơn hàng.',
        ], 200);
    }

    // 4. Đối soát số tài khoản thụ hưởng
    $is_sandbox          = get_option('sepay_is_sandbox', '1') === '1';
    $configured_account  = preg_replace('/\s+/', '', custom_sepay_get_setting('account_number', ''));
    $payload_account     = preg_replace('/\s+/', '', sanitize_text_field((string) ($payload['accountNumber'] ?? '')));

    if (! $is_sandbox && ! empty($configured_account) && ! empty($payload_account)) {
        if ($configured_account !== $payload_account) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Số tài khoản nhận tiền không trùng khớp với cấu hình hệ thống.',
            ], 200);
        }
    }

    // 5. Cập nhật trạng thái ĐÃ THANH TOÁN (Paid)
    $sepay_tx_id = sanitize_text_field((string) ($payload['id'] ?? ''));
    $ref_code    = sanitize_text_field((string) ($payload['referenceCode'] ?? ''));
    $paid_time   = sanitize_text_field((string) ($payload['transactionDate'] ?? current_time('mysql')));

    update_post_meta($order_id, 'payment_status', 'paid');
    update_post_meta($order_id, 'paid_at', $paid_time);
    update_post_meta($order_id, 'sepay_transaction_id', $sepay_tx_id);
    update_post_meta($order_id, 'sepay_reference_code', $ref_code);
    update_post_meta($order_id, 'sepay_webhook_history', $payload);

    // Kích hoạt action hook để các dịch vụ khác (Email, CRM, Zalo ZNS) có thể bắt sự kiện
    do_action('custom_sepay_order_paid', $order_id, $payment_code, $payload);
    do_action('ilynk_sepay_order_paid', $order_id, $payment_code, $payload);

    return new WP_REST_Response([
        'success'      => true,
        'message'      => 'Đơn hàng ' . $payment_code . ' đã được xác nhận thanh toán thành công.',
        'order_id'     => $order_id,
        'payment_code' => $payment_code,
        'developer'    => 'iLynk Solution',
    ], 200);
}
