<?php

namespace DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies;

use DeepWebSolutions\Framework\Core\PluginComponents\AbstractPluginFunctionality;
use DeepWebSolutions\Framework\Foundations\States\Activeable\ActiveableLocalTrait;
use DeepWebSolutions\Framework\Helpers\Security\Validation;
use DeepWebSolutions\Framework\Helpers\WordPress\Users;
use DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\LockManager;
use DeepWebSolutions\WC_Plugins\ManuallyApprovedPaymentMethods\Permissions;
use WP_User;

defined( 'ABSPATH' ) || exit;

/**
 * Unlocks payment methods based on the settings in the user's profile.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods\UnlockStrategies
 */
class UserMeta extends AbstractUnlockStrategy {
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
		return dws_wc_mapm_get_validated_general_option( 'override-by-user-meta' );
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

		$hooks_service->add_action( 'show_user_profile', $this, 'register_locked_payment_methods_fields', 30 );
		$hooks_service->add_action( 'edit_user_profile', $this, 'register_locked_payment_methods_fields', 30 );
		$hooks_service->add_action( 'personal_options_update', $this, 'save_locked_payment_methods_fields' );
		$hooks_service->add_action( 'edit_user_profile_update', $this, 'save_locked_payment_methods_fields' );
	}

	/**
	 * Grants access to payment methods based on user meta settings.
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
		$user_id = $user_id ?? get_current_user_id();
		foreach ( $locked_methods_ids as $key => $locked_method_id ) {
			if ( 'yes' === get_user_meta( $user_id, "dws_mapm_grant_access_{$locked_method_id}", true ) ) {
				unset( $locked_methods_ids[ $key ] );
			}
		}

		return $locked_methods_ids;
	}

	// endregion

	// region HOOKS

	/**
	 * Outputs HTML checkboxes to a user's back-end profile which if enabled activate blocked payment methods for the
	 * respective account.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   WP_User     $user       The user whose profile is currently being rendered.
	 */
	public function register_locked_payment_methods_fields( WP_User $user ) {
		if ( ! Users::has_capabilities( array( 'edit_user', Permissions::APPROVE_PAYMENT_METHODS_USER ) ) ) {
			return;
		}

		$locked_methods_ids = dws_wc_mapm_get_validated_option( 'general_locked-payment-methods' );
		if ( empty( $locked_methods_ids ) ) {
			return;
		}

		$gateways = WC()->payment_gateways()->payment_gateways(); ?>

		<h2>
			<?php esc_html_e( 'Manually Approved Payment Methods for WooCommerce', 'dws-mapm-for-woocommerce' ); ?>
		</h2>
		<table class="form-table" id="dws-manually-approveable-payment-methods">
			<tbody>
			<?php
			foreach ( $locked_methods_ids as $locked_method_id ) : // phpcs:ignore
				$field_id = "dws_mapm_grant_access_{$locked_method_id}";
				?>
				<tr>
					<th>
						<?php echo esc_html( $gateways[ $locked_method_id ]->title ); ?>
					</th>
					<td>
						<label for="<?php echo esc_attr( $field_id ); ?>">
							<input name="<?php echo esc_attr( $field_id ); ?>" type="checkbox" id="<?php echo esc_attr( $field_id ); ?>" value="1" <?php checked( get_user_meta( $user->ID, $field_id, true ), 'yes' ); ?>/>
							<?php
							echo wp_kses_post(
								sprintf(
									/* translators: Name of the payment gateway. */
									_x( 'Grant access to the <strong>%s</strong> payment method for this user?', 'user-meta-strategy', 'dws-mapm-for-woocommerce' ),
									$gateways[ $locked_method_id ]->title
								)
							);
							?>
						</label>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php
	}

	/**
	 * Saves the value of the checkboxes which unblock payment methods for a user to the user's meta.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   int     $user_id    The ID of the user whose profile was just saved.
	 */
	public function save_locked_payment_methods_fields( int $user_id ) {
		if ( ! Users::has_capabilities( array( 'edit_user', Permissions::APPROVE_PAYMENT_METHODS_USER ) ) ) {
			return;
		}

		$disabled_methods_ids = dws_wc_mapm_get_validated_option( 'general_locked-payment-methods' );
		foreach ( $disabled_methods_ids as $disabled_method_id ) {
			$key   = "dws_mapm_grant_access_{$disabled_method_id}";
			$value = Validation::validate_boolean_input( INPUT_POST, $key, false );

			( true === $value )
				? update_user_meta( $user_id, $key, 'yes' )
				: delete_user_meta( $user_id, $key );
		}
	}

	// endregion
}
