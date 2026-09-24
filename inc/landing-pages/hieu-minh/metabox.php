<?php
/**
 * Metabox UI for 'Hiểu Mình'
 * Scope: $post, $slug, $landings
 */

if (! defined('ABSPATH')) {
    exit;
}

// 1. Ưu tiên lấy dữ liệu trực tiếp từ bài viết hiện tại ($post->ID)
$custom_json_raw = get_post_meta($post->ID, 'landing_custom_json', true);
$defaults = array();
if (! empty($custom_json_raw)) {
    $parsed = json_decode($custom_json_raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
        $defaults = $parsed;
    }
}
if (empty($defaults) && function_exists('thientam_get_landing_default_data')) {
    $defaults = thientam_get_landing_default_data('hieu-minh');
}

if (function_exists('thientam_landing_default_seed_hieu_minh')) {
    $seed_data = thientam_landing_default_seed_hieu_minh();
    if (! empty($seed_data) && is_array($seed_data)) {
        $defaults = array_replace_recursive($seed_data, $defaults);
    }
}

$get_val = function($meta_key, $default_val = '') use ($post) {
    if (metadata_exists('post', $post->ID, $meta_key)) {
        return get_post_meta($post->ID, $meta_key, true);
    }
    return $default_val;
};

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
?>

    <link rel="stylesheet" href="<?php echo esc_url(get_stylesheet_directory_uri() . '/inc/landing-pages/common/metabox.css?ver=' . (file_exists(dirname(__DIR__) . '/common/metabox.css') ? filemtime(dirname(__DIR__) . '/common/metabox.css') : time())); ?>" />

    <div class="tt-tabs-wrapper">
        <div class="tt-tabs-nav">
            <button type="button" class="tt-tab-btn active" data-tab="tab-seo">01. Cấu hình SEO & Header</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-hero">02. Hero Banner</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-persp">03. Góc nhìn (Perspectives)</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-approach">04. Cách tiếp cận / Triết lý (Approach)</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-service-values">05. Không chỉ là thông tin</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-pkg">06. Bảng giá dịch vụ</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-package-guide">07. Chưa biết chọn gói nào?</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-why-thien-tam">08. Vì sao là Thiên Tâm?</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-expert">09. Chuyên gia đồng hành</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-comparison">10. So sánh nhanh</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-process">11. Quy trình luận giải</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-consultation-values">12. Giá trị sau buổi tư vấn</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-faq">13. FAQ (Hỏi đáp)</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-registration">14. Form đăng ký</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-feedback">15. Hình ảnh phản hồi (Feedback)</button>
        </div>

        <!-- TAB 1: SEO & HEADER (DÙNG CHUNG) -->
        <?php
        $tab_seo_id = 'tab-seo';
        $tab_seo_active = true;
        include dirname(__DIR__) . '/metabox-seo.php';
        ?>

        <!-- TAB 2: HERO BANNER (DÙNG CHUNG) -->
        <?php
        $tab_hero_id = 'tab-hero';
        $tab_hero_active = false;
        include dirname(__DIR__) . '/metabox-hero.php';
        ?>

        <!-- TAB 3: GÓC NHÌN (PERSPECTIVES - DÙNG CHUNG) -->
        <?php
        $tab_persp_id          = 'tab-persp';
        $tab_persp_active      = false;
        $tab_persp_wrap        = true;
        $tab_persp_heading     = '03. Góc nhìn (Perspectives)';
        $persp_data_key        = 'perspectives';
        $persp_prefix_name     = 'landing_perspectives_title_prefix';
        $persp_highlight_name  = 'landing_perspectives_title_highlight';
        $persp_lead_name       = 'landing_perspectives_lead';
        $persp_cards_field     = 'persp_cards';
        include dirname(__DIR__) . '/metabox-perspectives.php';
        ?>

        <!-- TAB 4: CÁCH TIẾP CẬN / TRIẾT LÝ (APPROACH - DÙNG CHUNG) -->
        <?php
        $tab_approach_id      = 'tab-approach';
        $tab_approach_active  = false;
        $tab_approach_wrap    = true;
        $tab_approach_heading = '04. Cách tiếp cận / Triết lý (Approach)';
        include dirname(__DIR__) . '/metabox-approach.php';
        ?>

        <!-- TAB 5: KHÔNG CHỈ LÀ THÔNG TIN (DÙNG CHUNG METABOX-PERSPECTIVES) -->
        <?php
        $tab_persp_id          = 'tab-service-values';
        $tab_persp_active      = false;
        $tab_persp_wrap        = true;
        $tab_persp_heading     = '05. Không chỉ là thông tin (Mức độ thấu hiểu)';
        $persp_data_key        = 'serviceValues';
        $persp_prefix_name     = 'landing_service_values_title_prefix';
        $persp_highlight_name  = 'landing_service_values_title_highlight';
        $persp_lead_name       = 'landing_service_values_lead';
        $persp_cards_field     = 'landing_service_values_cards';
        include dirname(__DIR__) . '/metabox-perspectives.php';
        ?>

        <!-- TAB 6: BẢNG GIÁ DỊCH VỤ (PACKAGES - DÙNG CHUNG) -->
        <?php
        $tab_pkg_id     = 'tab-pkg';
        $tab_pkg_active = false;
        $tab_pkg_wrap   = true;
        include dirname(__DIR__) . '/metabox-packages.php';
        ?>

        <!-- TAB 7: CHƯA BIẾT CHỌN GÓI NÀO? (DÙNG CHUNG METABOX-PERSPECTIVES) -->
        <?php
        $tab_persp_id          = 'tab-package-guide';
        $tab_persp_active      = false;
        $tab_persp_wrap        = true;
        $tab_persp_heading     = '07. Chưa biết chọn gói nào? (Hướng dẫn chọn gói)';
        $persp_data_key        = 'packageGuide';
        $persp_prefix_name     = 'landing_package_guide_title_prefix';
        $persp_highlight_name  = 'landing_package_guide_title_highlight';
        $persp_lead_name       = 'landing_package_guide_lead';
        $persp_cards_field     = 'landing_package_guide_cards';
        include dirname(__DIR__) . '/metabox-perspectives.php';
        ?>

        <!-- TAB 8: VÌ SAO LÀ THIÊN TÂM? (DÙNG CHUNG METABOX-PERSPECTIVES) -->
        <?php
        $tab_persp_id          = 'tab-why-thien-tam';
        $tab_persp_active      = false;
        $tab_persp_wrap        = true;
        $tab_persp_heading     = '08. Vì sao là Thiên Tâm? (Điểm khác biệt & Niềm tin)';
        $persp_data_key        = 'whyThienTam';
        $persp_prefix_name     = 'landing_why_thien_tam_title_prefix';
        $persp_highlight_name  = 'landing_why_thien_tam_title_highlight';
        $persp_lead_name       = 'landing_why_thien_tam_lead';
        $persp_cards_field     = 'landing_why_thien_tam_cards';
        include dirname(__DIR__) . '/metabox-perspectives.php';
        ?>

        <!-- TAB 9: CHUYÊN GIA ĐỒNG HÀNH (DÙNG CHUNG) -->
        <?php
        $tab_expert_id      = 'tab-expert';
        $tab_expert_active  = false;
        $tab_expert_wrap    = true;
        $tab_expert_heading = '09. Chuyên gia đồng hành (Profile & Tham vấn trực tiếp)';
        include dirname(__DIR__) . '/metabox-expert.php';
        ?>

        <!-- TAB 10: SO SÁNH NHANH (DÙNG CHUNG) -->
        <?php
        $tab_comp_id      = 'tab-comparison';
        $tab_comp_active  = false;
        $tab_comp_wrap    = true;
        $tab_comp_heading = '10. So sánh nhanh (Bảng so sánh 4 gói)';
        include dirname(__DIR__) . '/metabox-comparison.php';
        ?>

        <!-- TAB 11: QUY TRÌNH LUẬN GIẢI (PROCESS - DÙNG CHUNG) -->
        <?php
        $tab_proc_id      = 'tab-process';
        $tab_proc_active  = false;
        $tab_proc_wrap    = true;
        $tab_proc_heading = '11. Quy trình luận giải (Các bước làm việc)';
        include dirname(__DIR__) . '/metabox-process.php';
        ?>

        <!-- TAB 12: GIÁ TRỊ SAU BUỔI TƯ VẤN (DÙNG CHUNG METABOX-PERSPECTIVES) -->
        <?php
        $tab_persp_id          = 'tab-consultation-values';
        $tab_persp_active      = false;
        $tab_persp_wrap        = true;
        $tab_persp_heading     = '12. Giá trị sau buổi tư vấn (Tham vấn & Khai mở)';
        $persp_data_key        = 'consultationValues';
        $persp_prefix_name     = 'landing_consultation_values_title_prefix';
        $persp_highlight_name  = 'landing_consultation_values_title_highlight';
        $persp_lead_name       = 'landing_consultation_values_lead';
        $persp_cards_field     = 'landing_consultation_values_cards';
        include dirname(__DIR__) . '/metabox-perspectives.php';
        ?>

        <!-- TAB 13: FAQ (HỎI ĐÁP THƯỜNG GẶP - DÙNG CHUNG) -->
        <?php
        $tab_faq_id      = 'tab-faq';
        $tab_faq_active  = false;
        $tab_faq_wrap    = true;
        $tab_faq_heading = '13. Câu hỏi thường gặp (FAQ)';
        $faq_data_key    = 'faq';
        include dirname(__DIR__) . '/metabox-faq.php';
        ?>

        <!-- TAB 14: FORM ĐĂNG KÝ (DÙNG CHUNG) -->
        <?php
        $tab_reg_id      = 'tab-registration';
        $tab_reg_active  = false;
        $tab_reg_wrap    = true;
        $tab_reg_heading = '14. Cột giới thiệu Form Đăng ký tư vấn (Cột trái)';
        $reg_data_key    = 'form';
        include dirname(__DIR__) . '/metabox-registration.php';
        ?>

        <!-- TAB 15: HÌNH ẢNH PHẢN HỒI (FEEDBACK) -->
        <?php
        $feedback_prefix    = $get_val('landing_feedback_title_prefix', $defaults['feedback']['titlePrefix'] ?? '');
        $feedback_highlight = $get_val('landing_feedback_title_highlight', $defaults['feedback']['titleHighlight'] ?? '');
        $feedback_desc      = $get_val('landing_feedback_desc', $defaults['feedback']['desc'] ?? '');
        $feedback_items     = $get_json_val('landing_feedback_items', $defaults['feedback']['items'] ?? array());
        ?>
        <div id="tab-feedback" class="tt-tab-pane">
            <h3 style="margin-top:0; color:#0f3d61;">15. Hình ảnh phản hồi khách hàng (Feedback)</h3>
            <p class="description" style="margin-bottom:16px;">Phần hiển thị carousel hình ảnh feedback, tin nhắn cảm nhận ở cuối trang landing page.</p>

            <div class="tt-field-grid">
                <div class="tt-field-row">
                    <label>Tiêu đề đầu (Prefix)</label>
                    <input type="text" name="landing_feedback_title_prefix" class="widefat" placeholder="VD: Lắng nghe từ" value="<?php echo esc_attr($feedback_prefix); ?>" />
                </div>
                <div class="tt-field-row">
                    <label>Tiêu đề nổi bật (Highlight)</label>
                    <input type="text" name="landing_feedback_title_highlight" class="widefat" placeholder="VD: khách hàng thực tế" value="<?php echo esc_attr($feedback_highlight); ?>" />
                </div>
            </div>
            <div class="tt-field-row">
                <label>Mô tả ngắn</label>
                <textarea name="landing_feedback_desc" class="widefat" rows="2" placeholder="VD: Những chia sẻ, cảm nhận chân thực sau buổi tham vấn..."><?php echo esc_textarea($feedback_desc); ?></textarea>
            </div>

            <div class="tt-card">
                <div class="tt-card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <span>Danh sách hình ảnh phản hồi</span>
                    <button type="button" id="tt-btn-add-feedback-img" class="button button-secondary">+ Thêm hình ảnh</button>
                </div>
                <div id="tt-feedback-items-container" style="padding:15px; display:flex; flex-direction:column; gap:15px;">
                    <?php if (empty($feedback_items)) : ?>
                        <div class="tt-feedback-empty-notice" style="text-align:center; padding:20px; color:#888; border:1px dashed #ccd0d4; border-radius:8px;">
                            Chưa có hình ảnh phản hồi nào. Bấm <strong>"+ Thêm hình ảnh"</strong> để tải ảnh chụp màn hình feedback.
                        </div>
                    <?php else : ?>
                        <?php foreach ($feedback_items as $fbi => $fb_item) :
                            $fb_img = is_array($fb_item) ? ($fb_item['image'] ?? '') : (string) $fb_item;
                            $fb_alt = is_array($fb_item) ? ($fb_item['alt'] ?? '') : '';
                            $fb_title = is_array($fb_item) ? ($fb_item['title'] ?? '') : '';
                        ?>
                            <div class="tt-feedback-item-row" style="display:flex; gap:15px; align-items:flex-start; padding:12px; background:#f9f9f9; border:1px solid #e2e8f0; border-radius:8px;">
                                <div class="tt-feedback-img-preview" style="width:90px; height:90px; border-radius:6px; overflow:hidden; border:1px solid #cbd5e1; background:#fff; display:flex; align-items:center; justify-content:center; shrink:0;">
                                    <?php if (! empty($fb_img)) : ?>
                                        <img src="<?php echo esc_url($fb_img); ?>" style="width:100%; height:100%; object-fit:cover;" />
                                    <?php else : ?>
                                        <span style="font-size:11px; color:#94a3b8;">Chưa có ảnh</span>
                                    <?php endif; ?>
                                </div>
                                <div style="flex:1; display:flex; flex-direction:column; gap:8px;">
                                    <div style="display:flex; gap:8px;">
                                        <input type="text" name="feedback_items[<?php echo $fbi; ?>][image]" class="widefat tt-feedback-img-input" value="<?php echo esc_attr($fb_img); ?>" placeholder="URL hình ảnh (https://...)" />
                                        <button type="button" class="button button-secondary tt-btn-upload-feedback-img">📷 Chọn ảnh</button>
                                        <button type="button" class="button button-link-delete tt-btn-remove-feedback-img" style="color:#d63638;">Xóa</button>
                                    </div>
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                        <input type="text" name="feedback_items[<?php echo $fbi; ?>][title]" class="widefat" value="<?php echo esc_attr($fb_title); ?>" placeholder="Tiêu đề / Tên khách hàng (tùy chọn)" />
                                        <input type="text" name="feedback_items[<?php echo $fbi; ?>][alt]" class="widefat" value="<?php echo esc_attr($fb_alt); ?>" placeholder="Ghi chú ảnh / Alt text" />
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo esc_url(get_stylesheet_directory_uri() . '/js/lucide.min.js'); ?>"></script>
    <script src="<?php echo esc_url(get_stylesheet_directory_uri() . '/inc/landing-pages/common/metabox.js?ver=' . (file_exists(dirname(__DIR__) . '/common/metabox.js') ? filemtime(dirname(__DIR__) . '/common/metabox.js') : '1.0')); ?>"></script>
