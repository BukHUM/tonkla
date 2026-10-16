<?php
/**
 * Pricing Table Module
 */

defined( 'ABSPATH' ) || exit;

$module = get_query_var('module');

$plans = $module['plans'];
$section_title = $module['section_title'];
$section_description = $module['section_description'];

// Background Color
$bg_color = isset($module['bg_color']) ? $module['bg_color'] : 'white';
$bg_class = 'bg-' . $bg_color;
?>

<section class="py-20 <?php echo esc_attr($bg_class); ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <?php if ($section_title): ?>
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-heading font-bold text-slate-900 mt-2">
                    <?php echo esc_html($section_title); ?>
                </h2>
                <p class="text-slate-600 mt-4 max-w-2xl mx-auto"><?php echo esc_html($section_description); ?></p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <?php if ($plans):
                foreach ($plans as $plan): ?>
                    <div
                        class="bg-white rounded-2xl <?php echo $plan['recommended'] ? 'shadow-xl border-2 border-brand transform md:-translate-y-4' : 'shadow-sm border border-slate-200'; ?> overflow-hidden hover:shadow-xl transition relative">
                        <?php if ($plan['recommended']): ?>
                            <div class="absolute top-0 right-0 bg-brand text-white text-xs font-bold px-3 py-1 rounded-bl-lg">
                                POPULAR</div>
                        <?php endif; ?>

                        <div class="p-8">
                            <h3 class="text-lg font-bold <?php echo $plan['recommended'] ? 'text-brand' : 'text-slate-900'; ?>">
                                <?php echo esc_html($plan['name']); ?>
                            </h3>
                            <div class="my-4 flex items-baseline">
                                <span class="text-4xl font-bold text-slate-900"><?php echo esc_html($plan['price']); ?></span>
                                <span class="text-slate-500 ml-2"><?php echo esc_html($plan['period']); ?></span>
                            </div>
                            <p class="text-slate-500 text-sm mb-6"><?php echo esc_html($plan['description']); ?></p>
                            <?php
                            $btn_link = isset($plan['button_link']) ? $plan['button_link'] : '#';
                            if (isset($plan['button_link_type']) && $plan['button_link_type'] === 'post' && !empty($plan['button_link_content'])) {
                                $btn_link = get_permalink($plan['button_link_content'][0]['id']);
                            }
                            ?>
                            <a href="<?php echo esc_url($btn_link); ?>"
                                class="block w-full py-3 px-4 <?php echo $plan['recommended'] ? 'bg-brand hover:bg-brand-dark text-white shadow-brand/20' : 'bg-slate-100 hover:bg-slate-200 text-slate-800'; ?> font-bold text-center rounded-lg transition shadow-md">
                                <?php echo esc_html($plan['button_text'] ?: 'สั่งซื้อเลย'); ?>
                            </a>
                        </div>

                        <?php if (!empty($plan['features'])): ?>
                            <div
                                class="p-8 border-t border-slate-100 <?php echo $plan['recommended'] ? 'bg-brand/5' : 'bg-slate-50/50'; ?>">
                                <ul class="space-y-3 text-sm text-slate-600">
                                    <?php foreach ($plan['features'] as $feature): ?>
                                        <li class="flex items-start">
                                            <i
                                                class="fa-solid fa-check <?php echo $plan['recommended'] ? 'text-brand' : 'text-green-500'; ?> mt-1 mr-2"></i>
                                            <span><?php echo esc_html($feature['text']); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; endif; ?>
        </div>
    </div>
</section>