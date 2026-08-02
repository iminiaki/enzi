<?php defined( 'ABSPATH' ) || exit; ?>
<?php
/**
 * Post archives.
 *
 * @package Diako
 */

if ( diako_is_blog_archive() ) {
	diako_render_blog_archive_page();
	return;
}

get_header();
?>
<div class="container py-16 md:py-20">
	<?php if ( have_posts() ) : ?>
		<div class="diako-blog-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				diako_render_post_card();
			endwhile;
			?>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
