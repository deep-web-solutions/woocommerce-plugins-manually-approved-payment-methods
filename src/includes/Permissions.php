<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods;

use DeepWebSolutions\Framework\Core\Actions\Installable;
use DeepWebSolutions\Framework\Core\Actions\InstallableInterface;
use DeepWebSolutions\Framework\Core\PluginComponents\AbstractPluginFunctionality;

defined( 'ABSPATH' ) || exit;

/**
 * Collection of permissions used by the plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods
 */
class Permissions extends AbstractPluginFunctionality implements InstallableInterface {
	// region PERMISSION CONSTANTS

	/**
	 * Permission required to be able to approve a user's account for using locked payment methods.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  public
	 * @var     string
	 */
	public const APPROVE_PAYMENT_METHODS_USER = 'dws_mapm_approve_payment_methods_user';

	/**
	 * Permission required to be able to approve a specific order for being paid with a locked payment method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  public
	 * @var     string
	 */
	public const APPROVE_PAYMENT_METHODS_ORDER = 'dws_mapm_approve_payment_methods_order';

	// endregion

	// region INHERITED METHODS

	/**
	 * Adds the default capabilities to admins and shop managers.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Installable\InstallFailureException|null
	 */
	public function install(): ?Installable\InstallFailureException {
		$default_roles = array_filter(
			array(
				get_role( 'administrator' ),
				get_role( 'shop_manager' ),
			)
		);
		$default_caps  = self::get_reflection_class()->getConstants();

		foreach ( $default_roles as $role ) {
			foreach ( $default_caps as $capability ) {
				$role->add_cap( $capability );
			}
		}

		return null;
	}

	/**
	 * Installs newly added capabilities.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @param   string  $current_version    Currently installed version.
	 *
	 * @return  Installable\UpdateFailureException|null
	 */
	public function update( string $current_version ): ?Installable\UpdateFailureException {
		// currently not applicable
		return null;
	}

	/**
	 * Removes the installed capabilities from all roles.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @param   string  $current_version    Currently installed version.
	 *
	 * @return  Installable\UninstallFailureException|null
	 */
	public function uninstall( string $current_version ): ?Installable\UninstallFailureException {
		$remove_data = dws_wc_mapm_get_validated_option( 'plugin_remove-data-uninstall' );

		if ( true === $remove_data ) {
			$default_caps = self::get_reflection_class()->getConstants();
			foreach ( wp_roles()->role_objects as $role ) {
				foreach ( $default_caps as $capability ) {
					$role->remove_cap( $capability );
				}
			}
		}

		return null;
	}

	/**
	 * The permissions version is defined by the md5 hash of the constants defining said permissions.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_current_version(): string {
		return hash( 'md5', wp_json_encode( self::get_reflection_class()->getConstants() ) );
	}

	// endregion
}
