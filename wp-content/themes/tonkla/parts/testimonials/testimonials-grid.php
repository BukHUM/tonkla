<?php
/**
 * Testimonials Grid Module
 */

defined( 'ABSPATH' ) || exit;

$module = get_query_var('module');
$section_title = $module['section_title'];
$items = $module['items']; // Testimonials list

// Background Color
$bg_color = isset($module['bg_color']) ? $module['bg_color'] : 'white';
$bg_class = 'bg-' . $bg_color;
?>

<section class="py-20 <?php echo esc_attr($bg_class); ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($section_title): ?>
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-3xl font-heading font-bold text-slate-900">
                    <?php echo esc_html($section_title); ?>
                </h2>
                <div class="h-1 w-20 bg-brand mx-auto rounded-full mt-4"></div>
            </div>
        <?php endif; ?>

        <?php if ($items): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($items as $item):
                    $fallback_initial = mb_substr(isset($item['name']) ? $item['name'] : 'C', 0, 1);
                    ?>
                    <div
                        class="bg-white p-8 rounded-2xl border border-slate-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col h-full group">
                        <div class="mb-6 flex-grow">
                            <i
                                class="fa-solid fa-quote-left text-3xl text-brand/20 group-hover:text-brand transition-colors duration-300"></i>
                            <p class="mt-4 text-slate-600 italic leading-relaxed text-sm md:text-base">
                                "<?php echo nl2br(esc_html($item['quote'])); ?>"
                            </p>
                        </div>

                        <div class="flex items-center gap-4 border-t border-slate-100 pt-6 mt-auto">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['name']); ?>"
                                    class="w-12 h-12 rounded-full object-cover shadow-sm ring-2 ring-brand/10 group-hover:ring-brand/50 transition-all">
                            <?php else: ?>
                                <div
                                    class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden shadow-sm border border-slate-200">
                                    <i class="fa-solid fa-user text-slate-300 text-2xl mt-2"></i>
                                </div>
                            <?php endif; ?>

                            <div>
                                <p class="font-bold text-slate-900 text-sm"><?php echo esc_html($item['name']); ?></p>
                                <p class="text-xs text-brand font-medium"><?php echo esc_html($item['role']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>