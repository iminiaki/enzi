<?php defined( 'ABSPATH' ) || exit; ?>
<?php
/**
 * Homepage blog.
 *
 * @package Enzi
 */

$settings    = diako_get_theme_settings();
$blog        = $settings['blog'] ?? array();
$post_count  = max( 1, min( 12, absint( $blog['post_count'] ?? 4 ) ) );
$posts       = get_posts(
	array(
		'numberposts' => $post_count,
		'post_status' => 'publish',
	)
);
?>

<section class="border-t border-border bg-muted/20 py-16 md:py-20">
	<div class="container">
		<?php
		diako_section_heading(
			$blog['title'] ?? '',
			$blog['description'] ?? '',
			diako_theme_settings_url( $blog['button_url'] ?? '' ),
			$blog['button_text'] ?? ''
		);
		?>

		<?php if ( ! empty( $posts ) ) : ?>
			<div
				class="diako-carousel diako-carousel--track"
				data-diako-carousel="track"
				aria-roledescription="carousel"
				tabindex="0"
			>
				<div class="diako-carousel__track-wrap">
					<div class="diako-carousel__viewport" data-carousel-viewport>
						<ul class="diako-blog-track !list-none" data-carousel-track dir="rtl">
							<?php foreach ( $posts as $post ) : ?>
								<li>
									<?php diako_render_post_card( $post ); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php diako_render_carousel_arrows( array( 'placement' => 'track' ) ); ?>
				</div>
				<?php diako_render_carousel_dots(); ?>
			</div>
		<?php else : ?>
			<div class="<?php echo esc_attr( diako_card_classes( 'p-8 text-center text-muted-foreground' ) ); ?>">
				<?php esc_html_e( 'مقاله‌ای منتشر نشده است.', 'diako' ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
