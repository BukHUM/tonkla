<?php
/**
 * Admin performance helpers
 *
 * Speeds up wp-admin navigation by avoiding blocking work on every request.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Do not run WordPress.org update checks during admin page loads.
 *
 * `_maybe_update_plugins` was taking ~1.5–2s per admin request when the
 * update_plugins transient was missing. Checks still run via WP-Cron and
 * Dashboard → Updates. When DISABLE_WP_CRON is true, keep the default
 * admin_init checks so security updates are not skipped entirely.
 */
function tonkla_disable_blocking_admin_update_checks()
{
    if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
        return;
    }

    remove_action('admin_init', '_maybe_update_core');
    remove_action('admin_init', '_maybe_update_plugins');
    remove_action('admin_init', '_maybe_update_themes');
}
add_action('admin_init', 'tonkla_disable_blocking_admin_update_checks', 1);

/**
 * Whether current admin screen needs Tonkla admin assets / scripts.
 *
 * @return bool
 */
function tonkla_is_tonkla_admin_screen()
{
    if (!is_admin()) {
        return false;
    }

    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $screen_id = $screen ? (string) $screen->id : '';
    $screen_base = $screen ? (string) $screen->base : '';
    $post_type = ($screen && isset($screen->post_type)) ? (string) $screen->post_type : '';

    $is_carbon_page = ($page && str_contains($page, 'crb_carbon_fields'))
        || ($page && str_starts_with($page, 'tonkla-'))
        || str_contains($screen_id, 'crb_carbon_fields')
        || str_contains($screen_id, 'tonkla');

    $is_tools = defined('TONKLA_ADMIN_SLUG_TOOLS') && TONKLA_ADMIN_SLUG_TOOLS === $page;
    $is_page_editor = in_array($screen_base, array('post', 'page'), true) && 'page' === $post_type;
    $is_nav_menus = ('nav-menus' === $screen_base);

    return $is_carbon_page || $is_tools || $is_page_editor || $is_nav_menus;
}

/**
 * Enqueue the shared admin stylesheet.
 */
function tonkla_enqueue_admin_style()
{
    wp_enqueue_style(
        'tonkla-admin-style',
        get_template_directory_uri() . '/assets/css/admin.css',
        array(),
        (string) filemtime(get_template_directory() . '/assets/css/admin.css')
    );
}

/**
 * Admin stylesheet for Posts / Pages list screens (thumbnail column width).
 */
function tonkla_enqueue_admin_list_styles()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if ($screen && in_array($screen->post_type, array('post', 'page', 'newsletter'), true)) {
        tonkla_enqueue_admin_style();
    }
}

/**
 * Enqueue theme admin assets only where needed.
 *
 * @param string $hook_suffix Current admin page.
 */
function tonkla_admin_styles($hook_suffix = '')
{
    if ('edit.php' === $hook_suffix) {
        tonkla_enqueue_admin_list_styles();
        return;
    }

    if (!tonkla_is_tonkla_admin_screen()) {
        // Early admin_enqueue may run before screen is ready.
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        $is_tonkla_page = $page && (
            str_contains($page, 'crb_carbon_fields')
            || str_starts_with($page, 'tonkla-')
        );
        $post_type = isset($_GET['post_type']) ? sanitize_key(wp_unslash($_GET['post_type'])) : 'post';
        $is_page_edit = in_array($hook_suffix, array('post.php', 'post-new.php'), true)
            && ('page' === $post_type || ('post.php' === $hook_suffix && isset($_GET['post']) && 'page' === get_post_type(absint($_GET['post']))));
        $is_nav_menus = ('nav-menus.php' === $hook_suffix);

        if (!$is_tonkla_page && !$is_page_edit && !$is_nav_menus) {
            return;
        }
    }

    $theme_uri = get_template_directory_uri();
    $theme_dir = get_template_directory();

    tonkla_enqueue_admin_style();

    // No defer: save-bar reorder must run before Carbon applies sticky .fixed.
    wp_enqueue_script(
        'tonkla-admin-script',
        $theme_uri . '/assets/js/admin.js',
        array(),
        (string) filemtime($theme_dir . '/assets/js/admin.js'),
        true
    );
}
