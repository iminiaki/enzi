<?php
/**
 * WooCommerce fallback wrapper (cart, checkout, account).
 *
 * When present, woocommerce.php replaces the normal taxonomy template hierarchy.
 * Shop and taxonomy archives must be routed to our custom archive template.
 *
 * @package Diako
 */

defined( 'ABSPATH' ) || exit;

if (
	( function_exists( 'is_shop' ) && is_shop() )
	|| ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() )
	|| ( function_exists( 'diako_is_product_search' ) && diako_is_product_search() )
) {
	require get_template_directory() . '/woocommerce/archive-product.php';
	return;
}

get_header();

$is_cart     = function_exists( 'is_cart' ) && is_cart();
$is_checkout = function_exists( 'diako_is_checkout_page' ) && diako_is_checkout_page();
?>
<div class="diako-page woocommerce-page-wrap<?php echo $is_cart ? ' diako-cart-page' : ''; ?><?php echo $is_checkout ? ' diako-checkout-page' : ''; ?>">
	<?php if ( $is_cart ) : ?>
		<div class="diako-cart">
			<?php diako_render_cart_page_header(); ?>
	<?php elseif ( $is_checkout ) : ?>
		<div class="diako-checkout">
			<?php
			if ( diako_is_checkout_thankyou_page() ) {
				diako_render_checkout_thankyou_header();
			} else {
				diako_render_checkout_page_header();
			}
			?>
	<?php endif; ?>

	<?php woocommerce_content(); ?>

	<?php if ( $is_cart || $is_checkout ) : ?>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
