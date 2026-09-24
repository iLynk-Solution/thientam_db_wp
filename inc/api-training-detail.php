<?php

/**
 * REST API Endpoints for Training Detail Pages (Thiên Tâm)
 * Hỗ trợ các endpoint:
 *   - GET /wp-json/thientam/v1/training
 *   - GET /wp-json/thientam/v1/training/{slug}
 */

if (! defined("ABSPATH")) {
    exit;
}

add_action("rest_api_init", "thientam_register_training_rest_routes");
function thientam_register_training_rest_routes()
{
    // Danh sách tất cả các gói đào tạo
    register_rest_route("thientam/v1", "/training", array(
        "methods"             => "GET",
        "callback"            => "thientam_rest_get_all_training_pages",
        "permission_callback" => "__return_true",
    ));

    // Chi tiết gói đào tạo theo slug
    register_rest_route("thientam/v1", "/training/(?P<slug>[a-zA-Z0-9-_]+)", array(
        "methods"             => "GET",
        "callback"            => "thientam_rest_get_training_page_by_slug",
        "permission_callback" => "__return_true",
    ));

    // Mở rộng trường training_details vào WP REST API mặc định: /wp-json/wp/v2/pages
    register_rest_field("page", "training_details", array(
        "get_callback"    => function ($post_arr) {
            return thientam_format_training_page_data($post_arr["id"]);
        },
        "schema"          => null,
    ));
}

/**
 * Helper: Định dạng dữ liệu một trang đào tạo trả về cho Frontend
 */
function thientam_format_training_page_data($post_id)
{
    clean_post_cache($post_id);
    $post = get_post($post_id);
    if (! $post) {
        return null;
    }

    $subtitle = get_field("training_subtitle", $post_id) ?: get_post_meta($post_id, "training_subtitle", true) ?: get_post_meta($post_id, "_training_subtitle", true) ?: "";
    $level = get_field($post_id) ?: get_post_meta($post_id, true) ?: get_post_meta($post_id, "_training_level", true) ?: "Cơ bản - Nhập môn";
    $duration = get_field("training_duration", $post_id) ?: get_post_meta($post_id, "training_duration", true) ?: get_post_meta($post_id, "_training_duration", true) ?: "";
    $summary = get_field("training_summary", $post_id) ?: get_post_meta($post_id, "training_summary", true) ?: get_post_meta($post_id, "_training_summary", true) ?: $post->post_excerpt ?: "";
    $target_audience = get_field("training_target_audience", $post_id) ?: get_post_meta($post_id, "training_target_audience", true) ?: get_post_meta($post_id, "_training_target_audience", true) ?: "";

    // Metrics
    $raw_metrics = get_field("training_metrics", $post_id);
    $metrics = array();
    if (! empty($raw_metrics) && is_array($raw_metrics)) {
        foreach ($raw_metrics as $m) {
            $metrics[] = array(
                "value" => $m["value"] ?? "",
                "desc"  => $m["desc"] ?? "",
            );
        }
    } else {
        $meta_metrics = get_post_meta($post_id, "training_metrics", true) ?: get_post_meta($post_id, "_training_metrics", true);
        if (! empty($meta_metrics) && is_array($meta_metrics)) {
            $metrics = $meta_metrics;
        }
    }

    // Audience Items
    $raw_audience = get_field("training_audience_items", $post_id);
    $audience_items = array();
    if (! empty($raw_audience) && is_array($raw_audience)) {
        foreach ($raw_audience as $a) {
            $audience_items[] = is_array($a) ? ($a["item"] ?? "") : $a;
        }
    } else {
        $meta_audience = get_post_meta($post_id, "training_audience_items", true) ?: get_post_meta($post_id, "_training_audience_items", true);
        if (! empty($meta_audience) && is_array($meta_audience)) {
            $audience_items = $meta_audience;
        }
    }

    // Benefit Items
    $raw_benefits = get_field("training_benefit_items", $post_id);
    $benefit_items = array();
    if (! empty($raw_benefits) && is_array($raw_benefits)) {
        foreach ($raw_benefits as $b) {
            $benefit_items[] = array(
                "highlight" => $b["highlight"] ?? "",
                "text"      => $b["text"] ?? "",
            );
        }
    } else {
        $meta_benefits = get_post_meta($post_id, "training_benefit_items", true) ?: get_post_meta($post_id, "_training_benefit_items", true);
        if (! empty($meta_benefits) && is_array($meta_benefits)) {
            $benefit_items = $meta_benefits;
        }
    }

    // Lessons
    $raw_lessons = get_field("training_lessons", $post_id);
    $lessons = array();
    if (! empty($raw_lessons) && is_array($raw_lessons)) {
        foreach ($raw_lessons as $l) {
            $lessons[] = is_array($l) ? ($l["lesson_title"] ?? "") : $l;
        }
    } else {
        $meta_lessons = get_post_meta($post_id, "training_lessons", true) ?: get_post_meta($post_id, "_training_lessons", true);
        if (! empty($meta_lessons) && is_array($meta_lessons)) {
            $lessons = $meta_lessons;
        }
    }

    $thumbnail_id = get_post_thumbnail_id($post_id);
    $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, "full") : "";
    $placeholder_image = "https://site.thientam68.com/wp-content/uploads/2026/08/Placeholder-Thien-Tam.png";

    $seo_og_image = get_post_meta($post_id, "training_seo_og_image", true)
        ?: get_post_meta($post_id, "seo_og_image", true)
        ?: "";
    $seo_title = get_post_meta($post_id, "training_seo_meta_title", true)
        ?: get_post_meta($post_id, "seo_meta_title", true)
        ?: "";
    $seo_desc = get_post_meta($post_id, "training_seo_meta_description", true)
        ?: get_post_meta($post_id, "seo_meta_description", true)
        ?: "";
    $seo_keywords = get_post_meta($post_id, "training_seo_meta_keywords", true)
        ?: get_post_meta($post_id, "seo_meta_keywords", true)
        ?: "";

    // Thứ tự ưu tiên ảnh share: 1. Custom OG Image từ metabox -> 2. Thumbnail WP -> 3. Ảnh Placeholder Thiên Tâm
    $effective_image = $thumbnail_url ?: $placeholder_image;
    $effective_og_image = $seo_og_image ?: ($thumbnail_url ?: $placeholder_image);

    return array(
        "slug"           => $post->post_name,
        "category"       => html_entity_decode($post->post_title, ENT_QUOTES, "UTF-8"),
        "subtitle"       => html_entity_decode($subtitle, ENT_QUOTES, "UTF-8"),
        "isCategory"     => true,
        "level"          => $level,
        "duration"       => $duration,
        "summary"        => html_entity_decode($summary, ENT_QUOTES, "UTF-8"),
        "targetAudience" => html_entity_decode($target_audience, ENT_QUOTES, "UTF-8"),
        "audienceItems"  => $audience_items,
        "benefitItems"   => $benefit_items,
        "metrics"        => $metrics,
        "lessons"        => $lessons,
        "image"          => $effective_image,
        "og_image"       => $effective_og_image,
        "seo"            => array(
            "title"       => $seo_title ?: (html_entity_decode($post->post_title, ENT_QUOTES, "UTF-8") . " - Đào tạo Tử Vi Thiên Tâm"),
            "description" => $seo_desc ?: html_entity_decode($summary, ENT_QUOTES, "UTF-8"),
            "keywords"    => $seo_keywords,
            "og_image"    => $effective_og_image,
        ),
    );
}

/**
 * REST Handler: Lấy danh sách tất cả các trang đào tạo
 */
function thientam_rest_get_all_training_pages()
{
    $posts = get_posts(array(
        "post_type"      => "page",
        "post_status"    => "publish",
        "posts_per_page" => -1,
        "meta_key"       => "_wp_page_template",
        "meta_value"     => "template-training-detail.php",
        "orderby"        => "menu_order date",
        "order"          => "ASC",
    ));

    $packages = array();
    foreach ($posts as $post) {
        $packages[] = thientam_format_training_page_data($post->ID);
    }

    return new WP_REST_Response(array(
        "success" => true,
        "data"    => $packages,
        "count"   => count($packages),
    ), 200);
}

/**
 * REST Handler: Lấy chi tiết trang đào tạo theo slug
 */
function thientam_rest_get_training_page_by_slug($request)
{
    $slug = sanitize_title($request["slug"]);

    $posts = get_posts(array(
        "post_type"      => "page",
        "name"           => $slug,
        "post_status"    => "publish",
        "posts_per_page" => 1,
    ));

    if (empty($posts)) {
        return new WP_REST_Response(array(
            "success" => false,
            "message" => "Không tìm thấy gói đào tạo với slug: " . $slug,
        ), 404);
    }

    $package = thientam_format_training_page_data($posts[0]->ID);

    return new WP_REST_Response(array(
        "success" => true,
        "data"    => $package,
    ), 200);
}
