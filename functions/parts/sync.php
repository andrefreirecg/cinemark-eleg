<?php

add_action('save_post_vouchers_ingresso', 'ao_atualizar_ingresso', 10, 3);

function ao_atualizar_ingresso($post_ID, $post, $update) {
    if (!$update) {
        return;
    }
    update_estoque_vouchers('vouchers_ingresso');
    error_log("Voucher $post_ID foi atualizado.");
}

add_action('save_post_vouchers_combo', 'ao_atualizar_combo', 10, 3);


function ao_atualizar_combo($post_ID, $post, $update) {
    if (!$update) {
        return;
    }
    update_estoque_vouchers('vouchers_combo');
    error_log("Voucher $post_ID foi atualizado.");
}


add_action('save_post', 'verifica_e_atualiza_estoque_voucher', 10, 3);

add_action('deleted_post', 'verifica_e_atualiza_estoque_voucher', 10, 1);

add_action('trashed_post', 'verifica_e_atualiza_estoque_voucher', 10, 1);

function verifica_e_atualiza_estoque_voucher($post_id, $post = null)
{

    $todos_produtos_woocommerce = get_posts([
        'post_type' => 'product',
        'posts_per_page' => -1
    ]);
    foreach ($todos_produtos_woocommerce as $produto) {
        $cpt_attach = carbon_get_post_meta($produto->ID, 'post_type');
        if ($cpt_attach) {
            update_estoque_vouchers($cpt_attach);
            error_log("Estoque de $cpt_attach atualizado automaticamente devido a mudança no post");
        }
    }
}
