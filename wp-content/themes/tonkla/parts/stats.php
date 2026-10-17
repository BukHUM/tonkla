<?php
/**
 * Stats Module
 */

defined( 'ABSPATH' ) || exit;
$module = get_query_var('module');
$items = $module['items']; // Validated in page-flexible.php or caller
$bg_color = isset($module['bg_color']) ? $module['bg_color'] : 'white';

// Resolve classes based on background color
$container_classes = 'bg-white rounded-3xl shadow-xl p-8 md:p-12 border border-slate-100 backdrop-blur-sm bg-white/95';
$text_color = 'text-slate-900';
$label_color = 'text-slate-500';
$divider_color = 'divide-slate-200'; // Gray (standard)

if ($bg_color === 'dark') {
    $container_classes = 'bg-slate-900 rounded-3xl shadow-xl p-8 md:p-12 border border-slate-800';
    $text_color = 'text-white';
    $label_color = 'text-slate-400';
    $divider_color = 'divide-slate-700'; // Darker gray for dark mode
} elseif ($bg_color === 'brand') {
    $container_classes = 'bg-brand rounded-3xl shadow-xl p-8 md:p-12 border border-brand-light';
    $text_color = 'text-white';
    $label_color = 'text-green-100';
    $divider_color = 'divide-green-500/30'; // Subtle brand divider
} elseif ($bg_color === 'gray' || $bg_color === 'light-gray') {
    $container_classes = 'bg-slate-50 rounded-3xl shadow-xl p-8 md:p-12 border border-slate-200';
    $text_color = 'text-slate-900';
    $label_color = 'text-slate-500';
    $divider_color = 'divide-slate-200';
}

if (empty($items)) {
    return;
}
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20 -mt-12 mb-12">
    <div class="<?php echo esc_attr($container_classes); ?>">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 md:divide-x <?php echo esc_attr($divider_color); ?>">
            <?php foreach ($items as $index => $item):
                // Default icon if not set
                $icon = !empty($item['icon']) ? $item['icon'] : 'fa-solid fa-chart-simple';
                // Default color key to brand if not set
                $icon_color_key = !empty($item['icon_color']) ? $item['icon_color'] : 'brand';

                // Resolve class from key
                if ($icon_color_key === 'brand') {
                    $icon_class = 'text-brand';
                } elseif (strpos($icon_color_key, 'text-') === 0) {
                    // Legacy support for old values (e.g. text-blue-500)
                    $icon_class = $icon_color_key;
                } else {
                    $icon_class = 'text-' . $icon_color_key . '-500';
                }

                // If background is brand, force icon to be white if it matches brand color to avoid invisible icons
                if ($bg_color === 'brand' && ($icon_color_key === 'brand' || $icon_class === 'text-brand')) {
                    $icon_class = 'text-white';
                }
                ?>
                <div class="text-center group <?php echo $index > 0 ? 'md:pl-8' : ''; ?>">
                    <div
                        class="text-4xl <?php echo esc_attr($icon_class); ?> mb-4 transform group-hover:scale-110 transition-transform duration-300 inline-block drop-shadow-sm">
                        <?php
                        tonkla_render_icon(
                            $item,
                            array(
                                'fallback' => 'fa-solid fa-chart-simple',
                            )
                        );
                        ?>
                    </div>
                    <div
                        class="text-3xl md:text-4xl font-heading font-bold <?php echo esc_attr($text_color); ?> mb-2 tracking-tight">
                        <?php echo esc_html($item['stat_value']); ?>
                    </div>
                    <div
                        class="text-sm <?php echo esc_attr($label_color); ?> font-medium font-heading uppercase tracking-wider">
                        <?php echo esc_html($item['label']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>