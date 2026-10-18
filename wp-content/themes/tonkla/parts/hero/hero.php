<?php
/**
 * Hero Module Template Part
 */

defined( 'ABSPATH' ) || exit;

$module = get_query_var('module');

$hero_type = $module['hero_type'];
$badge = $module['badge'];
$headline = $module['headline'];
$description = $module['description'];
$buttons = $module['buttons'];

// New fields
// New fields
$bg_type = isset($module['bg_type']) ? $module['bg_type'] : 'pattern';
$bg_image = isset($module['bg_image']) ? $module['bg_image'] : '';
$bg_overlay = isset($module['bg_overlay']) ? $module['bg_overlay'] : '';
$enable_search = isset($module['enable_domain_search']) ? $module['enable_domain_search'] : false;

$bg_style_attr = '';
$bg_class = '';

if ($bg_type === 'image' && $bg_image) {
    $bg_image_url = wp_get_attachment_image_url($bg_image, 'full');
    if ($bg_image_url) {
        $bg_style_attr = "background-image: url('" . esc_url($bg_image_url) . "'); background-size: cover; background-position: center;";
    }
} elseif ($bg_type === 'pattern') {
    $bg_class = 'hero-bg';
} else {
    // Solid color, possibly handled by overlay or default bg
    if ($hero_type === 'service_alt') {
        $bg_class = 'bg-slate-900';
    }
}
?>

<section style="<?php echo esc_attr($bg_style_attr); ?>"
    class="relative <?php echo $hero_type === 'home' ? 'pt-32 pb-20 lg:pt-48 lg:pb-32' : 'pt-32 pb-20'; ?> <?php echo esc_attr($bg_class); ?> overflow-hidden">

    <?php if ($bg_overlay): ?>
        <div class="absolute inset-0 z-0" style="background-color: <?php echo esc_attr($bg_overlay); ?>;"></div>
    <?php endif; ?>

    <?php if ($hero_type === 'home'): ?>
        <div
            class="absolute top-0 right-0 -mt-20 -mr-20 w-96 h-96 bg-brand/20 rounded-full blur-3xl opacity-50 pointer-events-none">
        </div>
        <div
            class="absolute bottom-0 left-0 -mb-20 -ml-20 w-72 h-72 bg-blue-500/20 rounded-full blur-3xl opacity-50 pointer-events-none">
        </div>
    <?php elseif ($hero_type === 'service'): ?>
        <div
            class="absolute top-0 right-0 w-[800px] h-[800px] bg-brand/10 rounded-full blur-3xl -translate-y-1/3 translate-x-1/3 pointer-events-none">
        </div>
    <?php endif; ?>

    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 <?php echo $hero_type === 'home' ? 'text-center' : ''; ?>">

        <div
            class="flex flex-col <?php echo $hero_type === 'home' ? 'items-center' : 'lg:flex-row lg:items-center lg:gap-12'; ?>">

            <div class="<?php echo $hero_type === 'home' ? 'w-full' : 'lg:w-1/2'; ?>">
                <?php if ($badge): ?>
                    <span
                        class="inline-block py-1 px-3 rounded-full bg-slate-800 border border-slate-700 text-brand-light text-xs font-heading tracking-wide mb-6">
                        <?php echo esc_html($badge); ?>
                    </span>
                <?php endif; ?>

                <?php
                // Dark surfaces (pattern hero-bg, service_alt) need light text.
                $tonkla_hero_dark = ( 'service_alt' === $hero_type )
                    || ( 'pattern' === $bg_type )
                    || ( false !== strpos( (string) $bg_class, 'bg-slate-900' ) )
                    || ( false !== strpos( (string) $bg_class, 'hero-bg' ) );
                $tonkla_hero_heading = $tonkla_hero_dark ? 'text-white' : 'text-slate-900';
                $tonkla_hero_desc    = $tonkla_hero_dark ? 'text-slate-300' : 'text-slate-600';
                ?>
                <h1
                    class="text-4xl md:text-6xl font-heading font-bold <?php echo esc_attr( $tonkla_hero_heading ); ?> mb-6 leading-tight hero-headline">
                    <?php echo wp_kses_post($headline); ?>
                </h1>

                <p
                    class="text-lg md:text-xl <?php echo esc_attr( $tonkla_hero_desc ); ?> mb-8 max-w-3xl <?php echo $hero_type === 'home' ? 'mx-auto' : ''; ?> font-light leading-relaxed">
                    <?php echo esc_html($description); ?>
                </p>

                <?php if ($enable_search): ?>
                    <!-- Domain Search Box -->
                    <div
                        class="max-w-2xl mx-auto mb-10 bg-white p-2 rounded-xl border border-slate-200 flex flex-col sm:flex-row items-center gap-2 relative z-20">
                        <div class="flex-grow w-full relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-globe text-slate-400" aria-hidden="true"></i>
                            </div>
                            <label for="tonkla-domain-search" class="sr-only"><?php esc_html_e('ค้นหาโดเมน', 'tonkla'); ?></label>
                            <input type="text" id="tonkla-domain-search" name="domain"
                                class="w-full pl-10 pr-4 py-3 bg-transparent border border-transparent focus-visible:border-brand text-slate-800 placeholder-slate-400 font-medium font-sans rounded-lg"
                                placeholder="ค้นหาชื่อโดเมนของคุณ (เช่น tonklait.com)">
                        </div>
                        <button
                            class="w-full sm:w-auto px-8 py-3 bg-brand-dark hover:bg-brand text-white font-heading font-bold rounded-lg transition shadow-md whitespace-nowrap flex items-center justify-center gap-2">
                            <i class="fa-solid fa-magnifying-glass"></i> ค้นหา
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($buttons): ?>
                    <div class="flex flex-wrap gap-4 <?php echo $hero_type === 'home' ? 'justify-center' : ''; ?>">
                        <?php foreach ($buttons as $btn): ?>
                            <?php
                            // brand-dark meets WCAG AA with white text for typical brand hues.
                            $btn_style_class = 'bg-brand-dark hover:bg-brand text-white shadow-lg shadow-brand/30';
                    
                            if ($btn['style'] === 'secondary') {
                                $btn_style_class = 'bg-white/10 hover:bg-white/20 text-white border border-white/30 backdrop-blur-sm';
                            } elseif ($btn['style'] === 'dark') {
                                $btn_style_class = 'bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 shadow-lg';
                            }

                            // Link Logic
                            $btn_link = isset($btn['link']) ? $btn['link'] : '#';
                            if (isset($btn['link_type']) && $btn['link_type'] === 'post' && !empty($btn['link_content'])) {
                                $post_id = $btn['link_content'][0]['id'];
                                $btn_link = get_permalink($post_id);
                            }
                            ?>
                            <a href="<?php echo esc_url($btn_link); ?>"
                                class="px-8 py-3 rounded-lg font-heading font-bold transition flex items-center justify-center gap-2 <?php echo $btn_style_class; ?> group">
                                <?php if ( tonkla_icon_class_from_item( $btn ) || ! empty( $btn['icon_image'] ) ) : ?>
                                    <?php
                                    $icon_color_key = ! empty( $btn['icon_color'] ) ? $btn['icon_color'] : '';
                                    $icon_extra     = 'transition group-hover:scale-110';
                                    if ( $icon_color_key ) {
                                        if ( str_starts_with( (string) $icon_color_key, 'text-' ) ) {
                                            $icon_extra .= ' ' . $icon_color_key;
                                        } elseif ( 'brand' === $icon_color_key ) {
                                            $icon_extra .= ' text-brand-light';
                                        } else {
                                            $icon_extra .= ' text-' . $icon_color_key . '-300';
                                        }
                                    }
                                    tonkla_render_icon(
                                        $btn,
                                        array(
                                            'class'    => $icon_extra,
                                            'fallback' => '',
                                        )
                                    );
                                    ?>
                                <?php endif; ?>
                                <?php echo esc_html($btn['text']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($hero_type !== 'home'): ?>
                <div class="lg:w-1/2 mt-12 lg:mt-0">
                    <?php
                    if ($hero_type === 'service_alt') {
                        get_template_part('parts/hero/graphic', 'code');
                    } else {
                        get_template_part('parts/hero/graphic', 'server');
                    }
                    ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>