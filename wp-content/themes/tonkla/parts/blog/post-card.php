<?php
/**
 * Blog grid post card (expects loop context)
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<article
    class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition group flex flex-col">
    <div class="relative h-48 overflow-hidden">
        <a href="<?php the_permalink(); ?>" class="block h-full">
            <?php if (has_post_thumbnail()): ?>
                <?php
                echo wp_get_attachment_image(
                    get_post_thumbnail_id(),
                    'medium_large',
                    false,
                    array(
                        'class' => 'w-full h-full object-cover group-hover:scale-105 transition duration-500',
                        'alt' => the_title_attribute(array('echo' => false)),
                    )
                );
                ?>
            <?php else: ?>
                <div class="w-full h-full bg-slate-200 flex items-center justify-center text-slate-400">
                    <i class="fa-regular fa-image text-2xl"></i>
                </div>
            <?php endif; ?>
        </a>
        <?php
        $cats = get_the_category();
        if (!empty($cats)):
            echo '<span class="absolute top-3 right-3 bg-white/90 backdrop-blur text-xs font-bold px-2 py-1 rounded text-slate-700">' . esc_html($cats[0]->name) . '</span>';
        endif;
        ?>
    </div>
    <div class="p-6 flex-1 flex flex-col">
        <div class="text-caption mb-3 flex justify-between">
            <span><?php echo esc_html(get_the_date('j M Y')); ?></span>
            <span><?php echo esc_html(tonkla_reading_time()); ?> min read</span>
        </div>
        <h3
            class="text-xl font-heading font-bold text-slate-900 mb-3 group-hover:text-brand transition line-clamp-2">
            <a href="<?php the_permalink(); ?>">
                <?php the_title(); ?>
            </a>
        </h3>
        <p class="text-slate-600 text-sm mb-4 line-clamp-3 flex-1">
            <?php echo esc_html(get_the_excerpt()); ?>
        </p>
        <a href="<?php the_permalink(); ?>"
            class="text-brand text-sm font-bold hover:underline mt-auto"><?php esc_html_e('อ่านต่อ', 'tonkla'); ?></a>
    </div>
</article>
