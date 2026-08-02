<?php
/**
 * LEGACY (gaming) — disabled for Enzi cosmetics shop.
 * Use bootstrap-beauty-categories.php instead.
 *
 * Create used product categories and sync them into theme menus.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'diako_ensure_used_product_categories' ) ) {
	WP_CLI::error( 'Diako theme is not active or used-categories.php is missing.' );
}

$root_id = diako_merge_duplicate_used_root_category();

if ( $root_id <= 0 ) {
	$root_id = diako_ensure_used_product_categories();
}

if ( $root_id <= 0 ) {
	WP_CLI::error( 'Failed to create used product categories.' );
}

WP_CLI::success( sprintf( 'Used categories ready (root term ID: %d).', $root_id ) );

foreach ( array( 'primary', 'footer' ) as $location ) {
	$synced = diako_sync_used_categories_to_menu( $location );

	if ( $synced ) {
		WP_CLI::log( sprintf( 'Synced used categories into %s menu.', $location ) );
	} else {
		WP_CLI::warning( sprintf( 'No menu assigned to %s location.', $location ) );
	}
}

update_option( 'diako_used_categories_version', DIAKO_USED_CATEGORIES_VERSION );

WP_CLI::success( 'Done.' );
