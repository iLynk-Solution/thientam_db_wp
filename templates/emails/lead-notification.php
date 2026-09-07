<?php

/**
 * Template Email Thông Báo Lead Mới cho Admin / Tư Vấn Viên
 *
 * @var string $form_title
 * @var string $name
 * @var string $phone
 * @var string $email
 * @var string $message
 * @var array<string, mixed> $fields
 * @var string $page_url
 * @var string $admin_url
 */

include __DIR__ . '/header.php';
?>

<!-- Quick Contact Action Card -->
<div style="background: linear-gradient(135deg, #0A1C36 0%, #174C97 100%); border-radius: 14px; padding: 20px 22px; color: #ffffff; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(23, 76, 151, 0.2);">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="vertical-align: middle;">
                <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #D4AF37; font-weight: 700; display: block; margin-bottom: 4px;">Khách hàng yêu cầu:</span>
                <h2 style="margin: 0; font-size: 18px; font-weight: 800; color: #ffffff;"><?php echo esc_html($name ?: 'Khách vãng lai'); ?></h2>
            </td>
            <?php if (!empty($phone)) : ?>
                <td align="right" style="vertical-align: middle;">
                    <a href="tel:<?php echo esc_attr($phone); ?>" class="quick-action-btn" style="display: inline-block; background-color: #D4AF37; color: #0A1C36; padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: 800; text-decoration: none; box-shadow: 0 2px 6px rgba(0,0,0,0.2); white-space: nowrap;">
                        Gọi Ngay
                    </a>
                </td>
            <?php endif; ?>
        </tr>
    </table>
</div>

<p style="font-size: 14px; color: #334155; line-height: 1.6; margin-top: 0;">
    Xin chào Quản trị viên & Đội ngũ tư vấn,<br>
    Khách hàng đã để lại thông tin với các nội dung chi tiết bên dưới:
</p>

<!-- Detail Table -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="responsive-table" style="border-collapse: separate; border-spacing: 0; border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; margin-bottom: 22px; font-size: 13px;">
    <tbody>
        <tr>
            <td class="table-label" style="padding: 12px 16px; background-color: #F8FAFC; border-bottom: 1px solid #E2E8F0; font-weight: 600; width: 170px; color: #475569;">Họ và tên:</td>
            <td class="table-value" style="padding: 12px 16px; background-color: #ffffff; border-bottom: 1px solid #E2E8F0; color: #0D2A54; font-weight: 700; font-size: 14px;"><?php echo esc_html($name ?: '(Chưa nhập)'); ?></td>
        </tr>
        <tr>
            <td class="table-label" style="padding: 12px 16px; background-color: #F8FAFC; border-bottom: 1px solid #E2E8F0; font-weight: 600; color: #475569;">Số điện thoại:</td>
            <td class="table-value" style="padding: 12px 16px; background-color: #ffffff; border-bottom: 1px solid #E2E8F0;">
                <?php if (!empty($phone)) : ?>
                    <a href="tel:<?php echo esc_attr($phone); ?>" style="color: #174C97; font-weight: 700; text-decoration: none; font-size: 14px;"><?php echo esc_html($phone); ?></a>
                <?php else : ?>
                    <span style="color:#94A3B8;">(Chưa nhập)</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td class="table-label" style="padding: 12px 16px; background-color: #F8FAFC; border-bottom: 1px solid #E2E8F0; font-weight: 600; color: #475569;">Email:</td>
            <td class="table-value" style="padding: 12px 16px; background-color: #ffffff; border-bottom: 1px solid #E2E8F0;">
                <?php if (!empty($email)) : ?>
                    <a href="mailto:<?php echo esc_attr($email); ?>" style="color: #174C97; text-decoration: none;"><?php echo esc_html($email); ?></a>
                <?php else : ?>
                    <span style="color:#94A3B8;">(Chưa nhập)</span>
                <?php endif; ?>
            </td>
        </tr>

        <?php if (!empty($fields) && is_array($fields)) : ?>
            <?php foreach ($fields as $k => $v) : ?>
                <?php
                $k_clean = trim((string)$k);
                $k_lower = function_exists('mb_strtolower') ? mb_strtolower($k_clean, 'UTF-8') : strtolower($k_clean);
                if (in_array($k_lower, array('họ và tên', 'họ tên', 'số điện thoại', 'sđt', 'email', 'name', 'phone'))) {
                    continue;
                }
                $val_str = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (string)$v;
                ?>
                <tr>
                    <td class="table-label" style="padding: 12px 16px; background-color: #F8FAFC; border-bottom: 1px solid #E2E8F0; font-weight: 600; color: #475569;"><?php echo esc_html($k); ?>:</td>
                    <td class="table-value" style="padding: 12px 16px; background-color: #ffffff; border-bottom: 1px solid #E2E8F0; color: #0D2A54; font-weight: 600;"><?php echo nl2br(esc_html($val_str)); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($message)) : ?>
            <tr>
                <td class="table-label" style="padding: 12px 16px; background-color: #F8FAFC; border-bottom: 1px solid #E2E8F0; font-weight: 600; color: #475569;">Lời nhắn của khách:</td>
                <td class="table-value" style="padding: 12px 16px; background-color: #FFFDF5; border-bottom: 1px solid #E2E8F0; color: #334155; line-height: 1.6;">
                    <em>“<?php echo nl2br(esc_html($message)); ?>”</em>
                </td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($page_url)) : ?>
            <tr>
                <td class="table-label" style="padding: 12px 16px; background-color: #F8FAFC; border-bottom: 1px solid #E2E8F0; font-weight: 600; color: #475569;">Trang gửi form:</td>
                <td class="table-value" style="padding: 12px 16px; background-color: #ffffff; border-bottom: 1px solid #E2E8F0; font-size: 12px; word-break: break-all;">
                    <a href="<?php echo esc_url($page_url); ?>" target="_blank" style="color: #174C97;"><?php echo esc_html($page_url); ?></a>
                </td>
            </tr>
        <?php endif; ?>

        <tr>
            <td class="table-label" style="padding: 12px 16px; background-color: #F8FAFC; font-weight: 600; color: #475569;">Thời gian gửi:</td>
            <td class="table-value" style="padding: 12px 16px; background-color: #ffffff; color: #64748B;"><?php echo date('H:i:s - d/m/Y'); ?></td>
        </tr>
    </tbody>
</table>

<!-- Action Button -->
<div style="text-align: center; margin: 30px 0 10px;">
    <a href="<?php echo esc_url($admin_url); ?>" target="_blank" class="action-btn-full" style="display: inline-block; background: linear-gradient(135deg, #174C97 0%, #0D2A54 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 30px; font-weight: 800; font-size: 14px; box-shadow: 0 4px 14px rgba(23, 76, 151, 0.3);">
        Xem & Cập Nhật Trạng Thái (WP Admin)
    </a>
</div>

<?php
include __DIR__ . '/footer.php';
