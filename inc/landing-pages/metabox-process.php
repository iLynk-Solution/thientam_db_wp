<?php
/**
 * Shared Process Metabox Component (Quy trình đồng hành / tiếp nhận & tư vấn)
 * Scope: $post, $defaults, $get_val, $get_json_val (provided by parent metabox.php)
 * Optional parameters:
 *   $tab_proc_id       (string) Tab pane ID (default 'tab-proc')
 *   $tab_proc_active   (bool)   Is active pane (default false)
 *   $tab_proc_wrap     (bool)   Wrap inside <div class="tt-tab-pane"> (default true)
 *   $tab_proc_heading  (string) Section heading title (optional)
 *   $proc_data_key     (string) Key in $defaults array (default 'process')
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

if (! isset($get_json_val) || ! is_callable($get_json_val)) {
    $get_json_val = function ($meta_key, $default_arr = array()) use ($post) {
        $meta_val = get_post_meta($post->ID, $meta_key, true);
        if (! empty($meta_val)) {
            $decoded = is_array($meta_val) ? $meta_val : json_decode($meta_val, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return $default_arr;
    };
}

$tab_proc_id      = isset($tab_proc_id) && $tab_proc_id !== '' ? $tab_proc_id : 'tab-proc';
$tab_proc_active  = ! empty($tab_proc_active);
$tab_proc_wrap    = ! isset($tab_proc_wrap) || $tab_proc_wrap !== false;
$tab_proc_heading = isset($tab_proc_heading) ? $tab_proc_heading : '';
$proc_data_key    = ! empty($proc_data_key) ? $proc_data_key : 'process';

// 1. Header fields (ko gán text default)
$proc_prefix    = $get_val('landing_process_title_prefix', $defaults[$proc_data_key]['titlePrefix'] ?? '');
$proc_highlight = $get_val('landing_process_title_highlight', $defaults[$proc_data_key]['titleHighlight'] ?? '');
$proc_lead      = $get_val('landing_process_lead', $defaults[$proc_data_key]['lead'] ?? '');
$proc_notice    = $get_val('landing_process_notice', $defaults[$proc_data_key]['notice'] ?? '');

// 2. Steps list (dynamic repeater, ko gán cứng số lượng, ko gán text default)
$proc_steps_meta = get_post_meta($post->ID, 'landing_process_steps', true);
$proc_steps = array();
if (! empty($proc_steps_meta)) {
    $decoded = is_array($proc_steps_meta) ? $proc_steps_meta : json_decode($proc_steps_meta, true);
    if (is_array($decoded)) {
        $proc_steps = $decoded;
    }
}
if (empty($proc_steps)) {
    if (! empty($defaults[$proc_data_key]['steps']) && is_array($defaults[$proc_data_key]['steps'])) {
        $proc_steps = $defaults[$proc_data_key]['steps'];
    } else {
        $proc_steps = array();
    }
}
?>

<style>
.tt-proc-step-item {
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
}
.tt-proc-step-item:hover {
    border-color: #94a3b8;
    box-shadow: 0 2px 6px -1px rgba(15, 61, 97, 0.08);
}
.tt-proc-step-item.is-collapsed .tt-proc-toggle-icon {
    transform: rotate(180deg);
}
.tt-proc-step-item.is-collapsed .tt-proc-card-header {
    border-bottom: none !important;
}
</style>

<?php if ($tab_proc_wrap) : ?>
    <div id="<?php echo esc_attr($tab_proc_id); ?>" class="tt-tab-pane<?php echo $tab_proc_active ? ' active' : ''; ?>">
<?php endif; ?>

    <?php if (! empty($tab_proc_heading)) : ?>
        <h3 style="margin-top:0; color:#0f3d61;"><?php echo esc_html($tab_proc_heading); ?></h3>
    <?php endif; ?>

    <!-- 1. HEADER SECTION -->
    <div class="tt-card" style="margin-bottom:20px;">
        <div class="tt-card-header">1. Tiêu Đề & Lời Dẫn Quy Trình</div>
        <div class="tt-field-grid">
            <div class="tt-field-row">
                <label>Tiêu đề đầu (Tiền tố)</label>
                <input type="text" name="landing_process_title_prefix" class="widefat" value="<?php echo esc_attr($proc_prefix); ?>" placeholder="vd: 4 bước đồng hành cùng" />
            </div>
            <div class="tt-field-row">
                <label>Tiêu đề nổi bật (In nghiêng nổi bật)</label>
                <input type="text" name="landing_process_title_highlight" class="widefat" value="<?php echo esc_attr($proc_highlight); ?>" placeholder="vd: Thiên Tâm" />
            </div>
        </div>
        <div class="tt-field-row">
            <label>Lời dẫn quy trình</label>
            <textarea name="landing_process_lead" class="widefat" rows="2" placeholder="vd: Quy trình rõ ràng, minh bạch giúp bạn nắm bắt lộ trình làm việc..."><?php echo esc_textarea($proc_lead); ?></textarea>
        </div>
        <div class="tt-field-row">
            <label>Ghi chú chân quy trình (Notice)</label>
            <input type="text" name="landing_process_notice" class="widefat" value="<?php echo esc_attr($proc_notice); ?>" placeholder="vd: Cam kết bảo mật tuyệt đối thông tin lá số và dữ liệu cá nhân." />
        </div>
    </div>

    <!-- 2. STEPS REPEATER -->
    <div class="tt-card">
        <div class="tt-card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <span style="font-weight:700;">2. Các Bước Trong Quy Trình</span>
                <span style="font-size:12px; color:#64748b; font-weight:normal; margin-left:8px;">(Bấm vào từng bước để thu gọn / mở rộng)</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <button type="button" class="button tt-btn-collapse-all-proc" title="Thu gọn toàn bộ bước" style="height:32px; font-size:12px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;">
                    <span class="dashicons dashicons-arrow-up-alt2" style="font-size:15px; width:15px; height:15px; line-height:15px;"></span> Thu gọn tất cả
                </button>
                <button type="button" class="button tt-btn-expand-all-proc" title="Mở rộng toàn bộ bước" style="height:32px; font-size:12px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;">
                    <span class="dashicons dashicons-arrow-down-alt2" style="font-size:15px; width:15px; height:15px; line-height:15px;"></span> Mở rộng tất cả
                </button>
                <button type="button" class="button button-primary tt-btn-add-proc-step" style="background:#0f3d61; border-color:#0f3d61; height:32px; padding:0 14px; font-size:12px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;">
                    + Thêm bước
                </button>
            </div>
        </div>

        <div id="tt-proc-steps-container" style="display:flex; flex-direction:column; gap:12px; margin-top:14px;">
            <?php foreach ($proc_steps as $si => $st) : 
                $step_num   = ! empty($st['step']) ? $st['step'] : (! empty($st['num']) ? $st['num'] : sprintf('%02d', $si + 1));
                $step_title = $st['title'] ?? '';
                $step_desc  = $st['desc'] ?? '';
                $cur_icon   = isset($st['icon']) ? trim($st['icon']) : '';
                $has_icon   = ! empty($cur_icon) && $cur_icon !== 'none';
            ?>
                <div class="tt-card tt-proc-step-item tt-persp-card-row" data-index="<?php echo $si; ?>" style="background:#f8fafc; border:1px solid #cbd5e1; margin-bottom:0; border-radius:8px; padding:0; overflow:hidden;">
                    <!-- Collapsible Header -->
                    <div class="tt-proc-card-header" style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#f1f5f9; border-bottom:1px solid #cbd5e1; cursor:pointer; user-select:none;">
                        <div style="display:flex; align-items:center; gap:8px; flex:1; min-width:0;">
                            <span class="tt-proc-toggle-icon" style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:4px; background:#e2e8f0; color:#475569; transition:transform 0.2s ease; flex-shrink:0;">
                                <span class="dashicons dashicons-arrow-up-alt2" style="font-size:16px; width:16px; height:16px; line-height:16px;"></span>
                            </span>
                            <strong style="color:#0f3d61; font-size:13px; white-space:nowrap;">
                                Bước <span class="tt-proc-step-num"><?php echo esc_html($step_num); ?></span>:
                            </strong>
                            <span class="tt-proc-title-preview" style="font-weight:600; color:#1e293b; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:380px;">
                                <?php echo esc_html($step_title !== '' ? $step_title : '(Chưa nhập tiêu đề)'); ?>
                            </span>
                            <span class="tt-proc-icon-badge" style="<?php echo $has_icon ? 'display:inline-flex;' : 'display:none;'; ?> align-items:center; gap:4px; font-size:11px; font-weight:600; color:#0369a1; background:#e0f2fe; padding:2px 8px; border-radius:12px; white-space:nowrap;">
                                <span class="tt-proc-icon-badge-text"><?php echo esc_html($cur_icon); ?></span>
                            </span>
                        </div>

                        <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                            <button type="button" class="button-link-delete tt-btn-remove-proc-step" title="Xóa bước này" style="color:#b32d2e; font-size:12px; text-decoration:none; cursor:pointer; padding:3px 6px;">
                                ✕ Xóa bước
                            </button>
                            <button type="button" class="button tt-btn-toggle-proc-step" title="Thu gọn / Mở rộng" style="padding:0 8px; height:26px; line-height:24px; min-height:26px; font-size:11px; display:inline-flex; align-items:center; gap:2px;">
                                <span class="tt-toggle-text">Thu gọn</span>
                            </button>
                        </div>
                    </div>

                    <!-- Collapsible Body -->
                    <div class="tt-proc-step-body" style="padding:16px; background:#ffffff;">
                        <input type="hidden" name="process_steps[<?php echo $si; ?>][step]" class="tt-proc-step-input" value="<?php echo esc_attr($step_num); ?>" />
                        
                        <div style="display:flex; gap:16px; align-items:flex-start;">
                            <!-- Icon Picker Column -->
                            <div class="tt-persp-icon-col">
                                <label class="tt-field-label" style="font-size:11px; font-weight:600; margin-bottom:4px; display:block;">Icon Lucide</label>
                                <button type="button" class="tt-persp-icon-btn tt-btn-pick-lucide" title="Bấm để chọn / đổi icon Lucide">
                                    <span class="tt-persp-icon-preview">
                                        <?php if ($has_icon) : ?>
                                            <i data-lucide="<?php echo esc_attr($cur_icon); ?>"></i>
                                        <?php else : ?>
                                            <span style="font-size:14px; color:#94a3b8; font-weight:bold;">∅</span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="tt-persp-icon-name"><?php echo $has_icon ? esc_html($cur_icon) : '(Không icon)'; ?></span>
                                </button>
                                <span class="tt-persp-change-link tt-btn-pick-lucide" style="font-size:11px; margin-top:4px; display:inline-block; cursor:pointer; color:#0284c7;"><?php echo $has_icon ? 'Đổi icon ▾' : '+ Chọn icon ▾'; ?></span>
                                <input type="hidden" name="process_steps[<?php echo $si; ?>][icon]" class="tt-persp-icon-input" value="<?php echo esc_attr($cur_icon); ?>" />
                            </div>

                            <!-- Title and Desc Column -->
                            <div style="flex:1;">
                                <div style="margin-bottom:10px;">
                                    <label style="font-size:11px; font-weight:600; display:block; margin-bottom:4px;">Tiêu đề bước</label>
                                    <input type="text" name="process_steps[<?php echo $si; ?>][title]" class="widefat tt-proc-step-title-input" value="<?php echo esc_attr($step_title); ?>" placeholder="vd: Đăng ký nhu cầu" />
                                </div>
                                <div>
                                    <label style="font-size:11px; font-weight:600; display:block; margin-bottom:4px;">Mô tả bước</label>
                                    <textarea name="process_steps[<?php echo $si; ?>][desc]" class="widefat" rows="3" placeholder="vd: Ba mẹ để lại thông tin và nhu cầu hiện tại của gia đình qua form đăng ký..."><?php echo esc_textarea($step_desc); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top:14px; display:flex; justify-content:space-between; align-items:center;">
            <button type="button" class="button button-secondary tt-btn-add-proc-step">+ Thêm bước quy trình mới</button>
            <span style="font-size:12px; color:#64748b;">Số bước hiện tại: <strong id="tt-proc-step-count"><?php echo count($proc_steps); ?></strong></span>
        </div>
    </div>

    <script>
    (function($) {
        $(function() {
            function pad2(n) {
                return n < 10 ? '0' + n : '' + n;
            }

            // Function toggle collapse / expand for a step card
            function toggleProcStep($card, forceCollapse) {
                var $body = $card.find('.tt-proc-step-body');
                var $toggleText = $card.find('.tt-btn-toggle-proc-step .tt-toggle-text');
                var isCurrentlyCollapsed = $card.hasClass('is-collapsed');
                var shouldCollapse = (typeof forceCollapse !== 'undefined') ? forceCollapse : !isCurrentlyCollapsed;

                if (shouldCollapse) {
                    $body.slideUp(180, function() {
                        $card.addClass('is-collapsed');
                        $toggleText.text('Mở rộng');
                    });
                } else {
                    $card.removeClass('is-collapsed');
                    $body.slideDown(180, function() {
                        $toggleText.text('Thu gọn');
                    });
                }
            }

            // Click header to toggle collapse / expand
            $(document).on('click', '.tt-proc-card-header', function(e) {
                if ($(e.target).closest('.tt-btn-remove-proc-step, .tt-btn-toggle-proc-step, input, button').length) {
                    return;
                }
                e.preventDefault();
                toggleProcStep($(this).closest('.tt-proc-step-item'));
            });

            // Click toggle button inside header
            $(document).on('click', '.tt-btn-toggle-proc-step', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleProcStep($(this).closest('.tt-proc-step-item'));
            });

            // Collapse all steps
            $(document).on('click', '.tt-btn-collapse-all-proc', function(e) {
                e.preventDefault();
                $('#tt-proc-steps-container .tt-proc-step-item').each(function() {
                    toggleProcStep($(this), true);
                });
            });

            // Expand all steps
            $(document).on('click', '.tt-btn-expand-all-proc', function(e) {
                e.preventDefault();
                $('#tt-proc-steps-container .tt-proc-step-item').each(function() {
                    toggleProcStep($(this), false);
                });
            });

            // Live sync title into header preview
            $(document).on('input', '.tt-proc-step-title-input', function() {
                var val = $(this).val().trim();
                $(this).closest('.tt-proc-step-item').find('.tt-proc-title-preview').text(val || '(Chưa nhập tiêu đề)');
            });

            // Sync icon badge in header preview when selected via Lucide Modal
            $(document).on('click', '.tt-lucide-item', function() {
                var iconName = $(this).data('icon');
                var $row = window.currentActiveCardRow || (typeof currentActiveCardRow !== 'undefined' ? currentActiveCardRow : null);
                if ($row && $row.hasClass('tt-proc-step-item')) {
                    if (iconName) {
                        $row.find('.tt-proc-icon-badge').css('display', 'inline-flex');
                        $row.find('.tt-proc-icon-badge-text').text(iconName);
                    }
                }
            });

            $(document).on('click', '#tt-lucide-btn-none', function() {
                var $row = window.currentActiveCardRow || (typeof currentActiveCardRow !== 'undefined' ? currentActiveCardRow : null);
                if ($row && $row.hasClass('tt-proc-step-item')) {
                    $row.find('.tt-proc-icon-badge').hide();
                    $row.find('.tt-proc-icon-badge-text').text('');
                }
            });

            // Thêm bước quy trình mới
            $(document).on('click', '.tt-btn-add-proc-step', function(e) {
                e.preventDefault();
                var $container = $('#tt-proc-steps-container');
                var idx = $container.find('.tt-proc-step-item').length;
                var stepStr = pad2(idx + 1);

                var html = '<div class="tt-card tt-proc-step-item tt-persp-card-row" data-index="' + idx + '" style="background:#f8fafc; border:1px solid #cbd5e1; margin-bottom:0; border-radius:8px; padding:0; overflow:hidden;">' +
                    '<div class="tt-proc-card-header" style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#f1f5f9; border-bottom:1px solid #cbd5e1; cursor:pointer; user-select:none;">' +
                        '<div style="display:flex; align-items:center; gap:8px; flex:1; min-width:0;">' +
                            '<span class="tt-proc-toggle-icon" style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:4px; background:#e2e8f0; color:#475569; transition:transform 0.2s ease; flex-shrink:0;">' +
                                '<span class="dashicons dashicons-arrow-up-alt2" style="font-size:16px; width:16px; height:16px; line-height:16px;"></span>' +
                            '</span>' +
                            '<strong style="color:#0f3d61; font-size:13px; white-space:nowrap;">' +
                                'Bước <span class="tt-proc-step-num">' + stepStr + '</span>:' +
                            '</strong>' +
                            '<span class="tt-proc-title-preview" style="font-weight:600; color:#1e293b; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:380px;">(Chưa nhập tiêu đề)</span>' +
                            '<span class="tt-proc-icon-badge" style="display:none; align-items:center; gap:4px; font-size:11px; font-weight:600; color:#0369a1; background:#e0f2fe; padding:2px 8px; border-radius:12px; white-space:nowrap;">' +
                                '<span class="tt-proc-icon-badge-text"></span>' +
                            '</span>' +
                        '</div>' +
                        '<div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">' +
                            '<button type="button" class="button-link-delete tt-btn-remove-proc-step" title="Xóa bước này" style="color:#b32d2e; font-size:12px; text-decoration:none; cursor:pointer; padding:3px 6px;">✕ Xóa bước</button>' +
                            '<button type="button" class="button tt-btn-toggle-proc-step" title="Thu gọn / Mở rộng" style="padding:0 8px; height:26px; line-height:24px; min-height:26px; font-size:11px; display:inline-flex; align-items:center; gap:2px;">' +
                                '<span class="tt-toggle-text">Thu gọn</span>' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="tt-proc-step-body" style="padding:16px; background:#ffffff;">' +
                        '<input type="hidden" name="process_steps[' + idx + '][step]" class="tt-proc-step-input" value="' + stepStr + '" />' +
                        '<div style="display:flex; gap:16px; align-items:flex-start;">' +
                            '<div class="tt-persp-icon-col">' +
                                '<label class="tt-field-label" style="font-size:11px; font-weight:600; margin-bottom:4px; display:block;">Icon Lucide</label>' +
                                '<button type="button" class="tt-persp-icon-btn tt-btn-pick-lucide" title="Bấm để chọn / đổi icon Lucide">' +
                                    '<span class="tt-persp-icon-preview"><span style="font-size:14px; color:#94a3b8; font-weight:bold;">∅</span></span>' +
                                    '<span class="tt-persp-icon-name">(Không icon)</span>' +
                                '</button>' +
                                '<span class="tt-persp-change-link tt-btn-pick-lucide" style="font-size:11px; margin-top:4px; display:inline-block; cursor:pointer; color:#0284c7;">+ Chọn icon ▾</span>' +
                                '<input type="hidden" name="process_steps[' + idx + '][icon]" class="tt-persp-icon-input" value="" />' +
                            '</div>' +
                            '<div style="flex:1;">' +
                                '<div style="margin-bottom:10px;">' +
                                    '<label style="font-size:11px; font-weight:600; display:block; margin-bottom:4px;">Tiêu đề bước</label>' +
                                    '<input type="text" name="process_steps[' + idx + '][title]" class="widefat tt-proc-step-title-input" placeholder="vd: Đăng ký nhu cầu" />' +
                                '</div>' +
                                '<div>' +
                                    '<label style="font-size:11px; font-weight:600; display:block; margin-bottom:4px;">Mô tả bước</label>' +
                                    '<textarea name="process_steps[' + idx + '][desc]" class="widefat" rows="3" placeholder="vd: Ba mẹ để lại thông tin và nhu cầu hiện tại..."></textarea>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';

                $container.append(html);
                $('#tt-proc-step-count').text($container.find('.tt-proc-step-item').length);
                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    lucide.createIcons();
                }
            });

            // Xóa bước quy trình
            $(document).on('click', '.tt-btn-remove-proc-step', function(e) {
                e.preventDefault();
                var $container = $('#tt-proc-steps-container');
                $(this).closest('.tt-proc-step-item').remove();
                $container.find('.tt-proc-step-item').each(function(i) {
                    var stepStr = pad2(i + 1);
                    $(this).attr('data-index', i);
                    $(this).find('.tt-proc-step-num').text(stepStr);
                    $(this).find('.tt-proc-step-input').val(stepStr).attr('name', 'process_steps[' + i + '][step]');
                    $(this).find('.tt-persp-icon-input').attr('name', 'process_steps[' + i + '][icon]');
                    $(this).find('input[name*="[title]"]').attr('name', 'process_steps[' + i + '][title]');
                    $(this).find('textarea[name*="[desc]"]').attr('name', 'process_steps[' + i + '][desc]');
                });
                $('#tt-proc-step-count').text($container.find('.tt-proc-step-item').length);
            });
        });
    })(jQuery);
    </script>

<?php if ($tab_proc_wrap) : ?>
    </div>
<?php endif; ?>

<?php if (! defined('TT_LUCIDE_MODAL_RENDERED')) : define('TT_LUCIDE_MODAL_RENDERED', true); ?>
<!-- MODAL LUCIDE ICON PICKER (DÙNG CHUNG) -->
<div id="tt-lucide-modal" class="tt-lucide-modal-overlay">
    <div class="tt-lucide-modal-box">
        <!-- Header -->
        <div style="padding:14px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
            <div>
                <h3 style="margin:0; font-size:15px; color:#0f3d61; font-weight:700; display:flex; align-items:center; gap:6px;">
                    <i data-lucide="sparkles" style="width:18px; height:18px; color:#d97706;"></i> Chọn Biểu Tượng Lucide
                </h3>
                <p style="margin:2px 0 0 0; font-size:11px; color:#64748b;">Khám phá hơn 2.000+ biểu tượng Lucide hoặc tìm kiếm theo tên</p>
            </div>
            <button type="button" id="tt-lucide-close" style="background:none; border:none; font-size:24px; cursor:pointer; color:#64748b; line-height:1; padding:0 4px;">&times;</button>
        </div>

        <!-- Toolbar & Search -->
        <div style="padding:12px 20px; border-bottom:1px solid #e2e8f0; background:#ffffff;">
            <div style="display:flex; gap:8px; margin-bottom:10px;">
                <input type="text" id="tt-lucide-search" placeholder="Tìm trong 2.000+ icon (vd: calendar, clock, phone, check, user, file, star...)" style="flex:1; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;" />
                <button type="button" id="tt-lucide-btn-apply-custom" class="button" style="white-space:nowrap; height:34px;">Dùng tên vừa gõ</button>
            </div>
            <div id="tt-lucide-filter-tags" style="display:flex; flex-wrap:wrap; gap:6px;">
                <button type="button" class="tt-lucide-tag-btn active" data-cat="all">Tất cả (2.000+)</button>
                <button type="button" class="tt-lucide-tag-btn" data-cat="process">Quy trình & Các bước</button>
                <button type="button" class="tt-lucide-tag-btn" data-cat="edu">Trí tuệ & Học tập</button>
                <button type="button" class="tt-lucide-tag-btn" data-cat="care">Yêu thương & Gia đình</button>
                <button type="button" class="tt-lucide-tag-btn" data-cat="energy">Năng lượng & Rèn luyện</button>
                <button type="button" class="tt-lucide-tag-btn" data-cat="nature">Khám phá & Thiên nhiên</button>
                <button type="button" class="tt-lucide-tag-btn" data-cat="art">Sáng tạo & Kết nối</button>
            </div>
        </div>

        <!-- Icon Grid -->
        <div id="tt-lucide-grid" style="padding:16px 20px; overflow-y:auto; flex:1; display:grid; grid-template-columns:repeat(auto-fill, minmax(88px, 1fr)); gap:10px; background:#f8fafc; align-content:start;">
        </div>

        <!-- Footer -->
        <div style="padding:12px 20px; border-top:1px solid #e2e8f0; background:#ffffff; display:flex; justify-content:space-between; align-items:center; font-size:12px; color:#64748b;">
            <span>Đang chọn cho: <strong id="tt-lucide-target-label" style="color:#0f3d61; font-weight:700;">Thẻ #1</strong></span>
            <div>
                <button type="button" class="button" id="tt-lucide-btn-none" style="color:#b91c1c; border-color:#fca5a5; margin-right:8px;">✕ Không dùng icon</button>
                <button type="button" class="button" id="tt-lucide-cancel">Đóng</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
