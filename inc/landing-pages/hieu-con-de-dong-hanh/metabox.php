<?php
/**
 * Metabox UI for 'Hiểu con để đồng hành'
 * Scope: $post, $slug, $landings
 */

if (! defined('ABSPATH')) {
    exit;
}

// Lấy dữ liệu mặc định từ file vi.json thông qua API mapper
$defaults = thientam_get_landing_default_data('hieu-con-de-dong-hanh');

// Hàm trợ giúp lấy post_meta nếu có, fallback về mảng defaults
$get_val = function($meta_key, $default_val = '') use ($post) {
    $val = get_post_meta($post->ID, $meta_key, true);
    return ($val !== '' && $val !== false) ? $val : $default_val;
};

// Chuẩn bị sẵn dữ liệu cho từng tab:
// SEO & Meta
$meta_title       = $get_val('landing_meta_title', $defaults['meta']['title'] ?? '');
$meta_description = $get_val('landing_meta_description', $defaults['meta']['description'] ?? '');
$meta_keywords    = $get_val('landing_meta_keywords', isset($defaults['meta']['keywords']) && is_array($defaults['meta']['keywords']) ? implode(', ', $defaults['meta']['keywords']) : '');
$nav_cta_label    = $get_val('landing_nav_cta_label', $defaults['navCta']['label'] ?? 'Đăng ký tư vấn');
$nav_cta_href     = $get_val('landing_nav_cta_href', $defaults['navCta']['href'] ?? '#dang-ky');

// Hero Banner
$hero_kicker      = $get_val('landing_hero_kicker', $defaults['hero']['kicker'] ?? '');
$hero_prefix      = $get_val('landing_hero_title_prefix', $defaults['hero']['titlePrefix'] ?? '');
$hero_highlight   = $get_val('landing_hero_title_highlight', $defaults['hero']['titleHighlight'] ?? '');
$hero_lead_hl     = $get_val('landing_hero_lead_highlight', $defaults['hero']['leadHighlight'] ?? '');
$hero_lead        = $get_val('landing_hero_lead', $defaults['hero']['lead'] ?? '');
$hero_cta_pri     = $get_val('landing_hero_cta_primary', $defaults['hero']['ctaPrimary'] ?? 'Đăng ký ngay');
$hero_cta_sec     = $get_val('landing_hero_cta_secondary', $defaults['hero']['ctaSecondary'] ?? 'Xem chi tiết các gói');
$hero_image       = $get_val('landing_hero_image', $defaults['hero']['image'] ?? '');
$hero_image_badge = $get_val('landing_hero_image_badge', $defaults['hero']['imageBadge'] ?? '');
$hero_image_alt   = $get_val('landing_hero_image_alt', $defaults['hero']['imageAlt'] ?? '');
$hero_quote_bold  = $get_val('landing_hero_quote_bold', $defaults['hero']['imageQuoteBold'] ?? '');
$hero_quote_sub   = $get_val('landing_hero_quote_sub', $defaults['hero']['imageQuoteSub'] ?? '');
$hero_chips       = isset($defaults['hero']['chips']) && is_array($defaults['hero']['chips']) ? implode("\
", $defaults['hero']['chips']) : '';

// Hai điểm bắt đầu (Doors)
$doors_eyebrow    = $get_val('landing_doors_eyebrow', $defaults['entryDoors']['eyebrow'] ?? '');
$doors_prefix     = $get_val('landing_doors_title_prefix', $defaults['entryDoors']['titlePrefix'] ?? '');
$doors_highlight  = $get_val('landing_doors_title_highlight', $defaults['entryDoors']['titleHighlight'] ?? '');
$doors_lead       = $get_val('landing_doors_lead', $defaults['entryDoors']['lead'] ?? '');
$door1            = $defaults['entryDoors']['door1'] ?? array();
$door2            = $defaults['entryDoors']['door2'] ?? array();

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
    $approach_points_list = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode("\n", str_replace("\r", "", $approach_points_raw))));
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
$approach_points_str = implode("\n", array_filter($approach_points_lines));

// Perspectives & Relationship
$persp_prefix     = $get_val('landing_perspectives_title_prefix', $defaults['perspectives']['titlePrefix'] ?? '');
$persp_highlight  = $get_val('landing_perspectives_title_highlight', $defaults['perspectives']['titleHighlight'] ?? '');
$persp_lead       = $get_val('landing_perspectives_lead', $defaults['perspectives']['lead'] ?? '');
$persp_cards      = $defaults['perspectives']['cards'] ?? array();

$rel_prefix       = $get_val('landing_relationship_title_prefix', $defaults['relationship']['titlePrefix'] ?? '');
$rel_highlight    = $get_val('landing_relationship_title_highlight', $defaults['relationship']['titleHighlight'] ?? '');
$rel_lead         = $get_val('landing_relationship_lead', $defaults['relationship']['lead'] ?? '');
$rel_statement    = $get_val('landing_relationship_statement', $defaults['relationship']['statement'] ?? '');
$rel_stmt_badge   = $get_val('landing_relationship_statement_badge', $defaults['relationship']['statementBadge'] ?? '');
$rel_parent_items = isset($defaults['relationship']['parent']['items']) && is_array($defaults['relationship']['parent']['items']) ? implode("\
", $defaults['relationship']['parent']['items']) : '';
$rel_child_items  = isset($defaults['relationship']['child']['items']) && is_array($defaults['relationship']['child']['items']) ? implode("\
", $defaults['relationship']['child']['items']) : '';

// Packages
$pkg_prefix       = $get_val('landing_packages_title_prefix', $defaults['packages']['titlePrefix'] ?? '');
$pkg_highlight    = $get_val('landing_packages_title_highlight', $defaults['packages']['titleHighlight'] ?? '');
$pkg_lead         = $get_val('landing_packages_lead', $defaults['packages']['lead'] ?? '');
$pkg_btn          = $get_val('landing_packages_btn', $defaults['packages']['btn'] ?? '');
$packages_items   = $defaults['packages']['items'] ?? array();

// Expert
$expert_avatar      = $get_val('landing_expert_avatar', $defaults['expert']['avatar'] ?? '');
$expert_name        = $get_val('landing_expert_name', $defaults['expert']['name'] ?? '');
$expert_badge       = $get_val('landing_expert_badge', $defaults['expert']['badge'] ?? '');
$expert_desc        = $get_val('landing_expert_desc', $defaults['expert']['desc'] ?? '');
$expert_desc1       = $get_val('landing_expert_desc1', $defaults['expert']['desc1'] ?? '');
$expert_desc2       = $get_val('landing_expert_desc2', $defaults['expert']['desc2'] ?? '');
$expert_notice      = $get_val('landing_expert_notice', $defaults['expert']['notice'] ?? '');
$expert_btn         = $get_val('landing_expert_btn', $defaults['expert']['btn'] ?? '');
$expert_pills       = isset($defaults['expert']['pills']) && is_array($defaults['expert']['pills']) ? implode("\
", $defaults['expert']['pills']) : '';
$expert_credentials = isset($defaults['expert']['credentials']) && is_array($defaults['expert']['credentials']) ? implode("\
", $defaults['expert']['credentials']) : '';

// Process & Trust
$proc_prefix      = $get_val('landing_process_title_prefix', $defaults['process']['titlePrefix'] ?? '');
$proc_highlight   = $get_val('landing_process_title_highlight', $defaults['process']['titleHighlight'] ?? '');
$proc_lead        = $get_val('landing_process_lead', $defaults['process']['lead'] ?? '');
$proc_notice      = $get_val('landing_process_notice', $defaults['process']['notice'] ?? '');
$proc_steps       = $defaults['process']['steps'] ?? array();

$trust_prefix     = $get_val('landing_trust_title_prefix', $defaults['trust']['titlePrefix'] ?? '');
$trust_highlight  = $get_val('landing_trust_title_highlight', $defaults['trust']['titleHighlight'] ?? '');
$trust_lead       = $get_val('landing_trust_lead', $defaults['trust']['lead'] ?? '');
$trust_items      = isset($defaults['trust']['items']) && is_array($defaults['trust']['items']) ? implode("\
", $defaults['trust']['items']) : '';

// FAQ
$faq_title        = $get_val('landing_faq_title', $defaults['faq']['title'] ?? '');
$faq_desc         = $get_val('landing_faq_desc', $defaults['faq']['desc'] ?? '');
$faq_note         = $get_val('landing_faq_note', $defaults['faq']['note'] ?? '');
$faq_items        = $defaults['faq']['items'] ?? array();

// Final CTA & Form
$final_prefix     = $get_val('landing_final_title_prefix', $defaults['finalCta']['titlePrefix'] ?? '');
$final_highlight  = $get_val('landing_final_title_highlight', $defaults['finalCta']['titleHighlight'] ?? '');
$final_lead       = $get_val('landing_final_lead', $defaults['finalCta']['lead'] ?? '');
$final_statement  = $get_val('landing_final_statement', $defaults['finalCta']['statement'] ?? '');
$form_needs       = isset($defaults['form']['needsOptions']) && is_array($defaults['form']['needsOptions']) ? implode("\
", $defaults['form']['needsOptions']) : '';
?>
    <link rel="stylesheet" href="<?php echo esc_url(get_stylesheet_directory_uri() . '/inc/landing-pages/hieu-con-de-dong-hanh/metabox.css?ver=' . (file_exists(__DIR__ . '/metabox.css') ? filemtime(__DIR__ . '/metabox.css') : '1.0')); ?>" />

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
        </div>

        <!-- TAB 1: SEO & HEADER -->
        <div id="tab-seo" class="tt-tab-pane active">
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
            <div class="tt-card">
                <div class="tt-card-header">Nút CTA trên Thanh điều hướng (Header)</div>
                <div class="tt-field-grid">
                    <div class="tt-field-row">
                        <label>Tiêu đề nút</label>
                        <input type="text" name="landing_nav_cta_label" class="widefat" value="<?php echo esc_attr($nav_cta_label); ?>" />
                    </div>
                    <div class="tt-field-row">
                        <label>Liên kết nút</label>
                        <input type="text" name="landing_nav_cta_href" class="widefat" value="<?php echo esc_attr($nav_cta_href); ?>" />
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: HERO BANNER -->
        <div id="tab-hero" class="tt-tab-pane">
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
                    <label>Nút CTA chính (Primary)</label>
                    <input type="text" name="landing_hero_cta_primary" class="widefat" value="<?php echo esc_attr($hero_cta_pri); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Nút CTA phụ (Secondary)</label>
                    <input type="text" name="landing_hero_cta_secondary" class="widefat" value="<?php echo esc_attr($hero_cta_sec); ?>" />
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

        <!-- TAB 3: HAI ĐIỂM BẮT ĐẦU -->
        <div id="tab-doors" class="tt-tab-pane">
            <div class="tt-field-row">
                <label>Eyebrow</label>
                <input type="text" name="landing_doors_eyebrow" class="widefat" value="<?php echo esc_attr($doors_eyebrow); ?>" />
            </div>
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

            <!-- Cửa 1 -->
            <div class="tt-card">
                <div class="tt-card-header">Cánh cửa 01: Khám phá con từ sớm</div>
                <div class="tt-field-grid">
                    <div class="tt-field-row">
                        <label>Tiêu đề cửa 1</label>
                        <input type="text" name="door1_title" class="widefat" value="<?php echo esc_attr($door1['title'] ?? ''); ?>" />
                    </div>
                    <div class="tt-field-row">
                        <label>Huy hiệu / Badge</label>
                        <input type="text" name="door1_badge" class="widefat" value="<?php echo esc_attr($door1['badge'] ?? ''); ?>" />
                    </div>
                </div>
                <div class="tt-field-row">
                    <label>Mô tả</label>
                    <textarea name="door1_desc" class="widefat" rows="2"><?php echo esc_textarea($door1['desc'] ?? ''); ?></textarea>
                </div>
                <div class="tt-field-row">
                    <label>Các câu hỏi băn khoăn (mỗi dòng 1 câu hỏi)</label>
                    <textarea name="door1_questions" class="widefat" rows="4"><?php echo esc_textarea(isset($door1['questions']) && is_array($door1['questions']) ? implode("\
", $door1['questions']) : ''); ?></textarea>
                </div>
                <div class="tt-field-row">
                    <label>Điểm nhấn / Kết luận</label>
                    <input type="text" name="door1_highlight" class="widefat" value="<?php echo esc_attr($door1['highlight'] ?? ''); ?>" />
                </div>
            </div>

            <!-- Cửa 2 -->
            <div class="tt-card">
                <div class="tt-card-header">Cánh cửa 02: Đang gặp khoảng cách / Khó giao tiếp</div>
                <div class="tt-field-grid">
                    <div class="tt-field-row">
                        <label>Tiêu đề cửa 2</label>
                        <input type="text" name="door2_title" class="widefat" value="<?php echo esc_attr($door2['title'] ?? ''); ?>" />
                    </div>
                    <div class="tt-field-row">
                        <label>Huy hiệu / Badge</label>
                        <input type="text" name="door2_badge" class="widefat" value="<?php echo esc_attr($door2['badge'] ?? ''); ?>" />
                    </div>
                </div>
                <div class="tt-field-row">
                    <label>Mô tả</label>
                    <textarea name="door2_desc" class="widefat" rows="2"><?php echo esc_textarea($door2['desc'] ?? ''); ?></textarea>
                </div>
                <div class="tt-field-row">
                    <label>Các câu hỏi băn khoăn (mỗi dòng 1 câu hỏi)</label>
                    <textarea name="door2_questions" class="widefat" rows="4"><?php echo esc_textarea(isset($door2['questions']) && is_array($door2['questions']) ? implode("\
", $door2['questions']) : ''); ?></textarea>
                </div>
                <div class="tt-field-row">
                    <label>Điểm nhấn / Kết luận</label>
                    <input type="text" name="door2_highlight" class="widefat" value="<?php echo esc_attr($door2['highlight'] ?? ''); ?>" />
                </div>
            </div>
        </div>

        <!-- TAB 4: GÓC NHÌN & QUAN HỆ -->
        <div id="tab-persp" class="tt-tab-pane">
            <h3 style="margin-top:0; color:#0f3d61;">1. Góc nhìn đa chiều (Perspectives)</h3>
            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Tiêu đề đầu</label>
                    <input type="text" name="landing_perspectives_title_prefix" class="widefat" value="<?php echo esc_attr($persp_prefix); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Tiêu đề nổi bật</label>
                    <input type="text" name="landing_perspectives_title_highlight" class="widefat" value="<?php echo esc_attr($persp_highlight); ?>" />
                </div>
            </div>
            <div class="tt-field-row">
                <label>Lời dẫn</label>
                <textarea name="landing_perspectives_lead" class="widefat" rows="2"><?php echo esc_textarea($persp_lead); ?></textarea>
            </div>

            <div class="tt-card" style="background:#f8fafc; border:1px solid #cbd5e1;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <div>
                        <strong style="color:#0f3d61; font-size:14px;">Danh sách thẻ Góc nhìn</strong>
                        <p style="margin:2px 0 0 0; font-size:12px; color:#64748b;">Mỗi thẻ có biểu tượng Lucide riêng, tiêu đề và mô tả ngắn</p>
                    </div>
                    <button type="button" id="tt-btn-add-persp-card" class="button button-primary" style="background:#0f3d61; border-color:#0f3d61; height:34px; padding:0 14px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
                        + Thêm thẻ góc nhìn
                    </button>
                </div>

                <?php
                $persp_cards_list = !empty($persp_cards) && is_array($persp_cards) ? array_values($persp_cards) : array(array('title' => '', 'desc' => '', 'icon' => 'sun'));
                ?>
                <div id="tt-persp-cards-container" class="tt-persp-grid">
                    <?php foreach ($persp_cards_list as $ci => $cd) : 
                        $cur_icon = !empty($cd['icon']) ? $cd['icon'] : 'sun';
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
                                            <i data-lucide="<?php echo esc_attr($cur_icon); ?>"></i>
                                        </span>
                                        <span class="tt-persp-icon-name"><?php echo esc_html($cur_icon); ?></span>
                                    </button>
                                    <span class="tt-persp-change-link tt-btn-pick-lucide">Đổi icon ▾</span>
                                    <input type="hidden" name="persp_cards[<?php echo $ci; ?>][icon]" class="tt-persp-icon-input" value="<?php echo esc_attr($cur_icon); ?>" />
                                </div>

                                <!-- Inputs Column -->
                                <div class="tt-persp-fields-col">
                                    <div class="tt-persp-field-group">
                                        <label class="tt-field-label">Tiêu đề thẻ</label>
                                        <input type="text" name="persp_cards[<?php echo $ci; ?>][title]" class="widefat tt-input-title" value="<?php echo esc_attr($cd['title'] ?? ''); ?>" placeholder="vd: Tính cách & khí chất" />
                                    </div>
                                    <div class="tt-persp-field-group">
                                        <label class="tt-field-label">Mô tả chi tiết</label>
                                        <textarea name="persp_cards[<?php echo $ci; ?>][desc]" class="widefat tt-textarea-desc" rows="2" placeholder="Nhập mô tả góc nhìn..."><?php echo esc_textarea($cd['desc'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

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

        <!-- TAB 6: GÓI DỊCH VỤ -->
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
                <label>Mô tả phần gói</label>
                <textarea name="landing_packages_lead" class="widefat" rows="2"><?php echo esc_textarea($pkg_lead); ?></textarea>
            </div>
            <div class="tt-field-row">
                <label>Text nút bấm dùng chung</label>
                <input type="text" name="landing_packages_btn" class="widefat" value="<?php echo esc_attr($pkg_btn); ?>" />
            </div>

            <h3 style="color:#0f3d61; margin-top:24px;">Chi tiết 3 Gói Dịch Vụ</h3>
            <?php for ($pi = 0; $pi < 3; $pi++) : 
                $pitem = $packages_items[$pi] ?? array();
            ?>
                <div class="tt-card">
                    <div class="tt-card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <span>Gói 0<?php echo $pi + 1; ?>: <?php echo esc_html($pitem['title'] ?? 'Gói dịch vụ'); ?></span>
                        <label style="font-size:12px; font-weight:normal; margin:0;">
                            <input type="checkbox" name="pkg[<?php echo $pi; ?>][isFeatured]" value="1" <?php checked(! empty($pitem['isFeatured'])); ?> /> Gói nổi bật
                        </label>
                    </div>
                    <div class="tt-field-grid-3">
                        <div class="tt-field-row">
                            <label>Tên gói</label>
                            <input type="text" name="pkg[<?php echo $pi; ?>][title]" class="widefat" value="<?php echo esc_attr($pitem['title'] ?? ''); ?>" />
                        </div>
                        <div class="tt-field-row">
                            <label>Mã gói (ID)</label>
                            <input type="text" name="pkg[<?php echo $pi; ?>][id]" class="widefat" value="<?php echo esc_attr($pitem['id'] ?? ''); ?>" />
                        </div>
                        <div class="tt-field-row">
                            <label>Huy hiệu (Badge)</label>
                            <input type="text" name="pkg[<?php echo $pi; ?>][badge]" class="widefat" value="<?php echo esc_attr($pitem['badge'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="tt-field-grid">
                        <div class="tt-field-row">
                            <label>Giá bán hiện tại (VD: 2.500.000đ)</label>
                            <input type="text" name="pkg[<?php echo $pi; ?>][price]" class="widefat" value="<?php echo esc_attr($pitem['price'] ?? ''); ?>" />
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
                        <textarea name="pkg[<?php echo $pi; ?>][features]" class="widefat" rows="4"><?php echo esc_textarea(isset($pitem['features']) && is_array($pitem['features']) ? implode("\
", $pitem['features']) : ''); ?></textarea>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <!-- TAB 7: CHUYÊN GIA -->
        <div id="tab-expert" class="tt-tab-pane">
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
                    <label>Tên chuyên gia</label>
                    <input type="text" name="landing_expert_name" class="widefat" value="<?php echo esc_attr($expert_name); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Danh hiệu / Huy hiệu</label>
                    <input type="text" name="landing_expert_badge" class="widefat" value="<?php echo esc_attr($expert_badge); ?>" />
                </div>
            </div>
            <div class="tt-field-row">
                <label>Lời dẫn mở đầu</label>
                <textarea name="landing_expert_desc" class="widefat" rows="2"><?php echo esc_textarea($expert_desc); ?></textarea>
            </div>
            <div class="tt-field-row">
                <label>Đoạn giải thích 1</label>
                <textarea name="landing_expert_desc1" class="widefat" rows="3"><?php echo esc_textarea($expert_desc1); ?></textarea>
            </div>
            <div class="tt-field-row">
                <label>Đoạn giải thích 2</label>
                <textarea name="landing_expert_desc2" class="widefat" rows="3"><?php echo esc_textarea($expert_desc2); ?></textarea>
            </div>
            <div class="tt-field-row">
                <label>Ghi chú nhấn mạnh của chuyên gia</label>
                <input type="text" name="landing_expert_notice" class="widefat" value="<?php echo esc_attr($expert_notice); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Text nút liên hệ chuyên gia</label>
                <input type="text" name="landing_expert_btn" class="widefat" value="<?php echo esc_attr($expert_btn); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Tags nổi bật (Pills - mỗi dòng 1 tag)</label>
                <textarea name="landing_expert_pills" class="widefat" rows="3"><?php echo esc_textarea($expert_pills); ?></textarea>
            </div>
        </div>

        <!-- TAB 8: QUY TRÌNH & NIỀM TIN -->
        <div id="tab-proc" class="tt-tab-pane">
            <h3 style="margin-top:0; color:#0f3d61;">1. Quy Trình Đồng Hành</h3>
            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Tiêu đề đầu</label>
                    <input type="text" name="landing_process_title_prefix" class="widefat" value="<?php echo esc_attr($proc_prefix); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Tiêu đề nổi bật</label>
                    <input type="text" name="landing_process_title_highlight" class="widefat" value="<?php echo esc_attr($proc_highlight); ?>" />
                </div>
            </div>
            <div class="tt-field-row">
                <label>Lời dẫn quy trình</label>
                <textarea name="landing_process_lead" class="widefat" rows="2"><?php echo esc_textarea($proc_lead); ?></textarea>
            </div>
            <div class="tt-field-row">
                <label>Ghi chú chân quy trình</label>
                <input type="text" name="landing_process_notice" class="widefat" value="<?php echo esc_attr($proc_notice); ?>" />
            </div>

            <div class="tt-card">
                <div class="tt-card-header">4 Bước Quy Trình</div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <?php for ($si = 0; $si < 4; $si++) : 
                        $st = $proc_steps[$si] ?? array('num' => '0' . ($si + 1), 'title' => '', 'desc' => '');
                    ?>
                        <div class="tt-item-box">
                            <strong>Bước <?php echo esc_html($st['num'] ?? ('0' . ($si + 1))); ?>:</strong>
                            <div style="margin-top:6px;">
                                <label style="font-size:11px;">Tiêu đề bước</label>
                                <input type="text" name="process_steps[<?php echo $si; ?>][title]" class="widefat" value="<?php echo esc_attr($st['title'] ?? ''); ?>" />
                            </div>
                            <div style="margin-top:6px;">
                                <label style="font-size:11px;">Mô tả bước</label>
                                <textarea name="process_steps[<?php echo $si; ?>][desc]" class="widefat" rows="2"><?php echo esc_textarea($st['desc'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

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

        <!-- TAB 9: FAQ -->
        <div id="tab-faq" class="tt-tab-pane">
            <div class="tt-field-row">
                <label>Tiêu đề FAQ</label>
                <input type="text" name="landing_faq_title" class="widefat" value="<?php echo esc_attr($faq_title); ?>" />
            </div>
            <div class="tt-field-row">
                <label>Mô tả FAQ</label>
                <textarea name="landing_faq_desc" class="widefat" rows="2"><?php echo esc_textarea($faq_desc); ?></textarea>
            </div>
            <div class="tt-field-row">
                <label>Ghi chú chân FAQ</label>
                <input type="text" name="landing_faq_note" class="widefat" value="<?php echo esc_attr($faq_note); ?>" />
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
        </div>

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
    </div>

    <!-- MODAL LUCIDE ICON PICKER -->
    <div id="tt-lucide-modal" class="tt-lucide-modal-overlay">
        <div class="tt-lucide-modal-box">
            <!-- Header -->
            <div style="padding:14px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
                <div>
                    <h3 style="margin:0; font-size:15px; color:#0f3d61; font-weight:700; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="sparkles" style="width:18px; height:18px; color:#d97706;"></i> Chọn Biểu Tượng Lucide
                    </h3>
                    <p style="margin:2px 0 0 0; font-size:11px; color:#64748b;">Chọn từ danh sách biểu tượng phổ biến hoặc tìm kiếm tên tiếng Anh</p>
                </div>
                <button type="button" id="tt-lucide-close" style="background:none; border:none; font-size:24px; cursor:pointer; color:#64748b; line-height:1; padding:0 4px;">&times;</button>
            </div>

            <!-- Toolbar & Search -->
            <div style="padding:12px 20px; border-bottom:1px solid #e2e8f0; background:#ffffff;">
                <div style="display:flex; gap:8px; margin-bottom:10px;">
                    <input type="text" id="tt-lucide-search" placeholder="Tìm kiếm icon (vd: sun, heart, book, star, target, shield...)" style="flex:1; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;" />
                    <button type="button" id="tt-lucide-btn-apply-custom" class="button" style="white-space:nowrap; height:34px;">Dùng tên vừa gõ</button>
                </div>
                <div id="tt-lucide-filter-tags" style="display:flex; flex-wrap:wrap; gap:6px;">
                    <button type="button" class="tt-lucide-tag-btn active" data-cat="all">Tất cả</button>
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
                <button type="button" class="button" id="tt-lucide-cancel">Đóng</button>
            </div>
        </div>
    </div>

    <script>
    if (typeof lucide === 'undefined') {
        document.write('<script src="<?php echo esc_url(get_stylesheet_directory_uri() . '/js/lucide.min.js'); ?>"><\/script>');
    }
</script>
<script src="<?php echo esc_url(get_stylesheet_directory_uri() . '/inc/landing-pages/hieu-con-de-dong-hanh/metabox.js?ver=' . (file_exists(__DIR__ . '/metabox.js') ? filemtime(__DIR__ . '/metabox.js') : '1.0')); ?>"></script>
<?php
