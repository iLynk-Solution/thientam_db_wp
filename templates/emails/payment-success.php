<?php

/**
 * Template Email Xác Nhận Thanh Toán Thành Công - Thiên Tâm 68
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
 * @var string $paid_at
 * @var string $checkout_url
 * @var string $reference_code
 * @var string $transaction_id
 * @var string $customer_notes
 */

include __DIR__ . '/header.php';

$display_service = trim(($service_name ?? '') . (!empty($package_name) ? ' – ' . $package_name : ''));
if (empty($display_service)) {
    $display_service = 'Dịch vụ Phong Thủy Thiên Tâm';
}
?>

<p style="font-size: 15px; color: #0D2A54; line-height: 1.6; margin-top: 0; font-weight: 600;">
    Kính gửi Quý khách <?php echo esc_html($customer_name ?: 'Quý khách'); ?>,
</p>
<p style="font-size: 14px; color: #334155; line-height: 1.7;">
    <strong>Phong Thủy Thiên Tâm</strong> xin trân trọng thông báo giao dịch thanh toán trực tuyến qua <strong>VietQR Napas 24/7</strong> của Quý khách đã được hệ thống ghi nhận <strong>thành công</strong>.
</p>

<!-- Success Status Highlight Box -->
<div style="background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%); border: 1px solid #6EE7B7; border-radius: 12px; padding: 18px 20px; margin: 20px 0; text-align: center;">
    <div style="display: inline-block; width: 44px; height: 44px; line-height: 44px; border-radius: 50%; background-color: #059669; color: #ffffff; font-size: 24px; font-weight: 800; margin-bottom: 8px;">
        ✓
    </div>
    <div style="font-size: 13px; font-weight: 700; color: #065F46; text-transform: uppercase; letter-spacing: 0.8px;">
        Giao Dịch Đã Hoàn Tất Thành Công
    </div>
    <div style="font-size: 26px; font-weight: 900; color: #047857; margin-top: 4px; font-family: 'Segoe UI', Roboto, sans-serif;">
        <?php echo esc_html($amount_formatted ?: 'Đã thanh toán'); ?>
    </div>
</div>

<!-- Order Summary Box -->
<div style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; margin: 24px 0;">
    <div style="background-color: #0D2A54; color: #D4AF37; padding: 11px 18px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
        Chi Tiết Đơn Hàng & Thanh Toán:
    </div>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="responsive-table" style="font-size: 13px;">
        <tbody>
            <tr>
                <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; width: 170px; font-weight: 600;">Mã thanh toán:</td>
                <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #0D2A54; font-weight: 800; font-size: 15px; letter-spacing: 0.5px;">
                    <span style="display:inline-block; padding: 2px 8px; background-color: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; border-radius: 6px;">
                        <?php echo esc_html($payment_code); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Dịch vụ đăng ký:</td>
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
                <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Số tiền đã thanh toán:</td>
                <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #059669; font-weight: 800; font-size: 15px;">
                    <?php echo esc_html($amount_formatted); ?>
                </td>
            </tr>
            <tr>
                <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Thời gian thanh toán:</td>
                <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #334155;">
                    <?php echo esc_html($paid_at ?: date('d/m/Y H:i:s')); ?>
                </td>
            </tr>
            <tr>
                <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Phương thức:</td>
                <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #334155;">
                    VietQR Napas 24/7 (SePay tự động)
                </td>
            </tr>
            <?php if (!empty($reference_code) || !empty($transaction_id)) : ?>
                <tr>
                    <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Mã giao dịch ngân hàng:</td>
                    <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #475569; font-family: monospace;">
                        <?php echo esc_html($reference_code ?: $transaction_id); ?>
                    </td>
                </tr>
            <?php endif; ?>
            <tr>
                <td class="table-label" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 600;">Khách hàng:</td>
                <td class="table-value" style="padding: 12px 18px; border-bottom: 1px solid #E2E8F0; color: #0D2A54; font-weight: 700;">
                    <?php echo esc_html($customer_name ?: '(Chưa nhập)'); ?>
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
            <?php if (!empty($customer_notes)) : ?>
                <tr>
                    <td class="table-label" style="padding: 12px 18px; color: #64748B; font-weight: 600;">Ghi chú thông tin:</td>
                    <td class="table-value" style="padding: 12px 18px; color: #334155; line-height: 1.6; font-style: italic;">
                        “<?php echo nl2br(esc_html($customer_notes)); ?>”
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Next Steps Box -->
<div style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 12px; padding: 18px 20px; margin: 22px 0;">
    <h3 style="margin: 0 0 8px; font-size: 14px; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 6px;">
        Bước Tiếp Theo:
    </h3>
    <p style="margin: 0; font-size: 13.5px; color: #15803D; line-height: 1.65;">
        Hồ sơ đăng ký của Quý khách đã được chuyển trực tiếp đến Ban Chuyên Gia Thiên Tâm. Chuyên viên sẽ chủ động liên hệ với Quý khách qua số điện thoại <strong><?php echo esc_html($customer_phone ?: 'đã đăng ký'); ?></strong> trong thời gian sớm nhất để thống nhất lịch hẹn và bắt đầu triển khai dịch vụ.
    </p>
</div>

<?php if (!empty($checkout_url)) : ?>
    <!-- Button View Order -->
    <div style="text-align: center; margin: 26px 0;">
        <a href="<?php echo esc_url($checkout_url); ?>" target="_blank" style="display: inline-block; padding: 12px 28px; background: linear-gradient(135deg, #0D2A54 0%, #174C97 100%); color: #ffffff; text-decoration: none; border-radius: 30px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 14px rgba(13, 42, 84, 0.25);">
            Tra Cứu Đơn Hàng Trực Tuyến
        </a>
    </div>
<?php endif; ?>

<!-- Highlight Hotline Box -->
<div style="background-color: #FFFDF5; border: 1px solid #E6CA65; border-radius: 12px; padding: 16px 20px; margin: 22px 0; text-align: center;">
    <p style="margin: 0 0 4px; font-size: 13px; color: #854D0E; font-weight: 700;">
        CẦN HỖ TRỢ GẤP HOẶC GIẢI ĐÁP THẮC MẮC?
    </p>
    <p style="margin: 0; font-size: 13px; color: #475569;">
        Tổng đài Chuyên gia Thiên Tâm sẵn sàng đồng hành:
        <a href="tel:0935425238" style="color: #0D2A54; font-size: 16px; font-weight: 800; text-decoration: none; display: inline-block; margin-top: 4px;">0935 425 238</a>
    </p>
</div>

<p style="font-size: 14px; color: #334155; line-height: 1.7; margin-bottom: 0;">
    Thiên Tâm 68 xin chân thành cảm ơn sự tin tưởng và đồng hành của Quý khách!<br>
</p>

<?php
include __DIR__ . '/footer.php';
