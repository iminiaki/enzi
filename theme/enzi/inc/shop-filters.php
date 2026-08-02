<?php
/**
 * Built-in shop archive product filters.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attribute slugs used for layered navigation filters.
 *
 * Controlled from each attribute's "Archive filter" checkbox in wp-admin.
 *
 * @return array<int, string>
 */
function diako_get_shop_filter_attribute_slugs() {
	$slugs = diako_get_archive_filter_attribute_slugs();

	if ( empty( $slugs ) ) {
		$slugs = diako_get_default_shop_filter_attribute_slugs();
	}

	return apply_filters( 'diako_shop_filter_attribute_slugs', $slugs );
}

/**
 * Whether the current (or given) product category is the skincare branch.
 *
 * @param WP_Term|null $term Optional category term.
 * @return bool
 */
function diako_is_skincare_product_category( $term = null ) {
	if ( ! $term instanceof WP_Term ) {
		if ( ! is_product_category() ) {
			return false;
		}

		$term = get_queried_object();
	}

	if ( ! $term instanceof WP_Term || 'product_cat' !== $term->taxonomy ) {
		return false;
	}

	$skincare_slugs = array( 'skincare', 'skin-care' );

	if ( in_array( $term->slug, $skincare_slugs, true ) ) {
		return true;
	}

	foreach ( get_ancestors( (int) $term->term_id, 'product_cat' ) as $ancestor_id ) {
		$ancestor = get_term( (int) $ancestor_id, 'product_cat' );

		if ( $ancestor instanceof WP_Term && in_array( $ancestor->slug, $skincare_slugs, true ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Legacy alias kept for older call sites.
 *
 * @param WP_Term|null $term Optional category term.
 * @return bool
 */
function diako_is_games_product_category( $term = null ) {
	return diako_is_skincare_product_category( $term );
}

/**
 * Attribute slugs shown in the sidebar for the current archive context.
 *
 * skin-concern is limited to the skincare category branch; other archives keep
 * shared beauty filters only.
 *
 * @return array<int, string>
 */
function diako_get_shop_filter_attribute_slugs_for_context() {
	$slugs = diako_get_shop_filter_attribute_slugs();

	if ( is_product_category() && ! diako_is_skincare_product_category() ) {
		$slugs = array_values( array_diff( $slugs, array( 'skin-concern' ) ) );
	}

	return apply_filters( 'diako_shop_filter_attribute_slugs_for_context', $slugs );
}

/**
 * Persian labels for shop attribute filter panels.
 *
 * @return array<string, string>
 */
function diako_get_shop_filter_attribute_labels() {
	$labels = array(
		'skin-type'      => __( 'نوع پوست', 'diako' ),
		'skin-concern'   => __( 'نگرانی پوست', 'diako' ),
		'product-form'   => __( 'فرم محصول', 'diako' ),
		'spf'            => __( 'SPF', 'diako' ),
		'volume'         => __( 'حجم', 'diako' ),
		'brand'          => __( 'برند', 'diako' ),
		'manufacturer'   => __( 'برند', 'diako' ),
		'product-type'   => __( 'نوع محصول', 'diako' ),
		'product-family' => __( 'خانواده محصول', 'diako' ),
		'color'          => __( 'رنگ', 'diako' ),
	);

	return apply_filters( 'diako_shop_filter_attribute_labels', $labels );
}

/**
 * Localized title for a shop attribute filter panel.
 *
 * @param string $attribute_slug Attribute slug.
 * @return string
 */
function diako_get_shop_filter_attribute_label( $attribute_slug ) {
	$labels = diako_get_shop_filter_attribute_labels();

	if ( isset( $labels[ $attribute_slug ] ) ) {
		return $labels[ $attribute_slug ];
	}

	return wc_attribute_label( $attribute_slug );
}

/**
 * Translate WooCommerce attribute labels on shop archives.
 *
 * @param string $label Attribute label.
 * @param string $name  Attribute slug or name.
 * @return string
 */
function diako_translate_shop_attribute_label( $label, $name ) {
	if ( ! function_exists( 'diako_shop_has_sidebar' ) || ! diako_shop_has_sidebar() ) {
		return $label;
	}

	$labels = diako_get_shop_filter_attribute_labels();

	if ( isset( $labels[ $name ] ) ) {
		return $labels[ $name ];
	}

	if ( isset( $labels[ $label ] ) ) {
		return $labels[ $label ];
	}

	return $label;
}
add_filter( 'woocommerce_attribute_label', 'diako_translate_shop_attribute_label', 10, 2 );

/**
 * Current shop archive URL (path + query string).
 *
 * @return string
 */
function diako_get_shop_filter_page_url() {
	if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
		return home_url( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	}

	if ( is_shop() ) {
		return wc_get_page_permalink( 'shop' );
	}

	if ( is_product_category() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			$link = get_term_link( $term );

			return is_wp_error( $link ) ? home_url( '/' ) : $link;
		}
	}

	if ( is_product_tag() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			$link = get_term_link( $term );

			return is_wp_error( $link ) ? home_url( '/' ) : $link;
		}
	}

	if ( diako_is_product_search() ) {
		return add_query_arg(
			array(
				's'         => get_search_query(),
				'post_type' => 'product',
			),
			home_url( '/' )
		);
	}

	if ( diako_is_discount_page() ) {
		$page_id = diako_get_discount_page_id();

		if ( $page_id ) {
			return get_permalink( $page_id );
		}
	}

	return home_url( '/' );
}

/**
 * Build a shop filter URL while preserving other active filters.
 *
 * @param array<string, scalar|null> $args     Query args to set (null removes).
 * @param array<int, string>         $remove   Query keys to strip.
 * @return string
 */
function diako_build_shop_filter_url( array $args = array(), array $remove = array() ) {
	$url = diako_get_shop_filter_page_url();

	foreach ( $remove as $key ) {
		$url = remove_query_arg( $key, $url );
	}

	foreach ( $args as $key => $value ) {
		if ( null === $value || '' === $value ) {
			$url = remove_query_arg( $key, $url );
			continue;
		}

		$url = add_query_arg( $key, $value, $url );
	}

	return remove_query_arg( array( 'paged', 'product-page' ), $url );
}

/**
 * Sidebar widget wrapper args for WooCommerce filter widgets.
 *
 * @return array<string, string>
 */
function diako_get_shop_filter_widget_args() {
	return array(
		'before_widget' => '<div class="diako-shop-sidebar__panel diako-shop-filter widget %s">',
		'after_widget'  => '</div>',
		'before_title'  => '<div class="diako-shop-sidebar__panel-head"><span class="diako-shop-sidebar__panel-icon" aria-hidden="true">' . diako_lucide_icon_svg( 'sliders-horizontal', 'h-4 w-4' ) . '</span><h3 class="diako-shop-sidebar__panel-title widget-title">',
		'after_title'   => '</h3></div>',
	);
}

/**
 * Whether a layered nav widget produced visible markup.
 *
 * @param string $html Widget HTML.
 * @return bool
 */
function diako_shop_filter_widget_has_output( $html ) {
	$html = trim( (string) $html );

	if ( '' === $html ) {
		return false;
	}

	return (bool) preg_match( '/class="[^"]*woocommerce-widget-layered-nav-list|price_slider|widget_layered_nav_filters/', $html );
}

/**
 * URL that clears all shop filters for the current archive.
 *
 * @return string
 */
function diako_get_shop_filters_reset_url() {
	if ( is_shop() ) {
		return wc_get_page_permalink( 'shop' );
	}

	if ( is_product_category() || is_product_tag() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			$link = get_term_link( $term );

			return is_wp_error( $link ) ? home_url( '/' ) : $link;
		}
	}

	if ( diako_is_product_search() ) {
		return add_query_arg(
			array(
				's'         => get_search_query(),
				'post_type' => 'product',
			),
			home_url( '/' )
		);
	}

	if ( diako_is_discount_page() ) {
		$page_id = diako_get_discount_page_id();

		if ( $page_id ) {
			return get_permalink( $page_id );
		}
	}

	return home_url( '/' );
}

/**
 * Whether any built-in shop filters are currently active.
 *
 * @return bool
 */
function diako_shop_has_active_filters() {
	return ! empty( diako_get_shop_active_filter_items() );
}

/**
 * Collect all removable active filter chips for the current archive.
 *
 * @return array<int, array{class: string, url: string, label: string}>
 */
function diako_get_shop_active_filter_items() {
	$items     = array();
	$base_link = diako_get_shop_filter_page_url();

	foreach ( diako_get_shop_active_status_filter_items() as $item ) {
		$items[] = $item;
	}

	if ( class_exists( 'WC_Query' ) ) {
		$chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();

		if ( ! empty( $chosen_attributes ) ) {
			foreach ( $chosen_attributes as $taxonomy => $data ) {
				foreach ( $data['terms'] as $term_slug ) {
					$term = get_term_by( 'slug', $term_slug, $taxonomy );

					if ( ! $term instanceof WP_Term ) {
						continue;
					}

					$filter_name    = 'filter_' . wc_attribute_taxonomy_slug( $taxonomy );
					$current_filter = isset( $_GET[ $filter_name ] ) ? explode( ',', wc_clean( wp_unslash( $_GET[ $filter_name ] ) ) ) : array();
					$current_filter = array_map( 'sanitize_title', $current_filter );
					$new_filter     = array_diff( $current_filter, array( $term_slug ) );
					$link           = remove_query_arg( array( 'add-to-cart', $filter_name ), $base_link );

					if ( count( $new_filter ) > 0 ) {
						$link = add_query_arg( $filter_name, implode( ',', $new_filter ), $link );
					}

					$anchor_text = apply_filters( 'woocommerce_widget_layered_nav_term_anchor_text', $term->name, $term, $taxonomy );

					$items[] = array(
						'label' => $anchor_text,
						'url'   => $link,
						'class' => implode(
							' ',
							array(
								'chosen',
								'chosen-' . sanitize_html_class( str_replace( 'pa_', '', $taxonomy ) ),
								'chosen-' . sanitize_html_class( str_replace( 'pa_', '', $taxonomy ) . '-' . $term_slug ),
							)
						),
					);
				}
			}
		}
	}

	$min_price = isset( $_GET['min_price'] ) ? wc_clean( wp_unslash( $_GET['min_price'] ) ) : 0;
	$max_price = isset( $_GET['max_price'] ) ? wc_clean( wp_unslash( $_GET['max_price'] ) ) : 0;

	if ( $min_price ) {
		$items[] = array(
			'label' => sprintf(
				/* translators: %s: minimum price */
				__( 'حداقل %s', 'diako' ),
				diako_filter_persian_digits_string( wp_strip_all_tags( wc_price( $min_price ) ) )
			),
			'url'   => remove_query_arg( 'min_price', $base_link ),
			'class' => 'chosen chosen-min-price',
		);
	}

	if ( $max_price ) {
		$items[] = array(
			'label' => sprintf(
				/* translators: %s: maximum price */
				__( 'حداکثر %s', 'diako' ),
				diako_filter_persian_digits_string( wp_strip_all_tags( wc_price( $max_price ) ) )
			),
			'url'   => remove_query_arg( 'max_price', $base_link ),
			'class' => 'chosen chosen-max-price',
		);
	}

	if ( ! empty( $_GET['rating_filter'] ) ) {
		$rating_filter = array_filter( array_map( 'absint', explode( ',', wp_unslash( $_GET['rating_filter'] ) ) ) );

		foreach ( $rating_filter as $rating ) {
			$link_ratings = implode( ',', array_diff( $rating_filter, array( $rating ) ) );
			$link         = $link_ratings ? add_query_arg( 'rating_filter', $link_ratings, $base_link ) : remove_query_arg( 'rating_filter', $base_link );

			$items[] = array(
				'label' => sprintf(
					/* translators: %s: star rating */
					__( 'امتیاز %s از 5', 'diako' ),
					(string) $rating
				),
				'url'   => $link,
				'class' => 'chosen chosen-rating chosen-rating-' . absint( $rating ),
			);
		}
	}

	return apply_filters( 'diako_shop_active_filter_items', $items );
}

/**
 * Whether custom status filters are active.
 *
 * @return bool
 */
function diako_shop_has_status_filters() {
	if ( empty( $_GET['on_sale'] ) && empty( $_GET['stock_status'] ) ) {
		return false;
	}

	if ( ! empty( $_GET['on_sale'] ) && wc_string_to_bool( wp_unslash( $_GET['on_sale'] ) ) ) {
		return true;
	}

	if ( ! empty( $_GET['stock_status'] ) ) {
		$status = sanitize_text_field( wp_unslash( $_GET['stock_status'] ) );

		return in_array( $status, array( 'instock', 'outofstock', 'onbackorder' ), true );
	}

	return false;
}

/**
 * Apply on-sale and stock URL filters to the product query.
 *
 * @param WP_Query $query Main product query.
 * @return void
 */
function diako_apply_shop_status_filters( $query ) {
	if ( is_admin() || ! $query instanceof WP_Query ) {
		return;
	}

	$on_sale = ! empty( $_GET['on_sale'] ) && wc_string_to_bool( wp_unslash( $_GET['on_sale'] ) );

	$stock_status = '';
	if ( ! empty( $_GET['stock_status'] ) ) {
		$stock_status = sanitize_text_field( wp_unslash( $_GET['stock_status'] ) );
	}

	if ( ! $on_sale && ! in_array( $stock_status, array( 'instock', 'outofstock', 'onbackorder' ), true ) ) {
		return;
	}

	if ( $on_sale && function_exists( 'wc_get_product_ids_on_sale' ) ) {
		$sale_ids = wc_get_product_ids_on_sale();
		$post_in  = array_filter( array_map( 'absint', (array) $query->get( 'post__in' ) ) );

		if ( ! empty( $post_in ) ) {
			$sale_ids = array_values( array_intersect( $post_in, $sale_ids ) );
		}

	$query->set( 'post__in', ! empty( $sale_ids ) ? $sale_ids : array( 0 ) );

	if ( ! diako_shop_has_explicit_stock_status_filter() ) {
		diako_enable_instock_first_product_sort( $query );
	}
}

	if ( in_array( $stock_status, array( 'instock', 'outofstock', 'onbackorder' ), true ) ) {
		$meta_query = $query->get( 'meta_query' );

		if ( ! is_array( $meta_query ) ) {
			$meta_query = array();
		}

		$meta_query[] = array(
			'key'     => '_stock_status',
			'value'   => $stock_status,
			'compare' => '=',
		);

		$query->set( 'meta_query', $meta_query );
	}
}
add_action( 'woocommerce_product_query', 'diako_apply_shop_status_filters', 20 );

/**
 * Render on-sale / in-stock filter links.
 *
 * @return void
 */
function diako_render_shop_status_filters() {
	$on_sale_active = ! empty( $_GET['on_sale'] ) && wc_string_to_bool( wp_unslash( $_GET['on_sale'] ) );
	$stock_status   = ! empty( $_GET['stock_status'] ) ? sanitize_text_field( wp_unslash( $_GET['stock_status'] ) ) : '';
	$instock_active = 'instock' === $stock_status;

	$items = array(
		array(
			'label'  => __( 'فقط محصولات تخفیف‌دار', 'diako' ),
			'active' => $on_sale_active,
			'url'    => $on_sale_active
				? diako_build_shop_filter_url( array( 'on_sale' => null ) )
				: diako_build_shop_filter_url( array( 'on_sale' => '1' ) ),
		),
		array(
			'label'  => __( 'فقط کالاهای موجود', 'diako' ),
			'active' => $instock_active,
			'url'    => $instock_active
				? diako_build_shop_filter_url( array( 'stock_status' => null ) )
				: diako_build_shop_filter_url( array( 'stock_status' => 'instock' ) ),
		),
	);
	?>
	<div class="diako-shop-sidebar__panel diako-shop-filter diako-shop-filter--status">
		<div class="diako-shop-sidebar__panel-head">
			<span class="diako-shop-sidebar__panel-icon" aria-hidden="true">
				<?php echo diako_lucide_icon_svg( 'percent', 'h-4 w-4' ); // phpcs:ignore ?>
			</span>
			<h3 class="diako-shop-sidebar__panel-title"><?php esc_html_e( 'وضعیت محصول', 'diako' ); ?></h3>
		</div>
		<ul class="diako-shop-filter__list">
			<?php foreach ( $items as $item ) : ?>
				<li class="diako-shop-filter__item<?php echo ! empty( $item['active'] ) ? ' is-active' : ''; ?>">
					<a class="diako-shop-filter__link" href="<?php echo esc_url( $item['url'] ); ?>">
						<span><?php echo esc_html( $item['label'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/**
 * Active status filter chips for the sidebar.
 *
 * @return array<int, array{label: string, url: string, class: string}>
 */
function diako_get_shop_active_status_filter_items() {
	$items = array();

	if ( ! empty( $_GET['on_sale'] ) && wc_string_to_bool( wp_unslash( $_GET['on_sale'] ) ) ) {
		$items[] = array(
			'label' => __( 'فقط محصولات تخفیف‌دار', 'diako' ),
			'url'   => diako_build_shop_filter_url( array( 'on_sale' => null ) ),
			'class' => 'chosen chosen-on-sale',
		);
	}

	if ( ! empty( $_GET['stock_status'] ) ) {
		$status = sanitize_text_field( wp_unslash( $_GET['stock_status'] ) );
		$labels = array(
			'instock'     => __( 'فقط کالاهای موجود', 'diako' ),
			'outofstock'  => __( 'ناموجود', 'diako' ),
			'onbackorder' => __( 'پیش‌سفارش', 'diako' ),
		);

		if ( isset( $labels[ $status ] ) ) {
			$items[] = array(
				'label' => $labels[ $status ],
				'url'   => diako_build_shop_filter_url( array( 'stock_status' => null ) ),
				'class' => 'chosen chosen-stock-status chosen-stock-status-' . sanitize_html_class( $status ),
			);
		}
	}

	return $items;
}

/**
 * Output one removable active filter chip.
 *
 * @param string $class CSS classes for the list item.
 * @param string $url   URL that removes the filter.
 * @param string $label Visible filter label.
 * @return void
 */
function diako_render_shop_active_filter_item( $class, $url, $label ) {
	?>
	<li class="<?php echo esc_attr( $class ); ?>">
		<a
			rel="nofollow"
			aria-label="<?php echo esc_attr( sprintf( __( 'حذف فیلتر: %s', 'diako' ), $label ) ); ?>"
			href="<?php echo esc_url( $url ); ?>"
		>
			<span class="diako-shop-filter__chosen-label"><?php echo esc_html( $label ); ?></span>
			<span class="diako-shop-filter__remove" aria-hidden="true">
				<?php echo diako_lucide_icon_svg( 'x', 'h-3 w-3' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
		</a>
	</li>
	<?php
}

/**
 * Render active shop filters, including custom status filters.
 *
 * @return void
 */
function diako_render_shop_active_filters() {
	$items = diako_get_shop_active_filter_items();

	if ( empty( $items ) ) {
		return;
	}

	$wrapper      = diako_get_shop_filter_widget_args();
	$widget_class = 'woocommerce widget_layered_nav_filters';
	$title        = __( 'فیلترهای فعال', 'diako' );

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo sprintf( $wrapper['before_widget'], esc_attr( $widget_class ) );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $wrapper['before_title'] . esc_html( $title ) . $wrapper['after_title'];
	echo '<ul>';

	foreach ( $items as $item ) {
		diako_render_shop_active_filter_item( $item['class'], $item['url'], $item['label'] );
	}

	echo '</ul>';

	echo '<div class="diako-shop-filter__actions">';
	printf(
		'<a class="%1$s" href="%2$s">%3$s</a>',
		esc_attr( diako_button_classes( 'outline', 'sm', 'w-full' ) ),
		esc_url( diako_get_shop_filters_reset_url() ),
		esc_html__( 'پاک کردن همه فیلترها', 'diako' )
	);
	echo '</div>';

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $wrapper['after_widget'];
}

/**
 * Render built-in WooCommerce archive filters.
 *
 * @return void
 */
function diako_render_shop_filters() {
	if ( ! function_exists( 'diako_shop_has_sidebar' ) || ! diako_shop_has_sidebar() ) {
		return;
	}

	if ( ! diako_is_shop_filter_context() ) {
		return;
	}

	$wrapper       = diako_get_shop_filter_widget_args();
	$has_output    = false;
	$buffer_blocks = array();

	if ( diako_shop_has_active_filters() ) {
		ob_start();
		diako_render_shop_active_filters();
		$active_html = ob_get_clean();

		if ( diako_shop_filter_widget_has_output( $active_html ) ) {
			$buffer_blocks[] = $active_html;
			$has_output      = true;
		}
	}

	ob_start();
	diako_render_shop_status_filters();
	$buffer_blocks[] = ob_get_clean();
	$has_output      = true;

	if ( class_exists( 'WC_Widget_Price_Filter' ) ) {
		ob_start();
		the_widget(
			'WC_Widget_Price_Filter',
			array(
				'title' => __( 'محدوده قیمت', 'diako' ),
			),
			$wrapper
		);
		$price_html = diako_filter_persian_digits_string( (string) ob_get_clean() );

		if ( diako_shop_filter_widget_has_output( $price_html ) ) {
			$buffer_blocks[] = $price_html;
			$has_output      = true;
		}
	}

	foreach ( diako_get_shop_filter_attribute_slugs_for_context() as $attribute_slug ) {
		if ( ! taxonomy_exists( wc_attribute_taxonomy_name( $attribute_slug ) ) ) {
			continue;
		}

		ob_start();
		the_widget(
			'WC_Widget_Layered_Nav',
			array(
				'title'        => diako_get_shop_filter_attribute_label( $attribute_slug ),
				'attribute'    => $attribute_slug,
				'display_type' => 'list',
				'query_type'   => 'or',
			),
			$wrapper
		);
		$attribute_html = ob_get_clean();

		if ( diako_shop_filter_widget_has_output( $attribute_html ) ) {
			$buffer_blocks[] = $attribute_html;
			$has_output      = true;
		}
	}

	if ( ! $has_output ) {
		return;
	}

	echo '<div class="diako-shop-filters diako-shop-filters--built-in" data-shop-filters-built-in>';

	foreach ( $buffer_blocks as $block ) {
		echo $block; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo '</div>';
}

/**
 * Ensure WooCommerce price slider assets load on shop archives.
 *
 * @return void
 */
function diako_enqueue_shop_filter_assets() {
	if ( ! function_exists( 'diako_shop_has_sidebar' ) || ! diako_shop_has_sidebar() ) {
		return;
	}

	if ( ! diako_is_shop_filter_context() ) {
		return;
	}

	if ( ! class_exists( 'WC_Widget_Price_Filter' ) ) {
		return;
	}

	// Register slider params by constructing the widget, then enqueue in <head> time.
	new WC_Widget_Price_Filter();
	wp_enqueue_script( 'wc-price-slider' );
}
add_action( 'wp_enqueue_scripts', 'diako_enqueue_shop_filter_assets', 20 );

/**
 * Render layered nav term as a single row link (label + count inside anchor).
 *
 * @param string   $term_html Default HTML.
 * @param WP_Term  $term      Attribute term.
 * @param string   $link      Filter URL.
 * @param int      $count     Product count.
 * @return string
 */
function diako_filter_layered_nav_term_html( $term_html, $term, $link, $count ) {
	if ( ! $link || ! $term instanceof WP_Term ) {
		return $term_html;
	}

	$count_html = apply_filters(
		'woocommerce_layered_nav_count',
		'<span class="count">(' . absint( $count ) . ')</span>',
		$count,
		$term
	);

	return sprintf(
		'<a class="diako-shop-filter__term" rel="nofollow" href="%1$s"><span class="diako-shop-filter__term-label">%2$s</span>%3$s</a>',
		esc_url( $link ),
		esc_html( $term->name ),
		$count_html
	);
}
add_filter( 'woocommerce_layered_nav_term_html', 'diako_filter_layered_nav_term_html', 10, 4 );

/**
 * Apply WooCommerce catalog filters and keep results on sale products.
 *
 * @param WP_Query $query Product query.
 * @return void
 */
function diako_apply_discount_product_query_filters( WP_Query $query ) {
	if ( class_exists( 'WC_Query' ) ) {
		( new WC_Query() )->product_query( $query );
	}

	diako_apply_shop_status_filters( $query );

	if ( ! diako_shop_has_explicit_stock_status_filter() ) {
		diako_enable_instock_first_product_sort( $query );
	}

	if ( ! function_exists( 'wc_get_product_ids_on_sale' ) ) {
		return;
	}

	$sale_ids = wc_get_product_ids_on_sale();

	if ( empty( $sale_ids ) ) {
		$query->set( 'post__in', array( 0 ) );
		return;
	}

	$post_in = array_filter( array_map( 'absint', (array) $query->get( 'post__in' ) ) );

	if ( ! empty( $post_in ) ) {
		$post_in = array_values( array_intersect( $post_in, $sale_ids ) );
		$query->set( 'post__in', ! empty( $post_in ) ? $post_in : array( 0 ) );
		return;
	}

	$query->set( 'post__in', $sale_ids );
}

/**
 * Query on-sale products for the discount page, including active shop filters.
 *
 * @return WP_Query
 */
function diako_get_discount_products_query() {
	$sale_ids = function_exists( 'wc_get_product_ids_on_sale' ) ? wc_get_product_ids_on_sale() : array();

	$query = new WP_Query();
	$query->set( 'post_type', 'product' );
	$query->set( 'post_status', 'publish' );
	$query->set( 'posts_per_page', diako_products_per_page() );
	$query->set(
		'paged',
		max(
			1,
			(int) get_query_var( 'paged' ),
			(int) get_query_var( 'page' )
		)
	);
	$query->set( 'post__in', ! empty( $sale_ids ) ? $sale_ids : array( 0 ) );

	diako_apply_discount_product_query_filters( $query );
	$query->get_posts();

	return $query;
}
