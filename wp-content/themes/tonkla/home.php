<?php
/**
 * Blog home (Posts page)
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$title = 'บทความและสาระน่ารู้';
$description = 'อัปเดตเทรนด์เทคโนโลยี เคล็ดลับการดูแลเว็บไซต์ และความรู้ด้านความปลอดภัยทางไซเบอร์จากทีมงาน Tonkla IT';
$badge_text = 'KNOWLEDGE BASE';

if (is_home() && !is_front_page()) {
    $post_page_id = get_option('page_for_posts');
    if ($post_page_id) {
        $p_title = get_the_title($post_page_id);
        if ($p_title) {
            $title = $p_title;
        }
    }
}
?>

<div id="header-placeholder"></div>

<main class="flex-grow">
    <?php
    get_template_part('parts/blog/hero', null, array(
        'title' => $title,
        'description' => $description,
        'badge_text' => $badge_text,
        'search_id' => 'tonkla-home-search',
    ));

    get_template_part('parts/blog/category-nav');

    get_template_part('parts/blog/posts-loop', null, array(
        'empty_message' => __('ยังไม่มีบทความในขณะนี้', 'tonkla'),
        'show_home_link' => false,
    ));

    get_template_part('parts/blog/newsletter', null, array(
        'form_id' => 'newsletter-form',
        'email_id' => 'tonkla-home-newsletter-email',
        'hp_id' => 'tonkla-home-hp',
    ));
    ?>
</main>

<?php get_footer(); ?>
