<?php
/**
 * No products found.
 *
 * @package Diako
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-no-products-found">
	<div class="<?php echo esc_attr( diako_card_classes( 'p-8 text-center' ) ); ?>">
		<?php echo diako_lucide_icon_svg( 'package-x', 'mx-auto mb-4 h-12 w-12 text-muted-foreground' ); // phpcs:ignore ?>
		<p class="text-muted-foreground"><?php esc_html_e( 'محصولی مطابق انتخاب شما یافت نشد.', 'diako' ); ?></p>
		<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
			<div class="mt-4">
				<?php
				diako_button(
					array(
						'href'    => wc_get_page_permalink( 'shop' ),
						'label'   => __( 'مشاهده همه محصولات', 'diako' ),
						'variant' => 'default',
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</div>
