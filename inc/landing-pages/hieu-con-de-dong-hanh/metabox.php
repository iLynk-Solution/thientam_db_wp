<?php
/**
 * Metabox UI for 'Hiểu con để đồng hành'
 * Scope: $post, $slug, $landings
 */

if (! defined('ABSPATH')) {
    exit;
}

// 1. Ưu tiên lấy dữ liệu trực tiếp từ bài viết hiện tại ($post->ID)
$custom_json_raw = get_post_meta($post->ID, 'landing_custom_json', true);
$defaults = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($custom_json_raw) : array();
if (empty($defaults) && function_exists('thientam_get_landing_default_data')) {
    $defaults = thientam_get_landing_default_data('hieu-con-de-dong-hanh');
}

// Hàm trợ giúp lấy post_meta nếu có, chỉ fallback về mảng defaults khi trường CHƯA TỪNG được lưu trong DB
$get_val = function($meta_key, $default_val = '') use ($post) {
    if (metadata_exists('post', $post->ID, $meta_key)) {
        $val = get_post_meta($post->ID, $meta_key, true);
        if (is_string($val) && (strpos($val, 'Undefined variable') !== false || strpos($val, 'Warning') !== false)) {
            return '';
        }
        return $val;
    }
    return $default_val;
};

// Hàm trợ giúp lấy JSON post_meta dạng mảng
$get_json_val = function($meta_key, $default_arr = array()) use ($post) {
    if (metadata_exists('post', $post->ID, $meta_key)) {
        $val = get_post_meta($post->ID, $meta_key, true);
        if (! empty($val)) {
            $decoded = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($val) : (is_array($val) ? $val : json_decode(stripslashes($val), true));
            if (is_array($decoded) && ! empty($decoded)) {
                return $decoded;
            }
        }
    }
    return $default_arr;
};

// Chuẩn bị dữ liệu cho từng tab:

// Hai điểm bắt đầu (Doors)
$doors_prefix     = $get_val('landing_doors_title_prefix', $defaults['entryDoors']['titlePrefix'] ?? '');
$doors_highlight  = $get_val('landing_doors_title_highlight', $defaults['entryDoors']['titleHighlight'] ?? '');
$doors_lead       = $get_val('landing_doors_lead', $defaults['entryDoors']['lead'] ?? '');
$doors_saved      = $get_json_val('landing_entry_doors', $defaults['entryDoors'] ?? array());

$doors_items = array();
if (! empty($doors_saved['items']) && is_array($doors_saved['items'])) {
    $doors_items = array_values($doors_saved['items']);
} else {
    if (! empty($doors_saved['door1'])) $doors_items[] = $doors_saved['door1'];
    elseif (! empty($defaults['entryDoors']['door1'])) $doors_items[] = $defaults['entryDoors']['door1'];

    if (! empty($doors_saved['door2'])) $doors_items[] = $doors_saved['door2'];
    elseif (! empty($defaults['entryDoors']['door2'])) $doors_items[] = $defaults['entryDoors']['door2'];
}
$door1 = $doors_items[0] ?? array();
$door2 = $doors_items[1] ?? array();

// Approach
$approach_prefix       = $get_val('landing_approach_title_prefix', $defaults['approach']['titlePrefix'] ?? '');
$approach_highlight    = $get_val('landing_approach_title_highlight', $defaults['approach']['titleHighlight'] ?? '');
$approach_desc         = $get_val('landing_approach_desc', $defaults['approach']['desc'] ?? '');
$approach_notice       = $get_val('landing_approach_notice', $defaults['approach']['notice'] ?? '');
$approach_visual_quote = $get_val('landing_approach_visual_quote', $defaults['approach']['visualQuote'] ?? '');
$approach_visual_label = $get_val('landing_approach_visual_label', $defaults['approach']['visualLabel'] ?? '');
$approach_image        = $get_val('landing_approach_image', $defaults['approach']['image'] ?? '');
$approach_image_alt    = $get_val('landing_approach_image_alt', $defaults['approach']['imageAlt'] ?? '');
$approach_steps        = $defaults['approach']['steps'] ?? array();
$approach_points_raw   = $get_val('landing_approach_points', $defaults['approach']['points'] ?? array());
if (is_string($approach_points_raw)) {
    $decoded = json_decode($approach_points_raw, true);
    $approach_points_list = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode("
", str_replace("
", "", $approach_points_raw))));
} else {
    $approach_points_list = is_array($approach_points_raw) ? $approach_points_raw : array();
}

$approach_points_lines = array();
foreach ($approach_points_list as $pt) {
    if (is_array($pt)) {
        $b = trim($pt['bold'] ?? '');
        $t = trim($pt['text'] ?? '');
        if ($b !== '') {
            $approach_points_lines[] = "**{$b}** " . $t;
        } else {
            $approach_points_lines[] = $t;
        }
    } else {
        $approach_points_lines[] = trim($pt);
    }
}
$approach_points_str = implode("
", array_filter($approach_points_lines));

// Relationship

$rel_prefix       = $get_val('landing_relationship_title_prefix', $defaults['relationship']['titlePrefix'] ?? '');
$rel_highlight    = $get_val('landing_relationship_title_highlight', $defaults['relationship']['titleHighlight'] ?? '');
$rel_lead         = $get_val('landing_relationship_lead', $defaults['relationship']['lead'] ?? '');
$rel_statement    = $get_val('landing_relationship_statement', $defaults['relationship']['statement'] ?? '');
$rel_stmt_badge   = $get_val('landing_relationship_statement_badge', $defaults['relationship']['statementBadge'] ?? '');

$rel_parent_meta  = get_post_meta($post->ID, 'landing_relationship_parent', true);
if (! empty($rel_parent_meta)) {
    $decoded = json_decode($rel_parent_meta, true);
    $rel_parent_items = is_array($decoded) ? implode("
", $decoded) : $rel_parent_meta;
} else {
    $rel_parent_items = isset($defaults['relationship']['parent']['items']) && is_array($defaults['relationship']['parent']['items']) ? implode("
", $defaults['relationship']['parent']['items']) : '';
}

$rel_child_meta   = get_post_meta($post->ID, 'landing_relationship_child', true);
if (! empty($rel_child_meta)) {
    $decoded = json_decode($rel_child_meta, true);
    $rel_child_items = is_array($decoded) ? implode("
", $decoded) : $rel_child_meta;
} else {
    $rel_child_items = isset($defaults['relationship']['child']['items']) && is_array($defaults['relationship']['child']['items']) ? implode("
", $defaults['relationship']['child']['items']) : '';
}

// Packages
$pkg_prefix       = $get_val('landing_packages_title_prefix', $defaults['packages']['titlePrefix'] ?? '');
$pkg_highlight    = $get_val('landing_packages_title_highlight', $defaults['packages']['titleHighlight'] ?? '');
$pkg_lead         = $get_val('landing_packages_lead', $defaults['packages']['lead'] ?? '');
$pkg_btn          = $get_val('landing_packages_btn', $defaults['packages']['btn'] ?? '');
$packages_items   = $get_json_val('landing_packages_items', array());

// Expert
$expert_title_prefix    = $get_val('landing_expert_title_prefix', $defaults['expert']['titlePrefix'] ?? '');
$expert_title_highlight = $get_val('landing_expert_title_highlight', $defaults['expert']['titleHighlight'] ?? '');
$expert_avatar    = $get_val('landing_expert_avatar', $defaults['expert']['avatar'] ?? '');
$expert_name      = $get_val('landing_expert_name', $defaults['expert']['name'] ?? '');
$expert_badge     = $get_val('landing_expert_badge', $defaults['expert']['badge'] ?? '');
$expert_desc      = $get_val('landing_expert_desc', $defaults['expert']['desc'] ?? '');
$expert_desc1     = $get_val('landing_expert_desc1', $defaults['expert']['desc1'] ?? '');
$expert_desc2     = $get_val('landing_expert_desc2', $defaults['expert']['desc2'] ?? '');
$expert_notice    = $get_val('landing_expert_notice', $defaults['expert']['notice'] ?? '');
$expert_btn       = $get_val('landing_expert_btn', $defaults['expert']['btn'] ?? '');

$expert_pills_meta = get_post_meta($post->ID, 'landing_expert_pills', true);
if (! empty($expert_pills_meta)) {
    $decoded = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($expert_pills_meta) : json_decode($expert_pills_meta, true);
    $expert_pills = is_array($decoded) ? implode("\n", $decoded) : $expert_pills_meta;
} else {
    $expert_pills = isset($defaults['expert']['pills']) && is_array($defaults['expert']['pills']) ? implode("\n", $defaults['expert']['pills']) : '';
}

// Expert Direct Value
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

// Process & Trust
$proc_prefix      = $get_val('landing_process_title_prefix', $defaults['process']['titlePrefix'] ?? '');
$proc_highlight   = $get_val('landing_process_title_highlight', $defaults['process']['titleHighlight'] ?? '');
$proc_lead        = $get_val('landing_process_lead', $defaults['process']['lead'] ?? '');
$proc_notice      = $get_val('landing_process_notice', $defaults['process']['notice'] ?? '');
$proc_steps       = $get_json_val('landing_process_steps', $defaults['process']['steps'] ?? array());

$trust_prefix     = $get_val('landing_trust_title_prefix', $defaults['trust']['titlePrefix'] ?? '');
$trust_highlight  = $get_val('landing_trust_title_highlight', $defaults['trust']['titleHighlight'] ?? '');
$trust_lead       = $get_val('landing_trust_lead', $defaults['trust']['lead'] ?? '');

$trust_items_meta = get_post_meta($post->ID, 'landing_trust_items', true);
if (! empty($trust_items_meta)) {
    $decoded = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($trust_items_meta) : json_decode($trust_items_meta, true);
    $trust_items = is_array($decoded) ? implode("\n", $decoded) : $trust_items_meta;
} else {
    $trust_items = isset($defaults['trust']['items']) && is_array($defaults['trust']['items']) ? implode("\n", $defaults['trust']['items']) : '';
}

// FAQ
$faq_title        = $get_val('landing_faq_title', $defaults['faq']['title'] ?? '');
$faq_title_prefix    = $get_val('landing_faq_title_prefix', $defaults['faq']['titlePrefix'] ?? '');
$faq_title_highlight = $get_val('landing_faq_title_highlight', $defaults['faq']['titleHighlight'] ?? '');
$faq_desc         = $get_val('landing_faq_desc', $defaults['faq']['desc'] ?? '');
$faq_note         = $get_val('landing_faq_note', $defaults['faq']['note'] ?? '');
$faq_items        = $get_json_val('landing_faq_items', $defaults['faq']['items'] ?? array());

// Final CTA
$final_prefix     = $get_val('landing_final_title_prefix', $defaults['finalCta']['titlePrefix'] ?? '');
$final_highlight  = $get_val('landing_final_title_highlight', $defaults['finalCta']['titleHighlight'] ?? '');
$final_lead       = $get_val('landing_final_lead', $defaults['finalCta']['lead'] ?? '');
$final_statement  = $get_val('landing_final_statement', $defaults['finalCta']['statement'] ?? '');
?>
    <link rel="stylesheet" href="<?php echo esc_url(get_stylesheet_directory_uri() . '/inc/landing-pages/common/metabox.css?ver=' . (file_exists(dirname(__DIR__) . '/common/metabox.css') ? filemtime(dirname(__DIR__) . '/common/metabox.css') : '1.0')); ?>" />

    <div class="tt-tabs-wrapper">
        <div class="tt-tabs-nav">
            <button type="button" class="tt-tab-btn active" data-tab="tab-seo">01. SEO & Header</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-hero">02. Hero Banner</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-doors">03. Hai điểm bắt đầu</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-persp">04. Góc nhìn & Quan hệ</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-approach">05. Phương pháp tiếp cận</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-pkg">06. Gói dịch vụ</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-expert">07. Chuyên gia</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-proc">08. Quy trình & Niềm tin</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-faq">09. FAQ</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-final">10. Kêu gọi cuối trang</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-registration">11. Form đăng ký</button>
        </div>

        <!-- TAB 1: SEO & HEADER (DÙNG CHUNG) -->
        <?php
        $tab_seo_id = 'tab-seo';
        $tab_seo_active = true;
        include dirname(__DIR__) . '/metabox-seo.php';
        ?>

        <?php
        $tab_hero_id = 'tab-hero';
        $tab_hero_active = false;
        include dirname(__DIR__) . '/metabox-hero.php';
        ?>

        <!-- TAB 3: HAI ĐIỂM BẮT ĐẦU -->
        <div id="tab-doors" class="tt-tab-pane">
            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Tiêu đề đầu</label>
                    <input type="text" name="landing_doors_title_prefix" class="widefat" value="<?php echo esc_attr($doors_prefix); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Tiêu đề nổi bật</label>
                    <input type="text" name="landing_doors_title_highlight" class="widefat" value="<?php echo esc_attr($doors_highlight); ?>" />
                </div>
            </div>
            <div class="tt-field-row">
                <label>Lời dẫn</label>
                <textarea name="landing_doors_lead" class="widefat" rows="2"><?php echo esc_textarea($doors_lead); ?></textarea>
            </div>

                        <!-- Danh sách Cánh cửa (Accordion & Thêm/Xóa Item) -->
            <div class="tt-card" style="background:#f8fafc; border:1px solid #cbd5e1; margin-top:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:10px;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <strong style="color:#0f3d61; font-size:14px;">Danh sách Cánh cửa (Entry Doors)</strong>
                        <div class="tt-door-actions-bar">
                            <button type="button" class="tt-door-btn-toggle-all" id="tt-door-expand-all">Mở tất cả</button>
                            <span style="color:#cbd5e1;">|</span>
                            <button type="button" class="tt-door-btn-toggle-all" id="tt-door-collapse-all">Thu gọn tất cả</button>
                        </div>
                    </div>
                    <button type="button" id="tt-btn-add-door" class="button button-primary" style="background:#0f3d61; border-color:#0f3d61; height:34px; padding:0 14px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
                        + Thêm cánh cửa
                    </button>
                </div>

                <div id="tt-doors-container">
                    <?php foreach ($doors_items as $di => $door) :
                        $d_title = $door['title'] ?? '';
                        $d_badge = $door['badge'] ?? '';
                        $d_desc  = $door['desc'] ?? '';
                        $d_questions = isset($door['questions']) && is_array($door['questions']) ? implode("
", $door['questions']) : ($door['questions'] ?? '');
                        $d_highlight = $door['highlight'] ?? '';
                        $d_prefill   = $door['prefill'] ?? '';
                        $door_num_str = sprintf('%02d', $di + 1);
                    ?>
                        <div class="tt-door-accordion-item tt-door-row <?php echo $di === 0 ? 'is-open' : ''; ?>">
                            <div class="tt-door-accordion-header">
                                <div class="tt-door-header-left">
                                    <span class="tt-door-toggle-icon">▸</span>
                                    <strong class="tt-door-badge">Cánh cửa #<span class="door-num"><?php echo $door_num_str; ?></span></strong>
                                    <span class="tt-door-title-preview">
                                        <?php echo ! empty($d_title) ? esc_html($d_title) : '(Chưa đặt tiêu đề)'; ?>
                                    </span>
                                </div>
                                <button type="button" class="button-link-delete tt-btn-remove-door" style="cursor:pointer;" title="Xóa cánh cửa này">Xóa</button>
                            </div>
                            <div class="tt-door-accordion-body">
                                <div class="tt-field-grid">
                                    <div class="tt-field-row">
                                        <label>Tiêu đề cánh cửa</label>
                                        <input type="text" name="doors_items[<?php echo $di; ?>][title]" class="widefat tt-door-title-input" placeholder="Nhập tiêu đề cánh cửa..." value="<?php echo esc_attr($d_title); ?>" />
                                    </div>
                                    <div class="tt-field-row">
                                        <label>Huy hiệu / Badge</label>
                                        <input type="text" name="doors_items[<?php echo $di; ?>][badge]" class="widefat" placeholder="VD: Khám phá tiềm năng..." value="<?php echo esc_attr($d_badge); ?>" />
                                    </div>
                                </div>
                                <div class="tt-field-row">
                                    <label>Mô tả</label>
                                    <textarea name="doors_items[<?php echo $di; ?>][desc]" class="widefat" rows="2" placeholder="Nhập mô tả ngắn..."><?php echo esc_textarea($d_desc); ?></textarea>
                                </div>
                                <div class="tt-field-row">
                                    <label>Các câu hỏi băn khoăn (mỗi dòng 1 câu hỏi)</label>
                                    <textarea name="doors_items[<?php echo $di; ?>][questions]" class="widefat" rows="4" placeholder="Con mạnh ở đâu?&#10;Con phù hợp với điều gì?..."><?php echo esc_textarea($d_questions); ?></textarea>
                                </div>
                                <div class="tt-field-grid">
                                    <div class="tt-field-row">
                                        <label>Điểm nhấn / Kết luận</label>
                                        <input type="text" name="doors_items[<?php echo $di; ?>][highlight]" class="widefat" placeholder="VD: Đừng chờ con gặp vấn đề mới bắt đầu hiểu con." value="<?php echo esc_attr($d_highlight); ?>" />
                                    </div>
                                    <div class="tt-field-row">
                                        <label>Nhu cầu truyền vào Form tư vấn (tùy chọn)</label>
                                        <input type="text" name="doors_items[<?php echo $di; ?>][prefill]" class="widefat" placeholder="Để trống sẽ tự lấy theo tiêu đề" value="<?php echo esc_attr($d_prefill); ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- TAB 4: GÓC NHÌN & QUAN HỆ -->
        <div id="tab-persp" class="tt-tab-pane">
            <?php
            $tab_persp_wrap = false;
            $tab_persp_heading = '1. Góc nhìn đa chiều (Perspectives)';
            include dirname(__DIR__) . '/metabox-perspectives.php';
            ?>

            <hr style="margin:24px 0;" />
            <h3 style="color:#0f3d61;">2. Mối quan hệ Ba mẹ & Con</h3>
            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Tiêu đề đầu</label>
                    <input type="text" name="landing_relationship_title_prefix" class="widefat" value="<?php echo esc_attr($rel_prefix); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Tiêu đề nổi bật</label>
                    <input type="text" name="landing_relationship_title_highlight" class="widefat" value="<?php echo esc_attr($rel_highlight); ?>" />
                </div>
            </div>
            <div class="tt-field-row">
                <label>Lời dẫn</label>
                <textarea name="landing_relationship_lead" class="widefat" rows="2"><?php echo esc_textarea($rel_lead); ?></textarea>
            </div>
            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Về phía Ba mẹ (mỗi dòng 1 ý)</label>
                    <textarea name="landing_relationship_parent_items" class="widefat" rows="4"><?php echo esc_textarea($rel_parent_items); ?></textarea>
                </div>
                <div class="tt-field-row">
                    <label>Về phía Con (mỗi dòng 1 ý)</label>
                    <textarea name="landing_relationship_child_items" class="widefat" rows="4"><?php echo esc_textarea($rel_child_items); ?></textarea>
                </div>
            </div>
            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Thông điệp đúc kết mối quan hệ</label>
                    <input type="text" name="landing_relationship_statement" class="widefat" value="<?php echo esc_attr($rel_statement); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Huy hiệu thông điệp</label>
                    <input type="text" name="landing_relationship_statement_badge" class="widefat" value="<?php echo esc_attr($rel_stmt_badge); ?>" />
                </div>
            </div>
        </div>

        <!-- TAB 5: PHƯƠNG PHÁP TIẾP CẬN (APPROACH) -->
        <div id="tab-approach" class="tt-tab-pane">
            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Tiêu đề đầu</label>
                    <input type="text" name="landing_approach_title_prefix" class="widefat" value="<?php echo esc_attr($approach_prefix); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Tiêu đề nổi bật</label>
                    <input type="text" name="landing_approach_title_highlight" class="widefat" value="<?php echo esc_attr($approach_highlight); ?>" />
                </div>
            </div>
            <div class="tt-field-row">
                <label>Lời dẫn giải thích phương pháp</label>
                <textarea name="landing_approach_desc" class="widefat" rows="3"><?php echo esc_textarea($approach_desc); ?></textarea>
            </div>
            <div class="tt-field-row">
                <label>Ghi chú / Lưu ý phương pháp</label>
                <textarea name="landing_approach_notice" class="widefat" rows="2"><?php echo esc_textarea($approach_notice); ?></textarea>
            </div>

            <div class="tt-card">
                <div class="tt-card-header">Ảnh Minh Họa & Khung Triết Lý</div>
                <div class="tt-image-picker-row" style="margin-bottom:12px;">
                    <div id="tt-approach-image-preview" class="tt-image-preview">
                        <?php if (! empty($approach_image)) : ?>
                            <img src="<?php echo esc_url($approach_image); ?>" />
                        <?php else : ?>
                            <span style="font-size:11px; color:#888;">Chưa chọn ảnh</span>
                        <?php endif; ?>
                    </div>
                    <div style="flex:1;">
                        <input type="text" id="tt-approach-image-input" name="landing_approach_image" class="widefat" value="<?php echo esc_attr($approach_image); ?>" placeholder="https://... hoặc bấm nút Chọn ảnh bên dưới" style="margin-bottom:8px;" />
                        <div style="display:flex; gap:8px; align-items:center;">
                            <button type="button" id="tt-approach-image-btn" class="button button-secondary">📷 Chọn ảnh từ Thư viện</button>
                            <button type="button" id="tt-approach-image-remove" class="button button-link-delete" style="<?php echo empty($approach_image) ? 'display:none;' : ''; ?>">Xóa ảnh</button>
                        </div>
                        <p class="description" style="margin-top:6px;">Chọn ảnh từ Thư viện WordPress Media hoặc dán URL trực tiếp. Nếu để trống, website sẽ sử dụng ảnh mặc định thương hiệu.</p>
                    </div>
                </div>
                <div class="tt-field-row">
                    <label>Văn bản thay thế ảnh (Alt text)</label>
                    <input type="text" name="landing_approach_image_alt" class="widefat" value="<?php echo esc_attr($approach_image_alt); ?>" />
                </div>
                <div class="tt-field-grid">
                    <div class="tt-field-row">
                        <label>Câu trích dẫn nổi bật trên ảnh (Quote)</label>
                        <input type="text" name="landing_approach_visual_quote" class="widefat" value="<?php echo esc_attr($approach_visual_quote); ?>" />
                    </div>
                    <div class="tt-field-row">
                        <label>Nhãn triết lý (Label)</label>
                        <input type="text" name="landing_approach_visual_label" class="widefat" value="<?php echo esc_attr($approach_visual_label); ?>" />
                    </div>
                </div>
            </div>

            <div class="tt-field-row" style="margin-top:16px;">
                <label>4 Điểm Cốt Lõi Của Phương Pháp Tiếp Cận (mỗi dòng 1 ý, dùng **từ in đậm** để in đậm)</label>
                <textarea name="landing_approach_points" class="widefat" rows="5" placeholder="**Hiểu mình** để hiểu hoàn cảnh."><?php echo esc_textarea($approach_points_str); ?></textarea>
                <p class="description" style="margin-top:4px;">Ví dụ: <code>**Hiểu mình** để hiểu hoàn cảnh.</code></p>
            </div>
        </div>

        <!-- TAB 6: GÓI DỊCH VỤ (DÙNG CHUNG) -->
        <?php
        $tab_pkg_id     = 'tab-pkg';
        $tab_pkg_active = false;
        $tab_pkg_wrap   = true;
        include dirname(__DIR__) . '/metabox-packages.php';
        ?>

        <!-- TAB 7: CHUYÊN GIA (DÙNG CHUNG) -->
        <?php
        $tab_expert_id     = 'tab-expert';
        $tab_expert_active = false;
        $tab_expert_wrap   = true;
        include dirname(__DIR__) . '/metabox-expert.php';
        ?>

        <!-- TAB 8: QUY TRÌNH & NIỀM TIN -->
        <div id="tab-proc" class="tt-tab-pane">
            <?php
            $tab_proc_wrap = false;
            $tab_proc_heading = '1. Quy Trình Đồng Hành';
            include dirname(__DIR__) . '/metabox-process.php';
            ?>

            <hr style="margin:24px 0;" />
            <h3 style="color:#0f3d61;">2. 4 Điểm Tựa Niềm Tin (Trust Items)</h3>
            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Tiêu đề đầu</label>
                    <input type="text" name="landing_trust_title_prefix" class="widefat" value="<?php echo esc_attr($trust_prefix); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Tiêu đề nổi bật</label>
                    <input type="text" name="landing_trust_title_highlight" class="widefat" value="<?php echo esc_attr($trust_highlight); ?>" />
                </div>
            </div>
            <div class="tt-field-row">
                <label>Lời dẫn</label>
                <textarea name="landing_trust_lead" class="widefat" rows="2"><?php echo esc_textarea($trust_lead); ?></textarea>
            </div>
            <div class="tt-field-row">
                <label>Danh sách 4 điểm tựa niềm tin (mỗi dòng 1 ý)</label>
                <textarea name="landing_trust_items" class="widefat" rows="4"><?php echo esc_textarea($trust_items); ?></textarea>
            </div>
        </div>

        <!-- TAB 9: FAQ (Dùng chung) -->
        <?php
        $tab_faq_id = 'tab-faq';
        $tab_faq_active = false;
        $faq_data_key = 'faq';
        include dirname(__DIR__) . '/metabox-faq.php';
        ?>

        <!-- TAB 10: KẾT & FORM -->
        <div id="tab-final" class="tt-tab-pane">
            <h3 style="margin-top:0; color:#0f3d61;">Phần Kêu Gọi Cuối Trang (Final CTA)</h3>
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
                <label>Lời dẫn cuối trang</label>
                <textarea name="landing_final_lead" class="widefat" rows="2"><?php echo esc_textarea($final_lead); ?></textarea>
            </div>
            <div class="tt-field-row">
                <label>Tuyên ngôn cốt lõi (In to viền khung)</label>
                <input type="text" name="landing_final_statement" class="widefat" value="<?php echo esc_attr($final_statement); ?>" />
            </div>
        </div>

        <!-- TAB 11: FORM ĐĂNG KÝ (DÙNG CHUNG) -->
        <?php
        $tab_reg_id      = 'tab-registration';
        $tab_reg_active  = false;
        $tab_reg_wrap    = true;
        $tab_reg_heading = '11. Cột giới thiệu Form Đăng ký tư vấn (Cột trái)';
        $reg_data_key    = 'form';
        include dirname(__DIR__) . '/metabox-registration.php';
        ?>
    </div>

    <script src="<?php echo esc_url(get_stylesheet_directory_uri() . '/js/lucide.min.js'); ?>"></script>
<script src="<?php echo esc_url(get_stylesheet_directory_uri() . '/inc/landing-pages/common/metabox.js?ver=' . (file_exists(dirname(__DIR__) . '/common/metabox.js') ? filemtime(dirname(__DIR__) . '/common/metabox.js') : '1.0')); ?>"></script>
<?php
