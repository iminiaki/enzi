<?php defined( 'ABSPATH' ) || exit; ?>
<?php
/**
 * Template Name: محصولات تخفیف‌دار
 *
 * @package Diako
 */

get_header();

$discount_query = diako_get_discount_products_query();
$previous_query = $GLOBALS['wp_query'];
$GLOBALS['wp_query'] = $discount_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

wc_setup_loop(
	array(
		'total'        => $discount_query->found_posts,
		'total_pages'  => $discount_query->max_num_pages,
		'per_page'     => (int) $discount_query->get( 'posts_per_page' ),
		'current_page' => max( 1, (int) $discount_query->get( 'paged' ) ),
		'columns'      => diako_loop_columns(),
	)
);
?>

<div class="diako-page woocommerce-page-wrap">
	<?php diako_render_shop_archive_header(); ?>

	<?php if ( diako_shop_has_sidebar() ) : ?>
		<div class="diako-shop-drawer-backdrop" data-shop-drawer-backdrop hidden aria-hidden="true"></div>
	<?php endif; ?>

	<div class="diako-shop-shell">
		<?php diako_render_shop_sidebar(); ?>

		<div class="diako-shop-main" data-shop-main>
			<?php if ( $discount_query->have_posts() ) : ?>
				<div class="diako-shop-toolbar">
					<div class="flex flex-wrap items-center gap-3">
						<?php diako_render_shop_sidebar_toggle(); ?>
						<?php woocommerce_result_count(); ?>
					</div>
					<?php woocommerce_catalog_ordering(); ?>
				</div>

				<?php woocommerce_product_loop_start(); ?>

				<?php
				while ( $discount_query->have_posts() ) :
					$discount_query->the_post();
					wc_get_template_part( 'content', 'product' );
				endwhile;
				?>

				<?php woocommerce_product_loop_end(); ?>

				<?php woocommerce_pagination(); ?>
			<?php else : ?>
				<div class="mb-6 lg:hidden">
					<?php diako_render_shop_sidebar_toggle(); ?>
				</div>
				<?php wc_get_template( 'loop/no-products-found.php' ); ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php
wp_reset_postdata();
wc_reset_loop();
$GLOBALS['wp_query'] = $previous_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

get_footer();
