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
class PluginSettings extends AbstractSettingsGroup {
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
		return apply_filters(
			$this->get_hook_tag( 'definition' ),
			array(
				'remove-data-uninstall' => array(
					'title'    => _x( 'Remove all data on uninstallation?', 'settings', 'dws-mapm-for-woocommerce' ),
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'default'  => $this->get_default_value( 'plugin/remove-data-uninstall' ),
					'options'  => $this->get_supported_options( 'boolean' ),
					'desc_tip' => _x( 'If enabled, the plugin will remove all database data when removed and you will need to reconfigure everything if you install it again at a later time.', 'settings', 'dws-mapm-for-woocommerce' ),
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
	public function get_validated_option_value( string $field_id ) {
		$value = $this->get_option_value( $field_id );

		switch ( $field_id ) {
			case 'remove-data-uninstall':
				$value = $this->validate_value( $value, "plugin/{$field_id}", ValidationTypesEnum::BOOLEAN );
				break;
		}

		return apply_filters( $this->get_hook_tag( 'option', array( 'plugin' ) ), $value, $field_id );
	}

	// endregion
}
