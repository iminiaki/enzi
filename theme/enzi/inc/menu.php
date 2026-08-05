<?php defined( 'ABSPATH' ) || exit; ?>
<?php
/**
 * Fallback menu when no menu is assigned.
 *
 * @package Diako
 */

/**
 * Links always shown in the footer quick access section.
 *
 * @return array<string, string> URL => label.
 */
function diako_get_footer_quick_access_links() {
	$links = array();

	if ( function_exists( 'diako_get_mag_url' ) ) {
		$links[ diako_get_mag_url() ] = sprintf(
			/* translators: %s: store brand name */
			__( 'مجله %s', 'diako' ),
			diako_get_brand_name()
		);
	}

	if ( function_exists( 'diako_get_track_order_url' ) ) {
		$links[ diako_get_track_order_url() ] = __( 'پیگیری سفارش', 'diako' );
	}

	if ( function_exists( 'diako_get_terms_url' ) ) {
		$links[ diako_get_terms_url() ] = __( 'شرایط و قوانین', 'diako' );
	}

	if ( function_exists( 'diako_get_about_url' ) ) {
		$links[ diako_get_about_url() ] = __( 'درباره ما', 'diako' );
	}

	if ( function_exists( 'diako_get_contact_url' ) ) {
		$links[ diako_get_contact_url() ] = __( 'تماس با ما', 'diako' );
	}

	return $links;
}

/**
 * Create a synthetic footer nav menu item.
 *
 * @param string $url   Item URL.
 * @param string $title Item title.
 * @param int    $id    Item ID.
 * @return stdClass
 */
function diako_create_footer_menu_item( $url, $title, $id ) {
	$item                            = new stdClass();
	$item->ID                        = $id;
	$item->db_id                     = $id;
	$item->title                     = $title;
	$item->url                       = $url;
	$item->menu_item_parent          = 0;
	$item->menu_order                = $id;
	$item->type                      = 'custom';
	$item->object                    = 'custom';
	$item->object_id                 = $id;
	$item->classes                   = array( 'menu-item', 'menu-item-type-custom', 'menu-item-object-custom' );
	$item->xfn                       = '';
	$item->target                    = '';
	$item->attr_title                = '';
	$item->description               = '';
	$item->current                   = false;
	$item->current_item_ancestor     = false;
	$item->current_item_parent       = false;

	return $item;
}

function diako_fallback_menu() {
	$items = array(
		home_url( '/' )      => __( 'خانه', 'diako' ),
		home_url( '/shop/' ) => __( 'فروشگاه', 'diako' ),
		home_url( '/discount/' ) => __( 'تخفیف‌ها', 'diako' ),
	);

	if ( function_exists( 'diako_get_track_order_url' ) ) {
		$items[ diako_get_track_order_url() ] = __( 'پیگیری سفارش', 'diako' );
	}

	echo '<ul class="menu">';
	foreach ( $items as $url => $label ) {
		printf(
			'<li><a class="nav-link" href="%s">%s</a></li>',
			esc_url( $url ),
			esc_html( $label )
		);
	}
	echo '</ul>';
}

function diako_footer_fallback_menu() {
	$items = array(
		home_url( '/' )      => __( 'خانه', 'diako' ),
		home_url( '/shop/' ) => __( 'فروشگاه', 'diako' ),
		home_url( '/discount/' ) => __( 'تخفیف‌ها', 'diako' ),
	);

	$items = array_merge( $items, diako_get_footer_quick_access_links() );

	echo '<ul class="menu">';
	foreach ( $items as $url => $label ) {
		printf(
			'<li><a class="nav-link" href="%s">%s</a></li>',
			esc_url( $url ),
			esc_html( $label )
		);
	}
	echo '</ul>';
}

/**
 * Remove product category links from footer quick access menus.
 *
 * @param WP_Post[] $items Menu items.
 * @param stdClass  $args  Menu args.
 * @return WP_Post[]
 */
function diako_filter_footer_quick_access_menu_items( $items, $args ) {
	$is_footer_menu = ! empty( $args->theme_location ) && 'footer' === $args->theme_location;
	$strip_categories = ! empty( $args->diako_strip_product_categories );

	if ( ! $is_footer_menu && ! $strip_categories ) {
		return $items;
	}

	$items = array_values(
		array_filter(
			$items,
			function ( $item ) {
				return 'product_cat' !== $item->object;
			}
		)
	);

	if ( ! $is_footer_menu ) {
		return $items;
	}

	$existing_urls = array_map(
		function ( $item ) {
			return untrailingslashit( (string) $item->url );
		},
		$items
	);

	$menu_item_id = 900001;
	foreach ( diako_get_footer_quick_access_links() as $url => $label ) {
		if ( in_array( untrailingslashit( $url ), $existing_urls, true ) ) {
			continue;
		}

		$items[] = diako_create_footer_menu_item( $url, $label, $menu_item_id );
		++$menu_item_id;
	}

	return $items;
}

add_filter( 'wp_nav_menu_objects', 'diako_filter_footer_quick_access_menu_items', 10, 2 );

/**
 * Submenu indicator markup.
 *
 * @param string $class  Indicator class.
 * @param bool   $nested Whether this is a nested submenu indicator.
 * @return string
 */
function diako_get_submenu_indicator_markup( $class = 'diako-submenu-indicator', $nested = false ) {
	if ( $nested ) {
		return sprintf(
			'<span class="%1$s" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></span>',
			esc_attr( $class )
		);
	}

	return sprintf(
		'<span class="%1$s" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></span>',
		esc_attr( $class )
	);
}

/**
 * Append submenu indicator to parent menu items.
 *
 * @param string   $title Menu item title.
 * @param WP_Post  $item  Menu item.
 * @param stdClass $args  Menu args.
 * @param int      $depth Menu depth.
 * @return string
 */
function diako_append_submenu_indicator( $title, $item, $args, $depth ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $title;
	}

	if ( ! in_array( 'menu-item-has-children', $item->classes, true ) ) {
		return $title;
	}

	$indicator_class = $depth > 0 ? 'diako-submenu-indicator diako-submenu-indicator--nested' : 'diako-submenu-indicator';

	return '<span class="diako-menu-item-label">' . $title . '</span>' . diako_get_submenu_indicator_markup( $indicator_class, $depth > 0 );
}

add_filter( 'nav_menu_item_title', 'diako_append_submenu_indicator', 10, 4 );

function diako_is_current_menu_url( $url ) {
	return trailingslashit( $url ) === trailingslashit( diako_current_url() );
}

function diako_current_url() {
	global $wp;
	return home_url( add_query_arg( array(), $wp->request ) );
}

add_filter(
	'nav_menu_css_class',
	function ( $classes, $item, $args ) {
		if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
			return $classes;
		}

		if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current-page-ancestor', $classes, true ) ) {
			$classes[] = 'is-active';
		}

		return $classes;
	},
	10,
	3
);

add_filter(
	'nav_menu_link_attributes',
	function ( $atts, $item, $args ) {
		if ( empty( $args->theme_location ) || ! in_array( $args->theme_location, array( 'primary', 'footer' ), true ) ) {
			return $atts;
		}

		$classes   = isset( $atts['class'] ) ? explode( ' ', $atts['class'] ) : array();
		$classes[] = 'primary' === $args->theme_location ? 'nav-link' : 'text-sm text-muted-foreground transition-colors hover:text-foreground';

		if ( in_array( 'current-menu-item', $item->classes, true ) || in_array( 'current-menu-ancestor', $item->classes, true ) ) {
			$classes[] = 'nav-link-active';
		}

		if ( 'primary' === $args->theme_location && in_array( 'menu-item-has-children', $item->classes, true ) ) {
			$atts['aria-haspopup'] = 'true';
			$atts['aria-expanded'] = 'false';
		}

		$atts['class'] = implode( ' ', array_unique( array_filter( $classes ) ) );
		return $atts;
	},
	10,
	3
);
