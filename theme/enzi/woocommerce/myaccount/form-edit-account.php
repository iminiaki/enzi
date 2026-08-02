<?php
/**
 * Edit account form
 *
 * @package Diako
 * @version 10.5.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_edit_account_form' );
?>

<div class="<?php echo esc_attr( diako_card_classes( 'diako-checkout-section diako-account-edit-form' ) ); ?>">
	<form class="woocommerce-EditAccountForm edit-account diako-account-edit-form__form" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?>>
		<h2><?php esc_html_e( 'جزئیات حساب', 'diako' ); ?></h2>

		<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

		<div class="diako-account-edit-form__fields">
			<div class="diako-account-edit-form__row diako-account-edit-form__row--2">
				<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
					<label for="account_first_name"><?php esc_html_e( 'First name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" aria-required="true" />
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
					<label for="account_last_name"><?php esc_html_e( 'Last name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" aria-required="true" />
				</p>
			</div>

			<div class="diako-account-edit-form__row diako-account-edit-form__row--2">
				<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
					<label for="account_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
					<input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" aria-required="true" />
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
					<label for="account_display_name"><?php esc_html_e( 'Display name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_display_name" id="account_display_name" aria-describedby="account_display_name_description" value="<?php echo esc_attr( $user->display_name ); ?>" aria-required="true" />
					<span id="account_display_name_description" class="diako-account-edit-form__hint">
						<?php esc_html_e( 'This will be how your name will be displayed in the account section and in reviews', 'woocommerce' ); ?>
					</span>
				</p>
			</div>

			<?php do_action( 'woocommerce_edit_account_form_fields' ); ?>

			<fieldset class="diako-account-edit-form__password">
				<legend><?php esc_html_e( 'Password change', 'woocommerce' ); ?></legend>

				<div class="diako-account-edit-form__row diako-account-edit-form__row--3">
					<p class="woocommerce-form-row form-row">
						<label for="password_current"><?php esc_html_e( 'Current password (leave blank to leave unchanged)', 'woocommerce' ); ?></label>
						<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current" autocomplete="current-password" />
					</p>
					<p class="woocommerce-form-row form-row">
						<label for="password_1"><?php esc_html_e( 'New password (leave blank to leave unchanged)', 'woocommerce' ); ?></label>
						<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1" autocomplete="new-password" />
					</p>
					<p class="woocommerce-form-row form-row">
						<label for="password_2"><?php esc_html_e( 'Confirm new password', 'woocommerce' ); ?></label>
						<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2" autocomplete="new-password" />
					</p>
				</div>
			</fieldset>
		</div>

		<?php do_action( 'woocommerce_edit_account_form' ); ?>

		<p class="diako-account-edit-form__actions">
			<?php
			diako_button(
				array(
					'label'       => __( 'Save changes', 'woocommerce' ),
					'variant'     => 'default',
					'size'        => 'lg',
					'type'        => 'button',
					'button_type' => 'submit',
					'attrs'       => array(
						'name'  => 'save_account_details',
						'value' => __( 'Save changes', 'woocommerce' ),
					),
				)
			);
			?>
			<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
			<input type="hidden" name="action" value="save_account_details" />
		</p>

		<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
	</form>
</div>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
