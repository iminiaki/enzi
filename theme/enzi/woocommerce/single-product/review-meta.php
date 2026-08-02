<?php
/**
 * Review meta display.
 *
 * @package Diako
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

global $comment;

$verified = wc_review_is_from_verified_owner( $comment->comment_ID );

if ( '0' === $comment->comment_approved ) {
	?>
	<p class="meta diako-product-reviews__meta">
		<em class="woocommerce-review__awaiting-approval">
			<?php esc_html_e( 'Your review is awaiting approval', 'woocommerce' ); ?>
		</em>
	</p>
	<?php
	return;
}
?>
<p class="meta diako-product-reviews__meta">
	<strong class="woocommerce-review__author"><?php comment_author(); ?></strong>
	<?php if ( 'yes' === get_option( 'woocommerce_review_rating_verification_label' ) && $verified ) : ?>
		<span class="diako-product-reviews__verified"><?php esc_html_e( 'verified owner', 'woocommerce' ); ?></span>
	<?php endif; ?>
	<time class="woocommerce-review__published-date" datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>">
		<?php echo esc_html( get_comment_date( wc_date_format() ) ); ?>
	</time>
</p>
