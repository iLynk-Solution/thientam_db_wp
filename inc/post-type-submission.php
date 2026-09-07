<?php

/**
 * Custom Post Type & REST API for Form Submissions (Yêu cầu liên hệ / Leads)
 *
 * - Post type: `form_submission`
 * - Nhận payload JSON linh hoạt từ Next.js qua REST API: POST /wp-json/thientam/v1/submit-form
 * - Không cần tạo trường thủ công ở WP, tự động render bảng Key-Value linh hoạt
 * - Quản lý trạng thái tiếp nhận, ghi chú nội bộ, lọc danh sách và hiển thị badge số đơn mới
 *
 * @package HelloElementorChild
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * 1. Đăng ký Custom Post Type: form_submission
 */
add_action('init', 'thientam_register_submission_post_type');
function thientam_register_submission_post_type()
{
    $labels = array(
        'name'                  => 'Yêu cầu liên hệ',
        'singular_name'         => 'Yêu cầu liên hệ',
        'menu_name'             => 'Yêu cầu liên hệ',
        'name_admin_bar'        => 'Yêu cầu liên hệ',
        'edit_item'             => 'Xem chi tiết yêu cầu',
        'view_item'             => 'Xem yêu cầu',
        'all_items'             => 'Tất cả yêu cầu',
        'search_items'          => 'Tìm kiếm yêu cầu',
        'not_found'             => 'Chưa có yêu cầu liên hệ nào.',
        'not_found_in_trash'    => 'Không có yêu cầu nào trong thùng rác.',
    );

    $args = array(
        'labels'             => $labels,
        'description'        => 'Lưu trữ các lượt gửi form liên hệ, tư vấn, đăng ký khóa học từ Next.js frontend',
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => false,
        'rewrite'            => false,
        'capability_type'    => 'post',
        'capabilities'       => array(
            'create_posts' => 'do_not_allow', // Tắt hoàn toàn nút và chức năng Thêm mới bằng tay
        ),
        'map_meta_cap'       => true,
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 22,
        'menu_icon'          => 'dashicons-email-alt2',
        'supports'           => array('title'),
        'show_in_rest'       => false,
    );

    register_post_type('form_submission', $args);
}

// Xóa menu con "Thêm mới" (nếu có)
add_action('admin_menu', function () {
    remove_submenu_page('edit.php?post_type=form_submission', 'post-new.php?post_type=form_submission');
}, 999);

/**
 * 1.1. Thêm trang Cài đặt reCAPTCHA vào menu Yêu cầu liên hệ
 */
add_action('admin_menu', 'thientam_add_recaptcha_settings_submenu');
function thientam_add_recaptcha_settings_submenu()
{
    add_submenu_page(
        'edit.php?post_type=form_submission',
        'Cài đặt Google reCAPTCHA',
        'Cài đặt reCAPTCHA',
        'manage_options',
        'thientam-recaptcha-settings',
        'thientam_render_recaptcha_settings_page'
    );
}

function thientam_render_recaptcha_settings_page()
{
    // Lưu cài đặt khi submit
    $saved = false;
    if (isset($_POST['thientam_recaptcha_save_nonce']) && wp_verify_nonce($_POST['thientam_recaptcha_save_nonce'], 'thientam_save_recaptcha_settings')) {
        $enabled   = isset($_POST['thientam_recaptcha_enabled']) ? '1' : '0';
        $site_key  = isset($_POST['thientam_recaptcha_site_key']) ? sanitize_text_field($_POST['thientam_recaptcha_site_key']) : '';
        $secret_key = isset($_POST['thientam_recaptcha_secret_key']) ? sanitize_text_field($_POST['thientam_recaptcha_secret_key']) : '';
        $threshold = isset($_POST['thientam_recaptcha_score_threshold']) ? floatval($_POST['thientam_recaptcha_score_threshold']) : 0.5;

        update_option('thientam_recaptcha_enabled', $enabled);
        update_option('thientam_recaptcha_site_key', $site_key);
        update_option('thientam_recaptcha_secret_key', $secret_key);
        update_option('thientam_recaptcha_score_threshold', $threshold);
        $saved = true;
    }

    $enabled    = get_option('thientam_recaptcha_enabled', '0');
    $site_key   = get_option('thientam_recaptcha_site_key', '');
    $secret_key = get_option('thientam_recaptcha_secret_key', '');
    $threshold  = get_option('thientam_recaptcha_score_threshold', '0.5');
?>
    <div class="wrap" style="max-width: 860px; margin-top: 20px;">
        <h1 style="font-size: 22px; font-weight: bold; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            Cài đặt Google reCAPTCHA (Chống Spam Form)
        </h1>

        <?php if ($saved) : ?>
            <div class="notice notice-success is-dismissible" style="padding: 10px 15px;">
                <p><strong>✅ Đã lưu cấu hình reCAPTCHA thành công!</strong></p>
            </div>
        <?php endif; ?>

        <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 8px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;">
            <form method="post" action="">
                <?php wp_nonce_field('thientam_save_recaptcha_settings', 'thientam_recaptcha_save_nonce'); ?>

                <table class="form-table" role="presentation" style="margin-top: 0;">
                    <tbody>
                        <tr>
                            <th scope="row" style="width: 220px; font-weight: 600;">Trạng thái kích hoạt</th>
                            <td>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" name="thientam_recaptcha_enabled" value="1" <?php checked($enabled, '1'); ?> style="width: 18px; height: 18px;">
                                    <span style="font-weight: 600; color: #1d2327;">Bật bảo vệ Google reCAPTCHA v3 cho toàn bộ Form</span>
                                </label>
                                <p class="description" style="margin-top: 4px;">Khi bật, các form gửi từ website Next.js sẽ được kiểm tra chống bot và spam tự động.</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row" style="font-weight: 600;"><label for="thientam_recaptcha_site_key">Site Key (Khóa trang web)</label></th>
                            <td>
                                <input type="text" id="thientam_recaptcha_site_key" name="thientam_recaptcha_site_key" value="<?php echo esc_attr($site_key); ?>" class="regular-text" style="width: 100%; max-width: 500px; font-family: monospace;" placeholder="Ví dụ: 6Lc...">
                                <p class="description">Khóa công khai dùng ở giao diện Frontend Next.js để sinh token xác thực.</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row" style="font-weight: 600;"><label for="thientam_recaptcha_secret_key">Secret Key (Khóa bí mật)</label></th>
                            <td>
                                <input type="password" id="thientam_recaptcha_secret_key" name="thientam_recaptcha_secret_key" value="<?php echo esc_attr($secret_key); ?>" class="regular-text" style="width: 100%; max-width: 500px; font-family: monospace;" placeholder="Ví dụ: 6Lc...">
                                <p class="description">Khóa bí mật dùng ở Server WordPress để xác minh độ tin cậy với máy chủ Google (Không bị lộ ra ngoài).</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row" style="font-weight: 600;"><label for="thientam_recaptcha_score_threshold">Ngưỡng điểm an toàn (Score)</label></th>
                            <td>
                                <input type="number" step="0.1" min="0.0" max="1.0" id="thientam_recaptcha_score_threshold" name="thientam_recaptcha_score_threshold" value="<?php echo esc_attr($threshold); ?>" style="width: 100px;">
                                <p class="description">Điểm từ <code>0.0</code> (Chắc chắn là Bot) đến <code>1.0</code> (Người thật). Mặc định là <code>0.5</code>. Bất kỳ yêu cầu nào có điểm dưới mức này sẽ bị chặn.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #f0f0f1;">
                    <button type="submit" class="button button-primary button-large" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important; border: none !important; border-radius: 8px !important; color: #fff !important; font-size: 14px !important; font-weight: 600 !important; padding: 0 25px !important; height: 42px !important; line-height: 42px !important; display: inline-flex !important; align-items: center !important; gap: 8px !important; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35) !important; cursor: pointer !important; text-shadow: none !important;"><span class="dashicons dashicons-saved" style="font-size: 18px; width: 18px; height: 18px; margin-top: -1px;"></span> Lưu cấu hình reCAPTCHA</button>
                </div>
            </form>
        </div>

        <div style="background: #f0f6fc; border: 1px solid #c8d8e8; border-radius: 8px; padding: 20px;">
            <h3 style="margin-top: 0; font-size: 15px; color: #005a9c; display: flex; align-items: center; gap: 6px;">
                Hướng dẫn lấy Key Google reCAPTCHA v3 miễn phí:
            </h3>
            <ol style="margin-left: 20px; line-height: 1.8; color: #2c3338; font-size: 13px;">
                <li>Truy cập vào trang quản lý: <a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noopener noreferrer" style="font-weight: bold; text-decoration: underline;">Google reCAPTCHA Admin Console ↗</a></li>
                <li>Đặt nhãn (Label): <strong>Thiên Tâm 68</strong></li>
                <li>Chọn loại reCAPTCHA: <strong>reCAPTCHA v3</strong> (Xác minh yêu cầu bằng điểm số mà không làm phiền người dùng)</li>
                <li>Thêm các tên miền (Domains):
                    <code style="background: #e1ecf4; padding: 2px 6px; border-radius: 3px;">thientam68.com</code>,
                    <code style="background: #e1ecf4; padding: 2px 6px; border-radius: 3px;">site.thientam68.com</code>,
                    <code style="background: #e1ecf4; padding: 2px 6px; border-radius: 3px;">localhost</code> (để test)
                </li>
                <li>Nhấn <strong>Gửi (Submit)</strong>, sau đó sao chép <strong>Site Key</strong> và <strong>Secret Key</strong> dán vào form ở trên rồi lưu lại.</li>
            </ol>
        </div>
    </div>
<?php
}

/**
 * 2. Thêm Badge số lượng đơn MỚI vào Menu Admin
 */
add_action('admin_head', 'thientam_submission_admin_badge_styles');
function thientam_submission_admin_badge_styles()
{
?>
    <style>
        #adminmenu .thientam-lead-badge {
            float: right;
            margin-right: 0px;
            margin-top: -24px;
            background-color: #d63638 !important;
            color: #ffffff !important;
            border-radius: 10px;
            padding: 2px 7px;
            font-size: 11px;
            font-weight: 700;
            line-height: 14px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
    </style>
    <?php
}

add_action('admin_menu', 'thientam_add_submission_badge_to_menu');
function thientam_add_submission_badge_to_menu()
{
    global $menu;
    $count = thientam_count_new_submissions();
    if ($count > 0) {
        foreach ($menu as $key => $value) {
            if (isset($value[2]) && $value[2] === 'edit.php?post_type=form_submission') {
                $menu[$key][0] .= sprintf(' <span class="thientam-lead-badge">%1$d</span>', $count);
                break;
            }
        }
    }
}

function thientam_count_new_submissions()
{
    $query = new WP_Query(array(
        'post_type'      => 'form_submission',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => '_submission_status',
                'value'   => 'new',
                'compare' => '=',
            ),
            array(
                'key'     => '_submission_status',
                'compare' => 'NOT EXISTS',
            ),
        ),
    ));
    return $query->found_posts;
}

/**
 * 3. Tùy chỉnh cột trong danh sách WP Admin
 *
 * @param array<string, string> $columns
 * @return array<string, string>
 */
add_filter('manage_form_submission_posts_columns', 'thientam_submission_custom_columns');
function thientam_submission_custom_columns($columns)
{
    $new_columns = array(
        'cb'                 => $columns['cb'],
        'customer_name'      => 'Khách hàng',
        'customer_contact'   => 'Liên hệ (SĐT / Email)',
        'form_title'         => 'Loại Form',
        'submission_summary' => 'Nội dung vắn tắt',
        'submission_status'  => 'Trạng thái',
        'date'               => 'Thời gian gửi',
    );
    return $new_columns;
}

/**
 * @param string $column
 * @param int $post_id
 * @return void
 */
add_action('manage_form_submission_posts_custom_column', 'thientam_submission_custom_column_data', 10, 2);
function thientam_submission_custom_column_data($column, $post_id)
{
    $name    = get_post_meta($post_id, '_customer_name', true) ?: get_the_title($post_id);
    $phone   = get_post_meta($post_id, '_customer_phone', true);
    $email   = get_post_meta($post_id, '_customer_email', true);
    $form    = get_post_meta($post_id, '_form_title', true) ?: get_post_meta($post_id, '_form_id', true) ?: 'Form chung';
    $message = get_post_meta($post_id, '_customer_message', true);
    $status  = get_post_meta($post_id, '_submission_status', true) ?: 'new';

    switch ($column) {
        case 'customer_name':
            $edit_link = get_edit_post_link($post_id);
            echo '<strong><a href="' . esc_url($edit_link) . '" style="font-size:14px;color:#1d2327;">' . esc_html($name) . '</a></strong>';
            break;

        case 'customer_contact':
            if ($phone) {
                echo '<div style="margin-bottom:3px;"><span class="dashicons dashicons-phone" style="font-size:15px;width:15px;height:15px;vertical-align:middle;color:#2271b1;"></span> <a href="tel:' . esc_attr($phone) . '"><strong>' . esc_html($phone) . '</strong></a></div>';
            }
            if ($email) {
                echo '<div><span class="dashicons dashicons-email" style="font-size:15px;width:15px;height:15px;vertical-align:middle;color:#646970;"></span> <a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></div>';
            }
            if (!$phone && !$email) {
                echo '<span style="color:#a7aaad;">(Chưa có thông tin)</span>';
            }
            break;

        case 'form_title':
            $form_id = get_post_meta($post_id, '_form_id', true);
            if ($form_id === 'training_registration') {
                $form_display = 'Đăng ký khóa học';
            } elseif ($form_id === 'service_registration') {
                $form_display = 'Đăng ký dịch vụ';
            } elseif ($form_id === 'consultation_form') {
                $form_display = 'Gửi yêu cầu tư vấn';
            } elseif ($form_id === 'contact_form') {
                $form_display = 'Liên hệ';
            } else {
                $parts = explode(':', $form);
                $form_display = trim($parts[0]);
            }
            echo '<span style="display:inline-block;padding:3px 8px;border-radius:4px;background:#f0f0f1;color:#2c3338;font-weight:600;font-size:12px;">' . esc_html($form_display) . '</span>';
            break;

        case 'submission_summary':
            if ($message) {
                echo '<span style="color:#50575e;font-size:12px;line-height:1.4;">' . esc_html(wp_trim_words($message, 12, '...')) . '</span>';
            } else {
                $fields = get_post_meta($post_id, '_submission_fields', true);
                if (is_array($fields) && !empty($fields)) {
                    $preview = array();
                    foreach (array_slice($fields, 0, 2, true) as $k => $v) {
                        if (!in_array(strtolower($k), array('họ và tên', 'họ tên', 'số điện thoại', 'sđt', 'email', 'name', 'phone')) && is_scalar($v) && !empty($v)) {
                            $preview[] = esc_html($k) . ': <em>' . esc_html(wp_trim_words($v, 6)) . '</em>';
                        }
                    }
                    echo '<span style="color:#50575e;font-size:12px;">' . implode('<br>', $preview) . '</span>';
                } else {
                    echo '<span style="color:#a7aaad;">-</span>';
                }
            }
            break;

        case 'submission_status':
            $status_labels = array(
                'new'         => array('label' => 'Mới tiếp nhận', 'bg' => '#e7f5ea', 'color' => '#1e7e34', 'border' => '#c3e6cb'),
                'contacted'   => array('label' => 'Đang liên hệ',   'bg' => '#fff3cd', 'color' => '#856404', 'border' => '#ffeeba'),
                'completed'   => array('label' => 'Đã hoàn thành',  'bg' => '#cce5ff', 'color' => '#004085', 'border' => '#b8daff'),
                'cancelled'   => array('label' => 'Đã hủy / Bỏ qua', 'bg' => '#f8d7da', 'color' => '#721c24', 'border' => '#f5c6cb'),
            );
            $st = isset($status_labels[$status]) ? $status_labels[$status] : $status_labels['new'];
            echo '<span style="display:inline-block;padding:4px 9px;border-radius:12px;font-size:11px;font-weight:600;background:' . $st['bg'] . ';color:' . $st['color'] . ';border:1px solid ' . $st['border'] . ';">' . $st['label'] . '</span>';
            break;
    }
}

/**
 * 4. Bộ lọc trạng thái trên thanh Filter của danh sách
 */
add_action('restrict_manage_posts', 'thientam_submission_filter_by_status');
function thientam_submission_filter_by_status()
{
    global $typenow;
    if ($typenow === 'form_submission') {
        $current_status = isset($_GET['submission_status_filter']) ? sanitize_text_field($_GET['submission_status_filter']) : '';
    ?>
        <select name="submission_status_filter">
            <option value="">-- Tất cả trạng thái --</option>
            <option value="new" <?php selected($current_status, 'new'); ?>>Mới tiếp nhận</option>
            <option value="contacted" <?php selected($current_status, 'contacted'); ?>>Đang liên hệ</option>
            <option value="completed" <?php selected($current_status, 'completed'); ?>>Đã hoàn thành</option>
            <option value="cancelled" <?php selected($current_status, 'cancelled'); ?>>Đã hủy</option>
        </select>
    <?php
    }
}

/**
 * @param \WP_Query $query
 * @return void
 */
add_filter('parse_query', 'thientam_submission_apply_filter_query');
function thientam_submission_apply_filter_query($query)
{
    global $pagenow, $typenow;
    if (is_admin() && $pagenow === 'edit.php' && $typenow === 'form_submission' && isset($_GET['submission_status_filter']) && $_GET['submission_status_filter'] !== '') {
        $status = sanitize_text_field($_GET['submission_status_filter']);
        $query->query_vars['meta_query'] = array(
            array(
                'key'     => '_submission_status',
                'value'   => $status,
                'compare' => '=',
            ),
        );
    }
}

/**
 * 5. Meta Box Chi tiết Lead & Cập nhật Trạng thái
 */
add_action('add_meta_boxes', 'thientam_add_submission_metaboxes');
function thientam_add_submission_metaboxes()
{
    add_meta_box(
        'thientam_submission_detail_mb',
        'Chi tiết thông tin yêu cầu liên hệ',
        'thientam_render_submission_detail_metabox',
        'form_submission',
        'normal',
        'high'
    );

    add_meta_box(
        'thientam_submission_action_mb',
        '⚙️ Xử lý yêu cầu & Ghi chú',
        'thientam_render_submission_action_metabox',
        'form_submission',
        'side',
        'high'
    );
}

/**
 * @param \WP_Post $post
 * @return void
 */
function thientam_render_submission_detail_metabox($post)
{
    wp_nonce_field('thientam_save_submission_meta', 'thientam_submission_nonce');

    $name       = get_post_meta($post->ID, '_customer_name', true);
    $phone      = get_post_meta($post->ID, '_customer_phone', true);
    $email      = get_post_meta($post->ID, '_customer_email', true);
    $form_title = get_post_meta($post->ID, '_form_title', true) ?: get_post_meta($post->ID, '_form_id', true) ?: 'Form chung';
    $message    = get_post_meta($post->ID, '_customer_message', true);
    $fields     = get_post_meta($post->ID, '_submission_fields', true);
    $meta       = get_post_meta($post->ID, '_submission_meta', true);
    $page_url   = get_post_meta($post->ID, '_submission_page_url', true);
    $ip         = get_post_meta($post->ID, '_submission_ip', true);
    $created_at = get_the_date('H:i:s - d/m/Y', $post->ID);
    ?>
    <style>
        .thientam-sub-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .thientam-sub-table th,
        .thientam-sub-table td {
            padding: 10px 14px;
            border: 1px solid #e2e4e7;
            text-align: left;
            font-size: 13px;
        }

        .thientam-sub-table th {
            background-color: #f6f7f7;
            color: #1d2327;
            font-weight: 600;
            width: 220px;
        }

        .thientam-badge-form {
            background: #0073aa;
            color: #fff;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 12px;
        }
    </style>

    <div style="margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between; background: #f0f6fc; padding: 12px 16px; border-radius: 6px; border: 1px solid #c8d8e8;">
        <div>
            <span style="font-size: 13px; color: #50575e;">Nguồn Form:</span>
            <span class="thientam-badge-form"><?php echo esc_html($form_title); ?></span>
        </div>
        <div style="font-size: 12px; color: #646970;">
            🕒 Gửi lúc: <strong><?php echo esc_html($created_at); ?></strong>
        </div>
    </div>

    <table class="thientam-sub-table">
        <tbody>
            <tr>
                <th>👤 Họ và tên</th>
                <td><strong style="font-size: 15px; color: #1d2327;"><?php echo esc_html($name ?: '(Chưa nhập)'); ?></strong></td>
            </tr>
            <tr>
                <th>📞 Số điện thoại</th>
                <td>
                    <?php if ($phone) : ?>
                        <a href="tel:<?php echo esc_attr($phone); ?>" style="font-size: 14px; font-weight: bold; color: #2271b1; text-decoration: none;">
                            📞 <?php echo esc_html($phone); ?>
                        </a>
                    <?php else : ?>
                        <span style="color:#a7aaad;">(Chưa nhập)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>✉️ Địa chỉ Email</th>
                <td>
                    <?php if ($email) : ?>
                        <a href="mailto:<?php echo esc_attr($email); ?>" style="color: #2271b1; text-decoration: none;">
                            ✉️ <?php echo esc_html($email); ?>
                        </a>
                    <?php else : ?>
                        <span style="color:#a7aaad;">(Chưa nhập)</span>
                    <?php endif; ?>
                </td>
            </tr>

            <?php if (!empty($fields) && is_array($fields)) : ?>
                <?php foreach ($fields as $key => $val) : ?>
                    <?php
                    // Bỏ qua các trường cơ bản đã hiện ở trên
                    $k_clean = trim((string)$key);
                    $k_lower = function_exists('mb_strtolower') ? mb_strtolower($k_clean, 'UTF-8') : strtolower($k_clean);
                    if (in_array($k_lower, array('họ và tên', 'họ tên', 'tên', 'name', 'số điện thoại', 'sđt', 'phone', 'email', 'lời nhắn', 'tin nhắn', 'message', 'note', 'nội dung'))) {
                        continue;
                    }
                    ?>
                    <tr>
                        <th>📝 <?php echo esc_html($key); ?></th>
                        <td><strong><?php echo is_array($val) ? esc_html(json_encode($val, JSON_UNESCAPED_UNICODE)) : nl2br(esc_html($val)); ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($message) : ?>
                <tr>
                    <th>💬 Lời nhắn / Ghi chú của khách</th>
                    <td style="background: #fdfefe; font-size: 13px; line-height: 1.6;">
                        <?php echo nl2br(esc_html($message)); ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php if ($page_url || $ip) : ?>
                <tr style="background: #fafafa;">
                    <th>🌐 Thông tin kỹ thuật</th>
                    <td style="font-size: 12px; color: #646970;">
                        <?php if ($page_url) : ?>
                            <div><strong>URL gửi form:</strong> <a href="<?php echo esc_url($page_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($page_url); ?></a></div>
                        <?php endif; ?>
                        <?php if ($ip) : ?>
                            <div><strong>Địa chỉ IP:</strong> <?php echo esc_html($ip); ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
<?php
}

/**
 * @param \WP_Post $post
 * @return void
 */
function thientam_render_submission_action_metabox($post)
{
    $status = get_post_meta($post->ID, '_submission_status', true) ?: 'new';
    $notes  = get_post_meta($post->ID, '_admin_notes', true);
?>
    <div style="margin-bottom: 15px;">
        <label for="submission_status" style="display: block; font-weight: 600; margin-bottom: 5px;">Trạng thái xử lý:</label>
        <select name="submission_status" id="submission_status" style="width: 100%;">
            <option value="new" <?php selected($status, 'new'); ?>>🟢 Mới tiếp nhận</option>
            <option value="contacted" <?php selected($status, 'contacted'); ?>>🟡 Đang liên hệ / Tư vấn</option>
            <option value="completed" <?php selected($status, 'completed'); ?>>🔵 Đã hoàn thành / Chốt đơn</option>
            <option value="cancelled" <?php selected($status, 'cancelled'); ?>>⚪ Đã hủy / Không có nhu cầu</option>
        </select>
    </div>

    <div style="margin-bottom: 15px;">
        <label for="admin_notes" style="display: block; font-weight: 600; margin-bottom: 5px;">Ghi chú của tư vấn viên:</label>
        <textarea name="admin_notes" id="admin_notes" rows="5" style="width: 100%; font-size: 12px;" placeholder="Ví dụ: Đã gọi lúc 10h, khách hẹn tối liên hệ lại..."><?php echo esc_textarea($notes); ?></textarea>
    </div>

    <div style="text-align: right;">
        <button type="submit" class="button button-primary button-large" style="width:100%;">💾 Cập nhật thông tin</button>
    </div>
<?php
}

/**
 * 6. Lưu dữ liệu khi Admin cập nhật trạng thái / ghi chú
 *
 * @param int $post_id
 * @return void
 */
add_action('save_post_form_submission', 'thientam_save_submission_meta_data');
function thientam_save_submission_meta_data($post_id)
{
    if (!isset($_POST['thientam_submission_nonce']) || !wp_verify_nonce($_POST['thientam_submission_nonce'], 'thientam_save_submission_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['submission_status'])) {
        update_post_meta($post_id, '_submission_status', sanitize_text_field($_POST['submission_status']));
    }
    if (isset($_POST['admin_notes'])) {
        update_post_meta($post_id, '_admin_notes', sanitize_textarea_field($_POST['admin_notes']));
    }
}

/**
 * 7. REST API Endpoints cho Form & reCAPTCHA
 */
add_action('rest_api_init', 'thientam_register_submission_rest_route');
function thientam_register_submission_rest_route()
{
    // 7.1. Tiếp nhận Form Submission từ Frontend
    register_rest_route('thientam/v1', '/submit-form', array(
        'methods'             => 'POST',
        'callback'            => 'thientam_handle_rest_submission',
        'permission_callback' => '__return_true', // Cho phép gửi form công khai từ Frontend
    ));

    // 7.2. Trả về cấu hình public reCAPTCHA Site Key cho Frontend
    register_rest_route('thientam/v1', '/recaptcha-config', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_get_recaptcha_config',
        'permission_callback' => '__return_true',
    ));
}

/**
 * @return \WP_REST_Response
 */
function thientam_get_recaptcha_config()
{
    $enabled  = get_option('thientam_recaptcha_enabled', '0') === '1';
    $site_key = get_option('thientam_recaptcha_site_key', '');

    return new WP_REST_Response(array(
        'success'  => true,
        'enabled'  => $enabled && !empty($site_key),
        'site_key' => $site_key,
    ), 200);
}

/**
 * @param \WP_REST_Request $request
 * @return \WP_REST_Response
 */
function thientam_handle_rest_submission(WP_REST_Request $request)
{
    $params = $request->get_json_params();
    if (empty($params) || !is_array($params)) {
        $params = $request->get_params();
    }

    if (empty($params)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
        ), 400);
    }

    // Kiểm tra bảo mật Google reCAPTCHA nếu được bật (Tự động bỏ qua khi ở localhost/dev)
    $recaptcha_enabled = get_option('thientam_recaptcha_enabled', '0') === '1';
    $secret_key        = get_option('thientam_recaptcha_secret_key', '');

    $client_env   = isset($params['client_env']) ? sanitize_text_field($params['client_env']) : '';
    $header_env   = isset($_SERVER['HTTP_X_CLIENT_ENV']) ? sanitize_text_field($_SERVER['HTTP_X_CLIENT_ENV']) : '';
    $page_url     = isset($params['page_url']) ? (string)$params['page_url'] : '';
    $http_origin  = isset($_SERVER['HTTP_ORIGIN']) ? (string)$_SERVER['HTTP_ORIGIN'] : '';
    $http_referer = isset($_SERVER['HTTP_REFERER']) ? (string)$_SERVER['HTTP_REFERER'] : '';
    $remote_ip    = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
    $http_host    = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field($_SERVER['HTTP_HOST']) : '';

    $is_local_request = (
        $client_env === 'development' ||
        $header_env === 'development' ||
        strpos($page_url, 'localhost') !== false ||
        strpos($page_url, '127.0.0.1') !== false ||
        strpos($page_url, ':3000') !== false ||
        strpos($http_origin, 'localhost') !== false ||
        strpos($http_origin, '127.0.0.1') !== false ||
        strpos($http_origin, ':3000') !== false ||
        strpos($http_referer, 'localhost') !== false ||
        strpos($http_referer, '127.0.0.1') !== false ||
        strpos($http_referer, ':3000') !== false ||
        in_array($remote_ip, array('127.0.0.1', '::1'), true) ||
        strpos($http_host, 'localhost') !== false ||
        strpos($http_host, '.local') !== false ||
        (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local')
    );

    if ($recaptcha_enabled && !empty($secret_key) && !$is_local_request) {
        $recaptcha_token = isset($params['recaptcha_token']) ? sanitize_text_field($params['recaptcha_token']) : '';
        if (empty($recaptcha_token)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Vui lòng hoàn thành xác thực bảo mật reCAPTCHA.',
            ), 400);
        }

        // Gửi yêu cầu xác thực tới máy chủ Google reCAPTCHA
        $verify_res = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret'   => $secret_key,
                'response' => $recaptcha_token,
                'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '',
            ),
            'timeout' => 15,
        ));

        if (!is_wp_error($verify_res)) {
            $verify_body = json_decode(wp_remote_retrieve_body($verify_res), true);
            if (empty($verify_body['success'])) {
                return new WP_REST_Response(array(
                    'success' => false,
                    'message' => 'Xác thực reCAPTCHA không hợp lệ hoặc token đã hết hạn. Vui lòng thử lại.',
                ), 400);
            }

            // Kiểm tra điểm số tin cậy (v3 score)
            if (isset($verify_body['score'])) {
                $min_score = floatval(get_option('thientam_recaptcha_score_threshold', '0.5'));
                if ($verify_body['score'] < $min_score) {
                    return new WP_REST_Response(array(
                        'success' => false,
                        'message' => 'Hành vi gửi form bị nghi ngờ là tự động (Spam). Vui lòng thử lại sau ít phút.',
                    ), 403);
                }
            }
        }
    }

    // 1. Trích xuất thông tin cơ bản
    $form_id    = isset($params['form_id']) ? sanitize_text_field($params['form_id']) : 'contact_form';
    $form_title = isset($params['form_title']) ? sanitize_text_field($params['form_title']) : 'Form Liên Hệ';

    $name    = isset($params['name']) ? sanitize_text_field($params['name']) : '';
    $phone   = isset($params['phone']) ? sanitize_text_field($params['phone']) : '';
    $email   = isset($params['email']) ? sanitize_email($params['email']) : '';
    $message = isset($params['message']) ? sanitize_textarea_field($params['message']) : '';

    // Dữ liệu mở rộng
    $fields = isset($params['fields']) && is_array($params['fields']) ? $params['fields'] : array();
    $meta   = isset($params['meta']) && is_array($params['meta']) ? $params['meta'] : array();

    // Tự động gán nếu fields có chứa thông tin
    if (empty($name) && isset($fields['Họ và tên'])) {
        $name = sanitize_text_field($fields['Họ và tên']);
    }
    if (empty($phone) && isset($fields['Số điện thoại'])) {
        $phone = sanitize_text_field($fields['Số điện thoại']);
    }
    if (empty($email) && isset($fields['Email'])) {
        $email = sanitize_email($fields['Email']);
    }
    if (empty($message) && isset($fields['Lời nhắn'])) {
        $message = sanitize_textarea_field($fields['Lời nhắn']);
    }

    // Sanitize toàn bộ mảng fields
    $sanitized_fields = array();
    foreach ($fields as $k => $v) {
        $sanitized_key = sanitize_text_field($k);
        if (is_array($v)) {
            $sanitized_fields[$sanitized_key] = array_map('sanitize_text_field', $v);
        } else {
            $sanitized_fields[$sanitized_key] = sanitize_textarea_field((string)$v);
        }
    }

    // Lấy thông tin IP & URL
    $page_url   = isset($meta['page_url']) ? esc_url_raw($meta['page_url']) : '';
    $ip_address = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';

    // Tạo tiêu đề bài viết dễ nhận diện
    $post_title = '[' . $form_title . '] ' . ($name ? $name : 'Khách vãng lai') . ($phone ? ' - ' . $phone : '');

    // Tạo bài viết mới trong post type form_submission
    $post_id = wp_insert_post(array(
        'post_title'   => $post_title,
        'post_type'    => 'form_submission',
        'post_status'  => 'publish',
        'post_author'  => 1,
    ));

    if (is_wp_error($post_id)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'Lỗi khi lưu dữ liệu: ' . $post_id->get_error_message(),
        ), 500);
    }

    // Lưu các Post Meta
    update_post_meta($post_id, '_form_id', $form_id);
    update_post_meta($post_id, '_form_title', $form_title);
    update_post_meta($post_id, '_customer_name', $name);
    update_post_meta($post_id, '_customer_phone', $phone);
    update_post_meta($post_id, '_customer_email', $email);
    update_post_meta($post_id, '_customer_message', $message);
    update_post_meta($post_id, '_submission_fields', $sanitized_fields);
    update_post_meta($post_id, '_submission_meta', $meta);
    update_post_meta($post_id, '_submission_status', 'new');
    update_post_meta($post_id, '_submission_ip', $ip_address);
    update_post_meta($post_id, '_submission_user_agent', $user_agent);
    update_post_meta($post_id, '_submission_page_url', $page_url);

    // Gửi email HTML thông báo tự động qua SMTP cho Admin & Tư vấn viên
    if (function_exists('thientam_send_lead_notification_email')) {
        thientam_send_lead_notification_email($post_id, array(
            'form_title' => $form_title,
            'name'       => $name,
            'phone'      => $phone,
            'email'      => $email,
            'message'    => $message,
            'fields'     => $sanitized_fields,
            'page_url'   => $page_url,
        ));
    }

    return new WP_REST_Response(array(
        'success'       => true,
        'message'       => 'Yêu cầu của bạn đã được gửi thành công!',
        'submission_id' => $post_id,
    ), 200);
}
