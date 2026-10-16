<?php
/**
 * Content Block Module (Text + Media)
 */

defined( 'ABSPATH' ) || exit;

$module = get_query_var('module');

$layout_align = $module['layout_align'];
$content = $module['content']; // Rich Text
$media_type = $module['media_type'];
$image = $module['image']; // URL (since we set value type to url)

$flex_dir = ($layout_align === 'right') ? 'lg:flex-row-reverse' : 'lg:flex-row';

// Check if content is a full-width section (contains <section with py- class)
$is_fullwidth_section = (strpos($content, '<section') !== false && (strpos($content, 'class="py-') !== false || strpos($content, 'class=\'py-') !== false));

// Background Color
$bg_color = isset($module['bg_color']) ? $module['bg_color'] : 'white';
$bg_class = 'bg-' . $bg_color;
?>

<?php if ($is_fullwidth_section): ?>
    <?php
    // For full-width sections like Tech Stack, output content directly
    echo apply_filters('the_content', $content);
?>
<?php else: ?>
    <section class="py-20 <?php echo esc_attr($bg_class); ?>">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col <?php echo $flex_dir; ?> items-center gap-16">

                <div class="<?php echo ($layout_align === 'center') ? 'w-full text-center' : 'lg:w-1/2'; ?>">
                    <div class="prose prose-slate max-w-none">
                        <?php echo apply_filters('the_content', $content); ?>
                    </div>
                </div>

                <?php if ($layout_align !== 'center'): ?>
                    <div class="lg:w-1/2 relative w-full">
                        <?php if ($media_type === 'image' && $image): ?>
                            <div class="relative">
                                <div
                                    class="absolute inset-0 bg-gradient-to-tr from-brand to-blue-500 rounded-3xl transform rotate-3 opacity-20">
                                </div>
                                <?php echo wp_get_attachment_image($image, 'full', false, array('class' => 'rounded-3xl shadow-xl relative z-10 border border-slate-100 w-full')); ?>
                            </div>
                        <?php elseif ($media_type === 'code'): ?>
                            <?php if (!empty($module['code_content'])): ?>
                                <div class="w-full">
                                    <?php echo apply_filters('the_content', $module['code_content']); ?>
                                </div>
                            <?php else: ?>
                                <?php get_template_part('parts/hero/graphic', 'code'); ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>
<?php endif; ?>