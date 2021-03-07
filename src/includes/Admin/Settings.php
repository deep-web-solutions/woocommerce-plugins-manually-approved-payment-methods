<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Admin;

use DeepWebSolutions\Framework\Core\PluginComponents\AbstractPluginFunctionality;
use DeepWebSolutions\Framework\Foundations\Helpers\HooksHelpersTrait;
use DeepWebSolutions\Framework\Settings\Actions\Initializable\InitializeValidatedSettingsServiceTrait;
use DeepWebSolutions\Framework\Settings\Actions\SettingsActionResponse;
use DeepWebSolutions\Framework\Settings\Actions\Setupable\SetupSettingsTrait;
use DeepWebSolutions\Framework\Settings\SettingsService;
use DeepWebSolutions\Framework\Settings\SettingsServiceAwareInterface;
use DeepWebSolutions\Framework\Settings\ValidatedSettingsServiceAwareTrait;
use DeepWebSolutions\Framework\Utilities\Validation\ValidationServiceAwareInterface;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Admin\Settings\GeneralSettings;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the plugin's settings with WC.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods\Admin
 */
class Settings extends AbstractPluginFunctionality implements SettingsServiceAwareInterface, ValidationServiceAwareInterface {
	// region TRAITS

	use HooksHelpersTrait;
	use InitializeValidatedSettingsServiceTrait;
	use ValidatedSettingsServiceAwareTrait {
		get_option_value as get_option_value_trait;
		get_validated_option_value as get_validated_option_value_trait;
	}
	use SetupSettingsTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Returns the functionality's children in the plugin tree.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string[]
	 */
	protected function get_di_container_children(): array {
		return array( GeneralSettings::class );
	}

	/**
	 * Register the admin settings on a dedicated WC settings section.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   SettingsService     $settings_service       Instance of the settings service.
	 */
	protected function register_settings( SettingsService $settings_service ): void {
		$settings_service->register_submenu_page(
			'woocommerce',
			'checkout',
			'',
			_x( 'Manually Approved Payment Methods', 'settings', 'dws-mapm-for-woocommerce' ),
			'dws-mapm',
			'manage_woocommerce',
			array()
		);
	}

	/**
	 * Retrieves a setting field's value in a raw format.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 *
	 * @param   string  $field_id   The ID of the field within the settings to read from the database.
	 *
	 * @return  mixed
	 */
	public function get_option_value( string $field_id ) {
		return apply_filters( $this->get_hook_tag( 'option' ), null, $field_id );
	}

	/**
	 * Retrieves a setting field's value and runs it through a validation callback.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 *
	 * @param   string  $field_id   The ID of the field within the settings to read from the database.
	 *
	 * @return  mixed
	 */
	public function get_validated_option_value( string $field_id ) {
		return apply_filters( $this->get_hook_tag( 'validated-option' ), null, $field_id );
	}

	// endregion
}
