<?php
/**
 * The sidebar for pages (Left Side)
 */

defined( 'ABSPATH' ) || exit;
?>

<!-- Table of Contents -->
<?php
// Check if ToC is enabled
if (tonkla_is_toc_enabled()) {
    $slug = get_post_field('post_name', get_queried_object_id());
    if (in_array($slug, ['privacy-policy', 'terms-of-service', 'privacy', 'terms'])) {
        get_template_part('parts/toc/legal-toc');
    } else {
        get_template_part('parts/toc/table-of-contents');
    }
}
?>

<!-- Dynamic Sidebar Area -->
<?php if ( is_active_sidebar( 'sidebar-page' ) ) : ?>
	<div class="mt-6">
		<?php dynamic_sidebar( 'sidebar-page' ); ?>
	</div>
<?php endif; ?>