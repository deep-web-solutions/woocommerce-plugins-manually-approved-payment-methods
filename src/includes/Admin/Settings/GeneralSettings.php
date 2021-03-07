<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Admin\Settings;

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
 * @package DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods\Admin\Settings
 */
class GeneralSettings extends AbstractPluginFunctionality implements HooksHelpersAwareInterface, SettingsServiceAwareInterface, ValidationServiceAwareInterface {
	// region TRAITS

	use InitializeValidatedSettingsServiceTrait;
	use SettingsServiceAwareTrait { get_option_value as get_option_value_trait; }
	use SetupHooksTrait;
	use SetupSettingsTrait;
	use ValidationServiceAwareTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Registers actions and filters with the hooks service instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 * @noinspection PhpUndefinedMethodInspection
	 *
	 * @param   HooksService    $hooks_service      Instance of the hooks service.
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_filter( $this->get_parent()->get_hook_tag( 'option' ), $this, 'maybe_get_raw_setting', 10, 2 );
		$hooks_service->add_filter( $this->get_parent()->get_hook_tag( 'validated-option' ), $this, 'maybe_get_validated_setting', 10, 2 );
	}

	/**
	 * Registers the general settings options group with WC.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   SettingsService     $settings_service   Instance of the settings service.
	 */
	public function register_settings( SettingsService $settings_service ): void {
		$settings_service->register_options_group(
			'woocommerce',
			'dws-mapm-for-woocommerce_general',
			_x( 'General', 'settings', 'dws-mapm-for-woocommerce' ),
			array( $this, 'get_settings_definition' ),
			'checkout',
			array( 'section' => 'dws-mapm' )
		);
	}

	/**
	 * Retrieves a setting field's value in raw format.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 *
	 * @param   string  $field_id   The ID of the settings' field to retrieve.
	 *
	 * @return  mixed
	 */
	public function get_option_value( string $field_id ) {
		return $this->get_option_value_trait( 'woocommerce', $field_id, 'dws-mapm-for-woocommerce_general', array() );
	}

	// endregion

	// region HOOKS

	/**
	 * Retrieves a setting field's value in raw format.
	 *
	 * @param   mixed       $value      The value so far.
	 * @param   string      $field_id   The database ID of the setting.
	 *
	 * @return  mixed
	 */
	public function maybe_get_raw_setting( $value, string $field_id ) {
		return Strings::starts_with( $field_id, 'general_' )
			? $this->get_option_value( substr( $field_id, 8 ) )
			: $value;
	}

	/**
	 * Retrieves a setting field's value and runs it through a validation callback.
	 *
	 * @param   mixed       $value      The value so far.
	 * @param   string      $field_id    The database ID of the setting.
	 *
	 * @return  mixed
	 */
	public function maybe_get_validated_setting( $value, string $field_id ) {
		return Strings::starts_with( $field_id, 'general_' )
			? $this->get_general_option_value( substr( $field_id, 8 ) )
			: $value;
	}

	// endregion

	// region METHODS

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
	 * @return  mixed|void
	 */
	public function get_general_option_value( string $field_id ) {
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
