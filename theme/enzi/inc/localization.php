<?php
/**
 * Font and Persian digit localization.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Primary theme font stack.
 *
 * @return string
 */
function diako_get_font_family() {
	return "'Vazirmatn', Tahoma, sans-serif";
}

/**
 * Google Fonts stylesheet URL for Vazirmatn.
 *
 * @return string
 */
function diako_get_font_stylesheet_url() {
	return 'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;700&display=swap';
}

/**
 * Register the theme font stylesheet.
 *
 * @return void
 */
function diako_register_fonts() {
	wp_register_style(
		'diako-vazirmatn',
		diako_get_font_stylesheet_url(),
		array(),
		null
	);
}
add_action( 'admin_enqueue_scripts', 'diako_register_fonts', 1 );
add_action( 'login_enqueue_scripts', 'diako_register_fonts', 1 );

/**
 * Enqueue Vazirmatn in wp-admin, login, customizer, and block editor.
 *
 * @return void
 */
function diako_enqueue_admin_fonts() {
	wp_enqueue_style( 'diako-vazirmatn' );

	$family = diako_get_font_family();
	$css    = sprintf(
		'html :where(body,#wpwrap,#wpbody,#wpcontent,.wrap,h1,h2,h3,h4,h5,h6,.hndle,.wp-heading-inline,.components-heading,.postbox-header,.wp-core-ui,input,select,textarea,button,.button,.components-button,.editor-styles-wrapper){font-family:%1$s!important}',
		$family
	);

	wp_add_inline_style( 'diako-vazirmatn', $css );
}
add_action( 'admin_enqueue_scripts', 'diako_enqueue_admin_fonts', 20 );
add_action( 'login_enqueue_scripts', 'diako_enqueue_admin_fonts', 20 );
add_action( 'enqueue_block_editor_assets', 'diako_enqueue_admin_fonts', 20 );
add_action( 'customize_controls_enqueue_scripts', 'diako_enqueue_admin_fonts', 20 );

/**
 * Print the font stylesheet early in head to reduce FOUC on the storefront.
 *
 * @return void
 */
function diako_print_early_font_stylesheet() {
	if ( is_admin() ) {
		return;
	}

	// Storefront uses self-hosted Vazirmatn loaded from header.php / performance.php.
}
add_action( 'wp_head', 'diako_print_early_font_stylesheet', 1 );

/**
 * Convert digits in HTML text nodes without touching attributes, scripts, or styles.
 *
 * @param string $html HTML fragment.
 * @return string
 */
function diako_to_persian_digits_html( $html ) {
	if ( ! diako_should_use_persian_digits() || ! is_string( $html ) || '' === $html ) {
		return $html;
	}

	if ( ! preg_match( '/[0-9]/', $html ) ) {
		return $html;
	}

	if ( ! class_exists( 'DOMDocument' ) ) {
		return diako_to_persian_digits( $html );
	}

	$doc      = new DOMDocument();
	$previous = libxml_use_internal_errors( true );
	$wrapped  = '<?xml encoding="utf-8" ?><div id="diako-digit-root">' . $html . '</div>';

	if ( ! $doc->loadHTML( $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD ) ) {
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
		return $html;
	}

	$root = $doc->getElementById( 'diako-digit-root' );
	if ( ! $root ) {
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
		return $html;
	}

	$xpath = new DOMXPath( $doc );
	foreach ( $xpath->query( './/text()', $root ) as $text_node ) {
		$parent = $text_node->parentNode;
		if ( ! $parent instanceof DOMNode ) {
			continue;
		}

		if ( in_array( strtolower( $parent->nodeName ), array( 'script', 'style', 'noscript' ), true ) ) {
			continue;
		}

		$text_node->nodeValue = diako_to_persian_digits( $text_node->nodeValue );
	}

	$output = '';
	foreach ( $root->childNodes as $child ) {
		$output .= $doc->saveHTML( $child );
	}

	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	return $output;
}

/**
 * Convert plain text or HTML for display.
 *
 * @param string $value Rendered string.
 * @return string
 */
function diako_filter_persian_digits_string( $value ) {
	if ( ! is_string( $value ) || '' === $value || ! diako_should_use_persian_digits() ) {
		return $value;
	}

	if ( ! preg_match( '/[0-9]/', $value ) ) {
		return $value;
	}

	if ( false !== strpos( $value, '<' ) ) {
		return diako_to_persian_digits_html( $value );
	}

	return diako_to_persian_digits( $value );
}

/**
 * Strip WC bdi wrappers so prices render correctly in RTL.
 *
 * @param string $html Price HTML.
 * @return string
 */
function diako_normalize_price_html( $html ) {
	if ( ! is_string( $html ) || false === strpos( $html, 'woocommerce-Price-amount' ) ) {
		return $html;
	}

	return str_replace( array( '<bdi>', '</bdi>' ), '', $html );
}

/**
 * Convert a scalar value for display.
 *
 * @param mixed $value Value.
 * @return string
 */
function diako_number( $value ) {
	return diako_filter_persian_digits_string( (string) $value );
}

/**
 * Convert visible digits in paginated archive link markup.
 *
 * WordPress applies the `paginate_links` filter to individual URL strings before
 * they are wrapped in HTML. The generic digit helper must not run there or it
 * rewrites ports, paths, and query args (breaking pagination on filtered shops).
 *
 * @param string               $html Pagination markup.
 * @param array<string, mixed> $args Pagination args.
 * @return string
 */
function diako_filter_paginate_links_output_digits( $html, $args ) {
	unset( $args );

	return diako_filter_persian_digits_string( (string) $html );
}

/**
 * Register Persian digit filters.
 *
 * @return void
 */
function diako_register_persian_digit_filters() {
	$content_filters = array(
		'the_title',
		'the_content',
		'the_excerpt',
		'widget_text',
		'widget_text_content',
		'comment_text',
		'get_comment_text',
		'nav_menu_item_title',
		'get_the_date',
		'get_the_time',
		'get_the_modified_date',
		'get_the_modified_time',
	);

	foreach ( $content_filters as $filter ) {
		add_filter( $filter, 'diako_filter_persian_digits_string', 50 );
	}

	add_filter( 'paginate_links_output', 'diako_filter_paginate_links_output_digits', 50, 2 );

	$woocommerce_filters = array(
		'wc_price',
		'woocommerce_get_price_html',
		'woocommerce_cart_item_price',
		'woocommerce_cart_item_subtotal',
		'woocommerce_cart_subtotal',
		'woocommerce_cart_total',
		'woocommerce_cart_totals_order_total_html',
		'woocommerce_cart_totals_coupon_html',
		'woocommerce_coupon_discount_amount_html',
		'woocommerce_order_number',
		'woocommerce_result_count',
	);

	foreach ( $woocommerce_filters as $filter ) {
		add_filter( $filter, 'diako_filter_persian_digits_string', 50 );
		add_filter( $filter, 'diako_normalize_price_html', 55 );
	}

	add_filter( 'number_format_i18n', 'diako_filter_persian_digits_string', 50 );
}
add_action( 'init', 'diako_register_persian_digit_filters' );

/**
 * Convert visible digits in wp-admin after the dashboard renders.
 *
 * @return void
 */
function diako_print_admin_persian_digits_script() {
	if ( ! diako_should_use_persian_digits() ) {
		return;
	}
	?>
	<script>
		(function () {
			const map = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
			const skip = new Set(['SCRIPT', 'STYLE', 'INPUT', 'TEXTAREA', 'SELECT', 'CODE', 'PRE']);

			const shouldSkipNode = (node) => {
				if (!node || node.nodeType !== Node.ELEMENT_NODE) {
					return false;
				}

				if (skip.has(node.nodeName)) {
					return true;
				}

				if (node.classList && node.classList.contains('hidden')) {
					return true;
				}

				if (node.id && (node.id.indexOf('inline_') === 0 || node.id.indexOf('edit-') === 0)) {
					return true;
				}

				return false;
			};

			const convert = (value) =>
				String(value).replace(/[0-9]/g, (digit) => map[Number(digit)]);

			const walk = (node) => {
				if (!node) {
					return;
				}

				if (shouldSkipNode(node)) {
					return;
				}

				if (node.nodeType === Node.TEXT_NODE) {
					node.nodeValue = convert(node.nodeValue);
					return;
				}

				if (node.nodeType !== Node.ELEMENT_NODE) {
					return;
				}

				node.childNodes.forEach(walk);
			};

			const run = () => walk(document.getElementById('wpwrap') || document.body);

			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', run);
			} else {
				run();
			}
		})();
	</script>
	<?php
}
add_action( 'admin_print_footer_scripts', 'diako_print_admin_persian_digits_script', 100 );
add_action( 'login_footer', 'diako_print_admin_persian_digits_script', 100 );

/**
 * Convert WooCommerce AJAX cart fragments.
 *
 * @param array<string, string> $fragments Cart fragments.
 * @return array<string, string>
 */
function diako_persian_digits_cart_fragments( $fragments ) {
	foreach ( $fragments as $key => $html ) {
		if ( is_string( $html ) ) {
			$fragments[ $key ] = diako_filter_persian_digits_string( $html );
		}
	}

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'diako_persian_digits_cart_fragments', 50 );

/**
 * Localize Persian digit helper for frontend JavaScript.
 *
 * @return void
 */
function diako_localize_persian_digits_script() {
	wp_localize_script(
		'diako-main',
		'diakoLocale',
		array(
			'usePersianDigits' => diako_should_use_persian_digits(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'diako_localize_persian_digits_script', 110 );
