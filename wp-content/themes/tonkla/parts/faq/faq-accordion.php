<?php
/**
 * FAQ Accordion Module
 */

defined( 'ABSPATH' ) || exit;

$module = get_query_var('module');
$section_title = $module['section_title'];
$items = $module['items']; // Questions

// Background Color
$bg_color = isset($module['bg_color']) ? $module['bg_color'] : 'white';
$bg_class = 'bg-' . $bg_color;
?>

<section class="py-20 <?php echo esc_attr($bg_class); ?>">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($section_title): ?>
            <div class="text-center mb-12">
                <h2 class="text-2xl md:text-3xl font-heading font-bold text-slate-900">
                    <?php echo esc_html($section_title); ?>
                </h2>
            </div>
        <?php endif; ?>

        <?php if ($items): ?>
            <div class="space-y-4" x-data="{ active: null }">
                <?php foreach ($items as $index => $item): ?>
                    <div class="border border-slate-100 rounded-xl overflow-hidden">
                        <button @click="active = active === <?php echo $index; ?> ? null : <?php echo $index; ?>"
                            class="flex items-center justify-between w-full p-6 text-left bg-white hover:bg-slate-50 transition-colors duration-200 border border-transparent focus-visible:border-brand">
                            <span class="text-lg font-medium text-slate-800">
                                <?php echo esc_html($item['question']); ?>
                            </span>
                            <span class="ml-6 flex-shrink-0 text-brand transform transition-transform duration-200"
                                :class="{ 'rotate-180': active === <?php echo $index; ?> }">
                                <i class="fa-solid fa-chevron-down"></i>
                            </span>
                        </button>
                        <div x-show="active === <?php echo $index; ?>" x-collapse class="bg-white border-t border-slate-50">
                            <div class="p-6 pt-2 text-slate-600 leading-relaxed">
                                <?php echo wp_kses_post(wpautop($item['answer'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>