<?php
/**
 * Order tracking
 *
 * @package Diako
 * @version 10.6.0
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

$notes = $order->get_customer_order_notes();
?>
<div class="diako-track-order-result">
	<div class="diako-track-order-result__summary">
		<div class="diako-track-order-result__summary-head">
			<div>
				<p class="diako-track-order-result__eyebrow"><?php esc_html_e( 'وضعیت سفارش', 'diako' ); ?></p>
				<h2 class="diako-track-order-result__title">
					<?php
					printf(
						/* translators: %s: order number */
						esc_html__( 'سفارش #%s', 'diako' ),
						esc_html( $order->get_order_number() )
					);
					?>
				</h2>
			</div>
			<span class="<?php echo esc_attr( diako_badge_classes( 'outline' ) ); ?> diako-track-order-result__status">
				<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
			</span>
		</div>

		<ul class="diako-track-order-result__meta">
			<li>
				<span><?php esc_html_e( 'تاریخ ثبت', 'diako' ); ?></span>
				<strong><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></strong>
			</li>
			<li>
				<span><?php esc_html_e( 'مبلغ کل', 'diako' ); ?></span>
				<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
			</li>
			<?php if ( $order->get_payment_method_title() ) : ?>
				<li>
					<span><?php esc_html_e( 'روش پرداخت', 'diako' ); ?></span>
					<strong><?php echo esc_html( $order->get_payment_method_title() ); ?></strong>
				</li>
			<?php endif; ?>
		</ul>
	</div>

	<?php diako_render_order_tracking_timeline( $order ); ?>

	<?php if ( $notes ) : ?>
		<section class="diako-track-order-notes">
			<h3 class="diako-track-order-notes__title"><?php esc_html_e( 'به‌روزرسانی‌های سفارش', 'diako' ); ?></h3>
			<ol class="diako-track-order-notes__list">
				<?php foreach ( $notes as $note ) : ?>
					<li class="diako-track-order-notes__item">
						<time datetime="<?php echo esc_attr( gmdate( 'c', strtotime( $note->comment_date ) ) ); ?>">
							<?php echo esc_html( wp_date( wc_date_format() . ' ' . wc_time_format(), strtotime( $note->comment_date ) ) ); ?>
						</time>
						<div class="diako-track-order-notes__content">
							<?php echo wp_kses_post( wpautop( wptexturize( $note->comment_content ) ) ); ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>

	<div class="diako-track-order-result__details">
		<?php do_action( 'woocommerce_view_order', $order->get_id() ); ?>
	</div>

	<div class="diako-track-order-result__actions">
		<?php
		diako_button(
			array(
				'href'       => diako_get_track_order_url(),
				'label'      => __( 'پیگیری سفارش دیگر', 'diako' ),
				'variant'    => 'outline',
				'icon'       => 'refresh-cw',
				'icon_class' => 'h-4 w-4',
			)
		);

		if ( is_user_logged_in() && function_exists( 'wc_get_account_endpoint_url' ) ) {
			diako_button(
				array(
					'href'       => wc_get_account_endpoint_url( 'orders' ),
					'label'      => __( 'سفارش‌های من', 'diako' ),
					'variant'    => 'default',
					'icon'       => 'package',
					'icon_class' => 'h-4 w-4',
				)
			);
		}
		?>
	</div>
</div>
