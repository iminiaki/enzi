<?php defined( 'ABSPATH' ) || exit; ?>
<?php
/**
 * Cart page template.
 *
 * @package Diako
 */

get_header();
?>
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
<?php
get_footer();
