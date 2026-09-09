<?php

/**
 * Header Email Template - Thiên Tâm 68 (Tối ưu responsive hoàn hảo cho điện thoại)
 *
 * @var string $title
 * @var string $badge_text
 * @var string $subtitle
 */

$site_url = 'https://thientam68.com';
$logo_url = 'https://site.thientam68.com/wp-content/themes/thien-tam-data/images/Logo_Thien_Tam_Horizontal.png';
?>
<!DOCTYPE html>
<html lang="vi" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <title><?php echo esc_html($title ?? ''); ?></title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            height: 100% !important;
            width: 100% !important;
            -webkit-text-size-adjust: 100% !important;
            -ms-text-size-adjust: 100% !important;
        }

        table,
        td {
            mso-table-lspace: 0pt !important;
            mso-table-rspace: 0pt !important;
        }

        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }

        /* Mobile Viewport Optimizations */
        @media only screen and (max-width: 600px) {
            .email-outer-td {
                padding: 12px 6px !important;
            }

            .email-container {
                width: 100% !important;
                max-width: 100% !important;
                border-radius: 14px !important;
            }

            .email-header-pad {
                padding: 24px 15px 18px !important;
            }

            .email-title-pad {
                padding: 18px 16px 14px !important;
            }

            .email-title-text {
                font-size: 19px !important;
                line-height: 1.35 !important;
            }

            .email-body-pad {
                padding: 18px 16px 22px !important;
            }

            .email-footer-pad {
                padding: 20px 16px !important;
            }

            .email-logo-img {
                height: 42px !important;
                max-width: 210px !important;
            }

            .responsive-table td {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .responsive-table td.table-label {
                padding-bottom: 4px !important;
                border-bottom: none !important;
                font-weight: 700 !important;
            }

            .responsive-table td.table-value {
                padding-top: 0 !important;
            }

            .quick-action-btn {
                display: block !important;
                margin-top: 12px !important;
                width: 100% !important;
                box-sizing: border-box !important;
                text-align: center !important;
            }

            .action-btn-full {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
                padding: 14px 16px !important;
            }
        }
    </style>
</head>

<body style="margin: 0; padding: 0; background-color: #F1F5F9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F1F5F9; width: 100%;">
        <tr>
            <td align="center" class="email-outer-td" style="padding: 30px 15px;">
                <!-- Main Container Card -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="email-container" style="max-width: 640px; width: 100%; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(15, 50, 96, 0.08); border: 1px solid #E2E8F0;">
                    <!-- Brand Header (Nền trắng tinh tế, Logo căn giữa chuẩn xác) -->
                    <tr>
                        <td align="center" class="email-header-pad" style="background-color: #FFFFFF; padding: 35px 25px 24px; text-align: center; border-bottom: 1px solid #E2E8F0;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 0 auto; text-align: center;">
                                <tr>
                                    <td align="center" style="text-align: center;">
                                        <a href="<?php echo esc_url($site_url); ?>" target="_blank" style="text-decoration: none; display: inline-block; text-align: center;">
                                            <img src="<?php echo esc_url($logo_url); ?>" alt="Thiên Tâm" class="email-logo-img" style="height: 52px; width: auto; max-width: 260px; display: block; margin: 0 auto; border: 0;" />
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="text-align: center;">
                                        <div style="display: inline-block; font-size: 11px; font-weight: 700; color: #854D0E;">
                                            Hiểu Mệnh – Hiểu Người – Hiểu Không Gian Sống
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Title Banner Area -->
                    <tr>
                        <td class="email-title-pad" style="padding: 26px 32px 18px; text-align: center; border-bottom: 1px solid #F1F5F9; background-color: #FFFFFF;">
                            <?php if (!empty($badge_text)) : ?>
                                <span style="display:inline-block; padding: 5px 16px; background: #0D2A54; color: #D4AF37; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">
                                    <?php echo esc_html($badge_text); ?>
                                </span>
                            <?php endif; ?>
                            <h1 class="email-title-text" style="margin: 0; font-size: 22px; font-weight: 800; color: #0D2A54; line-height: 1.35;">
                                <?php echo esc_html($title ?? ''); ?>
                            </h1>
                            <?php if (!empty($subtitle)) : ?>
                                <p style="margin: 8px 0 0; font-size: 13px; color: #64748B; line-height: 1.5;">
                                    <?php echo esc_html($subtitle); ?>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Main Content Body -->
                    <tr>
                        <td class="email-body-pad" style="padding: 25px 32px 30px;">