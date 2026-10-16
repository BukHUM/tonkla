<?php
/**
 * Dedicated Warranty lookup page template (plugin-owned layout).
 *
 * Selected via Page Attributes → Template → Warranty (IT Warranty Tracker).
 *
 * @package ITWT
 */

defined( 'ABSPATH' ) || exit;

use ITWT\Settings;

get_header();

$settings     = Settings::get();
$page_title   = get_the_title();
$portal_title = (string) ( $settings['portal_title'] ?? '' );
$portal_desc  = (string) ( $settings['portal_description'] ?? '' );
$heading      = '' !== $portal_title ? $portal_title : (string) $page_title;
$logo_id      = (int) ( $settings['logo_attachment_id'] ?? 0 );
$logo_url     = $logo_id > 0 ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
$page_theme   = sanitize_key( (string) ( $settings['portal_theme'] ?? 'soft' ) );
if ( ! in_array( $page_theme, array( 'soft', 'minimal', 'dense' ), true ) ) {
	$page_theme = 'soft';
}
$accent_raw = sanitize_hex_color( (string) ( $settings['portal_accent_color'] ?? '' ) );
$page_style = $accent_raw ? '--itwt-accent:' . $accent_raw . ';' : '';
?>
<main id="primary" class="itwt-page" role="main" data-theme="<?php echo esc_attr( $page_theme ); ?>"<?php echo $page_style ? ' style="' . esc_attr( $page_style ) . '"' : ''; ?>>
	<div class="itwt-page__banner">
		<div class="itwt-page__banner-inner">
			<?php if ( is_string( $logo_url ) && '' !== $logo_url ) : ?>
				<img class="itwt-page__logo" src="<?php echo esc_url( $logo_url ); ?>" alt="" width="140" height="40" loading="eager" />
			<?php endif; ?>
			<p class="itwt-page__eyebrow"><?php esc_html_e( 'IT Warranty Tracker', 'it-warranty-tracker' ); ?></p>
			<h1 class="itwt-page__heading"><?php echo esc_html( $heading ); ?></h1>
			<?php if ( '' !== $portal_desc ) : ?>
				<p class="itwt-page__lead"><?php echo esc_html( $portal_desc ); ?></p>
			<?php else : ?>
				<p class="itwt-page__lead"><?php esc_html_e( 'กรอก Service Tag หรือ Serial Number เพื่อดูสถานะประกันและสัญญา MA', 'it-warranty-tracker' ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<div class="itwt-page__shell">
		<?php
		while ( have_posts() ) :
			the_post();
			$raw = (string) get_post_field( 'post_content', get_the_ID() );
			if ( has_shortcode( $raw, 'itwt_warranty_lookup' ) ) {
				the_content();
			} else {
				// Template alone is enough — portal renders from plugin settings.
				echo do_shortcode( '[itwt_warranty_lookup title="" desc=""]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
