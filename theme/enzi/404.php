<?php defined( 'ABSPATH' ) || exit; ?>
<?php
/**
 * 404 Not Found.
 *
 * @package Diako
 */

get_header();

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$mag_url  = function_exists( 'diako_get_mag_url' ) ? diako_get_mag_url() : home_url( '/mag/' );
?>
<div class="diako-error-page">
	<div class="diako-error-page__backdrop" aria-hidden="true"></div>

	<div class="container diako-error-page__inner">
		<div class="<?php echo esc_attr( diako_card_classes( 'diako-error-page__card' ) ); ?>">
			<div class="diako-error-page__badge" aria-hidden="true">
				<?php echo diako_lucide_icon_svg( 'sparkles', 'h-6 w-6' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<p class="diako-error-page__code" aria-hidden="true"><?php echo esc_html( function_exists( 'diako_number' ) ? diako_number( '404' ) : '404' ); ?></p>

			<div class="diako-error-page__content">
				<h1 class="diako-error-page__title"><?php esc_html_e( 'صفحه‌ای که دنبالش بودید پیدا نشد', 'diako' ); ?></h1>
				<p class="diako-error-page__desc">
					<?php esc_html_e( 'ممکن است آدرس اشتباه باشد یا این صفحه حذف شده باشد. می‌توانید جستجو کنید یا به بخش‌های اصلی سایت برگردید.', 'diako' ); ?>
				</p>
			</div>

			<form
				class="diako-error-page__search"
				role="search"
				method="get"
				action="<?php echo esc_url( home_url( '/' ) ); ?>"
			>
				<label class="sr-only" for="diako-error-search"><?php esc_html_e( 'جستجو در محصولات', 'diako' ); ?></label>
				<div class="diako-error-page__search-field">
					<span class="diako-error-page__search-icon" aria-hidden="true">
						<?php echo diako_lucide_icon_svg( 'search', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<input
						id="diako-error-search"
						class="diako-error-page__search-input"
						type="search"
						name="s"
						placeholder="<?php esc_attr_e( 'نام محصول را جستجو کنید…', 'diako' ); ?>"
						autocomplete="off"
					/>
					<input type="hidden" name="post_type" value="product" />
				</div>
				<button type="submit" class="<?php echo esc_attr( diako_button_classes( 'default', 'default', 'diako-error-page__search-submit shrink-0' ) ); ?>">
					<?php esc_html_e( 'جستجو', 'diako' ); ?>
				</button>
			</form>

			<div class="diako-error-page__actions">
				<?php
				diako_button(
					array(
						'href'    => home_url( '/' ),
						'label'    => __( 'صفحه اصلی', 'diako' ),
						'variant'  => 'default',
						'size'     => 'lg',
						'icon'     => 'arrow-left',
						'icon_class' => 'h-5 w-5',
					)
				);
				diako_button(
					array(
						'href'    => $shop_url,
						'label'    => __( 'فروشگاه', 'diako' ),
						'variant'  => 'outline',
						'size'     => 'lg',
						'icon'     => 'shopping-bag',
						'icon_class' => 'h-5 w-5',
					)
				);
				?>
			</div>

			<nav class="diako-error-page__links" aria-label="<?php esc_attr_e( 'مسیرهای پیشنهادی', 'diako' ); ?>">
				<a class="diako-error-page__link" href="<?php echo esc_url( $mag_url ); ?>">
					<?php echo diako_lucide_icon_svg( 'book-open', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php esc_html_e( 'مجله انزی', 'diako' ); ?></span>
				</a>
				<?php if ( function_exists( 'diako_get_discount_page_id' ) && diako_get_discount_page_id() ) : ?>
					<a class="diako-error-page__link" href="<?php echo esc_url( get_permalink( diako_get_discount_page_id() ) ); ?>">
						<?php echo diako_lucide_icon_svg( 'percent', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'محصولات تخفیف‌دار', 'diako' ); ?></span>
					</a>
				<?php endif; ?>
				<a class="diako-error-page__link" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' ) ); ?>">
					<?php echo diako_lucide_icon_svg( 'user', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php esc_html_e( 'حساب کاربری', 'diako' ); ?></span>
				</a>
			</nav>
		</div>
	</div>
</div>
<?php
get_footer();
