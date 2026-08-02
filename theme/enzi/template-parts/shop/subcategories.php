<?php
/**
 * Subcategory carousel for product category archives.
 *
 * @package Diako
 */

defined( 'ABSPATH' ) || exit;

$parent_term = get_queried_object();

if ( ! $parent_term instanceof WP_Term ) {
	return;
}

$subcategories = diako_get_product_category_subcategories( $parent_term );

if ( empty( $subcategories ) ) {
	return;
}
?>

<section class="diako-shop-subcategories" aria-label="<?php esc_attr_e( 'زیردسته‌ها', 'diako' ); ?>">
	<div class="diako-shop-subcategories__box">
		<header class="diako-shop-subcategories__header">
			<span class="diako-shop-subcategories__header-icon" aria-hidden="true">
				<?php echo diako_lucide_icon_svg( 'layout-grid', 'h-5 w-5' ); // phpcs:ignore ?>
			</span>
			<div class="min-w-0 flex-1 space-y-1">
				<h2 class="diako-shop-subcategories__title"><?php esc_html_e( 'زیردسته‌ها', 'diako' ); ?></h2>
				<p class="diako-shop-subcategories__desc">
					<?php
					printf(
						/* translators: %s: parent category name */
						esc_html__( 'زیرمجموعه‌های «%s» را انتخاب کنید', 'diako' ),
						esc_html( $parent_term->name )
					);
					?>
				</p>
			</div>
		</header>

		<div
			class="diako-carousel diako-carousel--track diako-shop-subcategories__carousel"
			data-diako-carousel="track"
			aria-roledescription="carousel"
			tabindex="0"
		>
			<div class="diako-carousel__track-wrap">
				<div class="diako-carousel__viewport" data-carousel-viewport>
					<ul class="diako-shop-subcategories__track !list-none" data-carousel-track dir="rtl">
						<?php foreach ( $subcategories as $term ) : ?>
							<?php
							$thumbnail_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
							$count        = (int) $term->count;
							$excerpt      = ! empty( $term->description )
								? wp_trim_words( wp_strip_all_tags( $term->description ), 12, '…' )
								: '';
							?>
							<li class="diako-shop-subcategories__item">
								<a
									href="<?php echo esc_url( get_term_link( $term ) ); ?>"
									class="diako-shop-subcategories__card group"
								>
									<div class="diako-shop-subcategories__media<?php echo $thumbnail_id ? '' : ' diako-shop-subcategories__media--placeholder'; ?>">
										<?php if ( $thumbnail_id ) : ?>
											<?php
											echo wp_get_attachment_image(
												$thumbnail_id,
												'woocommerce_thumbnail',
												false,
												array(
													'class' => 'diako-shop-subcategories__image',
													'alt'   => '',
												)
											);
											?>
										<?php else : ?>
											<span class="diako-shop-subcategories__fallback-icon" aria-hidden="true">
												<?php diako_render_product_category_sidebar_icon( $term ); ?>
											</span>
										<?php endif; ?>
										<span class="diako-shop-subcategories__media-overlay" aria-hidden="true"></span>
									</div>

									<div class="diako-shop-subcategories__body">
										<h3 class="diako-shop-subcategories__name"><?php echo esc_html( $term->name ); ?></h3>
										<?php if ( $excerpt ) : ?>
											<p class="diako-shop-subcategories__excerpt"><?php echo esc_html( $excerpt ); ?></p>
										<?php endif; ?>
										<div class="diako-shop-subcategories__footer">
											<?php if ( $count > 0 ) : ?>
												<span class="diako-shop-subcategories__badge">
													<?php
													printf(
														/* translators: %d: number of products */
														esc_html( _n( '%d محصول', '%d محصول', $count, 'diako' ) ),
														(int) $count
													);
													?>
												</span>
											<?php endif; ?>
											<span class="diako-shop-subcategories__arrow" aria-hidden="true">
												<?php echo diako_lucide_icon_svg( 'chevron-left', 'h-4 w-4' ); // phpcs:ignore ?>
											</span>
										</div>
									</div>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php diako_render_carousel_arrows( array( 'placement' => 'track' ) ); ?>
			</div>
			<?php diako_render_carousel_dots(); ?>
		</div>
	</div>
</section>
