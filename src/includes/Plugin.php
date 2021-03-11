<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods;

use DeepWebSolutions\Framework\Core\Actions\Foundations\Setupable\States\SetupableInactiveTrait;
use DeepWebSolutions\Framework\Core\Actions\Setupable\RunnablesOnSetupTrait;
use DeepWebSolutions\Framework\Core\PluginComponents\AbstractPluginRoot;
use DeepWebSolutions\Framework\Foundations\Helpers\HooksHelpersTrait;
use DeepWebSolutions\Framework\Utilities\Actions\Initializable\InitializeDependenciesCheckerTrait;
use DeepWebSolutions\Framework\Utilities\Dependencies\Checkers\HandlerChecker;
use DeepWebSolutions\Framework\Utilities\Dependencies\DependenciesCheckerInterface;
use DeepWebSolutions\Framework\Utilities\Dependencies\DependenciesServiceAwareTrait;
use DeepWebSolutions\Framework\Utilities\Dependencies\Handlers\WPPluginsHandler;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods
 */
final class Plugin extends AbstractPluginRoot {
	// region TRAITS

	use DependenciesServiceAwareTrait;
	use InitializeDependenciesCheckerTrait;
	use HooksHelpersTrait;
	use SetupableInactiveTrait;
	use RunnablesOnSetupTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Initializes the dependencies checker.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  HandlerChecker
	 */
	public function get_dependencies_checker(): DependenciesCheckerInterface {
		$dependencies_checker = new HandlerChecker();

		$dependencies_checker->register_handler(
			new WPPluginsHandler(
				$this->get_instance_name(),
				array(
					'woocommerce/woocommerce.php' => array(
						'name'            => 'WooCommerce',
						'min_version'     => '4.5.2',
						'version_checker' => function() {
							return defined( 'WC_VERSION' ) ? WC_VERSION : '0.0.0';
						},
					),
				)
			)
		);

		return $dependencies_checker;
	}

	/**
	 * Register plugin components.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Functionality::register_children_functionalities()
	 *
	 * @return  array
	 */
	protected function get_di_container_children(): array {
		return array_merge(
			parent::get_di_container_children(),
			array(
				Settings::class,
				Permissions::class,
				Integrations::class,
				LockManager::class,
			)
		);
	}

	// endregion

	// region SETTERS

	/**
	 * Sets the absolute path to the plugin file.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     PluginBase::set_plugin_file_path()
	 */
	protected function initialize_plugin_file_path(): void {
		$this->plugin_file_path = DWS_WC_MAPM_BASE_PATH . 'bootstrap.php';
	}

	// endregion
}
