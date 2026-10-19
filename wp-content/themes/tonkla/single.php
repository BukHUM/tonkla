<?php
/**
 * The template for displaying all single posts
 */

defined( 'ABSPATH' ) || exit;

get_header();

$tonkla_toc_enabled = tonkla_is_toc_enabled();
$tonkla_toc_items   = $tonkla_toc_enabled ? tonkla_get_toc_items() : array();
$tonkla_show_toc    = $tonkla_toc_enabled && ! empty( $tonkla_toc_items );
?>

<main class="tonkla-single-main flex-grow pb-10 md:pb-16 bg-slate-50">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

		<!--
			Top gap = .tonkla-single-main padding (nav + 1rem), not mt —
			avoids stacking and keeps breadcrumb out from under the fixed header.
		-->
		<nav class="flex mb-4 text-sm font-medium text-slate-500" aria-label="Breadcrumb">
			<ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-brand transition">หน้าแรก</a></li>
				<li><i class="fa-solid fa-chevron-right text-[10px]"></i></li>
				<?php
				$blog_link = get_post_type_archive_link( 'post' );
				if ( 'posts' === get_option( 'show_on_front' ) ) {
					$blog_link = home_url( '/' );
				} else {
					$posts_page = get_option( 'page_for_posts' );
					if ( $posts_page ) {
						$blog_link = get_permalink( $posts_page );
					}
				}
				?>
				<li><a href="<?php echo esc_url( $blog_link ); ?>" class="hover:text-brand transition">บทความ</a></li>
				<li><i class="fa-solid fa-chevron-right text-[10px]"></i></li>
				<li class="text-slate-600 truncate max-w-[200px] md:max-w-none" aria-current="page">
					<?php the_title(); ?>
				</li>
			</ol>
		</nav>

		<!--
			Main row fills max-w-7xl (same as header logo).
			TOC sits outside to the left via absolute — does not shrink the article.
		-->
		<div class="flex flex-col lg:flex-row gap-6">

			<div class="lg:w-2/3 min-w-0 relative">
				<?php if ( $tonkla_show_toc ) : ?>
					<aside class="hidden xl:block absolute right-full top-0 h-full w-64 xl:w-72 pr-6" aria-label="<?php echo esc_attr__( 'สารบัญ', 'tonkla' ); ?>">
						<div class="tonkla-sticky-panel">
							<?php get_template_part( 'parts/toc/table-of-contents' ); ?>
						</div>
					</aside>
				<?php endif; ?>

				<?php
				while ( have_posts() ) :
					the_post();

					// Compact TOC below xl — sticky under fixed nav (collapsed by default on phone).
					if ( $tonkla_show_toc ) :
						?>
						<div class="xl:hidden mb-6 tonkla-sticky-panel tonkla-sticky-toc-mobile">
							<?php get_template_part( 'parts/toc/table-of-contents' ); ?>
						</div>
						<?php
					endif;

					get_template_part( 'parts/content/content', 'single' );
				endwhile;
				?>
			</div>

			<aside class="lg:w-1/3 min-w-0">
				<div class="tonkla-sticky-panel">
					<?php get_sidebar( 'post' ); ?>
				</div>
			</aside>
		</div>
	</div>
</main>

<?php
get_footer();
