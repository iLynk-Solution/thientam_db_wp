<?php

/**
 * Template Email Cảm Ơn & Xác Nhận Gửi Cho Khách Hàng
 *
 * @var string $customer_name
 * @var string $service_title
 * @var string $customer_phone
 * @var string $customer_email
 * @var string $customer_message
 * @var array<string, mixed> $fields
 */

include __DIR__ . '/header.php';

// Tự động nhận diện chính xác loại form (Khóa học vs Dịch vụ vs Tư vấn chung)
$service_label   = 'Dịch vụ đăng ký';
$display_service = $service_title ?? '';
$is_course       = false;

// 1. Kiểm tra qua các trường trong fields
if (!empty($fields) && is_array($fields)) {
    foreach ($fields as $k => $v) {
        $k_str   = (string)$k;
        $k_lower = function_exists('mb_strtolower') ? mb_strtolower($k_str, 'UTF-8') : strtolower($k_str);
        if (strpos($k_lower, 'khóa học') !== false) {
            $service_label   = 'Khóa học đăng ký';
            $display_service = is_array($v) ? implode(', ', $v) : (string)$v;
            $is_course       = true;
            break;
        } elseif (strpos($k_lower, 'dịch vụ') !== false) {
            $service_label   = 'Dịch vụ đăng ký';
            $display_service = is_array($v) ? implode(', ', $v) : (string)$v;
            break;
        }
    }
}

// 2. Kiểm tra qua tên tiêu đề dịch vụ/khóa học
$title_lower = function_exists('mb_strtolower') ? mb_strtolower((string)$display_service, 'UTF-8') : strtolower((string)$display_service);
if (strpos($title_lower, 'khóa học') !== false || strpos($title_lower, 'đào tạo') !== false || strpos($title_lower, 'bát tự') !== false || strpos($title_lower, 'kinh dịch') !== false) {
    $service_label = 'Khóa học đăng ký';
    $is_course     = true;
}

$badge_lead_text = $is_course ? 'Đăng ký khóa học' : 'Đăng ký tư vấn';
$intro_action    = $is_course ? 'đăng ký tham gia khóa học' : 'yêu cầu tư vấn dịch vụ';
$team_name       = $is_course ? 'Ban Đào Tạo & Chuyên Gia Thiên Tâm' : 'Chuyên gia tư vấn của Thiên Tâm';
?>

<p style="font-size: 15px; color: #0D2A54; line-height: 1.6; margin-top: 0; font-weight: 600;">
    Kính gửi Quý khách <?php echo esc_html($customer_name ?: 'Quý khách'); ?>,
</p>
<p style="font-size: 14px; color: #334155; line-height: 1.7;">
    Lời đầu tiên, <strong>Thiên Tâm 68</strong> xin gửi lời chúc sức khỏe, an lạc và vạn sự cát lành đến Quý khách cùng gia đình.
</p>
<p style="font-size: 14px; color: #334155; line-height: 1.7;">
    Hệ thống của chúng tôi đã tiếp nhận thông tin <?php echo esc_html($intro_action); ?><?php echo !empty($display_service) ? ': <strong style="color:#0D2A54;">' . esc_html($display_service) . '</strong>' : ''; ?> của Quý khách. <?php echo esc_html($team_name); ?> sẽ chủ động liên hệ lại trong thời gian sớm nhất qua số điện thoại <strong><?php echo esc_html($customer_phone ?: 'đã đăng ký'); ?></strong> để trao đổi chi tiết.
</p>

<!-- Summary Submitted Box -->
<div style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; margin: 24px 0;">
    <div style="background-color: #0D2A54; color: #D4AF37; padding: 11px 18px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
        Thông Tin Quý Khách Đã Gửi:
    </div>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="responsive-table" style="font-size: 13px;">
        <tbody>
            <tr>
                <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; width: 160px; font-weight: 600;">Họ và tên:</td>
                <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #0D2A54; font-weight: 700; font-size: 14px;"><?php echo esc_html($customer_name ?: '(Chưa nhập)'); ?></td>
            </tr>
            <?php if (!empty($customer_phone)) : ?>
                <tr>
                    <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Số điện thoại:</td>
                    <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #174C97; font-weight: 700; font-size: 14px;"><?php echo esc_html($customer_phone); ?></td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($customer_email)) : ?>
                <tr>
                    <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Email:</td>
                    <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #334155;"><?php echo esc_html($customer_email); ?></td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($display_service)) : ?>
                <tr>
                    <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;"><?php echo esc_html($service_label); ?>:</td>
                    <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #0D2A54; font-weight: 700;">
                        <span style="display:inline-block; padding: 3px 10px; background-color: #EFF6FF; color: #1D4ED8; border-radius: 6px; font-size: 13px;"><?php echo esc_html($display_service); ?></span>
                    </td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($customer_message)) : ?>
                <tr>
                    <td class="table-label" style="padding: 12px 18px; color: #64748B; font-weight: 600;">Lời nhắn / Ghi chú:</td>
                    <td class="table-value" style="padding: 12px 18px; color: #334155; line-height: 1.6; font-style: italic;">“<?php echo nl2br(esc_html($customer_message)); ?>”</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Highlight Hotline Box -->
<div style="background-color: #FFFDF5; border: 1px solid #E6CA65; border-radius: 12px; padding: 18px 20px; margin: 22px 0; text-align: center;">
    <p style="margin: 0 0 6px; font-size: 13px; color: #854D0E; font-weight: 700;">
        CẦN TƯ VẤN TRỰC TIẾP NGAY BÂY GIỜ?
    </p>
    <p style="margin: 0; font-size: 13px; color: #475569;">
        Quý khách có thể kết nối ngay với Tổng đài Chuyên gia:
        <a href="tel:0935425238" style="color: #0D2A54; font-size: 16px; font-weight: 800; text-decoration: none; display: inline-block; margin-top: 4px;">0935 425 238</a>
    </p>
</div>

<p style="font-size: 14px; color: #334155; line-height: 1.7; margin-bottom: 0;">
    Trân trọng cảm ơn sự tin tưởng và đồng hành của Quý khách!<br>
</p>

<?php
include __DIR__ . '/footer.php';
