<?php
/**
 * Template tags.
 *
 * @package Diako
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product carousel section.
 *
 * @param array $args Section configuration.
 */
function diako_product_section( array $args ): void {
	$defaults = array(
		'title'       => '',
		'description' => '',
		'button_text' => __( 'مشاهده همه', 'diako' ),
		'button_url'  => '',
		'query'       => array(),
	);
	$args = wp_parse_args( $args, $defaults );

	$query_args = diako_apply_instock_first_to_product_query_args(
		wp_parse_args(
			$args['query'],
			array(
				'post_type'      => 'product',
				'posts_per_page' => 8,
				'post_status'    => 'publish',
			)
		)
	);

	get_template_part(
		'template-parts/home/product-section',
		null,
		array(
			'args'  => $args,
			'query' => new WP_Query( $query_args ),
		)
	);
}

/**
 * Homepage promo banner section.
 *
 * @param array $args Banner configuration.
 * @return void
 */
function diako_home_promo_banner( array $args = array() ): void {
	$defaults = array(
		'image_id'  => 0,
		'image_url' => '',
		'filename'  => '',
		'fallback'  => '',
		'url'       => '/product-category/skincare/',
		'alt'       => __( 'مراقبت پوست و زیبایی', 'diako' ),
	);

	$args = wp_parse_args( $args, $defaults );

	if ( ! empty( $args['url'] ) ) {
		$args['url'] = diako_theme_settings_url( (string) $args['url'] );
	}

	get_template_part(
		'template-parts/home/promo-banner',
		null,
		$args
	);
}

/**
 * Estimated reading time in minutes for a post.
 *
 * @param WP_Post|int|null $post Post object or ID.
 * @return int
 */
function diako_get_post_reading_time_minutes( $post = null ) {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return 1;
	}

	$content = wp_strip_all_tags( $post->post_content . ' ' . $post->post_excerpt );
	$content = trim( preg_replace( '/\s+/u', ' ', $content ) );

	if ( '' === $content ) {
		return 1;
	}

	$words = preg_split( '/\s+/u', $content, -1, PREG_SPLIT_NO_EMPTY );
	$count = is_array( $words ) ? count( $words ) : 0;

	return max( 1, (int) ceil( $count / 200 ) );
}

/**
 * Localized reading-time label for post cards.
 *
 * @param WP_Post|int|null $post Post object or ID.
 * @return string
 */
function diako_get_post_reading_time_label( $post = null ) {
	return sprintf(
		/* translators: %s: reading time in minutes */
		__( '%s دقیقه', 'diako' ),
		diako_number( diako_get_post_reading_time_minutes( $post ) )
	);
}

/**
 * Formatted post date for cards.
 *
 * @param WP_Post|int|null $post Post object or ID.
 * @return string
 */
function diako_get_post_card_date( $post = null ) {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return '';
	}

	return get_the_date( get_option( 'date_format' ), $post );
}

/**
 * Render a blog post card.
 *
 * @param WP_Post|int|null $post Post object or ID.
 * @return void
 */
function diako_render_post_card( $post = null ) {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return;
	}

	get_template_part(
		'template-parts/content/post-card',
		null,
		array(
			'post' => $post,
		)
	);
}
