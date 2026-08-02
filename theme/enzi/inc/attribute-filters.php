<?php
/**
 * Per-attribute archive filter visibility (WooCommerce attribute admin).
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DIAKO_ATTRIBUTE_ARCHIVE_FILTERS_OPTION', 'diako_attribute_archive_filters' );

/**
 * Default attribute slugs enabled as archive filters (initial migration).
 *
 * @return array<int, string>
 */
function diako_get_default_shop_filter_attribute_slugs() {
	$slugs = array(
		'skin-type',
		'skin-concern',
		'product-form',
		'spf',
		'volume',
		'brand',
		'manufacturer',
		'product-type',
		'color',
	);

	return apply_filters( 'diako_default_shop_filter_attribute_slugs', $slugs );
}

/**
 * Map of attribute slug => enabled for archive filters.
 *
 * @return array<string, bool>
 */
function diako_get_attribute_archive_filter_map() {
	$map = get_option( DIAKO_ATTRIBUTE_ARCHIVE_FILTERS_OPTION, null );

	if ( null === $map ) {
		$map = array_fill_keys( diako_get_default_shop_filter_attribute_slugs(), true );
		update_option( DIAKO_ATTRIBUTE_ARCHIVE_FILTERS_OPTION, $map, false );
	}

	return is_array( $map ) ? $map : array();
}

/**
 * Resolve a global attribute slug (without pa_ prefix) from its ID.
 *
 * @param int $attribute_id Attribute ID.
 * @return string
 */
function diako_get_attribute_slug_by_id( int $attribute_id ): string {
	if ( $attribute_id <= 0 ) {
		return '';
	}

	global $wpdb;

	$slug = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT attribute_name FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_id = %d",
			$attribute_id
		)
	);

	return $slug ? sanitize_title( (string) $slug ) : '';
}

/**
 * Whether an attribute is enabled for shop archive filters.
 *
 * @param int|string $attribute Attribute ID or slug (without pa_ prefix).
 * @return bool
 */
function diako_is_attribute_archive_filter_enabled( $attribute ) {
	$slug = '';

	if ( is_numeric( $attribute ) ) {
		$slug = diako_get_attribute_slug_by_id( (int) $attribute );
	} else {
		$slug = sanitize_title( (string) $attribute );
	}

	if ( '' === $slug ) {
		return false;
	}

	$map = diako_get_attribute_archive_filter_map();

	return ! empty( $map[ $slug ] );
}

/**
 * Persist archive-filter visibility for an attribute slug.
 *
 * @param string $slug    Attribute slug.
 * @param bool   $enabled Whether the filter is enabled.
 * @return void
 */
function diako_set_attribute_archive_filter_enabled( string $slug, bool $enabled ) {
	$slug = sanitize_title( $slug );

	if ( '' === $slug ) {
		return;
	}

	$map = diako_get_attribute_archive_filter_map();

	if ( $enabled ) {
		$map[ $slug ] = true;
	} else {
		unset( $map[ $slug ] );
	}

	update_option( DIAKO_ATTRIBUTE_ARCHIVE_FILTERS_OPTION, $map, false );
}

/**
 * Ordered slugs of attributes enabled for archive filters.
 *
 * @return array<int, string>
 */
function diako_get_archive_filter_attribute_slugs() {
	$map   = diako_get_attribute_archive_filter_map();
	$slugs = array_keys( array_filter( $map ) );

	if ( empty( $slugs ) ) {
		return array();
	}

	$ordered = array();

	if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
		foreach ( wc_get_attribute_taxonomies() as $attribute ) {
			$name = (string) $attribute->attribute_name;

			if ( in_array( $name, $slugs, true ) ) {
				$ordered[] = $name;
			}
		}
	}

	foreach ( $slugs as $slug ) {
		if ( ! in_array( $slug, $ordered, true ) ) {
			$ordered[] = $slug;
		}
	}

	return $ordered;
}

/**
 * Render archive filter checkbox on attribute add/edit screens.
 *
 * @return void
 */
function diako_render_attribute_archive_filter_field() {
	$attribute_id = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$checked      = $attribute_id ? diako_is_attribute_archive_filter_enabled( $attribute_id ) : false;
	?>
	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="diako_show_in_archive_filters"><?php esc_html_e( 'فیلتر آرشیو', 'diako' ); ?></label>
		</th>
		<td>
			<label for="diako_show_in_archive_filters">
				<input
					name="diako_show_in_archive_filters"
					id="diako_show_in_archive_filters"
					type="checkbox"
					value="1"
					<?php checked( $checked ); ?>
				/>
				<?php esc_html_e( 'نمایش به‌عنوان فیلتر در صفحات فروشگاه و دسته‌بندی', 'diako' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'اگر فعال باشد، این ویژگی در نوار فیلتر کناری آرشیو محصولات (فروشگاه و دسته‌بندی‌های مرتبط) نمایش داده می‌شود.', 'diako' ); ?>
			</p>
		</td>
	</tr>
	<?php
}
add_action( 'woocommerce_after_add_attribute_fields', 'diako_render_attribute_archive_filter_field' );
add_action( 'woocommerce_after_edit_attribute_fields', 'diako_render_attribute_archive_filter_field' );

/**
 * Resolve attribute slug from save payload or database.
 *
 * @param int                  $attribute_id Attribute ID.
 * @param array<string, mixed> $data         Attribute data from WooCommerce.
 * @return string
 */
function diako_get_attribute_slug_from_save( int $attribute_id, array $data = array() ): string {
	if ( ! empty( $data['attribute_name'] ) ) {
		return sanitize_title( (string) $data['attribute_name'] );
	}

	return diako_get_attribute_slug_by_id( $attribute_id );
}

/**
 * Whether the current request is saving the WooCommerce attribute admin form.
 *
 * @param int $attribute_id Attribute ID for edit saves (0 for add).
 * @return bool
 */
function diako_is_attribute_admin_form_save( int $attribute_id = 0 ): bool {
	if ( ! is_admin() || ! isset( $_POST['attribute_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return false;
	}

	if ( $attribute_id > 0 ) {
		return (bool) wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
			'woocommerce-save-attribute_' . $attribute_id
		);
	}

	return (bool) wp_verify_nonce(
		sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'woocommerce-add-new-attribute'
	);
}

/**
 * Save archive filter checkbox when an attribute is created.
 *
 * @param int                  $attribute_id Attribute ID.
 * @param array<string, mixed> $data         Attribute data.
 * @return void
 */
function diako_save_attribute_archive_filter_on_add( int $attribute_id, array $data ) {
	if ( ! current_user_can( 'manage_product_terms' ) || ! diako_is_attribute_admin_form_save() ) {
		return;
	}

	$slug    = diako_get_attribute_slug_from_save( $attribute_id, $data );
	$enabled = ! empty( $_POST['diako_show_in_archive_filters'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( $slug ) {
		diako_set_attribute_archive_filter_enabled( $slug, $enabled );
	}
}
add_action( 'woocommerce_attribute_added', 'diako_save_attribute_archive_filter_on_add', 10, 2 );

/**
 * Save archive filter checkbox when an attribute is updated.
 *
 * @param int                  $attribute_id Attribute ID.
 * @param array<string, mixed> $data         Attribute data.
 * @param string|null          $old_slug     Previous attribute slug.
 * @return void
 */
function diako_save_attribute_archive_filter_on_update( int $attribute_id, array $data, $old_slug = null ) {
	if ( ! current_user_can( 'manage_product_terms' ) || ! diako_is_attribute_admin_form_save( $attribute_id ) ) {
		return;
	}

	$new_slug = diako_get_attribute_slug_from_save( $attribute_id, $data );
	$enabled  = ! empty( $_POST['diako_show_in_archive_filters'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$old_slug = is_string( $old_slug ) ? $old_slug : '';

	if ( $old_slug && $old_slug !== $new_slug ) {
		$map = diako_get_attribute_archive_filter_map();
		unset( $map[ $old_slug ] );
		update_option( DIAKO_ATTRIBUTE_ARCHIVE_FILTERS_OPTION, $map, false );
	}

	if ( $new_slug ) {
		diako_set_attribute_archive_filter_enabled( $new_slug, $enabled );
	}
}
add_action( 'woocommerce_attribute_updated', 'diako_save_attribute_archive_filter_on_update', 10, 3 );

/**
 * Remove slug from archive filter map when attribute is deleted.
 *
 * @param int    $attribute_id Attribute ID.
 * @param string $name         Attribute slug.
 * @return void
 */
function diako_delete_attribute_archive_filter_on_remove( int $attribute_id, string $name ) {
	unset( $attribute_id );

	$map = diako_get_attribute_archive_filter_map();
	unset( $map[ sanitize_title( $name ) ] );
	update_option( DIAKO_ATTRIBUTE_ARCHIVE_FILTERS_OPTION, $map, false );
}
add_action( 'woocommerce_attribute_deleted', 'diako_delete_attribute_archive_filter_on_remove', 10, 2 );
