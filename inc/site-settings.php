<?php

/**
 * Native Site Settings & Localization Management (Cài đặt Website & Nội dung)
 *
 * Cho phép Quản trị viên tùy biến toàn bộ nội dung tĩnh của website:
 * - Tab 1: Chung & Doanh nghiệp (company, meta, header, footer, nav)
 * - Tab 2: Trang Chủ (heroHome, homeService, whyChoose, process, consultingProcess, testimonials, news)
 * - Tab 3: Trang Giới Thiệu (aboutPage)
 * - Tab 4: Trang Dịch Vụ (servicesPage, serviceDetailCommon)
 * - Tab 5: Trang Đào Tạo (trainingPage)
 * - Tab 6: Trang Tuyển Dụng (recruitment)
 * - Tab 7: Liên Hệ & Phụ Trợ (contactPage, notFound)
 *
 * @package HelloElementorChild
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * 1. Lấy toàn bộ Cài đặt Website trực tiếp từ Database (wp_options)
 */
function thientam_get_site_options()
{
    $options = get_option('thientam_site_options', []);
    if (is_array($options) && isset($options['consultingProcess'])) {
        if (isset($options['consultingProcess']['summaryText']) || isset($options['consultingProcess']['contactBtn'])) {
            unset($options['consultingProcess']['summaryText'], $options['consultingProcess']['contactBtn']);
            update_option('thientam_site_options', $options);
        }
    }
    if (is_array($options) && isset($options['process'])) {
        if (isset($options['process']['quote']) || isset($options['process']['quoteAuthor']) || isset($options['process']['quoteRole'])) {
            unset($options['process']['quote'], $options['process']['quoteAuthor'], $options['process']['quoteRole']);
            update_option('thientam_site_options', $options);
        }
        if (isset($options['process']['steps']) && is_array($options['process']['steps'])) {
            $has_detail = false;
            foreach ($options['process']['steps'] as &$st) {
                if (is_array($st) && isset($st['detail'])) {
                    unset($st['detail']);
                    $has_detail = true;
                }
            }
            unset($st);
            if ($has_detail) {
                update_option('thientam_site_options', $options);
            }
        }
    }
    return is_array($options) ? $options : [];
}

/**
 * Deep merge hai mảng lồng nhau khi lưu Form
 */
function thientam_deep_merge_arrays(array $default, array $custom)
{
    $merged = $default;

    foreach ($custom as $key => $value) {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            if (array_keys($value) === range(0, count($value) - 1)) {
                $merged[$key] = $value;
            } else {
                $merged[$key] = thientam_deep_merge_arrays($merged[$key], $value);
            }
        } else {
            $merged[$key] = $value;
        }
    }

    return $merged;
}

/**
 * 3. Lưu Cài đặt Website
 */
function thientam_save_site_options(array $data)
{
    update_option('thientam_site_options', $data);
}

/**
 * 4. Đăng ký Menu Admin
 */
add_action('admin_menu', 'thientam_register_site_settings_menu');
function thientam_register_site_settings_menu()
{
    add_menu_page(
        'Cài đặt Website (Thiên Tâm)',
        'Cài đặt Website',
        'manage_options',
        'thientam-site-settings',
        'thientam_render_site_settings_page',
        'dashicons-admin-generic',
        25
    );
}

/**
 * Đăng ký Scripts & Media Uploader cho trang Cài đặt
 */
add_action('admin_enqueue_scripts', function ($hook) {
    if (strpos($hook, 'thientam-site-settings') !== false) {
        wp_enqueue_media();
    }
});

/**
 * 5. Render Giao diện Quản trị Cài đặt Website
 */
function thientam_render_site_settings_page()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $message = '';
    $status = 'success';

    // Xử lý Lưu form
    if (isset($_POST['thientam_site_settings_nonce']) && wp_verify_nonce($_POST['thientam_site_settings_nonce'], 'thientam_save_site_settings_action')) {
        $posted_settings = $_POST['settings'] ?? [];
        if (is_array($posted_settings)) {
            $current_options = thientam_get_site_options();
            $updated_options = thientam_deep_merge_arrays($current_options, $posted_settings);

            // Xử lý lưu danh sách Marquee Tags khi submit từ Tab Trang Chủ
            if (isset($_POST['is_home_tab'])) {
                if (isset($updated_options['consultingProcess'])) {
                    unset($updated_options['consultingProcess']['summaryText'], $updated_options['consultingProcess']['contactBtn']);
                }
                if (isset($updated_options['process'])) {
                    unset($updated_options['process']['quote'], $updated_options['process']['quoteAuthor'], $updated_options['process']['quoteRole']);
                    if (isset($updated_options['process']['steps']) && is_array($updated_options['process']['steps'])) {
                        foreach ($updated_options['process']['steps'] as &$st) {
                            if (is_array($st)) {
                                unset($st['detail']);
                            }
                        }
                        unset($st);
                    }
                }
                // Xử lý lưu Hero Home (socialProofLabel & avatars CRUD)
                if (isset($_POST['settings']['heroHome'])) {
                    if (isset($_POST['settings']['heroHome']['socialProofLabel'])) {
                        $updated_options['heroHome']['socialProofLabel'] = trim(sanitize_text_field($_POST['settings']['heroHome']['socialProofLabel']));
                    }
                    $raw_avatars = $_POST['settings']['heroHome']['avatars'] ?? [];
                    $clean_avatars = [];
                    if (is_array($raw_avatars)) {
                        foreach ($raw_avatars as $av) {
                            if (! is_array($av)) {
                                continue;
                            }
                            $av_img = trim(sanitize_text_field($av['image'] ?? ''));
                            $av_alt = trim(sanitize_text_field($av['alt'] ?? ''));
                            if ($av_img !== '') {
                                $clean_avatars[] = [
                                    'image' => $av_img,
                                    'alt'   => $av_alt,
                                ];
                            }
                        }
                    }
                    $updated_options['heroHome']['avatars'] = $clean_avatars;
                }

                $raw_marquee = $_POST['settings']['marqueeTags'] ?? [];
                $clean_marquee = [];
                if (is_array($raw_marquee)) {
                    foreach ($raw_marquee as $mt) {
                        $trimmed = trim(sanitize_text_field($mt));
                        if ($trimmed !== '') {
                            $clean_marquee[] = $trimmed;
                        }
                    }
                }
                $updated_options['marqueeTags'] = $clean_marquee;

                // Xử lý lưu 3 Thẻ Lý Do & Số Liệu Thống Kê (whyChoose.items & whyChoose.stats)
                if (isset($_POST['settings']['whyChoose'])) {
                    $raw_wc = $_POST['settings']['whyChoose'];

                    $clean_items = [];
                    $clean_cards = [];
                    if (isset($raw_wc['items']) && is_array($raw_wc['items'])) {
                        foreach ($raw_wc['items'] as $idx => $it) {
                            if (! is_array($it)) {
                                continue;
                            }
                            $title = trim(sanitize_text_field($it['title'] ?? ''));
                            $num = trim(sanitize_text_field($it['num'] ?? $it['step'] ?? ('0' . ($idx + 1))));
                            $desc = trim(sanitize_textarea_field($it['desc'] ?? ''));
                            if ($title !== '' || $desc !== '') {
                                $clean_items[] = [
                                    'title' => $title,
                                    'num'   => $num,
                                    'desc'  => $desc,
                                ];
                                $clean_cards[] = [
                                    'step'  => $num,
                                    'title' => $title,
                                    'desc'  => $desc,
                                ];
                            }
                        }
                    }
                    $updated_options['whyChoose']['items'] = $clean_items;
                    $updated_options['whyChoose']['cards'] = $clean_cards;

                    $clean_stats = [];
                    if (isset($raw_wc['stats']) && is_array($raw_wc['stats'])) {
                        foreach ($raw_wc['stats'] as $st) {
                            if (! is_array($st)) {
                                continue;
                            }
                            $val = trim(sanitize_text_field($st['value'] ?? ''));
                            $lbl = trim(sanitize_text_field($st['label'] ?? ''));
                            if ($val !== '' || $lbl !== '') {
                                $clean_stats[] = [
                                    'value' => $val,
                                    'label' => $lbl,
                                ];
                            }
                        }
                    }
                    $updated_options['whyChoose']['stats'] = $clean_stats;
                }
            }

            // Xử lý lưu danh sách Mạng xã hội khi submit từ Tab Chung & Doanh nghiệp
            if (isset($_POST['is_general_tab'])) {
                $raw_social = $_POST['settings']['footer']['socialLinks'] ?? [];
                $clean_social = [];
                if (is_array($raw_social)) {
                    foreach ($raw_social as $s_item) {
                        if (! is_array($s_item)) {
                            continue;
                        }
                        $name = trim(sanitize_text_field($s_item['name'] ?? ''));
                        $href = trim(sanitize_text_field($s_item['href'] ?? ''));
                        $icon = trim(sanitize_text_field($s_item['icon'] ?? ''));
                        if ($name !== '' || $href !== '') {
                            $clean_social[] = [
                                'name' => $name,
                                'href' => $href,
                                'icon' => $icon ?: '/icons/facebook.svg',
                            ];
                        }
                    }
                }
                $updated_options['footer']['socialLinks'] = $clean_social;
            }

            thientam_save_site_options($updated_options);

            // Tự động kích hoạt Webhook xóa cache Next.js khi lưu Cài đặt Website
            if (function_exists('thientam_trigger_nextjs_revalidation')) {
                thientam_trigger_nextjs_revalidation(['/'], ['site-settings'], 'layout');
            }

            $message = 'Đã lưu tất cả thay đổi thành công! (Đã tự động gửi webhook xóa cache Next.js)';
        }
    }

    $options = thientam_get_site_options();
    $active_tab = sanitize_key($_GET['tab'] ?? 'general');

    $tabs = [
        'general'     => ['label' => '1. Chung & Doanh nghiệp', 'icon' => 'dashicons-building'],
        'home'        => ['label' => '2. Trang Chủ', 'icon' => 'dashicons-admin-home'],
        'about'       => ['label' => '3. Trang Giới Thiệu', 'icon' => 'dashicons-id-alt'],
        'services'    => ['label' => '4. Trang Dịch Vụ', 'icon' => 'dashicons-screenoptions'],
        'training'    => ['label' => '5. Trang Đào Tạo', 'icon' => 'dashicons-welcome-learn-more'],
        'recruitment' => ['label' => '6. Trang Tuyển Dụng', 'icon' => 'dashicons-groups'],
        'contact'     => ['label' => '7. Liên Hệ & Form', 'icon' => 'dashicons-email-alt'],
        'news'        => ['label' => '8. Tin Tức & Bài Viết', 'icon' => 'dashicons-welcome-write-blog'],
        'seo'         => ['label' => '9. SEO & Metadata', 'icon' => 'dashicons-search'],
    ];

?>
    <div class="wrap thientam-settings-wrap">
        <style>
            .thientam-settings-wrap {
                max-width: 1200px;
                margin: 20px 20px 40px 0;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
            }

            .thientam-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                background: #0f172a;
                color: #fff;
                padding: 20px 28px;
                border-radius: 12px;
                margin-bottom: 24px;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            }

            .thientam-header h1 {
                color: #f8fafc;
                margin: 0;
                font-size: 22px;
                font-weight: 700;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .thientam-header p {
                margin: 6px 0 0 0;
                color: #94a3b8;
                font-size: 13px;
            }

            .thientam-header .api-badge {
                background: #d97706;
                color: #fff;
                padding: 4px 10px;
                border-radius: 6px;
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .thientam-nav-tabs {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 24px;
                border-bottom: 2px solid #e2e8f0;
                padding-bottom: 12px;
            }

            .thientam-tab-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 18px;
                border-radius: 8px;
                font-size: 13px;
                font-weight: 600;
                text-decoration: none;
                color: #475569;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                transition: all 0.2s ease;
            }

            .thientam-tab-btn:hover {
                background: #f1f5f9;
                color: #0f172a;
            }

            .thientam-tab-btn.active {
                background: #0f172a;
                color: #fff;
                border-color: #0f172a;
                box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
            }

            .thientam-tab-btn .dashicons {
                font-size: 17px;
                width: 17px;
                height: 17px;
            }

            .thientam-card {
                background: #fff;
                border-radius: 12px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
                padding: 24px;
                margin-bottom: 24px;
            }

            .thientam-card-header {
                border-bottom: 1px solid #f1f5f9;
                padding-bottom: 14px;
                margin-bottom: 20px;
            }

            .thientam-card-header h2 {
                margin: 0;
                font-size: 17px;
                font-weight: 700;
                color: #1e293b;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .thientam-card-header p {
                margin: 4px 0 0 0;
                color: #64748b;
                font-size: 13px;
            }

            .thientam-grid-2 {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }

            .thientam-grid-3 {
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
                gap: 20px;
            }

            .thientam-field {
                margin-bottom: 18px;
            }

            .thientam-field label {
                display: block;
                font-size: 13px;
                font-weight: 600;
                color: #334155;
                margin-bottom: 6px;
            }

            .thientam-field input[type="text"],
            .thientam-field input[type="email"],
            .thientam-field textarea,
            .thientam-field select {
                width: 100%;
                border: 1px solid #cbd5e1;
                border-radius: 6px;
                padding: 9px 12px;
                font-size: 13px;
                transition: border 0.15s;
                background: #fff;
                color: #1e293b;
            }

            .thientam-field input[type="text"]:focus,
            .thientam-field textarea:focus {
                border-color: #d97706;
                outline: none;
                box-shadow: 0 0 0 1px #d97706;
            }

            .thientam-footer-bar {
                position: sticky;
                bottom: 20px;
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                padding: 14px 24px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
                z-index: 100;
                margin-top: 30px;
            }

            .btn-thientam-primary {
                background: #d97706;
                color: #fff;
                border: none;
                padding: 10px 24px;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                text-decoration: none;
                transition: background 0.2s;
            }

            .btn-thientam-primary:hover {
                background: #b45309;
                color: #fff;
            }

            .btn-thientam-danger {
                background: #ef4444;
                color: #fff;
                border: none;
                padding: 8px 16px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 600;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            .btn-thientam-danger:hover {
                background: #dc2626;
                color: #fff;
            }

            .thientam-repeater-item {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 16px;
                margin-bottom: 14px;
            }

            .thientam-repeater-header {
                font-weight: 700;
                color: #0f172a;
                margin-bottom: 12px;
                font-size: 13px;
                border-bottom: 1px dashed #cbd5e1;
                padding-bottom: 8px;
                display: flex;
                justify-content: space-between;
            }

            /* Marquee Tags CRUD Styling */
            .marquee-tags-list {
                display: flex;
                flex-direction: column;
                gap: 8px;
                margin-top: 14px;
                width: 100%;
                box-sizing: border-box;
            }

            .marquee-tag-row {
                display: flex;
                align-items: center;
                gap: 10px;
                background: #f8fafc;
                padding: 8px 12px;
                border-radius: 8px;
                border: 1px solid #e2e8f0;
                width: 100%;
                box-sizing: border-box;
                transition: all 0.2s ease;
            }

            .marquee-tag-row:hover {
                border-color: #cbd5e1;
                background: #ffffff;
                box-shadow: 0 2px 6px -1px rgba(0, 0, 0, 0.05);
            }

            .marquee-tag-badge {
                background: #e2e8f0;
                color: #475569;
                font-size: 12px;
                font-weight: 700;
                width: 28px;
                height: 28px;
                line-height: 28px;
                text-align: center;
                border-radius: 6px;
                flex-shrink: 0;
                display: inline-block;
                box-sizing: border-box;
            }

            .marquee-tag-input {
                flex: 1 !important;
                height: 36px !important;
                line-height: 36px !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 6px !important;
                padding: 0 12px !important;
                font-size: 14px !important;
                color: #1e293b !important;
                background: #ffffff !important;
                box-sizing: border-box !important;
                box-shadow: none !important;
                transition: border-color 0.15s, box-shadow 0.15s;
            }

            .marquee-tag-input:focus {
                border-color: #d97706 !important;
                box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.2) !important;
                outline: none !important;
            }

            .marquee-tag-actions {
                display: flex;
                align-items: center;
                gap: 6px;
                flex-shrink: 0;
            }

            .marquee-action-btn {
                width: 34px !important;
                height: 34px !important;
                min-height: 34px !important;
                max-height: 34px !important;
                padding: 0 !important;
                margin: 0 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                border-radius: 6px !important;
                cursor: pointer !important;
                background: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                color: #64748b !important;
                box-shadow: none !important;
                transition: all 0.15s ease;
                box-sizing: border-box !important;
                flex-shrink: 0 !important;
                line-height: 1 !important;
            }

            .marquee-action-btn:hover {
                background: #f1f5f9 !important;
                color: #0f172a !important;
                border-color: #94a3b8 !important;
            }

            .marquee-action-btn.btn-delete {
                border-color: #fecaca !important;
                color: #ef4444 !important;
                background: #ffffff !important;
            }

            .marquee-action-btn.btn-delete:hover {
                background: #fee2e2 !important;
                color: #dc2626 !important;
                border-color: #f87171 !important;
            }

            .marquee-action-btn .dashicons {
                width: 18px !important;
                height: 18px !important;
                font-size: 18px !important;
                line-height: 18px !important;
                margin: 0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .btn-thientam-add-tag {
                background: #0f172a !important;
                color: #ffffff !important;
                border: none !important;
                padding: 8px 16px !important;
                border-radius: 6px !important;
                font-size: 13px !important;
                font-weight: 600 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px !important;
                cursor: pointer !important;
                text-decoration: none !important;
                transition: all 0.2s ease;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
                line-height: 1.4 !important;
                white-space: nowrap !important;
            }

            .btn-thientam-add-tag:hover {
                background: #d97706 !important;
                color: #ffffff !important;
            }

            .btn-thientam-add-tag .dashicons {
                font-size: 18px !important;
                width: 18px !important;
                height: 18px !important;
                line-height: 18px !important;
                margin: 0 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            /* Social Links CRUD Styling */
            .social-links-list {
                display: flex;
                flex-direction: column;
                gap: 12px;
                margin-top: 14px;
                width: 100%;
                box-sizing: border-box;
            }

            .social-link-row {
                background: #f8fafc;
                padding: 12px 16px;
                border-radius: 8px;
                border: 1px solid #e2e8f0;
                width: 100%;
                box-sizing: border-box;
                transition: all 0.2s ease;
            }

            .social-link-row:hover {
                border-color: #cbd5e1;
                background: #ffffff;
                box-shadow: 0 3px 8px -2px rgba(0, 0, 0, 0.05);
            }

            .social-link-body {
                display: flex;
                align-items: center;
                gap: 12px;
                width: 100%;
                box-sizing: border-box;
            }

            .social-preview-wrap {
                width: 40px;
                height: 40px;
                min-width: 40px;
                border-radius: 50%;
                border: 1px solid #cbd5e1;
                background: #ffffff;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                padding: 4px;
                box-sizing: border-box;
                flex-shrink: 0;
            }

            .social-icon-preview {
                width: 100%;
                height: 100%;
                object-fit: contain;
            }

            .social-link-fields {
                display: grid;
                grid-template-columns: 1fr 1.3fr 1.7fr;
                gap: 12px;
                flex: 1;
                align-items: center;
            }

            .social-field-item {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }

            .social-field-item label {
                font-size: 11px !important;
                font-weight: 600 !important;
                color: #64748b !important;
                margin: 0 !important;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .social-field-item input[type="text"] {
                width: 100% !important;
                height: 36px !important;
                line-height: 36px !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 6px !important;
                padding: 0 10px !important;
                font-size: 13px !important;
                box-sizing: border-box !important;
                background: #ffffff !important;
            }

            .social-field-item input[type="text"]:focus {
                border-color: #d97706 !important;
                box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.2) !important;
                outline: none !important;
            }

            @media (max-width: 900px) {
                .social-link-body {
                    flex-direction: column;
                    align-items: stretch;
                }
            }

            /* HERO AVATARS CRUD REPEATER */
            .hero-avatar-row {
                background: #f8fafc;
                padding: 10px 14px;
                border-radius: 8px;
                border: 1px solid #e2e8f0;
                width: 100%;
                box-sizing: border-box;
                transition: all 0.2s ease;
            }

            .hero-avatar-row:hover {
                border-color: #cbd5e1;
                background: #ffffff;
                box-shadow: 0 3px 8px -2px rgba(0, 0, 0, 0.05);
            }

            .hero-avatar-body {
                display: flex;
                align-items: center;
                gap: 12px;
                width: 100%;
                box-sizing: border-box;
            }

            .hero-avatar-preview-wrap {
                width: 44px;
                height: 44px;
                min-width: 44px;
                border-radius: 50%;
                border: 2px solid #cbd5e1;
                background: #ffffff;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                box-sizing: border-box;
                flex-shrink: 0;
            }

            .hero-avatar-img-preview {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .hero-avatar-fields {
                display: grid;
                grid-template-columns: 1.8fr 1.2fr;
                gap: 12px;
                flex: 1;
                align-items: center;
            }

            .hero-avatar-field-item {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }

            .hero-avatar-field-item label {
                font-size: 11px !important;
                font-weight: 600 !important;
                color: #64748b !important;
                margin: 0 !important;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .hero-avatar-field-item input[type="text"] {
                width: 100% !important;
                height: 36px !important;
                line-height: 36px !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 6px !important;
                padding: 0 10px !important;
                font-size: 13px !important;
                box-sizing: border-box !important;
                background: #ffffff !important;
            }

            .hero-avatar-field-item input[type="text"]:focus {
                border-color: #d97706 !important;
                box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.2) !important;
                outline: none !important;
            }

            @media (max-width: 900px) {
                .hero-avatar-body {
                    flex-direction: column;
                    align-items: stretch;
                }

                .hero-avatar-fields {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <div class="thientam-header">
            <div>
                <h1><span class="dashicons dashicons-admin-settings" style="font-size: 26px;"></span> Cài đặt & Nội dung Website Thiên Tâm</h1>
                <p>Quản lý toàn bộ thông tin công ty, văn bản, tiêu đề và nội dung các trang — Tự động đồng bộ với Next.js Frontend</p>
            </div>
            <div>
                <span class="api-badge">REST API: /thientam/v1/settings</span>
            </div>
        </div>

        <?php if (! empty($message)): ?>
            <div class="notice notice-<?php echo esc_attr($status); ?> is-dismissible" style="padding: 12px 18px; border-radius: 8px; margin-bottom: 20px;">
                <p><strong><?php echo esc_html($message); ?></strong></p>
            </div>
        <?php endif; ?>

        <!-- Nav Tabs -->
        <div class="thientam-nav-tabs">
            <?php foreach ($tabs as $key => $tab): ?>
                <a href="<?php echo esc_url(add_query_arg(['page' => 'thientam-site-settings', 'tab' => $key], admin_url('admin.php'))); ?>"
                    class="thientam-tab-btn <?php echo $active_tab === $key ? 'active' : ''; ?>">
                    <span class="dashicons <?php echo esc_attr($tab['icon']); ?>"></span>
                    <?php echo esc_html($tab['label']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field('thientam_save_site_settings_action', 'thientam_site_settings_nonce'); ?>

            <!-- TAB 1: CHUNG & DOANH NGHIỆP -->
            <?php if ($active_tab === 'general'): ?>
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-building"></span> Thông tin Doanh nghiệp & Pháp lý (`company`)</h2>
                        <p>Hiển thị tại Header, Footer, Trang Giới thiệu và Trang Liên hệ</p>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tên công ty đầy đủ:</label>
                            <input type="text" name="settings[company][name]" value="<?php echo esc_attr($options['company']['name'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tên thương hiệu ngắn (Site Name):</label>
                            <input type="text" name="settings[company][siteName]" value="<?php echo esc_attr($options['company']['siteName'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Slogan chính:</label>
                            <input type="text" name="settings[company][slogan]" value="<?php echo esc_attr($options['company']['slogan'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tagline phụ:</label>
                            <input type="text" name="settings[company][tagline]" value="<?php echo esc_attr($options['company']['tagline'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Hotline tư vấn:</label>
                            <input type="text" name="settings[company][hotline]" value="<?php echo esc_attr($options['company']['hotline'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Email liên hệ:</label>
                            <input type="email" name="settings[company][email]" value="<?php echo esc_attr($options['company']['email'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Mã số thuế (MST):</label>
                            <input type="text" name="settings[company][taxCode]" value="<?php echo esc_attr($options['company']['taxCode'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Người đại diện pháp luật:</label>
                            <input type="text" name="settings[company][representative]" value="<?php echo esc_attr($options['company']['representative'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Ngày bắt đầu hoạt động:</label>
                            <input type="text" name="settings[company][startDate]" value="<?php echo esc_attr($options['company']['startDate'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Giờ mở cửa / Phục vụ:</label>
                            <input type="text" name="settings[company][hours]" value="<?php echo esc_attr($options['company']['hours'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tên quốc tế (International Name):</label>
                            <input type="text" name="settings[company][internationalName]" value="<?php echo esc_attr($options['company']['internationalName'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tên viết tắt (Short Name):</label>
                            <input type="text" name="settings[company][shortName]" value="<?php echo esc_attr($options['company']['shortName'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Địa chỉ văn phòng / Trụ sở:</label>
                        <input type="text" name="settings[company][address]" value="<?php echo esc_attr($options['company']['address'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Địa chỉ đăng ký thuế:</label>
                        <input type="text" name="settings[company][taxAddress]" value="<?php echo esc_attr($options['company']['taxAddress'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Ngành nghề chính:</label>
                        <input type="text" name="settings[company][mainIndustry]" value="<?php echo esc_attr($options['company']['mainIndustry'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả tóm tắt thương hiệu:</label>
                        <textarea rows="3" name="settings[company][desc]"><?php echo esc_textarea($options['company']['desc'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-admin-site-alt3"></span> Cấu hình Meta SEO Toàn Trang (`meta`)</h2>
                    </div>
                    <div class="thientam-field">
                        <label>Meta Title mặc định:</label>
                        <input type="text" name="settings[meta][title]" value="<?php echo esc_attr($options['meta']['title'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Meta Description mặc định:</label>
                        <textarea rows="3" name="settings[meta][description]"><?php echo esc_textarea($options['meta']['description'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-embed-generic"></span> Header & Topbar CTA (`header`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Nhãn nút CTA Đặt lịch (Header):</label>
                            <input type="text" name="settings[header][ctaButton]" value="<?php echo esc_attr($options['header']['ctaButton'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn Hotline:</label>
                            <input type="text" name="settings[header][hotlineLabel]" value="<?php echo esc_attr($options['header']['hotlineLabel'] ?? ''); ?>" />
                        </div>
                    </div>
                </div>

                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-admin-generic"></span> Biểu ngữ CTA & Bản quyền Chân Trang (`footer`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề CTA Footer (Phần 1):</label>
                            <input type="text" name="settings[footer][ctaBanner][titlePrefix]" value="<?php echo esc_attr($options['footer']['ctaBanner']['titlePrefix'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề CTA Footer (Nổi bật):</label>
                            <input type="text" name="settings[footer][ctaBanner][titleHighlight]" value="<?php echo esc_attr($options['footer']['ctaBanner']['titleHighlight'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả CTA Footer:</label>
                        <input type="text" name="settings[footer][ctaBanner][subtitle]" value="<?php echo esc_attr($options['footer']['ctaBanner']['subtitle'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Nhãn nút CTA Footer:</label>
                        <input type="text" name="settings[footer][ctaBanner][button]" value="<?php echo esc_attr($options['footer']['ctaBanner']['button'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả chân trang (Description):</label>
                        <textarea rows="3" name="settings[footer][description]"><?php echo esc_textarea($options['footer']['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="thientam-field">
                        <label>Văn bản Copyright:</label>
                        <input type="text" name="settings[footer][copyright]" value="<?php echo esc_attr($options['footer']['copyright'] ?? ''); ?>" />
                    </div>
                </div>

                <!-- LIÊN KẾT MẠNG XÃ HỘI CHÂN TRANG (SOCIAL LINKS CRUD) -->
                <div class="thientam-card" id="social-links-section">
                    <input type="hidden" name="is_general_tab" value="1" />
                    <div class="thientam-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h2><span class="dashicons dashicons-share"></span> Các Mạng Xã Hội Chân Trang (`footer.socialLinks`)</h2>
                            <p>Quản lý các nút biểu tượng mạng xã hội (Facebook, Zalo, YouTube, Messenger, TikTok...) ở chân trang. Bạn có thể chọn icon từ Thư viện Media hoặc nhập đường dẫn SVG.</p>
                        </div>
                        <button type="button" class="btn-thientam-add-tag" id="btn-add-social-link">
                            <span class="dashicons dashicons-plus-alt2"></span> Thêm mạng xã hội
                        </button>
                    </div>

                    <?php
                    $raw_social = $options['footer']['socialLinks'] ?? [
                        [
                            'name' => 'Facebook Thiên Tâm',
                            'href' => 'https://facebook.com',
                            'icon' => '/icons/facebook.svg',
                        ],
                        [
                            'name' => 'Zalo Thiên Tâm',
                            'href' => 'https://zalo.me',
                            'icon' => '/icons/zalo.svg',
                        ],
                        [
                            'name' => 'YouTube Thiên Tâm',
                            'href' => 'https://youtube.com',
                            'icon' => '/icons/youtube.svg',
                        ],
                        [
                            'name' => 'Messenger Thiên Tâm',
                            'href' => 'https://m.me',
                            'icon' => '/icons/messenger.svg',
                        ],
                    ];
                    if (! is_array($raw_social)) {
                        $raw_social = [];
                    }
                    ?>

                    <div id="social-links-list" class="social-links-list">
                        <?php foreach ($raw_social as $sidx => $sitem):
                            $sname = $sitem['name'] ?? '';
                            $shref = $sitem['href'] ?? '';
                            $sicon = $sitem['icon'] ?? '/icons/facebook.svg';
                        ?>
                            <div class="social-link-row">
                                <div class="social-link-body">
                                    <span class="marquee-tag-badge social-index"><?php echo esc_html($sidx + 1); ?></span>
                                    <div class="social-preview-wrap">
                                        <img src="<?php echo esc_url($sicon); ?>" class="social-icon-preview" alt="Preview" onerror="this.src='/icons/facebook.svg';" />
                                    </div>
                                    <div class="social-link-fields">
                                        <div class="social-field-item">
                                            <label>Tên mạng xã hội:</label>
                                            <input type="text" name="settings[footer][socialLinks][<?php echo esc_attr($sidx); ?>][name]" value="<?php echo esc_attr($sname); ?>" placeholder="Ví dụ: Facebook Thiên Tâm" class="social-input-name" />
                                        </div>
                                        <div class="social-field-item">
                                            <label>Đường dẫn liên kết (URL):</label>
                                            <input type="text" name="settings[footer][socialLinks][<?php echo esc_attr($sidx); ?>][href]" value="<?php echo esc_attr($shref); ?>" placeholder="https://facebook.com/..." class="social-input-href" />
                                        </div>
                                        <div class="social-field-item">
                                            <label>Biểu tượng / Icon:</label>
                                            <div style="display: flex; gap: 8px;">
                                                <input type="text" id="social_icon_input_<?php echo esc_attr($sidx); ?>" name="settings[footer][socialLinks][<?php echo esc_attr($sidx); ?>][icon]" value="<?php echo esc_attr($sicon); ?>" placeholder="/icons/facebook.svg hoặc URL..." class="social-input-icon" />
                                                <button type="button" class="button thientam-media-btn" data-target="#social_icon_input_<?php echo esc_attr($sidx); ?>">Chọn ảnh</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="marquee-tag-actions">
                                        <button type="button" class="marquee-action-btn btn-move-social-up" title="Di chuyển lên">
                                            <span class="dashicons dashicons-arrow-up-alt2"></span>
                                        </button>
                                        <button type="button" class="marquee-action-btn btn-move-social-down" title="Di chuyển xuống">
                                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                                        </button>
                                        <button type="button" class="marquee-action-btn btn-delete btn-delete-social" title="Xóa mạng xã hội">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="social-links-empty" style="<?php echo empty($raw_social) ? 'display: block;' : 'display: none;'; ?> text-align: center; padding: 26px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1; margin-top: 14px;">
                        <p style="color: #64748b; margin-bottom: 12px; font-size: 13px;">Chưa có liên kết mạng xã hội nào trong danh sách.</p>
                        <button type="button" class="button button-secondary" id="btn-reset-default-social">
                            <span class="dashicons dashicons-update" style="vertical-align: middle; margin-top: -2px;"></span> Khôi phục 4 mạng xã hội mặc định
                        </button>
                    </div>
                </div>

                <!-- CẤU HÌNH WEBHOOK XÓA CACHE NEXT.JS (ON-DEMAND ISR) -->
                <div class="thientam-card" style="border-left: 4px solid #d97706;">
                    <div class="thientam-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h2><span class="dashicons dashicons-update"></span> Cơ chế Tự động Xóa Cache Next.js (On-Demand ISR Webhook)</h2>
                            <p>Khi bật tính năng này, mỗi khi bạn Lưu bài viết hoặc thay đổi nội dung, WordPress sẽ tự động gửi lệnh xóa cache sang Next.js để trang hiển thị nội dung mới ngay lập tức mà web vẫn tải siêu tốc.</p>
                        </div>
                        <button type="button" class="btn-thientam-primary" id="btn-purge-nextjs-cache">
                            <span class="dashicons dashicons-update"></span> Xóa toàn bộ Cache Next.js ngay
                        </button>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Next.js Webhook URL:</label>
                            <input type="text" name="settings[webhook][url]" value="<?php echo esc_attr($options['webhook']['url'] ?? 'https://thientam68.com/api/revalidate'); ?>" placeholder="https://thientam68.com/api/revalidate" />
                            <small style="color: #64748b; font-size: 12px;">Endpoint nhận lệnh revalidation trên máy chủ Next.js</small>
                        </div>
                        <div class="thientam-field">
                            <label>Secret Token Bảo Mật (REVALIDATION_SECRET):</label>
                            <input type="text" name="settings[webhook][secret]" value="<?php echo esc_attr($options['webhook']['secret'] ?? 'thientam_revalidate_secret_2026'); ?>" />
                            <small style="color: #64748b; font-size: 12px;">Phải khớp với mã REVALIDATION_SECRET trong file .env trên Next.js</small>
                        </div>
                    </div>
                    <div class="thientam-field" style="margin-bottom: 0;">
                        <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: normal; cursor: pointer;">
                            <input type="checkbox" name="settings[webhook][enabled]" value="1" <?php checked($options['webhook']['enabled'] ?? '1', '1'); ?> />
                            <strong>Kích hoạt tính năng Webhook tự động xóa cache khi Lưu bài / Cài đặt</strong>
                        </label>
                    </div>
                    <div id="purge-cache-status" style="display: none; margin-top: 14px; padding: 10px 14px; border-radius: 6px; font-size: 13px;"></div>
                </div>
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-menu-alt3"></span> Menu Điều Hướng Chính (`nav`)</h2>
                        <p>Nhãn văn bản của 6 mục menu điều hướng chính trên thanh Header</p>
                    </div>
                    <div class="thientam-grid-3">
                        <?php
                        $default_nav_labels = ["Trang chủ", "Giới thiệu", "Dịch vụ", "Tin tức", "Đào tạo", "Liên hệ"];
                        $nav_items = $options["nav"] ?? [];
                        for ($ni = 0; $ni < 6; $ni++):
                            $nval = $nav_items[$ni]["label"] ?? ($default_nav_labels[$ni] ?? "");
                        ?>
                            <div class="thientam-field">
                                <label>Mục <?php echo ($ni + 1); ?>:</label>
                                <input type="text" name="settings[nav][<?php echo $ni; ?>][label]" value="<?php echo esc_attr($nval); ?>" />
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-editor-ul"></span> Nhãn Menu Thả Xuống & Header (`header`)</h2>
                        <p>Các nhãn phụ hiển thị trên menu thả xuống Dịch vụ & Đào tạo trên Header</p>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề danh mục dịch vụ:</label>
                            <input type="text" name="settings[header][servicesSubTitle]" value="<?php echo esc_attr($options["header"]["servicesSubTitle"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn nút xem tất cả dịch vụ:</label>
                            <input type="text" name="settings[header][servicesAllLabel]" value="<?php echo esc_attr($options["header"]["servicesAllLabel"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề chương trình đào tạo:</label>
                            <input type="text" name="settings[header][trainingSubTitle]" value="<?php echo esc_attr($options["header"]["trainingSubTitle"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn nút xem tất cả khóa học:</label>
                            <input type="text" name="settings[header][trainingAllLabel]" value="<?php echo esc_attr($options["header"]["trainingAllLabel"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn địa chỉ văn phòng:</label>
                            <input type="text" name="settings[header][addressLabel]" value="<?php echo esc_attr($options["header"]["addressLabel"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn giờ mở cửa:</label>
                            <input type="text" name="settings[header][hoursLabel]" value="<?php echo esc_attr($options["header"]["hoursLabel"] ?? ""); ?>" />
                        </div>
                    </div>
                </div>


                <!-- TAB 2: TRANG CHỦ -->
            <?php elseif ($active_tab === 'home'): ?>
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-megaphone"></span> Hero Banner Trang Chủ (`heroHome`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tagline đầu trang:</label>
                            <input type="text" name="settings[heroHome][tagline]" value="<?php echo esc_attr($options['heroHome']['tagline'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề dòng 1:</label>
                            <input type="text" name="settings[heroHome][titleLine1]" value="<?php echo esc_attr($options['heroHome']['titleLine1'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề dòng 2:</label>
                            <input type="text" name="settings[heroHome][titleLine2]" value="<?php echo esc_attr($options['heroHome']['titleLine2'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Mô tả ngắn Hero:</label>
                            <textarea rows="3" name="settings[heroHome][description]"><?php echo esc_textarea($options['heroHome']['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Nút CTA chính (Primary):</label>
                            <input type="text" name="settings[heroHome][ctaPrimary]" value="<?php echo esc_attr($options['heroHome']['ctaPrimary'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nút CTA phụ (Secondary):</label>
                            <input type="text" name="settings[heroHome][ctaSecondary]" value="<?php echo esc_attr($options['heroHome']['ctaSecondary'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Số lượng khách hàng tin tưởng (Social Proof Count):</label>
                            <input type="text" name="settings[heroHome][socialProofCount]" value="<?php echo esc_attr($options['heroHome']['socialProofCount'] ?? ''); ?>" placeholder="Ví dụ: 1000+ khách hàng" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn phụ khách hàng tin tưởng (Social Proof Label):</label>
                            <input type="text" name="settings[heroHome][socialProofLabel]" value="<?php echo esc_attr($options['heroHome']['socialProofLabel'] ?? ''); ?>" placeholder="Ví dụ: đã tin tưởng và hài lòng" />
                        </div>
                    </div>

                    <!-- HERO AVATARS CRUD -->
                    <div style="margin-top: 24px; padding-top: 20px; border-top: 1px dashed #cbd5e1;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 8px;">
                            <div>
                                <label style="font-weight: 700; font-size: 14px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                    <span class="dashicons dashicons-groups"></span> Danh sách ảnh đại diện khách hàng (Social Proof Avatars)
                                </label>
                                <p style="margin: 2px 0 0 0; font-size: 12px; color: #64748b;">Ảnh đại diện hình tròn xếp chồng hiển thị cạnh số lượng khách hàng ở góc trái banner.</p>
                            </div>
                            <button type="button" class="button button-primary" id="btn-add-hero-avatar">
                                <span class="dashicons dashicons-plus-alt2" style="margin-top: 3px;"></span> Thêm ảnh đại diện
                            </button>
                        </div>

                        <div id="hero-avatars-list" style="display: flex; flex-direction: column; gap: 10px;">
                            <?php
                            $avatars = $options['heroHome']['avatars'] ?? [];
                            if (!is_array($avatars)) {
                                $avatars = [];
                            }
                            ?>
                            <div id="hero-avatars-empty" style="<?php echo empty($avatars) ? '' : 'display: none;'; ?> padding: 16px; text-align: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; color: #64748b; font-size: 13px;">
                                Chưa có ảnh đại diện khách hàng nào. Nhấn <strong>"Thêm ảnh đại diện"</strong> để tải ảnh lên.
                            </div>
                            <?php foreach ($avatars as $av_idx => $av): ?>
                                <?php
                                $av_img = is_array($av) ? ($av['image'] ?? '') : (is_string($av) ? $av : '');
                                $av_alt = is_array($av) ? ($av['alt'] ?? '') : '';
                                $av_id = 'hero_avatar_input_' . $av_idx;
                                ?>
                                <div class="hero-avatar-row">
                                    <div class="hero-avatar-body">
                                        <span class="marquee-tag-badge hero-avatar-index"><?php echo ($av_idx + 1); ?></span>
                                        <div class="hero-avatar-preview-wrap" style="width: 44px; height: 44px; min-width: 44px; max-width: 44px; max-height: 44px; border-radius: 50%; overflow: hidden; border: 2px solid #cbd5e1; background: #ffffff; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                            <img src="<?php echo esc_url($av_img ?: '/images/thien-tam-icon-4x.png'); ?>" class="hero-avatar-img-preview" style="width: 100%; height: 100%; object-fit: cover; display: block;" alt="Preview" />
                                        </div>
                                        <div class="hero-avatar-fields">
                                            <div class="hero-avatar-field-item">
                                                <label>Đường dẫn hình ảnh (URL):</label>
                                                <div style="display: flex; gap: 6px;">
                                                    <input type="text" id="<?php echo esc_attr($av_id); ?>" name="settings[heroHome][avatars][<?php echo $av_idx; ?>][image]" value="<?php echo esc_attr($av_img); ?>" placeholder="https://... hoặc chọn từ thư viện" class="hero-avatar-input-img" />
                                                    <button type="button" class="button thientam-media-btn" data-target="#<?php echo esc_attr($av_id); ?>">Chọn ảnh</button>
                                                </div>
                                            </div>
                                            <div class="hero-avatar-field-item">
                                                <label>Tên / Mô tả (Alt):</label>
                                                <input type="text" name="settings[heroHome][avatars][<?php echo $av_idx; ?>][alt]" value="<?php echo esc_attr($av_alt); ?>" placeholder="Ví dụ: Khách hàng Doanh nghiệp" class="hero-avatar-input-alt" />
                                            </div>
                                        </div>
                                        <div class="marquee-tag-actions">
                                            <button type="button" class="marquee-action-btn btn-move-avatar-up" title="Di chuyển lên">
                                                <span class="dashicons dashicons-arrow-up-alt2"></span>
                                            </button>
                                            <button type="button" class="marquee-action-btn btn-move-avatar-down" title="Di chuyển xuống">
                                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                                            </button>
                                            <button type="button" class="marquee-action-btn btn-delete btn-delete-avatar" title="Xóa ảnh">
                                                <span class="dashicons dashicons-trash"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- DẢI CHỮ CHẠY NGANG TRANG CHỦ (MARQUEE TAGS CRUD) -->
                <div class="thientam-card" id="marquee-tags-section">
                    <input type="hidden" name="is_home_tab" value="1" />
                    <div class="thientam-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h2><span class="dashicons dashicons-tag"></span> Dải chữ chạy ngang Trang Chủ (`marqueeTags`)</h2>
                            <p>Quản lý các thẻ từ khóa chạy ngang dưới banner trang chủ (Thêm mới, Sửa nội dung, Xóa và Đổi thứ tự hiển thị).</p>
                        </div>
                        <button type="button" class="btn-thientam-add-tag" id="btn-add-marquee-tag">
                            <span class="dashicons dashicons-plus-alt2"></span> Thêm thẻ mới
                        </button>
                    </div>

                    <?php
                    $raw_marquee = $options['marqueeTags'] ?? [
                        "Phong thủy nhà ở",
                        "Tử vi bát tự",
                        "Phong thủy doanh nghiệp",
                        "Phong thủy số",
                        "Gia đạo bình an",
                        "Bản mệnh cát tường",
                        "Minh định tương lai",
                        "An trú hiện tại"
                    ];
                    if (!is_array($raw_marquee)) {
                        $raw_marquee = [];
                    }
                    ?>

                    <div id="marquee-tags-list" class="marquee-tags-list">
                        <?php foreach ($raw_marquee as $idx => $tag): ?>
                            <div class="marquee-tag-row">
                                <span class="marquee-tag-badge tag-index"><?php echo esc_html($idx + 1); ?></span>
                                <input type="text" name="settings[marqueeTags][]" value="<?php echo esc_attr($tag); ?>" placeholder="Nhập nội dung thẻ từ khóa..." class="marquee-tag-input" />
                                <div class="marquee-tag-actions">
                                    <button type="button" class="marquee-action-btn btn-move-tag-up" title="Di chuyển lên">
                                        <span class="dashicons dashicons-arrow-up-alt2"></span>
                                    </button>
                                    <button type="button" class="marquee-action-btn btn-move-tag-down" title="Di chuyển xuống">
                                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                                    </button>
                                    <button type="button" class="marquee-action-btn btn-delete btn-delete-tag" title="Xóa thẻ">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="marquee-tags-empty" style="<?php echo empty($raw_marquee) ? 'display: block;' : 'display: none;'; ?> text-align: center; padding: 26px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1; margin-top: 14px;">
                        <p style="color: #64748b; margin-bottom: 12px; font-size: 13px;">Chưa có thẻ từ khóa nào trong danh sách.</p>
                        <button type="button" class="button button-secondary" id="btn-reset-default-tags">
                            <span class="dashicons dashicons-update" style="vertical-align: middle; margin-top: -2px;"></span> Khôi phục 8 thẻ mặc định
                        </button>
                    </div>
                </div>

                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-star-filled"></span> Khối Dịch vụ Trọng tâm (`homeService`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề (Phần 1):</label>
                            <input type="text" name="settings[homeService][titlePrefix]" value="<?php echo esc_attr($options['homeService']['titlePrefix'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề (Nổi bật):</label>
                            <input type="text" name="settings[homeService][titleHighlight]" value="<?php echo esc_attr($options['homeService']['titleHighlight'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả ngắn dịch vụ:</label>
                        <textarea rows="2" name="settings[homeService][desc]"><?php echo esc_textarea($options['homeService']['desc'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-awards"></span> Vì sao chọn Thiên Tâm (`whyChoose`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề (Phần 1):</label>
                            <input type="text" name="settings[whyChoose][titlePrefix]" value="<?php echo esc_attr($options['whyChoose']['titlePrefix'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề (Nổi bật):</label>
                            <input type="text" name="settings[whyChoose][titleHighlight]" value="<?php echo esc_attr($options['whyChoose']['titleHighlight'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Triết lý kim chỉ nam (Title):</label>
                        <input type="text" name="settings[whyChoose][philosophyTitle]" value="<?php echo esc_attr($options['whyChoose']['philosophyTitle'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả triết lý kim chỉ nam:</label>
                        <textarea rows="2" name="settings[whyChoose][philosophyDesc]"><?php echo esc_textarea($options['whyChoose']['philosophyDesc'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-format-chat"></span> Header Khối Cảm Nhận & Tin Tức Trang Chủ</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Cảm nhận - Tiêu đề (Phần 1):</label>
                            <input type="text" name="settings[testimonials][titlePrefix]" value="<?php echo esc_attr($options['testimonials']['titlePrefix'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Cảm nhận - Tiêu đề (Nổi bật):</label>
                            <input type="text" name="settings[testimonials][titleHighlight]" value="<?php echo esc_attr($options['testimonials']['titleHighlight'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tin tức - Tiêu đề (Phần 1):</label>
                            <input type="text" name="settings[news][titlePrefix]" value="<?php echo esc_attr($options['news']['titlePrefix'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tin tức - Tiêu đề (Nổi bật):</label>
                            <input type="text" name="settings[news][titleHighlight]" value="<?php echo esc_attr($options['news']['titleHighlight'] ?? ''); ?>" />
                        </div>
                    </div>
                </div>
                <!-- QUY TRÌNH 4 BƯỚC TỔNG QUAN -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-networking"></span> Quy Trình 4 Bước Tổng Quan (`process`)</h2>
                        <p>Khối quy trình đồng hành tổng quan trên trang chủ</p>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề (Phần 1):</label>
                            <input type="text" name="settings[process][titlePrefix]" value="<?php echo esc_attr($options["process"]["titlePrefix"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề (Nổi bật):</label>
                            <input type="text" name="settings[process][titleHighlight]" value="<?php echo esc_attr($options["process"]["titleHighlight"] ?? ""); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả quy trình:</label>
                        <textarea rows="2" name="settings[process][desc]"><?php echo esc_textarea($options["process"]["desc"] ?? ""); ?></textarea>
                    </div>
                    <h3 style="font-size:14px; margin: 16px 0 10px; color:#1e293b;">4 Bước Quy Trình:</h3>
                    <?php
                    $process_steps = $options["process"]["steps"] ?? [];
                    for ($psi = 0; $psi < 4; $psi++):
                        $ps = $process_steps[$psi] ?? [];
                    ?>
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:12px;">
                            <div class="thientam-grid-2">
                                <div class="thientam-field" style="margin-bottom:8px;">
                                    <label>Bước <?php echo ($psi + 1); ?> - Tiêu đề:</label>
                                    <input type="text" name="settings[process][steps][<?php echo $psi; ?>][title]" value="<?php echo esc_attr($ps["title"] ?? ""); ?>" />
                                </div>
                                <div class="thientam-field" style="margin-bottom:8px;">
                                    <label>Số hiệu bước:</label>
                                    <input type="text" name="settings[process][steps][<?php echo $psi; ?>][step]" value="<?php echo esc_attr($ps["step"] ?? ("0" . ($psi + 1))); ?>" />
                                </div>
                            </div>
                            <div class="thientam-field" style="margin-bottom:0;">
                                <label>Mô tả tóm tắt:</label>
                                <input type="text" name="settings[process][steps][<?php echo $psi; ?>][desc]" value="<?php echo esc_attr($ps["desc"] ?? ""); ?>" />
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- QUY TRÌNH TƯ VẤN 5 BƯỚC -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-randomize"></span> Quy Trình Tư Vấn 5 Bước (`consultingProcess`)</h2>
                        <p>Khối quy trình tư vấn chi tiết 5 bước trên trang chủ</p>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề (Phần 1):</label>
                            <input type="text" name="settings[consultingProcess][titlePrefix]" value="<?php echo esc_attr($options["consultingProcess"]["titlePrefix"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề (Nổi bật):</label>
                            <input type="text" name="settings[consultingProcess][titleHighlight]" value="<?php echo esc_attr($options["consultingProcess"]["titleHighlight"] ?? ""); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả quy trình:</label>
                        <textarea rows="2" name="settings[consultingProcess][desc]"><?php echo esc_textarea($options["consultingProcess"]["desc"] ?? ""); ?></textarea>
                    </div>
                    <h3 style="font-size:14px; margin: 16px 0 10px; color:#1e293b;">5 Bước Tư Vấn Chi Tiết:</h3>
                    <?php
                    $cp_steps = $options["consultingProcess"]["steps"] ?? [];
                    for ($cpi = 0; $cpi < 5; $cpi++):
                        $cps = $cp_steps[$cpi] ?? [];
                    ?>
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:12px;">
                            <div class="thientam-grid-2">
                                <div class="thientam-field" style="margin-bottom:8px;">
                                    <label>Bước <?php echo ($cpi + 1); ?> - Tiêu đề:</label>
                                    <input type="text" name="settings[consultingProcess][steps][<?php echo $cpi; ?>][title]" value="<?php echo esc_attr($cps["title"] ?? ""); ?>" />
                                </div>
                                <div class="thientam-field" style="margin-bottom:8px;">
                                    <label>Số hiệu bước:</label>
                                    <input type="text" name="settings[consultingProcess][steps][<?php echo $cpi; ?>][step]" value="<?php echo esc_attr($cps["step"] ?? ("0" . ($cpi + 1))); ?>" />
                                </div>
                            </div>
                            <div class="thientam-grid-2">
                                <div class="thientam-field" style="margin-bottom:0;">
                                    <label>Mô tả bước:</label>
                                    <input type="text" name="settings[consultingProcess][steps][<?php echo $cpi; ?>][desc]" value="<?php echo esc_attr($cps["desc"] ?? ""); ?>" />
                                </div>
                                <div class="thientam-field" style="margin-bottom:0;">
                                    <label>Chi tiết bổ sung:</label>
                                    <input type="text" name="settings[consultingProcess][steps][<?php echo $cpi; ?>][detail]" value="<?php echo esc_attr($cps["detail"] ?? ""); ?>" />
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- 3 THẺ VÌ SAO CHỌN THIÊN TÂM & CHỈ SỐ -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-grid-view"></span> 3 Thẻ Lý Do & Số Liệu Thống Kê (`whyChoose.items` & `whyChoose.stats`)</h2>
                        <p>3 cột nổi bật và các con số ấn tượng trong phần Vì sao chọn Thiên Tâm</p>
                    </div>
                    <h3 style="font-size:14px; margin: 0 0 10px; color:#1e293b;">3 Thẻ Lý Do:</h3>
                    <?php
                    $wc_items = $options["whyChoose"]["items"] ?? [];
                    $wc_cards = $options["whyChoose"]["cards"] ?? [];
                    for ($wci = 0; $wci < 3; $wci++):
                        $wci_data = $wc_items[$wci] ?? [];
                        $wc_card_data = $wc_cards[$wci] ?? [];
                        $val_title = !empty($wci_data["title"]) ? $wci_data["title"] : ($wc_card_data["title"] ?? "");
                        $val_num   = !empty($wci_data["num"]) ? $wci_data["num"] : ($wc_card_data["step"] ?? ("0" . ($wci + 1)));
                        $val_desc  = !empty($wci_data["desc"]) ? $wci_data["desc"] : ($wc_card_data["desc"] ?? "");
                    ?>
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:12px;">
                            <div class="thientam-grid-2">
                                <div class="thientam-field" style="margin-bottom:8px;">
                                    <label>Thẻ <?php echo ($wci + 1); ?> - Tiêu đề:</label>
                                    <input type="text" name="settings[whyChoose][items][<?php echo $wci; ?>][title]" value="<?php echo esc_attr($val_title); ?>" />
                                </div>
                                <div class="thientam-field" style="margin-bottom:8px;">
                                    <label>Số thứ tự thẻ:</label>
                                    <input type="text" name="settings[whyChoose][items][<?php echo $wci; ?>][num]" value="<?php echo esc_attr($val_num); ?>" />
                                </div>
                            </div>
                            <div class="thientam-field" style="margin-bottom:0;">
                                <label>Nội dung mô tả:</label>
                                <textarea rows="2" name="settings[whyChoose][items][<?php echo $wci; ?>][desc]"><?php echo esc_textarea($val_desc); ?></textarea>
                            </div>
                        </div>
                    <?php endfor; ?>

                    <h3 style="font-size:14px; margin: 16px 0 10px; color:#1e293b;">Số Liệu Thống Kê (`whyChoose.stats`):</h3>
                    <div class="thientam-grid-3">
                        <?php
                        $wc_stats = $options["whyChoose"]["stats"] ?? [];
                        for ($wsi = 0; $wsi < 3; $wsi++):
                            $ws = $wc_stats[$wsi] ?? [];
                        ?>
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px;">
                                <div class="thientam-field" style="margin-bottom:8px;">
                                    <label>Chỉ số <?php echo ($wsi + 1); ?> (Giá trị):</label>
                                    <input type="text" name="settings[whyChoose][stats][<?php echo $wsi; ?>][value]" value="<?php echo esc_attr($ws["value"] ?? ""); ?>" placeholder="VD: 1000+" />
                                </div>
                                <div class="thientam-field" style="margin-bottom:0;">
                                    <label>Nhãn mô tả:</label>
                                    <input type="text" name="settings[whyChoose][stats][<?php echo $wsi; ?>][label]" value="<?php echo esc_attr($ws["label"] ?? ""); ?>" placeholder="VD: Khách hàng tin chọn" />
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>


                <!-- TAB 3: TRANG GIỚI THIỆU -->
            <?php elseif ($active_tab === 'about'): ?>
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-id-alt"></span> Nội dung Trang Giới Thiệu (`aboutPage`)</h2>
                    </div>
                    <div class="thientam-field">
                        <label>Tiêu đề trang:</label>
                        <input type="text" name="settings[aboutPage][title]" value="<?php echo esc_attr($options['aboutPage']['title'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả giới thiệu chung:</label>
                        <textarea rows="3" name="settings[aboutPage][description]"><?php echo esc_textarea($options['aboutPage']['description'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-shield"></span> 4 Giá trị cốt lõi (`aboutPage.coreValues`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề (Phần 1):</label>
                            <input type="text" name="settings[aboutPage][coreValues][titlePrefix]" value="<?php echo esc_attr($options['aboutPage']['coreValues']['titlePrefix'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề (Nổi bật):</label>
                            <input type="text" name="settings[aboutPage][coreValues][titleHighlight]" value="<?php echo esc_attr($options['aboutPage']['coreValues']['titleHighlight'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả định hướng giá trị:</label>
                        <textarea rows="2" name="settings[aboutPage][coreValues][desc]"><?php echo esc_textarea($options['aboutPage']['coreValues']['desc'] ?? ''); ?></textarea>
                    </div>

                    <?php
                    $values = $options['aboutPage']['coreValues']['items'] ?? [];
                    foreach ($values as $idx => $val):
                    ?>
                        <div class="thientam-repeater-item">
                            <div class="thientam-repeater-header">
                                <span>Giá trị cốt lõi #<?php echo $idx + 1; ?></span>
                            </div>
                            <div class="thientam-grid-2">
                                <div class="thientam-field">
                                    <label>Tên giá trị:</label>
                                    <input type="text" name="settings[aboutPage][coreValues][items][<?php echo $idx; ?>][title]" value="<?php echo esc_attr($val['title'] ?? ''); ?>" />
                                </div>
                                <div class="thientam-field">
                                    <label>Mô tả giải thích:</label>
                                    <input type="text" name="settings[aboutPage][coreValues][items][<?php echo $idx; ?>][desc]" value="<?php echo esc_attr($val['desc'] ?? ''); ?>" />
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-format-quote"></span> Khung Triết lý Quote Banner (`aboutPage.quoteBanner`)</h2>
                    </div>
                    <div class="thientam-field">
                        <label>Tiêu đề Quote:</label>
                        <input type="text" name="settings[aboutPage][quoteBanner][title]" value="<?php echo esc_attr($options['aboutPage']['quoteBanner']['title'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Nội dung trích dẫn:</label>
                        <textarea rows="2" name="settings[aboutPage][quoteBanner][desc]"><?php echo esc_textarea($options['aboutPage']['quoteBanner']['desc'] ?? ''); ?></textarea>
                    </div>
                </div>
                <!-- GIÁ TRỊ CỐT LÕI (CORE VALUES) -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-shield"></span> Giá Trị Cốt Lõi (`aboutPage.coreValues`)</h2>
                        <p>Khối giá trị cốt lõi và triết lý hoạt động trên trang Giới thiệu</p>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề (Phần 1):</label>
                            <input type="text" name="settings[aboutPage][coreValues][titlePrefix]" value="<?php echo esc_attr($options["aboutPage"]["coreValues"]["titlePrefix"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề (Nổi bật):</label>
                            <input type="text" name="settings[aboutPage][coreValues][titleHighlight]" value="<?php echo esc_attr($options["aboutPage"]["coreValues"]["titleHighlight"] ?? ""); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả giá trị cốt lõi:</label>
                        <textarea rows="2" name="settings[aboutPage][coreValues][desc]"><?php echo esc_textarea($options["aboutPage"]["coreValues"]["desc"] ?? ""); ?></textarea>
                    </div>
                    <h3 style="font-size:14px; margin: 16px 0 10px; color:#1e293b;">4 Giá Trị Cốt Lõi:</h3>
                    <?php
                    $cv_items = $options["aboutPage"]["coreValues"]["items"] ?? [];
                    for ($cvi = 0; $cvi < 4; $cvi++):
                        $cv = $cv_items[$cvi] ?? [];
                    ?>
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:12px;">
                            <div class="thientam-field" style="margin-bottom:8px;">
                                <label>Giá trị <?php echo ($cvi + 1); ?> - Tiêu đề:</label>
                                <input type="text" name="settings[aboutPage][coreValues][items][<?php echo $cvi; ?>][title]" value="<?php echo esc_attr($cv["title"] ?? ""); ?>" />
                            </div>
                            <div class="thientam-field" style="margin-bottom:0;">
                                <label>Mô tả chi tiết:</label>
                                <textarea rows="2" name="settings[aboutPage][coreValues][items][<?php echo $cvi; ?>][desc]"><?php echo esc_textarea($cv["desc"] ?? ""); ?></textarea>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- BANNER TRÍCH DẪN TRIẾT LÝ -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-format-quote"></span> Banner Trích Dẫn Triết Lý (`aboutPage.quoteBanner`)</h2>
                    </div>
                    <div class="thientam-field">
                        <label>Câu nói trích dẫn (Tiêu đề):</label>
                        <input type="text" name="settings[aboutPage][quoteBanner][title]" value="<?php echo esc_attr($options["aboutPage"]["quoteBanner"]["title"] ?? ""); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Lời bình luận / Diễn giải:</label>
                        <textarea rows="2" name="settings[aboutPage][quoteBanner][desc]"><?php echo esc_textarea($options["aboutPage"]["quoteBanner"]["desc"] ?? ""); ?></textarea>
                    </div>
                </div>


                <!-- TAB 4: TRANG DỊCH VỤ -->
            <?php elseif ($active_tab === 'services'): ?>
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-screenoptions"></span> Hero Trang Dịch Vụ (`servicesPage`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Eyebrow:</label>
                            <input type="text" name="settings[servicesPage][eyebrow]" value="<?php echo esc_attr($options['servicesPage']['eyebrow'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề chính:</label>
                            <input type="text" name="settings[servicesPage][title]" value="<?php echo esc_attr($options['servicesPage']['title'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả trang dịch vụ:</label>
                        <textarea rows="3" name="settings[servicesPage][description]"><?php echo esc_textarea($options['servicesPage']['description'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-pdf"></span> Cam kết Bảo mật & Tải PDF Điều khoản (`servicesPage.privacyCommitment`)</h2>
                    </div>
                    <div class="thientam-field">
                        <label>Tiêu đề cam kết (Nổi bật):</label>
                        <input type="text" name="settings[servicesPage][privacyCommitment][titleHighlight]" value="<?php echo esc_attr($options['servicesPage']['privacyCommitment']['titleHighlight'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả cam kết:</label>
                        <textarea rows="3" name="settings[servicesPage][privacyCommitment][desc]"><?php echo esc_textarea($options['servicesPage']['privacyCommitment']['desc'] ?? ''); ?></textarea>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Đường dẫn tệp PDF Điều khoản (URL):</label>
                            <input type="text" id="pdf_file_url" name="settings[servicesPage][privacyCommitment][fileUrl]" value="<?php echo esc_attr($options['servicesPage']['privacyCommitment']['fileUrl'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tên hiển thị văn bản PDF:</label>
                            <input type="text" name="settings[servicesPage][privacyCommitment][fileTitle]" value="<?php echo esc_attr($options['servicesPage']['privacyCommitment']['fileTitle'] ?? ''); ?>" />
                        </div>
                    </div>
                </div>
                <!-- ĐỒNG HÀNH TRÊN TỪNG PHƯƠNG DIỆN -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-compass"></span> Đồng Hành Trên Từng Phương Diện (`servicesPage.aspectsSection`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề (Phần 1):</label>
                            <input type="text" name="settings[servicesPage][aspectsSection][titlePrefix]" value="<?php echo esc_attr($options["servicesPage"]["aspectsSection"]["titlePrefix"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề (Nổi bật):</label>
                            <input type="text" name="settings[servicesPage][aspectsSection][titleHighlight]" value="<?php echo esc_attr($options["servicesPage"]["aspectsSection"]["titleHighlight"] ?? ""); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả phương diện:</label>
                        <textarea rows="2" name="settings[servicesPage][aspectsSection][desc]"><?php echo esc_textarea($options["servicesPage"]["aspectsSection"]["desc"] ?? ""); ?></textarea>
                    </div>
                </div>

                <!-- GÓC NHÌN & TRIẾT LÝ ĐỒNG HÀNH -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-visibility"></span> Góc Nhìn & Triết Lý Đồng Hành (`servicesPage.perspectiveSection`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề (Phần 1):</label>
                            <input type="text" name="settings[servicesPage][perspectiveSection][titlePrefix]" value="<?php echo esc_attr($options["servicesPage"]["perspectiveSection"]["titlePrefix"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề (Nổi bật):</label>
                            <input type="text" name="settings[servicesPage][perspectiveSection][titleHighlight]" value="<?php echo esc_attr($options["servicesPage"]["perspectiveSection"]["titleHighlight"] ?? ""); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả góc nhìn:</label>
                        <textarea rows="2" name="settings[servicesPage][perspectiveSection][desc]"><?php echo esc_textarea($options["servicesPage"]["perspectiveSection"]["desc"] ?? ""); ?></textarea>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Nhãn tag trích dẫn:</label>
                            <input type="text" name="settings[servicesPage][perspectiveSection][quote][tag]" value="<?php echo esc_attr($options["servicesPage"]["perspectiveSection"]["quote"]["tag"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Câu trích dẫn chính:</label>
                            <input type="text" name="settings[servicesPage][perspectiveSection][quote][title]" value="<?php echo esc_attr($options["servicesPage"]["perspectiveSection"]["quote"]["title"] ?? ""); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả trích dẫn triết lý:</label>
                        <textarea rows="2" name="settings[servicesPage][perspectiveSection][quote][desc]"><?php echo esc_textarea($options["servicesPage"]["perspectiveSection"]["quote"]["desc"] ?? ""); ?></textarea>
                    </div>
                </div>

                <!-- FAQ DỊCH VỤ TƯ VẤN -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-editor-help"></span> Câu Hỏi Thường Gặp Về Dịch Vụ (`servicesPage.faqSection`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề (Phần 1):</label>
                            <input type="text" name="settings[servicesPage][faqSection][titlePrefix]" value="<?php echo esc_attr($options["servicesPage"]["faqSection"]["titlePrefix"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề (Nổi bật):</label>
                            <input type="text" name="settings[servicesPage][faqSection][titleHighlight]" value="<?php echo esc_attr($options["servicesPage"]["faqSection"]["titleHighlight"] ?? ""); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả khối FAQ:</label>
                        <textarea rows="2" name="settings[servicesPage][faqSection][desc]"><?php echo esc_textarea($options["servicesPage"]["faqSection"]["desc"] ?? ""); ?></textarea>
                    </div>
                    <div class="thientam-grid-3">
                        <div class="thientam-field">
                            <label>Tiêu đề liên hệ trực tiếp:</label>
                            <input type="text" name="settings[servicesPage][faqSection][promptTitle]" value="<?php echo esc_attr($options["servicesPage"]["faqSection"]["promptTitle"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Mô tả liên hệ trực tiếp:</label>
                            <input type="text" name="settings[servicesPage][faqSection][promptDesc]" value="<?php echo esc_attr($options["servicesPage"]["faqSection"]["promptDesc"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn nút gửi câu hỏi:</label>
                            <input type="text" name="settings[servicesPage][faqSection][askBtn]" value="<?php echo esc_attr($options["servicesPage"]["faqSection"]["askBtn"] ?? ""); ?>" />
                        </div>
                    </div>

                    <h3 style="font-size:14px; margin: 16px 0 10px; color:#1e293b;">Danh Sách Câu Hỏi & Trả Lời:</h3>
                    <?php
                    $sf_items = $options["servicesPage"]["faqSection"]["items"] ?? [];
                    $faq_count = max(4, count($sf_items));
                    for ($sfi = 0; $sfi < $faq_count; $sfi++):
                        $sf = $sf_items[$sfi] ?? [];
                    ?>
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:12px;">
                            <div class="thientam-field" style="margin-bottom:8px;">
                                <label>Câu hỏi <?php echo ($sfi + 1); ?>:</label>
                                <input type="text" name="settings[servicesPage][faqSection][items][<?php echo $sfi; ?>][q]" value="<?php echo esc_attr($sf["q"] ?? ""); ?>" />
                            </div>
                            <div class="thientam-field" style="margin-bottom:0;">
                                <label>Câu trả lời:</label>
                                <textarea rows="2" name="settings[servicesPage][faqSection][items][<?php echo $sfi; ?>][a]"><?php echo esc_textarea($sf["a"] ?? ""); ?></textarea>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- THÔNG TIN CHUNG TRANG CHI TIẾT DỊCH VỤ -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-layout"></span> Cấu Hình Chung Chi Tiết Dịch Vụ (`serviceDetailCommon`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Nút đăng ký tư vấn:</label>
                            <input type="text" name="settings[serviceDetailCommon][heroBanner][primaryLabel]" value="<?php echo esc_attr($options["serviceDetailCommon"]["heroBanner"]["primaryLabel"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nút xem bảng giá:</label>
                            <input type="text" name="settings[serviceDetailCommon][heroBanner][pricingLabel]" value="<?php echo esc_attr($options["serviceDetailCommon"]["heroBanner"]["pricingLabel"] ?? ""); ?>" />
                        </div>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Khối liên quan - Tiêu đề (Phần 1):</label>
                            <input type="text" name="settings[serviceDetailCommon][relatedSection][titlePrefix]" value="<?php echo esc_attr($options["serviceDetailCommon"]["relatedSection"]["titlePrefix"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Khối liên quan - Tiêu đề (Nổi bật):</label>
                            <input type="text" name="settings[serviceDetailCommon][relatedSection][titleHighlight]" value="<?php echo esc_attr($options["serviceDetailCommon"]["relatedSection"]["titleHighlight"] ?? ""); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Khối liên quan - Mô tả:</label>
                        <textarea rows="2" name="settings[serviceDetailCommon][relatedSection][desc]"><?php echo esc_textarea($options["serviceDetailCommon"]["relatedSection"]["desc"] ?? ""); ?></textarea>
                    </div>
                </div>


                <!-- TAB 5: TRANG ĐÀO TẠO -->
            <?php elseif ($active_tab === 'training'): ?>
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-welcome-learn-more"></span> Hero Trang Đào Tạo (`trainingPage`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Eyebrow:</label>
                            <input type="text" name="settings[trainingPage][eyebrow]" value="<?php echo esc_attr($options['trainingPage']['eyebrow'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề chính:</label>
                            <input type="text" name="settings[trainingPage][title]" value="<?php echo esc_attr($options['trainingPage']['title'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Slogan khóa học:</label>
                        <input type="text" name="settings[trainingPage][subtitle]" value="<?php echo esc_attr($options['trainingPage']['subtitle'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả tổng quan khóa đào tạo:</label>
                        <textarea rows="3" name="settings[trainingPage][description]"><?php echo esc_textarea($options['trainingPage']['description'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-megaphone"></span> Khung Kêu gọi Đăng ký Cuối Trang (`trainingPage.cta`)</h2>
                    </div>
                    <div class="thientam-field">
                        <label>Tiêu đề CTA:</label>
                        <input type="text" name="settings[trainingPage][cta][titlePrefix]" value="<?php echo esc_attr($options['trainingPage']['cta']['titlePrefix'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả CTA:</label>
                        <input type="text" name="settings[trainingPage][cta][subtitle]" value="<?php echo esc_attr($options['trainingPage']['cta']['subtitle'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Nhãn nút Đăng ký:</label>
                        <input type="text" name="settings[trainingPage][cta][btnText]" value="<?php echo esc_attr($options['trainingPage']['cta']['btnText'] ?? ''); ?>" />
                    </div>
                </div>
                <!-- CHƯƠNG TRÌNH ĐÀO TẠO -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-book"></span> Chương Trình Đào Tạo (`trainingPage.curriculum`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề (Phần 1):</label>
                            <input type="text" name="settings[trainingPage][curriculum][titlePrefix]" value="<?php echo esc_attr($options["trainingPage"]["curriculum"]["titlePrefix"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề (Nổi bật):</label>
                            <input type="text" name="settings[trainingPage][curriculum][titleHighlight]" value="<?php echo esc_attr($options["trainingPage"]["curriculum"]["titleHighlight"] ?? ""); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả chương trình:</label>
                        <textarea rows="2" name="settings[trainingPage][curriculum][description]"><?php echo esc_textarea($options["trainingPage"]["curriculum"]["description"] ?? ""); ?></textarea>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Ghi chú tư vấn:</label>
                            <input type="text" name="settings[trainingPage][curriculum][consultNote]" value="<?php echo esc_attr($options["trainingPage"]["curriculum"]["consultNote"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn nút tư vấn lộ trình:</label>
                            <input type="text" name="settings[trainingPage][curriculum][consultBtn]" value="<?php echo esc_attr($options["trainingPage"]["curriculum"]["consultBtn"] ?? ""); ?>" />
                        </div>
                    </div>
                </div>

                <!-- ĐẶC ĐIỂM NỔI BẬT & KẾT QUẢ ĐẠT ĐƯỢC -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-star-filled"></span> Đặc Điểm Nổi Bật & Kết Quả Đạt Được (`trainingPage.features` & `trainingPage.outcomes`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Đặc điểm nổi bật - Tiêu đề (Phần 1):</label>
                            <input type="text" name="settings[trainingPage][features][titlePrefix]" value="<?php echo esc_attr($options["trainingPage"]["features"]["titlePrefix"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Đặc điểm nổi bật - Tiêu đề (Nổi bật):</label>
                            <input type="text" name="settings[trainingPage][features][titleHighlight]" value="<?php echo esc_attr($options["trainingPage"]["features"]["titleHighlight"] ?? ""); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả khối đặc điểm nổi bật:</label>
                        <textarea rows="2" name="settings[trainingPage][features][desc]"><?php echo esc_textarea($options["trainingPage"]["features"]["desc"] ?? ""); ?></textarea>
                    </div>
                    <h3 style="font-size:14px; margin: 16px 0 10px; color:#1e293b;">4 Đặc Điểm Nổi Bật:</h3>
                    <?php
                    $tf_items = $options["trainingPage"]["features"]["items"] ?? [];
                    for ($tfi = 0; $tfi < 4; $tfi++):
                        $tf = $tf_items[$tfi] ?? [];
                    ?>
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:12px;">
                            <div class="thientam-field" style="margin-bottom:8px;">
                                <label>Đặc điểm <?php echo ($tfi + 1); ?> - Tiêu đề:</label>
                                <input type="text" name="settings[trainingPage][features][items][<?php echo $tfi; ?>][title]" value="<?php echo esc_attr($tf["title"] ?? ""); ?>" />
                            </div>
                            <div class="thientam-field" style="margin-bottom:0;">
                                <label>Mô tả:</label>
                                <textarea rows="2" name="settings[trainingPage][features][items][<?php echo $tfi; ?>][desc]"><?php echo esc_textarea($tf["desc"] ?? ""); ?></textarea>
                            </div>
                        </div>
                    <?php endfor; ?>

                    <div class="thientam-grid-2" style="margin-top:20px; border-top:1px dashed #cbd5e1; padding-top:16px;">
                        <div class="thientam-field">
                            <label>Kết quả đạt được - Tiêu đề (Phần 1):</label>
                            <input type="text" name="settings[trainingPage][outcomes][titlePrefix]" value="<?php echo esc_attr($options["trainingPage"]["outcomes"]["titlePrefix"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Kết quả đạt được - Tiêu đề (Nổi bật):</label>
                            <input type="text" name="settings[trainingPage][outcomes][titleHighlight]" value="<?php echo esc_attr($options["trainingPage"]["outcomes"]["titleHighlight"] ?? ""); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả khối kết quả đạt được:</label>
                        <textarea rows="2" name="settings[trainingPage][outcomes][desc]"><?php echo esc_textarea($options["trainingPage"]["outcomes"]["desc"] ?? ""); ?></textarea>
                    </div>
                    <h3 style="font-size:14px; margin: 16px 0 10px; color:#1e293b;">4 Kết Quả Đầu Ra:</h3>
                    <?php
                    $to_items = $options["trainingPage"]["outcomes"]["items"] ?? [];
                    for ($toi = 0; $toi < 4; $toi++):
                        $to = $to_items[$toi] ?? [];
                    ?>
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:12px;">
                            <div class="thientam-field" style="margin-bottom:8px;">
                                <label>Kết quả <?php echo ($toi + 1); ?> - Tiêu đề:</label>
                                <input type="text" name="settings[trainingPage][outcomes][items][<?php echo $toi; ?>][title]" value="<?php echo esc_attr($to["title"] ?? ""); ?>" />
                            </div>
                            <div class="thientam-field" style="margin-bottom:0;">
                                <label>Mô tả:</label>
                                <textarea rows="2" name="settings[trainingPage][outcomes][items][<?php echo $toi; ?>][desc]"><?php echo esc_textarea($to["desc"] ?? ""); ?></textarea>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- FAQ KHÓA HỌC -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-editor-help"></span> Câu Hỏi Thường Gặp Khóa Đào Tạo (`trainingPage.faqSection` & `trainingPage.faqs`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề (Phần 1):</label>
                            <input type="text" name="settings[trainingPage][faqSection][titlePrefix]" value="<?php echo esc_attr($options["trainingPage"]["faqSection"]["titlePrefix"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề (Nổi bật):</label>
                            <input type="text" name="settings[trainingPage][faqSection][titleHighlight]" value="<?php echo esc_attr($options["trainingPage"]["faqSection"]["titleHighlight"] ?? ""); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả FAQ:</label>
                        <textarea rows="2" name="settings[trainingPage][faqSection][desc]"><?php echo esc_textarea($options["trainingPage"]["faqSection"]["desc"] ?? ""); ?></textarea>
                    </div>
                    <h3 style="font-size:14px; margin: 16px 0 10px; color:#1e293b;">Danh Sách Câu Hỏi & Trả Lời:</h3>
                    <?php
                    $t_faqs = $options["trainingPage"]["faqs"] ?? [];
                    $t_faq_count = max(5, count($t_faqs));
                    for ($tfi = 0; $tfi < $t_faq_count; $tfi++):
                        $tfq = $t_faqs[$tfi] ?? [];
                    ?>
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:12px;">
                            <div class="thientam-field" style="margin-bottom:8px;">
                                <label>Câu hỏi <?php echo ($tfi + 1); ?>:</label>
                                <input type="text" name="settings[trainingPage][faqs][<?php echo $tfi; ?>][q]" value="<?php echo esc_attr($tfq["q"] ?? ""); ?>" />
                            </div>
                            <div class="thientam-field" style="margin-bottom:0;">
                                <label>Câu trả lời:</label>
                                <textarea rows="2" name="settings[trainingPage][faqs][<?php echo $tfi; ?>][a]"><?php echo esc_textarea($tfq["a"] ?? ""); ?></textarea>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- FORM ĐĂNG KÝ KHÓA HỌC -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-clipboard"></span> Nhãn Form Đăng Ký Khóa Học (`trainingPage.registrationForm`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Khóa học mặc định:</label>
                            <input type="text" name="settings[trainingPage][registrationForm][defaultCourse]" value="<?php echo esc_attr($options["trainingPage"]["registrationForm"]["defaultCourse"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn nút gửi đăng ký:</label>
                            <input type="text" name="settings[trainingPage][registrationForm][submitBtn]" value="<?php echo esc_attr($options["trainingPage"]["registrationForm"]["submitBtn"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn Họ và tên:</label>
                            <input type="text" name="settings[trainingPage][registrationForm][nameLabel]" value="<?php echo esc_attr($options["trainingPage"]["registrationForm"]["nameLabel"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn Số điện thoại:</label>
                            <input type="text" name="settings[trainingPage][registrationForm][phoneLabel]" value="<?php echo esc_attr($options["trainingPage"]["registrationForm"]["phoneLabel"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn Email:</label>
                            <input type="text" name="settings[trainingPage][registrationForm][emailLabel]" value="<?php echo esc_attr($options["trainingPage"]["registrationForm"]["emailLabel"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn Ghi chú nhu cầu:</label>
                            <input type="text" name="settings[trainingPage][registrationForm][noteLabel]" value="<?php echo esc_attr($options["trainingPage"]["registrationForm"]["noteLabel"] ?? ""); ?>" />
                        </div>
                    </div>
                </div>


                <!-- TAB 6: TRANG TUYỂN DỤNG -->
            <?php elseif ($active_tab === 'recruitment'): ?>
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-groups"></span> Hero Trang Tuyển Dụng (`recruitment.hero`)</h2>
                    </div>
                    <div class="thientam-field">
                        <label>Tiêu đề chính:</label>
                        <input type="text" name="settings[recruitment][hero][title]" value="<?php echo esc_attr($options['recruitment']['hero']['title'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Khẩu hiệu / Slogan tuyển dụng:</label>
                        <input type="text" name="settings[recruitment][hero][subtitle]" value="<?php echo esc_attr($options['recruitment']['hero']['subtitle'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả môi trường & sứ mệnh:</label>
                        <textarea rows="3" name="settings[recruitment][hero][desc]"><?php echo esc_textarea($options['recruitment']['hero']['desc'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-archive"></span> Banner Ngân Hàng Nhân Tài Mở (`recruitment.openBanner`)</h2>
                    </div>
                    <div class="thientam-field">
                        <label>Tiêu đề banner:</label>
                        <input type="text" name="settings[recruitment][openBanner][title]" value="<?php echo esc_attr($options['recruitment']['openBanner']['title'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả gửi hồ sơ mở:</label>
                        <textarea rows="2" name="settings[recruitment][openBanner][desc]"><?php echo esc_textarea($options['recruitment']['openBanner']['desc'] ?? ''); ?></textarea>
                    </div>
                    <div class="thientam-field">
                        <label>Nhãn nút nộp hồ sơ mở:</label>
                        <input type="text" name="settings[recruitment][openBanner][button]" value="<?php echo esc_attr($options['recruitment']['openBanner']['button'] ?? ''); ?>" />
                    </div>
                </div>
                <!-- LÝ DO GIA NHẬP (WHY JOIN) -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-heart"></span> Lý Do Gia Nhập Thiên Tâm (`recruitment.whyJoin`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề phụ (Eyebrow):</label>
                            <input type="text" name="settings[recruitment][whyJoin][eyebrow]" value="<?php echo esc_attr($options["recruitment"]["whyJoin"]["eyebrow"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề chính:</label>
                            <input type="text" name="settings[recruitment][whyJoin][title]" value="<?php echo esc_attr($options["recruitment"]["whyJoin"]["title"] ?? ""); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả tổng quan:</label>
                        <textarea rows="2" name="settings[recruitment][whyJoin][desc]"><?php echo esc_textarea($options["recruitment"]["whyJoin"]["desc"] ?? ""); ?></textarea>
                    </div>
                    <h3 style="font-size:14px; margin: 16px 0 10px; color:#1e293b;">4 Điểm Hấp Dẫn Ứng Viên:</h3>
                    <?php
                    $rw_items = $options["recruitment"]["whyJoin"]["items"] ?? [];
                    for ($rwi = 0; $rwi < 4; $rwi++):
                        $rw = $rw_items[$rwi] ?? [];
                    ?>
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:12px;">
                            <div class="thientam-field" style="margin-bottom:8px;">
                                <label>Lý do <?php echo ($rwi + 1); ?> - Tiêu đề:</label>
                                <input type="text" name="settings[recruitment][whyJoin][items][<?php echo $rwi; ?>][title]" value="<?php echo esc_attr($rw["title"] ?? ""); ?>" />
                            </div>
                            <div class="thientam-field" style="margin-bottom:0;">
                                <label>Mô tả chi tiết:</label>
                                <textarea rows="2" name="settings[recruitment][whyJoin][items][<?php echo $rwi; ?>][desc]"><?php echo esc_textarea($rw["desc"] ?? ""); ?></textarea>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- QUY TRÌNH TUYỂN DỤNG -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-yes-alt"></span> Quy Trình Tuyển Dụng (`recruitment.process`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề phụ (Eyebrow):</label>
                            <input type="text" name="settings[recruitment][process][eyebrow]" value="<?php echo esc_attr($options["recruitment"]["process"]["eyebrow"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề chính:</label>
                            <input type="text" name="settings[recruitment][process][title]" value="<?php echo esc_attr($options["recruitment"]["process"]["title"] ?? ""); ?>" />
                        </div>
                    </div>
                    <h3 style="font-size:14px; margin: 16px 0 10px; color:#1e293b;">4 Bước Tuyển Dụng:</h3>
                    <?php
                    $rp_steps = $options["recruitment"]["process"]["steps"] ?? [];
                    for ($rpi = 0; $rpi < 4; $rpi++):
                        $rps = $rp_steps[$rpi] ?? [];
                    ?>
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:12px;">
                            <div class="thientam-grid-2">
                                <div class="thientam-field" style="margin-bottom:8px;">
                                    <label>Bước <?php echo ($rpi + 1); ?> - Tiêu đề:</label>
                                    <input type="text" name="settings[recruitment][process][steps][<?php echo $rpi; ?>][title]" value="<?php echo esc_attr($rps["title"] ?? ""); ?>" />
                                </div>
                                <div class="thientam-field" style="margin-bottom:8px;">
                                    <label>Số hiệu bước:</label>
                                    <input type="text" name="settings[recruitment][process][steps][<?php echo $rpi; ?>][step]" value="<?php echo esc_attr($rps["step"] ?? ("0" . ($rpi + 1))); ?>" />
                                </div>
                            </div>
                            <div class="thientam-field" style="margin-bottom:0;">
                                <label>Mô tả bước:</label>
                                <textarea rows="2" name="settings[recruitment][process][steps][<?php echo $rpi; ?>][desc]"><?php echo esc_textarea($rps["desc"] ?? ""); ?></textarea>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- POPUP FORM ỨNG TUYỂN -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-id"></span> Nhãn Popup Form Ứng Tuyển (`recruitment.applicationForm`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề Popup Modal:</label>
                            <input type="text" name="settings[recruitment][applicationForm][modalTitle]" value="<?php echo esc_attr($options["recruitment"]["applicationForm"]["modalTitle"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Mô tả phụ Popup:</label>
                            <input type="text" name="settings[recruitment][applicationForm][modalSubtitle]" value="<?php echo esc_attr($options["recruitment"]["applicationForm"]["modalSubtitle"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn nút nộp hồ sơ:</label>
                            <input type="text" name="settings[recruitment][applicationForm][submit]" value="<?php echo esc_attr($options["recruitment"]["applicationForm"]["submit"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề nộp thành công:</label>
                            <input type="text" name="settings[recruitment][applicationForm][successTitle]" value="<?php echo esc_attr($options["recruitment"]["applicationForm"]["successTitle"] ?? ""); ?>" />
                        </div>
                    </div>
                </div>


                <!-- TAB 7: LIÊN HỆ & POPUPS -->
            <?php elseif ($active_tab === 'contact'): ?>
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-location"></span> Trang Liên Hệ Trực Tiếp (`contactPage`)</h2>
                    </div>
                    <div class="thientam-field">
                        <label>Tiêu đề trang:</label>
                        <input type="text" name="settings[contactPage][title]" value="<?php echo esc_attr($options['contactPage']['title'] ?? ''); ?>" />
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả trang liên hệ:</label>
                        <textarea rows="2" name="settings[contactPage][subtitle]"><?php echo esc_textarea($options['contactPage']['subtitle'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-warning"></span> Trang Báo Lỗi 404 (`notFound`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề lỗi 404:</label>
                            <input type="text" name="settings[notFound][title]" value="<?php echo esc_attr($options['notFound']['title'] ?? ''); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nút quay lại Trang chủ:</label>
                            <input type="text" name="settings[notFound][homeBtn]" value="<?php echo esc_attr($options['notFound']['homeBtn'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="thientam-field">
                        <label>Mô tả trang 404:</label>
                        <input type="text" name="settings[notFound][desc]" value="<?php echo esc_attr($options['notFound']['desc'] ?? ''); ?>" />
                    </div>
                </div>
                <!-- FORM LIÊN HỆ & ĐẶT HẸN CHUNG -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-feedback"></span> Nhãn Form Liên Hệ & Đặt Lịch Chung (`contactForm.form`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Nhãn Họ và tên:</label>
                            <input type="text" name="settings[contactForm][form][fullName]" value="<?php echo esc_attr($options["contactForm"]["form"]["fullName"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn Số điện thoại:</label>
                            <input type="text" name="settings[contactForm][form][phone]" value="<?php echo esc_attr($options["contactForm"]["form"]["phone"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn Email:</label>
                            <input type="text" name="settings[contactForm][form][email]" value="<?php echo esc_attr($options["contactForm"]["form"]["email"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn Chọn dịch vụ:</label>
                            <input type="text" name="settings[contactForm][form][service]" value="<?php echo esc_attr($options["contactForm"]["form"]["service"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn Lời nhắn:</label>
                            <input type="text" name="settings[contactForm][form][message]" value="<?php echo esc_attr($options["contactForm"]["form"]["message"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn nút gửi liên hệ:</label>
                            <input type="text" name="settings[contactForm][form][submit]" value="<?php echo esc_attr($options["contactForm"]["form"]["submit"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề gửi thành công:</label>
                            <input type="text" name="settings[contactForm][form][successTitle]" value="<?php echo esc_attr($options["contactForm"]["form"]["successTitle"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Mô tả gửi thành công:</label>
                            <input type="text" name="settings[contactForm][form][successDesc]" value="<?php echo esc_attr($options["contactForm"]["form"]["successDesc"] ?? ""); ?>" />
                        </div>
                    </div>
                </div>

                <!-- THÔNG BÁO FORM ALERT CHUNG -->
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-bell"></span> Thông Báo Gửi Form Thành Công (`formSuccessAlert`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tiêu đề thông báo:</label>
                            <input type="text" name="settings[formSuccessAlert][title]" value="<?php echo esc_attr($options["formSuccessAlert"]["title"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Đoạn mở đầu (Prefix):</label>
                            <input type="text" name="settings[formSuccessAlert][prefix]" value="<?php echo esc_attr($options["formSuccessAlert"]["prefix"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tên loại dịch vụ:</label>
                            <input type="text" name="settings[formSuccessAlert][typeService]" value="<?php echo esc_attr($options["formSuccessAlert"]["typeService"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tên loại khóa học:</label>
                            <input type="text" name="settings[formSuccessAlert][typeCourse]" value="<?php echo esc_attr($options["formSuccessAlert"]["typeCourse"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tên loại tuyển dụng:</label>
                            <input type="text" name="settings[formSuccessAlert][typeJob]" value="<?php echo esc_attr($options["formSuccessAlert"]["typeJob"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Đoạn kết (Suffix):</label>
                            <input type="text" name="settings[formSuccessAlert][suffix]" value="<?php echo esc_attr($options["formSuccessAlert"]["suffix"] ?? ""); ?>" />
                        </div>
                    </div>
                </div>
                <!-- TAB 8: TIN TỨC & BÀI VIẾT -->
            <?php elseif ($active_tab === "news"): ?>
                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-admin-post"></span> Chi Tiết Bài Viết (`newsDetail`)</h2>
                        <p>Các nhãn hiển thị tại trang chi tiết bài viết, tác giả và chia sẻ bài viết</p>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Tác giả mặc định:</label>
                            <input type="text" name="settings[newsDetail][authorDefault]" value="<?php echo esc_attr($options["newsDetail"]["authorDefault"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiểu sử tác giả:</label>
                            <input type="text" name="settings[newsDetail][authorBio]" value="<?php echo esc_attr($options["newsDetail"]["authorBio"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn nút chia sẻ:</label>
                            <input type="text" name="settings[newsDetail][share]" value="<?php echo esc_attr($options["newsDetail"]["share"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Nhãn sao chép liên kết:</label>
                            <input type="text" name="settings[newsDetail][copyLink]" value="<?php echo esc_attr($options["newsDetail"]["copyLink"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề mục lục (Table of Contents):</label>
                            <input type="text" name="settings[newsDetail][tableOfContents]" value="<?php echo esc_attr($options["newsDetail"]["tableOfContents"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề danh mục:</label>
                            <input type="text" name="settings[newsDetail][categories]" value="<?php echo esc_attr($options["newsDetail"]["categories"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề bài viết gần đây:</label>
                            <input type="text" name="settings[newsDetail][recentPosts]" value="<?php echo esc_attr($options["newsDetail"]["recentPosts"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Tiêu đề bài viết liên quan:</label>
                            <input type="text" name="settings[newsDetail][relatedArticles]" value="<?php echo esc_attr($options["newsDetail"]["relatedArticles"] ?? ""); ?>" />
                        </div>
                    </div>
                    <div class="thientam-grid-2" style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px; padding:12px; margin-top:10px;">
                        <div class="thientam-field" style="margin-bottom:0;">
                            <label>Thanh bên - Tiêu đề Banner:</label>
                            <input type="text" name="settings[newsDetail][sidebarBanner][title]" value="<?php echo esc_attr($options["newsDetail"]["sidebarBanner"]["title"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field" style="margin-bottom:0;">
                            <label>Thanh bên - Nhãn nút:</label>
                            <input type="text" name="settings[newsDetail][sidebarBanner][button]" value="<?php echo esc_attr($options["newsDetail"]["sidebarBanner"]["button"] ?? ""); ?>" />
                        </div>
                    </div>
                </div>

                <div class="thientam-card">
                    <div class="thientam-card-header">
                        <h2><span class="dashicons dashicons-list-view"></span> Trang Danh Sách Tin Tức (`newsListing`)</h2>
                    </div>
                    <div class="thientam-grid-2">
                        <div class="thientam-field">
                            <label>Đơn vị đếm bài viết (VD: bài viết):</label>
                            <input type="text" name="settings[newsListing][postCountUnit]" value="<?php echo esc_attr($options["newsListing"]["postCountUnit"] ?? ""); ?>" />
                        </div>
                        <div class="thientam-field">
                            <label>Aria nhãn tìm kiếm:</label>
                            <input type="text" name="settings[newsListing][searchAria]" value="<?php echo esc_attr($options["newsListing"]["searchAria"] ?? ""); ?>" />
                        </div>
                    </div>
                </div>


                <!-- TAB 8: SEO & METADATA TỪNG TRANG -->
            <?php elseif ($active_tab === 'seo'): ?>
                <?php
                $seo_pages = [
                    'home'          => ['label' => '01. Trang Chủ', 'path' => '/', 'icon' => 'dashicons-admin-home'],
                    'about'         => ['label' => '02. Trang Giới Thiệu', 'path' => '/gioi-thieu', 'icon' => 'dashicons-id-alt'],
                    'services'      => ['label' => '03. Trang Dịch Vụ', 'path' => '/dich-vu', 'icon' => 'dashicons-screenoptions'],
                    'training'      => ['label' => '04. Trang Đào Tạo', 'path' => '/dao-tao', 'icon' => 'dashicons-welcome-learn-more'],
                    'news'          => ['label' => '05. Trang Tin Tức & Tri Thức', 'path' => '/tin-tuc', 'icon' => 'dashicons-format-aside'],
                    'recruitment'   => ['label' => '06. Trang Tuyển Dụng', 'path' => '/tuyen-dung', 'icon' => 'dashicons-groups'],
                    'contact'       => ['label' => '07. Trang Liên Hệ & Đặt Lịch', 'path' => '/lien-he', 'icon' => 'dashicons-email-alt'],
                    'privacy'       => ['label' => '08. Trang Chính Sách Bảo Mật', 'path' => '/chinh-sach-bao-mat', 'icon' => 'dashicons-shield'],
                    'terms'         => ['label' => '09. Trang Điều Khoản Dịch Vụ', 'path' => '/dieu-khoan-su-dung', 'icon' => 'dashicons-media-document'],
                    'complaints'    => ['label' => '10. Trang Giải Quyết Khiếu Nại', 'path' => '/giai-quyet-khieu-nai', 'icon' => 'dashicons-megaphone'],
                    'regulations'   => ['label' => '11. Trang Quy Chế Hoạt Động', 'path' => '/quy-che-hoat-dong', 'icon' => 'dashicons-list-view'],
                    'brandShowcase' => ['label' => '12. Trang Bộ Nhận Diện Thương Hiệu', 'path' => '/brand-showcase', 'icon' => 'dashicons-art'],
                    'notFound'      => ['label' => '13. Trang Báo Lỗi 404', 'path' => '/404', 'icon' => 'dashicons-warning'],
                ];
                ?>
                <div style="background: #fff8e1; border: 1px solid #ffe082; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; color: #8d6e63;">
                    <strong><span class="dashicons dashicons-info" style="font-size: 18px; vertical-align: middle;"></span> Lưu ý về SEO:</strong>
                    Các thiết lập dưới đây sẽ được đồng bộ trực tiếp lên thẻ <code>&lt;title&gt;</code>, <code>&lt;meta name="description"&gt;</code>, <code>&lt;meta name="keywords"&gt;</code>, và thẻ chia sẻ mạng xã hội (OpenGraph / Facebook / Zalo / Twitter). Nếu để trống, hệ thống sẽ tự động dùng giá trị mặc định của thương hiệu Thiên Tâm.
                </div>

                <?php foreach ($seo_pages as $pkey => $pinfo):
                    $pdata = $options['seo'][$pkey] ?? [];
                ?>
                    <div class="thientam-card">
                        <div class="thientam-card-header">
                            <h2><span class="dashicons <?php echo esc_attr($pinfo['icon']); ?>"></span> SEO <?php echo esc_html($pinfo['label']); ?> <code style="font-size: 12px; font-weight: normal; color: #d97706;"><?php echo esc_html($pinfo['path']); ?></code></h2>
                            <p>Cấu hình thẻ Tiêu đề, Thẻ mô tả, Từ khóa và Ảnh đại diện chia sẻ khi gửi link trang này</p>
                        </div>

                        <div class="thientam-grid-2">
                            <div class="thientam-field">
                                <label>Tiêu đề trang (Meta Title):</label>
                                <input type="text" name="settings[seo][<?php echo esc_attr($pkey); ?>][title]" value="<?php echo esc_attr($pdata['title'] ?? ''); ?>" placeholder="Nhập tiêu đề trang hiển thị trên tab trình duyệt và Google..." />
                            </div>
                            <div class="thientam-field">
                                <label>Từ khóa SEO (Keywords, cách nhau bằng dấu phẩy):</label>
                                <input type="text" name="settings[seo][<?php echo esc_attr($pkey); ?>][keywords]" value="<?php echo esc_attr($pdata['keywords'] ?? ''); ?>" placeholder="vd: Phong thủy Đà Lạt, Tử vi Bát tự, Phong thủy Thiên Tâm..." />
                            </div>
                        </div>

                        <div class="thientam-field">
                            <label>Mô tả trang (Meta Description - Tối ưu 150-160 ký tự):</label>
                            <textarea rows="2" name="settings[seo][<?php echo esc_attr($pkey); ?>][description]" placeholder="Nhập tóm tắt mô tả nội dung trang để hiển thị trên kết quả tìm kiếm Google..."><?php echo esc_textarea($pdata['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="thientam-grid-2" style="background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px dashed #cbd5e1; margin-top: 10px;">
                            <div class="thientam-field" style="margin-bottom: 0;">
                                <label>Tiêu đề chia sẻ MXH (OG Title - để trống sẽ tự lấy Meta Title):</label>
                                <input type="text" name="settings[seo][<?php echo esc_attr($pkey); ?>][ogTitle]" value="<?php echo esc_attr($pdata['ogTitle'] ?? ''); ?>" />
                            </div>
                            <div class="thientam-field" style="margin-bottom: 0;">
                                <label>Hình ảnh chia sẻ MXH (OG Image):</label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" id="og_image_<?php echo esc_attr($pkey); ?>" name="settings[seo][<?php echo esc_attr($pkey); ?>][ogImage]" value="<?php echo esc_attr($pdata['ogImage'] ?? ''); ?>" placeholder="/images/banner-home-1.png hoặc URL ảnh..." />
                                    <button type="button" class="button thientam-media-btn" data-target="#og_image_<?php echo esc_attr($pkey); ?>">Chọn ảnh</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="thientam-footer-bar">
                <div>
                    <span style="font-size: 13px; color: #64748b;">Mọi thay đổi sẽ được cập nhật tức thì đến API và Next.js Frontend.</span>
                </div>
                <div>
                    <button type="submit" class="btn-thientam-primary">
                        <span class="dashicons dashicons-saved"></span> Lưu Thay Đổi Cài Đặt
                    </button>
                </div>
            </div>
        </form>

        <script>
            jQuery(document).ready(function($) {
                $(document).on('click', '.thientam-media-btn', function(e) {
                    e.preventDefault();
                    var $btn = $(this);
                    var targetSelector = $btn.data('target');
                    var $input = $(targetSelector);

                    var customUploader = wp.media({
                        title: 'Chọn hình ảnh SEO / Banner',
                        button: {
                            text: 'Sử dụng ảnh này'
                        },
                        multiple: false
                    }).on('select', function() {
                        var attachment = customUploader.state().get('selection').first().toJSON();
                        $input.val(attachment.url).trigger('change');
                    }).open();
                });

                // ==================== HERO AVATARS CRUD REPEATER ====================
                function reindexHeroAvatars() {
                    var count = 0;
                    $('#hero-avatars-list .hero-avatar-row').each(function(index) {
                        count++;
                        $(this).find('.hero-avatar-index').text(count);
                        var inputId = 'hero_avatar_input_' + index;
                        $(this).find('.hero-avatar-input-img')
                            .attr('name', 'settings[heroHome][avatars][' + index + '][image]')
                            .attr('id', inputId);
                        $(this).find('.thientam-media-btn').attr('data-target', '#' + inputId);
                        $(this).find('.hero-avatar-input-alt')
                            .attr('name', 'settings[heroHome][avatars][' + index + '][alt]');
                    });
                    if (count === 0) {
                        $('#hero-avatars-empty').show();
                    } else {
                        $('#hero-avatars-empty').hide();
                    }
                }

                $(document).on('change input', '.hero-avatar-input-img', function() {
                    var val = $(this).val();
                    var $preview = $(this).closest('.hero-avatar-row').find('.hero-avatar-img-preview');
                    if (val) {
                        $preview.attr('src', val);
                    }
                });

                $('#btn-add-hero-avatar').on('click', function(e) {
                    e.preventDefault();
                    var nextIndex = $('#hero-avatars-list .hero-avatar-row').length;
                    var inputId = 'hero_avatar_input_' + nextIndex;
                    var newRow = `
                        <div class="hero-avatar-row">
                            <div class="hero-avatar-body">
                                <span class="marquee-tag-badge hero-avatar-index"></span>
                                <div class="hero-avatar-preview-wrap" style="width: 44px; height: 44px; min-width: 44px; max-width: 44px; max-height: 44px; border-radius: 50%; overflow: hidden; border: 2px solid #cbd5e1; background: #ffffff; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                    <img src="/images/thien-tam-icon-4x.png" class="hero-avatar-img-preview" style="width: 100%; height: 100%; object-fit: cover; display: block;" alt="Preview" />
                                </div>
                                <div class="hero-avatar-fields">
                                    <div class="hero-avatar-field-item">
                                        <label>Đường dẫn hình ảnh (URL):</label>
                                        <div style="display: flex; gap: 6px;">
                                            <input type="text" id="${inputId}" name="settings[heroHome][avatars][${nextIndex}][image]" value="" placeholder="https://... hoặc chọn từ thư viện" class="hero-avatar-input-img" />
                                            <button type="button" class="button thientam-media-btn" data-target="#${inputId}">Chọn ảnh</button>
                                        </div>
                                    </div>
                                    <div class="hero-avatar-field-item">
                                        <label>Tên / Mô tả (Alt):</label>
                                        <input type="text" name="settings[heroHome][avatars][${nextIndex}][alt]" value="" placeholder="Ví dụ: Khách hàng Doanh nghiệp" class="hero-avatar-input-alt" />
                                    </div>
                                </div>
                                <div class="marquee-tag-actions">
                                    <button type="button" class="marquee-action-btn btn-move-avatar-up" title="Di chuyển lên">
                                        <span class="dashicons dashicons-arrow-up-alt2"></span>
                                    </button>
                                    <button type="button" class="marquee-action-btn btn-move-avatar-down" title="Di chuyển xuống">
                                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                                    </button>
                                    <button type="button" class="marquee-action-btn btn-delete btn-delete-avatar" title="Xóa ảnh">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#hero-avatars-list').append(newRow);
                    reindexHeroAvatars();
                    $('#hero-avatars-list .hero-avatar-row:last-child .thientam-media-btn').trigger('click');
                });

                $(document).on('click', '.btn-delete-avatar', function(e) {
                    e.preventDefault();
                    $(this).closest('.hero-avatar-row').fadeOut(180, function() {
                        $(this).remove();
                        reindexHeroAvatars();
                    });
                });

                $(document).on('click', '.btn-move-avatar-up', function(e) {
                    e.preventDefault();
                    var $row = $(this).closest('.hero-avatar-row');
                    var $prev = $row.prev('.hero-avatar-row');
                    if ($prev.length) {
                        $row.insertBefore($prev);
                        reindexHeroAvatars();
                    }
                });

                $(document).on('click', '.btn-move-avatar-down', function(e) {
                    e.preventDefault();
                    var $row = $(this).closest('.hero-avatar-row');
                    var $next = $row.next('.hero-avatar-row');
                    if ($next.length) {
                        $row.insertAfter($next);
                        reindexHeroAvatars();
                    }
                });

                // ==================== MARQUEE TAGS CRUD REPEATER ====================
                function reindexMarqueeTags() {
                    var count = 0;
                    $('#marquee-tags-list .marquee-tag-row').each(function(index) {
                        count++;
                        $(this).find('.tag-index').text(count);
                    });
                    if (count === 0) {
                        $('#marquee-tags-empty').show();
                    } else {
                        $('#marquee-tags-empty').hide();
                    }
                }

                $('#btn-add-marquee-tag').on('click', function(e) {
                    e.preventDefault();
                    var newRow = `
                    <div class="marquee-tag-row">
                        <span class="marquee-tag-badge tag-index"></span>
                        <input type="text" name="settings[marqueeTags][]" value="" placeholder="Nhập nội dung thẻ từ khóa..." class="marquee-tag-input" />
                        <div class="marquee-tag-actions">
                            <button type="button" class="marquee-action-btn btn-move-tag-up" title="Di chuyển lên">
                                <span class="dashicons dashicons-arrow-up-alt2"></span>
                            </button>
                            <button type="button" class="marquee-action-btn btn-move-tag-down" title="Di chuyển xuống">
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                            </button>
                            <button type="button" class="marquee-action-btn btn-delete btn-delete-tag" title="Xóa thẻ">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                `;
                    $('#marquee-tags-list').append(newRow);
                    reindexMarqueeTags();
                    $('#marquee-tags-list .marquee-tag-row:last-child input').focus();
                });

                $(document).on('click', '.btn-delete-tag', function(e) {
                    e.preventDefault();
                    $(this).closest('.marquee-tag-row').fadeOut(180, function() {
                        $(this).remove();
                        reindexMarqueeTags();
                    });
                });

                $(document).on('click', '.btn-move-tag-up', function(e) {
                    e.preventDefault();
                    var $row = $(this).closest('.marquee-tag-row');
                    var $prev = $row.prev('.marquee-tag-row');
                    if ($prev.length) {
                        $row.insertBefore($prev);
                        reindexMarqueeTags();
                    }
                });

                $(document).on('click', '.btn-move-tag-down', function(e) {
                    e.preventDefault();
                    var $row = $(this).closest('.marquee-tag-row');
                    var $next = $row.next('.marquee-tag-row');
                    if ($next.length) {
                        $row.insertAfter($next);
                        reindexMarqueeTags();
                    }
                });

                $('#btn-reset-default-tags').on('click', function(e) {
                    e.preventDefault();
                    var defaultTags = [
                        "Phong thủy nhà ở",
                        "Tử vi bát tự",
                        "Phong thủy doanh nghiệp",
                        "Phong thủy số",
                        "Gia đạo bình an",
                        "Bản mệnh cát tường",
                        "Minh định tương lai",
                        "An trú hiện tại"
                    ];
                    $('#marquee-tags-list').empty();
                    defaultTags.forEach(function(tag) {
                        var row = `
                        <div class="marquee-tag-row">
                            <span class="marquee-tag-badge tag-index"></span>
                            <input type="text" name="settings[marqueeTags][]" value="${tag}" placeholder="Nhập nội dung thẻ từ khóa..." class="marquee-tag-input" />
                            <div class="marquee-tag-actions">
                                <button type="button" class="marquee-action-btn btn-move-tag-up" title="Di chuyển lên">
                                    <span class="dashicons dashicons-arrow-up-alt2"></span>
                                </button>
                                <button type="button" class="marquee-action-btn btn-move-tag-down" title="Di chuyển xuống">
                                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                                </button>
                                <button type="button" class="marquee-action-btn btn-delete btn-delete-tag" title="Xóa thẻ">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </div>
                    `;
                        $('#marquee-tags-list').append(row);
                    });
                    reindexMarqueeTags();
                });

                // ==================== SOCIAL LINKS CRUD REPEATER ====================
                function reindexSocialLinks() {
                    var count = 0;
                    $('#social-links-list .social-link-row').each(function(index) {
                        count++;
                        $(this).find('.social-index').text(count);
                        $(this).find('.social-input-name').attr('name', 'settings[footer][socialLinks][' + index + '][name]');
                        $(this).find('.social-input-href').attr('name', 'settings[footer][socialLinks][' + index + '][href]');
                        var iconId = 'social_icon_input_' + index;
                        $(this).find('.social-input-icon').attr('name', 'settings[footer][socialLinks][' + index + '][icon]').attr('id', iconId);
                        $(this).find('.thientam-media-btn').attr('data-target', '#' + iconId);
                    });
                    if (count === 0) {
                        $('#social-links-empty').show();
                    } else {
                        $('#social-links-empty').hide();
                    }
                }

                $(document).on('change input', '.social-input-icon', function() {
                    var val = $(this).val();
                    var $preview = $(this).closest('.social-link-row').find('.social-icon-preview');
                    if (val) {
                        $preview.attr('src', val);
                    }
                });

                $('#btn-add-social-link').on('click', function(e) {
                    e.preventDefault();
                    var nextIndex = $('#social-links-list .social-link-row').length;
                    var iconId = 'social_icon_input_' + nextIndex;
                    var newRow = `
                        <div class="social-link-row">
                            <div class="social-link-body">
                                <span class="marquee-tag-badge social-index"></span>
                                <div class="social-preview-wrap">
                                    <img src="/icons/facebook.svg" class="social-icon-preview" alt="Preview" onerror="this.src='/icons/facebook.svg';" />
                                </div>
                                <div class="social-link-fields">
                                    <div class="social-field-item">
                                        <label>Tên mạng xã hội:</label>
                                        <input type="text" name="settings[footer][socialLinks][${nextIndex}][name]" value="" placeholder="Ví dụ: TikTok Thiên Tâm" class="social-input-name" />
                                    </div>
                                    <div class="social-field-item">
                                        <label>Đường dẫn liên kết (URL):</label>
                                        <input type="text" name="settings[footer][socialLinks][${nextIndex}][href]" value="" placeholder="https://..." class="social-input-href" />
                                    </div>
                                    <div class="social-field-item">
                                        <label>Biểu tượng / Icon:</label>
                                        <div style="display: flex; gap: 8px;">
                                            <input type="text" id="${iconId}" name="settings[footer][socialLinks][${nextIndex}][icon]" value="/icons/facebook.svg" placeholder="/icons/... hoặc URL ảnh..." class="social-input-icon" />
                                            <button type="button" class="button thientam-media-btn" data-target="#${iconId}">Chọn ảnh</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="marquee-tag-actions">
                                    <button type="button" class="marquee-action-btn btn-move-social-up" title="Di chuyển lên">
                                        <span class="dashicons dashicons-arrow-up-alt2"></span>
                                    </button>
                                    <button type="button" class="marquee-action-btn btn-move-social-down" title="Di chuyển xuống">
                                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                                    </button>
                                    <button type="button" class="marquee-action-btn btn-delete btn-delete-social" title="Xóa mạng xã hội">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#social-links-list').append(newRow);
                    reindexSocialLinks();
                    $('#social-links-list .social-link-row:last-child .social-input-name').focus();
                });

                $(document).on('click', '.btn-delete-social', function(e) {
                    e.preventDefault();
                    $(this).closest('.social-link-row').fadeOut(180, function() {
                        $(this).remove();
                        reindexSocialLinks();
                    });
                });

                $(document).on('click', '.btn-move-social-up', function(e) {
                    e.preventDefault();
                    var $row = $(this).closest('.social-link-row');
                    var $prev = $row.prev('.social-link-row');
                    if ($prev.length) {
                        $row.insertBefore($prev);
                        reindexSocialLinks();
                    }
                });

                $(document).on('click', '.btn-move-social-down', function(e) {
                    e.preventDefault();
                    var $row = $(this).closest('.social-link-row');
                    var $next = $row.next('.social-link-row');
                    if ($next.length) {
                        $row.insertAfter($next);
                        reindexSocialLinks();
                    }
                });

                $('#btn-reset-default-social').on('click', function(e) {
                    e.preventDefault();
                    var defaultSocial = [{
                            name: 'Facebook Thiên Tâm',
                            href: 'https://facebook.com',
                            icon: '/icons/facebook.svg'
                        },
                        {
                            name: 'Zalo Thiên Tâm',
                            href: 'https://zalo.me',
                            icon: '/icons/zalo.svg'
                        },
                        {
                            name: 'YouTube Thiên Tâm',
                            href: 'https://youtube.com',
                            icon: '/icons/youtube.svg'
                        },
                        {
                            name: 'Messenger Thiên Tâm',
                            href: 'https://m.me',
                            icon: '/icons/messenger.svg'
                        }
                    ];
                    $('#social-links-list').empty();
                    defaultSocial.forEach(function(item, idx) {
                        var iconId = 'social_icon_input_' + idx;
                        var row = `
                            <div class="social-link-row">
                                <div class="social-link-body">
                                    <span class="marquee-tag-badge social-index">${idx + 1}</span>
                                    <div class="social-preview-wrap">
                                        <img src="${item.icon}" class="social-icon-preview" alt="Preview" onerror="this.src='/icons/facebook.svg';" />
                                    </div>
                                    <div class="social-link-fields">
                                        <div class="social-field-item">
                                            <label>Tên mạng xã hội:</label>
                                            <input type="text" name="settings[footer][socialLinks][${idx}][name]" value="${item.name}" placeholder="Ví dụ: Facebook Thiên Tâm" class="social-input-name" />
                                        </div>
                                        <div class="social-field-item">
                                            <label>Đường dẫn liên kết (URL):</label>
                                            <input type="text" name="settings[footer][socialLinks][${idx}][href]" value="${item.href}" placeholder="https://..." class="social-input-href" />
                                        </div>
                                        <div class="social-field-item">
                                            <label>Biểu tượng / Icon:</label>
                                            <div style="display: flex; gap: 8px;">
                                                <input type="text" id="${iconId}" name="settings[footer][socialLinks][${idx}][icon]" value="${item.icon}" placeholder="/icons/... hoặc URL ảnh..." class="social-input-icon" />
                                                <button type="button" class="button thientam-media-btn" data-target="#${iconId}">Chọn ảnh</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="marquee-tag-actions">
                                        <button type="button" class="marquee-action-btn btn-move-social-up" title="Di chuyển lên">
                                            <span class="dashicons dashicons-arrow-up-alt2"></span>
                                        </button>
                                        <button type="button" class="marquee-action-btn btn-move-social-down" title="Di chuyển xuống">
                                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                                        </button>
                                        <button type="button" class="marquee-action-btn btn-delete btn-delete-social" title="Xóa mạng xã hội">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#social-links-list').append(row);
                    });
                    reindexSocialLinks();
                });

                // ==================== PURGE NEXTJS CACHE BUTTON ====================
                $('#btn-purge-nextjs-cache').on('click', function(e) {
                    e.preventDefault();
                    var $btn = $(this);
                    var $status = $('#purge-cache-status');

                    $btn.prop('disabled', true).css('opacity', '0.7');
                    $status.show().css({
                        background: '#f1f5f9',
                        color: '#334155',
                        border: '1px solid #cbd5e1'
                    }).html('<span class="dashicons dashicons-update" style="animation: spin 1s infinite linear;"></span> Đang gửi lệnh xóa cache sang Next.js...');

                    $.post(ajaxurl, {
                        action: 'thientam_purge_nextjs_cache',
                        security: '<?php echo wp_create_nonce("thientam_purge_cache_nonce"); ?>'
                    }, function(res) {
                        $btn.prop('disabled', false).css('opacity', '1');
                        if (res && res.success) {
                            $status.css({
                                background: '#dcfce7',
                                color: '#15803d',
                                border: '1px solid #86efac'
                            }).html('<strong>Thành công:</strong> ' + (res.data.message || 'Đã xóa toàn bộ cache Next.js!'));
                        } else {
                            var err = (res && res.data && res.data.message) ? res.data.message : 'Không thể kết nối đến Next.js';
                            $status.css({
                                background: '#fee2e2',
                                color: '#b91c1c',
                                border: '1px solid #fca5a5'
                            }).html('<strong>Lỗi:</strong> ' + err);
                        }
                        setTimeout(function() {
                            $status.fadeOut(300);
                        }, 6000);
                    }).fail(function() {
                        $btn.prop('disabled', false).css('opacity', '1');
                        $status.css({
                            background: '#fee2e2',
                            color: '#b91c1c',
                            border: '1px solid #fca5a5'
                        }).html('<strong>Lỗi kết nối máy chủ WordPress.</strong>');
                    });
                });
            });
        </script>
    </div>
<?php
}
