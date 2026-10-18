<?php
/**
 * The template for displaying all single posts
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- HEADER START handled by get_header(), Layout adjustments below -->

<main class="flex-grow pt-24 lg:pt-28 pb-10 md:pb-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumb -->
        <nav class="flex mb-4 md:mb-6 text-sm font-medium" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-slate-400">
                <li><a href="<?php echo esc_url(home_url('/')); ?>" class="hover:text-brand transition">หน้าแรก</a></li>
                <li><i class="fa-solid fa-chevron-right text-[10px]"></i></li>
                <?php
                $blog_link = get_post_type_archive_link('post');
                if ('posts' == get_option('show_on_front')) {
                    $blog_link = home_url('/');
                } else {
                    $posts_page = get_option('page_for_posts');
                    if ($posts_page) {
                        $blog_link = get_permalink($posts_page);
                    }
                }
                ?>
                <li><a href="<?php echo esc_url($blog_link); ?>" class="hover:text-brand transition">บทความ</a></li>
                <li><i class="fa-solid fa-chevron-right text-[10px]"></i></li>
                <li class="text-slate-600 truncate max-w-[200px] md:max-w-none" aria-current="page">
                    <?php the_title(); ?>
                </li>
            </ol>
        </nav>

        <div class="flex flex-col lg:flex-row gap-6 lg:gap-12">

            <!-- Left Content Area -->
            <div class="lg:w-2/3 relative space-y-6">
                <?php
                $tonkla_toc_enabled = tonkla_is_toc_enabled();
                if ($tonkla_toc_enabled):
                    ?>
                    <div class="hidden 2xl:block absolute right-full top-0 h-full w-72 pr-12">
                        <div class="sticky top-28">
                            <?php get_template_part('parts/toc/table-of-contents'); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                while (have_posts()):
                    the_post();

                    if ($tonkla_toc_enabled):
                        ?>
                        <div class="2xl:hidden">
                            <?php get_template_part('parts/toc/table-of-contents'); ?>
                        </div>
                        <?php
                    endif;

                    get_template_part('parts/content/content', 'single');
                endwhile;
                ?>
            </div>

            <!-- Right Sidebar -->
            <aside class="lg:w-1/3 space-y-6 lg:space-y-8">
                <?php get_sidebar('post'); ?>
            </aside>
        </div>
    </div>
</main>

<?php
get_footer();
