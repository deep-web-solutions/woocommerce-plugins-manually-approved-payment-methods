<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Integrations;

use DeepWebSolutions\Framework\Core\States\Disableable\IntegrationTrait;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies\AbstractUnlockStrategy;

defined( 'ABSPATH' ) || exit;

/**
 * Template to encapsulate the most often needed functionalities of an integration-based payment method unlocking strategy.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods\Integrations
 */
abstract class AbstractIntegrationUnlockStrategy extends AbstractUnlockStrategy {
	// region TRAITS

	use IntegrationTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Disables the integration if the minimum version of WC Memberships is not present.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	abstract public function is_disabled_integration(): bool;

	// endregion
}
