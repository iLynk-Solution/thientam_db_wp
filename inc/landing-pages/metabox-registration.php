<?php
/**
 * Shared Registration Form Layout Metabox Component (Khối giới thiệu & Cam kết bảo mật - Cột trái)
 * Scope: $post, $defaults, $get_val (provided by parent metabox.php)
 * Optional parameters:
 *   $tab_reg_id       (string) Tab pane ID (default 'tab-registration')
 *   $tab_reg_active   (bool)   Is active pane (default false)
 *   $tab_reg_wrap     (bool)   Wrap inside <div class="tt-tab-pane"> (default true)
 *   $tab_reg_heading  (string) Section heading title (optional)
 *   $reg_data_key     (string) Key in $defaults array (default 'form')
 *
 * @package ThienTamData
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! isset($get_val) || ! is_callable($get_val)) {
    $get_val = function ($meta_key, $default_val = '') use ($post) {
        if (metadata_exists('post', $post->ID, $meta_key)) {
            return get_post_meta($post->ID, $meta_key, true);
        }
        return $default_val;
    };
}

$tab_reg_id      = isset($tab_reg_id) && $tab_reg_id !== '' ? $tab_reg_id : 'tab-registration';
$tab_reg_active  = ! empty($tab_reg_active);
$tab_reg_wrap    = ! isset($tab_reg_wrap) || $tab_reg_wrap !== false;
$tab_reg_heading = isset($tab_reg_heading) ? $tab_reg_heading : '';
$reg_data_key    = ! empty($reg_data_key) ? $reg_data_key : 'form';

// Dữ liệu cột trái Form Đăng ký
$reg_prefix    = $get_val('landing_form_title_prefix', $defaults[$reg_data_key]['titlePrefix'] ?? '');
$reg_highlight = $get_val('landing_form_title_highlight', $defaults[$reg_data_key]['titleHighlight'] ?? '');
$reg_suffix    = $get_val('landing_form_title_suffix', $defaults[$reg_data_key]['titleSuffix'] ?? '');
$reg_lead      = $get_val('landing_form_lead', $defaults[$reg_data_key]['lead'] ?? '');

$reg_hl_raw = $get_val('landing_form_highlights', '');
if ($reg_hl_raw !== '') {
    $reg_highlights = $reg_hl_raw;
} else {
    $reg_highlights = isset($defaults[$reg_data_key]['highlights']) && is_array($defaults[$reg_data_key]['highlights'])
        ? implode("\n", $defaults[$reg_data_key]['highlights'])
        : '';
}

$reg_sec_title = $get_val('landing_form_security_title', $defaults[$reg_data_key]['securityCommitmentTitle'] ?? '');
$reg_sec_desc  = $get_val('landing_form_security_desc', $defaults[$reg_data_key]['securityCommitmentDesc'] ?? '');
?>

<?php if ($tab_reg_wrap) : ?>
<div id="<?php echo esc_attr($tab_reg_id); ?>" class="tt-tab-pane <?php echo $tab_reg_active ? 'active' : ''; ?>">
<?php endif; ?>

    <?php if (! empty($tab_reg_heading)) : ?>
        <h3 style="margin-top:0; color:#0f3d61;"><?php echo esc_html($tab_reg_heading); ?></h3>
    <?php endif; ?>

    <div class="tt-field-grid" style="grid-template-columns: 1fr 1fr 1fr;">
        <div class="tt-field-row">
            <label>Tiêu đề đầu (Prefix)</label>
            <input type="text" name="landing_form_title_prefix" class="widefat" placeholder="VD: Để lại thông tin..." value="<?php echo esc_attr($reg_prefix); ?>" />
        </div>
        <div class="tt-field-row">
            <label>Tiêu đề nổi bật (Highlight)</label>
            <input type="text" name="landing_form_title_highlight" class="widefat" placeholder="VD: để nhận tư vấn..." value="<?php echo esc_attr($reg_highlight); ?>" />
        </div>
        <div class="tt-field-row">
            <label>Tiêu đề đuôi (Suffix - Tùy chọn)</label>
            <input type="text" name="landing_form_title_suffix" class="widefat" placeholder="VD: ngay hôm nay" value="<?php echo esc_attr($reg_suffix); ?>" />
        </div>
    </div>

    <div class="tt-field-row">
        <label>Lời dẫn / Mô tả phần giới thiệu (Lead)</label>
        <textarea name="landing_form_lead" class="widefat" rows="2" placeholder="VD: Để lại thông tin chính xác để chuyên gia Thiên Tâm lập lá số Tử Vi và luận giải chi tiết..."><?php echo esc_textarea($reg_lead); ?></textarea>
    </div>

    <div class="tt-field-row">
        <label>Danh sách Điểm nổi bật (Highlights) — Mỗi dòng 1 điểm</label>
        <textarea name="landing_form_highlights" class="widefat" rows="4" placeholder="VD:&#10;Bảo mật tuyệt đối thông tin lá số&#10;Tham vấn trực tiếp 1-1 cùng chuyên gia&#10;Đồng hành giải đáp rõ ràng trước mọi quyết định"><?php echo esc_textarea($reg_highlights); ?></textarea>
        <p class="description" style="font-size:12px; color:#64748b; margin-top:4px;">Mỗi dòng sẽ hiển thị thành một gạch đầu dòng có biểu tượng dấu tích tròn màu ngọc.</p>
    </div>

    <div class="tt-card" style="margin-top:20px;">
        <div class="tt-card-header">
            <strong>Khung Cam Kết Bảo Mật (Trust Badge Card)</strong>
        </div>
        <div style="padding:15px;">
            <div class="tt-field-row">
                <label>Tiêu đề cam kết</label>
                <input type="text" name="landing_form_security_title" class="widefat" placeholder="VD: Cam kết bảo mật & Chính trực" value="<?php echo esc_attr($reg_sec_title); ?>" />
            </div>
            <div class="tt-field-row" style="margin-bottom:0;">
                <label>Nội dung cam kết</label>
                <textarea name="landing_form_security_desc" class="widefat" rows="2" placeholder="VD: Mọi thông tin lá số và dữ liệu cá nhân được lưu trữ bảo mật tuyệt đối, trực tiếp trao đổi cùng chuyên gia theo lịch riêng."><?php echo esc_textarea($reg_sec_desc); ?></textarea>
            </div>
        </div>
    </div>

<?php if ($tab_reg_wrap) : ?>
</div>
<?php endif; ?>
