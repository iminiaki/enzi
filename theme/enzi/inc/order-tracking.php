<?php
/**
 * Order tracking page.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Track order page ID.
 *
 * @return int
 */
function diako_get_track_order_page_id() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$page_id = (int) get_option( 'diako_track_order_page_id', 0 );

	if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
		$cached = $page_id;
		return $cached;
	}

	$pages = get_pages(
		array(
			'meta_key'   => '_wp_page_template',
			'meta_value' => 'page-track-order.php',
			'number'     => 1,
		)
	);

	$cached = $pages ? (int) $pages[0]->ID : 0;

	if ( $cached ) {
		update_option( 'diako_track_order_page_id', $cached );
	}

	return $cached;
}

/**
 * Track order page URL.
 *
 * @return string
 */
function diako_get_track_order_url() {
	$page_id = diako_get_track_order_page_id();

	if ( $page_id ) {
		return get_permalink( $page_id );
	}

	return home_url( '/track-order/' );
}

/**
 * Whether the current page is the track order page.
 *
 * @return bool
 */
function diako_is_track_order_page() {
	return is_page() && is_page_template( 'page-track-order.php' );
}

/**
 * Create or repair the track order page.
 *
 * @return int
 */
function diako_ensure_track_order_page() {
	$page_id = diako_get_track_order_page_id();

	if ( $page_id ) {
		if ( 'page-track-order.php' !== get_page_template_slug( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', 'page-track-order.php' );
		}

		return $page_id;
	}

	$page = get_page_by_path( 'track-order' );

	if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
		$page_id = (int) $page->ID;
		update_post_meta( $page_id, '_wp_page_template', 'page-track-order.php' );
	} else {
		$page_id = wp_insert_post(
			array(
				'post_title'   => __( 'پیگیری سفارش', 'diako' ),
				'post_name'    => 'track-order',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			),
			true
		);

		if ( is_wp_error( $page_id ) || ! $page_id ) {
			return 0;
		}

		update_post_meta( $page_id, '_wp_page_template', 'page-track-order.php' );
	}

	update_option( 'diako_track_order_page_id', (int) $page_id );

	return (int) $page_id;
}

add_action(
	'after_setup_theme',
	function () {
		if ( get_option( 'diako_track_order_page_bootstrapped' ) ) {
			return;
		}

		diako_ensure_track_order_page();
		update_option( 'diako_track_order_page_bootstrapped', 1 );
	},
	100
);

add_filter(
	'body_class',
	function ( $classes ) {
		if ( diako_is_track_order_page() ) {
			$classes[] = 'woocommerce';
			$classes[] = 'woocommerce-page';
			$classes[] = 'diako-track-order-body';
		}

		return $classes;
	}
);

/**
 * Timeline steps for an order status.
 *
 * @param WC_Order $order Order object.
 * @return array<int, array{label: string, state: string}>
 */
function diako_get_order_tracking_timeline( WC_Order $order ) {
	$status   = $order->get_status();
	$steps    = array(
		array(
			'label' => __( 'ثبت سفارش', 'diako' ),
		),
		array(
			'label' => __( 'تأیید پرداخت', 'diako' ),
		),
		array(
			'label' => __( 'آماده‌سازی', 'diako' ),
		),
		array(
			'label' => __( 'تحویل', 'diako' ),
		),
	);
	$progress = array(
		'pending'    => 1,
		'failed'     => 1,
		'cancelled'  => 1,
		'on-hold'    => 2,
		'processing' => 3,
		'completed'  => 4,
	);

	$level = $progress[ $status ] ?? 2;

	if ( in_array( $status, array( 'cancelled', 'failed', 'refunded' ), true ) ) {
		foreach ( $steps as $index => $step ) {
			$steps[ $index ]['state'] = 'cancelled';
		}

		return $steps;
	}

	if ( 'completed' === $status ) {
		foreach ( $steps as $index => $step ) {
			$steps[ $index ]['state'] = 'done';
		}

		return $steps;
	}

	foreach ( $steps as $index => $step ) {
		$step_number = $index + 1;

		if ( $step_number <= $level ) {
			$steps[ $index ]['state'] = 'done';
		} elseif ( $step_number === $level + 1 ) {
			$steps[ $index ]['state'] = 'current';
		} else {
			$steps[ $index ]['state'] = 'upcoming';
		}
	}

	return $steps;
}

/**
 * Render order status timeline.
 *
 * @param WC_Order $order Order object.
 * @return void
 */
function diako_render_order_tracking_timeline( WC_Order $order ) {
	$steps  = diako_get_order_tracking_timeline( $order );
	$status = $order->get_status();
	?>
	<div class="diako-track-order-timeline" aria-label="<?php esc_attr_e( 'مراحل سفارش', 'diako' ); ?>">
		<?php if ( in_array( $status, array( 'cancelled', 'failed', 'refunded' ), true ) ) : ?>
			<p class="diako-track-order-timeline__alert diako-track-order-timeline__alert--<?php echo esc_attr( $status ); ?>">
				<?php echo esc_html( wc_get_order_status_name( $status ) ); ?>
			</p>
		<?php endif; ?>

		<ol class="diako-track-order-timeline__list">
			<?php foreach ( $steps as $index => $step ) : ?>
				<li class="diako-track-order-timeline__step diako-track-order-timeline__step--<?php echo esc_attr( $step['state'] ); ?>">
					<span class="diako-track-order-timeline__marker" aria-hidden="true">
						<?php if ( 'done' === $step['state'] ) : ?>
							<?php echo diako_lucide_icon_svg( 'check-circle', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php else : ?>
							<span class="diako-track-order-timeline__dot"></span>
						<?php endif; ?>
					</span>
					<span class="diako-track-order-timeline__label"><?php echo esc_html( $step['label'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
	<?php
}

/**
 * Output order tracking shortcode content.
 *
 * @return void
 */
function diako_render_order_tracking_content() {
	if ( ! class_exists( 'WC_Shortcode_Order_Tracking' ) ) {
		echo '<p class="text-muted-foreground">' . esc_html__( 'پیگیری سفارش در حال حاضر در دسترس نیست.', 'diako' ) . '</p>';
		return;
	}

	if ( function_exists( 'wc_print_notices' ) ) {
		wc_print_notices();
	}

	WC_Shortcode_Order_Tracking::output( array() );
}

/**
 * Render the track order page.
 *
 * @return void
 */
function diako_render_track_order_page() {
	$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
	$contact     = diako_get_company_contact_details();
	?>
	<div class="diako-page diako-track-order-page woocommerce-page-wrap">
		<div class="diako-track-order-page__layout">
			<div class="diako-track-order-page__main">
				<header class="diako-track-order-page__hero">
					<div class="diako-track-order-page__hero-icon" aria-hidden="true">
						<?php echo diako_lucide_icon_svg( 'package', 'h-7 w-7' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="diako-track-order-page__hero-content">
						<h1 class="diako-page-title"><?php esc_html_e( 'پیگیری سفارش', 'diako' ); ?></h1>
						<p class="diako-page-desc">
							<?php esc_html_e( 'با شماره سفارش و ایمیلی که هنگام خرید وارد کردید، وضعیت سفارش خود را ببینید.', 'diako' ); ?>
						</p>
					</div>
				</header>

				<div class="<?php echo esc_attr( diako_card_classes( 'diako-track-order-page__panel' ) ); ?>">
					<?php diako_render_order_tracking_content(); ?>
				</div>
			</div>

			<aside class="diako-track-order-page__aside">
				<div class="<?php echo esc_attr( diako_card_classes( 'diako-track-order-help' ) ); ?>">
					<h2 class="diako-track-order-help__title"><?php esc_html_e( 'راهنما', 'diako' ); ?></h2>
					<ul class="diako-track-order-help__list">
						<li><?php esc_html_e( 'شماره سفارش در ایمیل تأیید خرید و پیامک ارسال شده است.', 'diako' ); ?></li>
						<li><?php esc_html_e( 'ایمیل باید همان آدرسی باشد که هنگام تسویه‌حساب وارد کردید.', 'diako' ); ?></li>
						<li><?php esc_html_e( 'در صورت ورود به حساب کاربری، همه سفارش‌ها را یکجا می‌بینید.', 'diako' ); ?></li>
					</ul>
				</div>

				<div class="<?php echo esc_attr( diako_card_classes( 'diako-track-order-help' ) ); ?>">
					<h2 class="diako-track-order-help__title"><?php esc_html_e( 'نیاز به کمک دارید؟', 'diako' ); ?></h2>
					<ul class="diako-track-order-help__contact">
						<li>
							<?php echo diako_lucide_icon_svg( 'phone', 'h-4 w-4 shrink-0' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php diako_render_company_phone_link(); ?>
						</li>
						<li>
							<?php echo diako_lucide_icon_svg( 'mail', 'h-4 w-4 shrink-0' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<a href="mailto:<?php echo esc_attr( $contact['email'] ); ?>"><?php echo esc_html( $contact['email'] ); ?></a>
						</li>
					</ul>
					<div class="diako-track-order-help__actions">
						<?php
						diako_button(
							array(
								'href'       => $account_url,
								'label'      => __( 'حساب کاربری', 'diako' ),
								'variant'    => 'default',
								'icon'       => 'user',
								'icon_class' => 'h-4 w-4',
							)
						);
						?>
					</div>
				</div>
			</aside>
		</div>
	</div>
	<?php
}
