<?php
/**
 * Review star rating display.
 *
 * @package Diako
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $comment;

$rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );

if ( $rating && wc_review_ratings_enabled() ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo diako_render_star_rating( $rating, array( 'size' => 'md' ) );
}
