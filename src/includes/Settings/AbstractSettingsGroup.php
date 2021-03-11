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
use DeepWebSolutions\Framework\Utilities\Logging\LoggingService;
use DeepWebSolutions\Framework\Utilities\Validation\ValidationServiceAwareInterface;
use DeepWebSolutions\Framework\Utilities\Validation\ValidationServiceAwareTrait;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Template to encapsulate the most often needed functionalities for registering a group of options on the plugin's WC section.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods\Settings
 */
abstract class AbstractSettingsGroup extends AbstractPluginFunctionality implements HooksHelpersAwareInterface, SettingsServiceAwareInterface, ValidationServiceAwareInterface {
	// region TRAITS

	use InitializeValidatedSettingsServiceTrait;
	use SettingsServiceAwareTrait {
		get_option_value as get_option_value_trait;
	}
	use SetupHooksTrait;
	use SetupSettingsTrait;
	use ValidationServiceAwareTrait;

	// endregion

	// region FIELDS AND CONSTANTS

	/**
	 * The title of the settings group.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     string
	 */
	protected string $group_title;

	// endregion

	// region MAGIC METHODS

	/**
	 * AbstractSettingsGroup constructor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   LoggingService  $logging_service    Instance of the logging service.
	 * @param   string          $group_title        Title of the settings group.
	 * @param   string|null     $component_id       Unique ID of the settings page component. Optional.
	 * @param   string|null     $component_name     English-name of the settings page component. Optional.
	 */
	public function __construct( LoggingService $logging_service, string $group_title, ?string $component_id = null, ?string $component_name = null ) {
		parent::__construct( $logging_service, $component_id, $component_name );
		$this->group_title = $group_title;
	}

	// endregion

	// region INHERITED METHODS

	/**
	 * Registers actions and filters with the hooks service instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   HooksService    $hooks_service      Instance of the hooks service.
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_filter( $this->get_container_entry( Settings::class )->get_hook_tag( 'option' ), $this, 'maybe_get_raw_setting', 10, 2 );
		$hooks_service->add_filter( $this->get_container_entry( Settings::class )->get_hook_tag( 'validated-option' ), $this, 'maybe_get_validated_setting', 10, 2 );
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
			'dws-mapm-for-woocommerce_' . $this->get_settings_group_slug_suffix(),
			$this->group_title,
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
		return $this->get_option_value_trait( 'woocommerce', $field_id, 'dws-mapm-for-woocommerce_' . $this->get_settings_group_slug_suffix(), array() );
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
		return Strings::starts_with( $field_id, $this->get_settings_group_slug_suffix() . '_' )
			? $this->get_option_value( substr( $field_id, strlen( $this->get_settings_group_slug_suffix() ) + 1 ) )
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
		return Strings::starts_with( $field_id, $this->get_settings_group_slug_suffix() . '_' )
			? $this->get_validated_option_value( substr( $field_id, strlen( $this->get_settings_group_slug_suffix() ) + 1 ) )
			: $value;
	}

	// endregion

	// region METHODS

	/**
	 * Returns the settings group's slug suffix.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_settings_group_slug_suffix(): string {
		return str_replace( '-settings', '', parent::get_instance_safe_name() );
	}

	/**
	 * Returns the settings fields' definition.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array[]
	 */
	abstract public function get_settings_definition(): array;

	/**
	 * Retrieves the value of a setting, validated.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $field_id   The ID of the general option field to retrieve and validate.
	 *
	 * @return  mixed
	 */
	abstract public function get_validated_option_value( string $field_id );

	// endregion
}
