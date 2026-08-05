<?php
/**
 * WooCommerce integration.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	function () {
		add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
	},
	20
);

add_action(
	'woocommerce_init',
	function () {
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );

		remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 );
		remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );

		add_action( 'woocommerce_single_variation', 'diako_single_product_open_cart_form', 5 );
		add_action( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 );
		add_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
		add_action( 'woocommerce_single_variation', 'diako_single_product_close_cart_form', 35 );
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
		remove_filter( 'woocommerce_product_loop_start', 'woocommerce_maybe_show_product_subcategories' );
	}
);

add_filter(
	'body_class',
	function ( $classes ) {
		if ( function_exists( 'diako_shop_has_sidebar' ) && diako_shop_has_sidebar() ) {
			$classes[] = 'diako-shop-archive';
		}

		if ( function_exists( 'diako_is_discount_page' ) && diako_is_discount_page() ) {
			$classes[] = 'woocommerce';
			$classes[] = 'woocommerce-page';
		}

		return $classes;
	}
);

add_filter(
	'woocommerce_page_title',
	function ( $title ) {
		if ( diako_is_product_search() ) {
			return sprintf(
				/* translators: %s: search query */
				__( 'جستجو: %s', 'diako' ),
				get_search_query()
			);
		}

		if ( is_shop() ) {
			return __( 'فروشگاه', 'diako' );
		}

		if ( diako_is_discount_page() ) {
			return diako_get_discount_page_title();
		}

		return $title;
	}
);

/**
 * Whether the current request is a product search results page.
 *
 * @return bool
 */
function diako_is_product_search() {
	if ( ! is_search() ) {
		return false;
	}

	$post_type = get_query_var( 'post_type' );

	if ( is_array( $post_type ) ) {
		return in_array( 'product', $post_type, true );
	}

	return 'product' === $post_type;
}

/**
 * Discount products page ID (uses the محصولات تخفیف‌دار page template).
 *
 * @return int
 */
function diako_get_discount_page_id() {
	global $wp_query;

	if ( $wp_query instanceof WP_Query && $wp_query->get( 'diako_discount_page' ) ) {
		return (int) $wp_query->get( 'diako_discount_page' );
	}

	if ( is_page() && is_page_template( 'page-discount.php' ) ) {
		return (int) get_queried_object_id();
	}

	static $cached = null;

	if ( null === $cached ) {
		$pages = get_pages(
			array(
				'meta_key'   => '_wp_page_template',
				'meta_value' => 'page-discount.php',
				'number'     => 1,
			)
		);

		$cached = $pages ? (int) $pages[0]->ID : 0;
	}

	return $cached;
}

/**
 * Whether the current request is the discount products page.
 *
 * @param WP_Query|null $query Query instance.
 * @return bool
 */
function diako_is_discount_page( $query = null ) {
	if ( null === $query ) {
		global $wp_query;

		if ( $wp_query instanceof WP_Query && $wp_query->get( 'diako_discount_page' ) ) {
			return true;
		}

		return is_page() && is_page_template( 'page-discount.php' );
	}

	if ( $query->get( 'diako_discount_page' ) ) {
		return true;
	}

	return (bool) diako_get_discount_page_id_from_query( $query );
}

/**
 * Resolve the discount page ID from a query object during pre_get_posts.
 *
 * @param WP_Query $query Query instance.
 * @return int
 */
function diako_get_discount_page_id_from_query( WP_Query $query ) {
	$page_id = (int) $query->get( 'page_id' );

	if ( $page_id && 'page-discount.php' === get_page_template_slug( $page_id ) ) {
		return $page_id;
	}

	$page_id = (int) $query->get( 'p' );

	if ( $page_id && 'page-discount.php' === get_page_template_slug( $page_id ) ) {
		return $page_id;
	}

	$pagename = $query->get( 'pagename' );

	if ( $pagename ) {
		$page = get_page_by_path( $pagename );

		if ( $page instanceof WP_Post && 'page-discount.php' === get_page_template_slug( $page->ID ) ) {
			return (int) $page->ID;
		}
	}

	return 0;
}

/**
 * Discount products page title.
 *
 * @return string
 */
function diako_get_discount_page_title() {
	$page_id = diako_get_discount_page_id();

	if ( $page_id ) {
		return get_the_title( $page_id );
	}

	return __( 'محصولات تخفیف‌دار', 'diako' );
}

/**
 * Whether the current view supports built-in shop filters.
 *
 * @return bool
 */
function diako_is_shop_filter_context() {
	return is_shop() || is_product_taxonomy() || diako_is_product_search() || diako_is_discount_page();
}

/**
 * Whether the current shop archive should render the sidebar.
 *
 * @return bool
 */
function diako_shop_has_sidebar() {
	return diako_is_shop_filter_context();
}

/**
 * Coming Soon product category term ID.
 *
 * @return int
 */
function diako_get_coming_soon_category_id() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	if ( ! taxonomy_exists( 'product_cat' ) ) {
		$cached = 0;
		return $cached;
	}

	$term_id = (int) get_option( 'diako_coming_soon_category_id', 0 );

	if ( $term_id ) {
		$term = get_term( $term_id, 'product_cat' );

		if ( $term instanceof WP_Term && ! is_wp_error( $term ) ) {
			$cached = $term_id;
			return $cached;
		}
	}

	$term = get_term_by( 'slug', 'coming-soon', 'product_cat' );

	if ( $term instanceof WP_Term ) {
		$cached = (int) $term->term_id;
		update_option( 'diako_coming_soon_category_id', $cached );
		return $cached;
	}

	$cached = 0;
	return $cached;
}

/**
 * Coming Soon category archive URL.
 *
 * @return string
 */
function diako_get_coming_soon_category_url() {
	$term_id = diako_get_coming_soon_category_id();

	if ( $term_id ) {
		$link = get_term_link( $term_id, 'product_cat' );

		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}

	return home_url( '/product-category/coming-soon/' );
}

/**
 * Create or repair the Coming Soon product category.
 *
 * @return int Term ID or 0 on failure.
 */
function diako_ensure_coming_soon_category() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return 0;
	}

	$term_id = diako_get_coming_soon_category_id();

	if ( $term_id ) {
		return $term_id;
	}

	$result = wp_insert_term(
		__( 'به زودی', 'diako' ),
		'product_cat',
		array(
			'slug'        => 'coming-soon',
			'description' => sprintf(
				/* translators: %s: store brand name */
				__( 'محصولات جدید به زودی در %s.', 'diako' ),
				diako_get_brand_name()
			),
		)
	);

	if ( is_wp_error( $result ) ) {
		if ( 'term_exists' === $result->get_error_code() ) {
			$term_id = (int) $result->get_error_data();
		} else {
			return 0;
		}
	} else {
		$term_id = (int) $result['term_id'];
	}

	update_option( 'diako_coming_soon_category_id', $term_id );

	return $term_id;
}

// Enzi starts empty — do not auto-create the coming-soon product category.
// Call diako_ensure_coming_soon_category() manually if needed.

/**
 * Whether query args target only the coming-soon product category.
 *
 * @param array<string, mixed> $query_args WP_Query args.
 * @return bool
 */
function diako_query_args_is_coming_soon_only( array $query_args ): bool {
	$coming_soon_id = diako_get_coming_soon_category_id();

	if ( empty( $query_args['tax_query'] ) || ! is_array( $query_args['tax_query'] ) ) {
		return false;
	}

	foreach ( $query_args['tax_query'] as $tax ) {
		if ( ! is_array( $tax ) || 'product_cat' !== ( $tax['taxonomy'] ?? '' ) ) {
			continue;
		}

		$field = $tax['field'] ?? 'term_id';
		$terms = array_map( 'strval', (array) ( $tax['terms'] ?? array() ) );

		if ( 'slug' === $field && in_array( 'coming-soon', $terms, true ) ) {
			return true;
		}

		if ( $coming_soon_id && in_array( (string) $coming_soon_id, $terms, true ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Whether the current archive is the coming-soon category.
 *
 * @param WP_Query|null $query Query instance.
 * @return bool
 */
function diako_is_coming_soon_category_archive( $query = null ): bool {
	$coming_soon_id = diako_get_coming_soon_category_id();

	if ( ! $coming_soon_id ) {
		return false;
	}

	if ( null === $query ) {
		return function_exists( 'is_product_category' ) && is_product_category( $coming_soon_id );
	}

	if ( ! $query instanceof WP_Query ) {
		return false;
	}

	$queried = $query->get_queried_object();

	if ( $queried instanceof WP_Term && 'product_cat' === $queried->taxonomy && (int) $queried->term_id === $coming_soon_id ) {
		return true;
	}

	return diako_query_args_is_coming_soon_only(
		array(
			'tax_query' => (array) $query->get( 'tax_query' ),
		)
	);
}

/**
 * Whether a stock-status URL filter is active on shop archives.
 *
 * @return bool
 */
function diako_shop_has_explicit_stock_status_filter(): bool {
	if ( empty( $_GET['stock_status'] ) ) {
		return false;
	}

	$status = sanitize_text_field( wp_unslash( (string) $_GET['stock_status'] ) );

	return in_array( $status, array( 'instock', 'outofstock', 'onbackorder' ), true );
}

/**
 * Enable in-stock-first ordering on a product query.
 *
 * @param WP_Query $query Query instance.
 * @return void
 */
function diako_enable_instock_first_product_sort( WP_Query $query ): void {
	$query->set( 'diako_instock_first', true );
}

/**
 * Add in-stock-first ordering to product query args when appropriate.
 *
 * @param array<string, mixed> $query_args WP_Query args.
 * @return array<string, mixed>
 */
function diako_apply_instock_first_to_product_query_args( array $query_args ): array {
	if ( ( $query_args['post_type'] ?? '' ) !== 'product' || diako_query_args_is_coming_soon_only( $query_args ) ) {
		return $query_args;
	}

	$query_args['diako_instock_first'] = true;

	return $query_args;
}

/**
 * Sort in-stock products before out-of-stock items in SQL.
 *
 * @param array<string, string> $clauses Query clauses.
 * @param WP_Query              $query   Query instance.
 * @return array<string, string>
 */
function diako_posts_clauses_instock_first( array $clauses, WP_Query $query ): array {
	if ( is_admin() || ! $query->get( 'diako_instock_first' ) ) {
		return $clauses;
	}

	global $wpdb;

	if ( false === strpos( $clauses['join'], 'diako_stock_sort' ) ) {
		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS diako_stock_sort ON ({$wpdb->posts}.ID = diako_stock_sort.post_id AND diako_stock_sort.meta_key = '_stock_status') ";
	}

	$stock_order = "CASE diako_stock_sort.meta_value WHEN 'instock' THEN 0 WHEN 'onbackorder' THEN 1 ELSE 2 END ASC";

	if ( ! empty( $clauses['orderby'] ) ) {
		$clauses['orderby'] = "{$stock_order}, {$clauses['orderby']}";
	} else {
		$clauses['orderby'] = "{$stock_order}, {$wpdb->posts}.post_date DESC";
	}

	return $clauses;
}
add_filter( 'posts_clauses', 'diako_posts_clauses_instock_first', 20, 2 );

/**
 * Prioritize in-stock products on WooCommerce archive queries.
 *
 * @param WP_Query $query Main product query.
 * @return void
 */
function diako_prioritize_instock_on_wc_product_query( WP_Query $query ): void {
	if ( diako_is_coming_soon_category_archive( $query ) || diako_shop_has_explicit_stock_status_filter() ) {
		return;
	}

	diako_enable_instock_first_product_sort( $query );
}
add_action( 'woocommerce_product_query', 'diako_prioritize_instock_on_wc_product_query', 30 );

/**
 * Top-level product categories for the shop sidebar.
 *
 * @return array<int, WP_Term>
 */
function diako_get_shop_sidebar_categories() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'parent'     => 0,
			'exclude'    => array( (int) get_option( 'default_product_cat', 0 ) ),
			'orderby'    => 'term_id',
			'order'      => 'ASC',
		)
	);

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Direct child categories of a product category (for archive subcategory grids).
 *
 * @param WP_Term|null $term Category term; defaults to the current archive term.
 * @return array<int, WP_Term>
 */
function diako_get_product_category_subcategories( $term = null ) {
	if ( ! $term instanceof WP_Term ) {
		if ( ! is_product_category() ) {
			return array();
		}

		$term = get_queried_object();
	}

	if ( ! $term instanceof WP_Term || 'product_cat' !== $term->taxonomy ) {
		return array();
	}

	$args = apply_filters(
		'diako_product_category_subcategories_args',
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => (int) $term->term_id,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	$terms = get_terms( $args );

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Whether the current product category archive has subcategories to display.
 *
 * @return bool
 */
function diako_product_category_has_subcategories() {
	return is_product_category() && ! empty( diako_get_product_category_subcategories() );
}

/**
 * Render subcategory cards above the shop layout on category archives.
 *
 * @return void
 */
function diako_render_product_category_subcategories() {
	if ( ! diako_product_category_has_subcategories() ) {
		return;
	}

	get_template_part( 'template-parts/shop/subcategories' );
}

/**
 * Whether the shop sidebar should list top-level categories (shop index navigation).
 *
 * @return bool
 */
function diako_shop_sidebar_show_category_nav() {
	return is_shop() || diako_is_product_search() || diako_is_discount_page();
}

/**
 * Whether the shop sidebar widget area has active filter widgets.
 *
 * @return bool
 */
function diako_shop_sidebar_has_widgets() {
	return is_active_sidebar( 'shop-sidebar' );
}

/**
 * Child categories shown under an active parent in the sidebar.
 *
 * @param WP_Term $parent Parent category.
 * @return array<int, WP_Term>
 */
function diako_get_shop_sidebar_child_categories( $parent ) {
	if ( ! $parent instanceof WP_Term ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'parent'     => (int) $parent->term_id,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Whether a sidebar category (or its children) matches the current archive.
 *
 * @param WP_Term      $term         Sidebar category.
 * @param WP_Term|null $current_term Current archive term.
 * @return bool
 */
function diako_is_shop_sidebar_category_active( $term, $current_term ) {
	if ( ! $current_term instanceof WP_Term ) {
		return false;
	}

	if ( (int) $term->term_id === (int) $current_term->term_id ) {
		return true;
	}

	return (int) $current_term->parent === (int) $term->term_id;
}

/**
 * Lucide icon name for a product category.
 *
 * @param WP_Term $term Category term.
 * @return string
 */
function diako_get_product_category_icon_name( $term ) {
	$map = array(
		'skincare'     => 'sparkles',
		'makeup'       => 'heart',
		'beauty-tools' => 'package',
		'hair-care'    => 'sparkles',
		'body-care'    => 'heart',
		'coming-soon'  => 'clock',
	);

	return $map[ $term->slug ] ?? 'layout-grid';
}

/**
 * Render a category icon in the shop sidebar.
 *
 * @param WP_Term $term Category term.
 * @return void
 */
function diako_render_product_category_sidebar_icon( $term ) {
	if ( ! $term instanceof WP_Term ) {
		return;
	}

	$thumbnail_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );

	if ( $thumbnail_id ) {
		echo wp_get_attachment_image(
			$thumbnail_id,
			array( 40, 40 ),
			false,
			array(
				'class' => 'diako-category-icon h-5 w-5 object-contain',
				'alt'   => '',
			)
		);
		return;
	}

	echo diako_lucide_icon_svg( diako_get_product_category_icon_name( $term ), 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Sidebar banner image URL for product archive pages.
 *
 * @return string
 */
function diako_get_shop_sidebar_banner_url() {
	$default = '';

	if ( is_readable( DIAKO_DIR . '/assets/images/snapppay-banner.jpg' ) ) {
		$default = DIAKO_URI . '/assets/images/snapppay-banner.jpg';
	}

	return (string) apply_filters( 'diako_shop_sidebar_banner_url', $default );
}

/**
 * Render the sidebar banner on product archive pages.
 *
 * @return void
 */
function diako_render_shop_sidebar_banner() {
	$banner_url = diako_get_shop_sidebar_banner_url();

	if ( '' === $banner_url ) {
		return;
	}
	?>
	<div class="diako-shop-sidebar__banner">
		<img
			class="diako-shop-sidebar__banner-image"
			src="<?php echo esc_url( $banner_url ); ?>"
			alt="<?php esc_attr_e( 'اسنپ‌پی — خرید اعتباری', 'diako' ); ?>"
			width="280"
			height="281"
			loading="lazy"
			decoding="async"
		/>
	</div>
	<?php
}

/**
 * Render the shop sidebar template part.
 *
 * @return void
 */
function diako_render_shop_sidebar() {
	if ( ! diako_shop_has_sidebar() ) {
		return;
	}

	get_template_part( 'template-parts/shop/sidebar' );
}

/**
 * Archive description HTML for shop and taxonomy pages.
 *
 * @return string
 */
function diako_get_shop_archive_description() {
	if ( is_product_category() ) {
		// The full category description is long-form SEO content rendered at the
		// bottom of the page, so the header only shows a short tagline (avoids
		// duplicate content and an oversized header).
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			$brand     = diako_get_brand_name();
			$fallbacks = array(
				'coming-soon' => sprintf(
					/* translators: %s: store brand name */
					__( 'محصولات جدید به زودی در %s.', 'diako' ),
					$brand
				),
			);

			if ( isset( $fallbacks[ $term->slug ] ) ) {
				return '<p>' . esc_html( $fallbacks[ $term->slug ] ) . '</p>';
			}

			$description = term_description( $term, 'product_cat' );

			if ( $description ) {
				return wp_kses_post( wpautop( wp_trim_words( wp_strip_all_tags( $description ), 30, '…' ) ) );
			}

			return '<p>' . esc_html(
				sprintf(
					/* translators: 1: category name, 2: store brand name */
					__( 'خرید %1$s از %2$s.', 'diako' ),
					$term->name,
					$brand
				)
			) . '</p>';
		}

		return '';
	}

	if ( is_product_tag() ) {
		$description = term_description();

		return $description ? $description : '';
	}

	if ( ! diako_is_product_search() && is_shop() ) {
		return '<p>' . esc_html(
			sprintf(
				/* translators: %s: store brand name */
				__( 'جدیدترین محصولات را از %s بخواهید.', 'diako' ),
				diako_get_brand_name()
			)
		) . '</p>';
	}

	if ( diako_is_discount_page() ) {
		$page_id = diako_get_discount_page_id();

		if ( $page_id ) {
			$page = get_post( $page_id );

			if ( $page instanceof WP_Post && $page->post_excerpt ) {
				return wpautop( esc_html( $page->post_excerpt ) );
			}
		}

		return '<p>' . esc_html__( 'بهترین پیشنهادهای امروز', 'diako' ) . '</p>';
	}

	return '';
}

/**
 * Render the shop / category archive page header.
 *
 * @return void
 */
function diako_render_shop_archive_header() {
	if ( ! apply_filters( 'woocommerce_show_page_title', true ) ) {
		return;
	}

	$term     = is_product_category() ? get_queried_object() : null;
	$thumb_id = $term instanceof WP_Term ? (int) get_term_meta( $term->term_id, 'thumbnail_id', true ) : 0;
	$desc     = diako_get_shop_archive_description();
	?>
	<div class="diako-page-header<?php echo $thumb_id ? ' diako-page-header--has-media' : ''; ?>">
		<?php if ( $thumb_id ) : ?>
			<div class="diako-page-header__media">
				<?php
				echo wp_get_attachment_image(
					$thumb_id,
					'medium',
					false,
					array(
						'class' => 'diako-page-header__image',
						'alt'   => $term instanceof WP_Term ? esc_attr( $term->name ) : '',
					)
				);
				?>
			</div>
		<?php endif; ?>
		<div class="diako-page-header__content space-y-2">
			<h1 class="diako-page-title"><?php woocommerce_page_title(); ?></h1>
			<?php if ( $desc ) : ?>
				<div class="diako-page-desc prose prose-neutral dark:prose-invert max-w-none"><?php echo wp_kses_post( $desc ); ?></div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Render the mobile shop sidebar toggle button.
 *
 * @return void
 */
function diako_render_shop_sidebar_toggle() {
	if ( ! diako_shop_has_sidebar() ) {
		return;
	}

	$is_category_archive = is_product_category();
	$label               = $is_category_archive
		? __( 'فیلترها', 'diako' )
		: __( 'دسته‌بندی و جستجو', 'diako' );
	$icon                = $is_category_archive ? 'sliders-horizontal' : 'layout-grid';
	?>
	<button
		type="button"
		class="<?php echo esc_attr( diako_button_classes( 'outline', 'sm', 'lg:hidden' ) ); ?>"
		data-shop-drawer-toggle
		aria-controls="diako-shop-drawer"
		aria-expanded="false"
	>
		<?php echo diako_lucide_icon_svg( $icon, 'h-4 w-4' ); // phpcs:ignore ?>
		<?php echo esc_html( $label ); ?>
	</button>
	<?php
}

/**
 * Whether the product has attribute values for the specs tab.
 *
 * @param WC_Product|null $product Product.
 * @return bool
 */
function diako_product_has_specs( $product = null ) {
	if ( ! $product instanceof WC_Product ) {
		global $product;
	}

	if ( ! $product instanceof WC_Product || ! $product->has_attributes() ) {
		return false;
	}

	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! $attribute instanceof WC_Product_Attribute ) {
			continue;
		}

		if ( ! empty( $attribute->get_options() ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Render the product specifications tab panel.
 *
 * @return void
 */
function diako_render_product_specs_tab() {
	global $product;

	echo '<div class="diako-product-specs">';

	if ( diako_product_has_specs( $product ) ) {
		wc_display_product_attributes( $product );
	} else {
		echo '<p class="diako-product-specs__empty text-sm leading-7 text-muted-foreground">';
		esc_html_e( 'مشخصاتی برای این محصول ثبت نشده است.', 'diako' );
		echo '</p>';
	}

	echo '</div>';
}

/**
 * Always register the specifications tab on single product pages.
 *
 * WooCommerce only adds "additional_information" when a product has attributes;
 * most catalog items do not, so we register our own tab explicitly.
 *
 * @param array<string, array<string, mixed>> $tabs Product tabs.
 * @return array<string, array<string, mixed>>
 */
function diako_register_product_specs_tab( $tabs ) {
	if ( ! is_product() ) {
		return $tabs;
	}

	unset( $tabs['additional_information'] );

	$tabs['specifications'] = array(
		'title'    => __( 'مشخصات', 'diako' ),
		'priority' => 20,
		'callback' => 'diako_render_product_specs_tab',
	);

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'diako_register_product_specs_tab', 50 );

add_filter( 'woocommerce_single_product_flexslider_enabled', '__return_false' );

add_filter(
	'woocommerce_gallery_image_size',
	function ( $size ) {
		if ( is_product() ) {
			return 'woocommerce_single';
		}

		return $size;
	}
);

add_filter(
	'woocommerce_gallery_image_html_attachment_image_params',
	static function ( $params ) {
		if ( ! is_product() ) {
			return $params;
		}

		// Keep WooCommerce's `wp-post-image` so variation image swaps can find the main image.
		$existing         = isset( $params['class'] ) ? (string) $params['class'] : '';
		$params['class']  = trim( $existing . ' diako-product-gallery__img' );

		return $params;
	}
);

/**
 * Product tags for card badges.
 *
 * @param WC_Product $product Product.
 * @param int        $limit   Maximum tags to return.
 * @return array<int, WP_Term>
 */
function diako_get_product_card_tags( $product, $limit = 3 ) {
	if ( ! $product instanceof WC_Product ) {
		return array();
	}

	$tags = get_the_terms( $product->get_id(), 'product_tag' );

	if ( ! $tags || is_wp_error( $tags ) ) {
		return array();
	}

	$limit = (int) $limit;

	if ( $limit > 0 ) {
		return array_slice( $tags, 0, $limit );
	}

	return $tags;
}

/**
 * Primary category shown on product cards.
 *
 * @param WC_Product $product Product.
 * @return WP_Term|null
 */
function diako_get_product_card_primary_category( WC_Product $product ): ?WP_Term {
	$terms = wc_get_product_terms(
		$product->get_id(),
		'product_cat',
		array(
			'orderby' => 'parent',
			'order'   => 'DESC',
		)
	);

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return null;
	}

	foreach ( $terms as $term ) {
		if ( $term instanceof WP_Term && 'uncategorized' !== $term->slug && $term->parent > 0 ) {
			return $term;
		}
	}

	$term = $terms[0];

	return ( $term instanceof WP_Term && 'uncategorized' !== $term->slug ) ? $term : null;
}

/**
 * Whether a product should be treated as out of stock on cards.
 *
 * @param WC_Product $product Product.
 * @return bool
 */
function diako_product_card_is_out_of_stock( WC_Product $product ): bool {
	if ( 'outofstock' === $product->get_stock_status() ) {
		return true;
	}

	if ( ! $product->is_in_stock() ) {
		return true;
	}

	if ( $product->is_type( 'variable' ) ) {
		$variation_ids = $product->get_children();

		if ( empty( $variation_ids ) ) {
			return true;
		}

		foreach ( $variation_ids as $variation_id ) {
			$variation = wc_get_product( $variation_id );

			if ( $variation && $variation->is_in_stock() ) {
				return false;
			}
		}

		return true;
	}

	return false;
}

/**
 * Stock label for product cards.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function diako_get_product_card_stock_label( WC_Product $product ): string {
	if ( ! $product->managing_stock() && 'instock' === $product->get_stock_status() ) {
		return '';
	}

	switch ( $product->get_stock_status() ) {
		case 'outofstock':
			return __( 'ناموجود', 'diako' );
		case 'onbackorder':
			return __( 'پیش‌سفارش', 'diako' );
		default:
			if ( $product->managing_stock() && $product->get_stock_quantity() > 0 && $product->get_stock_quantity() <= 3 ) {
				return __( 'موجودی محدود', 'diako' );
			}
			return '';
	}
}

/**
 * CTA label for product cards.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function diako_get_product_card_cta_label( WC_Product $product ): string {
	if ( diako_product_card_is_out_of_stock( $product ) ) {
		return diako_stock_notify_label();
	}

	if ( $product->is_type( 'variable' ) ) {
		return __( 'انتخاب و خرید', 'diako' );
	}

	if ( $product->is_type( 'simple' ) && $product->is_purchasable() ) {
		return __( 'افزودن به سبد', 'diako' );
	}

	return __( 'مشاهده و خرید', 'diako' );
}

/**
 * Price HTML shown on product cards.
 *
 * Variable products show the lowest price prefixed with "از" instead of a range.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function diako_get_product_card_price_html( WC_Product $product ): string {
	if ( ! $product->is_type( 'variable' ) ) {
		return $product->get_price_html();
	}

	$prices = $product->get_variation_prices( true );

	if ( empty( $prices['price'] ) ) {
		return apply_filters( 'woocommerce_variable_empty_price_html', '', $product );
	}

	$min_variation_id = (int) key( $prices['price'] );
	$min_price        = (float) $prices['price'][ $min_variation_id ];
	$min_regular      = (float) $prices['regular_price'][ $min_variation_id ];

	if ( $min_price <= 0 && '' === $product->get_price() ) {
		return apply_filters( 'woocommerce_variable_empty_price_html', '', $product );
	}

	$classes = 'price diako-product-card__price--from';

	if ( $min_price < $min_regular ) {
		$classes .= ' diako-product-card__price--from-sale';

		// Same order as simple products: regular (del) on top, sale (ins) below.
		return sprintf(
			'<span class="%1$s"><del aria-hidden="true">%2$s</del> <span class="screen-reader-text">%3$s</span><ins aria-hidden="true">%4$s %5$s</ins><span class="screen-reader-text">%6$s</span></span>',
			esc_attr( $classes ),
			wp_kses_post( wc_price( $min_regular ) ),
			esc_html(
				sprintf(
					/* translators: %s: original price */
					__( 'قیمت اصلی: %s بود.', 'diako' ),
					wp_strip_all_tags( wc_price( $min_regular ) )
				)
			),
			esc_html__( 'از', 'diako' ),
			wp_kses_post( wc_price( $min_price ) ),
			esc_html(
				sprintf(
					/* translators: %s: current price */
					__( 'قیمت فعلی: %s.', 'diako' ),
					wp_strip_all_tags( wc_price( $min_price ) )
				)
			)
		);
	}

	return sprintf(
		'<span class="%1$s">%2$s %3$s</span>',
		esc_attr( $classes ),
		esc_html__( 'از', 'diako' ),
		wp_kses_post( wc_price( $min_price ) )
	);
}

/**
 * Render product card status badges over the media area.
 *
 * @param WC_Product|null $product Product.
 * @return void
 */
function diako_render_product_card_badges( $product = null ) {
	if ( ! $product instanceof WC_Product ) {
		global $product;
	}

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$sale_label  = diako_get_product_discount_badge_label( $product );
	$stock_label = diako_get_product_card_stock_label( $product );
	$has_badges  = '' !== $sale_label || $product->is_featured() || '' !== $stock_label;

	if ( ! $has_badges ) {
		return;
	}
	?>
	<div class="diako-product-card__badges">
		<?php if ( '' !== $sale_label ) : ?>
			<span class="diako-product-card__badge diako-product-card__badge--sale"><?php echo esc_html( $sale_label ); ?></span>
		<?php endif; ?>

		<?php if ( $product->is_featured() ) : ?>
			<span class="diako-product-card__badge diako-product-card__badge--featured"><?php esc_html_e( 'ویژه', 'diako' ); ?></span>
		<?php endif; ?>

		<?php if ( '' !== $stock_label ) : ?>
			<span class="diako-product-card__badge diako-product-card__badge--stock"><?php echo esc_html( $stock_label ); ?></span>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render product card taxonomy / attribute meta row.
 *
 * @param WC_Product|null $product Product.
 * @return void
 */
function diako_render_product_card_meta( $product = null ) {
	if ( ! $product instanceof WC_Product ) {
		global $product;
	}

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$category = diako_get_product_card_primary_category( $product );

	if ( ! $category instanceof WP_Term ) {
		return;
	}
	?>
	<div class="diako-product-card__meta">
		<div class="diako-product-card__meta-start">
			<a class="diako-product-card__chip diako-product-card__chip--category" href="<?php echo esc_url( get_term_link( $category ) ); ?>">
				<?php echo esc_html( $category->name ); ?>
			</a>
		</div>
	</div>
	<?php
}

/**
 * Render product tag badges.
 *
 * @param WC_Product|null     $product Product.
 * @param int                 $limit   Maximum tags (0 for all).
 * @param array<string,mixed> $args    Optional wrapper/tag classes.
 * @return void
 */
function diako_render_product_tags( $product = null, $limit = 2, $args = array() ) {
	if ( ! $product instanceof WC_Product ) {
		global $product;
	}

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$args = wp_parse_args(
		$args,
		array(
			'wrapper_class' => 'diako-product-tags',
			'tag_class'     => 'diako-product-tag',
		)
	);

	$tags = diako_get_product_card_tags( $product, $limit );

	if ( empty( $tags ) ) {
		return;
	}
	?>
	<div class="<?php echo esc_attr( $args['wrapper_class'] ); ?>">
		<?php foreach ( $tags as $tag ) : ?>
			<a class="<?php echo esc_attr( $args['tag_class'] ); ?>" href="<?php echo esc_url( get_term_link( $tag ) ); ?>">
				<?php echo esc_html( $tag->name ); ?>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Render compact tag list on product cards.
 *
 * @param WC_Product|null $product Product.
 * @param int             $limit   Maximum tags.
 * @return void
 */
function diako_render_product_card_tags( $product = null, $limit = 2 ) {
	diako_render_product_tags(
		$product,
		$limit,
		array(
			'wrapper_class' => 'diako-product-tags diako-product-card__tags',
		)
	);
}

/**
 * Render tag badges on the single product page (after short description).
 *
 * @param WC_Product|null $product Product.
 * @return void
 */
function diako_render_single_product_tags( $product = null ): void {
	diako_render_product_tags(
		$product,
		0,
		array(
			'wrapper_class' => 'diako-product-tags diako-single-product__tags',
		)
	);
}
add_action( 'woocommerce_single_product_summary', 'diako_render_single_product_tags', 21 );

/**
 * Render rating summary on product cards.
 *
 * @param WC_Product|null $product Product.
 * @return void
 */
function diako_render_product_card_rating( $product = null ) {
	if ( ! $product instanceof WC_Product ) {
		global $product;
	}

	if ( ! $product instanceof WC_Product || ! wc_review_ratings_enabled() ) {
		return;
	}

	$rating = (float) $product->get_average_rating();
	$count  = (int) $product->get_rating_count();

	if ( $count <= 0 || $rating <= 0 ) {
		return;
	}
	?>
	<div class="diako-product-card__rating">
		<?php echo diako_render_star_rating( $rating, array( 'size' => 'sm', 'class' => 'diako-product-card__stars' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<span class="diako-product-card__rating-count">(<?php echo esc_html( diako_number( $count ) ); ?>)</span>
	</div>
	<?php
}

/**
 * Render the sale discount badge on product cards.
 *
 * @param WC_Product|null $product Product.
 * @return void
 */
function diako_render_product_card_sale_badge( $product = null ) {
	diako_render_product_card_badges( $product );
}

/**
 * Discount percentage for a product on sale.
 *
 * @param WC_Product $product Product.
 * @return int
 */
function diako_get_product_discount_percentage( $product ) {
	if ( ! $product instanceof WC_Product || ! $product->is_on_sale() ) {
		return 0;
	}

	if ( $product->is_type( 'variable' ) ) {
		$percentages = array();

		foreach ( $product->get_children() as $child_id ) {
			$variation = wc_get_product( $child_id );

			if ( ! $variation instanceof WC_Product ) {
				continue;
			}

			$percentage = diako_get_product_discount_percentage( $variation );

			if ( $percentage > 0 ) {
				$percentages[] = $percentage;
			}
		}

		return empty( $percentages ) ? 0 : (int) max( $percentages );
	}

	$regular = (float) $product->get_regular_price();
	$sale    = (float) $product->get_sale_price();

	if ( $regular <= 0 ) {
		return 0;
	}

	if ( $sale <= 0 ) {
		$sale = (float) $product->get_price();
	}

	if ( $sale <= 0 || $sale >= $regular ) {
		return 0;
	}

	$percentage = (int) round( ( ( $regular - $sale ) / $regular ) * 100 );

	if ( $percentage <= 0 ) {
		return 1;
	}

	return $percentage;
}

/**
 * Formatted discount badge label for product cards.
 *
 * @param WC_Product|null $product Product.
 * @return string
 */
function diako_get_product_discount_badge_label( $product = null ) {
	if ( ! $product instanceof WC_Product ) {
		global $product;
	}

	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	$percentage = diako_get_product_discount_percentage( $product );

	if ( $percentage <= 0 ) {
		return '';
	}

	return diako_number( $percentage ) . '٪';
}

/**
 * Scheduled sale data for a single product or variation.
 *
 * @param WC_Product $product Product.
 * @return array{mode: string, timestamp: int}|null
 */
function diako_get_product_sale_schedule_for_product( WC_Product $product ): ?array {
	$now = time();

	$from = $product->get_date_on_sale_from();
	if ( $from ) {
		$from_ts = (int) $from->getTimestamp();

		if ( $from_ts > $now ) {
			return array(
				'mode'      => 'starts',
				'timestamp' => $from_ts,
			);
		}
	}

	$to = $product->get_date_on_sale_to();
	if ( $to ) {
		$to_ts = (int) $to->getTimestamp();

		if ( $to_ts > $now ) {
			return array(
				'mode'      => 'ends',
				'timestamp' => $to_ts,
			);
		}
	}

	return null;
}

/**
 * Pick the most relevant scheduled sale countdown for a product.
 *
 * @param WC_Product|null $product Product.
 * @return array{mode: string, timestamp: int}|null
 */
function diako_get_product_sale_schedule( $product = null ): ?array {
	if ( ! $product instanceof WC_Product ) {
		global $product;
	}

	if ( ! $product instanceof WC_Product ) {
		return null;
	}

	$schedules = array();

	$self_schedule = diako_get_product_sale_schedule_for_product( $product );
	if ( $self_schedule ) {
		$schedules[] = $self_schedule;
	}

	if ( $product->is_type( 'variable' ) ) {
		foreach ( $product->get_visible_children() as $child_id ) {
			$variation = wc_get_product( $child_id );

			if ( ! $variation instanceof WC_Product ) {
				continue;
			}

			$variation_schedule = diako_get_product_sale_schedule_for_product( $variation );

			if ( $variation_schedule ) {
				$schedules[] = $variation_schedule;
			}
		}
	}

	if ( empty( $schedules ) ) {
		return null;
	}

	$starts = array_values(
		array_filter(
			$schedules,
			static function ( $schedule ) {
				return 'starts' === $schedule['mode'];
			}
		)
	);

	if ( ! empty( $starts ) ) {
		usort(
			$starts,
			static function ( $left, $right ) {
				return $left['timestamp'] <=> $right['timestamp'];
			}
		);

		return $starts[0];
	}

	usort(
		$schedules,
		static function ( $left, $right ) {
			return $left['timestamp'] <=> $right['timestamp'];
		}
	);

	return $schedules[0];
}

/**
 * Format remaining sale schedule seconds for display.
 *
 * @param int $seconds Remaining seconds.
 * @return string
 */
function diako_format_sale_countdown_remaining( int $seconds ): string {
	if ( $seconds <= 0 ) {
		return '';
	}

	$days      = (int) floor( $seconds / DAY_IN_SECONDS );
	$remainder = $seconds % DAY_IN_SECONDS;
	$hours     = (int) floor( $remainder / HOUR_IN_SECONDS );
	$remainder = $remainder % HOUR_IN_SECONDS;
	$minutes   = (int) floor( $remainder / MINUTE_IN_SECONDS );
	$secs      = $remainder % MINUTE_IN_SECONDS;
	$time      = sprintf( '%02d:%02d:%02d', $hours, $minutes, $secs );

	if ( $days > 0 ) {
		return sprintf(
			/* translators: 1: days count, 2: H:M:S time */
			__( '%1$s روز %2$s', 'diako' ),
			diako_number( $days ),
			diako_number( $time )
		);
	}

	return diako_number( $time );
}

/**
 * Render a live sale countdown for scheduled products.
 *
 * @param WC_Product|null $product Product.
 * @param string          $context Display context: card|single.
 * @return void
 */
function diako_render_product_sale_countdown( $product = null, string $context = 'card' ): void {
	if ( ! $product instanceof WC_Product ) {
		global $product;
	}

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$schedule = diako_get_product_sale_schedule( $product );

	if ( ! $schedule ) {
		return;
	}

	$remaining = max( 0, (int) $schedule['timestamp'] - time() );

	if ( $remaining <= 0 ) {
		return;
	}

	$label = 'starts' === $schedule['mode']
		? __( 'مانده تا شروع تخفیف', 'diako' )
		: __( 'مانده تا پایان تخفیف', 'diako' );

	$context_class = 'card' === $context ? 'diako-sale-countdown--card' : 'diako-sale-countdown--single';
	?>
	<div
		class="diako-sale-countdown <?php echo esc_attr( $context_class ); ?>"
		data-sale-countdown
		data-sale-countdown-mode="<?php echo esc_attr( $schedule['mode'] ); ?>"
		data-sale-countdown-until="<?php echo esc_attr( (string) $schedule['timestamp'] ); ?>"
		data-sale-countdown-label-ends="<?php echo esc_attr( __( 'مانده تا پایان تخفیف', 'diako' ) ); ?>"
		data-sale-countdown-label-starts="<?php echo esc_attr( __( 'مانده تا شروع تخفیف', 'diako' ) ); ?>"
	>
		<?php echo diako_lucide_icon_svg( 'clock', 'diako-sale-countdown__icon h-3.5 w-3.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<div class="diako-sale-countdown__content">
			<span class="diako-sale-countdown__label"><?php echo esc_html( $label ); ?></span>
			<span class="diako-sale-countdown__time" data-sale-countdown-time><?php echo esc_html( diako_format_sale_countdown_remaining( $remaining ) ); ?></span>
		</div>
	</div>
	<?php
}

/**
 * Expose scheduled sale data on variable product variations.
 *
 * @param array<string,mixed> $data      Variation data.
 * @param WC_Product          $product   Parent product.
 * @param WC_Product_Variation $variation Variation.
 * @return array<string,mixed>
 */
function diako_add_variation_sale_countdown_data( array $data, WC_Product $product, WC_Product_Variation $variation ): array {
	unset( $product );

	$schedule = diako_get_product_sale_schedule_for_product( $variation );

	if ( ! $variation->is_in_stock() ) {
		$data['price_html']           = '';
		$data['diako_sale_countdown'] = null;
	} else {
		$data['diako_sale_countdown'] = $schedule
			? array(
				'mode'  => $schedule['mode'],
				'until' => $schedule['timestamp'],
			)
			: null;
	}

	return $data;
}
add_filter( 'woocommerce_available_variation', 'diako_add_variation_sale_countdown_data', 10, 3 );

/**
 * Products per row on shop/archive.
 */
function diako_loop_columns(): int {
	return diako_shop_has_sidebar() ? 3 : 4;
}
add_filter( 'loop_shop_columns', 'diako_loop_columns' );

/**
 * Products per page.
 */
function diako_products_per_page(): int {
	return 12;
}
add_filter( 'loop_shop_per_page', 'diako_products_per_page' );

/**
 * Loop/archive add to cart text.
 */
function diako_loop_add_to_cart_text(): string {
	return __( 'مشاهده و خرید', 'diako' );
}
add_filter( 'woocommerce_product_add_to_cart_text', 'diako_loop_add_to_cart_text' );

/**
 * Single product add to cart text.
 */
function diako_single_add_to_cart_text(): string {
	return __( 'افزودن به سبد خرید', 'diako' );
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'diako_single_add_to_cart_text' );

/**
 * CSS classes for the single-product add to cart button.
 *
 * @return string
 */
function diako_single_add_to_cart_button_classes() {
	return diako_button_classes( 'default', 'lg', 'single_add_to_cart_button' );
}

/**
 * Simplify quantity field args on single product pages.
 *
 * @param array<string, mixed> $args    Quantity input args.
 * @param WC_Product|null      $product Product object.
 * @return array<string, mixed>
 */
function diako_single_product_quantity_input_args( array $args, $product ): array {
	if ( ! is_product() ) {
		return $args;
	}

	$args['product_name'] = '';

	return $args;
}
add_filter( 'woocommerce_quantity_input_args', 'diako_single_product_quantity_input_args', 10, 2 );

/**
 * Product price block for the single-product cart area.
 *
 * @param WC_Product|null $product Product.
 * @return void
 */
function diako_render_single_product_cart_price( $product = null ): void {
	if ( ! $product instanceof WC_Product ) {
		global $product;
	}

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	if ( ! $product->is_in_stock() ) {
		return;
	}

	$is_variable = $product->is_type( 'variable' );
	$price_html  = $is_variable
		? diako_get_product_card_price_html( $product )
		: $product->get_price_html();

	if ( ! $price_html ) {
		return;
	}

	$classes = 'diako-add-to-cart-form__price';

	if ( $is_variable ) {
		$classes .= ' diako-add-to-cart-form__price--range';
	}

	$data_attr = $is_variable
		? sprintf( ' data-diako-default-price-html="%s"', esc_attr( $price_html ) )
		: '';

	// Variable "از" markup already includes a .price wrapper; simple prices need one.
	if ( $is_variable ) {
		printf(
			'<div class="%1$s"%2$s>%3$s</div>',
			esc_attr( $classes ),
			$data_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_kses_post( $price_html )
		);
	} else {
		printf(
			'<div class="%1$s"%2$s><p class="price">%3$s</p></div>',
			esc_attr( $classes ),
			$data_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_kses_post( $price_html )
		);
	}

	diako_render_product_sale_countdown( $product, 'single' );
}

/**
 * Open the variable-product cart wrapper (price + variation + quantity).
 */
function diako_single_product_open_cart_form(): void {
	global $product;

	echo '<div class="diako-add-to-cart-form diako-add-to-cart-form--variable">';

	if ( $product instanceof WC_Product && $product->is_type( 'variable' ) ) {
		diako_render_single_product_cart_price( $product );
	}
}

/**
 * Close the variable-product cart wrapper.
 */
function diako_single_product_close_cart_form(): void {
	echo '</div>';
}

/**
 * Persian labels for My Account navigation.
 *
 * @param array<string, string> $items Menu items.
 * @return array<string, string>
 */
function diako_account_menu_items( array $items ): array {
	$labels = array(
		'dashboard'       => __( 'پیشخوان', 'diako' ),
		'orders'          => __( 'سفارش‌ها', 'diako' ),
		'downloads'       => __( 'دانلودها', 'diako' ),
		'favorites'       => __( 'علاقه‌مندی‌ها', 'diako' ),
		'edit-address'    => __( 'آدرس‌ها', 'diako' ),
		'payment-methods' => __( 'روش‌های پرداخت', 'diako' ),
		'edit-account'    => __( 'جزئیات حساب', 'diako' ),
		'customer-logout' => __( 'خروج', 'diako' ),
	);

	foreach ( $labels as $endpoint => $label ) {
		if ( isset( $items[ $endpoint ] ) ) {
			$items[ $endpoint ] = $label;
		}
	}

	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'diako_account_menu_items' );

/**
 * Lucide icon slug for a My Account endpoint.
 *
 * @param string $endpoint Endpoint key.
 * @return string
 */
function diako_get_account_endpoint_icon( string $endpoint ): string {
	$icons = array(
		'dashboard'       => 'layout-grid',
		'orders'          => 'package',
		'downloads'       => 'download',
		'favorites'       => 'heart',
		'edit-address'    => 'map-pin',
		'payment-methods' => 'credit-card',
		'edit-account'    => 'user',
		'customer-logout' => 'log-out',
	);

	return $icons[ $endpoint ] ?? 'user';
}

/**
 * My Account page header.
 *
 * @return void
 */
function diako_render_account_page_header() {
	if ( ! is_user_logged_in() ) {
		?>
		<div class="diako-page-header diako-account-header">
			<h1 class="diako-page-title"><?php esc_html_e( 'ورود / ثبت‌نام', 'diako' ); ?></h1>
			<p class="diako-page-desc"><?php esc_html_e( 'برای پیگیری سفارش‌ها وارد حساب کاربری خود شوید.', 'diako' ); ?></p>
		</div>
		<?php
		return;
	}

	$user = wp_get_current_user();
	?>
	<div class="diako-page-header diako-account-header">
		<h1 class="diako-page-title"><?php esc_html_e( 'حساب کاربری', 'diako' ); ?></h1>
		<p class="diako-page-desc">
			<?php
			printf(
				/* translators: %s: user display name */
				esc_html__( 'سلام %s، از پیشخوان حساب خود استفاده کنید.', 'diako' ),
				esc_html( $user->display_name )
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Quick links on the account dashboard.
 *
 * @return void
 */
function diako_render_account_dashboard_quick_links() {
	$cards = array(
		array(
			'endpoint' => 'orders',
			'title'    => __( 'سفارش‌های من', 'diako' ),
			'desc'     => __( 'پیگیری و مشاهده سفارش‌های اخیر', 'diako' ),
			'icon'     => 'package',
		),
		array(
			'endpoint' => 'favorites',
			'title'    => __( 'علاقه‌مندی‌ها', 'diako' ),
			'desc'     => __( 'محصولات ذخیره‌شده برای خرید بعدی', 'diako' ),
			'icon'     => 'heart',
		),
		array(
			'endpoint' => 'edit-address',
			'title'    => __( 'آدرس‌ها', 'diako' ),
			'desc'     => __( 'مدیریت آدرس صورتحساب و ارسال', 'diako' ),
			'icon'     => 'map-pin',
		),
		array(
			'endpoint' => 'edit-account',
			'title'    => __( 'جزئیات حساب', 'diako' ),
			'desc'     => __( 'ویرایش نام، ایمیل و رمز عبور', 'diako' ),
			'icon'     => 'user',
		),
	);

	?>
	<div class="diako-account-quick-links">
		<?php foreach ( $cards as $card ) : ?>
			<?php if ( ! wc_get_account_menu_items() || ! array_key_exists( $card['endpoint'], wc_get_account_menu_items() ) ) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<a class="diako-account-quick-link" href="<?php echo esc_url( wc_get_account_endpoint_url( $card['endpoint'] ) ); ?>">
				<span class="diako-account-quick-link__icon" aria-hidden="true">
					<?php echo diako_lucide_icon_svg( $card['icon'], 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>
				<span class="diako-account-quick-link__body">
					<span class="diako-account-quick-link__title"><?php echo esc_html( $card['title'] ); ?></span>
					<span class="diako-account-quick-link__desc"><?php echo esc_html( $card['desc'] ); ?></span>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
}

add_filter(
	'woocommerce_show_page_title',
	function ( $show ) {
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			return is_user_logged_in() ? false : $show;
		}

		if ( function_exists( 'is_cart' ) && is_cart() ) {
			return false;
		}

		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			return false;
		}

		if ( diako_is_checkout_page() ) {
			return false;
		}

		if ( function_exists( 'diako_is_track_order_page' ) && diako_is_track_order_page() ) {
			return false;
		}

		return $show;
	}
);

add_filter(
	'body_class',
	function ( $classes ) {
		if ( function_exists( 'diako_is_login_default_layout_page' ) && diako_is_login_default_layout_page() ) {
			$classes[] = 'diako-login-default';
		}

		if ( function_exists( 'diako_should_load_storefront_theme_assets' ) && ! diako_should_load_storefront_theme_assets() ) {
			return $classes;
		}

		if ( function_exists( 'is_account_page' ) && is_account_page() && is_user_logged_in() ) {
			$classes[] = 'diako-my-account';
		}

		if ( function_exists( 'is_cart' ) && is_cart() ) {
			$classes[] = 'diako-cart-page';
		}

		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			$classes[] = 'diako-checkout-page';
		}

		if ( diako_is_checkout_page() && ! in_array( 'diako-checkout-page', $classes, true ) ) {
			$classes[] = 'diako-checkout-page';
		}

		if ( function_exists( 'diako_is_edit_address_page' ) && diako_is_edit_address_page() ) {
			$classes[] = 'diako-edit-address-page';
		}

		if ( function_exists( 'is_wc_endpoint_url' ) && is_account_page() && is_wc_endpoint_url( 'edit-account' ) ) {
			$classes[] = 'diako-edit-account-page';
		}

		return $classes;
	},
	20
);

/**
 * Cart page header.
 *
 * @return void
 */
function diako_render_cart_page_header() {
	$count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
	?>
	<div class="diako-page-header diako-cart-header">
		<h1 class="diako-page-title"><?php esc_html_e( 'سبد خرید', 'diako' ); ?></h1>
		<p
			class="diako-page-desc"
			id="diako-cart-header-desc"
			data-empty-text="<?php esc_attr_e( 'محصولات انتخاب‌شده خود را بررسی و تکمیل کنید.', 'diako' ); ?>"
			data-count-text="<?php esc_attr_e( '%d محصول در سبد خرید شماست.', 'diako' ); ?>"
		>
			<?php
			if ( $count > 0 ) {
				printf(
					/* translators: %d: number of items in cart */
					esc_html( _n( '%d محصول در سبد خرید شماست.', '%d محصول در سبد خرید شماست.', $count, 'diako' ) ),
					(int) $count
				);
			} else {
				esc_html_e( 'محصولات انتخاب‌شده خود را بررسی و تکمیل کنید.', 'diako' );
			}
			?>
		</p>
	</div>
	<?php
}

/**
 * Checkout page header.
 *
 * @return void
 */
function diako_render_checkout_page_header() {
	$count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
	?>
	<div class="diako-page-header diako-checkout-header">
		<h1 class="diako-page-title"><?php esc_html_e( 'تسویه حساب', 'diako' ); ?></h1>
		<p class="diako-page-desc">
			<?php
			if ( $count > 0 ) {
				printf(
					/* translators: %d: number of items in cart */
					esc_html( _n( 'تکمیل سفارش %d محصول', 'تکمیل سفارش %d محصول', $count, 'diako' ) ),
					(int) $count
				);
			} else {
				esc_html_e( 'اطلاعات خود را وارد کنید و سفارش را نهایی کنید.', 'diako' );
			}
			?>
		</p>
	</div>
	<?php
}

/**
 * Thank-you page header.
 *
 * @return void
 */
function diako_render_checkout_thankyou_header() {
	?>
	<div class="diako-page-header diako-checkout-header">
		<h1 class="diako-page-title"><?php esc_html_e( 'سفارش ثبت شد', 'diako' ); ?></h1>
		<p class="diako-page-desc"><?php esc_html_e( 'از خرید شما سپاسگزاریم.', 'diako' ); ?></p>
	</div>
	<?php
}

/**
 * Whether the current request is the WooCommerce checkout page.
 *
 * @return bool
 */
function diako_is_checkout_page(): bool {
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return false;
	}

	$checkout_id = wc_get_page_id( 'checkout' );

	if ( $checkout_id <= 0 ) {
		return false;
	}

	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return true;
	}

	return is_page( $checkout_id ) || (int) get_queried_object_id() === (int) $checkout_id;
}

/**
 * Whether the current request is the checkout thank-you screen.
 *
 * @return bool
 */
function diako_is_checkout_thankyou_page(): bool {
	return diako_is_checkout_page()
		&& function_exists( 'is_wc_endpoint_url' )
		&& is_wc_endpoint_url( 'order-received' );
}

/**
 * Whether the current request is the pay-for-order screen.
 *
 * @return bool
 */
function diako_is_checkout_order_pay_page(): bool {
	return diako_is_checkout_page()
		&& function_exists( 'is_wc_endpoint_url' )
		&& is_wc_endpoint_url( 'order-pay' );
}

/**
 * Order pay page header.
 *
 * @return void
 */
function diako_render_checkout_order_pay_header(): void {
	global $wp;

	$order_id = isset( $wp->query_vars['order-pay'] ) ? absint( $wp->query_vars['order-pay'] ) : 0;
	$order    = $order_id ? wc_get_order( $order_id ) : false;
	?>
	<div class="diako-page-header diako-checkout-header">
		<h1 class="diako-page-title"><?php esc_html_e( 'پرداخت سفارش', 'diako' ); ?></h1>
		<p class="diako-page-desc">
			<?php
			if ( $order instanceof WC_Order ) {
				printf(
					/* translators: %s: order number */
					esc_html__( 'برای تکمیل سفارش %s، روش پرداخت را انتخاب کنید.', 'diako' ),
					esc_html( diako_number( $order->get_order_number() ) )
				);
			} else {
				esc_html_e( 'برای تکمیل سفارش، وارد شوید و پرداخت را انجام دهید.', 'diako' );
			}
			?>
		</p>
	</div>
	<?php
}

/**
 * Render checkout / thank-you page layout.
 *
 * @return void
 */
function diako_render_checkout_page_layout() {
	$is_thankyou  = diako_is_checkout_thankyou_page();
	$is_order_pay = diako_is_checkout_order_pay_page();
	$page_classes = array( 'diako-checkout-page' );

	if ( $is_thankyou ) {
		$page_classes[] = 'diako-checkout-page--thankyou';
	}

	if ( $is_order_pay ) {
		$page_classes[] = 'diako-checkout-page--order-pay';
	}
	?>
	<div class="diako-page woocommerce-page-wrap <?php echo esc_attr( implode( ' ', $page_classes ) ); ?>">
		<div class="diako-checkout">
			<?php
			if ( $is_thankyou ) {
				diako_render_checkout_thankyou_header();
			} elseif ( $is_order_pay ) {
				diako_render_checkout_order_pay_header();
			} else {
				diako_render_checkout_page_header();
			}

			while ( have_posts() ) :
				the_post();
				the_content();
			endwhile;
			?>
		</div>
	</div>
	<?php
}

add_action(
	'init',
	function () {
		remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', 10 );
		add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display', 20 );
	},
	30
);

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( function_exists( 'is_cart' ) && is_cart() ) {
			wp_enqueue_script( 'wc-cart' );
		}

		if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {
			wp_enqueue_script( 'wc-checkout' );
		}

		if ( diako_is_checkout_page() && ! diako_is_checkout_thankyou_page() ) {
			wp_enqueue_script( 'wc-checkout' );
		}
	},
	20
);

/**
 * Cart / checkout discount row with label and amount in a flex row.
 *
 * @param string           $code   Coupon code.
 * @param WC_Coupon|string $coupon Coupon instance or code.
 */
function diako_render_cart_discount_row( $code, $coupon ) {
	if ( is_string( $coupon ) ) {
		$coupon = new WC_Coupon( $coupon );
	}

	$label = wc_cart_totals_coupon_label( $coupon, false );
	?>
	<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
		<td colspan="2" class="diako-coupon-totals-cell" data-title="<?php echo esc_attr( $label ); ?>">
			<div class="diako-coupon-totals__row">
				<div class="diako-coupon-totals__label">
					<span class="diako-coupon-totals__label-text"><?php esc_html_e( 'کد تخفیف', 'diako' ); ?></span>
					<span class="diako-coupon-totals__code"><?php echo esc_html( is_a( $coupon, 'WC_Coupon' ) ? $coupon->get_code() : $code ); ?></span>
				</div>
				<div class="diako-coupon-totals__value"><?php wc_cart_totals_coupon_html( $coupon ); ?></div>
			</div>
		</td>
	</tr>
	<?php
}

/**
 * Coupon remove control in cart/checkout totals (icon instead of [Remove] text).
 *
 * @param string         $coupon_html           Full coupon row HTML.
 * @param WC_Coupon|string $coupon              Coupon object or code.
 * @param string         $discount_amount_html  Formatted discount amount.
 * @return string
 */
function diako_cart_totals_coupon_html( $coupon_html, $coupon, $discount_amount_html ) {
	if ( is_string( $coupon ) ) {
		$coupon = new WC_Coupon( $coupon );
	}

	if ( ! is_a( $coupon, 'WC_Coupon' ) ) {
		return $coupon_html;
	}

	$checkout_context = defined( 'WOOCOMMERCE_CHECKOUT' ) && WOOCOMMERCE_CHECKOUT;
	$remove_url       = add_query_arg(
		'remove_coupon',
		rawurlencode( $coupon->get_code() ),
		$checkout_context ? wc_get_checkout_url() : wc_get_cart_url()
	);

	$remove_link = sprintf(
		'<a role="button" aria-label="%1$s" href="%2$s" class="woocommerce-remove-coupon diako-coupon-remove" data-coupon="%3$s"><span class="sr-only">%4$s</span></a>',
		esc_attr(
			sprintf(
				/* translators: %s: coupon code */
				__( 'Remove %s coupon', 'woocommerce' ),
				$coupon->get_code()
			)
		),
		esc_url( $remove_url ),
		esc_attr( $coupon->get_code() ),
		esc_html__( 'حذف کد تخفیف', 'diako' )
	);

	$discount_amount_html = diako_normalize_price_html( diako_filter_persian_digits_string( $discount_amount_html ) );

	return sprintf(
		'<span class="diako-coupon-amount">%s</span>%s',
		$discount_amount_html,
		$remove_link
	);
}
add_filter( 'woocommerce_cart_totals_coupon_html', 'diako_cart_totals_coupon_html', 10, 3 );
