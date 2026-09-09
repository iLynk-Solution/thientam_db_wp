<?php

/**
 * Quản lý Cấu hình & Metabox/ACF Fields cho các trang Landing Page Thiên Tâm
 * Tương thích 100% cả khi có hoặc KHÔNG CÓ plugin ACF.
 *
 * @package ThienTamData
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Danh sách các trang Landing được hỗ trợ trên toàn hệ thống
 */
function thientam_get_supported_landing_pages()
{
    return array(
        'hieu-con-de-dong-hanh' => array(
            'slug'  => 'hieu-con-de-dong-hanh',
            'name'  => 'Hiểu Con Để Đồng Hành',
            'route' => '/landing/hieu-con-de-dong-hanh',
        ),
    );
}

/**
 * Lấy cấu trúc nội dung mặc định của landing page trực tiếp trong PHP
 */
function thientam_get_landing_default_data($slug)
{
    if ($slug !== 'hieu-con-de-dong-hanh') {
        return null;
    }

    static $cache = array();
    if (isset($cache[$slug])) {
        return $cache[$slug];
    }

    // Đọc toàn bộ dữ liệu từ file JSON gốc trong thư mục inc
    $json_file = __DIR__ . '/landing-default-hieu-con.json';
    if (file_exists($json_file)) {
        $content = file_get_contents($json_file);
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $cache[$slug] = $decoded;
            return $decoded;
        }
    }

    return array();
}

/**
 * 1. Đăng ký Meta Boxes NATIVE cho Landing Pages (Chạy thuần WordPress)
 */
add_action('add_meta_boxes', 'thientam_register_landing_metaboxes', 10, 2);
function thientam_register_landing_metaboxes($post_type, $post)
{
    if ($post_type !== 'page' || ! $post) {
        return;
    }

    // Metabox chọn loại Landing ở Sidebar
    add_meta_box(
        'thientam_landing_tools_box',
        'Mẫu Landing Page',
        'thientam_render_landing_sidebar_metabox',
        'page',
        'side',
        'high'
    );

    // Metabox chi tiết nội dung Landing ở vùng chính (hiển thị khi chọn hieu-con-de-dong-hanh)
    $landing_type = get_post_meta($post->ID, 'thientam_landing_type', true);
    if ($landing_type === 'hieu-con-de-dong-hanh') {
        add_meta_box(
            'thientam_landing_content_box',
            'Cấu hình Nội dung: Hiểu Con Để Đồng Hành',
            'thientam_render_landing_content_metabox',
            'page',
            'normal',
            'high'
        );
    }
}

// Ẩn Content Editor và Excerpt mặc định khi trang là Landing Page
add_action('admin_init', 'thientam_hide_editor_excerpt_for_landing');
function thientam_hide_editor_excerpt_for_landing()
{
    $post_id = isset($_GET['post']) ? intval($_GET['post']) : (isset($_POST['post_ID']) ? intval($_POST['post_ID']) : 0);
    if (! $post_id) {
        return;
    }
    $landing_type = get_post_meta($post_id, 'thientam_landing_type', true);
    if ($landing_type && $landing_type !== 'none') {
        remove_post_type_support('page', 'editor');
        remove_post_type_support('page', 'excerpt');
    }
}

// Ẩn/Hiện động Content Editor và Excerpt ngay khi thay đổi dropdown Landing Type
add_action('admin_footer-post.php', 'thientam_landing_metabox_toggle_js');
add_action('admin_footer-post-new.php', 'thientam_landing_metabox_toggle_js');
function thientam_landing_metabox_toggle_js()
{
    global $post;
    if (! $post || $post->post_type !== 'page') {
        return;
    }
?>
    <script>
        (function($) {
            function checkLandingType() {
                var landingType = $('#thientam_landing_type').val();
                if (landingType && landingType !== 'none') {
                    $('#postdivrich').hide();
                    $('#postexcerpt').hide();
                } else {
                    $('#postdivrich').show();
                    $('#postexcerpt').show();
                }
            }

            $(document).ready(function() {
                checkLandingType();
                $('#thientam_landing_type').on('change', checkLandingType);
            });
        })(jQuery);
    </script>
<?php
}

/**
 * Render Sidebar Metabox: Cho phép chọn loại Landing trực tiếp
 */
function thientam_render_landing_sidebar_metabox($post)
{
    wp_nonce_field('thientam_save_landing_meta', 'thientam_landing_meta_nonce');

    $landing_type = get_post_meta($post->ID, 'thientam_landing_type', true) ?: 'none';
    $landings = thientam_get_supported_landing_pages();
?>
    <div style="font-size:13px; line-height:1.5;">
        <label for="thientam_landing_type" style="font-weight:600; display:block; margin-bottom:6px; color:#1d2327;">
            Chọn loại trang Landing:
        </label>
        <select name="thientam_landing_type" id="thientam_landing_type" style="width:100%; margin-bottom:12px; height:32px;">
            <option value="none" <?php selected($landing_type, 'none'); ?>>— Không dùng (Trang thường) —</option>
            <?php foreach ($landings as $slug => $info) : ?>
                <option value="<?php echo esc_attr($slug); ?>" <?php selected($landing_type, $slug); ?>>
                    <?php echo esc_html($info['name']); ?> (<?php echo esc_html($slug); ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($landing_type !== 'none' && isset($landings[$landing_type])) : ?>
            <?php $api_url = rest_url("thientam/v1/landing/{$landing_type}"); ?>
            <div style="background:#f0f6fc; border:1px solid #cce5ff; border-radius:6px; padding:10px; margin-bottom:12px;">
                <p style="margin:0 0 6px 0; color:#0c5460; font-size:12px;">
                    <strong>Đang kích hoạt:</strong> <?php echo esc_html($landings[$landing_type]['name']); ?>
                </p>
                <p style="margin:0 0 8px 0; font-size:11px; color:#666;">
                    Route Next.js: <code><?php echo esc_html($landings[$landing_type]['route']); ?></code>
                </p>
                <a href="<?php echo esc_url($api_url); ?>" target="_blank" class="button button-secondary button-small" style="width:100%; text-align:center; display:block;">
                    🔍 Xem REST API JSON
                </a>
            </div>

            <div style="border-top:1px solid #eee; padding-top:8px;">
                <label style="font-size:12px; color:#d63638; cursor:pointer; display:flex; align-items:flex-start; gap:6px;">
                    <input type="checkbox" name="thientam_reset_landing_defaults" value="1" style="margin-top:2px;" />
                    <span>Nạp lại toàn bộ dữ liệu mặc định hệ thống khi bấm Cập nhật</span>
                </label>
            </div>
        <?php else : ?>
            <p style="color:#666; font-size:12px; margin:0;">
                Hãy chọn <strong>Hiểu Con Để Đồng Hành</strong> và bấm <strong>Cập nhật / Lưu nháp</strong> để tải bảng chỉnh sửa nội dung chi tiết.
            </p>
        <?php endif; ?>
    </div>
<?php
}

/**
 * Render Content Metabox: Giao diện Tab chỉnh sửa toàn bộ nội dung landing
 */
function thientam_render_landing_content_metabox($post)
{
    $defaults = thientam_get_landing_default_data('hieu-con-de-dong-hanh') ?: array();

    // Helper lấy giá trị ưu tiên: post_meta > default
    $get_val = function ($meta_key, $default_val = '') use ($post) {
        $meta = get_post_meta($post->ID, $meta_key, true);
        return ($meta !== '' && $meta !== null && $meta !== false) ? $meta : $default_val;
    };

    $meta_title     = $get_val('landing_meta_title', isset($defaults['meta']['title']) ? $defaults['meta']['title'] : '');
    $meta_desc      = $get_val('landing_meta_description', isset($defaults['meta']['description']) ? $defaults['meta']['description'] : '');
    $meta_keywords  = $get_val('landing_meta_keywords', isset($defaults['meta']['keywords']) && is_array($defaults['meta']['keywords']) ? implode(', ', $defaults['meta']['keywords']) : '');

    $hero_kicker    = $get_val('landing_hero_kicker', isset($defaults['hero']['kicker']) ? $defaults['hero']['kicker'] : 'HIỂU CON ĐỂ ĐỒNG HÀNH');
    $hero_prefix    = $get_val('landing_hero_title_prefix', isset($defaults['hero']['titlePrefix']) ? $defaults['hero']['titlePrefix'] : 'Mỗi đứa trẻ là một');
    $hero_highlight = $get_val('landing_hero_title_highlight', isset($defaults['hero']['titleHighlight']) ? $defaults['hero']['titleHighlight'] : 'thế giới riêng.');
    $hero_lead_hl   = $get_val('landing_hero_lead_highlight', isset($defaults['hero']['leadHighlight']) ? $defaults['hero']['leadHighlight'] : 'Hiểu con sớm hơn để đồng hành đúng hơn.');
    $hero_lead      = $get_val('landing_hero_lead', isset($defaults['hero']['lead']) ? $defaults['hero']['lead'] : '');
    $hero_chips     = $get_val('landing_hero_chips', isset($defaults['hero']['chips']) && is_array($defaults['hero']['chips']) ? implode("\n", $defaults['hero']['chips']) : '');

    $persp_prefix   = $get_val('landing_perspectives_title_prefix', isset($defaults['perspectives']['titlePrefix']) ? $defaults['perspectives']['titlePrefix'] : 'Không phán đoán hay dán nhãn —');
    $persp_highlight = $get_val('landing_perspectives_title_highlight', isset($defaults['perspectives']['titleHighlight']) ? $defaults['perspectives']['titleHighlight'] : 'đồng hành cùng sự phát triển của con');
    $persp_lead     = $get_val('landing_perspectives_lead', isset($defaults['perspectives']['lead']) ? $defaults['perspectives']['lead'] : '');

    $rel_prefix     = $get_val('landing_relationship_title_prefix', isset($defaults['relationship']['titlePrefix']) ? $defaults['relationship']['titlePrefix'] : 'Không chỉ hiểu con,');
    $rel_highlight  = $get_val('landing_relationship_title_highlight', isset($defaults['relationship']['titleHighlight']) ? $defaults['relationship']['titleHighlight'] : 'mà gắn kết cả gia đình');
    $rel_statement  = $get_val('landing_relationship_statement', isset($defaults['relationship']['statement']) ? $defaults['relationship']['statement'] : '');

    $pkg_prefix     = $get_val('landing_packages_title_prefix', isset($defaults['packages']['titlePrefix']) ? $defaults['packages']['titlePrefix'] : 'Giải pháp đồng hành');
    $pkg_highlight  = $get_val('landing_packages_title_highlight', isset($defaults['packages']['titleHighlight']) ? $defaults['packages']['titleHighlight'] : 'phù hợp cho con');
    $pkg_lead       = $get_val('landing_packages_lead', isset($defaults['packages']['lead']) ? $defaults['packages']['lead'] : 'Hiểu một đứa trẻ → Hiểu ba mẹ và con → Hiểu bức tranh của cả gia đình.');
    $pkg_btn        = $get_val('landing_packages_btn', isset($defaults['packages']['btn']) ? $defaults['packages']['btn'] : 'Chọn gói');

    $expert_name    = $get_val('landing_expert_name', isset($defaults['expert']['name']) ? $defaults['expert']['name'] : 'Ms. Linda Trần');
    $expert_badge   = $get_val('landing_expert_badge', isset($defaults['expert']['badge']) ? $defaults['expert']['badge'] : 'Chuyên gia Thiên Tâm');
    $expert_desc    = $get_val('landing_expert_desc', isset($defaults['expert']['desc']) ? $defaults['expert']['desc'] : 'Hơn 20 năm kinh nghiệm trong Tử Vi và huyền học.');
    $expert_desc1   = $get_val('landing_expert_desc1', isset($defaults['expert']['desc1']) ? $defaults['expert']['desc1'] : '');
    $expert_desc2   = $get_val('landing_expert_desc2', isset($defaults['expert']['desc2']) ? $defaults['expert']['desc2'] : '');

    $faq_title      = $get_val('landing_faq_title', isset($defaults['faq']['title']) ? $defaults['faq']['title'] : 'Những điều ba mẹ thường băn khoăn');
    $faq_desc       = $get_val('landing_faq_desc', isset($defaults['faq']['desc']) ? $defaults['faq']['desc'] : 'Giải đáp rõ ràng về cách tiếp cận, quy trình và giá trị của buổi luận giải.');

    $final_prefix   = $get_val('landing_final_title_prefix', isset($defaults['finalCta']['titlePrefix']) ? $defaults['finalCta']['titlePrefix'] : 'Không phải để quyết định');
    $final_highlight = $get_val('landing_final_title_highlight', isset($defaults['finalCta']['titleHighlight']) ? $defaults['finalCta']['titleHighlight'] : 'con phải trở thành ai.');
    $final_lead     = $get_val('landing_final_lead', isset($defaults['finalCta']['lead']) ? $defaults['finalCta']['lead'] : '');
    $final_statement = $get_val('landing_final_statement', isset($defaults['finalCta']['statement']) ? $defaults['finalCta']['statement'] : 'Hiểu con để đồng hành, không phải để kiểm soát.');

    $custom_json    = get_post_meta($post->ID, 'landing_custom_json', true) ?: '';
?>
    <style>
        .tt-tabs-nav {
            display: flex;
            flex-wrap: wrap;
            background: #f0f0f1;
            border-bottom: 1px solid #c3c4c7;
            margin: -6px -12px 16px -12px;
        }

        .tt-tab-btn {
            padding: 10px 16px;
            font-weight: 600;
            font-size: 13px;
            border: none;
            background: transparent;
            cursor: pointer;
            color: #50575e;
            border-bottom: 2px solid transparent;
        }

        .tt-tab-btn:hover {
            color: #1d2327;
            background: #e6e6e7;
        }

        .tt-tab-btn.active {
            color: #0f3d61;
            background: #fff;
            border-bottom: 2px solid #0f3d61;
        }

        .tt-tab-pane {
            display: none;
        }

        .tt-tab-pane.active {
            display: block;
        }

        .tt-field-row {
            margin-bottom: 14px;
        }

        .tt-field-row label {
            display: block;
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 13px;
            color: #1d2327;
        }

        .tt-field-row .description {
            font-size: 11px;
            color: #646970;
            margin-top: 3px;
        }

        .tt-field-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        @media (max-width: 782px) {
            .tt-field-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="tt-tabs-wrapper">
        <div class="tt-tabs-nav">
            <button type="button" class="tt-tab-btn active" data-tab="tab-seo">01. SEO & Meta</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-hero">02. Hero Banner</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-persp">03. Góc nhìn & Quan hệ</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-pkg">04. Gói dịch vụ</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-expert">05. Chuyên gia</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-faq">06. FAQ</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-final">07. Thông điệp kết</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-json">08. JSON Tùy biến</button>
        </div>

        <!-- TAB 1: SEO -->
        <div id="tab-seo" class="tt-tab-pane active">
            <div class="tt-field-row">
                <label>Meta Title (Tiêu đề SEO)</label>
                <input type="text" name="landing_meta_title" class="widefat" value="<?php echo esc_attr($meta_title); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Meta Description (Mô tả SEO)</label>
                <textarea name="landing_meta_description" class="widefat" rows="3"><?php echo esc_textarea($meta_desc); ?></textarea>
            </div>
            <div class="tt-field-row">
                <label>Keywords SEO (Cách nhau bằng dấu phẩy)</label>
                <input type="text" name="landing_meta_keywords" class="widefat" value="<?php echo esc_attr($meta_keywords); ?>" />
            </div>
        </div>

        <!-- TAB 2: HERO -->
        <div id="tab-hero" class="tt-tab-pane">
            <div class="tt-field-row">
                <label>Kicker (Dòng nhãn nhỏ trên cùng)</label>
                <input type="text" name="landing_hero_kicker" class="widefat" value="<?php echo esc_attr($hero_kicker); ?>" />
            </div>
            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Tiêu đề đầu (Prefix)</label>
                    <input type="text" name="landing_hero_title_prefix" class="widefat" value="<?php echo esc_attr($hero_prefix); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Tiêu đề nổi bật (Highlight)</label>
                    <input type="text" name="landing_hero_title_highlight" class="widefat" value="<?php echo esc_attr($hero_highlight); ?>" />
                </div>
            </div>
            <div class="tt-field-row">
                <label>Dòng dẫn dắt in đậm</label>
                <input type="text" name="landing_hero_lead_highlight" class="widefat" value="<?php echo esc_attr($hero_lead_hl); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Mô tả mở đầu</label>
                <textarea name="landing_hero_lead" class="widefat" rows="3"><?php echo esc_textarea($hero_lead); ?></textarea>
            </div>
            <div class="tt-field-row">
                <label>Các điểm cam kết dưới nút (Mỗi dòng 1 mục)</label>
                <textarea name="landing_hero_chips" class="widefat" rows="3"><?php echo esc_textarea($hero_chips); ?></textarea>
            </div>
        </div>

        <!-- TAB 3: GÓC NHÌN & QUAN HỆ -->
        <div id="tab-persp" class="tt-tab-pane">
            <h4 style="margin: 6px 0 10px 0; color:#0f3d61; border-bottom:1px solid #eee; padding-bottom:6px;">Phần Góc Nhìn (Perspectives)</h4>
            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Góc nhìn: Tiêu đề đầu</label>
                    <input type="text" name="landing_perspectives_title_prefix" class="widefat" value="<?php echo esc_attr($persp_prefix); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Góc nhìn: Tiêu đề nổi bật</label>
                    <input type="text" name="landing_perspectives_title_highlight" class="widefat" value="<?php echo esc_attr($persp_highlight); ?>" />
                </div>
            </div>
            <div class="tt-field-row">
                <label>Góc nhìn: Mô tả</label>
                <textarea name="landing_perspectives_lead" class="widefat" rows="2"><?php echo esc_textarea($persp_lead); ?></textarea>
            </div>

            <h4 style="margin: 18px 0 10px 0; color:#0f3d61; border-bottom:1px solid #eee; padding-bottom:6px;">Phần Quan Hệ (Relationship)</h4>
            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Quan hệ: Tiêu đề đầu</label>
                    <input type="text" name="landing_relationship_title_prefix" class="widefat" value="<?php echo esc_attr($rel_prefix); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Quan hệ: Tiêu đề nổi bật</label>
                    <input type="text" name="landing_relationship_title_highlight" class="widefat" value="<?php echo esc_attr($rel_highlight); ?>" />
                </div>
            </div>
            <div class="tt-field-row">
                <label>Quan hệ: Thông điệp cốt lõi</label>
                <textarea name="landing_relationship_statement" class="widefat" rows="2"><?php echo esc_textarea($rel_statement); ?></textarea>
            </div>
        </div>

        <!-- TAB 4: GÓI DỊCH VỤ -->
        <div id="tab-pkg" class="tt-tab-pane">
            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Tiêu đề đầu</label>
                    <input type="text" name="landing_packages_title_prefix" class="widefat" value="<?php echo esc_attr($pkg_prefix); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Tiêu đề nổi bật</label>
                    <input type="text" name="landing_packages_title_highlight" class="widefat" value="<?php echo esc_attr($pkg_highlight); ?>" />
                </div>
            </div>
            <div class="tt-field-row">
                <label>Mô tả gói</label>
                <textarea name="landing_packages_lead" class="widefat" rows="2"><?php echo esc_textarea($pkg_lead); ?></textarea>
            </div>
            <div class="tt-field-row">
                <label>Text nút bấm dùng chung</label>
                <input type="text" name="landing_packages_btn" class="widefat" value="<?php echo esc_attr($pkg_btn); ?>" />
            </div>
        </div>

        <!-- TAB 5: CHUYÊN GIA -->
        <div id="tab-expert" class="tt-tab-pane">
            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Tên chuyên gia</label>
                    <input type="text" name="landing_expert_name" class="widefat" value="<?php echo esc_attr($expert_name); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Danh hiệu / Badge</label>
                    <input type="text" name="landing_expert_badge" class="widefat" value="<?php echo esc_attr($expert_badge); ?>" />
                </div>
            </div>
            <div class="tt-field-row">
                <label>Dòng nổi bật kinh nghiệm (Dưới tên chuyên gia)</label>
                <input type="text" name="landing_expert_desc" class="widefat" value="<?php echo esc_attr($expert_desc); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Đoạn giới thiệu 1</label>
                <textarea name="landing_expert_desc1" class="widefat" rows="2"><?php echo esc_textarea($expert_desc1); ?></textarea>
            </div>
            <div class="tt-field-row">
                <label>Đoạn giới thiệu 2</label>
                <textarea name="landing_expert_desc2" class="widefat" rows="2"><?php echo esc_textarea($expert_desc2); ?></textarea>
            </div>
        </div>

        <!-- TAB 6: FAQ -->
        <div id="tab-faq" class="tt-tab-pane">
            <div class="tt-field-row">
                <label>Tiêu đề FAQ</label>
                <input type="text" name="landing_faq_title" class="widefat" value="<?php echo esc_attr($faq_title); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Mô tả FAQ</label>
                <textarea name="landing_faq_desc" class="widefat" rows="2"><?php echo esc_textarea($faq_desc); ?></textarea>
            </div>
        </div>

        <!-- TAB 7: THÔNG ĐIỆP KẾT -->
        <div id="tab-final" class="tt-tab-pane">
            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Tiêu đề đầu</label>
                    <input type="text" name="landing_final_title_prefix" class="widefat" value="<?php echo esc_attr($final_prefix); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Tiêu đề nổi bật</label>
                    <input type="text" name="landing_final_title_highlight" class="widefat" value="<?php echo esc_attr($final_highlight); ?>" />
                </div>
            </div>
            <div class="tt-field-row">
                <label>Mô tả thông điệp kết</label>
                <textarea name="landing_final_lead" class="widefat" rows="3"><?php echo esc_textarea($final_lead); ?></textarea>
            </div>
            <div class="tt-field-row">
                <label>Câu trích dẫn tâm nguyện cốt lõi</label>
                <input type="text" name="landing_final_statement" class="widefat" value="<?php echo esc_attr($final_statement); ?>" />
            </div>
        </div>

        <!-- TAB 8: JSON TÙY BIẾN -->
        <div id="tab-json" class="tt-tab-pane">
            <div class="tt-field-row">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <label style="margin:0; font-weight:600;">JSON Tùy biến toàn trang (Ghi đè nâng cao)</label>
                    <button type="button" id="tt-btn-load-sample-json" class="button button-secondary button-small" style="display:flex; align-items:center; gap:4px;">
                        <span>📋 Nạp toàn bộ JSON mặc định vào đây</span>
                    </button>
                </div>
                <textarea id="tt-custom-json-textarea" name="landing_custom_json" class="widefat" rows="18" style="font-family:monospace; font-size:12px;"><?php echo esc_textarea($custom_json); ?></textarea>
                <p class="description">Nếu để trống, hệ thống sẽ tự động tổng hợp từ các Tab ở trên kết hợp với dữ liệu mặc định hệ thống. Nếu bạn dán JSON hợp lệ vào đây, hệ thống sẽ ưu tiên dùng JSON này để trả về cho Next.js.</p>
            </div>
        </div>
    </div>

    <script>
        (function($) {
            $(document).ready(function() {
                $('.tt-tab-btn').on('click', function(e) {
                    e.preventDefault();
                    var targetTab = $(this).data('tab');
                    $('.tt-tab-btn').removeClass('active');
                    $('.tt-tab-pane').removeClass('active');
                    $(this).addClass('active');
                    $('#' + targetTab).addClass('active');
                });

                $('#tt-btn-load-sample-json').on('click', function(e) {
                    e.preventDefault();
                    if ($('#tt-custom-json-textarea').val().trim() !== '' && !confirm('Thao tác này sẽ ghi đè nội dung JSON hiện tại bằng toàn bộ dữ liệu mặc định từ vi.json. Bạn có chắc chắn muốn tiếp tục?')) {
                        return;
                    }
                    var sample = <?php echo wp_json_encode($defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?>;
                    $('#tt-custom-json-textarea').val(JSON.stringify(sample, null, 2));
                });
            });
        })(jQuery);
    </script>
<?php
}

/**
 * 2. Xử lý Lưu dữ liệu Post Meta khi bấm Cập nhật / Xuất bản Trang
 */
add_action('save_post_page', 'thientam_save_landing_metabox_data', 10, 2);
function thientam_save_landing_metabox_data($post_id, $post)
{
    if (! isset($_POST['thientam_landing_meta_nonce']) || ! wp_verify_nonce($_POST['thientam_landing_meta_nonce'], 'thientam_save_landing_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (! current_user_can('edit_page', $post_id)) {
        return;
    }

    // 1. Lưu loại Landing Page
    if (isset($_POST['thientam_landing_type'])) {
        $type = sanitize_text_field(wp_unslash($_POST['thientam_landing_type']));
        update_post_meta($post_id, 'thientam_landing_type', $type);
    }

    // 2. Nếu người dùng tick chọn "Nạp lại toàn bộ dữ liệu mặc định hệ thống"
    if (! empty($_POST['thientam_reset_landing_defaults'])) {
        $defaults = thientam_get_landing_default_data('hieu-con-de-dong-hanh');
        if ($defaults) {
            update_post_meta($post_id, 'landing_meta_title', isset($defaults['meta']['title']) ? $defaults['meta']['title'] : '');
            update_post_meta($post_id, 'landing_meta_description', isset($defaults['meta']['description']) ? $defaults['meta']['description'] : '');
            update_post_meta($post_id, 'landing_meta_keywords', isset($defaults['meta']['keywords']) && is_array($defaults['meta']['keywords']) ? implode(', ', $defaults['meta']['keywords']) : '');

            update_post_meta($post_id, 'landing_hero_kicker', isset($defaults['hero']['kicker']) ? $defaults['hero']['kicker'] : '');
            update_post_meta($post_id, 'landing_hero_title_prefix', isset($defaults['hero']['titlePrefix']) ? $defaults['hero']['titlePrefix'] : '');
            update_post_meta($post_id, 'landing_hero_title_highlight', isset($defaults['hero']['titleHighlight']) ? $defaults['hero']['titleHighlight'] : '');
            update_post_meta($post_id, 'landing_hero_lead_highlight', isset($defaults['hero']['leadHighlight']) ? $defaults['hero']['leadHighlight'] : '');
            update_post_meta($post_id, 'landing_hero_lead', isset($defaults['hero']['lead']) ? $defaults['hero']['lead'] : '');
            update_post_meta($post_id, 'landing_hero_chips', isset($defaults['hero']['chips']) && is_array($defaults['hero']['chips']) ? implode("\n", $defaults['hero']['chips']) : '');

            update_post_meta($post_id, 'landing_perspectives_title_prefix', isset($defaults['perspectives']['titlePrefix']) ? $defaults['perspectives']['titlePrefix'] : '');
            update_post_meta($post_id, 'landing_perspectives_title_highlight', isset($defaults['perspectives']['titleHighlight']) ? $defaults['perspectives']['titleHighlight'] : '');
            update_post_meta($post_id, 'landing_perspectives_lead', isset($defaults['perspectives']['lead']) ? $defaults['perspectives']['lead'] : '');

            update_post_meta($post_id, 'landing_relationship_title_prefix', isset($defaults['relationship']['titlePrefix']) ? $defaults['relationship']['titlePrefix'] : '');
            update_post_meta($post_id, 'landing_relationship_title_highlight', isset($defaults['relationship']['titleHighlight']) ? $defaults['relationship']['titleHighlight'] : '');
            update_post_meta($post_id, 'landing_relationship_statement', isset($defaults['relationship']['statement']) ? $defaults['relationship']['statement'] : '');

            update_post_meta($post_id, 'landing_packages_title_prefix', isset($defaults['packages']['titlePrefix']) ? $defaults['packages']['titlePrefix'] : '');
            update_post_meta($post_id, 'landing_packages_title_highlight', isset($defaults['packages']['titleHighlight']) ? $defaults['packages']['titleHighlight'] : '');
            update_post_meta($post_id, 'landing_packages_lead', isset($defaults['packages']['lead']) ? $defaults['packages']['lead'] : '');
            update_post_meta($post_id, 'landing_packages_btn', isset($defaults['packages']['btn']) ? $defaults['packages']['btn'] : '');

            update_post_meta($post_id, 'landing_expert_name', isset($defaults['expert']['name']) ? $defaults['expert']['name'] : '');
            update_post_meta($post_id, 'landing_expert_badge', isset($defaults['expert']['badge']) ? $defaults['expert']['badge'] : '');
            update_post_meta($post_id, 'landing_expert_desc', isset($defaults['expert']['desc']) ? $defaults['expert']['desc'] : '');
            update_post_meta($post_id, 'landing_expert_desc1', isset($defaults['expert']['desc1']) ? $defaults['expert']['desc1'] : '');
            update_post_meta($post_id, 'landing_expert_desc2', isset($defaults['expert']['desc2']) ? $defaults['expert']['desc2'] : '');

            update_post_meta($post_id, 'landing_faq_title', isset($defaults['faq']['title']) ? $defaults['faq']['title'] : '');
            update_post_meta($post_id, 'landing_faq_desc', isset($defaults['faq']['desc']) ? $defaults['faq']['desc'] : '');

            update_post_meta($post_id, 'landing_final_title_prefix', isset($defaults['finalCta']['titlePrefix']) ? $defaults['finalCta']['titlePrefix'] : '');
            update_post_meta($post_id, 'landing_final_title_highlight', isset($defaults['finalCta']['titleHighlight']) ? $defaults['finalCta']['titleHighlight'] : '');
            update_post_meta($post_id, 'landing_final_lead', isset($defaults['finalCta']['lead']) ? $defaults['finalCta']['lead'] : '');
            update_post_meta($post_id, 'landing_final_statement', isset($defaults['finalCta']['statement']) ? $defaults['finalCta']['statement'] : '');

            delete_post_meta($post_id, 'landing_custom_json');
            return;
        }
    }

    // 3. Lưu từng trường text / textarea thông thường
    $text_fields = array(
        'landing_meta_title',
        'landing_meta_description',
        'landing_meta_keywords',
        'landing_hero_kicker',
        'landing_hero_title_prefix',
        'landing_hero_title_highlight',
        'landing_hero_lead_highlight',
        'landing_hero_lead',
        'landing_hero_chips',
        'landing_perspectives_title_prefix',
        'landing_perspectives_title_highlight',
        'landing_perspectives_lead',
        'landing_relationship_title_prefix',
        'landing_relationship_title_highlight',
        'landing_relationship_statement',
        'landing_packages_title_prefix',
        'landing_packages_title_highlight',
        'landing_packages_lead',
        'landing_packages_btn',
        'landing_expert_name',
        'landing_expert_badge',
        'landing_expert_desc',
        'landing_expert_desc1',
        'landing_expert_desc2',
        'landing_faq_title',
        'landing_faq_desc',
        'landing_final_title_prefix',
        'landing_final_title_highlight',
        'landing_final_lead',
        'landing_final_statement',
    );

    foreach ($text_fields as $f) {
        if (isset($_POST[$f])) {
            update_post_meta($post_id, $f, sanitize_textarea_field(wp_unslash($_POST[$f])));
        }
    }

    // 4. Lưu Custom JSON Override
    if (isset($_POST['landing_custom_json'])) {
        update_post_meta($post_id, 'landing_custom_json', wp_unslash($_POST['landing_custom_json']));
    }
}
