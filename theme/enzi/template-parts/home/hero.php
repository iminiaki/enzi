<?php defined( 'ABSPATH' ) || exit; ?>
<?php
/**
 * Homepage hero carousel.
 *
 * @package Enzi
 */

$slides   = diako_get_hero_slides();
$settings = diako_get_theme_settings();
$hero     = $settings['hero'] ?? array();

if ( empty( $slides ) ) {
	return;
}
?>

<section class="diako-hero-section">
	<div class="diako-hero-section__container">
		<div
			class="diako-carousel diako-carousel--fade"
			data-diako-carousel="fade"
			aria-roledescription="carousel"
			aria-label="<?php esc_attr_e( 'اسلایدر صفحه اصلی', 'diako' ); ?>"
			tabindex="0"
		>
			<div class="diako-carousel__frame diako-hero-carousel__frame">
				<?php diako_render_hero_slides(); ?>

				<?php diako_render_carousel_arrows( array( 'placement' => 'overlay' ) ); ?>
				<?php diako_render_carousel_dots(); ?>
			</div>
		</div>

		<div class="relative z-10 mx-auto mt-8 max-w-3xl space-y-5 text-center">
			<?php if ( ! empty( $hero['badge'] ) ) : ?>
				<span class="<?php echo esc_attr( diako_badge_classes( 'outline' ) ); ?>">
					<?php echo esc_html( $hero['badge'] ); ?>
				</span>
			<?php endif; ?>

			<?php if ( ! empty( $hero['title'] ) ) : ?>
				<h1 class="text-3xl font-bold leading-tight tracking-tight md:text-5xl">
					<?php echo esc_html( $hero['title'] ); ?>
				</h1>
			<?php endif; ?>

			<?php if ( ! empty( $hero['description'] ) ) : ?>
				<p class="text-base leading-8 text-muted-foreground md:text-lg">
					<?php echo esc_html( $hero['description'] ); ?>
				</p>
			<?php endif; ?>

			<div class="flex flex-wrap justify-center gap-3">
				<?php
				if ( ! empty( $hero['cta_primary_label'] ) && ! empty( $hero['cta_primary_url'] ) ) {
					diako_button(
						array(
							'href'    => diako_theme_settings_url( $hero['cta_primary_url'] ),
							'label'   => $hero['cta_primary_label'],
							'variant' => 'default',
							'size'    => 'lg',
						)
					);
				}

				if ( ! empty( $hero['cta_secondary_label'] ) && ! empty( $hero['cta_secondary_url'] ) ) {
					diako_button(
						array(
							'href'    => diako_theme_settings_url( $hero['cta_secondary_url'] ),
							'label'   => $hero['cta_secondary_label'],
							'variant' => 'outline',
							'size'    => 'lg',
						)
					);
				}
				?>
			</div>
		</div>
	</div>
</section>
