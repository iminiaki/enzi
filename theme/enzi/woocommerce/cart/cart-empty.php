<?php
/**
 * Empty cart page
 *
 * @package Diako
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="diako-cart-empty">
	<div class="diako-cart-empty__icon" aria-hidden="true">
		<?php echo diako_lucide_icon_svg( 'shopping-bag', 'h-10 w-10' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>

	<div class="diako-cart-empty__content">
		<?php
		/**
		 * Empty cart message.
		 *
		 * @hooked wc_empty_cart_message - 10
		 */
		do_action( 'woocommerce_cart_is_empty' );
		?>
	</div>

	<?php if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
		<div class="diako-cart-empty__actions">
			<?php
			diako_button(
				array(
					'href'    => apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ),
					'label'   => apply_filters( 'woocommerce_return_to_shop_text', __( 'Return to shop', 'woocommerce' ) ),
					'variant' => 'default',
					'size'    => 'lg',
				)
			);
			?>
		</div>
	<?php endif; ?>
</div>
