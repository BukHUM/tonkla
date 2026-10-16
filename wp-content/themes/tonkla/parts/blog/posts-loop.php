<?php
/**
 * Blog posts listing: featured + grid + pagination, or empty state
 *
 * @var array $args {
 *     @type string $empty_message
 *     @type bool   $show_home_link
 * }
 */

if (!defined('ABSPATH')) {
    exit;
}

$empty_message = isset($args['empty_message'])
    ? $args['empty_message']
    : __('ยังไม่มีบทความในขณะนี้', 'tonkla');
$show_home_link = !empty($args['show_home_link']);

if (!have_posts()):
    ?>
    <section class="py-20 bg-slate-50 text-center">
        <div class="max-w-7xl mx-auto px-4">
            <i class="fa-regular fa-folder-open text-6xl text-slate-300 mb-6"></i>
            <h2 class="text-2xl font-bold text-slate-900 mb-4"><?php esc_html_e('ไม่พบข้อมูล', 'tonkla'); ?></h2>
            <p class="text-slate-600"><?php echo esc_html($empty_message); ?></p>
            <?php if ($show_home_link): ?>
                <a href="<?php echo esc_url(home_url('/')); ?>"
                    class="inline-block mt-8 px-6 py-3 bg-brand text-white rounded-lg hover:bg-brand-dark transition"><?php esc_html_e('กลับสู่หน้าหลัก', 'tonkla'); ?></a>
            <?php endif; ?>
        </div>
    </section>
    <?php
    return;
endif;
?>

<?php if (!is_paged()):
    the_post();
    get_template_part('parts/blog/featured-post');
endif; ?>

<section class="py-12 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            while (have_posts()):
                the_post();
                get_template_part('parts/blog/post-card');
            endwhile;
            ?>
        </div>

        <div class="text-center mt-12">
            <?php
            the_posts_pagination(array(
                'mid_size' => 2,
                'prev_text' => __('&laquo; ก่อนหน้า', 'tonkla'),
                'next_text' => __('ถัดไป &raquo;', 'tonkla'),
                'class' => 'inline-flex gap-2',
            ));
            ?>
        </div>
    </div>
</section>
