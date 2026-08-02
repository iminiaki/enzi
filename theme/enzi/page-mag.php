<?php defined( 'ABSPATH' ) || exit; ?>
<?php
/**
 * Template Name: مجله
 *
 * Magazine index — lists blog posts with sidebar.
 *
 * @package Diako
 */

$GLOBALS['diako_is_mag_page'] = true;

$paged = max(
	1,
	(int) get_query_var( 'paged' ),
	(int) get_query_var( 'page' )
);

$mag_query = new WP_Query(
	array(
		'post_type'              => 'post',
		'post_status'            => 'publish',
		'posts_per_page'         => (int) get_option( 'posts_per_page', 12 ),
		'paged'                  => $paged,
		'ignore_sticky_posts'    => true,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	)
);

diako_render_blog_archive_page( $mag_query );
