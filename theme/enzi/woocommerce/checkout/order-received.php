<?php
/**
 * "Order received" message.
 *
 * @package Diako
 * @version 8.8.0
 *
 * @var WC_Order|false $order
 */

defined( 'ABSPATH' ) || exit;

if ( $order instanceof WC_Order ) {
	$message = sprintf(
		/* translators: %s: order number */
		__( 'سفارش شما با شماره %s ثبت شد. جزئیات سفارش در ادامه آمده است.', 'diako' ),
		diako_number( $order->get_order_number() )
	);
} else {
	$message = __( 'سفارش شما با موفقیت ثبت شد.', 'diako' );
}

$message = apply_filters( 'woocommerce_thankyou_order_received_text', esc_html( $message ), $order );
?>
<div class="diako-checkout-thankyou__hero">
	<div class="diako-checkout-thankyou__icon" aria-hidden="true">
		<?php echo diako_lucide_icon_svg( 'check-circle', 'h-10 w-10' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received diako-checkout-thankyou__message">
		<?php echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</p>
	<?php if ( $order instanceof WC_Order && $order->get_billing_phone() ) : ?>
		<p class="diako-checkout-thankyou__hint">
			<?php esc_html_e( 'پیامک تأیید سفارش به شماره موبایل شما ارسال می‌شود.', 'diako' ); ?>
		</p>
	<?php endif; ?>
</div>
