<?php
/**
 * Checkout Form
 *
 * @package Diako
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

$has_checkout_fields = (bool) $checkout->get_checkout_fields();
?>

<div class="diako-checkout-layout">
<?php if ( $has_checkout_fields ) : ?>

	<div class="diako-checkout__sidebar-coupon">
		<?php diako_render_checkout_coupon_form(); ?>
	</div>

<form name="checkout" method="post" class="checkout woocommerce-checkout diako-checkout-form" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<div class="diako-checkout__main">
		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="diako-checkout-section diako-checkout-customer" id="customer_details">
			<div class="col2-set diako-checkout-customer__grid">
				<div class="col-1 diako-checkout-customer__billing">
					<?php do_action( 'woocommerce_checkout_billing' ); ?>
				</div>

				<div class="col-2 diako-checkout-customer__extras">
					<?php do_action( 'woocommerce_checkout_shipping' ); ?>
				</div>
			</div>
		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
	</div>

	<aside class="diako-checkout__sidebar">
		<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

		<div id="order_review" class="woocommerce-checkout-review-order diako-checkout-review">
			<h3 id="order_review_heading" class="diako-checkout-review__title"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>

			<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
			<?php do_action( 'woocommerce_checkout_order_review' ); ?>
			<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
		</div>
	</aside>

</form>

<?php endif; ?>
</div>

<?php
do_action( 'woocommerce_after_checkout_form', $checkout );
