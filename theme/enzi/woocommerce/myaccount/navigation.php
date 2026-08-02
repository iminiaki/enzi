<?php
/**
 * My Account navigation.
 *
 * @package Diako
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_navigation' );
?>

<nav class="woocommerce-MyAccount-navigation diako-account-nav" aria-label="<?php esc_attr_e( 'منوی حساب کاربری', 'diako' ); ?>">
	<ul class="diako-account-nav__list">
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<?php
			$is_active = wc_is_current_account_menu_item( $endpoint );
			$is_logout = 'customer-logout' === $endpoint;
			$item_class = wc_get_account_menu_item_classes( $endpoint );
			?>
			<li class="<?php echo esc_attr( $item_class ); ?><?php echo $is_logout ? ' diako-account-nav__item--logout' : ''; ?>">
				<a
					class="diako-account-nav__link<?php echo $is_active ? ' diako-account-nav__link--active' : ''; ?>"
					href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"
					<?php echo $is_active ? 'aria-current="page"' : ''; ?>
				>
					<span class="diako-account-nav__icon" aria-hidden="true">
						<?php echo diako_lucide_icon_svg( diako_get_account_endpoint_icon( $endpoint ), 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<span class="diako-account-nav__label"><?php echo esc_html( $label ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php
do_action( 'woocommerce_after_account_navigation' );
