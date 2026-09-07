<?php

/**
 * Register ACF Field Group for Service Detail Page in Child Theme (Streamlined)
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('acf/init', function () {
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group(array(
            'key' => 'group_service_detail_settings',
            'title' => 'Cài đặt Chi tiết Dịch vụ (Service Detail)',
            'fields' => array(
                // TAB 1: HERO
                array('key' => 'field_tab_hero', 'label' => '01. Hero Banner', 'name' => '', 'type' => 'tab', 'placement' => 'top'),
                array('key' => 'field_service_hero_eyebrow', 'label' => 'Dòng tiêu đề phụ trên (Eyebrow)', 'name' => 'service_hero_eyebrow', 'type' => 'text', 'default_value' => 'DỊCH VỤ TƯ VẤN CÁ NHÂN'),
                array('key' => 'field_service_hero_title', 'label' => 'Tiêu đề chính (Title)', 'name' => 'service_hero_title', 'type' => 'text', 'default_value' => 'Sự vật • Sự việc'),
                array('key' => 'field_service_hero_subtitle', 'label' => 'Tiêu đề phụ dưới (Subtitle)', 'name' => 'service_hero_subtitle', 'type' => 'text', 'default_value' => 'Gỡ rối một vấn đề cụ thể'),
                array(
                    'key' => 'field_service_icon',
                    'label' => 'Biểu tượng dịch vụ (Icon)',
                    'name' => 'service_icon',
                    'type' => 'select',
                    'instructions' => 'Chọn icon đại diện cho dịch vụ này hiển thị trên các thẻ (Card) trang chủ và danh sách dịch vụ.',
                    'choices' => array(
                        'compass'     => 'Compass (La bàn / Sự vật sự việc)',
                        'smartphone'  => 'Smartphone (Điện thoại / Phong thủy số)',
                        'home'        => 'Home (Nhà ở / Không gian sống & doanh nghiệp)',
                        'sparkles'    => 'Sparkles (Tử vi / Bát tự / Kỳ môn mệnh)',
                        'users'       => 'Users (Gia đạo / Gia đình & Hòa hợp)',
                        'trending-up' => 'Trending Up (Doanh nghiệp / Tăng trưởng)',
                        'briefcase'   => 'Briefcase (Hồ sơ / Doanh nghiệp)',
                    ),
                    'default_value' => 'compass',
                    'allow_null'    => 0,
                    'ui'            => 1,
                ),
                array('key' => 'field_service_hero_description', 'label' => 'Mô tả ngắn Hero', 'name' => 'service_hero_description', 'type' => 'textarea', 'rows' => 3, 'default_value' => 'Thêm một góc nhìn khách quan để xem xét, định hướng và lựa chọn phương án phù hợp.'),

                // TAB 2: OVERVIEW
                array('key' => 'field_tab_overview', 'label' => '02. Tổng Quan (Overview)', 'name' => '', 'type' => 'tab', 'placement' => 'top'),
                array('key' => 'field_overview_title_prefix', 'label' => 'Tiêu đề đầu', 'name' => 'overview_title_prefix', 'type' => 'text', 'default_value' => 'Khi bạn cần thêm'),
                array('key' => 'field_overview_title_highlight', 'label' => 'Tiêu đề nổi bật', 'name' => 'overview_title_highlight', 'type' => 'text', 'default_value' => 'một góc nhìn'),
                array('key' => 'field_overview_desc', 'label' => 'Mô tả phần Tổng quan', 'name' => 'overview_desc', 'type' => 'textarea', 'rows' => 3),
                array(
                    'key' => 'field_overview_cards',
                    'label' => 'Các thẻ điểm nổi bật',
                    'name' => 'overview_cards',
                    'type' => 'repeater',
                    'layout' => 'block',
                    'button_label' => 'Thêm Thẻ Tổng Quan',
                    'sub_fields' => array(
                        array('key' => 'field_card_icon', 'label' => 'Icon', 'name' => 'card_icon', 'type' => 'select', 'choices' => array(
                            'briefcase' => 'Briefcase (Công việc)',
                            'calendar' => 'Calendar (Lịch ngày)',
                            'search' => 'Search (Tìm kiếm)',
                            'sparkles' => 'Sparkles (Bát tự)',
                            'home' => 'Home (Nhà cửa)',
                            'users' => 'Users (Gia đình)',
                        ), 'default_value' => 'briefcase'),
                        array('key' => 'field_card_title', 'label' => 'Tiêu đề thẻ', 'name' => 'card_title', 'type' => 'text'),
                        array('key' => 'field_card_desc', 'label' => 'Mô tả thẻ', 'name' => 'card_desc', 'type' => 'textarea', 'rows' => 2),
                    ),
                ),

                // TAB 3: PRICING
                array('key' => 'field_tab_pricing', 'label' => '03. Bảng Giá (Pricing)', 'name' => '', 'type' => 'tab', 'placement' => 'top'),
                array(
                    'key' => 'field_pricing_items',
                    'label' => 'Các gói trong bảng giá',
                    'name' => 'pricing_items',
                    'type' => 'repeater',
                    'layout' => 'table',
                    'button_label' => 'Thêm Gói Bảng Giá',
                    'sub_fields' => array(
                        array('key' => 'field_pricing_item_service', 'label' => 'Hạng mục tư vấn', 'name' => 'service', 'type' => 'text'),
                        array('key' => 'field_pricing_item_content', 'label' => 'Nội dung', 'name' => 'content', 'type' => 'text'),
                        array('key' => 'field_pricing_item_price', 'label' => 'Phí tư vấn', 'name' => 'price', 'type' => 'text', 'placeholder' => '1.500.000đ'),
                    ),
                ),
                array('key' => 'field_pricing_note', 'label' => 'Ghi chú chân bảng giá', 'name' => 'pricing_note', 'type' => 'text', 'default_value' => 'Nội dung tư vấn mang tính tham khảo và định hướng.'),

                // TAB 4: BENEFITS
                array('key' => 'field_tab_benefits', 'label' => '04. Quyền Lợi (Benefits)', 'name' => '', 'type' => 'tab', 'placement' => 'top'),
                array('key' => 'field_benefits_title_prefix', 'label' => 'Tiêu đề đầu quyền lợi', 'name' => 'benefits_title_prefix', 'type' => 'text', 'default_value' => 'Quyền lợi'),
                array('key' => 'field_benefits_title_highlight', 'label' => 'Tiêu đề nổi bật quyền lợi', 'name' => 'benefits_title_highlight', 'type' => 'text', 'default_value' => 'đồng hành'),
                array('key' => 'field_benefits_desc', 'label' => 'Mô tả phụ quyền lợi', 'name' => 'benefits_desc', 'type' => 'textarea', 'rows' => 2),
                array('key' => 'field_benefits_content', 'label' => 'Nội dung quyền lợi chi tiết', 'name' => 'benefits_content', 'type' => 'textarea', 'rows' => 3),

                // TAB 5: SEO
                array('key' => 'field_tab_seo', 'label' => '05. Cấu hình SEO', 'name' => '', 'type' => 'tab', 'placement' => 'top'),
                array('key' => 'field_seo_meta_title', 'label' => 'Tiêu đề SEO tùy biến (Meta Title)', 'name' => 'seo_meta_title', 'type' => 'text', 'placeholder' => 'Để trống sẽ tự động lấy Tiêu đề trang...'),
                array('key' => 'field_seo_meta_description', 'label' => 'Mô tả SEO tùy biến (Meta Description)', 'name' => 'seo_meta_description', 'type' => 'textarea', 'rows' => 3, 'placeholder' => 'Để trống sẽ tự động lấy Mô tả ngắn Hero...'),
                array('key' => 'field_seo_meta_keywords', 'label' => 'Từ khóa SEO (Meta Keywords)', 'name' => 'seo_meta_keywords', 'type' => 'text', 'placeholder' => 'phong thủy nhà ở, tư vấn tử vi, chuyên gia phong thủy...'),
                array('key' => 'field_seo_og_image', 'label' => 'Ảnh chia sẻ Mạng Xã Hội (OG Image URL)', 'name' => 'seo_og_image', 'type' => 'text', 'placeholder' => 'https://... (Để trống sẽ tự động lấy Ảnh đại diện / Featured Image)'),
            ),
            'location' => array(
                array(
                    array('param' => 'page_template', 'operator' => '==', 'value' => 'template-service-detail.php'),
                ),
            ),
        ));
    }
});
