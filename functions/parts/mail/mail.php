<?php

require_once 'vouchers.php';
add_filter('woocommerce_order_actions', 'custom_order_action_button');
function custom_order_action_button($actions) {
    $actions['send_custom_voucher_email'] = 'Enviar voucher personalizado por e-mail';
    return $actions;
}

add_action('woocommerce_order_action_send_custom_voucher_email', 'send_custom_voucher_email');
function send_custom_voucher_email($order) {


    $order_id = $order->get_id();
    $user_email = $order->get_billing_email();
    $nome = $order->get_billing_first_name();
    $todos_vouchers = get_vouchers_from_order($order_id);
    $html_content = mail_vouchers_combo_e_ingresso($nome, $todos_vouchers);
    $subject = 'Seu voucher chegou!';
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Envia o e-mail
    wp_mail($user_email, $subject, $html_content, $headers);
    return $html_content;
}

function get_vouchers_from_order($order_id) {
    $todos_vouchers = [];

    $post_types = ['vouchers_ingresso', 'vouchers_combo'];

    foreach ($post_types as $post_type) {
        $args = [
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        ];

        $query = new WP_Query($args);
        $voucher_ids = [];



        foreach ($query->posts as $post) {
            $pedido = carbon_get_post_meta($post->ID, 'pedido');
            if (!$pedido) {
                continue;
            }

            if ($pedido != $order_id) {
                continue;
            }
            $voucher_ids[] = $post->ID;
        }

        if (!empty($voucher_ids)) {
            $todos_vouchers[$post_type] = $voucher_ids;
        }
    }
    return $todos_vouchers;
}
