<?php
/**
 * Merge duplicate global WooCommerce attributes.
 *
 * Usage: wp eval-file merge-global-attributes.php --user=iminiaki
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce is not active.' );
}

$merges = array(
	array(
		'from' => 'pa_connectivity',
		'to'   => 'pa_connectivity-features',
	),
	array(
		'from' => 'pa_management-software',
		'to'   => 'pa_management',
	),
);

/**
 * Ensure a term exists in target taxonomy, reusing by name when possible.
 */
function merge_attr_ensure_target_term( string $target_taxonomy, string $name ): int {
	$name = trim( $name );
	if ( $name === '' ) {
		return 0;
	}

	$existing = get_term_by( 'name', $name, $target_taxonomy );
	if ( $existing && ! is_wp_error( $existing ) ) {
		return (int) $existing->term_id;
	}

	$created = wp_insert_term( $name, $target_taxonomy );
	if ( is_wp_error( $created ) ) {
		$existing = get_term_by( 'name', $name, $target_taxonomy );
		return $existing ? (int) $existing->term_id : 0;
	}

	return (int) $created['term_id'];
}

/**
 * Merge one attribute taxonomy into another.
 */
function merge_attr_taxonomies( string $from_taxonomy, string $to_taxonomy ): array {
	global $wpdb;

	$stats = array(
		'products_updated' => 0,
		'terms_moved'      => 0,
		'attribute_deleted'=> false,
	);

	if ( ! taxonomy_exists( $from_taxonomy ) ) {
		WP_CLI::warning( "Source taxonomy {$from_taxonomy} does not exist. Skipping." );
		return $stats;
	}

	if ( ! taxonomy_exists( $to_taxonomy ) ) {
		WP_CLI::error( "Target taxonomy {$to_taxonomy} does not exist." );
	}

	$product_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT tr.object_id
			FROM {$wpdb->term_relationships} tr
			INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
			INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
			WHERE tt.taxonomy = %s
			AND p.post_type = 'product'
			AND p.post_status != 'trash'",
			$from_taxonomy
		)
	);

	foreach ( $product_ids as $product_id ) {
		$product_id = (int) $product_id;
		$source_terms = wp_get_object_terms( $product_id, $from_taxonomy, array( 'fields' => 'all' ) );
		if ( is_wp_error( $source_terms ) || empty( $source_terms ) ) {
			continue;
		}

		$target_term_ids = wp_get_object_terms( $product_id, $to_taxonomy, array( 'fields' => 'ids' ) );
		if ( is_wp_error( $target_term_ids ) ) {
			$target_term_ids = array();
		}

		foreach ( $source_terms as $term ) {
			$target_term_id = merge_attr_ensure_target_term( $to_taxonomy, $term->name );
			if ( $target_term_id ) {
				$target_term_ids[] = $target_term_id;
				$stats['terms_moved']++;
			}
		}

		$target_term_ids = array_values( array_unique( array_map( 'intval', $target_term_ids ) ) );
		wp_set_object_terms( $product_id, $target_term_ids, $to_taxonomy, false );
		wp_set_object_terms( $product_id, array(), $from_taxonomy, false );

		$attributes = get_post_meta( $product_id, '_product_attributes', true );
		if ( is_array( $attributes ) ) {
			$from_attr = $attributes[ $from_taxonomy ] ?? null;
			unset( $attributes[ $from_taxonomy ] );

			if ( ! isset( $attributes[ $to_taxonomy ] ) && $from_attr ) {
				$attributes[ $to_taxonomy ] = array(
					'name'         => $to_taxonomy,
					'value'        => '',
					'position'     => (int) ( $from_attr['position'] ?? 0 ),
					'is_visible'   => (int) ( $from_attr['is_visible'] ?? 1 ),
					'is_variation' => (int) ( $from_attr['is_variation'] ?? 0 ),
					'is_taxonomy'  => 1,
				);
			}

			update_post_meta( $product_id, '_product_attributes', $attributes );
		}

		$stats['products_updated']++;
	}

	$from_slug = str_replace( 'pa_', '', $from_taxonomy );
	$attribute = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT attribute_id FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s",
			$from_slug
		)
	);

	if ( $attribute ) {
		$deleted = wc_delete_attribute( (int) $attribute->attribute_id );
		$stats['attribute_deleted'] = ! is_wp_error( $deleted );
	}

	delete_transient( 'wc_attribute_taxonomies' );

	return $stats;
}

foreach ( $merges as $merge ) {
	WP_CLI::log( "Merging {$merge['from']} -> {$merge['to']}..." );
	$stats = merge_attr_taxonomies( $merge['from'], $merge['to'] );
	WP_CLI::log(
		sprintf(
			"  Products updated: %d | Terms moved: %d | Source deleted: %s",
			$stats['products_updated'],
			$stats['terms_moved'],
			$stats['attribute_deleted'] ? 'yes' : 'no'
		)
	);
}

delete_transient( 'wc_attribute_taxonomies' );

WP_CLI::success( 'Attribute merges completed. Run lookup regeneration separately if needed.' );
