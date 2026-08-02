<?php
/**
 * Checkout customizations.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default store country (Iran).
 *
 * @return string
 */
function diako_get_store_country_code(): string {
	return 'IR';
}

/**
 * Resolve a Persian WooCommerce Shipping state code to its label.
 *
 * @param string $state_code State code or PWS term ID.
 * @param string $country    Country code.
 * @return string
 */
function diako_resolve_pws_state_name( string $state_code, string $country = 'IR' ): string {
	$state_code = trim( $state_code );

	if ( '' === $state_code ) {
		return '';
	}

	$states = WC()->countries->get_states( $country ) ?: array();

	if ( isset( $states[ $state_code ] ) ) {
		return (string) $states[ $state_code ];
	}

	if ( ctype_digit( $state_code ) ) {
		$term = get_term( (int) $state_code, 'state_city' );

		if ( $term && ! is_wp_error( $term ) ) {
			return (string) $term->name;
		}
	}

	return $state_code;
}

/**
 * Resolve a PWS city or district term ID to its label.
 *
 * @param string $value Location value.
 * @param string $type  Either city or district.
 * @return string
 */
function diako_resolve_pws_location_name( string $value, string $type = 'city' ): string {
	$value = trim( $value );

	if ( '' === $value ) {
		return '';
	}

	if ( ! ctype_digit( $value ) ) {
		return $value;
	}

	$term_id = (int) $value;

	if ( class_exists( 'PWS' ) ) {
		if ( 'district' === $type ) {
			$name = PWS()::get_district( $term_id );
		} else {
			$name = PWS()::get_city( $term_id );
		}

		if ( $name ) {
			return (string) $name;
		}
	}

	$term = get_term( $term_id, 'state_city' );

	if ( $term && ! is_wp_error( $term ) ) {
		return (string) $term->name;
	}

	return $value;
}

/**
 * Shipping address parts for order summary display.
 *
 * @param array<string, string> $destination Shipping package destination.
 * @return array<string, string>
 */
function diako_get_shipping_address_parts( array $destination = array() ): array {
	$customer   = WC()->customer;
	$country    = $destination['country'] ?? ( $customer ? $customer->get_shipping_country() : diako_get_store_country_code() );
	$state_code = $destination['state'] ?? ( $customer ? $customer->get_shipping_state() : '' );
	$state      = diako_resolve_pws_state_name( (string) $state_code, $country );

	$city     = diako_resolve_pws_location_name( (string) ( $destination['city'] ?? ( $customer ? $customer->get_shipping_city() : '' ) ), 'city' );
	$street   = $destination['address'] ?? $destination['address_1'] ?? ( $customer ? $customer->get_shipping_address_1() : '' );
	$postcode = $destination['postcode'] ?? ( $customer ? $customer->get_shipping_postcode() : '' );

	$district = $destination['district'] ?? '';
	if ( '' === $district && $customer ) {
		$district = (string) $customer->get_meta( 'shipping_district' );
	}
	if ( '' === $district && WC()->session ) {
		$district = (string) WC()->session->get( 'shipping_district', '' );
	}
	$district = diako_resolve_pws_location_name( $district, 'district' );

	$parts = array(
		'state'    => trim( (string) $state ),
		'city'     => trim( (string) $city ),
		'district' => trim( (string) $district ),
		'street'   => trim( (string) $street ),
		'postcode' => trim( (string) $postcode ),
	);

	return array_filter( $parts );
}

/**
 * Formatted shipping address for cart/checkout summaries.
 *
 * @param array<string, string> $destination Shipping package destination.
 * @return string
 */
function diako_format_shipping_address( array $destination = array() ): string {
	$parts = diako_get_shipping_address_parts( $destination );

	if ( empty( $parts ) ) {
		return '';
	}

	return diako_filter_persian_digits_string( implode( '، ', $parts ) );
}

/**
 * Whether a shipping address is available for display.
 *
 * @param array<string, string> $destination Shipping package destination.
 * @return bool
 */
function diako_has_shipping_address( array $destination = array() ): bool {
	return '' !== diako_format_shipping_address( $destination );
}

add_action(
	'init',
	function () {
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	},
	5
);

add_filter( 'woocommerce_checkout_show_terms', '__return_false' );
add_filter( 'wc_checkout_show_terms_checkbox', '__return_false' );
add_filter( 'woocommerce_terms_and_conditions_checkbox_enabled', '__return_false' );

add_filter(
	'default_checkout_billing_country',
	function () {
		return diako_get_store_country_code();
	}
);

add_filter(
	'default_checkout_shipping_country',
	function () {
		return diako_get_store_country_code();
	}
);

/**
 * Whether the current request is the My Account edit address screen.
 *
 * @return bool
 */
function diako_is_edit_address_page(): bool {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return false;
	}

	if ( ! function_exists( 'is_wc_endpoint_url' ) ) {
		return false;
	}

	return is_wc_endpoint_url( 'edit-address' );
}

/**
 * Whether checkout-style address field classes should be applied.
 *
 * @return bool
 */
function diako_uses_checkout_address_form_styles(): bool {
	if ( diako_is_edit_address_page() ) {
		return true;
	}

	return diako_is_checkout_page() && ! diako_is_checkout_thankyou_page();
}

/**
 * Apply checkout-style layout to billing/shipping address fields.
 *
 * @param array<string, array<string, mixed>> $fields Address fields.
 * @param string                              $prefix Field prefix: billing or shipping.
 * @return array<string, array<string, mixed>>
 */
function diako_customize_address_field_layout( array $fields, string $prefix ): array {
	unset( $fields[ $prefix . '_country' ] );
	unset( $fields[ $prefix . '_address_2' ] );

	if ( isset( $fields[ $prefix . '_state' ], $fields[ $prefix . '_city' ] ) ) {
		$fields[ $prefix . '_state' ]['priority'] = 70;
		$fields[ $prefix . '_state' ]['class']    = array(
			'form-row-last',
			'address-field',
			'diako-checkout-field-row',
		);

		$fields[ $prefix . '_city' ]['priority'] = 80;
		$fields[ $prefix . '_city' ]['class']    = array(
			'form-row-first',
			'address-field',
			'diako-checkout-field-row',
		);
	} elseif ( isset( $fields[ $prefix . '_state' ] ) ) {
		$fields[ $prefix . '_state' ]['class'] = array(
			'form-row-first',
			'address-field',
			'diako-checkout-field-row',
		);
	} elseif ( isset( $fields[ $prefix . '_city' ] ) ) {
		$fields[ $prefix . '_city' ]['class'] = array(
			'form-row-last',
			'address-field',
			'diako-checkout-field-row',
		);
	}

	if ( isset( $fields[ $prefix . '_district' ] ) ) {
		$fields[ $prefix . '_district' ]['priority'] = 85;
		$fields[ $prefix . '_district' ]['class']    = array(
			'form-row-wide',
			'address-field',
			'diako-checkout-field-row',
		);
		unset( $fields[ $prefix . '_district' ]['clear'] );
	}

	if ( isset( $fields[ $prefix . '_first_name' ] ) ) {
		$fields[ $prefix . '_first_name' ]['priority'] = 10;
		$fields[ $prefix . '_first_name' ]['class']    = array(
			'form-row-first',
			'diako-checkout-field-row',
		);
	}

	if ( isset( $fields[ $prefix . '_last_name' ] ) ) {
		$fields[ $prefix . '_last_name' ]['priority'] = 20;
		$fields[ $prefix . '_last_name' ]['class']   = array(
			'form-row-last',
			'diako-checkout-field-row',
		);
	}

	if ( isset( $fields[ $prefix . '_address_1' ] ) ) {
		$fields[ $prefix . '_address_1' ]['priority'] = 88;
		$fields[ $prefix . '_address_1' ]['class']    = array(
			'form-row-wide',
			'address-field',
			'diako-checkout-field-row',
		);
	}

	if ( isset( $fields[ $prefix . '_postcode' ] ) ) {
		$fields[ $prefix . '_postcode' ]['priority'] = 90;
		$fields[ $prefix . '_postcode' ]['class']    = array(
			'form-row-first',
			'address-field',
			'diako-checkout-field-row',
		);
	}

	if ( 'billing' === $prefix && isset( $fields['billing_phone'] ) ) {
		$fields['billing_phone']['priority'] = 100;
		$fields['billing_phone']['class']    = array(
			'form-row-last',
			'diako-checkout-field-row',
		);
	}

	if ( 'billing' === $prefix && isset( $fields['billing_email'] ) ) {
		$fields['billing_email']['priority'] = 110;
		$fields['billing_email']['class']    = array(
			'form-row-wide',
			'diako-checkout-field-row',
		);
	}

	return diako_apply_phone_ltr_field_attrs( $fields );
}

/**
 * Force phone form fields to render left-to-right.
 *
 * @param array<string, array<string, mixed>> $fields Address or checkout fields.
 * @return array<string, array<string, mixed>>
 */
function diako_apply_phone_ltr_field_attrs( array $fields ): array {
	foreach ( $fields as $key => $field ) {
		if ( ! is_array( $field ) ) {
			continue;
		}

		$type = (string) ( $field['type'] ?? '' );

		if ( 'tel' !== $type && false === strpos( (string) $key, 'phone' ) ) {
			continue;
		}

		$custom_attributes = isset( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] )
			? $field['custom_attributes']
			: array();
		$custom_attributes['dir'] = 'ltr';

		$input_class = $field['input_class'] ?? array();
		if ( ! is_array( $input_class ) ) {
			$input_class = array_filter( array_map( 'trim', explode( ' ', (string) $input_class ) ) );
		}
		$input_class[]                       = 'diako-phone-ltr';
		$fields[ $key ]['custom_attributes'] = $custom_attributes;
		$fields[ $key ]['input_class']       = $input_class;
	}

	return $fields;
}

/**
 * Customize billing and shipping checkout fields for Iran.
 *
 * @param array<string, array<string, array<string, mixed>>> $fields Checkout fields.
 * @return array<string, array<string, array<string, mixed>>>
 */
function diako_customize_checkout_fields( array $fields ): array {
	foreach ( array( 'billing', 'shipping' ) as $type ) {
		if ( ! isset( $fields[ $type ] ) ) {
			continue;
		}

		$fields[ $type ] = diako_customize_address_field_layout( $fields[ $type ], $type );
	}

	if ( WC()->customer ) {
		WC()->customer->set_billing_country( diako_get_store_country_code() );
		WC()->customer->set_shipping_country( diako_get_store_country_code() );
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'diako_customize_checkout_fields', 999 );

/**
 * Customize My Account edit address fields to match checkout.
 *
 * @param array<string, array<string, mixed>> $address      Address fields.
 * @param string                              $load_address Address type.
 * @return array<string, array<string, mixed>>
 */
function diako_customize_edit_address_fields( array $address, string $load_address ): array {
	if ( ! in_array( $load_address, array( 'billing', 'shipping' ), true ) ) {
		return $address;
	}

	$values = array();

	foreach ( $address as $key => $field ) {
		$values[ $key ] = $field['value'] ?? '';
	}

	if ( function_exists( 'WC' ) && WC()->checkout() ) {
		$checkout_fields = WC()->checkout()->get_checkout_fields( $load_address );

		foreach ( $checkout_fields as $key => $checkout_field ) {
			if ( 0 !== strpos( $key, $load_address . '_' ) ) {
				continue;
			}

			if ( isset( $address[ $key ] ) ) {
				$address[ $key ] = array_merge( $address[ $key ], $checkout_field );
			} else {
				$address[ $key ] = $checkout_field;
			}
		}
	}

	foreach ( $values as $key => $value ) {
		if ( isset( $address[ $key ] ) ) {
			$address[ $key ]['value'] = $value;
		}
	}

	$district_key = $load_address . '_district';

	if ( isset( $address[ $district_key ] ) ) {
		$user_id = get_current_user_id();

		if ( $user_id ) {
			$district_value = get_user_meta( $user_id, $district_key, true );

			if ( $district_value ) {
				$address[ $district_key ]['value'] = $district_value;
			}
		}
	}

	$address = diako_customize_address_field_layout( $address, $load_address );

	uasort( $address, 'wc_checkout_fields_uasort_comparison' );

	return $address;
}
add_filter( 'woocommerce_address_to_edit', 'diako_customize_edit_address_fields', 999, 2 );

add_action(
	'woocommerce_before_save_address_validation',
	function ( $user_id, $address_type ) {
		unset( $user_id );
		$_POST[ $address_type . '_country' ] = diako_get_store_country_code();
	},
	10,
	2
);

add_action(
	'woocommerce_customer_save_address',
	function ( $user_id, $address_type ) {
		update_user_meta( $user_id, $address_type . '_country', diako_get_store_country_code() );
	},
	10,
	2
);

add_filter(
	'woocommerce_checkout_posted_data',
	function ( $data ) {
		$country = diako_get_store_country_code();

		$data['billing_country']  = $country;
		$data['shipping_country'] = $country;

		return $data;
	}
);

add_filter(
	'woocommerce_form_field_args',
	function ( $args, $key, $value ) {
		if ( ! diako_uses_checkout_address_form_styles() ) {
			return $args;
		}

		$args['class'][]       = 'diako-checkout-field';
		$args['input_class'][] = 'diako-checkout-field__input';
		$args['label_class'][] = 'diako-checkout-field__label';

		if ( in_array( $args['type'], array( 'select', 'state', 'country', 'billing_city', 'shipping_city', 'billing_district', 'shipping_district' ), true ) ) {
			$args['input_class'][] = 'diako-checkout-field__select';
		}

		return $args;
	},
	20,
	3
);

/**
 * Render checkout coupon inside the main column.
 *
 * @return void
 */
function diako_render_checkout_coupon_form() {
	if ( ! wc_coupons_enabled() ) {
		return;
	}

	wc_get_template( 'checkout/form-coupon.php' );
}

/**
 * Load checkout CSS overrides after plugin styles.
 *
 * @return void
 */
function diako_enqueue_checkout_overrides() {
	if ( ! diako_should_enqueue_checkout_overrides() ) {
		return;
	}

	$path = DIAKO_DIR . '/assets/css/checkout-overrides.css';

	if ( ! file_exists( $path ) ) {
		return;
	}

	wp_enqueue_style(
		'diako-checkout-overrides',
		DIAKO_URI . '/assets/css/checkout-overrides.css',
		array( 'select2' ),
		(string) filemtime( $path )
	);
}
add_action( 'wp_enqueue_scripts', 'diako_enqueue_checkout_overrides', 1001 );

/**
 * Structured shipping method label for checkout/cart cards.
 *
 * @param WC_Shipping_Rate $method Shipping rate.
 * @return string
 */
function diako_get_shipping_method_label_markup( $method ): string {
	if ( ! is_a( $method, 'WC_Shipping_Rate' ) ) {
		return '';
	}

	$name      = $method->get_label();
	$has_cost  = 0 < $method->cost;
	$hide_cost = ! $has_cost && in_array( $method->get_method_id(), array( 'free_shipping', 'local_pickup' ), true );
	$price     = '';

	if ( $has_cost && ! $hide_cost && WC()->cart ) {
		if ( WC()->cart->display_prices_including_tax() ) {
			$price = wc_price( $method->cost + $method->get_shipping_tax() );
		} else {
			$price = wc_price( $method->cost );
		}
	}

	$html  = '<span class="diako-shipping-method__name">' . esc_html( $name ) . '</span>';
	$html .= $price ? '<span class="diako-shipping-method__price">' . wp_kses_post( $price ) . '</span>' : '';

	return '<span class="diako-shipping-method__content diako-shipping-method__content--split">' . $html . '</span>';
}

add_filter(
	'woocommerce_cart_shipping_method_full_label',
	function ( $label, $method ) {
		if ( ! is_cart() && ! diako_is_checkout_page() ) {
			return $label;
		}

		return diako_get_shipping_method_label_markup( $method );
	},
	20,
	2
);

/**
 * Structured address parts for order display.
 *
 * @param WC_Order $order Order.
 * @param string   $type  billing|shipping.
 * @return array<string, string>
 */
function diako_get_order_address_parts( WC_Order $order, string $type = 'billing' ): array {
	if ( 'shipping' === $type ) {
		$country    = $order->get_shipping_country();
		$state_code = $order->get_shipping_state();
		$city_value = $order->get_shipping_city();
		$street     = $order->get_shipping_address_1();
		$postcode   = $order->get_shipping_postcode();
		$name       = trim( $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() );
		$phone      = $order->get_shipping_phone() ?: $order->get_billing_phone();
		$email      = '';
		$district   = (string) $order->get_meta( 'shipping_district' );
	} else {
		$country    = $order->get_billing_country();
		$state_code = $order->get_billing_state();
		$city_value = $order->get_billing_city();
		$street     = $order->get_billing_address_1();
		$postcode   = $order->get_billing_postcode();
		$name       = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
		$phone      = $order->get_billing_phone();
		$email      = $order->get_billing_email();
		$district   = (string) $order->get_meta( 'billing_district' );
	}

	$state    = diako_resolve_pws_state_name( (string) $state_code, (string) $country );
	$city     = diako_resolve_pws_location_name( (string) $city_value, 'city' );
	$district = diako_resolve_pws_location_name( $district, 'district' );
	$location = array_values(
		array_unique(
			array_filter(
				array(
					$district,
					$city,
					$state !== $city ? $state : '',
				)
			)
		)
	);

	$parts = array(
		'name'     => $name,
		'location' => implode( '، ', $location ),
		'street'   => trim( (string) $street ),
		'postcode' => trim( (string) $postcode ),
		'phone'    => trim( (string) $phone ),
		'email'    => trim( (string) $email ),
	);

	return array_filter( $parts );
}

/**
 * Render a formatted order address card.
 *
 * @param WC_Order $order Order.
 * @param string   $type  billing|shipping.
 * @return void
 */
function diako_render_order_address_card( WC_Order $order, string $type = 'billing' ): void {
	$parts = diako_get_order_address_parts( $order, $type );

	if ( empty( $parts ) ) {
		echo '<p class="diako-order-address-card__empty">' . esc_html__( 'ثبت نشده', 'diako' ) . '</p>';
		return;
	}
	?>
	<div class="diako-order-address-card__lines">
		<?php if ( ! empty( $parts['name'] ) ) : ?>
			<p class="diako-order-address-card__name"><?php echo esc_html( $parts['name'] ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $parts['location'] ) ) : ?>
			<p class="diako-order-address-card__line">
				<?php echo diako_lucide_icon_svg( 'map-pin', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $parts['location'] ); ?></span>
			</p>
		<?php endif; ?>

		<?php if ( ! empty( $parts['street'] ) ) : ?>
			<p class="diako-order-address-card__line">
				<?php echo diako_lucide_icon_svg( 'map-pin', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $parts['street'] ); ?></span>
			</p>
		<?php endif; ?>

		<?php if ( ! empty( $parts['postcode'] ) ) : ?>
			<p class="diako-order-address-card__line diako-order-address-card__line--muted">
				<span><?php echo esc_html( sprintf( __( 'کد پستی: %s', 'diako' ), diako_number( $parts['postcode'] ) ) ); ?></span>
			</p>
		<?php endif; ?>

		<?php if ( ! empty( $parts['phone'] ) ) : ?>
			<p class="diako-order-address-card__line">
				<?php echo diako_lucide_icon_svg( 'phone', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php diako_render_phone_link( $parts['phone'] ); ?>
			</p>
		<?php endif; ?>

		<?php if ( ! empty( $parts['email'] ) ) : ?>
			<p class="diako-order-address-card__line">
				<?php echo diako_lucide_icon_svg( 'mail', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<a href="<?php echo esc_url( 'mailto:' . $parts['email'] ); ?>"><?php echo esc_html( $parts['email'] ); ?></a>
			</p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Whether checkout CSS overrides should load.
 *
 * @return bool
 */
function diako_should_enqueue_checkout_overrides(): bool {
	return diako_uses_checkout_address_form_styles()
		|| ( function_exists( 'diako_is_checkout_order_pay_page' ) && diako_is_checkout_order_pay_page() );
}

/**
 * Render order summary cards on the pay-for-order screen.
 *
 * @param WC_Order $order Order.
 * @return void
 */
function diako_render_order_pay_summary( WC_Order $order ): void {
	?>
	<ul class="woocommerce-order-overview diako-checkout-thankyou__details diako-order-pay__summary">
		<li class="woocommerce-order-overview__order order">
			<span class="diako-checkout-thankyou__detail-label"><?php esc_html_e( 'Order number:', 'woocommerce' ); ?></span>
			<strong><?php echo esc_html( diako_number( $order->get_order_number() ) ); ?></strong>
		</li>

		<li class="woocommerce-order-overview__date date">
			<span class="diako-checkout-thankyou__detail-label"><?php esc_html_e( 'Date:', 'woocommerce' ); ?></span>
			<strong><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></strong>
		</li>

		<li class="woocommerce-order-overview__status status">
			<span class="diako-checkout-thankyou__detail-label"><?php esc_html_e( 'Status', 'woocommerce' ); ?></span>
			<strong class="diako-checkout-thankyou__status-badge diako-checkout-thankyou__status-badge--<?php echo esc_attr( $order->get_status() ); ?>">
				<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
			</strong>
		</li>

		<li class="woocommerce-order-overview__total total">
			<span class="diako-checkout-thankyou__detail-label"><?php esc_html_e( 'Total:', 'woocommerce' ); ?></span>
			<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
		</li>

		<?php if ( $order->get_payment_method_title() ) : ?>
			<li class="woocommerce-order-overview__payment-method method">
				<span class="diako-checkout-thankyou__detail-label"><?php esc_html_e( 'Payment method:', 'woocommerce' ); ?></span>
				<strong><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
			</li>
		<?php endif; ?>
	</ul>
	<?php
}

add_filter(
	'woocommerce_pay_order_button_text',
	static function () {
		return __( 'پرداخت سفارش', 'diako' );
	}
);

add_action(
	'woocommerce_before_customer_login_form',
	static function () {
		if ( ! function_exists( 'diako_is_checkout_order_pay_page' ) || ! diako_is_checkout_order_pay_page() ) {
			return;
		}

		echo '<div class="diako-order-pay-auth">';
	},
	5
);

add_action(
	'woocommerce_after_customer_login_form',
	static function () {
		if ( ! function_exists( 'diako_is_checkout_order_pay_page' ) || ! diako_is_checkout_order_pay_page() ) {
			return;
		}

		echo '</div>';
	},
	99
);
