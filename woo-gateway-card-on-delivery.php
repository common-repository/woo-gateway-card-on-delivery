<?php
/*---------------------------------------------------------
Plugin Name: Woo Gateway Card On Delivery
Plugin URI: https://wordpress.org/plugins/woo-gateway-card-on-delivery/
Author: carlosramosweb
Author URI: http://plugins.criacaocriativa.com.br/
Donate link: http://donate.criacaocriativa.com.br/
Description: Make deliveries and earn Debit Card or Credit on Delivery.
Text Domain: woo-gateway-card-on-delivery
Domain Path: /languages/
Version: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html 
------------------------------------------------------------*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * Debit Card or Credit on Delivery Gateway for WooCommerce
 */
function init_card_on_delivery_gateway_class() {	
	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;
	
	class Woo_Gateway_Card_On_Delivery extends WC_Payment_Gateway {		
		public function __construct() {	
			// Loads the plugin text on the website.
			add_action( 'plugins_loaded', array( $this, 'woo_load_plugin_textdomain' ) );
			// Initialize order
			$this->order = new WC_Order( absint( get_query_var( 'order-pay' ) ) );			
			// Global variables
			$this->id                 = 'woo_card_delivery';
			$this->has_fields         = true;
			$this->enabled 			=  "no";
			$this->method_title       = __( 'Debit Card or Credit on Delivery', 'woo-gateway-card-on-delivery' );
			$this->method_description = __( 'Add new form of payment with card on delivery.', 'woo-gateway-card-on-delivery' );
			$this->supports           = array(
				'products',
			);
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();			
			// Define user set variables
			$this->title          	= $this->get_option( 'title' );
			$this->description    	= $this->get_option( 'description' );
			$this->cardtypes		= $this->get_option( 'cardtypes' );
			// Active if delivery
       		$this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );	
			// Save settings
			if ( is_admin() ) {				
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );				
			}			
			// Add hooks
			add_action( 'woocommerce_card_delivery', array( $this, 'payment_page' ) );	
		}
		
		/**
		 * Load the text field plugin for translation.
		 */
		public function woo_load_plugin_textdomain() {		
			 load_plugin_textdomain( 'myplugindomain', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');   
		}
		
		// Admin options
		public function admin_options() {				
			echo '<h3> ' . __( 'Debit Card or Credit on Delivery', 'woo-gateway-card-on-delivery' ) . ' </h3>';	
			echo '<table class="form-table" id="settings-block">';							
			$this->generate_settings_html();
			echo '</table>';
		}
		
		// Initialize fields
		public function init_form_fields() {			
			$this->form_fields = include( 'includes/settings-form-fields.php' );			
		}
		
		// Process payment
		public function process_payment( $order_id ) {
			global $woocommerce;			
			$order = new WC_Order( $order_id );	
			$current_user = wp_get_current_user();
			
			// Add Order Note
			if(!empty($_REQUEST['card-machine-indicated'])) {
				$order_note .= __( 'Take the card machine indicated: ', 'woo-gateway-card-on-delivery' );
				$order_note .= esc_attr($_REQUEST['card-machine-indicated']);
				$order->add_order_note( $order_note, $current_user->display_name );
			} else {
				$order_note .= __( 'Take the card machine indicated: ', 'woo-gateway-card-on-delivery' );
				$order->add_order_note( $order_note, $current_user->display_name );
			}
			// Empty Cart WooCommerce
			$woocommerce->cart->empty_cart();
						
			return array(
				'result'    => 'success',
				'redirect'  => add_query_arg( 'key', $order->order_key, add_query_arg( 'order-pay', $order_id, $order->get_checkout_payment_url( true ) ) )
			);
		}			
		
		// Icon
		public function get_icon() {
			$icon .= '<img src="' . plugins_url( '/woo-gateway-card-on-delivery/images/icon-card-delivery.png', dirname(__FILE__) ) . '" alt="'.__( 'Debit Card or Credit on Delivery', 'woo-gateway-card-on-delivery' ).'" /> ';		
			return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
		}
		/**
		 * Return the gateway's description.
		 *
		 * @return string
		 */
		public function get_description() {
			
			$default_fields = '<script type="text/javascript">jQuery(document).ready(function(){if (jQuery("input[type=radio][id=payment_method_woo_card_delivery]").is(":checked") ){ jQuery("select#card-machine-indicated").attr("required", "required");} else {jQuery("select#card-machine-indicated").removeAttr("required", "required");}});</script>';
			
			$default_fields .= '<p class="form-row form-row-wide hide-if-token">
			<select style="padding:8px 10px;" id="card-machine-indicated" name="card-machine-indicated" class="card-machine-indicated">';
			$default_fields .= '<option value="">'.__( 'Select a card type', 'woo-gateway-card-on-delivery' ).'</option>';
			foreach ($this->cardtypes as $cardtypes) {
				$default_fields .= '<option value="'.woo_check_the_name_card($cardtypes).'">'.woo_check_the_name_card($cardtypes).'</option>';
			}
			$default_fields .= '</select></p>';
			
			$description = apply_filters( 'woocommerce_gateway_description', $this->description, $this->id );
			
			return $description . $default_fields;
		}
		// =>
	}
	
	// check the name of the delivery
	function woo_check_the_name_card( $cardtypes ) {			
		$cardtypes = strtolower($cardtypes);
		switch ($cardtypes) {
			case 'debitcard':
				return __( 'Debit Card', 'woo-gateway-card-on-delivery' );
				break;
			case 'creditcard':
				return __( 'Credit Card', 'woo-gateway-card-on-delivery' );
				break;
			case 'foodcard':
				return __( 'Food Card', 'woo-gateway-card-on-delivery' );
				break;
		}
	}
	
	// check the name of the delivery
	function woo_check_the_name_ofthe_delivery( $chosen_methods ) {
		
		if ( strstr($chosen_methods, 'correios') ) {
			$chosen_methods = substr($chosen_methods, 0, -1);
		}
		return $chosen_methods;	
	}
	
	// Add custom payment gateway
	function add_card_on_delivery_gateway_class( $methods ) {
		
		if ( !is_admin() ) {
			$card_on_delivery_settings = get_option( 'woocommerce_woo_card_delivery_settings' );
			$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );	
			$chosen_methods_meta = $chosen_methods[0];	

		} else {
			$methods[] = 'Woo_Gateway_Card_On_Delivery'; 		
			return $methods;
		}
		
		if (is_numeric($chosen_methods_meta)) { 
			$chosen_methods_post = get_post( $chosen_methods_meta );
			if (!empty($chosen_methods_post->post_type) && $chosen_methods_post->post_type == "was") {
				$chosen_methods_meta = "advanced_shipping";
			} else {
				$chosen_methods_meta = $chosen_methods[0];	
			}
		}
		
		if ($card_on_delivery_settings[enabled] == "yes") {		
			if( is_array($cash_on_delivery_settings[enable_for_methods]) ) {
				foreach ($cash_on_delivery_settings[enable_for_methods] as $enable_for_methods ) {
					
					if( strstr($chosen_methods[0], $enable_for_methods) ) {	
						$methods[] = 'Woo_Gateway_Cash_On_Delivery';
						return $methods;
					}
					
				}
			} else {
				return $methods;
			}
		} else {
			return $methods;
		}
		
	}
	add_filter( 'woocommerce_payment_gateways', 'add_card_on_delivery_gateway_class' );
}
add_action( 'plugins_loaded', 'init_card_on_delivery_gateway_class' );