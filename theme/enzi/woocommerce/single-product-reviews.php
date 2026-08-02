<?php
/**
 * Single product reviews.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Diako
 * @version 9.7.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
	return;
}
?>
<div id="reviews" class="woocommerce-Reviews diako-product-reviews">
	<div id="comments" class="diako-product-reviews__comments">
		<h2 class="woocommerce-Reviews-title diako-product-reviews__title">
			<?php
			$count = $product->get_review_count();
			if ( $count && wc_review_ratings_enabled() ) {
				/* translators: 1: reviews count 2: product name */
				$reviews_title = sprintf(
					esc_html( _n( '%1$s review for %2$s', '%1$s reviews for %2$s', $count, 'woocommerce' ) ),
					esc_html( diako_to_persian_digits( (string) $count ) ),
					'<span>' . get_the_title() . '</span>'
				);
				echo apply_filters( 'woocommerce_reviews_title', $reviews_title, $count, $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				esc_html_e( 'Reviews', 'woocommerce' );
			}
			?>
		</h2>

		<?php if ( have_comments() ) : ?>
			<ol class="diako-product-reviews__list commentlist">
				<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) ); ?>
			</ol>

			<?php
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="woocommerce-pagination diako-product-reviews__pagination">';
				paginate_comments_links(
					apply_filters(
						'woocommerce_comment_pagination_args',
						array(
							'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
							'next_text' => is_rtl() ? '&larr;' : '&rarr;',
							'type'      => 'list',
						)
					)
				);
				echo '</nav>';
			endif;
			?>
		<?php else : ?>
			<div class="diako-product-reviews__empty">
				<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
		<div id="review_form_wrapper" class="diako-product-reviews__form-wrap">
			<div id="review_form" class="diako-product-reviews__form">
				<?php
				$commenter    = wp_get_current_commenter();
				$comment_form = array(
					'title_reply'         => have_comments() ? esc_html__( 'Add a review', 'woocommerce' ) : sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'woocommerce' ), get_the_title() ),
					'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'woocommerce' ),
					'title_reply_before'  => '<h3 id="reply-title" class="comment-reply-title diako-product-reviews__form-title">',
					'title_reply_after'   => '</h3>',
					'comment_notes_after' => '',
					'label_submit'        => esc_html__( 'Submit', 'woocommerce' ),
					'logged_in_as'        => '',
					'comment_field'       => '',
					'class_form'          => 'diako-product-reviews__comment-form comment-form',
					'submit_button'       => '<button type="submit" id="%2$s" class="%3$s" name="%1$s">%4$s</button>',
					'submit_field'        => '<p class="form-submit diako-product-reviews__submit">%1$s %2$s</p>',
				);

				$name_email_required = (bool) get_option( 'require_name_email', 1 );
				$fields              = array(
					'author' => array(
						'label'        => __( 'Name', 'woocommerce' ),
						'type'         => 'text',
						'value'        => $commenter['comment_author'],
						'required'     => $name_email_required,
						'autocomplete' => 'name',
					),
					'email'  => array(
						'label'        => __( 'Email', 'woocommerce' ),
						'type'         => 'email',
						'value'        => $commenter['comment_author_email'],
						'required'     => $name_email_required,
						'autocomplete' => 'email',
					),
				);

				$comment_form['fields'] = array();

				$name_email_row = '';

				if ( ! is_user_logged_in() ) {
					$author_field = $fields['author'];
					$email_field  = $fields['email'];

					$name_email_row  = '<div class="diako-product-reviews__field-row">';
					$name_email_row .= '<p class="comment-form-author diako-product-reviews__field">';
					$name_email_row .= '<label class="' . esc_attr( diako_label_classes() ) . '" for="author">' . esc_html( $author_field['label'] );
					if ( $author_field['required'] ) {
						$name_email_row .= ' <span class="required">*</span>';
					}
					$name_email_row .= '</label><input id="author" class="' . esc_attr( diako_input_classes() ) . '" name="author" type="text" autocomplete="name" value="' . esc_attr( $author_field['value'] ) . '" size="30" ' . ( $author_field['required'] ? 'required' : '' ) . ' /></p>';

					$name_email_row .= '<p class="comment-form-email diako-product-reviews__field">';
					$name_email_row .= '<label class="' . esc_attr( diako_label_classes() ) . '" for="email">' . esc_html( $email_field['label'] );
					if ( $email_field['required'] ) {
						$name_email_row .= ' <span class="required">*</span>';
					}
					$name_email_row .= '</label><input id="email" class="' . esc_attr( diako_input_classes() ) . '" name="email" type="email" autocomplete="email" value="' . esc_attr( $email_field['value'] ) . '" size="30" ' . ( $email_field['required'] ? 'required' : '' ) . ' /></p>';
					$name_email_row .= '</div>';
				}

				$account_page_url = wc_get_page_permalink( 'myaccount' );
				if ( $account_page_url ) {
					$comment_form['must_log_in'] = '<p class="must-log-in diako-product-reviews__notice">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woocommerce' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
				}

				if ( wc_review_ratings_enabled() ) {
					$comment_form['comment_field'] = diako_render_review_rating_field( true );
				}

				$comment_form['comment_field'] .= $name_email_row;
				$comment_form['comment_field'] .= '<p class="comment-form-comment diako-product-reviews__field"><label class="' . esc_attr( diako_label_classes() ) . '" for="comment">' . esc_html__( 'Your review', 'woocommerce' ) . ' <span class="required">*</span></label><textarea id="comment" class="' . esc_attr( diako_textarea_classes() ) . '" name="comment" cols="45" rows="6" required></textarea></p>';

				$comment_form['submit_button'] = '<button type="submit" id="%2$s" class="' . esc_attr( diako_button_classes( 'default' ) ) . '" name="%1$s">%4$s</button>';

				comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
				?>
			</div>
		</div>
	<?php else : ?>
		<p class="woocommerce-verification-required diako-product-reviews__notice"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ); ?></p>
	<?php endif; ?>
</div>
