<?php
/**
 * Shared Comparison Metabox Component (So sánh nhanh)
 * Scope: $post, $defaults, $get_val, $get_json_val (provided by parent metabox.php)
 * Optional parameters:
 *   $tab_comp_id       (string) Tab pane ID (default 'tab-comparison')
 *   $tab_comp_active   (bool)   Is active pane (default false)
 *   $tab_comp_wrap     (bool)   Wrap inside <div class="tt-tab-pane"> (default true)
 *   $tab_comp_heading  (string) Section heading title (optional)
 *
 * @package ThienTamData
 */

if (! defined('ABSPATH')) {
    exit;
}

// Fallback helpers nếu chưa được định nghĩa bởi parent metabox
if (! isset($get_val) || ! is_callable($get_val)) {
    $get_val = function ($meta_key, $default_val = '') use ($post) {
        if (metadata_exists('post', $post->ID, $meta_key)) {
            return get_post_meta($post->ID, $meta_key, true);
        }
        return $default_val;
    };
}

$tab_comp_id      = isset($tab_comp_id) && $tab_comp_id !== '' ? $tab_comp_id : 'tab-comparison';
$tab_comp_active  = ! empty($tab_comp_active);
$tab_comp_wrap    = ! isset($tab_comp_wrap) || $tab_comp_wrap !== false;
$tab_comp_heading = isset($tab_comp_heading) ? $tab_comp_heading : '';

// 1. Header fields
$comp_title_prefix    = $get_val('landing_comparison_title_prefix', $defaults['comparison']['titlePrefix'] ?? '');
$comp_title_highlight = $get_val('landing_comparison_title_highlight', $defaults['comparison']['titleHighlight'] ?? '');
$comp_desc            = $get_val('landing_comparison_desc', $defaults['comparison']['desc'] ?? ($defaults['comparison']['description'] ?? ''));

// 2. Column headers (4 gói)
$comp_col0_name  = $get_val('landing_comparison_col0_name', $defaults['comparison']['columns'][0]['name'] ?? '');
$comp_col0_price = $get_val('landing_comparison_col0_price', $defaults['comparison']['columns'][0]['price'] ?? '');
$comp_col1_name  = $get_val('landing_comparison_col1_name', $defaults['comparison']['columns'][1]['name'] ?? '');
$comp_col1_price = $get_val('landing_comparison_col1_price', $defaults['comparison']['columns'][1]['price'] ?? '');
$comp_col2_name  = $get_val('landing_comparison_col2_name', $defaults['comparison']['columns'][2]['name'] ?? '');
$comp_col2_price = $get_val('landing_comparison_col2_price', $defaults['comparison']['columns'][2]['price'] ?? '');
$comp_col3_name  = $get_val('landing_comparison_col3_name', $defaults['comparison']['columns'][3]['name'] ?? '');
$comp_col3_price = $get_val('landing_comparison_col3_price', $defaults['comparison']['columns'][3]['price'] ?? '');

// 3. Comparison rows
$comp_rows_meta = get_post_meta($post->ID, 'landing_comparison_rows', true);
$comp_rows = array();
if (! empty($comp_rows_meta)) {
    $decoded = is_array($comp_rows_meta) ? $comp_rows_meta : json_decode($comp_rows_meta, true);
    if (is_array($decoded)) {
        $comp_rows = $decoded;
    }
}
if (empty($comp_rows)) {
    if (! empty($defaults['comparison']['rows']) && is_array($defaults['comparison']['rows'])) {
        $comp_rows = $defaults['comparison']['rows'];
    } else {
        $comp_rows = array();
    }
}

// 4. Bottom Banner fields
$comp_note_title = $get_val('landing_comparison_note_title', $defaults['comparison']['noteTitle'] ?? '');
$comp_note_desc  = $get_val('landing_comparison_note_desc', $defaults['comparison']['noteDesc'] ?? '');
$comp_btn_text   = $get_val('landing_comparison_btn_text', $defaults['comparison']['btnText'] ?? '');
$comp_btn_href   = $get_val('landing_comparison_btn_href', $defaults['comparison']['btnHref'] ?? ($defaults['comparison']['anchor'] ?? ''));

function thientam_format_comp_display_val($val) {
    if ($val === true || $val === '1' || $val === 'true' || $val === 'v' || $val === '✓') {
        return '✓';
    }
    if ($val === false || $val === '0' || $val === 'false' || $val === '-' || $val === '—' || $val === '') {
        return '—';
    }
    return $val;
}
?>

<?php if ($tab_comp_wrap) : ?>
    <div id="<?php echo esc_attr($tab_comp_id); ?>" class="tt-tab-pane<?php echo $tab_comp_active ? ' active' : ''; ?>">
<?php endif; ?>

    <?php if (! empty($tab_comp_heading)) : ?>
        <h3 style="margin-top:0; color:#0f3d61;"><?php echo esc_html($tab_comp_heading); ?></h3>
    <?php endif; ?>

    <!-- 1. HEADER SECTION -->
    <div class="tt-card" style="margin-bottom:20px;">
        <div class="tt-card-header">1. Tiêu Đề & Mô Tả Section So Sánh</div>
        <div class="tt-field-grid">
            <div class="tt-field-row">
                <label>Tiêu đề đầu (Tiền tố)</label>
                <input type="text" name="landing_comparison_title_prefix" class="widefat" value="<?php echo esc_attr($comp_title_prefix); ?>" placeholder="vd: Khác biệt giữa 4 gói nằm ở độ sâu, " />
            </div>
            <div class="tt-field-row">
                <label>Tiêu đề nổi bật (In nghiêng nổi bật)</label>
                <input type="text" name="landing_comparison_title_highlight" class="widefat" value="<?php echo esc_attr($comp_title_highlight); ?>" placeholder="vd: tư vấn và sự đồng hành" />
            </div>
        </div>
        <div class="tt-field-row">
            <label>Ghi chú phụ / Lời dẫn</label>
            <textarea name="landing_comparison_desc" class="widefat" rows="2" placeholder="vd: Trên điện thoại, bạn có thể vuốt ngang bảng để xem đầy đủ."><?php echo esc_textarea($comp_desc); ?></textarea>
        </div>
    </div>

    <!-- 2. TABLE COLUMNS & ROWS -->
    <div class="tt-card" style="margin-bottom:20px;">
        <div class="tt-card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <span>2. Bảng Tiêu Chí So Sánh (Thêm/xóa dòng tùy ý)</span>
            <button type="button" class="button button-primary tt-btn-add-comp-row" style="background:#0f3d61; border-color:#0f3d61;">+ Thêm tiêu chí</button>
        </div>

        <p class="description" style="margin:8px 0 14px;">
            💡 <strong>Mẹo nhập:</strong> Nhập <code>1</code> hoặc <code>✓</code> để hiện tích xanh, nhập <code>0</code> hoặc <code>—</code> để hiện gạch ngang, hoặc nhập text tùy ý (vd: <code>~12 trang</code>, <code>Có</code>, <code>Chuyên sâu hơn</code>).
        </p>

        <!-- Column headers config -->
        <div style="background:#f1f5f9; padding:12px; border-radius:8px; margin-bottom:14px; border:1px solid #e2e8f0;">
            <strong style="color:#0f3d61; display:block; margin-bottom:8px; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Cấu hình 4 cột gói dịch vụ:</strong>
            <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:10px;">
                <div>
                    <label style="font-size:11px; font-weight:600;">Cột 1: Tên & Giá</label>
                    <input type="text" name="landing_comparison_col0_name" value="<?php echo esc_attr($comp_col0_name); ?>" class="widefat" style="margin-bottom:4px;" />
                    <input type="text" name="landing_comparison_col0_price" value="<?php echo esc_attr($comp_col0_price); ?>" class="widefat" placeholder="Giá" />
                </div>
                <div>
                    <label style="font-size:11px; font-weight:600;">Cột 2: Tên & Giá</label>
                    <input type="text" name="landing_comparison_col1_name" value="<?php echo esc_attr($comp_col1_name); ?>" class="widefat" style="margin-bottom:4px;" />
                    <input type="text" name="landing_comparison_col1_price" value="<?php echo esc_attr($comp_col1_price); ?>" class="widefat" placeholder="Giá" />
                </div>
                <div>
                    <label style="font-size:11px; font-weight:600;">Cột 3: Tên & Giá</label>
                    <input type="text" name="landing_comparison_col2_name" value="<?php echo esc_attr($comp_col2_name); ?>" class="widefat" style="margin-bottom:4px;" />
                    <input type="text" name="landing_comparison_col2_price" value="<?php echo esc_attr($comp_col2_price); ?>" class="widefat" placeholder="Giá" />
                </div>
                <div>
                    <label style="font-size:11px; font-weight:600;">Cột 4: Tên & Giá</label>
                    <input type="text" name="landing_comparison_col3_name" value="<?php echo esc_attr($comp_col3_name); ?>" class="widefat" style="margin-bottom:4px;" />
                    <input type="text" name="landing_comparison_col3_price" value="<?php echo esc_attr($comp_col3_price); ?>" class="widefat" placeholder="Giá" />
                </div>
            </div>
        </div>

        <!-- Rows Table -->
        <div style="overflow-x:auto;">
            <table class="widefat striped" style="border-collapse:collapse; min-width:680px;">
                <thead>
                    <tr style="background:#0f3d61; color:#fff;">
                        <th style="width:40px; text-align:center; color:#fff;">#</th>
                        <th style="width:30%; color:#fff;">Tên Tiêu Chí</th>
                        <th id="tt-comp-th-col0" style="width:15%; text-align:center; color:#fff;"><?php echo esc_html($comp_col0_name !== '' ? $comp_col0_name : 'Cột 1'); ?></th>
                        <th id="tt-comp-th-col1" style="width:15%; text-align:center; color:#fff;"><?php echo esc_html($comp_col1_name !== '' ? $comp_col1_name : 'Cột 2'); ?></th>
                        <th id="tt-comp-th-col2" style="width:15%; text-align:center; color:#fff;"><?php echo esc_html($comp_col2_name !== '' ? $comp_col2_name : 'Cột 3'); ?></th>
                        <th id="tt-comp-th-col3" style="width:15%; text-align:center; color:#fff;"><?php echo esc_html($comp_col3_name !== '' ? $comp_col3_name : 'Cột 4'); ?></th>
                        <th style="width:50px; text-align:center; color:#fff;">Xóa</th>
                    </tr>
                </thead>
                <tbody id="tt-comp-rows-tbody">
                    <?php foreach ($comp_rows as $idx => $r) : 
                        $feature = $r['feature'] ?? '';
                        $vals = $r['values'] ?? array('', '', '', '');
                        $v0 = thientam_format_comp_display_val($vals[0] ?? '');
                        $v1 = thientam_format_comp_display_val($vals[1] ?? '');
                        $v2 = thientam_format_comp_display_val($vals[2] ?? '');
                        $v3 = thientam_format_comp_display_val($vals[3] ?? '');
                    ?>
                        <tr class="tt-comp-row">
                            <td class="tt-comp-num" style="text-align:center; font-weight:600; color:#64748b; vertical-align:middle;"><?php echo $idx + 1; ?></td>
                            <td>
                                <input type="text" name="landing_comparison_rows[<?php echo $idx; ?>][feature]" value="<?php echo esc_attr($feature); ?>" class="widefat" placeholder="vd: Tử Vi, Độ dài bản luận..." />
                            </td>
                            <td>
                                <input type="text" name="landing_comparison_rows[<?php echo $idx; ?>][val_0]" value="<?php echo esc_attr($v0); ?>" class="widefat" style="text-align:center;" />
                            </td>
                            <td>
                                <input type="text" name="landing_comparison_rows[<?php echo $idx; ?>][val_1]" value="<?php echo esc_attr($v1); ?>" class="widefat" style="text-align:center;" />
                            </td>
                            <td>
                                <input type="text" name="landing_comparison_rows[<?php echo $idx; ?>][val_2]" value="<?php echo esc_attr($v2); ?>" class="widefat" style="text-align:center;" />
                            </td>
                            <td>
                                <input type="text" name="landing_comparison_rows[<?php echo $idx; ?>][val_3]" value="<?php echo esc_attr($v3); ?>" class="widefat" style="text-align:center;" />
                            </td>
                            <td style="text-align:center; vertical-align:middle;">
                                <button type="button" class="button-link-delete tt-btn-remove-comp-row" title="Xóa dòng này" style="color:#ef4444; font-size:16px; font-weight:bold; cursor:pointer;">✕</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center;">
            <button type="button" class="button button-secondary tt-btn-add-comp-row">+ Thêm tiêu chí mới</button>
            <span style="font-size:12px; color:#64748b;">Số tiêu chí hiện tại: <strong id="tt-comp-count"><?php echo count($comp_rows); ?></strong></span>
        </div>
    </div>

    <!-- 3. BOTTOM CALLOUT BANNER -->
    <div class="tt-card">
        <div class="tt-card-header">3. Khối Banner Kêu Gọi Hành Động Chân Bảng</div>
        <div class="tt-field-row">
            <label>Tiêu đề ghi chú</label>
            <input type="text" name="landing_comparison_note_title" class="widefat" value="<?php echo esc_attr($comp_note_title); ?>" placeholder="vd: Nhìn bảng vẫn chưa chắc?" />
        </div>
        <div class="tt-field-row">
            <label>Nội dung tóm tắt phân biệt nhanh</label>
            <textarea name="landing_comparison_note_desc" class="widefat" rows="2" placeholder="vd: Điểm phân biệt nhanh nhất: 129K = tổng quan..."><?php echo esc_textarea($comp_note_desc); ?></textarea>
        </div>
        <div class="tt-field-grid">
            <div class="tt-field-row">
                <label>Text nút hành động</label>
                <input type="text" name="landing_comparison_btn_text" class="widefat" value="<?php echo esc_attr($comp_btn_text); ?>" placeholder="vd: Chọn gói và để lại thông tin" />
            </div>
            <div class="tt-field-row">
                <label>Anchor liên kết nút (Target ID / URL)</label>
                <input type="text" name="landing_comparison_btn_href" class="widefat" value="<?php echo esc_attr($comp_btn_href); ?>" placeholder="#goi-dich-vu hoặc #dang-ky" />
            </div>
        </div>
    </div>

    <script>
    (function($) {
        $(function() {
            // Thêm dòng so sánh
            $(document).on('click', '.tt-btn-add-comp-row', function(e) {
                e.preventDefault();
                var $tbody = $('#tt-comp-rows-tbody');
                var idx = $tbody.find('.tt-comp-row').length;
                var html = '<tr class="tt-comp-row">' +
                    '<td class="tt-comp-num" style="text-align:center; font-weight:600; color:#64748b; vertical-align:middle;">' + (idx + 1) + '</td>' +
                    '<td><input type="text" name="landing_comparison_rows[' + idx + '][feature]" class="widefat" placeholder="Tên tiêu chí..." /></td>' +
                    '<td><input type="text" name="landing_comparison_rows[' + idx + '][val_0]" class="widefat" style="text-align:center;" value="✓" /></td>' +
                    '<td><input type="text" name="landing_comparison_rows[' + idx + '][val_1]" class="widefat" style="text-align:center;" value="✓" /></td>' +
                    '<td><input type="text" name="landing_comparison_rows[' + idx + '][val_2]" class="widefat" style="text-align:center;" value="✓" /></td>' +
                    '<td><input type="text" name="landing_comparison_rows[' + idx + '][val_3]" class="widefat" style="text-align:center;" value="✓" /></td>' +
                    '<td style="text-align:center; vertical-align:middle;"><button type="button" class="button-link-delete tt-btn-remove-comp-row" title="Xóa dòng này" style="color:#ef4444; font-size:16px; font-weight:bold; cursor:pointer;">✕</button></td>' +
                '</tr>';
                $tbody.append(html);
                $('#tt-comp-count').text($tbody.find('.tt-comp-row').length);
            });

            // Xóa dòng so sánh
            $(document).on('click', '.tt-btn-remove-comp-row', function(e) {
                e.preventDefault();
                var $tbody = $('#tt-comp-rows-tbody');
                $(this).closest('.tt-comp-row').remove();
                $tbody.find('.tt-comp-row').each(function(i) {
                    $(this).find('.tt-comp-num').text(i + 1);
                    $(this).find('input[name*="[feature]"]').attr('name', 'landing_comparison_rows[' + i + '][feature]');
                    $(this).find('input[name*="[val_0]"]').attr('name', 'landing_comparison_rows[' + i + '][val_0]');
                    $(this).find('input[name*="[val_1]"]').attr('name', 'landing_comparison_rows[' + i + '][val_1]');
                    $(this).find('input[name*="[val_2]"]').attr('name', 'landing_comparison_rows[' + i + '][val_2]');
                    $(this).find('input[name*="[val_3]"]').attr('name', 'landing_comparison_rows[' + i + '][val_3]');
                });
                $('#tt-comp-count').text($tbody.find('.tt-comp-row').length);
            });

            // Live sync column names to table headers
            $('input[name="landing_comparison_col0_name"]').on('input', function() { $('#tt-comp-th-col0').text($(this).val() || 'Cột 1'); });
            $('input[name="landing_comparison_col1_name"]').on('input', function() { $('#tt-comp-th-col1').text($(this).val() || 'Cột 2'); });
            $('input[name="landing_comparison_col2_name"]').on('input', function() { $('#tt-comp-th-col2').text($(this).val() || 'Cột 3'); });
            $('input[name="landing_comparison_col3_name"]').on('input', function() { $('#tt-comp-th-col3').text($(this).val() || 'Cột 4'); });
        });
    })(jQuery);
    </script>

<?php if ($tab_comp_wrap) : ?>
    </div>
<?php endif; ?>
