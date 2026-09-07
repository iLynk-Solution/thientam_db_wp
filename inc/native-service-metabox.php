<?php

/**
 * Native WordPress Meta Boxes for Service Detail Page with Lucide Icon Picker
 */

if (! defined('ABSPATH')) {
    exit;
}

// Nạp thư viện Lucide Icons và WordPress Media trong WordPress Admin
add_action('admin_enqueue_scripts', 'thientam_admin_enqueue_lucide');
/**
 * @param string $hook
 * @return void
 */
function thientam_admin_enqueue_lucide($hook)
{
    if (in_array($hook, array('post.php', 'post-new.php'))) {
        wp_enqueue_media();
        wp_enqueue_script('lucide-icons', get_stylesheet_directory_uri() . '/js/lucide.min.js', array(), '0.400.0', true);
    }
}

add_action('add_meta_boxes', 'thientam_register_service_detail_metabox', 10, 2);
/**
 * @param string $post_type
 * @param \WP_Post|object $post
 * @return void
 */
function thientam_register_service_detail_metabox($post_type, $post)
{
    if ($post_type !== 'page' || ! $post) {
        return;
    }

    // Không hiển thị trên trang cha dịch vụ (/services)
    if ($post->post_name === 'services' || ($post->post_parent == 0 && $post->ID == 2000)) {
        return;
    }

    $template = get_post_meta($post->ID, '_wp_page_template', true);
    $is_child_page = ($post->post_parent > 0);

    // Chỉ hiển thị khi là trang con hoặc chọn template Trang Chi Tiết Dịch Vụ
    if ($template === 'template-service-detail.php' || $is_child_page) {
        add_meta_box(
            'thientam_service_detail_box',
            '⚙️ Cài đặt Chi tiết Dịch vụ (Service Detail Settings)',
            'thientam_render_service_detail_metabox',
            'page',
            'normal',
            'high'
        );
    }
}

// Ẩn Content Editor mặc định khi dùng template Chi Tiết Dịch Vụ
add_action('admin_init', 'thientam_hide_editor_for_service_detail');
function thientam_hide_editor_for_service_detail()
{
    $post_id = isset($_GET['post']) ? intval($_GET['post']) : (isset($_POST['post_ID']) ? intval($_POST['post_ID']) : 0);
    if (! $post_id) {
        return;
    }
    $template = get_post_meta($post_id, '_wp_page_template', true);
    $post = get_post($post_id);
    $is_child = ($post && $post->post_parent > 0);
    if (($template === 'template-service-detail.php' || $is_child) && (! $post || $post->post_name !== 'services')) {
        remove_post_type_support('page', 'editor');
    }
}

// Ẩn/Hiện metabox và Content Editor theo Template động bằng Javascript trong Admin
add_action('admin_footer-post.php', 'thientam_service_metabox_toggle_js');
add_action('admin_footer-post-new.php', 'thientam_service_metabox_toggle_js');
function thientam_service_metabox_toggle_js()
{
    global $post;
    if (! $post || $post->post_type !== 'page') {
        return;
    }
?>
    <script>
        (function($) {
            function checkTemplate() {
                var template = $('#page_template').val();
                var parentId = $('#parent_id').val();
                var slug = $('#post_name').val();

                if (slug === 'services') {
                    $('#thientam_service_detail_box').hide();
                    $('#postdivrich').show();
                    return;
                }

                if (template === 'template-service-detail.php' || (parentId && parentId > 0)) {
                    $('#thientam_service_detail_box').show();
                    $('#postdivrich').hide();
                } else {
                    $('#thientam_service_detail_box').hide();
                    $('#postdivrich').show();
                }
            }
            $(document).ready(function() {
                checkTemplate();
                $('#page_template, #parent_id').on('change', checkTemplate);
            });
        })(jQuery);
    </script>
<?php
}

/**
 * @param \WP_Post|object $post
 * @return void
 */
function thientam_render_service_detail_metabox($post)
{
    wp_nonce_field('thientam_save_service_meta', 'thientam_service_meta_nonce');

    // Lấy dữ liệu Hero
    $hero_eyebrow   = get_post_meta($post->ID, 'service_hero_eyebrow', true) ?: 'DỊCH VỤ TƯ VẤN CÁ NHÂN';
    $hero_title     = get_post_meta($post->ID, 'service_hero_title', true) ?: $post->post_title;
    $hero_subtitle  = get_post_meta($post->ID, 'service_hero_subtitle', true) ?: '';
    $hero_icon      = get_post_meta($post->ID, 'service_icon', true) ?: 'compass';
    $hero_desc      = get_post_meta($post->ID, 'service_hero_description', true) ?: '';

    // Lấy Tổng Quan
    $overview_prefix    = get_post_meta($post->ID, 'overview_title_prefix', true) ?: 'Khi bạn cần thêm';
    $overview_highlight = get_post_meta($post->ID, 'overview_title_highlight', true) ?: 'một góc nhìn';
    $overview_desc      = get_post_meta($post->ID, 'overview_desc', true) ?: '';
    $overview_cards_count = intval(get_post_meta($post->ID, 'overview_cards', true));
    $overview_cards = array();
    if ($overview_cards_count > 0) {
        for ($i = 0; $i < $overview_cards_count; $i++) {
            $overview_cards[] = array(
                'icon'  => get_post_meta($post->ID, "overview_cards_{$i}_card_icon", true) ?: 'compass',
                'title' => get_post_meta($post->ID, "overview_cards_{$i}_card_title", true),
                'desc'  => get_post_meta($post->ID, "overview_cards_{$i}_card_desc", true),
            );
        }
    } else {
        $overview_cards = array(
            array('icon' => 'briefcase', 'title' => 'Công việc & quyết định', 'desc' => 'Phân vân trong công việc, lựa chọn hướng đi hoặc phương án thực hiện.'),
            array('icon' => 'calendar', 'title' => 'Sự kiện quan trọng', 'desc' => 'Chuẩn bị cho các sự kiện như khai trương, cưới hỏi, ký kết, đầu tư...'),
            array('icon' => 'search', 'title' => 'Tìm người • tìm vật', 'desc' => 'Cần tìm người, tìm vật bị mất hoặc các sự việc liên quan.'),
        );
    }

    // Lấy Bảng Giá (STT tự động theo vị trí dòng)
    $pricing_note        = get_post_meta($post->ID, 'pricing_note', true) ?: 'Nội dung tư vấn mang tính tham khảo và định hướng.';
    $pricing_items_count = intval(get_post_meta($post->ID, 'pricing_items', true));
    $pricing_items = array();
    if ($pricing_items_count > 0) {
        for ($i = 0; $i < $pricing_items_count; $i++) {
            $pricing_items[] = array(
                'service' => get_post_meta($post->ID, "pricing_items_{$i}_service", true),
                'content' => get_post_meta($post->ID, "pricing_items_{$i}_content", true),
                'price'   => get_post_meta($post->ID, "pricing_items_{$i}_price", true),
            );
        }
    } else {
        $pricing_items = array(
            array('service' => 'Luận giải sự việc hiện tại', 'content' => '01 sự việc cụ thể', 'price' => '1.500.000đ'),
            array('service' => 'Các sự kiện liên quan', 'content' => 'Tìm người, tìm vật bị mất', 'price' => '1.500.000đ'),
            array('service' => 'Xem ngày sự kiện quan trọng', 'content' => 'Khai trương, cưới hỏi và các sự kiện quan trọng', 'price' => '1.500.000đ'),
            array('service' => 'Luận quẻ Dịch về công việc', 'content' => 'Xem xét công việc, sự việc cần thực hiện', 'price' => '1.500.000đ'),
        );
    }

    // Lấy Quyền Lợi
    $benefits_prefix    = get_post_meta($post->ID, 'benefits_title_prefix', true) ?: 'Quyền lợi';
    $benefits_highlight = get_post_meta($post->ID, 'benefits_title_highlight', true) ?: 'đồng hành';
    $benefits_desc      = get_post_meta($post->ID, 'benefits_desc', true) ?: 'Thiên Tâm không chỉ dừng lại ở buổi tư vấn mà luôn sẵn sàng đồng hành, hỗ trợ Quý vị thấu đáo trong suốt quá trình áp dụng giải pháp.';
    $benefits_content   = get_post_meta($post->ID, 'benefits_content', true) ?: 'Sau buổi tư vấn, Thiên Tâm sẽ hỗ trợ giải đáp và làm rõ thêm các nội dung liên quan trực tiếp đến sự việc đã tư vấn, tùy theo phạm vi của từng dịch vụ.';

    // Lấy Cấu hình SEO
    $seo_meta_title       = get_post_meta($post->ID, 'seo_meta_title', true) ?: '';
    $seo_meta_description = get_post_meta($post->ID, 'seo_meta_description', true) ?: '';
    $seo_meta_keywords    = get_post_meta($post->ID, 'seo_meta_keywords', true) ?: '';
    $seo_og_image         = get_post_meta($post->ID, 'seo_og_image', true) ?: '';
?>
    <style>
        .tt-service-metabox-wrap * {
            box-sizing: border-box;
        }

        .tt-tabs {
            display: flex;
            border-bottom: 2px solid #2271b1;
            margin: 5px 0 20px 0;
            gap: 6px;
        }

        .tt-tab-btn {
            padding: 9px 18px;
            background: #f0f0f1;
            border: 1px solid #c3c4c7;
            border-bottom: none;
            cursor: pointer;
            font-weight: 600;
            border-radius: 6px 6px 0 0;
            color: #2c3338;
            transition: all 0.2s;
            font-size: 13px;
        }

        .tt-tab-btn:hover {
            background: #e5e5e5;
            color: #1d2327;
        }

        .tt-tab-btn.active {
            background: #2271b1;
            color: #fff;
            border-color: #2271b1;
        }

        .tt-tab-content {
            display: none;
            padding: 5px 0 15px 0;
        }

        .tt-tab-content.active {
            display: block;
        }

        .tt-field-row {
            margin-bottom: 18px;
        }

        .tt-field-row label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #1d2327;
            font-size: 13px;
        }

        .tt-field-row input[type="text"],
        .tt-field-row textarea {
            width: 100%;
            max-width: 850px;
            padding: 8px 12px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            font-size: 13px;
        }

        .tt-field-row input[type="text"]:focus,
        .tt-field-row textarea:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
            outline: 2px solid transparent;
        }

        /* Icon Picker Styles */
        .tt-icon-picker-box {
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 850px;
        }

        .tt-icon-preview {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: #f0f6fc;
            border: 1px solid #c3d9ed;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #174c97;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .tt-icon-preview svg,
        .tt-icon-preview i {
            width: 22px;
            height: 22px;
            stroke-width: 2;
        }

        .tt-icon-picker-compact {
            gap: 6px;
            justify-content: center;
            width: auto;
        }

        .tt-icon-picker-compact .tt-icon-preview {
            width: 32px;
            height: 32px;
        }

        .tt-icon-picker-compact .tt-icon-preview svg,
        .tt-icon-picker-compact .tt-icon-preview i {
            width: 16px;
            height: 16px;
        }

        .tt-icon-picker-compact .tt-btn-pick-icon {
            padding: 6px 8px;
            font-size: 11px;
            line-height: 1;
        }

        .tt-icon-input {
            flex-grow: 1;
            max-width: 260px !important;
            font-family: monospace;
            font-size: 13px;
        }

        .tt-btn-pick-icon {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 14px;
            background: #f6f7f7;
            color: #2271b1;
            border: 1px solid #2271b1;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tt-btn-pick-icon:hover {
            background: #2271b1;
            color: #fff;
        }

        /* Table Layout */
        .tt-table {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: #fff;
            border: 1px solid #c3c4c7;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            border-radius: 6px;
            overflow: hidden;
        }

        .tt-table th,
        .tt-table td {
            padding: 10px 12px;
            border: 1px solid #dcdcde;
            vertical-align: middle;
        }

        .tt-table th {
            background: #f6f7f7;
            font-weight: 600;
            color: #1d2327;
            font-size: 13px;
        }

        .tt-table tbody tr:hover {
            background: #f9fbfd;
        }

        .tt-table td input[type="text"],
        .tt-table td textarea {
            width: 100% !important;
            max-width: 100% !important;
            padding: 7px 10px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            font-size: 13px;
        }

        .tt-table td textarea {
            resize: vertical;
            min-height: 52px;
            line-height: 1.4;
        }

        .tt-row-index {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e7f1fa;
            color: #2271b1;
            font-weight: 700;
            font-size: 13px;
            margin: 4px auto 0 auto;
        }

        .tt-price-input {
            font-weight: 700 !important;
            color: #00705a !important;
        }

        .tt-center {
            text-align: center;
        }

        /* Action Buttons */
        .tt-btn-add {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 14px;
            padding: 8px 16px;
            background: #2271b1;
            color: #fff;
            border: 1px solid #2271b1;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
            transition: all 0.2s;
        }

        .tt-btn-add:hover {
            background: #135e96;
            border-color: #135e96;
            color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.12);
        }

        .tt-btn-remove {
            display: inline-block;
            padding: 5px 10px;
            background: #fcf0f1;
            color: #d63638;
            border: 1px solid #f5c2c7;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .tt-btn-remove:hover {
            background: #d63638;
            color: #fff;
            border-color: #d63638;
        }

        /* Lucide Icon Picker Modal */
        #tt-icon-picker-modal {
            display: none;
            position: fixed;
            z-index: 999999;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(2px);
            align-items: center;
            justify-content: center;
        }

        #tt-icon-picker-modal.active {
            display: flex;
        }

        .tt-modal-window {
            background: #fff;
            width: 90%;
            max-width: 820px;
            max-height: 85vh;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid #c3c4c7;
        }

        .tt-modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
        }

        .tt-modal-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
        }

        .tt-modal-close {
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: #64748b;
            line-height: 1;
            padding: 4px;
        }

        .tt-modal-close:hover {
            color: #d63638;
        }

        .tt-modal-search-wrap {
            padding: 14px 20px 10px;
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
        }

        .tt-modal-search {
            width: 100% !important;
            padding: 10px 14px !important;
            border-radius: 6px !important;
            border: 1px solid #cbd5e1 !important;
            font-size: 14px !important;
        }

        .tt-modal-search:focus {
            border-color: #2271b1 !important;
            box-shadow: 0 0 0 2px rgba(34, 113, 177, 0.2) !important;
        }

        .tt-modal-categories {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 10px;
        }

        .tt-cat-pill {
            padding: 4px 10px;
            border-radius: 20px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.15s;
        }

        .tt-cat-pill:hover,
        .tt-cat-pill.active {
            background: #2271b1;
            color: #fff;
            border-color: #2271b1;
        }

        .tt-modal-body {
            padding: 16px 20px;
            overflow-y: auto;
            flex-grow: 1;
        }

        .tt-icon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(88px, 1fr));
            gap: 10px;
        }

        .tt-icon-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 12px 6px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .tt-icon-item:hover {
            border-color: #2271b1;
            background: #f0f7fc;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(34, 113, 177, 0.12);
        }

        .tt-icon-item.active {
            border-color: #2271b1;
            background: #e7f2fa;
            box-shadow: 0 0 0 2px #2271b1;
        }

        .tt-icon-item svg,
        .tt-icon-item i {
            width: 24px;
            height: 24px;
            color: #174c97;
            margin-bottom: 6px;
            stroke-width: 2;
        }

        .tt-icon-item span {
            font-size: 11px;
            color: #475569;
            word-break: break-all;
            line-height: 1.2;
        }

        .tt-modal-footer {
            padding: 12px 20px;
            background: #f8fafc;
            border-top: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: #64748b;
        }

        /* WordPress Media Picker Styles */
        .tt-media-picker-wrap {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 6px;
        }

        .tt-media-preview-box {
            width: 160px;
            height: 90px;
            border-radius: 8px;
            border: 2px dashed #cbd5e1;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .tt-media-preview-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .tt-media-placeholder {
            font-size: 12px;
            color: #94a3b8;
            text-align: center;
            padding: 4px;
        }

        .tt-media-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: flex-start;
        }

        .tt-btn-media-pick {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            background: #2271b1;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tt-btn-media-pick:hover {
            background: #135e96;
            color: #fff;
        }

        .tt-btn-media-remove {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            background: #fff;
            color: #d63638;
            border: 1px solid #d63638;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tt-btn-media-remove:hover {
            background: #d63638;
            color: #fff;
        }
    </style>

    <div class="tt-service-metabox-wrap">
        <!-- TAB BUTTONS -->
        <div class="tt-tabs">
            <button type="button" class="tt-tab-btn active" data-tab="tab-hero">01. Hero Banner</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-overview">02. Tổng quan</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-pricing">03. Bảng giá</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-benefits">04. Quyền lợi</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-seo">05. Cấu hình SEO</button>
        </div>

        <!-- 01. TAB HERO -->
        <div id="tab-hero" class="tt-tab-content active">
            <div class="tt-field-row">
                <label>Dòng tiêu đề phụ trên (Eyebrow):</label>
                <input type="text" name="service_hero_eyebrow" value="<?php echo esc_attr($hero_eyebrow); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Tiêu đề chính (Title):</label>
                <input type="text" name="service_hero_title" value="<?php echo esc_attr($hero_title); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Tiêu đề phụ dưới (Subtitle):</label>
                <input type="text" name="service_hero_subtitle" value="<?php echo esc_attr($hero_subtitle); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Biểu tượng dịch vụ (Lucide Icon):</label>
                <div class="tt-icon-picker-box">
                    <div class="tt-icon-preview"><i data-lucide="<?php echo esc_attr($hero_icon); ?>"></i></div>
                    <input type="hidden" name="service_icon" class="tt-icon-input" value="<?php echo esc_attr($hero_icon); ?>" placeholder="compass" oninput="ttUpdateIconPreview(this)" />
                    <button type="button" class="tt-btn-pick-icon" onclick="ttOpenIconPicker(this)">🔍 Chọn Icon...</button>
                </div>
                <p class="description" style="margin-top: 5px;">Chọn icon từ thư viện Lucide hoặc nhập trực tiếp tên icon (ví dụ: compass, smartphone, home, sparkles, users, trending-up, briefcase...).</p>
            </div>
            <div class="tt-field-row">
                <label>Mô tả ngắn Hero:</label>
                <textarea name="service_hero_description" rows="3"><?php echo esc_textarea($hero_desc); ?></textarea>
            </div>
        </div>

        <!-- 02. TAB OVERVIEW -->
        <div id="tab-overview" class="tt-tab-content">
            <div class="tt-field-row">
                <label>Tiêu đề đầu:</label>
                <input type="text" name="overview_title_prefix" value="<?php echo esc_attr($overview_prefix); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Tiêu đề nổi bật (Highlight):</label>
                <input type="text" name="overview_title_highlight" value="<?php echo esc_attr($overview_highlight); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Mô tả tổng quan:</label>
                <textarea name="overview_desc" rows="3"><?php echo esc_textarea($overview_desc); ?></textarea>
            </div>
            <hr style="margin: 24px 0; border: 0; border-top: 1px solid #ddd;" />
            <h3 style="margin-bottom: 8px;">Danh sách thẻ điểm nổi bật (Overview Cards):</h3>
            <table class="tt-table" id="tt-overview-cards-table">
                <thead>
                    <tr>
                        <th style="width: 90px;" class="tt-center">Icon</th>
                        <th style="width: 32%;">Tiêu đề thẻ</th>
                        <th style="width: 48%;">Mô tả thẻ</th>
                        <th style="width: 70px;" class="tt-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($overview_cards as $c) : ?>
                        <tr>
                            <td class="tt-center">
                                <div class="tt-icon-picker-box tt-icon-picker-compact">
                                    <div class="tt-icon-preview"><i data-lucide="<?php echo esc_attr($c['icon']); ?>"></i></div>
                                    <input type="hidden" name="card_icon[]" class="tt-icon-input" value="<?php echo esc_attr($c['icon']); ?>" placeholder="compass" oninput="ttUpdateIconPreview(this)" />
                                    <button type="button" class="tt-btn-pick-icon" onclick="ttOpenIconPicker(this)">🔍</button>
                                </div>
                            </td>
                            <td><input type="text" name="card_title[]" value="<?php echo esc_attr($c['title']); ?>" /></td>
                            <td><textarea name="card_desc[]" rows="2"><?php echo esc_textarea($c['desc']); ?></textarea></td>
                            <td class="tt-center"><button type="button" class="tt-btn-remove" onclick="this.closest('tr').remove()">Xóa</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="button" class="tt-btn-add" onclick="ttAddOverviewCardRow()">+ Thêm Thẻ Tổng Quan</button>
        </div>

        <!-- 03. TAB PRICING (STT hiển thị tự động) -->
        <div id="tab-pricing" class="tt-tab-content">
            <h3 style="margin-bottom: 8px;">Các gói dịch vụ trong Bảng Giá:</h3>
            <table class="tt-table" id="tt-pricing-items-table">
                <thead>
                    <tr>
                        <th style="width: 55px;" class="tt-center">#</th>
                        <th style="width: 34%;">Hạng mục tư vấn</th>
                        <th style="width: 44%;">Nội dung chi tiết</th>
                        <th style="width: 18%;">Phí tư vấn</th>
                        <th style="width: 70px;" class="tt-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pricing_items as $idx => $p) : ?>
                        <tr>
                            <td class="tt-center"><span class="tt-row-index"><?php echo esc_html($idx + 1); ?></span></td>
                            <td><input type="text" name="pricing_service[]" value="<?php echo esc_attr($p['service']); ?>" placeholder="Tên gói tư vấn" /></td>
                            <td><textarea name="pricing_content[]" rows="2" placeholder="Nội dung chi tiết"><?php echo esc_textarea($p['content']); ?></textarea></td>
                            <td><input type="text" class="tt-price-input" name="pricing_price[]" value="<?php echo esc_attr($p['price']); ?>" placeholder="1.500.000đ" /></td>
                            <td class="tt-center"><button type="button" class="tt-btn-remove" onclick="ttRemovePricingRow(this)">Xóa</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="button" class="tt-btn-add" onclick="ttAddPricingRow()">+ Thêm Gói Bảng Giá</button>
            <div class="tt-field-row" style="margin-top: 24px;">
                <label>Ghi chú chân bảng giá (Note):</label>
                <input type="text" name="pricing_note" value="<?php echo esc_attr($pricing_note); ?>" />
            </div>
        </div>

        <!-- 04. TAB BENEFITS -->
        <div id="tab-benefits" class="tt-tab-content">
            <div class="tt-field-row">
                <label>Tiêu đề đầu quyền lợi:</label>
                <input type="text" name="benefits_title_prefix" value="<?php echo esc_attr($benefits_prefix); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Tiêu đề nổi bật quyền lợi:</label>
                <input type="text" name="benefits_title_highlight" value="<?php echo esc_attr($benefits_highlight); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Mô tả phụ quyền lợi:</label>
                <textarea name="benefits_desc" rows="2"><?php echo esc_textarea($benefits_desc); ?></textarea>
            </div>
            <div class="tt-field-row">
                <label>Nội dung quyền lợi chi tiết:</label>
                <textarea name="benefits_content" rows="4"><?php echo esc_textarea($benefits_content); ?></textarea>
            </div>
        </div>

        <!-- 05. TAB SEO -->
        <div id="tab-seo" class="tt-tab-content">
            <div class="tt-field-row">
                <label>Tiêu đề SEO tùy biến (Meta Title):</label>
                <input type="text" name="seo_meta_title" value="<?php echo esc_attr($seo_meta_title); ?>" placeholder="Để trống sẽ tự động lấy Tiêu đề trang..." />
                <p class="description" style="margin-top: 4px;">Tiêu đề hiển thị trên kết quả tìm kiếm Google và thẻ chia sẻ link mạng xã hội.</p>
            </div>
            <div class="tt-field-row">
                <label>Mô tả SEO tùy biến (Meta Description):</label>
                <textarea name="seo_meta_description" rows="3" placeholder="Để trống sẽ tự động lấy Mô tả ngắn Hero..."><?php echo esc_textarea($seo_meta_description); ?></textarea>
                <p class="description" style="margin-top: 4px;">Đoạn tóm tắt hiển thị dưới tiêu đề trang trên Google (khuyến nghị 120-160 ký tự).</p>
            </div>
            <div class="tt-field-row">
                <label>Từ khóa SEO (Meta Keywords):</label>
                <input type="text" name="seo_meta_keywords" value="<?php echo esc_attr($seo_meta_keywords); ?>" placeholder="phong thủy nhà ở, tư vấn tử vi, chuyên gia phong thủy..." />
                <p class="description" style="margin-top: 4px;">Các từ khóa cách nhau bởi dấu phẩy.</p>
            </div>
            <div class="tt-field-row">
                <label>Ảnh chia sẻ Mạng Xã Hội (OG Image):</label>
                <div class="tt-media-picker-wrap">
                    <div class="tt-media-preview-box" id="tt-seo-og-image-preview">
                        <?php if (! empty($seo_og_image)) : ?>
                            <img src="<?php echo esc_url($seo_og_image); ?>" alt="OG Image Preview" />
                        <?php else : ?>
                            <span class="tt-media-placeholder">Chưa chọn ảnh</span>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="seo_og_image" id="tt-seo-og-image-input" value="<?php echo esc_attr($seo_og_image); ?>" />
                    <div class="tt-media-actions">
                        <button type="button" class="tt-btn-media-pick" onclick="ttOpenWpMediaPicker()">🖼️ Chọn ảnh từ Media...</button>
                        <button type="button" class="tt-btn-media-remove" id="tt-seo-og-image-remove-btn" onclick="ttRemoveSeoOgImage()" style="<?php echo empty($seo_og_image) ? 'display:none;' : ''; ?>">Xóa ảnh</button>
                    </div>
                </div>
                <p class="description" style="margin-top: 6px;">Kích thước ảnh khuyến nghị 1200x630px khi share link lên Facebook, Zalo, Telegram. (Để trống sẽ tự động lấy Ảnh đại diện / Featured Image).</p>
            </div>
        </div>
    </div>

    <!-- LUCIDE ICON PICKER MODAL -->
    <div id="tt-icon-picker-modal">
        <div class="tt-modal-window">
            <div class="tt-modal-header">
                <h3>Thư viện biểu tượng Lucide (Icon Picker)</h3>
                <button type="button" class="tt-modal-close" onclick="ttCloseIconPicker()">&times;</button>
            </div>
            <div class="tt-modal-search-wrap">
                <input type="text" class="tt-modal-search" id="tt-icon-search-input" placeholder="🔍 Tìm kiếm icon (ví dụ: compass, star, user, chart, home...)..." oninput="ttFilterIcons()" />
                <div class="tt-modal-categories">
                    <button type="button" class="tt-cat-pill active" onclick="ttFilterCategory('all', this)">Tất cả</button>
                    <button type="button" class="tt-cat-pill" onclick="ttFilterCategory('fengshui', this)">Phong thủy & Tâm linh</button>
                    <button type="button" class="tt-cat-pill" onclick="ttFilterCategory('business', this)">Kinh doanh & Tài chính</button>
                    <button type="button" class="tt-cat-pill" onclick="ttFilterCategory('home', this)">Không gian & Nhà ở</button>
                    <button type="button" class="tt-cat-pill" onclick="ttFilterCategory('people', this)">Gia đình & Xã hội</button>
                    <button type="button" class="tt-cat-pill" onclick="ttFilterCategory('tech', this)">Thiết bị & Số hóa</button>
                </div>
            </div>
            <div class="tt-modal-body">
                <div class="tt-icon-grid" id="tt-icon-grid">
                    <!-- Icon Items injected by JS -->
                </div>
            </div>
            <div class="tt-modal-footer">
                <span id="tt-icon-count-label">Hiển thị 150+ icon phổ biến</span>
                <span>Click vào biểu tượng để chọn</span>
            </div>
        </div>
    </div>

    <script>
        // Danh sách icons phân loại
        var TT_LUCIDE_ICONS = [
            // Phong thủy & Tâm linh
            {
                name: "compass",
                cat: "fengshui",
                label: "Compass"
            },
            {
                name: "sparkles",
                cat: "fengshui",
                label: "Sparkles"
            },
            {
                name: "sun",
                cat: "fengshui",
                label: "Sun"
            },
            {
                name: "moon",
                cat: "fengshui",
                label: "Moon"
            },
            {
                name: "flame",
                cat: "fengshui",
                label: "Flame"
            },
            {
                name: "droplet",
                cat: "fengshui",
                label: "Droplet"
            },
            {
                name: "wind",
                cat: "fengshui",
                label: "Wind"
            },
            {
                name: "mountain",
                cat: "fengshui",
                label: "Mountain"
            },
            {
                name: "trees",
                cat: "fengshui",
                label: "Trees"
            },
            {
                name: "leaf",
                cat: "fengshui",
                label: "Leaf"
            },
            {
                name: "flower-2",
                cat: "fengshui",
                label: "Flower"
            },
            {
                name: "feather",
                cat: "fengshui",
                label: "Feather"
            },
            {
                name: "star",
                cat: "fengshui",
                label: "Star"
            },
            {
                name: "gem",
                cat: "fengshui",
                label: "Gem"
            },
            {
                name: "crown",
                cat: "fengshui",
                label: "Crown"
            },
            {
                name: "eye",
                cat: "fengshui",
                label: "Eye"
            },
            {
                name: "scroll",
                cat: "fengshui",
                label: "Scroll"
            },
            {
                name: "scale",
                cat: "fengshui",
                label: "Scale"
            },
            {
                name: "key",
                cat: "fengshui",
                label: "Key"
            },

            // Kinh doanh & Tài chính
            {
                name: "briefcase",
                cat: "business",
                label: "Briefcase"
            },
            {
                name: "trending-up",
                cat: "business",
                label: "Trending Up"
            },
            {
                name: "line-chart",
                cat: "business",
                label: "Line Chart"
            },
            {
                name: "bar-chart-3",
                cat: "business",
                label: "Bar Chart"
            },
            {
                name: "pie-chart",
                cat: "business",
                label: "Pie Chart"
            },
            {
                name: "landmark",
                cat: "business",
                label: "Landmark"
            },
            {
                name: "dollar-sign",
                cat: "business",
                label: "Dollar Sign"
            },
            {
                name: "wallet",
                cat: "business",
                label: "Wallet"
            },
            {
                name: "credit-card",
                cat: "business",
                label: "Credit Card"
            },
            {
                name: "badge-percent",
                cat: "business",
                label: "Percent"
            },
            {
                name: "calculator",
                cat: "business",
                label: "Calculator"
            },
            {
                name: "target",
                cat: "business",
                label: "Target"
            },
            {
                name: "award",
                cat: "business",
                label: "Award"
            },
            {
                name: "shield-check",
                cat: "business",
                label: "Shield Check"
            },
            {
                name: "file-text",
                cat: "business",
                label: "File Text"
            },
            {
                name: "clipboard-list",
                cat: "business",
                label: "Clipboard"
            },
            {
                name: "handshake",
                cat: "business",
                label: "Handshake"
            },

            // Không gian & Nhà ở
            {
                name: "home",
                cat: "home",
                label: "Home"
            },
            {
                name: "building",
                cat: "home",
                label: "Building"
            },
            {
                name: "building-2",
                cat: "home",
                label: "Building 2"
            },
            {
                name: "store",
                cat: "home",
                label: "Store"
            },
            {
                name: "map-pin",
                cat: "home",
                label: "Map Pin"
            },
            {
                name: "navigation",
                cat: "home",
                label: "Navigation"
            },
            {
                name: "layers",
                cat: "home",
                label: "Layers"
            },
            {
                name: "layout-grid",
                cat: "home",
                label: "Grid"
            },
            {
                name: "box",
                cat: "home",
                label: "Box"
            },
            {
                name: "package",
                cat: "home",
                label: "Package"
            },
            {
                name: "lamp",
                cat: "home",
                label: "Lamp"
            },
            {
                name: "door-open",
                cat: "home",
                label: "Door Open"
            },

            // Gia đình & Con người
            {
                name: "users",
                cat: "people",
                label: "Users"
            },
            {
                name: "user",
                cat: "people",
                label: "User"
            },
            {
                name: "user-check",
                cat: "people",
                label: "User Check"
            },
            {
                name: "user-plus",
                cat: "people",
                label: "User Plus"
            },
            {
                name: "heart",
                cat: "people",
                label: "Heart"
            },
            {
                name: "heart-handshake",
                cat: "people",
                label: "Harmony"
            },
            {
                name: "smile",
                cat: "people",
                label: "Smile"
            },
            {
                name: "baby",
                cat: "people",
                label: "Baby"
            },
            {
                name: "shield",
                cat: "people",
                label: "Shield"
            },
            {
                name: "help-circle",
                cat: "people",
                label: "Help"
            },
            {
                name: "message-circle",
                cat: "people",
                label: "Message"
            },
            {
                name: "phone-call",
                cat: "people",
                label: "Phone Call"
            },

            // Thiết bị & Số hóa
            {
                name: "smartphone",
                cat: "tech",
                label: "Smartphone"
            },
            {
                name: "phone",
                cat: "tech",
                label: "Phone"
            },
            {
                name: "laptop",
                cat: "tech",
                label: "Laptop"
            },
            {
                name: "globe",
                cat: "tech",
                label: "Globe"
            },
            {
                name: "search",
                cat: "tech",
                label: "Search"
            },
            {
                name: "calendar",
                cat: "tech",
                label: "Calendar"
            },
            {
                name: "clock",
                cat: "tech",
                label: "Clock"
            },
            {
                name: "mail",
                cat: "tech",
                label: "Mail"
            },
            {
                name: "settings",
                cat: "tech",
                label: "Settings"
            },
            {
                name: "sliders",
                cat: "tech",
                label: "Sliders"
            },
            {
                name: "zap",
                cat: "tech",
                label: "Zap"
            },
            {
                name: "book-open",
                cat: "tech",
                label: "Book Open"
            },
            {
                name: "check-circle",
                cat: "tech",
                label: "Check Circle"
            },
            {
                name: "check-circle-2",
                cat: "tech",
                label: "Check 2"
            },
            {
                name: "check",
                cat: "tech",
                label: "Check"
            },
            {
                name: "lightbulb",
                cat: "tech",
                label: "Lightbulb"
            },
            {
                name: "activity",
                cat: "tech",
                label: "Activity"
            },
            {
                name: "gift",
                cat: "tech",
                label: "Gift"
            },
            {
                name: "coffee",
                cat: "tech",
                label: "Coffee"
            },
            {
                name: "send",
                cat: "tech",
                label: "Send"
            },
            {
                name: "share-2",
                cat: "tech",
                label: "Share"
            },
            {
                name: "lock",
                cat: "tech",
                label: "Lock"
            },
            {
                name: "unlock",
                cat: "tech",
                label: "Unlock"
            },
            {
                name: "bell",
                cat: "tech",
                label: "Bell"
            },
            {
                name: "bookmark",
                cat: "tech",
                label: "Bookmark"
            },
            {
                name: "anchor",
                cat: "tech",
                label: "Anchor"
            }
        ];

        var ttActivePickerTarget = null;
        var ttCurrentCat = 'all';

        function ttInitLucide() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        // Khởi tạo icons khi load trang
        document.addEventListener('DOMContentLoaded', function() {
            ttInitLucide();
            ttRenderIconGrid();
        });

        // Tab chuyển đổi
        document.querySelectorAll('.tt-tab-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tt-tab-btn').forEach(function(b) {
                    b.classList.remove('active');
                });
                document.querySelectorAll('.tt-tab-content').forEach(function(c) {
                    c.classList.remove('active');
                });
                btn.classList.add('active');
                document.getElementById(btn.getAttribute('data-tab')).classList.add('active');
            });
        });

        // Cập nhật preview icon khi gõ trực tiếp tên
        function ttUpdateIconPreview(input) {
            var box = input.closest('.tt-icon-picker-box');
            if (!box) return;
            var preview = box.querySelector('.tt-icon-preview');
            var iconName = input.value.trim().toLowerCase() || 'compass';
            preview.innerHTML = '<i data-lucide="' + iconName + '"></i>';
            ttInitLucide();
        }

        // Mở Modal Icon Picker
        function ttOpenIconPicker(btn) {
            var box = btn.closest('.tt-icon-picker-box');
            if (!box) return;
            ttActivePickerTarget = box.querySelector('.tt-icon-input');

            var modal = document.getElementById('tt-icon-picker-modal');
            modal.classList.add('active');

            var currentVal = ttActivePickerTarget ? ttActivePickerTarget.value.trim().toLowerCase() : '';
            document.getElementById('tt-icon-search-input').value = '';
            ttFilterCategory('all');

            // Highlight current active icon
            document.querySelectorAll('.tt-icon-item').forEach(function(item) {
                if (item.getAttribute('data-icon') === currentVal) {
                    item.classList.add('active');
                    item.scrollIntoView({
                        block: 'center'
                    });
                } else {
                    item.classList.remove('active');
                }
            });

            document.getElementById('tt-icon-search-input').focus();
        }

        function ttCloseIconPicker() {
            var modal = document.getElementById('tt-icon-picker-modal');
            modal.classList.remove('active');
            ttActivePickerTarget = null;
        }

        function ttRenderIconGrid() {
            var grid = document.getElementById('tt-icon-grid');
            if (!grid) return;
            grid.innerHTML = '';

            TT_LUCIDE_ICONS.forEach(function(icon) {
                var item = document.createElement('div');
                item.className = 'tt-icon-item';
                item.setAttribute('data-icon', icon.name);
                item.setAttribute('data-cat', icon.cat);
                item.innerHTML = '<i data-lucide="' + icon.name + '"></i><span>' + icon.name + '</span>';
                item.onclick = function() {
                    ttSelectIcon(icon.name);
                };
                grid.appendChild(item);
            });
            ttInitLucide();
        }

        function ttSelectIcon(iconName) {
            if (ttActivePickerTarget) {
                ttActivePickerTarget.value = iconName;
                ttUpdateIconPreview(ttActivePickerTarget);
            }
            ttCloseIconPicker();
        }

        function ttFilterCategory(cat, pillBtn) {
            ttCurrentCat = cat;
            if (pillBtn) {
                document.querySelectorAll('.tt-cat-pill').forEach(function(p) {
                    p.classList.remove('active');
                });
                pillBtn.classList.add('active');
            }
            ttFilterIcons();
        }

        function ttFilterIcons() {
            var query = (document.getElementById('tt-icon-search-input').value || '').trim().toLowerCase();
            var items = document.querySelectorAll('.tt-icon-item');
            var visibleCount = 0;

            items.forEach(function(item) {
                var iconName = item.getAttribute('data-icon');
                var cat = item.getAttribute('data-cat');
                var matchCat = (ttCurrentCat === 'all' || cat === ttCurrentCat);
                var matchQuery = (!query || iconName.indexOf(query) !== -1);

                if (matchCat && matchQuery) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            var countLabel = document.getElementById('tt-icon-count-label');
            if (countLabel) {
                countLabel.textContent = 'Tìm thấy ' + visibleCount + ' icon';
            }
        }

        // Đóng modal khi bấm ra ngoài
        document.getElementById('tt-icon-picker-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                ttCloseIconPicker();
            }
        });

        // Overview Cards row
        function ttAddOverviewCardRow() {
            var tbody = document.querySelector('#tt-overview-cards-table tbody');
            var tr = document.createElement('tr');
            tr.innerHTML = '<td class="tt-center">' +
                '  <div class="tt-icon-picker-box tt-icon-picker-compact">' +
                '    <div class="tt-icon-preview"><i data-lucide="compass"></i></div>' +
                '    <input type="hidden" name="card_icon[]" class="tt-icon-input" value="compass" placeholder="compass" oninput="ttUpdateIconPreview(this)" />' +
                '    <button type="button" class="tt-btn-pick-icon" onclick="ttOpenIconPicker(this)">🔍</button>' +
                '  </div>' +
                '</td>' +
                '<td><input type="text" name="card_title[]" placeholder="Tiêu đề thẻ" /></td>' +
                '<td><textarea name="card_desc[]" rows="2" placeholder="Mô tả thẻ"></textarea></td>' +
                '<td class="tt-center"><button type="button" class="tt-btn-remove" onclick="this.closest(\'tr\').remove()">Xóa</button></td>';
            tbody.appendChild(tr);
            ttInitLucide();
        }

        // Pricing Rows
        function ttAddPricingRow() {
            var tbody = document.querySelector('#tt-pricing-items-table tbody');
            var rowCount = tbody.querySelectorAll('tr').length + 1;
            var tr = document.createElement('tr');
            tr.innerHTML = '<td class="tt-center"><span class="tt-row-index">' + rowCount + '</span></td>' +
                '<td><input type="text" name="pricing_service[]" placeholder="Tên gói tư vấn" /></td>' +
                '<td><textarea name="pricing_content[]" rows="2" placeholder="Nội dung chi tiết"></textarea></td>' +
                '<td><input type="text" class="tt-price-input" name="pricing_price[]" placeholder="1.500.000đ" /></td>' +
                '<td class="tt-center"><button type="button" class="tt-btn-remove" onclick="ttRemovePricingRow(this)">Xóa</button></td>';
            tbody.appendChild(tr);
        }

        function ttRemovePricingRow(btn) {
            var tr = btn.closest('tr');
            var tbody = tr.parentNode;
            tr.remove();
            // Re-index rows
            var rows = tbody.querySelectorAll('tr');
            rows.forEach(function(r, index) {
                var badge = r.querySelector('.tt-row-index');
                if (badge) {
                    badge.textContent = index + 1;
                }
            });
        }

        // WordPress Media Picker for SEO OG Image
        var ttSeoMediaFrame = null;
        function ttOpenWpMediaPicker() {
            if (ttSeoMediaFrame) {
                ttSeoMediaFrame.open();
                return;
            }
            ttSeoMediaFrame = wp.media({
                title: 'Chọn ảnh chia sẻ Mạng Xã Hội (OG Image)',
                button: {
                    text: 'Sử dụng ảnh này'
                },
                multiple: false
            });
            ttSeoMediaFrame.on('select', function() {
                var attachment = ttSeoMediaFrame.state().get('selection').first().toJSON();
                var url = attachment.url;
                document.getElementById('tt-seo-og-image-input').value = url;
                var previewBox = document.getElementById('tt-seo-og-image-preview');
                previewBox.innerHTML = '<img src="' + url + '" alt="OG Image Preview" />';
                document.getElementById('tt-seo-og-image-remove-btn').style.display = 'inline-flex';
            });
            ttSeoMediaFrame.open();
        }

        function ttRemoveSeoOgImage() {
            document.getElementById('tt-seo-og-image-input').value = '';
            var previewBox = document.getElementById('tt-seo-og-image-preview');
            previewBox.innerHTML = '<span class="tt-media-placeholder">Chưa chọn ảnh</span>';
            document.getElementById('tt-seo-og-image-remove-btn').style.display = 'none';
        }
    </script>
<?php
}

add_action('save_post_page', 'thientam_save_service_detail_metabox_data', 10, 2);
/**
 * @param int $post_id
 * @param \WP_Post|object $post
 * @return void
 */
function thientam_save_service_detail_metabox_data($post_id, $post)
{
    if (! isset($_POST['thientam_service_meta_nonce']) || ! wp_verify_nonce($_POST['thientam_service_meta_nonce'], 'thientam_save_service_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (! current_user_can('edit_page', $post_id)) {
        return;
    }

    $text_fields = array(
        'service_hero_eyebrow',
        'service_hero_title',
        'service_hero_subtitle',
        'service_icon',
        'overview_title_prefix',
        'overview_title_highlight',
        'benefits_title_prefix',
        'benefits_title_highlight',
        'seo_meta_title',
        'seo_meta_keywords',
        'seo_og_image'
    );
    foreach ($text_fields as $f) {
        if (isset($_POST[$f])) {
            update_post_meta($post_id, $f, sanitize_text_field(wp_unslash($_POST[$f])));
        }
    }

    $textarea_fields = array(
        'service_hero_description',
        'overview_desc',
        'pricing_note',
        'benefits_desc',
        'benefits_content',
        'seo_meta_description'
    );
    foreach ($textarea_fields as $f) {
        if (isset($_POST[$f])) {
            update_post_meta($post_id, $f, sanitize_textarea_field(wp_unslash($_POST[$f])));
        }
    }

    // Lưu Overview Cards
    if (isset($_POST['card_title']) && is_array($_POST['card_title'])) {
        $count = count($_POST['card_title']);
        update_post_meta($post_id, 'overview_cards', $count);
        for ($i = 0; $i < $count; $i++) {
            $icon  = isset($_POST['card_icon'][$i]) ? sanitize_text_field(wp_unslash($_POST['card_icon'][$i])) : 'compass';
            $title = isset($_POST['card_title'][$i]) ? sanitize_text_field(wp_unslash($_POST['card_title'][$i])) : '';
            $desc  = isset($_POST['card_desc'][$i]) ? sanitize_textarea_field(wp_unslash($_POST['card_desc'][$i])) : '';
            update_post_meta($post_id, "overview_cards_{$i}_card_icon", $icon);
            update_post_meta($post_id, "overview_cards_{$i}_card_title", $title);
            update_post_meta($post_id, "overview_cards_{$i}_card_desc", $desc);
        }
    }

    // Lưu Pricing Items (STT tự động)
    if (isset($_POST['pricing_service']) && is_array($_POST['pricing_service'])) {
        $count = count($_POST['pricing_service']);
        update_post_meta($post_id, 'pricing_items', $count);
        for ($i = 0; $i < $count; $i++) {
            $stt_formatted = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
            $srv   = isset($_POST['pricing_service'][$i]) ? sanitize_text_field(wp_unslash($_POST['pricing_service'][$i])) : '';
            $cnt   = isset($_POST['pricing_content'][$i]) ? sanitize_textarea_field(wp_unslash($_POST['pricing_content'][$i])) : '';
            $prc   = isset($_POST['pricing_price'][$i]) ? sanitize_text_field(wp_unslash($_POST['pricing_price'][$i])) : '';
            update_post_meta($post_id, "pricing_items_{$i}_stt", $stt_formatted);
            update_post_meta($post_id, "pricing_items_{$i}_service", $srv);
            update_post_meta($post_id, "pricing_items_{$i}_content", $cnt);
            update_post_meta($post_id, "pricing_items_{$i}_price", $prc);
        }
    }
}
