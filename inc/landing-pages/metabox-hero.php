<?php
/**
 * Shared Hero Banner Metabox Component
 * Scope: $post, $defaults, $get_val (provided by parent metabox.php)
 * Optional parameters: $tab_hero_id, $tab_hero_active
 *
 * @package ThienTamData
 */

if (! defined('ABSPATH')) {
    exit;
}

// 1. Chuẩn bị dữ liệu Hero Banner
$hero_kicker      = $get_val('landing_hero_kicker', $defaults['hero']['kicker'] ?? '');
$hero_prefix      = $get_val('landing_hero_title_prefix', $defaults['hero']['titlePrefix'] ?? '');
$hero_highlight   = $get_val('landing_hero_title_highlight', $defaults['hero']['titleHighlight'] ?? '');
$hero_lead_hl     = $get_val('landing_hero_lead_highlight', $defaults['hero']['leadHighlight'] ?? '');
$hero_lead        = $get_val('landing_hero_lead', $defaults['hero']['lead'] ?? '');
$raw_cta_pri      = $get_val('landing_hero_cta_primary', $defaults['hero']['ctaPrimary'] ?? 'Đăng ký ngay');
$hero_cta_pri     = is_array($raw_cta_pri) ? ($raw_cta_pri['label'] ?? ($raw_cta_pri['text'] ?? '')) : (string) $raw_cta_pri;

$raw_cta_sec      = $get_val('landing_hero_cta_secondary', $defaults['hero']['ctaSecondary'] ?? 'Xem chi tiết các gói');
$hero_cta_sec     = is_array($raw_cta_sec) ? ($raw_cta_sec['label'] ?? ($raw_cta_sec['text'] ?? '')) : (string) $raw_cta_sec;

$hero_cta_pri_href = (string) ($get_val('landing_hero_cta_primary_href', $defaults['hero']['ctaPrimaryHref'] ?? '#cua-vao'));
$hero_cta_sec_href = (string) ($get_val('landing_hero_cta_secondary_href', $defaults['hero']['ctaSecondaryHref'] ?? '#goi-dich-vu'));
$hero_image       = $get_val('landing_hero_image', $defaults['hero']['image'] ?? '');
$hero_image_badge = $get_val('landing_hero_image_badge', $defaults['hero']['imageBadge'] ?? '');
$hero_image_alt   = $get_val('landing_hero_image_alt', $defaults['hero']['imageAlt'] ?? '');
$hero_quote_bold  = $get_val('landing_hero_quote_bold', $defaults['hero']['imageQuoteBold'] ?? ($defaults['hero']['quote']['bold'] ?? ''));
$hero_quote_sub   = $get_val('landing_hero_quote_sub', $defaults['hero']['imageQuoteSub'] ?? ($defaults['hero']['quote']['sub'] ?? ''));
$hero_chips_raw   = $get_val('landing_hero_chips', '');
if ($hero_chips_raw !== '') {
    $hero_chips = $hero_chips_raw;
} else {
    $hero_chips = isset($defaults['hero']['chips']) && is_array($defaults['hero']['chips']) ? implode("\n", $defaults['hero']['chips']) : '';
}

$current_tab_id = ! empty($tab_hero_id) ? $tab_hero_id : 'tab-hero';
$is_active_pane = ! empty($tab_hero_active);
?>

        <!-- TAB: HERO BANNER (DÙNG CHUNG) -->
        <div id="<?php echo esc_attr($current_tab_id); ?>" class="tt-tab-pane <?php echo $is_active_pane ? 'active' : ''; ?>">
            <div class="tt-field-row">
                <label>Kicker (Huy hiệu nhỏ trên cùng)</label>
                <input type="text" name="landing_hero_kicker" class="widefat" value="<?php echo esc_attr($hero_kicker); ?>" />
            </div>
            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Tiêu đề đầu</label>
                    <input type="text" name="landing_hero_title_prefix" class="widefat" value="<?php echo esc_attr($hero_prefix); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Tiêu đề nổi bật</label>
                    <input type="text" name="landing_hero_title_highlight" class="widefat" value="<?php echo esc_attr($hero_highlight); ?>" />
                </div>
            </div>
            <div class="tt-field-row">
                <label>Lời dẫn in đậm</label>
                <input type="text" name="landing_hero_lead_highlight" class="widefat" value="<?php echo esc_attr($hero_lead_hl); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Đoạn dẫn chi tiết</label>
                <textarea name="landing_hero_lead" class="widefat" rows="2"><?php echo esc_textarea($hero_lead); ?></textarea>
            </div>
            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Nút CTA chính (Tiêu đề)</label>
                    <input type="text" name="landing_hero_cta_primary" class="widefat" value="<?php echo esc_attr($hero_cta_pri); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Nút CTA chính (Anchor / Liên kết)</label>
                    <input type="text" name="landing_hero_cta_primary_href" class="widefat" value="<?php echo esc_attr($hero_cta_pri_href); ?>" placeholder="#cua-vao hoặc #cac-goi-luan-giai" />
                </div>
                <div class="tt-field-row">
                    <label>Nút CTA phụ (Tiêu đề)</label>
                    <input type="text" name="landing_hero_cta_secondary" class="widefat" value="<?php echo esc_attr($hero_cta_sec); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Nút CTA phụ (Anchor / Liên kết)</label>
                    <input type="text" name="landing_hero_cta_secondary_href" class="widefat" value="<?php echo esc_attr($hero_cta_sec_href); ?>" placeholder="#goi-dich-vu hoặc #huong-dan-chon-goi" />
                </div>
            </div>
            <div class="tt-card">
                <div class="tt-card-header">Ảnh Minh Họa Hero Banner</div>
                <div class="tt-image-picker-row">
                    <div id="tt-hero-image-preview" class="tt-image-preview">
                        <?php if (! empty($hero_image)) : ?>
                            <img src="<?php echo esc_url($hero_image); ?>" />
                        <?php else : ?>
                            <span style="font-size:11px; color:#888;">Chưa chọn ảnh</span>
                        <?php endif; ?>
                    </div>
                    <div style="flex:1;">
                        <input type="text" id="tt-hero-image-input" name="landing_hero_image" class="widefat" value="<?php echo esc_attr($hero_image); ?>" placeholder="https://... hoặc bấm nút Chọn ảnh bên dưới" style="margin-bottom:8px;" />
                        <div style="display:flex; gap:8px; align-items:center;">
                            <button type="button" id="tt-hero-image-btn" class="button button-secondary">📷 Chọn ảnh từ Thư viện</button>
                            <button type="button" id="tt-hero-image-remove" class="button button-link-delete" style="<?php echo empty($hero_image) ? 'display:none;' : ''; ?>">Xóa ảnh</button>
                        </div>
                        <p class="description" style="margin-top:6px;">Chọn ảnh từ Thư viện WordPress Media hoặc dán URL trực tiếp. Nếu để trống, hệ thống sẽ sử dụng ảnh mặc định thương hiệu.</p>
                    </div>
                </div>
                <div class="tt-field-row">
                    <label>Huy hiệu nổi trên ảnh</label>
                    <input type="text" name="landing_hero_image_badge" class="widefat" value="<?php echo esc_attr($hero_image_badge); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Văn bản thay thế ảnh (Alt text)</label>
                    <input type="text" name="landing_hero_image_alt" class="widefat" value="<?php echo esc_attr($hero_image_alt); ?>" />
                </div>
                <div class="tt-field-grid">
                    <div class="tt-field-row">
                        <label>Trích dẫn in đậm dưới ảnh</label>
                        <input type="text" name="landing_hero_quote_bold" class="widefat" value="<?php echo esc_attr($hero_quote_bold); ?>" />
                    </div>
                    <div class="tt-field-row">
                        <label>Phụ đề trích dẫn dưới ảnh</label>
                        <input type="text" name="landing_hero_quote_sub" class="widefat" value="<?php echo esc_attr($hero_quote_sub); ?>" />
                    </div>
                </div>
            </div>
            <div class="tt-field-row">
                <label>Chips nổi bật (mỗi dòng 1 chip)</label>
                <textarea name="landing_hero_chips" class="widefat" rows="4"><?php echo esc_textarea($hero_chips); ?></textarea>
            </div>
        </div>
