<?php
/**
 * Shared Packages Metabox Component (Bảng giá dịch vụ / Các gói luận giải)
 * Scope: $post, $defaults, $get_val, $get_json_val (provided by parent metabox.php)
 * Optional parameters:
 *   $tab_pkg_id       (string) Tab pane ID (default 'tab-pkg')
 *   $tab_pkg_active   (bool)   Is active pane (default false)
 *   $tab_pkg_wrap     (bool)   Wrap inside <div class="tt-tab-pane"> (default true)
 *   $tab_pkg_heading  (string) Section heading title (optional)
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

// 1. Chuẩn bị dữ liệu Packages
$current_tab_id     = ! empty($tab_pkg_id) ? $tab_pkg_id : 'tab-pkg';
$pkg_prefix_name    = ! empty($pkg_prefix_name) ? $pkg_prefix_name : 'landing_packages_title_prefix';
$pkg_highlight_name = ! empty($pkg_highlight_name) ? $pkg_highlight_name : 'landing_packages_title_highlight';
$pkg_lead_name      = ! empty($pkg_lead_name) ? $pkg_lead_name : 'landing_packages_lead';
$pkg_btn_name       = ! empty($pkg_btn_name) ? $pkg_btn_name : 'landing_packages_btn';
$pkg_items_name     = ! empty($pkg_items_name) ? $pkg_items_name : 'landing_packages_items';
$pkg_data_key       = ! empty($pkg_data_key) ? $pkg_data_key : 'packages';

$pkg_prefix       = $get_val($pkg_prefix_name, $defaults[$pkg_data_key]['titlePrefix'] ?? '');
$pkg_highlight    = $get_val($pkg_highlight_name, $defaults[$pkg_data_key]['titleHighlight'] ?? '');
$pkg_lead         = $get_val($pkg_lead_name, $defaults[$pkg_data_key]['lead'] ?? '');
$pkg_btn          = $get_val($pkg_btn_name, $defaults[$pkg_data_key]['btn'] ?? 'Đăng ký ngay');
$packages_items   = $get_json_val($pkg_items_name, array());

$consult_title    = $get_val('landing_consultation_title', $defaults[$pkg_data_key]['consultation']['title'] ?? '');
$consult_desc     = $get_val('landing_consultation_desc', $defaults[$pkg_data_key]['consultation']['desc'] ?? '');
$consult_btn      = $get_val('landing_consultation_btn', $defaults[$pkg_data_key]['consultation']['btn'] ?? '');
$consult_prefill  = $get_val('landing_consultation_prefill', $defaults[$pkg_data_key]['consultation']['prefill'] ?? '');

$wrap_tab         = ! isset($tab_pkg_wrap) || (bool) $tab_pkg_wrap;
$is_active_pane   = ! empty($tab_pkg_active);
$pkg_list         = (is_array($packages_items) && ! empty($packages_items)) ? array_values($packages_items) : array();
$num_cards        = count($pkg_list);
?>

<?php if ($wrap_tab) : ?>
<!-- TAB: GÓI DỊCH VỤ (PACKAGES) DÙNG CHUNG -->
<div id="<?php echo esc_attr($current_tab_id); ?>" class="tt-tab-pane <?php echo $is_active_pane ? 'active' : ''; ?>">
<?php endif; ?>

    <input type="hidden" name="tt_packages_submitted" value="1" />

    <?php if (! empty($tab_pkg_heading)) : ?>
        <h3 style="margin-top:0; color:#0f3d61;"><?php echo esc_html($tab_pkg_heading); ?></h3>
    <?php endif; ?>

    <div class="tt-field-grid">
        <div class="tt-field-row">
            <label>Tiêu đề đầu</label>
            <input type="text" name="<?php echo esc_attr($pkg_prefix_name); ?>" class="widefat" value="<?php echo esc_attr($pkg_prefix); ?>" />
        </div>
        <div class="tt-field-row">
            <label>Tiêu đề nổi bật</label>
            <input type="text" name="<?php echo esc_attr($pkg_highlight_name); ?>" class="widefat" value="<?php echo esc_attr($pkg_highlight); ?>" />
        </div>
    </div>
    <div class="tt-field-row">
        <label>Mô tả phần gói (Lời dẫn)</label>
        <textarea name="<?php echo esc_attr($pkg_lead_name); ?>" class="widefat" rows="2"><?php echo esc_textarea($pkg_lead); ?></textarea>
    </div>
    <div class="tt-field-row">
        <label>Text nút bấm dùng chung (Mặc định: Đăng ký ngay)</label>
        <input type="text" name="<?php echo esc_attr($pkg_btn_name); ?>" class="widefat" value="<?php echo esc_attr($pkg_btn); ?>" />
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:24px; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
        <div>
            <h3 style="color:#0f3d61; margin:0; display:flex; align-items:center; gap:8px;">
                <span>Chi tiết các Gói Dịch Vụ</span>
                <span class="tt-packages-count-badge" style="font-size:12px; font-weight:600; background:#e0f2fe; color:#0369a1; padding:2px 9px; border-radius:12px;"><?php echo count($pkg_list); ?> gói</span>
            </h3>
            <p style="margin:2px 0 0 0; font-size:12px; color:#64748b;">Thêm, bớt hoặc chỉnh sửa nội dung và bảng giá không giới hạn. Bấm vào tiêu đề mỗi gói để thu gọn / mở rộng.</p>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <button type="button" class="button tt-btn-collapse-all-packages" title="Thu gọn toàn bộ danh sách gói" style="height:32px; font-size:12px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;">
                <span class="dashicons dashicons-arrow-up-alt2" style="font-size:15px; width:15px; height:15px; line-height:15px;"></span> Thu gọn tất cả
            </button>
            <button type="button" class="button tt-btn-expand-all-packages" title="Mở rộng toàn bộ danh sách gói" style="height:32px; font-size:12px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;">
                <span class="dashicons dashicons-arrow-down-alt2" style="font-size:15px; width:15px; height:15px; line-height:15px;"></span> Mở rộng tất cả
            </button>
            <button type="button" class="button button-primary tt-btn-add-package" style="background:#0f3d61; border-color:#0f3d61; height:32px; padding:0 14px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
                + Thêm gói dịch vụ
            </button>
        </div>
    </div>

    <!-- Empty State Notice -->
    <div id="tt-packages-empty-notice" class="tt-packages-empty-notice" style="<?php echo empty($pkg_list) ? 'display:block;' : 'display:none;'; ?> background:#f8fafc; border:2px dashed #cbd5e1; border-radius:8px; padding:32px 20px; text-align:center; color:#64748b; margin-bottom:16px;">
        <div style="font-size:28px; margin-bottom:8px;">📦</div>
        <div style="font-weight:600; font-size:14px; color:#334155; margin-bottom:4px;">Chưa có gói dịch vụ nào</div>
        <p style="margin:0 0 14px 0; font-size:12px; color:#64748b;">Bạn có thể tạo gói dịch vụ mới bằng cách bấm vào nút bên dưới.</p>
        <button type="button" class="button button-primary tt-btn-add-package" style="background:#0f3d61; border-color:#0f3d61; height:34px; padding:0 16px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
            + Thêm gói dịch vụ
        </button>
    </div>

    <div id="tt-packages-container" class="tt-packages-container">
        <?php 
        foreach ($pkg_list as $pi => $pitem) : 
            $num_formatted = sprintf('%02d', $pi + 1);
            $card_title = ! empty($pitem['title']) ? $pitem['title'] : 'Gói dịch vụ ' . $num_formatted;
            $card_price = ! empty($pitem['price']) ? $pitem['price'] : '';
            $is_featured = ! empty($pitem['isFeatured']);
        ?>
            <div class="tt-card tt-package-card-row" data-index="<?php echo $pi; ?>" style="background:#f8fafc; border:1px solid #cbd5e1; margin-bottom:14px; border-radius:8px; padding:0; overflow:hidden;">
                <div class="tt-package-card-header" style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px; background:#f1f5f9; border-bottom:1px solid #cbd5e1; cursor:pointer; user-select:none;">
                    <div class="tt-pkg-header-left" style="display:flex; align-items:center; gap:8px; flex:1; min-width:0;">
                        <span class="tt-pkg-toggle-icon" style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:4px; background:#e2e8f0; color:#475569; transition:transform 0.2s ease; flex-shrink:0;">
                            <span class="dashicons dashicons-arrow-up-alt2" style="font-size:16px; width:16px; height:16px; line-height:16px;"></span>
                        </span>
                        <span style="font-weight:700; color:#0f3d61; font-size:13px; white-space:nowrap;">
                            Gói <span class="pkg-num"><?php echo esc_html($num_formatted); ?></span>:
                        </span>
                        <span class="pkg-title-preview" style="font-weight:600; color:#1e293b; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:320px;">
                            <?php echo esc_html($card_title); ?>
                        </span>
                        <span class="pkg-price-preview" style="<?php echo ! empty($card_price) ? 'display:inline-block;' : 'display:none;'; ?> font-size:11px; font-weight:600; color:#0369a1; background:#e0f2fe; padding:2px 8px; border-radius:12px; white-space:nowrap;">
                            <?php echo esc_html($card_price); ?>
                        </span>
                        <span class="pkg-featured-badge" style="<?php echo $is_featured ? 'display:inline-block;' : 'display:none;'; ?> font-size:11px; font-weight:600; color:#b45309; background:#fef3c7; padding:2px 8px; border-radius:12px; white-space:nowrap;">
                            ★ Nổi bật
                        </span>
                    </div>
                    <div class="tt-pkg-header-right" style="display:flex; align-items:center; gap:12px; flex-shrink:0;">
                        <label class="tt-pkg-featured-label" style="font-size:12px; font-weight:normal; margin:0; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">
                            <input type="checkbox" name="pkg[<?php echo $pi; ?>][isFeatured]" class="tt-pkg-featured" value="1" <?php checked($is_featured); ?> /> <strong>Gói nổi bật (Highlight)</strong>
                        </label>
                        <button type="button" class="button-link-delete tt-btn-remove-package" title="Xóa gói này" style="color:#b32d2e; font-size:12px; text-decoration:none; cursor:pointer; padding:3px 6px;">
                            ✕ Xóa gói
                        </button>
                        <button type="button" class="button tt-btn-toggle-package" title="Thu gọn / Mở rộng" style="padding:0 8px; height:26px; line-height:24px; min-height:26px; font-size:11px; display:inline-flex; align-items:center; gap:2px;">
                            <span class="tt-toggle-text">Thu gọn</span>
                        </button>
                    </div>
                </div>

                <div class="tt-package-card-body" style="padding:16px;">
                    <div class="tt-field-grid-3">
                        <div class="tt-field-row">
                            <label>Tên gói</label>
                            <input type="text" name="pkg[<?php echo $pi; ?>][title]" class="widefat tt-pkg-input-title" value="<?php echo esc_attr($pitem['title'] ?? ''); ?>" placeholder="VD: Bản Luận Căn Bản" />
                        </div>
                        <div class="tt-field-row">
                            <label>Mã gói (ID - Tự động tạo từ tên gói)</label>
                            <?php 
                            $pkg_calc_id = ! empty($pitem['title']) ? sanitize_title($pitem['title']) : ($pitem['id'] ?? ('pkg-' . ($pi + 1)));
                            ?>
                            <input type="text" name="pkg[<?php echo $pi; ?>][id]" class="widefat tt-pkg-input-id" value="<?php echo esc_attr($pkg_calc_id); ?>" readonly style="background:#f1f5f9; color:#475569; cursor:not-allowed; border-color:#cbd5e1;" />
                        </div>
                        <div class="tt-field-row">
                            <label>Huy hiệu (Badge)</label>
                            <input type="text" name="pkg[<?php echo $pi; ?>][badge]" class="widefat" value="<?php echo esc_attr($pitem['badge'] ?? ''); ?>" placeholder="VD: PHỔ BIẾN NHẤT" />
                        </div>
                    </div>
                    <div class="tt-field-grid">
                        <div class="tt-field-row">
                            <label>Giá bán hiện tại (VD: 2.500.000đ)</label>
                            <input type="text" name="pkg[<?php echo $pi; ?>][price]" class="widefat tt-pkg-input-price" value="<?php echo esc_attr($pitem['price'] ?? ''); ?>" />
                        </div>
                        <div class="tt-field-row">
                            <label>Giá gốc gạch ngang (VD: Giá gốc 5.000.000đ)</label>
                            <input type="text" name="pkg[<?php echo $pi; ?>][oldPrice]" class="widefat" value="<?php echo esc_attr($pitem['oldPrice'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="tt-field-row">
                        <label>Slogan / Câu châm ngôn ngắn của gói</label>
                        <input type="text" name="pkg[<?php echo $pi; ?>][slogan]" class="widefat" value="<?php echo esc_attr($pitem['slogan'] ?? ''); ?>" />
                    </div>
                    <div class="tt-field-row">
                        <label>Đối tượng phù hợp (Target)</label>
                        <textarea name="pkg[<?php echo $pi; ?>][target]" class="widefat" rows="2"><?php echo esc_textarea($pitem['target'] ?? ''); ?></textarea>
                    </div>
                    <div class="tt-field-grid">
                        <div class="tt-field-row">
                            <label>Lời khuyên chọn gói (Guidance)</label>
                            <input type="text" name="pkg[<?php echo $pi; ?>][guidance]" class="widefat" value="<?php echo esc_attr($pitem['guidance'] ?? ''); ?>" />
                        </div>
                        <div class="tt-field-row">
                            <label>Gợi ý form khi bấm chọn (Prefill)</label>
                            <input type="text" name="pkg[<?php echo $pi; ?>][prefill]" class="widefat" value="<?php echo esc_attr($pitem['prefill'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="tt-field-row">
                        <label>Các tính năng nổi bật (Mỗi dòng 1 mục)</label>
                        <textarea name="pkg[<?php echo $pi; ?>][features]" class="widefat" rows="4"><?php echo esc_textarea(isset($pitem['features']) && is_array($pitem['features']) ? implode("\n", $pitem['features']) : ''); ?></textarea>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- BANNER TƯ VẤN / KHAI VẤN THÊM -->
    <div class="tt-card" style="background:#fff; border:1px solid #cbd5e1; margin-top:20px; border-radius:8px; padding:16px;">
        <div class="tt-card-header" style="font-weight:bold; color:#0f3d61; margin-bottom:12px; border-bottom:1px solid #e2e8f0; padding-bottom:8px;">
            Banner Tư Vấn Trực Tiếp / Hỗ Trợ Riêng (Tùy chọn dưới bảng giá)
        </div>
        <div class="tt-field-grid">
            <div class="tt-field-row">
                <label>Tiêu đề banner</label>
                <input type="text" name="landing_consultation_title" class="widefat" value="<?php echo esc_attr($consult_title); ?>" placeholder="VD: Cần được tư vấn trước khi chọn gói?" />
            </div>
            <div class="tt-field-row">
                <label>Nút bấm</label>
                <input type="text" name="landing_consultation_btn" class="widefat" value="<?php echo esc_attr($consult_btn); ?>" placeholder="VD: Đăng ký tư vấn nhanh" />
            </div>
        </div>
        <div class="tt-field-row">
            <label>Mô tả ngắn</label>
            <textarea name="landing_consultation_desc" class="widefat" rows="2"><?php echo esc_textarea($consult_desc); ?></textarea>
        </div>
        <div class="tt-field-row">
            <label>Nội dung điền sẵn vào form (Prefill)</label>
            <input type="text" name="landing_consultation_prefill" class="widefat" value="<?php echo esc_attr($consult_prefill); ?>" placeholder="VD: Tư vấn lựa chọn gói phù hợp" />
        </div>
    </div>

<?php if ($wrap_tab) : ?>
</div>
<?php endif; ?>
