<?php
/**
 * LEGACY (gaming) — not used by Enzi cosmetics shop.
 * Kept only for reference; do not run on this project.
 *
 * Create the global "ژانر بازی" attribute and assign genres to game products.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce is not active.' );
}

require_once __DIR__ . '/game-genre-rules.php';

const DIAKO_GAME_GENRE_ATTR_LABEL = 'ژانر بازی';
const DIAKO_GAME_GENRE_ATTR_SLUG  = 'game-genre';

/**
 * Parse CLI flags passed after `--`.
 *
 * @return array{dry_run: bool, force: bool, category: string}
 */
function diako_assign_game_genres_parse_args(): array {
	$args = array(
		'dry_run'  => ! empty( getenv( 'DIAKO_DRY_RUN' ) ),
		'force'    => ! empty( getenv( 'DIAKO_FORCE' ) ),
		'category' => getenv( 'DIAKO_CATEGORY' ) ? sanitize_title( (string) getenv( 'DIAKO_CATEGORY' ) ) : 'games',
	);

	foreach ( array_slice( $GLOBALS['argv'] ?? array(), 1 ) as $arg ) {
		if ( '--dry-run' === $arg || 'dry-run' === $arg ) {
			$args['dry_run'] = true;
		} elseif ( '--force' === $arg || 'force' === $arg ) {
			$args['force'] = true;
		} elseif ( str_starts_with( $arg, '--category=' ) ) {
			$args['category'] = sanitize_title( substr( $arg, 11 ) );
		}
	}

	return $args;
}

/**
 * Ensure the global game-genre attribute exists with the correct label.
 *
 * @return string Taxonomy name (pa_game-genre) or empty on failure.
 */
function diako_ensure_game_genre_attribute(): string {
	global $wpdb;

	$slug     = DIAKO_GAME_GENRE_ATTR_SLUG;
	$label    = DIAKO_GAME_GENRE_ATTR_LABEL;
	$taxonomy = wc_attribute_taxonomy_name( $slug );

	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT attribute_id, attribute_label, attribute_public
			FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
			WHERE attribute_name = %s",
			$slug
		)
	);

	if ( $row ) {
		$updates = array();

		if ( $row->attribute_label !== $label ) {
			$updates['attribute_label'] = $label;
		}

		// attribute_public enables layered nav / filtering in WooCommerce.
		if ( (int) $row->attribute_public !== 1 ) {
			$updates['attribute_public'] = 1;
		}

		if ( ! empty( $updates ) ) {
			$wpdb->update(
				"{$wpdb->prefix}woocommerce_attribute_taxonomies",
				$updates,
				array( 'attribute_id' => (int) $row->attribute_id )
			);
			WP_CLI::log( "Updated existing attribute: {$label} ({$taxonomy})" );
		}
	} else {
		$attribute_id = wc_create_attribute(
			array(
				'name'         => $label,
				'slug'         => $slug,
				'type'         => 'select',
				'order_by'     => 'menu_order',
				'has_archives' => true,
			)
		);

		if ( is_wp_error( $attribute_id ) ) {
			WP_CLI::error( 'Failed to create attribute: ' . $attribute_id->get_error_message() );
		}

		// wc_create_attribute does not set attribute_public; update directly.
		$wpdb->update(
			"{$wpdb->prefix}woocommerce_attribute_taxonomies",
			array( 'attribute_public' => 1 ),
			array( 'attribute_id' => (int) $attribute_id )
		);

		WP_CLI::log( "Created attribute: {$label} => {$taxonomy}" );
	}

	delete_transient( 'wc_attribute_taxonomies' );

	if ( class_exists( 'WC_Post_Types' ) ) {
		WC_Post_Types::register_taxonomies();
	}

	if ( ! taxonomy_exists( $taxonomy ) ) {
		register_taxonomy(
			$taxonomy,
			apply_filters( 'woocommerce_taxonomy_objects_' . $taxonomy, array( 'product' ) ),
			apply_filters(
				'woocommerce_taxonomy_args_' . $taxonomy,
				array(
					'labels'       => array( 'name' => $label ),
					'hierarchical' => false,
					'show_ui'      => false,
					'query_var'    => true,
					'rewrite'      => false,
				)
			)
		);
	}

	return $taxonomy;
}

/**
 * Ensure a genre term exists.
 *
 * @param string $taxonomy Attribute taxonomy.
 * @param string $name     Persian genre name.
 * @return int Term ID.
 */
function diako_ensure_game_genre_term( string $taxonomy, string $name ): int {
	$name = trim( $name );
	if ( '' === $name ) {
		return 0;
	}

	$term = term_exists( $name, $taxonomy );
	if ( ! $term ) {
		$term = wp_insert_term( $name, $taxonomy );
	}

	if ( is_wp_error( $term ) ) {
		$existing = get_term_by( 'name', $name, $taxonomy );
		return $existing ? (int) $existing->term_id : 0;
	}

	return (int) ( is_array( $term ) ? $term['term_id'] : $term );
}

/**
 * Resolve product category term by slug, with alias support.
 *
 * @param string $slug Requested slug.
 * @return WP_Term|null
 */
function diako_resolve_games_category( string $slug ): ?WP_Term {
	$candidates = array_unique(
		array(
			$slug,
			'games' === $slug ? 'game' : '',
			'game' === $slug ? 'games' : '',
		)
	);

	foreach ( $candidates as $candidate ) {
		if ( '' === $candidate ) {
			continue;
		}

		$term = get_term_by( 'slug', $candidate, 'product_cat' );
		if ( $term instanceof WP_Term && ! is_wp_error( $term ) ) {
			return $term;
		}
	}

	return null;
}

/**
 * Whether a product already has a game-genre term assigned.
 *
 * @param int    $product_id Product ID.
 * @param string $taxonomy   Attribute taxonomy.
 * @return bool
 */
function diako_product_has_game_genre( int $product_id, string $taxonomy ): bool {
	$terms = wp_get_object_terms( $product_id, $taxonomy, array( 'fields' => 'ids' ) );
	return ! is_wp_error( $terms ) && ! empty( $terms );
}

/**
 * Assign a genre term to a product and sync WooCommerce attribute meta.
 *
 * @param int    $product_id Product ID.
 * @param string $taxonomy   Attribute taxonomy.
 * @param int    $term_id    Genre term ID.
 * @return void
 */
function diako_assign_game_genre_to_product( int $product_id, string $taxonomy, int $term_id ): void {
	wp_set_object_terms( $product_id, array( $term_id ), $taxonomy, false );

	$attributes = get_post_meta( $product_id, '_product_attributes', true );
	if ( ! is_array( $attributes ) ) {
		$attributes = array();
	}

	$position = 0;
	foreach ( $attributes as $attr ) {
		if ( is_array( $attr ) && isset( $attr['position'] ) ) {
			$position = max( $position, (int) $attr['position'] + 1 );
		}
	}

	$attributes[ $taxonomy ] = array(
		'name'         => $taxonomy,
		'value'        => '',
		'position'     => $position,
		'is_visible'   => 1,
		'is_variation' => 0,
		'is_taxonomy'  => 1,
	);

	update_post_meta( $product_id, '_product_attributes', $attributes );

	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients( $product_id );
	}
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------

$cli_args = diako_assign_game_genres_parse_args();

WP_CLI::log( 'Ensuring global attribute "' . DIAKO_GAME_GENRE_ATTR_LABEL . '" (' . DIAKO_GAME_GENRE_ATTR_SLUG . ')...' );
$taxonomy = diako_ensure_game_genre_attribute();

if ( ! $taxonomy ) {
	WP_CLI::error( 'Could not register game-genre taxonomy.' );
}

$term_ids = array();
foreach ( diako_game_genre_terms() as $genre_name ) {
	$term_ids[ $genre_name ] = diako_ensure_game_genre_term( $taxonomy, $genre_name );
}
WP_CLI::log( 'Ensured ' . count( $term_ids ) . ' genre terms.' );

$category = diako_resolve_games_category( $cli_args['category'] );
if ( ! $category ) {
	WP_CLI::error( 'Product category not found for slug: ' . $cli_args['category'] );
}

WP_CLI::log(
	sprintf(
		'Processing products in category "%s" (%s)...%s',
		$category->name,
		$category->slug,
		$cli_args['dry_run'] ? ' [DRY RUN]' : ''
	)
);

$query = new WP_Query(
	array(
		'post_type'              => 'product',
		'post_status'            => array( 'publish', 'draft', 'private' ),
		'posts_per_page'         => -1,
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'tax_query'              => array(
			array(
				'taxonomy'         => 'product_cat',
				'field'            => 'term_id',
				'terms'            => array( (int) $category->term_id ),
				'include_children' => true,
			),
		),
	)
);

$stats = array(
	'found'    => count( $query->posts ),
	'assigned' => 0,
	'skipped'  => 0,
	'fallback' => 0,
);

$report = array();

foreach ( $query->posts as $product_id ) {
	$product_id = (int) $product_id;
	$title      = get_the_title( $product_id );

	if ( ! $cli_args['force'] && diako_product_has_game_genre( $product_id, $taxonomy ) ) {
		++$stats['skipped'];
		continue;
	}

	$genre   = diako_detect_game_genre_from_title( $title );
	$term_id = $term_ids[ $genre ] ?? 0;

	if ( ! $term_id ) {
		WP_CLI::warning( "Product {$product_id}: unknown genre '{$genre}' for '{$title}'" );
		continue;
	}

	if ( 'متفرقه' === $genre ) {
		++$stats['fallback'];
	}

	if ( $cli_args['dry_run'] ) {
		WP_CLI::log( "  [dry-run] #{$product_id} \"{$title}\" => {$genre}" );
		++$stats['assigned'];
		continue;
	}

	diako_assign_game_genre_to_product( $product_id, $taxonomy, $term_id );
	++$stats['assigned'];
	$report[] = array(
		'id'    => $product_id,
		'title' => $title,
		'genre' => $genre,
	);
}

if ( ! $cli_args['dry_run'] && $stats['assigned'] > 0 ) {
	WP_CLI::runcommand( 'wc palt regenerate', array( 'exit_error' => false ) );
}

delete_transient( 'wc_attribute_taxonomies' );

WP_CLI::success(
	sprintf(
		'Done. Found: %d | Assigned: %d | Skipped (existing): %d | Fallback (متفرقه): %d',
		$stats['found'],
		$stats['assigned'],
		$stats['skipped'],
		$stats['fallback']
	)
);

if ( $stats['fallback'] > 0 ) {
	WP_CLI::log( 'Review products tagged "متفرقه" and adjust rules in scripts/game-genre-rules.php if needed.' );
}

if ( $cli_args['dry_run'] ) {
	WP_CLI::log( 'Re-run without --dry-run to apply changes.' );
}
