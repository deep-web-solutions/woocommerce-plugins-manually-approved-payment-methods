<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies;

use DeepWebSolutions\Framework\Helpers\Security\Validation;
use DeepWebSolutions\Framework\Helpers\WordPress\Users;
use DeepWebSolutions\Framework\Settings\Actions\Initializable\InitializeSettingsServiceTrait;
use DeepWebSolutions\Framework\Settings\Actions\Setupable\SetupSettingsTrait;
use DeepWebSolutions\Framework\Settings\SettingsService;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
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
		return dws_wc_mapm_get_validated_general_option( 'override-by-order-meta' );
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
		parent::register_hooks( $hooks_service );

		$hooks_service->add_action( 'woocommerce_after_register_post_type', $this, 'register_locked_payment_methods_fields' );
	}

	/**
	 * Registers the WC order meta-box.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   SettingsService     $settings_service   Instance of the settings service.
	 */
	public function register_settings( SettingsService $settings_service ): void {
		if ( ! Users::has_capabilities( array( 'edit_shop_orders', Permissions::APPROVE_PAYMENT_METHODS_ORDER ) ) ) {
			return;
		} elseif ( empty( dws_wc_mapm_get_validated_general_option( 'locked-payment-methods' ) ) ) {
			return;
		}

		$settings_service->register_generic_group(
			'meta-box',
			'dws-manually-approveable-payment-methods',
			_x( 'Manually Approved Payment Methods', 'order', 'dws-mapm-for-woocommerce' ),
			array(),
			array(
				'post_types' => 'shop_order',
				'priority'   => 'default',
			)
		);
	}

	// endregion

	// region HOOKS

	/**
	 * Registers the applicable checkbox fields with each meta-box separately.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_locked_payment_methods_fields() {
		$locked_methods_ids  = dws_wc_mapm_get_validated_general_option( 'locked-payment-methods' );
		$locked_ids_customer = $locked_methods_ids;
		$gateways            = null;

		$post_id = Validation::validate_integer_input( INPUT_GET, 'post', 0 );
		if ( 'shop_order' === get_post_type( $post_id ) ) {
			$locked_ids_customer = dws_wc_mapm_check_payment_methods_access_for_user( $locked_methods_ids, wc_get_order( $post_id )->get_customer_id() );
			$gateways            = WC()->payment_gateways()->payment_gateways(); // For performance reasons, only load the payment gateways when viewing an actual order overview page.
		}

		foreach ( $locked_methods_ids as $locked_method_id ) {
			$this->register_locked_payment_method_field( $locked_method_id, $locked_ids_customer, $gateways );
		}
	}

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
				$value = $this->get_field_value( 'meta-box', "_dws_mapm_grant_access_{$locked_method_id}", $order_id, array() );

				if ( true === Validation::validate_boolean( $value, false ) ) {
					unset( $locked_methods_ids[ $key ] );
				}
			}
		}

		return $locked_methods_ids;
	}

	// endregion

	// region HELPERS

	/**
	 * Registers a single locked payment checkbox.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string          $locked_method_id       The ID of the payment gateway for which the checkbox is being registered.
	 * @param   array           $locked_ids_customer    The IDs of payment gateways that have NOT been unlocked by other means for the order's customer.
	 * @param   array|null      $gateways               List of gateways registered with WC or null if not viewing a WC order page.
	 */
	protected function register_locked_payment_method_field( string $locked_method_id, array $locked_ids_customer, ?array $gateways ) {
		if ( is_array( $gateways ) && ! isset( $gateways[ $locked_method_id ] ) ) {
			return;
		}

		$field_id             = "_dws_mapm_grant_access_{$locked_method_id}";
		$is_customer_unlocked = ! in_array( $locked_method_id, $locked_ids_customer, true );
		$is_customer_unlocked && add_filter(
			"rwmb_{$field_id}_field_meta",
			function () {
				return 1;
			}
		);

		$this->register_field(
			'meta-box',
			'dws-manually-approveable-payment-methods',
			$field_id,
			sprintf(
				/* translators: Name of the payment gateway. */
				_x( 'Grant access to the <i>%s</i> payment method for this order?', 'order-meta-strategy', 'dws-mapm-for-woocommerce' ),
				is_null( $gateways ) ? $locked_method_id : $gateways[ $locked_method_id ]->title
			),
			'checkbox',
			array(
				'std'      => $is_customer_unlocked ? 1 : 0,
				'disabled' => $is_customer_unlocked,
				'readonly' => $is_customer_unlocked,
				'desc'     => $is_customer_unlocked
					? _x( 'The customer is already granted access to this payment method through other means.', 'order-meta-strategy', 'dws-mapm-for-woocommerce' )
					: '',
			)
		);
	}

	// endregion
}
