<?php

/**
 * REST API Endpoints cho Cài đặt & Nội dung toàn website (Site Settings & Localization)
 *
 * Endpoint:
 * - GET  /wp-json/thientam/v1/settings : Lấy toàn bộ dữ liệu cấu hình & nội dung website
 * - POST /wp-json/thientam/v1/settings : Cập nhật cấu hình website (Admin)
 *
 * @package HelloElementorChild
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', function () {
    register_rest_route('thientam/v1', '/settings', [
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'thientam_api_get_site_settings',
            'permission_callback' => '__return_true',
        ],
        [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'thientam_api_update_site_settings',
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ],
    ]);
});

/**
 * Handler GET /wp-json/thientam/v1/settings
 */
function thientam_api_get_site_settings(WP_REST_Request $request)
{
    if (function_exists('thientam_get_site_options')) {
        $settings = thientam_get_site_options();
    } else {
        $settings = get_option('thientam_site_options', []);
    }

    if (empty($settings['marqueeTags']) || ! is_array($settings['marqueeTags'])) {
        $settings['marqueeTags'] = [];
    }

    if (empty($settings['footer']['socialLinks']) || ! is_array($settings['footer']['socialLinks'])) {
        if (! isset($settings['footer']) || ! is_array($settings['footer'])) {
            $settings['footer'] = [];
        }
        $settings['footer']['socialLinks'] = [
            [
                'name' => 'Facebook Thiên Tâm',
                'href' => 'https://facebook.com',
                'icon' => '/icons/facebook.svg',
            ],
            [
                'name' => 'Zalo Thiên Tâm',
                'href' => 'https://zalo.me',
                'icon' => '/icons/zalo.svg',
            ],
            [
                'name' => 'YouTube Thiên Tâm',
                'href' => 'https://youtube.com',
                'icon' => '/icons/youtube.svg',
            ],
            [
                'name' => 'Messenger Thiên Tâm',
                'href' => 'https://m.me',
                'icon' => '/icons/messenger.svg',
            ],
        ];
    }

    // Đồng bộ whyChoose.items và whyChoose.cards để mọi client nhận dữ liệu mới nhất
    if (isset($settings['whyChoose']) && is_array($settings['whyChoose'])) {
        $wc = $settings['whyChoose'];
        $raw_items = !empty($wc['items']) && is_array($wc['items']) ? $wc['items'] : (!empty($wc['cards']) && is_array($wc['cards']) ? $wc['cards'] : []);
        $norm_items = [];
        $norm_cards = [];
        foreach ($raw_items as $idx => $it) {
            if (!is_array($it)) continue;
            $title = $it['title'] ?? '';
            $num = $it['num'] ?? $it['step'] ?? ('0' . ($idx + 1));
            $desc = $it['desc'] ?? '';
            if ($title !== '' || $desc !== '') {
                $norm_items[] = [
                    'title' => $title,
                    'num'   => $num,
                    'desc'  => $desc,
                ];
                $norm_cards[] = [
                    'step'  => $num,
                    'title' => $title,
                    'desc'  => $desc,
                ];
            }
        }
        $settings['whyChoose']['items'] = $norm_items;
        $settings['whyChoose']['cards'] = $norm_cards;

        // Đảm bảo stats chỉ chứa các mục hợp lệ
        if (isset($wc['stats']) && is_array($wc['stats'])) {
            $norm_stats = [];
            foreach ($wc['stats'] as $st) {
                if (!is_array($st)) continue;
                $val = trim($st['value'] ?? '');
                $lbl = trim($st['label'] ?? '');
                if ($val !== '' || $lbl !== '') {
                    $norm_stats[] = [
                        'value' => $val,
                        'label' => $lbl,
                    ];
                }
            }
            $settings['whyChoose']['stats'] = $norm_stats;
        }
    }

    // Không lộ secret key của webhook ra ngoài REST API công khai
    unset($settings['webhook']);

    // Loại bỏ các trường không dùng trên UI
    if (isset($settings['consultingProcess']) && is_array($settings['consultingProcess'])) {
        unset($settings['consultingProcess']['summaryText'], $settings['consultingProcess']['contactBtn']);
    }
    if (isset($settings['process']) && is_array($settings['process'])) {
        unset($settings['process']['quote'], $settings['process']['quoteAuthor'], $settings['process']['quoteRole']);
        if (isset($settings['process']['steps']) && is_array($settings['process']['steps'])) {
            foreach ($settings['process']['steps'] as &$st) {
                if (is_array($st)) {
                    unset($st['detail']);
                }
            }
            unset($st);
        }
    }

    // Chuẩn hóa Hero Avatars
    if (isset($settings['heroHome'])) {
        if (! isset($settings['heroHome']['avatars']) || ! is_array($settings['heroHome']['avatars'])) {
            $settings['heroHome']['avatars'] = [];
        }
    }

    // Chuẩn hóa website domain không dùng www để tránh lỗi phân giải DNS crawler
    if (isset($settings['company']) && is_array($settings['company']) && !empty($settings['company']['website'])) {
        $settings['company']['website'] = preg_replace('#^https?://#', '', $settings['company']['website']);
        $settings['company']['website'] = preg_replace('#^www\.#', '', $settings['company']['website']);
    }

    // Đảm bảo cấu hình SEO toàn trang có ảnh ogImage hợp lệ (fallback Placeholder)
    $placeholder_og = 'https://site.thientam68.com/wp-content/uploads/2026/08/Placeholder-Thien-Tam.png';
    if (!isset($settings['seo']) || !is_array($settings['seo'])) {
        $settings['seo'] = [];
    }
    if (!isset($settings['seo']['home']) || !is_array($settings['seo']['home'])) {
        $settings['seo']['home'] = [];
    }
    if (empty($settings['seo']['home']['ogImage'])) {
        $settings['seo']['home']['ogImage'] = $placeholder_og;
    }

    $response = new WP_REST_Response([
        'success'   => true,
        'data'      => $settings,
        'timestamp' => current_time('mysql'),
    ], 200);

    $response->header('Access-Control-Allow-Origin', '*');
    $response->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-WP-Nonce');
    $response->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');

    return $response;
}

/**
 * Handler POST /wp-json/thientam/v1/settings
 */
function thientam_api_update_site_settings(WP_REST_Request $request)
{
    $body = $request->get_json_params();

    if (empty($body) || ! is_array($body)) {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ hoặc rỗng.',
        ], 400);
    }

    if (function_exists('thientam_save_site_options')) {
        thientam_save_site_options($body);
    } else {
        update_option('thientam_site_options', $body);
    }

    return new WP_REST_Response([
        'success'   => true,
        'message'   => 'Cập nhật cấu hình website thành công.',
        'data'      => function_exists('thientam_get_site_options') ? thientam_get_site_options() : $body,
        'timestamp' => current_time('mysql'),
    ], 200);
}
