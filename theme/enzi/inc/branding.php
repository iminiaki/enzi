<?php
/**
 * Theme branding (logos, colors, favicon).
 *
 * @package Lastify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default branding settings.
 *
 * @return array<string, mixed>
 */
function diako_get_default_branding_settings() {
	$default_logo = DIAKO_URI . '/assets/images/enzi-logo.png';

	return array(
		'logo_light_id'  => 0,
		'logo_light_url' => $default_logo,
		'logo_dark_id'   => 0,
		'logo_dark_url'  => $default_logo,
		'logo_alt'       => diako_get_default_brand_name(),
		'favicon_id'     => 0,
		'favicon_url'    => $default_logo,
		'footer_text'    => '',
		'contact'        => array(
			'phone_display' => '0912-777 22 97',
			'phone_tel'     => '+989127772297',
			'email'         => 'web@diakoo.shop',
			'address'       => 'تهران، بلوار میرداماد، پاساژ پایتخت، طبقه منفی ۱، پلاک ۱۲',
			'hours'         => __( 'شنبه تا پنج‌شنبه: ۱۰:۰۰ تا ۲۰:۰۰', 'diako' ),
			'response_time' => __( 'معمولاً در کمتر از ۲۴ ساعت پاسخ می‌دهیم.', 'diako' ),
			'map_url'       => 'https://maps.google.com/?q=' . rawurlencode( 'تهران، بلوار میرداماد، پاساژ پایتخت' ),
		),
		'social_links'   => array(
			'instagram' => 'https://instagram.com/diako',
			'telegram'  => 'https://t.me/diako',
			'whatsapp'  => '',
		),
		'colors'         => array(
			// Priority: #641F8C (primary) -> #A16207/#FACC15 (brand second) -> #FBEAFE (accent).
			'light' => array(
				'primary'      => '278 64% 34%',
				'accent'       => '291 91% 96%',
				'brand_orange'            => '38 92% 33%',
				'brand_orange_foreground' => '0 0% 100%',
				'background'   => '0 0% 100%',
				'foreground'   => '222 22% 12%',
				'border'       => '220 13% 91%',
				'ring'         => '278 64% 34%',
				'radius'       => '0.75rem',
			),
			'dark'  => array(
				'primary'      => '278 70% 52%',
				'accent'       => '278 28% 18%',
				'brand_orange'            => '48 96% 53%',
				'brand_orange_foreground' => '222 22% 12%',
				'background'   => '222 22% 6%',
				'foreground'   => '210 20% 96%',
				'border'       => '220 14% 18%',
				'ring'         => '278 70% 52%',
				'radius'       => '0.75rem',
			),
		),
	);
}

/**
 * Branding settings merged with defaults.
 *
 * @return array<string, mixed>
 */
function diako_get_branding_settings() {
	$settings = diako_get_theme_settings();
	$defaults = diako_get_default_branding_settings();
	$branding = isset( $settings['branding'] ) && is_array( $settings['branding'] ) ? $settings['branding'] : array();

	return diako_merge_theme_settings( $defaults, $branding );
}

/**
 * Resolve a branding asset URL from ID or URL field.
 *
 * @param array<string, mixed> $branding Branding settings.
 * @param string               $prefix   Field prefix (logo_light, logo_dark, favicon).
 * @return string
 */
function diako_resolve_branding_asset_url( array $branding, $prefix ) {
	$image_id = absint( $branding[ $prefix . '_id' ] ?? 0 );

	if ( $image_id > 0 ) {
		$url = wp_get_attachment_url( $image_id );

		if ( $url ) {
			return $url;
		}
	}

	return esc_url_raw( (string) ( $branding[ $prefix . '_url' ] ?? '' ) );
}

/**
 * Whether custom branding logos are configured.
 *
 * @return bool
 */
function diako_has_branding_logo() {
	$branding = diako_get_branding_settings();

	return '' !== diako_resolve_branding_asset_url( $branding, 'logo_light' )
		|| '' !== diako_resolve_branding_asset_url( $branding, 'logo_dark' );
}

/**
 * Branding logo URLs for light and dark mode.
 *
 * @return array{light: string, dark: string}
 */
function diako_get_branding_logo_urls() {
	$branding = diako_get_branding_settings();
	$light    = diako_resolve_branding_asset_url( $branding, 'logo_light' );
	$dark     = diako_resolve_branding_asset_url( $branding, 'logo_dark' );

	if ( '' === $light ) {
		$light = $dark;
	}

	if ( '' === $dark ) {
		$dark = $light;
	}

	return array(
		'light' => $light,
		'dark'  => $dark,
	);
}

/**
 * Parse a theme color into RGBA components.
 *
 * @param string $value Raw color value.
 * @return array{r: int, g: int, b: int, a: float}|null
 */
function diako_parse_theme_color( $value ) {
	$value = trim( (string) $value );

	if ( '' === $value ) {
		return null;
	}

	if ( preg_match( '/^rgba?\(\s*([\d.]+)\s*,\s*([\d.]+)\s*,\s*([\d.]+)(?:\s*,\s*([\d.]+))?\s*\)$/i', $value, $matches ) ) {
		return array(
			'r' => max( 0, min( 255, (int) round( (float) $matches[1] ) ) ),
			'g' => max( 0, min( 255, (int) round( (float) $matches[2] ) ) ),
			'b' => max( 0, min( 255, (int) round( (float) $matches[3] ) ) ),
			'a' => max( 0, min( 1, (float) ( $matches[4] ?? 1 ) ) ),
		);
	}

	if ( preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value, $matches ) ) {
		$hex = $matches[1];

		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		$alpha = 1.0;

		if ( 8 === strlen( $hex ) ) {
			$alpha = hexdec( substr( $hex, 6, 2 ) ) / 255;
			$hex   = substr( $hex, 0, 6 );
		}

		return array(
			'r' => hexdec( substr( $hex, 0, 2 ) ),
			'g' => hexdec( substr( $hex, 2, 2 ) ),
			'b' => hexdec( substr( $hex, 4, 2 ) ),
			'a' => round( $alpha, 2 ),
		);
	}

	if ( preg_match( '/^(\d{1,3})\s+(\d{1,3})%\s+(\d{1,3})%(?:\s*\/\s*([\d.]+))?$/', $value, $matches ) ) {
		$rgb = diako_hsl_to_rgb(
			(int) $matches[1],
			(int) $matches[2],
			(int) $matches[3]
		);

		return array(
			'r' => $rgb['r'],
			'g' => $rgb['g'],
			'b' => $rgb['b'],
			'a' => max( 0, min( 1, (float) ( $matches[4] ?? 1 ) ) ),
		);
	}

	return null;
}

/**
 * Convert HSL values to RGB.
 *
 * @param int $h Hue (0-360).
 * @param int $s Saturation (0-100).
 * @param int $l Lightness (0-100).
 * @return array{r: int, g: int, b: int}
 */
function diako_hsl_to_rgb( $h, $s, $l ) {
	$h = ( (float) $h ) / 360;
	$s = ( (float) $s ) / 100;
	$l = ( (float) $l ) / 100;

	if ( 0.0 === $s ) {
		$val = (int) round( $l * 255 );
		return array(
			'r' => $val,
			'g' => $val,
			'b' => $val,
		);
	}

	$hue2rgb = static function ( $p, $q, $t ) {
		if ( $t < 0 ) {
			$t += 1;
		}
		if ( $t > 1 ) {
			$t -= 1;
		}
		if ( $t < 1 / 6 ) {
			return $p + ( $q - $p ) * 6 * $t;
		}
		if ( $t < 1 / 2 ) {
			return $q;
		}
		if ( $t < 2 / 3 ) {
			return $p + ( $q - $p ) * ( 2 / 3 - $t ) * 6;
		}
		return $p;
	};

	$q = $l < 0.5 ? $l * ( 1 + $s ) : $l + $s - $l * $s;
	$p = 2 * $l - $q;

	return array(
		'r' => (int) round( $hue2rgb( $p, $q, $h + 1 / 3 ) * 255 ),
		'g' => (int) round( $hue2rgb( $p, $q, $h ) * 255 ),
		'b' => (int) round( $hue2rgb( $p, $q, $h - 1 / 3 ) * 255 ),
	);
}

/**
 * Convert RGBA components to HSL CSS variable value.
 *
 * @param array{r: int, g: int, b: int, a: float} $color Color components.
 * @return string
 */
function diako_theme_color_to_css_var( array $color ) {
	$r = $color['r'] / 255;
	$g = $color['g'] / 255;
	$b = $color['b'] / 255;

	$max = max( $r, $g, $b );
	$min = min( $r, $g, $b );
	$h   = 0;
	$s   = 0;
	$l   = ( $max + $min ) / 2;

	if ( $max !== $min ) {
		$d = $max - $min;
		$s = $l > 0.5 ? $d / ( 2 - $max - $min ) : $d / ( $max + $min );

		switch ( $max ) {
			case $r:
				$h = ( $g - $b ) / $d + ( $g < $b ? 6 : 0 );
				break;
			case $g:
				$h = ( $b - $r ) / $d + 2;
				break;
			default:
				$h = ( $r - $g ) / $d + 4;
				break;
		}

		$h /= 6;
	}

	$value = sprintf(
		'%d %d%% %d%%',
		(int) round( $h * 360 ),
		(int) round( $s * 100 ),
		(int) round( $l * 100 )
	);

	if ( isset( $color['a'] ) && $color['a'] < 1 ) {
		$value .= ' / ' . rtrim( rtrim( sprintf( '%.2f', $color['a'] ), '0' ), '.' );
	}

	return $value;
}

/**
 * Format RGBA components as an rgba() string.
 *
 * @param array{r: int, g: int, b: int, a: float} $color Color components.
 * @return string
 */
function diako_theme_color_to_rgba_string( array $color ) {
	$alpha = rtrim( rtrim( sprintf( '%.2f', $color['a'] ?? 1 ), '0' ), '.' );

	return sprintf(
		'rgba(%d, %d, %d, %s)',
		(int) $color['r'],
		(int) $color['g'],
		(int) $color['b'],
		$alpha
	);
}

/**
 * Convert a theme color to hex for native color inputs.
 *
 * @param array{r: int, g: int, b: int, a: float} $color Color components.
 * @return string
 */
function diako_theme_color_to_hex( array $color ) {
	return sprintf( '#%02x%02x%02x', (int) $color['r'], (int) $color['g'], (int) $color['b'] );
}

/**
 * Normalize any supported color format for CSS variables.
 *
 * @param string $value Raw value.
 * @param string $fallback Fallback value.
 * @return string
 */
function diako_sanitize_theme_color( $value, $fallback ) {
	$parsed = diako_parse_theme_color( $value );

	if ( null === $parsed ) {
		$parsed = diako_parse_theme_color( $fallback );
	}

	if ( null === $parsed ) {
		return $fallback;
	}

	return diako_theme_color_to_css_var( $parsed );
}

/**
 * Sanitize a CSS length value.
 *
 * @param string $value Raw value.
 * @param string $fallback Fallback value.
 * @return string
 */
function diako_sanitize_css_length( $value, $fallback ) {
	$value = trim( (string) $value );

	if ( preg_match( '/^\d+(\.\d+)?(px|rem|em|%)$/', $value ) ) {
		return $value;
	}

	return $fallback;
}

/**
 * Convert hex color to HSL triplet used by theme tokens.
 *
 * @param string $hex Hex color (#rrggbb).
 * @return string
 */
function diako_hex_to_hsl_triplet( $hex ) {
	$parsed = diako_parse_theme_color( (string) $hex );

	if ( null === $parsed ) {
		return '0 0% 0%';
	}

	return diako_theme_color_to_css_var( $parsed );
}

/**
 * Extract image ID and URL from branding form group.
 *
 * @param array<string, mixed> $input Raw branding input.
 * @param string               $key   Asset key.
 * @return array{id: int, url: string}
 */
function diako_extract_branding_asset( array $input, $key ) {
	$group = $input[ $key ] ?? null;

	if ( is_array( $group ) ) {
		return array(
			'id'  => absint( $group['image_id'] ?? 0 ),
			'url' => esc_url_raw( $group['image_url'] ?? '' ),
		);
	}

	return array(
		'id'  => absint( $input[ $key . '_id' ] ?? 0 ),
		'url' => esc_url_raw( $input[ $key . '_url' ] ?? '' ),
	);
}

/**
 * Sanitize branding settings from admin form.
 *
 * @param array<string, mixed> $input Raw branding input.
 * @return array<string, mixed>
 */
function diako_sanitize_branding_settings( array $input ) {
	$defaults   = diako_get_default_branding_settings();
	$logo_light = diako_extract_branding_asset( $input, 'logo_light' );
	$logo_dark  = diako_extract_branding_asset( $input, 'logo_dark' );
	$favicon    = diako_extract_branding_asset( $input, 'favicon' );

	$branding = array(
		'logo_light_id'  => $logo_light['id'],
		'logo_light_url' => $logo_light['url'],
		'logo_dark_id'   => $logo_dark['id'],
		'logo_dark_url'  => $logo_dark['url'],
		'logo_alt'       => sanitize_text_field( $input['logo_alt'] ?? '' ),
		'favicon_id'     => $favicon['id'],
		'favicon_url'    => $favicon['url'],
		'footer_text'    => sanitize_textarea_field( $input['footer_text'] ?? '' ),
		'contact'        => array(
			'phone_display' => sanitize_text_field( $input['contact']['phone_display'] ?? $defaults['contact']['phone_display'] ),
			'phone_tel'     => sanitize_text_field( $input['contact']['phone_tel'] ?? $defaults['contact']['phone_tel'] ),
			'email'         => sanitize_email( $input['contact']['email'] ?? $defaults['contact']['email'] ),
			'address'       => sanitize_text_field( $input['contact']['address'] ?? $defaults['contact']['address'] ),
			'hours'         => sanitize_text_field( $input['contact']['hours'] ?? $defaults['contact']['hours'] ),
			'response_time' => sanitize_text_field( $input['contact']['response_time'] ?? $defaults['contact']['response_time'] ),
			'map_url'       => esc_url_raw( $input['contact']['map_url'] ?? $defaults['contact']['map_url'] ),
		),
		'social_links'   => array(
			'instagram' => esc_url_raw( $input['social_links']['instagram'] ?? $defaults['social_links']['instagram'] ),
			'telegram'  => esc_url_raw( $input['social_links']['telegram'] ?? $defaults['social_links']['telegram'] ),
			'whatsapp'  => esc_url_raw( $input['social_links']['whatsapp'] ?? $defaults['social_links']['whatsapp'] ),
		),
		'colors'         => array(
			'light' => array(),
			'dark'  => array(),
		),
	);

	foreach ( array( 'light', 'dark' ) as $mode ) {
		$source = isset( $input['colors'][ $mode ] ) && is_array( $input['colors'][ $mode ] ) ? $input['colors'][ $mode ] : array();
		$base   = $defaults['colors'][ $mode ];

		foreach ( $base as $token => $fallback ) {
			if ( 'radius' === $token ) {
				$branding['colors'][ $mode ][ $token ] = diako_sanitize_css_length( $source[ $token ] ?? '', $fallback );
				continue;
			}

			$branding['colors'][ $mode ][ $token ] = diako_sanitize_theme_color( $source[ $token ] ?? '', $fallback );
		}

		$branding['colors'][ $mode ]['ring'] = $branding['colors'][ $mode ]['primary'];
	}

	return $branding;
}

/**
 * Ensure primary color meets WCAG AA contrast with white button text.
 *
 * @param string $color       HSL components string.
 * @param float  $max_lightness Maximum allowed lightness percentage.
 * @return string
 */
function diako_contrast_safe_primary( string $color, float $max_lightness = 36 ): string {
	if ( ! preg_match( '/^(\d+(?:\.\d+)?)\s+(\d+(?:\.\d+)?)%\s+(\d+(?:\.\d+)?)%$/', trim( $color ), $matches ) ) {
		return $color;
	}

	$lightness = (float) $matches[3];

	if ( $lightness > $max_lightness ) {
		return $matches[1] . ' ' . $matches[2] . '% ' . $max_lightness . '%';
	}

	return $color;
}

/**
 * Build CSS variable declarations for a color mode.
 *
 * @param array<string, string> $colors Color tokens.
 * @return string
 */
function diako_build_branding_css_vars( array $colors ) {
	$css = '';

	foreach ( $colors as $token => $value ) {
		if ( 'radius' === $token ) {
			$css .= '--radius:' . $value . ';';
			continue;
		}

		$var_name = str_replace( '_', '-', $token );
		$css     .= '--' . $var_name . ':' . $value . ';';

		if ( 'primary' === $token && empty( $colors['ring'] ) ) {
			$css .= '--ring:' . $value . ';';
		}
	}

	return $css;
}

/**
 * Output dynamic branding CSS variables.
 *
 * @return void
 */
function diako_output_branding_css() {
	if ( function_exists( 'diako_should_load_storefront_theme_assets' ) && ! diako_should_load_storefront_theme_assets() ) {
		return;
	}

	$branding = diako_get_branding_settings();
	$colors   = $branding['colors'] ?? array();

	$light = isset( $colors['light'] ) && is_array( $colors['light'] ) ? $colors['light'] : array();
	$dark  = isset( $colors['dark'] ) && is_array( $colors['dark'] ) ? $colors['dark'] : array();

	if ( ! empty( $light['primary'] ) ) {
		$light['primary'] = diako_contrast_safe_primary( (string) $light['primary'], 36 );
		$light['ring']    = $light['primary'];
	}

	if ( ! empty( $dark['primary'] ) ) {
		$dark['primary'] = diako_contrast_safe_primary( (string) $dark['primary'], 52 );
		$dark['ring']    = $dark['primary'];
	}

	if ( empty( $light ) && empty( $dark ) ) {
		return;
	}

	$css  = ':root{' . diako_build_branding_css_vars( $light ) . '--primary-foreground:0 0% 100%;}';
	$css .= 'html.dark{' . diako_build_branding_css_vars( $dark ) . '--primary-foreground:0 0% 100%;}';

	printf(
		'<style id="lastify-branding-css">%s</style>' . "\n",
		wp_strip_all_tags( $css )
	);
}
add_action( 'wp_head', 'diako_output_branding_css', 2 );

/**
 * Output favicon from branding settings.
 *
 * @return void
 */
function diako_output_branding_favicon() {
	$branding = diako_get_branding_settings();
	$url      = diako_resolve_branding_asset_url( $branding, 'favicon' );

	if ( '' === $url ) {
		return;
	}

	printf(
		'<link rel="icon" href="%s" sizes="any">' . "\n",
		esc_url( $url )
	);
}
add_action( 'wp_head', 'diako_output_branding_favicon', 3 );

/**
 * Resolve branding logo dimensions for width/height attributes.
 *
 * @return array{width: int, height: int}
 */
function diako_get_branding_logo_dimensions(): array {
	$branding = diako_get_branding_settings();
	$image_id = absint( $branding['logo_light_id'] ?? 0 );

	if ( $image_id > 0 ) {
		$meta = wp_get_attachment_metadata( $image_id );

		if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
			return array(
				'width'  => (int) $meta['width'],
				'height' => (int) $meta['height'],
			);
		}
	}

	return array(
		'width'  => 120,
		'height' => 32,
	);
}

/**
 * Render branding logo markup.
 *
 * @param array<string, string> $args Logo args.
 * @return void
 */
function diako_render_branding_logo( array $args ) {
	$urls       = diako_get_branding_logo_urls();
	$branding   = diako_get_branding_settings();
	$link_class = esc_attr( $args['link_class'] );
	$img_class  = esc_attr( diako_cn( 'diako-logo__image', $args['class'] ) );
	$alt        = '' !== ( $branding['logo_alt'] ?? '' ) ? $branding['logo_alt'] : $args['alt'];
	$dimensions = diako_get_branding_logo_dimensions();

	if ( $urls['light'] === $urls['dark'] ) {
		$markup = sprintf(
			'<img src="%1$s" alt="%2$s" class="%3$s diako-logo__image--branded" width="%4$d" height="%5$d" decoding="async">',
			esc_url( $urls['light'] ),
			esc_attr( $alt ),
			$img_class,
			$dimensions['width'],
			$dimensions['height']
		);
	} else {
		$markup = sprintf(
			'<img src="%1$s" alt="%2$s" class="%3$s diako-logo__image--branded dark:hidden" width="%5$d" height="%6$d" decoding="async"><img src="%4$s" alt="%2$s" class="%3$s diako-logo__image--branded hidden dark:block" width="%5$d" height="%6$d" decoding="async">',
			esc_url( $urls['light'] ),
			esc_attr( $alt ),
			$img_class,
			esc_url( $urls['dark'] ),
			$dimensions['width'],
			$dimensions['height']
		);
	}

	printf(
		'<a href="%1$s" class="%2$s" rel="home">%3$s</a>',
		esc_url( home_url( '/' ) ),
		$link_class,
		$markup
	);
}

/**
 * Footer copyright text from branding or default.
 *
 * @return string
 */
function diako_get_footer_copyright_text() {
	$branding = diako_get_branding_settings();
	$custom   = trim( (string) ( $branding['footer_text'] ?? '' ) );

	if ( '' !== $custom ) {
		return $custom;
	}

	return sprintf(
		/* translators: %s: store brand name */
		__( 'تمامی حقوق این وب‌سایت متعلق به فروشگاه %s می‌باشد.', 'diako' ),
		diako_get_brand_name()
	);
}

/**
 * Render a color token field in admin.
 *
 * @param string $name        Field name.
 * @param string $label       Field label.
 * @param string $color_value Stored color value.
 * @return void
 */
function diako_render_settings_color_field( $name, $label, $color_value ) {
	$parsed = diako_parse_theme_color( $color_value );

	if ( null === $parsed ) {
		$parsed = array(
			'r' => 0,
			'g' => 51,
			'b' => 204,
			'a' => 1,
		);
	}

	$hex   = diako_theme_color_to_hex( $parsed );
	$rgba  = diako_theme_color_to_rgba_string( $parsed );
	$alpha = (int) round( ( $parsed['a'] ?? 1 ) * 100 );
	?>
	<tr>
		<th scope="row"><label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label></th>
		<td>
			<div class="lastify-color-field" data-lastify-color-field>
				<input type="color" value="<?php echo esc_attr( $hex ); ?>" data-lastify-color-picker>
				<label class="lastify-color-field__alpha">
					<span><?php esc_html_e( 'شفافیت', 'diako' ); ?></span>
					<input type="range" min="0" max="100" step="1" value="<?php echo esc_attr( (string) $alpha ); ?>" data-lastify-color-alpha>
					<span data-lastify-color-alpha-label><?php echo esc_html( $alpha . '%' ); ?></span>
				</label>
				<input
					id="<?php echo esc_attr( $name ); ?>"
					class="regular-text lastify-color-field__value"
					type="text"
					name="<?php echo esc_attr( $name ); ?>"
					value="<?php echo esc_attr( $rgba ); ?>"
					data-lastify-color-value
					placeholder="rgba(0, 51, 204, 1)"
				>
			</div>
		</td>
	</tr>
	<?php
}

/**
 * Render general branding fields in settings.
 *
 * @param array<string, mixed> $settings Theme settings.
 * @return void
 */
function diako_render_branding_general_fields( array $settings ) {
	$branding = isset( $settings['branding'] ) && is_array( $settings['branding'] )
		? wp_parse_args( $settings['branding'], diako_get_default_branding_settings() )
		: diako_get_default_branding_settings();

	$colors = wp_parse_args(
		$branding['colors'] ?? array(),
		diako_get_default_branding_settings()['colors']
	);
	?>
	<h2><?php esc_html_e( 'هویت برند', 'diako' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="lastify_brand_name"><?php esc_html_e( 'نام برند', 'diako' ); ?></label></th>
			<td>
				<input id="lastify_brand_name" class="regular-text" type="text" name="lastify_settings[brand_name]" value="<?php echo esc_attr( $settings['brand_name'] ); ?>">
				<p class="description"><?php esc_html_e( 'نام فروشگاه در متن‌های قالب (مثلاً انزی).', 'diako' ); ?></p>
			</td>
		</tr>
		<?php diako_render_settings_field( 'lastify_settings[branding][logo_alt]', __( 'متن جایگزین لوگو', 'diako' ), $branding['logo_alt'] ); ?>
		<?php diako_render_settings_textarea( 'lastify_settings[branding][footer_text]', __( 'متن کپی‌رایت فوتر', 'diako' ), $branding['footer_text'] ); ?>
	</table>

	<h2><?php esc_html_e( 'اطلاعات تماس', 'diako' ); ?></h2>
	<table class="form-table" role="presentation">
		<?php
		$contact = wp_parse_args( $branding['contact'] ?? array(), diako_get_default_branding_settings()['contact'] );
		diako_render_settings_field( 'lastify_settings[branding][contact][phone_display]', __( 'شماره تلفن نمایشی', 'diako' ), $contact['phone_display'] );
		diako_render_settings_field( 'lastify_settings[branding][contact][phone_tel]', __( 'شماره تلفن لینک تماس', 'diako' ), $contact['phone_tel'] );
		diako_render_settings_field( 'lastify_settings[branding][contact][email]', __( 'ایمیل', 'diako' ), $contact['email'], 'email' );
		diako_render_settings_textarea( 'lastify_settings[branding][contact][address]', __( 'آدرس', 'diako' ), $contact['address'] );
		diako_render_settings_field( 'lastify_settings[branding][contact][hours]', __( 'ساعات پاسخ‌گویی', 'diako' ), $contact['hours'] );
		diako_render_settings_field( 'lastify_settings[branding][contact][response_time]', __( 'زمان پاسخ‌گویی', 'diako' ), $contact['response_time'] );
		diako_render_settings_field( 'lastify_settings[branding][contact][map_url]', __( 'لینک نقشه', 'diako' ), $contact['map_url'], 'url' );
		?>
	</table>

	<h2><?php esc_html_e( 'شبکه‌های اجتماعی', 'diako' ); ?></h2>
	<table class="form-table" role="presentation">
		<?php
		$social_links = wp_parse_args( $branding['social_links'] ?? array(), diako_get_default_branding_settings()['social_links'] );
		diako_render_settings_field( 'lastify_settings[branding][social_links][instagram]', __( 'اینستاگرام', 'diako' ), $social_links['instagram'], 'url' );
		diako_render_settings_field( 'lastify_settings[branding][social_links][telegram]', __( 'تلگرام', 'diako' ), $social_links['telegram'], 'url' );
		diako_render_settings_field( 'lastify_settings[branding][social_links][whatsapp]', __( 'واتساپ (اختیاری)', 'diako' ), $social_links['whatsapp'], 'url' );
		?>
		<tr>
			<th scope="row"></th>
			<td><p class="description"><?php esc_html_e( 'اگر واتساپ خالی باشد، لینک واتساپ از شماره تلفن ساخته می‌شود.', 'diako' ); ?></p></td>
		</tr>
	</table>

	<h2><?php esc_html_e( 'لوگو', 'diako' ); ?></h2>
	<table class="form-table" role="presentation">
		<?php
		diako_render_settings_image_field( 'lastify_settings[branding][logo_light]', array(
			'image_id'  => $branding['logo_light_id'],
			'image_url' => $branding['logo_light_url'],
		) );
		?>
		<tr>
			<th scope="row"></th>
			<td><p class="description"><?php esc_html_e( 'لوگوی حالت روشن', 'diako' ); ?></p></td>
		</tr>
		<?php
		diako_render_settings_image_field( 'lastify_settings[branding][logo_dark]', array(
			'image_id'  => $branding['logo_dark_id'],
			'image_url' => $branding['logo_dark_url'],
		) );
		?>
		<tr>
			<th scope="row"></th>
			<td><p class="description"><?php esc_html_e( 'لوگوی حالت تاریک', 'diako' ); ?></p></td>
		</tr>
		<?php
		diako_render_settings_image_field( 'lastify_settings[branding][favicon]', array(
			'image_id'  => $branding['favicon_id'],
			'image_url' => $branding['favicon_url'],
		) );
		?>
		<tr>
			<th scope="row"></th>
			<td><p class="description"><?php esc_html_e( 'فاویکون سایت (.ico، .png یا .svg)', 'diako' ); ?></p></td>
		</tr>
	</table>

	<h2><?php esc_html_e( 'رنگ‌های حالت روشن', 'diako' ); ?></h2>
	<table class="form-table" role="presentation">
		<?php
		diako_render_settings_color_field( 'lastify_settings[branding][colors][light][primary]', __( 'رنگ اصلی', 'diako' ), $colors['light']['primary'] );
		diako_render_settings_color_field( 'lastify_settings[branding][colors][light][accent]', __( 'رنگ تأکیدی', 'diako' ), $colors['light']['accent'] );
		diako_render_settings_color_field( 'lastify_settings[branding][colors][light][brand_orange]', __( 'رنگ دوم', 'diako' ), $colors['light']['brand_orange'] );
		?>
		<tr>
			<th scope="row"></th>
			<td><p class="description"><?php esc_html_e( 'نشان سبد خرید، برچسب تخفیف و دکمه‌های برجسته', 'diako' ); ?></p></td>
		</tr>
		<?php
		diako_render_settings_color_field( 'lastify_settings[branding][colors][light][background]', __( 'پس‌زمینه', 'diako' ), $colors['light']['background'] );
		diako_render_settings_color_field( 'lastify_settings[branding][colors][light][foreground]', __( 'متن', 'diako' ), $colors['light']['foreground'] );
		diako_render_settings_color_field( 'lastify_settings[branding][colors][light][border]', __( 'حاشیه', 'diako' ), $colors['light']['border'] );
		diako_render_settings_field( 'lastify_settings[branding][colors][light][radius]', __( 'گردی گوشه‌ها', 'diako' ), $colors['light']['radius'] );
		?>
	</table>

	<h2><?php esc_html_e( 'رنگ‌های حالت تاریک', 'diako' ); ?></h2>
	<table class="form-table" role="presentation">
		<?php
		diako_render_settings_color_field( 'lastify_settings[branding][colors][dark][primary]', __( 'رنگ اصلی', 'diako' ), $colors['dark']['primary'] );
		diako_render_settings_color_field( 'lastify_settings[branding][colors][dark][accent]', __( 'رنگ تأکیدی', 'diako' ), $colors['dark']['accent'] );
		diako_render_settings_color_field( 'lastify_settings[branding][colors][dark][brand_orange]', __( 'رنگ دوم', 'diako' ), $colors['dark']['brand_orange'] );
		?>
		<tr>
			<th scope="row"></th>
			<td><p class="description"><?php esc_html_e( 'نشان سبد خرید، برچسب تخفیف و دکمه‌های برجسته', 'diako' ); ?></p></td>
		</tr>
		<?php
		diako_render_settings_color_field( 'lastify_settings[branding][colors][dark][background]', __( 'پس‌زمینه', 'diako' ), $colors['dark']['background'] );
		diako_render_settings_color_field( 'lastify_settings[branding][colors][dark][foreground]', __( 'متن', 'diako' ), $colors['dark']['foreground'] );
		diako_render_settings_color_field( 'lastify_settings[branding][colors][dark][border]', __( 'حاشیه', 'diako' ), $colors['dark']['border'] );
		diako_render_settings_field( 'lastify_settings[branding][colors][dark][radius]', __( 'گردی گوشه‌ها', 'diako' ), $colors['dark']['radius'] );
		?>
	</table>
	<?php
}
