<?php
/**
 * Plugin Name: Quản lý Hệ thống Landing Pages (Thiên Tâm)
 * Description: Bộ điều phối trung tâm cho tất cả các trang Landing Page chuyên biệt của Thiên Tâm.
 * Version: 2.1.0
 * Author: Thiên Tâm Team
 */

if (! defined('ABSPATH')) {
    exit;
}

// 1. Nạp common helpers
require_once __DIR__ . '/common.php';

// 2. Tự động nạp api.php của từng landing page
foreach (thientam_get_supported_landing_pages() as $slug => $info) {
    if (! empty($info['dir']) && file_exists($info['dir'] . '/api.php')) {
        require_once $info['dir'] . '/api.php';
    }
}

/**
 * 3. Đăng ký Meta Box Quản lý Landing Page trong WP Admin
 */
add_action('add_meta_boxes', 'thientam_register_landing_metaboxes', 10, 2);
function thientam_register_landing_metaboxes($post_type, $post)
{
    if ($post_type !== 'page' || ! $post) {
        return;
    }

    // Metabox chọn loại Landing ở Sidebar bên phải
    add_meta_box(
        'thientam_landing_tools_box',
        'Mẫu Landing Page',
        'thientam_render_landing_sidebar_metabox',
        'page',
        'side',
        'high'
    );

    // Metabox chi tiết nội dung Landing ở vùng chính (hiển thị khi chọn landing hợp lệ)
    $landing_type = get_post_meta($post->ID, 'thientam_landing_type', true);
    $landings = thientam_get_supported_landing_pages();
    if ($landing_type && $landing_type !== 'none' && isset($landings[$landing_type])) {
        add_meta_box(
            'thientam_landing_content_box',
            'Cấu hình Nội dung: ' . esc_html($landings[$landing_type]['name']),
            'thientam_render_landing_content_metabox',
            'page',
            'normal',
            'high'
        );
    }
}

// Ẩn Content Editor và Excerpt mặc định khi trang là Landing Page
add_action('admin_init', 'thientam_hide_editor_excerpt_for_landing');
function thientam_hide_editor_excerpt_for_landing()
{
    $post_id = isset($_GET['post']) ? intval($_GET['post']) : (isset($_POST['post_ID']) ? intval($_POST['post_ID']) : 0);
    if (! $post_id) {
        return;
    }
    $landing_type = get_post_meta($post_id, 'thientam_landing_type', true);
    if ($landing_type && $landing_type !== 'none') {
        remove_post_type_support('page', 'editor');
        remove_post_type_support('page', 'excerpt');
    }
}

// Ẩn/Hiện động Content Editor và Excerpt ngay khi thay đổi dropdown Landing Type
add_action('admin_footer-post.php', 'thientam_landing_metabox_toggle_js');
add_action('admin_footer-post-new.php', 'thientam_landing_metabox_toggle_js');
function thientam_landing_metabox_toggle_js()
{
    global $post;
    if (! $post || $post->post_type !== 'page') {
        return;
    }
?>
    <script>
        (function($) {
            function checkLandingType() {
                var landingType = $('#thientam_landing_type').val();
                if (landingType && landingType !== 'none') {
                    $('#postdivrich').hide();
                    $('#postexcerpt').hide();
                } else {
                    $('#postdivrich').show();
                    $('#postexcerpt').show();
                }
            }

            $(document).ready(function() {
                checkLandingType();
                $('#thientam_landing_type').on('change', checkLandingType);
            });
        })(jQuery);
    </script>
<?php
}

/**
 * Render Sidebar Metabox: Cho phép chọn loại Landing trực tiếp
 */
function thientam_render_landing_sidebar_metabox($post)
{
    wp_nonce_field('thientam_save_landing_meta', 'thientam_landing_meta_nonce');

    $landing_type = get_post_meta($post->ID, 'thientam_landing_type', true) ?: 'none';
    $landings = thientam_get_supported_landing_pages();
?>
    <div style="font-size:13px; line-height:1.5;">
        <label for="thientam_landing_type" style="font-weight:600; display:block; margin-bottom:6px; color:#1d2327;">
            Chọn loại trang Landing:
        </label>
        <select name="thientam_landing_type" id="thientam_landing_type" style="width:100%; margin-bottom:12px; height:32px;">
            <option value="none" <?php selected($landing_type, 'none'); ?>>— Không dùng (Trang thường) —</option>
            <?php foreach ($landings as $slug => $info) : ?>
                <option value="<?php echo esc_attr($slug); ?>" <?php selected($landing_type, $slug); ?>>
                    <?php echo esc_html($info['name']); ?> (<?php echo esc_html($slug); ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($landing_type !== 'none' && isset($landings[$landing_type])) : ?>
            <?php $api_url = rest_url("thientam/v1/landing/{$landing_type}"); ?>
            <div style="background:#f0f6fc; border:1px solid #cce5ff; border-radius:6px; padding:10px; margin-bottom:12px;">
                <p style="margin:0 0 6px 0; color:#0c5460; font-size:12px;">
                    <strong>Đang kích hoạt:</strong> <?php echo esc_html($landings[$landing_type]['name']); ?>
                </p>
                <p style="margin:0 0 8px 0; font-size:11px; color:#666;">
                    Route Next.js: <code><?php echo esc_html($landings[$landing_type]['route']); ?></code>
                </p>
                <a href="<?php echo esc_url($api_url); ?>" target="_blank" class="button button-secondary button-small" style="width:100%; text-align:center; display:block;">
                    🔍 Xem REST API JSON
                </a>
            </div>
        <?php else : ?>
            <p style="color:#666; font-size:12px; margin:0;">
                Hãy chọn <strong>Mẫu Landing Page</strong> và bấm <strong>Cập nhật / Lưu nháp</strong> để tải bảng chỉnh sửa nội dung chi tiết.
            </p>
        <?php endif; ?>
    </div>
<?php
}

/**
 * Render Giao diện Nội dung Metabox: Gọi module tương ứng của landing
 */
function thientam_render_landing_content_metabox($post)
{
    $landings = thientam_get_supported_landing_pages();
    $current_slug = get_post_meta($post->ID, 'thientam_landing_type', true) ?: 'hieu-con-de-dong-hanh';

    if (isset($landings[$current_slug]) && file_exists($landings[$current_slug]['dir'] . '/metabox.php')) {
        $slug = $current_slug;
        include $landings[$current_slug]['dir'] . '/metabox.php';
    } else {
        echo '<div style="padding:20px; text-align:center; color:#64748b;">Vui lòng chọn loại Landing Page hợp lệ ở cột bên phải để bắt đầu cấu hình.</div>';
    }
}

/**
 * 4. Xử lý Lưu dữ liệu Post Meta khi Admin bấm Cập nhật Trang
 */
add_action('save_post_page', 'thientam_save_landing_orchestrator_metabox_data', 10, 2);
function thientam_save_landing_orchestrator_metabox_data($post_id, $post)
{
    if (! isset($_POST['thientam_landing_meta_nonce']) || ! wp_verify_nonce($_POST['thientam_landing_meta_nonce'], 'thientam_save_landing_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }
    if (! current_user_can('edit_page', $post_id)) {
        return;
    }

    $landings = thientam_get_supported_landing_pages();
    $slug = 'none';

    // 1. Lưu loại Landing Page
    if (isset($_POST['thientam_landing_type'])) {
        $slug = sanitize_text_field(wp_unslash($_POST['thientam_landing_type']));
        update_post_meta($post_id, 'thientam_landing_type', $slug);
    }

    if ($slug === 'none') {
        return;
    }

    // 2. Chuyển tiếp cho module save.php tương ứng xử lý
    if (isset($landings[$slug]) && file_exists($landings[$slug]['dir'] . '/save.php')) {
        include $landings[$slug]['dir'] . '/save.php';
    }
}

/**
 * 5. REST API Endpoints Router
 */
add_action('rest_api_init', 'thientam_register_landing_rest_routes');
function thientam_register_landing_rest_routes()
{
    // A. Danh sách các trang landing: GET /wp-json/thientam/v1/landings
    register_rest_route('thientam/v1', '/landings', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_landing_route_all',
        'permission_callback' => '__return_true',
    ));

    // B. Chi tiết 1 trang landing theo slug: GET /wp-json/thientam/v1/landing/{slug}
    register_rest_route('thientam/v1', '/landing/(?P<slug>[a-zA-Z0-9-_]+)', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_landing_route_detail',
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

    // C. Khởi tạo dữ liệu vào Database: POST/GET /wp-json/thientam/v1/landing/{slug}/init
    register_rest_route('thientam/v1', '/landing/(?P<slug>[a-zA-Z0-9-_]+)/init', array(
        'methods'             => array('POST', 'GET'),
        'callback'            => 'thientam_rest_landing_route_init',
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
}

function thientam_rest_landing_route_all($request)
{
    $landings = thientam_get_supported_landing_pages();
    $result = array();

    foreach ($landings as $slug => $info) {
        $page_id = thientam_get_landing_page_id($slug);

        $result[] = array(
            'slug'         => $slug,
            'name'         => $info['name'],
            'route'        => $info['route'],
            'has_wp_page'  => (bool) $page_id,
            'page_id'      => $page_id ?: null,
            'page_title'   => $page_id ? get_the_title($page_id) : null,
            'api_endpoint' => rest_url("thientam/v1/landing/{$slug}"),
            'init_endpoint'=> rest_url("thientam/v1/landing/{$slug}/init"),
        );
    }

    return new WP_REST_Response(array(
        'success' => true,
        'count'   => count($result),
        'data'    => $result,
    ), 200);
}

function thientam_rest_landing_route_detail($request)
{
    $slug = sanitize_title($request->get_param('slug'));
    $landings = thientam_get_supported_landing_pages();

    if (! isset($landings[$slug])) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => "Không tìm thấy trang landing '{$slug}' trong hệ thống.",
        ), 404);
    }

    $page_id = thientam_get_landing_page_id($slug);

    if (file_exists($landings[$slug]['dir'] . '/api.php')) {
        require_once $landings[$slug]['dir'] . '/api.php';
    }

    $func_name = 'thientam_landing_get_data_' . str_replace('-', '_', $slug);
    if (! function_exists($func_name)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => "Chưa cấu hình hàm xử lý dữ liệu cho landing page '{$slug}'.",
        ), 500);
    }

    $res = call_user_func($func_name, $page_id, $slug);

    if ($request->get_param('raw') === '1') {
        return new WP_REST_Response($res['data'] ?? array(), 200);
    }

    return new WP_REST_Response(array(
        'success'       => true,
        'slug'          => $slug,
        'name'          => $landings[$slug]['name'],
        'page_id'       => $page_id ?: null,
        'is_customized' => $res['is_customized'] ?? false,
        'updated_at'    => $res['updated_at'] ?? null,
        'data'          => $res['data'] ?? array(),
    ), 200);
}

function thientam_rest_landing_route_init($request)
{
    $slug = sanitize_title($request->get_param('slug'));
    $landings = thientam_get_supported_landing_pages();

    if (! isset($landings[$slug])) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => "Không tìm thấy trang landing '{$slug}' trong hệ thống.",
        ), 404);
    }

    $page_id = thientam_get_landing_page_id($slug);
    if (! $page_id) {
        $page = get_page_by_path($slug);
        if ($page) {
            $page_id = $page->ID;
            update_post_meta($page_id, 'thientam_landing_type', $slug);
        }
    }

    if (! $page_id) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => "Chưa có trang Page nào trong WordPress được gán loại landing '{$slug}'.",
        ), 400);
    }

    if (file_exists($landings[$slug]['dir'] . '/api.php')) {
        require_once $landings[$slug]['dir'] . '/api.php';
    }

    $func_name = 'thientam_landing_init_' . str_replace('-', '_', $slug);
    if (! function_exists($func_name)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => "Hàm khởi tạo cho landing page '{$slug}' chưa sẵn sàng.",
        ), 500);
    }

    $ok = call_user_func($func_name, $page_id);

    return new WP_REST_Response(array(
        'success'    => (bool) $ok,
        'message'    => $ok ? "Khởi tạo thành công toàn bộ dữ liệu vào Database WordPress cho trang ID {$page_id}." : "Khởi tạo thất bại.",
        'page_id'    => $page_id,
        'slug'       => $slug,
        'updated_at' => current_time('mysql'),
    ), $ok ? 200 : 500);
}
