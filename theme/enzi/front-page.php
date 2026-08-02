<?php defined( 'ABSPATH' ) || exit; ?>
<?php
/**
 * Front page template.
 *
 * @package Lastify
 */

get_header();

$settings         = diako_get_theme_settings();
$product_sections = $settings['product_sections'] ?? array();

if ( ! empty( $settings['hero']['enabled'] ) ) {
	get_template_part( 'template-parts/home/hero' );
}

if ( ! empty( $settings['categories']['enabled'] ) ) {
	get_template_part( 'template-parts/home/categories' );
}

$featured_products = $settings['featured_products'] ?? array();

if ( ! empty( $featured_products['enabled'] ) ) {
	diako_product_section(
		array(
			'title'       => $featured_products['title'] ?? '',
			'description' => $featured_products['description'] ?? '',
			'button_text' => $featured_products['button_text'] ?? __( 'مشاهده همه', 'diako' ),
			'button_url'  => diako_theme_settings_url( $featured_products['button_url'] ?? '' ),
			'query'       => diako_build_featured_products_query(),
		)
	);
}

$rendered_section_index = 0;

foreach ( $product_sections as $section ) {
	if ( empty( $section['enabled'] ) || diako_is_legacy_coming_soon_product_section( $section ) ) {
		continue;
	}

	$button_url = trim( (string) ( $section['button_url'] ?? '' ) );

	diako_product_section(
		array(
			'title'       => $section['title'] ?? '',
			'description' => $section['description'] ?? '',
			'button_url'  => diako_theme_settings_url( $button_url ),
			'query'       => diako_build_product_section_query( $section ),
		)
	);

	if ( 'on_sale' === ( $section['query_type'] ?? '' ) ) {
		diako_render_coming_soon_product_section( $settings );
	}

	if ( 0 === $rendered_section_index && ! empty( $settings['promo_banner']['enabled'] ) ) {
		diako_home_promo_banner( $settings['promo_banner'] );
	}

	if ( 2 === $rendered_section_index && ! empty( $settings['banners']['enabled'] ) ) {
		get_template_part( 'template-parts/home/banners' );
	}

	++$rendered_section_index;
}

if ( ! empty( $settings['blog']['enabled'] ) ) {
	get_template_part( 'template-parts/home/blog' );
}

get_footer();
