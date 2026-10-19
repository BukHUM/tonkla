<?php
/**
 * Featured post card (first post on page 1)
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<section class="py-12 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div
            class="group relative bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition-all duration-300 grid grid-cols-1 lg:grid-cols-2">
            <div class="h-64 lg:h-auto overflow-hidden relative">
                <?php if (has_post_thumbnail()): ?>
                    <?php
                    echo wp_get_attachment_image(
                        get_post_thumbnail_id(),
                        'large',
                        false,
                        array(
                            'class' => 'w-full h-full object-cover transition-transform duration-700 group-hover:scale-105',
                            'alt' => the_title_attribute(array('echo' => false)),
                        )
                    );
                    ?>
                <?php else: ?>
                    <div class="w-full h-full bg-slate-200 flex items-center justify-center text-slate-400">
                        <i class="fa-regular fa-image text-4xl"></i>
                    </div>
                <?php endif; ?>

                <?php if (is_sticky()): ?>
                    <div class="absolute top-4 left-4">
                        <span
                            class="bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Highlight</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="p-8 lg:p-12 flex flex-col justify-center">
                <div class="flex items-center gap-4 text-caption mb-4 font-medium uppercase tracking-wider">
                    <span class="text-brand"><i class="fa-solid fa-folder-open mr-1"></i>
                        <?php the_category(', '); ?>
                    </span>
                    <span><i class="fa-regular fa-clock mr-1"></i>
                        <?php echo esc_html(get_the_date('j M Y')); ?>
                    </span>
                </div>
                <h2
                    class="text-3xl font-heading font-bold text-slate-900 mb-4 group-hover:text-brand transition leading-tight">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_title(); ?>
                    </a>
                </h2>
                <p class="text-slate-600 mb-6 line-clamp-3 leading-relaxed">
                    <?php echo esc_html(get_the_excerpt()); ?>
                </p>
                <div class="mt-auto">
                    <a href="<?php the_permalink(); ?>"
                        class="inline-flex items-center text-brand font-bold hover:underline">
                        <?php esc_html_e('อ่านต่อ', 'tonkla'); ?> <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
