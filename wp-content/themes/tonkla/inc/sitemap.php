<?php
/**
 * Sitemap template helpers.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a post is marked noindex in Rank Math.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function tonkla_sitemap_is_noindex( $post_id ) {
	$robots = get_post_meta( $post_id, 'rank_math_robots', true );
	return false !== strpos( (string) $robots, 'noindex' );
}

/**
 * Render an expandable post list (first 5, then the rest).
 *
 * @param WP_Post[] $posts Posts to list.
 */
function tonkla_sitemap_render_post_list( $posts ) {
	$limit       = 5;
	$count       = count( $posts );
	$has_more    = $count > $limit;
	$first_batch = array_slice( $posts, 0, $limit );
	$rest_batch  = $has_more ? array_slice( $posts, $limit ) : array();
	$more_count  = $count - $limit;
	?>
	<div class="sitemap-list" x-data="{ expanded: false }">
		<ul>
			<?php foreach ( $first_batch as $post_item ) : ?>
				<li>
					<a href="<?php echo esc_url( get_permalink( $post_item ) ); ?>">
						<?php echo esc_html( get_the_title( $post_item ) ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ( $has_more ) : ?>
			<ul x-show="expanded" x-collapse style="display: none;">
				<?php foreach ( $rest_batch as $post_item ) : ?>
					<li>
						<a href="<?php echo esc_url( get_permalink( $post_item ) ); ?>">
							<?php echo esc_html( get_the_title( $post_item ) ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
			<button type="button" @click="expanded = !expanded"
				class="text-xs font-medium text-brand hover:underline mt-2 flex items-center gap-1 focus-visible:underline">
				<span x-text="expanded ? 'ย่อรายการ (Show Less)' : 'ดูทั้งหมด (Show All +<?php echo esc_attr( (string) $more_count ); ?>)'"></span>
				<i class="fa-solid" :class="expanded ? 'fa-angle-up' : 'fa-angle-down'" aria-hidden="true"></i>
			</button>
		<?php endif; ?>
	</div>
	<?php
}
