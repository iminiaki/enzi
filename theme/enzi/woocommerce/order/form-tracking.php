<?php
/**
 * Order tracking form
 *
 * @package Diako
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

global $post;

$order_id    = isset( $_REQUEST['orderid'] ) ? wc_clean( wp_unslash( $_REQUEST['orderid'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$order_email = isset( $_REQUEST['order_email'] ) ? sanitize_email( wp_unslash( $_REQUEST['order_email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<form action="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" method="post" class="woocommerce-form woocommerce-form-track-order track_order diako-track-order-form">
	<?php do_action( 'woocommerce_order_tracking_form_start' ); ?>

	<p class="diako-track-order-form__intro">
		<?php esc_html_e( 'برای پیگیری سفارش، شماره سفارش و ایمیل صورتحساب را وارد کنید. این اطلاعات در ایمیل تأیید سفارش برای شما ارسال شده است.', 'diako' ); ?>
	</p>

	<div class="diako-track-order-form__fields">
		<p class="form-row form-row-wide">
			<label for="orderid"><?php esc_html_e( 'شماره سفارش', 'diako' ); ?> <span class="required" aria-hidden="true">*</span></label>
			<input
				class="<?php echo esc_attr( diako_input_classes( 'input-text' ) ); ?>"
				type="text"
				name="orderid"
				id="orderid"
				value="<?php echo esc_attr( ltrim( $order_id, '#' ) ); ?>"
				placeholder="<?php esc_attr_e( 'مثال: ۱۲۳۴', 'diako' ); ?>"
				required
				autocomplete="off"
				inputmode="numeric"
			/>
		</p>

		<p class="form-row form-row-wide">
			<label for="order_email"><?php esc_html_e( 'ایمیل صورتحساب', 'diako' ); ?> <span class="required" aria-hidden="true">*</span></label>
			<input
				class="<?php echo esc_attr( diako_input_classes( 'input-text' ) ); ?>"
				type="email"
				name="order_email"
				id="order_email"
				value="<?php echo esc_attr( $order_email ); ?>"
				placeholder="<?php esc_attr_e( 'ایمیلی که هنگام خرید وارد کردید', 'diako' ); ?>"
				required
				autocomplete="email"
			/>
		</p>
	</div>

	<?php do_action( 'woocommerce_order_tracking_form' ); ?>

	<p class="form-row diako-track-order-form__submit">
		<?php
		diako_button(
			array(
				'label'       => __( 'پیگیری سفارش', 'diako' ),
				'variant'     => 'default',
				'size'        => 'lg',
				'type'        => 'button',
				'button_type' => 'submit',
				'attrs'       => array(
					'name'  => 'track',
					'value' => 'Track',
				),
				'icon'        => 'search',
				'icon_class'  => 'h-4 w-4',
			)
		);
		?>
	</p>

	<?php wp_nonce_field( 'woocommerce-order_tracking', 'woocommerce-order-tracking-nonce' ); ?>

	<?php do_action( 'woocommerce_order_tracking_form_end' ); ?>
</form>
