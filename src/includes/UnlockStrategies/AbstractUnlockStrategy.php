<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies;

use DeepWebSolutions\Framework\Core\PluginComponents\AbstractPluginFunctionality;
use DeepWebSolutions\Framework\Foundations\States\Activeable\ActiveableLocalTrait;
use DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\LockManager;

defined( 'ABSPATH' ) || exit;

/**
 * Template to encapsulate the most often needed functionalities of a payment method unlocking strategy.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies
 */
abstract class AbstractUnlockStrategy extends AbstractPluginFunctionality {
	// region TRAITS

	use ActiveableLocalTrait;
	use SetupHooksTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Checks whether the strategy is enabled or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	abstract public function is_active_local(): bool;

	/**
	 * Registers actions and filters with the hooks service.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   HooksService    $hooks_service      Instance of the hooks service.
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$lock_manager = $this->get_container_entry( LockManager::class );
		$hooks_service->add_filter( $lock_manager->get_hook_tag( 'locked_payment_methods' ), $this, 'maybe_grant_payment_methods_access' );
	}

	// endregion

	// region HOOKS

	/**
	 * Grants access to payment methods based on the current strategy.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array       $locked_methods_ids     IDs of WC payment gateways that are currently still locked.
	 *
	 * @return  array
	 */
	public function maybe_grant_payment_methods_access( array $locked_methods_ids ): array {
		if ( ! $this->is_disabled() && $this->is_active() ) {
			$locked_methods_ids = $this->filter_available_payment_methods( ...func_get_args() );
		}

		return $locked_methods_ids;
	}

	// endregion

	// region HELPERS

	/**
	 * Grants access to payment methods based on the current strategy.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $locked_methods_ids     IDs of WC payment gateways that are currently still locked.
	 *
	 * @return  array
	 */
	abstract protected function filter_available_payment_methods( array $locked_methods_ids ): array;

	// endregion
}
