<?php
/**
 * Shared Approach Metabox Component (Cách tiếp cận / Triết lý)
 * Scope: $post, $defaults, $get_val, $get_json_val (provided by parent metabox.php)
 * Optional parameters:
 *   $tab_approach_id       (string) Tab pane ID (default 'tab-approach')
 *   $tab_approach_active   (bool)   Is active pane (default false)
 *   $tab_approach_wrap     (bool)   Wrap inside <div class="tt-tab-pane"> (default true)
 *   $tab_approach_heading  (string) Section heading title (optional)
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

$tab_approach_id      = isset($tab_approach_id) && $tab_approach_id !== '' ? $tab_approach_id : 'tab-approach';
$tab_approach_active  = ! empty($tab_approach_active);
$tab_approach_wrap    = ! isset($tab_approach_wrap) || $tab_approach_wrap !== false;
$tab_approach_heading = isset($tab_approach_heading) ? $tab_approach_heading : '';

// 1. Dữ liệu Approach
$app_title_prefix    = $get_val('landing_approach_title_prefix', $defaults['approach']['titlePrefix'] ?? '');
$app_title_highlight = $get_val('landing_approach_title_highlight', $defaults['approach']['titleHighlight'] ?? '');
$app_card_title      = $get_val('landing_approach_card_title', $defaults['approach']['cardTitle'] ?? ($defaults['approach']['visualQuote'] ?? ''));
$app_card_badge      = $get_val('landing_approach_card_badge', $defaults['approach']['cardBadge'] ?? ($defaults['approach']['visualLabel'] ?? ''));
$app_desc1           = $get_val('landing_approach_desc1', $defaults['approach']['desc1'] ?? ($defaults['approach']['desc'] ?? ''));
$app_desc2           = $get_val('landing_approach_desc2', $defaults['approach']['desc2'] ?? '');
$app_image           = $get_val('landing_approach_image', $defaults['approach']['image'] ?? '');
$app_image_alt       = $get_val('landing_approach_image_alt', $defaults['approach']['imageAlt'] ?? '');
$app_btn             = $get_val('landing_approach_btn', $defaults['approach']['btn'] ?? '');

$app_pills_meta = get_post_meta($post->ID, 'landing_approach_pills', true);
if (! empty($app_pills_meta)) {
    $decoded = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($app_pills_meta) : json_decode($app_pills_meta, true);
    $app_pills_str = is_array($decoded) ? implode("\n", $decoded) : $app_pills_meta;
} else {
    if (isset($defaults['approach']['pills']) && is_array($defaults['approach']['pills'])) {
        $app_pills_str = implode("\n", $defaults['approach']['pills']);
    } elseif (isset($defaults['approach']['points']) && is_array($defaults['approach']['points'])) {
        $lines = array();
        foreach ($defaults['approach']['points'] as $pt) {
            if (is_array($pt)) {
                $b = trim($pt['bold'] ?? '');
                $t = trim($pt['text'] ?? '');
                $lines[] = $b !== '' ? "**{$b}** {$t}" : $t;
            } else {
                $lines[] = trim($pt);
            }
        }
        $app_pills_str = implode("\n", array_filter($lines));
    } else {
        $app_pills_str = '';
    }
}
?>

<?php if ($tab_approach_wrap) : ?>
<div id="<?php echo esc_attr($tab_approach_id); ?>" class="tt-tab-pane <?php echo $tab_approach_active ? 'active' : ''; ?>">
<?php endif; ?>

    <?php if (! empty($tab_approach_heading)) : ?>
        <h3 style="margin-top:0; color:#0f3d61;"><?php echo esc_html($tab_approach_heading); ?></h3>
    <?php endif; ?>

    <!-- 1. Tiêu đề Section -->
    <div class="tt-field-grid">
        <div class="tt-field-row">
            <label>Tiêu đề đầu</label>
            <input type="text" name="landing_approach_title_prefix" class="widefat" value="<?php echo esc_attr($app_title_prefix); ?>" placeholder="vd: Không phán đoán —" />
        </div>
        <div class="tt-field-row">
            <label>Tiêu đề nổi bật</label>
            <input type="text" name="landing_approach_title_highlight" class="widefat" value="<?php echo esc_attr($app_title_highlight); ?>" placeholder="vd: mà giúp bạn hiểu mình sâu hơn" />
        </div>
    </div>

    <!-- 2. Mô tả Section -->
    <div class="tt-field-row">
        <label>Đoạn giới thiệu 1 (Mô tả chính)</label>
        <textarea name="landing_approach_desc1" class="widefat" rows="3" placeholder="Thiên Tâm không hướng khách hàng phụ thuộc vào một lời luận giải, mà xem Tử Vi như một góc nhìn hỗ trợ..."><?php echo esc_textarea($app_desc1); ?></textarea>
    </div>

    <div class="tt-field-row">
        <label>Đoạn giới thiệu 2 (Mô tả làm rõ / Triết lý bổ sung - tùy chọn)</label>
        <textarea name="landing_approach_desc2" class="widefat" rows="3" placeholder="Giá trị của việc luận giải không nằm ở việc nghe một câu đúng hay sai..."><?php echo esc_textarea($app_desc2); ?></textarea>
    </div>

    <!-- 3. Khung Ảnh Minh Họa & Triết Lý -->
    <div class="tt-card" style="margin-top:16px;">
        <div class="tt-card-header">Ảnh Minh Họa & Khung Thẻ Triết Lý</div>
        <div class="tt-image-picker-row" style="margin-bottom:12px;">
            <div id="tt-approach-image-preview" class="tt-image-preview">
                <?php if (! empty($app_image)) : ?>
                    <img src="<?php echo esc_url($app_image); ?>" />
                <?php else : ?>
                    <span style="font-size:11px; color:#888;">Chưa chọn ảnh</span>
                <?php endif; ?>
            </div>
            <div style="flex:1;">
                <input type="text" id="tt-approach-image-input" name="landing_approach_image" class="widefat" value="<?php echo esc_attr($app_image); ?>" placeholder="https://... hoặc bấm nút Chọn ảnh bên dưới" style="margin-bottom:8px;" />
                <div style="display:flex; gap:8px; align-items:center;">
                    <button type="button" id="tt-approach-image-btn" class="button button-secondary">📷 Chọn ảnh từ Thư viện</button>
                    <button type="button" id="tt-approach-image-remove" class="button button-link-delete" style="<?php echo empty($app_image) ? 'display:none;' : ''; ?>">Xóa ảnh</button>
                </div>
                <p class="description" style="margin-top:6px;">Chọn ảnh từ Thư viện WordPress Media hoặc dán URL trực tiếp. Nếu để trống, website sẽ hiển thị khung nhận diện thương hiệu mặc định.</p>
            </div>
        </div>

        <div class="tt-field-row">
            <label>Văn bản thay thế ảnh (Alt text)</label>
            <input type="text" name="landing_approach_image_alt" class="widefat" value="<?php echo esc_attr($app_image_alt); ?>" placeholder="vd: Lá số không thay bạn lựa chọn." />
        </div>

        <div class="tt-field-grid">
            <div class="tt-field-row">
                <label>Huy hiệu trên thẻ ảnh (Badge / Label)</label>
                <input type="text" name="landing_approach_card_badge" class="widefat" value="<?php echo esc_attr($app_card_badge); ?>" placeholder="vd: Góc nhìn Thiên Tâm" />
            </div>
            <div class="tt-field-row">
                <label>Tiêu đề thẻ ảnh / Câu trích dẫn nổi bật (Card Title / Quote)</label>
                <input type="text" name="landing_approach_card_title" class="widefat" value="<?php echo esc_attr($app_card_title); ?>" placeholder="vd: Lá số không thay bạn lựa chọn." />
            </div>
        </div>
    </div>

    <!-- 4. Danh Sách Điểm Nhấn / Checklist -->
    <div class="tt-field-row" style="margin-top:16px;">
        <label>Danh Sách Điểm Cốt Lõi / Checklist Điểm Nhấn (mỗi dòng 1 ý)</label>
        <textarea name="landing_approach_pills" class="widefat" rows="5" placeholder="Hiểu mình và hoàn cảnh thực tế&#10;Nhận diện xu hướng và tiềm năng&#10;Hiểu đúng thời điểm để chủ động&#10;Không phán đoán áp đặt một chiều"><?php echo esc_textarea($app_pills_str); ?></textarea>
        <p class="description" style="margin-top:4px;">Nhập các ý cốt lõi, mỗi dòng 1 ý hiển thị cùng icon tích xanh.</p>
    </div>

    <!-- 5. Nút Kêu Gọi Hành Động (CTA Button) -->
    <div class="tt-field-row" style="margin-top:16px;">
        <label>Nhãn nút bấm kêu gọi hành động (CTA Button)</label>
        <input type="text" name="landing_approach_btn" class="widefat" value="<?php echo esc_attr($app_btn); ?>" placeholder="vd: Tìm hiểu thêm (hoặc Đăng ký tư vấn)" />
    </div>

<?php if ($tab_approach_wrap) : ?>
</div>
<?php endif; ?>
