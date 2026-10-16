<?php
/**
 * Custom Walker for Tonkla IT Mobile Menu
 */

if (!defined('ABSPATH')) {
    exit;
}

class Tonkla_Mobile_Nav_Walker extends Walker_Nav_Menu
{
    /**
     * @var int|null
     */
    private $current_parent_id = null;

    /**
     * Map badge color keys to Tailwind classes.
     *
     * @param string $badge_color Color key.
     * @return string
     */
    private function get_badge_classes($badge_color)
    {
        return tonkla_menu_badge_classes($badge_color);
    }

    /**
     * Map icon color keys to Tailwind text classes.
     *
     * @param string $icon_color Color key.
     * @return string
     */
    private function get_icon_text_class($icon_color)
    {
        return tonkla_menu_icon_text_class($icon_color);
    }

    public function start_lvl(&$output, $depth = 0, $args = null)
    {
        if ($depth === 0) {
            $output .= '<div class="hidden pl-4 border-l-2 border-slate-100 ml-4 space-y-2 mt-2 mobile-dropdown-content">';
        }
    }

    public function end_lvl(&$output, $depth = 0, $args = null)
    {
        if ($depth !== 0) {
            return;
        }

        if ($this->current_parent_id) {
            $enable_footer = carbon_get_nav_menu_item_meta($this->current_parent_id, 'enable_dropdown_footer');

            if ($enable_footer) {
                $footer_text = carbon_get_nav_menu_item_meta($this->current_parent_id, 'dropdown_footer_text');
                $footer_url = carbon_get_nav_menu_item_meta($this->current_parent_id, 'dropdown_footer_url');

                if (empty($footer_text)) {
                    $footer_text = 'ดูบริการทั้งหมด';
                }
                if (empty($footer_url)) {
                    $footer_url = '#';
                }

                $output .= '<div class="mt-2 pt-2 border-t border-slate-100">';
                $output .= '<a href="' . esc_url($footer_url) . '" class="flex items-center justify-center text-xs font-bold text-brand hover:underline px-2 py-2">';
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
        $badge_text = carbon_get_nav_menu_item_meta($item->ID, 'badge_text');
        $badge_color = carbon_get_nav_menu_item_meta($item->ID, 'badge_color');

        $badge_html = '';
        if ($badge_text) {
            $bg_color = $this->get_badge_classes($badge_color);
            $badge_html = '<span class="ml-auto ' . $bg_color . ' text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wide">' . esc_html($badge_text) . '</span>';
        }

        $icon_text_color = $this->get_icon_text_class($icon_color);

        if ($depth === 0) {
            if (in_array('menu-item-has-children', $item->classes, true)) {
                $this->current_parent_id = $item->ID;

                $output .= '<div>';
                $output .= '<button type="button" class="mobile-dropdown-toggle w-full flex justify-between items-center px-3 py-2 rounded-md text-lg font-heading font-bold text-slate-800 hover:text-brand hover:bg-slate-50 transition">';
                $output .= $title;
                $output .= '<i class="fa-solid fa-chevron-down text-sm transition-transform"></i>';
                $output .= '</button>';
            } else {
                $output .= '<a href="' . esc_url($item->url) . '" class="block px-3 py-2 rounded-md text-lg font-heading font-bold text-slate-800 hover:text-brand hover:bg-slate-50">';
                $output .= $title;
                $output .= '</a>';
            }
        } elseif ($depth === 1) {
            $output .= '<a href="' . esc_url($item->url) . '" class="flex items-center text-slate-600 hover:text-brand px-2 py-1">';

            if ($icon_class) {
                $output .= '<div class="w-6 text-center mr-2"><i class="' . esc_attr($icon_class) . ' ' . esc_attr($icon_text_color) . '"></i></div>';
            }

            $output .= '<span class="text-sm font-medium">' . $title . '</span>';
            $output .= $badge_html;
            $output .= '</a>';
        }
    }

    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        if ($depth === 0 && in_array('menu-item-has-children', $item->classes, true)) {
            $output .= '</div>';
        }
    }
}
