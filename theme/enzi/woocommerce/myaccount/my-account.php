<?php
/**
 * My Account page layout.
 *
 * @package Diako
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	?>
	<div class="woocommerce">
		<?php
		/**
		 * My Account content (login / register).
		 *
		 * @hooked woocommerce_account_content - 10
		 */
		do_action( 'woocommerce_account_content' );
		?>
	</div>
	<?php
	return;
}
?>
<div class="diako-account diako-account--logged-in">
	<?php diako_render_account_page_header(); ?>

	<div class="diako-account__shell">
		<?php
		/**
		 * My Account navigation.
		 *
		 * @hooked woocommerce_account_navigation - 10
		 */
		do_action( 'woocommerce_account_navigation' );
		?>

		<div class="woocommerce-MyAccount-content diako-account__content">
			<?php
			/**
			 * My Account content.
			 *
			 * @hooked woocommerce_account_content - 10
			 */
			do_action( 'woocommerce_account_content' );
			?>
		</div>
	</div>
</div>
