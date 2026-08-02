<?php
/**
 * Related products.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Diako
 * @version 10.3.0
 */

defined( 'ABSPATH' ) || exit;

if ( $related_products ) :
	/**
	 * Ensure related product images participate in lazy-loading threshold.
	 */
	if ( function_exists( 'wp_increase_content_media_count' ) ) {
		$content_media_count = wp_increase_content_media_count( 0 );

		if ( $content_media_count < wp_omit_loading_attr_threshold() ) {
			wp_increase_content_media_count( wp_omit_loading_attr_threshold() - $content_media_count );
		}
	}
	$heading          = apply_filters( 'woocommerce_product_related_products_heading', __( 'Related products', 'woocommerce' ) );
	$original_post    = $GLOBALS['post'] ?? null;
	$original_product = $GLOBALS['product'] ?? null;
	?>
	<section class="related products diako-related-products">
		<?php if ( $heading ) : ?>
			<h4 class="diako-related-products__title"><?php echo esc_html( $heading ); ?></h4>
		<?php endif; ?>

		<div
			class="diako-carousel diako-carousel--track diako-related-products__carousel"
			data-diako-carousel="track"
			aria-roledescription="carousel"
			tabindex="0"
		>
			<div class="diako-carousel__track-wrap">
				<div class="diako-carousel__viewport" data-carousel-viewport>
					<ul class="products !list-none" data-carousel-track dir="rtl">
						<?php foreach ( $related_products as $related_product ) : ?>
							<?php
							$post_object = get_post( $related_product->get_id() );

							$GLOBALS['post']    = $post_object; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							$GLOBALS['product'] = $related_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							setup_postdata( $GLOBALS['post'] );

							wc_get_template_part( 'content', 'product' );
							?>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php diako_render_carousel_arrows( array( 'placement' => 'track' ) ); ?>
			</div>
			<?php diako_render_carousel_dots(); ?>
		</div>
	</section>
	<?php
endif;

wp_reset_postdata();

if ( isset( $original_post ) ) {
	$GLOBALS['post'] = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
}

if ( isset( $original_product ) ) {
	$GLOBALS['product'] = $original_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
}
