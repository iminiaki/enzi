<?php defined( 'ABSPATH' ) || exit; ?>
<?php
/**
 * Homepage category banners.
 *
 * @package Enzi
 */

$settings = diako_get_theme_settings();
$banners  = $settings['banners']['items'] ?? array();
$items    = array();

foreach ( $banners as $banner ) {
	$image_url = diako_resolve_banner_image_url( $banner );
	$url       = diako_theme_settings_url( $banner['url'] ?? '' );

	if ( '' === $image_url || '' === $url ) {
		continue;
	}

	$items[] = array(
		'image_id'  => absint( $banner['image_id'] ?? 0 ),
		'image_url' => $image_url,
		'url'       => $url,
		'alt'       => trim( (string) ( $banner['alt'] ?? '' ) ) ?: __( 'بنر تبلیغاتی', 'diako' ),
	);
}

if ( empty( $items ) ) {
	return;
}
?>

<section class="py-16 md:py-20">
	<div class="container grid gap-4 md:grid-cols-2">
		<?php foreach ( $items as $item ) : ?>
			<a href="<?php echo esc_url( $item['url'] ); ?>" class="<?php echo esc_attr( diako_card_classes( 'group overflow-hidden p-0 hover:border-primary/50' ) ); ?>">
				<?php
				if ( ! empty( $item['image_id'] ) ) {
					echo wp_get_attachment_image(
						(int) $item['image_id'],
						'diako-banner',
						false,
						array(
							'class'   => 'aspect-[2/1] w-full object-cover transition-transform duration-500 group-hover:scale-105',
							'loading' => 'lazy',
							'alt'     => $item['alt'],
							'sizes'   => '(max-width: 768px) 100vw, 640px',
						)
					);
				} else {
					?>
					<img src="<?php echo esc_url( $item['image_url'] ); ?>" alt="<?php echo esc_attr( $item['alt'] ); ?>" class="aspect-[2/1] w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" decoding="async" width="640" height="320">
					<?php
				}
				?>
			</a>
		<?php endforeach; ?>
	</div>
</section>
