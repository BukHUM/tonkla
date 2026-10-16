<?php
/**
 * Tech Stack Grid Module
 * Optimized for displaying logos/icons with titles (e.g. Tools & Technologies)
 */

defined( 'ABSPATH' ) || exit;

$module = get_query_var('module');

$section_title = $module['section_title'];
$section_subtitle = $module['section_subtitle'];
$items = $module['items'];

// Background Color
$bg_color = isset($module['bg_color']) ? $module['bg_color'] : 'dark';
$bg_class = 'bg-' . $bg_color;

// Text Color Logic (Auto-switch based on BG)
$is_dark_bg = in_array($bg_color, array('dark', 'dark-gray', 'brand'));
$text_class = $is_dark_bg ? 'text-white' : 'text-slate-900';
$item_bg_class = $is_dark_bg ? 'bg-white/5 border-white/10' : 'bg-white border-slate-100';
$item_text_class = $is_dark_bg ? 'text-white' : 'text-slate-900';
$item_desc_class = $is_dark_bg ? 'text-slate-400' : 'text-slate-500';
?>

<section class="py-20 <?php echo esc_attr($bg_class . ' ' . $text_class); ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col lg:flex-row gap-16 items-start">
            <!-- Header Section (Left Side) -->
            <div class="lg:w-1/3 sticky top-24">
                <h2 class="text-3xl md:text-4xl font-heading font-bold mb-6 leading-tight">
                    <?php echo nl2br(esc_html($section_title)); ?>
                </h2>
                <?php if ($section_subtitle): ?>
                    <div class="text-brand font-bold mb-2">
                        <?php echo esc_html($section_subtitle); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($module['section_description'])): ?>
                    <p class="text-slate-300 mb-8 leading-relaxed">
                        <?php echo nl2br(esc_html($module['section_description'])); ?>
                    </p>
                <?php endif; ?>

                <?php
                $pf_link_text = isset($module['portfolio_link_text']) ? $module['portfolio_link_text'] : 'ดูตัวอย่างผลงาน';
                $pf_link = isset($module['portfolio_link']) ? $module['portfolio_link'] : '#';

                if (isset($module['portfolio_link_type']) && $module['portfolio_link_type'] === 'post' && !empty($module['portfolio_link_content'])) {
                    $pf_link = get_permalink($module['portfolio_link_content'][0]['id']);
                }
                ?>

                <a href="<?php echo esc_url($pf_link); ?>"
                    class="inline-flex items-center gap-2 text-brand font-bold hover:text-brand-light transition">
                    <?php echo esc_html($pf_link_text); ?> <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <!-- Grid Items (Right Side) -->
            <div class="lg:w-2/3 w-full">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php if ($items):
                        foreach ($items as $item):
                            $icon_type = isset($item['icon_type']) ? $item['icon_type'] : 'icon';
                            ?>
                            <div
                                class="<?php echo esc_attr($item_bg_class); ?> border rounded-2xl p-6 flex flex-col items-center justify-center text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300 aspect-square group">

                                <!-- Icon/Image Area -->
                                <div class="h-12 flex items-center justify-center mb-4">
                                    <?php if ($icon_type === 'image' && !empty($item['icon_image'])): ?>
                                        <?php
                                        // Use wp_get_attachment_image logic for responsive loading
                                        echo wp_get_attachment_image($item['icon_image'], 'thumbnail', false, array(
                                            'class' => 'max-h-12 w-auto object-contain transition group-hover:scale-110'
                                        ));
                                        ?>
                                    <?php elseif (!empty($item['icon_class'])):
                                        $icon_color_key = isset($item['icon_color']) ? $item['icon_color'] : 'brand';

                                        // Handle legacy values (text-...) vs new simple keys
                                        if (strpos($icon_color_key, 'text-') === 0) {
                                            $icon_color_class = $icon_color_key;
                                        } else {
                                            $icon_color_class = ($icon_color_key === 'brand') ? 'text-brand' : 'text-' . $icon_color_key . '-500';
                                        }
                                        ?>
                                        <i
                                            class="<?php echo esc_attr($item['icon_class'] . ' ' . $icon_color_class); ?> text-5xl mb-4 group-hover:scale-110 transition-transform"></i>
                                    <?php else: ?>
                                        <!-- Fallback -->
                                        <i class="fa-solid fa-code text-4xl text-slate-500"></i>
                                    <?php endif; ?>
                                </div>

                                <!-- Text -->
                                <h3 class="<?php echo esc_attr($item_text_class); ?> font-bold text-sm md:text-base mb-1">
                                    <?php echo esc_html($item['title']); ?>
                                </h3>
                                <?php if (!empty($item['description'])): ?>
                                    <span class="<?php echo esc_attr($item_desc_class); ?> text-xs">
                                        <?php echo esc_html($item['description']); ?>
                                    </span>
                                <?php endif; ?>

                            </div>
                        <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

    </div>
</section>