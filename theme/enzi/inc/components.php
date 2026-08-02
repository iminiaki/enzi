<?php
/**
 * shadcn-style UI helpers for PHP templates.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merge class names.
 *
 * @param string ...$classes Class names.
 * @return string
 */
function diako_cn( ...$classes ) {
	return implode( ' ', array_filter( $classes ) );
}

/**
 * Convert Western digits to Persian without breaking HTML entities.
 *
 * @param string $value Value.
 * @return string
 */
function diako_to_persian_digits( $value ) {
	static $digits = array(
		'0' => '۰',
		'1' => '۱',
		'2' => '۲',
		'3' => '۳',
		'4' => '۴',
		'5' => '۵',
		'6' => '۶',
		'7' => '۷',
		'8' => '۸',
		'9' => '۹',
	);

	$value = (string) $value;

	if ( '' === $value || ! preg_match( '/[0-9]/', $value ) ) {
		return $value;
	}

	$entities  = array();
	$protected = preg_replace_callback(
		'/&(?:#(?:x[0-9a-fA-F]+|[0-9]+)|[^\s&;]+);/',
		function ( $matches ) use ( &$entities ) {
			$index       = count( $entities );
			$placeholder = "\x01\x02" . chr( 65 + ( $index % 26 ) ) . chr( 97 + ( (int) ( $index / 26 ) % 26 ) ) . "\x01\x02";
			$entities[ $placeholder ] = $matches[0];

			return $placeholder;
		},
		$value
	);

	$converted = strtr( $protected, $digits );

	return strtr( $converted, $entities );
}

/**
 * Convert Persian/Arabic digits back to Western digits.
 *
 * @param string $value Value.
 * @return string
 */
function diako_to_western_digits( $value ) {
	$value = (string) $value;

	if ( '' === $value ) {
		return $value;
	}

	$map = array(
		'۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
		'۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
		'٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
		'٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
	);

	return strtr( $value, $map );
}

/**
 * Convert digits in translatable strings while preserving sprintf placeholders.
 *
 * @param string $value Value.
 * @return string
 */
function diako_to_persian_digits_translatable( $value ) {
	$value = (string) $value;

	if ( '' === $value || ! preg_match( '/[0-9]/', $value ) ) {
		return $value;
	}

	$protected = array();
	$index     = 0;

	$protected_value = preg_replace_callback(
		'/%(\d+\$)?[-+0\' #]*\*?\d*(?:\.\d+)?[bcdeEufFgGoxX%]|%%/',
		static function ( array $matches ) use ( &$protected, &$index ) {
			$key               = "\x01PLH" . chr( 65 + ( $index % 26 ) ) . chr( 97 + ( (int) ( $index / 26 ) % 26 ) ) . "\x01";
			$protected[ $key ] = $matches[0];
			$index++;
			return $key;
		},
		$value
	);

	$converted = diako_to_persian_digits( $protected_value );

	if ( $protected ) {
		$converted = strtr( $converted, $protected );
	}

	return $converted;
}

/**
 * Whether rendered output should use Persian digits.
 *
 * @return bool
 */
function diako_should_use_persian_digits() {
	return (bool) apply_filters( 'diako_use_persian_digits', true );
}

/**
 * Absolute path to the default theme logo file.
 *
 * @return string
 */
function diako_logo_asset_path() {
	return DIAKO_DIR . '/assets/images/lastaar-logo.webp';
}

/**
 * Theme logo URL.
 *
 * @return string
 */
function diako_logo_url() {
	if ( is_readable( diako_logo_asset_path() ) ) {
		return DIAKO_URI . '/assets/images/lastaar-logo.webp';
	}

	return DIAKO_URI . '/assets/images/logo.svg';
}

/**
 * Default theme logo image markup.
 *
 * @param string $class Extra classes.
 * @param string $alt   Image alt text.
 * @return string
 */
function diako_get_logo_image( $class = '', $alt = '' ) {
	if ( ! is_readable( diako_logo_asset_path() ) ) {
		return '';
	}

	if ( '' === $alt ) {
		$alt = get_bloginfo( 'name' );
	}

	return sprintf(
		'<img src="%1$s" alt="%2$s" class="%3$s" width="120" height="32" decoding="async">',
		esc_url( diako_logo_url() ),
		esc_attr( $alt ),
		esc_attr( diako_cn( 'diako-logo__image', $class ) )
	);
}

/**
 * Get inline logo SVG markup.
 *
 * @param string $class Extra classes.
 * @return string
 */
function diako_get_logo_svg( $class = '' ) {
	$path = DIAKO_DIR . '/assets/images/logo.svg';

	if ( ! file_exists( $path ) ) {
		return '';
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$svg = file_get_contents( $path );

	if ( false === $svg || '' === $svg ) {
		return '';
	}

	$class_attr = esc_attr( diako_cn( 'diako-logo__image', $class ) );

	return preg_replace(
		'/<svg\b([^>]*)>/',
		'<svg$1 class="' . $class_attr . '">',
		$svg,
		1
	);
}

/**
 * Render theme logo.
 *
 * @param array<string, string> $args Logo args.
 * @return void
 */
function diako_logo( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'class'      => '',
			'link_class' => 'diako-logo inline-flex shrink-0 items-center',
			'alt'        => get_bloginfo( 'name' ),
		)
	);

	$link_class = esc_attr( $args['link_class'] );

	if ( diako_has_branding_logo() ) {
		diako_render_branding_logo( $args );
		return;
	}

	if ( has_custom_logo() ) {
		$html = get_custom_logo();

		if ( $link_class ) {
			if ( preg_match( '/class="([^"]*)"/', $html, $matches ) ) {
				$html = preg_replace(
					'/class="([^"]*)"/',
					'class="$1 ' . $link_class . '"',
					$html,
					1
				);
			} else {
				$html = preg_replace(
					'/<a\s+/',
					'<a class="' . $link_class . '" ',
					$html,
					1
				);
			}
		}

		if ( $args['class'] && preg_match( '/<img\b/i', $html ) ) {
			if ( preg_match( '/<img[^>]*\bclass="/i', $html ) ) {
				$html = preg_replace(
					'/(<img[^>]*\bclass=")([^"]*)(")/i',
					'$1$2 ' . esc_attr( $args['class'] ) . '$3',
					$html,
					1
				);
			} else {
				$html = preg_replace(
					'/<img\b/i',
					'<img class="' . esc_attr( $args['class'] ) . '"',
					$html,
					1
				);
			}
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
		return;
	}

	$markup = diako_get_logo_image( $args['class'], $args['alt'] );

	if ( '' === $markup ) {
		$markup = diako_get_logo_svg( $args['class'] );
	}

	if ( '' === $markup ) {
		$markup = sprintf(
			'<span class="%s text-lg font-bold text-primary">%s</span>',
			esc_attr( diako_cn( 'diako-logo__text', $args['class'] ) ),
			esc_html( $args['alt'] )
		);
	}

	printf(
		'<a href="%1$s" class="%2$s" rel="home">%3$s</a>',
		esc_url( home_url( '/' ) ),
		$link_class,
		$markup // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/**
 * shadcn Button classes.
 *
 * @param string $variant Button variant.
 * @param string $size    Button size.
 * @param string $extra   Extra classes.
 * @return string
 */
function diako_button_classes( $variant = 'default', $size = 'default', $extra = '' ) {
	$base = 'diako-button inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl text-sm font-medium no-underline transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50';

	$variants = array(
		'default'     => 'bg-primary text-primary-foreground shadow hover:bg-primary/90',
		'destructive' => 'bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/90',
		'outline'     => 'border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground',
		'secondary'   => 'bg-secondary text-secondary-foreground shadow-sm hover:bg-secondary/80',
		'ghost'       => 'hover:bg-accent hover:text-accent-foreground',
		'link'        => 'text-primary underline-offset-4 hover:underline',
	);

	$sizes = array(
		'default' => 'h-[46px] px-4',
		'sm'      => 'h-10 px-3 text-xs',
		'lg'      => 'h-[46px] px-8',
		'icon'    => 'h-[46px] w-[46px]',
	);

	return diako_cn(
		$base,
		$variants[ $variant ] ?? $variants['default'],
		$sizes[ $size ] ?? $sizes['default'],
		$extra
	);
}

/**
 * Render shadcn-style button or link.
 *
 * @param array<string, mixed> $args Button args.
 * @return void
 */
function diako_button( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'href'         => '',
			'label'        => '',
			'variant'      => 'default',
			'size'         => 'default',
			'class'        => '',
			'type'         => 'link',
			'attrs'        => array(),
			'liquid'       => false,
			'view_mode'    => 'text',
			'button_type'  => 'submit',
			'icon'         => '',
			'icon_class'   => 'h-4 w-4',
		)
	);

	$class = diako_button_classes( $args['variant'], $args['size'], $args['class'] );
	$attr  = '';

	foreach ( $args['attrs'] as $key => $value ) {
		$attr .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
	}

	if ( $args['icon'] ) {
		$content = diako_lucide_icon_svg( $args['icon'], $args['icon_class'] ) . '<span>' . esc_html( $args['label'] ) . '</span>';
	} else {
		$content = esc_html( $args['label'] );
	}

	if ( 'button' === $args['type'] ) {
		$button_type = $args['button_type'];
		printf(
			'<button type="%s" class="%s"%s>%s</button>',
			esc_attr( $button_type ),
			esc_attr( $class ),
			$attr,
			$content // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
		return;
	}

	printf(
		'<a href="%s" class="%s"%s>%s</a>',
		esc_url( $args['href'] ),
		esc_attr( $class ),
		$attr,
		$content // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/**
 * Render an empty-state panel inside My Account tab content.
 *
 * @param array<string,mixed> $args Display args.
 * @return void
 */
function diako_render_account_empty_state( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'icon'    => 'package',
			'message' => '',
			'button'  => null,
		)
	);

	if ( '' === $args['message'] ) {
		return;
	}
	?>
	<div class="diako-account-empty">
		<div class="diako-account-empty__icon" aria-hidden="true">
			<?php echo diako_lucide_icon_svg( $args['icon'], 'h-10 w-10' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>

		<p class="diako-account-empty__message"><?php echo esc_html( $args['message'] ); ?></p>

		<?php if ( ! empty( $args['button'] ) && is_array( $args['button'] ) ) : ?>
			<div class="diako-account-empty__actions">
				<?php
				diako_button(
					wp_parse_args(
						$args['button'],
						array(
							'variant' => 'default',
							'size'    => 'lg',
						)
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * shadcn Badge classes.
 *
 * @param string $variant Badge variant.
 * @return string
 */
function diako_badge_classes( $variant = 'default' ) {
	$base = 'inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2';

	$variants = array(
		'default'     => 'border-transparent bg-primary text-primary-foreground shadow hover:bg-primary/80',
		'secondary'   => 'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80',
		'outline'     => 'text-foreground',
		'destructive' => 'border-transparent bg-destructive text-destructive-foreground shadow hover:bg-destructive/80',
	);

	return diako_cn( $base, $variants[ $variant ] ?? $variants['default'] );
}

/**
 * shadcn Card wrapper classes.
 *
 * @param string $extra Extra classes.
 * @return string
 */
function diako_card_classes( $extra = '' ) {
	return diako_cn(
		'rounded-xl border bg-card text-card-foreground shadow transition-colors',
		$extra
	);
}

/**
 * Resolve a media library image URL by filename.
 *
 * @param string $filename          Attachment filename.
 * @param string $fallback_relative Path under wp-content/uploads/.
 * @return string
 */
function diako_get_media_image_url( string $filename, string $fallback_relative = '' ) {
	static $cache = array();

	if ( isset( $cache[ $filename ] ) ) {
		return $cache[ $filename ];
	}

	$url = '';

	$attachments = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_wp_attached_file',
					'value'   => $filename,
					'compare' => 'LIKE',
				),
			),
		)
	);

	if ( ! empty( $attachments[0] ) ) {
		$attachment_url = wp_get_attachment_image_url( (int) $attachments[0], 'full' );

		if ( $attachment_url ) {
			$url = $attachment_url;
		}
	}

	if ( '' === $url && '' !== $fallback_relative ) {
		$url = content_url( 'uploads/' . ltrim( $fallback_relative, '/' ) );
	}

	$cache[ $filename ] = $url;

	return $url;
}

/**
 * shadcn Input classes.
 *
 * @param string $extra Extra classes.
 * @return string
 */
function diako_input_classes( $extra = '' ) {
	return diako_cn(
		'flex h-[46px] w-full rounded-2xl border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50',
		$extra
	);
}

/**
 * shadcn Textarea classes.
 *
 * @param string $extra Extra classes.
 * @return string
 */
function diako_textarea_classes( $extra = '' ) {
	return diako_cn(
		'flex min-h-[140px] w-full rounded-2xl border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50',
		$extra
	);
}

/**
 * Form label classes.
 *
 * @param string $extra Extra classes.
 * @return string
 */
function diako_label_classes( $extra = '' ) {
	return diako_cn(
		'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70',
		$extra
	);
}

/**
 * Company contact details used across the theme.
 *
 * @return array{phone_display: string, phone_tel: string, email: string, address: string}
 */
function diako_get_company_contact_details() {
	return array(
		'phone_display' => '0912-777 22 97',
		'phone_tel'     => '+989127772297',
		'email'         => 'web@diakoo.shop',
		'address'       => 'تهران، بلوار میرداماد، پاساژ پایتخت، طبقه منفی ۱، پلاک ۱۲',
	);
}

/**
 * HTML attributes for LTR phone numbers inside RTL layouts.
 *
 * @return string
 */
function diako_phone_ltr_attr(): string {
	return 'dir="ltr" class="diako-phone-ltr"';
}

/**
 * Normalize a phone number for tel: links.
 *
 * @param string $phone Raw phone value.
 * @return string
 */
function diako_phone_tel_href( string $phone ): string {
	$digits = preg_replace( '/\D+/', '', diako_to_western_digits( $phone ) );

	if ( str_starts_with( $digits, '09' ) && 11 === strlen( $digits ) ) {
		return '+98' . substr( $digits, 1 );
	}

	if ( str_starts_with( $digits, '98' ) ) {
		return '+' . $digits;
	}

	return $digits;
}

/**
 * Render a phone link with LTR number text.
 *
 * @param string $phone   Phone number to display and dial.
 * @param string $class   Link classes.
 * @param string $display Optional formatted display value.
 * @return void
 */
function diako_render_phone_link( string $phone, string $class = 'hover:text-foreground', string $display = '' ): void {
	$phone = trim( $phone );

	if ( '' === $phone ) {
		return;
	}

	if ( '' === $display ) {
		$display = $phone;
	}

	printf(
		'<a class="%1$s" href="tel:%2$s"><span %3$s>%4$s</span></a>',
		esc_attr( $class ),
		esc_attr( diako_phone_tel_href( $phone ) ),
		diako_phone_ltr_attr(),
		esc_html( diako_to_persian_digits( diako_to_western_digits( $display ) ) )
	);
}

/**
 * Render the company phone link.
 *
 * @param string $class Link classes.
 * @return void
 */
function diako_render_company_phone_link( $class = 'hover:text-foreground' ) {
	$contact = diako_get_company_contact_details();

	diako_render_phone_link( $contact['phone_tel'], $class, $contact['phone_display'] );
}

/**
 * Lucide icon SVG markup.
 *
 * @param string $name  Icon slug: phone, mail, map-pin, chevron-left, chevron-right, check, instagram, telegram, whatsapp.
 * @param string $class Extra classes.
 * @return string
 */
function diako_lucide_icon_svg( $name, $class = '' ) {
	$icons = array(
		'phone'         => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
		'mail'          => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
		'map-pin'       => '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
		'chevron-left'  => '<path d="m15 18-6-6 6-6"/>',
		'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
		'instagram'     => '<rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>',
		'telegram'      => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
		'whatsapp'      => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="M8 12h.01"/><path d="M12 12h.01"/><path d="M16 12h.01"/>',
		'shopping-cart' => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
		'search'        => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
		'percent'       => '<line x1="19" x2="5" y1="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>',
		'gamepad-2'     => '<line x1="6" x2="10" y1="11" y2="11"/><line x1="8" x2="8" y1="9" y2="13"/><line x1="15" x2="15.01" y1="12" y2="12"/><line x1="18" x2="18.01" y1="10" y2="10"/><path d="M17.32 5H6.68a4 4 0 0 0-3.978 3.59A7 7 0 0 0 2 12v2a7 7 0 0 0 7 7h6a7 7 0 0 0 7-7v-2a7 7 0 0 0-.69-3.41A4 4 0 0 0 17.32 5z"/>',
		'monitor'       => '<rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/>',
		'headphones'    => '<path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/>',
		'clock'         => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
		'shopping-bag'  => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
		'user'          => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
		'user-key'      => '<path d="M20 11v6"/><path d="M20 13h2"/><path d="M3 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 2.072.578"/><circle cx="10" cy="7" r="4"/><circle cx="20" cy="19" r="2"/>',
		'log-in'        => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/>',
		'package'       => '<path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73Z"/><path d="M12 22V12"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
		'book-open'     => '<path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>',
		'download'      => '<path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/>',
		'credit-card'   => '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
		'log-out'       => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>',
		'arrow-left'    => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
		'package-x'     => '<path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/><path d="m7.5 4.27 9 5.15"/><polyline points="3.29 7 12 12 20.71 7"/><line x1="12" x2="12" y1="22" y2="12"/><path d="m17 17 5 5"/><path d="m22 17-5 5"/>',
		'layout-grid'         => '<rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>',
		'sliders-horizontal'  => '<line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/>',
		'x'             => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
		'check'         => '<path d="M20 6 9 17l-5-5"/>',
		'refresh-cw'    => '<path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/>',
		'check-circle'  => '<path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/>',
		'scale'         => '<path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="M7 21h10"/><path d="M12 3v18"/><path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/>',
		'alert-circle'  => '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>',
		'bell'          => '<path d="M10.268 21a2 2 0 0 0 3.464 0"/><path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"/>',
		'heart'         => '<path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3.016 0L5 15c-1.5-1.5-3-3.2-3-5.5"/>',
		'copy'          => '<rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>',
		'target'        => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
		'sparkles'      => '<path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/>',
		'shield-check'  => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
		'users'         => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
	);

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	$class_attr = esc_attr( diako_cn( 'h-[18px] w-[18px] shrink-0', $class ) );

	return sprintf(
		'<svg class="%1$s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%2$s</svg>',
		$class_attr,
		$icons[ $name ]
	);
}

/**
 * Account / login page URL.
 *
 * @return string
 */
function diako_get_account_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$url = wc_get_page_permalink( 'myaccount' );
		if ( $url ) {
			return $url;
		}
	}

	return wp_login_url();
}

/**
 * Accessible label for the header account link.
 *
 * @return string
 */
function diako_get_account_link_aria_label() {
	if ( is_user_logged_in() ) {
		return __( 'حساب کاربری', 'diako' );
	}

	return __( 'ورود / ثبت‌نام', 'diako' );
}

/**
 * Lucide icon slug for the header account link.
 *
 * @return string
 */
function diako_get_account_icon_name() {
	return is_user_logged_in() ? 'user' : 'user-key';
}

/**
 * Header account icon link (login, register, or my account).
 *
 * @param string $extra_class Extra button classes.
 * @return void
 */
function diako_render_account_icon_link( $extra_class = '' ) {
	printf(
		'<a href="%1$s" class="%2$s" aria-label="%3$s">%4$s</a>',
		esc_url( diako_get_account_url() ),
		esc_attr( diako_button_classes( 'ghost', 'icon', $extra_class ) ),
		esc_attr( diako_get_account_link_aria_label() ),
		diako_lucide_icon_svg( diako_get_account_icon_name(), 'h-5 w-5' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/**
 * Mobile header login link (hidden on lg+; account icon lives in the mobile menu there).
 *
 * @return void
 */
function diako_render_header_login_link() {
	printf(
		'<a href="%1$s" class="%2$s" aria-label="%3$s">%4$s</a>',
		esc_url( diako_get_account_url() ),
		esc_attr( diako_button_classes( 'ghost', 'icon', 'inline-flex lg:hidden' ) ),
		esc_attr( diako_get_account_link_aria_label() ),
		diako_lucide_icon_svg( diako_get_account_icon_name(), 'h-5 w-5' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/**
 * Social profile links for the footer and other surfaces.
 *
 * @return array<int, array{icon: string, label: string, url: string}>
 */
function diako_get_social_links() {
	$contact      = diako_get_company_contact_details();
	$whatsapp_url = 'https://wa.me/' . preg_replace( '/\D+/', '', $contact['phone_tel'] );

	return apply_filters(
		'diako_social_links',
		array(
			array(
				'icon'  => 'instagram',
				'label' => __( 'اینستاگرام', 'diako' ),
				'url'   => 'https://instagram.com/diako',
			),
			array(
				'icon'  => 'telegram',
				'label' => __( 'تلگرام', 'diako' ),
				'url'   => 'https://t.me/diako',
			),
			array(
				'icon'  => 'whatsapp',
				'label' => __( 'واتساپ', 'diako' ),
				'url'   => $whatsapp_url,
			),
		)
	);
}

/**
 * Render footer social icon links.
 *
 * @param string $class Extra wrapper classes.
 * @return void
 */
function diako_render_social_links( $class = '' ) {
	$links = diako_get_social_links();

	if ( empty( $links ) ) {
		return;
	}

	echo '<div class="' . esc_attr( diako_cn( 'diako-social-links', $class ) ) . '">';

	foreach ( $links as $link ) {
		if ( empty( $link['url'] ) || empty( $link['icon'] ) ) {
			continue;
		}

		printf(
			'<a class="diako-social-links__link" href="%1$s" target="_blank" rel="noopener noreferrer" aria-label="%2$s">%3$s</a>',
			esc_url( $link['url'] ),
			esc_attr( $link['label'] ?? '' ),
			diako_lucide_icon_svg( $link['icon'], 'h-5 w-5' )
		);
	}

	echo '</div>';
}

/**
 * Allowed HTML for e-namad trust seal markup.
 *
 * @return array<string, array<string, bool>>
 */
function diako_get_enamad_allowed_html() {
	return array(
		'a'   => array(
			'href'            => true,
			'target'          => true,
			'rel'             => true,
			'referrerpolicy'  => true,
			'aria-label'      => true,
		),
		'img' => array(
			'src'             => true,
			'alt'             => true,
			'referrerpolicy'  => true,
			'loading'         => true,
			'decoding'        => true,
			'code'            => true,
			'width'           => true,
			'height'          => true,
			'class'           => true,
			'style'           => true,
		),
	);
}

/**
 * Inline Zibal logo SVG markup.
 *
 * @return string
 */
function diako_get_zibal_logo_markup() {
	$path = DIAKO_DIR . '/assets/images/zibal-logo.svg';

	if ( ! is_readable( $path ) ) {
		return '';
	}

	$svg = (string) file_get_contents( $path );

	if ( '' === $svg ) {
		return '';
	}

	$svg = preg_replace(
		'/<svg\b/',
		'<svg class="diako-trust-badges__zibal-logo" role="img" aria-hidden="true" focusable="false"',
		$svg,
		1
	);

	return $svg ? $svg : '';
}

/**
 * Render footer trust badges (e-namad, Zibal).
 *
 * @param string $class Extra wrapper classes.
 * @return void
 */
function diako_render_trust_badges( $class = '' ) {
	$zibal_url  = 'https://zibal.ir';
	$zibal_logo = diako_get_zibal_logo_markup();

	echo '<div class="' . esc_attr( diako_cn( 'diako-trust-badges', $class ) ) . '">';

	echo '<div class="diako-trust-badges__item diako-trust-badges__item--enamad">';
	echo wp_kses(
		"<a referrerpolicy='origin' target='_blank' href='https://trustseal.enamad.ir/?id=708036&Code=MRiodNuFI5Rq5PxUfTuHx4XflePpORjO'><img referrerpolicy='origin' src='https://trustseal.enamad.ir/logo.aspx?id=708036&Code=MRiodNuFI5Rq5PxUfTuHx4XflePpORjO' alt='' style='cursor:pointer' code='MRiodNuFI5Rq5PxUfTuHx4XflePpORjO'></a>",
		diako_get_enamad_allowed_html()
	);
	echo '</div>';

	if ( $zibal_logo ) {
		echo '<div class="diako-trust-badges__item diako-trust-badges__item--zibal">';
		printf(
			'<a class="diako-trust-badges__zibal-link" href="%1$s" target="_blank" rel="noopener noreferrer" aria-label="%2$s">%3$s</a>',
			esc_url( $zibal_url ),
			esc_attr__( 'درگاه پرداخت زیبال', 'diako' ),
			$zibal_logo // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
		echo '</div>';
	}

	echo '</div>';
}

/**
 * Pagination previous link markup with a Lucide chevron.
 *
 * @return string
 */
function diako_pagination_prev_link_content() {
	$icon = is_rtl() ? 'chevron-right' : 'chevron-left';

	return diako_lucide_icon_svg( $icon ) . '<span class="sr-only">' . esc_html__( 'صفحه قبل', 'diako' ) . '</span>';
}

/**
 * Pagination next link markup with a Lucide chevron.
 *
 * @return string
 */
function diako_pagination_next_link_content() {
	$icon = is_rtl() ? 'chevron-left' : 'chevron-right';

	return diako_lucide_icon_svg( $icon ) . '<span class="sr-only">' . esc_html__( 'صفحه بعد', 'diako' ) . '</span>';
}

/**
 * Apply Lucide icons to theme pagination controls.
 *
 * @param array<string, mixed> $args Pagination args.
 * @return array<string, mixed>
 */
function diako_pagination_icon_args( $args ) {
	$args['prev_text'] = diako_pagination_prev_link_content();
	$args['next_text'] = diako_pagination_next_link_content();

	return $args;
}

add_filter( 'the_posts_pagination_args', 'diako_pagination_icon_args' );
add_filter( 'woocommerce_pagination_args', 'diako_pagination_icon_args' );
add_filter( 'comment_pagination_defaults', 'diako_pagination_icon_args' );

/**
 * Star icon SVG markup.
 *
 * @return string
 */
function diako_star_icon_svg() {
	return '<svg class="diako-stars__icon" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/></svg>';
}

/**
 * Format a product rating for display without trailing decimals.
 *
 * @param float|int|string $rating Rating value.
 * @return string
 */
function diako_format_rating_display( $rating ) {
	$value = (float) $rating;

	if ( floor( $value ) === $value ) {
		return (string) (int) $value;
	}

	return rtrim( rtrim( number_format( $value, 2, '.', '' ), '0' ), '.' );
}

/**
 * Render a read-only star rating.
 *
 * @param float|int          $rating Rating value.
 * @param array<string,mixed> $args   Display args.
 * @return string
 */
function diako_render_star_rating( $rating, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'max'   => 5,
			'size'  => 'md',
			'class' => '',
		)
	);

	$rating     = max( 0, min( (float) $rating, (float) $args['max'] ) );
	$size_class = 'diako-stars--' . sanitize_html_class( (string) $args['size'] );
	$display    = diako_format_rating_display( $rating );
	$label      = sprintf(
		/* translators: %s: rating out of 5 */
		__( 'امتیاز %s از ۵', 'diako' ),
		diako_should_use_persian_digits() ? diako_to_persian_digits( $display ) : $display
	);

	$stars = '';
	for ( $index = 1; $index <= (int) $args['max']; $index++ ) {
		$stars .= sprintf(
			'<span class="diako-stars__star%s">%s</span>',
			$index <= (int) round( $rating ) ? ' is-filled' : '',
			diako_star_icon_svg()
		);
	}

	return sprintf(
		'<div class="diako-stars %1$s %2$s" role="img" aria-label="%3$s">%4$s</div>',
		esc_attr( $size_class ),
		esc_attr( $args['class'] ),
		esc_attr( $label ),
		$stars
	);
}

/**
 * Render the interactive review rating field.
 *
 * @param bool $required Whether rating is required.
 * @return string
 */
function diako_render_review_rating_field( $required = true ) {
	if ( ! wc_review_ratings_enabled() ) {
		return '';
	}

	$rating_labels = array(
		5 => __( 'Perfect', 'woocommerce' ),
		4 => __( 'Good', 'woocommerce' ),
		3 => __( 'Average', 'woocommerce' ),
		2 => __( 'Not that bad', 'woocommerce' ),
		1 => __( 'Very poor', 'woocommerce' ),
	);

	$buttons = '';
	for ( $value = 5; $value >= 1; $value-- ) {
		$buttons .= sprintf(
			'<button type="button" class="diako-stars__trigger" data-value="%1$d" role="radio" aria-checked="false" aria-label="%2$s">%3$s</button>',
			$value,
			esc_attr( $rating_labels[ $value ] ),
			diako_star_icon_svg()
		);
	}

	$options = '<option value="">' . esc_html__( 'Rate&hellip;', 'woocommerce' ) . '</option>';
	foreach ( $rating_labels as $value => $label ) {
		$options .= sprintf(
			'<option value="%1$d">%2$s</option>',
			$value,
			esc_html( $label )
		);
	}

	$required_attr = $required && wc_review_ratings_required() ? ' required' : '';

	ob_start();
	?>
	<div class="comment-form-rating diako-product-reviews__field diako-star-rating-field">
		<label class="<?php echo esc_attr( diako_label_classes( 'diako-star-rating-field__label' ) ); ?>" id="diako-rating-label" for="rating">
			<?php echo esc_html__( 'Your rating', 'woocommerce' ); ?>
			<?php if ( $required && wc_review_ratings_required() ) : ?>
				<span class="required">*</span>
			<?php endif; ?>
		</label>
		<div class="diako-star-rating-field__controls">
			<div
				class="diako-stars diako-stars--interactive diako-stars--lg"
				data-star-rating
				dir="ltr"
				role="radiogroup"
				aria-labelledby="diako-rating-label"
				data-rating-labels="<?php echo esc_attr( wp_json_encode( $rating_labels ) ); ?>"
				data-rating-placeholder="<?php echo esc_attr__( 'Rate&hellip;', 'woocommerce' ); ?>"
			>
				<?php echo $buttons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<p class="diako-star-rating-field__hint" data-rating-hint><?php echo esc_html__( 'Rate&hellip;', 'woocommerce' ); ?></p>
		</div>
		<select id="rating" name="rating" class="sr-only" tabindex="-1" aria-hidden="true"<?php echo $required_attr; ?>>
			<?php echo $options; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</select>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Carousel dot navigation container (populated by carousel.js).
 *
 * @return void
 */
function diako_render_carousel_dots() {
	?>
	<div
		class="diako-carousel__dots"
		data-carousel-dots
		role="tablist"
		aria-label="<?php esc_attr_e( 'اسلایدها', 'diako' ); ?>"
	></div>
	<?php
}

/**
 * Carousel prev/next arrows.
 *
 * @param array $args {
 *     @type string $placement `overlay` (hero) or `track` (product/blog rows).
 * }
 * @return void
 */
function diako_render_carousel_arrows( array $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'placement' => 'track',
		)
	);

	$placement = sanitize_html_class( $args['placement'] );
	$prev_icon = is_rtl() ? 'chevron-right' : 'chevron-left';
	$next_icon = is_rtl() ? 'chevron-left' : 'chevron-right';
	?>
	<div
		class="diako-carousel__nav diako-carousel__nav--<?php echo esc_attr( $placement ); ?>"
		role="group"
		aria-label="<?php esc_attr_e( 'کنترل‌های اسلایدر', 'diako' ); ?>"
	>
		<button
			type="button"
			class="diako-carousel__arrow diako-carousel__arrow--prev"
			data-carousel-prev
			aria-label="<?php esc_attr_e( 'قبلی', 'diako' ); ?>"
		>
			<?php echo diako_lucide_icon_svg( $prev_icon, 'size-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
		<button
			type="button"
			class="diako-carousel__arrow diako-carousel__arrow--next"
			data-carousel-next
			aria-label="<?php esc_attr_e( 'بعدی', 'diako' ); ?>"
		>
			<?php echo diako_lucide_icon_svg( $next_icon, 'size-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
	</div>
	<?php
}

/**
 * Render the product gallery thumbnail slider.
 *
 * @param WC_Product $product Product object.
 * @return void
 */
function diako_render_product_gallery_thumbs( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$image_ids = array();

	$featured_id = $product->get_image_id();
	if ( $featured_id ) {
		$image_ids[] = (int) $featured_id;
	}

	foreach ( $product->get_gallery_image_ids() as $attachment_id ) {
		$attachment_id = (int) $attachment_id;

		if ( $attachment_id && ! in_array( $attachment_id, $image_ids, true ) ) {
			$image_ids[] = $attachment_id;
		}
	}

	if ( count( $image_ids ) <= 1 ) {
		return;
	}
	?>
	<div class="diako-gallery-thumbs" data-gallery-thumbs<?php echo is_rtl() ? ' data-rtl="1"' : ''; ?>>
		<?php if ( is_rtl() ) : ?>
			<button type="button" class="diako-gallery-thumbs__nav diako-gallery-thumbs__nav--next" aria-label="<?php esc_attr_e( 'تصاویر بعدی', 'diako' ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
			</button>
			<div class="diako-gallery-thumbs__viewport">
				<div class="diako-gallery-thumbs__track" dir="rtl">
					<?php foreach ( $image_ids as $index => $attachment_id ) : ?>
						<button
							type="button"
							class="diako-gallery-thumbs__item<?php echo 0 === $index ? ' is-active' : ''; ?>"
							data-slide="<?php echo esc_attr( (string) $index ); ?>"
							aria-label="<?php echo esc_attr( sprintf( __( 'نمایش تصویر %d', 'diako' ), $index + 1 ) ); ?>"
						>
							<?php echo wp_get_attachment_image( $attachment_id, 'woocommerce_gallery_thumbnail', false, array( 'class' => 'diako-gallery-thumbs__image' ) ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
			<button type="button" class="diako-gallery-thumbs__nav diako-gallery-thumbs__nav--prev" aria-label="<?php esc_attr_e( 'تصاویر قبلی', 'diako' ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
			</button>
		<?php else : ?>
			<button type="button" class="diako-gallery-thumbs__nav diako-gallery-thumbs__nav--prev" aria-label="<?php esc_attr_e( 'Previous images', 'diako' ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
			</button>
			<div class="diako-gallery-thumbs__viewport">
				<div class="diako-gallery-thumbs__track" dir="ltr">
					<?php foreach ( $image_ids as $index => $attachment_id ) : ?>
						<button
							type="button"
							class="diako-gallery-thumbs__item<?php echo 0 === $index ? ' is-active' : ''; ?>"
							data-slide="<?php echo esc_attr( (string) $index ); ?>"
							aria-label="<?php echo esc_attr( sprintf( __( 'Show image %d', 'diako' ), $index + 1 ) ); ?>"
						>
							<?php echo wp_get_attachment_image( $attachment_id, 'woocommerce_gallery_thumbnail', false, array( 'class' => 'diako-gallery-thumbs__image' ) ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
			<button type="button" class="diako-gallery-thumbs__nav diako-gallery-thumbs__nav--next" aria-label="<?php esc_attr_e( 'Next images', 'diako' ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
			</button>
		<?php endif; ?>
	</div>
	<?php
}
