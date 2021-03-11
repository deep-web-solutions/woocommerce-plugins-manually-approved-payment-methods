<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies;

use DeepWebSolutions\Framework\Helpers\DataTypes\Arrays;
use DeepWebSolutions\Framework\Helpers\WordPress\Users;
use DeepWebSolutions\Framework\Utilities\Actions\Initializable\InitializeValidationServiceTrait;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DeepWebSolutions\Framework\Utilities\Validation\ValidationTypesEnum;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Settings\GeneralSettings;

defined( 'ABSPATH' ) || exit;

/**
 * Unlocks payment methods based on the user's roles.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies
 */
class UserRole extends AbstractUnlockStrategy {
	// region TRAITS

	use InitializeValidationServiceTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Checks if the functionality has been disabled in the plugin's settings.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	public function is_active_local(): bool {
		return dws_wc_mapm_get_validated_general_option( 'override-by-user-role' );
	}

	/**
	 * Registers actions and filters with the hooks service.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   HooksService    $hooks_service      Instance of the hooks service.
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		parent::register_hooks( $hooks_service );

		$general_settings = $this->get_container_entry( GeneralSettings::class );
		$hooks_service->add_filter( $general_settings->get_hook_tag( 'definition' ), $this, 'filter_settings_definition' );
		$hooks_service->add_filter( $general_settings->get_hook_tag( 'option', array( 'general' ) ), $this, 'filter_validated_option_value', 10, 2 );
	}

	/**
	 * Grants full access to all available payment methods if the user has certain roles.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array       $locked_methods_ids     IDs of WC payment gateways that are currently still locked.
	 * @param   int|null    $user_id                The ID of the user for which access should be granted.
	 *
	 * @return  array
	 */
	protected function filter_available_payment_methods( array $locked_methods_ids, ?int $user_id = null ): array {
		$roles   = dws_wc_mapm_get_validated_general_option( 'full-access-user-roles' );
		$user_id = $user_id ?? get_current_user_id();

		return Users::has_roles( $roles, $user_id, 'or' ) ? array() : $locked_methods_ids;
	}

	// endregion

	// region HOOKS

	/**
	 * Registers the strategy's options within the general options group.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $settings   Currently registered general options.
	 *
	 * @return  array
	 */
	public function filter_settings_definition( array $settings ): array {
		return Arrays::insert_after(
			$settings,
			'override-by-user-role',
			array(
				'full-access-user-roles' => array(
					'title'   => _x( 'User roles with full access to all enabled payment methods', 'settings', 'dws-mapm-for-woocommerce' ),
					'type'    => 'multiselect',
					'class'   => 'wc-enhanced-select',
					'default' => $this->get_default_value( 'general/full-access-user-roles' ),
					'options' => $this->get_supported_options( 'general/full-access-user-roles' ),
				),
			)
		);
	}

	/**
	 * Validates the strategy's options.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   mixed   $value      The current value of the field.
	 * @param   string  $field_id   The ID of the option currently being validated.
	 *
	 * @return  mixed
	 */
	public function filter_validated_option_value( $value, string $field_id ) {
		switch ( $field_id ) {
			case 'full-access-user-roles':
				$db_roles = (array) $value;
				foreach ( $db_roles as $key => $db_role ) {
					$db_role = $this->validate_value( $db_role, 'empty_string', ValidationTypesEnum::OPTION, array( 'options_key' => "general/{$field_id}" ) );
					if ( empty( $db_role ) ) {
						unset( $db_roles[ $key ] );
					}
				}
				$value = $db_roles;
				break;
		}

		return $value;
	}

	// endregion
}
