<?php
/**
 * Newsletter Functionality
 * 
 * Handles creation of Subscriber CPT, simple AJAX submission,
 * and double opt-in verification.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TONKLA_NEWSLETTER_TOKEN_META', '_newsletter_token');
define('TONKLA_NEWSLETTER_TOKEN_TIME_META', '_tonkla_newsletter_token_time');
define('TONKLA_NEWSLETTER_TOKEN_TTL', DAY_IN_SECONDS);
define('TONKLA_NEWSLETTER_RATE_LIMIT', 5);
define('TONKLA_NEWSLETTER_RATE_WINDOW', 10 * MINUTE_IN_SECONDS);

// 1. Register Custom Post Type for Subscribers
function tonkla_register_newsletter_cpt()
{
    $labels = array(
        'name' => _x('Newsletter', 'Post Type General Name', 'tonkla'),
        'singular_name' => _x('Subscriber', 'Post Type Singular Name', 'tonkla'),
        'menu_name' => __('Newsletter', 'tonkla'),
        'name_admin_bar' => __('Subscriber', 'tonkla'),
        'archives' => __('Item Archives', 'tonkla'),
        'attributes' => __('Item Attributes', 'tonkla'),
        'parent_item_colon' => __('Parent Item:', 'tonkla'),
        'all_items' => __('All Subscribers', 'tonkla'),
        'add_new_item' => __('Add New Subscriber', 'tonkla'),
        'add_new' => __('Add New', 'tonkla'),
        'new_item' => __('New Subscriber', 'tonkla'),
        'edit_item' => __('Edit Subscriber', 'tonkla'),
        'update_item' => __('Update Subscriber', 'tonkla'),
        'view_item' => __('View Subscriber', 'tonkla'),
        'view_items' => __('View Subscribers', 'tonkla'),
        'search_items' => __('Search Subscriber', 'tonkla'),
        'not_found' => __('Not found', 'tonkla'),
        'not_found_in_trash' => __('Not found in Trash', 'tonkla'),
    );
    $args = array(
        'label' => __('Subscriber', 'tonkla'),
        'labels' => $labels,
        'supports' => array('title', 'custom-fields'), // title = email
        'hierarchical' => false,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'crb_carbon_fields_container_dashboard.php', // Under TONKLA - IT
        'show_in_admin_bar' => false,
        'show_in_nav_menus' => false,
        'can_export' => true,
        'has_archive' => false,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'capability_type' => 'post',
        'capabilities' => array(
            'create_posts' => false, // Disable creating new ones via UI if desired, but we might want manual add
        ),
        'map_meta_cap' => true,
    );
    register_post_type('newsletter', $args);
}
add_action('init', 'tonkla_register_newsletter_cpt');

/**
 * Find a Carbon Fields submenu row by slug fragment.
 *
 * @param array  $existing Existing submenu rows.
 * @param string $needle   Slug fragment to match.
 * @return array|null
 */
function tonkla_find_submenu_item(array $existing, $needle)
{
    foreach ($existing as $item) {
        if (!empty($item[2]) && strpos($item[2], $needle) !== false) {
            return $item;
        }
    }
    return null;
}

// Add Newsletter submenu explicitly and reorder all submenus
add_action('admin_menu', 'tonkla_newsletter_submenu', 999);
function tonkla_newsletter_submenu()
{
    global $submenu;

    remove_submenu_page('crb_carbon_fields_container_dashboard.php', 'edit.php?post_type=newsletter');

    $parent = 'crb_carbon_fields_container_dashboard.php';
    $existing = isset($submenu[$parent]) ? $submenu[$parent] : array();
    $submenu[$parent] = array();

    $ordered = array(
        array(
            __('Dashboard', 'tonkla'),
            'manage_options',
            'crb_carbon_fields_container_dashboard.php',
            __('Dashboard', 'tonkla'),
        ),
    );

    $fragments = array(
        'header_footer',
        'social_media',
        'table_of_contents',
        'customer_service',
        'legal_pdpa',
        'advertising',
    );

    foreach ($fragments as $fragment) {
        $item = tonkla_find_submenu_item($existing, $fragment);
        if ($item) {
            $ordered[] = $item;
        }
    }

    $ordered[] = array(
        __('Newsletter', 'tonkla'),
        'manage_options',
        'edit.php?post_type=newsletter',
        __('Newsletter', 'tonkla'),
    );

    foreach (array('custom_scripts', 'tools') as $fragment) {
        $item = tonkla_find_submenu_item($existing, $fragment);
        if ($item) {
            $ordered[] = $item;
        }
    }

    $submenu[$parent] = $ordered;
}

// 2. Add Status Columns to Admin List
add_filter('manage_newsletter_posts_columns', 'tonkla_newsletter_columns');
function tonkla_newsletter_columns($columns)
{
    $columns = array(
        'cb' => $columns['cb'],
        'title' => __('Email Address', 'tonkla'), // Retitle 'Title' to 'Email Address'
        'status' => __('Status', 'tonkla'),
        'date' => $columns['date']
    );
    return $columns;
}

add_action('manage_newsletter_posts_custom_column', 'tonkla_newsletter_custom_column', 10, 2);
function tonkla_newsletter_custom_column($column, $post_id)
{
    if ($column === 'status') {
        $status = get_post_status($post_id);
        if ($status === 'publish') {
            echo '<span class="tonkla-nl-status tonkla-nl-status--active">' . esc_html__('Active', 'tonkla') . '</span>';
        } else {
            echo '<span class="tonkla-nl-status tonkla-nl-status--pending">' . esc_html__('Pending', 'tonkla') . '</span>';
        }
    }
}

// 3. AJAX Submission Handler
add_action('wp_ajax_tonkla_newsletter_subscribe', 'tonkla_handle_newsletter_subscribe');
add_action('wp_ajax_nopriv_tonkla_newsletter_subscribe', 'tonkla_handle_newsletter_subscribe');

function tonkla_handle_newsletter_subscribe()
{
    // Check Nonce
    if (!check_ajax_referer('tonkla_newsletter_nonce', 'security', false)) {
        wp_send_json_error(array('message' => __('Invalid Request', 'tonkla')));
    }

    // Check Honeypot
    if (!empty($_POST['hp_field'])) {
        // Bot detected
        wp_send_json_error(array('message' => __('Bot detected', 'tonkla')));
    }

    if (tonkla_newsletter_is_rate_limited()) {
        wp_send_json_error(array('message' => __('Too many requests. Please try again later.', 'tonkla')), 429);
    }

    // Validate Email
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    if (!is_email($email)) {
        wp_send_json_error(array('message' => __('Invalid email address', 'tonkla')));
    }

    $existing = tonkla_newsletter_find_subscriber($email);

    if ($existing) {
        if ($existing->post_status === 'publish') {
            wp_send_json_error(array('message' => __('This email is already subscribed.', 'tonkla')));
        } else {
            // Resend verification
            tonkla_send_verification_email($existing->ID, $email);
            wp_send_json_success(array('message' => __('Verification email has been resent. Please check your inbox.', 'tonkla')));
        }
    } else {
        // Create new subscriber (Pending)
        $post_id = wp_insert_post(array(
            'post_title' => $email,
            'post_type' => 'newsletter',
            'post_status' => 'pending', // Pending verification
        ));

        if ($post_id && !is_wp_error($post_id)) {
            tonkla_send_verification_email($post_id, $email);
            wp_send_json_success(array('message' => __('Please check your email to confirm your subscription.', 'tonkla')));
        } else {
            wp_send_json_error(array('message' => __('Could not save subscription. Try again.', 'tonkla')));
        }
    }
}

/**
 * Find a subscriber post by email (stored as post title).
 *
 * @param string $email Sanitized email.
 * @return WP_Post|null
 */
function tonkla_newsletter_find_subscriber($email)
{
    $query = new WP_Query(array(
        'post_type'              => 'newsletter',
        'post_status'            => array('publish', 'pending'),
        'title'                  => $email,
        'posts_per_page'         => 1,
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ));

    return $query->have_posts() ? $query->posts[0] : null;
}

/**
 * Per-IP rate limit for subscribe requests. Counts the current request.
 *
 * @return bool True when the limit is exceeded.
 */
function tonkla_newsletter_is_rate_limited()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown';
    $key = 'tonkla_nl_rl_' . md5($ip);
    $count = (int) get_transient($key);

    if ($count >= TONKLA_NEWSLETTER_RATE_LIMIT) {
        return true;
    }

    set_transient($key, $count + 1, TONKLA_NEWSLETTER_RATE_WINDOW);
    return false;
}

// 4. Send Verification Email
function tonkla_send_verification_email($post_id, $email)
{
    // Generate Token
    $token = wp_generate_password(32, false);
    update_post_meta($post_id, TONKLA_NEWSLETTER_TOKEN_META, $token);
    update_post_meta($post_id, TONKLA_NEWSLETTER_TOKEN_TIME_META, time());

    // Build Link
    $verify_link = add_query_arg(array(
        'action' => 'verify_newsletter',
        'token' => $token,
        'id' => $post_id
    ), home_url('/'));

    // Email Content
    $subject = __('Confirm your subscription to Tonkla IT', 'tonkla');
    $message = '<h3>' . __('Welcome!', 'tonkla') . '</h3>';
    $message .= '<p>' . __('Please click the link below to verify your email address:', 'tonkla') . '</p>';
    $message .= '<p><a href="' . esc_url($verify_link) . '">' . __('Confirm Subscription', 'tonkla') . '</a></p>';
    $message .= '<p>' . __('If you did not request this, please ignore this email.', 'tonkla') . '</p>';

    $headers = array('Content-Type: text/html; charset=UTF-8');

    wp_mail($email, $subject, $message, $headers);
}

// 5. Handle Verification Link
add_action('init', 'tonkla_verify_newsletter_token');
function tonkla_verify_newsletter_token()
{
    // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Email link; the one-time token is the verification.
    $action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';
    if ('verify_newsletter' !== $action || !isset($_GET['token'], $_GET['id'])) {
        return;
    }

    $post_id = absint($_GET['id']);
    $token = sanitize_text_field(wp_unslash($_GET['token']));
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    if (!$post_id || 'newsletter' !== get_post_type($post_id)) {
        return;
    }

    $stored_token = (string) get_post_meta($post_id, TONKLA_NEWSLETTER_TOKEN_META, true);
    $token_time = (int) get_post_meta($post_id, TONKLA_NEWSLETTER_TOKEN_TIME_META, true);

    if ('' === $stored_token || !hash_equals($stored_token, $token)) {
        return;
    }

    delete_post_meta($post_id, TONKLA_NEWSLETTER_TOKEN_META);
    delete_post_meta($post_id, TONKLA_NEWSLETTER_TOKEN_TIME_META);

    if (!$token_time || (time() - $token_time) > TONKLA_NEWSLETTER_TOKEN_TTL) {
        wp_safe_redirect(add_query_arg('newsletter_activated', 'expired', home_url('/')));
        exit;
    }

    wp_update_post(array(
        'ID' => $post_id,
        'post_status' => 'publish'
    ));

    wp_safe_redirect(add_query_arg('newsletter_activated', '1', home_url('/')));
    exit;
}

/**
 * Activation result passed to newsletter.js after the verification redirect.
 *
 * @return string '1' (activated), 'expired', or '' (none).
 */
function tonkla_newsletter_activation_status()
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display flag.
    $flag = isset($_GET['newsletter_activated']) ? sanitize_key(wp_unslash($_GET['newsletter_activated'])) : '';
    return in_array($flag, array('1', 'expired'), true) ? $flag : '';
}
