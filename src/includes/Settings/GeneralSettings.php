<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Settings;

use DeepWebSolutions\Framework\Core\PluginComponents\AbstractPluginFunctionality;
use DeepWebSolutions\Framework\Helpers\DataTypes\Strings;
use DeepWebSolutions\Framework\Helpers\WordPress\Hooks\HooksHelpersAwareInterface;
use DeepWebSolutions\Framework\Settings\Actions\Initializable\InitializeValidatedSettingsServiceTrait;
use DeepWebSolutions\Framework\Settings\Actions\Setupable\SetupSettingsTrait;
use DeepWebSolutions\Framework\Settings\SettingsService;
use DeepWebSolutions\Framework\Settings\SettingsServiceAwareInterface;
use DeepWebSolutions\Framework\Settings\SettingsServiceAwareTrait;
use DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DeepWebSolutions\Framework\Utilities\Validation\ValidationServiceAwareInterface;
use DeepWebSolutions\Framework\Utilities\Validation\ValidationServiceAwareTrait;
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
				'override-per-user'      => array(
					'title'    => _x( 'Grant access per user', 'settings', 'dws-mapm-for-woocommerce' ),
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'default'  => $this->get_default_value( 'general/override-per-user' ),
					'options'  => $this->get_supported_options( 'boolean' ),
					'desc_tip' => _x( 'Adds controls for granting access to each locked payment method separately to each user\'s profile page. Access is granted for all future orders and all current unpaid orders.', 'settings', 'dws-mapm-for-woocommerce' ),
				),
				'override-per-order'     => array(
					'title'    => _x( 'Grant access per order', 'settings', 'dws-mapm-for-woocommerce' ),
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'default'  => $this->get_default_value( 'general/override-per-order' ),
					'options'  => $this->get_supported_options( 'boolean' ),
					'desc_tip' => _x( 'Adds controls for granting access to each locked payment method separately to each unpaid order. Access is granted only for the respective order.', 'settings', 'dws-mapm-for-woocommerce' ),
				),
			)
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
			case 'override-per-user':
			case 'override-per-order':
				$value = $this->validate_value( $value, "general/{$field_id}", ValidationTypesEnum::BOOLEAN );
				break;
		}

		return apply_filters( $this->get_hook_tag( 'option', array( 'general' ) ), $value, $field_id );
	}

	// endregion
}
