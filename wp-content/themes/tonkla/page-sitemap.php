<?php
/**
 * Template Name: Sitemap
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/sitemap.php';

get_header();

$sitemap_posts_query = new WP_Query(
	array(
		'post_type'              => 'post',
		'post_status'            => 'publish',
		'posts_per_page'         => 500,
		'no_found_rows'          => true,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
		'orderby'                => 'date',
		'order'                  => 'DESC',
	)
);

$posts_by_category = array();
if ( $sitemap_posts_query->have_posts() ) {
	foreach ( $sitemap_posts_query->posts as $sitemap_post ) {
		if ( tonkla_sitemap_is_noindex( $sitemap_post->ID ) ) {
			continue;
		}
		$terms = get_the_category( $sitemap_post->ID );
		foreach ( $terms as $term ) {
			if ( 1 === (int) $term->term_id ) {
				continue;
			}
			if ( ! isset( $posts_by_category[ $term->term_id ] ) ) {
				$posts_by_category[ $term->term_id ] = array(
					'term'  => $term,
					'posts' => array(),
				);
			}
			$posts_by_category[ $term->term_id ]['posts'][] = $sitemap_post;
		}
	}
}

$public_types = get_post_types(
	array(
		'public'   => true,
		'_builtin' => false,
	),
	'objects'
);

$cpt_groups = array();
if ( ! empty( $public_types ) ) {
	$cpt_names = array_keys( $public_types );
	$cpt_query = new WP_Query(
		array(
			'post_type'              => $cpt_names,
			'post_status'            => 'publish',
			'posts_per_page'         => 200,
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
			'orderby'                => 'title',
			'order'                  => 'ASC',
		)
	);

	foreach ( $cpt_query->posts as $cpt_post ) {
		if ( tonkla_sitemap_is_noindex( $cpt_post->ID ) ) {
			continue;
		}
		$type = $cpt_post->post_type;
		if ( ! isset( $cpt_groups[ $type ] ) ) {
			$cpt_groups[ $type ] = array();
		}
		$cpt_groups[ $type ][] = $cpt_post;
	}
}
?>

<main class="flex-grow bg-slate-50 pt-20">
	<div class="bg-white border-b border-slate-100 py-12 md:py-16">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
			<h1 class="text-3xl md:text-4xl font-heading font-bold text-slate-900 mb-4">
				<?php the_title(); ?>
			</h1>
			<p class="text-lg text-slate-600 max-w-2xl mx-auto">
				แผนผังเว็บไซต์รวบรวมหน้าทั้งหมดเพื่อการเข้าถึงข้อมูลที่สะดวกรวดเร็ว
			</p>
		</div>
	</div>

	<div class="py-12 md:py-20">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-start">

				<div class="bg-white p-8 rounded-xl border border-slate-100">
					<h2 class="text-xl font-heading font-bold text-slate-900 mb-6 flex items-center gap-3 pb-4 border-b border-slate-50">
						<span class="w-10 h-10 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center" aria-hidden="true">
							<i class="fa-regular fa-file-lines"></i>
						</span>
						หน้าทั่วไป
						<span class="text-sm font-normal text-slate-400 ml-auto">(Pages)</span>
					</h2>
					<div class="sitemap-list">
						<ul>
							<?php
							wp_list_pages(
								array(
									'title_li'    => '',
									'post_status' => 'publish',
								)
							);
							?>
						</ul>
					</div>
				</div>

				<div class="bg-white p-8 rounded-xl border border-slate-100">
					<h2 class="text-xl font-heading font-bold text-slate-900 mb-6 flex items-center gap-3 pb-4 border-b border-slate-50">
						<span class="w-10 h-10 rounded-lg bg-brand/10 text-brand flex items-center justify-center" aria-hidden="true">
							<i class="fa-solid fa-newspaper"></i>
						</span>
						บทความ
						<span class="text-sm font-normal text-slate-400 ml-auto">(Articles)</span>
					</h2>
					<div class="space-y-8">
						<?php if ( empty( $posts_by_category ) ) : ?>
							<p class="text-sm text-slate-400"><?php esc_html_e( 'No articles found.', 'tonkla' ); ?></p>
						<?php else : ?>
							<?php foreach ( $posts_by_category as $group ) : ?>
								<div>
									<h3 class="font-bold text-slate-800 mb-3 flex items-center gap-2">
										<span class="w-2 h-2 rounded-full bg-brand" aria-hidden="true"></span>
										<?php echo esc_html( $group['term']->name ); ?>
										<span class="text-xs font-normal text-slate-400">(<?php echo esc_html( (string) count( $group['posts'] ) ); ?>)</span>
									</h3>
									<?php tonkla_sitemap_render_post_list( $group['posts'] ); ?>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>

				<div class="flex flex-col gap-8">
					<?php foreach ( $cpt_groups as $type_name => $type_posts ) : ?>
						<?php
						$pt         = $public_types[ $type_name ];
						$icon_class = 'fa-solid fa-layer-group';
						$bg_class   = 'bg-slate-50 text-slate-500';

						if ( false !== strpos( $type_name, 'service' ) ) {
							$icon_class = 'fa-solid fa-screwdriver-wrench';
							$bg_class   = 'bg-indigo-50 text-indigo-500';
						} elseif ( false !== strpos( $type_name, 'product' ) ) {
							$icon_class = 'fa-solid fa-box';
							$bg_class   = 'bg-orange-50 text-orange-500';
						} elseif ( false !== strpos( $type_name, 'portfolio' ) || false !== strpos( $type_name, 'work' ) ) {
							$icon_class = 'fa-solid fa-briefcase';
							$bg_class   = 'bg-pink-50 text-pink-500';
						}
						?>
						<div class="bg-white p-8 rounded-xl border border-slate-100">
							<h2 class="text-xl font-heading font-bold text-slate-900 mb-6 flex items-center gap-3 pb-4 border-b border-slate-50">
								<span class="w-10 h-10 rounded-lg <?php echo esc_attr( $bg_class ); ?> flex items-center justify-center" aria-hidden="true">
									<i class="<?php echo esc_attr( $icon_class ); ?>"></i>
								</span>
								<?php echo esc_html( $pt->labels->name ); ?>
							</h2>
							<?php tonkla_sitemap_render_post_list( $type_posts ); ?>
						</div>
					<?php endforeach; ?>
				</div>

			</div>
		</div>
	</div>
</main>

<?php
get_footer();
