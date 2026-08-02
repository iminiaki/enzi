<?php
/**
 * Blog archive sidebar.
 *
 * @package Diako
 */

defined( 'ABSPATH' ) || exit;

$mag_url        = diako_get_mag_url();
$is_mag         = diako_is_mag_page();
$current_term   = is_category() ? get_queried_object() : null;
$categories     = diako_get_blog_sidebar_categories();
$sidebar_label  = __( 'فیلتر و دسته‌بندی مقالات', 'diako' );
$nav_label      = __( 'دسته‌بندی مقالات', 'diako' );
?>

<div
	id="diako-blog-drawer"
	class="diako-shop-sidebar-wrap"
	data-shop-drawer
	aria-hidden="true"
>
	<div class="diako-shop-sidebar-wrap__panel">
		<div class="diako-shop-sidebar-wrap__header lg:hidden">
			<h2 class="text-base font-semibold"><?php echo esc_html( $sidebar_label ); ?></h2>
			<button
				type="button"
				class="<?php echo esc_attr( diako_button_classes( 'ghost', 'icon' ) ); ?>"
				data-shop-drawer-close
				aria-label="<?php esc_attr_e( 'بستن', 'diako' ); ?>"
			>
				<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
			</button>
		</div>

		<aside class="diako-shop-sidebar" aria-label="<?php echo esc_attr( $sidebar_label ); ?>">
			<?php if ( ! empty( $categories ) ) : ?>
				<nav class="diako-shop-sidebar__panel" aria-label="<?php echo esc_attr( $nav_label ); ?>">
					<div class="diako-shop-sidebar__panel-head">
						<span class="diako-shop-sidebar__panel-icon" aria-hidden="true">
							<?php echo diako_lucide_icon_svg( 'layout-grid', 'h-4 w-4' ); // phpcs:ignore ?>
						</span>
						<h2 class="diako-shop-sidebar__panel-title"><?php echo esc_html( $nav_label ); ?></h2>
					</div>

					<ul class="diako-shop-sidebar__list">
						<li class="diako-shop-sidebar__item<?php echo $is_mag ? ' is-active' : ''; ?>">
							<a
								class="diako-shop-sidebar__link"
								href="<?php echo esc_url( $mag_url ); ?>"
								<?php echo $is_mag ? ' aria-current="page"' : ''; ?>
							>
								<span class="diako-shop-sidebar__icon" aria-hidden="true">
									<?php echo diako_lucide_icon_svg( 'book-open', 'h-5 w-5' ); // phpcs:ignore ?>
								</span>
								<span><?php esc_html_e( 'همه مقالات', 'diako' ); ?></span>
							</a>
						</li>

						<?php foreach ( $categories as $term ) : ?>
							<?php
							$is_active   = diako_is_blog_sidebar_category_active( $term, $current_term );
							$child_terms = $is_active ? diako_get_blog_sidebar_child_categories( $term ) : array();
							?>
							<li class="diako-shop-sidebar__item<?php echo $is_active ? ' is-active' : ''; ?>">
								<a
									class="diako-shop-sidebar__link"
									href="<?php echo esc_url( get_category_link( $term ) ); ?>"
									<?php echo ( $current_term && (int) $current_term->term_id === (int) $term->term_id ) ? ' aria-current="page"' : ''; ?>
								>
									<span class="diako-shop-sidebar__icon" aria-hidden="true">
										<?php echo diako_lucide_icon_svg( 'book-open', 'h-5 w-5' ); // phpcs:ignore ?>
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
													href="<?php echo esc_url( get_category_link( $child_term ) ); ?>"
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

			<?php if ( diako_blog_sidebar_has_widgets() ) : ?>
				<div class="diako-shop-filters diako-shop-filters--widgets">
					<?php dynamic_sidebar( 'blog-sidebar' ); ?>
				</div>
			<?php endif; ?>
		</aside>
	</div>
</div>
