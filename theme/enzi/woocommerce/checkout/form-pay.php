<?php
/**
 * Pay for order form
 *
 * @package Diako
 * @version 8.2.0
 *
 * @var WC_Order $order
 * @var array    $available_gateways
 * @var string   $order_button_text
 */

defined( 'ABSPATH' ) || exit;

$totals = $order->get_order_item_totals(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
?>
<form id="order_review" class="diako-order-pay__form" method="post">
	<?php diako_render_order_pay_summary( $order ); ?>

	<div class="diako-order-pay__layout">
		<section class="diako-order-pay__review diako-order-details">
			<h2 class="diako-order-details__title"><?php esc_html_e( 'Order details', 'woocommerce' ); ?></h2>

			<div class="diako-order-details__table-wrap">
				<table class="shop_table diako-order-details__table">
					<thead>
						<tr>
							<th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
							<th class="product-quantity"><?php esc_html_e( 'Qty', 'woocommerce' ); ?></th>
							<th class="product-total"><?php esc_html_e( 'Totals', 'woocommerce' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( count( $order->get_items() ) > 0 ) : ?>
							<?php foreach ( $order->get_items() as $item_id => $item ) : ?>
								<?php
								if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
									continue;
								}
								?>
								<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ); ?>">
									<td class="product-name">
										<?php
										echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) );

										do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

										wc_display_item_meta( $item );

										do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
										?>
									</td>
									<td class="product-quantity"><?php echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', esc_html( $item->get_quantity() ) ) . '</strong>', $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<td class="product-subtotal"><?php echo $order->get_formatted_line_subtotal( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
					<?php if ( $totals ) : ?>
						<tfoot>
							<?php foreach ( $totals as $key => $total ) : ?>
								<tr class="<?php echo esc_attr( 'order_total' === $key ? 'order-total-row' : sanitize_html_class( $key ) ); ?>">
									<th scope="row" colspan="2"><?php echo wp_kses_post( $total['label'] ); ?></th>
									<td class="product-total"><?php echo wp_kses_post( $total['value'] ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tfoot>
					<?php endif; ?>
				</table>
			</div>
		</section>

		<aside class="diako-order-pay__sidebar">
			<?php do_action( 'woocommerce_pay_order_before_payment' ); ?>

			<div id="payment" class="woocommerce-checkout-payment diako-checkout-payment diako-order-pay__payment">
				<?php if ( $order->needs_payment() ) : ?>
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
									esc_html__( 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' )
								),
								'notice'
							);
							echo '</li>';
						}
						?>
					</ul>
				<?php endif; ?>

				<div class="form-row place-order diako-checkout-place-order">
					<input type="hidden" name="woocommerce_pay" value="1" />

					<?php wc_get_template( 'checkout/terms.php' ); ?>

					<?php do_action( 'woocommerce_pay_order_before_submit' ); ?>

					<?php
					echo apply_filters(
						'woocommerce_pay_order_button_html',
						'<button type="submit" class="' . esc_attr( diako_button_classes( 'default', 'lg', 'alt diako-checkout-place-order__button' ) ) . '" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' .
						diako_lucide_icon_svg( 'arrow-left', 'h-4 w-4' ) .
						'<span>' . esc_html( $order_button_text ) . '</span></button>'
					); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>

					<?php do_action( 'woocommerce_pay_order_after_submit' ); ?>

					<?php wp_nonce_field( 'woocommerce-pay', 'woocommerce-pay-nonce' ); ?>
				</div>
			</div>
		</aside>
	</div>
</form>
