<?php
/**
 * Activate Enzi theme on a fresh install (no Diako content IDs).
 *
 * Usage: wp eval-file /scripts/activate-enzi-theme.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

WP_CLI::log( 'Switching to Enzi theme...' );

switch_theme( 'enzi' );

if ( function_exists( 'diako_ensure_mag_page' ) ) {
	$mag_page_id = diako_ensure_mag_page();
	if ( $mag_page_id ) {
		WP_CLI::log( "  Magazine page ready (ID {$mag_page_id})" );
	}
	update_option( 'diako_mag_page_bootstrapped', 1 );
}

if ( function_exists( 'diako_ensure_terms_page' ) ) {
	$terms_page_id = diako_ensure_terms_page();
	if ( $terms_page_id ) {
		WP_CLI::log( "  Terms page ready (ID {$terms_page_id})" );
	}
	update_option( 'diako_terms_page_bootstrapped', 1 );
}

if ( function_exists( 'diako_ensure_track_order_page' ) ) {
	$track_page_id = diako_ensure_track_order_page();
	if ( $track_page_id ) {
		WP_CLI::log( "  Track order page ready (ID {$track_page_id})" );
	}
	update_option( 'diako_track_order_page_bootstrapped', 1 );
}

if ( is_readable( '/scripts/bootstrap-beauty-categories.php' ) ) {
	include '/scripts/bootstrap-beauty-categories.php';
}

flush_rewrite_rules();

WP_CLI::success( 'Enzi theme activated on a clean shop site (no imported content).' );
