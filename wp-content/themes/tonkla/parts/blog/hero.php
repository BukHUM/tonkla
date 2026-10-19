<?php
/**
 * Blog archive hero + search
 *
 * @var array $args {
 *     @type string $title
 *     @type string $description
 *     @type string $badge_text
 *     @type string $search_id
 * }
 */

if (!defined('ABSPATH')) {
    exit;
}

$title = isset($args['title']) ? $args['title'] : '';
$description = isset($args['description']) ? $args['description'] : '';
$badge_text = isset($args['badge_text']) ? $args['badge_text'] : 'KNOWLEDGE BASE';
$search_id = isset($args['search_id']) ? $args['search_id'] : 'tonkla-blog-search';
?>
<section class="pt-32 pb-12 bg-white relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-brand/5 rounded-full blur-3xl opacity-50 -mr-20 -mt-20"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span
            class="inline-block py-1 px-3 rounded-full bg-slate-100 border border-slate-200 text-caption tracking-wide mb-6">
            <?php echo esc_html($badge_text); ?>
        </span>
        <h1 class="text-4xl md:text-5xl font-heading font-bold text-slate-900 mb-6">
            <?php echo esc_html($title); ?>
        </h1>
        <p class="text-lg text-slate-600 mb-8 max-w-2xl mx-auto">
            <?php echo wp_kses_post($description); ?>
        </p>

        <div class="max-w-xl mx-auto relative">
            <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                <label for="<?php echo esc_attr($search_id); ?>" class="sr-only"><?php echo esc_html_x('ค้นหาบทความ', 'label', 'tonkla'); ?></label>
                <input type="search" id="<?php echo esc_attr($search_id); ?>"
                    class="search-field w-full pl-12 pr-4 py-3 rounded-full border border-slate-300 focus-visible:border-brand transition"
                    placeholder="<?php echo esc_attr_x('ค้นหาบทความ...', 'placeholder', 'tonkla'); ?>"
                    value="<?php echo esc_attr(get_search_query()); ?>" name="s" />
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true"></i>
            </form>
        </div>
    </div>
</section>
