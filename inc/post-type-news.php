<?php

/**
 * REST API for Standard WordPress Posts (Tin tức & Bài viết)
 * 
 * - Sử dụng trực tiếp Post Type mặc định của WordPress: `post` (Bài viết / Posts)
 * - Không tạo Custom Post Type riêng
 * - Sử dụng Chuyên mục (Category), Ảnh đại diện (Featured Image), Trích dẫn (Excerpt), Tác giả mặc định
 * - Decode thực thể HTML (&amp; -> &, &quot; -> ", &#039; -> ') để Frontend render chuẩn xác
 * - Sắp xếp lấy bài mới nhất (date DESC)
 * - Tích hợp REST API:
 *     + GET /wp-json/thientam/v1/news : Lấy danh sách tin tức mới nhất (hỗ trợ lọc category, tag, search, sort, pagination)
 *     + GET /wp-json/thientam/v1/news/categories : Lấy danh sách danh mục (Chuyên mục)
 *     + GET /wp-json/thientam/v1/news/category/{slug} : Lấy danh sách bài viết theo chuyên mục
 *     + GET /wp-json/thientam/v1/news/tags : Lấy danh sách thẻ bài viết (Tags)
 *     + GET /wp-json/thientam/v1/tags : Alias lấy danh sách thẻ bài viết
 *     + GET /wp-json/thientam/v1/news/tag/{slug} : Lấy danh sách bài viết theo thẻ (Tag)
 *     + GET /wp-json/thientam/v1/tags/{slug} : Alias lấy danh sách bài viết theo thẻ (Tag)
 *     + GET /wp-json/thientam/v1/news/{slug} : Lấy chi tiết bài viết
 * 
 * @package HelloElementorChild
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * 1. Đăng ký REST API Endpoints:
 *    - GET /wp-json/thientam/v1/news
 *    - GET /wp-json/thientam/v1/news/{slug}
 *
 * @return void
 */
add_action('rest_api_init', 'thientam_register_news_rest_routes');
function thientam_register_news_rest_routes()
{
    // Danh sách danh mục tin tức (Chuyên mục / Categories - CHỈ lấy taxonomy 'category')
    register_rest_route('thientam/v1', '/news/categories', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_news_categories',
        'permission_callback' => '__return_true',
    ));

    // Alias cho danh mục tin tức
    register_rest_route('thientam/v1', '/categories', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_news_categories',
        'permission_callback' => '__return_true',
    ));

    // Danh sách thẻ bài viết (Tags - taxonomy 'post_tag')
    register_rest_route('thientam/v1', '/news/tags', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_news_tags',
        'permission_callback' => '__return_true',
    ));

    // Alias cho danh sách thẻ bài viết
    register_rest_route('thientam/v1', '/tags', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_news_tags',
        'permission_callback' => '__return_true',
    ));

    // Danh sách bài viết theo thẻ (Tag)
    register_rest_route('thientam/v1', '/news/tag/(?P<slug>[a-zA-Z0-9-]+)', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_news_by_tag',
        'permission_callback' => '__return_true',
    ));

    // Alias lấy bài viết theo thẻ (Tag)
    register_rest_route('thientam/v1', '/news/tags/(?P<slug>[a-zA-Z0-9-]+)', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_news_by_tag',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('thientam/v1', '/tags/(?P<slug>[a-zA-Z0-9-]+)/news', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_news_by_tag',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('thientam/v1', '/tags/(?P<slug>[a-zA-Z0-9-]+)', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_news_by_tag',
        'permission_callback' => '__return_true',
    ));

    // Danh sách bài viết thuộc chuyên mục (Category)
    register_rest_route('thientam/v1', '/news/category/(?P<slug>[a-zA-Z0-9-]+)', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_news_by_category',
        'permission_callback' => '__return_true',
    ));

    // Alias lấy bài viết theo chuyên mục
    register_rest_route('thientam/v1', '/categories/(?P<slug>[a-zA-Z0-9-]+)/news', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_news_by_category',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('thientam/v1', '/categories/(?P<slug>[a-zA-Z0-9-]+)', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_news_by_category',
        'permission_callback' => '__return_true',
    ));

    // Danh sách bài viết
    register_rest_route('thientam/v1', '/news', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_news',
        'permission_callback' => '__return_true',
    ));

    // Chi tiết bài viết theo slug
    register_rest_route('thientam/v1', '/news/(?P<slug>[a-zA-Z0-9-]+)', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_news_detail',
        'permission_callback' => '__return_true',
    ));
}

/**
 * Helper: Trích xuất tên Category chính của Post (Chỉ lấy taxonomy category, loại trừ tags)
 *
 * @param int $post_id
 * @return string
 */
function thientam_get_primary_category_name($post_id)
{
    $categories = get_the_terms($post_id, 'category');
    $cat_name = 'Kiến thức phong thủy';
    if (! empty($categories) && ! is_wp_error($categories)) {
        foreach ($categories as $cat) {
            if ($cat->taxonomy === 'category' && $cat->slug !== 'uncategorized' && $cat->slug !== 'chua-phan-loai') {
                $cat_name = $cat->name;
                break;
            }
        }
        if ($cat_name === 'Kiến thức phong thủy' && isset($categories[0]) && $categories[0]->taxonomy === 'category') {
            $cat_name = $categories[0]->name;
        }
    }
    return html_entity_decode($cat_name, ENT_QUOTES, 'UTF-8');
}

/**
 * Callback REST API lấy danh sách Danh mục bài viết (Chuyên mục / Categories)
 * CHỈ lấy các term thuộc taxonomy 'category', không lấy Thẻ (Tags)
 *
 * @param \WP_REST_Request|object $request
 * @return \WP_REST_Response
 */
function thientam_rest_get_news_categories($request)
{
    $hide_empty_param = $request->get_param('hide_empty');
    $hide_empty = false;
    if ($hide_empty_param !== null && $hide_empty_param !== '') {
        if (function_exists('rest_sanitize_boolean')) {
            $hide_empty = rest_sanitize_boolean($hide_empty_param);
        } else {
            $hide_empty = in_array(strtolower((string) $hide_empty_param), array('1', 'true', 'yes'), true);
        }
    }

    $parent = $request->get_param('parent');
    $orderby = sanitize_text_field($request->get_param('orderby')) ?: 'name';
    $order = strtoupper(sanitize_text_field($request->get_param('order'))) === 'DESC' ? 'DESC' : 'ASC';

    $args = array(
        'taxonomy'   => 'category',
        'hide_empty' => $hide_empty,
        'orderby'    => $orderby,
        'order'      => $order,
    );

    if ($parent !== null && $parent !== '') {
        $args['parent'] = intval($parent);
    }

    // Sử dụng get_categories để chỉ truy vấn bảng Category của WordPress
    $terms = get_categories($args);

    if (is_wp_error($terms)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => $terms->get_error_message(),
            'data'    => array(),
        ), 500);
    }

    $categories = array();

    foreach ($terms as $term) {
        // Kiểm tra nghiêm ngặt: chỉ lấy term thuộc taxonomy 'category' (loại trừ post_tag và custom taxonomy khác)
        if (isset($term->taxonomy) && $term->taxonomy !== 'category') {
            continue;
        }

        // Mặc định bỏ qua chuyên mục chưa phân loại (uncategorized / chua-phan-loai)
        if ($term->slug === 'uncategorized' || $term->slug === 'chua-phan-loai') {
            continue;
        }

        $parent_name = '';
        if ($term->parent > 0) {
            $parent_term = get_term($term->parent, 'category');
            if ($parent_term && ! is_wp_error($parent_term) && isset($parent_term->taxonomy) && $parent_term->taxonomy === 'category') {
                $parent_name = html_entity_decode($parent_term->name, ENT_QUOTES, 'UTF-8');
            }
        }

        $categories[] = array(
            'id'          => (int) $term->term_id,
            'name'        => html_entity_decode($term->name, ENT_QUOTES, 'UTF-8'),
            'slug'        => $term->slug,
            'description' => html_entity_decode($term->description, ENT_QUOTES, 'UTF-8'),
            'count'       => (int) $term->count,
            'parent'      => (int) $term->parent,
            'parent_name' => $parent_name,
            'link'        => get_category_link($term->term_id) && ! is_wp_error(get_category_link($term->term_id)) ? get_category_link($term->term_id) : '',
        );
    }

    return new WP_REST_Response(array(
        'success' => true,
        'count'   => count($categories),
        'data'    => $categories,
    ), 200);
}

/**
 * Callback REST API lấy danh sách Thẻ bài viết (Tags)
 *
 * @param \WP_REST_Request|object $request
 * @return \WP_REST_Response
 */
function thientam_rest_get_news_tags($request)
{
    $hide_empty_param = $request->get_param('hide_empty');
    $hide_empty = false;
    if ($hide_empty_param !== null && $hide_empty_param !== '') {
        if (function_exists('rest_sanitize_boolean')) {
            $hide_empty = rest_sanitize_boolean($hide_empty_param);
        } else {
            $hide_empty = in_array(strtolower((string) $hide_empty_param), array('1', 'true', 'yes'), true);
        }
    }

    $per_page = intval($request->get_param('per_page') ?: $request->get_param('number') ?: 0);
    $search = sanitize_text_field($request->get_param('search') ?: $request->get_param('s') ?: $request->get_param('q'));
    $orderby = sanitize_text_field($request->get_param('orderby')) ?: 'name';
    $order = strtoupper(sanitize_text_field($request->get_param('order'))) === 'DESC' ? 'DESC' : 'ASC';

    $args = array(
        'taxonomy'   => 'post_tag',
        'hide_empty' => $hide_empty,
        'orderby'    => $orderby,
        'order'      => $order,
    );

    if ($per_page > 0) {
        $args['number'] = $per_page;
    }

    if (! empty($search)) {
        $args['search'] = $search;
    }

    $terms = get_tags($args);

    if (is_wp_error($terms)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => $terms->get_error_message(),
            'data'    => array(),
        ), 500);
    }

    $tags = array();
    foreach ($terms as $term) {
        if (isset($term->taxonomy) && $term->taxonomy !== 'post_tag') {
            continue;
        }

        $tags[] = array(
            'id'          => (int) $term->term_id,
            'name'        => html_entity_decode($term->name, ENT_QUOTES, 'UTF-8'),
            'slug'        => $term->slug,
            'description' => html_entity_decode($term->description, ENT_QUOTES, 'UTF-8'),
            'count'       => (int) $term->count,
            'link'        => get_tag_link($term->term_id) && ! is_wp_error(get_tag_link($term->term_id)) ? get_tag_link($term->term_id) : '',
        );
    }

    return new WP_REST_Response(array(
        'success' => true,
        'count'   => count($tags),
        'data'    => $tags,
    ), 200);
}

/**
 * Callback REST API lấy danh sách bài viết theo Thẻ (Tag)
 *
 * Endpoint:
 *   - GET /wp-json/thientam/v1/news/tag/{slug}
 *   - GET /wp-json/thientam/v1/news/tags/{slug}
 *   - GET /wp-json/thientam/v1/tags/{slug}/news
 *   - GET /wp-json/thientam/v1/tags/{slug}
 *
 * @param \WP_REST_Request|object $request
 * @return \WP_REST_Response
 */
function thientam_rest_get_news_by_tag($request)
{
    $slug = sanitize_title($request['slug']);
    $per_page = intval($request->get_param('per_page')) ?: 6;
    $paged = intval($request->get_param('page')) ?: 1;
    $cat_slug = sanitize_text_field($request->get_param('category'));
    $cat_id = intval($request->get_param('category_id'));
    $search = sanitize_text_field($request->get_param('search') ?: $request->get_param('s') ?: $request->get_param('q'));

    // Sắp xếp: Mặc định mới nhất (DESC), nếu chọn cũ nhất (oldest / asc) thì sắp xếp ASC
    $sort = strtolower(sanitize_text_field($request->get_param('sort') ?: $request->get_param('order') ?: ''));
    $orderby = sanitize_text_field($request->get_param('orderby')) ?: 'date';
    $order = in_array($sort, array('oldest', 'asc'), true) ? 'ASC' : 'DESC';

    // Tìm term tag theo slug hoặc ID
    $term = is_numeric($slug) ? get_term((int) $slug, 'post_tag') : get_term_by('slug', $slug, 'post_tag');

    if (! $term || is_wp_error($term) || (isset($term->taxonomy) && $term->taxonomy !== 'post_tag')) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'Thẻ bài viết không tồn tại hoặc đã bị xóa.',
            'tag'     => null,
            'data'    => array(),
        ), 404);
    }

    // Query bài viết theo tag
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
        'tag_id'         => $term->term_id,
        'orderby'        => $orderby,
        'order'          => $order,
    );

    if (! empty($cat_slug)) {
        $args['category_name'] = $cat_slug;
    } elseif ($cat_id > 0) {
        $args['cat'] = $cat_id;
    }

    if (! empty($search)) {
        $args['s'] = $search;
    }

    $query = new WP_Query($args);
    $posts = $query->posts;

    $news_items = array();

    foreach ($posts as $post) {
        $thumbnail_url = get_the_post_thumbnail_url($post->ID, 'large') ?: '';
        $cat_name = thientam_get_primary_category_name($post->ID);
        $author = get_the_author_meta('display_name', $post->post_author) ?: 'Chuyên gia Thiên Tâm';

        // Mô tả ngắn
        $desc = $post->post_excerpt;
        if (empty($desc)) {
            $desc = wp_trim_words(strip_tags($post->post_content), 30, '...');
        }

        // Tags của post
        $post_tags = get_the_tags($post->ID);
        $item_tags = array();
        if (! empty($post_tags) && ! is_wp_error($post_tags)) {
            foreach ($post_tags as $pt) {
                $item_tags[] = html_entity_decode($pt->name, ENT_QUOTES, 'UTF-8');
            }
        }

        $news_items[] = array(
            'id'       => $post->ID,
            'slug'     => $post->post_name,
            'title'    => html_entity_decode($post->post_title, ENT_QUOTES, 'UTF-8'),
            'category' => $cat_name,
            'desc'     => html_entity_decode($desc, ENT_QUOTES, 'UTF-8'),
            'date'     => get_the_date('d/m/Y H:m:i', $post->ID),
            'author'   => html_entity_decode($author, ENT_QUOTES, 'UTF-8'),
            'image'    => $thumbnail_url,
            'tags'     => $item_tags,
        );
    }

    $tag_info = array(
        'id'          => (int) $term->term_id,
        'name'        => html_entity_decode($term->name, ENT_QUOTES, 'UTF-8'),
        'slug'        => $term->slug,
        'description' => html_entity_decode($term->description, ENT_QUOTES, 'UTF-8'),
        'count'       => (int) $term->count,
        'link'        => get_tag_link($term->term_id) && ! is_wp_error(get_tag_link($term->term_id)) ? get_tag_link($term->term_id) : '',
    );

    return new WP_REST_Response(array(
        'success'     => true,
        'tag'         => $tag_info,
        'count'       => count($news_items),
        'total'       => (int) $query->found_posts,
        'total_pages' => (int) $query->max_num_pages,
        'data'        => $news_items,
    ), 200);
}

/**
 * Callback REST API lấy danh sách Tin tức từ Post Type 'post' mặc định
 *
 * @param \WP_REST_Request|object $request
 * @return \WP_REST_Response
 */
function thientam_rest_get_news($request)
{
    $per_page = intval($request->get_param('per_page')) ?: 6;
    $paged = intval($request->get_param('page')) ?: 1;
    $cat_slug = sanitize_text_field($request->get_param('category'));
    $cat_id = intval($request->get_param('category_id'));
    $tag_slug = sanitize_text_field($request->get_param('tag'));
    $tag_id = intval($request->get_param('tag_id'));
    $search = sanitize_text_field($request->get_param('search') ?: $request->get_param('s') ?: $request->get_param('q'));

    // Sắp xếp
    $sort = strtolower(sanitize_text_field($request->get_param('sort') ?: $request->get_param('order') ?: ''));
    $orderby = sanitize_text_field($request->get_param('orderby')) ?: 'date';
    $order = in_array($sort, array('oldest', 'asc'), true) ? 'ASC' : 'DESC';

    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
        'orderby'        => $orderby,
        'order'          => $order,
    );

    if (! empty($cat_slug)) {
        $args['category_name'] = $cat_slug;
    } elseif ($cat_id > 0) {
        $args['cat'] = $cat_id;
    }

    if (! empty($tag_slug)) {
        $args['tag'] = $tag_slug;
    } elseif ($tag_id > 0) {
        $args['tag_id'] = $tag_id;
    }

    if (! empty($search)) {
        $args['s'] = $search;
    }

    $query = new WP_Query($args);
    $posts = $query->posts;

    $news_items = array();

    foreach ($posts as $post) {
        $thumbnail_url = get_the_post_thumbnail_url($post->ID, 'large') ?: '';
        $cat_name = thientam_get_primary_category_name($post->ID);
        $author = get_the_author_meta('display_name', $post->post_author) ?: 'Chuyên gia Thiên Tâm';

        // Mô tả ngắn
        $desc = $post->post_excerpt;
        if (empty($desc)) {
            $desc = wp_trim_words(strip_tags($post->post_content), 30, '...');
        }

        // Tags của post
        $post_tags = get_the_tags($post->ID);
        $item_tags = array();
        if (! empty($post_tags) && ! is_wp_error($post_tags)) {
            foreach ($post_tags as $pt) {
                $item_tags[] = html_entity_decode($pt->name, ENT_QUOTES, 'UTF-8');
            }
        }

        $news_items[] = array(
            'id'       => $post->ID,
            'slug'     => $post->post_name,
            'title'    => html_entity_decode($post->post_title, ENT_QUOTES, 'UTF-8'),
            'category' => $cat_name,
            'desc'     => html_entity_decode($desc, ENT_QUOTES, 'UTF-8'),
            'date'     => get_the_date('d/m/Y H:m:i', $post->ID),
            'author'   => html_entity_decode($author, ENT_QUOTES, 'UTF-8'),
            'image'    => $thumbnail_url,
            'tags'     => $item_tags,
        );
    }

    return new WP_REST_Response(array(
        'success'     => true,
        'count'       => count($news_items),
        'total'       => (int) $query->found_posts,
        'total_pages' => (int) $query->max_num_pages,
        'data'        => $news_items,
    ), 200);
}

/**
 * Callback REST API lấy danh sách bài viết thuộc Chuyên mục (Category)
 *
 * Endpoint:
 *   - GET /wp-json/thientam/v1/news/category/{slug}
 *   - GET /wp-json/thientam/v1/categories/{slug}/news
 *   - GET /wp-json/thientam/v1/categories/{slug}
 *
 * @param \WP_REST_Request|object $request
 * @return \WP_REST_Response
 */
function thientam_rest_get_news_by_category($request)
{
    $slug = sanitize_title($request['slug']);
    $per_page = intval($request->get_param('per_page')) ?: 6;
    $paged = intval($request->get_param('page')) ?: 1;
    $tag_slug = sanitize_text_field($request->get_param('tag'));
    $search = sanitize_text_field($request->get_param('search') ?: $request->get_param('s'));

    // Sắp xếp: Mặc định mới nhất (DESC), nếu chọn cũ nhất (oldest / asc) thì sắp xếp ASC
    $sort = strtolower(sanitize_text_field($request->get_param('sort') ?: $request->get_param('order') ?: ''));
    $orderby = sanitize_text_field($request->get_param('orderby')) ?: 'date';
    $order = in_array($sort, array('oldest', 'asc'), true) ? 'ASC' : 'DESC';

    // Tìm term category theo slug hoặc ID
    $term = is_numeric($slug) ? get_term((int) $slug, 'category') : get_term_by('slug', $slug, 'category');

    if (! $term || is_wp_error($term) || (isset($term->taxonomy) && $term->taxonomy !== 'category')) {
        return new WP_REST_Response(array(
            'success'  => false,
            'message'  => 'Chuyên mục không tồn tại hoặc đã bị xóa.',
            'category' => null,
            'data'     => array(),
        ), 404);
    }

    // Lấy ID term hiện tại và tất cả term con (nếu có)
    $cat_ids = array($term->term_id);
    $child_terms = get_term_children($term->term_id, 'category');
    if (! empty($child_terms) && ! is_wp_error($child_terms)) {
        $cat_ids = array_merge($cat_ids, $child_terms);
    }
    $cat_ids = array_unique(array_filter($cat_ids));

    // Lấy thông tin chi tiết các chuyên mục con trực tiếp
    $direct_children = get_terms(array(
        'taxonomy'   => 'category',
        'parent'     => $term->term_id,
        'hide_empty' => false,
    ));
    $sub_categories = array();
    if (! empty($direct_children) && ! is_wp_error($direct_children)) {
        foreach ($direct_children as $child) {
            $sub_categories[] = array(
                'id'          => (int) $child->term_id,
                'name'        => html_entity_decode($child->name, ENT_QUOTES, 'UTF-8'),
                'slug'        => $child->slug,
                'description' => html_entity_decode($child->description, ENT_QUOTES, 'UTF-8'),
                'count'       => (int) $child->count,
            );
        }
    }

    // Query bài viết
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
        'category__in'   => array_values($cat_ids),
        'orderby'        => $orderby,
        'order'          => $order,
    );

    if (! empty($tag_slug)) {
        $args['tag'] = $tag_slug;
    }

    if (! empty($search)) {
        $args['s'] = $search;
    }

    $query = new WP_Query($args);
    $posts = $query->posts;

    $news_items = array();
    $collected_tags = array();

    foreach ($posts as $post) {
        $thumbnail_url = get_the_post_thumbnail_url($post->ID, 'large') ?: '';
        $cat_name = thientam_get_primary_category_name($post->ID);
        $author = get_the_author_meta('display_name', $post->post_author) ?: 'Chuyên gia Thiên Tâm';

        // Mô tả ngắn
        $desc = $post->post_excerpt;
        if (empty($desc)) {
            $desc = wp_trim_words(strip_tags($post->post_content), 30, '...');
        }

        // Tags của post
        $post_tags = get_the_tags($post->ID);
        $item_tags = array();
        if (! empty($post_tags) && ! is_wp_error($post_tags)) {
            foreach ($post_tags as $pt) {
                $item_tags[] = html_entity_decode($pt->name, ENT_QUOTES, 'UTF-8');
                $collected_tags[$pt->slug] = html_entity_decode($pt->name, ENT_QUOTES, 'UTF-8');
            }
        }

        $news_items[] = array(
            'id'       => $post->ID,
            'slug'     => $post->post_name,
            'title'    => html_entity_decode($post->post_title, ENT_QUOTES, 'UTF-8'),
            'category' => $cat_name,
            'desc'     => html_entity_decode($desc, ENT_QUOTES, 'UTF-8'),
            'date'     => get_the_date('d/m/Y H:m:i', $post->ID),
            'author'   => html_entity_decode($author, ENT_QUOTES, 'UTF-8'),
            'image'    => $thumbnail_url,
            'tags'     => $item_tags,
        );
    }

    $parent_name = '';
    if ($term->parent > 0) {
        $parent_term = get_term($term->parent, 'category');
        if ($parent_term && ! is_wp_error($parent_term) && isset($parent_term->taxonomy) && $parent_term->taxonomy === 'category') {
            $parent_name = html_entity_decode($parent_term->name, ENT_QUOTES, 'UTF-8');
        }
    }

    $category_info = array(
        'id'          => (int) $term->term_id,
        'name'        => html_entity_decode($term->name, ENT_QUOTES, 'UTF-8'),
        'slug'        => $term->slug,
        'description' => html_entity_decode($term->description, ENT_QUOTES, 'UTF-8'),
        'count'       => (int) $term->count,
        'parent'      => (int) $term->parent,
        'parent_name' => $parent_name,
        'link'        => get_category_link($term->term_id) && ! is_wp_error(get_category_link($term->term_id)) ? get_category_link($term->term_id) : '',
    );

    return new WP_REST_Response(array(
        'success'        => true,
        'category'       => $category_info,
        'sub_categories' => $sub_categories,
        'tags'           => array_values($collected_tags),
        'count'          => count($news_items),
        'total'          => (int) $query->found_posts,
        'total_pages'    => (int) $query->max_num_pages,
        'data'           => $news_items,
    ), 200);
}

/**
 * Callback REST API lấy chi tiết bài viết theo Slug
 *
 * @param \WP_REST_Request|object $request
 * @return \WP_REST_Response
 */
function thientam_rest_get_news_detail($request)
{
    $slug = sanitize_title($request['slug']);

    $posts = get_posts(array(
        'name'           => $slug,
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
    ));

    if (empty($posts)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'Bài viết không tồn tại hoặc đã bị gỡ.',
        ), 404);
    }

    $post = $posts[0];
    $thumbnail_url = get_the_post_thumbnail_url($post->ID, 'full') ?: '';
    $placeholder_image = 'https://site.thientam68.com/wp-content/uploads/2026/08/Placeholder-Thien-Tam.png';
    $custom_og_image = get_post_meta($post->ID, 'seo_og_image', true) ?: '';
    $effective_image = $thumbnail_url ?: $placeholder_image;
    $effective_og_image = $custom_og_image ?: ($thumbnail_url ?: $placeholder_image);
    $cat_name = thientam_get_primary_category_name($post->ID);
    $author = get_the_author_meta('display_name', $post->post_author) ?: 'Chuyên gia Thiên Tâm';

    // Tags
    $tags = get_the_tags($post->ID);
    $tag_names = array();
    if (! empty($tags) && ! is_wp_error($tags)) {
        $tag_names = wp_list_pluck($tags, 'name');
    }
    if (empty($tag_names)) {
        $tag_names = array('Phong thủy nhà ở', 'Phòng khách', 'Không gian sống');
    }

    // Mô tả ngắn (Excerpt)
    $desc = $post->post_excerpt;
    if (empty($desc)) {
        $desc = wp_trim_words(strip_tags($post->post_content), 35, '...');
    }

    // Bài viết liên quan (Chỉ lấy cùng chuyên mục cha / con của bài viết hiện tại, trừ bài hiện tại)
    $cat_ids = array();
    $terms = get_the_terms($post->ID, 'category');
    if (! empty($terms) && ! is_wp_error($terms)) {
        foreach ($terms as $term) {
            if ($term->slug !== 'uncategorized' && $term->slug !== 'chua-phan-loai') {
                $cat_ids[] = $term->term_id;

                // Lấy chuyên mục cha cao nhất nếu có
                $ancestors = get_ancestors($term->term_id, 'category');
                if (! empty($ancestors)) {
                    $cat_ids = array_merge($cat_ids, $ancestors);
                    foreach ($ancestors as $ancestor_id) {
                        $children = get_term_children($ancestor_id, 'category');
                        if (! empty($children) && ! is_wp_error($children)) {
                            $cat_ids = array_merge($cat_ids, $children);
                        }
                    }
                }

                // Lấy tất cả chuyên mục con nếu term hiện tại là cha
                $children = get_term_children($term->term_id, 'category');
                if (! empty($children) && ! is_wp_error($children)) {
                    $cat_ids = array_merge($cat_ids, $children);
                }
            }
        }
    }
    $cat_ids = array_unique(array_filter($cat_ids));

    $related_posts = array();
    if (! empty($cat_ids)) {
        $related_posts = get_posts(array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 3,
            'post__not_in'   => array($post->ID),
            'category__in'   => array_values($cat_ids),
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
    }

    $related_items = array();
    foreach ($related_posts as $rel) {
        $rel_thumb = get_the_post_thumbnail_url($rel->ID, 'large') ?: '';
        $rel_cat = thientam_get_primary_category_name($rel->ID);

        $related_items[] = array(
            'id'       => $rel->ID,
            'slug'     => $rel->post_name,
            'title'    => html_entity_decode($rel->post_title, ENT_QUOTES, 'UTF-8'),
            'desc'     => html_entity_decode($rel->post_excerpt, ENT_QUOTES, 'UTF-8'),
            'category' => $rel_cat,
            'date'     => get_the_date('d/m/Y H:m:i', $rel->ID),
            'author'   => get_the_author_meta('display_name', $rel->post_author),
            'image'    => $rel_thumb,
        );
    }

    $detail = array(
        'id'        => $post->ID,
        'slug'      => $post->post_name,
        'title'     => html_entity_decode($post->post_title, ENT_QUOTES, 'UTF-8'),
        'category'  => $cat_name,
        'desc'      => html_entity_decode($desc, ENT_QUOTES, 'UTF-8'),
        'content'   => apply_filters('the_content', $post->post_content),
        'date'      => get_the_date('d/m/Y H:m:i', $post->ID),
        'author'    => html_entity_decode($author, ENT_QUOTES, 'UTF-8'),
        'image'     => $effective_image,
        'og_image'  => $effective_og_image,
        'seo'       => array(
            'title'       => get_post_meta($post->ID, 'seo_meta_title', true) ?: (html_entity_decode($post->post_title, ENT_QUOTES, 'UTF-8') . ' | Tin tức Thiên Tâm'),
            'description' => get_post_meta($post->ID, 'seo_meta_description', true) ?: html_entity_decode($desc, ENT_QUOTES, 'UTF-8'),
            'keywords'    => get_post_meta($post->ID, 'seo_meta_keywords', true) ?: implode(', ', $tag_names),
            'og_image'    => $effective_og_image,
        ),
        'tags'      => $tag_names,
        'related'   => $related_items,
    );

    return new WP_REST_Response(array(
        'success' => true,
        'data'    => $detail,
    ), 200);
}
