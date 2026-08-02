<?php
/**
 * Header product search (AJAX).
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product search results URL.
 *
 * @param string $term Search query.
 * @return string
 */
function diako_get_product_search_url( string $term = '' ): string {
	$args = array(
		'post_type' => 'product',
	);

	if ( '' !== $term ) {
		$args['s'] = $term;
	}

	return esc_url( add_query_arg( $args, home_url( '/' ) ) );
}

/**
 * Header search toggle button.
 */
function diako_render_header_search_toggle(): void {
	?>
	<button
		type="button"
		class="<?php echo esc_attr( diako_button_classes( 'ghost', 'icon' ) ); ?>"
		data-header-search-toggle
		aria-controls="diako-header-search-panel"
		aria-expanded="false"
		aria-label="<?php esc_attr_e( 'جستجو در محصولات', 'diako' ); ?>"
	>
		<?php echo diako_lucide_icon_svg( 'search', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>
	<?php
}

/**
 * Full-screen search overlay (dimmed backdrop + panel under header).
 */
function diako_render_header_search_overlay(): void {
	$input_id = 'diako-header-search-input';
	?>
	<div
		class="diako-header-search-overlay"
		data-header-search-overlay
		role="dialog"
		aria-modal="true"
		aria-label="<?php esc_attr_e( 'جستجو در محصولات', 'diako' ); ?>"
		hidden
	>
		<div
			class="diako-header-search__backdrop"
			data-header-search-backdrop
			aria-hidden="true"
		></div>
		<div
			id="diako-header-search-panel"
			class="diako-header-search__panel"
			data-header-search-panel
		>
		<div class="diako-header-search__inner container">
			<form
				role="search"
				method="get"
				class="diako-header-search__form"
				action="<?php echo esc_url( home_url( '/' ) ); ?>"
				data-header-search-form
			>
				<label class="sr-only" for="<?php echo esc_attr( $input_id ); ?>">
					<?php esc_html_e( 'جستجو در محصولات', 'diako' ); ?>
				</label>
				<div class="diako-header-search__bar">
					<div class="diako-header-search__field">
						<span class="diako-header-search__field-icon" aria-hidden="true">
							<?php echo diako_lucide_icon_svg( 'search', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<input
							type="search"
							id="<?php echo esc_attr( $input_id ); ?>"
							name="s"
							class="diako-header-search__input"
							placeholder="<?php esc_attr_e( 'جستجوی محصول…', 'diako' ); ?>"
							autocomplete="off"
							data-header-search-input
						/>
						<input type="hidden" name="post_type" value="product" />
					</div>
					<div class="diako-header-search__actions">
						<button
							type="submit"
							class="<?php echo esc_attr( diako_button_classes( 'default', 'default', 'diako-header-search__submit' ) ); ?>"
							aria-label="<?php esc_attr_e( 'جستجو', 'diako' ); ?>"
						>
							<?php echo diako_lucide_icon_svg( 'search', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span class="diako-header-search__submit-label"><?php esc_html_e( 'جستجو', 'diako' ); ?></span>
						</button>
						<button
							type="button"
							class="diako-header-search__close"
							data-header-search-close
							aria-label="<?php esc_attr_e( 'بستن جستجو', 'diako' ); ?>"
						>
							<?php echo diako_lucide_icon_svg( 'x', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
					</div>
				</div>
			</form>

			<div
				class="diako-header-search__results"
				data-header-search-results
				aria-live="polite"
				aria-relevant="additions text"
				hidden
			></div>
		</div>
	</div>
	</div>
	<?php
}

/**
 * @deprecated 1.2.0 Use diako_render_header_search_overlay().
 */
function diako_render_header_search_panel(): void {
	diako_render_header_search_overlay();
}

/**
 * AJAX: live product search.
 */
function diako_ajax_product_search(): void {
	check_ajax_referer( 'diako_search', 'nonce' );

	if ( diako_rate_limit_block( 'product_search', 30, 900 ) ) {
		diako_rate_limit_json_error();
	}

	$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
	$term = trim( $term );

	if ( strlen( $term ) < 2 ) {
		wp_send_json_success(
			array(
				'products'     => array(),
				'total'        => 0,
				'view_all_url' => '',
			)
		);
	}

	$query = new WP_Query(
		diako_apply_instock_first_to_product_query_args(
			array(
				'post_type'              => 'product',
				'post_status'            => 'publish',
				's'                      => $term,
				'posts_per_page'         => 8,
				'no_found_rows'          => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		)
	);

	$products = array();

	if ( ! function_exists( 'wc_get_product' ) ) {
		wp_send_json_success(
			array(
				'products'     => array(),
				'total'        => 0,
				'view_all_url' => diako_get_product_search_url( $term ),
			)
		);
	}

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$product = wc_get_product( get_the_ID() );

			if ( ! $product || ! $product->is_visible() ) {
				continue;
			}

			$image_id = $product->get_image_id();
			$image    = $image_id
				? wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' )
				: wc_placeholder_img_src( 'woocommerce_thumbnail' );

			$products[] = array(
				'id'         => $product->get_id(),
				'name'       => diako_filter_persian_digits_string( $product->get_name() ),
				'url'        => esc_url( $product->get_permalink() ),
				'price_html' => diako_filter_persian_digits_string( wp_kses_post( diako_get_product_card_price_html( $product ) ) ),
				'image'      => esc_url( $image ? $image : '' ),
			);
		}
		wp_reset_postdata();
	}

	wp_send_json_success(
		array(
			'products'     => $products,
			'total'        => (int) $query->found_posts,
			'view_all_url' => diako_get_product_search_url( $term ),
			'total_label'  => diako_number( (int) $query->found_posts ),
		)
	);
}
add_action( 'wp_ajax_diako_product_search', 'diako_ajax_product_search' );
add_action( 'wp_ajax_nopriv_diako_product_search', 'diako_ajax_product_search' );
