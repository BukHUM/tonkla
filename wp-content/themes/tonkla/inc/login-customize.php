<?php
/**
 * Custom Login Page — Split Layout
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue Login Styles
 */
function tonkla_login_assets()
{
    wp_enqueue_style(
        'tonkla-fonts',
        'https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&family=Sarabun:wght@300;400;500;600&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'tonkla-login-style',
        get_template_directory_uri() . '/assets/css/login.css',
        array(),
        '2.0.2'
    );
}
add_action('login_enqueue_scripts', 'tonkla_login_assets');

/**
 * Brand panel (left side of split)
 */
function tonkla_login_brand_panel()
{
    $site_name = get_bloginfo('name');
    $logo_url  = get_template_directory_uri() . '/assets/images/logo-tonkla-landscape.png';
    ?>
    <aside class="tonkla-login-brand" aria-hidden="false">
        <div class="tonkla-login-brand__glow tonkla-login-brand__glow--a"></div>
        <div class="tonkla-login-brand__glow tonkla-login-brand__glow--b"></div>
        <div class="tonkla-login-brand__grid"></div>

        <div class="tonkla-login-brand__inner">
            <a class="tonkla-login-brand__logo" href="<?php echo esc_url(home_url('/')); ?>">
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>" width="220" height="56">
            </a>

            <div class="tonkla-login-brand__copy">
                <p class="tonkla-login-brand__eyebrow">Admin Console</p>
                <h2 class="tonkla-login-brand__title">
                    Infrastructure<br>
                    <span>ที่ไว้ใจได้</span>
                </h2>
                <p class="tonkla-login-brand__desc">
                    เข้าสู่ระบบเพื่อจัดการ Hosting, Server และบริการดิจิทัลของ <?php echo esc_html($site_name); ?>
                </p>
            </div>

            <div class="tonkla-login-brand__terminal" aria-hidden="true">
                <div class="tonkla-login-brand__terminal-bar">
                    <span></span><span></span><span></span>
                    <em>root@tonkla:~</em>
                </div>
                <div class="tonkla-login-brand__terminal-body">
                    <p><span class="c-green">➜</span> <span class="c-sky">systemctl</span> status nginx</p>
                    <p class="dim">● nginx.service — Active: <span class="c-green">running</span></p>
                    <p><span class="c-green">➜</span> <span class="c-sky">uptime</span></p>
                    <p class="dim">load average: <span class="c-amber">0.12</span>, 0.08, 0.05</p>
                    <p class="cursor-line"><span class="c-green">➜</span> <span class="blink">_</span></p>
                </div>
            </div>

            <ul class="tonkla-login-brand__meta">
                <li>Cloud Hosting</li>
                <li>Dedicated Server</li>
                <li>Cyber Security</li>
                <li>App Development</li>
            </ul>
        </div>
    </aside>
    <?php
}
add_action('login_header', 'tonkla_login_brand_panel');

/**
 * Change Login Logo URL
 */
function tonkla_login_logo_url()
{
    return home_url('/');
}
add_filter('login_headerurl', 'tonkla_login_logo_url');

/**
 * Change Login Logo Title
 */
function tonkla_login_headertext()
{
    return get_bloginfo('name');
}
add_filter('login_headertext', 'tonkla_login_headertext');
