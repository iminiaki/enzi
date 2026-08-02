<?php
/**
 * Site-wide coming soon / maintenance page.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DIAKO_COMING_SOON_EMAILS_OPTION', 'diako_coming_soon_emails' );

/**
 * Coming soon page settings from theme options.
 *
 * @return array<string, mixed>
 */
function diako_get_coming_soon_page_settings(): array {
	$defaults = diako_get_default_theme_settings()['coming_soon_page'] ?? array();
	$settings = diako_get_theme_settings();
	$page     = isset( $settings['coming_soon_page'] ) && is_array( $settings['coming_soon_page'] )
		? wp_parse_args( $settings['coming_soon_page'], $defaults )
		: $defaults;

	return $page;
}

/**
 * Whether the site-wide coming soon page is active.
 */
function diako_is_coming_soon_page_enabled(): bool {
	return ! empty( diako_get_coming_soon_page_settings()['enabled'] );
}

/**
 * Whether the current request should bypass the coming soon page.
 */
function diako_is_coming_soon_bypass_request(): bool {
	global $pagenow;

	$allowed_scripts = array( 'wp-login.php', 'wp-signup.php', 'wp-activate.php' );

	if ( isset( $pagenow ) && in_array( $pagenow, $allowed_scripts, true ) ) {
		return true;
	}

	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		return true;
	}

	return false;
}

/**
 * Whether visitors should see the coming soon page.
 */
function diako_should_show_coming_soon_page(): bool {
	if ( ! diako_is_coming_soon_page_enabled() ) {
		return false;
	}

	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return false;
	}

	if ( diako_is_coming_soon_bypass_request() ) {
		return false;
	}

	return true;
}

/**
 * Format stored launch date for datetime-local input.
 *
 * @param string $value Stored launch date.
 * @return string
 */
function diako_format_coming_soon_launch_date_for_input( string $value ): string {
	$timestamp = diako_get_coming_soon_launch_timestamp( array( 'launch_date' => $value ) );

	if ( null === $timestamp ) {
		return '';
	}

	return wp_date( 'Y-m-d\TH:i', $timestamp );
}

/**
 * Parse launch date from settings.
 *
 * @param array<string, mixed> $settings Page settings.
 * @return int|null Unix timestamp in site timezone, or null.
 */
function diako_get_coming_soon_launch_timestamp( array $settings ): ?int {
	$raw = trim( (string) ( $settings['launch_date'] ?? '' ) );

	if ( '' === $raw ) {
		return null;
	}

	$timestamp = strtotime( $raw );

	if ( false === $timestamp ) {
		return null;
	}

	return $timestamp;
}

/**
 * Intercept front-end requests when coming soon mode is enabled.
 */
function diako_maybe_render_coming_soon_page(): void {
	if ( ! diako_should_show_coming_soon_page() ) {
		return;
	}

	diako_render_coming_soon_page();
	exit;
}
add_action( 'template_redirect', 'diako_maybe_render_coming_soon_page', 0 );

/**
 * Output the standalone coming soon page.
 */
function diako_render_coming_soon_page(): void {
	$settings  = diako_get_coming_soon_page_settings();
	$launch_ts = diako_get_coming_soon_launch_timestamp( $settings );
	$show_cd   = ! empty( $settings['show_countdown'] ) && null !== $launch_ts && $launch_ts > time();

	nocache_headers();

	if ( null !== $launch_ts && $launch_ts > time() ) {
		status_header( 503 );
		header( 'Retry-After: ' . max( 60, $launch_ts - time() ) );
	} else {
		status_header( 503 );
	}

	$js_path    = DIAKO_DIR . '/assets/js/main.js';
	$js_version = file_exists( $js_path ) ? (string) filemtime( $js_path ) : DIAKO_VERSION;

	$title = trim( (string) ( $settings['title'] ?? '' ) );
	if ( '' === $title ) {
		$title = __( 'به زودی برمی‌گردیم', 'diako' );
	}

	?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?> dir="rtl">
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex, nofollow">
		<title><?php echo esc_html( $title . ' — ' . diako_get_brand_name() ); ?></title>
		<?php diako_theme_bootstrap_script(); ?>
		<?php diako_print_standalone_head_assets(); ?>
		<?php diako_print_standalone_recaptcha_assets(); ?>
		<script src="<?php echo esc_url( DIAKO_URI . '/assets/js/main.js' ); ?>?ver=<?php echo esc_attr( $js_version ); ?>" defer></script>
	</head>
	<body class="diako-coming-soon min-h-screen bg-background font-sans text-foreground antialiased">
		<?php get_template_part( 'template-parts/coming-soon/page', null, compact( 'settings', 'launch_ts', 'show_cd' ) ); ?>
	</body>
	</html>
	<?php
}

/**
 * AJAX: save newsletter email for launch notification.
 */
function diako_ajax_coming_soon_subscribe(): void {
	check_ajax_referer( 'diako_coming_soon_subscribe', 'nonce' );
	diako_require_recaptcha_for_ajax( 'newsletter_subscribe' );

	if ( diako_rate_limit_block( 'coming_soon_subscribe', 3, 900 ) ) {
		diako_rate_limit_json_error();
	}

	if ( ! diako_is_coming_soon_page_enabled() ) {
		wp_send_json_error(
			array( 'message' => __( 'ثبت‌نام در حال حاضر غیرفعال است.', 'diako' ) ),
			403
		);
	}

	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );

	if ( ! is_email( $email ) ) {
		wp_send_json_error(
			array( 'message' => __( 'لطفاً یک ایمیل معتبر وارد کنید.', 'diako' ) ),
			400
		);
	}

	$emails   = get_option( DIAKO_COMING_SOON_EMAILS_OPTION, array() );
	$emails   = is_array( $emails ) ? $emails : array();
	$existing = array_map(
		static function ( $row ) {
			return is_array( $row ) ? strtolower( (string) ( $row['email'] ?? '' ) ) : '';
		},
		$emails
	);

	if ( in_array( strtolower( $email ), $existing, true ) ) {
		wp_send_json_success(
			array( 'message' => __( 'ایمیل شما قبلاً ثبت شده است.', 'diako' ) )
		);
	}

	$added = diako_append_email_subscription( DIAKO_COMING_SOON_EMAILS_OPTION, $email );

	if ( is_wp_error( $added ) ) {
		wp_send_json_error(
			array( 'message' => $added->get_error_message() ),
			503
		);
	}

	wp_send_json_success(
		array( 'message' => __( 'ایمیل شما ثبت شد. به محض راه‌اندازی به شما اطلاع می‌دهیم.', 'diako' ) )
	);
}
add_action( 'wp_ajax_nopriv_diako_coming_soon_subscribe', 'diako_ajax_coming_soon_subscribe' );
add_action( 'wp_ajax_diako_coming_soon_subscribe', 'diako_ajax_coming_soon_subscribe' );

/**
 * Admin notice when coming soon mode is active.
 */
function diako_coming_soon_admin_notice(): void {
	if ( ! current_user_can( 'manage_options' ) || ! diako_is_coming_soon_page_enabled() ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || 'toplevel_page_lastify' === $screen->id ) {
		return;
	}

	$settings_url = admin_url( 'admin.php?page=lastify&tab=general' );
	?>
	<div class="notice notice-warning">
		<p>
			<?php
			printf(
				/* translators: %s: theme settings URL */
				wp_kses_post( __( 'حالت «به زودی» فعال است و بازدیدکنندگان سایت صفحه انتظار را می‌بینند. <a href="%s">تنظیمات قالب</a>', 'diako' ) ),
				esc_url( $settings_url )
			);
			?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'diako_coming_soon_admin_notice' );

/**
 * Render coming soon page fields on the General settings tab.
 *
 * @param array<string, mixed> $settings Theme settings.
 */
function diako_render_coming_soon_page_settings_fields( array $settings ): void {
	$page = wp_parse_args(
		$settings['coming_soon_page'] ?? array(),
		diako_get_default_theme_settings()['coming_soon_page']
	);
	?>
	<h2><?php esc_html_e( 'صفحه «به زودی» (کل سایت)', 'diako' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'با فعال‌سازی، همه صفحات سایت برای بازدیدکنان با یک صفحه انتظار جایگزین می‌شود. مدیران واردشده همچنان سایت را می‌بینند.', 'diako' ); ?>
	</p>
	<?php diako_render_settings_toggle( 'lastify_settings[coming_soon_page][enabled]', $page['enabled'], __( 'فعال‌سازی صفحه به زودی', 'diako' ) ); ?>
	<table class="form-table" role="presentation">
		<?php
		diako_render_settings_field( 'lastify_settings[coming_soon_page][title]', __( 'عنوان', 'diako' ), $page['title'] );
		diako_render_settings_textarea( 'lastify_settings[coming_soon_page][description]', __( 'توضیحات', 'diako' ), $page['description'] );
		?>
	</table>
	<?php diako_render_settings_toggle( 'lastify_settings[coming_soon_page][show_countdown]', $page['show_countdown'], __( 'نمایش شمارش معکوس', 'diako' ) ); ?>
	<table class="form-table" role="presentation">
		<?php
		diako_render_settings_field(
			'lastify_settings[coming_soon_page][launch_date]',
			__( 'تاریخ راه‌اندازی', 'diako' ),
			diako_format_coming_soon_launch_date_for_input( (string) $page['launch_date'] ),
			'datetime-local'
		);
		?>
	</table>
	<?php diako_render_settings_toggle( 'lastify_settings[coming_soon_page][show_newsletter]', $page['show_newsletter'], __( 'نمایش فرم ایمیل', 'diako' ) ); ?>
	<table class="form-table" role="presentation">
		<?php
		diako_render_settings_field( 'lastify_settings[coming_soon_page][newsletter_label]', __( 'متن راهنمای ایمیل', 'diako' ), $page['newsletter_label'] );
		?>
		<tr>
			<th scope="row"><?php esc_html_e( 'ثبت‌شده‌ها', 'diako' ); ?></th>
			<td>
				<?php
				$emails = get_option( DIAKO_COMING_SOON_EMAILS_OPTION, array() );
				$count  = is_array( $emails ) ? count( $emails ) : 0;
				echo esc_html(
					sprintf(
						/* translators: %d: subscriber count */
						_n( '%d ایمیل ثبت شده', '%d ایمیل ثبت شده', $count, 'diako' ),
						$count
					)
				);
				?>
			</td>
		</tr>
	</table>
	<?php diako_render_settings_toggle( 'lastify_settings[coming_soon_page][show_contact]', $page['show_contact'], __( 'نمایش اطلاعات تماس', 'diako' ) ); ?>
	<?php
}
