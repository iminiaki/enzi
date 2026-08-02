<?php
/**
 * Customer favorite products.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DIAKO_FAVORITES_META_KEY = '_diako_favorite_product_ids';

/**
 * Register My Account favorites endpoint.
 */
function diako_register_favorites_endpoint(): void {
	add_rewrite_endpoint( 'favorites', EP_ROOT | EP_PAGES );

	if ( '1' !== get_option( 'diako_favorites_endpoint_flushed' ) ) {
		flush_rewrite_rules( false );
		update_option( 'diako_favorites_endpoint_flushed', '1' );
	}
}
add_action( 'init', 'diako_register_favorites_endpoint' );

/**
 * Register favorites endpoint query var for WooCommerce.
 *
 * @param array<string, string> $query_vars Query vars.
 * @return array<string, string>
 */
function diako_register_favorites_account_query_var( array $query_vars ): array {
	$query_vars['favorites'] = 'favorites';
	return $query_vars;
}
add_filter( 'woocommerce_get_query_vars', 'diako_register_favorites_account_query_var' );

/**
 * Favorite product IDs for a user.
 *
 * @param int|null $user_id User ID.
 * @return int[]
 */
function diako_get_favorite_product_ids( ?int $user_id = null ): array {
	$user_id = $user_id ?: get_current_user_id();

	if ( ! $user_id ) {
		return array();
	}

	$ids = get_user_meta( $user_id, DIAKO_FAVORITES_META_KEY, true );

	if ( ! is_array( $ids ) ) {
		return array();
	}

	$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );

	return $ids;
}

/**
 * Store favorite product IDs.
 *
 * @param int[]    $ids     Product IDs.
 * @param int|null $user_id User ID.
 * @return void
 */
function diako_set_favorite_product_ids( array $ids, ?int $user_id = null ): void {
	$user_id = $user_id ?: get_current_user_id();

	if ( ! $user_id ) {
		return;
	}

	$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
	update_user_meta( $user_id, DIAKO_FAVORITES_META_KEY, $ids );
}

/**
 * Whether a product is in current user's favorites.
 *
 * @param int      $product_id Product ID.
 * @param int|null $user_id    User ID.
 * @return bool
 */
function diako_is_product_favorite( int $product_id, ?int $user_id = null ): bool {
	if ( ! $product_id ) {
		return false;
	}

	return in_array( $product_id, diako_get_favorite_product_ids( $user_id ), true );
}

/**
 * Toggle favorite product for current user.
 */
function diako_ajax_toggle_favorite_product(): void {
	check_ajax_referer( 'diako_favorites', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error(
			array(
				'message'  => __( 'برای افزودن به علاقه‌مندی‌ها وارد حساب کاربری شوید.', 'diako' ),
				'redirect' => wc_get_page_permalink( 'myaccount' ),
			),
			401
		);
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$product    = $product_id ? wc_get_product( $product_id ) : false;

	if ( ! $product || ! $product->is_visible() ) {
		wp_send_json_error(
			array( 'message' => __( 'محصول یافت نشد.', 'diako' ) ),
			404
		);
	}

	$ids       = diako_get_favorite_product_ids();
	$is_active = in_array( $product_id, $ids, true );

	if ( $is_active ) {
		$ids       = array_values( array_diff( $ids, array( $product_id ) ) );
		$is_active = false;
		$message   = __( 'از علاقه‌مندی‌ها حذف شد.', 'diako' );
	} else {
		$ids[]     = $product_id;
		$is_active = true;
		$message   = __( 'به علاقه‌مندی‌ها اضافه شد.', 'diako' );
	}

	diako_set_favorite_product_ids( $ids );

	wp_send_json_success(
		array(
			'active'  => $is_active,
			'count'   => count( $ids ),
			'message' => $message,
		)
	);
}
add_action( 'wp_ajax_diako_toggle_favorite_product', 'diako_ajax_toggle_favorite_product' );

/**
 * Render favorite toggle button.
 *
 * @param WC_Product|int|null $product Product object or ID.
 * @param string              $context card|single.
 * @return void
 */
function diako_render_favorite_button( $product = null, string $context = 'card' ): void {
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
	$is_active  = diako_is_product_favorite( $product_id );
	$label      = $is_active ? __( 'حذف از علاقه‌مندی‌ها', 'diako' ) : __( 'افزودن به علاقه‌مندی‌ها', 'diako' );
	$classes    = diako_cn(
		'diako-favorite-button',
		'diako-favorite-button--' . sanitize_html_class( $context ),
		$is_active ? 'is-active' : ''
	);
	?>
	<button
		type="button"
		class="<?php echo esc_attr( $classes ); ?>"
		data-favorite-toggle
		data-product-id="<?php echo esc_attr( (string) $product_id ); ?>"
		aria-label="<?php echo esc_attr( $label ); ?>"
		aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>"
	>
		<?php echo diako_lucide_icon_svg( 'heart', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>
	<?php
}

/**
 * Add favorites to My Account menu after orders.
 *
 * @param array<string, string> $items Menu items.
 * @return array<string, string>
 */
function diako_add_favorites_account_menu_item( array $items ): array {
	$next = array();

	foreach ( $items as $endpoint => $label ) {
		$next[ $endpoint ] = $label;

		if ( 'orders' === $endpoint ) {
			$next['favorites'] = __( 'علاقه‌مندی‌ها', 'diako' );
		}
	}

	if ( ! isset( $next['favorites'] ) ) {
		$next['favorites'] = __( 'علاقه‌مندی‌ها', 'diako' );
	}

	return $next;
}
add_filter( 'woocommerce_account_menu_items', 'diako_add_favorites_account_menu_item', 15 );

/**
 * Favorites endpoint content.
 */
function diako_render_favorites_account_endpoint(): void {
	$favorite_ids = diako_get_favorite_product_ids();
	?>
	<div class="diako-account-favorites">
		<header class="diako-account-section-head">
			<h2><?php esc_html_e( 'محصولات علاقه‌مندی', 'diako' ); ?></h2>
			<p><?php esc_html_e( 'محصولاتی که با قلب ذخیره کرده‌اید اینجا نمایش داده می‌شوند.', 'diako' ); ?></p>
		</header>

		<?php if ( empty( $favorite_ids ) ) : ?>
			<div class="diako-account-empty">
				<?php echo diako_lucide_icon_svg( 'heart', 'h-8 w-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<p><?php esc_html_e( 'هنوز محصولی به علاقه‌مندی‌ها اضافه نکرده‌اید.', 'diako' ); ?></p>
				<a class="<?php echo esc_attr( diako_button_classes( 'default', 'sm' ) ); ?>" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
					<?php esc_html_e( 'مشاهده فروشگاه', 'diako' ); ?>
				</a>
			</div>
		<?php else : ?>
			<?php
			$query = new WP_Query(
				array(
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'post__in'       => $favorite_ids,
					'orderby'        => 'post__in',
					'posts_per_page' => -1,
				)
			);
			?>

			<?php if ( $query->have_posts() ) : ?>
				<ul class="products diako-card-grid diako-favorites-grid">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						wc_get_template_part( 'content', 'product' );
					endwhile;
					wp_reset_postdata();
					?>
				</ul>
			<?php else : ?>
				<?php diako_set_favorite_product_ids( array() ); ?>
				<div class="diako-account-empty">
					<p><?php esc_html_e( 'محصولات ذخیره‌شده دیگر در دسترس نیستند.', 'diako' ); ?></p>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}
add_action( 'woocommerce_account_favorites_endpoint', 'diako_render_favorites_account_endpoint' );

/**
 * Favorite script config.
 */
function diako_enqueue_favorites_assets(): void {
	wp_localize_script(
		'diako-main',
		'diakoFavorites',
		array(
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'diako_favorites' ),
			'loggedIn' => is_user_logged_in(),
			'loginUrl' => wc_get_page_permalink( 'myaccount' ),
			'i18n'     => array(
				'add'      => __( 'افزودن به علاقه‌مندی‌ها', 'diako' ),
				'remove'   => __( 'حذف از علاقه‌مندی‌ها', 'diako' ),
				'login'    => __( 'برای افزودن به علاقه‌مندی‌ها وارد حساب کاربری شوید.', 'diako' ),
				'error'    => __( 'خطایی رخ داد. دوباره تلاش کنید.', 'diako' ),
				'added'    => __( 'به علاقه‌مندی‌ها اضافه شد.', 'diako' ),
				'removed'  => __( 'از علاقه‌مندی‌ها حذف شد.', 'diako' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'diako_enqueue_favorites_assets', 120 );
