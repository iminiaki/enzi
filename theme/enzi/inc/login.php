<?php
/**
 * Branded wp-login screen.
 *
 * @package Diako
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pages that should use the login plugin's default layout and styles.
 */
function diako_is_login_default_layout_page(): bool {
	if ( is_admin() || is_customize_preview() ) {
		return false;
	}

	if ( is_page( 'auth' ) ) {
		return true;
	}

	if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in() ) {
		return true;
	}

	return (bool) apply_filters( 'diako_is_login_default_layout_page', false );
}

/**
 * Whether the storefront theme shell (CSS, header, footer) should load.
 */
function diako_should_load_storefront_theme_assets(): bool {
	if ( function_exists( 'diako_is_login_default_layout_page' ) && diako_is_login_default_layout_page() ) {
		return false;
	}

	return (bool) apply_filters( 'diako_should_load_storefront_theme_assets', true );
}

/**
 * Minimal document shell for login plugins (Voroodak).
 *
 * @return void
 */
function diako_render_login_default_document_start(): void {
	?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?> dir="rtl">
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
	<?php
	wp_body_open();
}

/**
 * Close the login plugin document shell.
 *
 * @return void
 */
function diako_render_login_default_document_end(): void {
	wp_footer();
	echo '</body></html>';
}

/**
 * Load Vazirmatn on Voroodak / guest login pages (no theme CSS shell).
 *
 * @return void
 */
function diako_print_login_default_font_face(): void {
	if ( ! diako_is_login_default_layout_page() ) {
		return;
	}

	if ( function_exists( 'diako_print_critical_font_assets' ) ) {
		diako_print_critical_font_assets();
	}
}
add_action( 'wp_head', 'diako_print_login_default_font_face', 1 );

/**
 * Apply Vazirmatn to every element on the login plugin page.
 *
 * Loaded late so it wins over Voroodak's bundled font rules.
 *
 * @return void
 */
function diako_print_login_default_font_styles(): void {
	if ( ! diako_is_login_default_layout_page() ) {
		return;
	}

	$family = function_exists( 'diako_get_font_family' ) ? diako_get_font_family() : "'Vazirmatn', Tahoma, sans-serif";
	$css    = sprintf(
		'html,html body,html :where(*,:before,:after){font-family:%1$s!important;font-feature-settings:inherit;font-variation-settings:inherit}',
		$family
	);

	printf(
		'<style id="diako-login-default-font">%s</style>' . "\n",
		wp_strip_all_tags( $css )
	);
}
add_action( 'wp_head', 'diako_print_login_default_font_styles', 9999 );
add_action( 'wp_footer', 'diako_print_login_default_font_styles', 9999 );

/**
 * Full design tokens for the login screen (fills gaps in branding CSS vars).
 */
function diako_output_login_design_tokens(): void {
	$css = ':root{
		--card:0 0% 100%;
		--card-foreground:222 22% 12%;
		--muted:220 14% 96%;
		--muted-foreground:220 9% 46%;
		--accent:220 14% 96%;
		--input:220 13% 91%;
		--destructive:0 84% 60%;
		color-scheme:light;
	}
	html.dark{
		--card:222 18% 10%;
		--card-foreground:210 20% 96%;
		--muted:220 14% 16%;
		--muted-foreground:215 14% 62%;
		--accent:220 14% 18%;
		--input:220 14% 18%;
		color-scheme:dark;
	}';

	printf(
		'<style id="diako-login-tokens">%s</style>' . "\n",
		wp_strip_all_tags( $css )
	);
}

/**
 * Branded login screen assets.
 */
function diako_enqueue_login_assets(): void {
	$css_path = DIAKO_DIR . '/assets/css/login.css';
	$js_path  = DIAKO_DIR . '/assets/js/login.js';

	wp_enqueue_style(
		'diako-login',
		DIAKO_URI . '/assets/css/login.css',
		array( 'login', 'diako-vazirmatn' ),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : DIAKO_VERSION
	);

	wp_enqueue_script(
		'diako-login',
		DIAKO_URI . '/assets/js/login.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : DIAKO_VERSION,
		true
	);
}
add_action( 'login_enqueue_scripts', 'diako_enqueue_login_assets', 30 );

/**
 * Output branding CSS variables on the login screen.
 */
function diako_login_branding_css(): void {
	diako_output_login_design_tokens();

	if ( function_exists( 'diako_output_branding_css' ) ) {
		diako_output_branding_css();
	}

	if ( function_exists( 'diako_output_branding_favicon' ) ) {
		diako_output_branding_favicon();
	}
}
add_action( 'login_head', 'diako_login_branding_css', 2 );

/**
 * Apply theme before paint and set RTL on the login screen.
 */
function diako_login_head_setup(): void {
	?>
	<style>html{direction:rtl;}</style>
	<?php

	if ( function_exists( 'diako_theme_bootstrap_script' ) ) {
		diako_theme_bootstrap_script();
	}
}
add_action( 'login_head', 'diako_login_head_setup', 1 );

/**
 * Body classes for the login screen.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function diako_login_body_class( array $classes ): array {
	$classes[] = 'diako-login-page';

	return $classes;
}
add_filter( 'login_body_class', 'diako_login_body_class' );

/**
 * Card header injected via login_message (logo, subtitle, theme toggle).
 */
function diako_render_login_header(): void {
	$logos      = diako_get_branding_logo_urls();
	$light      = $logos['light'] ?: $logos['dark'];
	$dark       = $logos['dark'] ?: $logos['light'];
	$brand      = diako_get_brand_name();
	$same_logo  = $light === $dark;
	$dark_class = 'diako-login-logo diako-login-logo--dark' . ( $same_logo ? ' diako-login-logo--invert-dark' : '' );
	?>
	<div class="diako-login-header">
		<button
			type="button"
			class="diako-login-theme-toggle"
			data-theme-toggle
			aria-label="<?php esc_attr_e( 'تغییر حالت روشن/تاریک', 'diako' ); ?>"
		>
			<svg class="theme-icon theme-icon-sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
			<svg class="theme-icon theme-icon-moon is-hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
		</button>
		<div class="diako-login-header__logo">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $brand ); ?>">
				<img
					class="diako-login-logo diako-login-logo--light"
					src="<?php echo esc_url( $light ); ?>"
					alt="<?php echo esc_attr( $brand ); ?>"
					width="144"
					height="44"
					decoding="async"
				>
				<img
					class="<?php echo esc_attr( $dark_class ); ?>"
					src="<?php echo esc_url( $dark ); ?>"
					alt=""
					width="144"
					height="44"
					decoding="async"
					aria-hidden="true"
				>
			</a>
		</div>
		<p class="diako-login-header__subtitle"><?php echo esc_html( diako_get_login_subtitle() ); ?></p>
	</div>
	<?php
}

/**
 * Subtitle copy for the current login action.
 */
function diako_get_login_subtitle(): string {
	$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( (string) $_GET['action'] ) ) : 'login';

	if ( 'lostpassword' === $action ) {
		return __( 'ایمیل خود را وارد کنید تا لینک بازنشانی ارسال شود.', 'diako' );
	}

	if ( 'register' === $action ) {
		return __( 'برای ایجاد حساب کاربری، اطلاعات زیر را تکمیل کنید.', 'diako' );
	}

	if ( 'rp' === $action || 'resetpass' === $action ) {
		return __( 'رمز عبور جدید خود را وارد کنید.', 'diako' );
	}

	return sprintf(
		/* translators: %s: site name */
		__( 'ورود به پنل مدیریت %s', 'diako' ),
		diako_get_brand_name()
	);
}

/**
 * Prepend the card header via login_message.
 *
 * @param string $message Existing login message HTML.
 */
function diako_prepend_login_header( string $message ): string {
	ob_start();
	diako_render_login_header();

	return ob_get_clean() . $message;
}
add_filter( 'login_message', 'diako_prepend_login_header' );

/**
 * Customize the back-to-store link inside the card.
 *
 * @param string $link Default link HTML.
 */
function diako_login_site_link( string $link ): string {
	return sprintf(
		'<a href="%s">%s</a>',
		esc_url( home_url( '/' ) ),
		esc_html__( 'بازگشت به فروشگاه', 'diako' )
	);
}
add_filter( 'login_site_html_link', 'diako_login_site_link' );

/**
 * Login page document title.
 *
 * @param string $login_title Page title.
 * @param string $site_name   Site name.
 */
function diako_filter_login_title( string $login_title, string $site_name ): string {
	unset( $login_title, $site_name );

	return sprintf(
		/* translators: %s: site name */
		__( 'ورود — %s', 'diako' ),
		diako_get_brand_name()
	);
}
add_filter( 'login_title', 'diako_filter_login_title', 10, 2 );

/**
 * Logo link target on the login screen.
 */
function diako_login_header_url(): string {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'diako_login_header_url' );

/**
 * Logo link label on the login screen.
 */
function diako_login_header_text(): string {
	return diako_get_brand_name();
}
add_filter( 'login_headertext', 'diako_login_header_text' );
