<?php
/**
 * Assets.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme CSS asset.
 *
 * @return array{0: string, 1: string}
 */
function diako_theme_css_asset() {
	$relative = '/assets/css/main.css';
	$path     = DIAKO_DIR . $relative;
	$uri      = DIAKO_URI . $relative;
	$version  = file_exists( $path ) ? (string) filemtime( $path ) : DIAKO_VERSION;

	return array( $uri, $version );
}

/**
 * Print theme assets for standalone pages that bypass wp_head().
 *
 * @return void
 */
function diako_print_standalone_head_assets() {
	if ( function_exists( 'diako_print_critical_font_assets' ) ) {
		diako_print_critical_font_assets();
	}

	list( $css_uri, $css_version ) = diako_theme_css_asset();

	if ( function_exists( 'diako_print_theme_stylesheet' ) ) {
		diako_print_theme_stylesheet( $css_uri, $css_version );
	} else {
		printf(
			'<link rel="stylesheet" href="%1$s?ver=%2$s" id="diako-theme-css">' . "\n",
			esc_url( $css_uri ),
			esc_attr( $css_version )
		);
	}

	if ( function_exists( 'diako_output_branding_css' ) ) {
		diako_output_branding_css();
	}

	if ( function_exists( 'diako_output_branding_favicon' ) ) {
		diako_output_branding_favicon();
	}

	if ( get_stylesheet() !== get_template() ) {
		$child_css = get_stylesheet_directory() . '/assets/css/custom.css';

		if ( is_readable( $child_css ) ) {
			printf(
				'<link rel="stylesheet" href="%1$s?ver=%2$s" id="lastify-child-custom-css">' . "\n",
				esc_url( get_stylesheet_directory_uri() . '/assets/css/custom.css' ),
				esc_attr( (string) filemtime( $child_css ) )
			);
		}
	}
}

add_filter(
	'body_class',
	function ( $classes ) {
		if ( function_exists( 'diako_should_load_storefront_theme_assets' ) && ! diako_should_load_storefront_theme_assets() ) {
			return $classes;
		}

		$classes[] = 'bg-background';
		$classes[] = 'text-foreground';
		return $classes;
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( function_exists( 'diako_should_load_storefront_theme_assets' ) && ! diako_should_load_storefront_theme_assets() ) {
			return;
		}

		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'classic-theme-styles' );
		wp_dequeue_style( 'global-styles' );

		wp_enqueue_script(
			'diako-main',
			diako_theme_js_asset_uri( '/assets/js/main.js' ),
			array(),
			file_exists( diako_theme_js_asset_path( '/assets/js/main.js' ) ) ? (string) filemtime( diako_theme_js_asset_path( '/assets/js/main.js' ) ) : DIAKO_VERSION,
			true
		);

		wp_localize_script(
			'diako-main',
			'diakoSearch',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'diako_search' ),
				'minChars' => 2,
				'i18n'     => array(
					'searching'  => __( 'در حال جستجو…', 'diako' ),
					'noResults'  => __( 'محصولی یافت نشد.', 'diako' ),
					'viewAll'    => __( 'مشاهده همه نتایج', 'diako' ),
					'typeMore'   => __( 'حداقل ۲ حرف وارد کنید.', 'diako' ),
					'openLabel'  => __( 'جستجو در محصولات', 'diako' ),
					'closeLabel' => __( 'بستن جستجو', 'diako' ),
				),
			)
		);

		if ( function_exists( 'diako_shop_has_sidebar' ) && diako_shop_has_sidebar() ) {
			wp_localize_script(
				'diako-main',
				'diakoShopFilters',
				array(
					'visibleCount' => 3,
					'ajax'         => true,
					'i18n'         => array(
						'showMore' => __( 'نمایش %d مورد دیگر', 'diako' ),
						'showLess' => __( 'نمایش کمتر', 'diako' ),
						'loading'  => __( 'در حال به‌روزرسانی محصولات…', 'diako' ),
					),
				)
			);
		}

		if ( is_front_page() || is_product() || ( function_exists( 'diako_shop_has_sidebar' ) && diako_shop_has_sidebar() ) ) {
			wp_enqueue_script(
				'diako-carousel',
				diako_theme_js_asset_uri( '/assets/js/carousel.js' ),
				array(),
				file_exists( diako_theme_js_asset_path( '/assets/js/carousel.js' ) ) ? (string) filemtime( diako_theme_js_asset_path( '/assets/js/carousel.js' ) ) : DIAKO_VERSION,
				true
			);
		}

		if ( get_stylesheet() !== get_template() ) {
			$child_css = get_stylesheet_directory() . '/assets/css/custom.css';

			if ( is_readable( $child_css ) && function_exists( 'diako_stylesheet_has_rules' ) && diako_stylesheet_has_rules( $child_css ) ) {
				wp_enqueue_style(
					'lastify-child-custom',
					get_stylesheet_directory_uri() . '/assets/css/custom.css',
					array(),
					(string) filemtime( $child_css )
				);
			}
		}
	},
	100
);

