<?php
/**
 * Process Timeline Module
 * Displays workflow steps with numbered circles
 */

defined( 'ABSPATH' ) || exit;

$module = get_query_var('module');

$section_title = isset($module['section_title']) ? $module['section_title'] : 'ขั้นตอนการทำงาน';
$section_subtitle = isset($module['section_subtitle']) ? $module['section_subtitle'] : '';
$bg_color = isset($module['bg_color']) ? $module['bg_color'] : 'slate-50';
$steps = isset($module['steps']) ? $module['steps'] : array();

// Background class
$bg_class = 'bg-' . $bg_color;
?>

<section class="py-20 <?php echo esc_attr($bg_class); ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-heading font-bold text-slate-900">
                <?php echo esc_html($section_title); ?>
            </h2>
            <?php if ($section_subtitle): ?>
                <p class="text-slate-600 mt-2">
                    <?php echo esc_html($section_subtitle); ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="relative">
            <!-- Connecting Line (Desktop) -->
            <div class="hidden md:block absolute top-1/2 left-0 w-full h-1 bg-slate-200 -z-0 -translate-y-1/2"></div>

            <div
                class="grid grid-cols-1 md:grid-cols-<?php echo count($steps) > 4 ? '5' : count($steps); ?> gap-8 relative z-10">
                <?php
                $step_num = 1;
                foreach ($steps as $step):
                    $step_color_key = isset($step['icon_color']) ? $step['icon_color'] : 'brand';

                    // Determine background class based on color key using logic similar to other grids
                    if ($step_color_key === 'brand') {
                        $step_bg_class = 'bg-brand';
                    } elseif (strpos($step_color_key, '-') !== false) {
                        // Legacy support if complex keys are ever used (unlikely here but consistency is good)
                        $step_bg_class = 'bg-' . $step_color_key;
                    } else {
                        // Standard simple key: red -> bg-red-500
                        $step_bg_class = 'bg-' . $step_color_key . '-500';
                    }
                    ?>
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 text-center relative group">
                        <div
                            class="w-16 h-16 mx-auto <?php echo esc_attr($step_bg_class); ?> text-white rounded-full flex items-center justify-center text-2xl font-bold mb-4 ring-8 ring-white transition-colors">
                            <?php echo $step_num; ?>
                        </div>
                        <h4 class="font-bold text-lg mb-2">
                            <?php echo esc_html($step['title']); ?>
                        </h4>
                        <p class="text-sm text-slate-500">
                            <?php echo esc_html($step['description']); ?>
                        </p>
                    </div>
                    <?php
                    $step_num++;
                endforeach;
                ?>
            </div>
        </div>
    </div>
</section>