<?php
/**
 * Shared newsletter form fields (sidebar + blog CTA).
 *
 * @var array $args {
 *     @type string $form_id
 *     @type string $email_id
 *     @type string $hp_id
 *     @type string $form_class
 *     @type string $input_class
 *     @type string $button_class
 *     @type string $placeholder
 *     @type string $button_text
 * }
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

$form_id      = isset( $args['form_id'] ) ? $args['form_id'] : 'newsletter-form';
$email_id     = isset( $args['email_id'] ) ? $args['email_id'] : 'tonkla-newsletter-email';
$hp_id        = isset( $args['hp_id'] ) ? $args['hp_id'] : 'tonkla-newsletter-hp';
$form_class   = isset( $args['form_class'] ) ? $args['form_class'] : 'newsletter-form';
$input_class  = isset( $args['input_class'] ) ? $args['input_class'] : '';
$button_class = isset( $args['button_class'] ) ? $args['button_class'] : '';
$placeholder  = isset( $args['placeholder'] ) ? $args['placeholder'] : __( 'กรอกอีเมลของคุณ', 'tonkla' );
$button_text  = isset( $args['button_text'] ) ? $args['button_text'] : __( 'ติดตาม', 'tonkla' );
?>
<form id="<?php echo esc_attr( $form_id ); ?>" method="POST" class="<?php echo esc_attr( $form_class ); ?>">
	<?php wp_nonce_field( 'tonkla_newsletter_nonce', 'tonkla_newsletter_nonce_field' ); ?>
	<div class="tonkla-hp-field" aria-hidden="true">
		<label for="<?php echo esc_attr( $hp_id ); ?>"><?php esc_html_e( 'Leave blank', 'tonkla' ); ?></label>
		<input type="text" id="<?php echo esc_attr( $hp_id ); ?>" name="hp_field" tabindex="-1" autocomplete="off">
	</div>

	<label for="<?php echo esc_attr( $email_id ); ?>" class="sr-only"><?php esc_html_e( 'อีเมล', 'tonkla' ); ?></label>
	<input type="email" id="<?php echo esc_attr( $email_id ); ?>" name="email" required
		placeholder="<?php echo esc_attr( $placeholder ); ?>"
		class="<?php echo esc_attr( $input_class ); ?>">
	<button type="submit" class="<?php echo esc_attr( $button_class ); ?>">
		<span class="btn-text"><?php echo esc_html( $button_text ); ?></span>
		<span class="btn-loading hidden"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i></span>
	</button>
</form>
