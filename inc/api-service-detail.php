<?php

/**
 * REST API Endpoints for Service Details (Thiên Tâm)
 * Hỗ trợ cả REST API tùy biến: /wp-json/thientam/v1/services & /wp-json/thientam/v1/services/{slug}
 * Tự động vô hiệu hóa cache LiteSpeed/Redis/Nginx, đọc trực tiếp từ database
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Hỗ trợ CORS đầy đủ cho REST API
 */
add_action('init', function () {
    add_filter('rest_pre_serve_request', function ($served, $result, $request, $server) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, Cache-Control, Pragma, X-WP-Nonce, X-Client-Env');
        return $served;
    }, 10, 4);
});


/**
 * Vô hiệu hóa cache cho các request REST API
 */
add_action('rest_api_init', function () {
    if (! defined('DONOTCACHEPAGE')) {
        define('DONOTCACHEPAGE', true);
    }
    if (! defined('DONOTCACHEDB')) {
        define('DONOTCACHEDB', true);
    }
    if (! defined('DONOTMINIFY')) {
        define('DONOTMINIFY', true);
    }
});

/**
 * Đăng ký các route REST API cho Thiên Tâm Service
 */
add_action('rest_api_init', 'thientam_register_service_rest_routes');
function thientam_register_service_rest_routes()
{
    // 1. Lấy danh sách tất cả các dịch vụ: GET /wp-json/thientam/v1/services
    register_rest_route('thientam/v1', '/services', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_all_services',
        'permission_callback' => '__return_true',
    ));

    // 2. Lấy chi tiết 1 dịch vụ theo slug hoặc ID: GET /wp-json/thientam/v1/services/{slug}
    register_rest_route('thientam/v1', '/services/(?P<slug>[a-zA-Z0-9-_]+)', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_service_by_slug',
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

    // 3. Mở rộng trường service_details & thumbnail_url vào WP REST API mặc định: /wp-json/wp/v2/pages
    register_rest_field('page', 'service_details', array(
        'get_callback'    => function ($post_arr) {
            return thientam_format_service_detail_data($post_arr['id']);
        },
        'schema'          => null,
    ));

    register_rest_field('page', 'thumbnail_url', array(
        'get_callback'    => function ($post_arr) {
            $thumb = get_the_post_thumbnail_url($post_arr['id'], 'full');
            return $thumb ?: '';
        },
        'schema'          => null,
    ));
}

/**
 * Helper: Trích xuất và định dạng toàn bộ dữ liệu chi tiết dịch vụ chuẩn Next.js
 */
function thientam_format_service_detail_data($post_id)
{
    // Xóa cache bộ nhớ đệm postmeta
    clean_post_cache($post_id);
    wp_cache_delete($post_id, 'posts');
    wp_cache_delete($post_id, 'post_meta');

    $post = get_post($post_id);
    if (! $post) {
        return null;
    }

    $slug = $post->post_name;

    // Tiêu đề & Hero
    $title = get_post_meta($post_id, 'service_hero_title', true);
    if (empty($title) && function_exists('get_field')) {
        $title = get_field('service_hero_title', $post_id);
    }
    if (empty($title)) {
        $title = $post->post_title;
    }

    $eyebrow = get_post_meta($post_id, 'service_hero_eyebrow', true);
    if (empty($eyebrow) && function_exists('get_field')) {
        $eyebrow = get_field('service_hero_eyebrow', $post_id);
    }
    if (empty($eyebrow)) {
        $eyebrow = 'DỊCH VỤ TƯ VẤN CÁ NHÂN';
    }

    $subtitle = get_post_meta($post_id, 'service_hero_subtitle', true);
    if (empty($subtitle) && function_exists('get_field')) {
        $subtitle = get_field('service_hero_subtitle', $post_id);
    }
    if (empty($subtitle)) {
        $subtitle = '';
    }

    $description = get_post_meta($post_id, 'service_hero_description', true);
    if (empty($description) && function_exists('get_field')) {
        $description = get_field('service_hero_description', $post_id);
    }
    if (empty($description)) {
        $description = $post->post_content;
    }

    // Lấy Icon dịch vụ
    $icon = get_post_meta($post_id, 'service_icon', true);
    if (empty($icon) && function_exists('get_field')) {
        $icon = get_field('service_icon', $post_id);
    }
    if (empty($icon)) {
        $icon = 'compass';
    }

    // Lấy Ảnh đại diện (Featured Image / Thumbnail)
    $thumbnail_id = get_post_thumbnail_id($post_id);
    $thumbnail_url = get_the_post_thumbnail_url($post_id, 'full');
    $thumbnail_alt = $thumbnail_id ? (get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true) ?: $title) : '';

    // Nếu có thumbnail trên WP thì dùng, nếu chưa có thì fallback về file ảnh static theo slug
    $hero_image = $thumbnail_url ? $thumbnail_url : ('/images/banner-service-' . $slug . '.png');

    // Breadcrumb
    $breadcrumb = array(
        array('label' => 'Trang chủ', 'href' => '/'),
        array('label' => 'Dịch vụ', 'href' => '/services'),
        array('label' => strip_tags(str_replace(array("\r\n", "\r", "\n"), ' ', $post->post_title)), 'href' => '/services/' . $slug),
    );

    // Overview Cards
    $cards = array();
    $cards_count = intval(get_post_meta($post_id, 'overview_cards', true));

    if ($cards_count > 0) {
        for ($i = 0; $i < $cards_count; $i++) {
            $cards[] = array(
                'icon'  => get_post_meta($post_id, "overview_cards_{$i}_card_icon", true) ?: 'compass',
                'title' => get_post_meta($post_id, "overview_cards_{$i}_card_title", true) ?: '',
                'desc'  => get_post_meta($post_id, "overview_cards_{$i}_card_desc", true) ?: '',
            );
        }
    } elseif (function_exists('get_field') && ($acf_cards = get_field('overview_cards', $post_id))) {
        if (is_array($acf_cards)) {
            foreach ($acf_cards as $c) {
                $cards[] = array(
                    'icon'  => isset($c['card_icon']) ? $c['card_icon'] : 'compass',
                    'title' => isset($c['card_title']) ? $c['card_title'] : '',
                    'desc'  => isset($c['card_desc']) ? $c['card_desc'] : '',
                );
            }
        }
    }

    // Pricing Items
    $pricing_items = array();
    $items_count = intval(get_post_meta($post_id, 'pricing_items', true));

    if ($items_count > 0) {
        for ($i = 0; $i < $items_count; $i++) {
            $stt = get_post_meta($post_id, "pricing_items_{$i}_stt", true) ?: str_pad($i + 1, 2, '0', STR_PAD_LEFT);
            $pricing_items[] = array(
                'stt'     => $stt,
                'service' => get_post_meta($post_id, "pricing_items_{$i}_service", true) ?: '',
                'content' => get_post_meta($post_id, "pricing_items_{$i}_content", true) ?: '',
                'price'   => get_post_meta($post_id, "pricing_items_{$i}_price", true) ?: '',
            );
        }
    } elseif (function_exists('get_field') && ($acf_items = get_field('pricing_items', $post_id))) {
        if (is_array($acf_items)) {
            foreach ($acf_items as $idx => $item) {
                $stt = isset($item['stt']) && ! empty($item['stt']) ? $item['stt'] : str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
                $pricing_items[] = array(
                    'stt'     => $stt,
                    'service' => isset($item['service']) ? $item['service'] : '',
                    'content' => isset($item['content']) ? $item['content'] : '',
                    'price'   => isset($item['price']) ? $item['price'] : '',
                );
            }
        }
    }

    // Pricing Titles
    $pricing_title_prefix = ($slug === 'goi-doanh-nghiep') ? 'Hạng mục' : 'Bảng giá';
    $pricing_title_highlight = ($slug === 'goi-doanh-nghiep') ? 'tư vấn doanh nghiệp' : (($slug === 'phong-thuy-nha-o-doanh-nghiep') ? 'tư vấn không gian' : (($slug === 'tu-vi-bat-tu') ? 'Tử vi • Bát tự • Kỳ môn mệnh' : (($slug === 'tu-van-gia-dao') ? 'tư vấn Gia đạo' : (($slug === 'phong-thuy-so') ? 'Phong thủy số' : 'Sự vật • Sự việc'))));

    return array(
        'id'             => $post_id,
        'slug'           => $slug,
        'page_title'     => $post->post_title,
        'iconKey'        => $icon,
        'thumbnail_url'  => $thumbnail_url ?: '',
        'featured_image' => $thumbnail_url ? array(
            'id'    => $thumbnail_id,
            'url'   => $thumbnail_url,
            'alt'   => $thumbnail_alt,
        ) : null,
        'breadcrumb'     => $breadcrumb,
        'hero'           => array(
            'eyebrow'     => $eyebrow,
            'title'       => $title,
            'subtitle'    => $subtitle,
            'iconKey'     => $icon,
            'description' => $description,
            'image'       => $hero_image,
            'featured_image' => $thumbnail_url ? array(
                'id'    => $thumbnail_id,
                'url'   => $thumbnail_url,
                'alt'   => $thumbnail_alt,
            ) : null,
        ),
        'overviewSection' => array(
            'titlePrefix'    => get_post_meta($post_id, 'overview_title_prefix', true) ?: 'Khi bạn cần thêm',
            'titleHighlight' => get_post_meta($post_id, 'overview_title_highlight', true) ?: 'một góc nhìn',
            'desc'           => get_post_meta($post_id, 'overview_desc', true) ?: '',
            'cards'          => $cards,
        ),
        'pricingSection'  => array(
            'titlePrefix'    => $pricing_title_prefix,
            'titleHighlight' => $pricing_title_highlight,
            'desc'           => 'Bảng chi phí tư vấn minh bạch, rõ ràng theo từng hạng mục nhằm mang lại giải pháp thấu đáo và thiết thực nhất cho Quý vị.',
            'headers'        => array(
                'stt'     => 'STT',
                'service' => 'HẠNG MỤC TƯ VẤN',
                'content' => 'NỘI DUNG',
                'price'   => 'PHÍ TƯ VẤN',
            ),
            'items'          => $pricing_items,
            'note'           => get_post_meta($post_id, 'pricing_note', true) ?: '',
        ),
        'benefitsSection' => array(
            'titlePrefix'    => get_post_meta($post_id, 'benefits_title_prefix', true) ?: 'Quyền lợi',
            'titleHighlight' => get_post_meta($post_id, 'benefits_title_highlight', true) ?: 'đồng hành',
            'desc'           => get_post_meta($post_id, 'benefits_desc', true) ?: 'Thiên Tâm không chỉ dừng lại ở buổi tư vấn mà luôn sẵn sàng đồng hành, hỗ trợ Quý vị thấu đáo trong suốt quá trình áp dụng giải pháp.',
            'content'        => get_post_meta($post_id, 'benefits_content', true) ?: '',
        ),
        'seo' => array(
            'meta_title'       => get_post_meta($post_id, 'seo_meta_title', true) ?: '',
            'meta_description' => get_post_meta($post_id, 'seo_meta_description', true) ?: '',
            'meta_keywords'    => get_post_meta($post_id, 'seo_meta_keywords', true) ?: '',
            'og_image'         => get_post_meta($post_id, 'seo_og_image', true) ?: '',
        ),
    );
}

/**
 * 1. Callback lấy danh sách tất cả các trang Dịch Vụ (Rút gọn: slug, thumbnail_url, featured_image, mô tả ngắn)
 */
function thientam_rest_get_all_services($request)
{
    // Tắt hoàn toàn cache REST API của WordPress & Server & LiteSpeed
    if (! headers_sent()) {
        nocache_headers();
        header('X-LiteSpeed-Cache-Control: no-cache');
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    // 1. Lấy ID các trang có template 'template-service-detail.php' hoặc có cấu hình dịch vụ
    $services_by_meta = get_posts(array(
        'post_type'        => 'page',
        'post_status'      => 'publish',
        'posts_per_page'   => -1,
        'fields'           => 'ids',
        'meta_query'       => array(
            'relation' => 'OR',
            array(
                'key'     => '_wp_page_template',
                'value'   => 'template-service-detail.php',
                'compare' => '=',
            ),
            array(
                'key'     => 'service_hero_title',
                'value'   => '',
                'compare' => '!=',
            ),
        ),
        'suppress_filters' => true,
    ));

    // 2. Lấy ID các trang con trực thuộc trang cha 'services' hoặc 'dich-vu'
    $services_by_parent = array();
    $parent_page = get_page_by_path('services', OBJECT, 'page');
    if (! $parent_page) {
        $parent_page = get_page_by_path('dich-vu', OBJECT, 'page');
    }
    if ($parent_page) {
        $services_by_parent = get_posts(array(
            'post_type'        => 'page',
            'post_status'      => 'publish',
            'posts_per_page'   => -1,
            'post_parent'      => $parent_page->ID,
            'fields'           => 'ids',
            'suppress_filters' => true,
        ));
    }

    $all_service_ids = array_unique(array_merge($services_by_meta, $services_by_parent));

    // Loại trừ trang cha 'services'/'dich-vu' nếu lọt vào
    if ($parent_page && ($key = array_search($parent_page->ID, $all_service_ids)) !== false) {
        unset($all_service_ids[$key]);
    }

    if (empty($all_service_ids)) {
        return new WP_REST_Response(array(
            'success' => true,
            'count'   => 0,
            'data'    => array(),
        ), 200);
    }

    // Truy vấn tất cả trang dịch vụ tự động, sắp xếp theo thứ tự Menu Order
    $args = array(
        'post_type'        => 'page',
        'post_status'      => 'publish',
        'posts_per_page'   => -1,
        'post__in'         => $all_service_ids,
        'orderby'          => array('menu_order' => 'ASC', 'ID' => 'ASC'),
        'suppress_filters' => true,
    );

    $query = new WP_Query($args);
    $services = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            // Xóa cache meta để luôn đọc dữ liệu vừa sửa
            clean_post_cache($post_id);
            wp_cache_delete($post_id, 'posts');
            wp_cache_delete($post_id, 'post_meta');

            $post = get_post($post_id);
            $slug = $post->post_name;

            // Tiêu đề & Tiêu đề phụ
            $title = get_post_meta($post_id, 'service_hero_title', true);
            if (empty($title) && function_exists('get_field')) {
                $title = get_field('service_hero_title', $post_id);
            }
            if (empty($title)) {
                $title = get_the_title($post_id);
            }

            $subtitle = get_post_meta($post_id, 'service_hero_subtitle', true);
            if (empty($subtitle) && function_exists('get_field')) {
                $subtitle = get_field('service_hero_subtitle', $post_id);
            }

            // Mô tả ngắn của trang (Ưu tiên Excerpt nhập trực tiếp trong Admin -> hero_description -> content)
            $short_desc = !empty($post->post_excerpt) ? trim($post->post_excerpt) : '';
            if (empty($short_desc)) {
                $short_desc = get_post_meta($post_id, 'service_hero_description', true);
            }
            if (empty($short_desc) && function_exists('get_field')) {
                $short_desc = get_field('service_hero_description', $post_id);
            }
            if (empty($short_desc)) {
                $short_desc = wp_trim_words(wp_strip_all_tags($post->post_content), 30, '...');
            }

            // Thumbnail / Featured Image
            $thumbnail_id = get_post_thumbnail_id($post_id);
            $thumbnail_url = get_the_post_thumbnail_url($post_id, 'full');
            $thumbnail_alt = $thumbnail_id ? (get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true) ?: $title) : '';

            // Icon dịch vụ
            $icon = get_post_meta($post_id, 'service_icon', true);
            if (empty($icon) && function_exists('get_field')) {
                $icon = get_field('service_icon', $post_id);
            }
            if (empty($icon)) {
                $icon = 'compass';
            }

            $services[] = array(
                'id'             => $post_id,
                'slug'           => $slug,
                'page_title'     => $post->post_title,
                'subtitle'       => $subtitle ?: '',
                'iconKey'        => $icon,
                'description'    => $short_desc,
                'thumbnail_url'  => $thumbnail_url ?: '',
                'featured_image' => $thumbnail_url ? array(
                    'id'  => $thumbnail_id,
                    'url' => $thumbnail_url,
                    'alt' => $thumbnail_alt,
                ) : null,
                'menu_order'     => $post->menu_order,
            );
        }
        wp_reset_postdata();
    }

    return new WP_REST_Response(array(
        'success' => true,
        'count'   => count($services),
        'data'    => $services,
    ), 200);
}

/**
 * 2. Callback lấy chi tiết 1 dịch vụ theo slug hoặc ID
 */
function thientam_rest_get_service_by_slug($request)
{
    if (! headers_sent()) {
        nocache_headers();
        header('X-LiteSpeed-Cache-Control: no-cache');
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    $slug = sanitize_text_field($request->get_param('slug'));
    $post = null;

    if (is_numeric($slug)) {
        $post = get_post(intval($slug));
    } else {
        // 1. Tìm trực tiếp theo slug (post_name) qua WP_Query
        $pages = get_posts(array(
            'name'             => $slug,
            'post_type'        => array('page', 'post'),
            'post_status'      => 'publish',
            'posts_per_page'   => 1,
            'suppress_filters' => true,
        ));

        if (! empty($pages)) {
            $post = $pages[0];
        } else {
            // 2. Thử get_page_by_path cho các trường hợp URL phân cấp
            $post = get_page_by_path($slug, OBJECT, 'page');
            if (! $post) {
                $post = get_page_by_path('services/' . $slug, OBJECT, 'page');
            }
        }
    }

    if (! $post || $post->post_status !== 'publish') {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'Không tìm thấy dịch vụ: ' . $slug,
        ), 404);
    }

    $service_data = thientam_format_service_detail_data($post->ID);

    return new WP_REST_Response(array(
        'success' => true,
        'data'    => $service_data,
    ), 200);
}
