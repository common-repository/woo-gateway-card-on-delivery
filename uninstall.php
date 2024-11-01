<?php
// If the uniinstall file is not called from the wordpress output
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit ();
}
	// Clearing data from the options table
	// This will happen only if the Gateway Woo Cash On Delivery plugin is excluded from the system via WordPress
	$woo_card_delivery_settings = get_option( 'woocommerce_woo_card_delivery_settings' );
	if(!empty($woo_card_delivery_settings)) {
		unset($woo_card_delivery_settings);
	}

?>