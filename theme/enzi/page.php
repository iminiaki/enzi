<?php defined( 'ABSPATH' ) || exit; ?>
<?php
/**
 * Page template.
 *
 * @package Diako
 */

$is_login_default_layout = function_exists( 'diako_is_login_default_layout_page' ) && diako_is_login_default_layout_page();

if ( $is_login_default_layout ) {
	diako_render_login_default_document_start();

	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;

	diako_render_login_default_document_end();
	return;
}

get_header();

if ( function_exists( 'diako_is_checkout_page' ) && diako_is_checkout_page() ) {
	diako_render_checkout_page_layout();
	get_footer();
	return;
}

$is_wc_cart = function_exists( 'is_cart' ) && is_cart();
$is_account = function_exists( 'is_account_page' ) && is_account_page();
?>
<?php if ( $is_wc_cart ) : ?>
	<div class="diako-page woocommerce-page-wrap diako-cart-page">
		<div class="diako-cart">
			<?php diako_render_cart_page_header(); ?>
			<?php
			while ( have_posts() ) :
				the_post();
				the_content();
			endwhile;
			?>
		</div>
	</div>
<?php elseif ( $is_account && is_user_logged_in() ) : ?>
	<div class="diako-page woocommerce-page-wrap">
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
	</div>
<?php else : ?>
	<div class="container py-16 md:py-20">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'mx-auto' ); ?>>
				<?php if ( ! is_front_page() ) : ?>
					<header class="mb-8 space-y-2 border-b border-border pb-6">
						<h1 class="text-2xl font-bold tracking-tight md:text-3xl"><?php the_title(); ?></h1>
					</header>
				<?php endif; ?>
				<div class="prose prose-neutral dark:prose-invert max-w-none entry-content">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>
	</div>
<?php endif; ?>
<?php
get_footer();
