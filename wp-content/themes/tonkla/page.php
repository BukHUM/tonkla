<?php
/**
 * The template for displaying all pages
 */

defined( 'ABSPATH' ) || exit;

get_header();

$tonkla_show_sidebar = tonkla_is_toc_enabled() || is_active_sidebar( 'sidebar-page' );
?>

<main class="flex-grow bg-slate-50">
	<?php get_template_part( 'parts/hero/hero', 'page' ); ?>

	<!-- Content — spacing aligned with single.php (gap-6 / sticky panel) -->
	<section class="py-6 md:py-8 pb-10 md:pb-16">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex flex-col lg:flex-row gap-6">

				<?php if ( $tonkla_show_sidebar ) : ?>
					<aside class="hidden lg:block w-72 flex-shrink-0 min-w-0">
						<div class="tonkla-sticky-panel space-y-6">
							<?php get_sidebar( 'page' ); ?>
						</div>
					</aside>
				<?php endif; ?>

				<article
					class="min-w-0 flex-grow bg-white rounded-2xl p-5 md:p-8 shadow-sm border border-slate-100 prose prose-slate max-w-none prose-headings:font-heading prose-headings:font-bold prose-a:text-brand hover:prose-a:text-brand-dark prose-li:marker:text-brand-light">
					<?php
					while ( have_posts() ) :
						the_post();

						if ( tonkla_is_toc_enabled() ) :
							$slug     = get_post_field( 'post_name', get_queried_object_id() );
							$is_legal = in_array( $slug, array( 'privacy-policy', 'terms-of-service', 'privacy', 'terms' ), true );
							?>
							<div class="lg:hidden mb-6 not-prose tonkla-sticky-panel tonkla-sticky-toc-mobile">
								<?php
								if ( $is_legal ) {
									get_template_part( 'parts/toc/legal-toc' );
								} else {
									get_template_part( 'parts/toc/table-of-contents' );
								}
								?>
							</div>
							<?php
						endif;

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
