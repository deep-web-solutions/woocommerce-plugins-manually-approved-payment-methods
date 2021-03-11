<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods;

use DeepWebSolutions\Framework\Core\PluginComponents\AbstractPluginFunctionality;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Integrations\WC_Memberships_Integration;

defined( 'ABSPATH' ) || exit;

/**
 *
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods
 */
class Integrations extends AbstractPluginFunctionality {
	// region INHERITED METHODS

	/**
	 * Register integrations within the plugin tree.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Functionality::register_children_functionalities()
	 *
	 * @return  array
	 */
	protected function get_di_container_children(): array {
		return array( WC_Memberships_Integration::class );
	}

	// endregion
}
