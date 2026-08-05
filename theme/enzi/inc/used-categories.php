<?php
/**
 * Legacy second-hand category helpers (disabled for Enzi shop).
 *
 * Gaming "used games / consoles" trees were removed. Use
 * /scripts/bootstrap-beauty-categories.php for the beauty catalogue instead.
 *
 * @package Enzi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DIAKO_USED_CATEGORIES_VERSION', 3 );

/**
 * No second-hand category tree for Enzi shop.
 *
 * @return array<string, mixed>
 */
function diako_get_used_category_definitions(): array {
	return array();
}

/**
 * Ensure a product category exists with the expected parent/name.
 *
 * @param string $name      Category name.
 * @param string $slug      Category slug.
 * @param int    $parent_id Parent term ID.
 * @return int Term ID or 0 on failure.
 */
function diako_ensure_product_category_term( string $name, string $slug, int $parent_id = 0 ): int {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return 0;
	}

	$term = get_term_by( 'slug', $slug, 'product_cat' );

	if ( $term instanceof WP_Term ) {
		$updates = array();

		if ( $term->name !== $name ) {
			$updates['name'] = $name;
		}

		if ( (int) $term->parent !== $parent_id ) {
			$updates['parent'] = $parent_id;
		}

		if ( ! empty( $updates ) ) {
			wp_update_term( (int) $term->term_id, 'product_cat', $updates );
		}

		return (int) $term->term_id;
	}

	$result = wp_insert_term(
		$name,
		'product_cat',
		array(
			'slug'   => $slug,
			'parent' => $parent_id,
		)
	);

	if ( is_wp_error( $result ) ) {
		if ( 'term_exists' === $result->get_error_code() ) {
			$term_id = (int) $result->get_error_data();

			if ( $term_id > 0 ) {
				wp_update_term(
					$term_id,
					'product_cat',
					array(
						'name'   => $name,
						'parent' => $parent_id,
					)
				);

				return $term_id;
			}
		}

		return 0;
	}

	return (int) $result['term_id'];
}

/**
 * Recursively create/update used category terms.
 *
 * @param array<string,mixed> $definition Category definition node.
 * @param int                 $parent_id  Parent term ID.
 * @return int Root or node term ID.
 */
function diako_ensure_used_category_branch( array $definition, int $parent_id = 0 ): int {
	if ( empty( $definition['slug'] ) || empty( $definition['name'] ) ) {
		return 0;
	}

	$term_id = diako_ensure_product_category_term(
		(string) $definition['name'],
		(string) $definition['slug'],
		$parent_id
	);

	if ( $term_id <= 0 ) {
		return 0;
	}

	foreach ( (array) ( $definition['children'] ?? array() ) as $child ) {
		if ( ! is_array( $child ) ) {
			continue;
		}

		diako_ensure_used_category_branch( $child, $term_id );
	}

	return $term_id;
}

/**
 * Create/update all used categories.
 *
 * @return int Root term ID or 0 on failure.
 */
function diako_ensure_used_product_categories(): int {
	$root_id = diako_ensure_used_category_branch( diako_get_used_category_definitions(), 0 );

	if ( $root_id > 0 ) {
		update_option( 'diako_used_categories_root_id', $root_id );
	}

	return $root_id;
}

/**
 * Merge legacy daste-dovom root into the existing used category.
 *
 * @return int Canonical used root term ID or 0.
 */
function diako_merge_duplicate_used_root_category(): int {
	$used_term      = get_term_by( 'slug', 'used', 'product_cat' );
	$duplicate_term = get_term_by( 'slug', 'daste-dovom', 'product_cat' );

	if ( ! $used_term instanceof WP_Term ) {
		return 0;
	}

	$used_id = (int) $used_term->term_id;

	if ( ! $duplicate_term instanceof WP_Term || $used_id === (int) $duplicate_term->term_id ) {
		update_option( 'diako_used_categories_root_id', $used_id );

		return $used_id;
	}

	$duplicate_id = (int) $duplicate_term->term_id;
	$children     = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'parent'     => $duplicate_id,
		)
	);

	if ( is_array( $children ) ) {
		foreach ( $children as $child ) {
			wp_update_term(
				(int) $child->term_id,
				'product_cat',
				array(
					'parent' => $used_id,
				)
			);
		}
	}

	foreach ( array( 'primary', 'footer' ) as $location ) {
		$menu_id = diako_get_nav_menu_id_for_location( $location );

		if ( $menu_id <= 0 ) {
			continue;
		}

		$used_menu_item_id      = diako_find_nav_menu_category_item( $menu_id, $used_id );
		$duplicate_menu_item_id = diako_find_nav_menu_category_item( $menu_id, $duplicate_id );
		$menu_items             = wp_get_nav_menu_items( $menu_id );

		if ( ! is_array( $menu_items ) ) {
			continue;
		}

		if ( $duplicate_menu_item_id > 0 && $used_menu_item_id > 0 ) {
			foreach ( $menu_items as $menu_item ) {
				if ( (int) $menu_item->menu_item_parent !== $duplicate_menu_item_id ) {
					continue;
				}

				wp_update_nav_menu_item(
					$menu_id,
					(int) $menu_item->ID,
					array(
						'menu-item-title'     => $menu_item->title,
						'menu-item-object-id' => (int) $menu_item->object_id,
						'menu-item-object'    => $menu_item->object,
						'menu-item-type'      => $menu_item->type,
						'menu-item-status'    => 'publish',
						'menu-item-parent-id' => $used_menu_item_id,
					)
				);
			}

			wp_delete_post( $duplicate_menu_item_id, true );
		} elseif ( $duplicate_menu_item_id > 0 && $used_menu_item_id <= 0 ) {
			wp_update_nav_menu_item(
				$menu_id,
				$duplicate_menu_item_id,
				array(
					'menu-item-title'     => $used_term->name,
					'menu-item-object-id' => $used_id,
					'menu-item-object'    => 'product_cat',
					'menu-item-type'      => 'taxonomy',
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => 0,
				)
			);
		}
	}

	wp_delete_term( $duplicate_id, 'product_cat' );
	update_option( 'diako_used_categories_root_id', $used_id );

	return $used_id;
}

/**
 * Menu ID assigned to a theme location.
 *
 * @param string $location Theme location slug.
 * @return int
 */
function diako_get_nav_menu_id_for_location( string $location ): int {
	$locations = get_nav_menu_locations();

	if ( empty( $locations[ $location ] ) ) {
		return 0;
	}

	return (int) $locations[ $location ];
}

/**
 * Find an existing nav menu item for a product category.
 *
 * @param int $menu_id Menu ID.
 * @param int $term_id Category term ID.
 * @return int Menu item ID or 0.
 */
function diako_find_nav_menu_category_item( int $menu_id, int $term_id ): int {
	$items = wp_get_nav_menu_items( $menu_id );

	if ( ! is_array( $items ) ) {
		return 0;
	}

	foreach ( $items as $item ) {
		if ( 'taxonomy' === $item->type && 'product_cat' === $item->object && (int) $item->object_id === $term_id ) {
			return (int) $item->ID;
		}
	}

	return 0;
}

/**
 * Ensure a product category exists in a nav menu.
 *
 * @param int $menu_id             Menu ID.
 * @param int $term_id             Category term ID.
 * @param int $parent_menu_item_id Parent menu item ID.
 * @return int Menu item ID or 0.
 */
function diako_ensure_nav_menu_category_item( int $menu_id, int $term_id, int $parent_menu_item_id = 0 ): int {
	$term = get_term( $term_id, 'product_cat' );

	if ( ! $term instanceof WP_Term || is_wp_error( $term ) ) {
		return 0;
	}

	$item_id = diako_find_nav_menu_category_item( $menu_id, $term_id );

	$args = array(
		'menu-item-title'     => $term->name,
		'menu-item-object-id' => $term_id,
		'menu-item-object'    => 'product_cat',
		'menu-item-type'      => 'taxonomy',
		'menu-item-status'    => 'publish',
		'menu-item-parent-id' => $parent_menu_item_id,
	);

	$result = wp_update_nav_menu_item( $menu_id, $item_id, $args );

	return is_wp_error( $result ) ? 0 : (int) $result;
}

/**
 * Recursively sync category branches into a nav menu.
 *
 * @param int                 $menu_id             Menu ID.
 * @param array<string,mixed> $definition          Category definition node.
 * @param int                 $parent_menu_item_id Parent menu item ID.
 * @return int Menu item ID or 0.
 */
function diako_sync_used_category_menu_branch( int $menu_id, array $definition, int $parent_menu_item_id = 0 ): int {
	$term = get_term_by( 'slug', (string) $definition['slug'], 'product_cat' );

	if ( ! $term instanceof WP_Term ) {
		return 0;
	}

	$menu_item_id = diako_ensure_nav_menu_category_item( $menu_id, (int) $term->term_id, $parent_menu_item_id );

	if ( $menu_item_id <= 0 ) {
		return 0;
	}

	foreach ( (array) ( $definition['children'] ?? array() ) as $child ) {
		if ( ! is_array( $child ) ) {
			continue;
		}

		diako_sync_used_category_menu_branch( $menu_id, $child, $menu_item_id );
	}

	return $menu_item_id;
}

/**
 * Sync used categories into a theme menu location.
 *
 * @param string $location Theme location slug.
 * @return bool
 */
function diako_sync_used_categories_to_menu( string $location = 'primary' ): bool {
	$menu_id = diako_get_nav_menu_id_for_location( $location );

	if ( $menu_id <= 0 ) {
		return false;
	}

	diako_ensure_used_product_categories();
	diako_sync_used_category_menu_branch( $menu_id, diako_get_used_category_definitions(), 0 );

	return true;
}

/**
 * Bootstrap used categories and add them to primary/footer menus.
 *
 * @return void
 */
function diako_bootstrap_used_categories(): void {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return;
	}

	$current_version = (int) get_option( 'diako_used_categories_version', 0 );

	if ( $current_version >= DIAKO_USED_CATEGORIES_VERSION ) {
		return;
	}

	if ( $current_version < 2 ) {
		diako_merge_duplicate_used_root_category();
	}

	diako_ensure_used_product_categories();

	foreach ( array( 'primary', 'footer' ) as $location ) {
		diako_sync_used_categories_to_menu( $location );
	}

	update_option( 'diako_used_categories_version', DIAKO_USED_CATEGORIES_VERSION );
}

// Enzi starts empty — do not auto-create Diako used-product category trees.
// Run diako_bootstrap_used_categories() manually via WP-CLI if needed.
