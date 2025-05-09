<?php
add_action( 'after_setup_theme', 'crb_load' );
function crb_load() {
    \Carbon_Fields\Carbon_Fields::boot();
}

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$files = ['styles', 'cpt', 'admin_page', 'fields', 'storage', 'ajax', 'sync','mail/mail', 'woocommerce'];

foreach ( $files as $file ) {
    require_once 'parts/'. $file . '.php';
}

