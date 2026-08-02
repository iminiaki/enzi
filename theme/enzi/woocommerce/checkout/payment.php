<?php
/**
 * Checkout Payment Section
 *
 * @package Diako
 * @version 9.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>
<div id="payment" class="woocommerce-checkout-payment diako-checkout-payment">
	<?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
		<h4 class="diako-checkout-payment__title"><?php esc_html_e( 'Payment method', 'woocommerce' ); ?></h4>
		<ul class="wc_payment_methods payment_methods methods diako-checkout-payment__methods">
			<?php
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
				}
			} else {
				echo '<li class="diako-checkout-payment__empty">';
				wc_print_notice(
					apply_filters(
						'woocommerce_no_available_payment_methods_message',
						WC()->customer->get_billing_country()
							? esc_html__( 'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' )
							: esc_html__( 'Please fill in your details above to see available payment methods.', 'woocommerce' )
					),
					'notice'
				);
				echo '</li>';
			}
			?>
		</ul>
	<?php endif; ?>

	<div class="form-row place-order diako-checkout-place-order">
		<noscript>
			<?php
			printf(
				esc_html__( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'woocommerce' ),
				'<em>',
				'</em>'
			);
			?>
			<br/><button type="submit" class="button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'woocommerce' ); ?>"><?php esc_html_e( 'Update totals', 'woocommerce' ); ?></button>
		</noscript>

		<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

		<?php
		echo apply_filters(
			'woocommerce_order_button_html',
			'<button type="submit" class="' . esc_attr( diako_button_classes( 'default', 'lg', 'alt diako-checkout-place-order__button' ) ) . '" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' .
			diako_lucide_icon_svg( 'arrow-left', 'h-4 w-4' ) .
			'<span>' . esc_html( $order_button_text ) . '</span></button>'
		);
		?>

		<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</div>
</div>
<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
