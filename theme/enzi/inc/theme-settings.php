<?php
/**
 * Enzi shop theme settings (homepage & branding).
 *
 * @package Enzi
 */

defined( 'ABSPATH' ) || exit;

define( 'DIAKO_THEME_SETTINGS_OPTION', 'lastify_theme_settings' );

/**
 * Default store brand name shown in theme copy.
 *
 * @return string
 */
function diako_get_default_brand_name() {
	return 'انزی‌شاپ';
}

/**
 * Store brand name used in theme copy.
 *
 * @return string
 */
function diako_get_brand_name() {
	$settings = diako_get_theme_settings();
	$brand    = trim( (string) ( $settings['brand_name'] ?? '' ) );

	if ( '' === $brand || in_array( $brand, array( 'Lastify', 'لستیفای', 'انزی', 'Enzi shop' ), true ) ) {
		return diako_get_default_brand_name();
	}

	return $brand;
}

/**
 * Replace legacy theme-name placeholders saved as the store brand.
 *
 * @param mixed $value Setting value.
 * @return mixed
 */
function diako_replace_legacy_brand_strings( $value ) {
	if ( is_string( $value ) ) {
		$replacements = array(
			'Lastify'                              => diako_get_default_brand_name(),
			'لستیفای'                              => diako_get_default_brand_name(),
			'انزی'                                 => diako_get_default_brand_name(),
			'Enzi shop'                            => diako_get_default_brand_name(),
			'فروشگاه تخصصی مراقبت پوست و زیبایی'   => sprintf(
				/* translators: %s: store brand name */
				__( 'فروشگاه آنلاین %s', 'diako' ),
				diako_get_default_brand_name()
			),
			'مراقبت پوست و آرایش را از'            => __( 'خرید مطمئن را از', 'diako' ),
			'مراقبت پوست و زیبایی'                 => diako_get_default_brand_name(),
			'راهنما و مقالات مراقبت پوست، آرایش و زیبایی' => sprintf(
				/* translators: %s: store brand name */
				__( 'راهنماها، نکات خرید و مقالات %s', 'diako' ),
				diako_get_default_brand_name()
			),
			'روتین زیبایی'                         => __( 'خرید شما', 'diako' ),
			'جدیدترین مراقبت پوست'                 => sprintf(
				/* translators: %s: store brand name */
				__( 'جدیدترین محصولات %s', 'diako' ),
				diako_get_default_brand_name()
			),
			'جدیدترین محصولات مراقبت پوست'        => __( 'جدیدترین محصولات', 'diako' ),
			'محصولات مراقبت پوست و آرایش'          => __( 'جدیدترین محصولات', 'diako' ),
			'مجله انزی'                            => sprintf(
				/* translators: %s: store brand name */
				__( 'مجله %s', 'diako' ),
				diako_get_default_brand_name()
			),
		);

		return str_replace(
			array_keys( $replacements ),
			array_values( $replacements ),
			$value
		);
	}

	if ( is_array( $value ) ) {
		foreach ( $value as $key => $item ) {
			$value[ $key ] = diako_replace_legacy_brand_strings( $item );
		}
	}

	return $value;
}

/**
 * Default theme settings (matches original hardcoded front page).
 *
 * @return array<string, mixed>
 */
function diako_get_default_theme_settings() {
	$brand = diako_get_default_brand_name();

	return array(
		'brand_name' => $brand,
		'shop'       => diako_get_default_shop_settings(),
		'security'   => diako_get_default_security_settings(),
		'branding'   => diako_get_default_branding_settings(),
		'hero'       => array(
			'enabled'              => true,
			'badge'                => sprintf(
				/* translators: %s: store brand name */
				__( 'فروشگاه آنلاین %s', 'diako' ),
				$brand
			),
			'title'                => sprintf(
				/* translators: %s: store brand name */
				__( 'خرید مطمئن را از %s تجربه کنید', 'diako' ),
				$brand
			),
			'description'          => __( 'محصولات متنوع با ارسال سریع، پرداخت امن و پشتیبانی واقعی در تمام مراحل خرید.', 'diako' ),
			'cta_primary_label'    => __( 'مشاهده فروشگاه', 'diako' ),
			'cta_primary_url'      => '/shop/',
			'cta_secondary_label'  => __( 'تخفیف‌های امروز', 'diako' ),
			'cta_secondary_url'    => '/discount/',
		),
		'categories' => array(
			'enabled'      => true,
			'title'        => __( 'دسته‌بندی محصولات', 'diako' ),
			'description'  => sprintf(
				/* translators: %s: store brand name */
				__( 'انواع محصولات را در %s کشف کنید', 'diako' ),
				$brand
			),
			'button_text'  => __( 'همه محصولات', 'diako' ),
			'button_url'   => '/shop/',
			'items'        => array(
				array(
					'title' => __( 'محصولات جدید', 'diako' ),
					'desc'  => __( 'تازه‌ترین کالاهای اضافه‌شده به فروشگاه', 'diako' ),
					'link'  => '/shop/',
					'icon'  => 'sparkles',
				),
				array(
					'title' => __( 'پیشنهاد ویژه', 'diako' ),
					'desc'  => __( 'تخفیف‌ها و فرصت‌های خرید امروز', 'diako' ),
					'link'  => '/discount/',
					'icon'  => 'heart',
				),
				array(
					'title' => __( 'همه دسته‌ها', 'diako' ),
					'desc'  => sprintf(
						/* translators: %s: store brand name */
						__( 'مرور کامل محصولات %s', 'diako' ),
						$brand
					),
					'link'  => '/shop/',
					'icon'  => 'package',
				),
			),
		),
		'featured_products' => array(
			'enabled'     => true,
			'title'       => __( 'محصولات پرفروش', 'diako' ),
			'description' => __( 'محصولات برجسته فروشگاه', 'diako' ),
			'button_text' => __( 'مشاهده همه', 'diako' ),
			'button_url'  => '/shop/',
		),
		'coming_soon_products' => array(
			'enabled'     => true,
			'title'       => sprintf(
				/* translators: %s: store brand name */
				__( 'به زودی در %s', 'diako' ),
				$brand
			),
			'description' => __( 'محصولات جدید به زودی', 'diako' ),
			'button_text' => __( 'مشاهده همه', 'diako' ),
			'button_url'  => '',
		),
		'coming_soon_page'     => array(
			'enabled'          => false,
			'title'            => __( 'به زودی برمی‌گردیم', 'diako' ),
			'description'      => sprintf(
				/* translators: %s: store brand name */
				__( 'فروشگاه %s در حال آماده‌سازی است. به زودی با جدیدترین محصولات در خدمت شما هستیم.', 'diako' ),
				$brand
			),
			'show_countdown'   => true,
			'launch_date'      => '',
			'show_newsletter'  => true,
			'newsletter_label' => __( 'ایمیل خود را وارد کنید…', 'diako' ),
			'show_contact'     => true,
		),
		'product_sections' => array(
			array(
				'enabled'     => true,
				'title'       => sprintf(
					/* translators: %s: store brand name */
					__( 'محصولات برجسته %s', 'diako' ),
					$brand
				),
				'description' => sprintf(
					/* translators: %s: store brand name */
					__( 'منتخب محصولات %s', 'diako' ),
					$brand
				),
				'button_url'  => '/shop/',
				'query_type'  => 'featured',
			),
			array(
				'enabled'     => true,
				'title'       => sprintf(
					/* translators: %s: store brand name */
					__( 'تخفیف‌های امروز در %s', 'diako' ),
					$brand
				),
				'description' => sprintf(
					/* translators: %s: store brand name */
					__( 'بیشترین تخفیف‌ها در %s', 'diako' ),
					$brand
				),
				'button_url'  => '/discount/',
				'query_type'  => 'on_sale',
			),
			array(
				'enabled'       => true,
				'title'         => sprintf(
					/* translators: %s: store brand name */
					__( 'جدیدترین محصولات %s', 'diako' ),
					$brand
				),
				'description'   => sprintf(
					/* translators: %s: store brand name */
					__( 'تازه‌ترین کالاهای اضافه‌شده به %s', 'diako' ),
					$brand
				),
				'button_url'    => '/shop/',
				'query_type'    => 'category_id',
				'category_slug' => '',
				'category_id'   => 0,
			),
			array(
				'enabled'     => true,
				'title'       => sprintf(
					/* translators: %s: store brand name */
					__( 'خرید از %s', 'diako' ),
					$brand
				),
				'description' => __( 'محصولات متنوع با ارسال سریع به سراسر کشور', 'diako' ),
				'button_url'  => '/shop/',
				'query_type'  => 'featured',
			),
		),
		'promo_banner' => array(
			'enabled'   => true,
			'image_id'  => 0,
			'image_url' => '',
			'filename'  => '',
			'fallback'  => '',
			'url'       => '/shop/',
			'alt'       => $brand,
		),
		'banners'      => array(
			'enabled' => true,
			'items'   => array(
				array(
					'image_id'  => 0,
					'image_url' => '',
					'url'       => '/shop/',
					'alt'       => sprintf(
						/* translators: %s: store brand name */
						__( 'محصولات %s', 'diako' ),
						$brand
					),
				),
				array(
					'image_id'  => 0,
					'image_url' => '',
					'url'       => '/discount/',
					'alt'       => sprintf(
						/* translators: %s: store brand name */
						__( 'تخفیف‌های %s', 'diako' ),
						$brand
					),
				),
			),
		),
		'blog'         => array(
			'enabled'     => true,
			'title'       => sprintf(
				/* translators: %s: store brand name */
				__( 'مجله %s', 'diako' ),
				$brand
			),
			'description' => sprintf(
				/* translators: %s: store brand name */
				__( 'راهنماها، نکات خرید و مقالات %s', 'diako' ),
				$brand
			),
			'button_text' => __( 'مشاهده همه', 'diako' ),
			'button_url'  => '/mag/',
			'post_count'  => 4,
		),
	);
}

/**
 * Merge saved settings with defaults.
 *
 * @return array<string, mixed>
 */
function diako_get_theme_settings() {
	$defaults = diako_get_default_theme_settings();
	$saved    = get_option( DIAKO_THEME_SETTINGS_OPTION, array() );

	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	$settings = diako_merge_theme_settings( $defaults, $saved );

	return diako_replace_legacy_brand_strings( $settings );
}

/**
 * Deep-merge theme settings arrays.
 *
 * @param array<string, mixed> $defaults Defaults.
 * @param array<string, mixed> $saved    Saved values.
 * @return array<string, mixed>
 */
function diako_merge_theme_settings( array $defaults, array $saved ) {
	$merged = $defaults;

	foreach ( $saved as $key => $value ) {
		if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
			if ( diako_is_list_array( $value ) && diako_is_list_array( $merged[ $key ] ) ) {
				$default_list = $merged[ $key ];
				$merged_list  = array();
				$count        = max( count( $default_list ), count( $value ) );

				for ( $i = 0; $i < $count; $i++ ) {
					$default_item = $default_list[ $i ] ?? array();
					$saved_item   = $value[ $i ] ?? array();

					if ( is_array( $default_item ) && is_array( $saved_item ) ) {
						$merged_list[ $i ] = diako_merge_theme_settings( $default_item, $saved_item );
					} elseif ( is_array( $saved_item ) ) {
						$merged_list[ $i ] = $saved_item;
					} elseif ( is_array( $default_item ) ) {
						$merged_list[ $i ] = $default_item;
					}
				}

				$merged[ $key ] = $merged_list;
				continue;
			}

			$merged[ $key ] = diako_merge_theme_settings( $merged[ $key ], $value );
			continue;
		}

		$merged[ $key ] = $value;
	}

	return $merged;
}

/**
 * Whether an array is a list (sequential numeric keys).
 *
 * @param array<mixed> $array Array to check.
 * @return bool
 */
function diako_is_list_array( array $array ) {
	if ( array() === $array ) {
		return true;
	}

	return array_keys( $array ) === range( 0, count( $array ) - 1 );
}

/**
 * Build WP_Query args from a product section config.
 *
 * @param array<string, mixed> $section Section settings.
 * @return array<string, mixed>
 */
function diako_build_product_section_query( array $section ) {
	$query = array(
		'post_type'      => 'product',
		'posts_per_page' => 8,
		'post_status'    => 'publish',
	);

	$query_type = $section['query_type'] ?? 'category_id';

	switch ( $query_type ) {
		case 'on_sale':
			$query['meta_query'] = array(
				array(
					'key'     => '_sale_price',
					'value'   => '',
					'compare' => '!=',
				),
			);
			break;

		case 'category_slug':
			$slug = sanitize_title( (string) ( $section['category_slug'] ?? '' ) );

			if ( '' !== $slug ) {
				$query['tax_query'] = array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => $slug,
					),
				);
				$query['orderby']   = 'date';
				$query['order']     = 'DESC';
			}
			break;

		case 'featured':
			$query = diako_build_featured_products_query();
			break;

		case 'category_id':
		default:
			$term_id = absint( $section['category_id'] ?? 0 );

			if ( $term_id > 0 ) {
				$query['tax_query'] = array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $term_id,
					),
				);
			}
			break;
	}

	return $query;
}

/**
 * WP_Query args for featured (starred) products.
 *
 * @return array<string, mixed>
 */
function diako_build_featured_products_query() {
	$query = array(
		'post_type'           => 'product',
		'posts_per_page'      => 8,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
	);

	if ( function_exists( 'WC' ) && WC()->query ) {
		$query['meta_query'] = WC()->query->get_meta_query();
		$query['tax_query']  = WC()->query->get_tax_query();
	} else {
		$query['tax_query'] = array();
	}

	$query['tax_query'][] = array(
		'taxonomy' => 'product_visibility',
		'field'    => 'name',
		'terms'    => 'featured',
		'operator' => 'IN',
	);

	return $query;
}

/**
 * WP_Query args for products in the coming-soon category.
 *
 * @return array<string, mixed>
 */
function diako_build_coming_soon_products_query() {
	$term_id = function_exists( 'diako_get_coming_soon_category_id' ) ? diako_get_coming_soon_category_id() : 0;

	if ( $term_id > 0 ) {
		return diako_build_product_section_query(
			array(
				'query_type'  => 'category_id',
				'category_id' => $term_id,
			)
		);
	}

	return diako_build_product_section_query(
		array(
			'query_type'    => 'category_slug',
			'category_slug' => 'coming-soon',
		)
	);
}

/**
 * Render the homepage coming-soon product row.
 *
 * @param array<string, mixed> $settings Theme settings.
 * @return void
 */
function diako_render_coming_soon_product_section( array $settings = array() ): void {
	if ( empty( $settings ) ) {
		$settings = diako_get_theme_settings();
	}

	$coming_soon = $settings['coming_soon_products'] ?? array();

	if ( empty( $coming_soon['enabled'] ) ) {
		return;
	}

	$button_url = trim( (string) ( $coming_soon['button_url'] ?? '' ) );

	if ( '' === $button_url ) {
		$button_url = diako_get_coming_soon_category_url();
	}

	diako_product_section(
		array(
			'title'       => $coming_soon['title'] ?? '',
			'description' => $coming_soon['description'] ?? '',
			'button_text' => $coming_soon['button_text'] ?? __( 'مشاهده همه', 'diako' ),
			'button_url'  => diako_theme_settings_url( $button_url ),
			'query'       => diako_build_coming_soon_products_query(),
		)
	);
}

/**
 * Whether a product section config is the legacy coming-soon carousel.
 *
 * @param array<string, mixed> $section Section settings.
 * @return bool
 */
function diako_is_legacy_coming_soon_product_section( array $section ): bool {
	return 'category_slug' === ( $section['query_type'] ?? '' )
		&& 'coming-soon' === sanitize_title( (string) ( $section['category_slug'] ?? '' ) );
}

/**
 * Resolve banner image URL from settings item.
 *
 * @param array<string, mixed> $item Banner item settings.
 * @return string
 */
function diako_resolve_banner_image_url( array $item ) {
	$image_id = absint( $item['image_id'] ?? 0 );

	if ( $image_id > 0 ) {
		$url = wp_get_attachment_image_url( $image_id, 'full' );

		if ( $url ) {
			return $url;
		}
	}

	$image_url = trim( (string) ( $item['image_url'] ?? '' ) );

	if ( '' !== $image_url ) {
		return $image_url;
	}

	return '';
}

/**
 * Merge settings for a single admin tab into the saved option.
 *
 * @param array<string, mixed> $existing   Current saved settings.
 * @param array<string, mixed> $submitted  Sanitized form submission.
 * @param string               $active_tab Active settings tab.
 * @return array<string, mixed>
 */
function diako_merge_tab_settings( array $existing, array $submitted, $active_tab ) {
	switch ( $active_tab ) {
		case 'general':
			$existing['brand_name']       = $submitted['brand_name'];
			$existing['branding']         = $submitted['branding'];
			$existing['shop']             = $submitted['shop'];
			$existing['coming_soon_page'] = $submitted['coming_soon_page'];
			break;
		case 'hero':
			$existing['hero'] = $submitted['hero'];
			break;
		case 'categories':
			$existing['categories'] = $submitted['categories'];
			break;
		case 'products':
			$existing['product_sections']      = $submitted['product_sections'];
			$existing['featured_products']     = $submitted['featured_products'];
			$existing['coming_soon_products'] = $submitted['coming_soon_products'];
			break;
		case 'promo':
			$existing['promo_banner'] = $submitted['promo_banner'];
			break;
		case 'banners':
			$existing['banners'] = $submitted['banners'];
			break;
		case 'blog':
			$existing['blog'] = $submitted['blog'];
			break;
	}

	return $existing;
}

/**
 * Register Enzi shop admin menu.
 *
 * @return void
 */
function diako_register_theme_settings_menu() {
	add_menu_page(
		__( 'انزی‌شاپ', 'diako' ),
		__( 'انزی‌شاپ', 'diako' ),
		'manage_options',
		'lastify',
		'diako_render_theme_settings_page',
		DIAKO_URI . '/assets/images/lastaar-logo.webp',
		59
	);

	add_submenu_page(
		'lastify',
		__( 'تنظیمات صفحه اصلی', 'diako' ),
		__( 'تنظیمات صفحه اصلی', 'diako' ),
		'manage_options',
		'lastify',
		'diako_render_theme_settings_page'
	);
}
add_action( 'admin_menu', 'diako_register_theme_settings_menu', 9 );

/**
 * Enqueue admin assets for theme settings page.
 *
 * @param string $hook_suffix Current admin page hook.
 * @return void
 */
function diako_enqueue_theme_settings_admin_assets( $hook_suffix ) {
	wp_enqueue_style(
		'lastify-admin',
		DIAKO_URI . '/assets/css/admin-theme-settings.css',
		array(),
		DIAKO_VERSION
	);

	$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';

	if ( 'toplevel_page_lastify' !== $hook_suffix ) {
		return;
	}

	if ( in_array( $active_tab, array( 'general', 'slides', 'promo', 'banners' ), true ) ) {
		wp_enqueue_media();
	}

	wp_enqueue_script(
		'lastify-theme-settings',
		DIAKO_URI . '/assets/js/admin-theme-settings.js',
		array( 'jquery' ),
		DIAKO_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'diako_enqueue_theme_settings_admin_assets' );

/**
 * Sanitize submitted theme settings.
 *
 * @param array<string, mixed> $input Raw input.
 * @return array<string, mixed>
 */
function diako_sanitize_theme_settings( array $input ) {
	$defaults = diako_get_default_theme_settings();
	$output   = diako_get_default_theme_settings();

	$output['brand_name'] = sanitize_text_field( $input['brand_name'] ?? $defaults['brand_name'] );
	$saved                = get_option( DIAKO_THEME_SETTINGS_OPTION, array() );
	$existing             = is_array( $saved ) ? diako_merge_theme_settings( $defaults, $saved ) : $defaults;
	$output['security']   = diako_sanitize_security_settings(
		isset( $input['security'] ) && is_array( $input['security'] ) ? $input['security'] : array(),
		$existing
	);
	$output['branding']   = diako_sanitize_branding_settings(
		isset( $input['branding'] ) && is_array( $input['branding'] ) ? $input['branding'] : array()
	);
	$output['shop']       = diako_sanitize_shop_settings(
		isset( $input['shop'] ) && is_array( $input['shop'] ) ? $input['shop'] : array()
	);

	$coming_soon_page = $input['coming_soon_page'] ?? array();
	$launch_raw       = trim( (string) ( $coming_soon_page['launch_date'] ?? '' ) );
	$launch_date      = '';

	if ( '' !== $launch_raw ) {
		$launch_ts = strtotime( str_replace( 'T', ' ', $launch_raw ) );

		if ( false !== $launch_ts ) {
			$launch_date = wp_date( 'Y-m-d H:i', $launch_ts );
		}
	}

	$output['coming_soon_page'] = array(
		'enabled'          => ! empty( $coming_soon_page['enabled'] ),
		'title'            => sanitize_text_field( $coming_soon_page['title'] ?? $defaults['coming_soon_page']['title'] ),
		'description'      => sanitize_textarea_field( $coming_soon_page['description'] ?? $defaults['coming_soon_page']['description'] ),
		'show_countdown'   => ! empty( $coming_soon_page['show_countdown'] ),
		'launch_date'      => $launch_date,
		'show_newsletter'  => ! empty( $coming_soon_page['show_newsletter'] ),
		'newsletter_label' => sanitize_text_field( $coming_soon_page['newsletter_label'] ?? $defaults['coming_soon_page']['newsletter_label'] ),
		'show_contact'     => ! empty( $coming_soon_page['show_contact'] ),
	);

	$hero = $input['hero'] ?? array();
	$output['hero'] = array(
		'enabled'             => ! empty( $hero['enabled'] ),
		'badge'               => sanitize_text_field( $hero['badge'] ?? '' ),
		'title'               => sanitize_text_field( $hero['title'] ?? '' ),
		'description'         => sanitize_textarea_field( $hero['description'] ?? '' ),
		'cta_primary_label'   => sanitize_text_field( $hero['cta_primary_label'] ?? '' ),
		'cta_primary_url'     => diako_sanitize_settings_url( $hero['cta_primary_url'] ?? '' ),
		'cta_secondary_label' => sanitize_text_field( $hero['cta_secondary_label'] ?? '' ),
		'cta_secondary_url'   => diako_sanitize_settings_url( $hero['cta_secondary_url'] ?? '' ),
	);

	$categories = $input['categories'] ?? array();
	$output['categories'] = array(
		'enabled'     => ! empty( $categories['enabled'] ),
		'title'       => sanitize_text_field( $categories['title'] ?? '' ),
		'description' => sanitize_text_field( $categories['description'] ?? '' ),
		'button_text' => sanitize_text_field( $categories['button_text'] ?? '' ),
		'button_url'  => diako_sanitize_settings_url( $categories['button_url'] ?? '' ),
		'items'       => array(),
	);

	$category_items = $categories['items'] ?? array();
	if ( is_array( $category_items ) ) {
		foreach ( $category_items as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$output['categories']['items'][] = array(
				'title' => sanitize_text_field( $item['title'] ?? '' ),
				'desc'  => sanitize_text_field( $item['desc'] ?? '' ),
				'link'  => diako_sanitize_settings_url( $item['link'] ?? '' ),
				'icon'  => sanitize_key( $item['icon'] ?? 'sparkles' ),
			);
		}
	}

	$output['product_sections'] = array();
	$product_sections           = $input['product_sections'] ?? array();

	if ( is_array( $product_sections ) ) {
		foreach ( $product_sections as $section ) {
			if ( ! is_array( $section ) ) {
				continue;
			}

			$query_type = sanitize_key( $section['query_type'] ?? 'category_id' );

			if ( ! in_array( $query_type, array( 'category_id', 'category_slug', 'on_sale', 'featured' ), true ) ) {
				$query_type = 'category_id';
			}

			$button_url = diako_sanitize_settings_url( $section['button_url'] ?? '' );

			$output['product_sections'][] = array(
				'enabled'       => ! empty( $section['enabled'] ),
				'title'         => sanitize_text_field( $section['title'] ?? '' ),
				'description'   => sanitize_text_field( $section['description'] ?? '' ),
				'button_url'    => $button_url,
				'query_type'    => $query_type,
				'category_id'   => absint( $section['category_id'] ?? 0 ),
				'category_slug' => sanitize_title( $section['category_slug'] ?? '' ),
			);
		}
	}

	$featured = $input['featured_products'] ?? array();
	$output['featured_products'] = array(
		'enabled'     => ! empty( $featured['enabled'] ),
		'title'       => sanitize_text_field( $featured['title'] ?? $defaults['featured_products']['title'] ),
		'description' => sanitize_text_field( $featured['description'] ?? '' ),
		'button_text' => sanitize_text_field( $featured['button_text'] ?? $defaults['featured_products']['button_text'] ),
		'button_url'  => diako_sanitize_settings_url( $featured['button_url'] ?? '' ),
	);

	$coming_soon = $input['coming_soon_products'] ?? array();
	$output['coming_soon_products'] = array(
		'enabled'     => ! empty( $coming_soon['enabled'] ),
		'title'       => sanitize_text_field( $coming_soon['title'] ?? $defaults['coming_soon_products']['title'] ),
		'description' => sanitize_text_field( $coming_soon['description'] ?? '' ),
		'button_text' => sanitize_text_field( $coming_soon['button_text'] ?? $defaults['coming_soon_products']['button_text'] ),
		'button_url'  => diako_sanitize_settings_url( $coming_soon['button_url'] ?? '' ),
	);

	$promo = $input['promo_banner'] ?? array();
	$output['promo_banner'] = array(
		'enabled'   => ! empty( $promo['enabled'] ),
		'image_id'  => absint( $promo['image_id'] ?? 0 ),
		'image_url' => esc_url_raw( $promo['image_url'] ?? '' ),
		'filename'  => sanitize_file_name( $promo['filename'] ?? '' ),
		'fallback'  => sanitize_text_field( $promo['fallback'] ?? '' ),
		'url'       => diako_sanitize_settings_url( $promo['url'] ?? '' ),
		'alt'       => sanitize_text_field( $promo['alt'] ?? '' ),
	);

	$banners = $input['banners'] ?? array();
	$output['banners'] = array(
		'enabled' => ! empty( $banners['enabled'] ),
		'items'   => array(),
	);

	$banner_items = $banners['items'] ?? array();
	if ( is_array( $banner_items ) ) {
		foreach ( $banner_items as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$output['banners']['items'][] = array(
				'image_id'  => absint( $item['image_id'] ?? 0 ),
				'image_url' => esc_url_raw( $item['image_url'] ?? '' ),
				'url'       => diako_sanitize_settings_url( $item['url'] ?? '' ),
				'alt'       => sanitize_text_field( $item['alt'] ?? '' ),
			);
		}
	}

	$blog = $input['blog'] ?? array();
	$output['blog'] = array(
		'enabled'     => ! empty( $blog['enabled'] ),
		'title'       => sanitize_text_field( $blog['title'] ?? '' ),
		'description' => sanitize_text_field( $blog['description'] ?? '' ),
		'button_text' => sanitize_text_field( $blog['button_text'] ?? '' ),
		'button_url'  => diako_sanitize_settings_url( $blog['button_url'] ?? '' ),
		'post_count'  => max( 1, min( 12, absint( $blog['post_count'] ?? 4 ) ) ),
	);

	return $output;
}

/**
 * Render Enzi shop theme settings admin page.
 *
 * @return void
 */
function diako_render_theme_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$tabs = array(
		'general'    => __( 'عمومی', 'diako' ),
		'hero'       => __( 'بخش قهرمان', 'diako' ),
		'slides'     => __( 'اسلایدر', 'diako' ),
		'categories' => __( 'دسته‌بندی‌ها', 'diako' ),
		'products'   => __( 'کاروسل محصولات', 'diako' ),
		'promo'      => __( 'بنر تبلیغاتی', 'diako' ),
		'banners'    => __( 'بنرهای دسته', 'diako' ),
		'blog'       => __( 'مجله', 'diako' ),
	);

	$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';

	if ( ! isset( $tabs[ $active_tab ] ) ) {
		$active_tab = 'general';
	}

	if ( isset( $_POST['lastify_slides_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lastify_slides_nonce'] ) ), 'lastify_save_slides' ) ) {
		$slides_raw = isset( $_POST['lastify_hero_slides'] ) && is_array( $_POST['lastify_hero_slides'] ) ? wp_unslash( $_POST['lastify_hero_slides'] ) : array();
		diako_save_hero_slides_settings_tab( $slides_raw );

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'اسلایدها ذخیره شدند.', 'diako' ) . '</p></div>';
	}

	if ( isset( $_POST['lastify_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lastify_settings_nonce'] ) ), 'lastify_save_settings' ) ) {
		$raw         = isset( $_POST['lastify_settings'] ) && is_array( $_POST['lastify_settings'] ) ? wp_unslash( $_POST['lastify_settings'] ) : array();
		$existing    = diako_get_theme_settings();
		$submitted   = diako_sanitize_theme_settings( diako_merge_theme_settings( diako_get_default_theme_settings(), $raw ) );
		$merged_save = diako_merge_tab_settings( $existing, $submitted, $active_tab );

		update_option( DIAKO_THEME_SETTINGS_OPTION, $merged_save );

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'تنظیمات ذخیره شد.', 'diako' ) . '</p></div>';
	}

	$settings = diako_get_theme_settings();
	?>
	<div class="wrap lastify-settings">
		<h1><?php esc_html_e( 'تنظیمات انزی‌شاپ', 'diako' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'محتوای صفحه اصلی و نام برند فروشگاه را از اینجا مدیریت کنید.', 'diako' ); ?>
		</p>

		<nav class="nav-tab-wrapper lastify-settings__tabs">
			<?php foreach ( $tabs as $tab_id => $tab_label ) : ?>
				<a
					href="<?php echo esc_url( admin_url( 'admin.php?page=lastify&tab=' . $tab_id ) ); ?>"
					class="nav-tab<?php echo $active_tab === $tab_id ? ' nav-tab-active' : ''; ?>"
				>
					<?php echo esc_html( $tab_label ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<?php if ( 'slides' === $active_tab ) : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=lastify&tab=slides' ) ); ?>" class="lastify-settings__form">
				<?php wp_nonce_field( 'lastify_save_slides', 'lastify_slides_nonce' ); ?>
				<?php diako_render_hero_slides_settings_tab(); ?>
				<?php submit_button( __( 'ذخیره اسلایدها', 'diako' ) ); ?>
			</form>
		<?php else : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=lastify&tab=' . $active_tab ) ); ?>" class="lastify-settings__form">
			<?php wp_nonce_field( 'lastify_save_settings', 'lastify_settings_nonce' ); ?>

			<?php if ( 'general' === $active_tab ) : ?>
				<?php diako_render_branding_general_fields( $settings ); ?>
				<?php diako_render_shop_general_fields( $settings ); ?>
				<?php diako_render_security_general_fields( $settings ); ?>
				<?php diako_render_coming_soon_page_settings_fields( $settings ); ?>
			<?php endif; ?>

			<?php if ( 'hero' === $active_tab ) : ?>
				<?php diako_render_settings_toggle( 'lastify_settings[hero][enabled]', $settings['hero']['enabled'], __( 'نمایش بخش قهرمان', 'diako' ) ); ?>
				<table class="form-table" role="presentation">
					<?php
					diako_render_settings_field( 'lastify_settings[hero][badge]', __( 'برچسب', 'diako' ), $settings['hero']['badge'] );
					diako_render_settings_field( 'lastify_settings[hero][title]', __( 'عنوان اصلی', 'diako' ), $settings['hero']['title'] );
					diako_render_settings_textarea( 'lastify_settings[hero][description]', __( 'توضیحات', 'diako' ), $settings['hero']['description'] );
					diako_render_settings_field( 'lastify_settings[hero][cta_primary_label]', __( 'دکمه اصلی — متن', 'diako' ), $settings['hero']['cta_primary_label'] );
					diako_render_settings_field( 'lastify_settings[hero][cta_primary_url]', __( 'دکمه اصلی — لینک', 'diako' ), $settings['hero']['cta_primary_url'] );
					diako_render_settings_field( 'lastify_settings[hero][cta_secondary_label]', __( 'دکمه فرعی — متن', 'diako' ), $settings['hero']['cta_secondary_label'] );
					diako_render_settings_field( 'lastify_settings[hero][cta_secondary_url]', __( 'دکمه فرعی — لینک', 'diako' ), $settings['hero']['cta_secondary_url'] );
					?>
				</table>
			<?php endif; ?>

			<?php if ( 'categories' === $active_tab ) : ?>
				<?php diako_render_settings_toggle( 'lastify_settings[categories][enabled]', $settings['categories']['enabled'], __( 'نمایش بخش دسته‌بندی‌ها', 'diako' ) ); ?>
				<table class="form-table" role="presentation">
					<?php
					diako_render_settings_field( 'lastify_settings[categories][title]', __( 'عنوان بخش', 'diako' ), $settings['categories']['title'] );
					diako_render_settings_field( 'lastify_settings[categories][description]', __( 'توضیح بخش', 'diako' ), $settings['categories']['description'] );
					diako_render_settings_field( 'lastify_settings[categories][button_text]', __( 'متن دکمه', 'diako' ), $settings['categories']['button_text'] );
					diako_render_settings_field( 'lastify_settings[categories][button_url]', __( 'لینک دکمه', 'diako' ), $settings['categories']['button_url'] );
					?>
				</table>
				<h2><?php esc_html_e( 'کارت‌های دسته‌بندی', 'diako' ); ?></h2>
				<?php foreach ( $settings['categories']['items'] as $index => $item ) : ?>
					<div class="lastify-settings__card">
						<h3><?php echo esc_html( sprintf( /* translators: %d: card number */ __( 'کارت %d', 'diako' ), $index + 1 ) ); ?></h3>
						<table class="form-table" role="presentation">
							<?php
							diako_render_settings_field( "lastify_settings[categories][items][{$index}][title]", __( 'عنوان', 'diako' ), $item['title'] );
							diako_render_settings_field( "lastify_settings[categories][items][{$index}][desc]", __( 'توضیح', 'diako' ), $item['desc'] );
							diako_render_settings_field( "lastify_settings[categories][items][{$index}][link]", __( 'لینک', 'diako' ), $item['link'] );
							diako_render_settings_field( "lastify_settings[categories][items][{$index}][icon]", __( 'آیکون (Lucide)', 'diako' ), $item['icon'] );
							?>
						</table>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if ( 'products' === $active_tab ) : ?>
				<div class="lastify-settings__card">
					<h2><?php esc_html_e( 'محصولات پرفروش', 'diako' ); ?></h2>
					<p class="description"><?php esc_html_e( 'محصولات برجسته (ستاره‌دار) ووکامرس در این ردیف نمایش داده می‌شوند. اگر محصول برجسته‌ای نباشد، ردیف نمایش داده نمی‌شود.', 'diako' ); ?></p>
					<?php diako_render_settings_toggle( 'lastify_settings[featured_products][enabled]', $settings['featured_products']['enabled'], __( 'فعال', 'diako' ) ); ?>
					<table class="form-table" role="presentation">
						<?php
						diako_render_settings_field( 'lastify_settings[featured_products][title]', __( 'عنوان', 'diako' ), $settings['featured_products']['title'] );
						diako_render_settings_field( 'lastify_settings[featured_products][description]', __( 'توضیح', 'diako' ), $settings['featured_products']['description'] );
						diako_render_settings_field( 'lastify_settings[featured_products][button_text]', __( 'متن دکمه', 'diako' ), $settings['featured_products']['button_text'] );
						diako_render_settings_field( 'lastify_settings[featured_products][button_url]', __( 'لینک دکمه', 'diako' ), $settings['featured_products']['button_url'] );
						?>
					</table>
				</div>

				<div class="lastify-settings__card">
					<h2><?php esc_html_e( 'به زودی', 'diako' ); ?></h2>
					<p class="description"><?php esc_html_e( 'محصولات دسته «به زودی» بین «تخفیف‌های امروز» و «لوازم جانبی» نمایش داده می‌شوند. اگر محصولی در این دسته نباشد، ردیف نمایش داده نمی‌شود.', 'diako' ); ?></p>
					<?php diako_render_settings_toggle( 'lastify_settings[coming_soon_products][enabled]', $settings['coming_soon_products']['enabled'], __( 'فعال', 'diako' ) ); ?>
					<table class="form-table" role="presentation">
						<?php
						diako_render_settings_field( 'lastify_settings[coming_soon_products][title]', __( 'عنوان', 'diako' ), $settings['coming_soon_products']['title'] );
						diako_render_settings_field( 'lastify_settings[coming_soon_products][description]', __( 'توضیح', 'diako' ), $settings['coming_soon_products']['description'] );
						diako_render_settings_field( 'lastify_settings[coming_soon_products][button_text]', __( 'متن دکمه', 'diako' ), $settings['coming_soon_products']['button_text'] );
						diako_render_settings_field( 'lastify_settings[coming_soon_products][button_url]', __( 'لینک دکمه (خالی = دسته به زودی)', 'diako' ), $settings['coming_soon_products']['button_url'] );
						?>
					</table>
				</div>

				<h2><?php esc_html_e( 'کاروسل‌های محصول', 'diako' ); ?></h2>
				<p class="description"><?php esc_html_e( 'هر بخش یک کاروسل محصول در صفحه اصلی است. ترتیب نمایش همان ترتیب فیلدهاست.', 'diako' ); ?></p>
				<?php foreach ( $settings['product_sections'] as $index => $section ) : ?>
					<div class="lastify-settings__card">
						<h3><?php echo esc_html( sprintf( /* translators: %d: section number */ __( 'بخش %d', 'diako' ), $index + 1 ) ); ?></h3>
						<?php diako_render_settings_toggle( "lastify_settings[product_sections][{$index}][enabled]", $section['enabled'], __( 'فعال', 'diako' ) ); ?>
						<table class="form-table" role="presentation">
							<?php
							diako_render_settings_field( "lastify_settings[product_sections][{$index}][title]", __( 'عنوان', 'diako' ), $section['title'] );
							diako_render_settings_field( "lastify_settings[product_sections][{$index}][description]", __( 'توضیح', 'diako' ), $section['description'] );
							diako_render_settings_field( "lastify_settings[product_sections][{$index}][button_url]", __( 'لینک دکمه', 'diako' ), $section['button_url'] );
							?>
							<tr>
								<th scope="row"><label><?php esc_html_e( 'نوع فیلتر محصولات', 'diako' ); ?></label></th>
								<td>
									<select name="lastify_settings[product_sections][<?php echo esc_attr( (string) $index ); ?>][query_type]">
										<option value="category_id" <?php selected( $section['query_type'], 'category_id' ); ?>><?php esc_html_e( 'دسته (شناسه)', 'diako' ); ?></option>
										<option value="category_slug" <?php selected( $section['query_type'], 'category_slug' ); ?>><?php esc_html_e( 'دسته (نامک)', 'diako' ); ?></option>
										<option value="on_sale" <?php selected( $section['query_type'], 'on_sale' ); ?>><?php esc_html_e( 'محصولات تخفیف‌دار', 'diako' ); ?></option>
										<option value="featured" <?php selected( $section['query_type'], 'featured' ); ?>><?php esc_html_e( 'محصولات برجسته', 'diako' ); ?></option>
									</select>
								</td>
							</tr>
							<?php
							diako_render_settings_field( "lastify_settings[product_sections][{$index}][category_id]", __( 'شناسه دسته', 'diako' ), (string) $section['category_id'], 'number' );
							diako_render_settings_field( "lastify_settings[product_sections][{$index}][category_slug]", __( 'نامک دسته', 'diako' ), $section['category_slug'] );
							?>
						</table>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if ( 'promo' === $active_tab ) : ?>
				<?php diako_render_settings_toggle( 'lastify_settings[promo_banner][enabled]', $settings['promo_banner']['enabled'], __( 'نمایش بنر تبلیغاتی', 'diako' ) ); ?>
				<table class="form-table" role="presentation">
					<?php
					diako_render_settings_image_field(
						'lastify_settings[promo_banner]',
						$settings['promo_banner']
					);
					diako_render_settings_field( 'lastify_settings[promo_banner][filename]', __( 'نام فایل (پشتیبان)', 'diako' ), $settings['promo_banner']['filename'] );
					diako_render_settings_field( 'lastify_settings[promo_banner][fallback]', __( 'مسیر پشتیبان در آپلودها', 'diako' ), $settings['promo_banner']['fallback'] );
					diako_render_settings_field( 'lastify_settings[promo_banner][url]', __( 'لینک', 'diako' ), $settings['promo_banner']['url'] );
					diako_render_settings_field( 'lastify_settings[promo_banner][alt]', __( 'متن جایگزین', 'diako' ), $settings['promo_banner']['alt'] );
					?>
				</table>
			<?php endif; ?>

			<?php if ( 'banners' === $active_tab ) : ?>
				<?php diako_render_settings_toggle( 'lastify_settings[banners][enabled]', $settings['banners']['enabled'], __( 'نمایش بنرهای دسته', 'diako' ) ); ?>
				<?php foreach ( $settings['banners']['items'] as $index => $item ) : ?>
					<div class="lastify-settings__card">
						<h3><?php echo esc_html( sprintf( /* translators: %d: banner number */ __( 'بنر %d', 'diako' ), $index + 1 ) ); ?></h3>
						<table class="form-table" role="presentation">
							<?php
							diako_render_settings_image_field( "lastify_settings[banners][items][{$index}]", $item );
							diako_render_settings_field( "lastify_settings[banners][items][{$index}][url]", __( 'لینک', 'diako' ), $item['url'] );
							diako_render_settings_field( "lastify_settings[banners][items][{$index}][alt]", __( 'متن جایگزین', 'diako' ), $item['alt'] );
							?>
						</table>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if ( 'blog' === $active_tab ) : ?>
				<?php diako_render_settings_toggle( 'lastify_settings[blog][enabled]', $settings['blog']['enabled'], __( 'نمایش بخش مجله', 'diako' ) ); ?>
				<table class="form-table" role="presentation">
					<?php
					diako_render_settings_field( 'lastify_settings[blog][title]', __( 'عنوان بخش', 'diako' ), $settings['blog']['title'] );
					diako_render_settings_field( 'lastify_settings[blog][description]', __( 'توضیح بخش', 'diako' ), $settings['blog']['description'] );
					diako_render_settings_field( 'lastify_settings[blog][button_text]', __( 'متن دکمه', 'diako' ), $settings['blog']['button_text'] );
					diako_render_settings_field( 'lastify_settings[blog][button_url]', __( 'لینک دکمه', 'diako' ), $settings['blog']['button_url'] );
					diako_render_settings_field( 'lastify_settings[blog][post_count]', __( 'تعداد مقالات', 'diako' ), (string) $settings['blog']['post_count'], 'number' );
					?>
				</table>
			<?php endif; ?>

			<?php submit_button( __( 'ذخیره تنظیمات', 'diako' ) ); ?>
		</form>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render a text input settings row.
 *
 * @param string $name  Field name.
 * @param string $label Field label.
 * @param string $value Field value.
 * @param string $type  Input type.
 * @return void
 */
function diako_render_settings_field( $name, $label, $value, $type = 'text' ) {
	?>
	<tr>
		<th scope="row"><label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label></th>
		<td>
			<input
				id="<?php echo esc_attr( $name ); ?>"
				class="regular-text"
				type="<?php echo esc_attr( $type ); ?>"
				name="<?php echo esc_attr( $name ); ?>"
				value="<?php echo esc_attr( $value ); ?>"
			>
		</td>
	</tr>
	<?php
}

/**
 * Render a textarea settings row.
 *
 * @param string $name  Field name.
 * @param string $label Field label.
 * @param string $value Field value.
 * @return void
 */
function diako_render_settings_textarea( $name, $label, $value ) {
	?>
	<tr>
		<th scope="row"><label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label></th>
		<td>
			<textarea id="<?php echo esc_attr( $name ); ?>" class="large-text" rows="3" name="<?php echo esc_attr( $name ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
		</td>
	</tr>
	<?php
}

/**
 * Render a checkbox toggle.
 *
 * @param string $name    Field name.
 * @param bool   $checked Whether checked.
 * @param string $label   Label text.
 * @return void
 */
function diako_render_settings_toggle( $name, $checked, $label ) {
	?>
	<p>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $checked ); ?>>
			<?php echo esc_html( $label ); ?>
		</label>
	</p>
	<?php
}

/**
 * Render image picker field group.
 *
 * @param string               $prefix Field name prefix (array path without [image_id]).
 * @param array<string, mixed> $item   Image item data.
 * @return void
 */
function diako_render_settings_image_field( $prefix, array $item ) {
	$image_id  = absint( $item['image_id'] ?? 0 );
	$image_url = (string) ( $item['image_url'] ?? '' );
	$preview   = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : $image_url;
	?>
	<tr>
		<th scope="row"><?php esc_html_e( 'تصویر', 'diako' ); ?></th>
		<td>
			<div class="lastify-image-field" data-lastify-image-field>
				<input type="hidden" name="<?php echo esc_attr( $prefix ); ?>[image_id]" value="<?php echo esc_attr( (string) $image_id ); ?>" data-lastify-image-id>
				<input class="regular-text" type="url" name="<?php echo esc_attr( $prefix ); ?>[image_url]" value="<?php echo esc_attr( $image_url ); ?>" data-lastify-image-url placeholder="https://">
				<p>
					<button type="button" class="button" data-lastify-select-image><?php esc_html_e( 'انتخاب تصویر', 'diako' ); ?></button>
					<button type="button" class="button" data-lastify-clear-image><?php esc_html_e( 'حذف', 'diako' ); ?></button>
				</p>
				<div class="lastify-image-field__preview" data-lastify-image-preview>
					<?php if ( $preview ) : ?>
						<img src="<?php echo esc_url( $preview ); ?>" alt="">
					<?php endif; ?>
				</div>
			</div>
		</td>
	</tr>
	<?php
}
