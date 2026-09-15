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
            <button type="button" class="tt-tab-btn" data-tab="tab-service-values">04. Không chỉ là thông tin</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-pkg">05. Bảng giá dịch vụ</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-package-guide">06. Chưa biết chọn gói nào?</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-why-thien-tam">07. Vì sao là Thiên Tâm?</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-expert">08. Chuyên gia đồng hành</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-comparison">09. So sánh nhanh</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-process">10. Quy trình luận giải</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-consultation-values">11. Giá trị sau buổi tư vấn</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-faq">12. FAQ (Hỏi đáp)</button>
            <button type="button" class="tt-tab-btn" data-tab="tab-registration">13. Form đăng ký</button>
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
        $tab_persp_heading     = '';
        $persp_data_key        = 'perspectives';
        $persp_prefix_name     = 'landing_perspectives_title_prefix';
        $persp_highlight_name  = 'landing_perspectives_title_highlight';
        $persp_lead_name       = 'landing_perspectives_lead';
        $persp_cards_field     = 'persp_cards';
        include dirname(__DIR__) . '/metabox-perspectives.php';
        ?>

        <!-- TAB 4: KHÔNG CHỈ LÀ THÔNG TIN (DÙNG CHUNG METABOX-PERSPECTIVES) -->
        <?php
        $tab_persp_id          = 'tab-service-values';
        $tab_persp_active      = false;
        $tab_persp_wrap        = true;
        $tab_persp_heading     = '04. Không chỉ là thông tin (Mức độ thấu hiểu)';
        $persp_data_key        = 'serviceValues';
        $persp_prefix_name     = 'landing_service_values_title_prefix';
        $persp_highlight_name  = 'landing_service_values_title_highlight';
        $persp_lead_name       = 'landing_service_values_lead';
        $persp_cards_field     = 'landing_service_values_cards';
        include dirname(__DIR__) . '/metabox-perspectives.php';
        ?>

        <!-- TAB 5: BẢNG GIÁ DỊCH VỤ (PACKAGES - DÙNG CHUNG) -->
        <?php
        $tab_pkg_id     = 'tab-pkg';
        $tab_pkg_active = false;
        $tab_pkg_wrap   = true;
        include dirname(__DIR__) . '/metabox-packages.php';
        ?>

        <!-- TAB 6: CHƯA BIẾT CHỌN GÓI NÀO? (DÙNG CHUNG METABOX-PERSPECTIVES) -->
        <?php
        $tab_persp_id          = 'tab-package-guide';
        $tab_persp_active      = false;
        $tab_persp_wrap        = true;
        $tab_persp_heading     = '06. Chưa biết chọn gói nào? (Hướng dẫn chọn gói)';
        $persp_data_key        = 'packageGuide';
        $persp_prefix_name     = 'landing_package_guide_title_prefix';
        $persp_highlight_name  = 'landing_package_guide_title_highlight';
        $persp_lead_name       = 'landing_package_guide_lead';
        $persp_cards_field     = 'landing_package_guide_cards';
        include dirname(__DIR__) . '/metabox-perspectives.php';
        ?>

        <!-- TAB 7: VÌ SAO LÀ THIÊN TÂM? (DÙNG CHUNG METABOX-PERSPECTIVES) -->
        <?php
        $tab_persp_id          = 'tab-why-thien-tam';
        $tab_persp_active      = false;
        $tab_persp_wrap        = true;
        $tab_persp_heading     = '07. Vì sao là Thiên Tâm? (Điểm khác biệt & Niềm tin)';
        $persp_data_key        = 'whyThienTam';
        $persp_prefix_name     = 'landing_why_thien_tam_title_prefix';
        $persp_highlight_name  = 'landing_why_thien_tam_title_highlight';
        $persp_lead_name       = 'landing_why_thien_tam_lead';
        $persp_cards_field     = 'landing_why_thien_tam_cards';
        include dirname(__DIR__) . '/metabox-perspectives.php';
        ?>

        <!-- TAB 8: CHUYÊN GIA ĐỒNG HÀNH (DÙNG CHUNG) -->
        <?php
        $tab_expert_id      = 'tab-expert';
        $tab_expert_active  = false;
        $tab_expert_wrap    = true;
        $tab_expert_heading = '08. Chuyên gia đồng hành (Profile & Tham vấn trực tiếp)';
        include dirname(__DIR__) . '/metabox-expert.php';
        ?>

        <!-- TAB 9: SO SÁNH NHANH (DÙNG CHUNG) -->
        <?php
        $tab_comp_id      = 'tab-comparison';
        $tab_comp_active  = false;
        $tab_comp_wrap    = true;
        $tab_comp_heading = '09. So sánh nhanh (Bảng so sánh 4 gói)';
        include dirname(__DIR__) . '/metabox-comparison.php';
        ?>

        <!-- TAB 10: QUY TRÌNH LUẬN GIẢI (PROCESS - DÙNG CHUNG) -->
        <?php
        $tab_proc_id      = 'tab-process';
        $tab_proc_active  = false;
        $tab_proc_wrap    = true;
        $tab_proc_heading = '10. Quy trình luận giải (Các bước làm việc)';
        include dirname(__DIR__) . '/metabox-process.php';
        ?>

        <!-- TAB 11: GIÁ TRỊ SAU BUỔI TƯ VẤN (DÙNG CHUNG METABOX-PERSPECTIVES) -->
        <?php
        $tab_persp_id          = 'tab-consultation-values';
        $tab_persp_active      = false;
        $tab_persp_wrap        = true;
        $tab_persp_heading     = '11. Giá trị sau buổi tư vấn (Tham vấn & Khai mở)';
        $persp_data_key        = 'consultationValues';
        $persp_prefix_name     = 'landing_consultation_values_title_prefix';
        $persp_highlight_name  = 'landing_consultation_values_title_highlight';
        $persp_lead_name       = 'landing_consultation_values_lead';
        $persp_cards_field     = 'landing_consultation_values_cards';
        include dirname(__DIR__) . '/metabox-perspectives.php';
        ?>

        <!-- TAB 12: FAQ (HỎI ĐÁP THƯỜNG GẶP - DÙNG CHUNG) -->
        <?php
        $tab_faq_id      = 'tab-faq';
        $tab_faq_active  = false;
        $tab_faq_wrap    = true;
        $tab_faq_heading = '12. Câu hỏi thường gặp (FAQ)';
        $faq_data_key    = 'faq';
        include dirname(__DIR__) . '/metabox-faq.php';
        ?>

        <!-- TAB 13: FORM ĐĂNG KÝ (DÙNG CHUNG) -->
        <?php
        $tab_reg_id      = 'tab-registration';
        $tab_reg_active  = false;
        $tab_reg_wrap    = true;
        $tab_reg_heading = '13. Cột giới thiệu Form Đăng ký tư vấn (Cột trái)';
        $reg_data_key    = 'form';
        include dirname(__DIR__) . '/metabox-registration.php';
        ?>
    </div>

    <script src="<?php echo esc_url(get_stylesheet_directory_uri() . '/js/lucide.min.js'); ?>"></script>
    <script src="<?php echo esc_url(get_stylesheet_directory_uri() . '/inc/landing-pages/common/metabox.js?ver=' . (file_exists(dirname(__DIR__) . '/common/metabox.js') ? filemtime(dirname(__DIR__) . '/common/metabox.js') : '1.0')); ?>"></script>
