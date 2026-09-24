<?php
/**
 * REST API Data Mapper for 'Hiểu Mình'
 *
 * @package ThienTamData
 */

if (! defined('ABSPATH')) {
    exit;
}


function thientam_landing_get_data_hieu_minh($page_id, $slug)
{
    $data = array();
    $is_customized = false;
    $updated_at = null;

    $seed_defaults = thientam_landing_default_seed_hieu_minh();
    $defaults = function_exists('thientam_get_landing_default_data') ? thientam_get_landing_default_data('hieu-minh') : array();
    if (empty($defaults)) {
        $defaults = $seed_defaults;
    } else {
        $defaults = array_replace_recursive($seed_defaults, $defaults);
    }

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

        if (empty($data)) {
            $data = $defaults;
        } else {
            if (empty($data['serviceValues']) && ! empty($defaults['serviceValues'])) {
                $data['serviceValues'] = $defaults['serviceValues'];
            }
        }

        // 2. Ghi đè các trường post meta chuẩn của Hero
        $fields_map = array(
            'meta.title'                 => 'landing_meta_title',
            'meta.description'           => 'landing_meta_description',
            'navCta.label'               => 'landing_nav_cta_label',
            'navCta.href'                => 'landing_nav_cta_href',

            // Hero chuẩn
            'hero.kicker'                => 'landing_hero_kicker',
            'hero.titlePrefix'           => 'landing_hero_title_prefix',
            'hero.titleHighlight'        => 'landing_hero_title_highlight',
            'hero.leadHighlight'         => 'landing_hero_lead_highlight',
            'hero.lead'                  => 'landing_hero_lead',
            'hero.ctaPrimary'            => 'landing_hero_cta_primary',
            'hero.ctaSecondary'          => 'landing_hero_cta_secondary',
            'hero.ctaPrimaryHref'        => 'landing_hero_cta_primary_href',
            'hero.ctaSecondaryHref'      => 'landing_hero_cta_secondary_href',
            'hero.image'                 => 'landing_hero_image',
            'hero.imageAlt'              => 'landing_hero_image_alt',
            'hero.imageBadge'            => 'landing_hero_image_badge',
            'hero.quote.bold'            => 'landing_hero_quote_bold',
            'hero.quote.sub'             => 'landing_hero_quote_sub',

            // Perspectives
            'perspectives.eyebrow'        => 'landing_perspectives_eyebrow',
            'perspectives.titlePrefix'    => 'landing_perspectives_title_prefix',
            'perspectives.titleHighlight' => 'landing_perspectives_title_highlight',
            'perspectives.lead'           => 'landing_perspectives_lead',

            // Approach (Section 03: Cách tiếp cận / Triết lý)
            'approach.titlePrefix'        => 'landing_approach_title_prefix',
            'approach.titleHighlight'     => 'landing_approach_title_highlight',
            'approach.cardTitle'          => 'landing_approach_card_title',
            'approach.visualQuote'        => 'landing_approach_card_title',
            'approach.cardBadge'          => 'landing_approach_card_badge',
            'approach.visualLabel'        => 'landing_approach_card_badge',
            'approach.desc1'              => 'landing_approach_desc1',
            'approach.desc2'              => 'landing_approach_desc2',
            'approach.desc'               => 'landing_approach_desc1',
            'approach.image'              => 'landing_approach_image',
            'approach.imageAlt'           => 'landing_approach_image_alt',
            'approach.btn'                => 'landing_approach_btn',
            'approach.notice'             => 'landing_approach_notice',

            // Service Values (Section 04: Không chỉ là thông tin)
            'serviceValues.eyebrow'        => 'landing_service_values_eyebrow',
            'serviceValues.titlePrefix'    => 'landing_service_values_title_prefix',
            'serviceValues.titleHighlight' => 'landing_service_values_title_highlight',
            'serviceValues.lead'           => 'landing_service_values_lead',

            // Packages (Section 05: Bảng giá dịch vụ)
            'packages.titlePrefix'         => 'landing_packages_title_prefix',
            'packages.titleHighlight'      => 'landing_packages_title_highlight',
            'packages.lead'                => 'landing_packages_lead',
            'packages.btn'                 => 'landing_packages_btn',
            'packages.consultation.title'  => 'landing_consultation_title',
            'packages.consultation.desc'   => 'landing_consultation_desc',
            'packages.consultation.btn'    => 'landing_consultation_btn',
            'packages.consultation.prefill' => 'landing_consultation_prefill',

            // Package Guide (Section 06: Chưa biết chọn gói nào?)
            'packageGuide.eyebrow'        => 'landing_package_guide_eyebrow',
            'packageGuide.titlePrefix'    => 'landing_package_guide_title_prefix',
            'packageGuide.titleHighlight' => 'landing_package_guide_title_highlight',
            'packageGuide.lead'           => 'landing_package_guide_lead',

            // Why Thien Tam (Section 07: Vì sao là Thiên Tâm?)
            'whyThienTam.eyebrow'        => 'landing_why_thien_tam_eyebrow',
            'whyThienTam.titlePrefix'    => 'landing_why_thien_tam_title_prefix',
            'whyThienTam.titleHighlight' => 'landing_why_thien_tam_title_highlight',
            'whyThienTam.lead'           => 'landing_why_thien_tam_lead',

            // Expert (Section 08: Chuyên gia đồng hành)
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

            // Comparison (Section 09: So sánh nhanh)
            'comparison.eyebrow'        => 'landing_comparison_eyebrow',
            'comparison.titlePrefix'    => 'landing_comparison_title_prefix',
            'comparison.titleHighlight' => 'landing_comparison_title_highlight',
            'comparison.desc'           => 'landing_comparison_desc',
            'comparison.noteTitle'      => 'landing_comparison_note_title',
            'comparison.noteDesc'       => 'landing_comparison_note_desc',
            'comparison.btnText'        => 'landing_comparison_btn_text',
            'comparison.btnHref'        => 'landing_comparison_btn_href',

            // Process (Section 10: Quy trình luận giải)
            'process.titlePrefix'       => 'landing_process_title_prefix',
            'process.titleHighlight'    => 'landing_process_title_highlight',
            'process.lead'              => 'landing_process_lead',
            'process.notice'            => 'landing_process_notice',

            // Consultation Values (Section 12: Giá trị sau buổi tư vấn)
            'consultationValues.eyebrow'        => 'landing_consultation_values_eyebrow',
            'consultationValues.titlePrefix'    => 'landing_consultation_values_title_prefix',
            'consultationValues.titleHighlight' => 'landing_consultation_values_title_highlight',
            'consultationValues.lead'           => 'landing_consultation_values_lead',

            // FAQ (Section 13: Hỏi đáp thường gặp)
            'faq.titlePrefix'    => 'landing_faq_title_prefix',
            'faq.titleHighlight' => 'landing_faq_title_highlight',
            'faq.title'          => 'landing_faq_title',
            'faq.desc'           => 'landing_faq_desc',
            'faq.note'           => 'landing_faq_note',

            // Registration Form (Khối giới thiệu Form đăng ký - Cột trái)
            'form.titlePrefix'             => 'landing_form_title_prefix',
            'form.titleHighlight'          => 'landing_form_title_highlight',
            'form.titleSuffix'             => 'landing_form_title_suffix',
            'form.lead'                    => 'landing_form_lead',
            'form.securityCommitmentTitle' => 'landing_form_security_title',
            'form.securityCommitmentDesc'  => 'landing_form_security_desc',
        );

        foreach ($fields_map as $path => $mkey) {
            if (metadata_exists('post', $page_id, $mkey)) {
                $val = get_post_meta($page_id, $mkey, true);
                if ($val !== '') {
                    thientam_set_nested_array_value($data, $path, $val);
                    $is_customized = true;
                }
            }
        }

        // Tạo title tổng hợp nếu có prefix hoặc highlight
        $prefix = $data['hero']['titlePrefix'] ?? '';
        $hl     = $data['hero']['titleHighlight'] ?? '';
        if (! empty($prefix) || ! empty($hl)) {
            $data['hero']['title'] = trim($prefix . ' ' . $hl);
        }

        // Perspectives title tổng hợp
        $p_pre = $data['perspectives']['titlePrefix'] ?? '';
        $p_hl  = $data['perspectives']['titleHighlight'] ?? '';
        if (! empty($p_pre) || ! empty($p_hl)) {
            $data['perspectives']['title'] = trim($p_pre . ' ' . $p_hl);
        }

        // Approach title tổng hợp
        $app_pre = $data['approach']['titlePrefix'] ?? '';
        $app_hl  = $data['approach']['titleHighlight'] ?? '';
        if (! empty($app_pre) || ! empty($app_hl)) {
            $data['approach']['title'] = trim($app_pre . ' ' . $app_hl);
        }

        // Service Values title tổng hợp
        $sv_pre = $data['serviceValues']['titlePrefix'] ?? '';
        $sv_hl  = $data['serviceValues']['titleHighlight'] ?? '';
        if (! empty($sv_pre) || ! empty($sv_hl)) {
            $data['serviceValues']['title'] = trim($sv_pre . ' ' . $sv_hl);
        }

        // Packages title tổng hợp
        $pkg_pre = $data['packages']['titlePrefix'] ?? '';
        $pkg_hl  = $data['packages']['titleHighlight'] ?? '';
        if (! empty($pkg_pre) || ! empty($pkg_hl)) {
            $data['packages']['title'] = trim($pkg_pre . ' ' . $pkg_hl);
        }

        // Package Guide title tổng hợp
        $pg_pre = $data['packageGuide']['titlePrefix'] ?? '';
        $pg_hl  = $data['packageGuide']['titleHighlight'] ?? '';
        if (! empty($pg_pre) || ! empty($pg_hl)) {
            $data['packageGuide']['title'] = trim($pg_pre . ' ' . $pg_hl);
        }

        // Why Thien Tam title tổng hợp
        $wtt_pre = $data['whyThienTam']['titlePrefix'] ?? '';
        $wtt_hl  = $data['whyThienTam']['titleHighlight'] ?? '';
        if (! empty($wtt_pre) || ! empty($wtt_hl)) {
            $data['whyThienTam']['title'] = trim($wtt_pre . ' ' . $wtt_hl);
        }

        // Expert title tổng hợp
        $ex_pre = $data['expert']['titlePrefix'] ?? '';
        $ex_hl  = $data['expert']['titleHighlight'] ?? '';
        if (! empty($ex_pre) || ! empty($ex_hl)) {
            $data['expert']['title'] = trim($ex_pre . ' ' . $ex_hl);
        }

        // Comparison title tổng hợp
        $comp_pre = $data['comparison']['titlePrefix'] ?? '';
        $comp_hl  = $data['comparison']['titleHighlight'] ?? '';
        if (! empty($comp_pre) || ! empty($comp_hl)) {
            $data['comparison']['title'] = trim($comp_pre . ' ' . $comp_hl);
        }

        // Process title tổng hợp
        $proc_pre = $data['process']['titlePrefix'] ?? '';
        $proc_hl  = $data['process']['titleHighlight'] ?? '';
        if (! empty($proc_pre) || ! empty($proc_hl)) {
            $data['process']['title'] = trim($proc_pre . ' ' . $proc_hl);
        }

        // Consultation Values title tổng hợp
        $cv_pre = $data['consultationValues']['titlePrefix'] ?? '';
        $cv_hl  = $data['consultationValues']['titleHighlight'] ?? '';
        if (! empty($cv_pre) || ! empty($cv_hl)) {
            $data['consultationValues']['title'] = trim($cv_pre . ' ' . $cv_hl);
        }

        // FAQ title tổng hợp
        $faq_pre = $data['faq']['titlePrefix'] ?? '';
        $faq_hl  = $data['faq']['titleHighlight'] ?? '';
        if (! empty($faq_pre) || ! empty($faq_hl)) {
            $data['faq']['title'] = trim($faq_pre . ' ' . $faq_hl);
        }

        // Form title tổng hợp
        $form_pre = $data['form']['titlePrefix'] ?? '';
        $form_hl  = $data['form']['titleHighlight'] ?? '';
        $form_suf = $data['form']['titleSuffix'] ?? '';
        if (! empty($form_pre) || ! empty($form_hl) || ! empty($form_suf)) {
            $data['form']['title'] = trim(trim($form_pre . ' ' . $form_hl) . ' ' . $form_suf);
        }

        // Approach Pills & Points
        $app_pills_raw = get_post_meta($page_id, 'landing_approach_pills', true);
        if (! empty($app_pills_raw)) {
            $dec_app_pills = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($app_pills_raw) : json_decode($app_pills_raw, true);
            if (is_array($dec_app_pills)) {
                $data['approach']['pills'] = array_values($dec_app_pills);
                $is_customized = true;
            }
        }
        $app_points_raw = get_post_meta($page_id, 'landing_approach_points', true);
        if (! empty($app_points_raw)) {
            $dec_app_pts = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($app_points_raw) : json_decode($app_points_raw, true);
            if (is_array($dec_app_pts)) {
                $data['approach']['points'] = array_values($dec_app_pts);
                $is_customized = true;
            }
        }

        // Expert Pills & Direct Value
        $expert_pills_raw = get_post_meta($page_id, 'landing_expert_pills', true);
        if (! empty($expert_pills_raw)) {
            $dec_pills = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($expert_pills_raw) : json_decode($expert_pills_raw, true);
            if (is_array($dec_pills)) {
                $data['expert']['pills'] = array_values($dec_pills);
                $is_customized = true;
            }
        }

        $dv_c2_items_raw = get_post_meta($page_id, 'landing_expert_dv_card2_items', true);
        if (! empty($dv_c2_items_raw)) {
            $dec_items = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($dv_c2_items_raw) : json_decode($dv_c2_items_raw, true);
            if (is_array($dec_items)) {
                $data['expert']['directValue']['card2']['items'] = array_values($dec_items);
                $is_customized = true;
            }
        }

        $sdv_raw = get_post_meta($page_id, 'landing_expert_show_direct_value', true);
        if ($sdv_raw !== '') {
            $data['expert']['showDirectValue'] = ($sdv_raw === '1' || $sdv_raw === 1 || $sdv_raw === true || $sdv_raw === 'true');
            $is_customized = true;
        } else {
            $data['expert']['showDirectValue'] = ! empty($data['expert']['showDirectValue']);
        }

        // Xử lý cards của perspectives
        $cards_raw = get_post_meta($page_id, 'landing_perspectives_cards', true);
        if (! empty($cards_raw)) {
            $decoded_cards = is_array($cards_raw) ? $cards_raw : json_decode($cards_raw, true);
            if (is_array($decoded_cards) && ! empty($decoded_cards)) {
                $data['perspectives']['cards'] = array_values($decoded_cards);
                $is_customized = true;
            }
        }

        // Xử lý cards của serviceValues (Không chỉ là thông tin)
        $sv_cards_raw = get_post_meta($page_id, 'landing_service_values_cards', true);
        if (! empty($sv_cards_raw)) {
            $decoded_sv_cards = is_array($sv_cards_raw) ? $sv_cards_raw : json_decode($sv_cards_raw, true);
            if (is_array($decoded_sv_cards) && ! empty($decoded_sv_cards)) {
                $data['serviceValues']['cards'] = array_values($decoded_sv_cards);
                $is_customized = true;
            }
        }

        // Xử lý cards của packageGuide (Chưa biết chọn gói nào)
        $pg_cards_raw = get_post_meta($page_id, 'landing_package_guide_cards', true);
        if (! empty($pg_cards_raw)) {
            $decoded_pg_cards = is_array($pg_cards_raw) ? $pg_cards_raw : json_decode($pg_cards_raw, true);
            if (is_array($decoded_pg_cards) && ! empty($decoded_pg_cards)) {
                $data['packageGuide']['cards'] = array_values($decoded_pg_cards);
                $is_customized = true;
            }
        }

        // Xử lý cards của whyThienTam (Vì sao là Thiên Tâm)
        $wtt_cards_raw = get_post_meta($page_id, 'landing_why_thien_tam_cards', true);
        if (! empty($wtt_cards_raw)) {
            $decoded_wtt_cards = is_array($wtt_cards_raw) ? $wtt_cards_raw : json_decode($wtt_cards_raw, true);
            if (is_array($decoded_wtt_cards) && ! empty($decoded_wtt_cards)) {
                $data['whyThienTam']['cards'] = array_values($decoded_wtt_cards);
                $is_customized = true;
            }
        }

        // Xử lý cards của consultationValues (Giá trị sau buổi tư vấn)
        $cv_cards_raw = get_post_meta($page_id, 'landing_consultation_values_cards', true);
        if (! empty($cv_cards_raw)) {
            $decoded_cv_cards = is_array($cv_cards_raw) ? $cv_cards_raw : json_decode($cv_cards_raw, true);
            if (is_array($decoded_cv_cards) && ! empty($decoded_cv_cards)) {
                $data['consultationValues']['cards'] = array_values($decoded_cv_cards);
                $is_customized = true;
            }
        }

        // Xử lý packages items (Section 05: Bảng giá dịch vụ)
        $pkg_items_raw = get_post_meta($page_id, 'landing_packages_items', true);
        if (! empty($pkg_items_raw)) {
            $decoded_pkg = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($pkg_items_raw) : (is_array($pkg_items_raw) ? $pkg_items_raw : json_decode(stripslashes($pkg_items_raw), true));
            if (is_array($decoded_pkg) && ! empty($decoded_pkg)) {
                $data['packages']['items'] = array_values($decoded_pkg);
                $is_customized = true;
            }
        }

        // Xử lý packages consultation banner
        $consult_raw = get_post_meta($page_id, 'landing_packages_consultation', true);
        if (! empty($consult_raw)) {
            $decoded_consult = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($consult_raw) : (is_array($consult_raw) ? $consult_raw : json_decode(stripslashes($consult_raw), true));
            if (is_array($decoded_consult) && ! empty($decoded_consult)) {
                $data['packages']['consultation'] = $decoded_consult;
                $is_customized = true;
            }
        }

        // Xử lý Comparison columns & rows (Section 09: So sánh nhanh)
        $cols = array();
        $col_ids = array('can-ban', 'chuyen-sau', 'cung-cg', 'toan-dien');
        for ($i = 0; $i < 4; $i++) {
            $cname = get_post_meta($page_id, "landing_comparison_col{$i}_name", true);
            $cprice = get_post_meta($page_id, "landing_comparison_col{$i}_price", true);
            $c_name = $cname !== '' ? $cname : ($data['comparison']['columns'][$i]['name'] ?? '');
            $c_price = $cprice !== '' ? $cprice : ($data['comparison']['columns'][$i]['price'] ?? '');
            if ($c_name !== '' || $c_price !== '') {
                $cols[] = array(
                    'id'    => $col_ids[$i],
                    'name'  => $c_name,
                    'price' => $c_price,
                );
            }
        }
        if (! empty($cols)) {
            $data['comparison']['columns'] = $cols;
        }

        $comp_rows_raw = get_post_meta($page_id, 'landing_comparison_rows', true);
        if (! empty($comp_rows_raw)) {
            $decoded_comp_rows = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($comp_rows_raw) : (is_array($comp_rows_raw) ? $comp_rows_raw : json_decode(stripslashes($comp_rows_raw), true));
            if (is_array($decoded_comp_rows) && ! empty($decoded_comp_rows)) {
                $data['comparison']['rows'] = array_values($decoded_comp_rows);
                $is_customized = true;
            }
        }

        // Xử lý steps của Process (Section 10: Quy trình luận giải)
        $proc_steps_raw = get_post_meta($page_id, 'landing_process_steps', true);
        if (! empty($proc_steps_raw)) {
            $decoded_proc = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($proc_steps_raw) : (is_array($proc_steps_raw) ? $proc_steps_raw : json_decode(stripslashes($proc_steps_raw), true));
            if (is_array($decoded_proc) && ! empty($decoded_proc)) {
                $data['process']['steps'] = array_values($decoded_proc);
                $is_customized = true;
            }
        }

        // Xử lý FAQ items (Section 13: Hỏi đáp thường gặp)
        $faq_items_raw = get_post_meta($page_id, 'landing_faq_items', true);
        if (! empty($faq_items_raw)) {
            $decoded_faq = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($faq_items_raw) : (is_array($faq_items_raw) ? $faq_items_raw : json_decode(stripslashes($faq_items_raw), true));
            if (is_array($decoded_faq) && ! empty($decoded_faq)) {
                $data['faq']['items'] = array_values($decoded_faq);
                $is_customized = true;
            }
        }

        // Xử lý chips của hero (từng dòng)
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

        // Keywords
        $keywords_raw = get_post_meta($page_id, 'landing_meta_keywords', true);
        if (! empty($keywords_raw)) {
            $kws = array_filter(array_map('trim', explode(',', $keywords_raw)));
            if (! empty($kws)) {
                $data['meta']['keywords'] = array_values($kws);
                $is_customized = true;
            }
        }

        // OG Image & Thumbnail
        $custom_og = get_post_meta($page_id, 'landing_meta_og_image', true);
        $thumb_id = get_post_thumbnail_id($page_id);
        $wp_thumb = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'full') : '';
        $placeholder_og = 'https://site.thientam68.com/wp-content/uploads/2026/08/Placeholder-Thien-Tam.png';
        $final_og = $custom_og ?: ($wp_thumb ?: (!empty($data['meta']['ogImage']) ? $data['meta']['ogImage'] : (!empty($data['meta']['thumbnail']) ? $data['meta']['thumbnail'] : $placeholder_og)));

        if (! empty($wp_thumb)) {
            $data['meta']['thumbnail'] = $wp_thumb;
            $data['thumbnail'] = $wp_thumb;
        }
        $data['meta']['ogImage'] = $final_og;

        // Feedback (Hình ảnh phản hồi)
        if (! isset($data['feedback']) || ! is_array($data['feedback'])) {
            $data['feedback'] = array(
                'titlePrefix'    => '',
                'titleHighlight' => '',
                'desc'           => '',
                'items'          => array(),
            );
        }
        if (metadata_exists('post', $page_id, 'landing_feedback_title_prefix')) {
            $data['feedback']['titlePrefix'] = get_post_meta($page_id, 'landing_feedback_title_prefix', true);
        }
        if (metadata_exists('post', $page_id, 'landing_feedback_title_highlight')) {
            $data['feedback']['titleHighlight'] = get_post_meta($page_id, 'landing_feedback_title_highlight', true);
        }
        if (metadata_exists('post', $page_id, 'landing_feedback_desc')) {
            $data['feedback']['desc'] = get_post_meta($page_id, 'landing_feedback_desc', true);
        }
        $fb_raw = get_post_meta($page_id, 'landing_feedback_items', true);
        if (! empty($fb_raw)) {
            $decoded_fb = is_array($fb_raw) ? $fb_raw : json_decode($fb_raw, true);
            if (is_array($decoded_fb) && ! empty($decoded_fb)) {
                $data['feedback']['items'] = array_values($decoded_fb);
                $is_customized = true;
            }
        }
    } else {
        $data = $defaults;
    }

    return array(
        'data'          => $data,
        'is_customized' => $is_customized,
        'updated_at'    => $updated_at,
    );
}

function thientam_landing_default_seed_hieu_minh()
{
    return array(
        'meta' => array(
            'title'       => 'Tư Vấn Tử Vi Ứng Dụng & Thấu Hiểu Bản Thân | Thiên Tâm',
            'description' => 'Khám phá tiềm năng, giải mã định hướng cuộc sống và đưa ra quyết định vững vàng cùng chuyên gia Thiên Tâm qua góc nhìn Tử Vi khoa học, nhân văn.',
            'keywords'    => array('tử vi ứng dụng', 'thấu hiểu bản thân', 'định hướng cuộc đời', 'thiên tâm tử vi'),
        ),
        'navCta' => array(
            'label' => 'Đăng ký tư vấn',
            'href'  => '#dang-ky',
        ),
        'hero' => array(
            'kicker'         => 'GÓC NHÌN ĐA CHIỀU & KHOA HỌC TỪ TỬ VI ỨNG DỤNG',
            'titlePrefix'    => 'Thấu hiểu bản thân để',
            'titleHighlight' => 'chủ động kiến tạo tương lai',
            'leadHighlight'  => 'Không phán đoán áp đặt,',
            'lead'           => 'Thiên Tâm đồng hành giúp bạn soi sáng bức tranh nội tại, hiểu rõ điểm mạnh yếu và nắm bắt thời điểm quan trọng trong cuộc đời.',
            'ctaPrimary'     => 'Đăng ký tư vấn',
            'ctaSecondary'   => 'Tìm hiểu phương pháp',
            'ctaPrimaryHref' => '#dang-ky',
            'ctaSecondaryHref' => '#cach-tiep-can',
            'image'          => '/images/brand-showcase.png',
            'imageAlt'       => 'Tư vấn thấu hiểu bản thân Thiên Tâm',
            'imageBadge'     => 'Tư vấn 1-1 cùng Chuyên gia',
            'quote'          => array(
                'bold' => 'Lá số là bản đồ định hướng,',
                'sub'  => 'bạn là người cầm lái cuộc đời mình.',
            ),
            'chips'          => array(
                'Thấu hiểu tính cách & tiềm năng ẩn giấu',
                'Nhận diện các mốc chuyển vận quan trọng',
                'Định hướng sự nghiệp & các mối quan hệ',
                'Tư vấn trực tiếp, bảo mật tuyệt đối 100%',
            ),
        ),
        'approach' => array(
            'titlePrefix'    => 'Không phán đoán —',
            'titleHighlight' => 'mà giúp bạn hiểu mình sâu hơn',
            'cardTitle'      => 'Lá số không thay bạn lựa chọn.',
            'cardBadge'      => 'Góc nhìn Thiên Tâm',
            'desc1'          => 'Thiên Tâm không hướng khách hàng phụ thuộc vào một lời luận giải, mà xem Tử Vi như một góc nhìn hỗ trợ cho quá trình thấu hiểu bản thân và lựa chọn.',
            'desc2'          => 'Giá trị của việc luận giải không nằm ở việc nghe một câu "đúng" hay "sai", "tốt" hay "xấu". Điều Thiên Tâm hướng đến là giúp bạn hiểu mình, hiểu hoàn cảnh, nhận diện xu hướng và hiểu thời điểm để chủ động hơn trước lựa chọn của chính mình.',
            'image'          => '/images/brand-showcase.png',
            'imageAlt'       => 'Lá số không thay bạn lựa chọn.',
            'btn'            => 'Tìm hiểu thêm',
            'pills'          => array(
                'Hiểu mình và hoàn cảnh thực tế',
                'Nhận diện xu hướng và tiềm năng',
                'Hiểu đúng thời điểm để chủ động',
                'Không phán đoán áp đặt một chiều',
            ),
        ),
    );
}

function thientam_landing_init_hieu_minh($page_id)
{
    $seed_data = thientam_landing_default_seed_hieu_minh();

    $custom_json_raw = get_post_meta($page_id, 'landing_custom_json', true);
    if (! empty($custom_json_raw)) {
        $existing = json_decode($custom_json_raw, true);
        if (is_array($existing)) {
            $seed_data = array_replace_recursive($seed_data, $existing);
        }
    }

    update_post_meta($page_id, 'landing_custom_json', wp_json_encode($seed_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    update_post_meta($page_id, 'landing_is_initialized', '1');
    update_post_meta($page_id, 'landing_last_saved', current_time('mysql'));

    return true;
}
