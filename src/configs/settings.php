<?php

defined( 'ABSPATH' ) || exit;

return array(
	'defaults' => array(
		'empty_string' => '',
		'general'      => array(
			'locked-payment-methods' => array(),
			'override-by-user-role'  => 'yes',
			'full-access-user-roles' => array( 'administrator', 'shop_manager' ),
			'override-by-user-meta'  => 'yes',
			'override-by-order-meta' => 'no',
		),
		'plugin'       => array(
			'remove-data-uninstall' => 'no',
		),
	),
	'options'  => array(
		'boolean' => array(
			'yes' => _x( 'Yes', 'settings', 'dws-mapm-for-woocommerce' ),
			'no'  => _x( 'No', 'settings', 'dws-mapm-for-woocommerce' ),
		),
		'general' => array(
			'full-access-user-roles' => array_combine(
				array_keys( wp_roles()->roles ),
				array_column( wp_roles()->roles, 'name' )
			),
		),
	),
);
