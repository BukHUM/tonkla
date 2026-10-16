<?php
/**
 * Contact Section Module
 * Displays Contact Form (CF7) and Contact Info
 */

defined( 'ABSPATH' ) || exit;

$module = get_query_var('module');
$form_shortcode = isset($module['form_shortcode']) ? $module['form_shortcode'] : '';
?>

<section class="py-20 bg-white -mt-10 relative z-20 rounded-t-3xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden flex flex-col lg:flex-row">

            <!-- Contact Form (Left) -->
            <div class="lg:w-3/5 p-8 md:p-12">
                <h2 class="text-3xl font-heading font-bold text-slate-900 mb-6">ส่งข้อความถึงเรา</h2>
                <?php if ($form_shortcode): ?>
                    <div class="cf7-custom-style">
                        <?php echo do_shortcode($form_shortcode); ?>
                    </div>
                <?php else: ?>
                    <div class="p-4 bg-yellow-50 text-yellow-700 rounded-lg">
                        Please enter Contact Form 7 Shortcode in the module settings.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Contact Info (Right) -->
            <div
                class="lg:w-2/5 bg-slate-900 p-8 md:p-12 text-white flex flex-col justify-between relative overflow-hidden">
                <!-- Background Pattern -->
                <div
                    class="absolute inset-0 bg-brand/10 bg-[url('<?php echo get_template_directory_uri(); ?>/assets/images/grid-pattern.png')] opacity-20">
                </div>

                <div class="relative z-10">
                    <h3 class="text-2xl font-heading font-bold mb-8">
                        <?php echo esc_html($module['info_title']); ?>
                    </h3>

                    <ul class="space-y-8">
                        <!-- Address -->
                        <?php if (!empty($module['address'])): ?>
                            <li class="flex items-start gap-4">
                                <div
                                    class="w-12 h-12 bg-white/10 rounded-lg flex items-center justify-center text-brand flex-shrink-0 text-xl">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-lg mb-1">
                                        <?php echo esc_html($module['address_title']); ?>
                                    </h4>
                                    <p class="text-slate-300 text-sm leading-relaxed">
                                        <?php echo nl2br(esc_html($module['address'])); ?>
                                    </p>
                                </div>
                            </li>
                        <?php endif; ?>

                        <!-- Phone -->
                        <?php if (!empty($module['phone_office']) || !empty($module['phone_hotline'])): ?>
                            <li class="flex items-start gap-4">
                                <div
                                    class="w-12 h-12 bg-white/10 rounded-lg flex items-center justify-center text-brand flex-shrink-0 text-xl">
                                    <i class="fa-solid fa-phone"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-lg mb-1">
                                        <?php echo esc_html($module['phone_title']); ?>
                                    </h4>
                                    <?php if (!empty($module['phone_office'])): ?>
                                        <p class="text-slate-300 text-sm mb-1">
                                            <?php echo esc_html($module['phone_office']); ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($module['phone_hotline'])): ?>
                                        <p class="text-slate-300 text-sm">
                                            <?php echo esc_html($module['phone_hotline']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endif; ?>

                        <!-- Email -->
                        <?php if (!empty($module['emails'])): ?>
                            <li class="flex items-start gap-4">
                                <div
                                    class="w-12 h-12 bg-white/10 rounded-lg flex items-center justify-center text-brand flex-shrink-0 text-xl">
                                    <i class="fa-regular fa-envelope"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-lg mb-1">
                                        <?php echo esc_html($module['email_title']); ?>
                                    </h4>
                                    <?php foreach ($module['emails'] as $email): ?>
                                        <p class="text-slate-300 text-sm mb-1">
                                            <a href="mailto:<?php echo esc_attr($email['email']); ?>"
                                                class="hover:text-white transition">
                                                <?php echo esc_html($email['email']); ?>
                                            </a>
                                        </p>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                        <?php endif; ?>

                        <!-- LINE -->
                        <?php if (!empty($module['line_id'])): ?>
                            <li class="flex items-start gap-4">
                                <div
                                    class="w-12 h-12 bg-white/10 rounded-lg flex items-center justify-center text-brand flex-shrink-0 text-xl">
                                    <i class="fa-brands fa-line"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-lg mb-1">
                                        <?php echo esc_html($module['line_title']); ?>
                                    </h4>
                                    <p class="text-slate-300 text-sm">
                                        <?php echo esc_html($module['line_id']); ?>
                                    </p>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Socials -->
                <?php if (!empty($module['socials'])): ?>
                    <div class="relative z-10 mt-12 pt-8 border-t border-white/10">
                        <h4 class="font-bold mb-4 text-sm uppercase tracking-wider text-slate-400">Follow Us</h4>
                        <div class="flex gap-4">
                            <?php foreach ($module['socials'] as $social): ?>
                                <a href="<?php echo esc_url($social['link']); ?>" target="_blank"
                                    class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-brand transition text-white">
                                    <i class="<?php echo esc_attr($social['icon_class']); ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>