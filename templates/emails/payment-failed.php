<?php

/**
 * Template Email Thông Báo Thanh Toán Thất Bại / Đã Hủy - Thiên Tâm 68
 *
 * @var string $title
 * @var string $badge_text
 * @var string $subtitle
 * @var string $customer_name
 * @var string $customer_phone
 * @var string $customer_email
 * @var string $service_name
 * @var string $package_name
 * @var string $payment_code
 * @var string $amount_formatted
 * @var string $created_at
 * @var string $cancelled_reason
 * @var string $checkout_url
 * @var string $customer_notes
 */

include __DIR__ . '/header.php';

$display_service = trim(($service_name ?? '') . (!empty($package_name) ? ' – ' . $package_name : ''));
if (empty($display_service)) {
    $display_service = 'Dịch vụ Phong Thủy Thiên Tâm';
}

$is_site_disabled = ($cancelled_reason ?? '') === 'site_disabled';
$is_timeout       = ($cancelled_reason ?? '') === 'timeout';

if ($is_site_disabled) {
    $reason_title = 'Cổng thanh toán trực tuyến trên website hiện đang tạm tắt';
    $reason_desc  = 'Hệ thống hiện đang tạm tắt chức năng thanh toán trực tuyến qua VietQR. Yêu cầu thanh toán tự động cho đơn hàng này đã được hủy.';
} elseif ($is_timeout) {
    $reason_title = 'Mã thanh toán đã hết hiệu lực (Quá thời hạn 15 phút)';
    $reason_desc  = 'Mã thanh toán VietQR có hiệu lực trong vòng 15 phút kể từ thời điểm tạo đơn. Do chưa nhận được giao dịch chuyển khoản trong thời gian này, hệ thống đã tự động kết thúc phiên.';
} else {
    $reason_title = 'Giao dịch thanh toán chưa được hoàn tất';
    $reason_desc  = 'Hệ thống chưa ghi nhận được khoản chuyển khoản hợp lệ hoặc giao dịch đã bị hủy.';
}
?>

<p style="font-size: 15px; color: #0D2A54; line-height: 1.6; margin-top: 0; font-weight: 600;">
    Kính gửi Quý khách <?php echo esc_html($customer_name ?: 'Quý khách'); ?>,
</p>
<p style="font-size: 14px; color: #334155; line-height: 1.7;">
    <strong>Phong Thủy Thiên Tâm</strong> xin thông báo về trạng thái giao dịch thanh toán trực tuyến cho đơn hàng của Quý khách như sau:
</p>

<!-- Warning Status Highlight Box -->
<div style="background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%); border: 1px solid #FCD34D; border-radius: 12px; padding: 18px 20px; margin: 20px 0; text-align: center;">
    <div style="display: inline-block; width: 44px; height: 44px; line-height: 44px; border-radius: 50%; background-color: #D97706; color: #ffffff; font-size: 22px; font-weight: 800; margin-bottom: 8px;">
        !
    </div>
    <div style="font-size: 13px; font-weight: 700; color: #92400E; text-transform: uppercase; letter-spacing: 0.8px;">
        <?php echo esc_html($is_site_disabled ? 'Cổng Thanh Toán Tạm Đóng' : 'Thanh Toán Chưa Hoàn Tất'); ?>
    </div>
    <div style="font-size: 14px; font-weight: 600; color: #B45309; margin-top: 6px; line-height: 1.5; max-width: 480px; margin-left: auto; margin-right: auto;">
        <?php echo esc_html($reason_title); ?>
    </div>
</div>

<!-- Order Summary Box -->
<div style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; margin: 24px 0;">
    <div style="background-color: #0D2A54; color: #D4AF37; padding: 11px 18px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
        Thông Tin Đơn Hàng:
    </div>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="responsive-table" style="font-size: 13px;">
        <tbody>
            <tr>
                <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; width: 170px; font-weight: 600;">Mã đơn hàng:</td>
                <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #0D2A54; font-weight: 800; font-size: 15px; letter-spacing: 0.5px;">
                    <span style="display:inline-block; padding: 2px 8px; background-color: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; border-radius: 6px;">
                        <?php echo esc_html($payment_code); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Dịch vụ quan tâm:</td>
                <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #0D2A54; font-weight: 700;">
                    <?php echo esc_html($display_service); ?>
                </td>
            </tr>
            <tr>
                <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Nguồn tiếp nhận:</td>
                <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #0D2A54;">
                    <?php if (($order_source ?? '') === 'landing'): ?>
                        <span style="display:inline-block; padding: 2px 8px; background-color: #FDF2F8; color: #BE185D; border: 1px solid #FBCFE8; border-radius: 6px; font-weight: 700; font-size: 12px;">
                            🚀 Landing Page (<?php echo esc_html(($landing_slug ?? '') === 'hieu-minh' ? 'Hiểu Mình Để Định Hướng' : ((($landing_slug ?? '') === 'hieu-con-de-dong-hanh') ? 'Hiểu Con Để Đồng Hành' : ($landing_slug ?? ''))); ?>)
                        </span>
                    <?php else: ?>
                        <span style="display:inline-block; padding: 2px 8px; background-color: #F0F9FF; color: #0369A1; border: 1px solid #BAE6FD; border-radius: 6px; font-weight: 600; font-size: 12px;">
                            🌐 Trang dịch vụ trực tuyến
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Số tiền yêu cầu:</td>
                <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #0D2A54; font-weight: 700; font-size: 14px;">
                    <?php echo esc_html($amount_formatted); ?>
                </td>
            </tr>
            <tr>
                <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Trạng thái:</td>
                <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #DC2626; font-weight: 700;">
                    <span style="display:inline-block; padding: 3px 10px; background-color: #FEF2F2; color: #DC2626; border-radius: 6px; font-size: 13px;">
                        Đã hủy phiên thanh toán
                    </span>
                </td>
            </tr>
            <tr>
                <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Lý do:</td>
                <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #475569; line-height: 1.5;">
                    <?php echo esc_html($reason_desc); ?>
                </td>
            </tr>
            <?php if (!empty($customer_phone)) : ?>
                <tr>
                    <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Số điện thoại:</td>
                    <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #174C97; font-weight: 700;">
                        <?php echo esc_html($customer_phone); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Helpful Advice Box -->
<div style="background-color: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 12px; padding: 18px 20px; margin: 22px 0;">
    <h3 style="margin: 0 0 8px; font-size: 14px; font-weight: 700; color: #0D2A54; display: flex; align-items: center; gap: 6px;">
        💡 Quý Khách Đừng Lo Lắng:
    </h3>
    <p style="margin: 0; font-size: 13.5px; color: #475569; line-height: 1.65;">
        Thông tin đăng ký dịch vụ của Quý khách đã được lưu an toàn trong hệ thống của chúng tôi. Chuyên viên tư vấn Thiên Tâm sẽ sớm liên hệ lại để hỗ trợ Quý khách hoặc Quý khách có thể truy cập website để đăng ký lại bất kỳ lúc nào.
    </p>
</div>

<!-- Button Back To Website -->
<div style="text-align: center; margin: 26px 0;">
    <a href="https://thientam68.com" target="_blank" style="display: inline-block; padding: 12px 28px; background: linear-gradient(135deg, #0D2A54 0%, #174C97 100%); color: #ffffff; text-decoration: none; border-radius: 30px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 14px rgba(13, 42, 84, 0.25);">
        Truy Cập Lại Website Thiên Tâm 68 ↗
    </a>
</div>

<!-- Highlight Hotline Box -->
<div style="background-color: #FFFDF5; border: 1px solid #E6CA65; border-radius: 12px; padding: 16px 20px; margin: 22px 0; text-align: center;">
    <p style="margin: 0 0 4px; font-size: 13px; color: #854D0E; font-weight: 700;">
        CẦN HỖ TRỢ THANH TOÁN HOẶC ĐĂNG KÝ TRỰC TIẾP?
    </p>
    <p style="margin: 0; font-size: 13px; color: #475569;">
        Quý khách vui lòng gọi ngay Hotline để được tư vấn viên hỗ trợ tức thì:
        <a href="tel:0935425238" style="color: #0D2A54; font-size: 16px; font-weight: 800; text-decoration: none; display: inline-block; margin-top: 4px;">0935 425 238</a>
    </p>
</div>

<p style="font-size: 14px; color: #334155; line-height: 1.7; margin-bottom: 0;">
    Trân trọng cảm ơn sự quan tâm và đồng hành của Quý khách!<br>
</p>

<?php
include __DIR__ . '/footer.php';
