<div style="clear: both;" id="woocommerce_tax_ex_wrapper">
  <h3>Tax Exempt Details</h3>
  <?=woocommerce_form_field(
    $id_field_name,
    array(
      'type' => 'text',
      'class' => array(
        'ab-tax-exempt-id',
        'update_totals_on_change',
      ),
      'label' => __('Tax Exempt ID'),
    ),
    $tax_exempt_id
  )?>

  <script type="text/javascript">
    window.woocommerce_tax_ex_locations = <?=json_encode($location_data);?>
  </script>
</div>
