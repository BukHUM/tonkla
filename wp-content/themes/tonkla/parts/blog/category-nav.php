<?php
/**
 * Blog category filter navigation
 */

if (!defined('ABSPATH')) {
    exit;
}

$all_link = get_post_type_archive_link('post');
if ('posts' === get_option('show_on_front')) {
    $all_link = home_url('/');
} else {
    $posts_page = get_option('page_for_posts');
    if ($posts_page) {
        $all_link = get_permalink($posts_page);
    }
}

$all_active = (is_home() && !is_front_page());
$all_class = $all_active
    ? 'bg-slate-900 text-white'
    : 'bg-slate-100 text-slate-600 hover:bg-slate-200';

$categories = get_categories(array(
    'orderby' => 'name',
    'parent' => 0,
));
?>
<section class="py-6 border-b border-slate-100 bg-white sticky top-20 z-40 shadow-sm backdrop-blur-md bg-white/90">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap justify-center gap-2">
            <a href="<?php echo esc_url($all_link); ?>"
                class="px-5 py-2 rounded-full text-sm font-medium transition <?php echo esc_attr($all_class); ?>">
                <?php esc_html_e('ทั้งหมด', 'tonkla'); ?>
            </a>

            <?php foreach ($categories as $category):
                $is_active = false;
                if (is_category()) {
                    $current_cat = get_queried_object();
                    if ((int) $current_cat->term_id === (int) $category->term_id
                        || cat_is_ancestor_of($category->term_id, $current_cat->term_id)
                    ) {
                        $is_active = true;
                    }
                }

                $class = $is_active
                    ? 'bg-slate-900 text-white'
                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200';
                ?>
                <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>"
                    class="px-5 py-2 rounded-full text-sm font-medium transition <?php echo esc_attr($class); ?>">
                    <?php echo esc_html($category->name); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
