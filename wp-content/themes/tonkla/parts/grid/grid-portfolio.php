<?php
/**
 * Portfolio grid module.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

$module = get_query_var( 'module' );
if ( ! is_array( $module ) ) {
	return;
}

$section_title       = isset( $module['section_title'] ) ? (string) $module['section_title'] : '';
$section_subtitle    = isset( $module['section_subtitle'] ) ? (string) $module['section_subtitle'] : '';
$section_description = isset( $module['section_description'] ) ? (string) $module['section_description'] : '';
$layout              = isset( $module['layout'] ) ? (string) $module['layout'] : 'cols-3';
$bg_color            = isset( $module['bg_color'] ) ? (string) $module['bg_color'] : 'white';
$items               = isset( $module['items'] ) && is_array( $module['items'] ) ? $module['items'] : array();
$view_all_text       = isset( $module['view_all_text'] ) ? (string) $module['view_all_text'] : '';

$bg_class    = 'bg-' . sanitize_html_class( $bg_color );
$grid_class  = ( 'cols-2' === $layout ) ? 'grid-cols-1 md:grid-cols-2' : 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3';

/**
 * Resolve link from link_type / link / link_content pattern.
 *
 * @param array<string,mixed> $row        Row data.
 * @param string              $type_key   Type field.
 * @param string              $url_key    URL field.
 * @param string              $content_key Association field.
 * @return string
 */
$tonkla_resolve_link = static function ( array $row, $type_key = 'link_type', $url_key = 'link', $content_key = 'link_content' ) {
	$type = isset( $row[ $type_key ] ) ? (string) $row[ $type_key ] : 'custom';
	if ( 'post' === $type && ! empty( $row[ $content_key ][0]['id'] ) ) {
		$url = get_permalink( (int) $row[ $content_key ][0]['id'] );
		return $url ? $url : '';
	}
	return isset( $row[ $url_key ] ) ? (string) $row[ $url_key ] : '';
};

$view_all_url = $tonkla_resolve_link(
	$module,
	'view_all_link_type',
	'view_all_link',
	'view_all_link_content'
);
?>

<section class="py-20 <?php echo esc_attr( $bg_class ); ?>">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<?php if ( '' !== $section_title ) : ?>
			<div class="text-center mb-14">
				<?php if ( '' !== $section_subtitle ) : ?>
					<span class="text-brand font-bold tracking-wider uppercase text-sm">
						<?php echo esc_html( $section_subtitle ); ?>
					</span>
				<?php endif; ?>
				<h2 class="text-3xl md:text-4xl font-heading font-bold text-slate-900 mt-2">
					<?php echo esc_html( $section_title ); ?>
				</h2>
				<div class="h-1 w-20 bg-brand mx-auto rounded-full mt-4"></div>
				<?php if ( '' !== $section_description ) : ?>
					<p class="text-slate-600 mt-6 max-w-2xl mx-auto text-lg leading-relaxed">
						<?php echo nl2br( esc_html( $section_description ) ); ?>
					</p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $items ) ) : ?>
			<div class="grid <?php echo esc_attr( $grid_class ); ?> gap-8">
				<?php
				foreach ( $items as $item ) :
					if ( ! is_array( $item ) ) {
						continue;
					}
					$title       = isset( $item['title'] ) ? (string) $item['title'] : '';
					$category    = isset( $item['category'] ) ? (string) $item['category'] : '';
					$client      = isset( $item['client'] ) ? (string) $item['client'] : '';
					$year        = isset( $item['year'] ) ? (string) $item['year'] : '';
					$description = isset( $item['description'] ) ? (string) $item['description'] : '';
					$link_text   = isset( $item['link_text'] ) && '' !== $item['link_text']
						? (string) $item['link_text']
						: __( 'ดูรายละเอียด', 'tonkla' );
					$image_id    = ! empty( $item['image'] ) ? absint( $item['image'] ) : 0;
					$item_url    = $tonkla_resolve_link( $item );
					$meta_bits   = array_filter( array( $client, $year ) );
					?>
					<article class="group bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">
						<div class="relative aspect-[16/10] bg-slate-100 overflow-hidden">
							<?php
							if ( $image_id ) {
								echo wp_get_attachment_image(
									$image_id,
									'large',
									false,
									array(
										'class'   => 'w-full h-full object-cover transition duration-500 group-hover:scale-105',
										'loading' => 'lazy',
										'alt'     => $title,
									)
								);
							} else {
								echo '<div class="w-full h-full flex items-center justify-center text-slate-300"><i class="fa-solid fa-image text-4xl" aria-hidden="true"></i></div>';
							}
							?>
							<?php if ( '' !== $category ) : ?>
								<span class="absolute top-3 left-3 inline-flex items-center px-2.5 py-1 rounded-md bg-white/95 text-brand text-xs font-bold tracking-wide shadow-sm">
									<?php echo esc_html( $category ); ?>
								</span>
							<?php endif; ?>
						</div>

						<div class="p-6 flex flex-col flex-grow">
							<?php if ( ! empty( $meta_bits ) ) : ?>
								<p class="text-xs text-slate-400 font-medium mb-2">
									<?php echo esc_html( implode( ' · ', $meta_bits ) ); ?>
								</p>
							<?php endif; ?>

							<?php if ( '' !== $title ) : ?>
								<h3 class="text-lg font-heading font-bold text-slate-900 mb-2 group-hover:text-brand transition-colors">
									<?php if ( $item_url ) : ?>
										<a href="<?php echo esc_url( $item_url ); ?>" class="focus:outline-none focus-visible:ring-2 focus-visible:ring-brand rounded">
											<?php echo esc_html( $title ); ?>
										</a>
									<?php else : ?>
										<?php echo esc_html( $title ); ?>
									<?php endif; ?>
								</h3>
							<?php endif; ?>

							<?php if ( '' !== $description ) : ?>
								<p class="text-sm text-slate-500 leading-relaxed mb-4 flex-grow">
									<?php echo esc_html( $description ); ?>
								</p>
							<?php else : ?>
								<div class="flex-grow"></div>
							<?php endif; ?>

							<?php if ( $item_url ) : ?>
								<a href="<?php echo esc_url( $item_url ); ?>"
									class="inline-flex items-center gap-2 text-brand font-bold text-sm hover:underline mt-auto">
									<?php echo esc_html( $link_text ); ?>
									<i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
								</a>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $view_all_url && '' !== $view_all_text ) : ?>
			<div class="mt-12 text-center">
				<a href="<?php echo esc_url( $view_all_url ); ?>"
					class="inline-flex items-center gap-2 px-6 py-3 rounded-lg border border-slate-200 bg-white text-slate-800 font-heading font-bold hover:border-brand hover:text-brand transition">
					<?php echo esc_html( $view_all_text ); ?>
					<i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
