<?php
/**
 * Save Logic for 'Hiểu Mình'
 * Scope: $post_id, $post, $slug
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/api.php';

// 1. Lưu & Đồng bộ SEO Meta & Header Nav CTA (Dùng chung)
$defaults = array();
include dirname(__DIR__) . '/save-seo.php';

$fields = array(
    // Hero chuẩn
    'landing_hero_kicker',
    'landing_hero_title_prefix',
    'landing_hero_title_highlight',
    'landing_hero_lead_highlight',
    'landing_hero_lead',
    'landing_hero_cta_primary',
    'landing_hero_cta_secondary',
    'landing_hero_cta_primary_href',
    'landing_hero_cta_secondary_href',
    'landing_hero_image',
    'landing_hero_image_alt',
    'landing_hero_image_badge',
    'landing_hero_quote_bold',
    'landing_hero_quote_sub',
    'landing_hero_chips',
);

foreach ($fields as $f) {
    if (isset($_POST[$f])) {
        $val = wp_unslash($_POST[$f]);
        if (in_array($f, array('landing_hero_lead', 'landing_meta_description', 'landing_hero_chips'), true)) {
            $val = sanitize_textarea_field($val);
        } else {
            $val = sanitize_text_field($val);
        }
        update_post_meta($post_id, $f, $val);
    }
}

// 2. Lưu & Đồng bộ Perspectives (Góc nhìn - Dùng chung)
include dirname(__DIR__) . '/save-perspectives.php';

// 3. Lưu & Đồng bộ Cách tiếp cận / Triết lý (Approach - Dùng chung)
include dirname(__DIR__) . '/save-approach.php';

// 4. Lưu & Đồng bộ Service Values (Không chỉ là thông tin)
$sv_fields = array(
    'landing_service_values_eyebrow',
    'landing_service_values_title_prefix',
    'landing_service_values_title_highlight',
    'landing_service_values_lead',
);
foreach ($sv_fields as $f) {
    if (isset($_POST[$f])) {
        $val = wp_unslash($_POST[$f]);
        if ($f === 'landing_service_values_lead') {
            $val = sanitize_textarea_field($val);
        } else {
            $val = sanitize_text_field($val);
        }
        update_post_meta($post_id, $f, $val);
    }
}
if (isset($_POST['landing_service_values_cards'])) {
    $sv_cards_input = wp_unslash($_POST['landing_service_values_cards']);
    $sv_decoded = is_array($sv_cards_input) ? $sv_cards_input : json_decode($sv_cards_input, true);
    if (is_array($sv_decoded)) {
        $clean_sv_cards = array();
        foreach ($sv_decoded as $sc) {
            $clean_sv_cards[] = array(
                'title' => sanitize_text_field($sc['title'] ?? ''),
                'desc'  => sanitize_textarea_field($sc['desc'] ?? ''),
                'icon'  => sanitize_text_field($sc['icon'] ?? ''),
            );
        }
        update_post_meta($post_id, 'landing_service_values_cards', wp_json_encode($clean_sv_cards, JSON_UNESCAPED_UNICODE));
    }
}

// 4. Lưu & Đồng bộ Packages (Bảng giá dịch vụ - Dùng chung)
include dirname(__DIR__) . '/save-packages.php';

// 5. Lưu & Đồng bộ Package Guide (06. Chưa biết chọn gói nào?)
$pg_fields = array(
    'landing_package_guide_eyebrow',
    'landing_package_guide_title_prefix',
    'landing_package_guide_title_highlight',
    'landing_package_guide_lead',
);
foreach ($pg_fields as $f) {
    if (isset($_POST[$f])) {
        $val = wp_unslash($_POST[$f]);
        if ($f === 'landing_package_guide_lead') {
            $val = sanitize_textarea_field($val);
        } else {
            $val = sanitize_text_field($val);
        }
        update_post_meta($post_id, $f, $val);
    }
}
if (isset($_POST['landing_package_guide_cards'])) {
    $pg_cards_input = wp_unslash($_POST['landing_package_guide_cards']);
    $pg_decoded = is_array($pg_cards_input) ? $pg_cards_input : json_decode($pg_cards_input, true);
    if (is_array($pg_decoded)) {
        $clean_pg_cards = array();
        foreach ($pg_decoded as $pc) {
            $clean_pg_cards[] = array(
                'title' => sanitize_text_field($pc['title'] ?? ''),
                'desc'  => sanitize_textarea_field($pc['desc'] ?? ''),
                'icon'  => sanitize_text_field($pc['icon'] ?? ''),
            );
        }
        update_post_meta($post_id, 'landing_package_guide_cards', wp_json_encode($clean_pg_cards, JSON_UNESCAPED_UNICODE));
    }
}

// 6. Lưu & Đồng bộ Why Thien Tam (07. Vì sao là Thiên Tâm?)
$wtt_fields = array(
    'landing_why_thien_tam_eyebrow',
    'landing_why_thien_tam_title_prefix',
    'landing_why_thien_tam_title_highlight',
    'landing_why_thien_tam_lead',
);
foreach ($wtt_fields as $f) {
    if (isset($_POST[$f])) {
        $val = wp_unslash($_POST[$f]);
        if ($f === 'landing_why_thien_tam_lead') {
            $val = sanitize_textarea_field($val);
        } else {
            $val = sanitize_text_field($val);
        }
        update_post_meta($post_id, $f, $val);
    }
}
if (isset($_POST['landing_why_thien_tam_cards'])) {
    $wtt_cards_input = wp_unslash($_POST['landing_why_thien_tam_cards']);
    $wtt_decoded = is_array($wtt_cards_input) ? $wtt_cards_input : json_decode($wtt_cards_input, true);
    if (is_array($wtt_decoded)) {
        $clean_wtt_cards = array();
        foreach ($wtt_decoded as $wc) {
            $clean_wtt_cards[] = array(
                'title' => sanitize_text_field($wc['title'] ?? ''),
                'desc'  => sanitize_textarea_field($wc['desc'] ?? ''),
                'icon'  => sanitize_text_field($wc['icon'] ?? ''),
            );
        }
        update_post_meta($post_id, 'landing_why_thien_tam_cards', wp_json_encode($clean_wtt_cards, JSON_UNESCAPED_UNICODE));
    }
}

// 7. Lưu & Đồng bộ Expert (08. Chuyên gia đồng hành - Dùng chung)
include dirname(__DIR__) . '/save-expert.php';

// 8. Lưu & Đồng bộ Comparison (09. So sánh nhanh - Dùng chung)
include dirname(__DIR__) . '/save-comparison.php';

// 9. Lưu & Đồng bộ Process (10. Quy trình luận giải - Dùng chung)
include dirname(__DIR__) . '/save-process.php';

// 10. Lưu & Đồng bộ Consultation Values (11. Giá trị sau buổi tư vấn)
$cv_fields = array(
    'landing_consultation_values_eyebrow',
    'landing_consultation_values_title_prefix',
    'landing_consultation_values_title_highlight',
    'landing_consultation_values_lead',
);
foreach ($cv_fields as $f) {
    if (isset($_POST[$f])) {
        $val = wp_unslash($_POST[$f]);
        if ($f === 'landing_consultation_values_lead') {
            $val = sanitize_textarea_field($val);
        } else {
            $val = sanitize_text_field($val);
        }
        update_post_meta($post_id, $f, $val);
    }
}
if (isset($_POST['landing_consultation_values_cards'])) {
    $cv_cards_input = wp_unslash($_POST['landing_consultation_values_cards']);
    $cv_decoded = is_array($cv_cards_input) ? $cv_cards_input : json_decode($cv_cards_input, true);
    if (is_array($cv_decoded)) {
        $clean_cv_cards = array();
        foreach ($cv_decoded as $cc) {
            $clean_cv_cards[] = array(
                'title' => sanitize_text_field($cc['title'] ?? ''),
                'desc'  => sanitize_textarea_field($cc['desc'] ?? ''),
                'icon'  => sanitize_text_field($cc['icon'] ?? ''),
            );
        }
        update_post_meta($post_id, 'landing_consultation_values_cards', wp_json_encode($clean_cv_cards, JSON_UNESCAPED_UNICODE));
    }
}

// 11. Lưu & Đồng bộ FAQ (Dùng chung)
include dirname(__DIR__) . '/save-faq.php';

// 12. Lưu & Đồng bộ Form Đăng ký - Cột trái (Dùng chung)
include dirname(__DIR__) . '/save-registration.php';

// 12b. Lưu & Đồng bộ Feedback (Hình ảnh phản hồi)
$fb_fields = array(
    'landing_feedback_title_prefix',
    'landing_feedback_title_highlight',
    'landing_feedback_desc',
);
foreach ($fb_fields as $f) {
    if (isset($_POST[$f])) {
        $val = wp_unslash($_POST[$f]);
        $val = ($f === 'landing_feedback_desc') ? sanitize_textarea_field($val) : sanitize_text_field($val);
        update_post_meta($post_id, $f, $val);
    }
}
if (isset($_POST['feedback_items']) && is_array($_POST['feedback_items'])) {
    $clean_items = array();
    foreach ($_POST['feedback_items'] as $item) {
        $img = trim(sanitize_text_field(wp_unslash($item['image'] ?? '')));
        if (! empty($img)) {
            $clean_items[] = array(
                'image' => $img,
                'title' => sanitize_text_field(wp_unslash($item['title'] ?? '')),
                'alt'   => sanitize_text_field(wp_unslash($item['alt'] ?? '')),
            );
        }
    }
    update_post_meta($post_id, 'landing_feedback_items', wp_json_encode($clean_items, JSON_UNESCAPED_UNICODE));
} elseif (isset($_POST['landing_feedback_items'])) {
    $raw = wp_unslash($_POST['landing_feedback_items']);
    $decoded = is_array($raw) ? $raw : json_decode($raw, true);
    if (is_array($decoded)) {
        $clean_items = array();
        foreach ($decoded as $item) {
            $img = is_array($item) ? trim(sanitize_text_field($item['image'] ?? '')) : trim(sanitize_text_field($item));
            if (! empty($img)) {
                $clean_items[] = array(
                    'image' => $img,
                    'title' => is_array($item) ? sanitize_text_field($item['title'] ?? '') : '',
                    'alt'   => is_array($item) ? sanitize_text_field($item['alt'] ?? '') : '',
                );
            }
        }
        update_post_meta($post_id, 'landing_feedback_items', wp_json_encode($clean_items, JSON_UNESCAPED_UNICODE));
    }
}

// 13. Đồng bộ sang landing_custom_json
$res = thientam_landing_get_data_hieu_minh($post_id, 'hieu-minh');
if (! empty($res['data'])) {
    update_post_meta($post_id, 'landing_custom_json', wp_json_encode($res['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    update_post_meta($post_id, 'landing_is_initialized', '1');
    update_post_meta($post_id, 'landing_last_saved', current_time('mysql'));
}
