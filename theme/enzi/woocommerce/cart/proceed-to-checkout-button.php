<?php
/**
 * Proceed to checkout button
 *
 * @package Diako
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

?>
<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="checkout-button button alt wc-forward diako-cart-checkout__button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>">
	<?php echo diako_lucide_icon_svg( 'arrow-left', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php esc_html_e( 'Proceed to checkout', 'woocommerce' ); ?>
</a>
