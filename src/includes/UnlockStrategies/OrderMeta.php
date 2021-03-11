<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies;

use DeepWebSolutions\Framework\Core\PluginComponents\AbstractPluginFunctionality;
use DeepWebSolutions\Framework\Foundations\States\Activeable\ActiveableLocalTrait;
use DeepWebSolutions\Framework\Helpers\Security\Validation;
use DeepWebSolutions\Framework\Helpers\WordPress\Users;
use DeepWebSolutions\Framework\Settings\Actions\Initializable\InitializeSettingsServiceTrait;
use DeepWebSolutions\Framework\Settings\Actions\Setupable\SetupSettingsTrait;
use DeepWebSolutions\Framework\Settings\SettingsService;
use DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\LockManager;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Permissions;

defined( 'ABSPATH' ) || exit;

/**
 * Unlocks payment methods based on the order's custom fields settings.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies
 */
class OrderMeta extends AbstractUnlockStrategy {
	// region TRAITS

	use ActiveableLocalTrait;
	use InitializeSettingsServiceTrait;
	use SetupSettingsTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Checks if the functionality has been disabled in the plugin's settings.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	public function is_active_local(): bool {
		return dws_wc_mapm_get_validated_general_option( 'override-per-order' );
	}

	/**
	 * Registers the WC order metabox.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   SettingsService     $settings_service   Instance of the settings service.
	 */
	public function register_settings( SettingsService $settings_service ): void {
		if ( ! Users::has_capabilities( array( 'edit_shop_orders', Permissions::APPROVE_PAYMENT_METHODS_ORDER ) ) ) {
			return;
		}

		$locked_methods_ids = dws_wc_mapm_get_validated_general_option( 'locked-payment-methods' );

		if ( ! empty( $locked_methods_ids ) ) {
			$gateways = WC()->payment_gateways()->payment_gateways();
			$settings_service->register_generic_group(
				'meta-box',
				'dws-mapm',
				_x( 'Manually Approved Payment Methods', 'order', 'dws-mapm-for-woocommerce' ),
				array(
					...array_filter(
						array_map(
							function( string $locked_method_id ) use ( $gateways ) {
								return isset( $gateways[ $locked_method_id ] )
									? array(
										'id'   => "dws_mapm_grant_access_{$locked_method_id}",
										'name' => sprintf(
											/* translators: Name of the payment gateway. */
											_x( 'Grant access to the <i>%s</i> payment method for this user?', 'user-profile', 'dws-mapm-for-woocommerce' ),
											$gateways[ $locked_method_id ]->title
										),
										'type' => 'checkbox',
										'std'  => 0,
									) : false;
							},
							$locked_methods_ids
						),
					),
				),
				array(
					'post_types' => 'shop_order',
					'context'    => 'normal',
					'style'      => 'default',
					'priority'   => 'low',
				)
			);
		}
	}

	// endregion

	// region HOOKS

	/**
	 * Grants access to payment methods based on order settings.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array       $locked_methods_ids     IDs of WC payment gateways that are currently still locked.
	 * @param   int|null    $order_id               The ID of the order for which access should be granted.
	 *
	 * @return  array
	 */
	public function maybe_grant_payment_methods_access( array $locked_methods_ids, ?int $order_id = null ): array {
		if ( $order_id || is_checkout_pay_page() ) {
			$order_id = $order_id ?: $GLOBALS['wp']->query_vars['order-pay']; // phpcs:ignore
			foreach ( $locked_methods_ids as $key => $locked_method_id ) {
				$value = $this->get_field_value( 'meta-box', "dws_mapm_grant_access_{$locked_method_id}", $order_id, array() );

				if ( true === Validation::validate_boolean( $value, false ) ) {
					unset( $locked_methods_ids[ $key ] );
				}
			}
		}

		return $locked_methods_ids;
	}

	// endregion
}
