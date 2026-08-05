<?php defined( 'ABSPATH' ) || exit; ?>
</main>

<footer class="mt-auto border-t border-border bg-card">
	<div class="container grid gap-10 py-14 md:grid-cols-2 lg:grid-cols-4">
		<div class="space-y-4 lg:col-span-1">
			<?php
			diako_logo(
				array(
					'class'      => 'diako-logo__image--footer',
					'link_class' => 'diako-logo inline-flex items-center',
				)
			);
			?>
			<p class="text-sm leading-7 text-muted-foreground">
				<?php echo esc_html( get_bloginfo( 'description' ) ); ?>
			</p>
			<ul class="space-y-2 text-sm text-muted-foreground">
				<?php $contact = diako_get_company_contact_details(); ?>
				<li class="flex gap-2">
					<?php echo diako_lucide_icon_svg( 'map-pin', 'mt-0.5 shrink-0' ); // phpcs:ignore ?>
					<span><?php echo esc_html( $contact['address'] ); ?></span>
				</li>
				<li class="flex items-center gap-2">
					<?php echo diako_lucide_icon_svg( 'phone', 'shrink-0' ); // phpcs:ignore ?>
					<?php diako_render_company_phone_link(); ?>
				</li>
				<li class="flex items-center gap-2">
					<?php echo diako_lucide_icon_svg( 'mail', 'shrink-0' ); // phpcs:ignore ?>
					<a class="hover:text-foreground" href="mailto:<?php echo esc_attr( $contact['email'] ); ?>"><?php echo esc_html( $contact['email'] ); ?></a>
				</li>
			</ul>
		</div>

		<div class="space-y-4">
			<h2 class="text-sm font-semibold"><?php esc_html_e( 'دسترسی سریع', 'diako' ); ?></h2>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'menu space-y-2',
					'fallback_cb'    => 'diako_footer_fallback_menu',
				)
			);
			?>
		</div>

		<div class="space-y-4">
			<h2 class="text-sm font-semibold"><?php esc_html_e( 'دسته‌بندی محصولات', 'diako' ); ?></h2>
			<ul class="menu space-y-2 text-sm">
				<?php
				$exclude_cats = array_filter( array( (int) get_option( 'default_product_cat', 0 ) ) );
				$cats           = get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'hide_empty' => false,
						'parent'     => 0,
						'number'     => 8,
						'exclude'    => $exclude_cats,
					)
				);
				if ( ! is_wp_error( $cats ) && ! empty( $cats ) ) :
					foreach ( $cats as $term ) :
						?>
						<li><a class="text-muted-foreground hover:text-foreground" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a></li>
					<?php endforeach;
				else :
					?>
					<li><a class="text-muted-foreground hover:text-foreground" href="<?php echo esc_url( home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'همه محصولات', 'diako' ); ?></a></li>
					<li><a class="text-muted-foreground hover:text-foreground" href="<?php echo esc_url( home_url( '/discount/' ) ); ?>"><?php esc_html_e( 'تخفیف‌ها', 'diako' ); ?></a></li>
				<?php endif; ?>
			</ul>
		</div>

		<div class="space-y-4">
			<h2 class="text-sm font-semibold"><?php esc_html_e( 'نمادها', 'diako' ); ?></h2>
			<?php diako_render_trust_badges(); ?>
			<?php diako_render_social_links( 'pt-2' ); ?>
		</div>
	</div>

	<div class="border-t border-border">
		<div class="container flex flex-col items-start justify-between gap-3 py-6 text-sm text-muted-foreground md:flex-row md:items-center">
			<p><?php echo esc_html( diako_get_footer_copyright_text() ); ?></p>
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						__( 'طراحی و توسعه %s', 'diako' ),
						'<a class="font-medium text-primary underline underline-offset-2 transition-colors hover:text-primary/80" href="https://lastaar.com" target="_blank" rel="noopener noreferrer">' . esc_html__( 'لستار', 'diako' ) . '</a>'
					)
				);
				?>
			</p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
