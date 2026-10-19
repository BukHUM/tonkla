<?php
/**
 * Hero Template for Standard Pages
 *
 * Outer pad clears fixed nav; inner py keeps top/bottom visual gap equal.
 */

defined( 'ABSPATH' ) || exit;

$title         = get_the_title();
$modified_date = get_the_modified_date( 'j F Y' );
$grid_pattern_url = get_template_directory_uri() . '/assets/images/grid-pattern.png';
?>

<section class="tonkla-page-hero bg-slate-900 border-b border-slate-800 relative overflow-hidden">
	<div
		class="absolute inset-0 opacity-[0.03] invert"
		style="background-image: url('<?php echo esc_url( $grid_pattern_url ); ?>');">
	</div>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 py-10 md:py-12">
		<div class="w-full">
			<span
				class="inline-block py-1 px-3 rounded-full bg-slate-800/80 text-brand-light border border-slate-700 text-xs font-bold tracking-wide mb-4 uppercase backdrop-blur-sm">
				<?php
				$slug = get_post_field( 'post_name', get_queried_object_id() );
				if ( in_array( $slug, array( 'privacy-policy', 'terms-of-service', 'privacy', 'terms' ), true ) ) {
					echo 'LEGAL DOCUMENTS';
				} else {
					echo esc_html( get_post_type() );
				}
				?>
			</span>
			<h1 class="text-2xl md:text-4xl font-heading font-bold text-white mb-4 leading-tight">
				<?php echo esc_html( $title ); ?>
			</h1>
			<p class="text-base md:text-lg text-slate-400 mb-0">
				มีผลบังคับใช้: <span class="font-bold text-white">
					<?php echo esc_html( $modified_date ); ?>
				</span>
			</p>
			<?php if ( has_excerpt() ) : ?>
				<p class="text-sm text-slate-500 mb-0">
					<?php echo esc_html( get_the_excerpt() ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>
</section>
