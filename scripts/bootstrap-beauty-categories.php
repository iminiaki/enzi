<?php
/**
 * Seed generic category SEO descriptions for Enzi shop.
 *
 * Product categories are managed in WooCommerce admin — this script only
 * prepares SEO starter content when matching slugs exist.
 *
 * Usage: wp eval-file /scripts/bootstrap-beauty-categories.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! taxonomy_exists( 'product_cat' ) ) {
	WP_CLI::error( 'WooCommerce product_cat taxonomy is not available.' );
}

delete_option( 'diako_category_seo_desc_seeded' );

if ( function_exists( 'diako_seed_category_seo_content' ) ) {
	diako_seed_category_seo_content();
	WP_CLI::log( 'Category SEO descriptions seeded.' );
}

WP_CLI::success( 'Enzi shop category SEO bootstrap complete.' );
