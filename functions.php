<?php

use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Admin\Settings;
use function DeepWebSolutions\WC_Plugins\dws_wc_mapm_plugin_container;

defined( 'ABSPATH' ) || exit;

/**
 * Returns the raw database value of a plugin's option.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $field_id   ID of the option field to retrieve.
 *
 * @return  mixed|null
 */
function dws_wc_mapm_get_raw_option( string $field_id ) {
	try {
		$settings = dws_wc_mapm_plugin_container()->get( Settings::class );
		return $settings->get_option_value( $field_id );
	} catch ( Exception $exception ) {
		return null;
	}
}

/**
 * Returns the validated database value of a plugin's option.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $field_id   ID of the option field to retrieve.
 *
 * @return  mixed|null
 */
function dws_wc_mapm_get_validated_option( string $field_id ) {
	try {
		$settings = dws_wc_mapm_plugin_container()->get( Settings::class );

		return $settings->get_validated_option_value( $field_id );
	} catch ( Exception $exception ) {
		return null;
	}
}
