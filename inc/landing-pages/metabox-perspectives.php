<?php
/**
 * Shared Perspectives Metabox Component (Góc nhìn)
 * Scope: $post, $defaults, $get_val, $get_json_val (provided by parent metabox.php)
 * Optional parameters:
 *   $tab_persp_id      (string) Tab pane ID (default 'tab-persp')
 *   $tab_persp_active  (bool)   Is active pane (default false)
 *   $tab_persp_wrap    (bool)   Wrap inside <div class="tt-tab-pane"> (default true)
 *   $tab_persp_heading (string) Section heading title (optional)
 *
 * @package ThienTamData
 */

if (! defined('ABSPATH')) {
    exit;
}

// Fallback helpers nếu chưa được định nghĩa bởi parent metabox
if (! isset($get_val) || ! is_callable($get_val)) {
    $get_val = function($meta_key, $default_val = '') use ($post) {
        if (metadata_exists('post', $post->ID, $meta_key)) {
            return get_post_meta($post->ID, $meta_key, true);
        }
        return $default_val;
    };
}

if (! isset($get_json_val) || ! is_callable($get_json_val)) {
    $get_json_val = function($meta_key, $default_arr = array()) use ($post) {
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

// 1. Chuẩn bị dữ liệu Perspectives (Hỗ trợ cấu hình lại trường khi include nhiều lần)
$current_tab_id        = ! empty($tab_persp_id) ? $tab_persp_id : 'tab-persp';
$persp_prefix_name     = ! empty($persp_prefix_name) ? $persp_prefix_name : 'landing_perspectives_title_prefix';
$persp_highlight_name  = ! empty($persp_highlight_name) ? $persp_highlight_name : 'landing_perspectives_title_highlight';
$persp_lead_name       = ! empty($persp_lead_name) ? $persp_lead_name : 'landing_perspectives_lead';
$persp_cards_field     = ! empty($persp_cards_field) ? $persp_cards_field : 'persp_cards';
$persp_data_key        = ! empty($persp_data_key) ? $persp_data_key : 'perspectives';
$container_id          = 'tt-persp-cards-container-' . esc_attr($current_tab_id);
$persp_prefix     = $get_val($persp_prefix_name, $defaults[$persp_data_key]['titlePrefix'] ?? '');
$persp_highlight  = $get_val($persp_highlight_name, $defaults[$persp_data_key]['titleHighlight'] ?? '');
$persp_lead       = $get_val($persp_lead_name, $defaults[$persp_data_key]['lead'] ?? '');
$persp_cards      = $get_json_val($persp_cards_field, $defaults[$persp_data_key]['cards'] ?? array());

$wrap_tab         = ! isset($tab_persp_wrap) || (bool) $tab_persp_wrap;
$is_active_pane   = ! empty($tab_persp_active);
?>

<?php if ($wrap_tab) : ?>
<!-- TAB: GÓC NHÌN (PERSPECTIVES) DÙNG CHUNG -->
<div id="<?php echo esc_attr($current_tab_id); ?>" class="tt-tab-pane <?php echo $is_active_pane ? 'active' : ''; ?>">
<?php endif; ?>

    <?php if (! empty($tab_persp_heading)) : ?>
        <h3 style="margin-top:0; color:#0f3d61;"><?php echo esc_html($tab_persp_heading); ?></h3>
    <?php endif; ?>

    <div class="tt-field-grid">
        <div class="tt-field-row">
            <label>Tiêu đề đầu</label>
            <input type="text" name="<?php echo esc_attr($persp_prefix_name); ?>" class="widefat" value="<?php echo esc_attr($persp_prefix); ?>" />
        </div>
        <div class="tt-field-row">
            <label>Tiêu đề nổi bật</label>
            <input type="text" name="<?php echo esc_attr($persp_highlight_name); ?>" class="widefat" value="<?php echo esc_attr($persp_highlight); ?>" />
        </div>
    </div>
    <div class="tt-field-row">
        <label>Lời dẫn</label>
        <textarea name="<?php echo esc_attr($persp_lead_name); ?>" class="widefat" rows="2"><?php echo esc_textarea($persp_lead); ?></textarea>
    </div>

    <div class="tt-card" style="background:#f8fafc; border:1px solid #cbd5e1;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <div>
                <strong style="color:#0f3d61; font-size:14px;">Danh sách thẻ</strong>
                <p style="margin:2px 0 0 0; font-size:12px; color:#64748b;">Mỗi thẻ gồm biểu tượng Lucide (tùy chọn), tiêu đề và mô tả ngắn</p>
            </div>
            <button type="button" class="button button-primary tt-btn-add-persp-card" data-field-name="<?php echo esc_attr($persp_cards_field); ?>" data-container-id="<?php echo esc_attr($container_id); ?>" style="background:#0f3d61; border-color:#0f3d61; height:34px; padding:0 14px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
                + Thêm thẻ
            </button>
        </div>

        <?php
        $persp_cards_list = ! empty($persp_cards) && is_array($persp_cards) ? array_values($persp_cards) : array(array('title' => '', 'desc' => '', 'icon' => ''));
        ?>
        <div id="<?php echo esc_attr($container_id); ?>" class="tt-persp-grid">
            <?php foreach ($persp_cards_list as $ci => $cd) : 
                $cur_icon = isset($cd['icon']) ? trim($cd['icon']) : '';
                $has_icon = ! empty($cur_icon) && $cur_icon !== 'none';
            ?>
                <div class="tt-persp-card-box tt-persp-card-row">
                    <!-- Header -->
                    <div class="tt-persp-card-header">
                        <span class="tt-persp-card-badge">Thẻ #<span class="persp-num"><?php echo $ci + 1; ?></span></span>
                        <button type="button" class="button-link-delete tt-btn-remove-persp" title="Xóa thẻ này">✕ Xóa thẻ</button>
                    </div>

                    <!-- Body Layout -->
                    <div class="tt-persp-card-body">
                        <!-- Icon Selector Box -->
                        <div class="tt-persp-icon-col">
                            <label class="tt-field-label">Icon</label>
                            <button type="button" class="tt-persp-icon-btn tt-btn-pick-lucide" title="Bấm để chọn / đổi icon Lucide">
                                <span class="tt-persp-icon-preview">
                                    <?php if ($has_icon) : ?>
                                        <i data-lucide="<?php echo esc_attr($cur_icon); ?>"></i>
                                    <?php else : ?>
                                        <span style="font-size:14px; color:#94a3b8; font-weight:bold;">∅</span>
                                    <?php endif; ?>
                                </span>
                                <span class="tt-persp-icon-name"><?php echo $has_icon ? esc_html($cur_icon) : '(Không icon)'; ?></span>
                            </button>
                            <span class="tt-persp-change-link tt-btn-pick-lucide"><?php echo $has_icon ? 'Đổi icon ▾' : '+ Chọn icon ▾'; ?></span>
                            <input type="hidden" name="<?php echo esc_attr($persp_cards_field); ?>[<?php echo $ci; ?>][icon]" class="tt-persp-icon-input" value="<?php echo esc_attr($cur_icon); ?>" />
                        </div>

                        <!-- Inputs Column -->
                        <div class="tt-persp-fields-col">
                            <div class="tt-persp-field-group">
                                <label class="tt-field-label">Tiêu đề thẻ</label>
                                <input type="text" name="<?php echo esc_attr($persp_cards_field); ?>[<?php echo $ci; ?>][title]" class="widefat tt-input-title" value="<?php echo esc_attr($cd['title'] ?? ''); ?>" placeholder="vd: Tiêu đề thẻ" />
                            </div>
                            <div class="tt-persp-field-group">
                                <label class="tt-field-label">Mô tả chi tiết</label>
                                <textarea name="<?php echo esc_attr($persp_cards_field); ?>[<?php echo $ci; ?>][desc]" class="widefat tt-textarea-desc" rows="2" placeholder="Nhập mô tả..."><?php echo esc_textarea($cd['desc'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php if ($wrap_tab) : ?>
</div>
<?php endif; ?>

<?php if (! defined('TT_LUCIDE_MODAL_RENDERED')) : define('TT_LUCIDE_MODAL_RENDERED', true); ?>
<!-- MODAL LUCIDE ICON PICKER (DÙNG CHUNG) -->
<div id="tt-lucide-modal" class="tt-lucide-modal-overlay">
    <div class="tt-lucide-modal-box">
        <!-- Header -->
        <div style="padding:14px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
            <div>
                <h3 style="margin:0; font-size:15px; color:#0f3d61; font-weight:700; display:flex; align-items:center; gap:6px;">
                    <i data-lucide="sparkles" style="width:18px; height:18px; color:#d97706;"></i> Chọn Biểu Tượng Lucide
                </h3>
                <p style="margin:2px 0 0 0; font-size:11px; color:#64748b;">Khám phá hơn 2.000+ biểu tượng Lucide hoặc tìm kiếm theo tên</p>
            </div>
            <button type="button" id="tt-lucide-close" style="background:none; border:none; font-size:24px; cursor:pointer; color:#64748b; line-height:1; padding:0 4px;">&times;</button>
        </div>

        <!-- Toolbar & Search -->
        <div style="padding:12px 20px; border-bottom:1px solid #e2e8f0; background:#ffffff;">
            <div style="display:flex; gap:8px; margin-bottom:10px;">
                <input type="text" id="tt-lucide-search" placeholder="Tìm trong 2.000+ icon (vd: calendar, clock, phone, check, user, file, star...)" style="flex:1; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;" />
                <button type="button" id="tt-lucide-btn-apply-custom" class="button" style="white-space:nowrap; height:34px;">Dùng tên vừa gõ</button>
            </div>
            <div id="tt-lucide-filter-tags" style="display:flex; flex-wrap:wrap; gap:6px;">
                <button type="button" class="tt-lucide-tag-btn active" data-cat="all">Tất cả (2.000+)</button>
                <button type="button" class="tt-lucide-tag-btn" data-cat="process">Quy trình & Các bước</button>
                <button type="button" class="tt-lucide-tag-btn" data-cat="edu">Trí tuệ & Học tập</button>
                <button type="button" class="tt-lucide-tag-btn" data-cat="care">Yêu thương & Gia đình</button>
                <button type="button" class="tt-lucide-tag-btn" data-cat="energy">Năng lượng & Rèn luyện</button>
                <button type="button" class="tt-lucide-tag-btn" data-cat="nature">Khám phá & Thiên nhiên</button>
                <button type="button" class="tt-lucide-tag-btn" data-cat="art">Sáng tạo & Kết nối</button>
            </div>
        </div>

        <!-- Icon Grid -->
        <div id="tt-lucide-grid" style="padding:16px 20px; overflow-y:auto; flex:1; display:grid; grid-template-columns:repeat(auto-fill, minmax(88px, 1fr)); gap:10px; background:#f8fafc; align-content:start;">
        </div>

        <!-- Footer -->
        <div style="padding:12px 20px; border-top:1px solid #e2e8f0; background:#ffffff; display:flex; justify-content:space-between; align-items:center; font-size:12px; color:#64748b;">
            <span>Đang chọn cho: <strong id="tt-lucide-target-label" style="color:#0f3d61; font-weight:700;">Thẻ #1</strong></span>
            <div>
                <button type="button" class="button" id="tt-lucide-btn-none" style="color:#b91c1c; border-color:#fca5a5; margin-right:8px;">✕ Không dùng icon</button>
                <button type="button" class="button" id="tt-lucide-cancel">Đóng</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
