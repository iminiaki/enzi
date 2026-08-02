<?php defined( 'ABSPATH' ) || exit; ?>
<?php
/**
 * Homepage categories.
 *
 * @package Lastify
 */

$settings   = diako_get_theme_settings();
$section    = $settings['categories'] ?? array();
$categories = $section['items'] ?? array();

if ( empty( $categories ) ) {
	return;
}
?>

<section class="py-16 md:py-20">
	<div class="container">
		<?php
		diako_section_heading(
			$section['title'] ?? '',
			$section['description'] ?? '',
			diako_theme_settings_url( $section['button_url'] ?? '' ),
			$section['button_text'] ?? ''
		);
		?>

		<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
			<?php foreach ( $categories as $category ) : ?>
				<?php
				$title = $category['title'] ?? '';
				$desc  = $category['desc'] ?? '';
				$link  = diako_theme_settings_url( $category['link'] ?? '' );
				$icon  = $category['icon'] ?? 'sparkles';

				if ( '' === $title || '' === $link ) {
					continue;
				}
				?>
				<a href="<?php echo esc_url( $link ); ?>" class="<?php echo esc_attr( diako_card_classes( 'group p-6 hover:border-primary/50 hover:bg-accent/40' ) ); ?>">
					<div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-muted text-primary">
						<?php echo diako_lucide_icon_svg( $icon, 'h-8 w-8' ); // phpcs:ignore ?>
					</div>
					<h3 class="text-lg font-semibold group-hover:text-primary"><?php echo esc_html( $title ); ?></h3>
					<?php if ( '' !== $desc ) : ?>
						<p class="mt-2 text-sm text-muted-foreground"><?php echo esc_html( $desc ); ?></p>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
