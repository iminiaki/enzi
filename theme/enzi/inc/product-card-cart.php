<?php
/**
 * Product card AJAX add-to-cart and variation modal.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cart count badge HTML for header fragments.
 *
 * @param int|null $count Optional cart count override.
 * @return string
 */
function diako_get_cart_count_badge_html( ?int $count = null ): string {
	if ( null === $count ) {
		$count = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
	}

	$hidden = $count > 0 ? '' : ' hidden';

	return sprintf(
		'<span class="diako-cart-count absolute -top-0.5 -start-0.5 flex size-5 items-center justify-center rounded-full bg-brand-orange text-[10px] font-bold text-white"%1$s data-diako-cart-count>%2$s</span>',
		$hidden,
		esc_html( diako_number( $count ) )
	);
}

/**
 * Add cart count fragment for AJAX updates.
 *
 * @param array<string, string> $fragments Fragments.
 * @return array<string, string>
 */
function diako_cart_count_fragment( array $fragments ): array {
	$fragments['span[data-diako-cart-count]'] = diako_get_cart_count_badge_html();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'diako_cart_count_fragment', 20 );

/**
 * Variation modal shell in footer.
 */
function diako_render_variation_modal_shell(): void {
	if ( ! function_exists( 'WC' ) ) {
		return;
	}
	?>
	<div class="diako-variation-modal" data-diako-variation-modal hidden>
		<div class="diako-variation-modal__backdrop" data-diako-variation-modal-close aria-hidden="true"></div>
		<div
			class="diako-variation-modal__dialog"
			role="dialog"
			aria-modal="true"
			aria-labelledby="diako-variation-modal-title"
		>
			<button
				type="button"
				class="diako-variation-modal__close"
				data-diako-variation-modal-close
				aria-label="<?php esc_attr_e( 'بستن', 'diako' ); ?>"
			>
				<?php echo diako_lucide_icon_svg( 'x', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
			<div class="diako-variation-modal__body" data-diako-variation-modal-body></div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'diako_render_variation_modal_shell', 22 );

/**
 * Render variation attribute radios for the card modal.
 *
 * @param WC_Product_Variable $product Product.
 * @return string
 */
function diako_render_variation_modal_attributes( WC_Product_Variable $product ): string {
	$attributes = $product->get_variation_attributes();

	if ( empty( $attributes ) ) {
		return '';
	}

	$GLOBALS['diako_variation_radios_force'] = true;

	ob_start();
	?>
	<div class="diako-variations variations" role="presentation">
		<?php foreach ( $attributes as $attribute_name => $options ) : ?>
			<?php
			$attribute_id    = sanitize_title( $attribute_name );
			$attribute_label = wc_attribute_label( $attribute_name );
			?>
			<div class="diako-variation-row" data-attribute_name="attribute_<?php echo esc_attr( $attribute_id ); ?>">
				<div class="diako-variation-row__label label">
					<label id="<?php echo esc_attr( $attribute_id ); ?>-label" for="<?php echo esc_attr( $attribute_id ); ?>">
						<?php echo esc_html( $attribute_label ); ?>
					</label>
				</div>
				<div class="diako-variation-row__value value">
					<?php
					wc_dropdown_variation_attribute_options(
						array(
							'options'   => $options,
							'attribute' => $attribute_name,
							'product'   => $product,
						)
					);
					?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	$html = (string) ob_get_clean();

	unset( $GLOBALS['diako_variation_radios_force'] );

	return $html;
}

/**
 * Render variation modal content HTML.
 *
 * @param WC_Product_Variable $product Product.
 * @return string
 */
function diako_render_variation_modal_content( WC_Product_Variable $product ): string {
	$available_variations = $product->get_available_variations();
	$variations_json      = wp_json_encode( $available_variations );
	$variations_attr      = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );
	$price_html           = diako_get_product_card_price_html( $product );
	$image_html           = $product->get_image(
		'woocommerce_thumbnail',
		array(
			'class' => 'diako-variation-modal__image',
		)
	);

	ob_start();
	?>
	<form
		class="diako-variation-modal__form variations_form"
		data-diako-variation-modal-form
		data-product_id="<?php echo esc_attr( (string) $product->get_id() ); ?>"
		data-product_variations="<?php echo $variations_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
	>
		<div class="diako-variation-modal__product">
			<div class="diako-variation-modal__media">
				<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<div class="diako-variation-modal__meta">
				<h2 id="diako-variation-modal-title" class="diako-variation-modal__title">
					<?php echo esc_html( $product->get_name() ); ?>
				</h2>
				<div class="diako-variation-modal__price" data-diako-variation-modal-price data-diako-default-price-html="<?php echo esc_attr( $price_html ); ?>">
					<?php echo $price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</div>

		<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
			<p class="diako-variation-modal__status is-error">
				<?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?>
			</p>
		<?php else : ?>
			<?php echo diako_render_variation_modal_attributes( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

			<input type="hidden" name="variation_id" class="variation_id" value="0" />
			<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $product->get_id() ); ?>" />
			<input type="hidden" name="quantity" value="1" />

			<p class="diako-variation-modal__feedback" data-diako-variation-modal-feedback hidden role="status" aria-live="polite"></p>

			<button
				type="submit"
				class="<?php echo esc_attr( diako_button_classes( 'default', 'lg', 'diako-variation-modal__submit w-full' ) ); ?>"
				data-diako-variation-modal-submit
				disabled
			>
				<?php echo diako_lucide_icon_svg( 'shopping-bag', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php esc_html_e( 'افزودن به سبد', 'diako' ); ?></span>
			</button>
		<?php endif; ?>
	</form>
	<?php

	return (string) ob_get_clean();
}

/**
 * AJAX: variation modal markup.
 */
function diako_ajax_get_variation_modal(): void {
	check_ajax_referer( 'diako_card_cart', 'nonce' );

	if ( diako_rate_limit_block( 'card_variation_modal', 30, 900 ) ) {
		diako_rate_limit_json_error();
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$product    = $product_id ? wc_get_product( $product_id ) : null;

	if ( ! $product instanceof WC_Product_Variable || ! $product->is_visible() ) {
		wp_send_json_error(
			array(
				'message' => __( 'محصول متغیری یافت نشد.', 'diako' ),
			),
			404
		);
	}

	if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		wp_send_json_error(
			array(
				'message' => __( 'این محصول در حال حاضر قابل خرید نیست.', 'diako' ),
			),
			400
		);
	}

	wp_send_json_success(
		array(
			'html' => diako_render_variation_modal_content( $product ),
		)
	);
}
add_action( 'wp_ajax_diako_get_variation_modal', 'diako_ajax_get_variation_modal' );
add_action( 'wp_ajax_nopriv_diako_get_variation_modal', 'diako_ajax_get_variation_modal' );

/**
 * Collect variation attributes from request.
 *
 * @return array<string, string>
 */
function diako_card_cart_request_attributes(): array {
	$attributes = array();

	foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$key = (string) $key;

		if ( 0 !== strpos( $key, 'attribute_' ) ) {
			continue;
		}

		$attributes[ wc_clean( $key ) ] = wc_clean( wp_unslash( (string) $value ) );
	}

	return $attributes;
}

/**
 * AJAX: add product (simple or variation) to cart from product cards.
 */
function diako_ajax_card_add_to_cart(): void {
	check_ajax_referer( 'diako_card_cart', 'nonce' );

	if ( diako_rate_limit_block( 'card_add_to_cart', 40, 900 ) ) {
		diako_rate_limit_json_error();
	}

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error(
			array(
				'message' => __( 'سبد خرید در دسترس نیست.', 'diako' ),
			),
			500
		);
	}

	$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
	$quantity     = isset( $_POST['quantity'] ) ? max( 1, absint( $_POST['quantity'] ) ) : 1;
	$product      = $product_id ? wc_get_product( $product_id ) : null;

	if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
		wp_send_json_error(
			array(
				'message' => __( 'محصول یافت نشد.', 'diako' ),
			),
			404
		);
	}

	if ( $product->is_type( 'variable' ) ) {
		if ( $variation_id <= 0 ) {
			wp_send_json_error(
				array(
					'message' => __( 'لطفاً یک گزینه را انتخاب کنید.', 'diako' ),
				),
				400
			);
		}

		$variation = wc_get_product( $variation_id );

		if ( ! $variation || (int) $variation->get_parent_id() !== $product_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'گزینه انتخاب‌شده معتبر نیست.', 'diako' ),
				),
				400
			);
		}

		if ( ! $variation->is_purchasable() || ! $variation->is_in_stock() ) {
			wp_send_json_error(
				array(
					'message' => __( 'این گزینه در حال حاضر قابل خرید نیست.', 'diako' ),
				),
				400
			);
		}

		$attributes = diako_card_cart_request_attributes();
		$added      = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $attributes );
	} elseif ( $product->is_type( 'simple' ) ) {
		if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			wp_send_json_error(
				array(
					'message' => __( 'این محصول در حال حاضر قابل خرید نیست.', 'diako' ),
				),
				400
			);
		}

		$added = WC()->cart->add_to_cart( $product_id, $quantity );
	} else {
		wp_send_json_error(
			array(
				'message' => __( 'این نوع محصول از کارت قابل افزودن نیست.', 'diako' ),
				'url'     => $product->get_permalink(),
			),
			400
		);
	}

	if ( ! $added ) {
		$notices = wc_get_notices( 'error' );
		wc_clear_notices();
		$message = ! empty( $notices[0]['notice'] ) ? wp_strip_all_tags( $notices[0]['notice'] ) : __( 'افزودن به سبد خرید ناموفق بود.', 'diako' );

		wp_send_json_error(
			array(
				'message' => $message,
			),
			400
		);
	}

	wc_clear_notices();

	$cart_url = wc_get_cart_url();
	$message  = sprintf(
		/* translators: %s: product name */
		__( '«%s» به سبد خرید اضافه شد.', 'diako' ),
		$product->get_name()
	);

	$data = array(
		'message'   => $message,
		'cartUrl'   => $cart_url,
		'cartCount' => (int) WC()->cart->get_cart_contents_count(),
		'fragments' => apply_filters(
			'woocommerce_add_to_cart_fragments',
			array(
				'span[data-diako-cart-count]' => diako_get_cart_count_badge_html(),
			)
		),
		'cart_hash' => WC()->cart->get_cart_hash(),
	);

	wp_send_json_success( $data );
}
add_action( 'wp_ajax_diako_card_add_to_cart', 'diako_ajax_card_add_to_cart' );
add_action( 'wp_ajax_nopriv_diako_card_add_to_cart', 'diako_ajax_card_add_to_cart' );

/**
 * Localize card cart script config.
 */
function diako_enqueue_card_cart_assets(): void {
	if ( ! function_exists( 'WC' ) ) {
		return;
	}

	wp_localize_script(
		'diako-main',
		'diakoCardCart',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'diako_card_cart' ),
			'cartUrl' => wc_get_cart_url(),
			'i18n'    => array(
				'adding'          => __( 'در حال افزودن…', 'diako' ),
				'added'           => __( 'به سبد خرید اضافه شد.', 'diako' ),
				'error'           => __( 'خطایی رخ داد. دوباره تلاش کنید.', 'diako' ),
				'selectVariation' => __( 'لطفاً یک گزینه را انتخاب کنید.', 'diako' ),
				'loading'         => __( 'در حال بارگذاری…', 'diako' ),
				'viewCart'        => __( 'مشاهده سبد', 'diako' ),
				'addToCart'       => __( 'افزودن به سبد', 'diako' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'diako_enqueue_card_cart_assets', 126 );
