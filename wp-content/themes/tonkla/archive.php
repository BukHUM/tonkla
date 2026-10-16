<?php
/**
 * Category archive
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$queried_object = get_queried_object();
$term_id = isset($queried_object->term_id) ? (int) $queried_object->term_id : 0;

$custom_title = carbon_get_term_meta($term_id, 'custom_title');
$custom_desc = carbon_get_term_meta($term_id, 'custom_description');
$badge_text = carbon_get_term_meta($term_id, 'badge_text') ?: 'KNOWLEDGE BASE';

$title = $custom_title ?: single_cat_title('', false);
$description = $custom_desc ?: category_description();
if (empty($description)) {
    $description = 'อัปเดตเทรนด์เทคโนโลยี เคล็ดลับการดูแลเว็บไซต์ และความรู้ด้านความปลอดภัยทางไซเบอร์จากทีมงาน Tonkla IT';
}
?>

<div id="header-placeholder"></div>

<main class="flex-grow">
    <?php
    get_template_part('parts/blog/hero', null, array(
        'title' => $title,
        'description' => $description,
        'badge_text' => $badge_text,
        'search_id' => 'tonkla-archive-search',
    ));

    get_template_part('parts/blog/category-nav');

    get_template_part('parts/blog/posts-loop', null, array(
        'empty_message' => __('ขออภัย ยังไม่มีบทความในหมวดหมู่นี้ หรือลองค้นหาด้วยคำค้นอื่น', 'tonkla'),
        'show_home_link' => true,
    ));

    get_template_part('parts/blog/newsletter', null, array(
        'form_id' => 'newsletter-form',
        'email_id' => 'tonkla-archive-newsletter-email',
        'hp_id' => 'tonkla-archive-hp',
    ));
    ?>
</main>

<?php get_footer(); ?>
