<?php
/**
 * Demo import UI markup for Tools screen.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render demo import form HTML for Carbon Fields.
 *
 * @return string
 */
function tonkla_render_demo_import_ui() {
	$groups = array(
		__( 'Reference Pages', 'tonkla' )     => array(
			'home'    => array( 'Home', 'Main Landing Page' ),
			'about'   => array( 'About Us', 'Company Info & Team' ),
			'contact' => array( 'Contact Us', 'Maps & Form' ),
			'works'   => array( 'Works', 'Portfolio / Case Studies' ),
		),
		__( 'Services & Products', 'tonkla' ) => array(
			'web_dev'     => array( 'Web Application', 'Custom Dev Service' ),
			'hosting'     => array( 'Web Hosting', 'Pricing Tables' ),
			'n8n'         => array( 'n8n Automation', 'Workflow Service' ),
			'domains'     => array( 'Domains', 'Domain Search' ),
			'pentest'     => array( 'Pentest', 'Security Service' ),
			'proxmox'     => array( 'Proxmox', 'Server Solutions' ),
			'ssl'         => array( 'SSL Certificates', 'HTTPS Security' ),
			'system_impl' => array( 'System Implementation', 'IT Implementation' ),
			'mobile_app'  => array( 'Mobile App', 'App Development' ),
		),
		__( 'Legal & Components', 'tonkla' )  => array(
			'solutions' => array( 'Solutions (Hub)', 'Service Overview Page' ),
			'privacy'   => array( 'Privacy Policy', 'PDPA Content' ),
			'legal'     => array( 'Legal Pages', 'Terms & Conditions' ),
		),
	);

	ob_start();
	?>
	<div class="tonkla-demo-import">
		<h3>
			<span class="dashicons dashicons-download" aria-hidden="true"></span>
			<?php esc_html_e( 'Import Demo Content', 'tonkla' ); ?>
		</h3>
		<p>
			<?php esc_html_e( 'Select the pages you want to create or update.', 'tonkla' ); ?>
			<br>
			<span class="tonkla-demo-import__warn"><?php esc_html_e( 'Note: Existing pages with the same slug will be overwritten.', 'tonkla' ); ?></span>
		</p>

		<div class="tonkla-demo-import__actions">
			<button type="button" class="button" id="demo-select-all"><?php esc_html_e( 'Select All', 'tonkla' ); ?></button>
			<button type="button" class="button" id="demo-unselect-all"><?php esc_html_e( 'Unselect All', 'tonkla' ); ?></button>
		</div>

		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="tonkla_import_demo">
			<input type="hidden" name="_tonkla_import_nonce" value="<?php echo esc_attr( wp_create_nonce( 'tonkla_import_demo' ) ); ?>">

			<?php foreach ( $groups as $group_label => $pages ) : ?>
				<div class="tonkla-demo-import__group">
					<h4><?php echo esc_html( $group_label ); ?></h4>
					<div class="tonkla-demo-import__grid">
						<?php foreach ( $pages as $value => $meta ) : ?>
							<label>
								<input type="checkbox" class="demo-page-checkbox" name="pages[]" value="<?php echo esc_attr( $value ); ?>" checked>
								<strong><?php echo esc_html( $meta[0] ); ?></strong>
								<span class="tonkla-demo-import__desc"><?php echo esc_html( $meta[1] ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<button type="submit" class="button button-primary button-large tonkla-demo-import__submit">
				<?php esc_html_e( 'Import Selected Content', 'tonkla' ); ?>
			</button>
		</form>
	</div>
	<?php
	return ob_get_clean();
}
