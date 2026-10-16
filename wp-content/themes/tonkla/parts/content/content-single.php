<?php
/**
 * Template part for displaying single posts
 */

defined( 'ABSPATH' ) || exit;

$share_url = rawurlencode(get_permalink());
$share_title = rawurlencode(get_the_title());
$author_description = get_the_author_meta('description');
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden'); ?>>
    <!-- Featured Image -->
    <?php if (has_post_thumbnail()): ?>
        <div class="aspect-video relative overflow-hidden bg-slate-100">
            <?php the_post_thumbnail('full', array('class' => 'w-full h-full object-cover')); ?>

            <?php
            // Highlight/Category Badge
            $categories = get_the_category();
            if (!empty($categories)): ?>
                <div class="absolute top-6 left-6">
                    <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>"
                        class="bg-brand text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest shadow-lg hover:bg-brand-dark transition">
                        <?php echo esc_html($categories[0]->name); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Article Body -->
    <div class="p-6 md:p-12">
        <!-- Meta -->
        <div class="flex flex-wrap items-center gap-6 text-sm text-slate-400 mb-8 border-b border-slate-50 pb-8">
            <div class="flex items-center gap-2">
                <?php echo get_avatar(get_the_author_meta('ID'), 32, '', '', array('class' => 'w-8 h-8 rounded-full border border-slate-100')); ?>
                <span class="text-slate-900 font-bold">
                    <?php the_author(); ?>
                </span>
            </div>
            <div class="flex items-center gap-1.5">
                <i class="fa-regular fa-calendar"></i>
                <?php echo esc_html(get_the_date('j F Y')); ?>
            </div>

            <?php if (!empty($categories)): ?>
                <div class="flex items-center gap-1.5">
                    <i class="fa-regular fa-folder-open text-brand"></i>
                    <span class="text-brand font-medium">
                        <?php echo esc_html($categories[0]->name); ?>
                    </span>
                </div>
            <?php endif; ?>

            <div class="flex items-center gap-1.5">
                <i class="fa-regular fa-clock"></i>
                <?php echo esc_html(tonkla_reading_time()); ?> นาทีในการอ่าน
            </div>
        </div>

        <h1 class="text-2xl md:text-3xl lg:text-4xl font-heading font-bold text-slate-900 mb-8 leading-tight">
            <?php the_title(); ?>
        </h1>

        <!-- Content -->
        <div
            class="prose prose-slate prose max-w-none prose-headings:font-heading prose-headings:font-bold prose-a:text-brand prose-img:rounded-2xl pb-12 border-b border-slate-100">
            <?php the_content(); ?>
        </div>

        <!-- Tags -->
        <?php
        $tags = get_the_tags();
        if ($tags): ?>
            <div class="py-6 border-b border-slate-100 flex flex-wrap gap-2">
                <?php foreach ($tags as $tag): ?>
                    <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>"
                        class="inline-block px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium hover:bg-brand hover:text-white transition">
                        #<?php echo esc_html($tag->name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Social Share (Placeholder for now, can be dynamic later) -->
        <div class="py-8 flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <span class="text-sm font-bold text-slate-500 uppercase tracking-widest">Share this:</span>
                <div class="flex items-center gap-2">
                    <a href="<?php echo esc_url('https://www.facebook.com/sharer/sharer.php?u=' . $share_url); ?>" target="_blank" rel="noopener noreferrer"
                        class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center hover:-translate-y-1 transition duration-300 shadow-sm">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    <a href="<?php echo esc_url('https://twitter.com/intent/tweet?url=' . $share_url . '&text=' . $share_title); ?>"
                        target="_blank" rel="noopener noreferrer"
                        class="w-10 h-10 bg-sky-500 text-white rounded-full flex items-center justify-center hover:-translate-y-1 transition duration-300 shadow-sm">
                        <i class="fa-brands fa-twitter"></i>
                    </a>
                    <a href="<?php echo esc_url('https://social-plugins.line.me/lineit/share?url=' . $share_url); ?>" target="_blank" rel="noopener noreferrer"
                        class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center hover:-translate-y-1 transition duration-300 shadow-sm">
                        <i class="fa-brands fa-line text-lg"></i>
                    </a>
                    <button type="button" onclick="navigator.clipboard.writeText('<?php echo esc_js(get_permalink()); ?>')"
                        class="w-10 h-10 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center hover:-translate-y-1 transition duration-300 shadow-sm">
                        <i class="fa-solid fa-link"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</article>

<!-- Author Info -->
<div
    class="mt-8 bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex flex-col md:flex-row items-center md:items-start gap-6 text-center md:text-left">
    <?php echo get_avatar(get_the_author_meta('ID'), 96, '', '', array('class' => 'w-24 h-24 rounded-2xl object-cover border-4 border-slate-50')); ?>
    <div>
        <h4 class="text-xl font-heading font-bold text-slate-900 mb-2">
            <?php the_author(); ?>
        </h4>
        <p class="text-sm text-slate-500 mb-0">
            <?php echo esc_html($author_description ?: 'System Security Architect @ Tonkla IT. ผู้เชี่ยวชาญด้าน Cloud และ Network Security'); ?>
        </p>
    </div>
</div>