<?php
/**
 * Convert an existing simple product into a variable product with shade variations.
 *
 * Usage: wp eval-file /scripts/make-variable-product.php
 * Optional: wp eval-file /scripts/make-variable-product.php -- --slug=velvet-matte-lipstick
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wc_get_product' ) ) {
	WP_CLI::error( 'WooCommerce is not available.' );
}

$slug = 'velvet-matte-lipstick';

foreach ( $args as $arg ) {
	if ( 0 === strpos( $arg, '--slug=' ) ) {
		$slug = sanitize_title( substr( $arg, 7 ) );
	}
}

$posts = get_posts(
	array(
		'name'           => $slug,
		'post_type'      => 'product',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	)
);

if ( empty( $posts ) ) {
	WP_CLI::error( "Product with slug '{$slug}' not found." );
}

$product_id = (int) $posts[0];
$product    = wc_get_product( $product_id );

if ( ! $product ) {
	WP_CLI::error( 'Could not load product.' );
}

if ( $product->is_type( 'variable' ) ) {
	WP_CLI::success( "Product #{$product_id} is already variable." );
	return;
}

$base_price   = $product->get_regular_price() ?: $product->get_price() ?: '450000';
$sale_price   = $product->get_sale_price();
$stock_qty    = $product->get_stock_quantity();
$stock_qty    = null !== $stock_qty ? (int) $stock_qty : 40;
$attr_name    = 'رنگ';
$attr_slug    = 'pa_color';
$option_slugs = array( 'nude', 'rose', 'berry' );
$option_labels = array(
	'nude'  => 'نود',
	'rose'  => 'رز',
	'berry' => 'بری',
);
$option_prices = array(
	'nude'  => array(
		'regular' => (string) $base_price,
		'sale'    => $sale_price ? (string) $sale_price : '',
	),
	'rose'  => array(
		'regular' => (string) ( (float) $base_price + 30000 ),
		'sale'    => $sale_price ? (string) ( (float) $sale_price + 30000 ) : '',
	),
	'berry' => array(
		'regular' => (string) ( (float) $base_price + 50000 ),
		'sale'    => $sale_price ? (string) ( (float) $sale_price + 50000 ) : '',
	),
);

// Ensure global attribute taxonomy exists.
if ( ! taxonomy_exists( $attr_slug ) ) {
	$attribute_id = wc_create_attribute(
		array(
			'name'         => $attr_name,
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		)
	);

	if ( is_wp_error( $attribute_id ) ) {
		WP_CLI::error( $attribute_id->get_error_message() );
	}

	register_taxonomy(
		$attr_slug,
		apply_filters( 'woocommerce_taxonomy_objects_' . $attr_slug, array( 'product' ) ),
		apply_filters(
			'woocommerce_taxonomy_args_' . $attr_slug,
			array(
				'labels'       => array(
					'name' => $attr_name,
				),
				'hierarchical' => false,
				'show_ui'      => false,
				'query_var'    => true,
				'rewrite'      => false,
			)
		)
	);
}

$term_ids = array();

foreach ( $option_slugs as $option_slug ) {
	$existing = get_term_by( 'slug', $option_slug, $attr_slug );

	if ( $existing instanceof WP_Term ) {
		if ( $existing->name !== $option_labels[ $option_slug ] ) {
			wp_update_term(
				$existing->term_id,
				$attr_slug,
				array(
					'name' => $option_labels[ $option_slug ],
				)
			);
		}
		$term_ids[] = (int) $existing->term_id;
		continue;
	}

	$result = wp_insert_term(
		$option_labels[ $option_slug ],
		$attr_slug,
		array(
			'slug' => $option_slug,
		)
	);

	if ( is_wp_error( $result ) ) {
		WP_CLI::warning( $result->get_error_message() );
		continue;
	}

	$term_ids[] = (int) $result['term_id'];
}

if ( empty( $term_ids ) ) {
	WP_CLI::error( 'Could not resolve color attribute terms.' );
}

wp_set_object_terms( $product_id, $term_ids, $attr_slug );

$attribute = new WC_Product_Attribute();
$attribute->set_id( wc_attribute_taxonomy_id_by_name( 'color' ) );
$attribute->set_name( $attr_slug );
$attribute->set_options( $term_ids );
$attribute->set_position( 0 );
$attribute->set_visible( true );
$attribute->set_variation( true );

// Convert product type to variable.
wp_remove_object_terms( $product_id, 'simple', 'product_type' );
wp_set_object_terms( $product_id, 'variable', 'product_type', false );

$variable = new WC_Product_Variable( $product_id );
$variable->set_attributes( array( $attribute ) );
$variable->set_default_attributes( array() );
$variable->set_manage_stock( false );
$variable->set_stock_status( 'instock' );
$variable->set_regular_price( '' );
$variable->set_sale_price( '' );
$variable->set_price( '' );
$variable->save();

// Remove leftover simple price meta that can confuse variable pricing.
delete_post_meta( $product_id, '_regular_price' );
delete_post_meta( $product_id, '_sale_price' );
delete_post_meta( $product_id, '_price' );

$existing_children = $variable->get_children();

foreach ( $existing_children as $child_id ) {
	$child = wc_get_product( $child_id );
	if ( $child ) {
		$child->delete( true );
	}
}

$per_variation_stock = max( 1, (int) floor( $stock_qty / count( $option_slugs ) ) );

foreach ( $option_slugs as $index => $option_slug ) {
	$variation = new WC_Product_Variation();
	$variation->set_parent_id( $product_id );
	$variation->set_attributes(
		array(
			$attr_slug => $option_slug,
		)
	);
	$variation->set_status( 'publish' );
	$variation->set_regular_price( $option_prices[ $option_slug ]['regular'] );

	if ( '' !== $option_prices[ $option_slug ]['sale'] ) {
		$variation->set_sale_price( $option_prices[ $option_slug ]['sale'] );
	}

	$variation->set_manage_stock( true );
	$variation->set_stock_quantity( $per_variation_stock );
	$variation->set_stock_status( 'instock' );
	$variation->set_sku( $product->get_sku() ? $product->get_sku() . '-' . strtoupper( $option_slug ) : '' );
	$variation->save();

	WP_CLI::log( sprintf( 'Created variation #%d (%s)', $variation->get_id(), $option_labels[ $option_slug ] ) );
}

WC_Product_Variable::sync( $product_id );
wc_delete_product_transients( $product_id );

$fresh = wc_get_product( $product_id );

WP_CLI::success(
	sprintf(
		'Converted "%s" (#%d) to variable with %d variations. Permalink: %s',
		$fresh->get_name(),
		$product_id,
		count( $fresh->get_children() ),
		$fresh->get_permalink()
	)
);
