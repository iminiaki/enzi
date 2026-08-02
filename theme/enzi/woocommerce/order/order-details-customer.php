<?php
/**
 * Order Customer Details
 *
 * @package Diako
 * @version 8.7.0
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address();
?>
<section class="woocommerce-customer-details diako-order-customer-details">
	<div class="diako-order-customer-details__grid<?php echo $show_shipping ? ' diako-order-customer-details__grid--split' : ''; ?>">
		<article class="diako-order-address-card">
			<h2 class="woocommerce-column__title diako-order-address-card__title">
				<?php echo diako_lucide_icon_svg( 'credit-card', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php esc_html_e( 'Billing address', 'woocommerce' ); ?></span>
			</h2>

			<div class="diako-order-address-card__body">
				<?php diako_render_order_address_card( $order, 'billing' ); ?>
			</div>

			<?php do_action( 'woocommerce_order_details_after_customer_address', 'billing', $order ); ?>
		</article>

		<?php if ( $show_shipping ) : ?>
			<article class="diako-order-address-card">
				<h2 class="woocommerce-column__title diako-order-address-card__title">
					<?php echo diako_lucide_icon_svg( 'package', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php esc_html_e( 'Shipping address', 'woocommerce' ); ?></span>
				</h2>

				<div class="diako-order-address-card__body">
					<?php diako_render_order_address_card( $order, 'shipping' ); ?>
				</div>

				<?php do_action( 'woocommerce_order_details_after_customer_address', 'shipping', $order ); ?>
			</article>
		<?php endif; ?>
	</div>

	<?php do_action( 'woocommerce_order_details_after_customer_details', $order ); ?>
</section>
