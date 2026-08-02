<?php
/**
 * WooCommerce cron and HTTP request safeguards.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ensure WooCommerce admin helpers exist before admin_init callbacks run.
 *
 * Some admin-ajax requests can reach admin_init before WC_Admin::includes().
 *
 * @return void
 */
function diako_bootstrap_wc_admin_functions_early(): void {
	if ( function_exists( 'wc_get_page_screen_id' ) || ! defined( 'WC_ABSPATH' ) ) {
		return;
	}

	require_once WC_ABSPATH . 'includes/admin/wc-admin-functions.php';
}
add_action( 'admin_init', 'diako_bootstrap_wc_admin_functions_early', 0 );

/**
 * Normalize Persian digits in product quick edit POST data before WooCommerce saves.
 *
 * @return void
 */
function diako_normalize_product_quick_edit_post_data(): void {
	if ( ! wp_doing_ajax() || empty( $_POST['action'] ) || 'inline-save' !== $_POST['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	if ( empty( $_POST['woocommerce_quick_edit'] ) || empty( $_POST['post_type'] ) || 'product' !== $_POST['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	$keys = array(
		'post_ID',
		'post_author',
		'mm',
		'jj',
		'aa',
		'hh',
		'mn',
		'ss',
		'_regular_price',
		'_sale_price',
		'_weight',
		'_length',
		'_width',
		'_height',
		'_stock',
	);

	foreach ( $keys as $key ) {
		if ( isset( $_POST[ $key ] ) && is_scalar( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$_POST[ $key ] = diako_to_western_digits( (string) wp_unslash( $_POST[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}
	}
}
add_action( 'admin_init', 'diako_normalize_product_quick_edit_post_data', 1 );

/**
 * Re-schedule WooCommerce daily cron when missing (common after migration or cache flush).
 */
function diako_ensure_woocommerce_cron_events(): void {
	if ( ! function_exists( 'WC' ) ) {
		return;
	}

	$events = array(
		'wc_admin_daily'              => 'daily',
		'woocommerce_scheduled_sales' => 'daily',
	);

	foreach ( $events as $hook => $recurrence ) {
		if ( ! wp_next_scheduled( $hook ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, $recurrence, $hook );
		}
	}
}
add_action( 'init', 'diako_ensure_woocommerce_cron_events', 99 );

/**
 * Raise HTTP timeouts for WooCommerce background and status checks on slow hosts.
 *
 * @param array<string, mixed> $args Request arguments.
 * @return array<string, mixed>
 */
function diako_http_request_args( array $args ): array {
	$args['timeout'] = max( (int) ( $args['timeout'] ?? 5 ), 15 );

	return $args;
}
add_filter( 'http_request_args', 'diako_http_request_args' );

/**
 * Site Health: Persian WooCommerce SMS requires PHP SOAP on some gateways.
 *
 * @param array<string, mixed> $tests Site health tests.
 * @return array<string, mixed>
 */
function diako_register_pwsms_soap_site_health_test( array $tests ): array {
	$tests['direct']['diako_pwsms_soap'] = array(
		'label' => __( 'PHP SOAP for order SMS', 'diako' ),
		'test'  => function () {
			if ( ! class_exists( 'PW\PWSMS\Helper' ) || class_exists( 'SoapClient' ) ) {
				return array(
					'label'       => __( 'PHP SOAP extension is available for order SMS', 'diako' ),
					'status'      => 'good',
					'badge'       => array(
						'label' => __( 'WooCommerce', 'diako' ),
						'color' => 'green',
					),
					'description' => __( 'Order SMS gateways that use SOAP can run correctly.', 'diako' ),
					'actions'     => '',
					'test'        => 'diako_pwsms_soap',
				);
			}

			return array(
				'label'       => __( 'PHP SOAP extension is missing for order SMS', 'diako' ),
				'status'      => 'critical',
				'badge'       => array(
					'label' => __( 'WooCommerce', 'diako' ),
					'color' => 'red',
				),
				'description' => __( 'Persian WooCommerce SMS is active but PHP SOAP is not installed. Checkout can create orders and then fail with a generic error when SMS is sent.', 'diako' ),
				'actions'     => '',
				'test'        => 'diako_pwsms_soap',
			);
		},
	);

	return $tests;
}
add_filter( 'site_status_tests', 'diako_register_pwsms_soap_site_health_test' );
