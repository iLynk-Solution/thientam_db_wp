(function($) {
        $(document).ready(function() {
            // ==================== LUCIDE ICON DATA & FUNCTIONS ====================
            var LUCIDE_ICONS_DATA = [
                // Quy trình & Lộ trình các bước (process)
                { name: 'list-ordered', cat: 'process', label: 'Thứ tự các bước' },
                { name: 'clipboard-list', cat: 'process', label: 'Bảng kê khai đăng ký' },
                { name: 'clipboard-check', cat: 'process', label: 'Khảo sát kiểm tra' },
                { name: 'calendar-check', cat: 'process', label: 'Xác nhận lịch hẹn' },
                { name: 'calendar', cat: 'process', label: 'Lịch làm việc' },
                { name: 'clock', cat: 'process', label: 'Thời gian chính xác' },
                { name: 'timer', cat: 'process', label: 'Thời lượng buổi tư vấn' },
                { name: 'phone-call', cat: 'process', label: 'Cuộc gọi kết nối' },
                { name: 'phone', cat: 'process', label: 'Điện thoại liên hệ' },
                { name: 'messages-square', cat: 'process', label: 'Thảo luận trực tiếp' },
                { name: 'message-circle', cat: 'process', label: 'Tư vấn giải đáp' },
                { name: 'user-check', cat: 'process', label: 'Chuyên gia tiếp nhận' },
                { name: 'users', cat: 'process', label: 'Ba mẹ & Chuyên gia' },
                { name: 'file-text', cat: 'process', label: 'Hồ sơ kết quả' },
                { name: 'file-search', cat: 'process', label: 'Nghiên cứu hồ sơ' },
                { name: 'route', cat: 'process', label: 'Lộ trình phát triển' },
                { name: 'milestone', cat: 'process', label: 'Cột mốc quan trọng' },
                { name: 'compass', cat: 'process', label: 'Định hướng hành động' },
                { name: 'shield-check', cat: 'process', label: 'Bảo mật thông tin' },
                { name: 'badge-check', cat: 'process', label: 'Cam kết chất lượng' },
                { name: 'check-circle-2', cat: 'process', label: 'Hoàn thành bước' },
                { name: 'check-circle', cat: 'process', label: 'Xác nhận hoàn tất' },
                { name: 'presentation', cat: 'process', label: 'Buổi tham vấn 1-1' },
                { name: 'target', cat: 'process', label: 'Đích đến mục tiêu' },
                { name: 'package', cat: 'process', label: 'Bàn giao gói luận' },
                { name: 'gift', cat: 'process', label: 'Quà tặng kèm' },
                { name: 'arrow-right-circle', cat: 'process', label: 'Tiếp tục bước kế' },
                { name: 'rocket', cat: 'process', label: 'Khởi đầu bứt phá' },
                { name: 'heart-handshake', cat: 'process', label: 'Gắn kết đồng hành' },
                { name: 'sparkles', cat: 'process', label: 'Khai mở tiềm năng' },

                // Giáo dục & Trí tuệ (edu)
                {
                    name: 'book-open',
                    cat: 'edu',
                    label: 'Cuốn sách mở'
                },
                {
                    name: 'book',
                    cat: 'edu',
                    label: 'Cuốn sách'
                },
                {
                    name: 'graduation-cap',
                    cat: 'edu',
                    label: 'Mũ tốt nghiệp'
                },
                {
                    name: 'brain',
                    cat: 'edu',
                    label: 'Bộ não tư duy'
                },
                {
                    name: 'lightbulb',
                    cat: 'edu',
                    label: 'Bóng đèn sáng tạo'
                },
                {
                    name: 'sparkles',
                    cat: 'edu',
                    label: 'Tia sáng tài năng'
                },
                {
                    name: 'sparkle',
                    cat: 'edu',
                    label: 'Ngôi sao nhỏ'
                },
                {
                    name: 'award',
                    cat: 'edu',
                    label: 'Cúp khen thưởng'
                },
                {
                    name: 'trophy',
                    cat: 'edu',
                    label: 'Cúp vô địch'
                },
                {
                    name: 'medal',
                    cat: 'edu',
                    label: 'Huy chương'
                },
                {
                    name: 'puzzle',
                    cat: 'edu',
                    label: 'Mảnh ghép logic'
                },
                {
                    name: 'compass',
                    cat: 'edu',
                    label: 'La bàn định hướng'
                },
                {
                    name: 'target',
                    cat: 'edu',
                    label: 'Mục tiêu'
                },
                {
                    name: 'microscope',
                    cat: 'edu',
                    label: 'Kính hiển vi'
                },
                {
                    name: 'pencil',
                    cat: 'edu',
                    label: 'Bút chì'
                },
                {
                    name: 'library',
                    cat: 'edu',
                    label: 'Thư viện'
                },
                {
                    name: 'shapes',
                    cat: 'edu',
                    label: 'Hình khối'
                },
                {
                    name: 'glasses',
                    cat: 'edu',
                    label: 'Kính tri thức'
                },
                {
                    name: 'file-text',
                    cat: 'edu',
                    label: 'Tài liệu'
                },
                {
                    name: 'badge-check',
                    cat: 'edu',
                    label: 'Chứng nhận'
                },

                // Yêu thương & Gia đình (care)
                {
                    name: 'heart',
                    cat: 'care',
                    label: 'Trái tim'
                },
                {
                    name: 'heart-handshake',
                    cat: 'care',
                    label: 'Bắt tay thấu hiểu'
                },
                {
                    name: 'hand-heart',
                    cat: 'care',
                    label: 'Nâng niu'
                },
                {
                    name: 'heart-pulse',
                    cat: 'care',
                    label: 'Nhịp đập'
                },
                {
                    name: 'smile',
                    cat: 'care',
                    label: 'Nụ cười'
                },
                {
                    name: 'smile-plus',
                    cat: 'care',
                    label: 'Tươi vui'
                },
                {
                    name: 'laugh',
                    cat: 'care',
                    label: 'Rạng rỡ'
                },
                {
                    name: 'baby',
                    cat: 'care',
                    label: 'Em bé'
                },
                {
                    name: 'users',
                    cat: 'care',
                    label: 'Gia đình gắn kết'
                },
                {
                    name: 'user-check',
                    cat: 'care',
                    label: 'Người đồng hành'
                },
                {
                    name: 'user',
                    cat: 'care',
                    label: 'Cá nhân'
                },
                {
                    name: 'home',
                    cat: 'care',
                    label: 'Mái ấm'
                },
                {
                    name: 'sun',
                    cat: 'care',
                    label: 'Mặt trời'
                },
                {
                    name: 'shield',
                    cat: 'care',
                    label: 'Khiên chở che'
                },
                {
                    name: 'shield-check',
                    cat: 'care',
                    label: 'Bảo vệ an toàn'
                },
                {
                    name: 'footprints',
                    cat: 'care',
                    label: 'Dấu chân bước'
                },
                {
                    name: 'helping-hand',
                    cat: 'care',
                    label: 'Giúp đỡ'
                },
                {
                    name: 'life-buoy',
                    cat: 'care',
                    label: 'Phao cứu hộ'
                },

                // Năng lượng & Rèn luyện (energy)
                {
                    name: 'dumbbell',
                    cat: 'energy',
                    label: 'Quả tạ rèn luyện'
                },
                {
                    name: 'zap',
                    cat: 'energy',
                    label: 'Tia chớp năng lượng'
                },
                {
                    name: 'flame',
                    cat: 'energy',
                    label: 'Ngọn lửa đam mê'
                },
                {
                    name: 'star',
                    cat: 'energy',
                    label: 'Ngôi sao tỏa sáng'
                },
                {
                    name: 'activity',
                    cat: 'energy',
                    label: 'Hoạt động'
                },
                {
                    name: 'timer',
                    cat: 'energy',
                    label: 'Bộ đếm giờ'
                },
                {
                    name: 'clock',
                    cat: 'energy',
                    label: 'Đồng hồ'
                },
                {
                    name: 'calendar',
                    cat: 'energy',
                    label: 'Lịch trình'
                },
                {
                    name: 'check-circle',
                    cat: 'energy',
                    label: 'Hoàn thành'
                },
                {
                    name: 'thumbs-up',
                    cat: 'energy',
                    label: 'Khích lệ'
                },
                {
                    name: 'flag',
                    cat: 'energy',
                    label: 'Cột mốc'
                },
                {
                    name: 'rocket',
                    cat: 'energy',
                    label: 'Bứt phá'
                },
                {
                    name: 'gauge',
                    cat: 'energy',
                    label: 'Đo lường'
                },
                {
                    name: 'trending-up',
                    cat: 'energy',
                    label: 'Phát triển'
                },
                {
                    name: 'battery-charging',
                    cat: 'energy',
                    label: 'Nạp năng lượng'
                },
                {
                    name: 'crown',
                    cat: 'energy',
                    label: 'Vương miện'
                },

                // Khám phá & Thiên nhiên (nature)
                {
                    name: 'mountain',
                    cat: 'nature',
                    label: 'Ngọn núi'
                },
                {
                    name: 'trees',
                    cat: 'nature',
                    label: 'Rừng cây'
                },
                {
                    name: 'tree-pine',
                    cat: 'nature',
                    label: 'Cây thông'
                },
                {
                    name: 'sprout',
                    cat: 'nature',
                    label: 'Mầm cây non'
                },
                {
                    name: 'flower-2',
                    cat: 'nature',
                    label: 'Bông hoa'
                },
                {
                    name: 'leaf',
                    cat: 'nature',
                    label: 'Chiếc lá'
                },
                {
                    name: 'globe',
                    cat: 'nature',
                    label: 'Địa cầu'
                },
                {
                    name: 'rainbow',
                    cat: 'nature',
                    label: 'Cầu vồng'
                },
                {
                    name: 'telescope',
                    cat: 'nature',
                    label: 'Viễn kính'
                },
                {
                    name: 'map-pin',
                    cat: 'nature',
                    label: 'Điểm mốc'
                },
                {
                    name: 'anchor',
                    cat: 'nature',
                    label: 'Điểm tựa mỏ neo'
                },
                {
                    name: 'cloud-sun',
                    cat: 'nature',
                    label: 'Mây và nắng'
                },
                {
                    name: 'feather',
                    cat: 'nature',
                    label: 'Lông vũ'
                },

                // Sáng tạo & Kết nối (art)
                {
                    name: 'message-circle',
                    cat: 'art',
                    label: 'Trò chuyện'
                },
                {
                    name: 'message-square',
                    cat: 'art',
                    label: 'Trao đổi'
                },
                {
                    name: 'palette',
                    cat: 'art',
                    label: 'Bảng màu'
                },
                {
                    name: 'music',
                    cat: 'art',
                    label: 'Âm nhạc'
                },
                {
                    name: 'camera',
                    cat: 'art',
                    label: 'Hình ảnh'
                },
                {
                    name: 'bell',
                    cat: 'art',
                    label: 'Chuông báo'
                },
                {
                    name: 'phone',
                    cat: 'art',
                    label: 'Liên lạc'
                },
                {
                    name: 'send',
                    cat: 'art',
                    label: 'Gửi gắm'
                },
                {
                    name: 'gift',
                    cat: 'art',
                    label: 'Món quà'
                },
                {
                    name: 'key',
                    cat: 'art',
                    label: 'Chìa khóa'
                },
                {
                    name: 'coffee',
                    cat: 'art',
                    label: 'Tách trà'
                },
                {
                    name: 'wand-2',
                    cat: 'art',
                    label: 'Phép thuật'
                },
                {
                    name: 'eye',
                    cat: 'art',
                    label: 'Lắng nghe quan sát'
                }
            ];

            var currentActiveCardRow = null;

            function toKebabCase(str) {
                return str
                    .replace(/([A-Z]+)([A-Z][a-z])/g, '$1-$2')
                    .replace(/([a-z\d])([A-Z])/g, '$1-$2')
                    .toLowerCase();
            }

            var ALL_LUCIDE_ICONS = [];

            function ensureAllLucideIcons() {
                if (ALL_LUCIDE_ICONS.length > LUCIDE_ICONS_DATA.length) {
                    return ALL_LUCIDE_ICONS;
                }

                var map = {};
                var curatedList = [];

                LUCIDE_ICONS_DATA.forEach(function(item) {
                    if (!map[item.name]) {
                        map[item.name] = true;
                        curatedList.push({
                            name: item.name,
                            cat: item.cat || 'all',
                            label: item.label || item.name,
                            isCurated: true
                        });
                    }
                });

                var otherList = [];
                if (window.lucide && window.lucide.icons) {
                    Object.keys(window.lucide.icons).forEach(function(key) {
                        var kebab = toKebabCase(key);
                        if (!map[kebab]) {
                            map[kebab] = true;
                            otherList.push({
                                name: kebab,
                                cat: 'all',
                                label: kebab,
                                isCurated: false
                            });
                        }
                    });
                }

                otherList.sort(function(a, b) {
                    return a.name.localeCompare(b.name);
                });

                ALL_LUCIDE_ICONS = curatedList.concat(otherList);
                return ALL_LUCIDE_ICONS;
            }

            function refreshLucide() {
                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }
            }

            var currentFilteredIcons = [];
            var currentRenderedIndex = 0;
            var BATCH_SIZE = 100;

            function renderNextBatch() {
                var $grid = $('#tt-lucide-grid');
                var itemsToRender = currentFilteredIcons.slice(currentRenderedIndex, currentRenderedIndex + BATCH_SIZE);
                if (!itemsToRender.length) return;

                var currentVal = currentActiveCardRow ? currentActiveCardRow.find('.tt-persp-icon-input').val().trim().toLowerCase() : '';
                var html = '';

                itemsToRender.forEach(function(item) {
                    var isSel = (item.name === currentVal);
                    html += '<div class="tt-lucide-item' + (isSel ? ' selected' : '') + '" data-icon="' + item.name + '" title="' + item.label + ' (' + item.name + ')">' +
                        '<i data-lucide="' + item.name + '"></i>' +
                        '<span>' + item.name + '</span>' +
                        '</div>';
                });

                $('#tt-lucide-load-more-wrap').remove();
                $grid.append(html);
                currentRenderedIndex += itemsToRender.length;

                if (currentRenderedIndex < currentFilteredIcons.length) {
                    var remaining = currentFilteredIcons.length - currentRenderedIndex;
                    var nextCount = Math.min(BATCH_SIZE, remaining);
                    $grid.append(
                        '<div id="tt-lucide-load-more-wrap" style="grid-column:1/-1; text-align:center; padding:16px 0;">' +
                        '<button type="button" class="button tt-btn-load-more-lucide" style="font-size:12px; height:32px; padding:0 16px; border-radius:6px; cursor:pointer;">' +
                        '▼ Tải thêm ' + nextCount + ' icon nữa (Đã hiện ' + currentRenderedIndex + ' / ' + currentFilteredIcons.length + ')' +
                        '</button>' +
                        '</div>'
                    );
                }

                refreshLucide();
            }

            function renderLucideGrid(query, cat) {
                var allIcons = ensureAllLucideIcons();
                var $grid = $('#tt-lucide-grid');
                $grid.empty();
                query = (query || '').toLowerCase().trim();
                cat = cat || 'all';

                currentFilteredIcons = allIcons.filter(function(item) {
                    var matchCat = (cat === 'all' || item.cat === cat);
                    var matchQuery = !query || item.name.toLowerCase().indexOf(query) !== -1 || item.label.toLowerCase().indexOf(query) !== -1;
                    return matchCat && matchQuery;
                });

                currentRenderedIndex = 0;

                if (currentFilteredIcons.length === 0) {
                    $grid.html('<div style="grid-column:1/-1; text-align:center; padding:40px 10px; color:#64748b;">Không tìm thấy icon nào khớp với <strong>"' + $('<div>').text(query).html() + '"</strong>. Bạn có thể bấm <strong>"Dùng tên vừa gõ"</strong> để dùng icon này.</div>');
                    return;
                }

                renderNextBatch();
            }

            // Tự động tải thêm khi cuộn xuống cuối danh sách
            $(document).on('scroll', '#tt-lucide-grid', function() {
                if (this.scrollTop + this.clientHeight >= this.scrollHeight - 60) {
                    if (currentRenderedIndex < currentFilteredIcons.length) {
                        renderNextBatch();
                    }
                }
            });

            // Bấm nút Tải thêm icon
            $(document).on('click', '.tt-btn-load-more-lucide', function(e) {
                e.preventDefault();
                renderNextBatch();
            });

            setTimeout(refreshLucide, 200);

            // Đảm bảo modal nằm ở top-level body ngay khi trang load để không bị ẩn theo tab
            $(function() {
                if ($('#tt-lucide-modal').length && !$('#tt-lucide-modal').parent().is('body')) {
                    $('#tt-lucide-modal').appendTo('body');
                }
            });

            // Mở Modal chọn Lucide
            $(document).on('click', '.tt-btn-pick-lucide', function(e) {
                e.preventDefault();

                // Đảm bảo modal nằm ở body, tránh bị tab ẩn (display:none) bao bọc
                if ($('#tt-lucide-modal').length && !$('#tt-lucide-modal').parent().is('body')) {
                    $('#tt-lucide-modal').appendTo('body');
                }

                currentActiveCardRow = $(this).closest('.tt-persp-card-row, .tt-proc-step-item');
                window.currentActiveCardRow = currentActiveCardRow;

                var cardNum = currentActiveCardRow.find('.persp-num, .tt-proc-step-num').text();
                $('#tt-lucide-target-label').text('Thẻ/Bước #' + cardNum);

                $('#tt-lucide-search').val('');
                $('.tt-lucide-tag-btn').removeClass('active');
                $('.tt-lucide-tag-btn[data-cat="all"]').addClass('active');

                renderLucideGrid('', 'all');
                $('#tt-lucide-modal').css('display', 'flex');
                $('#tt-lucide-search').focus();
            });

            // Đóng Modal
            $('#tt-lucide-close, #tt-lucide-cancel').on('click', function(e) {
                e.preventDefault();
                $('#tt-lucide-modal').hide();
            });
            $('#tt-lucide-modal').on('click', function(e) {
                if (e.target === this) {
                    $(this).hide();
                }
            });

            // Tìm kiếm realtime
            $('#tt-lucide-search').on('input', function() {
                var q = $(this).val();
                var activeCat = $('.tt-lucide-tag-btn.active').data('cat') || 'all';
                renderLucideGrid(q, activeCat);
            });

            // Lọc theo danh mục
            $('.tt-lucide-tag-btn').on('click', function(e) {
                e.preventDefault();
                $('.tt-lucide-tag-btn').removeClass('active');
                $(this).addClass('active');
                var cat = $(this).data('cat');
                var q = $('#tt-lucide-search').val();
                renderLucideGrid(q, cat);
            });

            // Chọn icon từ grid
            $(document).on('click', '.tt-lucide-item', function(e) {
                e.preventDefault();
                var iconName = $(this).data('icon');
                if (currentActiveCardRow && iconName) {
                    currentActiveCardRow.find('.tt-persp-icon-input').val(iconName);
                    currentActiveCardRow.find('.tt-persp-icon-preview').html('<i data-lucide="' + iconName + '"></i>');
                    currentActiveCardRow.find('.tt-persp-icon-name').text(iconName);
                    currentActiveCardRow.find('.tt-persp-change-link').text('Đổi icon ▾');

                    // Đồng bộ badge ở header bước quy trình (nếu có)
                    if (currentActiveCardRow.hasClass('tt-proc-step-item')) {
                        currentActiveCardRow.find('.tt-proc-icon-badge').css('display', 'inline-flex');
                        currentActiveCardRow.find('.tt-proc-icon-badge-text').text(iconName);
                    }

                    refreshLucide();
                }
                $('#tt-lucide-modal').hide();
            });

            // Áp dụng tên gõ tự do
            $('#tt-lucide-btn-apply-custom').on('click', function(e) {
                e.preventDefault();
                var customName = $('#tt-lucide-search').val().trim().toLowerCase().replace(/[^a-z0-9-]/g, '-');
                if (currentActiveCardRow && customName) {
                    currentActiveCardRow.find('.tt-persp-icon-input').val(customName);
                    currentActiveCardRow.find('.tt-persp-icon-preview').html('<i data-lucide="' + customName + '"></i>');
                    currentActiveCardRow.find('.tt-persp-icon-name').text(customName);
                    currentActiveCardRow.find('.tt-persp-change-link').text('Đổi icon ▾');

                    // Đồng bộ badge ở header bước quy trình (nếu có)
                    if (currentActiveCardRow.hasClass('tt-proc-step-item')) {
                        currentActiveCardRow.find('.tt-proc-icon-badge').css('display', 'inline-flex');
                        currentActiveCardRow.find('.tt-proc-icon-badge-text').text(customName);
                    }

                    refreshLucide();
                }
                $('#tt-lucide-modal').hide();
            });

            // Bỏ chọn icon (Không dùng icon)
            $('#tt-lucide-btn-none').on('click', function(e) {
                e.preventDefault();
                if (currentActiveCardRow) {
                    currentActiveCardRow.find('.tt-persp-icon-input').val('');
                    currentActiveCardRow.find('.tt-persp-icon-preview').html('<span style="font-size:14px; color:#94a3b8; font-weight:bold;">∅</span>');
                    currentActiveCardRow.find('.tt-persp-icon-name').text('(Không icon)');
                    currentActiveCardRow.find('.tt-persp-change-link').text('+ Chọn icon ▾');

                    if (currentActiveCardRow.hasClass('tt-proc-step-item')) {
                        currentActiveCardRow.find('.tt-proc-icon-badge').hide();
                        currentActiveCardRow.find('.tt-proc-icon-badge-text').text('');
                    }
                }
                $('#tt-lucide-modal').hide();
            });

            // Thêm Thẻ Góc Nhìn Mới (Hỗ trợ nhiều container / tab)
            $(document).on('click', '.tt-btn-add-persp-card, #tt-btn-add-persp-card', function(e) {
                e.preventDefault();
                var fieldName = $(this).attr('data-field-name') || 'persp_cards';
                var containerId = $(this).attr('data-container-id');
                var $container = containerId ? $('#' + containerId) : null;
                if (! $container || ! $container.length) {
                    $container = $(this).closest('.tt-card').find('.tt-persp-grid');
                }
                if (! $container.length) {
                    $container = $('#tt-persp-cards-container');
                }
                var count = $container.find('.tt-persp-card-row').length;
                var html = '<div class="tt-persp-card-box tt-persp-card-row">' +
                    '<div class="tt-persp-card-header">' +
                    '<span class="tt-persp-card-badge">Thẻ #<span class="persp-num">' + (count + 1) + '</span></span>' +
                    '<button type="button" class="button-link-delete tt-btn-remove-persp" title="Xóa thẻ này">✕ Xóa thẻ</button>' +
                    '</div>' +
                    '<div class="tt-persp-card-body">' +
                    '<div class="tt-persp-icon-col">' +
                    '<label class="tt-field-label">Icon</label>' +
                    '<button type="button" class="tt-persp-icon-btn tt-btn-pick-lucide" title="Bấm để chọn / đổi icon Lucide">' +
                    '<span class="tt-persp-icon-preview"><span style="font-size:14px; color:#94a3b8; font-weight:bold;">∅</span></span>' +
                    '<span class="tt-persp-icon-name">(Không icon)</span>' +
                    '</button>' +
                    '<span class="tt-persp-change-link tt-btn-pick-lucide">+ Chọn icon ▾</span>' +
                    '<input type="hidden" name="' + fieldName + '[' + count + '][icon]" class="tt-persp-icon-input" value="" />' +
                    '</div>' +
                    '<div class="tt-persp-fields-col">' +
                    '<div class="tt-persp-field-group">' +
                    '<label class="tt-field-label">Tiêu đề thẻ</label>' +
                    '<input type="text" name="' + fieldName + '[' + count + '][title]" class="widefat tt-input-title" value="" placeholder="vd: Tiêu đề thẻ" />' +
                    '</div>' +
                    '<div class="tt-persp-field-group">' +
                    '<label class="tt-field-label">Mô tả chi tiết</label>' +
                    '<textarea name="' + fieldName + '[' + count + '][desc]" class="widefat tt-textarea-desc" rows="2" placeholder="Nhập mô tả..."></textarea>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>';

                $container.append(html);
                refreshLucide();
            });

            // Xóa Thẻ Góc Nhìn
            $(document).on('click', '.tt-btn-remove-persp', function(e) {
                e.preventDefault();
                var $container = $(this).closest('.tt-persp-grid');
                var $btn = $(this).closest('.tt-card').find('.tt-btn-add-persp-card, #tt-btn-add-persp-card');
                var fieldName = $btn.attr('data-field-name') || 'persp_cards';
                $(this).closest('.tt-persp-card-row').remove();
                $container.find('.tt-persp-card-row').each(function(index) {
                    $(this).find('.persp-num').text(index + 1);
                    $(this).find('input[name*="[title]"]').attr('name', fieldName + '[' + index + '][title]');
                    $(this).find('input[name*="[icon]"]').attr('name', fieldName + '[' + index + '][icon]');
                    $(this).find('textarea[name*="[desc]"]').attr('name', fieldName + '[' + index + '][desc]');
                });
            });
            // Chuyển Tabs (Document delegation + direct attribute reading + show/hide)
            $(document).on('click', '.tt-tab-btn', function(e) {
                e.preventDefault();
                var targetTab = $(this).attr('data-tab') || $(this).data('tab');
                if (!targetTab) return;
                
                $('.tt-tab-btn').removeClass('active');
                $('.tt-tab-pane').removeClass('active').hide();
                
                $(this).addClass('active');
                $('#' + targetTab).addClass('active').show();
            });

            // WordPress Media Picker cho Hero Banner Image
            var ttHeroMediaFrame = null;
            $('#tt-hero-image-btn').on('click', function(e) {
                e.preventDefault();
                if (ttHeroMediaFrame) {
                    ttHeroMediaFrame.open();
                    return;
                }
                ttHeroMediaFrame = wp.media({
                    title: 'Chọn ảnh cho Hero Banner',
                    button: {
                        text: 'Sử dụng ảnh này'
                    },
                    multiple: false
                });
                ttHeroMediaFrame.on('select', function() {
                    var attachment = ttHeroMediaFrame.state().get('selection').first().toJSON();
                    var url = attachment.url;
                    $('#tt-hero-image-input').val(url);
                    $('#tt-hero-image-preview').html('<img src="' + url + '" style="width:100%; height:100%; object-fit:cover;" />');
                    $('#tt-hero-image-remove').show();
                });
                ttHeroMediaFrame.open();
            });

            $('#tt-hero-image-remove').on('click', function(e) {
                e.preventDefault();
                $('#tt-hero-image-input').val('');
                $('#tt-hero-image-preview').html('<span style="font-size:11px; color:#888;">Chưa chọn ảnh</span>');
                $(this).hide();
            });

            $('#tt-hero-image-input').on('input change', function() {
                var val = $(this).val().trim();
                if (val !== '') {
                    $('#tt-hero-image-preview').html('<img src="' + val + '" />');
                    $('#tt-hero-image-remove').show();
                } else {
                    $('#tt-hero-image-preview').html('<span style="font-size:11px; color:#888;">Chưa chọn ảnh</span>');
                    $('#tt-hero-image-remove').hide();
                }
            });

            // WordPress Media Picker cho Approach Image
            var ttApproachMediaFrame = null;
            $('#tt-approach-image-btn').on('click', function(e) {
                e.preventDefault();
                if (ttApproachMediaFrame) {
                    ttApproachMediaFrame.open();
                    return;
                }
                ttApproachMediaFrame = wp.media({
                    title: 'Chọn ảnh Minh họa Phương pháp',
                    button: {
                        text: 'Sử dụng ảnh này'
                    },
                    multiple: false
                });
                ttApproachMediaFrame.on('select', function() {
                    var attachment = ttApproachMediaFrame.state().get('selection').first().toJSON();
                    var url = attachment.url;
                    $('#tt-approach-image-input').val(url);
                    $('#tt-approach-image-preview').html('<img src="' + url + '" style="width:100%; height:100%; object-fit:cover;" />');
                    $('#tt-approach-image-remove').show();
                });
                ttApproachMediaFrame.open();
            });

            $('#tt-approach-image-remove').on('click', function(e) {
                e.preventDefault();
                $('#tt-approach-image-input').val('');
                $('#tt-approach-image-preview').html('<span style="font-size:11px; color:#888;">Chưa chọn ảnh</span>');
                $(this).hide();
            });

            $('#tt-approach-image-input').on('input change', function() {
                var val = $(this).val().trim();
                if (val !== '') {
                    $('#tt-approach-image-preview').html('<img src="' + val + '" />');
                    $('#tt-approach-image-remove').show();
                } else {
                    $('#tt-approach-image-preview').html('<span style="font-size:11px; color:#888;">Chưa chọn ảnh</span>');
                    $('#tt-approach-image-remove').hide();
                }
            });

            // WordPress Media Picker cho Expert Avatar
            var ttExpertMediaFrame = null;
            $('#tt-expert-avatar-btn').on('click', function(e) {
                e.preventDefault();
                if (ttExpertMediaFrame) {
                    ttExpertMediaFrame.open();
                    return;
                }
                ttExpertMediaFrame = wp.media({
                    title: 'Chọn ảnh Chuyên gia',
                    button: {
                        text: 'Sử dụng ảnh này'
                    },
                    multiple: false
                });
                ttExpertMediaFrame.on('select', function() {
                    var attachment = ttExpertMediaFrame.state().get('selection').first().toJSON();
                    var url = attachment.url;
                    $('#tt-expert-avatar-input').val(url);
                    $('#tt-expert-avatar-preview').html('<img src="' + url + '" style="width:100%; height:100%; object-fit:cover;" />');
                    $('#tt-expert-avatar-remove').show();
                });
                ttExpertMediaFrame.open();
            });

            $('#tt-expert-avatar-remove').on('click', function(e) {
                e.preventDefault();
                $('#tt-expert-avatar-input').val('');
                $('#tt-expert-avatar-preview').html('<span style="font-size:11px; color:#888;">Chưa chọn ảnh</span>');
                $(this).hide();
            });

            $('#tt-expert-avatar-input').on('input change', function() {
                var val = $(this).val().trim();
                if (val !== '') {
                    $('#tt-expert-avatar-preview').html('<img src="' + val + '" />');
                    $('#tt-expert-avatar-remove').show();
                } else {
                    $('#tt-expert-avatar-preview').html('<span style="font-size:11px; color:#888;">Chưa chọn ảnh</span>');
                    $('#tt-expert-avatar-remove').hide();
                }
            });

            
            // ==================== DOORS ACCORDION HANDLERS ====================
            $(document).on('click', '.tt-door-accordion-header', function(e) {
                if ($(e.target).closest('.tt-btn-remove-door').length) {
                    return;
                }
                var $item = $(this).closest('.tt-door-accordion-item');
                $item.toggleClass('is-open');
            });

            $(document).on('click', '#tt-door-expand-all', function(e) {
                e.preventDefault();
                $('.tt-door-accordion-item').addClass('is-open');
            });

            $(document).on('click', '#tt-door-collapse-all', function(e) {
                e.preventDefault();
                $('.tt-door-accordion-item').removeClass('is-open');
            });

            $(document).on('input', '.tt-door-title-input', function() {
                var val = $(this).val().trim();
                var $preview = $(this).closest('.tt-door-accordion-item').find('.tt-door-title-preview');
                $preview.text(val !== '' ? val : '(Chưa đặt tiêu đề)');
            });

            $(document).on('click', '.tt-btn-remove-door', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (confirm('Bạn có chắc muốn xóa cánh cửa này?')) {
                    $(this).closest('.tt-door-accordion-item').remove();
                    $('.tt-door-accordion-item').each(function(index) {
                        var numStr = (index + 1 < 10 ? '0' : '') + (index + 1);
                        $(this).find('.door-num').text(numStr);
                        $(this).find('input, textarea').each(function() {
                            var name = $(this).attr('name');
                            if (name) {
                                $(this).attr('name', name.replace(/doors_items\[\d+\]/, 'doors_items[' + index + ']'));
                            }
                        });
                    });
                }
            });

            $('#tt-btn-add-door').on('click', function(e) {
                e.preventDefault();
                var count = $('.tt-door-accordion-item').length;
                var numStr = (count + 1 < 10 ? '0' : '') + (count + 1);
                var html = '<div class="tt-door-accordion-item tt-door-row is-open">' +
                    '<div class="tt-door-accordion-header">' +
                    '<div class="tt-door-header-left">' +
                    '<span class="tt-door-toggle-icon">▸</span>' +
                    '<strong class="tt-door-badge">Cánh cửa #<span class="door-num">' + numStr + '</span></strong>' +
                    '<span class="tt-door-title-preview">(Chưa đặt tiêu đề)</span>' +
                    '</div>' +
                    '<button type="button" class="button-link-delete tt-btn-remove-door" style="cursor:pointer;" title="Xóa cánh cửa này">Xóa</button>' +
                    '</div>' +
                    '<div class="tt-door-accordion-body">' +
                    '<div class="tt-field-grid">' +
                    '<div class="tt-field-row">' +
                    '<label>Tiêu đề cánh cửa</label>' +
                    '<input type="text" name="doors_items[' + count + '][title]" class="widefat tt-door-title-input" placeholder="Nhập tiêu đề cánh cửa..." value="" />' +
                    '</div>' +
                    '<div class="tt-field-row">' +
                    '<label>Huy hiệu / Badge</label>' +
                    '<input type="text" name="doors_items[' + count + '][badge]" class="widefat" placeholder="VD: Khám phá tiềm năng..." value="" />' +
                    '</div>' +
                    '</div>' +
                    '<div class="tt-field-row">' +
                    '<label>Mô tả</label>' +
                    '<textarea name="doors_items[' + count + '][desc]" class="widefat" rows="2" placeholder="Nhập mô tả ngắn..."></textarea>' +
                    '</div>' +
                    '<div class="tt-field-row">' +
                    '<label>Các câu hỏi băn khoăn (mỗi dòng 1 câu hỏi)</label>' +
                    '<textarea name="doors_items[' + count + '][questions]" class="widefat" rows="4" placeholder="Con mạnh ở đâu?&#10;Con phù hợp với điều gì?..."></textarea>' +
                    '</div>' +
                    '<div class="tt-field-grid">' +
                    '<div class="tt-field-row">' +
                    '<label>Điểm nhấn / Kết luận</label>' +
                    '<input type="text" name="doors_items[' + count + '][highlight]" class="widefat" placeholder="VD: Đừng chờ con gặp vấn đề..." value="" />' +
                    '</div>' +
                    '<div class="tt-field-row">' +
                    '<label>Nhu cầu truyền vào Form tư vấn (tùy chọn)</label>' +
                    '<input type="text" name="doors_items[' + count + '][prefill]" class="widefat" placeholder="Để trống sẽ lấy theo tiêu đề" value="" />' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>';

                var $newEl = $(html);
                $('#tt-doors-container').append($newEl);
                $newEl.find('.tt-door-title-input').focus();
            });

            // ==================== FAQ ACCORDION HANDLERS ====================
            // Toggle từng Accordion Item khi click vào Header
            $(document).on('click', '.tt-faq-accordion-header', function(e) {
                if ($(e.target).closest('.tt-btn-remove-faq').length) {
                    return; // Không toggle nếu bấm nút Xóa
                }
                var $item = $(this).closest('.tt-faq-accordion-item');
                $item.toggleClass('is-open');
            });

            // Mở tất cả
            $(document).on('click', '#tt-faq-expand-all', function(e) {
                e.preventDefault();
                $('.tt-faq-accordion-item').addClass('is-open');
            });

            // Thu gọn tất cả
            $(document).on('click', '#tt-faq-collapse-all', function(e) {
                e.preventDefault();
                $('.tt-faq-accordion-item').removeClass('is-open');
            });

            // Realtime cập nhật tiêu đề câu hỏi lên header accordion khi người dùng gõ
            $(document).on('input', '.tt-faq-q-input', function() {
                var val = $(this).val().trim();
                var $preview = $(this).closest('.tt-faq-accordion-item').find('.tt-faq-title-preview');
                $preview.text(val !== '' ? val : 'Chưa đặt câu hỏi...');
            });

            // Xóa câu hỏi FAQ
            $(document).on('click', '.tt-btn-remove-faq', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (confirm('Bạn có chắc muốn xóa câu hỏi này?')) {
                    $(this).closest('.tt-faq-accordion-item').remove();
                    $('.tt-faq-accordion-item').each(function(index) {
                        $(this).find('.faq-num').text(index + 1);
                        $(this).find('input[name*="[q]"]').attr('name', 'faq_items[' + index + '][q]');
                        $(this).find('textarea[name*="[a]"]').attr('name', 'faq_items[' + index + '][a]');
                    });
                }
            });

            // Thêm câu hỏi FAQ mới (mở sẵn và focus vào ô câu hỏi)
            $('#tt-btn-add-faq').on('click', function(e) {
                e.preventDefault();
                var count = $('.tt-faq-accordion-item').length;
                var html = '<div class="tt-faq-accordion-item tt-faq-row is-open">' +
                    '<div class="tt-faq-accordion-header">' +
                    '<div class="tt-faq-header-left">' +
                    '<span class="tt-faq-toggle-icon">▸</span>' +
                    '<strong class="tt-faq-badge">#<span class="faq-num">' + (count + 1) + '</span></strong>' +
                    '<span class="tt-faq-title-preview">Câu hỏi mới #' + (count + 1) + '</span>' +
                    '</div>' +
                    '<button type="button" class="button-link-delete tt-btn-remove-faq" style="cursor:pointer;" title="Xóa câu hỏi này">Xóa</button>' +
                    '</div>' +
                    '<div class="tt-faq-accordion-body">' +
                    '<div class="tt-field-row" style="margin-bottom:10px;">' +
                    '<label style="font-size:11px; font-weight:600; color:#475569; margin-bottom:4px;">Câu hỏi</label>' +
                    '<input type="text" name="faq_items[' + count + '][q]" class="widefat tt-faq-q-input" placeholder="Nhập câu hỏi..." value="" />' +
                    '</div>' +
                    '<div class="tt-field-row" style="margin-bottom:0;">' +
                    '<label style="font-size:11px; font-weight:600; color:#475569; margin-bottom:4px;">Câu trả lời</label>' +
                    '<textarea name="faq_items[' + count + '][a]" class="widefat" rows="3" placeholder="Nhập câu trả lời..."></textarea>' +
                    '</div>' +
                    '</div>' +
                    '</div>';

                var $newEl = $(html);
                $('#tt-faq-container').append($newEl);
                $newEl.find('.tt-faq-q-input').focus();
            });

            // ==================== REPEATER: GÓI DỊCH VỤ (PACKAGES) ====================
            // ==================== REPEATER: GÓI DỊCH VỤ (PACKAGES) ====================
            function ttReindexPackages() {
                var $rows = $('#tt-packages-container .tt-package-card-row');
                $('.tt-packages-count-badge').text($rows.length + ' gói');
                $rows.each(function(index) {
                    var num = index + 1;
                    var numFormatted = num < 10 ? '0' + num : num;
                    $(this).attr('data-index', index);
                    $(this).find('.pkg-num').text(numFormatted);
                    var curTitle = $(this).find('.tt-pkg-input-title').val().trim();
                    $(this).find('.pkg-title-preview').text(curTitle ? curTitle : 'Gói dịch vụ ' + numFormatted);

                    var curPrice = $(this).find('.tt-pkg-input-price').val().trim();
                    var $priceBadge = $(this).find('.pkg-price-preview');
                    if (curPrice) {
                        $priceBadge.text(curPrice).show();
                    } else {
                        $priceBadge.hide();
                    }

                    var isFeat = $(this).find('.tt-pkg-featured').is(':checked');
                    var $featBadge = $(this).find('.pkg-featured-badge');
                    if (isFeat) {
                        $featBadge.show();
                    } else {
                        $featBadge.hide();
                    }

                    var fields = ['title', 'id', 'badge', 'price', 'oldPrice', 'slogan', 'target', 'guidance', 'prefill', 'features'];
                    for (var f = 0; f < fields.length; f++) {
                        $(this).find('[name*="[' + fields[f] + ']"]').attr('name', 'pkg[' + index + '][' + fields[f] + ']');
                    }
                    $(this).find('.tt-pkg-featured').attr('name', 'pkg[' + index + '][isFeatured]');
                });
            }

            function togglePackageCard($card, forceCollapse) {
                var $body = $card.find('.tt-package-card-body');
                var $toggleText = $card.find('.tt-btn-toggle-package .tt-toggle-text');
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
            $(document).on('click', '.tt-package-card-header', function(e) {
                if ($(e.target).closest('.tt-pkg-featured-label, .tt-btn-remove-package, input, textarea').length) {
                    return;
                }
                e.preventDefault();
                var $card = $(this).closest('.tt-package-card-row');
                togglePackageCard($card);
            });

            // Click explicit toggle button inside header
            $(document).on('click', '.tt-btn-toggle-package', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $card = $(this).closest('.tt-package-card-row');
                togglePackageCard($card);
            });

            // Collapse all packages
            $(document).on('click', '.tt-btn-collapse-all-packages', function(e) {
                e.preventDefault();
                $('#tt-packages-container .tt-package-card-row').each(function() {
                    togglePackageCard($(this), true);
                });
            });

            // Expand all packages
            $(document).on('click', '.tt-btn-expand-all-packages', function(e) {
                e.preventDefault();
                $('#tt-packages-container .tt-package-card-row').each(function() {
                    togglePackageCard($(this), false);
                });
            });

            // Add new package
            $(document).on('click', '.tt-btn-add-package', function(e) {
                e.preventDefault();
                var $container = $('#tt-packages-container');
                var count = $container.find('.tt-package-card-row').length;
                var num = count + 1;
                var numFormatted = num < 10 ? '0' + num : num;

                $('#tt-packages-empty-notice').hide();

                var html = '<div class="tt-card tt-package-card-row" data-index="' + count + '" style="background:#f8fafc; border:1px solid #cbd5e1; margin-bottom:14px; border-radius:8px; padding:0; overflow:hidden;">' +
                    '<div class="tt-package-card-header" style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px; background:#f1f5f9; border-bottom:1px solid #cbd5e1; cursor:pointer; user-select:none;">' +
                        '<div class="tt-pkg-header-left" style="display:flex; align-items:center; gap:8px; flex:1; min-width:0;">' +
                            '<span class="tt-pkg-toggle-icon" style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:4px; background:#e2e8f0; color:#475569; transition:transform 0.2s ease; flex-shrink:0;">' +
                                '<span class="dashicons dashicons-arrow-up-alt2" style="font-size:16px; width:16px; height:16px; line-height:16px;"></span>' +
                            '</span>' +
                            '<span style="font-weight:700; color:#0f3d61; font-size:13px; white-space:nowrap;">' +
                                'Gói <span class="pkg-num">' + numFormatted + '</span>:' +
                            '</span>' +
                            '<span class="pkg-title-preview" style="font-weight:600; color:#1e293b; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:320px;">' +
                                'Gói dịch vụ ' + numFormatted +
                            '</span>' +
                            '<span class="pkg-price-preview" style="display:none; font-size:11px; font-weight:600; color:#0369a1; background:#e0f2fe; padding:2px 8px; border-radius:12px; white-space:nowrap;"></span>' +
                            '<span class="pkg-featured-badge" style="display:none; font-size:11px; font-weight:600; color:#b45309; background:#fef3c7; padding:2px 8px; border-radius:12px; white-space:nowrap;">★ Nổi bật</span>' +
                        '</div>' +
                        '<div class="tt-pkg-header-right" style="display:flex; align-items:center; gap:12px; flex-shrink:0;">' +
                            '<label class="tt-pkg-featured-label" style="font-size:12px; font-weight:normal; margin:0; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">' +
                                '<input type="checkbox" name="pkg[' + count + '][isFeatured]" class="tt-pkg-featured" value="1" /> <strong>Gói nổi bật (Highlight)</strong>' +
                            '</label>' +
                            '<button type="button" class="button-link-delete tt-btn-remove-package" title="Xóa gói này" style="color:#b32d2e; font-size:12px; text-decoration:none; cursor:pointer; padding:3px 6px;">' +
                                '✕ Xóa gói' +
                            '</button>' +
                            '<button type="button" class="button tt-btn-toggle-package" title="Thu gọn / Mở rộng" style="padding:0 8px; height:26px; line-height:24px; min-height:26px; font-size:11px; display:inline-flex; align-items:center; gap:2px;">' +
                                '<span class="tt-toggle-text">Thu gọn</span>' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="tt-package-card-body" style="padding:16px;">' +
                        '<div class="tt-field-grid-3">' +
                            '<div class="tt-field-row">' +
                                '<label>Tên gói</label>' +
                                '<input type="text" name="pkg[' + count + '][title]" class="widefat tt-pkg-input-title" value="" placeholder="VD: Bản Luận Mới" />' +
                            '</div>' +
                            '<div class="tt-field-row">' +
                                '<label>Mã gói (ID - Tự động tạo từ tên gói)</label>' +
                                '<input type="text" name="pkg[' + count + '][id]" class="widefat tt-pkg-input-id" value="pkg-' + num + '" readonly style="background:#f1f5f9; color:#475569; cursor:not-allowed; border-color:#cbd5e1;" />' +
                            '</div>' +
                            '<div class="tt-field-row">' +
                                '<label>Huy hiệu (Badge)</label>' +
                                '<input type="text" name="pkg[' + count + '][badge]" class="widefat" value="" placeholder="VD: MỚI" />' +
                            '</div>' +
                        '</div>' +
                        '<div class="tt-field-grid">' +
                            '<div class="tt-field-row">' +
                                '<label>Giá bán hiện tại (VD: 2.500.000đ)</label>' +
                                '<input type="text" name="pkg[' + count + '][price]" class="widefat tt-pkg-input-price" value="" />' +
                            '</div>' +
                            '<div class="tt-field-row">' +
                                '<label>Giá gốc gạch ngang (VD: Giá gốc 5.000.000đ)</label>' +
                                '<input type="text" name="pkg[' + count + '][oldPrice]" class="widefat" value="" />' +
                            '</div>' +
                        '</div>' +
                        '<div class="tt-field-row">' +
                            '<label>Slogan / Câu châm ngôn ngắn của gói</label>' +
                            '<input type="text" name="pkg[' + count + '][slogan]" class="widefat" value="" />' +
                        '</div>' +
                        '<div class="tt-field-row">' +
                            '<label>Đối tượng phù hợp (Target)</label>' +
                            '<textarea name="pkg[' + count + '][target]" class="widefat" rows="2"></textarea>' +
                        '</div>' +
                        '<div class="tt-field-grid">' +
                            '<div class="tt-field-row">' +
                                '<label>Lời khuyên chọn gói (Guidance)</label>' +
                                '<input type="text" name="pkg[' + count + '][guidance]" class="widefat" value="" />' +
                            '</div>' +
                            '<div class="tt-field-row">' +
                                '<label>Gợi ý form khi bấm chọn (Prefill)</label>' +
                                '<input type="text" name="pkg[' + count + '][prefill]" class="widefat" value="" />' +
                            '</div>' +
                        '</div>' +
                        '<div class="tt-field-row">' +
                            '<label>Các tính năng nổi bật (Mỗi dòng 1 mục)</label>' +
                            '<textarea name="pkg[' + count + '][features]" class="widefat" rows="4"></textarea>' +
                        '</div>' +
                    '</div>' +
                '</div>';

                var $newCard = $(html);
                $container.append($newCard);
                ttReindexPackages();
                $newCard.find('.tt-pkg-input-title').focus();
            });

            // Remove package (unrestricted, allow removing all)
            $(document).on('click', '.tt-btn-remove-package', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (! confirm('Bạn có chắc chắn muốn xóa gói dịch vụ này không?')) {
                    return;
                }
                var $card = $(this).closest('.tt-package-card-row');
                $card.fadeOut(150, function() {
                    $(this).remove();
                    ttReindexPackages();
                    if ($('#tt-packages-container .tt-package-card-row').length === 0) {
                        $('#tt-packages-empty-notice').show();
                    }
                });
            });

            function ttSlugify(text) {
                if (!text) return '';
                return text
                    .toString()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[đĐ]/g, 'd')
                    .replace(/[^a-z0-9\s-]/g, '')
                    .trim()
                    .replace(/[\s-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            // Live preview updates & auto generate ID
            $(document).on('input', '.tt-pkg-input-title', function() {
                var val = $(this).val().trim();
                var $row = $(this).closest('.tt-package-card-row');
                var num = $row.find('.pkg-num').text();
                $row.find('.pkg-title-preview').text(val ? val : 'Gói dịch vụ ' + num);
                var slug = ttSlugify(val);
                if (!slug) {
                    var idx = parseInt($row.attr('data-index'), 10);
                    slug = 'pkg-' + (isNaN(idx) ? num : (idx + 1));
                }
                $row.find('.tt-pkg-input-id').val(slug);
            });

            $(document).on('input', '.tt-pkg-input-price', function() {
                var val = $(this).val().trim();
                var $row = $(this).closest('.tt-package-card-row');
                var $priceBadge = $row.find('.pkg-price-preview');
                if (val) {
                    $priceBadge.text(val).show();
                } else {
                    $priceBadge.hide();
                }
            });

            $(document).on('change', '.tt-pkg-featured', function() {
                var $row = $(this).closest('.tt-package-card-row');
                var $featBadge = $row.find('.pkg-featured-badge');
                if ($(this).is(':checked')) {
                    $featBadge.show();
                } else {
                    $featBadge.hide();
                }
            });
        });
    })(jQuery);
