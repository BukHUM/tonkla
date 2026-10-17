<?php
/**
 * Service/Feature Grid Module
 */

defined( 'ABSPATH' ) || exit;

$module = get_query_var('module');

$grid_layout = $module['layout'];
$grid_items = $module['items']; // Complex items
$section_title = $module['section_title'];
$section_subtitle = $module['section_subtitle'];
$section_description = isset($module['section_description']) ? $module['section_description'] : '';

$icon_color = isset($item['icon_color']) ? $item['icon_color'] : 'brand';
$grid_classes = ($grid_layout === 'cols-4') ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4' : 'grid-cols-1 md:grid-cols-3';

// Background Color
$bg_color = isset($module['bg_color']) ? $module['bg_color'] : 'white';
$bg_class = 'bg-' . $bg_color;

// Content Alignment
$content_align = isset($module['content_align']) ? $module['content_align'] : 'left';
$align_class = 'text-left items-start';
if ($content_align === 'center') {
    $align_class = 'text-center items-center';
} elseif ($content_align === 'right') {
    $align_class = 'text-right items-end';
}
?>

<section class="py-20 <?php echo esc_attr($bg_class); ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <?php if ($section_title): ?>
            <div class="text-center mb-16">
                <?php if ($section_subtitle): ?>
                    <span
                        class="text-brand font-bold tracking-wider uppercase text-sm"><?php echo esc_html($section_subtitle); ?></span>
                <?php endif; ?>
                <h2 class="text-3xl md:text-4xl font-heading font-bold text-slate-900 mt-2">
                    <?php echo esc_html($section_title); ?>
                </h2>
                <div class="h-1 w-20 bg-brand mx-auto rounded-full mt-4"></div>

                <?php if (!empty($section_description)): ?>
                    <p class="text-slate-600 mt-6 max-w-2xl mx-auto text-lg leading-relaxed">
                        <?php echo nl2br(esc_html($section_description)); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="grid <?php echo $grid_classes; ?> gap-8">
            <?php if ($grid_items):
                foreach ($grid_items as $item):
                    // Icon Color Logic
                    // Icon Color Logic
                    $icon_color = isset($item['icon_color']) ? $item['icon_color'] : 'brand';

                    // Specific overrides for 'brand' or special cases if needed, otherwise dynamic
                    if ($icon_color === 'brand' || empty($icon_color)) {
                        $color_classes = 'bg-brand/10 text-brand';
                    } else {
                        // Dynamic Tailwind classes: bg-{color}-100 text-{color}-600
                        // Valid colors: indigo, blue, purple, red, orange, teal, cyan, emerald, rose, amber, yellow, lime, pink, etc.
                        $color_classes = 'bg-' . $icon_color . '-100 text-' . $icon_color . '-600';
                    }

                    // Link Logic
                    $link = '#';
                    $link_type = isset($item['link_type']) ? $item['link_type'] : 'custom';

                    if ($link_type === 'post' && !empty($item['link_content'])) {
                        $post_association = $item['link_content'];
                        if (is_array($post_association) && !empty($post_association[0])) {
                            $link = get_permalink($post_association[0]['id']);
                        }
                    } else {
                        $link = '#';
                        if (!empty($item['link_url'])) {
                            $link = $item['link_url'];
                        } elseif (isset($item['link'])) {
                            $link = $item['link'];
                        }
                    }
                    ?>
                    <div
                        class="bg-slate-50 p-8 rounded-2xl border border-slate-100 hover:shadow-lg transition group flex flex-col <?php echo esc_attr($align_class); ?>">
                        <?php if ( tonkla_icon_class_from_item( $item ) || ! empty( $item['icon_image'] ) ) : ?>
                            <div
                                class="w-14 h-14 <?php echo esc_attr($color_classes); ?> rounded-xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                                <?php
                                tonkla_render_icon(
                                    $item,
                                    array(
                                        'fallback' => '',
                                        'class'    => '',
                                    )
                                );
                                ?>
                            </div>
                        <?php endif; ?>

                        <h3 class="text-xl font-bold font-heading mb-2 text-slate-900 min-h-[3.5rem]">
                            <?php echo esc_html($item['title']); ?>
                        </h3>
                        <p class="text-slate-500 text-sm mb-4 leading-relaxed flex-grow">
                            <?php echo esc_html($item['description']); ?>
                        </p>

                        <?php
                        // Tech Stack Icons
                        $stack_icons = isset($item['stack_icons']) ? $item['stack_icons'] : array();
                        if (!empty($stack_icons)):
                            ?>
                            <div class="flex gap-3 text-2xl pt-4 border-t border-slate-200/60 mt-auto">
                                <?php foreach ( $stack_icons as $stack_icon ) :
                                    $stack_title = isset( $stack_icon['title'] ) ? $stack_icon['title'] : '';
                                    $color_cls   = tonkla_icon_color_classes( isset( $stack_icon['icon_color'] ) ? (string) $stack_icon['icon_color'] : 'blue', 'text' );
                                    ?>
                                    <span class="<?php echo esc_attr( $color_cls ); ?> hover:scale-110 transition-transform inline-flex" title="<?php echo esc_attr( $stack_title ); ?>">
                                        <?php
                                        tonkla_render_icon(
                                            $stack_icon,
                                            array(
                                                'fallback' => 'fa-solid fa-code',
                                                'class'    => '',
                                            )
                                        );
                                        ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($link) && $link !== '#'): ?>
                            <a href="<?php echo esc_url($link); ?>"
                                class="text-brand font-bold text-sm hover:underline flex items-center gap-1 mt-4">
                                รายละเอียด <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; endif; ?>
        </div>
    </div>
</section>