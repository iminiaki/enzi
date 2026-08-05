<?php
/**
 * Seed 4 mock Enzi shop products with images.
 *
 * Usage: wp eval-file /scripts/seed-mock-products.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wc_get_product' ) ) {
	WP_CLI::error( 'WooCommerce is not available.' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Import a theme asset into the media library (idempotent by source filename).
 *
 * @param string $file_path Absolute path.
 * @param string $title     Attachment title.
 * @return int
 */
function enzi_seed_import_attachment( $file_path, $title ) {
	if ( ! is_readable( $file_path ) ) {
		return 0;
	}

	$filename = basename( $file_path );
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'post_status'    => 'inherit',
			'meta_query'     => array(
				array(
					'key'   => '_enzi_mock_product_source',
					'value' => $filename,
				),
			),
			'fields'         => 'ids',
		)
	);

	if ( ! empty( $existing ) ) {
		return (int) $existing[0];
	}

	$tmp = wp_tempnam( $filename );

	if ( ! $tmp || ! copy( $file_path, $tmp ) ) {
		if ( $tmp ) {
			@unlink( $tmp );
		}
		return 0;
	}

	$attachment_id = media_handle_sideload(
		array(
			'name'     => $filename,
			'tmp_name' => $tmp,
		),
		0,
		$title
	);

	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $tmp );
		WP_CLI::warning( $attachment_id->get_error_message() );
		return 0;
	}

	update_post_meta( (int) $attachment_id, '_enzi_mock_product_source', $filename );

	return (int) $attachment_id;
}

$products = array(
	array(
		'title'       => 'ماگ سرامیکی انزی‌شاپ',
		'slug'        => 'enzi-ceramic-mug',
		'price'       => '890000',
		'regular'     => '1090000',
		'sku'         => 'ENZI-001',
		'stock'       => 24,
		'category'    => '',
		'image'       => 'mock-product-serum.png',
		'description' => 'ماگ سرامیکی با طراحی مینیمال، مناسب استفاده روزانه در خانه یا محل کار.',
		'short'       => 'سرامیک باکیفیت، طراحی ساده',
	),
	array(
		'title'       => 'دفتر یادداشت A5',
		'slug'        => 'enzi-a5-notebook',
		'price'       => '450000',
		'regular'     => '520000',
		'sku'         => 'ENZI-002',
		'stock'       => 40,
		'category'    => '',
		'image'       => 'mock-product-lipstick.png',
		'description' => 'دفتر یادداشت A5 با جلد سخت و کاغذ مناسب نوشتن روزانه.',
		'short'       => 'جلد سخت، کاغذ مرغوب',
	),
	array(
		'title'       => 'قمقمه استیل ضدزنگ',
		'slug'        => 'enzi-steel-bottle',
		'price'       => '620000',
		'regular'     => '750000',
		'sku'         => 'ENZI-003',
		'stock'       => 18,
		'category'    => '',
		'image'       => 'mock-product-hair-oil.png',
		'description' => 'قمقمه استیل با عایق حرارتی برای نگهداری نوشیدنی سرد یا گرم.',
		'short'       => 'استیل ضدزنگ، عایق حرارتی',
	),
	array(
		'title'       => 'ست خودکار و مداد',
		'slug'        => 'enzi-pen-pencil-set',
		'price'       => '780000',
		'regular'     => '890000',
		'sku'         => 'ENZI-004',
		'stock'       => 15,
		'category'    => '',
		'image'       => 'mock-product-brushes.png',
		'description' => 'ست خودکار و مداد با کیفیت نوشتاری مناسب برای استفاده روزمره.',
		'short'       => 'نوشتار روان، طراحی ارگونومیک',
	),
);

$theme_images = get_template_directory() . '/assets/images/products/';
$created      = 0;

foreach ( $products as $item ) {
	$existing = get_page_by_path( $item['slug'], OBJECT, 'product' );

	if ( $existing instanceof WP_Post ) {
		WP_CLI::log( "Exists: {$item['slug']} (#{$existing->ID})" );
		continue;
	}

	$product = new WC_Product_Simple();
	$product->set_name( $item['title'] );
	$product->set_slug( $item['slug'] );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_description( $item['description'] );
	$product->set_short_description( $item['short'] );
	$product->set_sku( $item['sku'] );
	$product->set_regular_price( $item['regular'] );
	$product->set_sale_price( $item['price'] );
	$product->set_price( $item['price'] );
	$product->set_manage_stock( true );
	$product->set_stock_quantity( $item['stock'] );
	$product->set_stock_status( 'instock' );
	$product->set_sold_individually( false );

	$term = ! empty( $item['category'] ) ? get_term_by( 'slug', $item['category'], 'product_cat' ) : false;

	if ( $term instanceof WP_Term ) {
		$product->set_category_ids( array( (int) $term->term_id ) );
	}

	$image_path = $theme_images . $item['image'];
	$image_id   = enzi_seed_import_attachment( $image_path, $item['title'] );

	if ( $image_id > 0 ) {
		$product->set_image_id( $image_id );
	}

	$product_id = $product->save();

	if ( ! $product_id ) {
		WP_CLI::warning( "Failed to create {$item['slug']}" );
		continue;
	}

	++$created;
	WP_CLI::log( "Created: {$item['title']} (#{$product_id})" );
}

WP_CLI::success( "Mock products ready. Created {$created} new product(s)." );
