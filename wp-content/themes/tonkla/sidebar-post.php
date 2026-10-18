<?php
/**
 * The sidebar containing the main widget area.
 */

defined( 'ABSPATH' ) || exit;
?>

<!-- Standard Container — match article card rhythm on mobile -->
<div class="space-y-6 lg:space-y-8">

    <!-- Trending/Recent Posts Section -->
    <div class="bg-white p-5 md:p-6 lg:p-8 rounded-2xl md:rounded-3xl shadow-sm border border-slate-100">
        <h2 class="text-base md:text-lg font-heading font-bold text-slate-900 mb-4 md:mb-5 border-b border-slate-100 pb-3 md:pb-4">
            บทความที่แนะนำ
        </h2>
        <div class="space-y-4 md:space-y-5">
            <?php
            $recent_posts = new WP_Query(array(
                'posts_per_page' => 3,
                'post__not_in' => array(get_the_ID()), // Exclude current
                'ignore_sticky_posts' => 1,
                'no_found_rows' => true,
                'update_post_term_cache' => false,
            ));

            if ($recent_posts->have_posts()):
                while ($recent_posts->have_posts()):
                    $recent_posts->the_post();
                    ?>
                    <a href="<?php the_permalink(); ?>" class="group flex gap-4">
                        <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 bg-slate-100">
                            <?php if (has_post_thumbnail()): ?>
                                <?php the_post_thumbnail('thumbnail', array('class' => 'w-full h-full object-cover group-hover:scale-110 transition duration-500')); ?>
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                    <i class="fa-regular fa-image text-2xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow">
                            <h5
                                class="text-sm font-bold text-slate-800 line-clamp-2 leading-relaxed group-hover:text-brand transition">
                                <?php the_title(); ?>
                            </h5>
                            <span class="text-[10px] text-slate-400 mt-1 block uppercase">
                                <?php echo esc_html(get_the_date('j M Y')); ?>
                            </span>
                        </div>
                    </a>
                    <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
    </div>

    <!-- Categories Widget -->
    <div class="bg-white p-5 md:p-6 lg:p-8 rounded-2xl md:rounded-3xl shadow-sm border border-slate-100">
        <h2 class="text-base md:text-lg font-heading font-bold text-slate-900 mb-4 md:mb-5 border-b border-slate-100 pb-3 md:pb-4">
            หมวดหมู่
        </h2>
        <ul class="space-y-3">
            <?php
            $categories = get_categories(array(
                'orderby' => 'name',
                'parent' => 0
            ));
            foreach ($categories as $category):
                $active_class = (is_category($category->term_id)) ? 'text-brand font-bold' : 'text-slate-600 hover:text-brand';
                ?>
                <li class="flex items-center justify-between">
                    <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>"
                        class="text-sm transition <?php echo esc_attr($active_class); ?>">
                        <i class="fa-solid fa-angle-right text-xs mr-2 text-slate-300"></i>
                        <?php echo esc_html($category->name); ?>
                    </a>
                    <span class="text-xs text-slate-400 bg-slate-50 px-2 py-1 rounded-md">
                        <?php echo esc_html((string) $category->count); ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Newsletter Widget -->
    <div class="bg-brand p-5 md:p-6 lg:p-8 rounded-2xl md:rounded-3xl shadow-lg relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-8 -mt-8"></div>
        <p class="text-lg md:text-xl font-heading font-bold text-white mb-3 md:mb-4 relative z-10 text-center">
            รับความรู้ใหม่ๆ ก่อนใคร
        </p>
        <p class="text-white/80 text-center text-sm mb-4 md:mb-6 relative z-10 leading-relaxed">
            สมัครสมาชิกรับบทความและโปรโมชั่นพิเศษ ส่งตรงถึงอีเมลคุณ
        </p>
        <div class="relative z-10 text-center">
            <?php
            get_template_part(
                'parts/blog/newsletter-form',
                null,
                array(
                    'form_id'      => 'newsletter-form',
                    'email_id'     => 'tonkla-sidebar-newsletter-email',
                    'hp_id'        => 'tonkla-sidebar-hp',
                    'form_class'   => 'space-y-3 newsletter-form',
                    'input_class'  => 'w-full px-4 py-3 rounded-xl bg-white/20 border border-white/30 text-white placeholder:text-white/50 focus-visible:border-white text-sm',
                    'button_class' => 'w-full py-3 bg-white text-brand font-bold rounded-xl hover:bg-slate-50 transition border border-white',
                    'placeholder'  => __('อีเมลของคุณ', 'tonkla'),
                    'button_text'  => __('สมัครรับข่าวสาร', 'tonkla'),
                )
            );
            ?>
        </div>
    </div>

    <!-- Custom Ads Widget -->
    <?php if (carbon_get_theme_option('ad_sidebar_enable')):
        $ad_type = carbon_get_theme_option('ad_sidebar_type'); // code or image
        $show_label = carbon_get_theme_option('ad_sidebar_label');
        ?>
        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 text-center">
            <?php if ($show_label): ?>
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-widest block mb-2">Advertising</span>
            <?php endif; ?>

            <?php if ($ad_type === 'code'): ?>
                <div class="ad-container overflow-hidden">
                    <?php echo carbon_get_theme_option('ad_sidebar_code'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Admin-controlled ad embed (manage_options). ?>
                </div>
            <?php elseif ($ad_type === 'image'):
                $ad_img = carbon_get_theme_option('ad_sidebar_image');
                $ad_link = carbon_get_theme_option('ad_sidebar_link');
                if ($ad_img):
                    ?>
                    <a href="<?php echo esc_url($ad_link ?: '#'); ?>" target="_blank" rel="nofollow">
                        <?php echo wp_get_attachment_image($ad_img, 'medium', false, array('class' => 'w-full h-full object-cover rounded-xl')); ?>
                    </a>
                <?php else:
                    // Fallback Placeholder if Image Type selected but empty 
                    ?>
                    <div
                        class="h-64 bg-slate-100 rounded-xl flex flex-col items-center justify-center text-slate-400 border-2 border-dashed border-slate-200">
                        <i class="fa-solid fa-rectangle-ad text-4xl mb-2"></i>
                        <span class="text-xs font-semibold uppercase tracking-wider">Your Banner Here</span>
                        <span class="text-[10px] mt-1">300 x 250</span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Dynamic Sidebar Area -->
    <?php if (is_active_sidebar('sidebar-post')): ?>
        <?php dynamic_sidebar('sidebar-post'); ?>
    <?php endif; ?>
</div>