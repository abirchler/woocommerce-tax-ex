<?php

class AB_Woocommerce_Tax_Ex {

  const COUNTRY_COL = 'tax_rate_country';

  const STATE_COL = 'tax_rate_state';

  public function __construct() {

    $this->id_field = 'ab_tax_exempt_id';

    $this->id_meta_field = 'Tax Exempt ID';
  }

  public function init() {

    add_action(
      'woocommerce_before_order_notes',
      array($this, 'checkout_content')
    );

    add_action(
      'woocommerce_checkout_update_order_review',
      array($this, 'update_order_review')
    );

    add_action(
      'woocommerce_checkout_update_order_meta',
      array($this, 'update_order_meta')
    );

    add_action(
      'woocommerce_admin_order_data_after_billing_address',
      'display_admin_order_meta',
      10,
      1
    );

    add_action(
      'wp_enqueue_scripts',
      array($this, 'enqueue_scripts'),
      100
    );
  }

  public function compile_tax_locations($taxes) {
  
    $compiled = array();

    foreach($taxes as $tax) {

      $country = $tax->{self::COUNTRY_COL};
      $state = $tax->{self::STATE_COL};

      $compiled[$country][] = $state;
    }

    $compiled = array_map('array_unique', $compiled);

    return $compiled;
  }

  public function get_taxes() {

    global $wpdb;

    $sql = <<<SQL
SELECT *
FROM {$wpdb->prefix}woocommerce_tax_rates
LEFT JOIN {$wpdb->prefix}woocommerce_tax_rate_locations
  ON {$wpdb->prefix}woocommerce_tax_rate_locations.tax_rate_id
    = {$wpdb->prefix}woocommerce_tax_rates.tax_rate_id
SQL;

    $taxes = $wpdb->get_results($sql);

    return $taxes;
  }

  public function get_tax_locations() {
  
    $taxes = $this->get_taxes();

    $locations = $this->compile_tax_locations($taxes);

    return $locations;
  }

  public function add_tax_locations() {

    exit('adding tax locations');

    $location_data = $this->get_tax_locations();

    $script = 'woocommerce_tax_ex_locations = ' . json_encode($location_data);

    echo '<script>' . $script . '</script>';
  }

  public function get_tax_id_number() {

    global $wpdb;

    $tax_id_number = '';

    if ( is_user_logged_in() ) {

      $user = wp_get_current_user();

      $user_id = $user->ID;

      $meta_field = esc_sql($this->id_meta_field);

      $sql = <<<SQL
SELECT
  {$wpdb->prefix}postmeta.meta_value,
  {$wpdb->prefix}posts.post_date
FROM {$wpdb->prefix}posts
JOIN {$wpdb->prefix}postmeta
  ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id
WHERE
  {$wpdb->prefix}posts.post_author = $user->ID
  AND {$wpdb->prefix}postmeta.meta_key = "$meta_field"
  AND {$wpdb->prefix}postmeta.meta_value != ""
  AND {$wpdb->prefix}postmeta.meta_value IS NOT NULL
ORDER BY {$wpdb->prefix}posts.post_date DESC
LIMIT 1
SQL;

      $row = $wpdb->get_row($sql);

      if ( $row = $wpdb->get_row($sql) ) {
      
        $tax_id_number = $row->meta_value;
      }

      return $tax_id_number;
    }
  }

  public function checkout_content( $checkout ) {

    $id_field_name = $this->id_field;

    $tax_exempt_id = $checkout->get_value( $id_field_name );

    if ( empty($tax_exempt_id ) ) {

      $tax_exempt_id = $this->get_tax_id_number();
    }

    $location_data = $this->get_tax_locations();

    include __DIR__ . '/template.php';
  }

  public function update_order_review( $post_data ) {

    ${$this->id_field} = '';

    parse_str($post_data);

    if ( ! empty( ${$this->id_field} ) ) {

      ${$this->id_field} = trim( ${$this->id_field} );
    }

    $is_exempt = ! empty( ${$this->id_field} );

    global $woocommerce;

    $woocommerce->customer->set_is_vat_exempt( $is_exempt );
  }

  public function update_order_meta( $order_id ) {

    if ( ! empty($_POST[$this->id_field]) ) {

      $tax_id = trim( $_POST[$this->id_field] );
    }

    if ( $tax_id !== '' ) {
    
      update_post_meta(
        $order_id,
        $this->id_meta_field,
        $tax_id
      );
    }
  }

  public function display_admin_order_meta( $order ) {

    $tax_id = get_post_meta( $order->id, $this->id_meta_field, true );

    $label = $this->id_meta_field;

    include __DIR__ . '/admin_template.php';
  }

  public function enqueue_scripts() {
  
    wp_enqueue_script(
      'ab_woocommerce_tax_ex',
      plugins_url( '/js/ab_woocommerce_tax_ex.js', __FILE__),
      array()
    );
  }
}
