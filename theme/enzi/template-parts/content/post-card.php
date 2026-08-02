<?php
/**
 * Blog post card.
 *
 * @package Diako
 */

defined( 'ABSPATH' ) || exit;

$post = $args['post'] ?? get_post();

if ( ! $post instanceof WP_Post ) {
	return;
}

$permalink   = get_permalink( $post );
$author_id   = (int) $post->post_author;
$author_name = get_the_author_meta( 'display_name', $author_id );
$author_url  = get_author_posts_url( $author_id );
?>
<article class="diako-post-card group h-full">
	<a href="<?php echo esc_url( $permalink ); ?>" class="diako-post-card__media" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail( $post ) ) : ?>
			<?php
			echo get_the_post_thumbnail(
				$post,
				'medium_large',
				array(
					'class' => 'diako-post-card__image',
					'alt'   => esc_attr( get_the_title( $post ) ),
				)
			); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		<?php else : ?>
			<div class="diako-post-card__placeholder" aria-hidden="true">
				<?php echo diako_lucide_icon_svg( 'book-open', 'h-10 w-10' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>
		<span class="diako-post-card__overlay" aria-hidden="true"></span>
		<span class="diako-post-card__badge">
			<?php echo diako_lucide_icon_svg( 'clock', 'h-3.5 w-3.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span><?php echo esc_html( diako_get_post_reading_time_label( $post ) ); ?></span>
		</span>
	</a>

	<div class="diako-post-card__body">
		<time class="diako-post-card__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post ) ); ?>">
			<?php echo esc_html( diako_get_post_card_date( $post ) ); ?>
		</time>

		<h3 class="diako-post-card__title">
			<a href="<?php echo esc_url( $permalink ); ?>">
				<?php echo esc_html( get_the_title( $post ) ); ?>
			</a>
		</h3>

		<p class="diako-post-card__excerpt line-clamp-2">
			<?php echo esc_html( wp_trim_words( get_the_excerpt( $post ), 18 ) ); ?>
		</p>

		<div class="diako-post-card__author">
			<a href="<?php echo esc_url( $author_url ); ?>" class="diako-post-card__author-link">
				<?php
				echo get_avatar(
					$author_id,
					64,
					'',
					'',
					array(
						'class' => 'diako-post-card__avatar',
					)
				); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
				<span class="diako-post-card__author-name"><?php echo esc_html( $author_name ); ?></span>
			</a>
		</div>
	</div>
</article>
