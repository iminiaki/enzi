<?php
/**
 * Checkout coupon form
 *
 * @package Diako
 * @version 9.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wc_coupons_enabled() ) {
	return;
}
?>
<div class="diako-checkout-coupon">
	<form class="checkout_coupon woocommerce-form-coupon diako-checkout-coupon__form" method="post" id="woocommerce-checkout-form-coupon">
		<label for="coupon_code" class="diako-checkout-coupon__label"><?php esc_html_e( 'Have a coupon?', 'woocommerce' ); ?></label>
		<div class="diako-checkout-coupon__row">
			<div class="diako-checkout-coupon__input-wrap">
				<input type="text" name="coupon_code" class="input-text diako-checkout-coupon__input" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" id="coupon_code" value="" autocomplete="off" />
			</div>
			<button type="submit" class="button diako-checkout-coupon__button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>">
				<?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?>
			</button>
		</div>
	</form>
</div>
