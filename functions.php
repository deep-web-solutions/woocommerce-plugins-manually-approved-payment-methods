<?php

use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Settings;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies\OrderMeta;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies\UserMeta;
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

/**
 * Returns the raw database value of a plugin's general option.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $field_id   ID of the option field to retrieve.
 *
 * @return  mixed|null
 */
function dws_wc_mapm_get_raw_general_option( string $field_id ) {
	try {
		$general_settings = dws_wc_mapm_plugin_container()->get( Settings\GeneralSettings::class );
		return $general_settings->get_option_value( $field_id );
	} catch ( Exception $exception ) {
		return null;
	}
}

/**
 * Returns the validated database value of a plugin's genral option.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $field_id   ID of the option field to retrieve.
 *
 * @return  mixed|null
 */
function dws_wc_mapm_get_validated_general_option( string $field_id ) {
	try {
		$general_settings = dws_wc_mapm_plugin_container()->get( Settings\GeneralSettings::class );
		return $general_settings->get_validated_option_value( $field_id );
	} catch ( Exception $exception ) {
		return null;
	}
}

/**
 * Removes the payment methods IDs from a given array if the user has been granted access to them.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   array   $locked_methods_ids     List of locked payment methods.
 * @param   int     $user_id                The ID of the user to check.
 *
 * @return  array|null
 */
function dws_wc_mapm_check_payment_methods_access_for_user( array $locked_methods_ids, int $user_id ): ?array {
	try {
		$user_profile = dws_wc_mapm_plugin_container()->get( UserMeta::class );
		return $user_profile->maybe_grant_payment_methods_access( $locked_methods_ids, $user_id );
	} catch ( Exception $exception ) {
		return null;
	}
}

/**
 * Removes the payment methods IDs from a given array if the order has been unlocked for those.
 *
 * @param   array   $locked_methods_ids     List of locked payment methods.
 * @param   int     $order_id               The ID of the order to check.
 *
 * @return  array|null
 */
function dws_wc_mapm_check_payment_methods_access_for_wc_order( array $locked_methods_ids, int $order_id ): ?array {
	try {
		$order_overview = dws_wc_mapm_plugin_container()->get( OrderMeta::class );
		return $order_overview->maybe_grant_payment_methods_access( $locked_methods_ids, $order_id );
	} catch ( Exception $exception ) {
		return null;
	}
}
