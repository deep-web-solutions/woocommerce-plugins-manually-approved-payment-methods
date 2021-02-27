<?php

namespace DeepWebSolutions\Plugins\WooCommerce\ManuallyApprovedPaymentMethods;

use DeepWebSolutions\Framework\Core\Abstracts\PluginFunctionality;
use DeepWebSolutions\Framework\Core\Abstracts\PluginRoot;
use DeepWebSolutions\Framework\Core\Traits\Setup\Inactive\DependenciesAdminNotice;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods
 */
final class Plugin extends PluginRoot {
	use DependenciesAdminNotice;

	// region DEPENDENCIES

	/**
	 * Register WooCommerce 4.5.2+ as a mandatory dependency for the whole plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array[]
	 */
	public function get_required_active_plugins(): array {
		return array(
			'woocommerce/woocommerce.php' => array(
				'name'            => 'WooCommerce',
				'min_version'     => '4.5.2',
				'version_checker' => function() {
					return get_option( 'woocommerce_db_version', '0.0.0' ); },
			),
		);
	}

	// endregion

	// region INHERITED METHODS

	/**
	 * Register plugin components.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     PluginFunctionality::register_children_functionalities()
	 *
	 * @return  array
	 */
	protected function define_children(): array {
		$plugin_components = array();

		return array_merge( parent::define_children(), $plugin_components );
	}

	// endregion

	// region SETTERS

	/**
	 * Sets the absolute path to the plugin file.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     PluginRoot::set_plugin_file_path()
	 */
	protected function set_plugin_file_path(): void {
		$this->plugin_file_path = DWS_WC_MAPM_BASE_PATH . 'bootstrap.php';
	}

	// endregion
}
