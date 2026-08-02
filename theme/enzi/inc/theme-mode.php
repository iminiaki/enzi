<?php
/**
 * Theme mode helpers.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline theme bootstrap script.
 *
 * @return void
 */
function diako_theme_bootstrap_script() {
	?>
	<script>
		(function () {
			try {
				var stored = localStorage.getItem('diako-theme');
				var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
				var theme = stored || (prefersDark ? 'dark' : 'light');
				var root = document.documentElement;
				root.classList.remove('dark');
				if (theme === 'dark') {
					root.classList.add('dark');
				}
				root.style.colorScheme = theme;
				root.dataset.theme = theme;
			} catch (e) {}
		})();
	</script>
	<?php
}

/**
 * Render theme toggle button.
 *
 * @param string $extra_class Extra button classes.
 * @return void
 */
function diako_theme_toggle( $extra_class = '' ) {
	?>
	<button
		type="button"
		class="<?php echo esc_attr( diako_button_classes( 'ghost', 'icon', diako_cn( 'theme-toggle', $extra_class ) ) ); ?>"
		data-theme-toggle
		aria-label="<?php esc_attr_e( 'تغییر حالت روشن/تاریک', 'diako' ); ?>"
	>
		<svg class="theme-icon theme-icon-sun hidden h-[18px] w-[18px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
		<svg class="theme-icon theme-icon-moon h-[18px] w-[18px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
	</button>
	<?php
}

/**
 * Section heading block.
 *
 * @param string $title       Title.
 * @param string $description Description.
 * @param string $action_url  Optional action URL.
 * @param string $action_text Optional action label.
 * @return void
 */
function diako_section_heading( $title, $description = '', $action_url = '', $action_text = '', $button_variant = 'ghost' ) {
	?>
	<div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
		<div class="space-y-2">
			<h2 class="text-2xl font-bold tracking-tight md:text-3xl"><?php echo esc_html( $title ); ?></h2>
			<?php if ( $description ) : ?>
				<p class="max-w-2xl text-muted-foreground"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $action_url && $action_text ) : ?>
			<?php
			diako_button(
				array(
					'href'    => $action_url,
					'label'   => $action_text,
					'variant' => 'outline',
				)
			);
			?>
		<?php endif; ?>
	</div>
	<?php
}
