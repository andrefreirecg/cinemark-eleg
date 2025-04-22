<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action('carbon_fields_register_fields', 'crb_attach_vouchers_ingresso');

function crb_attach_vouchers_ingresso()
{
    Container::make('post_meta', __('Voucher', 'vouchers_cinemark'))
        ->where('post_type', '=', 'vouchers_ingresso')
        ->add_fields(array(
            Field::make('text', 'ingresso', 'Código do Ingresso')->set_width(50)->set_attribute('type', 'password'),
            Field::make('select', 'pedido', 'Pedido')
                ->add_options(function () {
                    $orders = wc_get_orders(array(
                        'status' => 'completed',
                        'limit' => -1,
                    ));
                    $options = array();
                    $options[''] = '';

                    foreach ($orders as $order) {
                        $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                        $options[$order->get_id()] = '#' . $order->get_order_number() . ' - ' . $customer_name;
                    }
                    return $options;
                })
                ->set_width(50)
                ->set_help_text('Selecione um pedido do WooCommerce'),
        ));
}

add_action('carbon_fields_register_fields', 'crb_attach_vouchers_combo');

function crb_attach_vouchers_combo()
{
    Container::make('post_meta', __('Voucher', 'vouchers_cinemark'))
        ->where('post_type', '=', 'vouchers_combo')
        ->add_fields(array(
            Field::make('text', 'pipoca', 'Código Pipoca')->set_width(50)->set_attribute('type', 'password'),
            Field::make('text', 'refrigerante', 'Código Refrigerante')->set_width(50)->set_attribute('type', 'password'),
            Field::make('select', 'pedido', 'Pedido')
                ->add_options(function () {
                    $orders = wc_get_orders(array(
                        'status' => 'completed',
                        'limit' => -1,
                    ));
                    $options = array();
                    $options[''] = '';

                    foreach ($orders as $order) {
                        $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                        $options[$order->get_id()] = '#' . $order->get_order_number() . ' - ' . $customer_name;
                    }
                    return $options;
                })
                ->set_help_text('Selecione um pedido do WooCommerce'),

        ));
}


add_action('carbon_fields_register_fields', 'crb_attach_fields_products');

function crb_attach_fields_products()
{
    Container::make('post_meta', __('Produto', 'product'))
        ->where('post_type', '=', 'product')
        ->add_fields(array(
            Field::make('select', 'post_type', 'Tipo de post')
                ->add_options(function () {
                    $post_types = get_post_types();
                    $options = array();
                    $options[''] = '';
                    foreach ($post_types as $post_type) {
                        $options[$post_type] = $post_type;
                    }
                    return $options;
                })
                ->set_help_text('Selecione um tipo de post para ser responsável pela distribuição dos vouchers'),

        ));
}
