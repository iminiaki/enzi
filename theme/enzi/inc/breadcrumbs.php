<?php
/**
 * Breadcrumb navigation.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether breadcrumbs should render on the current request.
 *
 * @return bool
 */
function diako_should_show_breadcrumbs() {
	if ( is_front_page() || is_404() ) {
		return false;
	}

	return (bool) apply_filters( 'diako_show_breadcrumbs', true );
}

/**
 * Blog section crumb.
 *
 * @return array{label: string, url: string}
 */
function diako_get_blog_crumb() {
	$blog_page_id = (int) get_option( 'page_for_posts' );

	if ( $blog_page_id > 0 && get_post( $blog_page_id ) ) {
		return array(
			'label' => get_the_title( $blog_page_id ),
			'url'   => get_permalink( $blog_page_id ),
		);
	}

	return array(
		'label' => __( 'مجله', 'diako' ),
		'url'   => home_url( '/mag/' ),
	);
}

/**
 * Shop section crumb.
 *
 * @return array{label: string, url: string}
 */
function diako_get_shop_crumb() {
	if ( function_exists( 'wc_get_page_id' ) ) {
		$shop_page_id = wc_get_page_id( 'shop' );

		if ( $shop_page_id > 0 && get_post( $shop_page_id ) ) {
			return array(
				'label' => get_the_title( $shop_page_id ),
				'url'   => get_permalink( $shop_page_id ),
			);
		}
	}

	return array(
		'label' => __( 'فروشگاه', 'diako' ),
		'url'   => home_url( '/shop/' ),
	);
}

/**
 * Pick the most specific assigned term.
 *
 * @param WP_Term[] $terms Terms.
 * @return WP_Term|null
 */
function diako_get_deepest_term( array $terms ) {
	if ( empty( $terms ) ) {
		return null;
	}

	$deepest   = $terms[0];
	$max_depth = -1;

	foreach ( $terms as $term ) {
		$depth = count( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) );

		if ( $depth > $max_depth ) {
			$max_depth = $depth;
			$deepest   = $term;
		}
	}

	return $deepest;
}

/**
 * Primary category for a post.
 *
 * @param int $post_id Post ID.
 * @return WP_Term|null
 */
function diako_get_primary_category( $post_id ) {
	$categories = get_the_category( $post_id );

	if ( empty( $categories ) ) {
		return null;
	}

	if ( class_exists( 'WPSEO_Primary_Term' ) ) {
		$primary = new WPSEO_Primary_Term( 'category', $post_id );
		$term_id = $primary->get_primary_term();

		if ( $term_id ) {
			$term = get_term( $term_id, 'category' );

			if ( $term && ! is_wp_error( $term ) ) {
				return $term;
			}
		}
	}

	return diako_get_deepest_term( $categories );
}

/**
 * Append a taxonomy term and its ancestors.
 *
 * @param array<int, array{label: string, url: string}> $crumbs    Breadcrumb items.
 * @param WP_Term                                       $term      Term.
 * @param bool                                          $link_last Whether the term itself should link.
 * @return void
 */
function diako_append_term_chain( array &$crumbs, WP_Term $term, $link_last = false ) {
	$ancestors = array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) );

	foreach ( $ancestors as $ancestor_id ) {
		$ancestor = get_term( $ancestor_id, $term->taxonomy );

		if ( ! $ancestor || is_wp_error( $ancestor ) ) {
			continue;
		}

		$crumbs[] = array(
			'label' => $ancestor->name,
			'url'   => get_term_link( $ancestor ),
		);
	}

	$crumbs[] = array(
		'label' => $term->name,
		'url'   => $link_last ? get_term_link( $term ) : '',
	);
}

/**
 * Build breadcrumb items for the current request.
 *
 * @return array<int, array{label: string, url: string}>
 */
function diako_get_breadcrumbs() {
	$crumbs = array(
		array(
			'label' => __( 'خانه', 'diako' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( function_exists( 'is_shop' ) && is_shop() && ! is_search() ) {
		$shop         = diako_get_shop_crumb();
		$shop['url']  = '';
		$crumbs[]     = $shop;
		return apply_filters( 'diako_breadcrumbs', $crumbs );
	}

	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$crumbs[] = diako_get_shop_crumb();
		$term     = get_queried_object();

		if ( $term instanceof WP_Term ) {
			diako_append_term_chain( $crumbs, $term, false );
		}

		return apply_filters( 'diako_breadcrumbs', $crumbs );
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		$crumbs[] = diako_get_shop_crumb();
		$terms    = get_the_terms( get_the_ID(), 'product_cat' );

		if ( $terms && ! is_wp_error( $terms ) ) {
			$term = diako_get_deepest_term( $terms );

			if ( $term ) {
				diako_append_term_chain( $crumbs, $term, true );
			}
		}

		$crumbs[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);

		return apply_filters( 'diako_breadcrumbs', $crumbs );
	}

	if ( is_home() && ! is_front_page() ) {
		$blog        = diako_get_blog_crumb();
		$blog['url'] = '';
		$crumbs[]    = $blog;

		return apply_filters( 'diako_breadcrumbs', $crumbs );
	}

	if ( is_singular( 'post' ) ) {
		$crumbs[] = diako_get_blog_crumb();
		$category = diako_get_primary_category( get_the_ID() );

		if ( $category ) {
			diako_append_term_chain( $crumbs, $category, true );
		}

		$crumbs[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);

		return apply_filters( 'diako_breadcrumbs', $crumbs );
	}

	if ( is_category() ) {
		$crumbs[] = diako_get_blog_crumb();
		$term     = get_queried_object();

		if ( $term instanceof WP_Term ) {
			diako_append_term_chain( $crumbs, $term, false );
		}

		return apply_filters( 'diako_breadcrumbs', $crumbs );
	}

	if ( is_page() ) {
		$ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );

		foreach ( $ancestors as $ancestor_id ) {
			$crumbs[] = array(
				'label' => get_the_title( $ancestor_id ),
				'url'   => get_permalink( $ancestor_id ),
			);
		}

		$crumbs[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);

		return apply_filters( 'diako_breadcrumbs', $crumbs );
	}

	if ( is_search() ) {
		$crumbs[] = array(
			'label' => sprintf(
				/* translators: %s: search query */
				__( 'جستجو: %s', 'diako' ),
				get_search_query()
			),
			'url'   => '',
		);

		return apply_filters( 'diako_breadcrumbs', $crumbs );
	}

	if ( is_archive() ) {
		$crumbs[] = array(
			'label' => wp_strip_all_tags( get_the_archive_title() ),
			'url'   => '',
		);

		return apply_filters( 'diako_breadcrumbs', $crumbs );
	}

	if ( is_singular() ) {
		$crumbs[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);

		return apply_filters( 'diako_breadcrumbs', $crumbs );
	}

	return apply_filters( 'diako_breadcrumbs', $crumbs );
}

/**
 * Localize breadcrumb label text for display.
 *
 * @param string $label Breadcrumb label.
 * @return string
 */
function diako_localize_breadcrumb_label( $label ) {
	if ( ! diako_should_use_persian_digits() ) {
		return $label;
	}

	return diako_to_persian_digits( $label );
}

/**
 * Render breadcrumb navigation.
 *
 * @return void
 */
function diako_render_breadcrumbs() {
	if ( ! diako_should_show_breadcrumbs() ) {
		return;
	}

	$crumbs = diako_get_breadcrumbs();

	if ( count( $crumbs ) < 2 ) {
		return;
	}

	echo '<div class="container pt-6 md:pt-8">';
	echo '<nav class="diako-breadcrumb" aria-label="' . esc_attr__( 'مسیر', 'diako' ) . '">';
	echo '<ol class="diako-breadcrumb__list">';

	foreach ( $crumbs as $index => $crumb ) {
		$is_last = ( $index === count( $crumbs ) - 1 );
		$label   = diako_localize_breadcrumb_label( $crumb['label'] );

		echo '<li class="diako-breadcrumb__item' . ( $is_last ? ' diako-breadcrumb__item--current' : '' ) . '">';

		if ( ! $is_last && ! empty( $crumb['url'] ) ) {
			printf(
				'<a href="%s">%s</a>',
				esc_url( $crumb['url'] ),
				esc_html( $label )
			);
		} else {
			printf(
				'<span%s>%s</span>',
				$is_last ? ' aria-current="page"' : '',
				esc_html( $label )
			);
		}

		if ( ! $is_last ) {
			echo '<span class="diako-breadcrumb__separator" aria-hidden="true">/</span>';
		}

		echo '</li>';
	}

	echo '</ol>';
	echo '</nav>';
	echo '</div>';
}
