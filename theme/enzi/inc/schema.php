<?php
/**
 * JSON-LD structured data when no SEO plugin handles it.
 *
 * @package Diako
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the theme should output its own schema graph.
 *
 * @return bool
 */
function diako_should_output_theme_schema(): bool {
	return (bool) apply_filters( 'diako_output_schema', ! defined( 'WPSEO_VERSION' ) );
}

/**
 * Organization schema for the storefront.
 *
 * @return array<string, mixed>
 */
function diako_get_organization_schema(): array {
	$contact = function_exists( 'diako_get_company_contact_details' )
		? diako_get_company_contact_details()
		: array();

	$schema = array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => function_exists( 'diako_get_brand_name' ) ? diako_get_brand_name() : get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);

	if ( ! empty( $contact['email'] ) ) {
		$schema['email'] = $contact['email'];
	}

	if ( ! empty( $contact['phone_tel'] ) ) {
		$schema['telephone'] = $contact['phone_tel'];
	}

	if ( ! empty( $contact['address'] ) ) {
		$schema['address'] = array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $contact['address'],
			'addressCountry'  => 'IR',
		);
	}

	$logos = function_exists( 'diako_get_branding_logo_urls' ) ? diako_get_branding_logo_urls() : array();
	$logo  = $logos['light'] ?? '';

	if ( $logo ) {
		$schema['logo'] = array(
			'@type' => 'ImageObject',
			'url'   => $logo,
		);
	}

	return $schema;
}

/**
 * WebSite schema with product search action.
 *
 * @return array<string, mixed>
 */
function diako_get_website_schema(): array {
	$search_url = home_url( '/?s={search_term_string}' );

	if ( function_exists( 'wc_get_page_id' ) ) {
		$shop_id = wc_get_page_id( 'shop' );

		if ( $shop_id > 0 ) {
			$search_url = add_query_arg(
				array(
					's'         => '{search_term_string}',
					'post_type' => 'product',
				),
				get_permalink( $shop_id )
			);
		}
	}

	return array(
		'@type'           => 'WebSite',
		'@id'             => home_url( '/#website' ),
		'url'             => home_url( '/' ),
		'name'            => get_bloginfo( 'name' ),
		'description'     => get_bloginfo( 'description' ),
		'publisher'       => array( '@id' => home_url( '/#organization' ) ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'        => 'EntryPoint',
				'urlTemplate'  => $search_url,
			),
			'query-input' => 'required name=search_term_string',
		),
	);
}

/**
 * Output JSON-LD on the homepage.
 *
 * @return void
 */
function diako_output_homepage_schema(): void {
	if ( ! diako_should_output_theme_schema() || ! is_front_page() ) {
		return;
	}

	$graph = array(
		diako_get_organization_schema(),
		diako_get_website_schema(),
	);

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			),
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		)
	);
}
add_action( 'wp_head', 'diako_output_homepage_schema', 20 );

/**
 * Whether the theme should output fallback SEO tags.
 *
 * @return bool
 */
function diako_should_output_theme_seo_tags(): bool {
	return (bool) apply_filters( 'diako_output_seo_tags', ! defined( 'WPSEO_VERSION' ) );
}

/**
 * Resolve a meta description for the current request.
 *
 * @return string
 */
function diako_get_meta_description(): string {
	if ( is_front_page() && function_exists( 'diako_get_theme_settings' ) ) {
		$settings = diako_get_theme_settings();
		$hero     = $settings['hero'] ?? array();

		if ( ! empty( $hero['description'] ) ) {
			return wp_strip_all_tags( (string) $hero['description'] );
		}
	}

	if ( is_singular() ) {
		$post = get_post();

		if ( ! $post instanceof WP_Post ) {
			return '';
		}

		if ( has_excerpt( $post ) ) {
			return wp_strip_all_tags( get_the_excerpt( $post ) );
		}

		return wp_trim_words( wp_strip_all_tags( (string) $post->post_content ), 30, '' );
	}

	$tagline = get_bloginfo( 'description', 'display' );

	return is_string( $tagline ) ? wp_strip_all_tags( $tagline ) : '';
}

/**
 * Output meta description when no SEO plugin handles it.
 *
 * @return void
 */
function diako_output_meta_description(): void {
	if ( ! diako_should_output_theme_seo_tags() || is_admin() ) {
		return;
	}

	$description = trim( diako_get_meta_description() );

	if ( '' === $description ) {
		return;
	}

	printf(
		'<meta name="description" content="%s">' . "\n",
		esc_attr( $description )
	);
}
add_action( 'wp_head', 'diako_output_meta_description', 1 );

/**
 * Provide Yoast with a fallback meta description when none is configured.
 *
 * @param string $description Existing Yoast description.
 * @return string
 */
function diako_filter_yoast_meta_description( string $description ): string {
	if ( is_front_page() && function_exists( 'diako_get_theme_settings' ) ) {
		$settings = diako_get_theme_settings();
		$hero     = $settings['hero'] ?? array();

		if ( ! empty( $hero['description'] ) ) {
			return wp_strip_all_tags( (string) $hero['description'] );
		}
	}

	if ( '' !== trim( $description ) ) {
		return $description;
	}

	return diako_get_meta_description();
}
add_filter( 'wpseo_metadesc', 'diako_filter_yoast_meta_description' );
add_filter( 'wpseo_opengraph_desc', 'diako_filter_yoast_meta_description' );
