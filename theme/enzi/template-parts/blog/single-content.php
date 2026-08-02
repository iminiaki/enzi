<?php
/**
 * Single post main content.
 *
 * @package Diako
 */

defined( 'ABSPATH' ) || exit;

$post_id     = get_the_ID();
$author_id   = (int) get_the_author_meta( 'ID' );
$author_name = get_the_author();
$author_url  = get_author_posts_url( $author_id );
$categories  = get_the_category( $post_id );
?>
<article <?php post_class( 'diako-post-single' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="diako-post-single__media">
			<?php
			the_post_thumbnail(
				'large',
				array(
					'class' => 'diako-post-single__image',
					'alt'   => esc_attr( get_the_title() ),
				)
			);
			?>
		</div>
	<?php endif; ?>

	<div class="diako-post-single__body">
		<header class="diako-post-single__header">
			<h1 class="diako-post-single__title"><?php the_title(); ?></h1>

			<div class="diako-post-single__meta">
				<?php if ( ! empty( $categories ) ) : ?>
					<div class="diako-post-single__categories" aria-label="<?php esc_attr_e( 'دسته‌بندی‌ها', 'diako' ); ?>">
						<?php foreach ( $categories as $category ) : ?>
							<a class="diako-post-single__category" href="<?php echo esc_url( get_category_link( $category ) ); ?>">
								<?php echo esc_html( $category->name ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<time class="diako-post-single__meta-item" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
					<?php echo diako_lucide_icon_svg( 'clock', 'h-4 w-4 shrink-0' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html( diako_get_post_card_date() ); ?></span>
				</time>

				<a class="diako-post-single__meta-item diako-post-single__author" href="<?php echo esc_url( $author_url ); ?>">
					<?php
					echo get_avatar(
						$author_id,
						64,
						'',
						'',
						array(
							'class' => 'diako-post-single__avatar',
						)
					); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<span><?php echo esc_html( $author_name ); ?></span>
				</a>

				<span class="diako-post-single__meta-item">
					<?php echo diako_lucide_icon_svg( 'book-open', 'h-4 w-4 shrink-0' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html( diako_get_post_reading_time_label() ); ?></span>
				</span>

				<?php diako_render_post_share_buttons(); ?>
			</div>
		</header>

		<div class="diako-post-single__content prose prose-neutral dark:prose-invert max-w-none entry-content">
			<?php the_content(); ?>
		</div>

		<?php
		wp_link_pages(
			array(
				'before' => '<nav class="diako-post-single__pages" aria-label="' . esc_attr__( 'صفحات مقاله', 'diako' ) . '"><p class="diako-post-single__pages-label">' . esc_html__( 'صفحات:', 'diako' ) . '</p>',
				'after'  => '</nav>',
			)
		);
		?>
	</div>
</article>
