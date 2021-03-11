<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Settings;

use DeepWebSolutions\Framework\Utilities\Validation\ValidationTypesEnum;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the plugin's General Settings with WC.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods\Settings
 */
class GeneralSettings extends AbstractSettingsGroup {
	// region INHERITED METHODS

	/**
	 * Returns the settings fields' definition.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array[]
	 */
	public function get_settings_definition(): array {
		$enabled_gateways = array_filter(
			WC()->payment_gateways()->payment_gateways(),
			function( \WC_Payment_Gateway $gateway ) {
				return 'yes' === $gateway->enabled;
			}
		);

		return apply_filters(
			$this->get_hook_tag( 'definition' ),
			array(
				'locked-payment-methods' => array(
					'title'    => _x( 'Payment methods which require manual approval', 'settings', 'dws-mapm-for-woocommerce' ),
					'type'     => 'multiselect',
					'class'    => 'wc-enhanced-select',
					'default'  => $this->get_default_value( 'general/locked-payment-methods' ),
					'options'  => array_combine(
						array_column( $enabled_gateways, 'id' ),
						array_column( $enabled_gateways, 'title' ),
					),
					'desc_tip' => _x( 'Only enabled payment methods can be selected', 'settings', 'dws-mapm-for-woocommerce' ),
				),
				'override-by-user-role'  => array(
					'title'    => _x( 'Grant access through user roles', 'settings', 'dws-mapm-for-woocommerce' ),
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'default'  => $this->get_default_value( 'general/override-by-user-role' ),
					'options'  => $this->get_supported_options( 'boolean' ),
					'desc_tip' => _x( 'Users with certain roles will be granted full access to the locked payment methods.', 'settings', 'dws-mapm-for-woocommerce' ),
				),
				'override-by-user-meta'  => array(
					'title'    => _x( 'Grant access through user fields', 'settings', 'dws-mapm-for-woocommerce' ),
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'default'  => $this->get_default_value( 'general/override-by-user-meta' ),
					'options'  => $this->get_supported_options( 'boolean' ),
					'desc_tip' => _x( 'Adds controls for granting access to each locked payment method separately to each user\'s profile page. Access is granted for all future orders and all current unpaid orders.', 'settings', 'dws-mapm-for-woocommerce' ),
				),
				'override-by-order-meta' => array(
					'title'    => _x( 'Grant access through order fields', 'settings', 'dws-mapm-for-woocommerce' ),
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'default'  => $this->get_default_value( 'general/override-by-order-meta' ),
					'options'  => $this->get_supported_options( 'boolean' ),
					'desc_tip' => _x( 'Adds controls for granting access to each locked payment method separately to each unpaid order. Access is granted only for the respective order.', 'settings', 'dws-mapm-for-woocommerce' ),
				),
			),
			$enabled_gateways
		);
	}

	/**
	 * Retrieves the value of a general setting, validated.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $field_id   The ID of the general option field to retrieve and validate.
	 *
	 * @return  mixed
	 */
	public function get_validated_option_value( string $field_id ) {
		$value = $this->get_option_value( $field_id );

		switch ( $field_id ) {
			case 'locked-payment-methods':
				$value = array_filter( (array) $value, 'is_string' );
				break;
			case 'override-by-user-role':
			case 'override-by-user-meta':
			case 'override-by-order-meta':
				$value = $this->validate_value( $value, "general/{$field_id}", ValidationTypesEnum::BOOLEAN );
				break;
		}

		return apply_filters( $this->get_hook_tag( 'option', array( 'general' ) ), $value, $field_id );
	}

	// endregion
}
