<?php

/**
 * Custom Post Type & REST API for Testimonials (Cảm nhận khách hàng)
 * 
 * - Post type: `testimonial`
 * - Không có single page riêng ở frontend (publicly_queryable = false, has_archive = false)
 * - Quản lý tập trung trong WP Admin với các meta fields cốt lõi (Họ tên, Chức vụ, Lời cảm nhận, Đánh giá sao, Ảnh đại diện)
 * - Ký tự viết tắt (Initials) và Màu sắc thẻ được giao diện Next.js Frontend tự động tạo và phân phối
 * - Sắp xếp lấy bài mới nhất (date DESC)
 * - Tích hợp REST API: GET /wp-json/thientam/v1/testimonials
 * - Tự động nạp 7 dữ liệu mẫu mặc định nếu chưa có bài viết nào
 * 
 * @package HelloElementorChild
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * 1. Đăng ký Custom Post Type: testimonial
 *
 * @return void
 */
add_action('init', 'thientam_register_testimonial_post_type');
function thientam_register_testimonial_post_type()
{
    $labels = array(
        'name'                  => 'Cảm nhận khách hàng',
        'singular_name'         => 'Cảm nhận khách hàng',
        'menu_name'             => 'Cảm nhận KH',
        'name_admin_bar'        => 'Cảm nhận khách hàng',
        'add_new'               => 'Thêm mới',
        'add_new_item'          => 'Thêm mới',
        'new_item'              => 'Cảm nhận mới',
        'edit_item'             => 'Chỉnh sửa cảm nhận',
        'view_item'             => 'Xem cảm nhận',
        'all_items'             => 'Tất cả cảm nhận',
        'search_items'          => 'Tìm kiếm cảm nhận',
        'parent_item_colon'     => 'Cảm nhận cha:',
        'not_found'             => 'Chưa có cảm nhận nào.',
        'not_found_in_trash'    => 'Không có cảm nhận nào trong thùng rác.',
    );

    $args = array(
        'labels'             => $labels,
        'description'        => 'Đánh giá, cảm nhận từ các gia chủ và doanh nghiệp gửi đến Thiên Tâm',
        'public'             => false,             // Không công khai URL trực tiếp
        'publicly_queryable' => false,             // Không tạo page riêng cho từng item
        'show_ui'            => true,              // Hiển thị giao diện quản trị trong WP Admin
        'show_in_menu'       => true,              // Hiển thị menu riêng trong Admin
        'query_var'          => false,
        'rewrite'            => false,             // Tắt rewrite URL
        'capability_type'    => 'post',
        'has_archive'        => false,             // Không tạo trang lưu trữ archive
        'hierarchical'       => false,
        'menu_position'      => 21,                // Ngay dưới Trang & Dịch vụ
        'menu_icon'          => 'dashicons-testimonial',
        'show_in_rest'       => true,              // Hỗ trợ REST API
        'supports'           => array('title', 'thumbnail'), // Title là Tên khách hàng, Thumbnail là Ảnh đại diện
    );

    register_post_type('testimonial', $args);
}

/**
 * 2. Redirect an toàn nếu có ai cố tình truy cập single testimonial
 *
 * @return void
 */
add_action('template_redirect', 'thientam_disable_single_testimonial_page');
function thientam_disable_single_testimonial_page()
{
    if (is_singular('testimonial')) {
        wp_redirect(home_url('/'), 301);
        exit;
    }
}

/**
 * 3. Đăng ký Meta Box cho Cảm nhận khách hàng
 *
 * @return void
 */
add_action('add_meta_boxes', 'thientam_register_testimonial_metabox');
function thientam_register_testimonial_metabox()
{
    add_meta_box(
        'thientam_testimonial_details',
        'Thông tin Cảm nhận khách hàng',
        'thientam_render_testimonial_metabox',
        'testimonial',
        'normal',
        'high'
    );
}

/**
 * 4. Giao diện nhập liệu Meta Box trong WP Admin
 *
 * @param \WP_Post|object $post
 * @return void
 */
function thientam_render_testimonial_metabox($post)
{
    wp_nonce_field('thientam_save_testimonial_meta', 'thientam_testimonial_nonce');

    $post_id    = isset($post->ID) ? intval($post->ID) : 0;
    $role       = get_post_meta($post_id, '_testimonial_role', true);
    $quote      = get_post_meta($post_id, '_testimonial_quote', true);
    $rating     = get_post_meta($post_id, '_testimonial_rating', true);
    if (! $rating) {
        $rating = 5;
    }
?>
    <style>
        .thientam-meta-field {
            margin-bottom: 18px;
        }

        .thientam-meta-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #1d2327;
        }

        .thientam-meta-field .description {
            color: #646970;
            font-size: 12px;
            margin-top: 4px;
            display: block;
        }

        .thientam-meta-field input[type="text"],
        .thientam-meta-field textarea,
        .thientam-meta-field select {
            width: 100%;
            max-width: 600px;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #8c8f94;
        }

        .thientam-meta-field textarea {
            min-height: 100px;
            line-height: 1.5;
        }
    </style>

    <div class="thientam-testimonial-metabox-wrapper">
        <div class="thientam-meta-field">
            <label for="testimonial_role">Chức vụ / Danh xưng / Địa phương <span style="color:#d63638;">*</span></label>
            <input type="text" id="testimonial_role" name="testimonial_role" value="<?php echo esc_attr($role); ?>" placeholder="Ví dụ: Giám đốc Doanh nghiệp BĐS, Gia chủ tại Hà Nội..." required />
            <span class="description">Hiển thị dưới tên khách hàng.</span>
        </div>

        <div class="thientam-meta-field">
            <label for="testimonial_quote">Nội dung cảm nhận / Lời đánh giá <span style="color:#d63638;">*</span></label>
            <textarea id="testimonial_quote" name="testimonial_quote" placeholder="Nhập trích đoạn cảm nhận hoặc phản hồi từ khách hàng..." required><?php echo esc_textarea($quote); ?></textarea>
            <span class="description">Nội dung trích dẫn chia sẻ của khách hàng.</span>
        </div>

        <div class="thientam-meta-field">
            <label for="testimonial_rating">Đánh giá chất lượng (Số sao):</label>
            <select id="testimonial_rating" name="testimonial_rating" style="max-width: 150px;">
                <option value="5" <?php selected($rating, 5); ?>>⭐⭐⭐⭐⭐</option>
                <option value="4" <?php selected($rating, 4); ?>>⭐⭐⭐⭐</option>
                <option value="3" <?php selected($rating, 3); ?>>⭐⭐⭐</option>
            </select>
        </div>
    </div>
<?php
}

/**
 * 5. Lưu dữ liệu Meta Box khi lưu Post
 *
 * @param int $post_id
 * @return void
 */
add_action('save_post_testimonial', 'thientam_save_testimonial_meta');
function thientam_save_testimonial_meta($post_id)
{
    $post_id = intval($post_id);

    if (! isset($_POST['thientam_testimonial_nonce']) || ! wp_verify_nonce($_POST['thientam_testimonial_nonce'], 'thientam_save_testimonial_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    // Lưu Role
    if (isset($_POST['testimonial_role'])) {
        update_post_meta($post_id, '_testimonial_role', sanitize_text_field($_POST['testimonial_role']));
    }

    // Lưu Quote
    if (isset($_POST['testimonial_quote'])) {
        update_post_meta($post_id, '_testimonial_quote', sanitize_textarea_field($_POST['testimonial_quote']));
    }

    // Lưu Rating
    if (isset($_POST['testimonial_rating'])) {
        update_post_meta($post_id, '_testimonial_rating', intval($_POST['testimonial_rating']));
    }
}

/**
 * 6. Tùy biến cột danh sách Testimonial trong WP Admin
 *
 * @param array $columns
 * @return array
 */
add_filter('manage_testimonial_posts_columns', 'thientam_testimonial_admin_columns');
function thientam_testimonial_admin_columns($columns)
{
    $new_columns = array(
        'cb'                   => $columns['cb'],
        'title'                => 'Khách hàng',
        'testimonial_role'     => 'Chức vụ / Gia chủ',
        'testimonial_quote'    => 'Trích dẫn cảm nhận',
        'testimonial_rating'   => 'Đánh giá',
        'date'                 => 'Ngày đăng',
    );
    return $new_columns;
}

/**
 * Render nội dung từng cột custom trong danh sách
 *
 * @param string $column
 * @param int $post_id
 * @return void
 */
add_action('manage_testimonial_posts_custom_column', 'thientam_testimonial_admin_custom_column', 10, 2);
function thientam_testimonial_admin_custom_column($column, $post_id)
{
    $post_id = intval($post_id);

    switch ($column) {
        case 'testimonial_role':
            echo '<strong>' . esc_html(get_post_meta($post_id, '_testimonial_role', true)) . '</strong>';
            break;
        case 'testimonial_quote':
            $quote = get_post_meta($post_id, '_testimonial_quote', true);
            echo '<em>“' . esc_html(wp_trim_words($quote, 15, '...')) . '”</em>';
            break;
        case 'testimonial_rating':
            $rating = intval(get_post_meta($post_id, '_testimonial_rating', true)) ?: 5;
            echo str_repeat('⭐', $rating);
            break;
    }
}

/**
 * 7. Đăng ký REST API Endpoint: GET /wp-json/thientam/v1/testimonials
 * Lấy danh sách cảm nhận từ MỚI NHẤT trở về trước (date DESC)
 *
 * @return void
 */
add_action('rest_api_init', 'thientam_register_testimonials_rest_route');
function thientam_register_testimonials_rest_route()
{
    register_rest_route('thientam/v1', '/testimonials', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_testimonials',
        'permission_callback' => '__return_true',
    ));
}

/**
 * Callback REST API lấy danh sách Testimonials
 *
 * @param \WP_REST_Request|object $request
 * @return \WP_REST_Response
 */
function thientam_rest_get_testimonials($request)
{
    $posts = get_posts(array(
        'post_type'      => 'testimonial',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));

    $testimonials = array();

    foreach ($posts as $post) {
        $thumbnail_url = get_the_post_thumbnail_url($post->ID, 'medium') ?: '';

        $testimonials[] = array(
            'id'       => $post->ID,
            'name'     => $post->post_title,
            'role'     => get_post_meta($post->ID, '_testimonial_role', true) ?: '',
            'quote'    => get_post_meta($post->ID, '_testimonial_quote', true) ?: '',
            'rating'   => intval(get_post_meta($post->ID, '_testimonial_rating', true)) ?: 5,
            'avatar'   => $thumbnail_url,
        );
    }

    return new WP_REST_Response(array(
        'success' => true,
        'data'    => $testimonials,
        'count'   => count($testimonials),
    ), 200);
}

/**
 * 8. Tự động khởi tạo (Seed) 7 đánh giá mặc định từ Thiên Tâm nếu database chưa có bài nào
 *
 * @return void
 */
add_action('admin_init', 'thientam_seed_default_testimonials');
function thientam_seed_default_testimonials()
{
    // Chỉ kiểm tra 1 lần nếu chưa có dữ liệu
    $seeded = get_option('thientam_testimonials_seeded', false);
    if ($seeded) {
        return;
    }

    $existing = get_posts(array(
        'post_type'      => 'testimonial',
        'post_status'    => 'any',
        'posts_per_page' => 1,
    ));

    if (! empty($existing)) {
        update_option('thientam_testimonials_seeded', true);
        return;
    }

    $default_items = array(
        array(
            'name'     => 'Nguyễn Văn Minh',
            'role'     => 'Giám đốc Doanh nghiệp BĐS',
            'quote'    => 'Sau khi được Thiên Tâm tư vấn cải tạo lại hướng bàn làm việc và bố trí phòng khách, công việc kinh doanh của công ty tôi khởi sắc rõ rệt. Cảm ơn sự tận tâm của đội ngũ!',
            'rating'   => 5,
        ),
        array(
            'name'     => 'Trần Thu Hà',
            'role'     => 'Gia chủ tại Hà Nội',
            'quote'    => 'Gia đình tôi từng gặp nhiều xáo trộn không rõ nguyên do. Nhờ Thiên Tâm tư vấn phong thủy nhà ở và hướng dẫn bài trí phù hợp bản mệnh, không khí gia đình rộn rã tiếng cười trở lại.',
            'rating'   => 5,
        ),
        array(
            'name'     => 'Lê Hoài Nam',
            'role'     => 'Nhà đầu tư cá nhân',
            'quote'    => 'Ấn tượng nhất là cách giải thích phong thủy rất khoa học, dễ hiểu, không hề mê tín. Việc chọn SIM phong thủy số tương sinh cũng mang lại cho tôi nhiều may mắn trong đàm phán.',
            'rating'   => 5,
        ),
        array(
            'name'     => 'Phạm Quốc Hùng',
            'role'     => 'Nhà sáng lập Doanh nghiệp',
            'quote'    => 'Được tư vấn giải pháp bố trí lại văn phòng và chọn thời điểm khởi sự, tôi cảm thấy an tâm và công việc kinh doanh hanh thông hơn hẳn.',
            'rating'   => 5,
        ),
        array(
            'name'     => 'Vũ Minh Tâm',
            'role'     => 'Gia chủ tại TP.HCM',
            'quote'    => 'Tư vấn gia đạo và bản mệnh rất thấu đáo. Đội ngũ Thiên Tâm phân tích kỹ lưỡng, đưa ra giải pháp chân thành và thiết thực cho gia đình.',
            'rating'   => 5,
        ),
        array(
            'name'     => 'Nguyễn Thị Lan Anh',
            'role'     => 'Gia chủ tại Đà Nẵng',
            'quote'    => 'Cảm ơn Thiên Tâm đã giúp gia đình tôi chọn được hướng nhà và ngày khởi công hợp mệnh. Từ khi chuyển về nhà mới, mọi việc đều thuận hơn, gia đình cũng thêm gắn kết và an yên.',
            'rating'   => 5,
        ),
        array(
            'name'     => 'Trần Đức Thịnh',
            'role'     => 'Kỹ sư — Hà Nội',
            'quote'    => 'Thiên Tâm không chỉ tư vấn phong thủy mà còn giải thích rõ ràng lý do đằng sau từng đề xuất. Điều đó giúp tôi hiểu và tin tưởng hơn, chứ không chỉ đơn thuần làm theo mà không biết vì sao.',
            'rating'   => 5,
        ),
    );

    foreach ($default_items as $item) {
        $post_id = wp_insert_post(array(
            'post_title'   => $item['name'],
            'post_type'    => 'testimonial',
            'post_status'  => 'publish',
        ));

        if ($post_id && ! is_wp_error($post_id)) {
            update_post_meta($post_id, '_testimonial_role', $item['role']);
            update_post_meta($post_id, '_testimonial_quote', $item['quote']);
            update_post_meta($post_id, '_testimonial_rating', $item['rating']);
        }
    }

    update_option('thientam_testimonials_seeded', true);
}
