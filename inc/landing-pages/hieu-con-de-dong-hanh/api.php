<?php
/**
 * REST API Data Mapper for 'Hiểu con để đồng hành'
 *
 * @package ThienTamData
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Lấy dữ liệu đầy đủ cho landing Hiểu con để đồng hành
 */
function thientam_landing_get_data_hieu_con_de_dong_hanh($page_id, $slug)
{
    $data = array();
    $is_customized = false;
    $updated_at = null;

    if ($page_id) {
        $updated_at = get_the_modified_date('c', $page_id);

        // 1. Nạp từ landing_custom_json nếu có
        $custom_json_raw = get_post_meta($page_id, 'landing_custom_json', true);
        if (! empty($custom_json_raw)) {
            $parsed = json_decode($custom_json_raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                $data = $parsed;
                $is_customized = true;
            }
        }

        // 2. Ghép / Bổ sung từ các trường section meta để luôn đầy đủ dữ liệu
        $sections_map = array(
            'entryDoors'                 => 'landing_entry_doors',
            'approach.steps'             => 'landing_approach_steps',
            'approach.points'            => 'landing_approach_points',
            'packages.items'             => 'landing_packages_items',
            'packages.consultation'      => 'landing_packages_consultation',
            'perspectives.cards'         => 'landing_perspectives_cards',
            'relationship.parent.items'  => 'landing_relationship_parent',
            'relationship.child.items'   => 'landing_relationship_child',
            'expert.pills'               => 'landing_expert_pills',
            'process.steps'              => 'landing_process_steps',
            'trust.items'                => 'landing_trust_items',
            'faq.items'                  => 'landing_faq_items',
            'finalCta'                   => 'landing_final_cta',
            'nav'                        => 'landing_nav',
            'navCta'                     => 'landing_nav_cta',
        );
        foreach ($sections_map as $path => $mkey) {
            $raw = get_post_meta($page_id, $mkey, true);
            if (! empty($raw)) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    thientam_set_nested_array_value($data, $path, $decoded);
                    $is_customized = true;
                }
            }
        }

        // 3. Ghi đè các trường text đơn lẻ từ Admin tabs nếu admin có sửa
        $fields_map = array(
            'meta.title'                           => 'landing_meta_title',
            'meta.description'                     => 'landing_meta_description',
            'navCta.label'                         => 'landing_nav_cta_label',
            'navCta.href'                          => 'landing_nav_cta_href',

            // Hero
            'hero.kicker'                          => 'landing_hero_kicker',
            'hero.titlePrefix'                     => 'landing_hero_title_prefix',
            'hero.titleHighlight'                  => 'landing_hero_title_highlight',
            'hero.leadHighlight'                   => 'landing_hero_lead_highlight',
            'hero.lead'                            => 'landing_hero_lead',
            'hero.ctaPrimary'                      => 'landing_hero_cta_primary',
            'hero.ctaSecondary'                    => 'landing_hero_cta_secondary',
            'hero.image'                           => 'landing_hero_image',
            'hero.imageAlt'                        => 'landing_hero_image_alt',
            'hero.imageBadge'                      => 'landing_hero_image_badge',
            'hero.quote.bold'                      => 'landing_hero_quote_bold',
            'hero.quote.sub'                       => 'landing_hero_quote_sub',

            // Entry Doors
            'entryDoors.eyebrow'                   => 'landing_doors_eyebrow',
            'entryDoors.titlePrefix'               => 'landing_doors_title_prefix',
            'entryDoors.titleHighlight'            => 'landing_doors_title_highlight',
            'entryDoors.lead'                      => 'landing_doors_lead',

            // Perspectives & Relationship
            'perspectives.titlePrefix'             => 'landing_perspectives_title_prefix',
            'perspectives.titleHighlight'          => 'landing_perspectives_title_highlight',
            'perspectives.lead'                    => 'landing_perspectives_lead',
            'relationship.titlePrefix'             => 'landing_relationship_title_prefix',
            'relationship.titleHighlight'          => 'landing_relationship_title_highlight',
            'relationship.lead'                    => 'landing_relationship_lead',
            'relationship.statement'               => 'landing_relationship_statement',
            'relationship.statementBadge'          => 'landing_relationship_statement_badge',

            // Approach
            'approach.titlePrefix'                 => 'landing_approach_title_prefix',
            'approach.titleHighlight'              => 'landing_approach_title_highlight',
            'approach.desc'                        => 'landing_approach_desc',
            'approach.notice'                      => 'landing_approach_notice',
            'approach.visualQuote'                 => 'landing_approach_visual_quote',
            'approach.visualLabel'                 => 'landing_approach_visual_label',
            'approach.image'                       => 'landing_approach_image',
            'approach.imageAlt'                    => 'landing_approach_image_alt',

            // Packages
            'packages.titlePrefix'                 => 'landing_packages_title_prefix',
            'packages.titleHighlight'              => 'landing_packages_title_highlight',
            'packages.lead'                        => 'landing_packages_lead',
            'packages.btn'                         => 'landing_packages_btn',
            'packages.consultation.title'          => 'landing_consultation_title',
            'packages.consultation.desc'           => 'landing_consultation_desc',
            'packages.consultation.btn'            => 'landing_consultation_btn',
            'packages.consultation.prefill'        => 'landing_consultation_prefill',

            // Expert
            'expert.avatar'                        => 'landing_expert_avatar',
            'expert.name'                          => 'landing_expert_name',
            'expert.badge'                         => 'landing_expert_badge',
            'expert.desc'                          => 'landing_expert_desc',
            'expert.desc1'                         => 'landing_expert_desc1',
            'expert.desc2'                         => 'landing_expert_desc2',
            'expert.notice'                        => 'landing_expert_notice',
            'expert.btn'                           => 'landing_expert_btn',

            // Process & Trust
            'process.titlePrefix'                  => 'landing_process_title_prefix',
            'process.titleHighlight'               => 'landing_process_title_highlight',
            'process.lead'                         => 'landing_process_lead',
            'process.notice'                       => 'landing_process_notice',
            'trust.titlePrefix'                    => 'landing_trust_title_prefix',
            'trust.titleHighlight'                 => 'landing_trust_title_highlight',
            'trust.lead'                           => 'landing_trust_lead',

            // FAQ
            'faq.title'                            => 'landing_faq_title',
            'faq.desc'                             => 'landing_faq_desc',
            'faq.note'                             => 'landing_faq_note',

            // Final CTA
            'finalCta.titlePrefix'                 => 'landing_final_title_prefix',
            'finalCta.titleHighlight'              => 'landing_final_title_highlight',
            'finalCta.lead'                        => 'landing_final_lead',
            'finalCta.statement'                   => 'landing_final_statement',
        );

        foreach ($fields_map as $json_path => $meta_key) {
            $val = get_post_meta($page_id, $meta_key, true);
            if ($val !== '' && $val !== null && $val !== false) {
                thientam_set_nested_array_value($data, $json_path, $val);
                $is_customized = true;
            }
        }

        // Xử lý chips của hero
        $chips_raw = get_post_meta($page_id, 'landing_hero_chips', true);
        if (! empty($chips_raw)) {
            $chips = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $chips_raw))));
            if (! empty($chips)) {
                $data['hero']['chips'] = array_values($chips);
                $is_customized = true;
            }
        }

        // Xử lý keywords của meta
        $keywords_raw = get_post_meta($page_id, 'landing_meta_keywords', true);
        if (! empty($keywords_raw)) {
            $kws = array_filter(array_map('trim', explode(',', $keywords_raw)));
            if (! empty($kws)) {
                $data['meta']['keywords'] = array_values($kws);
                $is_customized = true;
            }
        }

        // Xử lý Thumbnail WP cho Meta & OpenGraph
        $thumb_id = get_post_thumbnail_id($page_id);
        $wp_thumb = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'full') : '';
        if (empty($wp_thumb) && $page_id) {
            $wp_thumb = get_the_post_thumbnail_url($page_id, 'full') ?: '';
        }

        if (! empty($wp_thumb)) {
            $data['meta']['thumbnail'] = $wp_thumb;
            $data['thumbnail'] = $wp_thumb;
            if (empty($data['meta']['image'])) {
                $data['meta']['image'] = $wp_thumb;
            }
        }
    }

    // 4. Fallback chỉ khi Database hoàn toàn chưa có bất kỳ dữ liệu gì
    if (empty($data)) {
        $data = thientam_get_landing_default_data($slug) ?: array();
    }

    if ($page_id) {
        $curr_alt = get_post_meta($page_id, 'landing_hero_image_alt', true);
        if (empty($curr_alt) && !empty($data['hero']['imageAlt'])) {
            update_post_meta($page_id, 'landing_hero_image_alt', $data['hero']['imageAlt']);
        }
    }

    // Bổ sung imageAlt cho approach nếu thiếu
    if (isset($data['approach']) && empty($data['approach']['imageAlt'])) {
        $data['approach']['imageAlt'] = 'Phương pháp tiếp cận Tử Vi khoa học Thiên Tâm';
    }

    // Sửa lỗi nếu expert.name bị lưu nhầm thành url ảnh
    if (isset($data['expert']['name']) && strpos($data['expert']['name'], 'http') === 0) {
        $data['expert']['name'] = 'Ms. Linda Trần';
        if ($page_id) {
            update_post_meta($page_id, 'landing_expert_name', 'Ms. Linda Trần');
        }
    }

    // Tự động phục hồi vào post meta nếu database đang rỗng
    if ($page_id) {
        $entry_doors_raw = get_post_meta($page_id, 'landing_entry_doors', true);
        $entry_doors_dec = !empty($entry_doors_raw) ? json_decode($entry_doors_raw, true) : null;
        if (empty($entry_doors_dec) || empty($entry_doors_dec['door1']['title'])) {
            update_post_meta($page_id, 'landing_entry_doors', wp_json_encode($data['entryDoors'], JSON_UNESCAPED_UNICODE));
        }

        $proc_steps_raw = get_post_meta($page_id, 'landing_process_steps', true);
        $proc_steps_dec = !empty($proc_steps_raw) ? json_decode($proc_steps_raw, true) : null;
        if (empty($proc_steps_dec) || (isset($proc_steps_dec[0]['title']) && $proc_steps_dec[0]['title'] === '')) {
            update_post_meta($page_id, 'landing_process_steps', wp_json_encode($data['process']['steps'], JSON_UNESCAPED_UNICODE));
        }

        $trust_raw = get_post_meta($page_id, 'landing_trust_items', true);
        $trust_dec = !empty($trust_raw) ? json_decode($trust_raw, true) : null;
        if (empty($trust_dec)) {
            update_post_meta($page_id, 'landing_trust_items', wp_json_encode($data['trust']['items'], JSON_UNESCAPED_UNICODE));
        }

        $pills_raw = get_post_meta($page_id, 'landing_expert_pills', true);
        $pills_dec = !empty($pills_raw) ? json_decode($pills_raw, true) : null;
        if (empty($pills_dec)) {
            update_post_meta($page_id, 'landing_expert_pills', wp_json_encode($data['expert']['pills'], JSON_UNESCAPED_UNICODE));
        }



        // Cập nhật lại landing_custom_json tổng hợp
        update_post_meta($page_id, 'landing_custom_json', wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }


    // Khởi tạo các phần còn thiếu vào database nếu đang bị rỗng
    if ($page_id) {
        // 1. Perspectives cards (6 thẻ)
        $persp_raw = get_post_meta($page_id, 'landing_perspectives_cards', true);
        $persp_dec = !empty($persp_raw) ? json_decode($persp_raw, true) : null;
        $persp_empty = empty($persp_dec);
        if (! $persp_empty && is_array($persp_dec)) {
            $all_blank = true;
            foreach ($persp_dec as $c) {
                if (! empty($c['title'])) { $all_blank = false; break; }
            }
            if ($all_blank) $persp_empty = true;
        }
        if ($persp_empty) {
            $default_cards = array(
                array("title" => "Tính cách & khí chất", "desc" => "Những đặc điểm nổi bật trong cách con thể hiện và tương tác."),
                array("title" => "Điểm mạnh & điểm cần phát triển", "desc" => "Nhìn rõ hơn điều có thể khuyến khích và điều cần rèn luyện."),
                array("title" => "Tiềm năng", "desc" => "Có thêm góc nhìn về thế mạnh và khả năng phát triển của con."),
                array("title" => "Xu hướng học tập", "desc" => "Hiểu thêm về xu hướng của con trong quá trình học tập và trưởng thành."),
                array("title" => "Điều nên khuyến khích", "desc" => "Những đặc điểm tích cực ba mẹ có thể tạo điều kiện để con phát huy."),
                array("title" => "Điều cần rèn luyện", "desc" => "Những khía cạnh con có thể từng bước phát triển thêm.")
            );
            $data['perspectives']['cards'] = $default_cards;
            update_post_meta($page_id, 'landing_perspectives_cards', wp_json_encode($default_cards, JSON_UNESCAPED_UNICODE));
        }

        // 2. Relationship Parent & Child items
        $rel_p_raw = get_post_meta($page_id, 'landing_relationship_parent', true);
        $rel_p_dec = !empty($rel_p_raw) ? json_decode($rel_p_raw, true) : null;
        if (empty($rel_p_dec)) {
            $p_items = array("Cách suy nghĩ", "Cách giao tiếp", "Cách kỳ vọng");
            $data['relationship']['parent']['items'] = $p_items;
            update_post_meta($page_id, 'landing_relationship_parent', wp_json_encode($p_items, JSON_UNESCAPED_UNICODE));
        }

        $rel_c_raw = get_post_meta($page_id, 'landing_relationship_child', true);
        $rel_c_dec = !empty($rel_c_raw) ? json_decode($rel_c_raw, true) : null;
        if (empty($rel_c_dec)) {
            $c_items = array("Tính cách & khí chất", "Điểm mạnh", "Điểm cần phát triển");
            $data['relationship']['child']['items'] = $c_items;
            update_post_meta($page_id, 'landing_relationship_child', wp_json_encode($c_items, JSON_UNESCAPED_UNICODE));
        }

        // 3. Packages Items (3 gói)
        $pkg_raw = get_post_meta($page_id, 'landing_packages_items', true);
        $pkg_dec = !empty($pkg_raw) ? json_decode($pkg_raw, true) : null;
        $pkg_empty = empty($pkg_dec);
        if (! $pkg_empty && is_array($pkg_dec)) {
            $all_blank = true;
            foreach ($pkg_dec as $p) {
                if (! empty($p['title'])) { $all_blank = false; break; }
            }
            if ($all_blank) $pkg_empty = true;
        }
        if ($pkg_empty) {
            $default_pkgs = array(
                array(
                    "id" => "hieu-con",
                    "badge" => "GÓI 01",
                    "title" => "HIỂU CON",
                    "slogan" => "Con mạnh ở đâu? Con cần gì để phát triển tốt hơn?",
                    "price" => "990.000đ",
                    "oldPrice" => "Giá gốc 3.000.000đ",
                    "target" => "Dành cho ba mẹ muốn khám phá một đứa trẻ, đặc biệt khi con còn nhỏ hoặc chưa có vấn đề lớn.",
                    "features" => array(
                        "Tính cách · Khí chất",
                        "Điểm mạnh · Điểm yếu",
                        "Tiềm năng · Xu hướng học tập",
                        "Khả năng phát triển",
                        "Điều nên khuyến khích",
                        "Điều cần rèn luyện"
                    ),
                    "isFeatured" => false,
                    "guidance" => "Phù hợp: Muốn khám phá tính cách & tiềm năng con từ sớm",
                    "btn" => "Chọn gói Hiểu Con",
                    "prefill" => "Muốn khám phá con từ sớm"
                ),
                array(
                    "id" => "dong-hanh",
                    "badge" => "Gói đề xuất",
                    "title" => "HIỂU CON ĐỂ ĐỒNG HÀNH",
                    "slogan" => "Hiểu con để đồng hành, không phải để kiểm soát.",
                    "price" => "2.500.000đ",
                    "oldPrice" => "Giá gốc 5.000.000đ",
                    "target" => "Dành cho ba mẹ không chỉ muốn biết “Con là người như thế nào?”, mà còn muốn hiểu “Ba mẹ nên đồng hành với con như thế nào?”.",
                    "features" => array(
                        "Về con: tính cách, khí chất, điểm mạnh, điểm cần phát triển",
                        "Về ba mẹ: cách suy nghĩ, giao tiếp, kỳ vọng",
                        "Về mối quan hệ: tương đồng, khác biệt, điểm dễ xung đột",
                        "Gợi ý cách giao tiếp phù hợp",
                        "Có thể hỏi thêm vấn đề liên quan đến lá số cá nhân"
                    ),
                    "isFeatured" => true,
                    "guidance" => "Phù hợp: Muốn hiểu cả con và cách ba mẹ đồng hành",
                    "btn" => "Chọn gói Đồng Hành",
                    "prefill" => "Đang gặp khoảng cách / khó giao tiếp với con"
                ),
                array(
                    "id" => "hieu-gia-dinh",
                    "badge" => "GÓI 03",
                    "title" => "HIỂU GIA ĐÌNH",
                    "slogan" => "Hiểu mình — Hiểu con — Hiểu nhau.",
                    "price" => "4.000.000đ",
                    "oldPrice" => "Giá gốc 8.000.000đ",
                    "target" => "Dành cho gia đình muốn nhìn rộng hơn vào cách các thành viên tương tác với nhau.",
                    "features" => array(
                        "Vai trò và xu hướng của từng thành viên",
                        "Điểm tương đồng · Điểm khác biệt",
                        "Điểm dễ phát sinh xung đột",
                        "Cách tương tác phù hợp",
                        "Có thể hỏi thêm vấn đề liên quan đến lá số cá nhân"
                    ),
                    "isFeatured" => false,
                    "guidance" => "Phù hợp: Muốn nhìn toàn bộ bức tranh tương tác gia đình",
                    "btn" => "Chọn gói Hiểu Gia Đình",
                    "prefill" => "Muốn hiểu mối quan hệ trong gia đình"
                )
            );
            $data['packages']['items'] = $default_pkgs;
            update_post_meta($page_id, 'landing_packages_items', wp_json_encode($default_pkgs, JSON_UNESCAPED_UNICODE));
        }

        // 4. Consultation box
        $consult_raw = get_post_meta($page_id, 'landing_packages_consultation', true);
        $consult_dec = !empty($consult_raw) ? json_decode($consult_raw, true) : null;
        if (empty($consult_dec) || empty($consult_dec['title'])) {
            $default_consult = array(
                "title"   => "Ba mẹ vẫn đang băn khoăn chưa biết gói nào phù hợp nhất?",
                "desc"    => "Chuyên gia Thiên Tâm sẽ lắng nghe độ tuổi và tình huống thực tế của con để gợi ý lộ trình phù hợp nhất.",
                "btn"     => "Tư vấn chọn gói phù hợp",
                "prefill" => "Chưa biết nên chọn gói nào"
            );
            $data['packages']['consultation'] = $default_consult;
            update_post_meta($page_id, 'landing_packages_consultation', wp_json_encode($default_consult, JSON_UNESCAPED_UNICODE));
            update_post_meta($page_id, 'landing_consultation_title', $default_consult['title']);
            update_post_meta($page_id, 'landing_consultation_desc', $default_consult['desc']);
            update_post_meta($page_id, 'landing_consultation_btn', $default_consult['btn']);
            update_post_meta($page_id, 'landing_consultation_prefill', $default_consult['prefill']);
        }

        // Cập nhật lại landing_custom_json
        update_post_meta($page_id, 'landing_custom_json', wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    return array(
        'data'          => $data,
        'is_customized' => $is_customized,
        'updated_at'    => $updated_at,
    );
}

/**
 * Hàm khởi tạo dữ liệu mặc định vào database cho Hiểu con
 */
function thientam_landing_init_hieu_con_de_dong_hanh($page_id)
{
    $defaults = thientam_get_landing_default_data('hieu-con-de-dong-hanh');
    if (empty($defaults)) {
        return false;
    }

    update_post_meta($page_id, 'landing_custom_json', wp_json_encode($defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    update_post_meta($page_id, 'landing_is_initialized', '1');
    update_post_meta($page_id, 'landing_last_saved', current_time('mysql'));

    return true;
}

// Giữ alias tương thích
if (! function_exists('thientam_init_landing_data_to_post_meta')) {
function thientam_init_landing_data_to_post_meta($page_id)
{
    return thientam_landing_init_hieu_con_de_dong_hanh($page_id);
}
}
