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
        $data = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($custom_json_raw) : array();
        if (! empty($data)) {
            $is_customized = true;
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
            'expert.directValue'         => 'landing_expert_direct_value',
            'process.steps'              => 'landing_process_steps',
            'trust.items'                => 'landing_trust_items',
            'faq.items'                  => 'landing_faq_items',
            'finalCta'                   => 'landing_final_cta',
            'nav'                        => 'landing_nav',
            'navCta'                     => 'landing_nav_cta',
        );
        foreach ($sections_map as $path => $mkey) {
            if (metadata_exists('post', $page_id, $mkey)) {
                $raw = get_post_meta($page_id, $mkey, true);
                if (! empty($raw)) {
                    $decoded = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($raw) : (is_array($raw) ? $raw : json_decode(stripslashes($raw), true));
                    if (is_array($decoded) && ! empty($decoded)) {
                        thientam_set_nested_array_value($data, $path, $decoded);
                        $is_customized = true;
                    }
                }
            }
        }

        // Đảm bảo packages.items nạp từ post meta nếu data chưa có
        $pkg_meta = get_post_meta($page_id, 'landing_packages_items', true);
        $pkg_dec = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($pkg_meta) : (is_array($pkg_meta) ? $pkg_meta : json_decode(stripslashes($pkg_meta), true));
        if (! empty($pkg_dec) && is_array($pkg_dec)) {
            $data['packages']['items'] = $pkg_dec;
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
            'hero.ctaPrimaryHref'                  => 'landing_hero_cta_primary_href',
            'hero.ctaSecondaryHref'                => 'landing_hero_cta_secondary_href',
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
            'expert.titlePrefix'                  => 'landing_expert_title_prefix',
            'expert.titleHighlight'               => 'landing_expert_title_highlight',
            'expert.avatar'                        => 'landing_expert_avatar',
            'expert.name'                          => 'landing_expert_name',
            'expert.badge'                         => 'landing_expert_badge',
            'expert.desc'                          => 'landing_expert_desc',
            'expert.desc1'                         => 'landing_expert_desc1',
            'expert.desc2'                         => 'landing_expert_desc2',
            'expert.notice'                        => 'landing_expert_notice',
            'expert.btn'                           => 'landing_expert_btn',
            'expert.btnHref'                       => 'landing_expert_btn_href',
            'expert.anchor'                        => 'landing_expert_btn_href',
            'expert.directValue.card1.eyebrow'     => 'landing_expert_dv_card1_eyebrow',
            'expert.directValue.card1.title'       => 'landing_expert_dv_card1_title',
            'expert.directValue.card1.desc'        => 'landing_expert_dv_card1_desc',
            'expert.directValue.card2.title'       => 'landing_expert_dv_card2_title',

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
            'faq.titlePrefix'                      => 'landing_faq_title_prefix',
            'faq.titleHighlight'                   => 'landing_faq_title_highlight',
            'faq.desc'                             => 'landing_faq_desc',
            'faq.note'                             => 'landing_faq_note',

            // Final CTA
            'finalCta.titlePrefix'                 => 'landing_final_title_prefix',
            'finalCta.titleHighlight'              => 'landing_final_title_highlight',
            'finalCta.lead'                        => 'landing_final_lead',
            'finalCta.statement'                   => 'landing_final_statement',

            // Registration Form (Khối giới thiệu Form đăng ký - Cột trái)
            'form.titlePrefix'                     => 'landing_form_title_prefix',
            'form.titleHighlight'                  => 'landing_form_title_highlight',
            'form.titleSuffix'                     => 'landing_form_title_suffix',
            'form.lead'                            => 'landing_form_lead',
            'form.securityCommitmentTitle'         => 'landing_form_security_title',
            'form.securityCommitmentDesc'          => 'landing_form_security_desc',
        );

        foreach ($fields_map as $json_path => $meta_key) {
            if (metadata_exists('post', $page_id, $meta_key)) {
                $val = get_post_meta($page_id, $meta_key, true);
                if (is_string($val) && (strpos($val, 'Undefined variable') !== false || strpos($val, 'Warning') !== false)) {
                    $val = '';
                }
                // Nếu trên admin rỗng thì api trả về rỗng luôn
                thientam_set_nested_array_value($data, $json_path, $val !== false && $val !== null ? $val : '');
                $is_customized = true;
            }
        }

        // Xử lý entryDoors (Cánh cửa): tự count number theo item 01, 02, 03... và backward-compatible
        if (! empty($data['entryDoors']) && is_array($data['entryDoors'])) {
            $doors_list = array();
            if (! empty($data['entryDoors']['items']) && is_array($data['entryDoors']['items'])) {
                $doors_list = array_values($data['entryDoors']['items']);
            } elseif (! empty($data['entryDoors']['door1']) || ! empty($data['entryDoors']['door2'])) {
                if (! empty($data['entryDoors']['door1'])) $doors_list[] = $data['entryDoors']['door1'];
                if (! empty($data['entryDoors']['door2'])) $doors_list[] = $data['entryDoors']['door2'];
            }
            foreach ($doors_list as $di => &$d_item) {
                $d_item['number'] = sprintf('%02d', $di + 1);
            }
            unset($d_item);
            $data['entryDoors']['items'] = $doors_list;
            if (isset($doors_list[0])) $data['entryDoors']['door1'] = $doors_list[0];
            if (isset($doors_list[1])) $data['entryDoors']['door2'] = $doors_list[1];
        }



        // Xử lý items của directValue card2
        $dv_c2_items_raw = get_post_meta($page_id, 'landing_expert_dv_card2_items', true);
        if (! empty($dv_c2_items_raw)) {
            $dec_items = json_decode($dv_c2_items_raw, true);
            if (is_array($dec_items) && ! empty($dec_items)) {
                $data['expert']['directValue']['card2']['items'] = array_values($dec_items);
            }
        }

        $sdv_raw = get_post_meta($page_id, 'landing_expert_show_direct_value', true);
        if ($sdv_raw !== '') {
            $data['expert']['showDirectValue'] = ($sdv_raw === '1' || $sdv_raw === 1 || $sdv_raw === true || $sdv_raw === 'true');
            $is_customized = true;
        } else {
            $data['expert']['showDirectValue'] = ! empty($data['expert']['showDirectValue']);
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

        // Xử lý highlights của form đăng ký (từng dòng)
        $form_hl_raw = get_post_meta($page_id, 'landing_form_highlights', true);
        if (! empty($form_hl_raw)) {
            $form_chips = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $form_hl_raw))));
            if (! empty($form_chips)) {
                $data['form']['highlights'] = array_values($form_chips);
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

    // 4. Fallback chỉ khi trang chưa từng được khởi tạo trong Database
    if (empty($data) && ! metadata_exists('post', $page_id, 'landing_is_initialized')) {
        $data = thientam_get_landing_default_data($slug) ?: array();
    }

    // Luôn đồng bộ dữ liệu packages.items vào landing_custom_json trong Database
    if ($page_id && ! empty($data['packages']['items'])) {
        $stored_json = get_post_meta($page_id, 'landing_custom_json', true);
        $stored_arr = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($stored_json) : array();
        if (! is_array($stored_arr)) $stored_arr = array();
        if (empty($stored_arr['packages']['items'])) {
            $stored_arr['packages']['items'] = $data['packages']['items'];
            update_post_meta($page_id, 'landing_custom_json', wp_slash(wp_json_encode($stored_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)));
        }
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

