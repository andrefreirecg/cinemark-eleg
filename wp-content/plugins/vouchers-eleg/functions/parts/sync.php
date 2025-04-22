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
