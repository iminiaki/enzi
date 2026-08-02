<?php
/**
 * Simple product add to cart.
 *
 * @package Diako
 * @version 10.2.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

echo wc_get_stock_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

$can_purchase = $product->is_in_stock() && $product->is_purchasable();
$price_html   = $product->get_price_html();

if ( ! $can_purchase && ! $price_html ) {
	if ( ! $product->is_in_stock() ) {
		diako_render_stock_notify_form( $product->get_id(), 0, 'single' );
	}

	return;
}

do_action( 'woocommerce_before_add_to_cart_form' );

if ( $can_purchase ) :
	?>
	<form class="cart diako-add-to-cart-form" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype="multipart/form-data">
		<?php diako_render_single_product_cart_price( $product ); ?>

		<div class="diako-add-to-cart-form__actions">
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<?php
		do_action( 'woocommerce_before_add_to_cart_quantity' );

		woocommerce_quantity_input(
			array(
				'min_value'   => $product->get_min_purchase_quantity(),
				'max_value'   => $product->get_max_purchase_quantity(),
				'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			)
		);

		do_action( 'woocommerce_after_add_to_cart_quantity' );
		?>

		<button
			type="submit"
			name="add-to-cart"
			value="<?php echo esc_attr( $product->get_id() ); ?>"
			class="<?php echo esc_attr( diako_single_add_to_cart_button_classes() ); ?>"
		>
			<?php echo diako_lucide_icon_svg( 'shopping-bag', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span><?php echo esc_html( $product->single_add_to_cart_text() ); ?></span>
		</button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
		</div>
	</form>
	<?php
else :
	?>
	<div class="diako-add-to-cart-form diako-add-to-cart-form--price-only">
		<?php if ( ! $product->is_in_stock() ) : ?>
			<?php diako_render_stock_notify_form( $product->get_id(), 0, 'single' ); ?>
		<?php endif; ?>
	</div>
	<?php
endif;

do_action( 'woocommerce_after_add_to_cart_form' );
