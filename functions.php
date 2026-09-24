<?php

/**
 * Theme functions and definitions.
 *
 * @package HelloElementorChild
 */

if (! defined("ABSPATH")) {
    exit; // Exit if accessed directly.
}

define("HELLO_ELEMENTOR_CHILD_VERSION", "1.0.0");

/**
 * Load child theme scripts & styles.
 */
function hello_elementor_child_scripts_styles()
{
    wp_enqueue_style(
        "hello-elementor-child-style",
        get_stylesheet_directory_uri() . "/style.css",
        [
            "hello-elementor-theme-style",
        ],
        HELLO_ELEMENTOR_CHILD_VERSION
    );
}
add_action("wp_enqueue_scripts", "hello_elementor_child_scripts_styles", 20);

/**
 * Tắt hoàn toàn Gutenberg Block Editor (Dùng Classic Editor)
 */
add_filter("use_block_editor_for_post", "__return_false", 10);
add_filter("use_block_editor_for_post_type", "__return_false", 10);
add_filter("use_widgets_block_editor", "__return_false");

// Xóa CSS mặc định của Gutenberg ở frontend để web nhẹ hơn
add_action("wp_enqueue_scripts", function () {
    wp_dequeue_style("wp-block-library");
    wp_dequeue_style("wp-block-library-theme");
    wp_dequeue_style("global-styles");
}, 100);

/**
 * Nạp khung nhập liệu chi tiết dịch vụ NATIVE thuần WordPress (KHÔNG CẦN PLUGIN)
 */
require_once get_stylesheet_directory() . "/inc/native-service-metabox.php";
/**
 * Nạp REST API endpoints cho Chi tiết dịch vụ
 */
require_once get_stylesheet_directory() . "/inc/api-service-detail.php";
/**
 * Nạp Post Type & REST API Cảm nhận khách hàng (Testimonials)
 */
require_once get_stylesheet_directory() . "/inc/post-type-testimonial.php";
/**
 * Nạp Post Type & REST API Tin tức & Tri thức (News)
 */
require_once get_stylesheet_directory() . "/inc/post-type-news.php";
/**
 * Nạp Post Type, ACF Field Groups & REST API Gói đào tạo (Training)
 */
require_once get_stylesheet_directory() . "/inc/post-type-training.php";
/**
 * Nạp Post Type, ACF Field Groups & REST API Tuyển dụng (Recruitment)
 */
require_once get_stylesheet_directory() . "/inc/post-type-recruitment.php";
/**
 * Nạp Post Type & REST API Tiếp nhận Form Submissions (Leads)
 */
require_once get_stylesheet_directory() . "/inc/post-type-submission.php";
// Nạp Cấu hình Gửi Email SMTP & Thông báo Lead (Native SMTP Settings)
require_once get_stylesheet_directory() . "/inc/smtp-settings.php";
/**
 * Nạp Cấu hình & ACF Fields cho các trang Landing Page
 */
require_once get_stylesheet_directory() . "/inc/acf-landing-pages.php";
/**
 * Nạp Cài đặt Website & Nội dung (Site Settings & Localization)
 */
require_once get_stylesheet_directory() . "/inc/site-settings.php";
/**
 * Nạp REST API Endpoints cho Cài đặt Website (/wp-json/thientam/v1/settings)
 */
require_once get_stylesheet_directory() . "/inc/api-site-settings.php";
/**
 * Nạp Hệ thống Webhook On-Demand ISR Revalidation (WordPress -> Next.js)
 */
require_once get_stylesheet_directory() . "/inc/webhook-revalidate.php";




/**
 * Hỗ trợ Thumbnail / Ảnh đại diện (Featured Image) & Excerpt cho Page và Post
 */
add_action("after_setup_theme", function () {
    add_theme_support("post-thumbnails");
    add_post_type_support("page", "thumbnail");
    add_post_type_support("page", "excerpt");
});

/**
 * Giới hạn tối đa 5 bản sao lưu chỉnh sửa (Revisions) để tối ưu cơ sở dữ liệu
 */
add_filter("wp_revisions_to_keep", function ($num, $post) {
    return 5;
}, 10, 2);

/**
 * Điều hướng toàn bộ giao diện Frontend WordPress hiển thị màn hình Logo Thiên Tâm
 * (Vì frontend chính đã chạy trên Next.js độc lập)
 */
add_filter("template_include", function ($template) {
    if (! is_admin() && ! defined("REST_REQUEST")) {
        $index_splash = get_stylesheet_directory() . "/index.php";
        if (file_exists($index_splash)) {
            return $index_splash;
        }
    }
    return $template;
}, 99);

/**
 * Tắt hoàn toàn tính năng Bình luận (Comments) trên toàn bộ hệ thống WordPress
 */
// 1. Đóng comments và trackbacks ở frontend
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);
add_filter('comments_array', '__return_empty_array', 10, 2);

// 2. Ẩn menu Bình luận (Comments) trong WP Admin
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

// 3. Chặn truy cập trực tiếp vào trang quản trị bình luận & gỡ support comments ở các post types
add_action('admin_init', function () {
    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
        wp_safe_redirect(admin_url());
        exit;
    }
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

// 4. Xóa icon bình luận trên Admin Bar
add_action('admin_bar_menu', function ($wp_admin_bar) {
    $wp_admin_bar->remove_node('comments');
}, 999);

// 5. Xóa widget Recent Comments ở Dashboard
add_action('wp_dashboard_setup', function () {
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
});

// 6. Chặn gửi bình luận qua POST request
add_action('pre_comment_on_post', function () {
    wp_die('Bình luận đã bị vô hiệu hóa trên website Thiên Tâm.', '', array('response' => 403));
});
