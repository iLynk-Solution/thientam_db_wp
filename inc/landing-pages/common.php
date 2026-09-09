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
            $decoded = json_decode($custom_json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
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
