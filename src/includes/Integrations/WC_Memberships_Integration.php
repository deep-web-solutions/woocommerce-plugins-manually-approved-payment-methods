<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Integrations;

use DeepWebSolutions\Framework\Utilities\Dependencies\Handlers\WPPluginsHandler;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use WP_Post;

defined( 'ABSPATH' ) || exit;

/**
 * Unlocks payment methods based on active user memberships.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods\Integrations
 */
class WC_Memberships_Integration extends AbstractIntegrationUnlockStrategy {
	// region INHERITED METHODS

	/**
	 * Disables the integration if the minimum version of WC Memberships is not present.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	public function is_disabled_integration(): bool {
		$deps_checker = new WPPluginsHandler(
			$this->get_instance_name(),
			array(
				'woocommerce-memberships/woocommerce-memberships.php' => array(
					'name'            => 'WooCommerce Memberships',
					'min_version'     => '1.7.0',
					'version_checker' => function() {
						return get_option( 'wc_memberships_version', '0.0.0' );
					},
				),
			)
		);

		return $deps_checker->are_dependencies_fulfilled();
	}

	/**
	 * Checks if the functionality has been disabled in the plugin's settings.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	public function is_active_local(): bool {
		return true;
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

		$hooks_service->add_filter( 'wc_membership_plan_data_tabs', $this, 'register_membership_tab' );
		$hooks_service->add_action( 'wc_membership_plan_data_panels', $this, 'output_membership_fields' );
		$hooks_service->add_action( 'wc_memberships_save_meta_box', $this, 'save_membership_fields', 10, 4 );
	}

	/**
	 * Grants access to payment methods based on membership settings.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array       $locked_methods_ids     IDs of WC payment gateways that are currently still locked.
	 * @param   int|null    $user_id                The ID of the user for which access should be granted.
	 *
	 * @return  array
	 */
	protected function filter_available_payment_methods( array $locked_methods_ids, ?int $user_id = null ): array {
		$user_id            = $user_id ?? get_current_user_id();
		$active_memberships = wc_memberships_get_user_active_memberships( $user_id );

		foreach ( $active_memberships as $membership ) {
			$membership_plan_id   = $membership->get_plan_id();
			$unlocked_methods_ids = (array) get_post_meta( $membership_plan_id, '_dws_mapm_unlocked_payment_methods', true );

			foreach ( $unlocked_methods_ids as $unlocked_method_id ) {
				$locked_methods_ids = array_filter(
					$locked_methods_ids,
					function( string $value ) use ( $unlocked_method_id ) {
						return $value !== $unlocked_method_id;
					}
				);
			}
		}

		return $locked_methods_ids;
	}

	// endregion

	// region HOOKS

	/**
	 * Registers a new data tab within the WC Membership Plan meta box.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $data_tabs  Currently registered data tabs.
	 *
	 * @return  array
	 */
	public function register_membership_tab( array $data_tabs ): array {
		$data_tabs['dws_mapm'] = array(
			'label'  => _x( 'Unlocked Payment Methods', 'wc-memberships-integration', 'dws-mapm-for-woocommerce' ),
			'target' => 'dws-mapm-membership-plan-data',
		);

		return $data_tabs;
	}

	/**
	 * Output the custom membership fields on the custom tab.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function output_membership_fields() {
		$gateways             = WC()->payment_gateways()->payment_gateways();
		$locked_methods_ids   = dws_wc_mapm_get_validated_general_option( 'locked-payment-methods' );
		$unlocked_methods_ids = (array) get_post_meta( $GLOBALS['post']->ID, '_dws_mapm_unlocked_payment_methods', true );

		?>

		<div id="dws-mapm-membership-plan-data" class="panel woocommerce_options_panel">
			<p class="form-field">
				<label for="_dws_mapm_unlocked_payment_methods">
					<?php esc_html_e( 'Grant access to payment methods:', 'dws-mapm-for-woocommerce' ); ?>
				</label>

				<select
					name="_dws_mapm_unlocked_payment_methods[]"
					id="_dws_mapm_unlocked_payment_methods"
					class="wc-enhanced-select-nostd"
					multiple="multiple"
					data-allow_clear="true"
					data-placeholder="<?php esc_attr_e( 'Payment methods', 'dws-mapm-for-woocommerce' ); ?>"
					style="width: 90%;">
					<?php
					foreach ( $locked_methods_ids as $locked_method_id ) : // phpcs:ignore
						if ( isset( $gateways[ $locked_method_id ] ) ) : // phpcs:ignore
							?>
						<option value="<?php echo esc_attr( $locked_method_id ); ?>" <?php selected( true, in_array( $locked_method_id, $unlocked_methods_ids, true ) ); ?> >
							<?php echo esc_html( $gateways[ $locked_method_id ]->title ); ?>
						</option>
							<?php
						endif;
					endforeach;
					?>
				</select>
			</p>
		</div>

		<?php
	}

	/**
	 * Save the custom membership fields.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @param   array       $post_data      The $_POST data.
	 * @param   string      $meta_box_id    The meta box ID.
	 * @param   int         $post_id        The ID of the membership plan post.
	 * @param   WP_Post     $post           The actual membership plan post.
	 */
	public function save_membership_fields( array $post_data, string $meta_box_id, int $post_id, WP_Post $post ) {
		$unlocked_methods_ids = filter_input( INPUT_POST, '_dws_mapm_unlocked_payment_methods', FILTER_DEFAULT, FILTER_FORCE_ARRAY );
		if ( ! empty( $unlocked_methods_ids ) ) {
			$locked_methods_ids   = dws_wc_mapm_get_validated_general_option( 'locked-payment-methods' );
			$unlocked_methods_ids = array_filter(
				$unlocked_methods_ids,
				function( $value ) use ( $locked_methods_ids ) {
					return in_array( $value, $locked_methods_ids, true );
				}
			);
		}

		update_post_meta( $post_id, '_dws_mapm_unlocked_payment_methods', $unlocked_methods_ids );
	}

	// endregion
}
