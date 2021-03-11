<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods;

use DeepWebSolutions\Framework\Core\PluginComponents\AbstractPluginFunctionality;
use DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies\OrderMeta;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies\UserMeta;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies\UserRole;

defined( 'ABSPATH' ) || exit;

/**
 * Collection of permissions used by the plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods
 */
class LockManager extends AbstractPluginFunctionality {
	// region TRAITS

	use SetupHooksTrait;

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
		return array( OrderMeta::class, UserMeta::class, UserRole::class );
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
		$hooks_service->add_filter( 'woocommerce_available_payment_gateways', $this, 'remove_locked_payment_methods', 999 );
	}

	// endregion

	// region HOOKS

	/**
	 * Removes payment methods from the list of available ones based on the plugin's settings.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $gateways   Currently available payment methods.
	 *
	 * @return  array
	 */
	public function remove_locked_payment_methods( array $gateways ): array {
		$locked_methods_ids = dws_wc_mapm_get_validated_option( 'general_locked-payment-methods' );
		$locked_methods_ids = apply_filters( $this->get_hook_tag( 'locked_payment_methods' ), $locked_methods_ids, $gateways );

		foreach ( $locked_methods_ids as $locked_method_id ) {
			unset( $gateways[ $locked_method_id ] );
		}

		return $gateways;
	}

	// endregion
}
