<?php

/**
 * Template Email Kiểm Tra Kết Nối SMTP (Test Email)
 *
 * @var string $to_email
 * @var string $host
 * @var string $port
 * @var string $from_email
 */

include __DIR__ . '/header.php';
?>

<p style="font-size: 14px; color: #334155; line-height: 1.6; margin-top: 0;">
    Xin chào Quản trị viên,<br>
    Đây là email kiểm tra gửi từ hệ thống máy chủ của <strong>Thiên Tâm 68</strong> để xác nhận rằng cấu hình SMTP đang hoạt động mượt mà và an toàn.
</p>

<!-- Status Card -->
<div style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-left: 4px solid #10B981; border-radius: 12px; padding: 18px 20px; margin: 20px 0;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 13px;">
        <tr>
            <td style="padding: 4px 0; color: #64748B; width: 140px;">Thời gian gửi:</td>
            <td style="padding: 4px 0; color: #0D2A54; font-weight: 700;"><?php echo date('H:i:s - d/m/Y'); ?></td>
        </tr>
        <tr>
            <td style="padding: 4px 0; color: #64748B;">Máy chủ SMTP:</td>
            <td style="padding: 4px 0; color: #0D2A54; font-family: monospace;"><?php echo esc_html($host); ?> (Port <?php echo esc_html($port); ?>)</td>
        </tr>
        <tr>
            <td style="padding: 4px 0; color: #64748B;">Gửi từ:</td>
            <td style="padding: 4px 0; color: #0D2A54;"><?php echo esc_html($from_email); ?></td>
        </tr>
        <tr>
            <td style="padding: 4px 0; color: #64748B;">Nhận tại:</td>
            <td style="padding: 4px 0; color: #174C97; font-weight: 700;"><?php echo esc_html($to_email); ?></td>
        </tr>
    </table>
</div>

<div style="text-align: center; margin-top: 25px;">
    <a href="https://thientam68.com" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #174C97 0%, #0D2A54 100%); color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 30px; font-weight: 700; font-size: 13px; box-shadow: 0 4px 12px rgba(23, 76, 151, 0.25);">
        Truy Cập Website Thiên Tâm 68
    </a>
</div>

<?php
include __DIR__ . '/footer.php';
