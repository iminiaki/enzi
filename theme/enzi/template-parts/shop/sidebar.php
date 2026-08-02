<?php
/**
 * Shop archive sidebar: filters on category pages, categories on the shop index.
 *
 * @package Diako
 */

defined( 'ABSPATH' ) || exit;

$current_term       = is_product_category() ? get_queried_object() : null;
$categories         = diako_shop_sidebar_show_category_nav() ? diako_get_shop_sidebar_categories() : array();
$shop_url           = wc_get_page_id( 'shop' ) > 0 ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/shop/' );
$is_shop            = is_shop();
$show_category_nav  = diako_shop_sidebar_show_category_nav();
$drawer_title       = is_product_category()
	? __( 'فیلتر محصولات', 'diako' )
	: __( 'فیلتر و دسته‌بندی', 'diako' );
$sidebar_aria_label = is_product_category()
	? __( 'فیلتر محصولات', 'diako' )
	: __( 'فیلترها و دسته‌بندی محصولات', 'diako' );
?>

<div
	id="diako-shop-drawer"
	class="diako-shop-sidebar-wrap"
	data-shop-drawer
	aria-hidden="true"
>
	<div class="diako-shop-sidebar-wrap__panel">
		<div class="diako-shop-sidebar-wrap__header lg:hidden">
			<h2 class="text-base font-semibold"><?php echo esc_html( $drawer_title ); ?></h2>
			<button
				type="button"
				class="<?php echo esc_attr( diako_button_classes( 'ghost', 'icon' ) ); ?>"
				data-shop-drawer-close
				aria-label="<?php esc_attr_e( 'بستن', 'diako' ); ?>"
			>
				<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
			</button>
		</div>

		<aside class="diako-shop-sidebar" aria-label="<?php echo esc_attr( $sidebar_aria_label ); ?>">
			<?php diako_render_shop_sidebar_banner(); ?>
			<?php if ( $show_category_nav && ! empty( $categories ) ) : ?>
				<nav class="diako-shop-sidebar__panel" aria-label="<?php esc_attr_e( 'دسته‌بندی محصولات', 'diako' ); ?>">
					<div class="diako-shop-sidebar__panel-head">
						<span class="diako-shop-sidebar__panel-icon" aria-hidden="true">
							<?php echo diako_lucide_icon_svg( 'layout-grid', 'h-4 w-4' ); // phpcs:ignore ?>
						</span>
						<h2 class="diako-shop-sidebar__panel-title"><?php esc_html_e( 'دسته‌بندی محصولات', 'diako' ); ?></h2>
					</div>

					<ul class="diako-shop-sidebar__list">
						<li class="diako-shop-sidebar__item<?php echo $is_shop ? ' is-active' : ''; ?>">
							<a
								class="diako-shop-sidebar__link"
								href="<?php echo esc_url( $shop_url ); ?>"
								<?php echo $is_shop ? ' aria-current="page"' : ''; ?>
							>
								<span class="diako-shop-sidebar__icon" aria-hidden="true">
									<?php echo diako_lucide_icon_svg( 'layout-grid', 'h-5 w-5' ); // phpcs:ignore ?>
								</span>
								<span><?php esc_html_e( 'همه محصولات', 'diako' ); ?></span>
							</a>
						</li>

						<?php foreach ( $categories as $term ) : ?>
							<?php
							$is_active   = diako_is_shop_sidebar_category_active( $term, $current_term );
							$child_terms = $is_active ? diako_get_shop_sidebar_child_categories( $term ) : array();
							?>
							<li class="diako-shop-sidebar__item<?php echo $is_active ? ' is-active' : ''; ?>">
								<a
									class="diako-shop-sidebar__link"
									href="<?php echo esc_url( get_term_link( $term ) ); ?>"
									<?php echo ( $current_term && (int) $current_term->term_id === (int) $term->term_id ) ? ' aria-current="page"' : ''; ?>
								>
									<span class="diako-shop-sidebar__icon" aria-hidden="true">
										<?php diako_render_product_category_sidebar_icon( $term ); ?>
									</span>
									<span><?php echo esc_html( $term->name ); ?></span>
								</a>

								<?php if ( ! empty( $child_terms ) ) : ?>
									<ul class="diako-shop-sidebar__sublist">
										<?php foreach ( $child_terms as $child_term ) : ?>
											<?php $is_child_active = $current_term && (int) $current_term->term_id === (int) $child_term->term_id; ?>
											<li class="diako-shop-sidebar__subitem<?php echo $is_child_active ? ' is-active' : ''; ?>">
												<a
													class="diako-shop-sidebar__sublink"
													href="<?php echo esc_url( get_term_link( $child_term ) ); ?>"
													<?php echo $is_child_active ? ' aria-current="page"' : ''; ?>
												>
													<?php echo esc_html( $child_term->name ); ?>
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>

			<?php diako_render_shop_filters(); ?>

			<?php if ( diako_shop_sidebar_has_widgets() ) : ?>
				<div class="diako-shop-filters diako-shop-filters--widgets">
					<?php dynamic_sidebar( 'shop-sidebar' ); ?>
				</div>
			<?php endif; ?>
		</aside>
	</div>
</div>
