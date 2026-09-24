<?php
/**
 * Custom Post Type: Tuyển dụng (Recruitment) & ACF Custom Fields & REST API
 *
 * - Post Type: `recruitment`
 * - Menu Admin: "Tuyển dụng" (icon: dashicons-businessman)
 * - Taxonomy: `recruitment_department` ("Phòng ban")
 * - ACF Local Field Group: `group_recruitment_settings`
 * - REST API Endpoints:
 *     + GET /wp-json/thientam/v1/recruitment
 *     + GET /wp-json/thientam/v1/recruitment/{slug}
 *     + GET /wp-json/thientam/v1/recruitment/departments
 * - Tự động tạo 6 vị trí tuyển dụng mẫu khi chưa có dữ liệu
 *
 * @package HelloElementorChild
 */

if (! defined("ABSPATH")) {
    exit;
}

/**
 * 1. Đăng ký CPT & Taxonomy Tuyển dụng
 */
add_action("init", "thientam_register_recruitment_cpt_and_tax", 5);
function thientam_register_recruitment_cpt_and_tax()
{
    // Taxonomy: Phòng ban tuyển dụng
    $tax_labels = [
        "name"              => "Phòng ban",
        "singular_name"     => "Phòng ban",
        "search_items"      => "Tìm kiếm phòng ban",
        "all_items"         => "Tất cả phòng ban",
        "parent_item"       => "Phòng ban cha",
        "parent_item_colon" => "Phòng ban cha:",
        "edit_item"         => "Sửa phòng ban",
        "update_item"       => "Cập nhật phòng ban",
        "add_new_item"      => "Thêm phòng ban mới",
        "new_item_name"     => "Tên phòng ban mới",
        "menu_name"         => "Phòng ban",
    ];

    register_taxonomy("recruitment_department", ["recruitment"], [
        "hierarchical"      => true,
        "labels"            => $tax_labels,
        "show_ui"           => true,
        "show_admin_column" => true,
        "query_var"         => true,
        "show_in_rest"      => true,
        "rewrite"           => ["slug" => "phong-ban", "with_front" => false],
    ]);

    // Custom Post Type: Tuyển dụng
    $cpt_labels = [
        "name"               => "Tuyển dụng",
        "singular_name"      => "Vị trí tuyển dụng",
        "menu_name"          => "Tuyển dụng",
        "name_admin_bar"     => "Tuyển dụng",
        "add_new"            => "Thêm vị trí mới",
        "add_new_item"       => "Thêm vị trí tuyển dụng mới",
        "new_item"           => "Vị trí mới",
        "edit_item"          => "Chỉnh sửa vị trí",
        "view_item"          => "Xem vị trí",
        "all_items"          => "Tất cả vị trí",
        "search_items"       => "Tìm kiếm vị trí tuyển dụng",
        "not_found"          => "Chưa có vị trí tuyển dụng nào.",
        "not_found_in_trash" => "Không có vị trí nào trong thùng rác.",
    ];

    register_post_type("recruitment", [
        "labels"             => $cpt_labels,
        "description"        => "Các vị trí tuyển dụng và cơ hội nghề nghiệp tại Thiên Tâm",
        "public"             => true,
        "publicly_queryable" => true,
        "show_ui"            => true,
        "show_in_menu"       => true,
        "query_var"          => true,
        "rewrite"            => ["slug" => "tuyen-dung", "with_front" => false],
        "capability_type"    => "post",
        "has_archive"        => false,
        "hierarchical"       => false,
        "menu_position"      => 22,
        "menu_icon"          => "dashicons-businessman",
        "show_in_rest"       => true,
        "supports"           => ["title", "editor", "excerpt", "thumbnail"],
    ]);
}

/**
 * 2. Đăng ký ACF Field Group bằng mã PHP (tương thích ngay cả khi chưa sync acf-json)
 */
add_action("acf/init", "thientam_register_recruitment_acf_group");
function thientam_register_recruitment_acf_group()
{
    if (! function_exists("acf_add_local_field_group")) {
        return;
    }

    acf_add_local_field_group([
        "key"    => "group_recruitment_settings",
        "title"  => "Thông tin chi tiết Vị Trí Tuyển Dụng (Job Settings)",
        "fields" => [
            [
                "key"          => "field_recruitment_salary",
                "label"        => "Mức lương / Thu nhập (Salary)",
                "name"         => "salary",
                "type"         => "text",
                "instructions" => "Ví dụ: 18.000.000 - 30.000.000 VNĐ + Thưởng dự án hoặc Thỏa thuận theo năng lực",
                "placeholder"  => "18.000.000 - 30.000.000 VNĐ",
            ],
            [
                "key"          => "field_recruitment_location",
                "label"        => "Địa điểm làm việc (Location)",
                "name"         => "location",
                "type"         => "text",
                "instructions" => "Ví dụ: Đà Lạt & TP. Hồ Chí Minh hoặc Đà Lạt / Hybrid",
                "placeholder"  => "Đà Lạt & TP. Hồ Chí Minh",
            ],
            [
                "key"           => "field_recruitment_type",
                "label"         => "Hình thức làm việc (Employment Type)",
                "name"          => "type",
                "type"          => "select",
                "instructions"  => "Chọn hình thức làm việc",
                "choices"       => [
                    "Toàn thời gian"               => "Toàn thời gian",
                    "Bán thời gian"                => "Bán thời gian",
                    "Cộng tác viên / Bán thời gian"=> "Cộng tác viên / Bán thời gian",
                    "Làm việc từ xa (Remote)"      => "Làm việc từ xa (Remote)",
                ],
                "default_value" => "Toàn thời gian",
            ],
            [
                "key"          => "field_recruitment_experience",
                "label"        => "Kinh nghiệm yêu cầu (Experience)",
                "name"         => "experience",
                "type"         => "text",
                "instructions" => "Ví dụ: Từ 2 năm kinh nghiệm hoặc Đam mê triết học / Hán Nôm",
                "placeholder"  => "Từ 2 năm kinh nghiệm",
            ],
            [
                "key"          => "field_recruitment_deadline",
                "label"        => "Hạn nộp hồ sơ (Deadline)",
                "name"         => "deadline",
                "type"         => "text",
                "instructions" => "Ví dụ: 31/10/2026",
                "placeholder"  => "31/10/2026",
            ],
            [
                "key"           => "field_recruitment_is_featured",
                "label"         => "Vị trí nổi bật (Featured Job)",
                "name"          => "is_featured",
                "type"          => "true_false",
                "instructions"  => "Bật để gắn huy hiệu Nổi bật và hiển thị viền vàng trang trọng",
                "message"       => "Đánh dấu là vị trí tuyển dụng nổi bật",
                "default_value" => 0,
            ],
            [
                "key"          => "field_recruitment_overview",
                "label"        => "Tổng quan vai trò (Role Overview)",
                "name"         => "overview",
                "type"         => "textarea",
                "rows"         => 4,
                "instructions" => "Giới thiệu ý nghĩa vai trò, sứ mệnh công việc đối với khách hàng và Thiên Tâm.",
            ],
            [
                "key"          => "field_recruitment_responsibilities",
                "label"        => "Nhiệm vụ & Trách nhiệm chính (Responsibilities)",
                "name"         => "responsibilities",
                "type"         => "textarea",
                "rows"         => 6,
                "instructions" => "Mỗi dòng là một gạch đầu dòng nhiệm vụ.",
            ],
            [
                "key"          => "field_recruitment_requirements",
                "label"        => "Yêu cầu ứng viên (Requirements)",
                "name"         => "requirements",
                "type"         => "textarea",
                "rows"         => 6,
                "instructions" => "Mỗi dòng là một gạch đầu dòng yêu cầu về bằng cấp, kỹ năng và thái độ.",
            ],
            [
                "key"          => "field_recruitment_benefits",
                "label"        => "Quyền lợi & Chế độ đãi ngộ (Benefits)",
                "name"         => "benefits",
                "type"         => "textarea",
                "rows"         => 6,
                "instructions" => "Mỗi dòng là một gạch đầu dòng quyền lợi (lương thưởng, đào tạo, văn hóa...).",
            ],
            [
                "key"          => "field_recruitment_work_location",
                "label"        => "Địa chỉ nơi làm việc cụ thể (Specific Work Location)",
                "name"         => "work_location",
                "type"         => "text",
                "instructions" => "Ví dụ: Số 61/2 An Bình, Phường Xuân Hương, TP. Đà Lạt",
            ],
            [
                "key"          => "field_recruitment_work_time",
                "label"        => "Thời gian làm việc (Work Hours)",
                "name"         => "work_time",
                "type"         => "text",
                "instructions" => "Ví dụ: Thứ Hai đến Thứ Bảy (08:00 - 17:30)",
            ],
        ],
        "location" => [
            [
                [
                    "param"    => "post_type",
                    "operator" => "==",
                    "value"    => "recruitment",
                ],
            ],
        ],
        "menu_order" => 0,
        "position"   => "normal",
        "style"      => "default",
        "active"     => true,
    ]);
}

/**
 * 2.1 Đăng ký Native WordPress Meta Box cho Tuyển dụng
 * Hiển thị 100% trong Admin ngay cả khi WordPress KHÔNG cài plugin ACF
 */
add_action("add_meta_boxes", "thientam_register_recruitment_native_metabox");
function thientam_register_recruitment_native_metabox()
{
    add_meta_box(
        "thientam_recruitment_details",
        "💼 Thông tin Vị trí Tuyển dụng & Chế độ đãi ngộ (Job Details)",
        "thientam_render_recruitment_native_metabox",
        "recruitment",
        "normal",
        "high"
    );
}

/**
 * 2.2 Giao diện khung nhập liệu Native Meta Box trong WP Admin
 */
function thientam_render_recruitment_native_metabox($post)
{
    wp_nonce_field("thientam_save_recruitment_meta", "thientam_recruitment_nonce");
    $id = $post->ID;

    $salary          = get_post_meta($id, "salary", true) ?: "";
    $location        = get_post_meta($id, "location", true) ?: "";
    $type            = get_post_meta($id, "type", true) ?: "Toàn thời gian";
    $experience      = get_post_meta($id, "experience", true) ?: "";
    $deadline        = get_post_meta($id, "deadline", true) ?: "";
    $is_featured     = get_post_meta($id, "is_featured", true);
    $work_location   = get_post_meta($id, "work_location", true) ?: "";
    $work_time       = get_post_meta($id, "work_time", true) ?: "";
    $overview        = get_post_meta($id, "overview", true) ?: "";

    $responsibilities = get_post_meta($id, "responsibilities", true);
    if (is_array($responsibilities)) {
        $responsibilities = implode("\n", array_filter($responsibilities));
    }

    $requirements = get_post_meta($id, "requirements", true);
    if (is_array($requirements)) {
        $requirements = implode("\n", array_filter($requirements));
    }

    $benefits = get_post_meta($id, "benefits", true);
    if (is_array($benefits)) {
        $benefits = implode("\n", array_filter($benefits));
    }
    ?>
    <style>
        .tt-job-metabox {
            font-size: 13px;
            color: #1d2327;
            padding: 8px 0;
        }
        .tt-job-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px 24px;
            margin-bottom: 20px;
        }
        @media (max-width: 782px) {
            .tt-job-grid {
                grid-template-columns: 1fr;
            }
        }
        .tt-job-field {
            margin-bottom: 16px;
        }
        .tt-job-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #1d2327;
        }
        .tt-job-field input[type="text"],
        .tt-job-field select,
        .tt-job-field textarea {
            width: 100%;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #8c8f94;
            font-size: 13px;
            line-height: 1.5;
            background: #fff;
            box-sizing: border-box;
        }
        .tt-job-field input[type="text"]:focus,
        .tt-job-field select:focus,
        .tt-job-field textarea:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
            outline: 2px solid transparent;
        }
        .tt-job-field .desc {
            color: #646970;
            font-size: 12px;
            margin-top: 4px;
            display: block;
        }
        .tt-job-featured-box {
            background: #fdf8ed;
            border: 1px solid #f6e05e;
            border-radius: 6px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 38px;
            box-sizing: border-box;
        }
        .tt-job-section-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            padding-bottom: 8px;
            margin-bottom: 16px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>

    <div class="tt-job-metabox">
        <div class="tt-job-section-title">
            <span>📌 Thông số cơ bản vị trí tuyển dụng</span>
        </div>

        <div class="tt-job-grid">
            <div class="tt-job-field">
                <label for="recruitment_salary">Mức lương / Thu nhập (Salary)</label>
                <input type="text" id="recruitment_salary" name="salary" value="<?php echo esc_attr($salary); ?>" placeholder="VD: 18.000.000 - 30.000.000 VNĐ hoặc Thỏa thuận" />
                <span class="desc">Hiển thị nổi bật trên thẻ việc làm và đầu trang chi tiết.</span>
            </div>

            <div class="tt-job-field">
                <label for="recruitment_location">Địa điểm làm việc (Location)</label>
                <input type="text" id="recruitment_location" name="location" value="<?php echo esc_attr($location); ?>" placeholder="VD: Đà Lạt & TP. Hồ Chí Minh hoặc Đà Lạt / Hybrid" />
                <span class="desc">Khu vực hoặc thành phố làm việc chính.</span>
            </div>

            <div class="tt-job-field">
                <label for="recruitment_type">Hình thức làm việc (Employment Type)</label>
                <select id="recruitment_type" name="type">
                    <option value="Toàn thời gian" <?php selected($type, "Toàn thời gian"); ?>>Toàn thời gian</option>
                    <option value="Bán thời gian" <?php selected($type, "Bán thời gian"); ?>>Bán thời gian</option>
                    <option value="Cộng tác viên / Bán thời gian" <?php selected($type, "Cộng tác viên / Bán thời gian"); ?>>Cộng tác viên / Bán thời gian</option>
                    <option value="Làm việc từ xa (Remote)" <?php selected($type, "Làm việc từ xa (Remote)"); ?>>Làm việc từ xa (Remote)</option>
                </select>
                <span class="desc">Phân loại thời gian / chế độ công việc.</span>
            </div>

            <div class="tt-job-field">
                <label for="recruitment_experience">Kinh nghiệm yêu cầu (Experience)</label>
                <input type="text" id="recruitment_experience" name="experience" value="<?php echo esc_attr($experience); ?>" placeholder="VD: Từ 2 năm kinh nghiệm hoặc Không yêu cầu" />
                <span class="desc">Số năm kinh nghiệm hoặc yêu cầu chuyên môn tương đương.</span>
            </div>

            <div class="tt-job-field">
                <label for="recruitment_deadline">Hạn nộp hồ sơ (Deadline)</label>
                <input type="text" id="recruitment_deadline" name="deadline" value="<?php echo esc_attr($deadline); ?>" placeholder="VD: 31/10/2026" />
                <span class="desc">Ngày hết hạn nhận hồ sơ ứng tuyển.</span>
            </div>

            <div class="tt-job-field">
                <label>Vị trí nổi bật (Featured Job)</label>
                <div class="tt-job-featured-box">
                    <input type="checkbox" id="recruitment_is_featured" name="is_featured" value="1" <?php checked(!empty($is_featured)); ?> style="margin:0; width:18px; height:18px; cursor:pointer;" />
                    <label for="recruitment_is_featured" style="margin:0; font-weight:600; cursor:pointer;">
                        ⭐ Đánh dấu là vị trí Nổi Bật (Ưu tiên hiển thị viền vàng và huy hiệu)
                    </label>
                </div>
            </div>

            <div class="tt-job-field">
                <label for="recruitment_work_location">Địa chỉ nơi làm việc cụ thể</label>
                <input type="text" id="recruitment_work_location" name="work_location" value="<?php echo esc_attr($work_location); ?>" placeholder="VD: Số 61/2 An Bình, Phường Xuân Hương, TP. Đà Lạt" />
                <span class="desc">Địa chỉ chi tiết nơi ứng viên làm việc hàng ngày.</span>
            </div>


            <div class="tt-job-field">
                <label for="recruitment_work_time">Thời gian làm việc (Work Hours)</label>
                <input type="text" id="recruitment_work_time" name="work_time" value="<?php echo esc_attr($work_time); ?>" placeholder="VD: Thứ Hai đến Thứ Bảy (08:00 - 17:30)" />
                <span class="desc">Khung giờ làm việc trong tuần.</span>
            </div>
        </div>

        <div class="tt-job-section-title" style="margin-top: 10px;">
            <span>📝 Nội dung mô tả chi tiết vị trí</span>
        </div>

        <div class="tt-job-field">
            <label for="recruitment_overview">Tổng quan vai trò (Role Overview)</label>
            <textarea id="recruitment_overview" name="overview" rows="4" placeholder="Giới thiệu ý nghĩa vai trò, sứ mệnh công việc đối với khách hàng và Thiên Tâm..."><?php echo esc_textarea($overview); ?></textarea>
            <span class="desc">Đoạn văn ngắn giới thiệu tổng quan sứ mệnh công việc.</span>
        </div>

        <div class="tt-job-field">
            <label for="recruitment_responsibilities">Nhiệm vụ & Trách nhiệm chính (Responsibilities)</label>
            <textarea id="recruitment_responsibilities" name="responsibilities" rows="6" placeholder="Tiếp nhận dữ liệu, lập hồ sơ...&#10;Phân tích cấu trúc mệnh cục...&#10;Soạn thảo bản luận giải chuyên sâu...&#10;(Mỗi dòng là một đầu việc)"><?php echo esc_textarea($responsibilities); ?></textarea>
            <span class="desc"><strong>Lưu ý:</strong> Xuống dòng để tạo từng gạch đầu dòng nhiệm vụ trên website.</span>
        </div>

        <div class="tt-job-field">
            <label for="recruitment_requirements">Yêu cầu ứng viên (Requirements)</label>
            <textarea id="recruitment_requirements" name="requirements" rows="6" placeholder="Tốt nghiệp Cao đẳng/Đại học...&#10;Có tối thiểu 2 năm kinh nghiệm...&#10;Tư duy chính trực, điềm đạm...&#10;(Mỗi dòng là một yêu cầu)"><?php echo esc_textarea($requirements); ?></textarea>
            <span class="desc"><strong>Lưu ý:</strong> Xuống dòng để tạo từng gạch đầu dòng tiêu chuẩn ứng viên.</span>
        </div>

        <div class="tt-job-field">
            <label for="recruitment_benefits">Quyền lợi & Chế độ đãi ngộ (Benefits)</label>
            <textarea id="recruitment_benefits" name="benefits" rows="6" placeholder="Thu nhập xứng tầm năng lực: 20 - 35 triệu...&#10;Linh hoạt hình thức làm việc...&#10;Tiếp cận kho tàng cổ thư quý hiếm...&#10;(Mỗi dòng là một quyền lợi)"><?php echo esc_textarea($benefits); ?></textarea>
            <span class="desc"><strong>Lưu ý:</strong> Xuống dòng để tạo từng gạch đầu dòng quyền lợi dành cho nhân sự.</span>
        </div>
    </div>
    <?php
}

/**
 * 2.3 Lưu dữ liệu Native Meta Box khi lưu Post Tuyển dụng
 */
add_action("save_post_recruitment", "thientam_save_recruitment_native_meta");
function thientam_save_recruitment_native_meta($post_id)
{
    $post_id = intval($post_id);

    if (! isset($_POST["thientam_recruitment_nonce"]) || ! wp_verify_nonce($_POST["thientam_recruitment_nonce"], "thientam_save_recruitment_meta")) {
        return;
    }

    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
        return;
    }

    if (! current_user_can("edit_post", $post_id)) {
        return;
    }

    $text_fields = ["salary", "location", "type", "experience", "deadline", "work_location", "work_time"];
    foreach ($text_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }

    // Featured checkbox
    $is_featured = !empty($_POST["is_featured"]) ? 1 : 0;
    update_post_meta($post_id, "is_featured", $is_featured);

    // Textareas
    $textarea_fields = ["overview", "responsibilities", "requirements", "benefits"];
    foreach ($textarea_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_textarea_field($_POST[$field]));
        }
    }
}

/**
 * 3. Tùy chỉnh cột danh sách trong Admin
 */
add_filter("manage_recruitment_posts_columns", function ($columns) {
    $new_cols = [];
    $new_cols["cb"] = $columns["cb"];
    $new_cols["title"] = "Vị trí tuyển dụng";
    $new_cols["department"] = "Phòng ban";
    $new_cols["salary"] = "Mức lương";
    $new_cols["date"] = $columns["date"];
    return $new_cols;
});

add_action("manage_recruitment_posts_custom_column", function ($column, $post_id) {
    switch ($column) {
        case "department":
            $terms = get_the_terms($post_id, "recruitment_department");
            if (!empty($terms) && !is_wp_error($terms)) {
                $names = wp_list_pluck($terms, "name");
                echo esc_html(implode(", ", $names));
            } else {
                echo "—";
            }
            break;
        case "salary":
            echo esc_html(get_post_meta($post_id, "salary", true) ?: "—");
            break;
    }
}, 10, 2);

// Hiển thị trạng thái "⭐ Nổi bật" ngay cạnh tiêu đề mà không tốn thêm cột
add_filter("display_post_states", function ($post_states, $post) {
    if ($post->post_type === "recruitment" && get_post_meta($post->ID, "is_featured", true)) {
        $post_states["recruitment_featured"] = "⭐ Nổi bật";
    }
    return $post_states;
}, 10, 2);

/**
 * 4. Hàm Format dữ liệu Job chuẩn theo Next.js Frontend
 */
function thientam_format_recruitment_item($post)
{
    $id = $post->ID;
    $departments = get_the_terms($id, "recruitment_department");
    $dept_name = (!empty($departments) && !is_wp_error($departments)) ? $departments[0]->name : "Chung";
    $dept_slug = (!empty($departments) && !is_wp_error($departments)) ? $departments[0]->slug : "all";

    $clean_str = function ($val) {
        if (!$val) return "";
        return html_entity_decode(strip_tags((string) $val), ENT_QUOTES, "UTF-8");
    };

    $parse_lines = function ($val) use ($clean_str) {
        if (is_array($val)) {
            $cleaned = array_map($clean_str, $val);
            return array_values(array_filter($cleaned));
        }
        if (empty($val)) {
            return [];
        }
        $clean = str_replace("\r", "", (string) $val);
        $lines = explode("\n", $clean);
        $trimmed = array_map("trim", $lines);
        $filtered = array_filter($trimmed);
        return array_values(array_map($clean_str, $filtered));
    };

    return [
        "id"               => $id,
        "slug"             => $post->post_name,
        "title"            => $clean_str(get_the_title($post)),
        "department"       => $clean_str(get_post_meta($id, "department", true) ?: $dept_name),
        "departmentSlug"   => $dept_slug,
        "location"         => $clean_str(get_post_meta($id, "location", true) ?: "Đà Lạt"),
        "type"             => $clean_str(get_post_meta($id, "type", true) ?: "Toàn thời gian"),
        "salary"           => $clean_str(get_post_meta($id, "salary", true) ?: "Thỏa thuận theo năng lực"),
        "experience"       => $clean_str(get_post_meta($id, "experience", true) ?: "Không yêu cầu"),
        "deadline"         => $clean_str(get_post_meta($id, "deadline", true) ?: ""),
        "isFeatured"       => (bool) get_post_meta($id, "is_featured", true),
        "createdAt"        => get_the_date("d/m/Y", $post),
        "desc"             => $clean_str(get_the_excerpt($post) ?: wp_trim_words(strip_tags($post->post_content), 30)),
        "image"            => get_the_post_thumbnail_url($id, "full") ?: "https://site.thientam68.com/wp-content/uploads/2026/08/Placeholder-Thien-Tam.png",
        "og_image"         => get_post_meta($id, "seo_og_image", true) ?: (get_the_post_thumbnail_url($id, "full") ?: "https://site.thientam68.com/wp-content/uploads/2026/08/Placeholder-Thien-Tam.png"),
        "overview"         => $clean_str(get_post_meta($id, "overview", true) ?: $post->post_content),
        "responsibilities" => $parse_lines(get_post_meta($id, "responsibilities", true)),
        "requirements"     => $parse_lines(get_post_meta($id, "requirements", true)),
        "benefits"         => $parse_lines(get_post_meta($id, "benefits", true)),
        "workLocation"     => $clean_str(get_post_meta($id, "work_location", true) ?: ""),
        "workTime"         => $clean_str(get_post_meta($id, "work_time", true) ?: ""),
    ];
}

/**
 * 5. Đăng ký REST API Endpoints: /wp-json/thientam/v1/recruitment
 */
add_action("rest_api_init", function () {
    // Danh sách phòng ban (phải đăng ký TRƯỚC wildcard slug để tránh conflict)
    register_rest_route("thientam/v1", "/recruitment/departments", [
        "methods"             => "GET",
        "callback"            => "thientam_api_get_recruitment_departments",
        "permission_callback" => "__return_true",
    ]);

    // Danh sách tuyển dụng (có phân trang, search, department, sort)
    register_rest_route("thientam/v1", "/recruitment", [
        "methods"             => "GET",
        "callback"            => "thientam_api_get_recruitment_list",
        "permission_callback" => "__return_true",
    ]);

    // Chi tiết 1 vị trí theo slug (phải sau /departments)
    register_rest_route("thientam/v1", "/recruitment/(?P<slug>[a-zA-Z0-9-_]+)", [
        "methods"             => "GET",
        "callback"            => "thientam_api_get_recruitment_detail",
        "permission_callback" => "__return_true",
    ]);
});

function thientam_api_get_recruitment_list($request)
{
    $paged = $request->get_param("page") ? intval($request->get_param("page")) : 1;
    $per_page = $request->get_param("per_page") ? intval($request->get_param("per_page")) : 9;
    $search = $request->get_param("search") ?: "";
    $dept = $request->get_param("department") ?: "";
    $type = $request->get_param("type") ?: "";
    $sort = $request->get_param("sort") ?: "latest";

    $args = [
        "post_type"      => "recruitment",
        "post_status"    => "publish",
        "posts_per_page" => $per_page,
        "paged"          => $paged,
        "orderby"        => "date",
        "order"          => $sort === "oldest" ? "ASC" : "DESC",
    ];

    if (!empty($search)) {
        $args["s"] = sanitize_text_field($search);
    }

    if (!empty($dept) && $dept !== "all") {
        $args["tax_query"] = [
            [
                "taxonomy" => "recruitment_department",
                "field"    => "slug",
                "terms"    => sanitize_text_field($dept),
            ],
        ];
    }

    if (!empty($type) && $type !== "all") {
        $args["meta_query"][] = [
            "key"     => "type",
            "value"   => sanitize_text_field($type),
            "compare" => "LIKE",
        ];
    }

    $query = new WP_Query($args);
    $data = [];

    foreach ($query->posts as $post) {
        $data[] = thientam_format_recruitment_item($post);
    }

    return new WP_REST_Response([
        "success"     => true,
        "count"       => count($data),
        "total"       => intval($query->found_posts),
        "total_pages" => intval($query->max_num_pages),
        "data"        => $data,
    ], 200);
}

function thientam_api_get_recruitment_detail($request)
{
    $slug = sanitize_title($request["slug"]);
    $posts = get_posts([
        "post_type"      => "recruitment",
        "name"           => $slug,
        "post_status"    => "publish",
        "posts_per_page" => 1,
    ]);

    if (empty($posts)) {
        return new WP_REST_Response([
            "success" => false,
            "message" => "Không tìm thấy vị trí tuyển dụng",
        ], 404);
    }

    return new WP_REST_Response([
        "success" => true,
        "data"    => thientam_format_recruitment_item($posts[0]),
    ], 200);
}

function thientam_api_get_recruitment_departments()
{
    $terms = get_terms([
        "taxonomy"   => "recruitment_department",
        "hide_empty" => false,
    ]);

    $total_publish = intval(wp_count_posts("recruitment")->publish ?? 0);
    $data = [
        ["id" => "all", "name" => "Tất cả vị trí", "slug" => "all", "count" => $total_publish],
    ];

    if (!is_wp_error($terms)) {
        foreach ($terms as $t) {
            $data[] = [
                "id"    => $t->term_id,
                "name"  => html_entity_decode($t->name, ENT_QUOTES, "UTF-8"),
                "slug"  => $t->slug,
                "count" => intval($t->count),
            ];
        }
    }

    return new WP_REST_Response(["success" => true, "data" => $data], 200);
}
