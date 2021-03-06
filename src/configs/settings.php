<?php

defined( 'ABSPATH' ) || exit;

return array(
	'defaults' => array(
		'general' => array(
			'locked-payment-methods' => array(),
			'override-per-user'      => 'yes',
			'override-per-order'     => 'no',
		),
	),
	'options'  => array(
		'boolean' => array(
			'yes' => _x( 'Yes', 'settings', 'dws-mapm-for-woocommerce' ),
			'no'  => _x( 'No', 'settings', 'dws-mapm-for-woocommerce' ),
		),
	),
);
