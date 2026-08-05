<?php
/**
 * Product card in loops.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Diako
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product || ! $product->is_visible() ) {
	return;
}

$permalink       = $product->get_permalink();
$gallery_ids     = $product->get_gallery_image_ids();
$hover_image_id  = ! empty( $gallery_ids ) ? (int) $gallery_ids[0] : 0;
$is_out_of_stock = diako_product_card_is_out_of_stock( $product );
$card_classes    = diako_card_classes(
	diako_cn(
		'diako-product-card group flex h-full flex-col overflow-hidden rounded-3xl p-3 shadow-sm',
		$is_out_of_stock ? 'diako-product-card--out-of-stock' : ''
	)
);
?>
<li <?php wc_product_class( '', $product ); ?>>
	<article class="<?php echo esc_attr( $card_classes ); ?>">
		<div class="diako-product-card__media<?php echo $is_out_of_stock ? ' diako-product-card__media--out-of-stock' : ''; ?>">
			<a href="<?php echo esc_url( $permalink ); ?>" class="diako-product-card__media-link" tabindex="-1" aria-hidden="true">
				<?php
				echo $product->get_image(
					'diako-product-card',
					array(
						'class' => 'diako-product-card__image diako-product-card__image--primary',
					)
				); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				if ( $hover_image_id ) {
					echo wp_get_attachment_image(
						$hover_image_id,
						'diako-product-card',
						false,
						array(
							'class'   => 'diako-product-card__image diako-product-card__image--secondary',
							'loading' => 'lazy',
							'alt'     => '',
						)
					);
				}
				?>
			</a>

			<?php diako_render_favorite_button( $product, 'card' ); ?>
			<?php diako_render_compare_button( $product, 'card' ); ?>
			<?php diako_render_product_card_badges( $product ); ?>
			<?php diako_render_product_card_tags( $product, 2 ); ?>
		</div>

		<div class="diako-product-card__body">
			<?php diako_render_product_card_meta( $product ); ?>

			<h3 class="diako-product-card__title">
				<a href="<?php echo esc_url( $permalink ); ?>">
					<?php echo esc_html( $product->get_name() ); ?>
				</a>
			</h3>

			<?php diako_render_product_card_rating( $product ); ?>

			<?php diako_render_product_card_color_swatches( $product ); ?>

			<div class="diako-product-card__footer">
				<?php if ( ! $is_out_of_stock ) : ?>
					<div class="diako-product-card__price-block">
						<div class="diako-product-card__price">
							<?php echo diako_get_product_card_price_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>
				<?php endif; ?>

				<?php
				if ( $is_out_of_stock ) {
					diako_render_stock_notify_card_trigger( $product );
				} else {
					$cta_label     = diako_get_product_card_cta_label( $product );
					$product_type  = $product->get_type();
					$supports_ajax = in_array( $product_type, array( 'simple', 'variable' ), true ) && $product->is_purchasable();

					if ( $supports_ajax ) {
						diako_button(
							array(
								'type'        => 'button',
								'button_type' => 'button',
								'label'       => $cta_label,
								'variant'     => 'default',
								'size'        => 'icon',
								'icon'        => 'shopping-bag',
								'icon_class'  => 'h-5 w-5',
								'class'       => 'diako-product-card__cta',
								'attrs'       => array(
									'aria-label'          => $cta_label,
									'data-diako-card-atc' => '',
									'data-product-id'     => (string) $product->get_id(),
									'data-product-type'   => $product_type,
									'data-product-name'   => $product->get_name(),
									'data-product-url'    => $permalink,
								),
							)
						);
					} else {
						diako_button(
							array(
								'href'       => $permalink,
								'label'      => $cta_label,
								'variant'    => 'default',
								'size'       => 'icon',
								'icon'       => 'shopping-bag',
								'icon_class' => 'h-5 w-5',
								'class'      => 'diako-product-card__cta',
								'attrs'      => array( 'aria-label' => $cta_label ),
							)
						);
					}
				}
				?>
			</div>
		</div>

		<?php diako_render_product_sale_countdown( $product, 'card' ); ?>
	</article>
</li>
