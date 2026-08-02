<?php
/**
 * Coming soon page markup.
 *
 * @package Diako
 *
 * @var array<string, mixed> $args
 */

defined( 'ABSPATH' ) || exit;

$settings  = isset( $args['settings'] ) && is_array( $args['settings'] ) ? $args['settings'] : diako_get_coming_soon_page_settings();
$launch_ts = isset( $args['launch_ts'] ) ? $args['launch_ts'] : diako_get_coming_soon_launch_timestamp( $settings );
$show_cd   = ! empty( $args['show_cd'] );

$title       = trim( (string) ( $settings['title'] ?? '' ) );
$description = trim( (string) ( $settings['description'] ?? '' ) );
$brand       = diako_get_brand_name();
$contact     = diako_get_company_contact_details();

if ( '' === $title ) {
	$title = __( 'به زودی برمی‌گردیم', 'diako' );
}

if ( '' === $description ) {
	$description = sprintf(
		/* translators: %s: store brand name */
		__( 'فروشگاه %s در حال آماده‌سازی است. به زودی با جدیدترین محصولات مراقبت پوست و زیبایی در خدمت شما هستیم.', 'diako' ),
		$brand
	);
}
?>
<div class="diako-coming-soon__backdrop" aria-hidden="true"></div>

<div class="diako-coming-soon__page">
	<div class="diako-coming-soon__theme">
		<?php diako_theme_toggle(); ?>
	</div>

	<div class="<?php echo esc_attr( diako_card_classes( 'diako-coming-soon__card' ) ); ?>">
		<div class="diako-coming-soon__logo">
			<?php
			diako_render_branding_logo(
				array(
					'class'      => 'diako-coming-soon__logo-image',
					'link_class' => 'diako-logo inline-flex items-center',
					'alt'        => $brand,
				)
			);
			?>
		</div>

		<div class="diako-coming-soon__content">
			<p class="diako-coming-soon__eyebrow"><?php echo esc_html( $brand ); ?></p>
			<h1 class="diako-coming-soon__title"><?php echo esc_html( $title ); ?></h1>
			<p class="diako-coming-soon__desc"><?php echo esc_html( $description ); ?></p>
		</div>

		<?php if ( $show_cd && null !== $launch_ts ) : ?>
			<div
				class="diako-coming-soon__countdown"
				data-coming-soon-countdown
				data-target="<?php echo esc_attr( (string) $launch_ts ); ?>"
				aria-live="polite"
				aria-label="<?php esc_attr_e( 'شمارش معکوس تا راه‌اندازی', 'diako' ); ?>"
			>
				<div class="diako-coming-soon__countdown-item">
					<span class="diako-coming-soon__countdown-value" data-unit="days">۰</span>
					<span class="diako-coming-soon__countdown-label"><?php esc_html_e( 'روز', 'diako' ); ?></span>
				</div>
				<div class="diako-coming-soon__countdown-item">
					<span class="diako-coming-soon__countdown-value" data-unit="hours">۰</span>
					<span class="diako-coming-soon__countdown-label"><?php esc_html_e( 'ساعت', 'diako' ); ?></span>
				</div>
				<div class="diako-coming-soon__countdown-item">
					<span class="diako-coming-soon__countdown-value" data-unit="minutes">۰</span>
					<span class="diako-coming-soon__countdown-label"><?php esc_html_e( 'دقیقه', 'diako' ); ?></span>
				</div>
				<div class="diako-coming-soon__countdown-item">
					<span class="diako-coming-soon__countdown-value" data-unit="seconds">۰</span>
					<span class="diako-coming-soon__countdown-label"><?php esc_html_e( 'ثانیه', 'diako' ); ?></span>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $settings['show_newsletter'] ) ) : ?>
			<form
				class="diako-coming-soon__form"
				data-coming-soon-form
				data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'diako_coming_soon_subscribe' ) ); ?>"
				data-error-message="<?php echo esc_attr( __( 'خطا در ثبت ایمیل. دوباره تلاش کنید.', 'diako' ) ); ?>"
				novalidate
			>
				<label class="sr-only" for="diako-coming-soon-email"><?php esc_html_e( 'ایمیل', 'diako' ); ?></label>
				<input
					id="diako-coming-soon-email"
					class="<?php echo esc_attr( diako_input_classes( 'diako-coming-soon__input' ) ); ?>"
					type="email"
					name="email"
					inputmode="email"
					autocomplete="email"
					required
					placeholder="<?php echo esc_attr( trim( (string) ( $settings['newsletter_label'] ?? '' ) ) ?: __( 'ایمیل خود را وارد کنید…', 'diako' ) ); ?>"
				>
				<button type="submit" class="<?php echo esc_attr( diako_button_classes( 'default', 'default', 'diako-coming-soon__submit' ) ); ?>">
					<?php esc_html_e( 'خبرم کن', 'diako' ); ?>
				</button>
			</form>
			<p class="diako-coming-soon__notice" data-coming-soon-notice hidden></p>
		<?php endif; ?>

		<?php if ( ! empty( $settings['show_contact'] ) ) : ?>
			<div class="diako-coming-soon__contact">
				<a class="diako-coming-soon__contact-link" href="tel:<?php echo esc_attr( $contact['phone_tel'] ); ?>">
					<?php echo diako_lucide_icon_svg( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span <?php echo diako_phone_ltr_attr(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( diako_to_persian_digits( $contact['phone_display'] ) ); ?></span>
				</a>
				<a class="diako-coming-soon__contact-link" href="mailto:<?php echo esc_attr( $contact['email'] ); ?>">
					<?php echo diako_lucide_icon_svg( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span dir="ltr"><?php echo esc_html( $contact['email'] ); ?></span>
				</a>
			</div>
		<?php endif; ?>

		<p class="diako-coming-soon__footer"><?php echo esc_html( diako_get_footer_copyright_text() ); ?></p>
	</div>
</div>
