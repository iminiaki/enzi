<?php
/**
 * My Addresses
 *
 * @package Diako
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing'  => __( 'Billing address', 'woocommerce' ),
			'shipping' => __( 'Shipping address', 'woocommerce' ),
		),
		$customer_id
	);
} else {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing' => __( 'Billing address', 'woocommerce' ),
		),
		$customer_id
	);
}
?>

<p class="diako-account-addresses__desc">
	<?php echo apply_filters( 'woocommerce_my_account_my_address_description', esc_html__( 'The following addresses will be used on the checkout page by default.', 'woocommerce' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</p>

<div class="woocommerce-Addresses addresses<?php echo ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) ? ' woocommerce-Addresses--grid' : ''; ?>">
	<?php foreach ( $get_addresses as $name => $address_title ) : ?>
		<?php
		$address   = wc_get_account_formatted_address( $name );
		$edit_url  = wc_get_endpoint_url( 'edit-address', $name );
		$btn_label = $address
			? sprintf(
				/* translators: %s: Address title */
				__( 'Edit %s', 'woocommerce' ),
				$address_title
			)
			: sprintf(
				/* translators: %s: Address title */
				__( 'Add %s', 'woocommerce' ),
				$address_title
			);
		?>
		<div class="woocommerce-Address">
			<header class="woocommerce-Address-title title">
				<h2><?php echo esc_html( $address_title ); ?></h2>
			</header>

			<address>
				<?php
				echo $address ? wp_kses_post( $address ) : esc_html__( 'You have not set up this type of address yet.', 'woocommerce' );

				do_action( 'woocommerce_my_account_after_my_address', $name );
				?>
			</address>

			<div class="diako-account-address__actions">
				<?php
				diako_button(
					array(
						'href'       => $edit_url,
						'label'      => $btn_label,
						'variant'    => 'outline',
						'size'       => 'default',
						'icon'       => 'map-pin',
						'icon_class' => 'h-4 w-4',
					)
				);
				?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
