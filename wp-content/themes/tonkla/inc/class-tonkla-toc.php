<?php
/**
 * Tonkla Table of Contents Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Tonkla_TOC
{

    /**
     * @var array
     */
    private $headings = array();

    public function __construct()
    {
        if (!is_admin()) {
            add_filter('the_content', array($this, 'process_content'), 20);
        }
    }

    /**
     * Process content: Add IDs to headings and extract them.
     *
     * @param string $content Post content.
     * @return string
     */
    public function process_content($content)
    {
        if (!is_singular('post') && !is_page()) {
            return $content;
        }

        if (!tonkla_is_toc_enabled()) {
            return $content;
        }

        $depths = carbon_get_theme_option('toc_depth');
        if (empty($depths)) {
            $depths = array('h2', 'h3', 'h4');
        }

        $regex = '/<(' . implode('|', $depths) . ')([^>]*)>(.*?)<\/\1>/i';

        return preg_replace_callback($regex, function ($matches) {
            $tag = $matches[1];
            $attrs = $matches[2];
            $text = strip_tags($matches[3]);
            $raw_text = $matches[3];

            $id = sanitize_title($text);
            if (empty($id)) {
                $id = wp_unique_id('heading-');
            }

            if (preg_match('/id=["\']([^"\']+)["\']/', $attrs, $id_match)) {
                $id = $id_match[1];
            } else {
                $attrs .= ' id="' . esc_attr($id) . '"';
            }

            global $tonkla_toc_items;
            $tonkla_toc_items[] = array(
                'id' => $id,
                'text' => $text,
                'tag' => strtolower($tag),
                'level' => (int) substr($tag, 1),
            );

            return '<' . $tag . $attrs . '>' . $raw_text . '</' . $tag . '>';
        }, $content);
    }
}

/**
 * Bootstrap TOC (keeps a single instance).
 *
 * @return Tonkla_TOC
 */
function tonkla_toc_bootstrap()
{
    static $instance = null;
    if (null === $instance) {
        $instance = new Tonkla_TOC();
    }
    return $instance;
}
tonkla_toc_bootstrap();

/**
 * Helper to get TOC items (requires the_content to have run, or parses manually).
 *
 * @return array
 */
function tonkla_get_toc_items()
{
    global $tonkla_toc_items, $post;

    if (!empty($tonkla_toc_items)) {
        return $tonkla_toc_items;
    }

    if (!isset($post->post_content)) {
        return array();
    }

    $content = $post->post_content;
    $depths = carbon_get_theme_option('toc_depth') ?: array('h2', 'h3', 'h4');
    $regex = '/<(' . implode('|', $depths) . ')([^>]*)>(.*?)<\/\1>/i';

    preg_match_all($regex, $content, $matches, PREG_SET_ORDER);

    $items = array();
    foreach ($matches as $match) {
        $tag = $match[1];
        $attrs = $match[2];
        $text = strip_tags($match[3]);

        if (preg_match('/id=["\']([^"\']+)["\']/', $attrs, $id_match)) {
            $id = $id_match[1];
        } else {
            $id = sanitize_title($text);
            if (empty($id)) {
                $id = wp_unique_id('heading-');
            }
        }

        $items[] = array(
            'id' => $id,
            'text' => $text,
            'tag' => strtolower($tag),
            'level' => (int) substr($tag, 1),
        );
    }

    return $items;
}
