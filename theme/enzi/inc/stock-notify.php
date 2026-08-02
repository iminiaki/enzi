<?php
/**
 * Back-in-stock email alerts.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Alert label shown on out-of-stock products.
 */
function diako_stock_notify_label(): string {
	return __( 'موجود شد به من خبر بده', 'diako' );
}

/**
 * Shorter label for product card notify button.
 */
function diako_stock_notify_card_label(): string {
	return __( 'موجود شد خبرم کن', 'diako' );
}

/**
 * Register internal post type for subscriptions.
 */
function diako_register_stock_notify_post_type(): void {
	register_post_type(
		'diako_stock_alert',
		array(
			'labels'              => array(
				'name'          => __( 'اعلان موجودی', 'diako' ),
				'singular_name' => __( 'درخواست اعلان', 'diako' ),
				'menu_name'     => __( 'اعلان موجودی', 'diako' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=product',
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => array( 'title' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'exclude_from_search' => true,
		)
	);
}
add_action( 'init', 'diako_register_stock_notify_post_type' );

/**
 * Maximum digits allowed for Iranian mobile numbers.
 */
function diako_stock_notify_phone_max_length(): int {
	return 11;
}

/**
 * Normalize Iranian mobile number to 09XXXXXXXXX.
 *
 * @param string $phone Raw phone input.
 * @return string
 */
function diako_stock_notify_normalize_phone( string $phone ): string {
	$phone  = diako_to_western_digits( trim( $phone ) );
	$digits = preg_replace( '/\D+/', '', $phone );

	if ( str_starts_with( $digits, '98' ) && 12 === strlen( $digits ) ) {
		$digits = '0' . substr( $digits, 2 );
	} elseif ( ! str_starts_with( $digits, '0' ) && str_starts_with( $digits, '9' ) && 10 === strlen( $digits ) ) {
		$digits = '0' . $digits;
	}

	return $digits;
}

/**
 * @param string $phone Phone number.
 * @return bool
 */
function diako_stock_notify_is_valid_phone( string $phone ): bool {
	return ! is_wp_error( diako_stock_notify_validate_phone( $phone ) );
}

/**
 * Validate and normalize a phone number.
 *
 * @param string $phone Raw phone input.
 * @return string|WP_Error Normalized phone or error.
 */
function diako_stock_notify_validate_phone( string $phone ) {
	$phone  = diako_to_western_digits( trim( $phone ) );
	$digits = preg_replace( '/\D+/', '', $phone );
	$max    = diako_stock_notify_phone_max_length();

	if ( '' === $digits ) {
		return new WP_Error( 'empty_phone', __( 'شماره موبایل را وارد کنید.', 'diako' ) );
	}

	if ( strlen( $digits ) > $max ) {
		return new WP_Error(
			'phone_too_long',
			sprintf(
				/* translators: %d: maximum digits */
				__( 'شماره موبایل نباید بیشتر از %d رقم باشد.', 'diako' ),
				$max
			)
		);
	}

	$phone = diako_stock_notify_normalize_phone( $phone );

	if ( strlen( $phone ) !== $max ) {
		return new WP_Error(
			'phone_length',
			sprintf(
				/* translators: %d: required digits */
				__( 'شماره موبایل باید %d رقم باشد.', 'diako' ),
				$max
			)
		);
	}

	if ( ! preg_match( '/^09\d{9}$/', $phone ) ) {
		return new WP_Error(
			'invalid_phone',
			__( 'شماره موبایل معتبر وارد کنید. (مثال: 09123456789)', 'diako' )
		);
	}

	return $phone;
}

/**
 * @param int    $product_id   Parent product ID.
 * @param int    $variation_id Variation ID (0 for simple / any).
 * @param string $phone        Subscriber phone.
 * @return bool
 */
function diako_stock_notify_is_subscribed( int $product_id, int $variation_id, string $phone ): bool {
	$phone = diako_stock_notify_normalize_phone( $phone );

	if ( ! $product_id || ! diako_stock_notify_is_valid_phone( $phone ) ) {
		return false;
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'diako_stock_alert',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'   => '_diako_stock_notify_product_id',
					'value' => $product_id,
				),
				array(
					'key'   => '_diako_stock_notify_variation_id',
					'value' => $variation_id,
				),
				array(
					'key'   => '_diako_stock_notify_phone',
					'value' => $phone,
				),
				array(
					'key'   => '_diako_stock_notify_status',
					'value' => 'pending',
				),
			),
		)
	);

	return ! empty( $query->posts );
}

/**
 * Create a pending stock alert.
 *
 * @param int    $product_id   Parent product ID.
 * @param int    $variation_id Variation ID.
 * @param string $phone        Phone number.
 * @return int|WP_Error Post ID or error.
 */
function diako_stock_notify_create_subscription( int $product_id, int $variation_id, string $phone ) {
	$validated = diako_stock_notify_validate_phone( $phone );

	if ( is_wp_error( $validated ) ) {
		return $validated;
	}

	$phone = $validated;

	if ( ! $product_id ) {
		return new WP_Error( 'invalid_request', __( 'شماره موبایل معتبر وارد کنید.', 'diako' ) );
	}

	$product = wc_get_product( $variation_id > 0 ? $variation_id : $product_id );

	if ( ! $product ) {
		return new WP_Error( 'invalid_product', __( 'محصول یافت نشد.', 'diako' ) );
	}

	if ( $product->is_in_stock() ) {
		return new WP_Error( 'in_stock', __( 'این محصول هم‌اکنون موجود است.', 'diako' ) );
	}

	$parent_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();

	if ( $variation_id > 0 && $parent_id !== $product_id ) {
		return new WP_Error( 'invalid_variation', __( 'گزینه محصول نامعتبر است.', 'diako' ) );
	}

	if ( diako_stock_notify_is_subscribed( $parent_id, $variation_id, $phone ) ) {
		return new WP_Error( 'already_subscribed', __( 'قبلاً برای این محصول ثبت‌نام کرده‌اید. به محض موجود شدن به شما پیامک می‌فرستیم.', 'diako' ) );
	}

	$title = sprintf(
		/* translators: 1: product name, 2: phone */
		__( '%1$s — %2$s', 'diako' ),
		$product->get_name(),
		$phone
	);

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'diako_stock_alert',
			'post_status' => 'publish',
			'post_title'  => $title,
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	update_post_meta( $post_id, '_diako_stock_notify_product_id', $parent_id );
	update_post_meta( $post_id, '_diako_stock_notify_variation_id', $variation_id );
	update_post_meta( $post_id, '_diako_stock_notify_phone', $phone );
	update_post_meta( $post_id, '_diako_stock_notify_status', 'pending' );

	$user_id = get_current_user_id();
	if ( $user_id ) {
		update_post_meta( $post_id, '_diako_stock_notify_user_id', $user_id );
	}

	return $post_id;
}

/**
 * Default phone for the notify form.
 */
function diako_stock_notify_default_phone(): string {
	if ( ! is_user_logged_in() ) {
		return '';
	}

	$phone = '';

	if ( class_exists( 'WC_Customer' ) ) {
		$customer = new WC_Customer( get_current_user_id() );
		$phone    = (string) $customer->get_billing_phone();
	}

	if ( '' === $phone ) {
		$phone = (string) get_user_meta( get_current_user_id(), 'billing_phone', true );
	}

	if ( '' === $phone ) {
		return '';
	}

	$phone = diako_stock_notify_normalize_phone( $phone );

	return diako_stock_notify_is_valid_phone( $phone ) ? $phone : '';
}

/**
 * Whether the notify form phone field should be locked.
 *
 * @param string $default_phone Prefilled phone value.
 * @return bool
 */
function diako_stock_notify_phone_is_locked( string $default_phone ): bool {
	return '' !== $default_phone && diako_stock_notify_is_valid_phone( $default_phone );
}

/**
 * Render modal shell (once per page).
 */
function diako_render_stock_notify_modal(): void {
	static $rendered = false;

	if ( $rendered || ! function_exists( 'wc_get_product' ) ) {
		return;
	}

	$rendered = true;
	?>
	<div class="diako-stock-notify-modal" data-stock-notify-modal hidden>
		<div class="diako-stock-notify-modal__backdrop" data-stock-notify-close tabindex="-1" aria-hidden="true"></div>
		<div
			class="diako-stock-notify-modal__dialog"
			role="dialog"
			aria-modal="true"
			aria-labelledby="diako-stock-notify-title"
		>
			<button
				type="button"
				class="diako-stock-notify-modal__close"
				data-stock-notify-close
				aria-label="<?php esc_attr_e( 'بستن', 'diako' ); ?>"
			>
				<?php echo diako_lucide_icon_svg( 'x', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
			<div class="diako-stock-notify-modal__body" data-stock-notify-modal-body>
				<?php diako_render_stock_notify_form( 0, 0, 'modal' ); ?>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'diako_render_stock_notify_modal', 20 );

/**
 * Card trigger button.
 *
 * @param WC_Product $product Product.
 */
function diako_render_stock_notify_card_trigger( WC_Product $product ): void {
	$product_id = $product->get_id();

	$label = diako_stock_notify_card_label();

	diako_button(
		array(
			'type'         => 'button',
			'label'        => $label,
			'variant'      => 'outline',
			'size'         => 'sm',
			'icon'         => 'bell',
			'icon_class'   => 'h-4 w-4 shrink-0',
			'class'        => 'diako-product-card__cta diako-product-card__cta--notify',
			'attrs'        => array(
				'aria-label'                => $label,
				'data-stock-notify-trigger' => '',
				'data-product-id'           => (string) $product_id,
				'data-variation-id'         => '0',
				'data-product-name'         => $product->get_name(),
			),
		)
	);
}

/**
 * Stock notify form.
 *
 * @param int    $product_id   Product ID (0 in modal until opened).
 * @param int    $variation_id Variation ID.
 * @param string $context      modal|single|variation.
 */
function diako_render_stock_notify_form( int $product_id = 0, int $variation_id = 0, string $context = 'modal' ): void {
	$product_name = '';

	if ( $product_id > 0 ) {
		$product = wc_get_product( $variation_id > 0 ? $variation_id : $product_id );
		if ( $product ) {
			$product_name = $product->get_name();
		}
	}

	$is_modal      = 'modal' === $context;
	$default_phone = diako_stock_notify_default_phone();
	$phone_locked  = diako_stock_notify_phone_is_locked( $default_phone );
	$phone_max     = diako_stock_notify_phone_max_length();
	?>
	<div
		class="diako-stock-notify<?php echo $is_modal ? '' : ' diako-stock-notify--inline'; ?>"
		data-stock-notify-form-wrap
		<?php echo $is_modal ? '' : ' data-stock-notify-inline'; ?>
	>
		<?php if ( $is_modal ) : ?>
			<h2 id="diako-stock-notify-title" class="diako-stock-notify__title">
				<?php echo esc_html( diako_stock_notify_label() ); ?>
			</h2>
			<p class="diako-stock-notify__product" data-stock-notify-product-name hidden></p>
		<?php else : ?>
			<h2 class="diako-stock-notify__title diako-stock-notify__title--inline">
				<?php echo esc_html( diako_stock_notify_label() ); ?>
			</h2>
			<?php if ( $product_name ) : ?>
				<p class="diako-stock-notify__product"><?php echo esc_html( $product_name ); ?></p>
			<?php endif; ?>
		<?php endif; ?>

		<p class="diako-stock-notify__desc">
			<?php esc_html_e( 'شماره موبایل خود را وارد کنید تا به محض موجود شدن محصول به شما اطلاع دهیم.', 'diako' ); ?>
		</p>

		<form class="diako-stock-notify__form" data-stock-notify-form novalidate>
			<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $product_id ); ?>" data-stock-notify-product-id />
			<input type="hidden" name="variation_id" value="<?php echo esc_attr( (string) $variation_id ); ?>" data-stock-notify-variation-id />

			<label class="diako-stock-notify__label" for="<?php echo esc_attr( 'diako-stock-notify-phone-' . $context ); ?>">
				<?php esc_html_e( 'شماره موبایل', 'diako' ); ?>
			</label>
			<input
				type="tel"
				id="<?php echo esc_attr( 'diako-stock-notify-phone-' . $context ); ?>"
				name="phone"
				class="diako-stock-notify__input diako-phone-ltr<?php echo $phone_locked ? ' diako-stock-notify__input--locked' : ''; ?>"
				value="<?php echo esc_attr( $default_phone ); ?>"
				placeholder="09123456789"
				required
				autocomplete="tel"
				inputmode="numeric"
				dir="ltr"
				maxlength="<?php echo esc_attr( (string) $phone_max ); ?>"
				pattern="09[0-9]{9}"
				data-stock-notify-phone
				<?php echo $phone_locked ? 'readonly aria-readonly="true" data-stock-notify-phone-prefilled' : ''; ?>
			/>

			<p class="diako-stock-notify__feedback" data-stock-notify-feedback role="status" aria-live="polite" hidden></p>

			<button
				type="submit"
				class="<?php echo esc_attr( diako_button_classes( 'default', 'lg', 'diako-stock-notify__submit w-full' ) ); ?>"
				data-stock-notify-submit
			>
				<?php echo diako_lucide_icon_svg( 'bell', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php esc_html_e( 'ثبت درخواست', 'diako' ); ?></span>
			</button>
		</form>
	</div>
	<?php
}

/**
 * AJAX: subscribe to stock alert.
 */
function diako_ajax_stock_notify_subscribe(): void {
	check_ajax_referer( 'diako_stock_notify', 'nonce' );

	if ( diako_rate_limit_block( 'stock_notify', 5, 900 ) ) {
		diako_rate_limit_json_error();
	}

	$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
	$phone_raw = isset( $_POST['phone'] ) ? wp_unslash( (string) $_POST['phone'] ) : '';

	$result = diako_stock_notify_create_subscription( $product_id, $variation_id, $phone_raw );

	if ( is_wp_error( $result ) ) {
		$code = $result->get_error_code();

		if ( 'already_subscribed' === $code ) {
			wp_send_json_success(
				array(
					'message' => __( 'درخواست شما ثبت شد. به محض موجود شدن محصول پیامک می‌فرستیم.', 'diako' ),
				)
			);
		}

		wp_send_json_error(
			array(
				'message' => $result->get_error_message(),
				'code'    => $code,
			),
			400
		);
	}

	wp_send_json_success(
		array(
			'message' => __( 'درخواست شما ثبت شد. به محض موجود شدن محصول پیامک می‌فرستیم.', 'diako' ),
		)
	);
}
add_action( 'wp_ajax_diako_stock_notify_subscribe', 'diako_ajax_stock_notify_subscribe' );
add_action( 'wp_ajax_nopriv_diako_stock_notify_subscribe', 'diako_ajax_stock_notify_subscribe' );

/**
 * Find pending alerts for a product or variation.
 *
 * @param int $product_id   Parent product ID.
 * @param int $variation_id Variation ID (0 matches parent-level alerts only).
 * @return int[] Post IDs.
 */
function diako_stock_notify_get_pending_alert_ids( int $product_id, int $variation_id = 0 ): array {
	$meta_query = array(
		'relation' => 'AND',
		array(
			'key'   => '_diako_stock_notify_product_id',
			'value' => $product_id,
		),
		array(
			'key'   => '_diako_stock_notify_status',
			'value' => 'pending',
		),
	);

	if ( $variation_id > 0 ) {
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'   => '_diako_stock_notify_variation_id',
				'value' => $variation_id,
			),
			array(
				'key'   => '_diako_stock_notify_variation_id',
				'value' => 0,
			),
		);
	} else {
		$meta_query[] = array(
			'key'   => '_diako_stock_notify_variation_id',
			'value' => 0,
		);
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'diako_stock_alert',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => $meta_query,
		)
	);

	return array_map( 'intval', $query->posts );
}

/**
 * Send back-in-stock emails for pending alerts.
 *
 * @param int $product_id   Parent product ID.
 * @param int $variation_id Variation that restocked (0 for simple).
 */
function diako_stock_notify_send_for_product( int $product_id, int $variation_id = 0 ): void {
	$alert_ids = diako_stock_notify_get_pending_alert_ids( $product_id, $variation_id );

	if ( empty( $alert_ids ) ) {
		return;
	}

	$product = wc_get_product( $variation_id > 0 ? $variation_id : $product_id );

	if ( ! $product || ! $product->is_in_stock() ) {
		return;
	}

	$product_name = $product->get_name();
	$product_url  = $product->get_permalink();
	$site_name    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

	$subject = sprintf(
		/* translators: %s: product name */
		__( 'محصول «%s» موجود شد', 'diako' ),
		$product_name
	);

	foreach ( $alert_ids as $alert_id ) {
		$phone = (string) get_post_meta( $alert_id, '_diako_stock_notify_phone', true );

		if ( diako_stock_notify_is_valid_phone( $phone ) ) {
			/**
			 * Send back-in-stock SMS notification.
			 *
			 * @param string     $phone    Normalized mobile number.
			 * @param WC_Product $product  Product object.
			 * @param int        $alert_id Alert post ID.
			 */
			$sent = apply_filters( 'diako_stock_notify_send_notification', false, $phone, $product, $alert_id );

			if ( $sent ) {
				update_post_meta( $alert_id, '_diako_stock_notify_status', 'notified' );
				update_post_meta( $alert_id, '_diako_stock_notify_notified_at', (string) time() );
			}

			continue;
		}

		$email = (string) get_post_meta( $alert_id, '_diako_stock_notify_email', true );

		if ( ! is_email( $email ) ) {
			continue;
		}

		$message = sprintf(
			/* translators: 1: product name, 2: product URL, 3: site name */
			__(
				"سلام،\n\nمحصول «%1\$s» که برای آن درخواست اعلان ثبت کرده بودید، دوباره موجود شده است.\n\nمشاهده و خرید:\n%2\$s\n\n— %3\$s",
				'diako'
			),
			$product_name,
			$product_url,
			$site_name
		);

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

		$sent = wp_mail( $email, $subject, $message, $headers );

		if ( $sent ) {
			update_post_meta( $alert_id, '_diako_stock_notify_status', 'notified' );
			update_post_meta( $alert_id, '_diako_stock_notify_notified_at', (string) time() );
		}
	}
}

/**
 * When a product or variation is marked in stock.
 *
 * @param int        $product_id Product or variation ID.
 * @param string     $status     Stock status.
 * @param WC_Product $product    Product object.
 */
function diako_stock_notify_on_status_change( $product_id, $status, $product ): void {
	if ( 'instock' !== $status || ! $product instanceof WC_Product ) {
		return;
	}

	if ( $product->is_type( 'variation' ) ) {
		diako_stock_notify_send_for_product( $product->get_parent_id(), $product->get_id() );
		return;
	}

	diako_stock_notify_send_for_product( $product->get_id(), 0 );
}
add_action( 'woocommerce_product_set_stock_status', 'diako_stock_notify_on_status_change', 10, 3 );
add_action( 'woocommerce_variation_set_stock_status', 'diako_stock_notify_on_status_change', 10, 3 );

/**
 * When stock quantity is updated and item becomes purchasable.
 *
 * @param WC_Product $product Product.
 */
function diako_stock_notify_on_stock_quantity( $product ): void {
	if ( ! $product instanceof WC_Product || ! $product->is_in_stock() ) {
		return;
	}

	if ( $product->is_type( 'variation' ) ) {
		diako_stock_notify_send_for_product( $product->get_parent_id(), $product->get_id() );
		return;
	}

	diako_stock_notify_send_for_product( $product->get_id(), 0 );
}
add_action( 'woocommerce_product_set_stock', 'diako_stock_notify_on_stock_quantity', 10, 1 );
add_action( 'woocommerce_variation_set_stock', 'diako_stock_notify_on_stock_quantity', 10, 1 );

/**
 * Admin list columns.
 *
 * @param string[] $columns Columns.
 * @return string[]
 */
function diako_stock_notify_admin_columns( array $columns ): array {
	$new = array();

	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;

		if ( 'title' === $key ) {
			$new['diako_product'] = __( 'محصول', 'diako' );
			$new['diako_phone']   = __( 'موبایل', 'diako' );
			$new['diako_status']  = __( 'وضعیت', 'diako' );
		}
	}

	return $new;
}
add_filter( 'manage_diako_stock_alert_posts_columns', 'diako_stock_notify_admin_columns' );

/**
 * Render admin column cells.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function diako_stock_notify_admin_column_content( string $column, int $post_id ): void {
	if ( 'diako_product' === $column ) {
		$product_id   = (int) get_post_meta( $post_id, '_diako_stock_notify_product_id', true );
		$variation_id = (int) get_post_meta( $post_id, '_diako_stock_notify_variation_id', true );
		$product      = wc_get_product( $variation_id > 0 ? $variation_id : $product_id );

		if ( $product ) {
			echo '<a href="' . esc_url( get_edit_post_link( $product->get_parent_id() ? $product->get_parent_id() : $product->get_id() ) ) . '">';
			echo esc_html( $product->get_name() );
			echo '</a>';
		}
		return;
	}

	if ( 'diako_phone' === $column ) {
		$phone = (string) get_post_meta( $post_id, '_diako_stock_notify_phone', true );

		if ( '' === $phone ) {
			$phone = (string) get_post_meta( $post_id, '_diako_stock_notify_email', true );
			echo esc_html( $phone );
			return;
		}

		printf(
			'<span %1$s>%2$s</span>',
			diako_phone_ltr_attr(),
			esc_html( diako_to_persian_digits( diako_to_western_digits( $phone ) ) )
		);
		return;
	}

	if ( 'diako_status' === $column ) {
		$status = (string) get_post_meta( $post_id, '_diako_stock_notify_status', true );
		echo 'notified' === $status
			? esc_html__( 'اطلاع‌رسانی شده', 'diako' )
			: esc_html__( 'در انتظار', 'diako' );
	}
}
add_action( 'manage_diako_stock_alert_posts_custom_column', 'diako_stock_notify_admin_column_content', 10, 2 );

/**
 * Enqueue stock notify script.
 */
function diako_enqueue_stock_notify_assets(): void {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return;
	}

	wp_enqueue_script(
		'diako-stock-notify',
		diako_theme_js_asset_uri( '/assets/js/stock-notify.js' ),
		array( 'jquery' ),
		file_exists( diako_theme_js_asset_path( '/assets/js/stock-notify.js' ) ) ? (string) filemtime( diako_theme_js_asset_path( '/assets/js/stock-notify.js' ) ) : DIAKO_VERSION,
		true
	);

	wp_localize_script(
		'diako-stock-notify',
		'diakoStockNotify',
		array(
			'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
			'nonce'          => wp_create_nonce( 'diako_stock_notify' ),
			'phoneMaxLength' => diako_stock_notify_phone_max_length(),
			'i18n'           => array(
				'submitting'   => __( 'در حال ثبت…', 'diako' ),
				'submit'       => __( 'ثبت درخواست', 'diako' ),
				'error'        => __( 'خطایی رخ داد. دوباره تلاش کنید.', 'diako' ),
				'phoneRequired' => __( 'شماره موبایل را وارد کنید.', 'diako' ),
				'phoneTooLong'  => sprintf(
					/* translators: %d: maximum digits */
					__( 'شماره موبایل نباید بیشتر از %d رقم باشد.', 'diako' ),
					diako_stock_notify_phone_max_length()
				),
				'phoneLength'   => sprintf(
					/* translators: %d: required digits */
					__( 'شماره موبایل باید %d رقم باشد.', 'diako' ),
					diako_stock_notify_phone_max_length()
				),
				'invalidPhone'  => __( 'شماره موبایل معتبر وارد کنید. (مثال: 09123456789)', 'diako' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'diako_enqueue_stock_notify_assets', 110 );

/**
 * Notify form when a selected variation is out of stock.
 */
function diako_render_stock_notify_variation_wrap(): void {
	global $product;

	if ( ! $product instanceof WC_Product || ! $product->is_type( 'variable' ) || ! $product->is_in_stock() ) {
		return;
	}
	?>
	<div class="diako-stock-notify-variation-wrap" data-stock-notify-variation-wrap hidden>
		<?php diako_render_stock_notify_form( $product->get_id(), 0, 'variation' ); ?>
	</div>
	<?php
}
add_action( 'woocommerce_after_add_to_cart_form', 'diako_render_stock_notify_variation_wrap', 15 );

/**
 * Notify form on single product when the whole variable product is unavailable.
 */
function diako_render_single_stock_notify_when_unavailable(): void {
	global $product;

	if ( ! $product instanceof WC_Product || $product->is_in_stock() ) {
		return;
	}

	if ( $product->is_type( 'simple' ) ) {
		return;
	}

	if ( $product->is_type( 'variable' ) ) {
		diako_render_stock_notify_form( $product->get_id(), 0, 'single' );
	}
}
add_action( 'woocommerce_single_product_summary', 'diako_render_single_stock_notify_when_unavailable', 32 );
