<?php
/**
 * Enzi child theme — site-specific customizations.
 *
 * Parent theme (enzi) loads first; add overrides here only.
 *
 * @package Enzi_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue child theme stylesheet (loads after parent CSS via wp_head).
 *
 * @return void
 */
function lastify_child_enqueue_styles(): void {
	if ( get_stylesheet() === get_template() ) {
		return;
	}

	$path = get_stylesheet_directory() . '/style.css';

	if ( ! is_readable( $path ) ) {
		return;
	}

	if ( function_exists( 'diako_stylesheet_has_rules' ) && ! diako_stylesheet_has_rules( $path ) ) {
		return;
	}

	wp_enqueue_style(
		'lastify-child-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'lastify_child_enqueue_styles', 20 );

/**
 * Load optional child includes (create inc/custom.php for PHP hooks).
 *
 * @return void
 */
function lastify_child_load_includes(): void {
	$custom = get_stylesheet_directory() . '/inc/custom.php';

	if ( is_readable( $custom ) ) {
		require_once $custom;
	}
}
add_action( 'after_setup_theme', 'lastify_child_load_includes', 20 );
