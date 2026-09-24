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

// Trích xuất các thông tin chi tiết & thanh toán từ $fields
$is_waiting_payment  = false;
$payment_status_text = '';
$cost_text           = '';
$payment_method_text = '';
$package_name_text   = '';

if (!empty($fields) && is_array($fields)) {
    foreach ($fields as $k => $v) {
        $k_clean = trim((string)$k);
        $k_lower = function_exists('mb_strtolower') ? mb_strtolower($k_clean, 'UTF-8') : strtolower($k_clean);
        $v_str   = is_array($v) ? implode(', ', $v) : trim((string)$v);
        $v_lower = function_exists('mb_strtolower') ? mb_strtolower($v_str, 'UTF-8') : strtolower($v_str);

        if (strpos($k_lower, 'khóa học') !== false) {
            $service_label   = 'Khóa học đăng ký';
            $display_service = $v_str;
            $is_course       = true;
        } elseif (strpos($k_lower, 'dịch vụ đăng ký') !== false || strpos($k_lower, 'dịch vụ quan tâm') !== false) {
            if (empty($display_service) || $display_service === 'Form Liên Hệ' || strpos($display_service, '[') === 0) {
                $display_service = $v_str;
            }
        }

        if (in_array($k_lower, array('gói dịch vụ', 'gói dịch vụ quan tâm', 'gói luận giải', 'gói đăng ký'))) {
            $package_name_text = $v_str;
        }

        if (in_array($k_lower, array('chi phí', 'phí tư vấn', 'giá gói', 'học phí'))) {
            $cost_text = $v_str;
        }

        if (in_array($k_lower, array('phương thức', 'phương thức thanh toán', 'hình thức thanh toán'))) {
            $payment_method_text = $v_str;
        }

        if ($k_lower === 'tình trạng thanh toán' || $k_lower === 'trạng thái thanh toán') {
            $payment_status_text = $v_str;
            if (strpos($v_lower, 'chờ') !== false || strpos($v_lower, 'đang chờ') !== false) {
                $is_waiting_payment = true;
            }
        }
    }
}

// 2. Kiểm tra qua tên tiêu đề dịch vụ/khóa học
$title_lower = function_exists('mb_strtolower') ? mb_strtolower((string)$display_service, 'UTF-8') : strtolower((string)$display_service);
if (strpos($title_lower, 'khóa học') !== false || strpos($title_lower, 'đào tạo') !== false || strpos($title_lower, 'bát tự') !== false || strpos($title_lower, 'kinh dịch') !== false) {
    $service_label = 'Khóa học đăng ký';
    $is_course     = true;
}

// Bổ sung kiểm tra tình trạng thanh toán nếu có phương thức VietQR / SePay hoặc chi phí dạng tiền
if (!$is_waiting_payment) {
    $has_vietqr = !empty($payment_method_text) && (stripos($payment_method_text, 'vietqr') !== false || stripos($payment_method_text, 'sepay') !== false || stripos($payment_method_text, 'chuyển khoản') !== false);
    $has_cost   = !empty($cost_text) && stripos($cost_text, 'thỏa thuận') === false && stripos($cost_text, 'miễn phí') === false && preg_match('/\d+/', $cost_text);

    if ($has_vietqr || $has_cost) {
        $is_waiting_payment  = true;
        $payment_status_text = 'Đang chờ thanh toán';
    }
} elseif (empty($payment_status_text)) {
    $payment_status_text = 'Đang chờ thanh toán';
}

$team_name = $is_course ? 'Ban Đào Tạo & Chuyên Gia Thiên Tâm' : 'Chuyên gia tư vấn của Thiên Tâm';
?>

<p style="font-size: 15px; color: #0D2A54; line-height: 1.6; margin-top: 0; font-weight: 600;">
    Kính gửi Quý khách <?php echo esc_html($customer_name ?: 'Quý khách'); ?>,
</p>
<p style="font-size: 14px; color: #334155; line-height: 1.7;">
    Lời đầu tiên, <strong>Thiên Tâm 68</strong> xin gửi lời chúc sức khỏe, an lạc và vạn sự cát lành đến Quý khách cùng gia đình.
</p>

<?php if ($is_waiting_payment) : ?>
    <p style="font-size: 14px; color: #334155; line-height: 1.7;">
        Hệ thống của chúng tôi đã tiếp nhận thông tin đăng ký<?php echo !empty($display_service) ? ': <strong style="color:#0D2A54;">' . esc_html($display_service) . '</strong>' : ''; ?> của Quý khách. Đơn đăng ký hiện đang ở tình trạng <strong style="color: #B45309;">Đang chờ thanh toán</strong>.
    </p>

    <!-- Highlight Box Đang Chờ Thanh Toán -->
    <div style="background: linear-gradient(135deg, #FFFDF5 0%, #FEF3C7 100%); border: 1px solid #FCD34D; border-radius: 12px; padding: 18px 20px; margin: 20px 0; text-align: center;">
        <div style="display: inline-block; width: 44px; height: 44px; line-height: 44px; border-radius: 50%; background-color: #D97706; color: #ffffff; font-size: 20px; font-weight: 800; margin-bottom: 8px;">
            ⏳
        </div>
        <div style="font-size: 13px; font-weight: 700; color: #92400E; text-transform: uppercase; letter-spacing: 0.8px;">
            Tình Trạng: Đang Chờ Thanh Toán
        </div>
        <?php if (!empty($cost_text)) : ?>
            <div style="font-size: 24px; font-weight: 900; color: #B45309; margin-top: 4px; font-family: 'Segoe UI', Roboto, sans-serif;">
                <?php echo esc_html($cost_text); ?>
            </div>
        <?php endif; ?>
        <p style="margin: 8px auto 0; font-size: 13px; color: #78350F; line-height: 1.5; max-width: 500px;">
            Hệ thống đang chờ giao dịch chuyển khoản VietQR Napas 24/7 của Quý khách. Sau khi thanh toán thành công, hệ thống sẽ tự động gửi email xác nhận và kích hoạt lịch tư vấn ngay lập tức.
        </p>
    </div>
<?php else : ?>
    <p style="font-size: 14px; color: #334155; line-height: 1.7;">
        Hệ thống của chúng tôi đã tiếp nhận thông tin <?php echo esc_html($is_course ? 'đăng ký tham gia khóa học' : 'yêu cầu tư vấn dịch vụ'); ?><?php echo !empty($display_service) ? ': <strong style="color:#0D2A54;">' . esc_html($display_service) . '</strong>' : ''; ?> của Quý khách. <?php echo esc_html($team_name); ?> sẽ chủ động liên hệ lại trong thời gian sớm nhất qua số điện thoại <strong><?php echo esc_html($customer_phone ?: 'đã đăng ký'); ?></strong> để trao đổi chi tiết.
    </p>
<?php endif; ?>

<!-- Summary Submitted Box -->
<div style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; margin: 24px 0;">
    <div style="background-color: #0D2A54; color: #D4AF37; padding: 11px 18px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
        Thông Tin Quý Khách Đã Gửi:
    </div>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="responsive-table" style="font-size: 13px;">
        <tbody>
            <tr>
                <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; width: 170px; font-weight: 600;">Họ và tên:</td>
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
            <?php if (!empty($package_name_text)) : ?>
                <tr>
                    <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Gói dịch vụ:</td>
                    <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #0D2A54; font-weight: 700;">
                        <?php echo esc_html($package_name_text); ?>
                    </td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($cost_text)) : ?>
                <tr>
                    <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Chi phí:</td>
                    <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #059669; font-weight: 800; font-size: 14px;">
                        <?php echo esc_html($cost_text); ?>
                    </td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($payment_method_text)) : ?>
                <tr>
                    <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Phương thức:</td>
                    <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #334155;">
                        <span style="display:inline-block; padding: 2px 8px; background-color: #EFF6FF; color: #1D4ED8; border-radius: 6px; font-size: 12px; font-weight: 600;">
                            <?php echo esc_html($payment_method_text); ?>
                        </span>
                    </td>
                </tr>
            <?php endif; ?>

            <?php if ($is_waiting_payment || !empty($payment_status_text)) : ?>
                <tr>
                    <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Tình trạng thanh toán:</td>
                    <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0;">
                        <?php if ($is_waiting_payment || strpos(strtolower($payment_status_text), 'chờ') !== false) : ?>
                            <span style="display:inline-block; padding: 4px 12px; background-color: #FEF3C7; color: #92400E; border: 1px solid #FCD34D; border-radius: 6px; font-weight: 700; font-size: 13px;">
                                ⏳ Đang chờ thanh toán
                            </span>
                        <?php else : ?>
                            <span style="display:inline-block; padding: 4px 12px; background-color: #DEF7EC; color: #03543F; border: 1px solid #BCF0DA; border-radius: 6px; font-weight: 700; font-size: 13px;">
                                <?php echo esc_html($payment_status_text); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>

            <!-- Các trường chi tiết khác được khách gửi -->
            <?php if (!empty($fields) && is_array($fields)) : ?>
                <?php
                $skip_keys = array(
                    'họ và tên', 'họ tên', 'số điện thoại', 'sđt', 'email', 'name', 'phone',
                    'dịch vụ đăng ký', 'dịch vụ quan tâm', 'khóa học đăng ký',
                    'gói dịch vụ', 'gói dịch vụ quan tâm', 'gói luận giải', 'gói đăng ký',
                    'chi phí', 'phí tư vấn', 'giá gói', 'học phí',
                    'phương thức', 'phương thức thanh toán', 'hình thức thanh toán',
                    'tình trạng thanh toán', 'trạng thái thanh toán', 'lời nhắn', 'ghi chú'
                );
                ?>
                <?php foreach ($fields as $k => $v) : ?>
                    <?php
                    $k_clean = trim((string)$k);
                    $k_lower = function_exists('mb_strtolower') ? mb_strtolower($k_clean, 'UTF-8') : strtolower($k_clean);
                    if (in_array($k_lower, $skip_keys)) {
                        continue;
                    }
                    $val_str = is_array($v) ? implode(', ', $v) : (string)$v;
                    ?>
                    <tr>
                        <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;"><?php echo esc_html($k); ?>:</td>
                        <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #0D2A54;"><?php echo nl2br(esc_html($val_str)); ?></td>
                    </tr>
                <?php endforeach; ?>
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

<?php if ($is_waiting_payment) : ?>
    <!-- Hướng dẫn hoàn tất thanh toán -->
    <div style="background-color: #F0FDF4; border: 1px dashed #86EFAC; border-radius: 10px; padding: 14px 18px; margin: 18px 0; font-size: 13px; color: #166534; line-height: 1.6;">
        💡 <strong>Lưu ý:</strong> Nếu Quý khách chưa kịp quét mã QR VietQR hoặc đã đóng trình duyệt, Chuyên viên Thiên Tâm sẽ chủ động liên hệ qua số <strong><?php echo esc_html($customer_phone ?: 'điện thoại'); ?></strong> để hỗ trợ Quý khách hoàn tất một cách thuận tiện nhất.
    </div>
<?php endif; ?>

<!-- Highlight Hotline Box -->
<div style="background-color: #FFFDF5; border: 1px solid #E6CA65; border-radius: 12px; padding: 18px 20px; margin: 22px 0; text-align: center;">
    <p style="margin: 0 0 6px; font-size: 13px; color: #854D0E; font-weight: 700;">
        CẦN HỖ TRỢ TRỰC TIẾP NGAY BÂY GIỜ?
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

