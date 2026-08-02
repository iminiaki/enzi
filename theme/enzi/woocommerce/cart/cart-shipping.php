<?php
/**
 * Shipping Methods Display
 *
 * @package Diako
 * @version 8.8.0
 */

defined( 'ABSPATH' ) || exit;

$formatted_destination    = isset( $formatted_destination ) ? $formatted_destination : WC()->countries->get_formatted_address( $package['destination'], ', ' );
$has_calculated_shipping  = ! empty( $has_calculated_shipping );
$show_shipping_calculator = ! empty( $show_shipping_calculator );
$calculator_text          = '';
$shipping_address         = diako_format_shipping_address( $package['destination'] ?? array() );
?>
<tr class="woocommerce-shipping-totals shipping">
	<td colspan="2" class="diako-shipping-totals-cell">
		<div class="diako-shipping-totals">
			<div class="diako-shipping-totals__header">
				<?php echo esc_html( wp_strip_all_tags( (string) $package_name ) ); ?>
			</div>

			<div class="diako-shipping-totals__body">
				<?php if ( ! empty( $available_methods ) && is_array( $available_methods ) ) : ?>
					<ul id="shipping_method" class="woocommerce-shipping-methods diako-shipping-methods">
						<?php foreach ( $available_methods as $method ) : ?>
							<?php
							$input_id = sprintf( 'shipping_method_%1$d_%2$s', $index, esc_attr( sanitize_title( $method->id ) ) );
							?>
							<li class="diako-shipping-method">
								<?php if ( 1 < count( $available_methods ) ) : ?>
									<?php
									printf(
										'<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="%2$s" value="%3$s" class="shipping_method" %4$s />',
										(int) $index,
										esc_attr( $input_id ),
										esc_attr( $method->id ),
										checked( $method->id, $chosen_method, false )
									);
									printf(
										'<label for="%1$s" class="diako-shipping-method__label">%2$s</label>',
										esc_attr( $input_id ),
										diako_get_shipping_method_label_markup( $method ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									);
									?>
								<?php else : ?>
									<?php
									printf(
										'<input type="hidden" name="shipping_method[%1$d]" data-index="%1$d" id="%2$s" value="%3$s" class="shipping_method" />',
										(int) $index,
										esc_attr( $input_id ),
										esc_attr( $method->id )
									);
									?>
									<div class="diako-shipping-method__label diako-shipping-method__label--single">
										<?php echo diako_get_shipping_method_label_markup( $method ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</div>
								<?php endif; ?>
								<?php do_action( 'woocommerce_after_shipping_rate', $method, $index ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php elseif ( ! $has_calculated_shipping || ! $formatted_destination ) : ?>
					<p class="diako-shipping-totals__notice">
						<?php
						if ( is_cart() && 'no' === get_option( 'woocommerce_enable_shipping_calc' ) ) {
							echo wp_kses_post( apply_filters( 'woocommerce_shipping_not_enabled_on_cart_html', __( 'Shipping costs are calculated during checkout.', 'woocommerce' ) ) );
						} else {
							echo wp_kses_post( apply_filters( 'woocommerce_shipping_may_be_available_html', __( 'Enter your address to view shipping options.', 'woocommerce' ) ) );
						}
						?>
					</p>
				<?php elseif ( ! is_cart() ) : ?>
					<p class="diako-shipping-totals__notice">
						<?php echo wp_kses_post( apply_filters( 'woocommerce_no_shipping_available_html', __( 'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'woocommerce' ) ) ); ?>
					</p>
				<?php else : ?>
					<p class="diako-shipping-totals__notice">
						<?php
						echo wp_kses_post(
							apply_filters(
								'woocommerce_cart_no_shipping_available_html',
								sprintf( esc_html__( 'No shipping options were found for %s.', 'woocommerce' ) . ' ', '<strong>' . esc_html( $formatted_destination ) . '</strong>' ),
								$formatted_destination
							)
						);
						$calculator_text = esc_html__( 'Enter a different address', 'woocommerce' );
						?>
					</p>
				<?php endif; ?>

				<?php if ( $shipping_address ) : ?>
					<div class="diako-shipping-totals__address">
						<span class="diako-shipping-totals__address-icon" aria-hidden="true">
							<?php echo diako_lucide_icon_svg( 'map-pin', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<div class="diako-shipping-totals__address-content">
							<span class="diako-shipping-totals__address-label"><?php esc_html_e( 'آدرس ارسال', 'diako' ); ?></span>
							<p class="diako-shipping-totals__address-text"><?php echo esc_html( $shipping_address ); ?></p>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $show_package_details ) : ?>
				<p class="woocommerce-shipping-contents diako-shipping-totals__details"><small><?php echo esc_html( $package_details ); ?></small></p>
			<?php endif; ?>

			<?php if ( $show_shipping_calculator ) : ?>
				<?php woocommerce_shipping_calculator( $calculator_text ); ?>
			<?php endif; ?>
		</div>
	</td>
</tr>
