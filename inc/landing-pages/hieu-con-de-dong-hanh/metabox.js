(function($) {
        $(document).ready(function() {
            // ==================== LUCIDE ICON DATA & FUNCTIONS ====================
            var LUCIDE_ICONS_DATA = [
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

            function refreshLucide() {
                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }
            }

            function renderLucideGrid(query, cat) {
                var $grid = $('#tt-lucide-grid');
                $grid.empty();
                query = (query || '').toLowerCase().trim();
                cat = cat || 'all';

                var filtered = LUCIDE_ICONS_DATA.filter(function(item) {
                    var matchCat = (cat === 'all' || item.cat === cat);
                    var matchQuery = !query || item.name.toLowerCase().indexOf(query) !== -1 || item.label.toLowerCase().indexOf(query) !== -1;
                    return matchCat && matchQuery;
                });

                if (filtered.length === 0) {
                    $grid.html('<div style="grid-column:1/-1; text-align:center; padding:40px 10px; color:#64748b;">Không tìm thấy icon nào phù hợp. Bạn có thể bấm <strong>"Dùng tên vừa gõ"</strong> để dùng icon này.</div>');
                    return;
                }

                var currentVal = currentActiveCardRow ? currentActiveCardRow.find('.tt-persp-icon-input').val().trim().toLowerCase() : '';

                filtered.forEach(function(item) {
                    var isSel = (item.name === currentVal);
                    var html = '<div class="tt-lucide-item' + (isSel ? ' selected' : '') + '" data-icon="' + item.name + '" title="' + item.label + ' (' + item.name + ')">' +
                        '<i data-lucide="' + item.name + '"></i>' +
                        '<span>' + item.name + '</span>' +
                        '</div>';
                    $grid.append(html);
                });

                refreshLucide();
            }

            setTimeout(refreshLucide, 200);

            // Mở Modal chọn Lucide
            $(document).on('click', '.tt-btn-pick-lucide', function(e) {
                e.preventDefault();
                currentActiveCardRow = $(this).closest('.tt-persp-card-row');
                var cardNum = currentActiveCardRow.find('.persp-num').text();
                $('#tt-lucide-target-label').text('Thẻ #' + cardNum);

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
                    refreshLucide();
                }
                $('#tt-lucide-modal').hide();
            });

            // Thêm Thẻ Góc Nhìn Mới
            $('#tt-btn-add-persp-card').on('click', function(e) {
                e.preventDefault();
                var count = $('.tt-persp-card-row').length;
                var defaultIcon = 'sun';
                var html = '<div class="tt-persp-card-box tt-persp-card-row">' +
                    '<div class="tt-persp-card-header">' +
                    '<span class="tt-persp-card-badge">Thẻ #<span class="persp-num">' + (count + 1) + '</span></span>' +
                    '<button type="button" class="button-link-delete tt-btn-remove-persp" title="Xóa thẻ này">Xóa thẻ</button>' +
                    '</div>' +
                    '<div class="tt-persp-card-body">' +
                    '<div class="tt-persp-icon-col">' +
                    '<label class="tt-field-label">Icon</label>' +
                    '<button type="button" class="tt-persp-icon-btn tt-btn-pick-lucide" title="Bấm để chọn / đổi icon Lucide">' +
                    '<span class="tt-persp-icon-preview">' +
                    '<i data-lucide="' + defaultIcon + '"></i>' +
                    '</span>' +
                    '<span class="tt-persp-icon-name">' + defaultIcon + '</span>' +
                    '</button>' +
                    '<span class="tt-persp-change-link tt-btn-pick-lucide">Đổi icon ▾</span>' +
                    '<input type="hidden" name="persp_cards[' + count + '][icon]" class="tt-persp-icon-input" value="' + defaultIcon + '" />' +
                    '</div>' +
                    '<div class="tt-persp-fields-col">' +
                    '<div class="tt-persp-field-group">' +
                    '<label class="tt-field-label">Tiêu đề thẻ</label>' +
                    '<input type="text" name="persp_cards[' + count + '][title]" class="widefat tt-input-title" value="" placeholder="vd: Tiêu đề góc nhìn" />' +
                    '</div>' +
                    '<div class="tt-persp-field-group">' +
                    '<label class="tt-field-label">Mô tả chi tiết</label>' +
                    '<textarea name="persp_cards[' + count + '][desc]" class="widefat tt-textarea-desc" rows="2" placeholder="Nhập mô tả góc nhìn..."></textarea>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>';

                $('#tt-persp-cards-container').append(html);
                refreshLucide();
            });

            // Xóa Thẻ Góc Nhìn
            $(document).on('click', '.tt-btn-remove-persp', function(e) {
                e.preventDefault();
                $(this).closest('.tt-persp-card-row').remove();
                $('.tt-persp-card-row').each(function(index) {
                    $(this).find('.persp-num').text(index + 1);
                    $(this).find('input[name*="[title]"]').attr('name', 'persp_cards[' + index + '][title]');
                    $(this).find('input[name*="[icon]"]').attr('name', 'persp_cards[' + index + '][icon]');
                    $(this).find('textarea[name*="[desc]"]').attr('name', 'persp_cards[' + index + '][desc]');
                });
            });

            // Chuyển Tabs
            $('.tt-tab-btn').on('click', function(e) {
                e.preventDefault();
                var targetTab = $(this).data('tab');
                $('.tt-tab-btn').removeClass('active');
                $('.tt-tab-pane').removeClass('active');
                $(this).addClass('active');
                $('#' + targetTab).addClass('active');
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
        });
    })(jQuery);
