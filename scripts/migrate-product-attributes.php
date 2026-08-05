<?php
/**
 * LEGACY (gaming catalogue) — not used by Enzi shop.
 *
 * Migrate per-product custom WooCommerce attributes to global attributes.
 *
 * Usage: wp eval-file migrate-product-attributes.php --user=iminiaki
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce is not active.' );
}

// Canonical name merges (custom name => global attribute label).
$name_map = array(
	'cpu'                  => 'CPU',
	'کاربرد ها'            => 'کاربردها',
	'سایر ویژگی ها'        => 'سایر ویژگی‌ها',
	'سرعت انتقال دادها'    => 'سرعت انتقال داده‌ها',
	'رنگ'                  => 'رنگ',
	'سال ساخت'             => 'سال ساخت',
	'تاریخ عرضه'           => 'تاریخ عرضه',
	'سبک بازی'             => 'ژانر بازی',
);

// Global attribute label => slug (without pa_ prefix).
$slug_map = array(
	'رنگ'                  => 'color',
	'ژانر بازی'            => 'game-genre',
	'خانواده محصول'        => 'product-family',
	'تاریخ عرضه'           => 'publication-date',
	'سال ساخت'             => 'year-of-manufacture',
	'نوع'                  => 'product-type',
	'سازنده'               => 'manufacturer',
	'کاربردها'             => 'use-cases',
	'مناسب'                => 'compatible-with',
	'سایر ویژگی‌ها'        => 'other-features',
	'باتری'                => 'battery',
	'خروجی صدا'            => 'audio-output',
	'محدوده استفاده بی‌سیم' => 'wireless-range',
	'فیدبک'                => 'feedback',
	'نوع اتصال'            => 'connection-type',
	'امکانات ارتباطی'      => 'connectivity-features',
	'امکانات ارتباط'       => 'connectivity',
	'میکروفون'             => 'microphone',
	'تریگرها'              => 'triggers',
	'لوازم جانبی قابل اتصال' => 'compatible-accessories',
	'RAM'                  => 'ram',
	'GPU'                  => 'gpu',
	'صدا'                  => 'audio',
	'مدت زمان شارژ'        => 'charge-time',
	'CPU'                  => 'cpu',
	'وزن'                  => 'weight',
	'نشانگر LED'           => 'led-indicator',
	'درایو'                => 'drive',
	'رابط‌های دستگاه'      => 'device-ports',
	'ظرفیت حافظه'          => 'storage-capacity',
	'Wifi'                 => 'wifi',
	'شبکه آنلاین'          => 'online-network',
	'خروجی تصویر'          => 'video-output',
	'Display'              => 'display',
	'Noise Cancellation'   => 'noise-cancellation',
	'USB'                  => 'usb',
	'اقلام همراه'          => 'included-items',
	'اندازه صفحه نمایش'    => 'screen-size',
	'اندازه میکروفون'      => 'microphone-size',
	'بلوتوث'               => 'bluetooth',
	'بند دوشی'             => 'shoulder-strap',
	'توضیحات صفحه نمایش'   => 'screen-details',
	'جای کابل'             => 'cable-storage',
	'جایگاه شارژ همزمان کنترلرها' => 'controller-charging-dock',
	'جعبه دنده'            => 'gear-shifter',
	'جنس فرمان'            => 'wheel-material',
	'جیب خارجی'            => 'external-pocket',
	'جیب داخلی'            => 'internal-pocket',
	'حافظه داخلی'          => 'internal-memory',
	'حساسیت میکروفون'      => 'microphone-sensitivity',
	'دسته بازی'            => 'game-controller',
	'دکمه‌های حرفه‌ای'      => 'pro-buttons',
	'سازگار با سيستم‌عامل‌هاي' => 'compatible-os',
	'سایر قابلیت‌ها'       => 'other-capabilities',
	'سرعت انتقال داده‌ها'  => 'data-transfer-speed',
	'سنسورها'              => 'sensors',
	'صفحه نمایش لمسی'      => 'touchscreen',
	'طول کابل'             => 'cable-length',
	'عمر باتری'            => 'battery-life',
	'قابلیت نصب'           => 'mounting',
	'قطر فرمان'            => 'wheel-diameter',
	'ماژول‌ها'             => 'modules',
	'محدوده فرکانسی میکروفون' => 'microphone-frequency-range',
	'محفظه ضربه‌گیر'        => 'protective-case',
	'محفظه کنترلر'          => 'controller-case',
	'مديريت'               => 'management-software',
	'مدیریت'               => 'management',
	'مقاومت'               => 'resistance',
	'منبع تغذيه'           => 'power-supply',
	'نسل'                  => 'generation',
	'پدال‌ها'              => 'pedals',
	'پلتفرم‌های سازگار'    => 'compatible-platforms',
	'پورت'                 => 'ports',
	'پورت AUX'             => 'aux-port',
	'پورت HDMI'            => 'hdmi-port',
	'چرخش فرمان'           => 'wheel-rotation',
	'چینش آنالوگ‌ها'       => 'analog-layout',
	'کابل AC'              => 'ac-cable',
	'کابل HDMI'            => 'hdmi-cable',
	'کابل USB'             => 'usb-cable',
	'کنترل'                => 'controls',
	'گرافیک'               => 'graphics',
);

/**
 * Resolve canonical attribute label from a custom attribute name.
 */
function migrate_attr_canonical_name( string $name, array $name_map ): string {
	$name = trim( $name );
	return $name_map[ $name ] ?? $name;
}

/**
 * Get or create a global WooCommerce attribute taxonomy.
 */
function migrate_attr_get_taxonomy( string $label, string $slug ): string {
	global $wpdb;

	$taxonomy = wc_attribute_taxonomy_name( $slug );
	$exists   = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT attribute_id FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s",
			$slug
		)
	);

	if ( ! $exists ) {
		$attribute_id = wc_create_attribute(
			array(
				'name'         => $label,
				'slug'         => $slug,
				'type'         => 'select',
				'order_by'     => 'menu_order',
				'has_archives' => false,
			)
		);

		if ( is_wp_error( $attribute_id ) ) {
			WP_CLI::warning( "Failed to create attribute {$label}: " . $attribute_id->get_error_message() );
			return '';
		}

		delete_transient( 'wc_attribute_taxonomies' );
		WC_Post_Types::register_taxonomies();
	}

	if ( ! taxonomy_exists( $taxonomy ) ) {
		register_taxonomy(
			$taxonomy,
			apply_filters( 'woocommerce_taxonomy_objects_' . $taxonomy, array( 'product' ) ),
			apply_filters(
				'woocommerce_taxonomy_args_' . $taxonomy,
				array(
					'labels'       => array( 'name' => $label ),
					'hierarchical' => false,
					'show_ui'      => false,
					'query_var'    => true,
					'rewrite'      => false,
				)
			)
		);
	}

	return $taxonomy;
}

/**
 * Ensure a term exists and return its ID.
 */
function migrate_attr_ensure_term( string $taxonomy, string $value ): int {
	$value = trim( $value );
	if ( $value === '' ) {
		return 0;
	}

	$term = term_exists( $value, $taxonomy );
	if ( ! $term ) {
		$term = wp_insert_term( $value, $taxonomy );
	}

	if ( is_wp_error( $term ) ) {
		// Retry lookup in case of duplicate slug conflicts.
		$existing = get_term_by( 'name', $value, $taxonomy );
		return $existing ? (int) $existing->term_id : 0;
	}

	return (int) ( is_array( $term ) ? $term['term_id'] : $term );
}

WP_CLI::log( 'Creating global attributes...' );
$taxonomy_cache = array();

foreach ( $slug_map as $label => $slug ) {
	$taxonomy = migrate_attr_get_taxonomy( $label, $slug );
	if ( $taxonomy ) {
		$taxonomy_cache[ $label ] = $taxonomy;
		WP_CLI::log( "  OK: {$label} => {$taxonomy}" );
	}
}

global $wpdb;
$products = $wpdb->get_results(
	"SELECT p.ID, pm.meta_value
	FROM {$wpdb->posts} p
	INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
	WHERE p.post_type = 'product'
	AND p.post_status != 'trash'
	AND pm.meta_key = '_product_attributes'"
);

$stats = array(
	'products_processed' => 0,
	'attributes_migrated' => 0,
	'terms_assigned'      => 0,
	'custom_removed'      => 0,
);

WP_CLI::log( 'Migrating product attributes...' );

foreach ( $products as $row ) {
	$product_id = (int) $row->ID;
	$attributes = maybe_unserialize( $row->meta_value );

	if ( ! is_array( $attributes ) || empty( $attributes ) ) {
		continue;
	}

	$changed       = false;
	$terms_by_tax  = array();
	$keys_to_remove = array();

	foreach ( $attributes as $key => $attr ) {
		if ( ! empty( $attr['is_taxonomy'] ) ) {
			continue;
		}

		$custom_name    = trim( (string) ( $attr['name'] ?? '' ) );
		$canonical_name = migrate_attr_canonical_name( $custom_name, $name_map );

		if ( ! isset( $slug_map[ $canonical_name ] ) ) {
			WP_CLI::warning( "Product {$product_id}: no slug mapping for '{$custom_name}'" );
			continue;
		}

		$taxonomy = $taxonomy_cache[ $canonical_name ] ?? '';
		if ( ! $taxonomy ) {
			continue;
		}

		$values = array_filter(
			array_map( 'trim', explode( '|', (string) ( $attr['value'] ?? '' ) ) )
		);

		if ( empty( $values ) ) {
			$keys_to_remove[] = $key;
			$changed          = true;
			continue;
		}

		if ( ! isset( $terms_by_tax[ $taxonomy ] ) ) {
			$terms_by_tax[ $taxonomy ] = array();
		}

		foreach ( $values as $value ) {
			$term_id = migrate_attr_ensure_term( $taxonomy, $value );
			if ( $term_id ) {
				$terms_by_tax[ $taxonomy ][] = $term_id;
				$stats['terms_assigned']++;
			}
		}

		if ( ! isset( $attributes[ $taxonomy ] ) ) {
			$attributes[ $taxonomy ] = array(
				'name'         => $taxonomy,
				'value'        => '',
				'position'     => (int) ( $attr['position'] ?? 0 ),
				'is_visible'   => (int) ( $attr['is_visible'] ?? 1 ),
				'is_variation' => (int) ( $attr['is_variation'] ?? 0 ),
				'is_taxonomy'  => 1,
			);
		}

		$keys_to_remove[] = $key;
		$changed          = true;
		$stats['attributes_migrated']++;
	}

	if ( ! $changed ) {
		continue;
	}

	foreach ( $keys_to_remove as $key ) {
		unset( $attributes[ $key ] );
		$stats['custom_removed']++;
	}

	foreach ( $terms_by_tax as $taxonomy => $term_ids ) {
		$existing_terms = wp_get_object_terms( $product_id, $taxonomy, array( 'fields' => 'ids' ) );
		if ( is_wp_error( $existing_terms ) ) {
			$existing_terms = array();
		}
		$merged = array_unique( array_merge( $existing_terms, $term_ids ) );
		wp_set_object_terms( $product_id, $merged, $taxonomy, false );
	}

	update_post_meta( $product_id, '_product_attributes', $attributes );
	$stats['products_processed']++;

	if ( 0 === $stats['products_processed'] % 25 ) {
		WP_CLI::log( "  Processed {$stats['products_processed']} products..." );
	}
}

// Regenerate WooCommerce attribute lookup table if available.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::runcommand( 'wc palt regenerate --yes', array( 'exit_error' => false ) );
}

delete_transient( 'wc_attribute_taxonomies' );

WP_CLI::success(
	sprintf(
		"Done. Products updated: %d | Attributes migrated: %d | Terms assigned: %d | Custom entries removed: %d | Global attributes: %d",
		$stats['products_processed'],
		$stats['attributes_migrated'],
		$stats['terms_assigned'],
		$stats['custom_removed'],
		count( $taxonomy_cache )
	)
);
