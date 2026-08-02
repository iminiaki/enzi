<?php
/**
 * Create Enzi cosmetics / skincare product categories and seed SEO descriptions.
 *
 * Usage: wp eval-file /scripts/bootstrap-beauty-categories.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! taxonomy_exists( 'product_cat' ) ) {
	WP_CLI::error( 'WooCommerce product_cat taxonomy is not available.' );
}

$categories = array(
	array(
		'name' => 'مراقبت پوست',
		'slug' => 'skincare',
	),
	array(
		'name' => 'آرایش',
		'slug' => 'makeup',
	),
	array(
		'name' => 'ابزار زیبایی',
		'slug' => 'beauty-tools',
	),
	array(
		'name' => 'مراقبت مو',
		'slug' => 'hair-care',
	),
	array(
		'name' => 'مراقبت بدن',
		'slug' => 'body-care',
	),
);

foreach ( $categories as $category ) {
	$existing = get_term_by( 'slug', $category['slug'], 'product_cat' );

	if ( $existing instanceof WP_Term ) {
		WP_CLI::log( "Exists: {$category['slug']} (#{$existing->term_id})" );
		continue;
	}

	$result = wp_insert_term(
		$category['name'],
		'product_cat',
		array(
			'slug' => $category['slug'],
		)
	);

	if ( is_wp_error( $result ) ) {
		WP_CLI::warning( "Failed {$category['slug']}: " . $result->get_error_message() );
		continue;
	}

	WP_CLI::log( "Created: {$category['slug']} (#{$result['term_id']})" );
}

// Allow SEO seed to run for the new beauty slugs.
delete_option( 'diako_category_seo_desc_seeded' );

if ( function_exists( 'diako_seed_category_seo_content' ) ) {
	diako_seed_category_seo_content();
	WP_CLI::log( 'Category SEO descriptions seeded.' );
}

WP_CLI::success( 'Beauty categories ready.' );
