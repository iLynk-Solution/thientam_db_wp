<?php

/**
 * Native SMTP Mailer & Email Settings Module (Không cần Plugin ngoài)
 *
 * - Tùy chỉnh máy chủ SMTP (Gmail, Zoho, SendGrid, Mailgun, SMTP Hosting...)
 * - Tự động hook vào PHPMailer của WordPress qua action `phpmailer_init`
 * - Gửi email thông báo HTML đẹp mắt, sang trọng chuẩn thương hiệu Thiên Tâm 68
 * - Các template HTML được tách riêng trong thư mục `templates/emails/`
 * - Gửi email xác nhận / cảm ơn tự động cho khách hàng khi điền form
 * - Công cụ gửi thử Email Test trực tiếp từ trang quản trị với giao diện cao cấp
 *
 * @package HelloElementorChild
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * 1. Thêm trang Cài đặt SMTP vào Menu Admin
 */
add_action('admin_menu', 'thientam_add_smtp_settings_submenu');
function thientam_add_smtp_settings_submenu()
{
    // Thêm vào dưới menu "Yêu cầu liên hệ"
    add_submenu_page(
        'edit.php?post_type=form_submission',
        'Cài đặt SMTP Email',
        'Cài đặt SMTP Email',
        'manage_options',
        'thientam-smtp-settings',
        'thientam_render_smtp_settings_page'
    );

    // Đồng thời thêm vào menu "Cài đặt" (Settings) của WordPress
    add_options_page(
        'Cấu hình SMTP Email',
        'SMTP Email',
        'manage_options',
        'thientam-smtp-settings-options',
        'thientam_render_smtp_settings_page'
    );
}

// Biến lưu log lỗi PHPMailer gần nhất
global $thientam_last_mail_error;
$thientam_last_mail_error = '';

add_action('wp_mail_failed', function ($wp_error) {
    global $thientam_last_mail_error;
    if (is_wp_error($wp_error)) {
        $thientam_last_mail_error = $wp_error->get_error_message();
    }
});

/**
 * 2. Hook cấu hình PHPMailer của WordPress
 *
 * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer
 * @return void
 */
add_action('phpmailer_init', 'thientam_configure_phpmailer');
function thientam_configure_phpmailer($phpmailer)
{
    $enabled = get_option('thientam_smtp_enabled', '0') === '1';
    if (!$enabled) {
        return;
    }

    $host       = trim((string) get_option('thientam_smtp_host', ''));
    $port       = (int) get_option('thientam_smtp_port', 465);
    $encryption = trim((string) get_option('thientam_smtp_encryption', 'ssl'));
    $username   = trim((string) get_option('thientam_smtp_username', ''));
    // Tự động xóa khoảng cách nếu người dùng copy từ Google App Password (dạng: xxxx xxxx xxxx xxxx)
    $password   = trim(str_replace(' ', '', (string) get_option('thientam_smtp_password', '')));
    $from_email = trim((string) get_option('thientam_smtp_from_email', ''));
    $from_name  = get_option('thientam_smtp_from_name', get_bloginfo('name'));

    if (empty($host) || empty($username)) {
        return;
    }

    $phpmailer->isSMTP();
    $phpmailer->Host       = $host;
    $phpmailer->SMTPAuth   = true; // Luôn bật xác thực khi cấu hình tài khoản & mật khẩu
    $phpmailer->Port       = $port;
    $phpmailer->Username   = $username;
    $phpmailer->Password   = $password;
    $phpmailer->Timeout    = 25;
    $phpmailer->CharSet    = 'UTF-8';

    if ($encryption === 'ssl') {
        $phpmailer->SMTPSecure = 'ssl';
    } elseif ($encryption === 'tls') {
        $phpmailer->SMTPSecure = 'tls';
    } else {
        $phpmailer->SMTPSecure = '';
        $phpmailer->SMTPAutoTLS = false;
    }

    // Bỏ qua kiểm tra chứng chỉ SSL cục bộ nếu server chưa cài CA Bundle
    $phpmailer->SMTPOptions = array(
        'ssl' => array(
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ),
    );

    if (!empty($from_email) && is_email($from_email)) {
        $phpmailer->setFrom($from_email, $from_name);
    } else {
        $phpmailer->setFrom($username, $from_name);
    }
}

/**
 * Tự động đặt kiểu email HTML mặc định khi gửi
 *
 * @return string
 */
add_filter('wp_mail_content_type', 'thientam_set_html_mail_content_type');
function thientam_set_html_mail_content_type()
{
    return 'text/html';
}

/**
 * Helper nạp và render file template HTML từ thư mục `templates/emails/`
 *
 * @param string $template_name Tên file template (không cần đuôi .php)
 * @param array<string, mixed> $data Dữ liệu truyền vào template
 * @return string
 */
function thientam_render_email_template($template_name, $data = array())
{
    $template_file = get_stylesheet_directory() . '/templates/emails/' . $template_name . '.php';
    if (!file_exists($template_file)) {
        return '';
    }

    extract($data, EXTR_SKIP);
    ob_start();
    include $template_file;
    return (string) ob_get_clean();
}

/**
 * 3. Render Giao diện Trang Cài đặt SMTP & Test Email
 */
function thientam_render_smtp_settings_page()
{
    global $thientam_last_mail_error;
    $saved_notice = '';
    $test_notice  = '';

    // Xử lý Lưu cấu hình
    if (isset($_POST['thientam_smtp_save_nonce']) && wp_verify_nonce($_POST['thientam_smtp_save_nonce'], 'thientam_save_smtp_settings')) {
        $enabled      = isset($_POST['thientam_smtp_enabled']) ? '1' : '0';
        $host         = isset($_POST['thientam_smtp_host']) ? trim(sanitize_text_field($_POST['thientam_smtp_host'])) : '';
        $port         = isset($_POST['thientam_smtp_port']) ? (int) $_POST['thientam_smtp_port'] : 465;
        $encryption   = isset($_POST['thientam_smtp_encryption']) ? trim(sanitize_text_field($_POST['thientam_smtp_encryption'])) : 'ssl';
        $username     = isset($_POST['thientam_smtp_username']) ? trim(sanitize_text_field($_POST['thientam_smtp_username'])) : '';
        // Xóa khoảng trắng trong mật khẩu ứng dụng (đặc biệt khi copy từ Google)
        $password     = isset($_POST['thientam_smtp_password']) ? trim(str_replace(' ', '', sanitize_text_field($_POST['thientam_smtp_password']))) : '';
        $from_email   = isset($_POST['thientam_smtp_from_email']) ? trim(sanitize_email($_POST['thientam_smtp_from_email'])) : '';
        $from_name    = isset($_POST['thientam_smtp_from_name']) ? trim(sanitize_text_field($_POST['thientam_smtp_from_name'])) : '';
        $recipients   = isset($_POST['thientam_smtp_notification_emails']) ? sanitize_textarea_field($_POST['thientam_smtp_notification_emails']) : '';
        $auto_reply   = isset($_POST['thientam_smtp_enable_autoreply']) ? '1' : '0';

        update_option('thientam_smtp_enabled', $enabled);
        update_option('thientam_smtp_host', $host);
        update_option('thientam_smtp_port', $port);
        update_option('thientam_smtp_encryption', $encryption);
        update_option('thientam_smtp_auth', '1');
        update_option('thientam_smtp_username', $username);
        if (!empty($password)) {
            update_option('thientam_smtp_password', $password);
        }
        update_option('thientam_smtp_from_email', $from_email);
        update_option('thientam_smtp_from_name', $from_name);
        update_option('thientam_smtp_notification_emails', $recipients);
        update_option('thientam_smtp_enable_autoreply', $auto_reply);

        $saved_notice = '<div class="notice notice-success is-dismissible" style="padding:10px 15px;"><p><strong>✅ Đã lưu cấu hình SMTP thành công!</strong></p></div>';
    }

    // Xử lý Gửi Email Test
    if (isset($_POST['thientam_test_email_nonce']) && wp_verify_nonce($_POST['thientam_test_email_nonce'], 'thientam_send_test_email')) {
        $to_email = isset($_POST['thientam_test_email_to']) ? sanitize_email($_POST['thientam_test_email_to']) : '';
        if (empty($to_email) || !is_email($to_email)) {
            $test_notice = '<div class="notice notice-error" style="padding:10px 15px;"><p><strong>❌ Địa chỉ email nhận không hợp lệ.</strong></p></div>';
        } else {
            $thientam_last_mail_error = '';
            $site_name = get_bloginfo('name') ?: 'Thiên Tâm 68';
            $subject   = '[' . $site_name . '] Kiểm tra kết nối SMTP thành công!';
            $message   = thientam_render_email_template('test-email', array(
                'title'      => 'Kết Nối SMTP Thành Công!',
                'badge_text' => 'KIỂM TRA HỆ THỐNG',
                'subtitle'   => 'Hệ thống gửi thư tự động của Thiên Tâm đã được cấu hình chuẩn xác.',
                'to_email'   => $to_email,
                'host'       => get_option('thientam_smtp_host', 'smtp.gmail.com'),
                'port'       => get_option('thientam_smtp_port', '465'),
                'from_email' => get_option('thientam_smtp_from_email', ''),
            ));

            // Gửi thử và bắt kết quả
            $mail_sent = wp_mail($to_email, $subject, $message);
            if ($mail_sent) {
                $test_notice = '<div class="notice notice-success is-dismissible" style="padding:12px 18px; border-left-color: #46b450;"><p><strong>🚀 Email kiểm tra đã được gửi thành công đến <code>' . esc_html($to_email) . '</code>!</strong><br><span style="color:#50575e;font-size:12px;">Vui lòng kiểm tra hộp thư đến (Inbox) hoặc thư mục Spam.</span></p></div>';
            } else {
                $err_detail = !empty($thientam_last_mail_error) ? '<div style="margin-top: 8px; background: #fff; padding: 8px 12px; border: 1px solid #f5c6cb; border-radius: 4px; font-family: monospace; font-size: 12px; color: #721c24;">Chi tiết lỗi từ máy chủ: ' . esc_html($thientam_last_mail_error) . '</div>' : '';

                $test_notice = '<div class="notice notice-error" style="padding:12px 18px; border-left-color: #dc3232;">
                    <p style="margin: 0 0 6px;"><strong>❌ Gửi email thất bại!</strong> Vui lòng kiểm tra lại:</p>
                    <ul style="margin: 0 0 0 18px; list-style: disc; font-size: 13px; line-height: 1.6;">
                        <li><strong>Tài khoản & Mật khẩu ứng dụng (App Password):</strong> Đối với Gmail, bạn bắt buộc phải dùng <em>Mật khẩu ứng dụng 16 ký tự</em> (không dùng mật khẩu đăng nhập Gmail thông thường).</li>
                        <li><strong>Port & Mã hóa:</strong> Chọn <code>SSL</code> với cổng <code>465</code> (hoặc <code>TLS</code> với cổng <code>587</code>).</li>
                        <li><strong>Xác minh 2 bước (2FA):</strong> Tài khoản Gmail gửi phải bật Xác minh 2 bước thì mới tạo được Mật khẩu ứng dụng.</li>
                    </ul>
                    ' . $err_detail . '
                </div>';
            }
        }
    }

    // Lấy giá trị hiện tại
    $enabled      = get_option('thientam_smtp_enabled', '0');
    $host         = get_option('thientam_smtp_host', 'smtp.gmail.com');
    $port         = get_option('thientam_smtp_port', '465');
    $encryption   = get_option('thientam_smtp_encryption', 'ssl');
    $auth         = get_option('thientam_smtp_auth', '1');
    $username     = get_option('thientam_smtp_username', '');
    $password     = get_option('thientam_smtp_password', '');
    $from_email   = get_option('thientam_smtp_from_email', get_option('admin_email'));
    $from_name    = get_option('thientam_smtp_from_name', 'Thiên Tâm 68');
    $recipients   = get_option('thientam_smtp_notification_emails', get_option('admin_email'));
    $auto_reply   = get_option('thientam_smtp_enable_autoreply', '1');
?>
    <div class="wrap" style="max-width: 960px; margin-top: 20px;">
        <h1 style="font-size: 22px; font-weight: bold; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            Cài đặt Cấu hình SMTP Gửi Email (Native)
        </h1>

        <?php echo $saved_notice; ?>
        <?php echo $test_notice; ?>

        <div style="display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start;">
            <!-- Cột trái: Form cấu hình SMTP -->
            <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 8px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <form method="post" action="">
                    <?php wp_nonce_field('thientam_save_smtp_settings', 'thientam_smtp_save_nonce'); ?>

                    <table class="form-table" role="presentation" style="margin-top: 0;">
                        <tbody>
                            <tr>
                                <th scope="row" style="width: 200px; font-weight: 600;">Kích hoạt SMTP</th>
                                <td>
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="checkbox" name="thientam_smtp_enabled" value="1" <?php checked($enabled, '1'); ?> style="width: 18px; height: 18px;">
                                        <span style="font-weight: 600; color: #1d2327;">Bật gửi toàn bộ email qua máy chủ SMTP</span>
                                    </label>
                                    <p class="description" style="margin-top: 4px;">Giúp email gửi từ website vào thẳng Hộp thư đến (Inbox), không bị rơi vào Spam.</p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row" style="font-weight: 600;"><label for="thientam_smtp_host">SMTP Host</label></th>
                                <td>
                                    <input type="text" id="thientam_smtp_host" name="thientam_smtp_host" value="<?php echo esc_attr($host); ?>" class="regular-text" style="width: 100%;" placeholder="smtp.gmail.com">
                                    <p class="description">Ví dụ: <code>smtp.gmail.com</code> (Gmail), <code>smtp.zoho.com</code> (Zoho), <code>smtp-relay.brevo.com</code>...</p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row" style="font-weight: 600;"><label for="thientam_smtp_encryption">Loại mã hóa & Cổng</label></th>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                                        <label><input type="radio" name="thientam_smtp_encryption" value="ssl" <?php checked($encryption, 'ssl'); ?>> SSL (Khuyên dùng - Cổng 465)</label>
                                        <label><input type="radio" name="thientam_smtp_encryption" value="tls" <?php checked($encryption, 'tls'); ?>> TLS (Cổng 587)</label>
                                        <label><input type="radio" name="thientam_smtp_encryption" value="none" <?php checked($encryption, 'none'); ?>> Không mã hóa (Cổng 25)</label>
                                    </div>
                                    <div style="margin-top: 8px;">
                                        <label for="thientam_smtp_port" style="font-size: 12px; color: #50575e;">Cổng kết nối (Port): </label>
                                        <input type="number" id="thientam_smtp_port" name="thientam_smtp_port" value="<?php echo esc_attr($port); ?>" style="width: 90px;">
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row" style="font-weight: 600;"><label for="thientam_smtp_username">Tài khoản SMTP</label></th>
                                <td>
                                    <input type="text" id="thientam_smtp_username" name="thientam_smtp_username" value="<?php echo esc_attr($username); ?>" class="regular-text" style="width: 100%; font-family: monospace;" placeholder="your-email@gmail.com">
                                    <p class="description">Địa chỉ email hoặc tài khoản đăng nhập máy chủ SMTP.</p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row" style="font-weight: 600;"><label for="thientam_smtp_password">Mật khẩu ứng dụng (App Password)</label></th>
                                <td>
                                    <input type="password" id="thientam_smtp_password" name="thientam_smtp_password" value="<?php echo esc_attr($password); ?>" class="regular-text" style="width: 100%; font-family: monospace;" placeholder="<?php echo $password ? '••••••••••••••••' : 'Nhập mật khẩu ứng dụng 16 ký tự'; ?>">
                                    <p class="description">Đối với Gmail, hãy dùng <strong>Mật khẩu ứng dụng</strong> (16 ký tự), không dùng mật khẩu Gmail chính.</p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row" style="font-weight: 600;"><label for="thientam_smtp_from_email">Email người gửi (From Email)</label></th>
                                <td>
                                    <input type="email" id="thientam_smtp_from_email" name="thientam_smtp_from_email" value="<?php echo esc_attr($from_email); ?>" class="regular-text" style="width: 100%;" placeholder="noreply@thientam68.com">
                                    <p class="description">Email hiển thị ở mục Người gửi khi khách hàng nhận thư.</p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row" style="font-weight: 600;"><label for="thientam_smtp_from_name">Tên người gửi (From Name)</label></th>
                                <td>
                                    <input type="text" id="thientam_smtp_from_name" name="thientam_smtp_from_name" value="<?php echo esc_attr($from_name); ?>" class="regular-text" style="width: 100%;" placeholder="Thiên Tâm 68">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row" style="font-weight: 600;"><label for="thientam_smtp_notification_emails">Email nhận thông báo Lead mới</label></th>
                                <td>
                                    <textarea id="thientam_smtp_notification_emails" name="thientam_smtp_notification_emails" rows="3" class="large-text" style="width: 100%;" placeholder="admin@thientam68.com, hotro@thientam68.com"><?php echo esc_textarea($recipients); ?></textarea>
                                    <p class="description">Khi có khách gửi form tư vấn/đăng ký, hệ thống sẽ gửi email báo ngay cho các địa chỉ này (phân cách nhiều email bằng dấu phẩy <code>,</code>).</p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row" style="font-weight: 600;">Email cảm ơn khách hàng</th>
                                <td>
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="checkbox" name="thientam_smtp_enable_autoreply" value="1" <?php checked($auto_reply, '1'); ?> style="width: 18px; height: 18px;">
                                        <span style="font-weight: 600; color: #1d2327;">Tự động gửi email cảm ơn & xác nhận cho khách hàng</span>
                                    </label>
                                    <p class="description" style="margin-top: 4px;">Khi khách hàng nhập email vào form, hệ thống sẽ tự động gửi thư cảm ơn với giao diện Thiên Tâm sang trọng.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #f0f0f1;">
                        <button type="submit" class="button button-primary button-large" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important; border: none !important; border-radius: 8px !important; color: #fff !important; font-size: 14px !important; font-weight: 600 !important; padding: 0 25px !important; height: 42px !important; line-height: 42px !important; display: inline-flex !important; align-items: center !important; gap: 8px !important; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35) !important; cursor: pointer !important; text-shadow: none !important;"><span class="dashicons dashicons-saved" style="font-size: 18px; width: 18px; height: 18px; margin-top: -1px;"></span> Lưu cấu hình SMTP</button>
                    </div>
                </form>
            </div>

            <!-- Cột phải: Hướng dẫn -->
            <div style="display: flex; flex-direction: column; gap: 20px;">

                <!-- Box Test Email -->
                <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; font-size: 15px; display: flex; align-items: center; gap: 6px; color: #1d2327;">
                        🧪 Gửi Email Kiểm Tra (Test)
                    </h3>
                    <p style="font-size: 12px; color: #646970; margin-bottom: 15px;">
                        Gửi một email thử nghiệm với giao diện Thiên Tâm để kiểm tra thông số SMTP có hoạt động chính xác không.
                    </p>

                    <form method="post" action="">
                        <?php wp_nonce_field('thientam_send_test_email', 'thientam_test_email_nonce'); ?>
                        <div style="margin-bottom: 12px;">
                            <label for="thientam_test_email_to" style="display: block; font-weight: 600; font-size: 12px; margin-bottom: 4px;">Gửi đến email:</label>
                            <input type="email" id="thientam_test_email_to" name="thientam_test_email_to" value="<?php echo esc_attr(get_option('admin_email')); ?>" style="width: 100%;" required>
                        </div>
                        <button type="submit" class="button button-secondary" style="width: 100%; font-weight: 600; justify-content: center;">
                            Gửi thử ngay
                        </button>
                    </form>
                </div>

                <!-- Box Xem Trước Email Templates -->
                <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; font-size: 15px; display: flex; align-items: center; gap: 6px; color: #1d2327;">
                        👁️ Xem Trước Các Mẫu Email
                    </h3>
                    <p style="font-size: 12px; color: #646970; margin-bottom: 12px;">
                        Kiểm tra giao diện thực tế của các email tự động gửi cho khách hàng và quản trị viên:
                    </p>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=thientam_preview_email&template=payment-success')); ?>" target="_blank" class="button button-secondary" style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; font-weight: 600; padding: 4px 10px; height: auto;">
                            <span>🎉 Thanh toán thành công</span>
                            <span style="color:#059669; font-size: 11px;">Xem ↗</span>
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=thientam_preview_email&template=payment-failed&reason=timeout')); ?>" target="_blank" class="button button-secondary" style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; font-weight: 600; padding: 4px 10px; height: auto;">
                            <span>⏱️ Thanh toán thất bại (Hết hạn)</span>
                            <span style="color:#d97706; font-size: 11px;">Xem ↗</span>
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=thientam_preview_email&template=payment-failed&reason=site_disabled')); ?>" target="_blank" class="button button-secondary" style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; font-weight: 600; padding: 4px 10px; height: auto;">
                            <span>🔴 Thanh toán hủy (Site tắt thanh toán)</span>
                            <span style="color:#dc2626; font-size: 11px;">Xem ↗</span>
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=thientam_preview_email&template=customer-confirmation')); ?>" target="_blank" class="button button-secondary" style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; font-weight: 600; padding: 4px 10px; height: auto;">
                            <span>💌 Thư cảm ơn khách gửi form</span>
                            <span style="color:#0284c7; font-size: 11px;">Xem ↗</span>
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=thientam_preview_email&template=lead-notification')); ?>" target="_blank" class="button button-secondary" style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; font-weight: 600; padding: 4px 10px; height: auto;">
                            <span>🔔 Thông báo Lead mới cho Admin</span>
                            <span style="color:#64748b; font-size: 11px;">Xem ↗</span>
                        </a>
                    </div>
                </div>

                <!-- Box Hướng dẫn Gmail -->
                <div style="background: #f0f6fc; border: 1px solid #c8d8e8; border-radius: 8px; padding: 18px;">
                    <h4 style="margin-top: 0; font-size: 14px; color: #005a9c; display: flex; align-items: center; gap: 6px;">
                        Hướng dẫn cấu hình Gmail SMTP:
                    </h4>
                    <ol style="margin-left: 18px; line-height: 1.7; color: #2c3338; font-size: 12px; margin-bottom: 0;">
                        <li>Bật <strong>Xác minh 2 bước (2-Step Verification)</strong> cho tài khoản Gmail.</li>
                        <li>Truy cập <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener noreferrer" style="font-weight: bold; text-decoration: underline;">Tạo Mật khẩu ứng dụng ↗</a></li>
                        <li>Đặt tên ứng dụng: <strong>Website Thiên Tâm</strong> rồi bấm <strong>Tạo</strong>.</li>
                        <li>Sao chép <strong>16 ký tự mật khẩu</strong> dán vào ô <em>Mật khẩu ứng dụng</em> bên cạnh.</li>
                        <li>Điền SMTP Host: <code>smtp.gmail.com</code> | Port: <code>465</code> | Mã hóa: <code>SSL</code>.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
<?php
}

/**
 * Ajax Handler xem trước email template trực tiếp trên trình duyệt
 */
add_action('wp_ajax_thientam_preview_email', 'thientam_ajax_preview_email');
function thientam_ajax_preview_email()
{
    if (!current_user_can('manage_options')) {
        wp_die('Bạn không có quyền truy cập trang này.');
    }

    $template = isset($_GET['template']) ? sanitize_key($_GET['template']) : 'lead-notification';
    header('Content-Type: text/html; charset=utf-8');

    if ($template === 'payment-success') {
        echo thientam_render_email_template('payment-success', array(
            'title'            => 'Xác Nhận Thanh Toán Thành Công',
            'badge_text'       => 'THANH TOÁN THÀNH CÔNG',
            'subtitle'         => 'Hệ thống VietQR Napas 24/7 đã tự động ghi nhận thanh toán của Quý khách.',
            'customer_name'    => 'Nguyễn Văn An',
            'customer_phone'   => '0935 425 238',
            'customer_email'   => 'nguyenvanan@gmail.com',
            'service_name'     => 'Hiểu Con Để Đồng Hành',
            'package_name'     => 'Gói Đồng Hành Toàn Diện',
            'payment_code'     => 'TT8866',
            'amount_formatted' => '1.500.000đ',
            'paid_at'          => date('d/m/Y H:i:s'),
            'checkout_url'     => 'https://thientam68.com/order/demo-token-123',
            'reference_code'   => 'MBVCB.992837192',
            'transaction_id'   => 'TX9827364',
            'customer_notes'   => 'Ngày sinh: 15/08/2012 | Giờ sinh: 09:30',
        ));
    } elseif ($template === 'payment-failed') {
        $reason = isset($_GET['reason']) ? sanitize_key($_GET['reason']) : 'timeout';
        $badge = ($reason === 'site_disabled') ? 'CỔNG THANH TOÁN TẠM ĐÓNG' : 'THANH TOÁN CHƯA HOÀN TẤT';
        echo thientam_render_email_template('payment-failed', array(
            'title'            => 'Thông Báo Trạng Thái Đơn Hàng',
            'badge_text'       => $badge,
            'subtitle'         => 'Yêu cầu thanh toán của đơn hàng chưa được hoàn tất hoặc đã kết thúc phiên.',
            'customer_name'    => 'Nguyễn Văn An',
            'customer_phone'   => '0935 425 238',
            'customer_email'   => 'nguyenvanan@gmail.com',
            'service_name'     => 'Hiểu Con Để Đồng Hành',
            'package_name'     => 'Gói Cơ Bản',
            'payment_code'     => 'TT8866',
            'amount_formatted' => '1.000.000đ',
            'created_at'       => date('d/m/Y H:i'),
            'cancelled_reason' => $reason,
            'checkout_url'     => 'https://thientam68.com/order/demo-token-123',
            'customer_notes'   => 'Ngày sinh: 15/08/2012',
        ));
    } elseif ($template === 'customer-confirmation') {
        echo thientam_render_email_template('customer-confirmation', array(
            'title'            => 'Tiếp Nhận Yêu Cầu Thành Công',
            'badge_text'       => '',
            'subtitle'         => 'Thiên Tâm rất hân hạnh được đồng hành cùng Quý vị.',
            'customer_name'    => 'Nguyễn Văn An',
            'service_title'    => 'Tư vấn Phong thủy Nhà ở',
            'customer_phone'   => '0935 425 238',
            'customer_email'   => 'nguyenvanan@gmail.com',
            'customer_message' => 'Tôi cần tư vấn hướng xây nhà phố và bố trí bàn thờ, phòng khách theo phong thủy tuổi 1985.',
            'fields'           => array(
                'Dịch vụ quan tâm' => 'Tư vấn Phong thủy Nhà ở',
            ),
        ));
    } elseif ($template === 'test-email') {
        echo thientam_render_email_template('test-email', array(
            'title'      => 'Kết Nối SMTP Thành Công!',
            'badge_text' => 'KIỂM TRA HỆ THỐNG',
            'subtitle'   => 'Hệ thống gửi thư tự động của Thiên Tâm đã được cấu hình chuẩn xác.',
            'to_email'   => get_option('admin_email', 'admin@thientam68.com'),
            'host'       => get_option('thientam_smtp_host', 'smtp.gmail.com'),
            'port'       => get_option('thientam_smtp_port', '465'),
            'from_email' => get_option('thientam_smtp_from_email', 'thientamdl68@gmail.com'),
        ));
    } else {
        // Mặc định lead-notification
        echo thientam_render_email_template('lead-notification', array(
            'title'      => 'Yêu Cầu Tư Vấn Mới',
            'badge_text' => 'ĐĂNG KÝ DỊCH VỤ',
            'subtitle'   => 'Thông tin vừa được gửi trực tiếp từ website thientam68.com',
            'post_id'    => 1,
            'form_title' => 'Đăng ký dịch vụ',
            'name'       => 'Nguyễn Văn An',
            'phone'      => '0935 425 238',
            'email'      => 'nguyenvanan@gmail.com',
            'message'    => "Tôi đang chuẩn bị xây nhà phố 3 tầng tại Đà Lạt, cần chuyên gia xem hướng đất và tư vấn bố trí phòng thờ, phòng khách theo tuổi gia chủ.",
            'fields'     => array(
                'Dịch vụ đăng ký' => 'Tư vấn Phong thủy Nhà ở',
                'Nhu cầu cụ thể' => 'Tư vấn hướng nhà và bố trí công năng',
            ),
            'page_url'   => 'https://thientam68.com/services/phong-thuy-nha-o',
            'admin_url'  => admin_url('edit.php?post_type=form_submission'),
        ));
    }

    exit;
}

/**
 * 4. Hàm gửi Email HTML Thông Báo Lead Mới cho Admin / Tư Vấn Viên
 *
 * @param int $post_id
 * @param array<string, mixed> $data
 * @return bool
 */
function thientam_send_lead_notification_email($post_id, $data = array())
{
    $recipients_str = get_option('thientam_smtp_notification_emails', get_option('admin_email'));
    if (empty($recipients_str)) {
        return false;
    }

    $emails = array_map('trim', explode(',', $recipients_str));
    $valid_emails = array_filter($emails, 'is_email');
    if (empty($valid_emails)) {
        return false;
    }

    $form_title = isset($data['form_title']) ? $data['form_title'] : 'Form Liên Hệ';
    $name       = isset($data['name']) && $data['name'] ? $data['name'] : 'Khách vãng lai';
    $phone      = isset($data['phone']) ? $data['phone'] : '';
    $email      = isset($data['email']) ? $data['email'] : '';
    $message    = isset($data['message']) ? $data['message'] : '';
    $fields     = isset($data['fields']) && is_array($data['fields']) ? $data['fields'] : array();
    $page_url   = isset($data['page_url']) ? $data['page_url'] : '';
    $site_name  = get_bloginfo('name') ?: 'Thiên Tâm 68';
    $admin_url  = admin_url('post.php?post=' . $post_id . '&action=edit');

    $subject = '[' . $site_name . '] ' . $form_title . ': ' . $name . ($phone ? ' (' . $phone . ')' : '');

    // Render body từ file template templates/emails/lead-notification.php
    $body = thientam_render_email_template('lead-notification', array(
        'title'      => 'Yêu Cầu Tư Vấn Mới',
        'badge_text' => $form_title,
        'subtitle'   => 'Thông tin vừa được gửi trực tiếp từ website thientam68.com',
        'post_id'    => $post_id,
        'form_title' => $form_title,
        'name'       => $name,
        'phone'      => $phone,
        'email'      => $email,
        'message'    => $message,
        'fields'     => $fields,
        'page_url'   => $page_url,
        'admin_url'  => $admin_url,
    ));

    $sent = wp_mail($valid_emails, $subject, $body);

    // Tự động gửi email cảm ơn cho khách hàng nếu có email và bật tính năng
    if ($email && is_email($email) && get_option('thientam_smtp_enable_autoreply', '1') === '1') {
        thientam_send_customer_confirmation_email($name, $email, $form_title, array(
            'phone'    => $phone,
            'message'  => $message,
            'fields'   => $fields,
            'page_url' => $page_url,
        ));
    }

    return $sent;
}

/**
 * 5. Gửi Email Cảm Ơn & Xác Nhận Tự Động cho Khách Hàng
 *
 * @param string $customer_name
 * @param string $customer_email
 * @param string $service_title
 * @param array<string, mixed> $extra_data
 * @return bool
 */
function thientam_send_customer_confirmation_email($customer_name, $customer_email, $service_title = '', $extra_data = array())
{
    $site_name = get_bloginfo('name') ?: 'Thiên Tâm 68';
    $subject   = '[' . $site_name . '] Cảm ơn Quý khách đã gửi yêu cầu tư vấn';

    // Render body từ file template templates/emails/customer-confirmation.php
    $body = thientam_render_email_template('customer-confirmation', array(
        'title'            => 'Tiếp Nhận Yêu Cầu Thành Công',
        'badge_text'       => '',
        'subtitle'         => 'Thiên Tâm rất hân hạnh được đồng hành cùng Quý vị.',
        'customer_name'    => $customer_name,
        'service_title'    => $service_title,
        'customer_phone'   => isset($extra_data['phone']) ? $extra_data['phone'] : '',
        'customer_email'   => $customer_email,
        'customer_message' => isset($extra_data['message']) ? $extra_data['message'] : '',
        'fields'           => isset($extra_data['fields']) ? $extra_data['fields'] : array(),
    ));

    return wp_mail($customer_email, $subject, $body);
}

/**
 * 6. Gửi Email Xác Nhận Thanh Toán Thành Công Cho Khách Hàng & Admin
 *
 * @param int $order_id
 * @param string $payment_code
 * @param array $transaction_data
 * @return bool
 */
function thientam_send_payment_success_email($order_id, $payment_code = '', $transaction_data = array())
{
    // Chống gửi lặp email
    if (get_post_meta($order_id, '_sepay_success_email_sent', true) === '1') {
        return false;
    }

    $customer_name    = (string) get_post_meta($order_id, 'customer_name', true);
    $customer_email   = (string) get_post_meta($order_id, 'customer_email', true);
    $customer_phone   = (string) get_post_meta($order_id, 'customer_phone', true);
    $service_name     = (string) get_post_meta($order_id, 'service_name', true);
    $package_name     = (string) get_post_meta($order_id, 'package_name', true);
    $customer_notes   = (string) get_post_meta($order_id, 'customer_notes', true);
    $amount           = (int) get_post_meta($order_id, 'amount', true);
    $amount_formatted = number_format($amount, 0, ',', '.') . 'đ';
    $paid_at          = (string) get_post_meta($order_id, 'paid_at', true) ?: current_time('d/m/Y H:i:s');
    $checkout_url     = (string) get_post_meta($order_id, 'checkout_url', true);
    $ref_code         = (string) get_post_meta($order_id, 'sepay_reference_code', true);
    $tx_id            = (string) get_post_meta($order_id, 'sepay_transaction_id', true);
    $order_source     = (string) get_post_meta($order_id, 'order_source', true);
    $landing_slug     = (string) get_post_meta($order_id, 'landing_slug', true);

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

    if ($order_source === 'landing' && !empty($landing_slug) && strpos($checkout_url, 'slug=') === false) {
        $checkout_url .= (strpos($checkout_url, '?') !== false ? '&' : '?') . 'from=landing&slug=' . $landing_slug;
    }

    if (empty($payment_code)) {
        $payment_code = (string) get_post_meta($order_id, 'payment_code', true);
    }

    $site_name = get_bloginfo('name') ?: 'Thiên Tâm 68';
    $subject   = '[' . $site_name . '] Xác nhận thanh toán thành công đơn hàng #' . $payment_code . ' (' . $amount_formatted . ')';

    $template_data = array(
        'title'            => 'Xác Nhận Thanh Toán Thành Công',
        'badge_text'       => 'THANH TOÁN THÀNH CÔNG',
        'subtitle'         => 'Hệ thống VietQR Napas 24/7 đã tự động ghi nhận thanh toán của Quý khách.',
        'customer_name'    => $customer_name,
        'customer_phone'   => $customer_phone,
        'customer_email'   => $customer_email,
        'service_name'     => $service_name,
        'package_name'     => $package_name,
        'payment_code'     => $payment_code,
        'amount_formatted' => $amount_formatted,
        'paid_at'          => $paid_at,
        'checkout_url'     => $checkout_url,
        'reference_code'   => $ref_code,
        'transaction_id'   => $tx_id,
        'customer_notes'   => $customer_notes,
        'order_source'     => $order_source,
        'landing_slug'     => $landing_slug,
    );

    $body = thientam_render_email_template('payment-success', $template_data);
    if (empty($body)) {
        return false;
    }

    $sent = false;
    if (!empty($customer_email) && is_email($customer_email)) {
        $sent = wp_mail($customer_email, $subject, $body);
    }

    // Gửi thông báo cho Admin/Tư vấn viên
    $recipients_str = get_option('thientam_smtp_notification_emails', get_option('admin_email'));
    if (!empty($recipients_str)) {
        $raw_emails = array_map('trim', explode(',', $recipients_str));
        $valid_emails = array_filter($raw_emails, 'is_email');
        if (!empty($valid_emails)) {
            $admin_subject = '[' . $site_name . '] [ĐÃ THANH TOÁN] ' . $customer_name . ' - #' . $payment_code . ' (' . $amount_formatted . ')';
            wp_mail($valid_emails, $admin_subject, $body);
        }
    }

    update_post_meta($order_id, '_sepay_success_email_sent', '1');
    update_post_meta($order_id, '_sepay_success_email_sent_at', current_time('mysql'));

    return $sent;
}
add_action('custom_sepay_order_paid', 'thientam_send_payment_success_email', 10, 3);
add_action('ilynk_sepay_order_paid', 'thientam_send_payment_success_email', 10, 3);

/**
 * 7. Gửi Email Thông Báo Thanh Toán Chưa Hoàn Tất / Đã Hủy
 *
 * @param int $order_id
 * @param string $reason ('timeout' | 'site_disabled' | etc.)
 * @return bool
 */
function thientam_send_payment_failed_email($order_id, $reason = '')
{
    // Chống gửi lặp email
    if (get_post_meta($order_id, '_sepay_cancel_email_sent', true) === '1') {
        return false;
    }

    // Không gửi nếu đơn này đã được thanh toán
    if (get_post_meta($order_id, 'payment_status', true) === 'paid') {
        return false;
    }

    $customer_name    = (string) get_post_meta($order_id, 'customer_name', true);
    $customer_email   = (string) get_post_meta($order_id, 'customer_email', true);
    $customer_phone   = (string) get_post_meta($order_id, 'customer_phone', true);
    $service_name     = (string) get_post_meta($order_id, 'service_name', true);
    $package_name     = (string) get_post_meta($order_id, 'package_name', true);
    $customer_notes   = (string) get_post_meta($order_id, 'customer_notes', true);
    $amount           = (int) get_post_meta($order_id, 'amount', true);
    $amount_formatted = number_format($amount, 0, ',', '.') . 'đ';
    $created_at       = (string) get_post_meta($order_id, 'created_at', true) ?: current_time('d/m/Y H:i');
    $checkout_url     = (string) get_post_meta($order_id, 'checkout_url', true);
    $payment_code     = (string) get_post_meta($order_id, 'payment_code', true);
    $order_source     = (string) get_post_meta($order_id, 'order_source', true);
    $landing_slug     = (string) get_post_meta($order_id, 'landing_slug', true);

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

    if ($order_source === 'landing' && !empty($landing_slug) && strpos($checkout_url, 'slug=') === false) {
        $checkout_url .= (strpos($checkout_url, '?') !== false ? '&' : '?') . 'from=landing&slug=' . $landing_slug;
    }

    if (empty($reason)) {
        $reason = (string) get_post_meta($order_id, 'cancelled_reason', true);
    }

    if (empty($customer_email) || !is_email($customer_email)) {
        return false;
    }

    $site_name  = get_bloginfo('name') ?: 'Thiên Tâm 68';
    $badge_text = ($reason === 'site_disabled') ? 'CỔNG THANH TOÁN TẠM ĐÓNG' : 'THANH TOÁN CHƯA HOÀN TẤT';
    $subject    = '[' . $site_name . '] Thông báo trạng thái đơn hàng #' . $payment_code;

    $template_data = array(
        'title'            => 'Thông Báo Trạng Thái Đơn Hàng',
        'badge_text'       => $badge_text,
        'subtitle'         => 'Yêu cầu thanh toán của đơn hàng chưa được hoàn tất hoặc đã kết thúc phiên.',
        'customer_name'    => $customer_name,
        'customer_phone'   => $customer_phone,
        'customer_email'   => $customer_email,
        'service_name'     => $service_name,
        'package_name'     => $package_name,
        'payment_code'     => $payment_code,
        'amount_formatted' => $amount_formatted,
        'created_at'       => $created_at,
        'cancelled_reason' => $reason,
        'checkout_url'     => $checkout_url,
        'customer_notes'   => $customer_notes,
        'order_source'     => $order_source,
        'landing_slug'     => $landing_slug,
    );

    $body = thientam_render_email_template('payment-failed', $template_data);
    if (empty($body)) {
        return false;
    }

    $sent = wp_mail($customer_email, $subject, $body);

    update_post_meta($order_id, '_sepay_cancel_email_sent', '1');
    update_post_meta($order_id, '_sepay_cancel_email_sent_at', current_time('mysql'));

    return $sent;
}
add_action('custom_sepay_order_cancelled', 'thientam_send_payment_failed_email', 10, 2);
add_action('ilynk_sepay_order_cancelled', 'thientam_send_payment_failed_email', 10, 2);
