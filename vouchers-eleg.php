<?php
/**
 * Plugin Name:     Vouchers - ElegibilidadeBrasil > Cinemark
 * Plugin URI:      https://elegibilidadebrasil.org
 * Description:     A custom plugin for Cinemark Vouchers integrated with ElegibilidadeBrasil.
 * Author:          Maker Solluções
 * Author URI:      https://www.makersolucoes.com.br
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     ms-vouchers
 * Domain Path:     /languages
 * Version:         1.0.1
 *
 * @package         WooAsaas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once 'vendor/autoload.php';
require_once 'functions/index.php';


use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$pluginUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/andrefreirecg/cinemark-eleg',
    __FILE__,
    'ms-vouchers'
);

// Opcional: Verifique a versão do plugin diretamente do arquivo `readme` ou de outro arquivo
$pluginUpdateChecker->setBranch('plugin'); // Defina o branch que você deseja monitorar
