<?php

use DeepWebSolutions\Framework\Core\PluginComponents\Actions\Installation;
use DeepWebSolutions\Framework\Core\PluginComponents\Actions\Internationalization;
use DeepWebSolutions\Framework\Foundations\Plugin\PluginInterface;
use DeepWebSolutions\Framework\Helpers\WordPress\Request;
use DeepWebSolutions\Framework\Settings\Handlers\MetaBox_Handler;
use DeepWebSolutions\Framework\Settings\SettingsService;
use DeepWebSolutions\Framework\Utilities\AdminNotices\Stores\OptionsStoreAdmin;
use DeepWebSolutions\Framework\Utilities\AdminNotices\Stores\UserMetaStoreAdmin;
use DeepWebSolutions\Framework\Utilities\Hooks\Handlers\HooksHandler;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DeepWebSolutions\Framework\Utilities\Logging\LoggingService;
use DeepWebSolutions\Framework\Utilities\Validation\ValidationService;
use DeepWebSolutions\Framework\WooCommerce\Settings\WC_Handler;
use DeepWebSolutions\Framework\WooCommerce\Utilities\WC_Logger as DWS_WC_Logger;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Plugin;
use DI\ContainerBuilder;
use function DeepWebSolutions\WC_Plugins\dws_wc_mapm_plugin_container;
use function DI\factory;
use function DI\get;
use function DI\autowire;

defined( 'ABSPATH' ) || exit;

return array(
	// Foundations
	PluginInterface::class      => get( Plugin::class ),

	// Utilities
	HooksService::class         => factory(
		function( Plugin $plugin, LoggingService $logging_service, HooksHandler $handler ) {
			$hooks_service = new HooksService( $plugin, $logging_service, $handler );
			$plugin->register_runnable_on_setup( $hooks_service );
			return $hooks_service;
		}
	),
	LoggingService::class       => factory(
		function( PluginInterface $plugin ) {
			$min_log_level = Request::has_debug() ? WC_Log_Levels::DEBUG : WC_Log_Levels::ERROR;
			$handler       = new WC_Log_Handler_File();
			$loggers       = array(
				'framework' => new DWS_WC_Logger( 'framework', array( $handler ), $min_log_level ),
				'plugin'    => new DWS_WC_Logger( 'plugin', array( $handler ), $min_log_level ),
			);

			return new LoggingService( $plugin, $loggers, Request::has_debug() );
		}
	),

	'admin_notices_key'         => factory(
		function( PluginInterface $plugin ) {
			return '_dws_admin_notices_' . $plugin->get_plugin_safe_slug();
		}
	),
	OptionsStoreAdmin::class    => autowire()->constructorParameter( 'option_key', get( 'admin_notices_key' ) ),
	UserMetaStoreAdmin::class   => autowire()->constructorParameter( 'meta_key', get( 'admin_notices_key' ) ),

	// Core
	Installation::class         => autowire()->constructorParameter( 'component_name', 'Installation' ),
	Internationalization::class => autowire()->constructorParameter( 'component_name', 'Internationalization' ),

	// Settings
	SettingsService::class      => factory(
		function( Plugin $plugin, LoggingService $logging_service ) {
			return new SettingsService( $plugin, $logging_service, array( new WC_Handler(), new MetaBox_Handler() ) );
		}
	),
	ValidationService::class    => factory(
		function( Plugin $plugin, LoggingService $logging_service ) {
			$container = ( new ContainerBuilder() )->addDefinitions( __DIR__ . '/src/configs/settings.php' )->build();
			return new ValidationService( $plugin, $logging_service, $container );
		}
	),

	// Plugin
	Plugin::class               => autowire()->method( 'set_container', dws_wc_mapm_plugin_container() ),
);
