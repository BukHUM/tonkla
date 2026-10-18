<?php
/**
 * Template part for displaying single posts
 */

defined( 'ABSPATH' ) || exit;

$share_url           = rawurlencode( get_permalink() );
$share_title         = rawurlencode( get_the_title() );
$author_description  = get_the_author_meta( 'description' );
$categories          = get_the_category();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-2xl md:rounded-3xl shadow-sm border border-slate-100 overflow-hidden'); ?>>
    <!-- Featured Image -->
    <?php if (has_post_thumbnail()): ?>
        <div class="aspect-video relative overflow-hidden bg-slate-100">
            <?php the_post_thumbnail('full', array('class' => 'w-full h-full object-cover')); ?>

            <?php if (!empty($categories)): ?>
                <div class="absolute top-4 left-4 md:top-6 md:left-6">
                    <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>"
                        class="bg-brand-dark text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest shadow-lg hover:bg-brand transition">
                        <?php echo esc_html($categories[0]->name); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Article Body — mobile spacing scale: 16 / 24 -->
    <div class="p-5 md:p-8 lg:p-12">
        <!-- Meta -->
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-400 mb-5 md:mb-6 border-b border-slate-100 pb-5 md:pb-6">
            <div class="flex items-center gap-2">
                <?php echo get_avatar(get_the_author_meta('ID'), 32, '', '', array('class' => 'w-8 h-8 rounded-full border border-slate-100')); ?>
                <span class="text-slate-900 font-bold">
                    <?php the_author(); ?>
                </span>
            </div>
            <div class="flex items-center gap-1.5">
                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                <?php echo esc_html(get_the_date('j F Y')); ?>
            </div>

            <?php if (!empty($categories)): ?>
                <div class="flex items-center gap-1.5">
                    <i class="fa-regular fa-folder-open text-brand" aria-hidden="true"></i>
                    <span class="text-brand font-medium">
                        <?php echo esc_html($categories[0]->name); ?>
                    </span>
                </div>
            <?php endif; ?>

            <div class="flex items-center gap-1.5">
                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                <?php echo esc_html(tonkla_reading_time()); ?> นาทีในการอ่าน
            </div>
        </div>

        <h1 class="text-2xl md:text-3xl lg:text-4xl font-heading font-bold text-slate-900 mb-5 md:mb-6 leading-tight">
            <?php the_title(); ?>
        </h1>

        <!-- Content -->
        <div
            class="prose prose-slate prose max-w-none prose-headings:font-heading prose-headings:font-bold prose-a:text-brand prose-img:rounded-2xl pb-6 md:pb-8 border-b border-slate-100">
            <?php the_content(); ?>
        </div>

        <!-- Tags -->
        <?php
        $tags = get_the_tags();
        if ($tags): ?>
            <div class="py-5 border-b border-slate-100 flex flex-wrap gap-2">
                <?php foreach ($tags as $tag): ?>
                    <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>"
                        class="inline-block px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium hover:bg-brand hover:text-white transition">
                        #<?php echo esc_html($tag->name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Social Share -->
        <div class="pt-5 pb-0 md:pt-6 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <span class="text-sm font-bold text-slate-500 uppercase tracking-widest">Share this:</span>
                <div class="flex items-center gap-2">
                    <a href="<?php echo esc_url('https://www.facebook.com/sharer/sharer.php?u=' . $share_url); ?>" target="_blank" rel="noopener noreferrer"
                        class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center hover:-translate-y-1 transition duration-300 shadow-sm"
                        aria-label="<?php echo esc_attr__('Share on Facebook', 'tonkla'); ?>">
                        <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
                    </a>
                    <a href="<?php echo esc_url('https://twitter.com/intent/tweet?url=' . $share_url . '&text=' . $share_title); ?>"
                        target="_blank" rel="noopener noreferrer"
                        class="w-10 h-10 bg-sky-500 text-white rounded-full flex items-center justify-center hover:-translate-y-1 transition duration-300 shadow-sm"
                        aria-label="<?php echo esc_attr__('Share on X', 'tonkla'); ?>">
                        <i class="fa-brands fa-twitter" aria-hidden="true"></i>
                    </a>
                    <a href="<?php echo esc_url('https://social-plugins.line.me/lineit/share?url=' . $share_url); ?>" target="_blank" rel="noopener noreferrer"
                        class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center hover:-translate-y-1 transition duration-300 shadow-sm"
                        aria-label="<?php echo esc_attr__('Share on LINE', 'tonkla'); ?>">
                        <i class="fa-brands fa-line text-lg" aria-hidden="true"></i>
                    </a>
                    <button type="button" onclick="navigator.clipboard.writeText('<?php echo esc_js(get_permalink()); ?>')"
                        class="w-10 h-10 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center hover:-translate-y-1 transition duration-300 shadow-sm"
                        aria-label="<?php echo esc_attr__('Copy link', 'tonkla'); ?>">
                        <i class="fa-solid fa-link" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</article>

<!-- Author Info -->
<div
    class="bg-white p-5 md:p-8 rounded-2xl md:rounded-3xl shadow-sm border border-slate-100 flex flex-col md:flex-row items-center md:items-start gap-4 md:gap-6 text-center md:text-left">
    <?php echo get_avatar(get_the_author_meta('ID'), 96, '', '', array('class' => 'w-20 h-20 md:w-24 md:h-24 rounded-2xl object-cover border-4 border-slate-50')); ?>
    <div>
        <p class="text-lg md:text-xl font-heading font-bold text-slate-900 mb-1 md:mb-2">
            <?php the_author(); ?>
        </p>
        <p class="text-sm text-slate-500 mb-0 leading-relaxed">
            <?php echo esc_html($author_description ?: 'System Security Architect @ Tonkla IT. ผู้เชี่ยวชาญด้าน Cloud และ Network Security'); ?>
        </p>
    </div>
</div>