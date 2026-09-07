<?php
/**
 * Frontend Headless Splash Template
 *
 * Hiển thị Logo Thiên Tâm và thông báo chuyển hướng sang website chính: https://thientam68.com/
 *
 * @package HelloElementorChild
 */

if (! defined('ABSPATH')) {
    exit;
}

$logo_url = get_stylesheet_directory_uri() . '/images/Logo_Thien_Tam_Horizontal.png';
$favicon_url = get_stylesheet_directory_uri() . '/images/thien-tam-icon-4x.png';
$main_site_url = 'https://thientam68.com/';
$site_name = get_bloginfo('name') ?: 'Thiên Tâm';
?><!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($site_name); ?> - Hệ thống API & Quản trị</title>
    <link rel="icon" type="image/png" href="<?php echo esc_url($favicon_url); ?>">
    <link rel="apple-touch-icon" href="<?php echo esc_url($favicon_url); ?>">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #07152B 0%, #0B2447 50%, #050E1D 100%);
            color: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            overflow: hidden;
            position: relative;
        }

        /* Hiệu ứng ánh sáng tỏa tròn mờ */
        body::before {
            content: '';
            position: absolute;
            width: min(700px, 95vw);
            height: min(700px, 95vw);
            background: radial-gradient(circle, rgba(212, 175, 55, 0.14) 0%, rgba(11, 36, 71, 0.05) 55%, transparent 70%);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        .splash-card {
            text-align: center;
            padding: 40px 24px;
            position: relative;
            z-index: 1;
            max-width: 580px;
            width: 90%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
            animation: splashFadeIn 0.9s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .logo-img {
            max-width: min(480px, 85vw);
            max-height: 38vh;
            width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
            filter: drop-shadow(0 14px 30px rgba(0, 0, 0, 0.5));
            transition: transform 0.35s ease;
            user-select: none;
        }

        .logo-img:hover {
            transform: scale(1.02);
        }

        .info-box {
            margin-top: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .info-text {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.6;
            letter-spacing: 0.2px;
        }

        .main-link-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 26px;
            border-radius: 50px;
            background: linear-gradient(135deg, #C5A869 0%, #D4AF37 50%, #B89748 100%);
            color: #07152B;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.3);
            transition: all 0.3s ease;
        }

        .main-link-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(212, 175, 55, 0.45);
            background: linear-gradient(135deg, #D4AF37 0%, #E5C358 50%, #C5A869 100%);
        }

        .main-link-btn:active {
            transform: translateY(0);
        }

        .main-link-btn svg {
            width: 16px;
            height: 16px;
            transition: transform 0.3s ease;
        }

        .main-link-btn:hover svg {
            transform: translateX(3px);
        }

        @keyframes splashFadeIn {
            from {
                opacity: 0;
                transform: translateY(16px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 480px) {
            .info-text {
                font-size: 14px;
            }
            .main-link-btn {
                font-size: 13px;
                padding: 10px 22px;
            }
        }
    </style>
</head>
<body>
    <div class="splash-card">
        <a href="<?php echo esc_url($main_site_url); ?>" title="Truy cập website chính Thiên Tâm">
            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>" class="logo-img">
        </a>

        <div class="info-box">
            <p class="info-text">
                Mọi nội dung vui lòng truy cập tại
            </p>
            <a href="<?php echo esc_url($main_site_url); ?>" class="main-link-btn" rel="noopener noreferrer">
                <span>thientam68.com</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>
    </div>
</body>
</html>
