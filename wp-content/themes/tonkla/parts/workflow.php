<?php
/**
 * n8n Workflow Module
 */

defined( 'ABSPATH' ) || exit;

$module = get_query_var('module');

$headline = $module['headline'];
$nodes = $module['nodes'];

// Background Color
$bg_color = isset($module['bg_color']) ? $module['bg_color'] : 'white';
$bg_class = 'bg-' . $bg_color;
?>

<section class="py-20 <?php echo esc_attr($bg_class); ?> relative overflow-hidden">
    <!-- Background Decor -->
    <div class="absolute top-0 right-0 w-1/2 h-full bg-n8n/10 skew-x-12 transform origin-top-right"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <?php if ($headline): ?>
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl md:text-5xl font-heading font-bold text-white mb-6 leading-tight">
                    <?php echo esc_html($headline); ?>
                </h2>
            </div>
        <?php endif; ?>

        <div
            class="bg-slate-800/50 border border-slate-700 rounded-xl p-8 md:p-12 relative overflow-hidden">
            <!-- Connecting Line (Desktop) -->
            <div class="absolute top-1/2 left-0 w-full h-1 bg-slate-700 -translate-y-1/2 hidden md:block z-0"></div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative z-10">
                <?php if ($nodes):
                    foreach ($nodes as $node):
                        $color_val = isset($node['color_class']) ? $node['color_class'] : '';
                        $border_class = 'border-brand'; // Default
                
                        if ($color_val) {
                            if (strpos($color_val, 'border-') === 0) {
                                // Legacy text input support
                                $border_class = $color_val;
                            } elseif ($color_val === 'brand') {
                                $border_class = 'border-brand';
                            } else {
                                // Standardized select key
                                $border_class = 'border-' . $color_val . '-500';
                            }
                        }
                        ?>
                        <div
                            class="bg-white rounded-xl p-6 shadow-lg text-center border-b-4 <?php echo esc_attr($border_class); ?>">
                            <div
                                class="w-12 h-12 bg-slate-50 text-slate-700 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                                <i class="<?php echo esc_attr($node['icon']); ?>"></i>
                            </div>
                            <h3 class="font-bold text-slate-800"><?php echo esc_html($node['title']); ?></h3>
                            <p class="text-xs text-slate-500 mt-2"><?php echo esc_html($node['description']); ?></p>
                        </div>
                    <?php endforeach; endif; ?>
            </div>

            <div
                class="absolute top-4 right-4 flex items-center gap-2 bg-black/30 px-3 py-1 rounded-full text-xs text-green-400 border border-white/10">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Active
            </div>
        </div>
    </div>
</section>