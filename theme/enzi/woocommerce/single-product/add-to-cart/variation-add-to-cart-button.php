<?php
/**
 * Single variation cart button.
 *
 * @package Diako
 * @version 10.5.2
 */

defined( 'ABSPATH' ) || exit;

global $product;

$starts_disabled = diako_variable_add_to_cart_starts_disabled( $product );
$button_classes  = diako_single_add_to_cart_button_classes();

if ( $starts_disabled ) {
	$button_classes .= ' disabled wc-variation-selection-needed';
}
?>
<div class="woocommerce-variation-add-to-cart variations_button diako-add-to-cart-form__actions">
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

	<button type="submit" class="<?php echo esc_attr( $button_classes ); ?>"<?php disabled( $starts_disabled, true ); ?>>
		<?php echo diako_lucide_icon_svg( 'shopping-bag', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<span><?php echo esc_html( $product->single_add_to_cart_text() ); ?></span>
	</button>

	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />
</div>
