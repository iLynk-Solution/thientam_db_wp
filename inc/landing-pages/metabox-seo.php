<?php
/**
 * Shared SEO & Header Metabox Component
 * Scope: $post, $defaults, $get_val (provided by parent metabox.php)
 * Optional parameters: $tab_seo_id, $tab_seo_active
 *
 * @package ThienTamData
 */

if (! defined('ABSPATH')) {
    exit;
}

// Chuẩn bị dữ liệu SEO & Nav
$meta_title        = $get_val('landing_meta_title', $defaults['meta']['title'] ?? '');
$meta_description  = $get_val('landing_meta_description', $defaults['meta']['description'] ?? '');
$meta_keywords     = $get_val('landing_meta_keywords', isset($defaults['meta']['keywords']) && is_array($defaults['meta']['keywords']) ? implode(', ', $defaults['meta']['keywords']) : '');
$nav_cta_label     = $get_val('landing_nav_cta_label', $defaults['navCta']['label'] ?? 'Đăng ký tư vấn');
$nav_cta_href      = $get_val('landing_nav_cta_href', $defaults['navCta']['href'] ?? '#dang-ky');

$current_tab_seo_id = ! empty($tab_seo_id) ? $tab_seo_id : 'tab-seo';
$is_active_seo_pane = ! isset($tab_seo_active) || ! empty($tab_seo_active);
?>

        <!-- TAB: SEO & HEADER (DÙNG CHUNG) -->
        <div id="<?php echo esc_attr($current_tab_seo_id); ?>" class="tt-tab-pane <?php echo $is_active_seo_pane ? 'active' : ''; ?>">
            <div class="tt-field-row">
                <label>Meta Title (Tiêu đề SEO)</label>
                <input type="text" name="landing_meta_title" class="widefat" value="<?php echo esc_attr($meta_title); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Meta Description (Mô tả SEO)</label>
                <textarea name="landing_meta_description" class="widefat" rows="2"><?php echo esc_textarea($meta_description); ?></textarea>
            </div>
            <div class="tt-field-row">
                <label>Keywords (Từ khóa SEO, phân cách bởi dấu phẩy)</label>
                <input type="text" name="landing_meta_keywords" class="widefat" value="<?php echo esc_attr($meta_keywords); ?>" />
            </div>
            <div class="tt-card" style="margin-top:16px;">
                <div class="tt-card-header">Nút CTA trên Thanh điều hướng (Header)</div>
                <div class="tt-field-grid">
                    <div class="tt-field-row">
                        <label>Tiêu đề nút Header</label>
                        <input type="text" name="landing_nav_cta_label" class="widefat" value="<?php echo esc_attr($nav_cta_label); ?>" />
                    </div>
                    <div class="tt-field-row">
                        <label>Liên kết nút Header</label>
                        <input type="text" name="landing_nav_cta_href" class="widefat" value="<?php echo esc_attr($nav_cta_href); ?>" />
                    </div>
                </div>
            </div>
        </div>
