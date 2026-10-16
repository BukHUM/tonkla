<?php
/**
 * Map & FAQ Section Module
 */

defined( 'ABSPATH' ) || exit;

$module = get_query_var('module');
$map_code = isset($module['map_embed_code']) ? $module['map_embed_code'] : '';
$faq_items = isset($module['faq_items']) ? $module['faq_items'] : array();
?>

<section class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

            <!-- Google Map Embed -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 h-[400px] lg:h-auto">
                <?php if ($map_code): ?>
                    <div class="w-full h-full rounded-lg overflow-hidden">
                        <?php
                        echo wp_kses(
                            $map_code,
                            array(
                                'iframe' => array(
                                    'src'             => true,
                                    'width'           => true,
                                    'height'          => true,
                                    'style'           => true,
                                    'class'           => true,
                                    'title'           => true,
                                    'frameborder'     => true,
                                    'allowfullscreen' => true,
                                    'allow'           => true,
                                    'loading'         => true,
                                    'referrerpolicy'  => true,
                                ),
                            )
                        );
                        ?>
                    </div>
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-400 rounded-lg">
                        Map Embed Code not set
                    </div>
                <?php endif; ?>
            </div>

            <!-- FAQ -->
            <div>
                <?php if (!empty($module['faq_title'])): ?>
                    <h2 class="text-2xl font-heading font-bold text-slate-900 mb-6">
                        <?php echo esc_html($module['faq_title']); ?>
                    </h2>
                <?php endif; ?>

                <?php if ($faq_items): ?>
                    <div class="space-y-4">
                        <?php foreach ($faq_items as $item): ?>
                            <details class="group bg-white rounded-lg shadow-sm border border-slate-200">
                                <summary
                                    class="flex justify-between items-center font-bold text-slate-800 p-4 cursor-pointer list-none">
                                    <?php echo esc_html($item['question']); ?>
                                    <span class="transition group-open:rotate-180">
                                        <i class="fa-solid fa-chevron-down text-slate-400"></i>
                                    </span>
                                </summary>
                                <div class="px-4 pb-4 text-slate-600 text-sm leading-relaxed">
                                    <?php echo do_shortcode(wpautop($item['answer'])); ?>
                                </div>
                            </details>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>