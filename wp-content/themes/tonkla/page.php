<?php
/**
 * The template for displaying all pages
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- HEADER START handled by get_header() -->

<main class="flex-grow">
    <!-- Hero Section -->
    <?php get_template_part('parts/hero/hero', 'page'); ?>

    <!-- Content Section -->
    <section class="py-12 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-12">

                <!-- Sidebar (Left) -->
                <?php
                $show_sidebar = tonkla_is_toc_enabled() || is_active_sidebar('sidebar-page');
                if ($show_sidebar):
                    ?>
                    <aside class="hidden lg:block w-72 flex-shrink-0">
                        <div class="sticky top-28 space-y-8">
                            <?php get_sidebar('page'); ?>
                        </div>
                    </aside>
                <?php endif; ?>

                <!-- Main Content -->
                <article
                    class="flex-grow bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-slate-100 prose prose-slate prose max-w-none prose-headings:font-heading prose-headings:font-bold prose-a:text-brand hover:prose-a:text-brand-dark prose-li:marker:text-brand-light">
                    <?php
                    while (have_posts()):
                        the_post();

                        // Mobile/Tablet TOC (Standard Flow)
                        // Visible only when Sidebar is hidden (lg:hidden)
                        if (tonkla_is_toc_enabled()):
                            $slug = get_post_field('post_name', get_queried_object_id());
                            $is_legal = in_array($slug, ['privacy-policy', 'terms-of-service', 'privacy', 'terms']);
                            ?>
                            <div class="block lg:hidden mb-8 not-prose">
                                <?php
                                if ($is_legal) {
                                    get_template_part('parts/toc/legal-toc');
                                } else {
                                    get_template_part('parts/toc/table-of-contents');
                                }
                                ?>
                            </div>
                        <?php endif;

                        the_content();
                    endwhile;
                    ?>
                </article>

            </div>
        </div>
    </section>
</main>

<?php
get_footer();
