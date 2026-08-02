<?php
/**
 * Seed 4 mock Enzi beauty products with images.
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
		'title'       => 'سرم ویتامین C روشن‌کننده',
		'slug'        => 'vitamin-c-brightening-serum',
		'price'       => '890000',
		'regular'     => '1090000',
		'sku'         => 'ENZI-SK-001',
		'stock'       => 24,
		'category'    => 'skincare',
		'image'       => 'mock-product-serum.png',
		'description' => 'سرم روشن‌کننده با ویتامین C برای یکنواخت‌کردن رنگ پوست، افزایش درخشندگی و بهبود بافت. مناسب استفاده روزانه در روتین مراقبت پوست.',
		'short'       => 'روشن‌کننده، آنتی‌اکسیدان، مناسب انواع پوست',
	),
	array(
		'title'       => 'رژلب مات مخملی',
		'slug'        => 'velvet-matte-lipstick',
		'price'       => '450000',
		'regular'     => '520000',
		'sku'         => 'ENZI-MK-001',
		'stock'       => 40,
		'category'    => 'makeup',
		'image'       => 'mock-product-lipstick.png',
		'description' => 'رژلب مات با بافت مخملی و ماندگاری بالا. پوشش یکنواخت بدون خشکی لب، مناسب استفاده روزانه و مجلسی.',
		'short'       => 'مات مخملی، ماندگار، رنگ غنی',
	),
	array(
		'title'       => 'روغن آرگان مراقبت مو',
		'slug'        => 'argan-hair-care-oil',
		'price'       => '620000',
		'regular'     => '750000',
		'sku'         => 'ENZI-HC-001',
		'stock'       => 18,
		'category'    => 'hair-care',
		'image'       => 'mock-product-hair-oil.png',
		'description' => 'روغن آرگان مغذی برای ترمیم موهای خشک و آسیب‌دیده، افزایش نرمی و درخشش بدون سنگینی.',
		'short'       => 'تغذیه و ترمیم، درخشش طبیعی',
	),
	array(
		'title'       => 'ست براش آرایشی حرفه‌ای',
		'slug'        => 'pro-makeup-brush-set',
		'price'       => '780000',
		'regular'     => '890000',
		'sku'         => 'ENZI-BT-001',
		'stock'       => 15,
		'category'    => 'beauty-tools',
		'image'       => 'mock-product-brushes.png',
		'description' => 'ست براش حرفه‌ای برای پخش یکنواخت کرم پودر، پودر، سایه و گونه‌نما. الیاف نرم مناسب پوست حساس.',
		'short'       => 'الیاف نرم، کاربرد چندمنظوره',
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

	$term = get_term_by( 'slug', $item['category'], 'product_cat' );

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
