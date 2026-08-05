<?php
/**
 * Contact page.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DIAKO_CONTACT_INQUIRY_POST_TYPE', 'diako_contact_inquiry' );

/**
 * Contact page ID.
 *
 * @return int
 */
function diako_get_contact_page_id(): int {
	static $cached = null;

	if ( null !== $cached ) {
		return (int) $cached;
	}

	$page_id = (int) get_option( 'diako_contact_page_id', 0 );

	if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
		$cached = $page_id;
		return (int) $cached;
	}

	$pages = get_pages(
		array(
			'meta_key'   => '_wp_page_template',
			'meta_value' => 'page-contact.php',
			'number'     => 1,
		)
	);

	$cached = $pages ? (int) $pages[0]->ID : 0;

	if ( $cached ) {
		update_option( 'diako_contact_page_id', $cached );
	}

	return (int) $cached;
}

/**
 * Contact page URL.
 *
 * @return string
 */
function diako_get_contact_url(): string {
	$page_id = diako_get_contact_page_id();

	if ( $page_id ) {
		return get_permalink( $page_id );
	}

	return home_url( '/contact/' );
}

/**
 * Whether the current page is the contact page.
 *
 * @return bool
 */
function diako_is_contact_page(): bool {
	return is_page() && is_page_template( 'page-contact.php' );
}

/**
 * Extra contact page copy and settings.
 *
 * @return array<string, string>
 */
function diako_get_contact_page_info(): array {
	$contact = function_exists( 'diako_get_company_contact_details' ) ? diako_get_company_contact_details() : array();
	$defaults = array(
		'hours'         => (string) ( $contact['hours'] ?? __( 'شنبه تا پنج‌شنبه: ۱۰:۰۰ تا ۲۰:۰۰', 'diako' ) ),
		'response_time' => (string) ( $contact['response_time'] ?? __( 'معمولاً در کمتر از ۲۴ ساعت پاسخ می‌دهیم.', 'diako' ) ),
		'map_url'       => (string) ( $contact['map_url'] ?? ( 'https://maps.google.com/?q=' . rawurlencode( 'تهران، بلوار میرداماد، پاساژ پایتخت' ) ) ),
	);

	return apply_filters( 'diako_contact_page_info', $defaults );
}

/**
 * Subject options for the contact form.
 *
 * @return array<string, string>
 */
function diako_get_contact_form_subjects(): array {
	return apply_filters(
		'diako_contact_form_subjects',
		array(
			'general'     => __( 'سؤال عمومی', 'diako' ),
			'order'       => __( 'پیگیری سفارش', 'diako' ),
			'product'     => __( 'مشاوره خرید محصول', 'diako' ),
			'technical'   => __( 'پشتیبانی فنی', 'diako' ),
			'partnership' => __( 'همکاری و فروش عمده', 'diako' ),
		)
	);
}

/**
 * Register contact inquiry post type.
 */
function diako_register_contact_inquiry_post_type(): void {
	register_post_type(
		DIAKO_CONTACT_INQUIRY_POST_TYPE,
		array(
			'labels'              => array(
				'name'          => __( 'پیام‌های تماس', 'diako' ),
				'singular_name' => __( 'پیام تماس', 'diako' ),
				'menu_name'     => __( 'پیام‌های تماس', 'diako' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'capability_type'     => 'diako_contact_inquiry',
			'capabilities'        => array(
				'edit_post'              => 'manage_options',
				'read_post'              => 'manage_options',
				'delete_post'            => 'manage_options',
				'edit_posts'             => 'manage_options',
				'edit_others_posts'      => 'manage_options',
				'publish_posts'          => 'manage_options',
				'read_private_posts'     => 'manage_options',
				'create_posts'           => 'manage_options',
				'delete_posts'           => 'manage_options',
				'delete_private_posts'   => 'manage_options',
				'delete_published_posts' => 'manage_options',
				'delete_others_posts'    => 'manage_options',
				'edit_private_posts'     => 'manage_options',
				'edit_published_posts'   => 'manage_options',
			),
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => array( 'title', 'editor' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-email-alt',
		)
	);
}
add_action( 'init', 'diako_register_contact_inquiry_post_type' );

/**
 * Create or repair the contact page.
 *
 * @return int
 */
function diako_ensure_contact_page(): int {
	$page_id = diako_get_contact_page_id();

	if ( $page_id ) {
		if ( 'page-contact.php' !== get_page_template_slug( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', 'page-contact.php' );
		}

		return $page_id;
	}

	$page = get_page_by_path( 'contact' );

	if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
		$page_id = (int) $page->ID;
		update_post_meta( $page_id, '_wp_page_template', 'page-contact.php' );
	} else {
		$page_id = wp_insert_post(
			array(
				'post_title'   => __( 'تماس با ما', 'diako' ),
				'post_name'    => 'contact',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			),
			true
		);

		if ( is_wp_error( $page_id ) || ! $page_id ) {
			return 0;
		}

		$page_id = (int) $page_id;
		update_post_meta( $page_id, '_wp_page_template', 'page-contact.php' );
	}

	update_option( 'diako_contact_page_id', $page_id );

	return $page_id;
}

add_action(
	'after_setup_theme',
	function () {
		if ( get_option( 'diako_contact_page_bootstrapped' ) ) {
			return;
		}

		diako_ensure_contact_page();
		update_option( 'diako_contact_page_bootstrapped', 1 );
	},
	100
);

/**
 * Validate contact form submission.
 *
 * @param array<string, mixed> $data Raw form data.
 * @return array<string, mixed>|WP_Error
 */
function diako_validate_contact_form( array $data ) {
	$name    = sanitize_text_field( $data['name'] ?? '' );
	$email   = sanitize_email( $data['email'] ?? '' );
	$phone   = sanitize_text_field( $data['phone'] ?? '' );
	$subject = sanitize_key( $data['subject'] ?? '' );
	$message = sanitize_textarea_field( $data['message'] ?? '' );
	$subjects = diako_get_contact_form_subjects();

	if ( '' === $name ) {
		return new WP_Error( 'empty_name', __( 'نام خود را وارد کنید.', 'diako' ) );
	}

	if ( ! is_email( $email ) ) {
		return new WP_Error( 'invalid_email', __( 'ایمیل معتبر وارد کنید.', 'diako' ) );
	}

	if ( ! isset( $subjects[ $subject ] ) ) {
		return new WP_Error( 'invalid_subject', __( 'موضوع پیام را انتخاب کنید.', 'diako' ) );
	}

	if ( '' === $message ) {
		return new WP_Error( 'empty_message', __( 'متن پیام را وارد کنید.', 'diako' ) );
	}

	if ( mb_strlen( $message ) < 10 ) {
		return new WP_Error( 'short_message', __( 'پیام باید حداقل ۱۰ کاراکتر باشد.', 'diako' ) );
	}

	return array(
		'name'    => $name,
		'email'   => $email,
		'phone'   => $phone,
		'subject' => $subject,
		'message' => $message,
	);
}

/**
 * Save a contact inquiry and notify admin.
 *
 * @param array<string, string> $data Validated form data.
 * @return int|WP_Error
 */
function diako_create_contact_inquiry( array $data ) {
	$subjects     = diako_get_contact_form_subjects();
	$subject_label = $subjects[ $data['subject'] ] ?? $data['subject'];
	$title        = sprintf(
		/* translators: 1: subject label, 2: sender name */
		__( '%1$s — %2$s', 'diako' ),
		$subject_label,
		$data['name']
	);

	$post_id = wp_insert_post(
		array(
			'post_type'    => DIAKO_CONTACT_INQUIRY_POST_TYPE,
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_content' => $data['message'],
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	update_post_meta( $post_id, '_diako_contact_name', $data['name'] );
	update_post_meta( $post_id, '_diako_contact_email', $data['email'] );
	update_post_meta( $post_id, '_diako_contact_phone', $data['phone'] );
	update_post_meta( $post_id, '_diako_contact_subject', $data['subject'] );
	update_post_meta( $post_id, '_diako_contact_status', 'new' );

	$admin_email = get_option( 'admin_email' );
	$site_name   = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

	$body = sprintf(
		"%s\n\n%s: %s\n%s: %s\n%s: %s\n%s: %s\n\n%s:\n%s",
		__( 'پیام جدید از فرم تماس:', 'diako' ),
		__( 'نام', 'diako' ),
		$data['name'],
		__( 'ایمیل', 'diako' ),
		$data['email'],
		__( 'تلفن', 'diako' ),
		$data['phone'] ?: '-',
		__( 'موضوع', 'diako' ),
		$subject_label,
		__( 'پیام', 'diako' ),
		$data['message']
	);

	wp_mail(
		$admin_email,
		sprintf(
			/* translators: 1: site name, 2: subject label */
			__( '[%1$s] پیام تماس: %2$s', 'diako' ),
			$site_name,
			$subject_label
		),
		$body,
		array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . sanitize_email( $data['email'] ),
		)
	);

	return (int) $post_id;
}

/**
 * AJAX: submit contact form.
 */
function diako_ajax_contact_form_submit(): void {
	check_ajax_referer( 'diako_contact_form', 'nonce' );
	diako_require_recaptcha_for_ajax( 'contact_form' );

	if ( diako_rate_limit_block( 'contact_form', 3, 900 ) ) {
		diako_rate_limit_json_error();
	}

	if ( ! empty( $_POST['company'] ) ) {
		wp_send_json_error(
			array( 'message' => __( 'ارسال پیام انجام نشد.', 'diako' ) ),
			400
		);
	}

	$result = diako_validate_contact_form(
		array(
			'name'    => wp_unslash( $_POST['name'] ?? '' ),
			'email'   => wp_unslash( $_POST['email'] ?? '' ),
			'phone'   => wp_unslash( $_POST['phone'] ?? '' ),
			'subject' => wp_unslash( $_POST['subject'] ?? '' ),
			'message' => wp_unslash( $_POST['message'] ?? '' ),
		)
	);

	if ( is_wp_error( $result ) ) {
		wp_send_json_error(
			array( 'message' => $result->get_error_message() ),
			400
		);
	}

	$created = diako_create_contact_inquiry( $result );

	if ( is_wp_error( $created ) ) {
		wp_send_json_error(
			array( 'message' => __( 'خطا در ثبت پیام. لطفاً دوباره تلاش کنید.', 'diako' ) ),
			500
		);
	}

	wp_send_json_success(
		array(
			'message' => __( 'پیام شما ثبت شد. به زودی با شما تماس می‌گیریم.', 'diako' ),
		)
	);
}
add_action( 'wp_ajax_nopriv_diako_contact_form_submit', 'diako_ajax_contact_form_submit' );
add_action( 'wp_ajax_diako_contact_form_submit', 'diako_ajax_contact_form_submit' );

/**
 * Admin list columns for contact inquiries.
 *
 * @param array<string, string> $columns Columns.
 * @return array<string, string>
 */
function diako_contact_inquiry_columns( array $columns ): array {
	return array(
		'cb'            => $columns['cb'] ?? '',
		'title'         => __( 'موضوع', 'diako' ),
		'diako_contact' => __( 'فرستنده', 'diako' ),
		'date'          => __( 'تاریخ', 'diako' ),
	);
}
add_filter( 'manage_' . DIAKO_CONTACT_INQUIRY_POST_TYPE . '_posts_columns', 'diako_contact_inquiry_columns' );

/**
 * Render admin list column content.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function diako_contact_inquiry_column_content( string $column, int $post_id ): void {
	if ( 'diako_contact' !== $column ) {
		return;
	}

	$name  = (string) get_post_meta( $post_id, '_diako_contact_name', true );
	$email = (string) get_post_meta( $post_id, '_diako_contact_email', true );
	$phone = (string) get_post_meta( $post_id, '_diako_contact_phone', true );

	echo esc_html( $name );
	if ( $email ) {
		echo '<br><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
	}
	if ( $phone ) {
		echo '<br><span dir="ltr" class="diako-phone-ltr">' . esc_html( diako_to_persian_digits( diako_to_western_digits( $phone ) ) ) . '</span>';
	}
}
add_action( 'manage_' . DIAKO_CONTACT_INQUIRY_POST_TYPE . '_posts_custom_column', 'diako_contact_inquiry_column_content', 10, 2 );

/**
 * Localize contact form script.
 */
function diako_localize_contact_form_script(): void {
	if ( ! diako_is_contact_page() ) {
		return;
	}

	wp_localize_script(
		'diako-main',
		'diakoContactForm',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'diako_contact_form' ),
			'i18n'    => array(
				'sending'     => __( 'در حال ارسال…', 'diako' ),
				'submit'      => __( 'ارسال پیام', 'diako' ),
				'submitError' => __( 'خطا در ارسال پیام. دوباره تلاش کنید.', 'diako' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'diako_localize_contact_form_script', 120 );

/**
 * Render the contact page.
 */
function diako_render_contact_page(): void {
	$contact     = diako_get_company_contact_details();
	$info        = diako_get_contact_page_info();
	$subjects    = diako_get_contact_form_subjects();
	$social      = diako_get_social_links();
	$track_url   = function_exists( 'diako_get_track_order_url' ) ? diako_get_track_order_url() : home_url( '/track-order/' );
	$terms_url   = function_exists( 'diako_get_terms_url' ) ? diako_get_terms_url() : home_url( '/terms/' );
	$shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$mag_url     = function_exists( 'diako_get_mag_url' ) ? diako_get_mag_url() : home_url( '/mag/' );
	$whatsapp    = 'https://wa.me/' . preg_replace( '/\D+/', '', $contact['phone_tel'] );
	?>
	<div class="diako-page diako-contact-page">
		<header class="diako-contact-page__hero">
			<div class="diako-contact-page__hero-icon" aria-hidden="true">
				<?php echo diako_lucide_icon_svg( 'headphones', 'h-7 w-7' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<div class="diako-contact-page__hero-content">
				<h1 class="diako-page-title"><?php esc_html_e( 'تماس با ما', 'diako' ); ?></h1>
				<p class="diako-page-desc">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: store brand name */
							__( 'سؤالی درباره سفارش، محصول یا همکاری دارید؟ تیم پشتیبانی %s آماده کمک به شماست.', 'diako' ),
							diako_get_brand_name()
						)
					);
					?>
				</p>
				<p class="diako-contact-page__response"><?php echo esc_html( $info['response_time'] ); ?></p>
			</div>
			<div class="diako-contact-page__hero-actions">
				<?php
				diako_button(
					array(
						'href'       => 'tel:' . $contact['phone_tel'],
						'label'      => __( 'تماس تلفنی', 'diako' ),
						'variant'    => 'default',
						'icon'       => 'phone',
						'icon_class' => 'h-4 w-4',
					)
				);
				diako_button(
					array(
						'href'       => $whatsapp,
						'label'      => __( 'واتساپ', 'diako' ),
						'variant'    => 'outline',
						'icon'       => 'whatsapp',
						'icon_class' => 'h-4 w-4',
					)
				);
				?>
			</div>
		</header>

		<div class="diako-contact-page__layout">
			<div class="diako-contact-page__main">
				<div class="<?php echo esc_attr( diako_card_classes( 'diako-contact-form-card' ) ); ?>">
					<div class="diako-contact-form-card__head">
						<h2 class="diako-contact-form-card__title"><?php esc_html_e( 'ارسال پیام', 'diako' ); ?></h2>
						<p class="diako-contact-form-card__desc"><?php esc_html_e( 'فرم زیر را پر کنید تا در اسرع وقت پاسخ دهیم.', 'diako' ); ?></p>
					</div>

					<form class="diako-contact-form" data-contact-form novalidate>
						<div class="diako-contact-form__grid">
							<div class="diako-contact-form__field">
								<label class="<?php echo esc_attr( diako_label_classes() ); ?>" for="diako-contact-name"><?php esc_html_e( 'نام و نام خانوادگی', 'diako' ); ?></label>
								<input id="diako-contact-name" class="<?php echo esc_attr( diako_input_classes() ); ?>" type="text" name="name" required autocomplete="name">
							</div>

							<div class="diako-contact-form__field">
								<label class="<?php echo esc_attr( diako_label_classes() ); ?>" for="diako-contact-email"><?php esc_html_e( 'ایمیل', 'diako' ); ?></label>
								<input id="diako-contact-email" class="<?php echo esc_attr( diako_input_classes() ); ?>" type="email" name="email" required autocomplete="email" dir="ltr">
							</div>

							<div class="diako-contact-form__field">
								<label class="<?php echo esc_attr( diako_label_classes() ); ?>" for="diako-contact-phone"><?php esc_html_e( 'شماره تماس (اختیاری)', 'diako' ); ?></label>
								<input id="diako-contact-phone" class="<?php echo esc_attr( diako_input_classes( 'diako-phone-ltr' ) ); ?>" type="tel" name="phone" autocomplete="tel" inputmode="tel" dir="ltr">
							</div>

							<div class="diako-contact-form__field">
								<label class="<?php echo esc_attr( diako_label_classes() ); ?>" for="diako-contact-subject"><?php esc_html_e( 'موضوع', 'diako' ); ?></label>
								<select id="diako-contact-subject" class="<?php echo esc_attr( diako_input_classes() ); ?>" name="subject" required>
									<option value=""><?php esc_html_e( 'انتخاب کنید…', 'diako' ); ?></option>
									<?php foreach ( $subjects as $value => $label ) : ?>
										<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="diako-contact-form__field diako-contact-form__field--full">
								<label class="<?php echo esc_attr( diako_label_classes() ); ?>" for="diako-contact-message"><?php esc_html_e( 'پیام شما', 'diako' ); ?></label>
								<textarea id="diako-contact-message" class="<?php echo esc_attr( diako_textarea_classes( 'min-h-[160px]' ) ); ?>" name="message" rows="6" required></textarea>
							</div>
						</div>

						<input type="text" name="company" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

						<div class="diako-contact-form__actions">
							<button type="submit" class="<?php echo esc_attr( diako_button_classes( 'default', 'lg', 'diako-contact-form__submit' ) ); ?>" data-contact-submit>
								<?php echo diako_lucide_icon_svg( 'mail', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span><?php esc_html_e( 'ارسال پیام', 'diako' ); ?></span>
							</button>
							<p class="diako-contact-form__notice" data-contact-notice hidden role="status" aria-live="polite"></p>
						</div>
					</form>
				</div>
			</div>

			<aside class="diako-contact-page__aside">
				<div class="<?php echo esc_attr( diako_card_classes( 'diako-contact-info' ) ); ?>">
					<h2 class="diako-contact-info__title"><?php esc_html_e( 'راه‌های ارتباطی', 'diako' ); ?></h2>
					<ul class="diako-contact-info__list">
						<li class="diako-contact-info__item">
							<span class="diako-contact-info__icon" aria-hidden="true">
								<?php echo diako_lucide_icon_svg( 'phone', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
							<div>
								<p class="diako-contact-info__label"><?php esc_html_e( 'تلفن پشتیبانی', 'diako' ); ?></p>
								<?php diako_render_company_phone_link( 'diako-contact-info__value' ); ?>
							</div>
						</li>
						<li class="diako-contact-info__item">
							<span class="diako-contact-info__icon" aria-hidden="true">
								<?php echo diako_lucide_icon_svg( 'mail', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
							<div>
								<p class="diako-contact-info__label"><?php esc_html_e( 'ایمیل', 'diako' ); ?></p>
								<a class="diako-contact-info__value" href="mailto:<?php echo esc_attr( $contact['email'] ); ?>" dir="ltr"><?php echo esc_html( $contact['email'] ); ?></a>
							</div>
						</li>
						<li class="diako-contact-info__item">
							<span class="diako-contact-info__icon" aria-hidden="true">
								<?php echo diako_lucide_icon_svg( 'map-pin', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
							<div>
								<p class="diako-contact-info__label"><?php esc_html_e( 'آدرس', 'diako' ); ?></p>
								<p class="diako-contact-info__value"><?php echo esc_html( $contact['address'] ); ?></p>
							</div>
						</li>
						<li class="diako-contact-info__item">
							<span class="diako-contact-info__icon" aria-hidden="true">
								<?php echo diako_lucide_icon_svg( 'clock', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
							<div>
								<p class="diako-contact-info__label"><?php esc_html_e( 'ساعات پاسخگویی', 'diako' ); ?></p>
								<p class="diako-contact-info__value"><?php echo esc_html( $info['hours'] ); ?></p>
							</div>
						</li>
					</ul>
				</div>

				<?php if ( ! empty( $social ) ) : ?>
					<div class="<?php echo esc_attr( diako_card_classes( 'diako-contact-social' ) ); ?>">
						<h2 class="diako-contact-social__title"><?php esc_html_e( 'شبکه‌های اجتماعی', 'diako' ); ?></h2>
						<div class="diako-social-links">
							<?php foreach ( $social as $link ) : ?>
								<a
									class="diako-social-links__link"
									href="<?php echo esc_url( $link['url'] ); ?>"
									target="_blank"
									rel="noopener noreferrer"
									aria-label="<?php echo esc_attr( $link['label'] ); ?>"
								>
									<?php echo diako_lucide_icon_svg( $link['icon'], 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="<?php echo esc_attr( diako_card_classes( 'diako-contact-map' ) ); ?>">
					<h2 class="diako-contact-map__title"><?php esc_html_e( 'موقعیت ما', 'diako' ); ?></h2>
					<p class="diako-contact-map__address"><?php echo esc_html( $contact['address'] ); ?></p>
					<a class="<?php echo esc_attr( diako_button_classes( 'outline', 'default', 'w-full' ) ); ?>" href="<?php echo esc_url( $info['map_url'] ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo diako_lucide_icon_svg( 'map-pin', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'مسیریابی در نقشه', 'diako' ); ?></span>
					</a>
				</div>
			</aside>
		</div>

		<section class="diako-contact-page__faq" aria-label="<?php esc_attr_e( 'راهنمای سریع', 'diako' ); ?>">
			<h2 class="diako-contact-page__faq-title"><?php esc_html_e( 'راهنمای سریع', 'diako' ); ?></h2>
			<div class="diako-contact-faq-grid">
				<a class="<?php echo esc_attr( diako_card_classes( 'diako-contact-faq-card' ) ); ?>" href="<?php echo esc_url( $track_url ); ?>">
					<span class="diako-contact-faq-card__icon" aria-hidden="true"><?php echo diako_lucide_icon_svg( 'package', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<h3 class="diako-contact-faq-card__title"><?php esc_html_e( 'پیگیری سفارش', 'diako' ); ?></h3>
					<p class="diako-contact-faq-card__desc"><?php esc_html_e( 'وضعیت سفارش خود را با شماره سفارش و ایمیل بررسی کنید.', 'diako' ); ?></p>
				</a>
				<a class="<?php echo esc_attr( diako_card_classes( 'diako-contact-faq-card' ) ); ?>" href="<?php echo esc_url( $shop_url ); ?>">
					<span class="diako-contact-faq-card__icon" aria-hidden="true"><?php echo diako_lucide_icon_svg( 'shopping-bag', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<h3 class="diako-contact-faq-card__title"><?php esc_html_e( 'مشاهده فروشگاه', 'diako' ); ?></h3>
					<p class="diako-contact-faq-card__desc"><?php echo esc_html( sprintf( __( 'جدیدترین محصولات %s را ببینید.', 'diako' ), diako_get_brand_name() ) ); ?></p>
				</a>
				<a class="<?php echo esc_attr( diako_card_classes( 'diako-contact-faq-card' ) ); ?>" href="<?php echo esc_url( $terms_url ); ?>">
					<span class="diako-contact-faq-card__icon" aria-hidden="true"><?php echo diako_lucide_icon_svg( 'scale', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<h3 class="diako-contact-faq-card__title"><?php esc_html_e( 'شرایط و قوانین', 'diako' ); ?></h3>
					<p class="diako-contact-faq-card__desc"><?php esc_html_e( 'قوانین خرید، ارسال و سیاست‌های فروشگاه را مطالعه کنید.', 'diako' ); ?></p>
				</a>
				<a class="<?php echo esc_attr( diako_card_classes( 'diako-contact-faq-card' ) ); ?>" href="<?php echo esc_url( $mag_url ); ?>">
					<span class="diako-contact-faq-card__icon" aria-hidden="true"><?php echo diako_lucide_icon_svg( 'book-open', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<h3 class="diako-contact-faq-card__title"><?php echo esc_html( sprintf( __( 'مجله %s', 'diako' ), diako_get_brand_name() ) ); ?></h3>
					<p class="diako-contact-faq-card__desc"><?php echo esc_html( sprintf( __( 'راهنماها و مقالات %s را بخوانید.', 'diako' ), diako_get_brand_name() ) ); ?></p>
				</a>
			</div>
		</section>
	</div>
	<?php
}
