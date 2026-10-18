<?php
/**
 * Tonkla IT functions and definitions
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Composer autoload (svg-sanitize for front icon SVG + admin upload).
 */
$tonkla_autoload = get_template_directory() . '/vendor/autoload.php';
if ( is_readable( $tonkla_autoload ) ) {
	require_once $tonkla_autoload;
}

/**
 * Front-end security headers (XFO, nosniff, Referrer-Policy, Permissions-Policy)
 */
require_once get_template_directory() . '/inc/security-headers.php';

/**
 * Large theme options: keep out of autoload
 */
require_once get_template_directory() . '/inc/options-autoload.php';

/**
 * Shared admin page slugs (needed when Carbon Fields registers on front too).
 */
require_once get_template_directory() . '/inc/admin-constants.php';

/**
 * Appearance: color presets, fonts, CSS custom properties (front-end).
 */
require_once get_template_directory() . '/inc/appearance.php';

/**
 * Front-end icon helper (page builder templates).
 */
require_once get_template_directory() . '/inc/builder/icons.php';

/**
 * Page Builder field helpers (needed when Carbon registers page_modules on the front).
 */
require_once get_template_directory() . '/inc/builder/modules.php';
require_once get_template_directory() . '/inc/builder/fields.php';

/**
 * Admin-only bootstrapping — skip on front to cut TTFB.
 */
if ( is_admin() ) {
	require_once get_template_directory() . '/inc/builder/svg-upload.php';
	require_once get_template_directory() . '/inc/builder/admin-assets.php';
	require_once get_template_directory() . '/inc/admin-performance.php';
	require_once get_template_directory() . '/inc/admin-chrome.php';
	require_once get_template_directory() . '/inc/admin-menu-order.php';
	require_once get_template_directory() . '/inc/admin-tools.php';
	require_once get_template_directory() . '/inc/admin-thumbnails.php';

	add_action( 'admin_enqueue_scripts', 'tonkla_admin_styles' );
}

if (!defined('TONKLA_DEFAULT_LOGO')) {
    define('TONKLA_DEFAULT_LOGO', get_template_directory_uri() . '/assets/images/logo-tonkla-landscape.png');
}

/** Intrinsic size of assets/images/logo-tonkla-landscape.png */
if (!defined('TONKLA_DEFAULT_LOGO_WIDTH')) {
    define('TONKLA_DEFAULT_LOGO_WIDTH', 190);
}
if (!defined('TONKLA_DEFAULT_LOGO_HEIGHT')) {
    define('TONKLA_DEFAULT_LOGO_HEIGHT', 62);
}

/**
 * Resolve Carbon image field value to an attachment ID when possible.
 *
 * @param mixed $value Attachment ID or image URL.
 * @return int Attachment ID or 0.
 */
function tonkla_resolve_image_id($value)
{
    if (is_numeric($value)) {
        return absint($value);
    }

    if (is_string($value) && $value !== '') {
        return (int) attachment_url_to_postid($value);
    }

    return 0;
}

/**
 * Default footer blurb when Carbon "footer_description" is empty.
 * Uses the site tagline from Settings → General (distributable-theme safe).
 *
 * @return string
 */
function tonkla_default_footer_description(): string {
	$tagline = get_bloginfo( 'description', 'display' );
	$tagline = is_string( $tagline ) ? trim( wp_strip_all_tags( $tagline ) ) : '';
	if ( '' !== $tagline ) {
		return $tagline;
	}
	$name = get_bloginfo( 'name', 'display' );
	$name = is_string( $name ) ? trim( wp_strip_all_tags( $name ) ) : '';
	if ( '' === $name ) {
		return '';
	}
	/* translators: %s: site name from Settings → General */
	return sprintf( __( 'เว็บไซต์อย่างเป็นทางการของ %s', 'tonkla' ), $name );
}

/**
 * Default copyright line when Carbon "copyright_text" is empty.
 *
 * @return string HTML allowed through wp_kses_post at the call site.
 */
function tonkla_default_copyright_text(): string {
	$name = get_bloginfo( 'name', 'display' );
	$name = is_string( $name ) ? trim( wp_strip_all_tags( $name ) ) : '';
	if ( '' === $name ) {
		$name = 'WordPress';
	}
	return sprintf(
		/* translators: 1: current year, 2: site name */
		__( '&copy; %1$s %2$s. All rights reserved.', 'tonkla' ),
		esc_html( wp_date( 'Y' ) ),
		esc_html( $name )
	);
}

/**
 * Resolved footer description (Carbon option or site-based default).
 *
 * @return string
 */
function tonkla_get_footer_description(): string {
	$custom = function_exists( 'carbon_get_theme_option' ) ? (string) carbon_get_theme_option( 'footer_description' ) : '';
	$custom = trim( $custom );
	return '' !== $custom ? $custom : tonkla_default_footer_description();
}

/**
 * Resolved copyright HTML (Carbon option or site-based default).
 *
 * @return string
 */
function tonkla_get_copyright_text(): string {
	$custom = function_exists( 'carbon_get_theme_option' ) ? (string) carbon_get_theme_option( 'copyright_text' ) : '';
	$custom = trim( $custom );
	return '' !== $custom ? $custom : tonkla_default_copyright_text();
}

/**
 * Print theme logo with explicit width/height (avoids CLS / unsized-images).
 *
 * @param string $context 'header' or 'footer'.
 * @param string $class   CSS classes for the img.
 */
function tonkla_the_logo($context = 'header', $class = '')
{
    $option_key = ('footer' === $context) ? 'footer_logo' : 'main_logo';
    $logo       = function_exists('carbon_get_theme_option') ? carbon_get_theme_option($option_key) : '';

    if ('footer' === $context && empty($logo) && function_exists('carbon_get_theme_option')) {
        $logo = carbon_get_theme_option('main_logo');
    }

    $alt     = get_bloginfo('name');
    $attrs   = array(
        'class'    => $class,
        'alt'      => $alt,
        'decoding' => 'async',
    );

    if ('header' === $context) {
        $attrs['fetchpriority'] = 'high';
    } else {
        $attrs['loading'] = 'lazy';
    }

    $attachment_id = tonkla_resolve_image_id($logo);
    if ($attachment_id) {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image escapes.
        echo wp_get_attachment_image($attachment_id, 'full', false, $attrs);
        return;
    }

    $src = (!empty($logo) && is_string($logo)) ? $logo : TONKLA_DEFAULT_LOGO;

    printf(
        '<img src="%1$s" alt="%2$s" class="%3$s" width="%4$d" height="%5$d" decoding="async"%6$s>',
        esc_url($src),
        esc_attr($alt),
        esc_attr($class),
        (int) TONKLA_DEFAULT_LOGO_WIDTH,
        (int) TONKLA_DEFAULT_LOGO_HEIGHT,
        ('header' === $context) ? ' fetchpriority="high"' : ' loading="lazy"'
    );
}

/**
 * Whether the current view renders a newsletter subscribe form.
 *
 * @return bool
 */
function tonkla_needs_newsletter_assets()
{
    return is_home()
        || is_singular('post')
        || is_category()
        || is_tag()
        || is_author()
        || is_date();
}

/**
 * Whether the current view needs @tailwindcss/typography (prose) styles.
 *
 * @return bool
 */
function tonkla_needs_prose_assets()
{
    if (is_singular('post')) {
        return true;
    }

    if (!is_page()) {
        return false;
    }

    $template = get_page_template_slug();
    if ('page-flexible.php' !== $template) {
        // Default page template uses prose around the_content().
        return true;
    }

    if (!function_exists('carbon_get_post_meta')) {
        return false;
    }

    $modules = carbon_get_post_meta(get_queried_object_id(), 'page_modules');
    if (empty($modules) || !is_array($modules)) {
        return false;
    }

    foreach ($modules as $module) {
        if (!empty($module['_type']) && 'content_block' === $module['_type']) {
            return true;
        }
    }

    return false;
}

/**
 * Ensure category archives query posts.
 *
 * @param WP_Query $query Main query.
 */
function tonkla_verify_post_type($query)
{
    if (!is_admin() && $query->is_main_query() && $query->is_category()) {
        if (empty($query->get('post_type'))) {
            $query->set('post_type', 'post');
        }
    }
}
add_action('pre_get_posts', 'tonkla_verify_post_type');


/**
 * Whether the current front-end view needs Contact Form 7 assets.
 *
 * @return bool
 */
function tonkla_needs_cf7_assets()
{
    if (!function_exists('wpcf7_enqueue_scripts')) {
        return false;
    }

    if (is_singular()) {
        $post = get_post();
        if ($post instanceof WP_Post) {
            if (has_shortcode($post->post_content, 'contact-form-7')
                || false !== strpos($post->post_content, '[contact-form-7')
            ) {
                return true;
            }

            if (function_exists('carbon_get_post_meta')) {
                $modules = carbon_get_post_meta($post->ID, 'page_modules');
                if (!empty($modules) && is_array($modules)) {
                    $encoded = wp_json_encode($modules);
                    if (is_string($encoded)
                        && (false !== strpos($encoded, 'contact-form-7') || false !== strpos($encoded, 'wpcf7'))
                    ) {
                        return true;
                    }
                }
            }
        }
    }

    return false;
}

/**
 * Theme Setup
 */
function tonkla_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    add_theme_support('menus');

    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'tonkla'),
        'footer_services' => esc_html__('Footer Services', 'tonkla'),
        'footer_solutions' => esc_html__('Footer Solutions', 'tonkla'),
        'footer_customers' => esc_html__('Footer Customers', 'tonkla'),
        'footer_legal' => esc_html__('Footer Legal', 'tonkla'),
    ));
}
add_action('after_setup_theme', 'tonkla_setup');

/**
 * Enqueue scripts and styles
 */

// Include Custom Post Types & Taxonomies
require_once get_template_directory() . '/inc/newsletter.php';


function tonkla_scripts()
{
    $theme_uri = get_template_directory_uri();
    $theme_dir = get_template_directory();
    $defer     = array(
        'in_footer' => true,
        'strategy'  => 'defer',
    );

    // Self-hosted Prompt + Sarabun when still selected (see inc/appearance.php).
    $tonkla_style_deps = array();
    if ( ! function_exists( 'tonkla_needs_local_fonts' ) || tonkla_needs_local_fonts() ) {
        wp_enqueue_style(
            'tonkla-fonts',
            $theme_uri . '/assets/css/fonts.css',
            array(),
            (string) filemtime($theme_dir . '/assets/css/fonts.css')
        );
        $tonkla_style_deps[] = 'tonkla-fonts';
    }

    // Tailwind (render-blocking — required for layout; async caused blank/FOUC with .tk-reveal).
    wp_enqueue_style(
        'tonkla-output',
        $theme_uri . '/assets/css/main.css',
        $tonkla_style_deps,
        (string) filemtime($theme_dir . '/assets/css/main.css')
    );

    // Typography plugin CSS only on content pages (keeps homepage main.css lean).
    if (tonkla_needs_prose_assets()) {
        wp_enqueue_style(
            'tonkla-prose',
            $theme_uri . '/assets/css/prose.css',
            array('tonkla-output'),
            (string) filemtime($theme_dir . '/assets/css/prose.css')
        );
    }

    // Alpine.js Plugins (Collapse) - Must load before Alpine Core
    wp_enqueue_script('alpine-collapse', 'https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.13.3/dist/cdn.min.js', array(), '3.13.3', $defer);

    // Enqueue Alpine.js
    wp_enqueue_script('alpine', 'https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js', array('alpine-collapse'), '3.13.3', $defer);

    wp_enqueue_script('tonkla-script', $theme_uri . '/assets/js/main.js', array(), (string) filemtime($theme_dir . '/assets/js/main.js'), $defer);

    // Font Awesome CSS+fonts after first paint — avoid ~250KiB competing with LCP.
    wp_localize_script(
        'tonkla-script',
        'tonklaPerf',
        array(
            'faCss' => $theme_uri . '/assets/vendor/fontawesome/css/all.min.css?ver=6.4.0',
        )
    );

    // Newsletter + SweetAlert only where the subscribe form is rendered.
    if (tonkla_needs_newsletter_assets()) {
        wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11.14.0/dist/sweetalert2.all.min.js', array(), '11.14.0', $defer);
        wp_enqueue_script('tonkla-newsletter', $theme_uri . '/assets/js/newsletter.js', array('sweetalert2'), (string) filemtime($theme_dir . '/assets/js/newsletter.js'), $defer);
        wp_localize_script('tonkla-newsletter', 'tonkla_ajax', array(
            'ajax_url'  => admin_url('admin-ajax.php'),
            'nonce'     => wp_create_nonce('tonkla_newsletter_nonce'),
            'activated' => tonkla_newsletter_activation_status(),
            'brand'     => function_exists( 'tonkla_brand_hex' ) ? tonkla_brand_hex() : '#10b981',
        ));
    }
}
add_action('wp_enqueue_scripts', 'tonkla_scripts');

/**
 * Preload critical stylesheets early (fonts still load via @font-face when needed).
 */
function tonkla_preload_critical_assets()
{
    $theme_uri = get_template_directory_uri();
    $theme_dir = get_template_directory();

    $main_ver = (string) filemtime($theme_dir . '/assets/css/main.css');

    if ( ! function_exists( 'tonkla_needs_local_fonts' ) || tonkla_needs_local_fonts() ) {
        $fonts_ver = (string) filemtime($theme_dir . '/assets/css/fonts.css');
        printf(
            '<link rel="preload" href="%s" as="style" />' . "\n",
            esc_url($theme_uri . '/assets/css/fonts.css?ver=' . rawurlencode($fonts_ver))
        );
    }
    printf(
        '<link rel="preload" href="%s" as="style" />' . "\n",
        esc_url($theme_uri . '/assets/css/main.css?ver=' . rawurlencode($main_ver))
    );
}
add_action('wp_head', 'tonkla_preload_critical_assets', 1);

/**
 * Skip Contact Form 7 CSS/JS on pages without a form (homepage often loads them unused).
 */
function tonkla_maybe_disable_cf7_assets()
{
    if (is_admin() || tonkla_needs_cf7_assets()) {
        return;
    }

    add_filter('wpcf7_load_css', '__return_false');
    add_filter('wpcf7_load_js', '__return_false');
}
add_action('wp_enqueue_scripts', 'tonkla_maybe_disable_cf7_assets', 1);

/**
 * Drop emoji detection assets on the front end (unused; saves a late script request).
 */
function tonkla_disable_front_emojis()
{
    if (is_admin()) {
        return;
    }

    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('wp_print_scripts', 'print_emoji_detection_script');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    add_filter('emoji_svg_url', '__return_false');
}
add_action('init', 'tonkla_disable_front_emojis');

/**
 * Print admin-controlled header scripts (Theme Options → Custom Scripts).
 */
function tonkla_print_header_scripts()
{
    if (!function_exists('carbon_get_theme_option')) {
        return;
    }
    $scripts = carbon_get_theme_option('header_scripts');
    if ($scripts) {
        echo $scripts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Raw scripts entered by administrators (manage_options).
    }
}
add_action('wp_head', 'tonkla_print_header_scripts', 99);

/**
 * Print admin-controlled footer scripts (Theme Options → Custom Scripts).
 */
function tonkla_print_footer_scripts()
{
    if (!function_exists('carbon_get_theme_option')) {
        return;
    }
    $scripts = carbon_get_theme_option('footer_scripts');
    if ($scripts) {
        echo $scripts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Raw scripts entered by administrators (manage_options).
    }
}
add_action('wp_footer', 'tonkla_print_footer_scripts', 99);

/**
 * Carbon Fields Setup
 */
use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action('carbon_fields_register_fields', 'tonkla_register_custom_fields');
/**
 * Register Carbon containers on every request.
 * Complex fields (page_modules) must be registered for carbon_get_* to unpack on the front end.
 */
function tonkla_register_custom_fields()
{
    require_once get_template_directory() . '/inc/carbon-fields.php';
}

add_action('after_setup_theme', 'tonkla_crb_load');
function tonkla_crb_load()
{
    // Only boot if the plugin is active
    if (class_exists('\\Carbon_Fields\\Carbon_Fields')) {
        \Carbon_Fields\Carbon_Fields::boot();
    }
}

/**
 * Import Demo Script (admin only)
 */
if (is_admin()) {
    require_once get_template_directory() . '/inc/import-demo.php';
}

/**
 * Custom Login Page
 */
require_once get_template_directory() . '/inc/login-customize.php';

/**
 * Custom Nav Walker
 */
require_once get_template_directory() . '/inc/menu-helpers.php';
require_once get_template_directory() . '/inc/class-tonkla-walker.php';
require_once get_template_directory() . '/inc/class-tonkla-mobile-walker.php';
require_once get_template_directory() . '/inc/class-tonkla-toc.php';

/**
 * Language Switcher Helper
 */

/**
 * Resolve a Polylang flag image URL from language data.
 *
 * @param array       $lang_data Language row from pll_the_languages.
 * @param string|null $current_flag_url Optional current language flag URL.
 * @return string
 */
function tonkla_resolve_flag_url($lang_data, $current_flag_url = null)
{
    $flag_url = '';

    if (!empty($current_flag_url)) {
        $flag_url = $current_flag_url;
    } elseif (!empty($lang_data['flag_url'])) {
        $flag_url = $lang_data['flag_url'];
    } elseif (!empty($lang_data['flag'])) {
        if (filter_var($lang_data['flag'], FILTER_VALIDATE_URL)) {
            $flag_url = $lang_data['flag'];
        } elseif (preg_match('/src=["\']([^"\']+)["\']/', $lang_data['flag'], $matches)) {
            $flag_url = $matches[1];
        }
    }

    return $flag_url;
}

/**
 * Strip width/height attributes from a Polylang flag HTML snippet.
 *
 * @param string $flag_html Flag HTML.
 * @return string
 */
function tonkla_strip_flag_dimensions($flag_html)
{
    return preg_replace('/(width|height)=["\'][^"\']*["\']/', '', $flag_html);
}

/**
 * Render flag markup for language switcher buttons.
 *
 * @param bool   $show_flag Whether flags are enabled.
 * @param string $flag_url  Resolved flag URL.
 * @param array  $lang_data Language row.
 * @param string $alt       Alt text.
 * @param string $img_class Tailwind classes for <img>.
 * @param string $wrap_class Tailwind classes for raw-flag wrapper.
 * @return string
 */
function tonkla_render_flag_markup($show_flag, $flag_url, $lang_data, $alt, $img_class, $wrap_class)
{
    $html = '';

    if ($show_flag && $flag_url) {
        $html = '<img src="' . esc_url($flag_url) . '" alt="' . esc_attr($alt) . '" class="' . esc_attr($img_class) . '">';
    } elseif ($show_flag && !empty($lang_data['flag'])) {
        $raw_flag = tonkla_strip_flag_dimensions($lang_data['flag']);
        $html = '<span class="' . esc_attr($wrap_class) . '">' . wp_kses($raw_flag, array('img' => array('src' => true, 'alt' => true, 'class' => true, 'width' => true, 'height' => true, 'loading' => true))) . '</span>';
    }

    return $html;
}

/**
 * Render language switcher button markup.
 *
 * @param string $mode desktop|mobile
 */
function tonkla_language_switcher($mode = 'desktop')
{
    if (!function_exists('pll_the_languages') || !carbon_get_theme_option('enable_language_switcher') || count(pll_languages_list()) < 2) {
        return;
    }

    $show_flag = carbon_get_theme_option('show_language_flag');
    $languages = pll_the_languages(array('raw' => 1));
    $current_lang = pll_current_language('slug');
    $cur_lang_data = isset($languages[$current_lang]) ? $languages[$current_lang] : reset($languages);
    $flag_url = tonkla_resolve_flag_url($cur_lang_data, pll_current_language('flag_url'));
    $lang_slug = strtoupper($cur_lang_data['slug']);

    if ($mode === 'desktop') {
        $flag_html = tonkla_render_flag_markup(
            $show_flag,
            $flag_url,
            $cur_lang_data,
            $lang_slug,
            'w-6 h-auto rounded shadow-sm border border-slate-200',
            ' [&>img]:w-6 [&>img]:h-auto [&>img]:rounded [&>img]:shadow-sm [&>img]:border [&>img]:border-slate-200'
        );

        echo '<div class="relative group">';
        echo '<button id="lang-switch-btn" type="button" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100 transition border border-slate-200 focus-visible:border-brand">';
        echo $flag_html;
        echo '<span class="text-sm font-medium text-slate-600">' . esc_html($lang_slug) . '</span>';
        echo '<i class="fa-solid fa-globe text-xs text-slate-400"></i>';
        echo '</button>';
        echo '</div>';
        return;
    }

    if ($mode === 'mobile') {
        $flag_html = tonkla_render_flag_markup(
            $show_flag,
            $flag_url,
            $cur_lang_data,
            $lang_slug,
            'w-6 h-auto rounded shadow-sm',
            '[&>img]:w-6 [&>img]:h-auto [&>img]:rounded [&>img]:shadow-sm'
        );

        echo '<button id="mobile-lang-btn" type="button" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 font-medium w-full justify-center">';
        echo $flag_html;
        echo '<span>' . esc_html($cur_lang_data['name']) . '</span>';
        echo '<i class="fa-solid fa-globe text-xs text-slate-400 ml-1"></i>';
        echo '</button>';
    }
}

function tonkla_language_modal()
{
    if (!function_exists('pll_the_languages') || !carbon_get_theme_option('enable_language_switcher') || count(pll_languages_list()) < 2) {
        return;
    }

    $languages = pll_the_languages(array('raw' => 1));
    if (empty($languages)) {
        return;
    }

    ?>
    <div id="language-modal" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-0" id="lang-modal-backdrop">
        </div>

        <!-- Modal Content -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left border border-slate-200 transition-all sm:my-8 sm:w-full sm:max-w-sm opacity-0 translate-y-4 scale-95"
                    id="lang-modal-panel">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
                        <div class="text-center">
                            <h3 class="text-lg font-heading font-bold text-slate-900 mb-4">เลือกภาษา (Select Language)</h3>
                            <div class="space-y-3">
                                <?php
                                $show_flag = carbon_get_theme_option('show_language_flag');
                                foreach ($languages as $lang):
                                    $active_class = $lang['current_lang'] ? 'border-2 border-brand bg-brand/5' : 'border border-slate-200 hover:border-brand hover:bg-slate-50';
                                    $check_icon = $lang['current_lang'] ? '<i class="fa-solid fa-check text-brand text-xl"></i>' : '';
                                    $flag_url = tonkla_resolve_flag_url($lang);
                                    ?>
                                    <a href="<?php echo esc_url($lang['url']); ?>"
                                        class="w-full flex items-center justify-between p-3 rounded-xl transition group <?php echo esc_attr($active_class); ?>">
                                        <div class="flex items-center gap-3">
                                            <?php if ($show_flag): ?>
                                                <?php if ($flag_url): ?>
                                                    <img src="<?php echo esc_url($flag_url); ?>"
                                                        alt="<?php echo esc_attr($lang['name']); ?>"
                                                        class="w-8 h-auto rounded shadow-sm">
                                                <?php elseif (!empty($lang['flag'])):
                                                    $raw_flag = tonkla_strip_flag_dimensions($lang['flag']);
                                                    echo '<span class="[&>img]:w-8 [&>img]:h-auto [&>img]:rounded [&>img]:shadow-sm">' . wp_kses($raw_flag, array('img' => array('src' => true, 'alt' => true, 'class' => true, 'width' => true, 'height' => true, 'loading' => true))) . '</span>';
                                                endif; ?>
                                            <?php endif; ?>
                                            <div class="text-left">
                                                <div class="font-medium text-slate-900 group-hover:text-brand transition">
                                                    <?php echo esc_html($lang['name']); ?>
                                                </div>
                                                <div class="text-xs text-slate-500 uppercase tracking-wide">
                                                    <?php echo esc_html($lang['slug']); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php echo $check_icon; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button"
                            class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-900 border border-slate-300 hover:bg-slate-50 focus-visible:border-brand sm:mt-0 sm:w-auto"
                            id="close-lang-modal">
                            ยกเลิก (Cancel)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php

}
add_action('wp_footer', 'tonkla_language_modal');


/**
 * Register Widget Areas
 */
function tonkla_widgets_init()
{
    register_sidebar(array(
        'name' => esc_html__('Sidebar', 'tonkla'),
        'id' => 'sidebar-1',
        'description' => esc_html__('Add widgets here.', 'tonkla'),
        'before_widget' => '<section id="%1$s" class="widget %2$s mb-8 bg-white p-8 rounded-3xl shadow-sm border border-slate-100">',
        'after_widget' => '</section>',
        'before_title' => '<h2 class="widget-title text-lg font-heading font-bold text-slate-900 mb-6 border-b border-slate-100 pb-4">',
        'after_title' => '</h2>',
    ));

    register_sidebar(array(
        'name' => esc_html__('Single Post Sidebar', 'tonkla'),
        'id' => 'sidebar-post',
        'description' => esc_html__('Sidebar for Single Posts (Right Side).', 'tonkla'),
        'before_widget' => '<section id="%1$s" class="widget %2$s mb-8 bg-white p-8 rounded-3xl shadow-sm border border-slate-100">',
        'after_widget' => '</section>',
        'before_title' => '<h4 class="widget-title text-lg font-heading font-bold text-slate-900 mb-6 border-b border-slate-100 pb-4">',
        'after_title' => '</h4>',
    ));

    register_sidebar(array(
        'name' => esc_html__('Page Sidebar', 'tonkla'),
        'id' => 'sidebar-page',
        'description' => esc_html__('Sidebar for Standard Pages (Left Side).', 'tonkla'),
        'before_widget' => '<section id="%1$s" class="widget %2$s mb-8 bg-white p-6 rounded-xl shadow-sm border border-slate-100">',
        'after_widget' => '</section>',
        'before_title' => '<h4 class="widget-title font-heading font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">',
        'after_title' => '</h4>',
    ));
}
add_action('widgets_init', 'tonkla_widgets_init');

/**
 * Helper: Check if Table of Contents should be enabled
 * Considers Global Option + Post Override
 */
function tonkla_is_toc_enabled($post_id = null)
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    // 1. Get Post-specific override
    // Note: carbon_get_post_meta returns empty string if not set, or default value if defined via set_default_value
    $visibility = carbon_get_post_meta($post_id, 'toc_visibility');

    // 2. Handle Overrides
    if ($visibility === 'hide') {
        return false;
    }
    if ($visibility === 'show') {
        // Force show, even if global is off? 
        // Typically yes, "Show" implies explicitly enabling it.
        return true;
    }

    // 3. Fallback to Global Option (default behaviour)
    return carbon_get_theme_option('toc_enable');
}

/**
 * Calculate Reading Time
 */
function tonkla_reading_time()
{
    $content = get_post_field('post_content');
    $word_count = str_word_count(strip_tags($content));
    $readingtime = ceil($word_count / 200);

    // Fallback for Thai content which str_word_count might mishandle
    // Just a rough estimate if word count seems too low (e.g. < 5 words)
    if ($word_count < 5) {
        $char_count = mb_strlen(strip_tags($content));
        $readingtime = ceil($char_count / 1000); // Rough estimate for Thai chars
    }

    if ($readingtime == 0) {
        return 1;
    }
    return $readingtime;
}
