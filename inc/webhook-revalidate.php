<?php

/**
 * Webhook On-Demand ISR Cache Revalidation (WordPress ➔ Next.js)
 *
 * Tự động gửi thông báo qua Webhook tới Next.js (/api/revalidate) để xóa cache
 * và tái tạo lại trang tĩnh tức thì (On-Demand ISR) mỗi khi:
 * - Thêm / Sửa / Xóa bài viết Tin tức, Dịch vụ, Đào tạo, Tuyển dụng, Đánh giá
 * - Thay đổi cấu hình Cài đặt Website (Site Settings & Localization)
 *
 * @package HelloElementorChild
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Lấy cấu hình URL Webhook Next.js
 */
function thientam_get_webhook_url()
{
    $options = get_option('thientam_site_options', []);
    $url = $options['webhook']['url'] ?? '';
    if (empty($url)) {
        $url = 'https://thientam68.com/api/revalidate';
    }
    return esc_url_raw(trim($url));
}

/**
 * Lấy mã Secret Token xác thực Webhook
 */
function thientam_get_webhook_secret()
{
    $options = get_option('thientam_site_options', []);
    $secret = $options['webhook']['secret'] ?? '';
    if (empty($secret)) {
        $secret = 'thientam_revalidate_secret_2026';
    }
    return trim($secret);
}

/**
 * Kiểm tra Webhook có được kích hoạt không
 */
function thientam_is_webhook_enabled()
{
    $options = get_option('thientam_site_options', []);
    if (! isset($options['webhook']['enabled'])) {
        return true; // Mặc định BẬT
    }
    return ! empty($options['webhook']['enabled']);
}

/**
 * Gửi yêu cầu xóa cache sang Next.js (Chạy ngầm Non-blocking)
 *
 * @param array  $paths Danh sách đường dẫn URL cần xóa cache (ví dụ: ['/', '/tin-tuc'])
 * @param array  $tags  Danh sách Next.js cache tags cần invalidate (ví dụ: ['news', 'site-settings'])
 * @param string $type  Loại xóa cache: 'page' hoặc 'layout'
 * @return bool
 */
function thientam_trigger_nextjs_revalidation(array $paths = [], array $tags = [], string $type = 'page')
{
    if (! thientam_is_webhook_enabled()) {
        return false;
    }

    $url = thientam_get_webhook_url();
    $secret = thientam_get_webhook_secret();

    if (empty($url) || empty($secret)) {
        return false;
    }

    $clean_paths = array_values(array_unique(array_filter(array_map('trim', $paths))));
    $clean_tags  = array_values(array_unique(array_filter(array_map('trim', $tags))));

    if (empty($clean_paths) && empty($clean_tags)) {
        $clean_paths = ['/'];
    }

    $payload = [
        'secret' => $secret,
        'paths'  => $clean_paths,
        'tags'   => $clean_tags,
        'type'   => $type === 'layout' ? 'layout' : 'page',
    ];

    // Gửi bất đồng bộ (non-blocking) với timeout 3s để không bao giờ làm chậm thao tác lưu bài viết của Admin
    wp_remote_post($url, [
        'headers'     => [
            'Content-Type'        => 'application/json; charset=utf-8',
            'x-revalidate-secret' => $secret,
        ],
        'body'        => wp_json_encode($payload),
        'timeout'     => 3,
        'blocking'    => false,
        'sslverify'   => false,
    ]);

    return true;
}

/**
 * Gửi yêu cầu xóa cache đồng bộ (Dùng cho Nút bấm thủ công "Xóa Cache Ngay" trong Admin)
 */
function thientam_purge_nextjs_cache_sync(array $paths = ['/'], array $tags = ['site-settings'], string $type = 'layout')
{
    $url = thientam_get_webhook_url();
    $secret = thientam_get_webhook_secret();

    if (empty($url) || empty($secret)) {
        return [
            'success' => false,
            'message' => 'Chưa cấu hình URL hoặc Secret Token của Next.js Revalidation Webhook.',
        ];
    }

    $payload = [
        'secret' => $secret,
        'paths'  => $paths,
        'tags'   => $tags,
        'type'   => $type,
    ];

    $response = wp_remote_post($url, [
        'headers'     => [
            'Content-Type'        => 'application/json; charset=utf-8',
            'x-revalidate-secret' => $secret,
        ],
        'body'        => wp_json_encode($payload),
        'timeout'     => 8,
        'blocking'    => true,
        'sslverify'   => false,
    ]);

    if (is_wp_error($response)) {
        return [
            'success' => false,
            'message' => 'Lỗi kết nối tới Next.js: ' . $response->get_error_message(),
        ];
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $json = json_decode($body, true);

    if ($status_code === 200 && ! empty($json['success'])) {
        return [
            'success' => true,
            'message' => $json['message'] ?? 'Đã xóa toàn bộ cache Next.js thành công!',
            'data'    => $json,
        ];
    }

    if ($status_code === 404) {
        return [
            'success' => false,
            'message' => 'Mã lỗi HTTP 404 (Not Found): Endpoint /api/revalidate chưa có trên máy chủ https://thientam68.com. Bạn cần deploy mã nguồn Next.js mới lên server production để tính năng xóa cache hoạt động.',
        ];
    }

    if ($status_code === 401) {
        return [
            'success' => false,
            'message' => 'Mã lỗi HTTP 401 (Unauthorized): Mã Secret Token không khớp với REVALIDATION_SECRET trong .env của Next.js.',
        ];
    }

    $clean_msg = $json['message'] ?? (strlen($body) > 120 ? substr(strip_tags($body), 0, 120) . '...' : strip_tags($body));

    return [
        'success' => false,
        'message' => 'Next.js trả về mã lỗi HTTP ' . $status_code . ': ' . ($clean_msg ?: 'Không thể phản hồi'),
    ];
}

/**
 * Lắng nghe sự kiện Lưu bài viết (save_post)
 */
add_action('save_post', function ($post_id, $post, $update) {
    // Bỏ qua nếu là autosave hoặc revision
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }
    // Chỉ kích hoạt khi bài viết được xuất bản (publish)
    if ($post->post_status !== 'publish' && $post->post_status !== 'trash') {
        return;
    }

    $slug = $post->post_name;
    $post_type = $post->post_type;

    switch ($post_type) {
        case 'post': // Tin tức & Tri thức
            thientam_trigger_nextjs_revalidation(
                ['/', '/tin-tuc', '/tin-tuc/' . $slug],
                ['news', 'news-' . $slug]
            );
            break;

        case 'service': // Dịch vụ
            thientam_trigger_nextjs_revalidation(
                ['/', '/dich-vu', '/dich-vu/' . $slug],
                ['services', 'service-' . $slug]
            );
            break;

        case 'training': // Đào tạo
            thientam_trigger_nextjs_revalidation(
                ['/', '/dao-tao', '/dao-tao/' . $slug],
                ['training', 'training-' . $slug]
            );
            break;

        case 'recruitment': // Tuyển dụng
            thientam_trigger_nextjs_revalidation(
                ['/tuyen-dung', '/tuyen-dung/' . $slug],
                ['recruitment', 'recruitment-' . $slug]
            );
            break;

        case 'testimonial': // Đánh giá khách hàng
            thientam_trigger_nextjs_revalidation(
                ['/'],
                ['testimonials']
            );
            break;

        case 'page': // Trang tĩnh
            $page_path = ($slug === 'home' || $slug === 'trang-chu') ? '/' : '/' . $slug;
            thientam_trigger_nextjs_revalidation(
                [$page_path, '/'],
                ['site-settings']
            );
            break;
    }
}, 20, 3);

/**
 * Lắng nghe sự kiện Xóa hoặc Khôi phục bài viết (trashed / untrashed / deleted)
 */
add_action('trashed_post', 'thientam_handle_post_status_change');
add_action('untrashed_post', 'thientam_handle_post_status_change');
add_action('deleted_post', 'thientam_handle_post_status_change');

function thientam_handle_post_status_change($post_id)
{
    $post_type = get_post_type($post_id);
    if (! in_array($post_type, ['post', 'service', 'training', 'recruitment', 'testimonial', 'page'], true)) {
        return;
    }
    // Xóa cache trang chủ và trang danh mục tương ứng
    $paths = ['/'];
    $tags = [];
    if ($post_type === 'post') {
        $paths[] = '/tin-tuc';
        $tags[] = 'news';
    } elseif ($post_type === 'service') {
        $paths[] = '/dich-vu';
        $tags[] = 'services';
    } elseif ($post_type === 'training') {
        $paths[] = '/dao-tao';
        $tags[] = 'training';
    } elseif ($post_type === 'recruitment') {
        $paths[] = '/tuyen-dung';
        $tags[] = 'recruitment';
    }
    thientam_trigger_nextjs_revalidation($paths, $tags);
}

/**
 * AJAX Handler: Xử lý nút bấm "Xóa Cache Next.js Ngay" trong trang Quản trị Cài đặt
 */
add_action('wp_ajax_thientam_purge_nextjs_cache', function () {
    check_ajax_referer('thientam_purge_cache_nonce', 'security');

    if (! current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Bạn không có quyền thực hiện thao tác này.']);
    }

    $result = thientam_purge_nextjs_cache_sync(['/'], ['site-settings', 'news', 'services', 'training', 'recruitment', 'testimonials'], 'layout');

    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
});

/**
 * Thêm nút "⚡ Xóa Cache Next.js" trực tiếp trên thanh Admin Bar trên cùng màn hình
 */
add_action('admin_bar_menu', function ($wp_admin_bar) {
    if (! current_user_can('manage_options')) {
        return;
    }
    $wp_admin_bar->add_node([
        'id'    => 'thientam_quick_purge_cache',
        'title' => '<span class="ab-icon dashicons dashicons-update" style="top: 2px;"></span> <span class="ab-label">⚡ Xóa Cache Next.js</span>',
        'href'  => '#',
        'meta'  => [
            'onclick' => 'thientamAdminBarPurgeCache(event); return false;',
            'title'   => 'Xóa toàn bộ Cache trên Next.js để làm mới giao diện ngay lập tức',
        ],
    ]);
}, 999);

/**
 * Script xử lý click nút Xóa Cache trên Admin Bar
 */
add_action('admin_footer', function () {
    if (! current_user_can('manage_options')) {
        return;
    }
    $nonce = wp_create_nonce('thientam_purge_cache_nonce');
    ?>
    <script>
    function thientamAdminBarPurgeCache(e) {
        if (e) e.preventDefault();
        if (!confirm('Bạn có muốn xóa toàn bộ bộ nhớ tạm (Cache) trên Next.js ngay bây giờ?')) {
            return false;
        }
        var $btn = jQuery('#wp-admin-bar-thientam_quick_purge_cache .ab-item');
        var originalHtml = $btn.html();
        $btn.html('<span class="ab-icon dashicons dashicons-update" style="animation: rotation 1s infinite linear; color: #ffffff !important;"></span> <span class="ab-label" style="color: #ffffff !important;">Đang xóa cache...</span>');

        jQuery.post(ajaxurl, {
            action: 'thientam_purge_nextjs_cache',
            security: '<?php echo esc_js($nonce); ?>'
        }, function(res) {
            $btn.html(originalHtml);
            if (res && res.success) {
                alert('✅ Thành công: ' + (res.data.message || 'Đã xóa toàn bộ cache Next.js!'));
            } else {
                var msg = (res && res.data && res.data.message) ? res.data.message : 'Không thể xóa cache';
                alert('❌ Lỗi: ' + msg);
            }
        }).fail(function() {
            $btn.html(originalHtml);
            alert('❌ Lỗi kết nối máy chủ.');
        });
        return false;
    }
    </script>
    <style>
    @keyframes rotation {
        from { transform: rotate(0deg); }
        to { transform: rotate(359deg); }
    }
    #wpadminbar #wp-admin-bar-thientam_quick_purge_cache > .ab-item {
        background-color: #d97706 !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        padding: 0 12px !important;
        transition: background-color 0.2s ease !important;
    }
    #wpadminbar #wp-admin-bar-thientam_quick_purge_cache:hover > .ab-item {
        background-color: #b45309 !important;
        color: #ffffff !important;
    }
    #wpadminbar #wp-admin-bar-thientam_quick_purge_cache .ab-item,
    #wpadminbar #wp-admin-bar-thientam_quick_purge_cache .ab-item *,
    #wpadminbar #wp-admin-bar-thientam_quick_purge_cache .ab-item .ab-label,
    #wpadminbar #wp-admin-bar-thientam_quick_purge_cache .ab-item .ab-icon,
    #wpadminbar #wp-admin-bar-thientam_quick_purge_cache .ab-item .ab-icon:before,
    #wpadminbar #wp-admin-bar-thientam_quick_purge_cache:hover .ab-item,
    #wpadminbar #wp-admin-bar-thientam_quick_purge_cache:hover .ab-item *,
    #wpadminbar #wp-admin-bar-thientam_quick_purge_cache:hover .ab-item .ab-label,
    #wpadminbar #wp-admin-bar-thientam_quick_purge_cache:hover .ab-item .ab-icon,
    #wpadminbar #wp-admin-bar-thientam_quick_purge_cache:hover .ab-item .ab-icon:before {
        color: #ffffff !important;
    }
    </style>
    <?php
});

