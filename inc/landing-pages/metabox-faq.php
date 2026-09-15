<?php
/**
 * Shared FAQ Metabox Component (Hỏi đáp thường gặp)
 * Scope: $post, $defaults, $get_val, $get_json_val (provided by parent metabox.php)
 * Optional parameters:
 *   $tab_faq_id       (string) Tab pane ID (default 'tab-faq')
 *   $tab_faq_active   (bool)   Is active pane (default false)
 *   $tab_faq_wrap     (bool)   Wrap inside <div class="tt-tab-pane"> (default true)
 *   $tab_faq_heading  (string) Section heading title (optional)
 *   $faq_data_key     (string) Key in $defaults array (default 'faq')
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

$tab_faq_id      = isset($tab_faq_id) && $tab_faq_id !== '' ? $tab_faq_id : 'tab-faq';
$tab_faq_active  = ! empty($tab_faq_active);
$tab_faq_wrap    = ! isset($tab_faq_wrap) || $tab_faq_wrap !== false;
$tab_faq_heading = isset($tab_faq_heading) ? $tab_faq_heading : '';
$faq_data_key    = ! empty($faq_data_key) ? $faq_data_key : 'faq';

// Header fields (ko gán text default)
$faq_prefix    = $get_val('landing_faq_title_prefix', $defaults[$faq_data_key]['titlePrefix'] ?? '');
$faq_highlight = $get_val('landing_faq_title_highlight', $defaults[$faq_data_key]['titleHighlight'] ?? '');
$faq_title     = $get_val('landing_faq_title', $defaults[$faq_data_key]['title'] ?? '');
$faq_desc      = $get_val('landing_faq_desc', $defaults[$faq_data_key]['desc'] ?? '');
$faq_note      = $get_val('landing_faq_note', $defaults[$faq_data_key]['note'] ?? '');
$faq_items     = $get_json_val('landing_faq_items', $defaults[$faq_data_key]['items'] ?? array());

// Nếu $faq_prefix & $faq_highlight rỗng nhưng $faq_title có giá trị, dùng $faq_prefix = $faq_title
if ($faq_prefix === '' && $faq_highlight === '' && $faq_title !== '') {
    $faq_prefix = $faq_title;
}
?>

<?php if ($tab_faq_wrap) : ?>
<div id="<?php echo esc_attr($tab_faq_id); ?>" class="tt-tab-pane <?php echo $tab_faq_active ? 'active' : ''; ?>">
<?php endif; ?>

    <?php if (! empty($tab_faq_heading)) : ?>
        <h3 style="margin-top:0; color:#0f3d61;"><?php echo esc_html($tab_faq_heading); ?></h3>
    <?php endif; ?>

    <div class="tt-field-grid">
        <div class="tt-field-row">
            <label>Tiêu đề đầu (Prefix)</label>
            <input type="text" name="landing_faq_title_prefix" class="widefat" placeholder="VD: Câu hỏi thường gặp về..." value="<?php echo esc_attr($faq_prefix); ?>" />
        </div>
        <div class="tt-field-row">
            <label>Tiêu đề nổi bật (Highlight)</label>
            <input type="text" name="landing_faq_title_highlight" class="widefat" placeholder="VD: Luận giải Tử Vi Thiên Tâm" value="<?php echo esc_attr($faq_highlight); ?>" />
        </div>
    </div>

    <div class="tt-field-row">
        <label>Mô tả phần FAQ</label>
        <textarea name="landing_faq_desc" class="widefat" rows="2" placeholder="VD: Những thắc mắc thường gặp trước khi đăng ký tham vấn..."><?php echo esc_textarea($faq_desc); ?></textarea>
    </div>

    <div class="tt-field-row">
        <label>Ghi chú chân FAQ (Tùy chọn)</label>
        <input type="text" name="landing_faq_note" class="widefat" placeholder="VD: Mọi thông tin lá số và dữ liệu cá nhân của bạn được bảo mật tuyệt đối." value="<?php echo esc_attr($faq_note); ?>" />
    </div>

    <div class="tt-card">
        <div class="tt-card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span>Danh sách Câu hỏi & Trả lời</span>
                <div class="tt-faq-actions-bar">
                    <button type="button" class="tt-faq-btn-toggle-all" id="tt-faq-expand-all">Mở tất cả</button>
                    <span style="color:#cbd5e1;">|</span>
                    <button type="button" class="tt-faq-btn-toggle-all" id="tt-faq-collapse-all">Thu gọn tất cả</button>
                </div>
            </div>
            <button type="button" id="tt-btn-add-faq" class="button button-secondary" style="font-weight:normal;">+ Thêm câu hỏi</button>
        </div>
        <div id="tt-faq-container">
            <?php foreach ($faq_items as $fi => $fq) :
                $q_text = $fq['q'] ?? '';
                $a_text = $fq['a'] ?? '';
            ?>
                <div class="tt-faq-accordion-item tt-faq-row <?php echo $fi === 0 ? 'is-open' : ''; ?>">
                    <div class="tt-faq-accordion-header">
                        <div class="tt-faq-header-left">
                            <span class="tt-faq-toggle-icon">▸</span>
                            <strong class="tt-faq-badge">#<span class="faq-num"><?php echo $fi + 1; ?></span></strong>
                            <span class="tt-faq-title-preview">
                                <?php echo ! empty($q_text) ? esc_html($q_text) : 'Chưa đặt câu hỏi...'; ?>
                            </span>
                        </div>
                        <button type="button" class="button-link-delete tt-btn-remove-faq" style="cursor:pointer;" title="Xóa câu hỏi này">Xóa</button>
                    </div>
                    <div class="tt-faq-accordion-body">
                        <div class="tt-field-row" style="margin-bottom:10px;">
                            <label style="font-size:11px; font-weight:600; color:#475569; margin-bottom:4px;">Câu hỏi</label>
                            <input type="text" name="faq_items[<?php echo $fi; ?>][q]" class="widefat tt-faq-q-input" placeholder="Nhập câu hỏi..." value="<?php echo esc_attr($q_text); ?>" />
                        </div>
                        <div class="tt-field-row" style="margin-bottom:0;">
                            <label style="font-size:11px; font-weight:600; color:#475569; margin-bottom:4px;">Câu trả lời</label>
                            <textarea name="faq_items[<?php echo $fi; ?>][a]" class="widefat" rows="3" placeholder="Nhập câu trả lời..."><?php echo esc_textarea($a_text); ?></textarea>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php if ($tab_faq_wrap) : ?>
</div>
<?php endif; ?>
