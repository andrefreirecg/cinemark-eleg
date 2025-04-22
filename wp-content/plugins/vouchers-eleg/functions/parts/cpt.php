<?php

function add_custom_post_type()
{
    register_post_type('vouchers_ingresso', array(
        'labels' => array(
            'name' => __('Vouchers Ingresso'),
            'singular_name' => __('Voucher')
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'voucher_ingresso'),
        'supports' => array('title'),
        'menu_icon' => 'dashicons-tickets',
    ));
    register_post_type('vouchers_combo', array(
        'labels' => array(
            'name' => __('Vouchers Combo'),
            'singular_name' => __('Voucher')
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'voucher_combo'),
        'supports' => array('title'),
        'menu_icon' => 'dashicons-tickets',
    ));
}
add_action('init', 'add_custom_post_type');
