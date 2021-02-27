<?php
/**
 * A very early error message displayed if something doesn't check out.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WC-Plugins\ManuallyApprovedPaymentMethods\templates\admin
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="error notice dws-plugin-corrupted-error">
	<p>
		<?php
		echo wp_kses(
			sprintf(
				/* translators: %s: Manually Approved Payment Methods for WooCommerce Plugin Name */
				__( 'It seems like <strong>%s</strong> is corrupted. Please reinstall!', 'dws-manually-approved-payment-methods-for-woocommerce' ),
				DWS_WC_MAPM_PLUGIN_NAME
			),
			array(
				'strong' => array(),
			)
		);
		?>
	</p>
</div>
