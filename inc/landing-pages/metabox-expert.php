<?php

/**
 * Shared Expert Metabox Component (Chuyên gia đồng hành)
 * Scope: $post, $defaults, $get_val, $get_json_val (provided by parent metabox.php)
 * Optional parameters:
 *   $tab_expert_id       (string) Tab pane ID (default 'tab-expert')
 *   $tab_expert_active   (bool)   Is active pane (default false)
 *   $tab_expert_wrap     (bool)   Wrap inside <div class="tt-tab-pane"> (default true)
 *   $tab_expert_heading  (string) Section heading title (optional)
 *
 * @package ThienTamData
 */

if (! defined('ABSPATH')) {
    exit;
}

// Fallback helpers nếu chưa được định nghĩa bởi parent metabox
if (! isset($get_val) || ! is_callable($get_val)) {
    $get_val = function ($meta_key, $default_val = '') use ($post) {
        if (metadata_exists('post', $post->ID, $meta_key)) {
            return get_post_meta($post->ID, $meta_key, true);
        }
        return $default_val;
    };
}

if (! isset($get_json_val) || ! is_callable($get_json_val)) {
    $get_json_val = function ($meta_key, $default_arr = array()) use ($post) {
        $meta_val = get_post_meta($post->ID, $meta_key, true);
        if (! empty($meta_val)) {
            $decoded = is_array($meta_val) ? $meta_val : json_decode($meta_val, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return $default_arr;
    };
}

$tab_expert_id      = isset($tab_expert_id) && $tab_expert_id !== '' ? $tab_expert_id : 'tab-expert';
$tab_expert_active  = ! empty($tab_expert_active);
$tab_expert_wrap    = ! isset($tab_expert_wrap) || $tab_expert_wrap !== false;
$tab_expert_heading = isset($tab_expert_heading) ? $tab_expert_heading : '';

// 1. Dữ liệu chuyên gia
$expert_title_prefix    = $get_val('landing_expert_title_prefix', $defaults['expert']['titlePrefix'] ?? '');
$expert_title_highlight = $get_val('landing_expert_title_highlight', $defaults['expert']['titleHighlight'] ?? '');
$expert_avatar          = $get_val('landing_expert_avatar', $defaults['expert']['avatar'] ?? '');
$expert_name            = $get_val('landing_expert_name', $defaults['expert']['name'] ?? '');
$expert_badge           = $get_val('landing_expert_badge', $defaults['expert']['badge'] ?? '');
$expert_desc            = $get_val('landing_expert_desc', $defaults['expert']['desc'] ?? '');
$expert_desc1           = $get_val('landing_expert_desc1', $defaults['expert']['desc1'] ?? '');
$expert_desc2           = $get_val('landing_expert_desc2', $defaults['expert']['desc2'] ?? '');
$expert_notice          = $get_val('landing_expert_notice', $defaults['expert']['notice'] ?? '');
$expert_btn             = $get_val('landing_expert_btn', $defaults['expert']['btn'] ?? '');
$expert_btn_href        = $get_val('landing_expert_btn_href', $defaults['expert']['btnHref'] ?? ($defaults['expert']['anchor'] ?? '#dang-ky-tu-van'));

$expert_pills_meta = get_post_meta($post->ID, 'landing_expert_pills', true);
if (! empty($expert_pills_meta)) {
    $decoded = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($expert_pills_meta) : json_decode($expert_pills_meta, true);
    $expert_pills = is_array($decoded) ? implode("\n", $decoded) : $expert_pills_meta;
} else {
    $expert_pills = isset($defaults['expert']['pills']) && is_array($defaults['expert']['pills']) ? implode("\n", $defaults['expert']['pills']) : '';
}

// 2. Direct Value
$dv_raw = get_post_meta($post->ID, 'landing_expert_direct_value', true);
$dv_data = array();
if (! empty($dv_raw)) {
    $decoded = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($dv_raw) : json_decode($dv_raw, true);
    if (is_array($decoded)) {
        $dv_data = $decoded;
    }
}
if (empty($dv_data) && isset($defaults['expert']['directValue'])) {
    $dv_data = $defaults['expert']['directValue'];
}

$dv_card1_eyebrow = $get_val('landing_expert_dv_card1_eyebrow', $dv_data['card1']['eyebrow'] ?? '');
$dv_card1_title   = $get_val('landing_expert_dv_card1_title', $dv_data['card1']['title'] ?? '');
$dv_card1_desc    = $get_val('landing_expert_dv_card1_desc', $dv_data['card1']['desc'] ?? '');

$dv_card2_title   = $get_val('landing_expert_dv_card2_title', $dv_data['card2']['title'] ?? '');
$dv_card2_items_meta = get_post_meta($post->ID, 'landing_expert_dv_card2_items', true);
if (! empty($dv_card2_items_meta)) {
    $dec_items = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($dv_card2_items_meta) : json_decode($dv_card2_items_meta, true);
    $dv_card2_items_str = is_array($dec_items) ? implode("\n", $dec_items) : $dv_card2_items_meta;
} elseif (! empty($dv_data['card2']['items']) && is_array($dv_data['card2']['items'])) {
    $dv_card2_items_str = implode("\n", $dv_data['card2']['items']);
} else {
    $dv_card2_items_str = '';
}

// Checkbox bật/tắt Giá trị tham vấn trực tiếp (Direct Value)
$show_dv_meta = get_post_meta($post->ID, 'landing_expert_show_direct_value', true);
if ($show_dv_meta !== '') {
    $show_direct_value = ($show_dv_meta === '1' || $show_dv_meta === 1 || $show_dv_meta === true || $show_dv_meta === 'true');
} else {
    $show_direct_value = ! empty($defaults['expert']['showDirectValue']);
}
?>

<?php if ($tab_expert_wrap) : ?>
    <div id="<?php echo esc_attr($tab_expert_id); ?>" class="tt-tab-pane<?php echo $tab_expert_active ? ' active' : ''; ?>">
    <?php endif; ?>

    <?php if (! empty($tab_expert_heading)) : ?>
        <h3 style="margin-top:0; color:#0f3d61;"><?php echo esc_html($tab_expert_heading); ?></h3>
    <?php endif; ?>

    <div class="tt-card">
        <div class="tt-card-header">Ảnh Chân Dung Chuyên Gia</div>
        <div class="tt-image-picker-row">
            <div id="tt-expert-avatar-preview" class="tt-image-preview">
                <?php if (! empty($expert_avatar)) : ?>
                    <img src="<?php echo esc_url($expert_avatar); ?>" style="width:100%; height:100%; object-fit:cover;" />
                <?php else : ?>
                    <span style="font-size:11px; color:#888;">Chưa chọn ảnh</span>
                <?php endif; ?>
            </div>
            <div style="flex:1;">
                <input type="text" id="tt-expert-avatar-input" name="landing_expert_avatar" class="widefat" value="<?php echo esc_attr($expert_avatar); ?>" placeholder="https://... hoặc bấm nút Chọn ảnh bên dưới" style="margin-bottom:8px;" />
                <div style="display:flex; gap:8px; align-items:center;">
                    <button type="button" id="tt-expert-avatar-btn" class="button button-secondary">📷 Chọn ảnh từ Thư viện</button>
                    <button type="button" id="tt-expert-avatar-remove" class="button button-link-delete" style="<?php echo empty($expert_avatar) ? 'display:none;' : ''; ?>">Xóa ảnh</button>
                </div>
                <p class="description" style="margin-top:6px;">Chọn ảnh từ Thư viện WordPress Media hoặc dán URL trực tiếp. Nếu để trống, hệ thống sẽ sử dụng ảnh mặc định thương hiệu.</p>
            </div>
        </div>
    </div>

    <div class="tt-field-grid">
        <div class="tt-field-row">
            <label>Tiêu đề đầu (Tiền tố)</label>
            <input type="text" name="landing_expert_title_prefix" class="widefat" value="<?php echo esc_attr($expert_title_prefix); ?>" />
        </div>
        <div class="tt-field-row">
            <label>Tiêu đề nổi bật (Tên nổi bật)</label>
            <input type="text" name="landing_expert_title_highlight" class="widefat" value="<?php echo esc_attr($expert_title_highlight); ?>" />
        </div>
    </div>

    <div class="tt-field-grid">
        <div class="tt-field-row">
            <label>Tên chuyên gia</label>
            <input type="text" name="landing_expert_name" class="widefat" value="<?php echo esc_attr($expert_name); ?>" />
        </div>
        <div class="tt-field-row">
            <label>Danh hiệu / Huy hiệu</label>
            <input type="text" name="landing_expert_badge" class="widefat" value="<?php echo esc_attr($expert_badge); ?>" />
        </div>
    </div>

    <div class="tt-field-row">
        <label>Lời dẫn mở đầu (hỗ trợ xuống dòng và **in đậm**)</label>
        <textarea name="landing_expert_desc" class="widefat" rows="2"><?php echo esc_textarea($expert_desc); ?></textarea>
    </div>
    <div class="tt-field-row">
        <label>Đoạn giải thích 1 (hỗ trợ xuống dòng và **in đậm**)</label>
        <textarea name="landing_expert_desc1" class="widefat" rows="4"><?php echo esc_textarea($expert_desc1); ?></textarea>
        <p class="description" style="margin:2px 0 6px;">Hỗ trợ xuống dòng và in đậm bằng cú pháp <code>**nội dung in đậm**</code>.</p>
    </div>
    <div class="tt-field-row">
        <label>Đoạn giải thích 2 (hỗ trợ xuống dòng và **in đậm**)</label>
        <textarea name="landing_expert_desc2" class="widefat" rows="4"><?php echo esc_textarea($expert_desc2); ?></textarea>
        <p class="description" style="margin:2px 0 6px;">Hỗ trợ xuống dòng và in đậm bằng cú pháp <code>**nội dung in đậm**</code>.</p>
    </div>
    <div class="tt-field-row">
        <label>Ghi chú nhấn mạnh của chuyên gia (hỗ trợ **in đậm**)</label>
        <input type="text" name="landing_expert_notice" class="widefat" value="<?php echo esc_attr($expert_notice); ?>" />
    </div>
    <div class="tt-field-grid">
        <div class="tt-field-row">
            <label>Text nút liên hệ chuyên gia</label>
            <input type="text" name="landing_expert_btn" class="widefat" value="<?php echo esc_attr($expert_btn); ?>" />
        </div>
        <div class="tt-field-row">
            <label>Anchor liên kết nút bấm (Target ID / URL)</label>
            <input type="text" name="landing_expert_btn_href" class="widefat" value="<?php echo esc_attr($expert_btn_href); ?>" placeholder="#dang-ky-tu-van hoặc #dang-ky" />
        </div>
    </div>
    <div class="tt-field-row">
        <label>Tags nổi bật (Pills - mỗi dòng 1 tag)</label>
        <textarea name="landing_expert_pills" class="widefat" rows="3"><?php echo esc_textarea($expert_pills); ?></textarea>
    </div>

    <h3 style="color:#0f3d61; margin-top:24px; border-top:1px solid #e2e8f0; padding-top:16px;">Giá Trị Tham Vấn Trực Tiếp (Direct Value)</h3>

    <div class="tt-field-row" style="background:#f8fafc; padding:12px 16px; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:16px;">
        <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; font-size:13px; color:#1e293b; margin-bottom:0;">
            <input type="checkbox" name="landing_expert_show_direct_value" id="tt-expert-show-dv" value="1" <?php checked($show_direct_value); ?> />
            <span>Hiển thị phần Giá Trị Tham Vấn Trực Tiếp (Có / Không)</span>
        </label>
        <p class="description" style="margin:4px 0 0 24px;">Tích chọn (Có) để bật hiển thị khối 2 thẻ giá trị tham vấn trực tiếp trên landing page, bỏ tích (Không) để tắt.</p>
    </div>

    <div id="tt-expert-dv-fields" style="<?php echo $show_direct_value ? '' : 'display:none;'; ?>">
        <div class="tt-card">
            <div class="tt-card-header">Thẻ 01: Vì sao tư vấn trực tiếp quan trọng?</div>
            <div class="tt-field-row">
                <label>Eyebrow (Huy hiệu nhỏ)</label>
                <input type="text" name="landing_expert_dv_card1_eyebrow" class="widefat" value="<?php echo esc_attr($dv_card1_eyebrow); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Tiêu đề</label>
                <input type="text" name="landing_expert_dv_card1_title" class="widefat" value="<?php echo esc_attr($dv_card1_title); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Mô tả chi tiết (hỗ trợ xuống dòng và **in đậm**)</label>
                <textarea name="landing_expert_dv_card1_desc" class="widefat" rows="3"><?php echo esc_textarea($dv_card1_desc); ?></textarea>
                <p class="description" style="margin:2px 0 6px;">Hỗ trợ xuống dòng và in đậm bằng cú pháp <code>**nội dung in đậm**</code>.</p>
            </div>
        </div>

        <div class="tt-card">
            <div class="tt-card-header">Thẻ 02: Lợi ích chuyển hóa từ “Biết” đến “Hiểu”</div>
            <div class="tt-field-row">
                <label>Tiêu đề</label>
                <input type="text" name="landing_expert_dv_card2_title" class="widefat" value="<?php echo esc_attr($dv_card2_title); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Các điểm then chốt (Mỗi dòng 1 ý có icon tích xanh, hỗ trợ **in đậm**)</label>
                <textarea name="landing_expert_dv_card2_items" class="widefat" rows="4"><?php echo esc_textarea($dv_card2_items_str); ?></textarea>
                <p class="description" style="margin:2px 0 6px;">Mỗi dòng 1 ý gạch đầu dòng. Hỗ trợ cú pháp <code>**in đậm**</code>.</p>
            </div>
        </div>
    </div>

    <script>
    (function($) {
        $(function() {
            $('#tt-expert-show-dv').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#tt-expert-dv-fields').slideDown(200);
                } else {
                    $('#tt-expert-dv-fields').slideUp(200);
                }
            });
        });
    })(jQuery);
    </script>

    <?php if ($tab_expert_wrap) : ?>
    </div>
<?php endif; ?>