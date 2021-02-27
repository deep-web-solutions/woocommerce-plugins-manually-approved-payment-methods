<?php
/**
 * The Manually Approved Payment Methods for WooCommerce bootstrap file.
 *
 * @since               1.0.0
 * @version             1.0.0
 * @package             DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods
 * @author              Deep Web Solutions
 * @copyright           2021 Deep Web Solutions
 * @license             GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:             Manually Approved Payment Methods for WooCommerce
 * Description:             A WooCommerce extension which allows shop managers to hide payment methods from customers that haven't been manually granted access yet.
 * Version:                 1.0.0
 * Requires at least:       5.5
 * Requires PHP:            7.4
 * Author:                  Deep Web Solutions
 * Author URI:              https://www.deep-web-solutions.com
 * License:                 GPL-3.0+
 * License URI:             http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:             dws-manually-approved-payment-methods-for-woocommerce
 * Domain Path:             /src/languages
 * WC requires at least:    4.5
 * WC tested up to:         5.0
 */

namespace DeepWebSolutions\Plugins;

use DeepWebSolutions\Framework\Core\Abstracts\Exceptions\Initialization\FunctionalityInitializationFailure;
use DeepWebSolutions\Plugins\WooCommerce\ManuallyApprovedPaymentMethods\Plugin;
use DI\Container;
use DI\ContainerBuilder;
use function DeepWebSolutions\Framework\dws_wp_framework_check_php_wp_requirements_met;
use function DeepWebSolutions\Framework\dws_wp_framework_get_temp_dir_path;
use function DeepWebSolutions\Framework\dws_wp_framework_get_temp_dir_url;
use function DeepWebSolutions\Framework\dws_wp_framework_get_whitelabel_name;
use function DeepWebSolutions\Framework\dws_wp_framework_output_requirements_error;

defined( 'ABSPATH' ) || exit;

// Start by autoloading dependencies and defining a few functions for running the bootstrapper.
// The conditional check makes the whole thing compatible with Composer-based WP management.
file_exists( __DIR__ . '/vendor/autoload.php' ) && require_once __DIR__ . '/vendor/autoload.php';

// Check that the DWS WP Framework is loaded
if ( ! defined( 'DeepWebSolutions\Framework\DWS_WP_FRAMEWORK_BOOTSTRAPPER_INIT' ) ) {
	add_action(
		'admin_notices',
		function() {
			define( 'DWS_WC_MAPM_PLUGIN_NAME', 'Deep Web Solutions: Manually Approved Payment Methods for WooCommerce' );
			require_once __DIR__ . '/src/templates/admin/composer-error.php';
		}
	);
	return;
}

// Define plugins' constants.
define( 'DWS_WC_MAPM_BASE_PATH', plugin_dir_path( __FILE__ ) );
define( 'DWS_WC_MAPM_PLUGIN_BASE_URL', plugin_dir_url( __FILE__ ) );

define( 'DWS_WC_MAPM_PLUGIN_NAME', dws_wp_framework_get_whitelabel_name() . ': Manually Approved Payment Methods for WooCommerce' );
define( 'DWS_WC_MAPM_PLUGIN_VERSION', '1.0.0' );

define( 'DWS_WC_MAPM_PLUGIN_TEMP_DIR_NAME', 'dws-wc-product-tables' );
define( 'DWS_WC_MAPM_TEMP_DIR_PATH', dws_wp_framework_get_temp_dir_path() . DWS_WC_MAPM_PLUGIN_TEMP_DIR_NAME . DIRECTORY_SEPARATOR );
define( 'DWS_WC_MAPM_TEMP_DIR_URL', dws_wp_framework_get_temp_dir_url() . DWS_WC_MAPM_PLUGIN_TEMP_DIR_NAME . '/' );

// Define minimum environment requirements.
define( 'DWS_WC_MAPM_PLUGIN_MIN_PHP', '7.4' );
define( 'DWS_WC_MAPM_PLUGIN_MIN_WP', '5.5' );

/**
 * Returns the plugin's main class instance.
 *
 * @noinspection PhpDocMissingThrowsInspection
 *
 * @return  Plugin
 */
function dws_wc_mapm_plugin(): Plugin {
	/* @noinspection PhpUnhandledExceptionInspection */
	return dws_wc_mapm_plugin_container()->get( Plugin::class );
}

/**
 * Returns a container singleton that enables one to setup unit testing by passing an environment file for class mapping in PHP-DI.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $environment    The environment rules that the container should be initialized on.
 *
 * @throws  \Exception      Thrown if initializing the container fails.
 *
 * @return  Container
 */
function dws_wc_mapm_plugin_container( $environment = 'prod' ): Container {
	static $container = null;

	if ( is_null( $container ) ) {
		$container = ( new ContainerBuilder() )
			->addDefinitions( __DIR__ . "/config_{$environment}.php" )
			->build();
	}

	return $container;
}

/**
 * Initialization function shortcut.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
function dws_wc_mapm_plugin_initialize(): ?FunctionalityInitializationFailure {
	return dws_wc_mapm_plugin()->initialize();
}

/**
 * Activate function shortcut.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
function dws_wc_mapm_plugin_activate(): void {
	if ( is_null( dws_wc_mapm_plugin_initialize() ) ) {
		dws_wc_mapm_plugin()->activate();
	}
}

/**
 * Uninstall function shortcut.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
function dws_wc_mapm_plugin_uninstall(): void {
	if ( is_null( dws_wc_mapm_plugin_initialize() ) ) {
		dws_wc_mapm_plugin()->uninstall();
	}
}

// Start plugin initialization if system requirements check out.
if ( dws_wp_framework_check_php_wp_requirements_met( DWS_WC_MAPM_PLUGIN_MIN_PHP, DWS_WC_MAPM_PLUGIN_MIN_WP ) ) {
	add_action( 'plugins_loaded', __NAMESPACE__ . '\dws_wc_mapm_plugin_initialize' );

	register_activation_hook( __FILE__, __NAMESPACE__ . '\dws_wc_mapm_plugin_activate' );
	register_uninstall_hook( __FILE__, __NAMESPACE__ . '\dws_wc_mapm_plugin_uninstall' );
} else {
	dws_wp_framework_output_requirements_error(
		DWS_WC_MAPM_PLUGIN_NAME,
		DWS_WC_MAPM_PLUGIN_VERSION,
		DWS_WC_MAPM_PLUGIN_MIN_PHP,
		DWS_WC_MAPM_PLUGIN_MIN_WP
	);
}
