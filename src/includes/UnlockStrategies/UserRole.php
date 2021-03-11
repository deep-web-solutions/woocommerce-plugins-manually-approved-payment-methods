<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies;

use DeepWebSolutions\Framework\Helpers\WordPress\Users;

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
	// region HOOKS

	/**
	 * Grants full access to all available payment methods if the user has certain roles.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $locked_methods_ids     IDs of WC payment gateways that are currently still locked.
	 * @param   array   $params                 Parameters to pass on to the user roles checker.
	 *
	 * @return  array
	 */
	public function maybe_grant_payment_methods_access( array $locked_methods_ids, array $params = array() ): array {
		$params = wp_parse_args(
			$params,
			array(
				'roles'   => array( 'administrator', 'shop_manager' ),
				'user_id' => null,
				'logic'   => 'or',
			)
		);
		$params = apply_filters( $this->get_hook_tag( 'params' ), $params, $locked_methods_ids );

		return Users::has_roles( (array) $params['roles'], intval( $params['user_id'] ), strval( $params['logic'] ) )
			? array()
			: $locked_methods_ids;
	}

	// endregion
}
