<?php

/**
 * Native WordPress Meta Boxes for Training Detail Page (Separate Boxes)
 */

if (! defined("ABSPATH")) {
    exit;
}

add_action("add_meta_boxes", "thientam_register_training_detail_metaboxes", 10, 2);
function thientam_register_training_detail_metaboxes($post_type, $post)
{
    if ($post_type !== "page" || ! $post) {
        return;
    }

    $template = get_post_meta($post->ID, "_wp_page_template", true);

    if ($template === "template-training-detail.php") {
        add_meta_box("thientam_box_training_general", "Thông tin chung Gói Đào Tạo (General Info)", "thientam_render_box_training_general", "page", "normal", "high");
        add_meta_box("thientam_box_training_metrics", "Chỉ số nổi bật (Key Metrics Bar)", "thientam_render_box_training_metrics", "page", "normal", "high");
        add_meta_box("thientam_box_training_audience", "Đối tượng phù hợp chi tiết (Audience Items)", "thientam_render_box_training_audience", "page", "normal", "high");
        add_meta_box("thientam_box_training_benefits", "Hình thức & Quyền lợi học viên (Benefit Items)", "thientam_render_box_training_benefits", "page", "normal", "high");
        add_meta_box("thientam_box_training_lessons", "Danh sách Chuyên đề / Bài giảng (Lessons)", "thientam_render_box_training_lessons", "page", "normal", "high");
        add_meta_box("thientam_box_training_seo", "Tối ưu SEO & Ảnh chia sẻ Mạng Xã Hội (SEO & OG Share)", "thientam_render_box_training_seo", "page", "normal", "high");
    }
}

// Enqueue WP Media cho uploader ảnh SEO
add_action('admin_enqueue_scripts', 'thientam_training_enqueue_media');
function thientam_training_enqueue_media($hook) {
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        wp_enqueue_media();
    }
}

// Ẩn Content Editor mặc định khi dùng template Chi Tiết Đào Tạo
add_action("admin_init", "thientam_hide_editor_for_training_detail");
function thientam_hide_editor_for_training_detail()
{
    $post_id = isset($_GET["post"]) ? intval($_GET["post"]) : (isset($_POST["post_ID"]) ? intval($_POST["post_ID"]) : 0);
    if (! $post_id) {
        return;
    }
    $template = get_post_meta($post_id, "_wp_page_template", true);
    if ($template === "template-training-detail.php") {
        remove_post_type_support("page", "editor");
    }
}

// Ẩn/Hiện các metabox theo Template động bằng Javascript
add_action("admin_footer-post.php", "thientam_training_boxes_toggle_js");
add_action("admin_footer-post-new.php", "thientam_training_boxes_toggle_js");
function thientam_training_boxes_toggle_js()
{
    global $post;
    if (! $post || $post->post_type !== "page") {
        return;
    }
?>
    <script>
        (function($) {
            function toggleTrainingBoxes() {
                var isTraining = ($("#page_template").val() === "template-training-detail.php");
                var boxIds = [
                    "#thientam_box_training_general",
                    "#thientam_box_training_metrics",
                    "#thientam_box_training_audience",
                    "#thientam_box_training_benefits",
                    "#thientam_box_training_lessons",
                    "#thientam_box_training_seo"
                ];

                $.each(boxIds, function(i, id) {
                    if (isTraining) {
                        $(id).show();
                    } else {
                        $(id).hide();
                    }
                });

                if (isTraining) {
                    $("#postdivrich").hide();
                } else if ($("#page_template").val() !== "template-service-detail.php") {
                    $("#postdivrich").show();
                }
            }

            $(document).ready(function() {
                toggleTrainingBoxes();
                $("#page_template").on("change", toggleTrainingBoxes);
            });
        })(jQuery);
    </script>
<?php
}

// BOX 1: THÔNG TIN CHUNG
function thientam_render_box_training_general($post)
{
    wp_nonce_field("thientam_save_training_boxes", "thientam_training_boxes_nonce");
    $post_id = $post->ID;

    $subtitle = get_post_meta($post_id, "training_subtitle", true) ?: get_post_meta($post_id, "_training_subtitle", true) ?: "";
    $level = get_post_meta($post_id, true) ?: get_post_meta($post_id, "_training_level", true) ?: "Cơ bản - Nhập môn";
    $duration = get_post_meta($post_id, "training_duration", true) ?: get_post_meta($post_id, "_training_duration", true) ?: "";
    $summary = get_post_meta($post_id, "training_summary", true) ?: get_post_meta($post_id, "_training_summary", true) ?: "";
    $target_audience = get_post_meta($post_id, "training_target_audience", true) ?: get_post_meta($post_id, "_training_target_audience", true) ?: "";
?>
    <style>
        .tt-field-row { margin-bottom: 16px; }
        .tt-field-row label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #1d2327; }
        .tt-field-row input[type="text"], .tt-field-row textarea { width: 100%; max-width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #8c8f94; font-size: 14px; box-sizing: border-box; }
        .tt-field-row textarea { min-height: 70px; line-height: 1.5; }
        .tt-field-row .description { color: #646970; font-size: 12px; margin-top: 4px; display: block; }
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

// BOX 2: CHỈ SỐ NỔI BẬT
function thientam_render_box_training_metrics($post)
{
    $post_id = $post->ID;
    $raw_metrics = get_field("training_metrics", $post_id) ?: get_post_meta($post_id, "training_metrics", true) ?: get_post_meta($post_id, "_training_metrics", true) ?: array();
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
            $("#btn-add-metric").on("click", function() {
                var idx = $("#table-training-metrics tbody tr").length;
                var row = "<tr>" +
                    "<td><input type="text" name="native_training_metrics[" + idx + "][value]" style="width:100%;" placeholder="60%+ Thực hành" /></td>" +
                    "<td><input type="text" name="native_training_metrics[" + idx + "][desc]" style="width:100%;" placeholder="Luận giải lá số thực tế" /></td>" +
                    "<td style="text-align: center;"><button type="button" class="tt-btn-remove" onclick="jQuery(this).closest('tr').remove();">×</button></td>" +
                    "</tr>";
                $("#table-training-metrics tbody").append(row);
            });
        });
    </script>
<?php
}

// BOX 3: ĐỐI TƯỢNG CHI TIẾT
function thientam_render_box_training_audience($post)
{
    $post_id = $post->ID;
    $raw_audience = get_field("training_audience_items", $post_id) ?: get_post_meta($post_id, "training_audience_items", true) ?: get_post_meta($post_id, "_training_audience_items", true) ?: array();
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
            $("#btn-add-audience").on("click", function() {
                var row = "<tr>" +
                    "<td><input type="text" name="native_training_audience[]" style="width:100%;" placeholder="Nhập đối tượng phù hợp..." /></td>" +
                    "<td style="text-align: center;"><button type="button" class="tt-btn-remove" onclick="jQuery(this).closest('tr').remove();">×</button></td>" +
                    "</tr>";
                $("#table-training-audience tbody").append(row);
            });
        });
    </script>
<?php
}

// BOX 4: HÌNH THỨC & QUYỀN LỢI HỌC VIÊN
function thientam_render_box_training_benefits($post)
{
    $post_id = $post->ID;
    $raw_benefits = get_field("training_benefit_items", $post_id) ?: get_post_meta($post_id, "training_benefit_items", true) ?: get_post_meta($post_id, "_training_benefit_items", true) ?: array();
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
            $("#btn-add-benefit").on("click", function() {
                var idx = $("#table-training-benefits tbody tr").length;
                var row = "<tr>" +
                    "<td><input type="text" name="native_training_benefits[" + idx + "][highlight]" style="width:100%;" placeholder="Giáo trình độc quyền:" /></td>" +
                    "<td><input type="text" name="native_training_benefits[" + idx + "][text]" style="width:100%;" placeholder="Tài liệu đúc kết thực chiến..." /></td>" +
                    "<td style="text-align: center;"><button type="button" class="tt-btn-remove" onclick="jQuery(this).closest('tr').remove();">×</button></td>" +
                    "</tr>";
                $("#table-training-benefits tbody").append(row);
            });
        });
    </script>
<?php
}

// BOX 5: NỘI DUNG CHUYÊN ĐỀ
function thientam_render_box_training_lessons($post)
{
    $post_id = $post->ID;
    $raw_lessons = get_field("training_lessons", $post_id) ?: get_post_meta($post_id, "training_lessons", true) ?: get_post_meta($post_id, "_training_lessons", true) ?: array();
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
            $("#btn-add-lesson").on("click", function() {
                var count = $("#table-training-lessons tbody tr").length + 1;
                var row = "<tr>" +
                    "<td style="text-align: center; font-weight: 600; color: #646970;">" + count + "</td>" +
                    "<td><input type="text" name="native_training_lessons[]" style="width:100%;" placeholder="Nhập tên chuyên đề..." /></td>" +
                    "<td style="text-align: center;"><button type="button" class="tt-btn-remove" onclick="jQuery(this).closest('tr').remove();">×</button></td>" +
                    "</tr>";
                $("#table-training-lessons tbody").append(row);
            });
        });
    </script>
<?php
}

// BOX 6: TỐI ƯU SEO & ẢNH CHIA SẺ MẠNG XÃ HỘI (OG IMAGE)
function thientam_render_box_training_seo($post)
{
    $post_id = $post->ID;
    $seo_title = get_post_meta($post_id, "training_seo_meta_title", true) ?: get_post_meta($post_id, "seo_meta_title", true) ?: "";
    $seo_desc = get_post_meta($post_id, "training_seo_meta_description", true) ?: get_post_meta($post_id, "seo_meta_description", true) ?: "";
    $seo_keywords = get_post_meta($post_id, "training_seo_meta_keywords", true) ?: get_post_meta($post_id, "seo_meta_keywords", true) ?: "";
    $seo_og_image = get_post_meta($post_id, "training_seo_og_image", true) ?: get_post_meta($post_id, "seo_og_image", true) ?: "";
    $default_placeholder = "https://site.thientam68.com/wp-content/uploads/2026/08/Placeholder-Thien-Tam.png";
    $thumbnail_id = get_post_thumbnail_id($post_id);
    $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, "full") : "";
    $effective_preview = $seo_og_image ?: ($thumbnail_url ?: $default_placeholder);
?>
    <style>
        .tt-seo-field-group { margin-bottom: 16px; }
        .tt-seo-field-group label { display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b; }
        .tt-seo-field-group p.description { color: #64748b; font-size: 13px; margin: 4px 0 0; }
        .tt-seo-preview-wrap { display: flex; gap: 16px; align-items: flex-start; margin-top: 8px; flex-wrap: wrap; }
        .tt-seo-thumb-box { width: 220px; height: 115px; border-radius: 8px; border: 1px solid #cbd5e1; overflow: hidden; background: #f8fafc; display: flex; align-items: center; justify-content: center; }
        .tt-seo-thumb-box img { width: 100%; height: 100%; object-fit: cover; }
        .tt-seo-btn-group { display: flex; flex-direction: column; gap: 8px; justify-content: center; }
    </style>
    <div class="tt-seo-field-group">
        <label for="training_seo_meta_title">Tiêu đề SEO tùy biến (Meta Title):</label>
        <input type="text" id="training_seo_meta_title" name="training_seo_meta_title" value="<?php echo esc_attr($seo_title); ?>" style="width: 100%;" placeholder="Để trống sẽ tự động lấy: [Tên khóa học] - Đào tạo Tử Vi Thiên Tâm" />
        <p class="description">Tiêu đề hiển thị trên thanh tiêu đề trình duyệt và kết quả tìm kiếm Google (50-60 ký tự).</p>
    </div>
    <div class="tt-seo-field-group">
        <label for="training_seo_meta_description">Mô tả SEO tùy biến (Meta Description):</label>
        <textarea id="training_seo_meta_description" name="training_seo_meta_description" rows="3" style="width: 100%;" placeholder="Để trống sẽ tự động lấy Tóm tắt khóa học..."><?php echo esc_textarea($seo_desc); ?></textarea>
        <p class="description">Đoạn tóm tắt hiển thị trên Google và khi chia sẻ liên kết (120-160 ký tự).</p>
    </div>
    <div class="tt-seo-field-group">
        <label for="training_seo_meta_keywords">Từ khóa SEO (Meta Keywords):</label>
        <input type="text" id="training_seo_meta_keywords" name="training_seo_meta_keywords" value="<?php echo esc_attr($seo_keywords); ?>" style="width: 100%;" placeholder="tử vi nhập môn, đào tạo tử vi, khóa học cổ học, phong thủy thiên tâm..." />
        <p class="description">Các từ khóa cách nhau bởi dấu phẩy.</p>
    </div>
    <div class="tt-seo-field-group">
        <label>Ảnh chia sẻ Mạng Xã Hội (OG Image / Social Share):</label>
        <div class="tt-seo-preview-wrap">
            <div class="tt-seo-thumb-box" id="tt-training-og-preview">
                <img src="<?php echo esc_url($effective_preview); ?>" alt="Preview" />
            </div>
            <div class="tt-seo-btn-group">
                <input type="hidden" name="training_seo_og_image" id="tt-training-og-input" value="<?php echo esc_attr($seo_og_image); ?>" />
                <button type="button" class="button button-primary" id="tt-training-og-pick-btn">🖼️ Chọn ảnh từ Media...</button>
                <button type="button" class="button" id="tt-training-og-remove-btn" style="<?php echo empty($seo_og_image) ? 'display:none;' : ''; ?>">Xóa ảnh tùy biến (Lấy từ Featured Image)</button>
                <p class="description" style="max-width: 480px;">
                    <strong>Quy tắc nhận ảnh:</strong> Ưu tiên ảnh tùy biến tại đây &rarr; Nếu không có sẽ lấy Ảnh đại diện (Featured Image) của trang &rarr; Nếu chưa cài ảnh đại diện sẽ tự động dùng ảnh mặc định Thiên Tâm: <code><?php echo esc_html($default_placeholder); ?></code>.
                </p>
            </div>
        </div>
    </div>
    <script>
        jQuery(document).ready(function($) {
            var mediaFrame = null;
            var defaultFallback = <?php echo json_encode($thumbnail_url ?: $default_placeholder); ?>;

            $("#tt-training-og-pick-btn").on("click", function(e) {
                e.preventDefault();
                if (mediaFrame) {
                    mediaFrame.open();
                    return;
                }
                mediaFrame = wp.media({
                    title: "Chọn ảnh chia sẻ Mạng Xã Hội (OG Image)",
                    button: { text: "Sử dụng ảnh này" },
                    multiple: false
                });
                mediaFrame.on("select", function() {
                    var attachment = mediaFrame.state().get("selection").first().toJSON();
                    var url = attachment.url;
                    $("#tt-training-og-input").val(url);
                    $("#tt-training-og-preview img").attr("src", url);
                    $("#tt-training-og-remove-btn").show();
                });
                mediaFrame.open();
            });

            $("#tt-training-og-remove-btn").on("click", function(e) {
                e.preventDefault();
                $("#tt-training-og-input").val("");
                $("#tt-training-og-preview img").attr("src", defaultFallback);
                $(this).hide();
            });
        });
    </script>
<?php
}

// Lưu dữ liệu tất cả các metaboxes
add_action("save_post_page", "thientam_save_training_boxes_data", 10, 2);
function thientam_save_training_boxes_data($post_id, $post)
{
    if (! isset($_POST["thientam_training_boxes_nonce"]) || ! wp_verify_nonce($_POST["thientam_training_boxes_nonce"], "thientam_save_training_boxes")) {
        return;
    }

    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
        return;
    }

    if (! current_user_can("edit_page", $post_id)) {
        return;
    }

    // 1. Lưu thông tin chung
    $fields = array("training_subtitle", "training_duration", "training_summary", "training_target_audience");
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $val = sanitize_text_field($_POST[$field]);
            update_post_meta($post_id, $field, $val);
            update_post_meta($post_id, "_" . $field, $val);
        }
    }

    // 2. Lưu metrics
    if (isset($_POST["native_training_metrics"]) && is_array($_POST["native_training_metrics"])) {
        $metrics = array();
        $acf_metrics = array();
        foreach ($_POST["native_training_metrics"] as $m) {
            $val = sanitize_text_field($m["value"] ?? "");
            $desc = sanitize_text_field($m["desc"] ?? "");
            if ($val !== "" || $desc !== "") {
                $metrics[] = array("value" => $val, "desc" => $desc);
                $acf_metrics[] = array("field_metric_value" => $val, "field_metric_desc" => $desc);
            }
        }
        update_post_meta($post_id, "training_metrics", $metrics);
        update_post_meta($post_id, "_training_metrics", $metrics);
        if (function_exists("update_field")) {
            update_field("training_metrics", $metrics, $post_id);
        }
    }

    // 3. Lưu audience
    if (isset($_POST["native_training_audience"]) && is_array($_POST["native_training_audience"])) {
        $audience = array();
        $acf_audience = array();
        foreach ($_POST["native_training_audience"] as $item) {
            $val = sanitize_text_field($item);
            if ($val !== "") {
                $audience[] = $val;
                $acf_audience[] = array("item" => $val);
            }
        }
        update_post_meta($post_id, "training_audience_items", $audience);
        update_post_meta($post_id, "_training_audience_items", $audience);
        if (function_exists("update_field")) {
            update_field("training_audience_items", $acf_audience, $post_id);
        }
    }

    // 4. Lưu benefits
    if (isset($_POST["native_training_benefits"]) && is_array($_POST["native_training_benefits"])) {
        $benefits = array();
        foreach ($_POST["native_training_benefits"] as $b) {
            $highlight = sanitize_text_field($b["highlight"] ?? "");
            $text = sanitize_text_field($b["text"] ?? "");
            if ($highlight !== "" || $text !== "") {
                $benefits[] = array("highlight" => $highlight, "text" => $text);
            }
        }
        update_post_meta($post_id, "training_benefit_items", $benefits);
        update_post_meta($post_id, "_training_benefit_items", $benefits);
        if (function_exists("update_field")) {
            update_field("training_benefit_items", $benefits, $post_id);
        }
    }

    // 5. Lưu lessons
    if (isset($_POST["native_training_lessons"]) && is_array($_POST["native_training_lessons"])) {
        $lessons = array();
        $acf_lessons = array();
        foreach ($_POST["native_training_lessons"] as $l) {
            $val = sanitize_text_field($l);
            if ($val !== "") {
                $lessons[] = $val;
                $acf_lessons[] = array("lesson_title" => $val);
            }
        }
        update_post_meta($post_id, "training_lessons", $lessons);
        update_post_meta($post_id, "_training_lessons", $lessons);
        if (function_exists("update_field")) {
            update_field("training_lessons", $acf_lessons, $post_id);
        }
    }

    // 6. Lưu SEO & OG Image
    $seo_fields = array(
        "training_seo_meta_title"       => "seo_meta_title",
        "training_seo_meta_description" => "seo_meta_description",
        "training_seo_meta_keywords"    => "seo_meta_keywords",
        "training_seo_og_image"         => "seo_og_image"
    );
    foreach ($seo_fields as $post_key => $alias_key) {
        if (isset($_POST[$post_key])) {
            $val = sanitize_text_field(wp_unslash($_POST[$post_key]));
            update_post_meta($post_id, $post_key, $val);
            update_post_meta($post_id, "_" . $post_key, $val);
            update_post_meta($post_id, $alias_key, $val);
            update_post_meta($post_id, "_" . $alias_key, $val);
        }
    }
}
