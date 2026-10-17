<?php
/**
 * CTA Module
 */

defined( 'ABSPATH' ) || exit;

$module = get_query_var('module');

$cta_title = $module['title'];
$cta_text = $module['text'];
$cta_buttons = $module['buttons'];
$cta_buttons = $module['buttons'];
$bg_color = isset($module['bg_color']) ? $module['bg_color'] : 'brand';
$bg_class = 'bg-' . $bg_color;

// Determine text color based on background
$is_dark_bg = in_array($bg_color, array('dark', 'dark-gray', 'brand'));
$text_class = $is_dark_bg ? 'text-white' : 'text-slate-900';
$desc_class = $is_dark_bg ? 'text-slate-300' : 'text-slate-600';
$card_bg_class = $is_dark_bg ? 'bg-white/5 border border-white/10' : 'bg-slate-50 border border-slate-100';
?>

<section class="py-20 <?php echo esc_attr($bg_class . ' ' . $text_class); ?>">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="<?php echo esc_attr($card_bg_class); ?> rounded-3xl shadow-xl overflow-hidden">
            <div class="p-8 md:p-12 text-center">
                <h2 class="text-3xl md:text-4xl font-heading font-bold mb-4">
                    <?php echo esc_html($cta_title); ?>
                </h2>
                <p class="<?php echo esc_attr($desc_class); ?> mb-8 text-lg">
                    <?php echo nl2br(esc_html($cta_text)); ?>
                </p>

                <?php if ($cta_buttons): ?>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <?php foreach ($cta_buttons as $btn): ?>
                            <?php
                            $btn_link = isset($btn['link']) ? $btn['link'] : '#';
                            if (isset($btn['link_type']) && $btn['link_type'] === 'post' && !empty($btn['link_content'])) {
                                $btn_link = get_permalink($btn['link_content'][0]['id']);
                            }
                            ?>
                            <a href="<?php echo esc_url($btn_link); ?>"
                                class="px-8 py-4 <?php echo $btn['style'] === 'primary' ? 'bg-brand-dark hover:bg-brand text-white' : 'bg-slate-700 hover:bg-slate-600 text-white'; ?> font-heading font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-lg">
                                <?php echo esc_html($btn['text']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>