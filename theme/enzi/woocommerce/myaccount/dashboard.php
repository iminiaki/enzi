<?php
/**
 * My Account dashboard.
 *
 * @package Diako
 * @version 4.4.0
 */

defined( 'ABSPATH' ) || exit;

$allowed_html = array(
	'a'      => array(
		'href' => array(),
	),
	'strong' => array(),
);

$current_user = wp_get_current_user();
?>

<div class="diako-account-dashboard">
	<div class="diako-account-welcome">
		<p class="diako-account-welcome__greeting">
			<?php
			printf(
				/* translators: %s: user display name */
				wp_kses( __( 'سلام <strong>%1$s</strong>', 'diako' ), $allowed_html ),
				esc_html( $current_user->display_name )
			);
			?>
		</p>
		<p class="diako-account-welcome__desc">
			<?php
			if ( wc_shipping_enabled() ) {
				$dashboard_desc = __( 'از پیشخوان می‌توانید <a href="%1$s">سفارش‌های اخیر</a> را ببینید، <a href="%2$s">آدرس ارسال و صورتحساب</a> را مدیریت کنید و <a href="%3$s">رمز عبور و جزئیات حساب</a> را ویرایش کنید.', 'diako' );
			} else {
				$dashboard_desc = __( 'از پیشخوان می‌توانید <a href="%1$s">سفارش‌های اخیر</a> را ببینید، <a href="%2$s">آدرس صورتحساب</a> را مدیریت کنید و <a href="%3$s">رمز عبور و جزئیات حساب</a> را ویرایش کنید.', 'diako' );
			}

			printf(
				wp_kses( $dashboard_desc, $allowed_html ),
				esc_url( wc_get_endpoint_url( 'orders' ) ),
				esc_url( wc_get_endpoint_url( 'edit-address' ) ),
				esc_url( wc_get_endpoint_url( 'edit-account' ) )
			);
			?>
		</p>
	</div>

	<?php diako_render_account_dashboard_quick_links(); ?>
</div>

<?php
/**
 * My Account dashboard.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );

/**
 * Deprecated woocommerce_before_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_before_my_account' );

/**
 * Deprecated woocommerce_after_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_after_my_account' );
