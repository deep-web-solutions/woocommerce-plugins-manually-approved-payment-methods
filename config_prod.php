<?php

use DeepWebSolutions\Framework\Core\Actions\Installation;
use DeepWebSolutions\Framework\Core\Actions\Internationalization;
use DeepWebSolutions\Framework\Helpers\WordPress\Requests;
use DeepWebSolutions\Framework\Settings\Factories\HandlerFactory;
use DeepWebSolutions\Framework\Utilities\Factories\LoggerFactory;
use DeepWebSolutions\Framework\Utilities\Handlers\HooksHandler;
use DeepWebSolutions\Framework\Utilities\Interfaces\Resources\Pluginable;
use DeepWebSolutions\Framework\Utilities\Services\LoggingService;
use DeepWebSolutions\Framework\WooCommerce\Settings\Adapter as WooCommerceAdapter;
use DeepWebSolutions\Framework\WooCommerce\Settings\Handler as WooCommerceHandler;
use DeepWebSolutions\Framework\WooCommerce\Utilities\Logger as WooCommerceLogger;
use DeepWebSolutions\Plugins\WooCommerce\ManuallyApprovedPaymentMethods\Plugin;
use function DeepWebSolutions\Plugins\dws_wc_mapm_plugin_container;
use function DI\factory;
use function DI\get;
use function DI\autowire;

defined( 'ABSPATH' ) || exit;

return array(
	// Utilities
	Pluginable::class           => get( Plugin::class ),
	LoggerFactory::class        => factory(
		function() {
			$min_log_level = Requests::has_debug() ? WC_Log_Levels::DEBUG : WC_Log_Levels::ERROR;
			$handler       = new WC_Log_Handler_File();

			$logger_factory = new LoggerFactory();

			$logger_factory->register_factory_callable(
				'framework',
				function() use ( $handler, $min_log_level ) {
					$logger = new WooCommerceLogger( 'framework', array( $handler ), $min_log_level );
					dws_wc_mapm_plugin_container()->call( array( $logger, 'set_plugin' ) );

					return $logger;
				}
			);
			$logger_factory->register_factory_callable(
				'plugin',
				function() use ( $handler, $min_log_level ) {
					$logger = new WooCommerceLogger( 'plugin', array( $handler ), $min_log_level );
					dws_wc_mapm_plugin_container()->call( array( $logger, 'set_plugin' ) );

					return $logger;
				}
			);

			return $logger_factory;
		}
	),
	LoggingService::class       => autowire()->constructorParameter( 'include_sensitive', Requests::has_debug() ),

	// Core
	Installation::class         => autowire()->constructorParameter( 'node_name', 'Installation' ),
	Internationalization::class => autowire()->constructorParameter( 'node_name', 'Internationalization' ),

	// Settings
	HandlerFactory::class       => factory(
		function( LoggingService $logging_service ) {
			$handler_factory = new HandlerFactory( $logging_service );

			$handler_factory->register_factory_callable(
				'woocommerce',
				function() use ( $logging_service ) {
					return new WooCommerceHandler( new WooCommerceAdapter(), $logging_service );
				}
			);

			return $handler_factory;
		}
	),

	// Plugin
	Plugin::class               => autowire()
		->method( 'set_container', dws_wc_mapm_plugin_container() )
		->method( 'register_runnable_on_setup', get( HooksHandler::class ) ),
);
