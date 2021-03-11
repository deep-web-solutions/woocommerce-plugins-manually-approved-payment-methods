<?php

use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Integrations\WC_Memberships_Integration;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Settings;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies\OrderMeta;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies\UserMeta;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies\UserRole;
use function DeepWebSolutions\WC_Plugins\dws_wc_mapm_plugin;
use function DeepWebSolutions\WC_Plugins\dws_wc_mapm_plugin_container;

defined( 'ABSPATH' ) || exit;

// region OPTIONS

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

// endregion

// region LOCK CHECKING

/**
 * Removes the payment methods IDs from a given array if the user has been unlocked for those.
 *
 * @param   array   $locked_methods_ids     List of locked payment methods.
 * @param   int     $user_id                The ID of the user to check.
 *
 * @return  array|null
 */
function dws_wc_mapm_check_payment_methods_access_by_user_meta( array $locked_methods_ids, int $user_id ): ?array {
	try {
		$user_meta_checker = dws_wc_mapm_plugin_container()->get( UserMeta::class );
		return $user_meta_checker->maybe_grant_payment_methods_access( $locked_methods_ids, $user_id );
	} catch ( Exception $exception ) {
		return null;
	}
}

/**
 * Removes the payment methods IDs from a given array if the user has been unlocked for those based on their user role.
 *
 * @param   array   $locked_methods_ids     List of locked payment methods.
 * @param   int     $user_id                The ID of the user to check.
 *
 * @return  array|null
 */
function dws_wc_mapm_check_payment_methods_access_by_user_roles( array $locked_methods_ids, int $user_id ): ?array {
	try {
		$user_role_checker = dws_wc_mapm_plugin_container()->get( UserRole::class );
		return $user_role_checker->maybe_grant_payment_methods_access( $locked_methods_ids, $user_id );
	} catch ( Exception $exception ) {
		return null;
	}
}

/**
 * Removes the payment methods IDs from a given array if the user has been unlocked for those based on their active memberships.
 *
 * @param   array   $locked_methods_ids     List of locked payment methods.
 * @param   int     $user_id                The ID of the user to check.
 *
 * @return  array|null
 */
function dws_wc_mapm_check_payment_methods_access_by_wc_memberships( array $locked_methods_ids, int $user_id ): ?array {
	try {
		$memberships_checker = dws_wc_mapm_plugin_container()->get( WC_Memberships_Integration::class );
		return $memberships_checker->maybe_grant_payment_methods_access( $locked_methods_ids, $user_id );
	} catch ( Exception $exception ) {
		return null;
	}
}

/**
 * Removes the payment methods IDs from a given array if the user has been granted access to them either through their
 * user role or through meta fields.
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
	$locked_methods_ids = dws_wc_mapm_check_payment_methods_access_by_user_roles( $locked_methods_ids, $user_id );
	$locked_methods_ids = dws_wc_mapm_check_payment_methods_access_by_user_meta( $locked_methods_ids, $user_id );
	$locked_methods_ids = dws_wc_mapm_check_payment_methods_access_by_wc_memberships( $locked_methods_ids, $user_id );

	return apply_filters( dws_wc_mapm_plugin()->get_hook_tag( 'check_payment_methods_access_for_user' ), $locked_methods_ids, $user_id );
}

/**
 * Removes the payment methods IDs from a given array if the order has been unlocked for those.
 *
 * @param   array   $locked_methods_ids     List of locked payment methods.
 * @param   int     $order_id               The ID of the order to check.
 *
 * @return  array|null
 */
function dws_wc_mapm_check_payment_methods_access_by_wc_order_meta( array $locked_methods_ids, int $order_id ): ?array {
	try {
		$order_overview = dws_wc_mapm_plugin_container()->get( OrderMeta::class );
		return $order_overview->maybe_grant_payment_methods_access( $locked_methods_ids, $order_id );
	} catch ( Exception $exception ) {
		return null;
	}
}

/**
 * Removes the payment methods IDs from a given array if the order can be paid using them either because of the order's
 * meta fields or because the order's customer is granted access to them.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   array   $locked_methods_ids     List of locked payment methods.
 * @param   int     $order_id               The ID of the order to check.
 *
 * @return  array|null
 */
function dws_wc_mapm_check_payment_methods_access_for_wc_order( array $locked_methods_ids, int $order_id ): ?array {
	$order = wc_get_order( $order_id );
	if ( ! $order instanceof WC_Order ) {
		return null;
	}

	$locked_methods_ids = dws_wc_mapm_check_payment_methods_access_by_wc_order_meta( $locked_methods_ids, $order_id );
	$locked_methods_ids = dws_wc_mapm_check_payment_methods_access_for_user( $locked_methods_ids, $order->get_customer_id() );

	return apply_filters( dws_wc_mapm_plugin()->get_hook_tag( 'check_payment_methods_access_for_wc_order' ), $locked_methods_ids, $order_id );
}

// endregion
