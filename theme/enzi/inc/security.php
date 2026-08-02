<?php
/**
 * Theme security hardening.
 *
 * @package Diako
 */

defined( 'ABSPATH' ) || exit;

/** Maximum stored newsletter / waitlist email entries. */
const DIAKO_MAX_EMAIL_LIST_ENTRIES = 5000;

/**
 * Resolve client IP for rate limiting (respects common proxy headers).
 *
 * @return string
 */
function diako_get_client_ip(): string {
	$headers = array(
		'HTTP_CF_CONNECTING_IP',
		'HTTP_X_FORWARDED_FOR',
		'REMOTE_ADDR',
	);

	foreach ( $headers as $header ) {
		if ( empty( $_SERVER[ $header ] ) ) {
			continue;
		}

		$value = sanitize_text_field( wp_unslash( (string) $_SERVER[ $header ] ) );

		if ( 'HTTP_X_FORWARDED_FOR' === $header ) {
			$value = trim( explode( ',', $value )[0] );
		}

		if ( filter_var( $value, FILTER_VALIDATE_IP ) ) {
			return $value;
		}
	}

	return '0.0.0.0';
}

/**
 * Check whether a rate limit bucket is exhausted.
 *
 * @param string $action Action identifier.
 * @param int    $max_attempts Maximum attempts within the window.
 * @return bool True when limit is exceeded.
 */
function diako_rate_limit_exceeded( string $action, int $max_attempts ): bool {
	$key = 'diako_rl_' . md5( $action . '|' . diako_get_client_ip() );

	return (int) get_transient( $key ) >= $max_attempts;
}

/**
 * Record one attempt against a rate limit bucket.
 *
 * @param string $action Action identifier.
 * @param int    $window_seconds Window length in seconds.
 * @return void
 */
function diako_rate_limit_hit( string $action, int $window_seconds = 900 ): void {
	$key   = 'diako_rl_' . md5( $action . '|' . diako_get_client_ip() );
	$count = (int) get_transient( $key );

	set_transient( $key, $count + 1, $window_seconds );
}

/**
 * Block the request when the rate limit is exceeded; otherwise record a hit.
 *
 * @param string $action Action identifier.
 * @param int    $max_attempts Maximum attempts within the window.
 * @param int    $window_seconds Window length in seconds.
 * @return bool True when the request should be blocked.
 */
function diako_rate_limit_block( string $action, int $max_attempts = 5, int $window_seconds = 900 ): bool {
	if ( diako_rate_limit_exceeded( $action, $max_attempts ) ) {
		return true;
	}

	diako_rate_limit_hit( $action, $window_seconds );

	return false;
}

/**
 * Send a JSON rate-limit error response and exit.
 *
 * @return void
 */
function diako_rate_limit_json_error(): void {
	wp_send_json_error(
		array(
			'message' => __( 'تعداد درخواست‌ها بیش از حد مجاز است. لطفاً چند دقیقه دیگر تلاش کنید.', 'diako' ),
		),
		429
	);
}

/**
 * Append an email to a capped option-backed subscription list.
 *
 * @param string $option_name Option key.
 * @param string $email       Valid email address.
 * @return true|WP_Error
 */
function diako_append_email_subscription( string $option_name, string $email ) {
	$emails = get_option( $option_name, array() );
	$emails = is_array( $emails ) ? $emails : array();

	$existing = array_map(
		static function ( $row ) {
			return is_array( $row ) ? strtolower( (string) ( $row['email'] ?? '' ) ) : '';
		},
		$emails
	);

	if ( in_array( strtolower( $email ), $existing, true ) ) {
		return true;
	}

	if ( count( $emails ) >= DIAKO_MAX_EMAIL_LIST_ENTRIES ) {
		return new WP_Error(
			'list_full',
			__( 'در حال حاضر امکان ثبت ایمیل جدید وجود ندارد.', 'diako' )
		);
	}

	$emails[] = array(
		'email' => $email,
		'time'  => current_time( 'mysql' ),
	);

	update_option( $option_name, $emails, false );

	return true;
}

/**
 * Sanitize a theme setting URL (relative path or http/https only).
 *
 * @param mixed $url Raw URL value.
 * @return string
 */
function diako_sanitize_settings_url( $url ): string {
	$url = trim( (string) $url );

	if ( '' === $url ) {
		return '';
	}

	if ( preg_match( '#^https?://#i', $url ) ) {
		return (string) esc_url_raw( $url, array( 'http', 'https' ) );
	}

	if ( str_starts_with( $url, '//' ) || preg_match( '#^[a-z][a-z0-9+\-.]*:#i', $url ) ) {
		return '';
	}

	return sanitize_text_field( $url );
}

/**
 * Resolve a theme setting URL for output (blocks protocol-relative URLs).
 *
 * @param string $url URL or path.
 * @return string
 */
function diako_theme_settings_url( $url ) {
	$url = trim( (string) $url );

	if ( '' === $url ) {
		return '';
	}

	if ( str_starts_with( $url, 'http://' ) || str_starts_with( $url, 'https://' ) ) {
		return esc_url( $url );
	}

	if ( str_starts_with( $url, '//' ) || preg_match( '#^[a-z][a-z0-9+\-.]*:#i', $url ) ) {
		return '';
	}

	return home_url( '/' . ltrim( $url, '/' ) );
}

/**
 * Send baseline security headers on the front end.
 *
 * @return void
 */
function diako_send_security_headers(): void {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}

	if ( headers_sent() ) {
		return;
	}

	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
}
add_action( 'send_headers', 'diako_send_security_headers' );

/**
 * Rate-limit WooCommerce order tracking submissions.
 *
 * @return void
 */
function diako_rate_limit_order_tracking(): void {
	if ( empty( $_POST['woocommerce-order-tracking-nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	if ( ! function_exists( 'wc_add_notice' ) ) {
		return;
	}

	if ( ! wp_verify_nonce(
		sanitize_text_field( wp_unslash( $_POST['woocommerce-order-tracking-nonce'] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'woocommerce-order-tracking'
	) ) {
		return;
	}

	if ( ! diako_rate_limit_exceeded( 'order_tracking', 10 ) ) {
		diako_rate_limit_hit( 'order_tracking', 900 );
		return;
	}

	wc_add_notice(
		__( 'تعداد درخواست‌های پیگیری بیش از حد مجاز است. لطفاً چند دقیقه دیگر تلاش کنید.', 'diako' ),
		'error'
	);

	unset( $_POST['orderid'], $_POST['order_email'], $_REQUEST['orderid'], $_REQUEST['order_email'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
}
add_action( 'template_redirect', 'diako_rate_limit_order_tracking', 5 );

/**
 * Disable XML-RPC pingbacks and block user enumeration via author archives.
 *
 * @return void
 */
function diako_harden_public_endpoints(): void {
	if ( is_admin() ) {
		return;
	}

	if ( is_author() && ! is_user_logged_in() ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}
add_action( 'template_redirect', 'diako_harden_public_endpoints', 1 );

/**
 * Remove WordPress version from front-end output.
 *
 * @return void
 */
function diako_remove_version_disclosure(): void {
	remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'init', 'diako_remove_version_disclosure' );

/**
 * Disable XML-RPC when not explicitly enabled via filter.
 *
 * @param bool $enabled Whether XML-RPC is enabled.
 * @return bool
 */
function diako_maybe_disable_xmlrpc( bool $enabled ): bool {
	if ( apply_filters( 'diako_enable_xmlrpc', false ) ) {
		return $enabled;
	}

	return false;
}
add_filter( 'xmlrpc_enabled', 'diako_maybe_disable_xmlrpc' );

/**
 * Block public REST user enumeration endpoints.
 *
 * @param array<string, mixed> $endpoints Registered REST routes.
 * @return array<string, mixed>
 */
function diako_restrict_public_rest_users( array $endpoints ): array {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}

	unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );

	return $endpoints;
}
add_filter( 'rest_endpoints', 'diako_restrict_public_rest_users' );

/**
 * Default security-related theme settings.
 *
 * @return array<string, mixed>
 */
function diako_get_default_security_settings(): array {
	return array(
		'recaptcha_enabled'         => false,
		'recaptcha_site_key'        => '',
		'recaptcha_secret_key'      => '',
		'recaptcha_score_threshold' => 0.5,
	);
}

/**
 * Security settings from theme options.
 *
 * @return array<string, mixed>
 */
function diako_get_security_settings(): array {
	$settings = diako_get_theme_settings();
	$security = isset( $settings['security'] ) && is_array( $settings['security'] )
		? $settings['security']
		: array();

	return wp_parse_args( $security, diako_get_default_security_settings() );
}

/**
 * reCAPTCHA site key (wp-config constant overrides theme settings).
 *
 * @return string
 */
function diako_get_recaptcha_site_key(): string {
	if ( defined( 'DIAKO_RECAPTCHA_SITE_KEY' ) ) {
		return sanitize_text_field( (string) DIAKO_RECAPTCHA_SITE_KEY );
	}

	return sanitize_text_field( (string) ( diako_get_security_settings()['recaptcha_site_key'] ?? '' ) );
}

/**
 * reCAPTCHA secret key (wp-config constant overrides theme settings).
 *
 * @return string
 */
function diako_get_recaptcha_secret_key(): string {
	if ( defined( 'DIAKO_RECAPTCHA_SECRET_KEY' ) ) {
		return sanitize_text_field( (string) DIAKO_RECAPTCHA_SECRET_KEY );
	}

	return sanitize_text_field( (string) ( diako_get_security_settings()['recaptcha_secret_key'] ?? '' ) );
}

/**
 * Whether reCAPTCHA v3 verification is active.
 */
function diako_is_recaptcha_enabled(): bool {
	if ( defined( 'DIAKO_RECAPTCHA_ENABLED' ) ) {
		return (bool) DIAKO_RECAPTCHA_ENABLED;
	}

	$site_key = diako_get_recaptcha_site_key();
	$secret   = diako_get_recaptcha_secret_key();

	if ( '' === $site_key || '' === $secret ) {
		return false;
	}

	if ( defined( 'DIAKO_RECAPTCHA_SITE_KEY' ) && defined( 'DIAKO_RECAPTCHA_SECRET_KEY' ) ) {
		return true;
	}

	return ! empty( diako_get_security_settings()['recaptcha_enabled'] );
}

/**
 * Minimum acceptable reCAPTCHA v3 score.
 */
function diako_get_recaptcha_score_threshold(): float {
	if ( defined( 'DIAKO_RECAPTCHA_SCORE_THRESHOLD' ) ) {
		return max( 0.1, min( 1.0, (float) DIAKO_RECAPTCHA_SCORE_THRESHOLD ) );
	}

	$threshold = (float) ( diako_get_security_settings()['recaptcha_score_threshold'] ?? 0.5 );

	return max( 0.1, min( 1.0, $threshold ) );
}

/**
 * Sanitize security settings on save.
 *
 * @param array<string, mixed> $input    Submitted values.
 * @param array<string, mixed> $existing Existing merged settings.
 * @return array<string, mixed>
 */
function diako_sanitize_security_settings( array $input, array $existing = array() ): array {
	$defaults         = diako_get_default_security_settings();
	$existing_security = wp_parse_args( $existing['security'] ?? array(), $defaults );
	$secret           = sanitize_text_field( $input['recaptcha_secret_key'] ?? '' );

	if ( '' === $secret ) {
		$secret = $existing_security['recaptcha_secret_key'] ?? '';
	}

	return array(
		'recaptcha_enabled'         => ! empty( $input['recaptcha_enabled'] ),
		'recaptcha_site_key'        => sanitize_text_field( $input['recaptcha_site_key'] ?? '' ),
		'recaptcha_secret_key'      => $secret,
		'recaptcha_score_threshold' => max( 0.1, min( 1.0, (float) ( $input['recaptcha_score_threshold'] ?? $defaults['recaptcha_score_threshold'] ) ) ),
	);
}

/**
 * Verify a reCAPTCHA v3 token with Google.
 *
 * @param string $token  Client token.
 * @param string $action Expected action name.
 * @return true|WP_Error
 */
function diako_verify_recaptcha_token( string $token, string $action ) {
	if ( ! diako_is_recaptcha_enabled() ) {
		return true;
	}

	$token = trim( $token );

	if ( '' === $token ) {
		return new WP_Error(
			'recaptcha_missing',
			__( 'تأیید امنیتی انجام نشد. صفحه را بارگذاری مجدد کنید و دوباره تلاش کنید.', 'diako' )
		);
	}

	$response = wp_remote_post(
		'https://www.google.com/recaptcha/api/siteverify',
		array(
			'timeout' => 10,
			'body'    => array(
				'secret'   => diako_get_recaptcha_secret_key(),
				'response' => $token,
				'remoteip' => diako_get_client_ip(),
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_Error(
			'recaptcha_request_failed',
			__( 'تأیید امنیتی در حال حاضر ممکن نیست. لطفاً کمی بعد دوباره تلاش کنید.', 'diako' )
		);
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );

	if ( 200 !== $code || ! is_array( $body ) || empty( $body['success'] ) ) {
		return new WP_Error(
			'recaptcha_failed',
			__( 'تأیید امنیتی ناموفق بود. دوباره تلاش کنید.', 'diako' )
		);
	}

	if ( ! empty( $body['action'] ) && (string) $body['action'] !== $action ) {
		return new WP_Error(
			'recaptcha_action_mismatch',
			__( 'تأیید امنیتی نامعتبر است.', 'diako' )
		);
	}

	$score     = isset( $body['score'] ) ? (float) $body['score'] : 0.0;
	$threshold = (float) apply_filters( 'diako_recaptcha_score_threshold', diako_get_recaptcha_score_threshold() );

	if ( $score < $threshold ) {
		return new WP_Error(
			'recaptcha_low_score',
			__( 'ارسال شما به عنوان فعالیت مشکوک شناسایی شد. لطفاً دوباره تلاش کنید.', 'diako' )
		);
	}

	return true;
}

/**
 * Verify reCAPTCHA for the current AJAX request or send a JSON error.
 *
 * @param string $action Expected action name.
 * @return void
 */
function diako_require_recaptcha_for_ajax( string $action ): void {
	$token  = isset( $_POST['recaptcha_token'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['recaptcha_token'] ) ) : '';
	$result = diako_verify_recaptcha_token( $token, $action );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error(
			array( 'message' => $result->get_error_message() ),
			400
		);
	}
}

/**
 * Whether the current request should load the reCAPTCHA script.
 */
function diako_should_load_recaptcha(): bool {
	if ( ! diako_is_recaptcha_enabled() ) {
		return false;
	}

	if ( function_exists( 'diako_is_contact_page' ) && diako_is_contact_page() ) {
		return true;
	}

	if ( function_exists( 'diako_is_single_post_page' ) && diako_is_single_post_page() ) {
		return true;
	}

	if ( function_exists( 'diako_should_show_coming_soon_page' ) && diako_should_show_coming_soon_page() ) {
		return true;
	}

	return false;
}

/**
 * Enqueue Google reCAPTCHA v3 when needed.
 *
 * @return void
 */
function diako_enqueue_recaptcha_script(): void {
	if ( ! diako_should_load_recaptcha() ) {
		return;
	}

	$site_key = diako_get_recaptcha_site_key();

	if ( '' === $site_key ) {
		return;
	}

	wp_enqueue_script(
		'google-recaptcha-v3',
		'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $site_key ),
		array(),
		null,
		true
	);

	wp_localize_script(
		'diako-main',
		'diakoRecaptcha',
		array(
			'enabled' => true,
			'siteKey' => $site_key,
			'i18n'    => array(
				'unavailable' => __( 'تأیید امنیتی در دسترس نیست. صفحه را بارگذاری مجدد کنید.', 'diako' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'diako_enqueue_recaptcha_script', 15 );

/**
 * Ensure main.js loads after reCAPTCHA when both are enqueued.
 *
 * @return void
 */
function diako_set_recaptcha_script_dependency(): void {
	if ( ! wp_script_is( 'google-recaptcha-v3', 'registered' ) ) {
		return;
	}

	global $wp_scripts;

	if ( isset( $wp_scripts->registered['diako-main'] ) ) {
		$wp_scripts->registered['diako-main']->deps[] = 'google-recaptcha-v3';
	}
}
add_action( 'wp_enqueue_scripts', 'diako_set_recaptcha_script_dependency', 110 );

/**
 * Load reCAPTCHA on the standalone coming soon page.
 *
 * @return void
 */
function diako_print_standalone_recaptcha_assets(): void {
	if ( ! diako_should_load_recaptcha() ) {
		return;
	}

	$site_key = diako_get_recaptcha_site_key();

	if ( '' === $site_key ) {
		return;
	}

	printf(
		'<script src="%1$s" id="google-recaptcha-v3-js"></script>' . "\n",
		esc_url( 'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $site_key ) )
	);

	printf(
		'<script>window.diakoRecaptcha=%1$s;</script>' . "\n",
		wp_json_encode(
			array(
				'enabled' => true,
				'siteKey' => $site_key,
				'i18n'    => array(
					'unavailable' => __( 'تأیید امنیتی در دسترس نیست. صفحه را بارگذاری مجدد کنید.', 'diako' ),
				),
			)
		)
	);
}

/**
 * Render security settings in the theme admin general tab.
 *
 * @param array<string, mixed> $settings Theme settings.
 * @return void
 */
function diako_render_security_general_fields( array $settings ): void {
	$security = wp_parse_args( $settings['security'] ?? array(), diako_get_default_security_settings() );
	?>
	<h2><?php esc_html_e( 'امنیت و reCAPTCHA', 'diako' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'کلیدهای Google reCAPTCHA v3 را از console.cloud.google.com/security/recaptcha وارد کنید. برای فرم تماس و عضویت خبرنامه استفاده می‌شود.', 'diako' ); ?>
	</p>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><?php esc_html_e( 'فعال‌سازی reCAPTCHA', 'diako' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="lastify_settings[security][recaptcha_enabled]" value="1" <?php checked( ! empty( $security['recaptcha_enabled'] ) ); ?>>
					<?php esc_html_e( 'تأیید reCAPTCHA v3 برای فرم‌های عمومی', 'diako' ); ?>
				</label>
			</td>
		</tr>
		<?php
		diako_render_settings_field(
			'lastify_settings[security][recaptcha_site_key]',
			__( 'Site Key', 'diako' ),
			$security['recaptcha_site_key'] ?? ''
		);
		?>
		<tr>
			<th scope="row"><label for="lastify_recaptcha_secret_key"><?php esc_html_e( 'Secret Key', 'diako' ); ?></label></th>
			<td>
				<input
					id="lastify_recaptcha_secret_key"
					class="regular-text"
					type="password"
					name="lastify_settings[security][recaptcha_secret_key]"
					value=""
					autocomplete="new-password"
					placeholder="<?php echo esc_attr( ! empty( $security['recaptcha_secret_key'] ) ? '••••••••••••' : '' ); ?>"
				>
				<p class="description"><?php esc_html_e( 'برای حفظ کلید فعلی، این فیلد را خالی بگذارید.', 'diako' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="lastify_recaptcha_score_threshold"><?php esc_html_e( 'حداقل امتیاز', 'diako' ); ?></label></th>
			<td>
				<input
					id="lastify_recaptcha_score_threshold"
					class="small-text"
					type="number"
					min="0.1"
					max="1"
					step="0.1"
					name="lastify_settings[security][recaptcha_score_threshold]"
					value="<?php echo esc_attr( (string) ( $security['recaptcha_score_threshold'] ?? 0.5 ) ); ?>"
				>
				<p class="description"><?php esc_html_e( 'امتیاز v3 بین ۰.۱ (سخت‌گیرانه‌تر) تا ۱. پیش‌فرض: ۰.۵', 'diako' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}
