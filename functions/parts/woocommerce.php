<?php

add_action('woocommerce_order_status_completed', 'baixa_voucher');

function baixa_voucher($order_id)
{
    $order = wc_get_order($order_id);

    $produtos = $order->get_items();

    foreach ($produtos as $produto) {
        $produto_id = $produto['product_id'];
        $quantidade = $produto['quantity'];
        $post_type_attach = carbon_get_post_meta($produto_id, 'post_type');
        baixa_voucher_produto($quantidade, $post_type_attach, $order_id);
    }
    error_log("Pedido #$order_id foi concluído!");

}
function baixa_voucher_produto($quantidade, $post_type, $order_id)
{
    $args_pt = [
        'post_type' => $post_type,
        'posts_per_page' => $quantidade,
        'order' => [
            'meta_key' => 'created_at',
            'order' => 'DESC'
        ],
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'pedido',
                'value' => '',
                'compare' => '='
            ],
            [
                'key' => 'pedido',
                'compare' => 'NOT EXISTS'
            ]
        ]
    ];
    $query_pt = new WP_Query($args_pt);
    $posts = $query_pt->get_posts();
    foreach ($posts as $post) {
        $post_id = $post->ID;
        $pedido = carbon_get_post_meta($post_id, 'pedido');
        if ($pedido == '') {
            update_post_meta($post_id, 'pedido', $order_id);
        }
    }
    update_estoque_vouchers($post_type);
}
