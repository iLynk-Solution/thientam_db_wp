<?php

/**
 * REST API Endpoints for Landing Pages (Thiên Tâm)
 * GET /wp-json/thientam/v1/landings
 * GET /wp-json/thientam/v1/landing/{slug}
 *
 * @package ThienTamData
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', function () {
    // 1. Danh sách các trang landing: GET /wp-json/thientam/v1/landings
    register_rest_route('thientam/v1', '/landings', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_all_landings',
        'permission_callback' => '__return_true',
    ));

    // 2. Chi tiết 1 trang landing theo slug: GET /wp-json/thientam/v1/landing/{slug}
    register_rest_route('thientam/v1', '/landing/(?P<slug>[a-zA-Z0-9-_]+)', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_landing_by_slug',
        'permission_callback' => '__return_true',
        'args'                => array(
            'slug' => array(
                'required'          => true,
                'validate_callback' => function ($param) {
                    return is_string($param);
                },
            ),
        ),
    ));
});

/**
 * Handler: Lấy danh sách landing pages
 */
function thientam_rest_get_all_landings($request)
{
    $landings = thientam_get_supported_landing_pages();
    $result = array();

    foreach ($landings as $slug => $info) {
        // Tìm page WordPress gắn với slug này
        $page_id = thientam_get_landing_page_id($slug);

        $result[] = array(
            'slug'         => $slug,
            'name'         => $info['name'],
            'route'        => $info['route'],
            'has_wp_page'  => (bool) $page_id,
            'page_id'      => $page_id ?: null,
            'page_title'   => $page_id ? get_the_title($page_id) : null,
            'api_endpoint' => rest_url("thientam/v1/landing/{$slug}"),
        );
    }

    return new WP_REST_Response(array(
        'success' => true,
        'count'   => count($result),
        'data'    => $result,
    ), 200);
}

/**
 * Helper: Tìm post_id của Page có meta thientam_landing_type tương ứng
 */
function thientam_get_landing_page_id($slug)
{
    $query = new WP_Query(array(
        'post_type'      => 'page',
        'post_status'    => array('publish', 'private', 'draft'),
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'     => 'thientam_landing_type',
                'value'   => $slug,
                'compare' => '=',
            ),
        ),
    ));

    return ! empty($query->posts) ? $query->posts[0] : null;
}

/**
 * Handler: Lấy chi tiết nội dung cho một trang landing
 */
function thientam_rest_get_landing_by_slug($request)
{
    $slug = sanitize_title($request->get_param('slug'));
    $landings = thientam_get_supported_landing_pages();

    if (! isset($landings[$slug])) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => "Không tìm thấy trang landing '{$slug}' trong hệ thống.",
        ), 404);
    }

    // 1. Nạp dữ liệu JSON mặc định
    $data = thientam_get_landing_default_data($slug) ?: array();

    // 2. Tìm trang WP tương ứng
    $page_id = thientam_get_landing_page_id($slug);
    $is_customized = false;
    $updated_at = null;

    if ($page_id) {
        $updated_at = get_the_modified_date('c', $page_id);

        // Kiểm tra xem có Custom JSON Override không
        $custom_json_raw = get_post_meta($page_id, 'landing_custom_json', true);
        if (! empty($custom_json_raw)) {
            $parsed = json_decode($custom_json_raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                $data = array_replace_recursive($data, $parsed);
                $is_customized = true;
            }
        }

        // Merge các trường ACF đơn lẻ lên trên dữ liệu gốc
        $fields_map = array(
            'meta.title'                           => 'landing_meta_title',
            'meta.description'                     => 'landing_meta_description',
            'hero.kicker'                          => 'landing_hero_kicker',
            'hero.titlePrefix'                     => 'landing_hero_title_prefix',
            'hero.titleHighlight'                  => 'landing_hero_title_highlight',
            'hero.leadHighlight'                   => 'landing_hero_lead_highlight',
            'hero.lead'                            => 'landing_hero_lead',
            'perspectives.titlePrefix'             => 'landing_perspectives_title_prefix',
            'perspectives.titleHighlight'          => 'landing_perspectives_title_highlight',
            'perspectives.lead'                    => 'landing_perspectives_lead',
            'relationship.titlePrefix'             => 'landing_relationship_title_prefix',
            'relationship.titleHighlight'          => 'landing_relationship_title_highlight',
            'relationship.statement'               => 'landing_relationship_statement',
            'packages.titlePrefix'                 => 'landing_packages_title_prefix',
            'packages.titleHighlight'              => 'landing_packages_title_highlight',
            'packages.lead'                        => 'landing_packages_lead',
            'packages.btn'                         => 'landing_packages_btn',
            'expert.name'                          => 'landing_expert_name',
            'expert.badge'                         => 'landing_expert_badge',
            'expert.desc'                          => 'landing_expert_desc',
            'expert.desc1'                         => 'landing_expert_desc1',
            'expert.desc2'                         => 'landing_expert_desc2',
            'faq.title'                            => 'landing_faq_title',
            'faq.desc'                             => 'landing_faq_desc',
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

        // Xử lý riêng trường chips của hero (nếu có nhập)
        $chips_raw = get_post_meta($page_id, 'landing_hero_chips', true);
        if (! empty($chips_raw)) {
            $chips = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $chips_raw))));
            if (! empty($chips)) {
                $data['hero']['chips'] = array_values($chips);
                $is_customized = true;
            }
        }

        // Xử lý keywords của meta (nếu có nhập)
        $keywords_raw = get_post_meta($page_id, 'landing_meta_keywords', true);
        if (! empty($keywords_raw)) {
            $kws = array_filter(array_map('trim', explode(',', $keywords_raw)));
            if (! empty($kws)) {
                $data['meta']['keywords'] = array_values($kws);
                $is_customized = true;
            }
        }
    }

    // Nếu gọi với param ?raw=1 hoặc ?format=raw thì trả về trực tiếp dict
    if ($request->get_param('raw') === '1') {
        return new WP_REST_Response($data, 200);
    }

    return new WP_REST_Response(array(
        'success'       => true,
        'slug'          => $slug,
        'name'          => $landings[$slug]['name'],
        'page_id'       => $page_id ?: null,
        'is_customized' => $is_customized,
        'updated_at'    => $updated_at,
        'data'          => $data,
    ), 200);
}

/**
 * Utility: Gán giá trị vào mảng nhiều cấp theo cú pháp dot notation (vd: 'meta.title')
 */
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
