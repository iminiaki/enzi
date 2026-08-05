<?php
/**
 * Color product attribute (pa_color) with term color picker + storefront swatches.
 *
 * @package Diako
 */

defined( 'ABSPATH' ) || exit;

const DIAKO_COLOR_ATTRIBUTE_SLUG = 'color';
const DIAKO_COLOR_ATTRIBUTE_TAXONOMY = 'pa_color';
const DIAKO_COLOR_TERM_META_KEY = 'diako_attribute_color';
const DIAKO_COLOR_ATTRIBUTE_SETTINGS_OPTION = 'diako_color_attribute_settings';

/**
 * Default settings for the global color attribute.
 *
 * @return array<string, string>
 */
function diako_get_default_color_attribute_settings(): array {
	return array(
		'filter_display' => 'both',
	);
}

/**
 * Saved settings for the global color attribute.
 *
 * @return array<string, string>
 */
function diako_get_color_attribute_settings(): array {
	$saved = get_option( DIAKO_COLOR_ATTRIBUTE_SETTINGS_OPTION, array() );

	return wp_parse_args(
		is_array( $saved ) ? $saved : array(),
		diako_get_default_color_attribute_settings()
	);
}

/**
 * Sanitize sidebar filter display mode for pa_color.
 *
 * @param string $mode Raw mode.
 * @return string color|name|both
 */
function diako_sanitize_color_filter_display_mode( string $mode ): string {
	$mode = sanitize_key( $mode );

	if ( ! in_array( $mode, array( 'color', 'name', 'both' ), true ) ) {
		return diako_get_default_color_attribute_settings()['filter_display'];
	}

	return $mode;
}

/**
 * Migrate legacy theme-settings value into the color attribute settings option.
 *
 * @return void
 */
function diako_maybe_migrate_color_filter_display_setting(): void {
	if ( get_option( 'diako_color_filter_display_migrated' ) ) {
		return;
	}

	$theme_settings = get_option( DIAKO_THEME_SETTINGS_OPTION, array() );

	if ( is_array( $theme_settings ) && ! empty( $theme_settings['shop']['color_filter_display'] ) ) {
		$mode = diako_sanitize_color_filter_display_mode( (string) $theme_settings['shop']['color_filter_display'] );

		update_option(
			DIAKO_COLOR_ATTRIBUTE_SETTINGS_OPTION,
			array(
				'filter_display' => $mode,
			),
			false
		);
	}

	update_option( 'diako_color_filter_display_migrated', 1, false );
}
add_action( 'init', 'diako_maybe_migrate_color_filter_display_setting', 5 );

/**
 * How color attribute terms render in the shop sidebar filter.
 *
 * @return string color|name|both
 */
function diako_get_color_filter_display_mode(): string {
	diako_maybe_migrate_color_filter_display_setting();

	return diako_sanitize_color_filter_display_mode(
		(string) ( diako_get_color_attribute_settings()['filter_display'] ?? 'both' )
	);
}

/**
 * Whether the current WooCommerce attribute admin screen is editing pa_color.
 *
 * @return bool
 */
function diako_is_color_attribute_admin_screen(): bool {
	if ( ! is_admin() || ! isset( $_GET['edit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return false;
	}

	$attribute_id = absint( wp_unslash( $_GET['edit'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( $attribute_id <= 0 ) {
		return false;
	}

	if ( function_exists( 'diako_get_attribute_slug_by_id' ) ) {
		return DIAKO_COLOR_ATTRIBUTE_SLUG === diako_get_attribute_slug_by_id( $attribute_id );
	}

	if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return false;
	}

	foreach ( wc_get_attribute_taxonomies() as $attribute ) {
		if ( (int) $attribute->attribute_id === $attribute_id ) {
			return DIAKO_COLOR_ATTRIBUTE_SLUG === (string) $attribute->attribute_name;
		}
	}

	return false;
}

/**
 * Render sidebar filter display options on the color attribute edit screen.
 *
 * @return void
 */
function diako_render_color_attribute_filter_display_field(): void {
	if ( ! diako_is_color_attribute_admin_screen() ) {
		return;
	}

	$current = diako_get_color_filter_display_mode();
	?>
	<tr class="form-field">
		<th scope="row" valign="top">
			<label><?php esc_html_e( 'نمایش در فیلتر فروشگاه', 'diako' ); ?></label>
		</th>
		<td>
			<fieldset>
				<legend class="screen-reader-text"><?php esc_html_e( 'نمایش در فیلتر فروشگاه', 'diako' ); ?></legend>
				<label>
					<input type="radio" name="diako_color_filter_display" value="both" <?php checked( $current, 'both' ); ?> />
					<?php esc_html_e( 'رنگ و نام', 'diako' ); ?>
				</label>
				<br />
				<label>
					<input type="radio" name="diako_color_filter_display" value="color" <?php checked( $current, 'color' ); ?> />
					<?php esc_html_e( 'فقط دایره رنگ', 'diako' ); ?>
				</label>
				<br />
				<label>
					<input type="radio" name="diako_color_filter_display" value="name" <?php checked( $current, 'name' ); ?> />
					<?php esc_html_e( 'فقط نام رنگ', 'diako' ); ?>
				</label>
				<p class="description">
					<?php esc_html_e( 'نحوه نمایش گزینه‌های رنگ در فیلتر کناری صفحات فروشگاه و دسته‌بندی.', 'diako' ); ?>
				</p>
			</fieldset>
		</td>
	</tr>
	<?php
}
add_action( 'woocommerce_after_edit_attribute_fields', 'diako_render_color_attribute_filter_display_field', 15 );

/**
 * Persist sidebar filter display mode when the color attribute is saved.
 *
 * @param int                  $attribute_id Attribute ID.
 * @param array<string, mixed> $data         Attribute data.
 * @return void
 */
function diako_save_color_attribute_filter_display_setting( int $attribute_id, array $data ): void {
	if ( ! current_user_can( 'manage_product_terms' ) ) {
		return;
	}

	if ( ! function_exists( 'diako_is_attribute_admin_form_save' ) || ! diako_is_attribute_admin_form_save( $attribute_id ) ) {
		return;
	}

	$slug = function_exists( 'diako_get_attribute_slug_from_save' )
		? diako_get_attribute_slug_from_save( $attribute_id, $data )
		: '';

	if ( DIAKO_COLOR_ATTRIBUTE_SLUG !== $slug || ! isset( $_POST['diako_color_filter_display'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	update_option(
		DIAKO_COLOR_ATTRIBUTE_SETTINGS_OPTION,
		array(
			'filter_display' => diako_sanitize_color_filter_display_mode(
				sanitize_text_field( wp_unslash( (string) $_POST['diako_color_filter_display'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			),
		),
		false
	);
}
add_action( 'woocommerce_attribute_updated', 'diako_save_color_attribute_filter_display_setting', 10, 2 );

/**
 * Whether a taxonomy/attribute key is the theme color attribute.
 *
 * @param string $attribute Attribute name (pa_color or color).
 * @return bool
 */
function diako_is_color_attribute( string $attribute ): bool {
	$attribute = sanitize_title( $attribute );

	return in_array( $attribute, array( DIAKO_COLOR_ATTRIBUTE_TAXONOMY, DIAKO_COLOR_ATTRIBUTE_SLUG ), true );
}

/**
 * Sanitize a hex color value.
 *
 * @param string $color Raw color.
 * @return string Normalized #RRGGBB or empty string.
 */
function diako_sanitize_attribute_color( string $color ): string {
	$color = trim( $color );

	if ( '' === $color ) {
		return '';
	}

	if ( function_exists( 'sanitize_hex_color' ) ) {
		$sanitized = sanitize_hex_color( $color );

		return is_string( $sanitized ) ? $sanitized : '';
	}

	if ( preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color ) ) {
		if ( 4 === strlen( $color ) ) {
			return sprintf(
				'#%1$s%1$s%2$s%2$s%3$s%3$s',
				$color[1],
				$color[2],
				$color[3]
			);
		}

		return strtoupper( $color );
	}

	return '';
}

/**
 * Default hex colors for common lipstick/swatch slugs.
 *
 * @return array<string, string>
 */
function diako_get_default_color_swatch_map(): array {
	return array(
		'nude'  => '#E8D5C4',
		'rose'  => '#D46A8A',
		'berry' => '#6B2D5B',
		'red'   => '#C62828',
		'pink'  => '#EC407A',
		'black' => '#1A1A1A',
		'white' => '#F5F5F5',
		'beige' => '#E8D5C4',
		'brown' => '#6D4C41',
		'coral' => '#FF7F50',
		'plum'  => '#7B1FA2',
		'wine'  => '#722F37',
	);
}

/**
 * Resolve a term color from meta, with slug-based fallback.
 *
 * @param WP_Term|int|string $term Term object, ID, or slug.
 * @param string             $taxonomy Taxonomy. Default pa_color.
 * @return string Hex color or empty.
 */
function diako_get_attribute_color( $term, string $taxonomy = DIAKO_COLOR_ATTRIBUTE_TAXONOMY ): string {
	if ( is_numeric( $term ) ) {
		$term = get_term( (int) $term, $taxonomy );
	} elseif ( is_string( $term ) ) {
		$term = get_term_by( 'slug', $term, $taxonomy );
	}

	if ( ! $term instanceof WP_Term ) {
		return '';
	}

	$stored = diako_sanitize_attribute_color( (string) get_term_meta( $term->term_id, DIAKO_COLOR_TERM_META_KEY, true ) );

	if ( '' !== $stored ) {
		return $stored;
	}

	$map = diako_get_default_color_swatch_map();
	$slug = sanitize_title( $term->slug );

	return isset( $map[ $slug ] ) ? $map[ $slug ] : '';
}

/**
 * Whether a hex color is visually light (for check/border contrast).
 *
 * @param string $hex Hex color.
 * @return bool
 */
function diako_is_light_attribute_color( string $hex ): bool {
	$hex = ltrim( diako_sanitize_attribute_color( $hex ), '#' );

	if ( 6 !== strlen( $hex ) ) {
		return false;
	}

	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );

	// Relative luminance threshold.
	return ( ( ( $r * 299 ) + ( $g * 587 ) + ( $b * 114 ) ) / 1000 ) >= 180;
}

/**
 * Ensure the global WooCommerce color attribute exists.
 *
 * @return void
 */
function diako_ensure_color_attribute(): void {
	if ( ! function_exists( 'wc_create_attribute' ) || ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return;
	}

	foreach ( wc_get_attribute_taxonomies() as $attribute ) {
		if ( DIAKO_COLOR_ATTRIBUTE_SLUG === $attribute->attribute_name ) {
			return;
		}
	}

	$result = wc_create_attribute(
		array(
			'name'         => __( 'رنگ', 'diako' ),
			'slug'         => DIAKO_COLOR_ATTRIBUTE_SLUG,
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		)
	);

	if ( is_wp_error( $result ) ) {
		return;
	}

	delete_transient( 'wc_attribute_taxonomies' );

	if ( ! taxonomy_exists( DIAKO_COLOR_ATTRIBUTE_TAXONOMY ) && function_exists( 'wc_get_attribute_taxonomy_name' ) ) {
		register_taxonomy(
			DIAKO_COLOR_ATTRIBUTE_TAXONOMY,
			apply_filters( 'woocommerce_taxonomy_objects_' . DIAKO_COLOR_ATTRIBUTE_TAXONOMY, array( 'product' ) ),
			apply_filters(
				'woocommerce_taxonomy_args_' . DIAKO_COLOR_ATTRIBUTE_TAXONOMY,
				array(
					'labels'       => array(
						'name' => __( 'رنگ', 'diako' ),
					),
					'hierarchical' => false,
					'show_ui'      => false,
					'query_var'    => true,
					'rewrite'      => false,
				)
			)
		);
	}
}
add_action( 'woocommerce_init', 'diako_ensure_color_attribute', 5 );

/**
 * Seed default colors onto existing color terms that have no meta yet.
 *
 * @return void
 */
function diako_seed_color_attribute_term_colors(): void {
	if ( ! taxonomy_exists( DIAKO_COLOR_ATTRIBUTE_TAXONOMY ) ) {
		return;
	}

	$seeded = (string) get_option( 'diako_color_swatches_seeded', '' );

	if ( DIAKO_VERSION === $seeded ) {
		return;
	}

	$map   = diako_get_default_color_swatch_map();
	$terms = get_terms(
		array(
			'taxonomy'   => DIAKO_COLOR_ATTRIBUTE_TAXONOMY,
			'hide_empty' => false,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		update_option( 'diako_color_swatches_seeded', DIAKO_VERSION, false );
		return;
	}

	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}

		$existing = diako_sanitize_attribute_color( (string) get_term_meta( $term->term_id, DIAKO_COLOR_TERM_META_KEY, true ) );

		if ( '' !== $existing ) {
			continue;
		}

		$slug = sanitize_title( $term->slug );

		if ( isset( $map[ $slug ] ) ) {
			update_term_meta( $term->term_id, DIAKO_COLOR_TERM_META_KEY, $map[ $slug ] );
		}
	}

	update_option( 'diako_color_swatches_seeded', DIAKO_VERSION, false );
}
add_action( 'woocommerce_init', 'diako_seed_color_attribute_term_colors', 20 );

/**
 * Color picker field on "Add new color" form.
 *
 * @return void
 */
function diako_color_attribute_add_form_field(): void {
	?>
	<div class="form-field term-diako-attribute-color-wrap">
		<label for="diako_attribute_color"><?php esc_html_e( 'رنگ نمونه', 'diako' ); ?></label>
		<input
			type="text"
			name="diako_attribute_color"
			id="diako_attribute_color"
			value=""
			class="diako-attribute-color-field"
			data-default-color="#CCCCCC"
		/>
		<p class="description"><?php esc_html_e( 'این رنگ به‌جای نام در صفحه محصول، کارت محصول و مودال انتخاب نمایش داده می‌شود.', 'diako' ); ?></p>
	</div>
	<?php
}
add_action( 'pa_color_add_form_fields', 'diako_color_attribute_add_form_field' );

/**
 * Color picker field on "Edit color" form.
 *
 * @param WP_Term $term Term.
 * @return void
 */
function diako_color_attribute_edit_form_field( $term ): void {
	if ( ! $term instanceof WP_Term ) {
		return;
	}

	$color = diako_get_attribute_color( $term );
	?>
	<tr class="form-field term-diako-attribute-color-wrap">
		<th scope="row">
			<label for="diako_attribute_color"><?php esc_html_e( 'رنگ نمونه', 'diako' ); ?></label>
		</th>
		<td>
			<input
				type="text"
				name="diako_attribute_color"
				id="diako_attribute_color"
				value="<?php echo esc_attr( $color ); ?>"
				class="diako-attribute-color-field"
				data-default-color="<?php echo esc_attr( $color ? $color : '#CCCCCC' ); ?>"
			/>
			<p class="description"><?php esc_html_e( 'این رنگ به‌جای نام در صفحه محصول، کارت محصول و مودال انتخاب نمایش داده می‌شود.', 'diako' ); ?></p>
		</td>
	</tr>
	<?php
}
add_action( 'pa_color_edit_form_fields', 'diako_color_attribute_edit_form_field' );

/**
 * Save color term meta.
 *
 * @param int $term_id Term ID.
 * @return void
 */
function diako_save_color_attribute_term_meta( int $term_id ): void {
	if ( ! isset( $_POST['diako_attribute_color'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	$color = diako_sanitize_attribute_color( wp_unslash( (string) $_POST['diako_attribute_color'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	if ( '' === $color ) {
		delete_term_meta( $term_id, DIAKO_COLOR_TERM_META_KEY );
		return;
	}

	update_term_meta( $term_id, DIAKO_COLOR_TERM_META_KEY, $color );
}
add_action( 'created_pa_color', 'diako_save_color_attribute_term_meta' );
add_action( 'edited_pa_color', 'diako_save_color_attribute_term_meta' );

/**
 * Add color column to attribute terms list.
 *
 * @param array<string, string> $columns Columns.
 * @return array<string, string>
 */
function diako_color_attribute_columns( array $columns ): array {
	$new = array();

	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;

		if ( 'name' === $key ) {
			$new['diako_attribute_color'] = __( 'رنگ', 'diako' );
		}
	}

	return $new;
}
add_filter( 'manage_edit-pa_color_columns', 'diako_color_attribute_columns' );

/**
 * Render color column content.
 *
 * @param string $content Column content.
 * @param string $column  Column key.
 * @param int    $term_id Term ID.
 * @return string
 */
function diako_color_attribute_column_content( string $content, string $column, int $term_id ): string {
	if ( 'diako_attribute_color' !== $column ) {
		return $content;
	}

	$color = diako_get_attribute_color( $term_id );

	if ( '' === $color ) {
		return '&mdash;';
	}

	return sprintf(
		'<span class="diako-admin-color-swatch" style="background-color:%1$s" title="%1$s"></span><code>%1$s</code>',
		esc_attr( $color )
	);
}
add_filter( 'manage_pa_color_custom_column', 'diako_color_attribute_column_content', 10, 3 );

/**
 * Enqueue wp-color-picker on color attribute term screens.
 *
 * @param string $hook_suffix Current admin page.
 * @return void
 */
function diako_enqueue_color_attribute_admin_assets( string $hook_suffix ): void {
	if ( ! in_array( $hook_suffix, array( 'edit-tags.php', 'term.php' ), true ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || DIAKO_COLOR_ATTRIBUTE_TAXONOMY !== $screen->taxonomy ) {
		return;
	}

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );

	$css = '.diako-admin-color-swatch{display:inline-block;width:22px;height:22px;margin-inline-end:8px;border-radius:999px;border:1px solid rgba(0,0,0,.15);vertical-align:middle;box-shadow:inset 0 0 0 1px rgba(255,255,255,.35)}';
	wp_add_inline_style( 'wp-color-picker', $css );

	$js = <<<'JS'
(function ($) {
	function initColorFields() {
		$('.diako-attribute-color-field').each(function () {
			var $field = $(this);
			if ($field.data('diakoColorReady')) {
				return;
			}
			$field.wpColorPicker();
			$field.data('diakoColorReady', true);
		});
	}
	$(initColorFields);
	$(document).on('ajaxComplete', initColorFields);
})(jQuery);
JS;
	wp_add_inline_script( 'wp-color-picker', $js );
}
add_action( 'admin_enqueue_scripts', 'diako_enqueue_color_attribute_admin_assets' );

/**
 * Color options available on a variable product.
 *
 * @param WC_Product $product Product.
 * @return array<int, array{slug:string,name:string,color:string,term_id:int}>
 */
function diako_get_product_color_swatches( WC_Product $product ): array {
	if ( ! $product->is_type( 'variable' ) || ! taxonomy_exists( DIAKO_COLOR_ATTRIBUTE_TAXONOMY ) ) {
		return array();
	}

	/** @var WC_Product_Variable $product */
	$attributes = $product->get_variation_attributes();
	$options    = $attributes[ DIAKO_COLOR_ATTRIBUTE_TAXONOMY ] ?? array();

	if ( empty( $options ) || ! is_array( $options ) ) {
		return array();
	}

	$swatches = array();

	foreach ( $options as $slug ) {
		$slug  = (string) $slug;
		$term  = get_term_by( 'slug', $slug, DIAKO_COLOR_ATTRIBUTE_TAXONOMY );
		$color = diako_get_attribute_color( $term instanceof WP_Term ? $term : $slug );

		if ( '' === $color ) {
			continue;
		}

		$swatches[] = array(
			'slug'    => $slug,
			'name'    => $term instanceof WP_Term ? $term->name : $slug,
			'color'   => $color,
			'term_id' => $term instanceof WP_Term ? (int) $term->term_id : 0,
		);
	}

	return $swatches;
}

/**
 * Render decorative color swatches for a product card.
 *
 * @param WC_Product|null $product Product.
 * @return void
 */
function diako_render_product_card_color_swatches( $product = null ): void {
	if ( ! $product instanceof WC_Product ) {
		global $product;
	}

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$swatches = diako_get_product_color_swatches( $product );

	if ( empty( $swatches ) ) {
		return;
	}

	$max_visible = 5;
	$total       = count( $swatches );
	$visible     = array_slice( $swatches, 0, $max_visible );
	$extra       = $total - count( $visible );
	?>
	<ul class="diako-product-card__swatches" aria-label="<?php esc_attr_e( 'رنگ‌های موجود', 'diako' ); ?>">
		<?php foreach ( $visible as $swatch ) : ?>
			<li>
				<span
					class="diako-product-card__swatch<?php echo diako_is_light_attribute_color( $swatch['color'] ) ? ' is-light' : ''; ?>"
					style="background-color: <?php echo esc_attr( $swatch['color'] ); ?>;"
					title="<?php echo esc_attr( $swatch['name'] ); ?>"
					aria-label="<?php echo esc_attr( $swatch['name'] ); ?>"
				></span>
			</li>
		<?php endforeach; ?>
		<?php if ( $extra > 0 ) : ?>
			<li>
				<span class="diako-product-card__swatch-more" aria-hidden="true">+<?php echo esc_html( (string) $extra ); ?></span>
			</li>
		<?php endif; ?>
	</ul>
	<?php
}
