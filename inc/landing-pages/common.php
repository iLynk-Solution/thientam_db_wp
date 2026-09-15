<?php
/**
 * Common Helpers & Supported Landing Pages Registry
 *
 * @package ThienTamData
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('thientam_get_supported_landing_pages')) {
function thientam_get_supported_landing_pages()
{
    return array(
        'hieu-con-de-dong-hanh' => array(
            'name'        => 'Hiểu Con Để Đồng Hành',
            'slug'        => 'hieu-con-de-dong-hanh',
            'route'       => '/landing/hieu-con-de-dong-hanh',
            'suggest_uri' => '/landing/hieu-con-de-dong-hanh',
            'dir'         => __DIR__ . '/hieu-con-de-dong-hanh',
        ),
        'hieu-minh' => array(
            'name'        => 'Hiểu Mình',
            'slug'        => 'hieu-minh',
            'route'       => '/landing/hieu-minh',
            'suggest_uri' => '/landing/hieu-minh',
            'dir'         => __DIR__ . '/hieu-minh',
        ),
        // Có thể mở rộng thêm các trang Landing Page mới tại đây
    );
}
}

if (! function_exists('thientam_get_landing_page_id')) {
function thientam_get_landing_page_id($slug = 'hieu-con-de-dong-hanh')
{
    $cache_key = 'thientam_landing_page_id_' . $slug;
    $page_id = wp_cache_get($cache_key, 'thientam');
    if ($page_id !== false) {
        return (int) $page_id;
    }

    $q = new WP_Query(array(
        'post_type'      => 'page',
        'posts_per_page' => 1,
        'post_status'    => array('publish', 'draft', 'pending', 'private'),
        'meta_query'     => array(
            array(
                'key'     => 'thientam_landing_type',
                'value'   => $slug,
                'compare' => '=',
            ),
        ),
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ));

    if (! empty($q->posts)) {
        $page_id = (int) $q->posts[0];
        wp_cache_set($cache_key, $page_id, 'thientam', 3600);
        return $page_id;
    }

    $p = get_page_by_path($slug, OBJECT, 'page');
    if ($p) {
        $page_id = (int) $p->ID;
        wp_cache_set($cache_key, $page_id, 'thientam', 3600);
        return $page_id;
    }

    return null;
}
}

if (! function_exists('thientam_decode_json_meta')) {
/**
 * Giải mã JSON an toàn từ WordPress post_meta (tự động xử lý cả chuỗi đã slash hoặc chưa slash)
 */
function thientam_decode_json_meta($raw)
{
    if (empty($raw)) {
        return array();
    }
    if (is_array($raw)) {
        return $raw;
    }
    if (! is_string($raw)) {
        return array();
    }

    $decoded = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return $decoded;
    }

    $decoded = json_decode(stripslashes($raw), true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return $decoded;
    }

    if (function_exists('wp_unslash')) {
        $decoded = json_decode(wp_unslash($raw), true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
    }

    return array();
}
}

if (! function_exists('thientam_get_landing_default_data')) {
function thientam_get_landing_default_data($slug = 'hieu-con-de-dong-hanh')
{
    static $cache = array();
    if (isset($cache[$slug])) {
        return $cache[$slug];
    }

    $page_id = function_exists('thientam_get_landing_page_id') ? thientam_get_landing_page_id($slug) : null;
    if ($page_id) {
        $custom_json = get_post_meta($page_id, 'landing_custom_json', true);
        if (! empty($custom_json)) {
            $decoded = thientam_decode_json_meta($custom_json);
            if (! empty($decoded)) {
                $cache[$slug] = $decoded;
                return $decoded;
            }
        }
    }

    return array();
}
}

if (! function_exists('thientam_set_nested_array_value')) {
function thientam_set_nested_array_value(&$arr, $path, $value)
{
    $keys = explode('.', $path);
    $temp = &$arr;

    foreach ($keys as $k) {
        if (! isset($temp[$k]) || ! is_array($temp[$k])) {
            $temp[$k] = array();
        }
        $temp = &$temp[$k];
    }

    $temp = $value;
}
}

if (! function_exists('thientam_landing_save_seo_meta')) {
/**
 * Xử lý lưu và đồng bộ SEO Meta & Thumbnail dùng chung cho mọi Landing Page
 *
 * @param int $post_id
 * @param array &$defaults Mảng dữ liệu JSON tổng hợp của landing page (truyền theo tham chiếu)
 */
function thientam_landing_save_seo_meta($post_id, &$defaults)
{
    if (! is_array($defaults)) {
        $defaults = array();
    }
    if (! isset($defaults['meta']) || ! is_array($defaults['meta'])) {
        $defaults['meta'] = array();
    }

    // 1. Meta Title
    if (isset($_POST['landing_meta_title'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_meta_title']));
        update_post_meta($post_id, 'landing_meta_title', $val);
        $defaults['meta']['title'] = $val;
    }

    // 2. Meta Description
    if (isset($_POST['landing_meta_description'])) {
        $val = sanitize_textarea_field(wp_unslash($_POST['landing_meta_description']));
        update_post_meta($post_id, 'landing_meta_description', $val);
        $defaults['meta']['description'] = $val;
    }

    // 3. Meta Keywords
    if (isset($_POST['landing_meta_keywords'])) {
        $raw = wp_unslash($_POST['landing_meta_keywords']);
        update_post_meta($post_id, 'landing_meta_keywords', sanitize_text_field($raw));
        $kws = array_filter(array_map('trim', explode(',', $raw)));
        $defaults['meta']['keywords'] = array_values($kws);
    }

    // 4. Tự động đồng bộ Thumbnail WP (Featured Image)
    $wp_thumb_url = get_the_post_thumbnail_url($post_id, 'full');
    if (! empty($wp_thumb_url)) {
        $defaults['meta']['thumbnail'] = $wp_thumb_url;
        $defaults['thumbnail'] = $wp_thumb_url;
        $defaults['meta']['image'] = $wp_thumb_url;
    }

    // 5. Header Nav CTA (đồng bộ cùng tab SEO & Header)
    if (isset($_POST['landing_nav_cta_label'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_nav_cta_label']));
        update_post_meta($post_id, 'landing_nav_cta_label', $val);
        if (! isset($defaults['navCta']) || ! is_array($defaults['navCta'])) {
            $defaults['navCta'] = array();
        }
        $defaults['navCta']['label'] = $val;
    }
    if (isset($_POST['landing_nav_cta_href'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_nav_cta_href']));
        update_post_meta($post_id, 'landing_nav_cta_href', $val);
        if (! isset($defaults['navCta']) || ! is_array($defaults['navCta'])) {
            $defaults['navCta'] = array();
        }
        $defaults['navCta']['href'] = $val;
    }
}
}

if (! function_exists('thientam_landing_save_perspectives_meta')) {
/**
 * Xử lý lưu và đồng bộ Perspectives (Góc nhìn đa chiều) dùng chung cho mọi Landing Page
 *
 * @param int $post_id
 * @param array &$defaults Mảng dữ liệu JSON tổng hợp của landing page (truyền theo tham chiếu)
 */
function thientam_landing_save_perspectives_meta($post_id, &$defaults)
{
    if (! is_array($defaults)) {
        $defaults = array();
    }
    if (! isset($defaults['perspectives']) || ! is_array($defaults['perspectives'])) {
        $defaults['perspectives'] = array();
    }

    // 0. Eyebrow (Dòng chữ nhỏ trên đầu)
    if (isset($_POST['landing_perspectives_eyebrow'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_perspectives_eyebrow']));
        update_post_meta($post_id, 'landing_perspectives_eyebrow', $val);
        $defaults['perspectives']['eyebrow'] = $val;
    }

    // 1. Tiêu đề đầu (prefix)
    if (isset($_POST['landing_perspectives_title_prefix'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_perspectives_title_prefix']));
        update_post_meta($post_id, 'landing_perspectives_title_prefix', $val);
        $defaults['perspectives']['titlePrefix'] = $val;
    }

    // 2. Tiêu đề nổi bật (highlight)
    if (isset($_POST['landing_perspectives_title_highlight'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_perspectives_title_highlight']));
        update_post_meta($post_id, 'landing_perspectives_title_highlight', $val);
        $defaults['perspectives']['titleHighlight'] = $val;
    }

    // 3. Tiêu đề tổng hợp
    $pre = $defaults['perspectives']['titlePrefix'] ?? (get_post_meta($post_id, 'landing_perspectives_title_prefix', true) ?: '');
    $hl  = $defaults['perspectives']['titleHighlight'] ?? (get_post_meta($post_id, 'landing_perspectives_title_highlight', true) ?: '');
    if ($pre !== '' || $hl !== '') {
        $defaults['perspectives']['title'] = trim($pre . ' ' . $hl);
    }

    // 4. Lời dẫn (lead)
    if (isset($_POST['landing_perspectives_lead'])) {
        $val = sanitize_textarea_field(wp_unslash($_POST['landing_perspectives_lead']));
        update_post_meta($post_id, 'landing_perspectives_lead', $val);
        $defaults['perspectives']['lead'] = $val;
    }

    // 5. Thẻ Góc nhìn (cards) với Lucide Icon
    if (isset($_POST['persp_cards']) && is_array($_POST['persp_cards'])) {
        $new_cards = array();
        foreach ($_POST['persp_cards'] as $c) {
            $t  = sanitize_text_field(wp_unslash($c['title'] ?? ''));
            $d  = sanitize_textarea_field(wp_unslash($c['desc'] ?? ''));
            $ic = sanitize_key(wp_unslash($c['icon'] ?? 'sun'));
            if ($t !== '' || $d !== '') {
                $new_cards[] = array(
                    'title' => $t,
                    'desc'  => $d,
                    'icon'  => $ic,
                );
            }
        }
        $defaults['perspectives']['cards'] = $new_cards;
        update_post_meta($post_id, 'landing_perspectives_cards', wp_json_encode($new_cards, JSON_UNESCAPED_UNICODE));
    }
}
}

if (! function_exists('thientam_landing_save_packages_meta')) {
/**
 * Xử lý lưu và đồng bộ Packages (Bảng giá dịch vụ) dùng chung
 *
 * @param int $post_id
 * @param array &$defaults
 */
function thientam_landing_save_packages_meta($post_id, &$defaults)
{
    if (! is_array($defaults)) {
        $defaults = array();
    }
    if (! isset($defaults['packages']) || ! is_array($defaults['packages'])) {
        $defaults['packages'] = array();
    }

    $pkg_fields = array(
        'landing_packages_title_prefix',
        'landing_packages_title_highlight',
        'landing_packages_lead',
        'landing_packages_btn',
    );
    foreach ($pkg_fields as $f) {
        if (isset($_POST[$f])) {
            $val = wp_unslash($_POST[$f]);
            $val = ($f === 'landing_packages_lead') ? sanitize_textarea_field($val) : sanitize_text_field($val);
            update_post_meta($post_id, $f, $val);
        }
    }

    $defaults['packages']['titlePrefix']    = get_post_meta($post_id, 'landing_packages_title_prefix', true);
    $defaults['packages']['titleHighlight'] = get_post_meta($post_id, 'landing_packages_title_highlight', true);
    $defaults['packages']['lead']           = get_post_meta($post_id, 'landing_packages_lead', true);
    $defaults['packages']['btn']            = get_post_meta($post_id, 'landing_packages_btn', true);

    $pre = $defaults['packages']['titlePrefix'] ?? '';
    $hl  = $defaults['packages']['titleHighlight'] ?? '';
    if ($pre !== '' || $hl !== '') {
        $defaults['packages']['title'] = trim($pre . ' ' . $hl);
    }

    // Packages items
    if (isset($_POST['tt_packages_submitted'])) {
        $new_packages = array();
        if (isset($_POST['pkg']) && is_array($_POST['pkg'])) {
            foreach ($_POST['pkg'] as $pi => $p) {
                $features = array_filter(array_map('trim', explode("\n", str_replace("\r", "", wp_unslash($p['features'] ?? '')))));
                $title_val = sanitize_text_field(wp_unslash($p['title'] ?? ''));
                $pkg_id = ! empty($title_val) ? sanitize_title($title_val) : sanitize_title(wp_unslash($p['id'] ?? ''));
                if (empty($pkg_id)) {
                    $pkg_id = 'pkg-' . ($pi + 1);
                }
                $new_packages[] = array(
                    'id'         => $pkg_id,
                    'badge'      => sanitize_text_field(wp_unslash($p['badge'] ?? '')),
                    'title'      => sanitize_text_field(wp_unslash($p['title'] ?? '')),
                    'slogan'     => sanitize_text_field(wp_unslash($p['slogan'] ?? '')),
                    'price'      => sanitize_text_field(wp_unslash($p['price'] ?? '')),
                    'oldPrice'   => sanitize_text_field(wp_unslash($p['oldPrice'] ?? '')),
                    'target'     => sanitize_textarea_field(wp_unslash($p['target'] ?? '')),
                    'features'   => array_values($features),
                    'isFeatured' => ! empty($p['isFeatured']),
                    'guidance'   => sanitize_text_field(wp_unslash($p['guidance'] ?? '')),
                    'prefill'    => sanitize_text_field(wp_unslash($p['prefill'] ?? '')),
                );
            }
        }
        $defaults['packages']['items'] = $new_packages;
        update_post_meta($post_id, 'landing_packages_items', wp_slash(wp_json_encode($new_packages, JSON_UNESCAPED_UNICODE)));
    } elseif (isset($_POST['pkg']) && is_array($_POST['pkg'])) {
        $new_packages = array();
        foreach ($_POST['pkg'] as $pi => $p) {
            $features = array_filter(array_map('trim', explode("\n", str_replace("\r", "", wp_unslash($p['features'] ?? '')))));
            $pkg_id = sanitize_title(wp_unslash($p['id'] ?? ''));
            if (empty($pkg_id) && ! empty($p['title'])) {
                $pkg_id = sanitize_title(wp_unslash($p['title']));
            }
            if (empty($pkg_id)) {
                $pkg_id = 'pkg-' . ($pi + 1);
            }
            $new_packages[] = array(
                'id'         => $pkg_id,
                'badge'      => sanitize_text_field(wp_unslash($p['badge'] ?? '')),
                'title'      => sanitize_text_field(wp_unslash($p['title'] ?? '')),
                'slogan'     => sanitize_text_field(wp_unslash($p['slogan'] ?? '')),
                'price'      => sanitize_text_field(wp_unslash($p['price'] ?? '')),
                'oldPrice'   => sanitize_text_field(wp_unslash($p['oldPrice'] ?? '')),
                'target'     => sanitize_textarea_field(wp_unslash($p['target'] ?? '')),
                'features'   => array_values($features),
                'isFeatured' => ! empty($p['isFeatured']),
                'guidance'   => sanitize_text_field(wp_unslash($p['guidance'] ?? '')),
                'prefill'    => sanitize_text_field(wp_unslash($p['prefill'] ?? '')),
            );
        }
        $defaults['packages']['items'] = $new_packages;
        update_post_meta($post_id, 'landing_packages_items', wp_slash(wp_json_encode($new_packages, JSON_UNESCAPED_UNICODE)));
    } elseif (empty($defaults['packages']['items'])) {
        $existing_pkg = get_post_meta($post_id, 'landing_packages_items', true);
        if (! empty($existing_pkg)) {
            $dec = function_exists('thientam_decode_json_meta') ? thientam_decode_json_meta($existing_pkg) : (is_array($existing_pkg) ? $existing_pkg : json_decode(stripslashes($existing_pkg), true));
            if (is_array($dec) && ! empty($dec)) {
                $defaults['packages']['items'] = $dec;
            }
        }
    }

    // Consultation banner
    if (isset($_POST['landing_consultation_title'])) {
        $defaults['packages']['consultation'] = array(
            'title'   => sanitize_text_field(wp_unslash($_POST['landing_consultation_title'])),
            'desc'    => sanitize_textarea_field(wp_unslash($_POST['landing_consultation_desc'] ?? '')),
            'btn'     => sanitize_text_field(wp_unslash($_POST['landing_consultation_btn'] ?? '')),
            'prefill' => sanitize_text_field(wp_unslash($_POST['landing_consultation_prefill'] ?? '')),
        );
        update_post_meta($post_id, 'landing_packages_consultation', wp_json_encode($defaults['packages']['consultation'], JSON_UNESCAPED_UNICODE));
    }
}
}

if (! function_exists('thientam_landing_save_expert_meta')) {
/**
 * Shared save logic for Expert section (Chuyên gia đồng hành)
 *
 * @param int   $post_id
 * @param array $defaults Passed by reference
 */
function thientam_landing_save_expert_meta($post_id, &$defaults)
{
    if (! isset($defaults['expert']) || ! is_array($defaults['expert'])) {
        $defaults['expert'] = array();
    }

    // Expert Avatar
    if (isset($_POST['landing_expert_avatar'])) {
        $av = esc_url_raw(trim(wp_unslash($_POST['landing_expert_avatar'])));
        if (! empty($av)) {
            $defaults['expert']['avatar'] = $av;
        } else {
            unset($defaults['expert']['avatar']);
        }
        update_post_meta($post_id, 'landing_expert_avatar', $av);
    }

    // Expert Pills
    if (isset($_POST['landing_expert_pills'])) {
        $pills = array_filter(array_map('trim', explode("\n", str_replace("\r", "", wp_unslash($_POST['landing_expert_pills'])))));
        $defaults['expert']['pills'] = array_values($pills);
        update_post_meta($post_id, 'landing_expert_pills', wp_json_encode(array_values($pills), JSON_UNESCAPED_UNICODE));
    }

    // Expert Basic info
    if (isset($_POST['landing_expert_title_prefix'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_expert_title_prefix']));
        $defaults['expert']['titlePrefix'] = $val;
        update_post_meta($post_id, 'landing_expert_title_prefix', $val);
    }
    if (isset($_POST['landing_expert_title_highlight'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_expert_title_highlight']));
        $defaults['expert']['titleHighlight'] = $val;
        update_post_meta($post_id, 'landing_expert_title_highlight', $val);
    }
    if (isset($_POST['landing_expert_name'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_expert_name']));
        $defaults['expert']['name'] = $val;
        update_post_meta($post_id, 'landing_expert_name', $val);
    }
    if (isset($_POST['landing_expert_badge'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_expert_badge']));
        $defaults['expert']['badge'] = $val;
        update_post_meta($post_id, 'landing_expert_badge', $val);
    }
    if (isset($_POST['landing_expert_desc'])) {
        $val = sanitize_textarea_field(wp_unslash($_POST['landing_expert_desc']));
        $defaults['expert']['desc'] = $val;
        update_post_meta($post_id, 'landing_expert_desc', $val);
    }
    if (isset($_POST['landing_expert_desc1'])) {
        $val = sanitize_textarea_field(wp_unslash($_POST['landing_expert_desc1']));
        $defaults['expert']['desc1'] = $val;
        update_post_meta($post_id, 'landing_expert_desc1', $val);
    }
    if (isset($_POST['landing_expert_desc2'])) {
        $val = sanitize_textarea_field(wp_unslash($_POST['landing_expert_desc2']));
        $defaults['expert']['desc2'] = $val;
        update_post_meta($post_id, 'landing_expert_desc2', $val);
    }
    if (isset($_POST['landing_expert_notice'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_expert_notice']));
        $defaults['expert']['notice'] = $val;
        update_post_meta($post_id, 'landing_expert_notice', $val);
    }
    if (isset($_POST['landing_expert_btn'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_expert_btn']));
        $defaults['expert']['btn'] = $val;
        update_post_meta($post_id, 'landing_expert_btn', $val);
    }
    if (isset($_POST['landing_expert_btn_href'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_expert_btn_href']));
        $defaults['expert']['btnHref'] = $val;
        $defaults['expert']['anchor']  = $val;
        update_post_meta($post_id, 'landing_expert_btn_href', $val);
    }

    // Direct Value visibility checkbox (Có / Không)
    if (isset($_POST['landing_expert_name']) || isset($_POST['landing_expert_btn_href']) || isset($_POST['landing_expert_title_prefix'])) {
        $show_dv = ! empty($_POST['landing_expert_show_direct_value']) ? '1' : '0';
        update_post_meta($post_id, 'landing_expert_show_direct_value', $show_dv);
        $defaults['expert']['showDirectValue'] = ($show_dv === '1');
    }

    // Direct Value
    if (isset($_POST['landing_expert_dv_card1_title']) || isset($_POST['landing_expert_dv_card2_title']) || isset($_POST['landing_expert_dv_card1_eyebrow'])) {
        $c1_eyebrow = sanitize_text_field(wp_unslash($_POST['landing_expert_dv_card1_eyebrow'] ?? ''));
        $c1_title   = sanitize_text_field(wp_unslash($_POST['landing_expert_dv_card1_title'] ?? ''));
        $c1_desc    = sanitize_textarea_field(wp_unslash($_POST['landing_expert_dv_card1_desc'] ?? ''));

        $c2_title   = sanitize_text_field(wp_unslash($_POST['landing_expert_dv_card2_title'] ?? ''));
        $c2_items   = array_filter(array_map('trim', explode("\n", str_replace("\r", "", wp_unslash($_POST['landing_expert_dv_card2_items'] ?? '')))));

        // Guard against any warning strings
        if (strpos($c1_eyebrow, 'Undefined variable') !== false || strpos($c1_eyebrow, 'Warning') !== false) $c1_eyebrow = '';
        if (strpos($c1_title, 'Undefined variable') !== false || strpos($c1_title, 'Warning') !== false) $c1_title = '';
        if (strpos($c1_desc, 'Undefined variable') !== false || strpos($c1_desc, 'Warning') !== false) $c1_desc = '';
        if (strpos($c2_title, 'Undefined variable') !== false || strpos($c2_title, 'Warning') !== false) $c2_title = '';
        $c2_items = array_values(array_filter($c2_items, function ($it) {
            return strpos($it, 'Undefined variable') === false && strpos($it, 'Warning') === false;
        }));

        update_post_meta($post_id, 'landing_expert_dv_card1_eyebrow', $c1_eyebrow);
        update_post_meta($post_id, 'landing_expert_dv_card1_title', $c1_title);
        update_post_meta($post_id, 'landing_expert_dv_card1_desc', $c1_desc);
        update_post_meta($post_id, 'landing_expert_dv_card2_title', $c2_title);
        update_post_meta($post_id, 'landing_expert_dv_card2_items', wp_json_encode(array_values($c2_items), JSON_UNESCAPED_UNICODE));

        $direct_value = array(
            'card1' => array(
                'eyebrow' => $c1_eyebrow,
                'title'   => $c1_title,
                'desc'    => $c1_desc,
            ),
            'card2' => array(
                'title' => $c2_title,
                'items' => array_values($c2_items),
                'btn'   => 'Đăng ký tư vấn cùng chuyên gia',
            ),
        );

        $defaults['expert']['directValue'] = $direct_value;
        update_post_meta($post_id, 'landing_expert_direct_value', wp_json_encode($direct_value, JSON_UNESCAPED_UNICODE));
    }
}
}

if (! function_exists('thientam_landing_save_comparison_meta')) {
/**
 * Shared save logic for Comparison section (So sánh nhanh các gói dịch vụ)
 *
 * @param int   $post_id
 * @param array $defaults Passed by reference
 */
function thientam_landing_save_comparison_meta($post_id, &$defaults)
{
    if (! isset($defaults['comparison']) || ! is_array($defaults['comparison'])) {
        $defaults['comparison'] = array();
    }

    // 1. Header fields
    if (isset($_POST['landing_comparison_eyebrow'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_comparison_eyebrow']));
        $defaults['comparison']['eyebrow'] = $val;
        update_post_meta($post_id, 'landing_comparison_eyebrow', $val);
    }
    if (isset($_POST['landing_comparison_title_prefix'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_comparison_title_prefix']));
        $defaults['comparison']['titlePrefix'] = $val;
        update_post_meta($post_id, 'landing_comparison_title_prefix', $val);
    }
    if (isset($_POST['landing_comparison_title_highlight'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_comparison_title_highlight']));
        $defaults['comparison']['titleHighlight'] = $val;
        update_post_meta($post_id, 'landing_comparison_title_highlight', $val);
    }
    if (isset($_POST['landing_comparison_desc'])) {
        $val = sanitize_textarea_field(wp_unslash($_POST['landing_comparison_desc']));
        $defaults['comparison']['desc'] = $val;
        $defaults['comparison']['description'] = $val;
        update_post_meta($post_id, 'landing_comparison_desc', $val);
    }

    // Tiêu đề tổng hợp
    $comp_pre = $defaults['comparison']['titlePrefix'] ?? '';
    $comp_hl  = $defaults['comparison']['titleHighlight'] ?? '';
    if (! empty($comp_pre) || ! empty($comp_hl)) {
        $defaults['comparison']['title'] = trim($comp_pre . ' ' . $comp_hl);
    }

    // 2. Columns config
    $cols = array();
    $col_ids = array('can-ban', 'chuyen-sau', 'cung-cg', 'toan-dien');
    $default_names = array('Căn bản', 'Chuyên sâu', 'Cùng CG', 'Toàn diện');
    $default_prices = array('129K', '449K', '890K', '1.690K');
    for ($i = 0; $i < 4; $i++) {
        $name_key = "landing_comparison_col{$i}_name";
        $price_key = "landing_comparison_col{$i}_price";

        $col_name = isset($_POST[$name_key]) ? sanitize_text_field(wp_unslash($_POST[$name_key])) : ($defaults['comparison']['columns'][$i]['name'] ?? '');
        $col_price = isset($_POST[$price_key]) ? sanitize_text_field(wp_unslash($_POST[$price_key])) : ($defaults['comparison']['columns'][$i]['price'] ?? '');

        update_post_meta($post_id, $name_key, $col_name);
        update_post_meta($post_id, $price_key, $col_price);

        $cols[] = array(
            'id'    => $col_ids[$i],
            'name'  => $col_name,
            'price' => $col_price,
        );
    }
    $defaults['comparison']['columns'] = $cols;

    // 3. Comparison rows (repeater động, không giới hạn số lượng)
    if (isset($_POST['landing_comparison_rows']) && is_array($_POST['landing_comparison_rows'])) {
        $clean_rows = array();
        foreach ($_POST['landing_comparison_rows'] as $r) {
            $feature = sanitize_text_field(wp_unslash($r['feature'] ?? ''));
            if ($feature === '') {
                continue;
            }

            $parse_val = function ($v) {
                $v = trim((string) $v);
                if ($v === '1' || $v === '✓' || strtolower($v) === 'true' || strtolower($v) === 'v') {
                    return true;
                }
                if ($v === '0' || $v === '—' || $v === '-' || strtolower($v) === 'false') {
                    return false;
                }
                return $v;
            };

            $vals = array(
                $parse_val(sanitize_text_field(wp_unslash($r['val_0'] ?? ''))),
                $parse_val(sanitize_text_field(wp_unslash($r['val_1'] ?? ''))),
                $parse_val(sanitize_text_field(wp_unslash($r['val_2'] ?? ''))),
                $parse_val(sanitize_text_field(wp_unslash($r['val_3'] ?? ''))),
            );

            $clean_rows[] = array(
                'feature' => $feature,
                'values'  => $vals,
            );
        }
        $defaults['comparison']['rows'] = $clean_rows;
        update_post_meta($post_id, 'landing_comparison_rows', wp_json_encode($clean_rows, JSON_UNESCAPED_UNICODE));
    }

    // 4. Bottom Banner
    if (isset($_POST['landing_comparison_note_title'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_comparison_note_title']));
        $defaults['comparison']['noteTitle'] = $val;
        update_post_meta($post_id, 'landing_comparison_note_title', $val);
    }
    if (isset($_POST['landing_comparison_note_desc'])) {
        $val = sanitize_textarea_field(wp_unslash($_POST['landing_comparison_note_desc']));
        $defaults['comparison']['noteDesc'] = $val;
        update_post_meta($post_id, 'landing_comparison_note_desc', $val);
    }
    if (isset($_POST['landing_comparison_btn_text'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_comparison_btn_text']));
        $defaults['comparison']['btnText'] = $val;
        update_post_meta($post_id, 'landing_comparison_btn_text', $val);
    }
    if (isset($_POST['landing_comparison_btn_href'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_comparison_btn_href']));
        $defaults['comparison']['btnHref'] = $val;
        $defaults['comparison']['anchor']  = $val;
        update_post_meta($post_id, 'landing_comparison_btn_href', $val);
    }
}
}

if (! function_exists('thientam_landing_save_process_meta')) {
/**
 * Shared save logic for Process section (Quy trình đồng hành / tiếp nhận & tư vấn)
 *
 * @param int   $post_id
 * @param array $defaults Passed by reference
 */
function thientam_landing_save_process_meta($post_id, &$defaults)
{
    if (! isset($defaults['process']) || ! is_array($defaults['process'])) {
        $defaults['process'] = array();
    }

    if (isset($_POST['landing_process_title_prefix'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_process_title_prefix']));
        $defaults['process']['titlePrefix'] = $val;
        update_post_meta($post_id, 'landing_process_title_prefix', $val);
    }
    if (isset($_POST['landing_process_title_highlight'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_process_title_highlight']));
        $defaults['process']['titleHighlight'] = $val;
        update_post_meta($post_id, 'landing_process_title_highlight', $val);
    }
    if (isset($_POST['landing_process_lead'])) {
        $val = sanitize_textarea_field(wp_unslash($_POST['landing_process_lead']));
        $defaults['process']['lead'] = $val;
        update_post_meta($post_id, 'landing_process_lead', $val);
    }
    if (isset($_POST['landing_process_notice'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_process_notice']));
        $defaults['process']['notice'] = $val;
        update_post_meta($post_id, 'landing_process_notice', $val);
    }

    // Tiêu đề tổng hợp
    $proc_pre = $defaults['process']['titlePrefix'] ?? '';
    $proc_hl  = $defaults['process']['titleHighlight'] ?? '';
    if (! empty($proc_pre) || ! empty($proc_hl)) {
        $defaults['process']['title'] = trim($proc_pre . ' ' . $proc_hl);
    }

    // Steps repeater
    if (isset($_POST['process_steps']) && is_array($_POST['process_steps'])) {
        $new_steps = array();
        foreach ($_POST['process_steps'] as $idx => $st) {
            $st_title = sanitize_text_field(wp_unslash($st['title'] ?? ''));
            $st_desc  = sanitize_textarea_field(wp_unslash($st['desc'] ?? ''));
            if ($st_title === '' && $st_desc === '') {
                continue;
            }
            $step_num  = ! empty($st['step']) ? sanitize_text_field(wp_unslash($st['step'])) : sprintf('%02d', count($new_steps) + 1);
            $step_icon = sanitize_text_field(wp_unslash($st['icon'] ?? ''));
            $new_steps[] = array(
                'step'  => $step_num,
                'title' => $st_title,
                'desc'  => $st_desc,
                'icon'  => $step_icon,
            );
        }
        $defaults['process']['steps'] = $new_steps;
        update_post_meta($post_id, 'landing_process_steps', wp_json_encode($new_steps, JSON_UNESCAPED_UNICODE));
    }
}
}

if (! function_exists('thientam_landing_save_faq_meta')) {
/**
 * Shared save logic for FAQ section (Hỏi đáp thường gặp)
 *
 * @param int   $post_id
 * @param array $defaults Passed by reference
 */
function thientam_landing_save_faq_meta($post_id, &$defaults)
{
    if (! isset($defaults['faq']) || ! is_array($defaults['faq'])) {
        $defaults['faq'] = array();
    }

    if (isset($_POST['landing_faq_title_prefix'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_faq_title_prefix']));
        $defaults['faq']['titlePrefix'] = $val;
        update_post_meta($post_id, 'landing_faq_title_prefix', $val);
    }
    if (isset($_POST['landing_faq_title_highlight'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_faq_title_highlight']));
        $defaults['faq']['titleHighlight'] = $val;
        update_post_meta($post_id, 'landing_faq_title_highlight', $val);
    }
    if (isset($_POST['landing_faq_title'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_faq_title']));
        $defaults['faq']['title'] = $val;
        update_post_meta($post_id, 'landing_faq_title', $val);
    }
    if (isset($_POST['landing_faq_desc'])) {
        $val = sanitize_textarea_field(wp_unslash($_POST['landing_faq_desc']));
        $defaults['faq']['desc'] = $val;
        update_post_meta($post_id, 'landing_faq_desc', $val);
    }
    if (isset($_POST['landing_faq_note'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_faq_note']));
        $defaults['faq']['note'] = $val;
        update_post_meta($post_id, 'landing_faq_note', $val);
    }

    // Tiêu đề tổng hợp
    $faq_pre = $defaults['faq']['titlePrefix'] ?? '';
    $faq_hl  = $defaults['faq']['titleHighlight'] ?? '';
    if (! empty($faq_pre) || ! empty($faq_hl)) {
        $defaults['faq']['title'] = trim($faq_pre . ' ' . $faq_hl);
    }

    // FAQ items repeater
    if (isset($_POST['faq_items']) && is_array($_POST['faq_items'])) {
        $new_faqs = array();
        foreach ($_POST['faq_items'] as $item) {
            $q = sanitize_text_field(wp_unslash($item['q'] ?? ''));
            $a = sanitize_textarea_field(wp_unslash($item['a'] ?? ''));
            if ($q !== '' || $a !== '') {
                $new_faqs[] = array('q' => $q, 'a' => $a);
            }
        }
        $defaults['faq']['items'] = $new_faqs;
        update_post_meta($post_id, 'landing_faq_items', wp_json_encode($new_faqs, JSON_UNESCAPED_UNICODE));
    }
}
}

if (! function_exists('thientam_landing_save_registration_meta')) {
/**
 * Shared save logic for Registration Form Layout (Cột trái Form Đăng ký)
 *
 * @param int   $post_id
 * @param array $defaults Passed by reference
 */
function thientam_landing_save_registration_meta($post_id, &$defaults)
{
    if (! isset($defaults['form']) || ! is_array($defaults['form'])) {
        $defaults['form'] = array();
    }

    if (isset($_POST['landing_form_title_prefix'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_form_title_prefix']));
        $defaults['form']['titlePrefix'] = $val;
        update_post_meta($post_id, 'landing_form_title_prefix', $val);
    }
    if (isset($_POST['landing_form_title_highlight'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_form_title_highlight']));
        $defaults['form']['titleHighlight'] = $val;
        update_post_meta($post_id, 'landing_form_title_highlight', $val);
    }
    if (isset($_POST['landing_form_title_suffix'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_form_title_suffix']));
        $defaults['form']['titleSuffix'] = $val;
        update_post_meta($post_id, 'landing_form_title_suffix', $val);
    }
    if (isset($_POST['landing_form_lead'])) {
        $val = sanitize_textarea_field(wp_unslash($_POST['landing_form_lead']));
        $defaults['form']['lead'] = $val;
        update_post_meta($post_id, 'landing_form_lead', $val);
    }
    if (isset($_POST['landing_form_highlights'])) {
        $raw_hl = wp_unslash($_POST['landing_form_highlights']);
        update_post_meta($post_id, 'landing_form_highlights', sanitize_textarea_field($raw_hl));
        $chips = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $raw_hl))));
        $defaults['form']['highlights'] = array_values($chips);
    }
    if (isset($_POST['landing_form_security_title'])) {
        $val = sanitize_text_field(wp_unslash($_POST['landing_form_security_title']));
        $defaults['form']['securityCommitmentTitle'] = $val;
        update_post_meta($post_id, 'landing_form_security_title', $val);
    }
    if (isset($_POST['landing_form_security_desc'])) {
        $val = sanitize_textarea_field(wp_unslash($_POST['landing_form_security_desc']));
        $defaults['form']['securityCommitmentDesc'] = $val;
        update_post_meta($post_id, 'landing_form_security_desc', $val);
    }

    // Tiêu đề tổng hợp
    $pre  = $defaults['form']['titlePrefix'] ?? '';
    $hl   = $defaults['form']['titleHighlight'] ?? '';
    $suf  = $defaults['form']['titleSuffix'] ?? '';
    if (! empty($pre) || ! empty($hl) || ! empty($suf)) {
        $defaults['form']['title'] = trim(trim($pre . ' ' . $hl) . ' ' . $suf);
    }
}
}

