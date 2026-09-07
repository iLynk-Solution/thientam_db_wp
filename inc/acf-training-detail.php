<?php

/**
 * Register ACF Field Groups for Training Detail Page with separate boxes
 */

if (! defined("ABSPATH")) {
    exit;
}

add_action("acf/init", function () {
    if (! function_exists("acf_add_local_field_group")) {
        return;
    }

    $location = array(
        array(
            array(
                "param"    => "page_template",
                "operator" => "==",
                "value"    => "template-training-detail.php",
            ),
        ),
    );

    // BOX 1: THÔNG TIN CHUNG (General Info Fields)
    acf_add_local_field_group(array(
        "key"                   => "group_training_general_info",
        "title"                 => "Thông tin chung Gói Đào Tạo (General Info)",
        "fields"                => array(
            array(
                "key"          => "field_training_subtitle",
                "label"        => "Tiêu đề phụ (Subtitle)",
                "name"         => "training_subtitle",
                "type"         => "text",
                "instructions" => "VD: Giáo trình chuẩn hóa Cổ học phương Đông & Tử Vi thực chiến",
                "placeholder"  => "Nhập tiêu đề phụ...",
            ),
            
            array(
                "key"          => "field_training_duration",
                "label"        => "Thời lượng / Số chuyên đề (Duration)",
                "name"         => "training_duration",
                "type"         => "text",
                "instructions" => "VD: 10 chuyên đề cốt lõi, 9 chuyên đề chuyên sâu...",
                "placeholder"  => "10 chuyên đề cốt lõi",
            ),
            
            array(
                "key"          => "field_training_target_audience",
                "label"        => "Đối tượng phù hợp tổng quát (Target Audience Summary)",
                "name"         => "training_target_audience",
                "type"         => "textarea",
                "rows"         => 3,
                "instructions" => "Tóm tắt đối tượng phù hợp nhất với khóa học này.",
            ),
        ),
        "location"              => $location,
        "menu_order"            => 1,
        "position"              => "normal",
        "style"                 => "default",
        "label_placement"       => "top",
        "instruction_placement" => "label",
        "active"                => true,
    ));

    // BOX 2: CHỈ SỐ NỔI BẬT (Key Metrics Bar)
    acf_add_local_field_group(array(
        "key"                   => "group_training_metrics",
        "title"                 => "Chỉ số nổi bật (Key Metrics Bar)",
        "fields"                => array(
            array(
                "key"          => "field_training_metrics",
                "label"        => "Danh sách chỉ số",
                "name"         => "training_metrics",
                "type"         => "repeater",
                "instructions" => "Thêm 4 chỉ số thống kê (Số chuyên đề, % Thực hành, Hình thức học, Chế độ hỗ trợ)",
                "layout"       => "table",
                "button_label" => "Thêm Chỉ Số",
                "sub_fields"   => array(
                    array(
                        "key"         => "field_metric_value",
                        "label"       => "Giá trị (Value)",
                        "name"        => "value",
                        "type"        => "text",
                        "placeholder" => "VD: 10 Chuyên đề, 60%+ Thực hành",
                    ),
                    array(
                        "key"         => "field_metric_desc",
                        "label"       => "Mô tả ngắn (Description)",
                        "name"        => "desc",
                        "type"        => "text",
                        "placeholder" => "VD: Lộ trình bài bản từ gốc, Luận giải lá số thực tế",
                    ),
                ),
            ),
        ),
        "location"              => $location,
        "menu_order"            => 2,
        "position"              => "normal",
        "style"                 => "default",
        "label_placement"       => "top",
        "instruction_placement" => "label",
        "active"                => true,
    ));

    // BOX 3: ĐỐI TƯỢNG CHI TIẾT (Audience Items)
    acf_add_local_field_group(array(
        "key"                   => "group_training_audience",
        "title"                 => "Đối tượng phù hợp chi tiết (Audience Items)",
        "fields"                => array(
            array(
                "key"          => "field_training_audience_items",
                "label"        => "Danh sách đối tượng chi tiết",
                "name"         => "training_audience_items",
                "type"         => "repeater",
                "instructions" => "Các gạch đầu dòng liệt kê đối tượng phù hợp khóa học",
                "layout"       => "table",
                "button_label" => "Thêm Đối Tượng",
                "sub_fields"   => array(
                    array(
                        "key"         => "field_audience_item_text",
                        "label"       => "Nội dung đối tượng",
                        "name"        => "item",
                        "type"        => "text",
                        "placeholder" => "VD: Người muốn hiểu sâu về mệnh bàn cá nhân, gia đình và con cái",
                    ),
                ),
            ),
        ),
        "location"              => $location,
        "menu_order"            => 3,
        "position"              => "normal",
        "style"                 => "default",
        "label_placement"       => "top",
        "instruction_placement" => "label",
        "active"                => true,
    ));

    // BOX 4: HÌNH THỨC & QUYỀN LỢI HỌC VIÊN (Benefit Items)
    acf_add_local_field_group(array(
        "key"                   => "group_training_benefits",
        "title"                 => "Hình thức & Quyền lợi học viên (Benefit Items)",
        "fields"                => array(
            array(
                "key"          => "field_training_benefit_items",
                "label"        => "Danh sách quyền lợi",
                "name"         => "training_benefit_items",
                "type"         => "repeater",
                "instructions" => "Các quyền lợi và hình thức học dành cho học viên",
                "layout"       => "table",
                "button_label" => "Thêm Quyền Lợi",
                "sub_fields"   => array(
                    array(
                        "key"         => "field_benefit_item_highlight",
                        "label"       => "Tiêu đề in đậm (Highlight)",
                        "name"        => "highlight",
                        "type"        => "text",
                        "placeholder" => "VD: Hình thức linh hoạt:, Giáo trình độc quyền:",
                    ),
                    array(
                        "key"         => "field_benefit_item_text",
                        "label"       => "Nội dung chi tiết (Text)",
                        "name"        => "text",
                        "type"        => "text",
                        "placeholder" => "VD: Học trực tiếp hoặc online qua Zoom có ghi hình...",
                    ),
                ),
            ),
        ),
        "location"              => $location,
        "menu_order"            => 4,
        "position"              => "normal",
        "style"                 => "default",
        "label_placement"       => "top",
        "instruction_placement" => "label",
        "active"                => true,
    ));

    // BOX 5: NỘI DUNG CHUYÊN ĐỀ (Lessons)
    acf_add_local_field_group(array(
        "key"                   => "group_training_lessons",
        "title"                 => "Danh sách Chuyên đề / Bài giảng (Lessons)",
        "fields"                => array(
            array(
                "key"          => "field_training_lessons",
                "label"        => "Danh sách các chuyên đề / bài giảng",
                "name"         => "training_lessons",
                "type"         => "repeater",
                "instructions" => "Nhập danh sách bài học theo thứ tự chương trình giảng dạy",
                "layout"       => "table",
                "button_label" => "Thêm Chuyên Đề",
                "sub_fields"   => array(
                    array(
                        "key"         => "field_lesson_title",
                        "label"       => "Tên chuyên đề / bài học",
                        "name"        => "lesson_title",
                        "type"        => "text",
                        "placeholder" => "VD: Giới thiệu nhập môn, Ý nghĩa 14 sao chính tinh...",
                    ),
                ),
            ),
        ),
        "location"              => $location,
        "menu_order"            => 5,
        "position"              => "normal",
        "style"                 => "default",
        "label_placement"       => "top",
        "instruction_placement" => "label",
        "active"                => true,
    ));
});
