<?php
/**
 * Feature Split Module (Software & Automation)
 */

defined( 'ABSPATH' ) || exit;

$module = get_query_var('module');

$section_subtitle = $module['section_subtitle'];
$section_title = $module['section_title'];
$section_description = $module['section_description'];
$layout_columns = !empty($module['layout_columns']) ? $module['layout_columns'] : '2';
$items = $module['items']; // Complex list with nested visual data

// Background Color
$bg_color = isset($module['bg_color']) ? $module['bg_color'] : 'white';
$bg_class = 'bg-' . $bg_color;
?>

<section class="py-20 <?php echo esc_attr($bg_class); ?>" x-data="{ activeIndex: 0 }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div
            class="grid grid-cols-1 <?php echo $layout_columns == '2' ? 'lg:grid-cols-2 gap-16 items-center' : 'gap-12'; ?>">

            <!-- Content Side (List) -->
            <div>
                <?php if ($section_subtitle): ?>
                    <span
                        class="text-n8n font-bold tracking-wider uppercase text-sm"><?php echo esc_html($section_subtitle); ?></span>
                <?php endif; ?>

                <?php if ($section_title): ?>
                    <h2 class="text-3xl md:text-4xl font-heading font-bold text-slate-900 mt-2 mb-6 text-balance">
                        <?php echo esc_html($section_title); ?>
                    </h2>
                <?php endif; ?>

                <?php if ($section_description): ?>
                    <p class="text-slate-600 mb-8 leading-relaxed">
                        <?php echo nl2br(esc_html($section_description)); ?>
                    </p>
                <?php endif; ?>

                <?php if ($items): ?>
                    <div class="space-y-4">
                        <?php foreach ($items as $index => $item):
                            $icon_color = isset($item['icon_color']) ? $item['icon_color'] : 'brand';
                            $icon_bg = 'bg-brand/10 text-brand';
                            $hover_border = 'hover:border-brand';
                            $hover_icon_bg = 'group-hover:bg-brand';
                            $hover_text = 'group-hover:text-brand';

                            // Visual Data for 1-Column mode inline display
                            $media_type = isset($item['media_type']) ? $item['media_type'] : 'code';
                            $image = isset($item['image']) ? $item['image'] : '';
                            $code_window_title = isset($item['code_window_title']) ? $item['code_window_title'] : '';
                            $code_content = isset($item['code_content']) ? $item['code_content'] : '';
                            $code_button_text = isset($item['code_button_text']) ? $item['code_button_text'] : '';
                            $code_button_link = isset($item['code_button_link']) ? $item['code_button_link'] : '#';

                            // Color Logic
                            if ($icon_color === 'brand') {
                                // Default Brand
                                $icon_bg = 'bg-brand/10 text-brand';
                                $hover_border = 'hover:border-brand';
                                $hover_icon_bg = 'group-hover:bg-brand';
                                $hover_text = 'group-hover:text-brand';
                            } elseif ($icon_color === 'n8n') {
                                // Legacy n8n
                                $icon_bg = 'bg-n8n/10 text-n8n';
                                $hover_border = 'hover:border-n8n';
                                $hover_icon_bg = 'group-hover:bg-n8n';
                                $hover_text = 'group-hover:text-n8n';
                            } elseif ($icon_color === 'laravel') {
                                // Legacy Laravel
                                $icon_bg = 'bg-red-100 text-red-600';
                                $hover_border = 'hover:border-red-500';
                                $hover_icon_bg = 'group-hover:bg-red-500';
                                $hover_text = 'group-hover:text-red-500';
                            } else {
                                // Standardized Dynamic Colors
                                // e.g. blue -> bg-blue-100 text-blue-600
                                $icon_bg = "bg-{$icon_color}-100 text-{$icon_color}-600";
                                $hover_border = "hover:border-{$icon_color}-500";
                                $hover_icon_bg = "group-hover:bg-{$icon_color}-500";
                                $hover_text = "group-hover:text-{$icon_color}-500";
                            }

                            // Alpine Class Binding for Active State (only for 2 cols)
                            // We need to ensure the ring color matches (e.g. ring-blue-500)
                            // For brand/n8n it's simple, for others add -500 if needed?
                            // Actually Tailwind usually needs explicit numbers for ring-{color}-500 unless defined in config.
                            // Assuming 'brand' and 'n8n' are defined as colors in config.
                            // For standard colors, we need ring-{color}-500.
                    
                            $ring_color_class = $icon_color;
                            if ($icon_color !== 'brand' && $icon_color !== 'n8n' && strpos($icon_color, '-') === false && $icon_color !== 'laravel') {
                                $ring_color_class = $icon_color . '-500';
                            }
                            // Laravel map to red-500
                            if ($icon_color === 'laravel') {
                                $ring_color_class = 'red-500';
                            }

                            $alpine_classes = "activeIndex === $index ? 'border-{$ring_color_class} border-opacity-100' : ''";
                            ?>
                            <!-- Item Wrapper -->
                            <div class="space-y-4">
                                <!-- Clickable List Item -->
                                <div @click="<?php echo $layout_columns == '2' ? "activeIndex = $index" : ""; ?>"
                                    :class="{ 'border-slate-300 shadow-md': activeIndex === <?php echo $index; ?> && '<?php echo $layout_columns; ?>' === '2', 'border-slate-200': activeIndex !== <?php echo $index; ?> || '<?php echo $layout_columns; ?>' !== '2' }"
                                    class="flex items-center gap-4 p-4 bg-white rounded-xl border <?php echo esc_attr($hover_border); ?> hover:shadow-md transition group cursor-pointer">
                                    <div
                                        class="w-12 h-12 <?php echo esc_attr($icon_bg); ?> rounded-full flex items-center justify-center text-xl <?php echo esc_attr($hover_icon_bg); ?> group-hover:text-white transition">
                                        <?php
                                        tonkla_render_icon(
                                            $item,
                                            array(
                                                'fallback' => 'fa-solid fa-circle',
                                            )
                                        );
                                        ?>
                                    </div>
                                    <div class="flex-grow">
                                        <h3 class="font-bold text-slate-800 text-base"><?php echo esc_html($item['title']); ?></h3>
                                        <p class="text-xs text-slate-500"><?php echo esc_html($item['description']); ?></p>
                                    </div>
                                    <div class="ml-auto text-slate-300 <?php echo esc_attr($hover_text); ?>">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </div>
                                </div>

                                <!-- In 1-Column Mode: Show Visual Content IMMEDIATELY below item -->
                                <?php if ($layout_columns == '1'): ?>
                                    <div class="pl-4 md:pl-8">
                                        <?php if ($media_type === 'image' && $image): ?>
                                            <div class="bg-white rounded-2xl p-2 shadow-lg border border-slate-100 overflow-hidden">
                                                <?php echo wp_get_attachment_image($image, 'large', false, array('class' => 'w-full h-auto rounded-xl')); ?>
                                            </div>
                                        <?php elseif ($media_type === 'code'): ?>
                                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                                <div class="flex items-center gap-3 mb-4 border-b border-slate-100 pb-3">
                                                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                                    <span
                                                        class="text-xs text-slate-400 ml-auto font-mono"><?php echo esc_html($code_window_title); ?></span>
                                                </div>
                                                <div
                                                    class="p-4 bg-slate-50 rounded-lg text-xs font-mono text-slate-600 overflow-x-auto">
                                                    <?php echo wp_kses_post($code_content); ?>
                                                </div>
                                                <?php if ($code_button_text): ?>
                                                    <div class="text-center mt-4">
                                                        <a href="<?php echo esc_url($code_button_link); ?>"
                                                            class="inline-block px-6 py-2 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800 transition">
                                                            <?php echo esc_html($code_button_text); ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Visual Side (Right) - Only for 2 Columns -->
            <?php if ($layout_columns == '2' && $items): ?>
                <div class="relative flex items-center justify-center">
                    <div
                        class="absolute inset-0 bg-gradient-to-tr from-brand to-blue-500 rounded-3xl transform rotate-3 opacity-20">
                    </div>

                    <!-- Loop through items and show based on activeIndex -->
                    <?php foreach ($items as $index => $item):
                        $media_type = isset($item['media_type']) ? $item['media_type'] : 'code';
                        $image = isset($item['image']) ? $item['image'] : '';
                        $code_window_title = isset($item['code_window_title']) ? $item['code_window_title'] : '';
                        $code_content = isset($item['code_content']) ? $item['code_content'] : '';
                        $code_button_text = isset($item['code_button_text']) ? $item['code_button_text'] : '';

                        // Resolve Button Link
                        $code_button_link = '#';
                        $code_button_link_type = isset($item['code_button_link_type']) ? $item['code_button_link_type'] : 'custom';

                        if ($code_button_link_type === 'post' && !empty($item['code_button_link_content'])) {
                            $post_association = $item['code_button_link_content'];
                            if (is_array($post_association) && !empty($post_association[0])) {
                                $code_button_link = get_permalink($post_association[0]['id']);
                            }
                        } else {
                            $code_button_link = isset($item['code_button_link']) ? $item['code_button_link'] : '#';
                        }
                        ?>
                        <div x-show="activeIndex === <?php echo $index; ?>"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4"
                            x-transition:enter-end="opacity-100 translate-y-0" class="relative z-10" style="display: none;">
                            <!-- Default hidden, Alpine handles display -->

                            <?php if ($media_type === 'image' && $image): ?>
                                <div class="bg-white rounded-3xl p-4 shadow-xl border border-slate-100 overflow-hidden">
                                    <?php echo wp_get_attachment_image($image, 'large', false, array('class' => 'w-full h-auto rounded-2xl')); ?>
                                </div>

                            <?php elseif ($media_type === 'code'): ?>
                                <div class="bg-white rounded-3xl p-8 shadow-xl border border-slate-100">
                                    <!-- Mac Window Header -->
                                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                        <span
                                            class="text-xs text-slate-400 ml-auto font-mono"><?php echo esc_html($code_window_title); ?></span>
                                    </div>

                                    <!-- Code Content -->
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-4">
                                            <div class="bg-yellow-100 text-yellow-600 p-3 rounded-lg"><i
                                                    class="fa-solid fa-bolt"></i></div>
                                            <div class="h-1 flex-grow bg-slate-100 rounded relative overflow-hidden">
                                                <div class="absolute inset-0 bg-slate-300 w-1/2 animate-pulse"></div>
                                            </div>
                                            <div class="bg-green-100 text-green-600 p-3 rounded-lg"><i
                                                    class="fa-brands fa-line"></i></div>
                                        </div>

                                        <div class="p-4 bg-slate-50 rounded-lg text-xs font-mono text-slate-600 overflow-x-auto">
                                            <?php echo wp_kses_post($code_content); ?>
                                        </div>

                                        <?php if ($code_button_text): ?>
                                            <div class="text-center mt-4">
                                                <a href="<?php echo esc_url($code_button_link); ?>"
                                                    class="inline-block px-6 py-2 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800 transition">
                                                    <?php echo esc_html($code_button_text); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>