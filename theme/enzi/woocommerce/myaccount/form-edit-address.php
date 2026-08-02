<?php
/**
 * Edit address form
 *
 * @package Diako
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$page_title = ( 'billing' === $load_address )
	? __( 'آدرس صورتحساب', 'diako' )
	: __( 'آدرس ارسال', 'diako' );

do_action( 'woocommerce_before_edit_account_address_form' );
?>

<?php if ( ! $load_address ) : ?>
	<?php wc_get_template( 'myaccount/my-address.php' ); ?>
<?php else : ?>
	<div class="<?php echo esc_attr( diako_card_classes( 'diako-checkout-section diako-account-address-form' ) ); ?>">
		<form method="post" class="woocommerce-EditAddressForm edit-address diako-account-address-form__form" novalidate>
			<h2><?php echo esc_html( apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address ) ); ?></h2>

			<div class="woocommerce-address-fields">
				<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

				<div class="woocommerce-address-fields__field-wrapper">
					<?php
					uasort( $address, 'wc_checkout_fields_uasort_comparison' );

					foreach ( $address as $key => $field ) {
						woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
					}
					?>
				</div>

				<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

				<p class="diako-account-address-form__actions">
					<?php
					diako_button(
						array(
							'label'       => __( 'ذخیره آدرس', 'diako' ),
							'variant'     => 'default',
							'size'        => 'lg',
							'type'        => 'button',
							'button_type' => 'submit',
							'attrs'       => array(
								'name'  => 'save_address',
								'value' => __( 'Save address', 'woocommerce' ),
							),
						)
					);
					?>
					<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
					<input type="hidden" name="action" value="edit_address" />
				</p>
			</div>
		</form>
	</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_edit_account_address_form' ); ?>
