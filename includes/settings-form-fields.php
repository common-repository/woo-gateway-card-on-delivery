<?php
/*
 * Exit if file accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shipping_methods = array();

if ( is_admin() ) {
	foreach ( WC()->shipping()->load_shipping_methods() as $method ) {
		$title = empty( $method->method_title ) ? ucfirst( $method->id ) : $method->method_title; 
        $shipping_methods[ strtolower( $method->id ) ] = esc_html( $title ); 
	}
}

return array(
	'enabled' => array(
		'title' 	=> __( 'Enable/Disable', 'woo-gateway-card-on-delivery' ),
		'type' 		=> 'checkbox',
		'label' 	=> __( 'Enable Debit Card or Credit on Delivery', 'woo-gateway-card-on-delivery' ),
		'default' 	=> 'yes'
	),
	'title' => array(
		'title' 		=> __( 'Title', 'woo-gateway-card-on-delivery' ),
		'type' 			=> 'text',
		'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woo-gateway-card-on-delivery' ),
		'default' 		=> __( 'Debit Card or Credit on Delivery', 'woo-gateway-card-on-delivery' ),
		'desc_tip'   	=> true,
	),
	'description' => array(
		'title'       	=> __( 'Description', 'woo-gateway-card-on-delivery' ),
		'type'       	=> 'textarea',
		'description' 	=> __( 'Payment method description that the customer will see on your checkout.', 'woo-gateway-card-on-delivery' ),
		'default'     	=> __( 'Select the type of card you want to pay.', 'woo-gateway-card-on-delivery' ),
		'desc_tip'    	=> true,
	),
	'cardtypes' => array(
		'title'    => __( 'Accepted Cards', 'woo-gateway-card-on-delivery' ),
		'type'     => 'multiselect',
		'class'    => 'chosen_select',
		'css'      => 'width: 350px;',
		'desc_tip' => __( 'Select the card types to accept.', 'woo-gateway-card-on-delivery' ),
		'options'  => array(
			'debitcard'		=>  __( 'Debit Card', 'woo-gateway-card-on-delivery' ),
			'creditcard'	=>  __( 'Credit Card', 'woo-gateway-card-on-delivery' ),
			'foodcard'		=>  __( 'Food Card', 'woo-gateway-card-on-delivery' ),
		),
		'default' => array( 'debitcard', 'creditcard' ),
	),
	'enable_for_methods' => array(
		'title'             => __( 'Enable for shipping methods', 'woo-gateway-card-on-delivery' ),
		'type'              => 'multiselect',
		'class'             => 'wc-enhanced-select',
		'css'               => 'width: 450px;',
		'default'           => '',
		'description'       => __( 'If Debit Card or Credit on Delivery is only available for certain methods, set it up here.', 'woo-gateway-card-on-delivery' ),
		'options'           => $shipping_methods,
		'desc_tip'          => true,
		'custom_attributes' => array(
			'data-placeholder' => __( 'Select shipping methods', 'woo-gateway-card-on-delivery' )
		)
	),
);