<?php defined( 'ABSPATH' ) || exit; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class('bg-slate-50 text-slate-800 flex flex-col min-h-screen'); ?>>
    <?php wp_body_open(); ?>

    <nav class="fixed w-full z-50 bg-white/95 backdrop-blur-md shadow-sm transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">

                <!-- Logo -->
                <a href="<?php echo esc_url(home_url('/')); ?>" class="flex-shrink-0 flex items-center group">
                    <?php tonkla_the_logo('header', 'h-10 w-auto'); ?>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex space-x-1 items-center">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'container' => false,
                        'items_wrap' => '%3$s', // No UL wrapper
                        'walker' => new Tonkla_Nav_Walker(),
                    ));
                    ?>
                </div>

                <!-- Right Side Buttons -->
                <div class="hidden lg:flex items-center gap-4">
                    <?php tonkla_language_switcher('desktop'); ?>

                    <?php if (carbon_get_theme_option('enable_client_login')): ?>
                        <div class="h-6 w-px bg-slate-200"></div>
                        <a href="<?php echo esc_url(carbon_get_theme_option('login_url') ?: '#'); ?>"
                            class="flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white rounded-full hover:bg-slate-800 transition text-sm font-heading font-medium shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            <i class="fa-solid fa-user-lock"></i> เข้าระบบลูกค้า
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <div class="lg:hidden flex items-center">
                    <button id="mobile-menu-btn" type="button"
                        class="text-slate-600 hover:text-brand border border-transparent focus-visible:border-brand p-2 rounded-lg"
                        aria-label="<?php echo esc_attr__('Open menu', 'tonkla'); ?>">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Panel (Hidden by default; open via .is-open) -->
        <div id="mobile-menu"
            class="lg:hidden bg-white border-t border-slate-100 shadow-xl absolute w-full left-0"
            aria-hidden="true">
            <div class="px-4 pt-2 pb-6 space-y-2">

                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container' => false,
                    'items_wrap' => '%3$s',
                    'walker' => new Tonkla_Mobile_Nav_Walker(),
                ));
                ?>

                <!-- Static Mobile Items -->
                <!-- Static Mobile Items -->
                <?php if (carbon_get_theme_option('enable_client_login')): ?>
                    <a href="<?php echo esc_url(carbon_get_theme_option('login_url') ?: '#'); ?>"
                        class="block px-3 py-2 rounded-md text-base font-medium text-white bg-slate-900 text-center mt-4">
                        <i class="fa-solid fa-user-lock mr-2"></i> เข้าระบบลูกค้า
                    </a>
                <?php endif; ?>

                <div class="mt-4 pt-4 border-t border-slate-100 flex justify-center">
                    <?php tonkla_language_switcher('mobile'); ?>
                </div>
            </div>
        </div>
    </nav>