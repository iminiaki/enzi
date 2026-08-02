<?php
/**
 * Payment methods
 *
 * @package Diako
 * @version 8.9.0
 */

defined( 'ABSPATH' ) || exit;

$saved_methods = wc_get_customer_saved_methods_list( get_current_user_id() );
$has_methods   = (bool) $saved_methods;
$types         = wc_get_account_payment_methods_types();

do_action( 'woocommerce_before_account_payment_methods', $has_methods );

if ( $has_methods ) :
	?>
	<table class="woocommerce-MyAccount-paymentMethods shop_table shop_table_responsive account-payment-methods-table">
		<thead>
			<tr>
				<?php foreach ( wc_get_account_payment_methods_columns() as $column_id => $column_name ) : ?>
					<th class="woocommerce-PaymentMethod woocommerce-PaymentMethod--<?php echo esc_attr( $column_id ); ?> payment-method-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<?php foreach ( $saved_methods as $type => $methods ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
			<?php foreach ( $methods as $method ) : ?>
				<tr class="payment-method<?php echo ! empty( $method['is_default'] ) ? ' default-payment-method' : ''; ?>">
					<?php foreach ( wc_get_account_payment_methods_columns() as $column_id => $column_name ) : ?>
						<td class="woocommerce-PaymentMethod woocommerce-PaymentMethod--<?php echo esc_attr( $column_id ); ?> payment-method-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
							<?php
							if ( has_action( 'woocommerce_account_payment_methods_column_' . $column_id ) ) {
								do_action( 'woocommerce_account_payment_methods_column_' . $column_id, $method );
							} elseif ( 'method' === $column_id ) {
								if ( ! empty( $method['method']['last4'] ) ) {
									/* translators: 1: credit card type 2: last 4 digits */
									echo sprintf( esc_html__( '%1$s ending in %2$s', 'woocommerce' ), esc_html( wc_get_credit_card_type_label( $method['method']['brand'] ) ), esc_html( $method['method']['last4'] ) );
								} else {
									echo esc_html( wc_get_credit_card_type_label( $method['method']['brand'] ) );
								}
							} elseif ( 'expires' === $column_id ) {
								echo esc_html( $method['expires'] );
							} elseif ( 'actions' === $column_id ) {
								foreach ( $method['actions'] as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
									echo '<a href="' . esc_url( $action['url'] ) . '" class="button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>&nbsp;';
								}
							}
							?>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		<?php endforeach; ?>
	</table>
	<?php
else :
	$empty_button = null;

	if ( WC()->payment_gateways->get_available_payment_gateways() ) {
		$empty_button = array(
			'href'  => wc_get_endpoint_url( 'add-payment-method' ),
			'label' => __( 'Add payment method', 'woocommerce' ),
			'icon'  => 'credit-card',
		);
	}

	diako_render_account_empty_state(
		array(
			'icon'    => 'credit-card',
			'message' => __( 'No saved methods found.', 'woocommerce' ),
			'button'  => $empty_button,
		)
	);
endif;

do_action( 'woocommerce_after_account_payment_methods', $has_methods );

if ( $has_methods && WC()->payment_gateways->get_available_payment_gateways() ) :
	?>
	<div class="diako-account-payment-methods__actions">
		<?php
		diako_button(
			array(
				'href'       => wc_get_endpoint_url( 'add-payment-method' ),
				'label'      => __( 'Add payment method', 'woocommerce' ),
				'variant'    => 'outline',
				'icon'       => 'credit-card',
				'icon_class' => 'h-4 w-4',
			)
		);
		?>
	</div>
	<?php
endif;
