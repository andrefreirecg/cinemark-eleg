<?php

function enqueue_plugin_styles()
{
    $current_screen = get_current_screen();
    $plugin_url = plugins_url().'/vouchers-eleg/';
    if ($current_screen->id == 'toplevel_page_cinemark-elegibilidade') {
        wp_enqueue_style('jquery-modal', $plugin_url . 'assets/jquery.modal.min.css', array(), '0.9.1');
    }
}
add_action('admin_enqueue_scripts', 'enqueue_plugin_styles');
function enqueue_plugin_scripts()
{
    $current_screen = get_current_screen();
    $plugin_url = plugins_url().'/vouchers-eleg/';
    if ($current_screen->id == 'toplevel_page_cinemark-elegibilidade') {
        wp_enqueue_script('jquery-modal', $plugin_url . 'assets/jquery.modal.min.js', array('jquery'), '0.9.1', true);
        wp_enqueue_script('tailwind', $plugin_url . 'assets/tailwind.js', array('jquery'), '3.4.16', true);
    }
}
add_action('admin_enqueue_scripts', 'enqueue_plugin_scripts');
