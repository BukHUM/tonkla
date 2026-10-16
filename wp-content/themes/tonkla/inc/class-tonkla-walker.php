<?php
/**
 * Custom Walker for Tonkla IT Menu
 * Matches the specific Tailwind structure in header.html
 */

if (!defined('ABSPATH')) {
    exit;
}

class Tonkla_Nav_Walker extends Walker_Nav_Menu
{
    /**
     * @var int|null
     */
    private $current_parent_id = null;

    /**
     * Build badge HTML for a menu item.
     *
     * @param string $badge_text  Badge label.
     * @param string $badge_color Color key.
     * @return string
     */
    private function get_badge_html($badge_text, $badge_color)
    {
        if (!$badge_text) {
            return '';
        }

        $bg_color = tonkla_menu_badge_classes($badge_color);

        return '<span class="ml-2 ' . esc_attr($bg_color) . ' text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wide">' . esc_html($badge_text) . '</span>';
    }

    /**
     * Resolve icon text color class.
     *
     * @param string $icon_color Color key or legacy class.
     * @return string
     */
    private function get_icon_text_class($icon_color)
    {
        return tonkla_menu_icon_text_class($icon_color);
    }

    /**
     * Render top-level menu item markup.
     *
     * @param object $item  Menu item.
     * @param string $title Filtered title.
     * @return string
     */
    private function render_top_level_item($item, $title)
    {
        if (in_array('menu-item-has-children', $item->classes, true)) {
            $this->current_parent_id = $item->ID;
            $html = '<div class="relative group h-20 flex items-center">';
            $html .= '<button type="button" class="font-heading px-4 py-2 text-slate-600 hover:text-brand font-medium transition flex items-center gap-1 h-full border border-transparent focus-visible:border-brand rounded-lg">';
            $html .= $title;
            $html .= '<i class="fa-solid fa-chevron-down text-xs transition-transform group-hover:rotate-180"></i>';
            $html .= '</button>';
            return $html;
        }

        $html = '<a href="' . esc_url($item->url) . '" class="font-heading px-4 py-2 text-slate-600 hover:text-brand font-medium transition">';
        $html .= $title;
        $html .= '</a>';
        return $html;
    }

    /**
     * Render dropdown (depth 1) menu item markup.
     *
     * @param object $item       Menu item.
     * @param string $title      Filtered title.
     * @param string $icon_class Icon class.
     * @param string $icon_color Icon color key.
     * @param string $description Description text.
     * @param string $badge_html Badge HTML.
     * @return string
     */
    private function render_dropdown_item($item, $title, $icon_class, $icon_color, $description, $badge_html)
    {
        $html = '<a href="' . esc_url($item->url) . '" class="block px-4 py-3 rounded-lg hover:bg-slate-50 group/item">';
        $html .= '<div class="font-bold text-slate-800 group-hover/item:text-brand text-sm flex items-center">';

        if ($icon_class) {
            $icon_text_color = $this->get_icon_text_class($icon_color);
            $html .= '<i class="' . esc_attr($icon_class) . ' mr-2 ' . esc_attr($icon_text_color) . ' group-hover/item:text-brand transition"></i>';
        }

        $html .= $title;
        $html .= $badge_html;
        $html .= '</div>';

        if ($description) {
            $html .= '<div class="text-xs text-slate-500 pl-6">' . esc_html($description) . '</div>';
        }

        $html .= '</a>';
        return $html;
    }

    public function start_lvl(&$output, $depth = 0, $args = null)
    {
        if ($depth === 0) {
            $output .= '<div class="absolute top-full left-0 w-72 bg-white rounded-xl shadow-xl border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform translate-y-2 group-hover:translate-y-0 overflow-hidden z-50">';
            $output .= '<div class="p-2 space-y-1">';
        }
    }

    public function end_lvl(&$output, $depth = 0, $args = null)
    {
        if ($depth !== 0) {
            return;
        }

        $output .= '</div>';

        if ($this->current_parent_id) {
            $enable_footer = carbon_get_nav_menu_item_meta($this->current_parent_id, 'enable_dropdown_footer');

            if ($enable_footer) {
                $footer_text = carbon_get_nav_menu_item_meta($this->current_parent_id, 'dropdown_footer_text') ?: 'ดูบริการทั้งหมด';
                $footer_url = carbon_get_nav_menu_item_meta($this->current_parent_id, 'dropdown_footer_url') ?: '#';

                $output .= '<div class="bg-slate-50 p-3 text-center border-t border-slate-100">';
                $output .= '<a href="' . esc_url($footer_url) . '" class="text-xs font-bold text-brand hover:underline">';
                $output .= esc_html($footer_text);
                $output .= ' <i class="fa-solid fa-arrow-right ml-1"></i>';
                $output .= '</a>';
                $output .= '</div>';
            }
        }

        $output .= '</div>';
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $title = apply_filters('the_title', $item->title, $item->ID);
        $icon_class = carbon_get_nav_menu_item_meta($item->ID, 'icon_class');
        $icon_color = carbon_get_nav_menu_item_meta($item->ID, 'icon_color');
        $description = carbon_get_nav_menu_item_meta($item->ID, 'description');
        $badge_text = carbon_get_nav_menu_item_meta($item->ID, 'badge_text');
        $badge_color = carbon_get_nav_menu_item_meta($item->ID, 'badge_color');
        $badge_html = $this->get_badge_html($badge_text, $badge_color);

        if ($depth === 0) {
            $output .= $this->render_top_level_item($item, $title);
            return;
        }

        if ($depth === 1) {
            $output .= $this->render_dropdown_item($item, $title, $icon_class, $icon_color, $description, $badge_html);
        }
    }

    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        if ($depth === 0 && in_array('menu-item-has-children', $item->classes, true)) {
            $output .= '</div>';
        }
    }
}
