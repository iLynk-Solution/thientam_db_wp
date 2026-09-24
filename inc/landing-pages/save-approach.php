<?php
/**
 * Shared Approach Save Module (Cách tiếp cận / Triết lý)
 * Scope: $post_id, $defaults (passed by reference)
 *
 * @package ThienTamData
 */

if (! defined('ABSPATH')) {
    exit;
}

if (function_exists('thientam_landing_save_approach_meta')) {
    thientam_landing_save_approach_meta($post_id, $defaults);
}
