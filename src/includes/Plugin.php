<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods;

use DeepWebSolutions\Framework\Core\Actions\Foundations\Setupable\States\SetupableInactiveTrait;
use DeepWebSolutions\Framework\Core\Actions\Setupable\RunnablesOnSetupTrait;
use DeepWebSolutions\Framework\Core\PluginComponents\AbstractPluginRoot;
use DeepWebSolutions\Framework\Helpers\WordPress\Users;
use DeepWebSolutions\Framework\Utilities\Actions\Initializable\InitializeDependenciesCheckerTrait;
use DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DeepWebSolutions\Framework\Utilities\Dependencies\Checkers\HandlerChecker;
use DeepWebSolutions\Framework\Utilities\Dependencies\DependenciesCheckerInterface;
use DeepWebSolutions\Framework\Utilities\Dependencies\DependenciesServiceAwareTrait;
use DeepWebSolutions\Framework\Utilities\Dependencies\Handlers\WPPluginsHandler;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

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
	use SetupableInactiveTrait;
	use SetupHooksTrait;
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
							return get_option( 'woocommerce_db_version', '0.0.0' );
						},
					),
				)
			)
		);

		return $dependencies_checker;
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
		$hooks_service->add_filter('woocommerce_available_payment_gateways', $this, 'remove_locked_payment_methods', 999 );
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
				Permissions::class,
				Admin\Settings::class,
				Admin\UserProfile::class,
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
		if ( ! Users::has_roles( array( 'administrator', 'shop_manager' ), null, 'or' ) ) {
			$locked_methods_ids = dws_wc_mapm_get_validated_option( 'general_locked-payment-methods' );
			$locked_methods_ids = apply_filters( $this->get_hook_tag( 'locked_payment_methods' ), $locked_methods_ids );

			foreach ( $locked_methods_ids as $locked_method_id ) {
				unset( $gateways[ $locked_method_id ] );
			}
		}

		return $gateways;
	}

	// endregion
}
