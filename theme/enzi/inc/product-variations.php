<?php
/**
 * Variable product variation UI on single product pages.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether every variation attribute has a default value configured.
 *
 * @param WC_Product $product Product.
 * @return bool
 */
function diako_variable_has_complete_default_selection( WC_Product $product ): bool {
	if ( ! $product instanceof WC_Product_Variable ) {
		return false;
	}

	$attributes = $product->get_variation_attributes();

	if ( empty( $attributes ) ) {
		return false;
	}

	foreach ( array_keys( $attributes ) as $attribute_name ) {
		if ( '' === $product->get_variation_default_attribute( $attribute_name ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Whether the add to cart button should start disabled for a variable product.
 *
 * @param WC_Product $product Product.
 * @return bool
 */
function diako_variable_add_to_cart_starts_disabled( WC_Product $product ): bool {
	return $product instanceof WC_Product_Variable && ! diako_variable_has_complete_default_selection( $product );
}

/**
 * Render one variation radio option.
 *
 * @param string $select_name Select field name.
 * @param string $select_id   Select field ID.
 * @param string $value       Option value.
 * @param string $label       Option label.
 * @param bool   $is_selected Whether selected.
 * @param string $attribute   Attribute taxonomy/key (e.g. pa_color).
 * @return string
 */
function diako_render_variation_radio_option( string $select_name, string $select_id, string $value, string $label, bool $is_selected, string $attribute = '' ): string {
	$input_id   = $select_id . '-v-' . sanitize_title( $value );
	$check_icon = diako_lucide_icon_svg( 'check', 'h-3.5 w-3.5' );
	$is_color   = $attribute && function_exists( 'diako_is_color_attribute' ) && diako_is_color_attribute( $attribute );
	$color      = $is_color && function_exists( 'diako_get_attribute_color' )
		? diako_get_attribute_color( $value, taxonomy_exists( $attribute ) ? $attribute : DIAKO_COLOR_ATTRIBUTE_TAXONOMY )
		: '';

	if ( $is_color && '' !== $color ) {
		$is_light = function_exists( 'diako_is_light_attribute_color' ) && diako_is_light_attribute_color( $color );
		$classes  = 'diako-variation-option diako-variation-option--swatch' . ( $is_selected ? ' is-selected' : '' ) . ( $is_light ? ' is-light' : '' );

		return sprintf(
			'<label class="%1$s" for="%2$s" title="%3$s"><input class="diako-variation-option__input" type="radio" id="%2$s" name="%4$s-radio" value="%5$s" data-diako-variation-radio data-target-select="%6$s" %7$s /><span class="diako-variation-option__swatch" style="background-color:%8$s" aria-hidden="true"><span class="diako-variation-option__check">%9$s</span></span><span class="sr-only">%3$s</span></label>',
			esc_attr( $classes ),
			esc_attr( $input_id ),
			esc_attr( $label ),
			esc_attr( $select_id ),
			esc_attr( $value ),
			esc_attr( $select_name ),
			checked( $is_selected, true, false ),
			esc_attr( $color ),
			$check_icon // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	return sprintf(
		'<label class="diako-variation-option%1$s" for="%2$s"><input class="diako-variation-option__input" type="radio" id="%2$s" name="%3$s-radio" value="%4$s" data-diako-variation-radio data-target-select="%5$s" %6$s /><span class="diako-variation-option__label"><span class="diako-variation-option__check" aria-hidden="true">%8$s</span><span class="diako-variation-option__text">%7$s</span></span></label>',
		$is_selected ? ' is-selected' : '',
		esc_attr( $input_id ),
		esc_attr( $select_id ),
		esc_attr( $value ),
		esc_attr( $select_name ),
		checked( $is_selected, true, false ),
		esc_html( $label ),
		$check_icon // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/**
 * Whether variation radios should replace dropdowns.
 *
 * @return bool
 */
function diako_should_use_variation_radios(): bool {
	if ( ! empty( $GLOBALS['diako_variation_radios_force'] ) ) {
		return true;
	}

	return function_exists( 'is_product' ) && is_product();
}

/**
 * Replace variation dropdowns with radio buttons (hidden select kept for WooCommerce JS).
 *
 * @param string               $html Dropdown HTML.
 * @param array<string, mixed> $args Dropdown args.
 * @return string
 */
function diako_variation_attribute_options_radio_html( string $html, array $args ): string {
	if ( ! diako_should_use_variation_radios() ) {
		return $html;
	}

	$product   = $args['product'] ?? null;
	$attribute = $args['attribute'] ?? '';

	if ( ! $product instanceof WC_Product_Variable || ! $attribute ) {
		return $html;
	}

	$options  = $args['options'] ?? false;
	$name     = $args['name'] ? (string) $args['name'] : 'attribute_' . sanitize_title( $attribute );
	$id       = $args['id'] ? (string) $args['id'] : sanitize_title( $attribute );
	$selected = isset( $args['selected'] ) ? (string) $args['selected'] : '';
	$class    = isset( $args['class'] ) ? (string) $args['class'] : '';
	$none     = isset( $args['show_option_none'] ) ? (string) $args['show_option_none'] : __( 'Choose an option', 'woocommerce' );

	if ( false === $options ) {
		$attributes = $product->get_variation_attributes();
		$options    = $attributes[ $attribute ] ?? array();
	}

	if ( empty( $options ) || ! is_array( $options ) ) {
		return $html;
	}

	$select_class = trim( 'diako-variation-select sr-only ' . $class );

	$select_html = sprintf(
		'<select class="%1$s" name="%2$s" id="%3$s" data-attribute_name="%2$s" data-show_option_none="%4$s">',
		esc_attr( $select_class ),
		esc_attr( $name ),
		esc_attr( $id ),
		esc_attr( $none )
	);
	$select_html .= '<option value="">' . esc_html( $none ) . '</option>';

	$options_class = 'diako-variation-options';

	if ( function_exists( 'diako_is_color_attribute' ) && diako_is_color_attribute( (string) $attribute ) ) {
		$options_class .= ' diako-variation-options--swatches';
	}

	$radios_html = sprintf(
		'<div class="%1$s" role="radiogroup" aria-labelledby="%2$s-label" data-diako-variation-radios>',
		esc_attr( $options_class ),
		esc_attr( $id )
	);

	$rendered_options = 0;

	if ( taxonomy_exists( $attribute ) ) {
		$terms = wc_get_product_terms(
			$product->get_id(),
			$attribute,
			array(
				'fields' => 'all',
			)
		);

		foreach ( $terms as $term ) {
			if ( ! $term instanceof WP_Term || ! in_array( $term->slug, $options, true ) ) {
				continue;
			}

			$is_selected  = $selected === $term->slug;
			$select_html .= sprintf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $term->slug ),
				selected( $is_selected, true, false ),
				esc_html( $term->name )
			);
			$radios_html .= diako_render_variation_radio_option( $name, $id, $term->slug, $term->name, $is_selected, (string) $attribute );
			++$rendered_options;
		}

		// Fallback: resolve option slugs directly when product term cache is stale/mismatched.
		if ( 0 === $rendered_options ) {
			foreach ( $options as $option ) {
				$option = (string) $option;
				$term   = get_term_by( 'slug', $option, $attribute );

				if ( ! $term instanceof WP_Term ) {
					continue;
				}

				$is_selected  = $selected === $term->slug;
				$select_html .= sprintf(
					'<option value="%1$s" %2$s>%3$s</option>',
					esc_attr( $term->slug ),
					selected( $is_selected, true, false ),
					esc_html( $term->name )
				);
				$radios_html .= diako_render_variation_radio_option( $name, $id, $term->slug, $term->name, $is_selected, (string) $attribute );
				++$rendered_options;
			}
		}
	}

	if ( 0 === $rendered_options ) {
		foreach ( $options as $option ) {
			$option       = (string) $option;
			$is_selected  = $selected === $option;
			$select_html .= sprintf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $option ),
				selected( $is_selected, true, false ),
				esc_html( $option )
			);
			$radios_html .= diako_render_variation_radio_option( $name, $id, $option, $option, $is_selected, (string) $attribute );
			++$rendered_options;
		}
	}

	$select_html .= '</select>';
	$radios_html .= '</div>';

	return '<div class="diako-variation-field">' . $radios_html . $select_html . '</div>';
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'diako_variation_attribute_options_radio_html', 20, 2 );

/**
 * Remove the reset variations link.
 *
 * @param string $link Reset link HTML.
 * @return string
 */
function diako_hide_reset_variations_link( string $link ): string {
	return '';
}
add_filter( 'woocommerce_reset_variations_link', 'diako_hide_reset_variations_link' );

/**
 * Hide the reset variations link.
 *
 * @return bool
 */
function diako_reset_variations_link_visibility(): bool {
	return false;
}
add_filter( 'woocommerce_reset_variations_link_visibility', 'diako_reset_variations_link_visibility' );

/**
 * Ensure WooCommerce variation scripts and wp.template dependencies load on variable products.
 *
 * @return void
 */
function diako_enqueue_variable_product_assets(): void {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	$product = wc_get_product( get_queried_object_id() );

	if ( ! $product instanceof WC_Product_Variable ) {
		return;
	}

	wp_enqueue_script( 'underscore' );
	wp_enqueue_script( 'wp-util' );
	wp_enqueue_script( 'wc-add-to-cart-variation' );
}
add_action( 'wp_enqueue_scripts', 'diako_enqueue_variable_product_assets', 25 );

/**
 * Keep deferred variation dependencies ordered after jQuery.
 *
 * @param array<int, string> $handles Script handles with defer enabled.
 * @return array<int, string>
 */
function diako_variable_product_defer_script_handles( array $handles ): array {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return $handles;
	}

	$product = wc_get_product( get_queried_object_id() );

	if ( ! $product instanceof WC_Product_Variable ) {
		return $handles;
	}

	return array_values(
		array_unique(
			array_merge(
				$handles,
				array(
					'underscore',
					'wp-util',
					'wc-add-to-cart-variation',
				)
			)
		)
	);
}
add_filter( 'diako_defer_script_handles', 'diako_variable_product_defer_script_handles' );
