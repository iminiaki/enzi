<?php
/**
 * Thankyou page
 *
 * @package Diako
 * @version 8.1.0
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="woocommerce-order diako-checkout-thankyou">

	<?php if ( $order ) : ?>

		<?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<div class="diako-checkout-thankyou__status diako-checkout-thankyou__status--failed">
				<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed">
					<?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?>
				</p>

				<div class="diako-checkout-thankyou__actions">
					<?php
					diako_button(
						array(
							'href'    => $order->get_checkout_payment_url(),
							'label'   => __( 'Pay', 'woocommerce' ),
							'variant' => 'default',
							'size'    => 'default',
						)
					);
					if ( is_user_logged_in() ) {
						diako_button(
							array(
								'href'    => wc_get_page_permalink( 'myaccount' ),
								'label'   => __( 'My account', 'woocommerce' ),
								'variant' => 'outline',
								'size'    => 'default',
							)
						);
					}
					?>
				</div>
			</div>

		<?php else : ?>

			<?php wc_get_template( 'checkout/order-received.php', array( 'order' => $order ) ); ?>

			<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details diako-checkout-thankyou__details">
				<li class="woocommerce-order-overview__order order">
					<span class="diako-checkout-thankyou__detail-label"><?php esc_html_e( 'Order number:', 'woocommerce' ); ?></span>
					<strong><?php echo esc_html( diako_number( $order->get_order_number() ) ); ?></strong>
				</li>

				<li class="woocommerce-order-overview__date date">
					<span class="diako-checkout-thankyou__detail-label"><?php esc_html_e( 'Date:', 'woocommerce' ); ?></span>
					<strong><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></strong>
				</li>

				<li class="woocommerce-order-overview__status status">
					<span class="diako-checkout-thankyou__detail-label"><?php esc_html_e( 'Status', 'woocommerce' ); ?></span>
					<strong class="diako-checkout-thankyou__status-badge diako-checkout-thankyou__status-badge--<?php echo esc_attr( $order->get_status() ); ?>">
						<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
					</strong>
				</li>

				<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
					<li class="woocommerce-order-overview__email email">
						<span class="diako-checkout-thankyou__detail-label"><?php esc_html_e( 'Email:', 'woocommerce' ); ?></span>
						<strong><?php echo esc_html( $order->get_billing_email() ); ?></strong>
					</li>
				<?php endif; ?>

				<li class="woocommerce-order-overview__total total">
					<span class="diako-checkout-thankyou__detail-label"><?php esc_html_e( 'Total:', 'woocommerce' ); ?></span>
					<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
				</li>

				<?php if ( $order->get_payment_method_title() ) : ?>
					<li class="woocommerce-order-overview__payment-method method">
						<span class="diako-checkout-thankyou__detail-label"><?php esc_html_e( 'Payment method:', 'woocommerce' ); ?></span>
						<strong><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
					</li>
				<?php endif; ?>
			</ul>

			<div class="diako-checkout-thankyou__sections">
				<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
				<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
			</div>

			<div class="diako-checkout-thankyou__actions">
				<?php
				if ( is_user_logged_in() ) {
					diako_button(
						array(
							'href'    => wc_get_endpoint_url( 'view-order', $order->get_id(), wc_get_page_permalink( 'myaccount' ) ),
							'label'   => __( 'مشاهده سفارش', 'diako' ),
							'variant' => 'default',
							'size'    => 'default',
							'icon'    => 'package',
						)
					);
				}

				diako_button(
					array(
						'href'    => wc_get_page_permalink( 'shop' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
						'label'   => __( 'ادامه خرید', 'diako' ),
						'variant' => 'outline',
						'size'    => 'default',
						'icon'    => 'shopping-bag',
					)
				);
				?>
			</div>

		<?php endif; ?>

	<?php else : ?>

		<?php wc_get_template( 'checkout/order-received.php', array( 'order' => false ) ); ?>

	<?php endif; ?>

</div>
