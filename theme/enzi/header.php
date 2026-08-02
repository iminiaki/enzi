<?php defined( 'ABSPATH' ) || exit; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php diako_theme_bootstrap_script(); ?>
	<?php
	if ( function_exists( 'diako_print_critical_font_assets' ) ) {
		diako_print_critical_font_assets();
	}
	if ( function_exists( 'diako_print_critical_lcp_preload' ) ) {
		diako_print_critical_lcp_preload();
	}
	list( $css_uri, $css_version ) = diako_theme_css_asset();
	if ( function_exists( 'diako_print_theme_stylesheet' ) ) {
		diako_print_theme_stylesheet( $css_uri, $css_version );
	} else {
		?>
	<link rel="stylesheet" href="<?php echo esc_url( $css_uri ); ?>?ver=<?php echo esc_attr( $css_version ); ?>" id="diako-theme-css" />
		<?php
	}
	?>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'flex min-h-screen flex-col' ); ?>>
<?php wp_body_open(); ?>

<?php
$header_class = 'site-header sticky top-0 z-50 w-full transition-[background-color,border-color,backdrop-filter] duration-300';
if ( is_front_page() ) {
	$header_class .= ' site-header--overlay';
} else {
	$header_class .= ' border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60';
}
?>
<header class="<?php echo esc_attr( $header_class ); ?>">
	<div class="container">
		<div class="flex h-16 items-center justify-between gap-3">
			<?php
			diako_logo(
				array(
					'class'      => 'diako-logo__image',
					'link_class' => 'diako-logo flex shrink-0 items-center',
				)
			);
			?>

			<nav class="site-nav hidden flex-1 justify-center lg:flex" aria-label="<?php esc_attr_e( 'منوی اصلی', 'diako' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu'           => 41,
						'container'      => false,
						'menu_class'     => 'menu',
						'menu_id'        => 'primary-menu',
						'fallback_cb'    => 'diako_fallback_menu',
					)
				);
				?>
			</nav>

			<div class="flex items-center gap-2">
				<?php diako_render_header_search_toggle(); ?>

				<?php diako_render_header_login_link(); ?>
				<?php diako_render_account_icon_link( 'hidden lg:inline-flex' ); ?>

				<a
					href="<?php echo esc_url( wc_get_cart_url() ); ?>"
					class="<?php echo esc_attr( diako_button_classes( 'ghost', 'icon' ) ); ?> relative inline-flex"
					aria-label="<?php esc_attr_e( 'سبد خرید', 'diako' ); ?>"
					data-diako-cart-link
				>
					<?php echo diako_lucide_icon_svg( 'shopping-bag', 'h-5 w-5' ); // phpcs:ignore ?>
					<?php
					if ( function_exists( 'diako_get_cart_count_badge_html' ) ) {
						echo diako_get_cart_count_badge_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} elseif ( function_exists( 'WC' ) && WC()->cart && WC()->cart->get_cart_contents_count() > 0 ) {
						?>
						<span class="absolute -top-0.5 -start-0.5 flex size-5 items-center justify-center rounded-full bg-brand-orange text-[10px] font-bold text-white" data-diako-cart-count>
							<?php echo esc_html( diako_number( WC()->cart->get_cart_contents_count() ) ); ?>
						</span>
						<?php
					}
					?>
				</a>

				<button
					class="<?php echo esc_attr( diako_button_classes( 'outline', 'icon', 'lg:hidden' ) ); ?>"
					type="button"
					data-nav-toggle
					aria-controls="mobile-menu"
					aria-label="<?php esc_attr_e( 'باز کردن منو', 'diako' ); ?>"
					aria-expanded="false"
				>
					<svg class="diako-nav-icon-open" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
					<svg class="diako-nav-icon-close hidden" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
				</button>

				<?php diako_theme_toggle( 'hidden lg:inline-flex' ); ?>
			</div>
		</div>
	</div>
</header>

<?php diako_render_header_search_overlay(); ?>

<div class="diako-mobile-nav-backdrop" data-mobile-nav-backdrop hidden aria-hidden="true"></div>

<aside
	id="mobile-menu"
	class="diako-mobile-nav"
	data-mobile-nav
	aria-hidden="true"
	aria-label="<?php esc_attr_e( 'منوی موبایل', 'diako' ); ?>"
>
	<div class="diako-mobile-nav__panel">
		<div class="diako-mobile-nav__header">
			<span class="text-sm font-semibold"><?php esc_html_e( 'منو', 'diako' ); ?></span>
			<button
				class="<?php echo esc_attr( diako_button_classes( 'ghost', 'icon', 'h-10 w-10' ) ); ?>"
				type="button"
				data-nav-close
				aria-label="<?php esc_attr_e( 'بستن منو', 'diako' ); ?>"
			>
				<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
			</button>
		</div>
		<div class="diako-mobile-nav__content">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'menu'           => 41,
					'container'      => false,
					'menu_class'     => 'menu',
					'menu_id'        => 'mobile-primary-menu',
					'fallback_cb'    => 'diako_fallback_menu',
				)
			);
			?>
			<div class="diako-mobile-nav__actions diako-mobile-nav__actions--tools">
				<?php diako_theme_toggle( 'diako-mobile-nav__theme-toggle' ); ?>
			</div>
		</div>
	</div>
</aside>

<?php diako_render_breadcrumbs(); ?>

<main class="flex-1">
