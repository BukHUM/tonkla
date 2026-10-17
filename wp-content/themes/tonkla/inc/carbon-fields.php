<?php
defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;


function tonkla_icon_colors()
{
    return array(
        'brand' => 'Brand Green',
        'slate' => 'Slate',
        'red' => 'Red',
        'orange' => 'Orange',
        'amber' => 'Amber',
        'yellow' => 'Yellow',
        'lime' => 'Lime',
        'green' => 'Green',
        'emerald' => 'Emerald',
        'teal' => 'Teal',
        'cyan' => 'Cyan',
        'sky' => 'Sky Blue',
        'blue' => 'Blue',
        'indigo' => 'Indigo',
        'violet' => 'Violet',
        'purple' => 'Purple',
        'fuchsia' => 'Fuchsia',
        'pink' => 'Pink',
        'rose' => 'Rose',
    );
}

/**
 * Shared background color options for page builder modules.
 *
 * @return array
 */
function tonkla_bg_color_options()
{
    return array(
        'white'      => __( 'ขาว', 'tonkla' ),
        'light'      => __( 'อ่อน', 'tonkla' ),
        'light-gray' => __( 'เทาอ่อน', 'tonkla' ),
        'gray'       => __( 'เทา', 'tonkla' ),
        'dark-gray'  => __( 'เทาเข้ม', 'tonkla' ),
        'dark'       => __( 'เข้ม', 'tonkla' ),
        'brand'      => __( 'สีแบรนด์', 'tonkla' ),
    );
}

/**
 * Button link type options (custom vs post).
 *
 * @return array
 */
function tonkla_button_link_type_options()
{
    return array(
        'custom' => __( 'URL กำหนดเอง', 'tonkla' ),
        'post'   => __( 'หน้า / โพสต์', 'tonkla' ),
    );
}

/**
 * Shared complex field item title template.
 *
 * @return string
 */
function tonkla_complex_item_title()
{
    return '<%- title || "Item" %>';
}

/**
 * Theme Options
 */
Container::make('theme_options', __('Dashboard', 'tonkla'))
    ->set_page_menu_title('TONKLA - IT')
    ->set_icon('dashicons-admin-site-alt3')
    ->add_fields(array(
        Field::make('html', 'dashboard_stats')
            ->set_html(function () {
                $theme = wp_get_theme();
                $php_version = phpversion();
                $wp_version = get_bloginfo('version');
                $memory_limit = ini_get('memory_limit');
                $max_execution_time = ini_get('max_execution_time');

                // Status Checks
                $php_ok = version_compare($php_version, '7.4', '>=');
                $mem_ok = (int) filter_var($memory_limit, FILTER_SANITIZE_NUMBER_INT) >= 128; // Simple check
            
                ob_start();
                $php_class = $php_ok ? 'is-ok' : 'is-bad';
                $mem_class = $mem_ok ? 'is-ok' : 'is-warn';
                ?>
    <div class="tonkla-dashboard">
        <div class="tonkla-dashboard__hero">
            <h1><?php esc_html_e( 'Welcome to Tonkla IT Theme', 'tonkla' ); ?></h1>
            <p>
                <?php
                printf(
                    /* translators: %s: theme version */
                    esc_html__( 'Version %s | One-Stop Service for Digital Business', 'tonkla' ),
                    esc_html( $theme->get( 'Version' ) )
                );
                ?>
            </p>
        </div>

        <div class="tonkla-dashboard__grid">
            <div class="tonkla-dashboard__card">
                <h3>
                    <span class="dashicons dashicons-desktop" aria-hidden="true"></span>
                    <?php esc_html_e( 'System Status', 'tonkla' ); ?>
                </h3>
                <ul class="tonkla-dashboard__list">
                    <li>
                        <span class="tonkla-dashboard__label"><?php esc_html_e( 'WordPress Version', 'tonkla' ); ?></span>
                        <span class="tonkla-dashboard__value"><?php echo esc_html( $wp_version ); ?></span>
                    </li>
                    <li>
                        <span class="tonkla-dashboard__label"><?php esc_html_e( 'PHP Version', 'tonkla' ); ?></span>
                        <span class="tonkla-dashboard__value <?php echo esc_attr( $php_class ); ?>"><?php echo esc_html( $php_version ); ?></span>
                    </li>
                    <li>
                        <span class="tonkla-dashboard__label"><?php esc_html_e( 'Memory Limit', 'tonkla' ); ?></span>
                        <span class="tonkla-dashboard__value <?php echo esc_attr( $mem_class ); ?>"><?php echo esc_html( $memory_limit ); ?></span>
                    </li>
                    <li>
                        <span class="tonkla-dashboard__label"><?php esc_html_e( 'Max Execution Time', 'tonkla' ); ?></span>
                        <span class="tonkla-dashboard__value"><?php echo esc_html( $max_execution_time ); ?>s</span>
                    </li>
                </ul>
            </div>

            <div class="tonkla-dashboard__card">
                <h3>
                    <span class="dashicons dashicons-admin-links" aria-hidden="true"></span>
                    <?php esc_html_e( 'Quick Actions', 'tonkla' ); ?>
                </h3>
                <div class="tonkla-dashboard__links">
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=crb_carbon_fields_container_header_footer' ) ); ?>">
                        <span class="dashicons dashicons-images-alt2" aria-hidden="true"></span>
                        <?php esc_html_e( 'Change Logo', 'tonkla' ); ?>
                    </a>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=crb_carbon_fields_container_social_media' ) ); ?>">
                        <span class="dashicons dashicons-share" aria-hidden="true"></span>
                        <?php esc_html_e( 'Update Social Links', 'tonkla' ); ?>
                    </a>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=crb_carbon_fields_container_tools' ) ); ?>">
                        <span class="dashicons dashicons-download" aria-hidden="true"></span>
                        <?php esc_html_e( 'Import Demo Content', 'tonkla' ); ?>
                    </a>
                </div>
            </div>

            <div class="tonkla-dashboard__card">
                <h3>
                    <span class="dashicons dashicons-admin-plugins" aria-hidden="true"></span>
                    <?php esc_html_e( 'Required Plugins', 'tonkla' ); ?>
                </h3>
                <ul class="tonkla-dashboard__list">
                    <?php
                    $required_plugins = array(
                        array(
                            'name'     => 'Carbon Fields',
                            'check'    => class_exists( '\\Carbon_Fields\\Carbon_Fields' ),
                            'url'      => 'https://wordpress.org/plugins/carbon-fields/',
                            'optional' => false,
                        ),
                        array(
                            'name'     => 'Polylang (Optional)',
                            'check'    => function_exists( 'pll_the_languages' ),
                            'url'      => 'https://wordpress.org/plugins/polylang/',
                            'optional' => true,
                        ),
                    );

                    foreach ( $required_plugins as $plugin ) {
                        $is_active   = $plugin['check'];
                        $is_optional = ! empty( $plugin['optional'] );
                        $status_class = 'is-bad';
                        $status_icon  = '✗';
                        $status_text  = __( 'Not Installed', 'tonkla' );

                        if ( $is_active ) {
                            $status_class = 'is-ok';
                            $status_icon  = '✓';
                            $status_text  = __( 'Active', 'tonkla' );
                        } elseif ( $is_optional ) {
                            $status_class = 'is-warn';
                            $status_icon  = '○';
                            $status_text  = __( 'Not Installed (Optional)', 'tonkla' );
                        }
                        ?>
                        <li>
                            <div>
                                <strong class="tonkla-dashboard__plugin-name"><?php echo esc_html( $plugin['name'] ); ?></strong>
                                <?php if ( ! $is_active ) : ?>
                                    <br>
                                    <a class="tonkla-dashboard__plugin-download" href="<?php echo esc_url( $plugin['url'] ); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php esc_html_e( 'Download', 'tonkla' ); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <span class="tonkla-dashboard__value <?php echo esc_attr( $status_class ); ?>">
                                <?php echo esc_html( $status_icon . ' ' . $status_text ); ?>
                            </span>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <?php
                return ob_get_clean();
            }),
    ));

Container::make('theme_options', __('Header & Footer', 'tonkla'))
    ->set_page_parent('crb_carbon_fields_container_dashboard.php')
    ->add_fields(array(
        Field::make('html', 'hf_heading')->set_html('<h2>Header & Footer Settings</h2>'),
        // Moved Logo Fields Here
        Field::make('image', 'main_logo', __('Main Logo', 'tonkla')),
        Field::make('image', 'footer_logo', __('Footer Logo', 'tonkla')),

        Field::make('separator', 'hf_sep_1', ''),

        Field::make('checkbox', 'enable_client_login', __('Enable Client Login Button', 'tonkla'))
            ->set_default_value(true),
        Field::make('text', 'login_url', __('Client Login URL', 'tonkla'))
            ->set_attribute('placeholder', 'https://portal.tonkla.co.th'),
        call_user_func(function () {
            $field = Field::make('checkbox', 'enable_language_switcher', __('Enable Language Switcher', 'tonkla'));

            $pll_active = function_exists('pll_the_languages');
            $lang_count = $pll_active ? count(pll_languages_list()) : 0;

            if (!$pll_active) {
                $field->set_classes('carbon-disabled-field');
                $field->set_help_text('<span style="color: red;">Note: "Polylang" plugin is not active. Language switcher is disabled.</span>');
                $field->set_default_value(false); // Force default off if possible
            } elseif ($lang_count < 2) {
                $field->set_classes('carbon-disabled-field');
                $field->set_help_text('<span style="color: orange;">Note: Less than 2 languages configured. Language switcher will be hidden.</span>');
                $field->set_default_value(false);
            } else {
                $field->set_help_text('<span style="color: green;">Polylang detected. The switcher will function automatically.</span>');
            }

            return $field;
        }),
        Field::make('checkbox', 'show_language_flag', __('Show Language Flag', 'tonkla'))
            ->set_default_value(true)
            ->set_help_text(__('Toggle the display of country flags in the language switcher.', 'tonkla')),
        Field::make('checkbox', 'enable_back_to_top', __('Enable Back to Top Button', 'tonkla'))
            ->set_default_value(true),
        Field::make('textarea', 'footer_description', __('Footer Description', 'tonkla')),
        Field::make('text', 'copyright_text', __('Copyright Text', 'tonkla')),
    ));

Container::make('theme_options', __('Social Media', 'tonkla'))
    ->set_page_parent('crb_carbon_fields_container_dashboard.php') // Updated parent slug
    ->add_fields(array(
        Field::make('html', 'social_heading')->set_html('<h2>Contact & Social</h2>'),
        Field::make('text', 'phone_number', __('Phone Number', 'tonkla')),
        Field::make('text', 'line_url', __('Line URL', 'tonkla')),
        Field::make('text', 'facebook_url', __('Facebook URL', 'tonkla')),
        Field::make('text', 'twitter_url', __('Twitter URL', 'tonkla')),
        Field::make('text', 'instagram_url', __('Instagram URL', 'tonkla')),
    ));

Container::make('theme_options', __('Custom Scripts', 'tonkla')) // Renamed from Scripts & Tracking
    ->set_page_parent('crb_carbon_fields_container_dashboard.php')
    ->add_fields(array(
        Field::make('html', 'scripts_heading')->set_html('<h2>Custom Scripts (Header/Footer)</h2>'),
        Field::make('textarea', 'header_scripts', __('Header Scripts', 'tonkla'))
            ->set_help_text('Add scripts to the <head> tag. e.g. Google Analytics'),
        Field::make('textarea', 'footer_scripts', __('Footer Scripts', 'tonkla')),
    ));

Container::make('theme_options', __('Table of Contents', 'tonkla'))
    ->set_page_parent('crb_carbon_fields_container_dashboard.php')
    ->add_fields(array(
        Field::make('checkbox', 'toc_enable', __('Enable Table of Contents', 'tonkla'))
            ->set_default_value(true),
        Field::make('text', 'toc_title', __('Title', 'tonkla'))
            ->set_default_value('สารบัญ'),
        Field::make('select', 'toc_position', __('Position', 'tonkla'))
            ->add_options(array(
                'left' => 'Left',
                'right' => 'Right',
                'content' => 'Inside Content (Top)',
            ))
            ->set_default_value('left'),
        Field::make('multiselect', 'toc_depth', __('Heading Levels', 'tonkla'))
            ->add_options(array(
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'h5' => 'H5',
                'h6' => 'H6',
            ))
            ->set_default_value(array('h2', 'h3', 'h4')),
        Field::make('checkbox', 'toc_auto_number', __('Enable Auto Numbering', 'tonkla'))
            ->set_default_value(false),
    ));

Container::make('theme_options', __('Customer Service', 'tonkla'))
    ->set_page_parent('crb_carbon_fields_container_dashboard.php')
    ->add_fields(array(
        Field::make('checkbox', 'chat_enable', __('Enable Chat Widget', 'tonkla'))
            ->set_default_value(true),
        Field::make('select', 'chat_provider', __('Provider', 'tonkla'))
            ->add_options(array(
                'facebook' => 'Facebook Messenger',
                'line' => 'LINE Official Account',
                'custom' => 'Custom / Other',
            )),
        Field::make('textarea', 'chat_script', __('Chat Script / Embed Code', 'tonkla'))
            ->set_help_text('Paste the JS SDK or iframe code here.'),
    ));

Container::make('theme_options', __('Tools', 'tonkla'))
    ->set_page_parent('crb_carbon_fields_container_dashboard.php')
    ->add_fields(array(
        Field::make('html', 'demo_import_ui')
            ->set_html(function () {
                require_once get_template_directory() . '/inc/admin-demo-import-ui.php';
                return tonkla_render_demo_import_ui();
            }),
    ));

Container::make('theme_options', __('Legal (PDPA)', 'tonkla'))
    ->set_page_parent('crb_carbon_fields_container_dashboard.php')
    ->add_fields(array(
        Field::make('checkbox', 'pdpa_enable', __('Enable Cookie Consent Banner', 'tonkla'))
            ->set_default_value(true),

        Field::make('select', 'pdpa_position', __('Banner Position', 'tonkla'))
            ->add_options(array(
                'bottom-left' => 'Floating Bottom Left (Default)',
                'bottom-right' => 'Floating Bottom Right',
                'bottom-bar' => 'Full Width Bottom Bar',
                'center' => 'Centered Modal',
            ))
            ->set_default_value('bottom-left'),

        Field::make('rich_text', 'pdpa_text', __('Consent Text', 'tonkla'))
            ->set_default_value('เราใช้คุกกี้เพื่อเพิ่มประสิทธิภาพและประสบการณ์ที่ดีในการใช้งานเว็บไซต์ คุณสามารถศึกษารายละเอียดได้ที่ <a href="#">นโยบายความเป็นส่วนตัว</a>'),

        Field::make('text', 'pdpa_accept_text', __('Accept Button Text', 'tonkla'))
            ->set_default_value('ยอมรับทั้งหมด'),

        Field::make('text', 'pdpa_reject_text', __('Reject Button Text', 'tonkla'))
            ->set_default_value('ปฏิเสธ/ปิด'),

        Field::make('association', 'pdpa_policy_page', __('Privacy Policy Page', 'tonkla'))
            ->set_types(array(
                array(
                    'type' => 'post',
                    'post_type' => 'page',
                )
            ))
            ->set_max(1)
            ->set_help_text('Select the page that contains your privacy policy.'),

        Field::make('textarea', 'pdpa_scripts', __('Consent-Required Scripts', 'tonkla'))
            ->set_help_text('<strong>Important:</strong> Paste real Analytics / Pixel embed codes here (after consent only). Leave empty until you have a real Measurement ID — do not leave placeholder IDs like UA-XXXXX-Y.'),
    ));

Container::make('theme_options', __('Advertising', 'tonkla'))
    ->set_page_parent('crb_carbon_fields_container_dashboard.php')
    ->add_fields(array(
        Field::make('html', 'ads_heading')->set_html('<h2>Advertising / Banners</h2><p class="description">Manage ad slots across the website. Supports HTML, Images, or Ad Scripts (e.g. Google AdSense).</p>'),

        Field::make('separator', 'ad_sidebar_sep', __('Sidebar Ad (Single Post)', 'tonkla')),
        Field::make('checkbox', 'ad_sidebar_enable', __('Enable Sidebar Ad', 'tonkla'))
            ->set_default_value(true),
        Field::make('select', 'ad_sidebar_type', __('Ad Type', 'tonkla'))
            ->add_options(array(
                'code' => 'Custom Code / AdSense',
                'image' => 'Image Banner',
            ))
            ->set_default_value('code'),

        // Code Option
        Field::make('textarea', 'ad_sidebar_code', __('Ad Code', 'tonkla'))
            ->set_help_text('Paste your Google AdSense code or any HTML script here.')
            ->set_conditional_logic(array(
                array('field' => 'ad_sidebar_type', 'value' => 'code')
            )),

        // Image Option
        Field::make('image', 'ad_sidebar_image', __('Banner Image', 'tonkla'))
            ->set_conditional_logic(array(
                array('field' => 'ad_sidebar_type', 'value' => 'image')
            )),
        Field::make('text', 'ad_sidebar_link', __('Link URL', 'tonkla'))
            ->set_conditional_logic(array(
                array('field' => 'ad_sidebar_type', 'value' => 'image')
            )),

        Field::make('checkbox', 'ad_sidebar_label', __('Show "Advertising" Label', 'tonkla'))
            ->set_default_value(true),
    ));

add_action('admin_notices', function () {
    if (!isset($_GET['import_status'])) {
        return;
    }
    $status = sanitize_key(wp_unslash($_GET['import_status']));
    if ('success' === $status) {
        echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__('Import Complete!', 'tonkla') . '</strong> ' . esc_html__('All selected pages have been created/updated successfully.', 'tonkla') . ' <a href="' . esc_url(home_url('/')) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('View Site', 'tonkla') . ' &rarr;</a></p></div>';
    } elseif ('empty' === $status) {
        echo '<div class="notice notice-warning is-dismissible"><p><strong>' . esc_html__('No pages selected.', 'tonkla') . '</strong> ' . esc_html__('Please select at least one page to import.', 'tonkla') . '</p></div>';
    }
});

/**
 * Menu Item Fields
 */
Container::make('nav_menu_item', __('Menu Settings', 'tonkla'))
    ->add_fields(array(
        Field::make('text', 'icon_class', __('ไอคอน (FontAwesome)', 'tonkla'))
            ->set_classes('tonkla-icon-field')
            ->set_help_text(__('กด “เลือกไอคอน” หรือพิมพ์คลาส เช่น fa-solid fa-house', 'tonkla')),
        Field::make('select', 'icon_color', __('สีไอคอน', 'tonkla'))
            ->add_options(tonkla_icon_colors())
            ->set_classes('tonkla-color-select')
            ->set_default_value('slate'),
        Field::make('text', 'description', __('Description', 'tonkla')),
        Field::make('text', 'badge_text', __('Badge Text', 'tonkla')),
        Field::make('select', 'badge_color', __('Badge Color', 'tonkla'))
            ->add_options(tonkla_icon_colors()),

        // Dropdown Footer Settings
        Field::make('separator', 'dropdown_footer_sep', __('Dropdown Footer', 'tonkla')),
        Field::make('checkbox', 'enable_dropdown_footer', __('เปิดใช้ปุ่มด้านล่าง Dropdown', 'tonkla'))
            ->set_help_text('แสดงปุ่ม "ดูทั้งหมด" ที่ด้านล่างของ dropdown menu (สำหรับเมนูที่มีรายการย่อย)')
            ->set_default_value(false),

        Field::make('text', 'dropdown_footer_text', __('ข้อความปุ่ม', 'tonkla'))
            ->set_default_value('ดูบริการทั้งหมด')
            ->set_conditional_logic(array(
                array('field' => 'enable_dropdown_footer', 'value' => true)
            )),

        Field::make('text', 'dropdown_footer_url', __('URL ปุ่ม', 'tonkla'))
            ->set_help_text('ลิงก์ที่ต้องการให้ไปเมื่อคลิกปุ่ม "ดูทั้งหมด"')
            ->set_conditional_logic(array(
                array('field' => 'enable_dropdown_footer', 'value' => true)
            )),
    ));

/**
 * Page Builder (Flexible Content) — defined in inc/builder/page-modules.php
 */
require_once get_template_directory() . '/inc/builder/page-modules.php';
tonkla_register_page_builder_fields();

/**
 * Page/Post Display Settings
 */
Container::make('post_meta', __('Display Settings', 'tonkla'))
    ->where('post_type', '=', 'page')
    ->or_where('post_type', '=', 'post')
    ->add_fields(array(
        Field::make('select', 'toc_visibility', __('Table of Contents', 'tonkla'))
            ->add_options(array(
                'default' => 'Default (Follow Theme Options)',
                'show' => 'Show',
                'hide' => 'Hide',
            ))
            ->set_default_value('default')
            ->set_help_text('Override the global Table of Contents setting for this specific page/post.'),
    ));

// Fix first submenu item label
add_action('admin_menu', 'tonkla_fix_dashboard_label', 999);
function tonkla_fix_dashboard_label()
{
    global $submenu;
    $parent_slug = 'crb_carbon_fields_container_dashboard.php';

    if (isset($submenu[$parent_slug]) && isset($submenu[$parent_slug][0])) {
        $submenu[$parent_slug][0][0] = 'Dashboard';
    }
}

/**
 * Category/Term Meta
 */
Container::make('term_meta', __('Category Settings', 'tonkla'))
    ->where('term_taxonomy', '=', 'category')
    ->add_fields(array(
        Field::make('text', 'custom_title', __('Custom Title', 'tonkla'))
            ->set_help_text('Override the default category name (e.g. "Knowledge Base" instead of "Uncategorized")'),
        Field::make('textarea', 'custom_description', __('Custom Description', 'tonkla'))
            ->set_rows(3),
        Field::make('text', 'badge_text', __('Badge Text', 'tonkla'))
            ->set_default_value('KNOWLEDGE BASE'),
    ));

