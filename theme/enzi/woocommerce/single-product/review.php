<?php
/**
 * Review comments template.
 *
 * @package Diako
 * @version 2.6.0
 */

defined( 'ABSPATH' ) || exit;
?>
<li <?php comment_class( 'diako-product-reviews__item' ); ?> id="li-comment-<?php comment_ID(); ?>">

	<article id="comment-<?php comment_ID(); ?>" class="comment_container diako-product-reviews__card">

		<div class="diako-product-reviews__card-header">
			<div class="diako-product-reviews__identity">
				<?php woocommerce_review_display_gravatar( $comment ); ?>
				<div class="diako-product-reviews__meta-wrap">
					<?php woocommerce_review_display_meta(); ?>
				</div>
			</div>

			<div class="diako-product-reviews__rating-wrap">
				<?php woocommerce_review_display_rating(); ?>
			</div>
		</div>

		<div class="comment-text diako-product-reviews__content">
			<?php
			do_action( 'woocommerce_review_before_comment_text', $comment );
			woocommerce_review_display_comment_text();
			do_action( 'woocommerce_review_after_comment_text', $comment );
			?>
		</div>

	</article>
</li>
