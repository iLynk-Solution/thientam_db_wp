<?php

/**
 * Custom Post Type: Đào tạo (Training Packages) & Custom Fields
 * 
 * - Post Type: `training`
 * - Menu Admin: "Đào tạo" (icon: dashicons-welcome-learn-more)
 * - Tương thích 100% với WordPress Native Custom Fields, Native Metaboxes & ACF
 * - Hỗ trợ REST API:
 *     + GET /wp-json/thientam/v1/training
 *     + GET /wp-json/thientam/v1/training/{slug}
 * - Tự động nạp sẵn toàn bộ trường tùy chỉnh & 7 gói đào tạo mẫu
 *
 * @package HelloElementorChild
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * 1. Đăng ký Custom Post Type: training
 *
 * @return void
 */
add_action('init', 'thientam_register_training_cpt', 5);
function thientam_register_training_cpt()
{
    $labels = array(
        'name'                  => 'Đào tạo',
        'singular_name'         => 'Gói đào tạo',
        'menu_name'             => 'Đào tạo',
        'name_admin_bar'        => 'Gói đào tạo',
        'add_new'               => 'Thêm gói mới',
        'add_new_item'          => 'Thêm gói đào tạo mới',
        'new_item'              => 'Gói đào tạo mới',
        'edit_item'             => 'Chỉnh sửa gói đào tạo',
        'view_item'             => 'Xem gói đào tạo',
        'all_items'             => 'Tất cả gói đào tạo',
        'search_items'          => 'Tìm kiếm gói đào tạo',
        'parent_item_colon'     => 'Gói đào tạo cha:',
        'not_found'             => 'Chưa có gói đào tạo nào.',
        'not_found_in_trash'    => 'Không có gói đào tạo nào trong thùng rác.',
    );

    $args = array(
        'labels'             => $labels,
        'description'        => 'Các khóa học và chương trình đào tạo Tử Vi, Cổ học phương Đông tại Thiên Tâm',
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'dao-tao', 'with_front' => false),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-welcome-learn-more',
        'show_in_rest'       => true,
        'supports'           => array('title', 'thumbnail', 'excerpt'),
    );

    register_post_type('training', $args);
}

/**
 * Ẩn metabox "Trường tùy chỉnh" (postcustom) cho Đào tạo
 *
 * @return void
 */
add_action('admin_menu', 'thientam_remove_postcustom_metabox_for_training');
function thientam_remove_postcustom_metabox_for_training()
{
    remove_meta_box('postcustom', 'training', 'normal');
}

add_action('admin_head', 'thientam_hide_postcustom_css_for_training');
function thientam_hide_postcustom_css_for_training()
{
    global $post, $pagenow;
    if (in_array($pagenow, array('post.php', 'post-new.php'))) {
        if ($post && ($post->post_type === 'training' || get_post_meta($post->ID, '_wp_page_template', true) === 'template-training-detail.php')) {
            echo '<style>#postcustom, #postcustom-hide { display: none !important; }</style>';
        }
    }
}

/**
 * 2. Đăng ký ACF Field Groups (5 Box riêng biệt)
 *
 * @return void
 */
add_action('acf/init', 'thientam_register_training_acf_groups');
function thientam_register_training_acf_groups()
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    $locations = array(
        array(
            array('param' => 'post_type', 'operator' => '==', 'value' => 'training'),
        ),
        array(
            array('param' => 'page_template', 'operator' => '==', 'value' => 'template-training-detail.php'),
        ),
    );

    // BOX 1: THÔNG TIN CHUNG
    acf_add_local_field_group(array(
        'key'                   => 'group_training_general_info',
        'title'                 => 'Thông tin chung Gói Đào Tạo (General Info)',
        'fields'                => array(
            array(
                'key'          => 'field_training_subtitle',
                'label'        => 'Tiêu đề phụ (Subtitle)',
                'name'         => 'training_subtitle',
                'type'         => 'text',
                'instructions' => 'VD: Giáo trình chuẩn hóa Cổ học phương Đông & Tử Vi thực chiến',
                'placeholder'  => 'Nhập tiêu đề phụ...',
            ),
            
            array(
                'key'          => 'field_training_duration',
                'label'        => 'Thời lượng / Số chuyên đề (Duration)',
                'name'         => 'training_duration',
                'type'         => 'text',
                'instructions' => 'VD: 10 chuyên đề cốt lõi, 9 chuyên đề chuyên sâu...',
                'placeholder'  => '10 chuyên đề cốt lõi',
            ),
            
            array(
                'key'          => 'field_training_target_audience',
                'label'        => 'Đối tượng phù hợp tổng quát (Target Audience Summary)',
                'name'         => 'training_target_audience',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Tóm tắt đối tượng phù hợp nhất với khóa học này.',
            ),
        ),
        'location'              => $locations,
        'menu_order'            => 1,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'active'                => true,
    ));

    // BOX 2: CHỈ SỐ NỔI BẬT
    acf_add_local_field_group(array(
        'key'                   => 'group_training_metrics',
        'title'                 => 'Chỉ số nổi bật (Key Metrics Bar)',
        'fields'                => array(
            array(
                'key'          => 'field_training_metrics',
                'label'        => 'Danh sách chỉ số',
                'name'         => 'training_metrics',
                'type'         => 'repeater',
                'instructions' => 'Thêm các chỉ số thống kê (Số chuyên đề, % Thực hành, Hình thức học, Chế độ hỗ trợ)',
                'layout'       => 'table',
                'button_label' => 'Thêm Chỉ Số',
                'sub_fields'   => array(
                    array(
                        'key'         => 'field_metric_value',
                        'label'       => 'Giá trị (Value)',
                        'name'        => 'value',
                        'type'        => 'text',
                        'placeholder' => 'VD: 10 Chuyên đề, 60%+ Thực hành',
                    ),
                    array(
                        'key'         => 'field_metric_desc',
                        'label'       => 'Mô tả ngắn (Description)',
                        'name'        => 'desc',
                        'type'        => 'text',
                        'placeholder' => 'VD: Lộ trình bài bản từ gốc, Luận giải lá số thực tế',
                    ),
                ),
            ),
        ),
        'location'              => $locations,
        'menu_order'            => 2,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'active'                => true,
    ));

    // BOX 3: ĐỐI TƯỢNG CHI TIẾT
    acf_add_local_field_group(array(
        'key'                   => 'group_training_audience',
        'title'                 => 'Đối tượng phù hợp chi tiết (Audience Items)',
        'fields'                => array(
            array(
                'key'          => 'field_training_audience_items',
                'label'        => 'Danh sách đối tượng chi tiết',
                'name'         => 'training_audience_items',
                'type'         => 'repeater',
                'instructions' => 'Các gạch đầu dòng liệt kê đối tượng phù hợp khóa học',
                'layout'       => 'table',
                'button_label' => 'Thêm Đối Tượng',
                'sub_fields'   => array(
                    array(
                        'key'         => 'field_audience_item_text',
                        'label'       => 'Nội dung đối tượng',
                        'name'        => 'item',
                        'type'        => 'text',
                        'placeholder' => 'VD: Người muốn hiểu sâu về mệnh bàn cá nhân, gia đình và con cái',
                    ),
                ),
            ),
        ),
        'location'              => $locations,
        'menu_order'            => 3,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'active'                => true,
    ));

    // BOX 4: HÌNH THỨC & QUYỀN LỢI HỌC VIÊN
    acf_add_local_field_group(array(
        'key'                   => 'group_training_benefits',
        'title'                 => 'Hình thức & Quyền lợi học viên (Benefit Items)',
        'fields'                => array(
            array(
                'key'          => 'field_training_benefit_items',
                'label'        => 'Danh sách quyền lợi',
                'name'         => 'training_benefit_items',
                'type'         => 'repeater',
                'instructions' => 'Các quyền lợi và hình thức học dành cho học viên',
                'layout'       => 'table',
                'button_label' => 'Thêm Quyền Lợi',
                'sub_fields'   => array(
                    array(
                        'key'         => 'field_benefit_item_highlight',
                        'label'       => 'Tiêu đề in đậm (Highlight)',
                        'name'        => 'highlight',
                        'type'        => 'text',
                        'placeholder' => 'VD: Hình thức linh hoạt:, Giáo trình độc quyền:',
                    ),
                    array(
                        'key'         => 'field_benefit_item_text',
                        'label'       => 'Nội dung chi tiết (Text)',
                        'name'        => 'text',
                        'type'        => 'text',
                        'placeholder' => 'VD: Học trực tiếp hoặc online qua Zoom có ghi hình...',
                    ),
                ),
            ),
        ),
        'location'              => $locations,
        'menu_order'            => 4,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'active'                => true,
    ));

    // BOX 5: NỘI DUNG CHUYÊN ĐỀ
    acf_add_local_field_group(array(
        'key'                   => 'group_training_lessons',
        'title'                 => 'Danh sách Chuyên đề / Bài giảng (Lessons)',
        'fields'                => array(
            array(
                'key'          => 'field_training_lessons',
                'label'        => 'Danh sách các chuyên đề / bài giảng',
                'name'         => 'training_lessons',
                'type'         => 'repeater',
                'instructions' => 'Nhập danh sách bài học theo thứ tự chương trình giảng dạy',
                'layout'       => 'table',
                'button_label' => 'Thêm Chuyên Đề',
                'sub_fields'   => array(
                    array(
                        'key'         => 'field_lesson_title',
                        'label'       => 'Tên chuyên đề / bài học',
                        'name'        => 'lesson_title',
                        'type'        => 'text',
                        'placeholder' => 'VD: Giới thiệu nhập môn, Ý nghĩa 14 sao chính tinh...',
                    ),
                ),
            ),
        ),
        'location'              => $locations,
        'menu_order'            => 5,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'active'                => true,
    ));
}

/**
 * 3. Đăng ký Native WordPress Meta Boxes
 *
 * @param string $post_type
 * @param \WP_Post|object $post
 * @return void
 */
add_action('add_meta_boxes', 'thientam_register_training_native_boxes', 10, 2);
function thientam_register_training_native_boxes($post_type, $post)
{
    if (! $post) {
        return;
    }

    $template = get_post_meta($post->ID, '_wp_page_template', true);
    $is_training = ($post_type === 'training' || ($post_type === 'page' && $template === 'template-training-detail.php'));

    if ($is_training) {
        $screen = ($post_type === 'training') ? 'training' : 'page';
        add_meta_box('thientam_box_training_general', 'Thông tin chung Gói Đào Tạo (General Info)', 'thientam_render_native_box_general', $screen, 'normal', 'high');
        add_meta_box('thientam_box_training_metrics', 'Chỉ số nổi bật (Key Metrics Bar)', 'thientam_render_native_box_metrics', $screen, 'normal', 'high');
        add_meta_box('thientam_box_training_audience', 'Đối tượng phù hợp chi tiết (Audience Items)', 'thientam_render_native_box_audience', $screen, 'normal', 'high');
        add_meta_box('thientam_box_training_benefits', 'Hình thức & Quyền lợi học viên (Benefit Items)', 'thientam_render_native_box_benefits', $screen, 'normal', 'high');
        add_meta_box('thientam_box_training_lessons', 'Danh sách Chuyên đề / Bài giảng (Lessons)', 'thientam_render_native_box_lessons', $screen, 'normal', 'high');
    }
}

/**
 * Ẩn Content Editor mặc định khi dùng template Chi Tiết Đào Tạo
 *
 * @return void
 */
add_action('admin_init', 'thientam_hide_editor_for_training');
function thientam_hide_editor_for_training()
{
    $post_id = isset($_GET['post']) ? intval($_GET['post']) : (isset($_POST['post_ID']) ? intval($_POST['post_ID']) : 0);
    if (! $post_id) {
        return;
    }
    $template = get_post_meta($post_id, '_wp_page_template', true);
    $post = get_post($post_id);
    if (($post && $post->post_type === 'training') || $template === 'template-training-detail.php') {
        remove_post_type_support('training', 'editor');
        remove_post_type_support('page', 'editor');
    }
}

/**
 * BOX 1: GENERAL INFO
 *
 * @param \WP_Post|object $post
 * @return void
 */
function thientam_render_native_box_general($post)
{
    wp_nonce_field('thientam_save_training_meta_action', 'thientam_training_meta_nonce');
    $post_id = $post->ID;

    $subtitle = get_post_meta($post_id, 'training_subtitle', true) ?: '';
    
    $duration = get_post_meta($post_id, 'training_duration', true) ?: '';
    $summary = get_post_meta($post_id, 'training_summary', true) ?: '';
    $target_audience = get_post_meta($post_id, 'training_target_audience', true) ?: '';
?>
    <style>
        .tt-field-row { margin-bottom: 16px; }
        .tt-field-row label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #1d2327; }
        .tt-field-row input[type="text"], .tt-field-row textarea { width: 100%; max-width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #8c8f94; font-size: 14px; box-sizing: border-box; }
        .tt-field-row textarea { min-height: 70px; line-height: 1.5; }
        .tt-repeater-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .tt-repeater-table th { background: #f0f0f1; padding: 8px 12px; text-align: left; font-size: 13px; border: 1px solid #ccd0d4; }
        .tt-repeater-table td { padding: 8px 12px; border: 1px solid #ccd0d4; background: #fff; vertical-align: middle; }
        .tt-btn-add { background: #2271b1; color: #fff; border: none; padding: 6px 14px; border-radius: 4px; cursor: pointer; font-weight: 600; margin-top: 10px; }
        .tt-btn-add:hover { background: #135e96; }
        .tt-btn-remove { background: #d63638; color: #fff; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; }
    </style>

    <div class="tt-field-row">
        <label for="training_subtitle">Tiêu đề phụ (Subtitle)</label>
        <input type="text" id="training_subtitle" name="training_subtitle" value="<?php echo esc_attr($subtitle); ?>" placeholder="VD: Giáo trình chuẩn hóa Cổ học phương Đông & Tử Vi thực chiến" />
    </div>
    
    <div class="tt-field-row">
        <label for="training_duration">Thời lượng / Số chuyên đề (Duration)</label>
        <input type="text" id="training_duration" name="training_duration" value="<?php echo esc_attr($duration); ?>" placeholder="VD: 10 chuyên đề cốt lõi" />
    </div>
    
    <div class="tt-field-row">
        <label for="training_target_audience">Đối tượng phù hợp tổng quát (Target Audience Summary)</label>
        <textarea id="training_target_audience" name="training_target_audience" rows="3" placeholder="Tóm tắt đối tượng phù hợp nhất..."><?php echo esc_textarea($target_audience); ?></textarea>
    </div>
<?php
}

/**
 * BOX 2: METRICS
 *
 * @param \WP_Post|object $post
 * @return void
 */
function thientam_render_native_box_metrics($post)
{
    $post_id = $post->ID;
    $raw_metrics = get_post_meta($post_id, 'training_metrics_array', true);
    if (empty($raw_metrics)) {
        $count = intval(get_post_meta($post_id, 'training_metrics', true));
        $raw_metrics = array();
        for ($i = 0; $i < $count; $i++) {
            $raw_metrics[] = array(
                'value' => get_post_meta($post_id, "training_metrics_{$i}_value", true) ?: '',
                'desc'  => get_post_meta($post_id, "training_metrics_{$i}_desc", true) ?: '',
            );
        }
    }
?>
    <table class="tt-repeater-table" id="table-training-metrics">
        <thead>
            <tr>
                <th style="width: 35%;">Giá trị (Value)</th>
                <th>Mô tả ngắn (Description)</th>
                <th style="width: 60px; text-align: center;">Xóa</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($raw_metrics) && is_array($raw_metrics)) : ?>
                <?php foreach ($raw_metrics as $index => $m) : ?>
                    <tr>
                        <td><input type="text" name="native_training_metrics[<?php echo $index; ?>][value]" value="<?php echo esc_attr(isset($m['value']) ? $m['value'] : ''); ?>" style="width:100%;" placeholder="10 Chuyên đề" /></td>
                        <td><input type="text" name="native_training_metrics[<?php echo $index; ?>][desc]" value="<?php echo esc_attr(isset($m['desc']) ? $m['desc'] : ''); ?>" style="width:100%;" placeholder="Lộ trình bài bản từ gốc" /></td>
                        <td style="text-align: center;"><button type="button" class="tt-btn-remove" onclick="jQuery(this).closest('tr').remove();">×</button></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td><input type="text" name="native_training_metrics[0][value]" value="" style="width:100%;" placeholder="10 Chuyên đề" /></td>
                    <td><input type="text" name="native_training_metrics[0][desc]" value="" style="width:100%;" placeholder="Lộ trình bài bản từ gốc" /></td>
                    <td style="text-align: center;"><button type="button" class="tt-btn-remove" onclick="jQuery(this).closest('tr').remove();">×</button></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <button type="button" class="tt-btn-add" id="btn-add-metric">+ Thêm Chỉ Số</button>

    <script>
        jQuery(document).ready(function($) {
            $('#btn-add-metric').on('click', function() {
                var idx = $('#table-training-metrics tbody tr').length;
                var row = '<tr>' +
                    '<td><input type="text" name="native_training_metrics[' + idx + '][value]" style="width:100%;" placeholder="60%+ Thực hành" /></td>' +
                    '<td><input type="text" name="native_training_metrics[' + idx + '][desc]" style="width:100%;" placeholder="Luận giải lá số thực tế" /></td>' +
                    '<td style="text-align: center;"><button type="button" class="tt-btn-remove" onclick="jQuery(this).closest(\'tr\').remove();">×</button></td>' +
                    '</tr>';
                $('#table-training-metrics tbody').append(row);
            });
        });
    </script>
<?php
}

/**
 * BOX 3: AUDIENCE
 *
 * @param \WP_Post|object $post
 * @return void
 */
function thientam_render_native_box_audience($post)
{
    $post_id = $post->ID;
    $raw_audience = get_post_meta($post_id, 'training_audience_items_array', true);
    if (empty($raw_audience)) {
        $count = intval(get_post_meta($post_id, 'training_audience_items', true));
        $raw_audience = array();
        for ($i = 0; $i < $count; $i++) {
            $raw_audience[] = get_post_meta($post_id, "training_audience_items_{$i}_item", true) ?: '';
        }
    }
?>
    <table class="tt-repeater-table" id="table-training-audience">
        <thead>
            <tr>
                <th>Nội dung đối tượng phù hợp</th>
                <th style="width: 60px; text-align: center;">Xóa</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($raw_audience) && is_array($raw_audience)) : ?>
                <?php foreach ($raw_audience as $index => $a) :
                    $val = is_array($a) ? (isset($a['item']) ? $a['item'] : '') : $a;
                ?>
                    <tr>
                        <td><input type="text" name="native_training_audience[]" value="<?php echo esc_attr($val); ?>" style="width:100%;" placeholder="VD: Người muốn hiểu sâu về mệnh bàn cá nhân..." /></td>
                        <td style="text-align: center;"><button type="button" class="tt-btn-remove" onclick="jQuery(this).closest('tr').remove();">×</button></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td><input type="text" name="native_training_audience[]" value="" style="width:100%;" placeholder="VD: Người muốn hiểu sâu về mệnh bàn cá nhân..." /></td>
                    <td style="text-align: center;"><button type="button" class="tt-btn-remove" onclick="jQuery(this).closest('tr').remove();">×</button></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <button type="button" class="tt-btn-add" id="btn-add-audience">+ Thêm Đối Tượng</button>

    <script>
        jQuery(document).ready(function($) {
            $('#btn-add-audience').on('click', function() {
                var row = '<tr>' +
                    '<td><input type="text" name="native_training_audience[]" style="width:100%;" placeholder="Nhập đối tượng phù hợp..." /></td>' +
                    '<td style="text-align: center;"><button type="button" class="tt-btn-remove" onclick="jQuery(this).closest(\'tr\').remove();">×</button></td>' +
                    '</tr>';
                $('#table-training-audience tbody').append(row);
            });
        });
    </script>
<?php
}

/**
 * BOX 4: BENEFITS
 *
 * @param \WP_Post|object $post
 * @return void
 */
function thientam_render_native_box_benefits($post)
{
    $post_id = $post->ID;
    $raw_benefits = get_post_meta($post_id, 'training_benefit_items_array', true);
    if (empty($raw_benefits)) {
        $count = intval(get_post_meta($post_id, 'training_benefit_items', true));
        $raw_benefits = array();
        for ($i = 0; $i < $count; $i++) {
            $raw_benefits[] = array(
                'highlight' => get_post_meta($post_id, "training_benefit_items_{$i}_highlight", true) ?: '',
                'text'      => get_post_meta($post_id, "training_benefit_items_{$i}_text", true) ?: '',
            );
        }
    }
?>
    <table class="tt-repeater-table" id="table-training-benefits">
        <thead>
            <tr>
                <th style="width: 30%;">Tiêu đề in đậm (Highlight)</th>
                <th>Nội dung chi tiết (Text)</th>
                <th style="width: 60px; text-align: center;">Xóa</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($raw_benefits) && is_array($raw_benefits)) : ?>
                <?php foreach ($raw_benefits as $index => $b) : ?>
                    <tr>
                        <td><input type="text" name="native_training_benefits[<?php echo $index; ?>][highlight]" value="<?php echo esc_attr(isset($b['highlight']) ? $b['highlight'] : ''); ?>" style="width:100%;" placeholder="Hình thức linh hoạt:" /></td>
                        <td><input type="text" name="native_training_benefits[<?php echo $index; ?>][text]" value="<?php echo esc_attr(isset($b['text']) ? $b['text'] : ''); ?>" style="width:100%;" placeholder="Học trực tiếp hoặc online qua Zoom..." /></td>
                        <td style="text-align: center;"><button type="button" class="tt-btn-remove" onclick="jQuery(this).closest('tr').remove();">×</button></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td><input type="text" name="native_training_benefits[0][highlight]" value="" style="width:100%;" placeholder="Hình thức linh hoạt:" /></td>
                    <td><input type="text" name="native_training_benefits[0][text]" value="" style="width:100%;" placeholder="Học trực tiếp hoặc online qua Zoom..." /></td>
                    <td style="text-align: center;"><button type="button" class="tt-btn-remove" onclick="jQuery(this).closest('tr').remove();">×</button></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <button type="button" class="tt-btn-add" id="btn-add-benefit">+ Thêm Quyền Lợi</button>

    <script>
        jQuery(document).ready(function($) {
            $('#btn-add-benefit').on('click', function() {
                var idx = $('#table-training-benefits tbody tr').length;
                var row = '<tr>' +
                    '<td><input type="text" name="native_training_benefits[' + idx + '][highlight]" style="width:100%;" placeholder="Giáo trình độc quyền:" /></td>' +
                    '<td><input type="text" name="native_training_benefits[' + idx + '][text]" style="width:100%;" placeholder="Tài liệu đúc kết thực chiến..." /></td>' +
                    '<td style="text-align: center;"><button type="button" class="tt-btn-remove" onclick="jQuery(this).closest(\'tr\').remove();">×</button></td>' +
                    '</tr>';
                $('#table-training-benefits tbody').append(row);
            });
        });
    </script>
<?php
}

/**
 * BOX 5: LESSONS
 *
 * @param \WP_Post|object $post
 * @return void
 */
function thientam_render_native_box_lessons($post)
{
    $post_id = $post->ID;
    $raw_lessons = get_post_meta($post_id, 'training_lessons_array', true);
    if (empty($raw_lessons)) {
        $count = intval(get_post_meta($post_id, 'training_lessons', true));
        $raw_lessons = array();
        for ($i = 0; $i < $count; $i++) {
            $raw_lessons[] = get_post_meta($post_id, "training_lessons_{$i}_lesson_title", true) ?: '';
        }
    }
?>
    <table class="tt-repeater-table" id="table-training-lessons">
        <thead>
            <tr>
                <th style="width: 50px; text-align: center;">STT</th>
                <th>Tên chuyên đề / bài giảng</th>
                <th style="width: 60px; text-align: center;">Xóa</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($raw_lessons) && is_array($raw_lessons)) : ?>
                <?php foreach ($raw_lessons as $index => $l) :
                    $title = is_array($l) ? (isset($l['lesson_title']) ? $l['lesson_title'] : '') : $l;
                ?>
                    <tr>
                        <td style="text-align: center; font-weight: 600; color: #646970;"><?php echo ($index + 1); ?></td>
                        <td><input type="text" name="native_training_lessons[]" value="<?php echo esc_attr($title); ?>" style="width:100%;" placeholder="VD: Giới thiệu nhập môn..." /></td>
                        <td style="text-align: center;"><button type="button" class="tt-btn-remove" onclick="jQuery(this).closest('tr').remove();">×</button></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td style="text-align: center; font-weight: 600; color: #646970;">1</td>
                    <td><input type="text" name="native_training_lessons[]" value="" style="width:100%;" placeholder="VD: Giới thiệu nhập môn..." /></td>
                    <td style="text-align: center;"><button type="button" class="tt-btn-remove" onclick="jQuery(this).closest('tr').remove();">×</button></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <button type="button" class="tt-btn-add" id="btn-add-lesson">+ Thêm Chuyên Đề</button>

    <script>
        jQuery(document).ready(function($) {
            $('#btn-add-lesson').on('click', function() {
                var count = $('#table-training-lessons tbody tr').length + 1;
                var row = '<tr>' +
                    '<td style="text-align: center; font-weight: 600; color: #646970;">' + count + '</td>' +
                    '<td><input type="text" name="native_training_lessons[]" style="width:100%;" placeholder="Nhập tên chuyên đề..." /></td>' +
                    '<td style="text-align: center;"><button type="button" class="tt-btn-remove" onclick="jQuery(this).closest(\'tr\').remove();">×</button></td>' +
                    '</tr>';
                $('#table-training-lessons tbody').append(row);
            });
        });
    </script>
<?php
}

/**
 * Lưu dữ liệu Meta Boxes & WordPress Custom Fields
 *
 * @param int $post_id
 * @param \WP_Post|object $post
 * @return void
 */
add_action('save_post', 'thientam_save_training_boxes_data', 10, 2);
function thientam_save_training_boxes_data($post_id, $post)
{
    if (! isset($_POST['thientam_training_meta_nonce']) || ! wp_verify_nonce($_POST['thientam_training_meta_nonce'], 'thientam_save_training_meta_action')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    // 1. Lưu thông tin chung
    $fields = array('training_subtitle', 'training_duration', 'training_summary', 'training_target_audience');
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $val = sanitize_text_field(wp_unslash($_POST[$field]));
            update_post_meta($post_id, $field, $val);
        }
    }

    // 2. Lưu metrics
    if (isset($_POST['native_training_metrics']) && is_array($_POST['native_training_metrics'])) {
        $metrics = array();
        $count = count($_POST['native_training_metrics']);
        update_post_meta($post_id, 'training_metrics', $count);
        $i = 0;
        foreach ($_POST['native_training_metrics'] as $m) {
            $val = sanitize_text_field(wp_unslash($m['value'] ?? ''));
            $desc = sanitize_text_field(wp_unslash($m['desc'] ?? ''));
            update_post_meta($post_id, "training_metrics_{$i}_value", $val);
            update_post_meta($post_id, "training_metrics_{$i}_desc", $desc);
            $metrics[] = array('value' => $val, 'desc' => $desc);
            $i++;
        }
        update_post_meta($post_id, 'training_metrics_array', $metrics);
        if (function_exists('update_field')) {
            update_field('training_metrics', $metrics, $post_id);
        }
    }

    // 3. Lưu audience
    if (isset($_POST['native_training_audience']) && is_array($_POST['native_training_audience'])) {
        $audience = array();
        $count = count($_POST['native_training_audience']);
        update_post_meta($post_id, 'training_audience_items', $count);
        $i = 0;
        foreach ($_POST['native_training_audience'] as $item) {
            $val = sanitize_text_field(wp_unslash($item));
            update_post_meta($post_id, "training_audience_items_{$i}_item", $val);
            $audience[] = array('item' => $val);
            $i++;
        }
        update_post_meta($post_id, 'training_audience_items_array', $audience);
        if (function_exists('update_field')) {
            update_field('training_audience_items', $audience, $post_id);
        }
    }

    // 4. Lưu benefits
    if (isset($_POST['native_training_benefits']) && is_array($_POST['native_training_benefits'])) {
        $benefits = array();
        $count = count($_POST['native_training_benefits']);
        update_post_meta($post_id, 'training_benefit_items', $count);
        $i = 0;
        foreach ($_POST['native_training_benefits'] as $b) {
            $highlight = sanitize_text_field(wp_unslash($b['highlight'] ?? ''));
            $text = sanitize_text_field(wp_unslash($b['text'] ?? ''));
            update_post_meta($post_id, "training_benefit_items_{$i}_highlight", $highlight);
            update_post_meta($post_id, "training_benefit_items_{$i}_text", $text);
            $benefits[] = array('highlight' => $highlight, 'text' => $text);
            $i++;
        }
        update_post_meta($post_id, 'training_benefit_items_array', $benefits);
        if (function_exists('update_field')) {
            update_field('training_benefit_items', $benefits, $post_id);
        }
    }

    // 5. Lưu lessons
    if (isset($_POST['native_training_lessons']) && is_array($_POST['native_training_lessons'])) {
        $lessons = array();
        $count = count($_POST['native_training_lessons']);
        update_post_meta($post_id, 'training_lessons', $count);
        $i = 0;
        foreach ($_POST['native_training_lessons'] as $l) {
            $val = sanitize_text_field(wp_unslash($l));
            update_post_meta($post_id, "training_lessons_{$i}_lesson_title", $val);
            $lessons[] = array('lesson_title' => $val);
            $i++;
        }
        update_post_meta($post_id, 'training_lessons_array', $lessons);
        if (function_exists('update_field')) {
            update_field('training_lessons', $lessons, $post_id);
        }
    }
}

/**
 * 4. REST API Endpoints
 *
 * @return void
 */
add_action('rest_api_init', 'thientam_register_training_rest_routes');
function thientam_register_training_rest_routes()
{
    register_rest_route('thientam/v1', '/training', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_all_trainings',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('thientam/v1', '/training/(?P<slug>[a-zA-Z0-9-_]+)', array(
        'methods'             => 'GET',
        'callback'            => 'thientam_rest_get_training_by_slug',
        'permission_callback' => '__return_true',
    ));
}

/**
 * Định dạng dữ liệu trả về cho 1 gói đào tạo
 *
 * @param int $post_id
 * @return array|null
 */
function thientam_format_training_response_data($post_id)
{
    clean_post_cache($post_id);
    $post = get_post($post_id);
    if (! $post) {
        return null;
    }

    $subtitle = get_post_meta($post_id, 'training_subtitle', true) ?: '';
    
    $duration = get_post_meta($post_id, 'training_duration', true) ?: '';
    $summary = $post->post_excerpt ?: get_post_meta($post_id, 'training_summary', true) ?: '';
    $target_audience = get_post_meta($post_id, 'training_target_audience', true) ?: '';

    // Metrics
    $raw_metrics = get_post_meta($post_id, 'training_metrics_array', true);
    if (empty($raw_metrics)) {
        $count = intval(get_post_meta($post_id, 'training_metrics', true));
        $raw_metrics = array();
        for ($i = 0; $i < $count; $i++) {
            $raw_metrics[] = array(
                'value' => get_post_meta($post_id, "training_metrics_{$i}_value", true) ?: '',
                'desc'  => get_post_meta($post_id, "training_metrics_{$i}_desc", true) ?: '',
            );
        }
    }

    // Audience Items
    $raw_audience = get_post_meta($post_id, 'training_audience_items_array', true);
    $audience_items = array();
    if (! empty($raw_audience) && is_array($raw_audience)) {
        foreach ($raw_audience as $a) {
            $audience_items[] = is_array($a) ? (isset($a['item']) ? $a['item'] : '') : $a;
        }
    } else {
        $count = intval(get_post_meta($post_id, 'training_audience_items', true));
        for ($i = 0; $i < $count; $i++) {
            $audience_items[] = get_post_meta($post_id, "training_audience_items_{$i}_item", true) ?: '';
        }
    }

    // Benefit Items
    $raw_benefits = get_post_meta($post_id, 'training_benefit_items_array', true);
    $benefit_items = array();
    if (! empty($raw_benefits) && is_array($raw_benefits)) {
        foreach ($raw_benefits as $b) {
            $benefit_items[] = array(
                'highlight' => isset($b['highlight']) ? $b['highlight'] : '',
                'text'      => isset($b['text']) ? $b['text'] : '',
            );
        }
    } else {
        $count = intval(get_post_meta($post_id, 'training_benefit_items', true));
        for ($i = 0; $i < $count; $i++) {
            $benefit_items[] = array(
                'highlight' => get_post_meta($post_id, "training_benefit_items_{$i}_highlight", true) ?: '',
                'text'      => get_post_meta($post_id, "training_benefit_items_{$i}_text", true) ?: '',
            );
        }
    }

    // Lessons
    $raw_lessons = get_post_meta($post_id, 'training_lessons_array', true);
    $lessons = array();
    if (! empty($raw_lessons) && is_array($raw_lessons)) {
        foreach ($raw_lessons as $l) {
            $lessons[] = is_array($l) ? (isset($l['lesson_title']) ? $l['lesson_title'] : '') : $l;
        }
    } else {
        $count = intval(get_post_meta($post_id, 'training_lessons', true));
        for ($i = 0; $i < $count; $i++) {
            $lessons[] = get_post_meta($post_id, "training_lessons_{$i}_lesson_title", true) ?: '';
        }
    }

    $thumbnail_id = get_post_thumbnail_id($post_id);
    $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'full') : '';

    return array(
        'slug'           => $post->post_name,
        'category'       => html_entity_decode($post->post_title, ENT_QUOTES, 'UTF-8'),
        'subtitle'       => html_entity_decode($subtitle, ENT_QUOTES, 'UTF-8'),
        'isCategory'     => true,
        
        'duration'       => $duration,
        'summary'        => html_entity_decode($summary, ENT_QUOTES, 'UTF-8'),
        'targetAudience' => html_entity_decode($target_audience, ENT_QUOTES, 'UTF-8'),
        'audienceItems'  => $audience_items,
        'benefitItems'   => $benefit_items,
        'metrics'        => $raw_metrics,
        'lessons'        => $lessons,
        'image'          => $thumbnail_url,
    );
}

/**
 * REST Callback: Lấy danh sách tất cả gói đào tạo
 *
 * @return \WP_REST_Response
 */
function thientam_rest_get_all_trainings()
{
    $posts = get_posts(array(
        'post_type'      => array('training', 'page'),
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order date',
        'order'          => 'ASC',
    ));

    $packages = array();
    foreach ($posts as $p) {
        $template = get_post_meta($p->ID, '_wp_page_template', true);
        if ($p->post_type === 'training' || $template === 'template-training-detail.php') {
            $packages[] = thientam_format_training_response_data($p->ID);
        }
    }

    return new WP_REST_Response(array(
        'success' => true,
        'data'    => $packages,
        'count'   => count($packages),
    ), 200);
}

/**
 * REST Callback: Lấy chi tiết gói đào tạo theo slug
 *
 * @param \WP_REST_Request|array $request
 * @return \WP_REST_Response
 */
function thientam_rest_get_training_by_slug($request)
{
    $slug = sanitize_title($request['slug']);

    $posts = get_posts(array(
        'post_type'      => array('training', 'page'),
        'name'           => $slug,
        'post_status'    => 'publish',
        'posts_per_page' => 1,
    ));

    if (empty($posts)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'Không tìm thấy gói đào tạo với slug: ' . $slug,
        ), 404);
    }

    $package = thientam_format_training_response_data($posts[0]->ID);

    return new WP_REST_Response(array(
        'success' => true,
        'data'    => $package,
    ), 200);
}

/**
 * 5. Tự động nạp sẵn toàn bộ custom fields vào cơ sở dữ liệu
 *
 * @return void
 */
add_action('admin_init', 'thientam_seed_training_custom_fields');
function thientam_seed_training_custom_fields()
{
    $seeded = get_option('thientam_training_cpt_fields_seeded_v3', false);
    if ($seeded) {
        return;
    }

    $default_packages = array(
        array(
            'id'             => 'tu-vi-nhap-mon',
            'category'       => 'Tử vi nhập môn',
            'subtitle'       => 'Giáo trình chuẩn hóa Cổ học phương Đông & Tử Vi thực chiến',
            'level'          => 'Cơ bản - Nhập môn',
            'duration'       => '10 chuyên đề cốt lõi',
            'summary'        => 'Trang bị nền tảng kiến thức chuẩn xác, hệ thống hóa toàn bộ 14 chính tinh, các bộ nhóm sao, cung vị và phương pháp luận đoán tổng quát trên 12 cung địa bàn.',
            'targetAudience' => 'Người mới bắt đầu tìm hiểu Cổ học & Tử Vi, muốn tự lập và luận giải lá số cho bản thân, gia đình.',
            'audienceItems'  => array(
                'Người muốn hiểu sâu về mệnh bàn cá nhân, gia đình và con cái',
                'Người yêu thích Cổ học phương Đông muốn tiếp cận Tử Vi một cách khoa học, bài bản',
            ),
            'benefitItems'   => array(
                array(
                    'highlight' => 'Hình thức linh hoạt:',
                    'text'      => ' Học trực tiếp hoặc online qua Zoom có ghi hình bài giảng để xem lại',
                ),
                array(
                    'highlight' => 'Giáo trình độc quyền:',
                    'text'      => ' Tài liệu đúc kết thực chiến, bảng tra cứu tinh gọn',
                ),
                array(
                    'highlight' => 'Đồng hành trọn đời:',
                    'text'      => ' Tham gia cộng đồng học viên Thiên Tâm, được giải đáp thắc mắc không giới hạn',
                ),
            ),
            'metrics'        => array(
                array('value' => '10 Chuyên đề', 'desc' => 'Lộ trình bài bản từ gốc'),
                array('value' => '60%+ Thực hành', 'desc' => 'Luận giải lá số thực tế'),
                array('value' => 'Online & Trực tiếp', 'desc' => 'Linh hoạt thời gian'),
                array('value' => 'Đồng hành 1:1', 'desc' => 'Hỗ trợ giải đáp trọn đời'),
            ),
            'lessons'        => array(
                'Giới thiệu nhập môn',
                'Ý nghĩa 14 sao chính tinh',
                'Bộ nhóm sao quan trọng trong luận đoán',
                'Ý nghĩa các nhóm sao khi luận đoán',
                'Vị trí các cung sắp xếp trên sơ đồ lá số Tử vi',
                'Cách truy vấn lá số sai, đúng qua cách an sao',
                'Ý nghĩa và luận đoán Vòng Trường sinh',
                'Tầm quan trọng của Vòng Lộc tồn và Thái tuế',
                '12 cách cục lá số Tử Vi',
                'Phương pháp luận đoán tổng quát lá số Tử vi trên 12 cung địa bàn',
            ),
        ),
        array(
            'id'             => 'tu-vi-nang-cao',
            'category'       => 'Tử vi nâng cao',
            'subtitle'       => 'Nâng tầm tư duy luận đoán đa chiều & giải mã hạn sâu sắc',
            'level'          => 'Nâng cao - Thực chiến',
            'duration'       => '9 chuyên đề chuyên sâu',
            'summary'        => 'Nâng tầm tư duy luận đoán đa chiều với phân tích tính cách lộ ẩn, tương tác vòng tam luân, tài sản - tiền bạc, vận hạn đại vận - tiểu hạn và hóa giải hung hiểm.',
            'targetAudience' => 'Học viên đã nắm vững căn bản, nhà quản trị, tư vấn viên hoặc người muốn nghiên cứu chuyên sâu để ứng dụng vào quản trị, kinh doanh và định hướng cuộc đời.',
            'audienceItems'  => array(
                'Học viên đã nắm vững căn bản, muốn nâng tầm tư duy luận đoán đa chiều',
                'Nhà quản trị, tư vấn viên cần đọc vị tính cách và tiềm năng nhân sự',
            ),
            'benefitItems'   => array(
                array(
                    'highlight' => 'Phân tích thực chiến:',
                    'text'      => ' Luận giải trực tiếp trên hàng chục lá số phức tạp và ca khó',
                ),
                array(
                    'highlight' => 'Tài liệu chuyên sâu:',
                    'text'      => ' Bộ cẩm nang luận hạn đại vận, tiểu hạn và phương pháp hóa giải',
                ),
                array(
                    'highlight' => 'Đồng hành 1:1:',
                    'text'      => ' Chuyên gia giải đáp và sửa bài luận lá số trực tiếp cho từng học viên',
                ),
            ),
            'metrics'        => array(
                array('value' => '9 Chuyên đề', 'desc' => 'Phân tích hạn chuyên sâu'),
                array('value' => '70%+ Thực hành', 'desc' => 'Luận giải ca khó & đa chiều'),
                array('value' => 'Online & Trực tiếp', 'desc' => 'Linh hoạt thời gian'),
                array('value' => 'Đồng hành 1:1', 'desc' => 'Hỗ trợ giải đáp trọn đời'),
            ),
            'lessons'        => array(
                'Luận đoán theo bộ sao',
                'Phân tích tính cách lộ và ẩn',
                'Phân tích tương tác Vòng tam luân với đương số',
                'Xác định cách cục tích lũy tiền, tài sản ít hay nhiều',
                'Ảnh hưởng của Tuần - Triệt',
                'Luận đoán đại vận',
                'Luận Tổng quát tiểu hạn / Lưu niên tiểu hạn (hạn năm)',
                'Ứng dụng tứ hóa khi luận đoán',
                'Phương pháp cân bằng và cách hóa giải những trường hợp không tốt',
            ),
        ),
        array(
            'id'             => 'tu-vi-chuyen-sau',
            'category'       => 'Tử vi chuyên sâu',
            'subtitle'       => 'Phương pháp luận hạn độc quyền & ứng dụng thực tế chuyên sâu',
            'level'          => 'Chuyên sâu - Ứng dụng',
            'duration'       => '6 chuyên đề chuyên sâu',
            'summary'        => 'Phương pháp luận hạn độc quyền, giải mã vận hạn từng tháng, đoán định sự kiện, tuyển dụng nhân sự và ứng dụng Phong thủy trong lá số Tử Vi.',
            'targetAudience' => 'Nhà lãnh đạo, doanh chủ, chuyên gia nhân sự và người thực hành luận giải chuyên nghiệp muốn giải quyết các bài toán thực tiễn phức tạp.',
            'audienceItems'  => array(
                'Chủ doanh nghiệp, lãnh đạo cấp cao muốn ứng dụng Tử Vi vào quản trị và đầu tư',
                'Chuyên gia nhân sự, tư vấn viên muốn dự đoán sự kiện và hóa giải vận hạn',
            ),
            'benefitItems'   => array(
                array(
                    'highlight' => 'Phương pháp độc quyền:',
                    'text'      => ' Tiếp cận kỹ thuật luận hạn chi tiết từng tháng và bí quyết định sự kiện',
                ),
                array(
                    'highlight' => 'Ứng dụng doanh nghiệp:',
                    'text'      => ' Tích hợp Phong thủy và Tử Vi vào tuyển dụng, sắp xếp bộ máy nhân sự',
                ),
                array(
                    'highlight' => 'Hỗ trợ cố vấn:',
                    'text'      => ' Được tham vấn trực tiếp các tình huống kinh doanh và quản trị thực tế',
                ),
            ),
            'metrics'        => array(
                array('value' => '6 Chuyên đề', 'desc' => 'Phương pháp luận hạn độc quyền'),
                array('value' => '80%+ Thực hành', 'desc' => 'Ứng dụng doanh nghiệp & nhân sự'),
                array('value' => 'Online & Trực tiếp', 'desc' => 'Linh hoạt thời gian'),
                array('value' => 'Đồng hành 1:1', 'desc' => 'Hỗ trợ giải đáp trọn đời'),
            ),
            'lessons'        => array(
                'Luận hạn tháng',
                'Luận tổng quát cục diện theo tình hình chung',
                'Luận Hạn chuyên sâu theo phương pháp độc quyền',
                'Ứng dụng trong tuyển dụng nhân sự, hợp tác, đoán định sự kiện, Ứng dụng Phong thủy trong lá số Tử vi',
                'Xem bệnh tật',
                'Tìm Phương án hóa giải sự việc trong lá số',
            ),
        ),
        array(
            'id'             => 'tu-vi-chan-truyen',
            'category'       => 'Tử vi chân truyền',
            'subtitle'       => 'Kết hợp tinh hoa Tử Vi, Kinh Dịch & Phong thủy thực nghiệm',
            'level'          => 'Chân truyền - Tinh hoa',
            'duration'       => '14 chuyên đề tinh hoa',
            'summary'        => 'Kết hợp tinh hoa Tử Vi, Kinh Dịch, Hà Đồ và Phong thủy thực nghiệm: Luận đoán và điều chỉnh số điện thoại, biển số xe, quẻ Mệnh, phong thủy không gian và chiêu cảm năng lượng.',
            'targetAudience' => 'Người muốn đạt đến trình độ thấu triệt toàn diện Cổ học, ứng dụng thuật số và phong thủy vào mọi mặt đời sống & kinh doanh.',
            'audienceItems'  => array(
                'Người nghiên cứu sâu muốn thấu triệt toàn diện Cổ học: Tử Vi, Kinh Dịch, Hà Đồ, Phong thủy',
                'Nhà thực hành huyền học chuyên nghiệp muốn sở hữu công cụ luận đoán toàn diện',
            ),
            'benefitItems'   => array(
                array(
                    'highlight' => 'Tinh hoa Cổ học:',
                    'text'      => ' Kết hợp 64 quẻ Dịch, Hà Đồ, Số điện thoại, Biển số xe và Phong thủy thực nghiệm',
                ),
                array(
                    'highlight' => 'Năng lượng & Chiêu cảm:',
                    'text'      => ' Phương pháp cân bằng Ngũ hành và chiêu cảm năng lượng xử lý công việc',
                ),
                array(
                    'highlight' => 'Đặc quyền Chân truyền:',
                    'text'      => ' Sinh hoạt chuyên đề đỉnh cao và kết nối mạng lưới học viên tinh hoa',
                ),
            ),
            'metrics'        => array(
                array('value' => '14 Chuyên đề', 'desc' => 'Tinh hoa Tử Vi & Kinh Dịch'),
                array('value' => 'Thực chiến 100%', 'desc' => 'Phong thủy thực nghiệm & quẻ Mệnh'),
                array('value' => 'Online & Trực tiếp', 'desc' => 'Linh hoạt thời gian'),
                array('value' => 'Đồng hành 1:1', 'desc' => 'Hỗ trợ giải đáp trọn đời'),
            ),
            'lessons'        => array(
                'Ứng dụng luận đoán số điện thoại, số xe, số nhà, tài khoản, tìm và điều chỉnh.',
                'Tìm hiểu 64 quẻ kinh dịch (Ý nghĩa - Hào từ)',
                'Phương pháp Luận Số Điện thoại - Bảng Số xe',
                'Phương pháp lấy quẻ - Luận sự kiện',
                'Phương pháp Luận đoán quẻ Mệnh',
                'Phương pháp Đọc và điều chỉnh sự kiện',
                'Tìm hiểu kiến thức cơ bản trên Hà đồ',
                'Phương pháp sử dụng công cụ',
                'Tìm và cân bằng Ngũ hành bổ khuyết cho Mệnh',
                'Phong thủy cho bàn làm việc',
                'Phong thủy cho căn nhà',
                'Phong thủy cho Công ty',
                'Phương pháp ứng dụng Ngũ hành vào ẩm thực, điều trị, ....',
                'Phương pháp để chiêu cảm năng lượng xử lý công việc',
            ),
        ),
        array(
            'id'             => 'ky-mon-phong-thuy-chien-luoc',
            'category'       => 'Kỳ môn phong thủy - Chiến lược',
            'subtitle'       => 'Chiến lược đỉnh cao trên bàn Kỳ môn & quản trị quyết định',
            'level'          => 'Chiến lược đỉnh cao',
            'duration'       => '2 chuyên đề chiến lược',
            'summary'        => 'Ứng dụng Kỳ môn bàn để đọc thế trận, hoạch định chiến lược kinh doanh, nắm bắt thời điểm vàng và ra quyết định tối ưu trong các lĩnh vực trọng yếu.',
            'targetAudience' => 'Chủ doanh nghiệp, nhà đầu tư, lãnh đạo chiến lược cần công cụ dự trù tình huống và lựa chọn thời cơ chuẩn xác.',
            'audienceItems'  => array(
                'Lãnh đạo doanh nghiệp, nhà đầu tư cần công cụ đọc thế trận và hoạch định chiến lược',
                'Người cần đưa ra quyết định trọng yếu trong đàm phán, kinh doanh và đầu tư',
            ),
            'benefitItems'   => array(
                array(
                    'highlight' => 'Chiến lược đỉnh cao:',
                    'text'      => ' Đọc và sử dụng Kỳ môn bàn để nắm bắt thời cơ và vị thế',
                ),
                array(
                    'highlight' => 'Ứng dụng đa lĩnh vực:',
                    'text'      => ' Áp dụng ngay vào đàm phán, ký kết hợp đồng và điều hành doanh nghiệp',
                ),
                array(
                    'highlight' => 'Cố vấn chiến lược:',
                    'text'      => ' Được hướng dẫn trực tiếp cách giải quyết các bài toán chiến lược hóc búa',
                ),
            ),
            'metrics'        => array(
                array('value' => '2 Chuyên đề', 'desc' => 'Kỳ môn bàn chiến lược'),
                array('value' => 'Thực chiến cao', 'desc' => 'Hoạch định & ra quyết định'),
                array('value' => 'Online & Trực tiếp', 'desc' => 'Linh hoạt thời gian'),
                array('value' => 'Đồng hành 1:1', 'desc' => 'Hỗ trợ giải đáp trọn đời'),
            ),
            'lessons'        => array(
                'Kỳ môn bàn - Cách đọc và sử dụng',
                'Áp dụng Chiến lược trên bàn kỳ môn cho các lĩnh vực',
            ),
        ),
        array(
            'id'             => 'kinh-dich-phoi-hop-thuc-chien',
            'category'       => 'Kinh dịch phối hợp thực chiến',
            'subtitle'       => 'Thấu suốt 64 quẻ Dịch & nghệ thuật ứng biến linh hoạt',
            'level'          => 'Thực chiến - Ứng biến',
            'duration'       => '2 chuyên đề thực chiến',
            'summary'        => 'Thấu hiểu nguyên lý 64 quẻ Dịch và phối hợp linh hoạt trong phong thủy, định số và tìm giải pháp xử lý hiệu quả cho các sự việc phát sinh.',
            'targetAudience' => 'Học viên muốn làm chủ tư duy Kinh Dịch để ứng biến nhanh, dự đoán chiều hướng phát triển của sự việc.',
            'audienceItems'  => array(
                'Học viên muốn làm chủ nghệ thuật ứng biến linh hoạt qua 64 quẻ Kinh Dịch',
                'Người muốn kết hợp Kinh Dịch vào chọn số, phong thủy và tìm phương án xử lý sự việc',
            ),
            'benefitItems'   => array(
                array(
                    'highlight' => 'Nguyên lý Dịch học:',
                    'text'      => ' Nắm vững bản chất 64 quẻ Dịch, hào từ và cách lập quẻ chuẩn xác',
                ),
                array(
                    'highlight' => 'Ứng dụng giải pháp:',
                    'text'      => ' Phối hợp Dịch học vào phong thủy, số học và giải quyết sự việc phát sinh',
                ),
                array(
                    'highlight' => 'Đồng hành thực hành:',
                    'text'      => ' Thực hành lấy quẻ và luận giải các sự việc thực tế phát sinh hàng ngày',
                ),
            ),
            'metrics'        => array(
                array('value' => '2 Chuyên đề', 'desc' => '64 quẻ Dịch thực chiến'),
                array('value' => 'Ứng biến nhanh', 'desc' => 'Ứng dụng trong số & phong thủy'),
                array('value' => 'Online & Trực tiếp', 'desc' => 'Linh hoạt thời gian'),
                array('value' => 'Đồng hành 1:1', 'desc' => 'Hỗ trợ giải đáp trọn đời'),
            ),
            'lessons'        => array(
                'Tìm hiểu kiến thức cơ bản trên 64 quẻ dịch',
                'Ứng dụng quẻ dịch trong phong thủy - số - giải pháp cho các sự việc',
            ),
        ),
        array(
            'id'             => 'tu-tru-bat-tu',
            'category'       => 'Tứ trụ - Bát tự',
            'subtitle'       => 'Giải mã bản mệnh, cân bằng ngũ hành & định hướng cuộc đời',
            'level'          => 'Chuyên sâu bản mệnh',
            'duration'       => '2 chuyên đề ứng dụng',
            'summary'        => 'Đọc và phân tích sơ đồ Tứ Trụ (Năm, Tháng, Ngày, Giờ sinh), phối hợp nhận diện vấn đề và ứng dụng cân bằng mệnh cục từ cá nhân đến vận hành doanh nghiệp.',
            'targetAudience' => 'Người quan tâm đến phân tích mệnh lý Tứ Trụ Bát Tự để thấu suốt bản thân và định hướng quản trị nhân sự.',
            'audienceItems'  => array(
                'Người muốn thấu suốt bản mệnh, ngũ hành bổ khuyết và chu kỳ vận mệnh qua Tứ Trụ',
                'Doanh chủ và nhà quản lý muốn ứng dụng Bát Tự vào thấu hiểu tính cách và dùng người',
            ),
            'benefitItems'   => array(
                array(
                    'highlight' => 'Phân tích Tứ Trụ:',
                    'text'      => ' Cách lập và đọc sơ đồ Năm, Tháng, Ngày, Giờ sinh chuẩn xác',
                ),
                array(
                    'highlight' => 'Cân bằng Mệnh cục:',
                    'text'      => ' Phương pháp bổ khuyết Ngũ hành cho cá nhân và vận hành doanh nghiệp',
                ),
                array(
                    'highlight' => 'Hỗ trợ trọn đời:',
                    'text'      => ' Được hỗ trợ luận giải và định hướng cân bằng mệnh cục dài hạn',
                ),
            ),
            'metrics'        => array(
                array('value' => '2 Chuyên đề', 'desc' => 'Phân tích sơ đồ Tứ Trụ'),
                array('value' => 'Cân bằng Mệnh', 'desc' => 'Ứng dụng cá nhân & doanh nghiệp'),
                array('value' => 'Online & Trực tiếp', 'desc' => 'Linh hoạt thời gian'),
                array('value' => 'Đồng hành 1:1', 'desc' => 'Hỗ trợ giải đáp trọn đời'),
            ),
            'lessons'        => array(
                'Cách đọc sơ đồ tứ trụ - Phối hợp và đọc vấn đề',
                'Ứng dụng tứ trụ trong cuộc sống từ cá nhân đến doanh nghiệp',
            ),
        ),
    );

    foreach ($default_packages as $order => $pkg) {
        $existing = get_posts(array(
            'post_type'      => 'training',
            'name'           => $pkg['id'],
            'post_status'    => 'any',
            'posts_per_page' => 1,
        ));

        $post_id = ! empty($existing) ? $existing[0]->ID : 0;

        if (! $post_id) {
            $post_id = wp_insert_post(array(
                'post_title'   => $pkg['category'],
                'post_name'    => $pkg['id'],
                'post_status'  => 'publish',
                'post_type'    => 'training',
                'post_excerpt' => $pkg['summary'],
                'menu_order'   => $order + 1,
            ));
        }

        if ($post_id && ! is_wp_error($post_id)) {
            // Lưu các trường đơn
            update_post_meta($post_id, 'training_subtitle', $pkg['subtitle']);
            
            update_post_meta($post_id, 'training_duration', $pkg['duration']);
            update_post_meta($post_id, 'training_summary', $pkg['summary']);
            update_post_meta($post_id, 'training_target_audience', $pkg['targetAudience']);

            // Lưu metrics
            $count_m = count($pkg['metrics']);
            update_post_meta($post_id, 'training_metrics', $count_m);
            foreach ($pkg['metrics'] as $i => $m) {
                update_post_meta($post_id, "training_metrics_{$i}_value", $m['value']);
                update_post_meta($post_id, "training_metrics_{$i}_desc", $m['desc']);
            }
            update_post_meta($post_id, 'training_metrics_array', $pkg['metrics']);

            // Lưu audience
            $count_a = count($pkg['audienceItems']);
            update_post_meta($post_id, 'training_audience_items', $count_a);
            $audience_obj = array();
            foreach ($pkg['audienceItems'] as $i => $a) {
                update_post_meta($post_id, "training_audience_items_{$i}_item", $a);
                $audience_obj[] = array('item' => $a);
            }
            update_post_meta($post_id, 'training_audience_items_array', $audience_obj);

            // Lưu benefits
            $count_b = count($pkg['benefitItems']);
            update_post_meta($post_id, 'training_benefit_items', $count_b);
            foreach ($pkg['benefitItems'] as $i => $b) {
                update_post_meta($post_id, "training_benefit_items_{$i}_highlight", $b['highlight']);
                update_post_meta($post_id, "training_benefit_items_{$i}_text", $b['text']);
            }
            update_post_meta($post_id, 'training_benefit_items_array', $pkg['benefitItems']);

            // Lưu lessons
            $count_l = count($pkg['lessons']);
            update_post_meta($post_id, 'training_lessons', $count_l);
            $lessons_obj = array();
            foreach ($pkg['lessons'] as $i => $l) {
                update_post_meta($post_id, "training_lessons_{$i}_lesson_title", $l);
                $lessons_obj[] = array('lesson_title' => $l);
            }
            update_post_meta($post_id, 'training_lessons_array', $lessons_obj);

            if (function_exists('update_field')) {
                update_field('training_subtitle', $pkg['subtitle'], $post_id);
                
                update_field('training_duration', $pkg['duration'], $post_id);
                update_field('training_summary', $pkg['summary'], $post_id);
                update_field('training_target_audience', $pkg['targetAudience'], $post_id);
                update_field('training_metrics', $pkg['metrics'], $post_id);
                update_field('training_audience_items', $audience_obj, $post_id);
                update_field('training_benefit_items', $pkg['benefitItems'], $post_id);
                update_field('training_lessons', $lessons_obj, $post_id);
            }
        }
    }

    update_option('thientam_training_cpt_fields_seeded_v3', true);
}
