<?php
/**
 * Homepage promo banner.
 *
 * @package Diako
 */

defined( 'ABSPATH' ) || exit;

$image_id  = absint( $args['image_id'] ?? 0 );
$image_url = trim( (string) ( $args['image_url'] ?? '' ) );
$filename  = isset( $args['filename'] ) ? (string) $args['filename'] : '';
$fallback  = isset( $args['fallback'] ) ? (string) $args['fallback'] : '';
$url       = isset( $args['url'] ) ? (string) $args['url'] : '';
$alt       = isset( $args['alt'] ) ? (string) $args['alt'] : '';

$banner_url = '';

if ( $image_id > 0 ) {
	$banner_url = (string) wp_get_attachment_image_url( $image_id, 'full' );
}

if ( '' === $banner_url && '' !== $image_url ) {
	$banner_url = $image_url;
}

if ( '' === $banner_url && '' !== $filename ) {
	$banner_url = diako_get_media_image_url( $filename, $fallback );
}

if ( '' === $banner_url ) {
	return;
}
?>

<section class="diako-home-promo-banner">
	<div class="container">
		<?php if ( '' !== $url ) : ?>
			<a
				href="<?php echo esc_url( $url ); ?>"
				class="diako-home-promo-banner__link group block overflow-hidden"
			>
				<img
					class="diako-home-promo-banner__image"
					src="<?php echo esc_url( $banner_url ); ?>"
					alt="<?php echo esc_attr( $alt ); ?>"
					width="1280"
					height="400"
					loading="lazy"
					decoding="async"
				/>
			</a>
		<?php else : ?>
			<div class="diako-home-promo-banner__frame overflow-hidden">
				<img
					class="diako-home-promo-banner__image"
					src="<?php echo esc_url( $banner_url ); ?>"
					alt="<?php echo esc_attr( $alt ); ?>"
					width="1280"
					height="400"
					loading="lazy"
					decoding="async"
				/>
			</div>
		<?php endif; ?>
	</div>
</section>
