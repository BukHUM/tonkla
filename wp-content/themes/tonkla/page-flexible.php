<?php
/**
 * Template Name: Tonkla Page Builder
 */

defined( 'ABSPATH' ) || exit;

get_header(); ?>

<main id="content" class="site-content">
    <?php
    $modules = carbon_get_the_post_meta('page_modules');

    if (!empty($modules)):
        foreach ($modules as $module):

            // Carbon Fields stores module type in '_type' key
            $type = $module['_type'];

            // We need to pass the module data to the template part
            // Using set_query_var is the WordPress way to pass data to get_template_part
            set_query_var('module', $module);

            if ($type === 'hero_module'):
                get_template_part('parts/hero/hero');

            elseif ($type === 'service_grid'):
                get_template_part('parts/grid/grid-services');

            elseif ($type === 'pricing_table'):
                get_template_part('parts/pricing/pricing-hosting');

            elseif ($type === 'content_block'):
                get_template_part('parts/content/content-block');

            elseif ($type === 'feature_split'):
                get_template_part('parts/content/feature-split');

            elseif ($type === 'workflow_module'):
                get_template_part('parts/workflow');

            elseif ($type === 'cta_module'):
                get_template_part('parts/cta');

            elseif ($type === 'stats_module'):
                get_template_part('parts/stats');

            elseif ($type === 'process_module'):
                get_template_part('parts/process');

            elseif ($type === 'faq_module'):
                get_template_part('parts/faq/faq-accordion');

            elseif ($type === 'testimonials_module'):
                get_template_part('parts/testimonials/testimonials-grid');

            elseif ($type === 'tech_stack_grid'):
                get_template_part('parts/grid/grid-tech-stack');

            elseif ($type === 'portfolio_module'):
                get_template_part('parts/grid/grid-portfolio');

            elseif ($type === 'contact_section'):
                get_template_part('parts/contact/contact-section');

            elseif ($type === 'map_faq_section'):
                get_template_part('parts/contact/map-faq');

            endif;

        endforeach;
    endif;
    ?>
</main>

<?php
get_footer();
