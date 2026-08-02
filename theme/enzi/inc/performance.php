<?php
/**
 * Front-end performance optimizations (LCP, resource hints).
 *
 * @package Diako
 */

defined( 'ABSPATH' ) || exit;

/** Responsive sizes attribute for homepage hero images. */
const DIAKO_HERO_IMAGE_SIZES = '(min-width: 1280px) 1216px, calc(100vw - 2rem)';

/** Responsive sizes attribute for product cards in carousels/grids. */
const DIAKO_PRODUCT_CARD_IMAGE_SIZES = '(max-width: 640px) 46vw, (max-width: 1024px) 30vw, 240px';

/** Self-hosted Vazirmatn font URL. */
const DIAKO_FONT_URL = DIAKO_URI . '/assets/fonts/vazirmatn.woff2';

/**
 * Register theme image sizes used for LCP-sensitive assets.
 *
 * @return void
 */
function diako_register_theme_image_sizes(): void {
	add_image_size( 'diako-hero-sm', 640, 0, false );
	add_image_size( 'diako-hero-md', 1024, 0, false );
	add_image_size( 'diako-hero', 1280, 0, false );
	add_image_size( 'diako-product-card', 320, 320, true );
	add_image_size( 'diako-banner', 640, 320, true );
}
add_action( 'after_setup_theme', 'diako_register_theme_image_sizes', 20 );

/**
 * Tune WooCommerce thumbnail size for card layouts.
 *
 * @param array<string, mixed> $size Image size args.
 * @return array<string, mixed>
 */
function diako_filter_product_thumbnail_size( array $size ): array {
	$size['width']  = 320;
	$size['height'] = 320;
	$size['crop']   = 1;

	return $size;
}
add_filter( 'woocommerce_get_image_size_woocommerce_thumbnail', 'diako_filter_product_thumbnail_size' );

/**
 * Add responsive `sizes` for product card images.
 *
 * @param array<string, string> $attr       Image attributes.
 * @param WP_Post               $attachment Attachment post.
 * @param string|int[]          $size       Requested size.
 * @return array<string, string>
 */
function diako_add_product_card_image_sizes( array $attr, WP_Post $attachment, $size ): array {
	$card_sizes = array( 'woocommerce_thumbnail', 'diako-product-card', 'diako-banner' );

	if ( 'diako-banner' === $size ) {
		$attr['sizes'] = '(max-width: 768px) 100vw, 640px';
		return $attr;
	}

	if ( ! in_array( $size, $card_sizes, true ) ) {
		return $attr;
	}

	$attr['sizes'] = DIAKO_PRODUCT_CARD_IMAGE_SIZES;

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'diako_add_product_card_image_sizes', 10, 3 );

/**
 * Print self-hosted font preload and inline @font-face before theme CSS.
 *
 * @return void
 */
function diako_print_critical_font_assets(): void {
	if ( is_admin() ) {
		return;
	}

	$font_url = esc_url( DIAKO_FONT_URL );

	printf(
		'<link rel="preload" href="%1$s" as="font" type="font/woff2" crossorigin>' . "\n",
		$font_url
	);

	echo '<style id="diako-critical-font">';
	echo '@font-face{font-family:"Vazirmatn";font-style:normal;font-weight:100 900;font-display:swap;';
	printf( 'src:url("%s") format("woff2");}', $font_url );
	echo '</style>' . "\n";
}

/**
 * Print the main theme stylesheet synchronously to avoid layout shift.
 *
 * Preloads the CSS for early download while keeping render-blocking apply order.
 *
 * @param string $uri     Stylesheet URL.
 * @param string $version Asset version.
 * @param string $id      Link element ID.
 * @return void
 */
function diako_print_theme_stylesheet( string $uri, string $version, string $id = 'diako-theme-css' ): void {
	$href = esc_url( $uri ) . '?ver=' . esc_attr( $version );

	if ( ! is_admin() ) {
		printf(
			'<link rel="preload" href="%1$s" as="style">' . "\n",
			$href
		);
	}

	printf(
		'<link rel="stylesheet" href="%1$s" id="%2$s">' . "\n",
		$href,
		esc_attr( $id )
	);
}

/**
 * Preload the homepage hero LCP image as early as possible.
 *
 * @return void
 */
function diako_print_critical_lcp_preload(): void {
	if ( is_admin() || ! is_front_page() || ! function_exists( 'diako_get_hero_slides' ) ) {
		return;
	}

	$slides = diako_get_hero_slides();

	if ( empty( $slides[0]['image'] ) ) {
		return;
	}

	$slide = $slides[0];
	$attrs = array(
		'rel'           => 'preload',
		'as'            => 'image',
		'href'          => $slide['image'],
		'fetchpriority' => 'high',
	);

	if ( ! empty( $slide['srcset'] ) ) {
		$attrs['imagesrcset'] = $slide['srcset'];
		$attrs['imagesizes']  = $slide['sizes'] ?? DIAKO_HERO_IMAGE_SIZES;
	}

	$markup = '<link';

	foreach ( $attrs as $key => $value ) {
		$markup .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
	}

	echo $markup . '>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Lazy-load third-party trust badge images below the fold.
 *
 * @param string $html Trust badge HTML.
 * @return string
 */
function diako_lazy_load_trust_badge_images( string $html ): string {
	if ( is_admin() || '' === $html ) {
		return $html;
	}

	return str_replace( '<img ', '<img loading="lazy" decoding="async" ', $html );
}

/**
 * Add long-lived cache headers for static theme assets and uploads.
 *
 * @return void
 */
function diako_send_static_cache_headers(): void {
	if ( is_admin() || headers_sent() ) {
		return;
	}

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( (string) $_SERVER['REQUEST_URI'] ) : '';

	if ( '' === $request_uri ) {
		return;
	}

	$path = wp_parse_url( $request_uri, PHP_URL_PATH );

	if ( ! is_string( $path ) || '' === $path ) {
		return;
	}

	$is_static = preg_match(
		'#/wp-content/(?:themes|uploads|plugins)/.+\.(?:css|js|woff2?|ttf|otf|eot|svg|png|jpe?g|webp|gif|avif|ico)$#i',
		$path
	) || preg_match(
		'#/wp-includes/.+\.(?:css|js|woff2?|ttf|otf|eot|svg|png|jpe?g|webp|gif|avif|ico)$#i',
		$path
	);

	if ( ! $is_static ) {
		return;
	}

	header( 'Cache-Control: public, max-age=31536000, immutable' );
}
add_action( 'send_headers', 'diako_send_static_cache_headers', 20 );

/**
 * Pages where login plugins (e.g. Voroodak) need jQuery in default load order.
 */
function diako_is_login_sensitive_page(): bool {
	if ( is_admin() || is_customize_preview() ) {
		return false;
	}

	if ( function_exists( 'diako_is_login_default_layout_page' ) && diako_is_login_default_layout_page() ) {
		return true;
	}

	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_user_logged_in() ) {
		return true;
	}

	return (bool) apply_filters( 'diako_is_login_sensitive_page', false );
}

/**
 * Move jQuery to the footer on the storefront.
 *
 * @return void
 */
function diako_move_jquery_to_footer(): void {
	if ( is_admin() || is_customize_preview() || diako_is_login_sensitive_page() ) {
		return;
	}

	wp_scripts()->add_data( 'jquery', 'group', 1 );
	wp_scripts()->add_data( 'jquery-core', 'group', 1 );
	wp_scripts()->add_data( 'jquery-migrate', 'group', 1 );
}
add_action( 'wp_enqueue_scripts', 'diako_move_jquery_to_footer', 1 );

/**
 * Ensure jQuery loads before login-plugin scripts on auth/account pages.
 *
 * @return void
 */
function diako_enqueue_login_sensitive_scripts(): void {
	if ( ! diako_is_login_sensitive_page() ) {
		return;
	}

	wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'diako_enqueue_login_sensitive_scripts', 5 );

/**
 * Defer non-critical frontend scripts while preserving execution order.
 *
 * @param string $tag    Script tag HTML.
 * @param string $handle Script handle.
 * @param string $src    Script source URL.
 * @return string
 */
function diako_defer_frontend_scripts( string $tag, string $handle, string $src ): string {
	if ( is_admin() || is_customize_preview() || diako_is_login_sensitive_page() ) {
		return $tag;
	}

	$defer_handles = apply_filters(
		'diako_defer_script_handles',
		array(
			'jquery',
			'jquery-core',
			'jquery-migrate',
			'jquery-ui-core',
			'jquery-ui-mouse',
			'jquery-ui-widget',
			'jquery-ui-slider',
			'wc-jquery-ui-touchpunch',
			'wc-accounting',
			'wc-price-slider',
			'diako-main',
			'diako-carousel',
			'diako-stock-notify',
			'sourcebuster-js',
			'wc-order-attribution-js',
		)
	);

	if ( ! in_array( $handle, $defer_handles, true ) ) {
		return $tag;
	}

	if ( false !== strpos( $tag, ' defer' ) || false !== strpos( $tag, ' async' ) ) {
		return $tag;
	}

	return str_replace( '<script ', '<script defer ', $tag );
}
add_filter( 'script_loader_tag', 'diako_defer_frontend_scripts', 10, 3 );

/**
 * Remove WooCommerce block styles that are not needed on the storefront.
 *
 * @return void
 */
function diako_remove_wc_block_styles(): void {
	if ( is_admin() ) {
		return;
	}

	$handles = array(
		'wc-blocks-style',
		'wc-blocks-style-rtl',
		'wc-blocks-vendors-style',
		'wc-blocks-vendors-style-rtl',
	);

	foreach ( $handles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'diako_remove_wc_block_styles', 999 );

/**
 * Skip empty child-theme stylesheets that only add render-blocking requests.
 *
 * @return void
 */
function diako_skip_empty_child_styles(): void {
	if ( is_admin() || get_stylesheet() === get_template() ) {
		return;
	}

	$styles = array(
		'lastify-child-style'  => get_stylesheet_directory() . '/style.css',
		'lastify-child-custom' => get_stylesheet_directory() . '/assets/css/custom.css',
	);

	foreach ( $styles as $handle => $path ) {
		if ( ! is_readable( $path ) || diako_stylesheet_has_rules( $path ) ) {
			continue;
		}

		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'diako_skip_empty_child_styles', 1000 );

/**
 * Whether a CSS file contains real rules beyond the theme header comment.
 *
 * @param string $path Absolute CSS file path.
 * @return bool
 */
function diako_stylesheet_has_rules( string $path ): bool {
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$contents = file_get_contents( $path );

	if ( false === $contents || '' === $contents ) {
		return false;
	}

	$css = preg_replace( '/\/\*[\s\S]*?\*\//', '', $contents );
	$css = preg_replace( '/^\s*\/\*[\s\S]*?\*\/\s*/', '', $css );

	return '' !== trim( (string) $css );
}

/**
 * Remove scripts that are not needed on the homepage.
 *
 * @return void
 */
function diako_optimize_homepage_assets(): void {
	if ( is_admin() || ! is_front_page() ) {
		return;
	}

	wp_dequeue_script( 'sourcebuster-js' );
	wp_deregister_script( 'sourcebuster-js' );
	wp_dequeue_script( 'wc-order-attribution-js' );
	wp_deregister_script( 'wc-order-attribution-js' );
	wp_dequeue_script( 'wc-add-to-cart' );
	wp_dequeue_script( 'wc-cart-fragments' );
}
add_action( 'wp_enqueue_scripts', 'diako_optimize_homepage_assets', 1001 );

/**
 * Disable low-value core scripts on the storefront.
 *
 * @return void
 */
function diako_disable_low_value_core_assets(): void {
	if ( is_admin() ) {
		return;
	}

	wp_deregister_script( 'wp-embed' );

	if ( ! is_user_logged_in() ) {
		wp_deregister_script( 'heartbeat' );
	}
}
add_action( 'wp_enqueue_scripts', 'diako_disable_low_value_core_assets', 100 );

/**
 * Resolve a minified JS asset path when present.
 *
 * @param string $relative Relative path under the theme directory.
 * @return string
 */
function diako_theme_js_asset_path( string $relative ): string {
	$absolute = DIAKO_DIR . $relative;

	if ( ! file_exists( $absolute ) ) {
		return $absolute;
	}

	$min_relative = preg_replace( '/\.js$/', '.min.js', $relative );
	$min_absolute = DIAKO_DIR . $min_relative;

	if ( is_string( $min_relative ) && is_readable( $min_absolute ) && filemtime( $min_absolute ) >= filemtime( $absolute ) ) {
		return $min_absolute;
	}

	return $absolute;
}

/**
 * Resolve a minified JS asset URI when present.
 *
 * @param string $relative Relative path under the theme directory.
 * @return string
 */
function diako_theme_js_asset_uri( string $relative ): string {
	$path = diako_theme_js_asset_path( $relative );

	if ( str_ends_with( $path, '.min.js' ) ) {
		return DIAKO_URI . preg_replace( '/\.js$/', '.min.js', $relative );
	}

	return DIAKO_URI . $relative;
}
