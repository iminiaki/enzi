<?php
/**
 * Product archive (shop, categories, tags, search).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Diako
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="diako-page woocommerce-page-wrap">
	<?php diako_render_shop_archive_header(); ?>
	<?php diako_render_product_category_subcategories(); ?>

	<?php if ( diako_shop_has_sidebar() ) : ?>
		<div class="diako-shop-drawer-backdrop" data-shop-drawer-backdrop hidden aria-hidden="true"></div>
	<?php endif; ?>

	<div class="diako-shop-shell">
		<?php diako_render_shop_sidebar(); ?>

		<div class="diako-shop-main" data-shop-main>
			<?php do_action( 'woocommerce_before_main_content' ); ?>

			<?php if ( woocommerce_product_loop() ) : ?>
				<?php woocommerce_output_all_notices(); ?>

				<div class="diako-shop-toolbar">
					<div class="flex flex-wrap items-center gap-3">
						<?php diako_render_shop_sidebar_toggle(); ?>
						<?php woocommerce_result_count(); ?>
					</div>
					<?php woocommerce_catalog_ordering(); ?>
				</div>

				<?php woocommerce_product_loop_start(); ?>

				<?php if ( wc_get_loop_prop( 'total' ) ) : ?>
					<?php while ( have_posts() ) : ?>
						<?php the_post(); ?>
						<?php wc_get_template_part( 'content', 'product' ); ?>
					<?php endwhile; ?>
				<?php endif; ?>

				<?php woocommerce_product_loop_end(); ?>
				<?php do_action( 'woocommerce_after_shop_loop' ); ?>
			<?php else : ?>
				<?php woocommerce_output_all_notices(); ?>
				<div class="mb-6 lg:hidden">
					<?php diako_render_shop_sidebar_toggle(); ?>
				</div>
				<?php do_action( 'woocommerce_no_products_found' ); ?>
			<?php endif; ?>

			<?php do_action( 'woocommerce_after_main_content' ); ?>
		</div>
	</div>

	<?php diako_render_product_category_seo_content(); ?>
</div>

<?php
get_footer();
