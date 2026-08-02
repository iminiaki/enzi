<?php
/**
 * Single post sidebar: TOC, related posts, newsletter.
 *
 * @package Diako
 *
 * @var array<string, mixed> $args
 */

defined( 'ABSPATH' ) || exit;

$post         = isset( $args['post'] ) && $args['post'] instanceof WP_Post ? $args['post'] : get_post();
$toc_items    = diako_get_post_toc_items( $post );
$related      = diako_get_related_posts( $post, 3 );
$sidebar_label = __( 'ابزارهای مقاله', 'diako' );
?>
<div
	id="diako-post-drawer"
	class="diako-shop-sidebar-wrap"
	data-shop-drawer
	aria-hidden="true"
>
	<div class="diako-shop-sidebar-wrap__panel">
		<div class="diako-shop-sidebar-wrap__header lg:hidden">
			<h2 class="text-base font-semibold"><?php echo esc_html( $sidebar_label ); ?></h2>
			<button
				type="button"
				class="<?php echo esc_attr( diako_button_classes( 'ghost', 'icon' ) ); ?>"
				data-shop-drawer-close
				aria-label="<?php esc_attr_e( 'بستن', 'diako' ); ?>"
			>
				<?php echo diako_lucide_icon_svg( 'x', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>

		<aside class="diako-shop-sidebar diako-post-sidebar" aria-label="<?php echo esc_attr( $sidebar_label ); ?>">
			<?php if ( ! empty( $toc_items ) ) : ?>
				<?php
				$toc_visible_count = 4;
				$toc_total         = count( $toc_items );
				$toc_has_more      = $toc_total > $toc_visible_count;
				$toc_hidden_count  = max( 0, $toc_total - $toc_visible_count );
				?>
				<section class="diako-post-sidebar__panel diako-post-toc" data-post-toc>
					<div class="diako-post-sidebar__panel-head">
						<span class="diako-post-sidebar__panel-icon" aria-hidden="true">
							<?php echo diako_lucide_icon_svg( 'book-open', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<h2 class="diako-post-sidebar__panel-title"><?php esc_html_e( 'فهرست مطالب', 'diako' ); ?></h2>
					</div>
					<nav class="diako-post-toc__nav" aria-label="<?php esc_attr_e( 'فهرست مطالب', 'diako' ); ?>">
						<ol class="diako-post-toc__list">
							<?php foreach ( $toc_items as $index => $item ) : ?>
								<li class="diako-post-toc__item diako-post-toc__item--level-<?php echo esc_attr( (string) $item['level'] ); ?><?php echo ( $toc_has_more && $index >= $toc_visible_count ) ? ' diako-post-toc__item--collapsed' : ''; ?>">
									<a class="diako-post-toc__link" href="#<?php echo esc_attr( $item['id'] ); ?>" data-toc-link>
										<?php echo esc_html( $item['text'] ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ol>
						<?php if ( $toc_has_more ) : ?>
							<button
								type="button"
								class="diako-post-toc__toggle"
								data-toc-toggle
								aria-expanded="false"
								data-hidden-count="<?php echo esc_attr( (string) $toc_hidden_count ); ?>"
							>
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s: number of hidden TOC items */
										__( 'نمایش %s مورد دیگر', 'diako' ),
										function_exists( 'diako_number' ) ? diako_number( (string) $toc_hidden_count ) : (string) $toc_hidden_count
									)
								);
								?>
							</button>
						<?php endif; ?>
					</nav>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $related ) ) : ?>
				<section class="diako-post-sidebar__panel diako-post-related">
					<div class="diako-post-sidebar__panel-head">
						<span class="diako-post-sidebar__panel-icon" aria-hidden="true">
							<?php echo diako_lucide_icon_svg( 'layout-grid', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<h2 class="diako-post-sidebar__panel-title"><?php esc_html_e( 'مقالات مرتبط', 'diako' ); ?></h2>
					</div>
					<div class="diako-post-related__list">
						<?php foreach ( $related as $related_post ) : ?>
							<?php
							if ( ! $related_post instanceof WP_Post ) {
								continue;
							}
							?>
							<a class="diako-post-related__item" href="<?php echo esc_url( get_permalink( $related_post ) ); ?>">
								<span class="diako-post-related__media">
									<?php if ( has_post_thumbnail( $related_post ) ) : ?>
										<?php
										echo get_the_post_thumbnail(
											$related_post,
											'thumbnail',
											array(
												'class' => 'diako-post-related__image',
												'alt'   => esc_attr( get_the_title( $related_post ) ),
											)
										); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										?>
									<?php else : ?>
										<span class="diako-post-related__placeholder" aria-hidden="true">
											<?php echo diako_lucide_icon_svg( 'book-open', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</span>
									<?php endif; ?>
								</span>
								<span class="diako-post-related__title"><?php echo esc_html( get_the_title( $related_post ) ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<section class="diako-post-sidebar__panel diako-post-subscribe">
				<div class="diako-post-sidebar__panel-head">
					<span class="diako-post-sidebar__panel-icon" aria-hidden="true">
						<?php echo diako_lucide_icon_svg( 'mail', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<h2 class="diako-post-sidebar__panel-title"><?php esc_html_e( 'عضویت در خبرنامه', 'diako' ); ?></h2>
				</div>
				<p class="diako-post-subscribe__desc">
					<?php esc_html_e( 'جدیدترین مقالات مجله انزی را در ایمیل خود دریافت کنید.', 'diako' ); ?>
				</p>
				<form class="diako-post-subscribe__form" data-blog-subscribe-form novalidate>
					<label class="sr-only" for="diako-blog-subscribe-email"><?php esc_html_e( 'ایمیل', 'diako' ); ?></label>
					<input
						id="diako-blog-subscribe-email"
						class="<?php echo esc_attr( diako_input_classes( 'diako-post-subscribe__input' ) ); ?>"
						type="email"
						name="email"
						inputmode="email"
						autocomplete="email"
						required
						placeholder="<?php esc_attr_e( 'ایمیل خود را وارد کنید…', 'diako' ); ?>"
					>
					<button type="submit" class="<?php echo esc_attr( diako_button_classes( 'default', 'default', 'diako-post-subscribe__submit w-full' ) ); ?>">
						<?php esc_html_e( 'عضویت', 'diako' ); ?>
					</button>
				</form>
				<p class="diako-post-subscribe__notice" data-blog-subscribe-notice hidden></p>
			</section>
		</aside>
	</div>
</div>
