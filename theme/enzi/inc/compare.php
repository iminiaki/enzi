<?php
/**
 * Product compare.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render compare toggle button.
 *
 * @param WC_Product|int|null $product Product object or ID.
 * @param string              $context card|single.
 * @return void
 */
function diako_render_compare_button( $product = null, string $context = 'card' ): void {
	if ( ! $product instanceof WC_Product ) {
		$product = $product ? wc_get_product( $product ) : null;
	}

	if ( ! $product instanceof WC_Product ) {
		global $product;
	}

	if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
		return;
	}

	$product_id = $product->get_id();
	$classes    = diako_cn(
		'diako-compare-button',
		'diako-compare-button--' . sanitize_html_class( $context )
	);
	?>
	<button
		type="button"
		class="<?php echo esc_attr( $classes ); ?>"
		data-compare-toggle
		data-product-id="<?php echo esc_attr( (string) $product_id ); ?>"
		data-product-name="<?php echo esc_attr( $product->get_name() ); ?>"
		aria-label="<?php esc_attr_e( 'افزودن به مقایسه', 'diako' ); ?>"
		aria-pressed="false"
	>
		<?php echo diako_lucide_icon_svg( 'scale', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>
	<?php
}

/**
 * Render single product favorite/compare actions outside cart form.
 */
function diako_render_single_product_secondary_actions(): void {
	global $product;

	if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
		return;
	}
	?>
	<div class="diako-single-product-actions">
		<?php diako_render_favorite_button( $product, 'single' ); ?>
		<?php diako_render_compare_button( $product, 'single' ); ?>
	</div>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'diako_render_single_product_secondary_actions', 31 );

/**
 * Compare modal and floating bar.
 */
function diako_render_compare_shell(): void {
	?>
	<div class="diako-compare-bar" data-compare-bar hidden>
		<div class="diako-compare-bar__inner">
			<span class="diako-compare-bar__icon" aria-hidden="true">
				<?php echo diako_lucide_icon_svg( 'scale', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
			<span class="diako-compare-bar__text">
				<?php esc_html_e( 'مقایسه', 'diako' ); ?>
				<span data-compare-count><?php echo esc_html( diako_number( 0 ) ); ?></span>
				<?php esc_html_e( 'محصول', 'diako' ); ?>
			</span>
			<button type="button" class="diako-compare-bar__action" data-compare-open>
				<?php esc_html_e( 'مشاهده', 'diako' ); ?>
			</button>
			<button type="button" class="diako-compare-bar__clear" data-compare-clear aria-label="<?php esc_attr_e( 'پاک کردن مقایسه', 'diako' ); ?>">
				<?php echo diako_lucide_icon_svg( 'x', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>
	</div>

	<div class="diako-compare-modal" data-compare-modal hidden>
		<div class="diako-compare-modal__backdrop" data-compare-close aria-hidden="true"></div>
		<div class="diako-compare-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="diako-compare-title">
			<header class="diako-compare-modal__header">
				<div>
					<h2 id="diako-compare-title"><?php esc_html_e( 'مقایسه محصولات', 'diako' ); ?></h2>
					<p><?php esc_html_e( 'محصولات انتخاب‌شده را کنار هم بررسی کنید.', 'diako' ); ?></p>
				</div>
				<button type="button" class="diako-compare-modal__close" data-compare-close aria-label="<?php esc_attr_e( 'بستن', 'diako' ); ?>">
					<?php echo diako_lucide_icon_svg( 'x', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
			</header>
			<div class="diako-compare-modal__body" data-compare-body></div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'diako_render_compare_shell', 25 );

/**
 * Compare value helper.
 *
 * @param string $value Value.
 * @return string
 */
function diako_compare_value( string $value ): string {
	$value = trim( wp_strip_all_tags( $value ) );
	return '' !== $value ? $value : '—';
}

/**
 * Product compare data.
 *
 * @param WC_Product $product Product.
 * @return array<string, string>
 */
function diako_get_product_compare_data( WC_Product $product ): array {
	$category_names = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) );
	$rating         = (float) $product->get_average_rating();

	return array(
		'image'      => $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'diako-compare-table__image' ) ),
		'name'       => $product->get_name(),
		'url'        => $product->get_permalink(),
		'price'      => $product->get_price_html() ? wp_kses_post( $product->get_price_html() ) : '—',
		'stock'      => $product->is_in_stock() ? __( 'موجود', 'diako' ) : __( 'ناموجود', 'diako' ),
		'sku'        => diako_compare_value( $product->get_sku() ),
		'categories' => ! is_wp_error( $category_names ) && ! empty( $category_names ) ? implode( '، ', $category_names ) : '—',
		'rating'     => $rating > 0 ? diako_number( $rating ) . ' / 5' : '—',
	);
}

/**
 * Render compare table.
 *
 * @param WC_Product[] $products Products.
 * @return string
 */
function diako_render_compare_table( array $products ): string {
	$attribute_rows = array();

	foreach ( $products as $product ) {
		foreach ( $product->get_attributes() as $attribute ) {
			if ( ! is_object( $attribute ) || ! method_exists( $attribute, 'get_visible' ) || ! $attribute->get_visible() ) {
				continue;
			}

			$key                    = $attribute->get_name();
			$attribute_rows[ $key ] = wc_attribute_label( $key, $product );
		}
	}

	$attribute_rows = array_slice( $attribute_rows, 0, 8, true );

	ob_start();
	?>
	<div class="diako-compare-table-wrap">
		<table class="diako-compare-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ویژگی', 'diako' ); ?></th>
					<?php foreach ( $products as $product ) : ?>
						<?php $data = diako_get_product_compare_data( $product ); ?>
						<th>
							<div class="diako-compare-table__product">
								<button type="button" class="diako-compare-table__remove" data-compare-remove="<?php echo esc_attr( (string) $product->get_id() ); ?>" aria-label="<?php esc_attr_e( 'حذف از مقایسه', 'diako' ); ?>">
									<?php echo diako_lucide_icon_svg( 'x', 'h-3.5 w-3.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</button>
								<a href="<?php echo esc_url( $data['url'] ); ?>">
									<?php echo $data['image']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<span><?php echo esc_html( $data['name'] ); ?></span>
								</a>
							</div>
						</th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php
				$base_rows = array(
					'price'      => __( 'قیمت', 'diako' ),
					'stock'      => __( 'وضعیت موجودی', 'diako' ),
					'sku'        => __( 'شناسه کالا', 'diako' ),
					'categories' => __( 'دسته‌بندی', 'diako' ),
					'rating'     => __( 'امتیاز کاربران', 'diako' ),
				);
				?>
				<?php foreach ( $base_rows as $key => $label ) : ?>
					<tr>
						<th><?php echo esc_html( $label ); ?></th>
						<?php foreach ( $products as $product ) : ?>
							<?php $data = diako_get_product_compare_data( $product ); ?>
							<td><?php echo wp_kses_post( $data[ $key ] ); ?></td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>

				<?php foreach ( $attribute_rows as $attribute_name => $label ) : ?>
					<tr>
						<th><?php echo esc_html( $label ); ?></th>
						<?php foreach ( $products as $product ) : ?>
							<td><?php echo esc_html( diako_compare_value( $product->get_attribute( $attribute_name ) ) ); ?></td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * AJAX: render compare table.
 */
function diako_ajax_render_compare_products(): void {
	check_ajax_referer( 'diako_compare', 'nonce' );

	if ( diako_rate_limit_block( 'compare_render', 20, 900 ) ) {
		diako_rate_limit_json_error();
	}

	$ids = isset( $_POST['ids'] ) ? (array) wp_unslash( $_POST['ids'] ) : array();
	$ids = array_slice( array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) ), 0, 4 );

	if ( empty( $ids ) ) {
		wp_send_json_error( array( 'message' => __( 'محصولی برای مقایسه انتخاب نشده است.', 'diako' ) ), 400 );
	}

	$products = array();

	foreach ( $ids as $id ) {
		$product = wc_get_product( $id );

		if ( $product && $product->is_visible() ) {
			$products[] = $product;
		}
	}

	if ( empty( $products ) ) {
		wp_send_json_error( array( 'message' => __( 'محصولی برای مقایسه یافت نشد.', 'diako' ) ), 404 );
	}

	wp_send_json_success(
		array(
			'html' => diako_render_compare_table( $products ),
		)
	);
}
add_action( 'wp_ajax_diako_render_compare_products', 'diako_ajax_render_compare_products' );
add_action( 'wp_ajax_nopriv_diako_render_compare_products', 'diako_ajax_render_compare_products' );

/**
 * Compare script config.
 */
function diako_enqueue_compare_assets(): void {
	wp_localize_script(
		'diako-main',
		'diakoCompare',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'diako_compare' ),
			'max'     => 4,
			'i18n'    => array(
				'add'      => __( 'افزودن به مقایسه', 'diako' ),
				'remove'   => __( 'حذف از مقایسه', 'diako' ),
				'loading'  => __( 'در حال ساخت مقایسه…', 'diako' ),
				'empty'    => __( 'محصولی برای مقایسه انتخاب نشده است.', 'diako' ),
				'max'      => __( 'حداکثر ۴ محصول قابل مقایسه است.', 'diako' ),
				'error'    => __( 'خطایی رخ داد. دوباره تلاش کنید.', 'diako' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'diako_enqueue_compare_assets', 125 );
