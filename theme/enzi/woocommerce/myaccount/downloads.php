<?php
/**
 * Downloads
 *
 * @package Diako
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;

$downloads     = WC()->customer->get_downloadable_products();
$has_downloads = (bool) $downloads;

do_action( 'woocommerce_before_account_downloads', $has_downloads );

if ( $has_downloads ) :
	do_action( 'woocommerce_before_available_downloads' );
	do_action( 'woocommerce_available_downloads', $downloads );
	do_action( 'woocommerce_after_available_downloads' );
else :
	diako_render_account_empty_state(
		array(
			'icon'    => 'download',
			'message' => __( 'No downloads available yet.', 'woocommerce' ),
			'button'  => wc_get_page_id( 'shop' ) > 0
				? array(
					'href'  => apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ),
					'label' => apply_filters( 'woocommerce_return_to_shop_text', __( 'Browse products', 'woocommerce' ) ),
				)
				: null,
		)
	);
endif;

do_action( 'woocommerce_after_account_downloads', $has_downloads );
