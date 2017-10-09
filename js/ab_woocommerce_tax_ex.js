jQuery(function($){

  var countryMenus = $("#shipping_country, #billing_country");
  var stateMenus = $("#shipping_state, #billing_state");

  var shipMenu = stateMenus.filter("#shipping_state");
  var billMenu = stateMenus.filter("#billing_state");

  var shipCountryMenu = countryMenus.filter("#shipping_country");
  var billCountryMenu = countryMenus.filter("#billing_country");

  var shipDifferentCheckbox = $("#ship-to-different-address-checkbox");

  var taxIdWrapper = $("#woocommerce_tax_ex_wrapper");

  if ( taxIdWrapper.length ) {
    
    var taxIdInput = taxIdWrapper.find(".ab-tax-exempt-id");

    var updateTaxId = function(){

      var billCountry = billCountryMenu.val();
      var shipCountry = billCountry;

      var billState = billMenu.val();
      var shipState = billState;

      if (shipDifferentCheckbox.is(":checked")) {

        shipCountry = shipCountryMenu.val();
        shipState = shipMenu.val();
      }

      var showMenu = false;

      if ( typeof woocommerce_tax_ex_locations[shipCountry] !== "undefined" ) {
        
        showMenu = woocommerce_tax_ex_locations[shipCountry].indexOf(shipState) >= 0;
      }

      if ( showMenu ) {
      
        taxIdInput.removeAttr("disabled");
        taxIdWrapper.show();
      }
      else {
        taxIdInput.attr("disabled", "disabled");
        taxIdWrapper.hide();
      }
    }

    stateMenus.on("change", updateTaxId);
    countryMenus.on("change", updateTaxId);
    shipDifferentCheckbox.on("change", updateTaxId);

    updateTaxId();
  }


});
