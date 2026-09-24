<?php

/**
 * Save Logic for 'Hiểu con để đồng hành'
 * Scope: $post_id, $post, $slug
 */

if (! defined('ABSPATH')) {
    exit;
}

// Nạp baseline custom_json để cập nhật đồng bộ
$custom_json_raw = get_post_meta($post_id, 'landing_custom_json', true);
$defaults = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($custom_json_raw) : array();
if (empty($defaults) && function_exists('thientam_get_landing_default_data')) {
    $defaults = thientam_get_landing_default_data('hieu-con-de-dong-hanh');
}

// 2. Lưu các trường văn bản cơ bản
$text_fields = array(
    'landing_meta_title',
    'landing_meta_description',
    'landing_meta_keywords',

    'landing_nav_cta_label',
    'landing_nav_cta_href',
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
    'landing_doors_eyebrow',
    'landing_doors_title_prefix',
    'landing_doors_title_highlight',
    'landing_doors_lead',
    'landing_perspectives_title_prefix',
    'landing_perspectives_title_highlight',
    'landing_perspectives_lead',
    'landing_approach_title_prefix',
    'landing_approach_title_highlight',
    'landing_approach_desc',
    'landing_approach_notice',
    'landing_approach_visual_quote',
    'landing_approach_visual_label',
    'landing_approach_image',
    'landing_approach_image_alt',
    'landing_relationship_title_prefix',
    'landing_relationship_title_highlight',
    'landing_relationship_lead',
    'landing_relationship_statement',
    'landing_relationship_statement_badge',
    'landing_packages_title_prefix',
    'landing_packages_title_highlight',
    'landing_packages_lead',
    'landing_packages_btn',
    'landing_consultation_title',
    'landing_consultation_desc',
    'landing_consultation_btn',
    'landing_consultation_prefill',
    'landing_expert_title_prefix',
    'landing_expert_title_highlight',
    'landing_expert_avatar',
    'landing_expert_name',
    'landing_expert_badge',
    'landing_expert_desc',
    'landing_expert_desc1',
    'landing_expert_desc2',
    'landing_expert_notice',
    'landing_expert_btn',
    'landing_process_title_prefix',
    'landing_process_title_highlight',
    'landing_process_lead',
    'landing_process_notice',
    'landing_trust_title_prefix',
    'landing_trust_title_highlight',
    'landing_trust_lead',
    'landing_faq_title_prefix',
    'landing_faq_title_highlight',
    'landing_faq_title',
    'landing_faq_desc',
    'landing_faq_note',
    'landing_feedback_title_prefix',
    'landing_feedback_title_highlight',
    'landing_feedback_desc',
    'landing_final_title_prefix',
    'landing_final_title_highlight',
    'landing_final_lead',
    'landing_final_statement',
);

foreach ($text_fields as $f) {
    if (isset($_POST[$f])) {
        $raw_val = wp_unslash($_POST[$f]);
        if ($f === 'landing_hero_image' || $f === 'landing_expert_avatar' || $f === 'landing_approach_image') {
            $clean_val = esc_url_raw(trim($raw_val));
        } else {
            $clean_val = sanitize_textarea_field($raw_val);
        }
        update_post_meta($post_id, $f, $clean_val);
    }
}

// 3. Đồng bộ vào JSON data
// SEO Meta & Header Nav CTA (Thành phần dùng chung cho tất cả Landing Pages)
include dirname(__DIR__) . '/save-seo.php';

// Hero
if (isset($_POST['landing_hero_kicker'])) $defaults['hero']['kicker'] = sanitize_text_field(wp_unslash($_POST['landing_hero_kicker']));
if (isset($_POST['landing_hero_title_prefix'])) $defaults['hero']['titlePrefix'] = sanitize_text_field(wp_unslash($_POST['landing_hero_title_prefix']));
if (isset($_POST['landing_hero_title_highlight'])) $defaults['hero']['titleHighlight'] = sanitize_text_field(wp_unslash($_POST['landing_hero_title_highlight']));
if (isset($_POST['landing_hero_lead_highlight'])) $defaults['hero']['leadHighlight'] = sanitize_text_field(wp_unslash($_POST['landing_hero_lead_highlight']));
if (isset($_POST['landing_hero_lead'])) $defaults['hero']['lead'] = sanitize_textarea_field(wp_unslash($_POST['landing_hero_lead']));
if (isset($_POST['landing_hero_cta_primary'])) $defaults['hero']['ctaPrimary'] = sanitize_text_field(wp_unslash($_POST['landing_hero_cta_primary']));
if (isset($_POST['landing_hero_cta_secondary'])) $defaults['hero']['ctaSecondary'] = sanitize_text_field(wp_unslash($_POST['landing_hero_cta_secondary']));
if (isset($_POST['landing_hero_image_alt'])) $defaults['hero']['imageAlt'] = sanitize_text_field(wp_unslash($_POST['landing_hero_image_alt']));
if (isset($_POST['landing_hero_image_badge'])) $defaults['hero']['imageBadge'] = sanitize_text_field(wp_unslash($_POST['landing_hero_image_badge']));
if (isset($_POST['landing_hero_quote_bold'])) $defaults['hero']['quote']['bold'] = sanitize_text_field(wp_unslash($_POST['landing_hero_quote_bold']));
if (isset($_POST['landing_hero_quote_sub'])) $defaults['hero']['quote']['sub'] = sanitize_text_field(wp_unslash($_POST['landing_hero_quote_sub']));
if (isset($_POST['landing_hero_image'])) {
    $img = esc_url_raw(trim(wp_unslash($_POST['landing_hero_image'])));
    if (! empty($img)) {
        $defaults['hero']['image'] = $img;
    } else {
        unset($defaults['hero']['image']);
    }
}
if (isset($_POST['landing_hero_chips'])) {
    $chips = array_filter(array_map('trim', explode("\n", str_replace("\r", "", wp_unslash($_POST['landing_hero_chips'])))));
    $defaults['hero']['chips'] = array_values($chips);
}

// Hai điểm bắt đầu (Doors) - hỗ trợ cả dynamic doors_items và form cũ
if (isset($_POST['doors_items']) && is_array($_POST['doors_items'])) {
    $clean_doors = array();
    foreach ($_POST['doors_items'] as $idx => $item) {
        $t = sanitize_text_field(wp_unslash($item['title'] ?? ''));
        $b = sanitize_text_field(wp_unslash($item['badge'] ?? ''));
        $d = sanitize_textarea_field(wp_unslash($item['desc'] ?? ''));
        $h = sanitize_text_field(wp_unslash($item['highlight'] ?? ''));
        $pf = sanitize_text_field(wp_unslash($item['prefill'] ?? ''));
        if ($pf === '' && $t !== '') {
            $pf = $t;
        }
        $q_raw = wp_unslash($item['questions'] ?? '');
        $q_arr = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $q_raw))));

        if ($t !== '' || $d !== '' || !empty($q_arr) || $b !== '') {
            $clean_doors[] = array(
                'number'    => sprintf('%02d', count($clean_doors) + 1),
                'title'     => $t,
                'badge'     => $b,
                'desc'      => $d,
                'questions' => array_values($q_arr),
                'highlight' => $h,
                'prefill'   => $pf,
            );
        }
    }

    $defaults['entryDoors']['eyebrow']        = sanitize_text_field(wp_unslash($_POST['landing_doors_eyebrow'] ?? ''));
    $defaults['entryDoors']['titlePrefix']    = sanitize_text_field(wp_unslash($_POST['landing_doors_title_prefix'] ?? ''));
    $defaults['entryDoors']['titleHighlight'] = sanitize_text_field(wp_unslash($_POST['landing_doors_title_highlight'] ?? ''));
    $defaults['entryDoors']['lead']           = sanitize_textarea_field(wp_unslash($_POST['landing_doors_lead'] ?? ''));
    $defaults['entryDoors']['items']          = $clean_doors;

    if (isset($clean_doors[0])) $defaults['entryDoors']['door1'] = $clean_doors[0];
    if (isset($clean_doors[1])) $defaults['entryDoors']['door2'] = $clean_doors[1];

    update_post_meta($post_id, 'landing_entry_doors', wp_json_encode($defaults['entryDoors'], JSON_UNESCAPED_UNICODE));
} elseif (isset($_POST['door1_title'])) {
    $q1 = array_filter(array_map('trim', explode("\n", str_replace("\r", "", wp_unslash($_POST['door1_questions'] ?? '')))));
    $q2 = array_filter(array_map('trim', explode("\n", str_replace("\r", "", wp_unslash($_POST['door2_questions'] ?? '')))));

    $defaults['entryDoors']['eyebrow'] = sanitize_text_field(wp_unslash($_POST['landing_doors_eyebrow'] ?? ''));
    $defaults['entryDoors']['titlePrefix'] = sanitize_text_field(wp_unslash($_POST['landing_doors_title_prefix'] ?? ''));
    $defaults['entryDoors']['titleHighlight'] = sanitize_text_field(wp_unslash($_POST['landing_doors_title_highlight'] ?? ''));
    $defaults['entryDoors']['lead'] = sanitize_textarea_field(wp_unslash($_POST['landing_doors_lead'] ?? ''));

    $defaults['entryDoors']['door1']['title'] = sanitize_text_field(wp_unslash($_POST['door1_title'] ?? ''));
    $defaults['entryDoors']['door1']['badge'] = sanitize_text_field(wp_unslash($_POST['door1_badge'] ?? ''));
    $defaults['entryDoors']['door1']['desc'] = sanitize_textarea_field(wp_unslash($_POST['door1_desc'] ?? ''));
    $defaults['entryDoors']['door1']['highlight'] = sanitize_text_field(wp_unslash($_POST['door1_highlight'] ?? ''));
    if (isset($_POST['door1_btn'])) {
        $defaults['entryDoors']['door1']['btn'] = sanitize_text_field(wp_unslash($_POST['door1_btn']));
    }
    if (isset($_POST['door1_prefill'])) {
        $defaults['entryDoors']['door1']['prefill'] = sanitize_text_field(wp_unslash($_POST['door1_prefill']));
    }
    $defaults['entryDoors']['door1']['questions'] = array_values($q1);

    $defaults['entryDoors']['door2']['title'] = sanitize_text_field(wp_unslash($_POST['door2_title'] ?? ''));
    $defaults['entryDoors']['door2']['badge'] = sanitize_text_field(wp_unslash($_POST['door2_badge'] ?? ''));
    $defaults['entryDoors']['door2']['desc'] = sanitize_textarea_field(wp_unslash($_POST['door2_desc'] ?? ''));
    $defaults['entryDoors']['door2']['highlight'] = sanitize_text_field(wp_unslash($_POST['door2_highlight'] ?? ''));
    if (isset($_POST['door2_btn'])) {
        $defaults['entryDoors']['door2']['btn'] = sanitize_text_field(wp_unslash($_POST['door2_btn']));
    }
    if (isset($_POST['door2_prefill'])) {
        $defaults['entryDoors']['door2']['prefill'] = sanitize_text_field(wp_unslash($_POST['door2_prefill']));
    }
    $defaults['entryDoors']['door2']['questions'] = array_values($q2);

    update_post_meta($post_id, 'landing_entry_doors', wp_json_encode($defaults['entryDoors'], JSON_UNESCAPED_UNICODE));
}

// Perspectives (Dùng chung)
include dirname(__DIR__) . '/save-perspectives.php';

// Relationship
if (isset($_POST['landing_relationship_parent_items'])) {
    $p_items = array_filter(array_map('trim', explode("\n", str_replace("\r", "", wp_unslash($_POST['landing_relationship_parent_items'])))));
    $c_items = array_filter(array_map('trim', explode("\n", str_replace("\r", "", wp_unslash($_POST['landing_relationship_child_items'] ?? '')))));
    $defaults['relationship']['parent']['items'] = array_values($p_items);
    $defaults['relationship']['child']['items'] = array_values($c_items);
    update_post_meta($post_id, 'landing_relationship_parent', wp_json_encode(array_values($p_items), JSON_UNESCAPED_UNICODE));
    update_post_meta($post_id, 'landing_relationship_child', wp_json_encode(array_values($c_items), JSON_UNESCAPED_UNICODE));
}


// Approach
if (isset($_POST['landing_approach_title_prefix'])) $defaults['approach']['titlePrefix'] = sanitize_text_field(wp_unslash($_POST['landing_approach_title_prefix']));
if (isset($_POST['landing_approach_title_highlight'])) $defaults['approach']['titleHighlight'] = sanitize_text_field(wp_unslash($_POST['landing_approach_title_highlight']));
if (isset($_POST['landing_approach_desc'])) $defaults['approach']['desc'] = sanitize_textarea_field(wp_unslash($_POST['landing_approach_desc']));
if (isset($_POST['landing_approach_notice'])) $defaults['approach']['notice'] = sanitize_textarea_field(wp_unslash($_POST['landing_approach_notice']));
if (isset($_POST['landing_approach_visual_quote'])) $defaults['approach']['visualQuote'] = sanitize_text_field(wp_unslash($_POST['landing_approach_visual_quote']));
if (isset($_POST['landing_approach_visual_label'])) $defaults['approach']['visualLabel'] = sanitize_text_field(wp_unslash($_POST['landing_approach_visual_label']));
if (isset($_POST['landing_approach_image'])) {
    $aimg = esc_url_raw(trim(wp_unslash($_POST['landing_approach_image'])));
    if (! empty($aimg)) {
        $defaults['approach']['image'] = $aimg;
    } else {
        unset($defaults['approach']['image']);
    }
}
if (isset($_POST['landing_approach_image_alt'])) $defaults['approach']['imageAlt'] = sanitize_text_field(wp_unslash($_POST['landing_approach_image_alt']));

if (isset($_POST['landing_approach_points'])) {
    $lines = array_filter(array_map('trim', explode("\n", str_replace("\r", "", wp_unslash($_POST['landing_approach_points'])))));
    $new_points = array();
    foreach ($lines as $line) {
        if (preg_match('/^\*\*(.*?)\*\*\s*(.*)$/u', $line, $matches)) {
            $new_points[] = array(
                'bold' => sanitize_text_field(trim($matches[1])),
                'text' => sanitize_textarea_field(trim($matches[2])),
            );
        } else {
            $new_points[] = array(
                'bold' => '',
                'text' => sanitize_textarea_field($line),
            );
        }
    }
    $defaults['approach']['points'] = $new_points;
    update_post_meta($post_id, 'landing_approach_points', wp_json_encode($new_points, JSON_UNESCAPED_UNICODE));
} elseif (isset($_POST['approach_points']) && is_array($_POST['approach_points'])) {
    $new_points = array();
    foreach ($_POST['approach_points'] as $pt) {
        $new_points[] = array(
            'bold' => sanitize_text_field(wp_unslash($pt['bold'] ?? '')),
            'text' => sanitize_textarea_field(wp_unslash($pt['text'] ?? '')),
        );
    }
    $defaults['approach']['points'] = $new_points;
    update_post_meta($post_id, 'landing_approach_points', wp_json_encode($new_points, JSON_UNESCAPED_UNICODE));
}

// Packages (Bảng giá dịch vụ - Dùng chung)
include dirname(__DIR__) . '/save-packages.php';

// Expert (Chuyên gia đồng hành - Dùng chung)
include dirname(__DIR__) . '/save-expert.php';

// Process Steps (Dùng chung)
include dirname(__DIR__) . '/save-process.php';
if (isset($_POST['trust_cards']) && is_array($_POST['trust_cards'])) {
    $t_items = array();
    foreach ($_POST['trust_cards'] as $tc) {
        $cleaned = sanitize_textarea_field(wp_unslash($tc));
        if ($cleaned !== '') {
            $t_items[] = $cleaned;
        }
    }
    $defaults['trust']['items'] = array_values($t_items);
    update_post_meta($post_id, 'landing_trust_items', wp_json_encode(array_values($t_items), JSON_UNESCAPED_UNICODE));
} elseif (isset($_POST['landing_trust_items'])) {
    $t_items = array_filter(array_map('trim', explode("\n", str_replace("\r", "", wp_unslash($_POST['landing_trust_items'])))));
    $defaults['trust']['items'] = array_values($t_items);
    update_post_meta($post_id, 'landing_trust_items', wp_json_encode(array_values($t_items), JSON_UNESCAPED_UNICODE));
}

// FAQ (Dùng chung)
include dirname(__DIR__) . '/save-faq.php';

// Feedback (Hình ảnh phản hồi)
if (! isset($defaults['feedback']) || ! is_array($defaults['feedback'])) {
    $defaults['feedback'] = array();
}
if (isset($_POST['landing_feedback_title_prefix'])) {
    $defaults['feedback']['titlePrefix'] = sanitize_text_field(wp_unslash($_POST['landing_feedback_title_prefix']));
}
if (isset($_POST['landing_feedback_title_highlight'])) {
    $defaults['feedback']['titleHighlight'] = sanitize_text_field(wp_unslash($_POST['landing_feedback_title_highlight']));
}
if (isset($_POST['landing_feedback_desc'])) {
    $defaults['feedback']['desc'] = sanitize_textarea_field(wp_unslash($_POST['landing_feedback_desc']));
}
if (isset($_POST['feedback_items']) && is_array($_POST['feedback_items'])) {
    $clean_items = array();
    foreach ($_POST['feedback_items'] as $item) {
        $img = esc_url_raw(trim(wp_unslash($item['image'] ?? '')));
        $alt = sanitize_text_field(wp_unslash($item['alt'] ?? ''));
        $title = sanitize_text_field(wp_unslash($item['title'] ?? ''));
        if (! empty($img)) {
            $clean_items[] = array(
                'image' => $img,
                'alt'   => $alt,
                'title' => $title,
            );
        }
    }
    $defaults['feedback']['items'] = $clean_items;
    update_post_meta($post_id, 'landing_feedback_items', wp_json_encode($clean_items, JSON_UNESCAPED_UNICODE));
} elseif (isset($_POST['landing_feedback_items'])) {
    $raw = wp_unslash($_POST['landing_feedback_items']);
    $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
    if (is_array($decoded)) {
        $clean_items = array();
        foreach ($decoded as $item) {
            $img = esc_url_raw(trim($item['image'] ?? ''));
            if (! empty($img)) {
                $clean_items[] = array(
                    'image' => $img,
                    'alt'   => sanitize_text_field($item['alt'] ?? ''),
                    'title' => sanitize_text_field($item['title'] ?? ''),
                );
            }
        }
        $defaults['feedback']['items'] = $clean_items;
        update_post_meta($post_id, 'landing_feedback_items', wp_json_encode($clean_items, JSON_UNESCAPED_UNICODE));
    }
}

// Form Đăng ký - Cột trái (Dùng chung)
include dirname(__DIR__) . '/save-registration.php';

// Form needs options

// 4. Lưu lại JSON tổng hợp vào landing_custom_json để REST API luôn có dữ liệu mới nhất
update_post_meta($post_id, 'landing_custom_json', wp_slash(wp_json_encode($defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)));
update_post_meta($post_id, 'landing_is_initialized', '1');
update_post_meta($post_id, 'landing_last_saved', current_time('mysql'));

// Clear cache để API và metabox luôn phản ánh đúng
if (function_exists('wp_cache_delete')) {
    wp_cache_delete('thientam_landing_page_id_' . $slug, 'thientam');
}
delete_transient('thientam_landing_' . $slug);
