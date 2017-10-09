<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

/*
Plugin Name: WooCommerce Tax Ex
Plugin URI:  https://github.com/abirchler/woocommerce-tax-ex
Description: Tax exemption for WooCommerce
Version:     0.0.1
Author:      Aaron Birchler
Author URI:  https://aaronbirchler.com
License:     MIT
*/

if ( in_array(
    'woocommerce/woocommerce.php',
    apply_filters ( 'active_plugins', get_option( 'active_plugins' ) )
) ) {
  require_once __DIR__ . '/plugin.class.php';

  $plugin = new AB_Woocommerce_Tax_Ex();

  $plugin->init();
}
