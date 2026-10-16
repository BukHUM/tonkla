<?php
/**
 * Blog newsletter CTA (AJAX form)
 *
 * @var array $args {
 *     @type string $form_id
 *     @type string $email_id
 *     @type string $hp_id
 * }
 */

if (!defined('ABSPATH')) {
    exit;
}

$form_id = isset($args['form_id']) ? $args['form_id'] : 'newsletter-form';
$email_id = isset($args['email_id']) ? $args['email_id'] : 'tonkla-blog-newsletter-email';
$hp_id = isset($args['hp_id']) ? $args['hp_id'] : 'tonkla-blog-hp';
?>
<section class="py-20 bg-slate-900 text-white relative overflow-hidden">
    <div
        class="absolute inset-0 opacity-10"
        style="background-image: url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/grid-pattern.png' ); ?>');"
        aria-hidden="true"
    ></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <i class="fa-regular fa-envelope-open text-5xl mb-6 text-slate-400"></i>
        <h2 class="text-3xl md:text-4xl font-heading font-bold text-white mb-4">
            <?php esc_html_e('ไม่พลาดทุกข่าวสารจากเรา', 'tonkla'); ?>
        </h2>
        <p class="text-slate-400 mb-8 max-w-xl mx-auto">
            <?php esc_html_e('สมัครรับข่าวสารเทคโนโลยี โปรโมชั่น และบทความดีๆ ส่งตรงถึงอีเมลคุณ (ไม่มีสแปมแน่นอน)', 'tonkla'); ?>
        </p>

        <?php
        get_template_part(
            'parts/blog/newsletter-form',
            null,
            array(
                'form_id'      => $form_id,
                'email_id'     => $email_id,
                'hp_id'        => $hp_id,
                'form_class'   => 'max-w-md mx-auto flex gap-2 relative newsletter-form',
                'input_class'  => 'flex-grow px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus-visible:border-brand',
                'button_class' => 'px-6 py-3 bg-brand hover:bg-brand-dark text-white font-bold rounded-lg transition whitespace-nowrap border border-brand',
                'placeholder'  => __('กรอกอีเมลของคุณ', 'tonkla'),
                'button_text'  => __('ติดตาม', 'tonkla'),
            )
        );
        ?>
    </div>
</section>
