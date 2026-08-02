<?php
/**
 * Homepage product section.
 *
 * @package Diako
 */

defined( 'ABSPATH' ) || exit;

$section_args = $args['args'] ?? array();
$query        = $args['query'] ?? null;

if ( ! $query instanceof WP_Query || ! $query->have_posts() ) {
	return;
}
?>

<section class="bg-muted/20 py-16 md:py-20">
	<div class="container">
		<?php
		diako_section_heading(
			$section_args['title'] ?? '',
			$section_args['description'] ?? '',
			$section_args['button_url'] ?? '',
			$section_args['button_text'] ?? __( 'مشاهده همه', 'diako' )
		);
		?>

		<div
			class="diako-carousel diako-carousel--track"
			data-diako-carousel="track"
			aria-roledescription="carousel"
			tabindex="0"
		>
			<div class="diako-carousel__track-wrap">
				<div class="diako-carousel__viewport" data-carousel-viewport>
					<ul class="products product-track !list-none" data-carousel-track dir="rtl">
						<?php
						while ( $query->have_posts() ) :
							$query->the_post();
							wc_get_template_part( 'content', 'product' );
						endwhile;
						wp_reset_postdata();
						?>
					</ul>
				</div>
				<?php diako_render_carousel_arrows( array( 'placement' => 'track' ) ); ?>
			</div>
			<?php diako_render_carousel_dots(); ?>
		</div>
	</div>
</section>
