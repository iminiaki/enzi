<?php
/**
 * Blog / magazine archive helpers.
 *
 * @package Diako
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'body_class',
	function ( $classes ) {
		if ( function_exists( 'diako_blog_has_sidebar' ) && diako_blog_has_sidebar() ) {
			$classes[] = 'diako-blog-archive';
		}

		if ( function_exists( 'diako_is_single_post_page' ) && diako_is_single_post_page() ) {
			$classes[] = 'diako-post-single-page';
		}

		return $classes;
	}
);

/**
 * Magazine index URL.
 *
 * @return string
 */
function diako_get_mag_url() {
	$blog_page_id = (int) get_option( 'page_for_posts' );

	if ( $blog_page_id > 0 ) {
		return get_permalink( $blog_page_id );
	}

	$mag_page_id = diako_get_mag_page_id();

	if ( $mag_page_id ) {
		return get_permalink( $mag_page_id );
	}

	return home_url( '/mag/' );
}

/**
 * All published pages using the magazine template.
 *
 * @return array<int, WP_Post>
 */
function diako_get_mag_pages() {
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'page-mag.php',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);

	return is_array( $pages ) ? $pages : array();
}

/**
 * Magazine page ID if one exists.
 *
 * @return int
 */
function diako_get_mag_page_id() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$mag_page = get_page_by_path( 'mag' );

	if ( $mag_page instanceof WP_Post && 'publish' === $mag_page->post_status ) {
		$cached = (int) $mag_page->ID;
		update_option( 'diako_mag_page_id', $cached );
		return $cached;
	}

	$stored_id = (int) get_option( 'diako_mag_page_id' );

	if ( $stored_id && 'publish' === get_post_status( $stored_id ) ) {
		$cached = $stored_id;
		return $cached;
	}

	$pages = diako_get_mag_pages();

	if ( ! empty( $pages[0] ) ) {
		$cached = (int) $pages[0]->ID;
		update_option( 'diako_mag_page_id', $cached );
		return $cached;
	}

	$cached = 0;

	return $cached;
}

/**
 * Remove duplicate magazine pages, keeping the canonical one.
 *
 * @param int $keep_id Page ID to keep.
 * @return void
 */
function diako_cleanup_duplicate_mag_pages( $keep_id ) {
	$keep_id = (int) $keep_id;

	if ( ! $keep_id ) {
		return;
	}

	foreach ( diako_get_mag_pages() as $page ) {
		if ( (int) $page->ID === $keep_id ) {
			continue;
		}

		wp_trash_post( (int) $page->ID );
	}
}

/**
 * Create or repair the magazine page.
 *
 * @return int Page ID or 0 on failure.
 */
function diako_ensure_mag_page() {
	$pages = diako_get_mag_pages();

	if ( ! empty( $pages ) ) {
		$page_id = diako_get_mag_page_id();

		if ( $page_id && 'page-mag.php' !== get_page_template_slug( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', 'page-mag.php' );
		}

		diako_cleanup_duplicate_mag_pages( $page_id );

		return $page_id;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'   => sprintf(
				/* translators: %s: store brand name */
				__( 'مجله %s', 'diako' ),
				diako_get_brand_name()
			),
			'post_name'    => 'mag',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		),
		true
	);

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return 0;
	}

	update_post_meta( $page_id, '_wp_page_template', 'page-mag.php' );
	update_option( 'diako_mag_page_id', (int) $page_id );

	return (int) $page_id;
}

add_action(
	'after_setup_theme',
	function () {
		if ( get_option( 'diako_mag_page_bootstrapped' ) ) {
			return;
		}

		diako_ensure_mag_page();
		update_option( 'diako_mag_page_bootstrapped', 1 );
	},
	100
);

/**
 * Use the magazine archive layout for the mag page.
 *
 * @param string $template Current template path.
 * @return string
 */
function diako_mag_page_template( $template ) {
	if ( is_page() && is_page_template( 'page-mag.php' ) ) {
		$mag_template = locate_template( 'page-mag.php' );
		return $mag_template ? $mag_template : $template;
	}

	$mag_page_id = diako_get_mag_page_id();

	if ( $mag_page_id && is_page( $mag_page_id ) ) {
		$mag_template = locate_template( 'page-mag.php' );
		return $mag_template ? $mag_template : $template;
	}

	return $template;
}
add_filter( 'template_include', 'diako_mag_page_template', 20 );

/**
 * Whether the current view is the magazine index page.
 *
 * @return bool
 */
function diako_is_mag_page() {
	if ( ! empty( $GLOBALS['diako_is_mag_page'] ) ) {
		return true;
	}

	if ( is_home() && ! is_front_page() ) {
		return true;
	}

	if ( is_page_template( 'page-mag.php' ) ) {
		return true;
	}

	$mag_page_id = diako_get_mag_page_id();

	return $mag_page_id > 0 && is_page( $mag_page_id );
}

/**
 * Whether the current request is a blog archive with the magazine layout.
 *
 * @return bool
 */
function diako_is_blog_archive() {
	if ( is_admin() ) {
		return false;
	}

	if ( diako_is_mag_page() ) {
		return true;
	}

	if ( is_singular() ) {
		return false;
	}

	if ( is_category() || is_tag() || is_author() || is_date() ) {
		return true;
	}

	if ( is_archive() && ! ( function_exists( 'is_shop' ) && is_shop() ) && ! ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) ) {
		$post_type = get_query_var( 'post_type' );

		if ( empty( $post_type ) || 'post' === $post_type ) {
			return true;
		}

		if ( is_array( $post_type ) && in_array( 'post', $post_type, true ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Whether the blog sidebar should render.
 *
 * @return bool
 */
function diako_blog_has_sidebar() {
	return diako_is_blog_archive();
}

/**
 * Top-level post categories for the blog sidebar.
 *
 * @return array<int, WP_Term>
 */
function diako_get_blog_sidebar_categories() {
	$terms = get_categories(
		array(
			'hide_empty' => true,
			'parent'     => 0,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	return is_array( $terms ) ? $terms : array();
}

/**
 * Whether a blog sidebar category matches the current archive.
 *
 * @param WP_Term      $term         Category term.
 * @param WP_Term|null $current_term Current queried term.
 * @return bool
 */
function diako_is_blog_sidebar_category_active( WP_Term $term, $current_term = null ) {
	if ( ! $current_term instanceof WP_Term ) {
		if ( is_category() ) {
			$current_term = get_queried_object();
		}
	}

	if ( ! $current_term instanceof WP_Term ) {
		return false;
	}

	if ( (int) $term->term_id === (int) $current_term->term_id ) {
		return true;
	}

	return term_is_ancestor_of( (int) $term->term_id, (int) $current_term->term_id, 'category' );
}

/**
 * Child categories for an active parent in the blog sidebar.
 *
 * @param WP_Term $parent Parent category.
 * @return array<int, WP_Term>
 */
function diako_get_blog_sidebar_child_categories( WP_Term $parent ) {
	$terms = get_categories(
		array(
			'hide_empty' => true,
			'parent'     => (int) $parent->term_id,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	return is_array( $terms ) ? $terms : array();
}

/**
 * Whether the blog sidebar widget area has widgets.
 *
 * @return bool
 */
function diako_blog_sidebar_has_widgets() {
	return is_active_sidebar( 'blog-sidebar' );
}

/**
 * Render the blog sidebar.
 *
 * @return void
 */
function diako_render_blog_sidebar() {
	if ( ! diako_blog_has_sidebar() ) {
		return;
	}

	get_template_part( 'template-parts/blog/sidebar' );
}

/**
 * Render the mobile blog sidebar toggle.
 *
 * @return void
 */
function diako_render_blog_sidebar_toggle() {
	if ( ! diako_blog_has_sidebar() ) {
		return;
	}
	?>
	<button
		type="button"
		class="<?php echo esc_attr( diako_button_classes( 'outline', 'sm', 'lg:hidden' ) ); ?>"
		data-shop-drawer-toggle
		aria-controls="diako-blog-drawer"
		aria-expanded="false"
	>
		<?php echo diako_lucide_icon_svg( 'layout-grid', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php esc_html_e( 'دسته‌بندی مقالات', 'diako' ); ?>
	</button>
	<?php
}

/**
 * Blog archive page title.
 *
 * @return string
 */
function diako_get_blog_archive_title() {
	if ( diako_is_mag_page() ) {
		$mag_page_id = diako_get_mag_page_id();

		if ( $mag_page_id ) {
			return get_the_title( $mag_page_id );
		}

		$blog_page_id = (int) get_option( 'page_for_posts' );

		if ( $blog_page_id > 0 ) {
			return get_the_title( $blog_page_id );
		}

		return sprintf(
			/* translators: %s: store brand name */
			__( 'مجله %s', 'diako' ),
			diako_get_brand_name()
		);
	}

	if ( is_category() ) {
		return single_cat_title( '', false );
	}

	if ( is_tag() ) {
		return single_tag_title( '', false );
	}

	if ( is_author() ) {
		return get_the_author();
	}

	if ( is_date() ) {
		return wp_strip_all_tags( get_the_archive_title() );
	}

	if ( is_archive() ) {
		return wp_strip_all_tags( get_the_archive_title() );
	}

	return sprintf(
		/* translators: %s: store brand name */
		__( 'مجله %s', 'diako' ),
		diako_get_brand_name()
	);
}

/**
 * Blog archive description HTML.
 *
 * @return string
 */
function diako_get_blog_archive_description() {
	if ( diako_is_mag_page() ) {
		$mag_page_id = diako_get_mag_page_id();

		if ( $mag_page_id ) {
			$page = get_post( $mag_page_id );

			if ( $page instanceof WP_Post && $page->post_excerpt ) {
				return wpautop( esc_html( $page->post_excerpt ) );
			}
		}

		return '<p>' . esc_html(
			sprintf(
				/* translators: %s: store brand name */
				__( 'راهنماها، نکات خرید و مقالات %s', 'diako' ),
				diako_get_brand_name()
			)
		) . '</p>';
	}

	if ( is_category() || is_tag() ) {
		$description = term_description();

		if ( $description ) {
			return wp_kses_post( $description );
		}
	}

	if ( is_author() ) {
		$bio = get_the_author_meta( 'description' );

		if ( $bio ) {
			return wpautop( esc_html( $bio ) );
		}
	}

	return '';
}

/**
 * Render blog archive header.
 *
 * @return void
 */
function diako_render_blog_archive_header() {
	$title = diako_get_blog_archive_title();
	$desc  = diako_get_blog_archive_description();

	if ( '' === $title && '' === $desc ) {
		return;
	}
	?>
	<div class="diako-page-header">
		<div class="diako-page-header__content space-y-2">
			<?php if ( $title ) : ?>
				<h1 class="diako-page-title"><?php echo esc_html( $title ); ?></h1>
			<?php endif; ?>
			<?php if ( $desc ) : ?>
				<div class="diako-page-desc prose prose-neutral dark:prose-invert max-w-none"><?php echo wp_kses_post( $desc ); ?></div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Render blog post loop grid and pagination.
 *
 * @param WP_Query|null $query Optional query.
 * @return void
 */
function diako_render_blog_posts_loop( $query = null ) {
	if ( ! $query instanceof WP_Query ) {
		global $wp_query;
		$query = $wp_query;
	}

	if ( ! $query->have_posts() ) :
		?>
		<div class="<?php echo esc_attr( diako_card_classes( 'p-8 text-center text-muted-foreground' ) ); ?>">
			<?php esc_html_e( 'مقاله‌ای یافت نشد.', 'diako' ); ?>
		</div>
		<?php
		return;
	endif;
	?>
	<div class="diako-blog-grid">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();
			diako_render_post_card();
		endwhile;
		?>
	</div>

	<?php
	$pagination = paginate_links(
		array(
			'total'     => (int) $query->max_num_pages,
			'current'   => max( 1, (int) $query->get( 'paged' ), (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) ),
			'mid_size'  => 2,
			'prev_text' => diako_pagination_prev_link_content(),
			'next_text' => diako_pagination_next_link_content(),
			'type'      => 'list',
		)
	);

	if ( $pagination ) :
		?>
		<nav class="mt-10 woocommerce-pagination" aria-label="<?php esc_attr_e( 'صفحه‌بندی مقالات', 'diako' ); ?>">
			<?php echo wp_kses_post( $pagination ); ?>
		</nav>
		<?php
	endif;
}

/**
 * Render the full blog archive page shell.
 *
 * @param WP_Query|null $query Optional custom query.
 * @return void
 */
function diako_render_blog_archive_page( $query = null ) {
	$GLOBALS['diako_is_mag_page'] = $query instanceof WP_Query || diako_is_mag_page();

	get_header();
	?>
	<div class="diako-page diako-blog-page">
		<?php diako_render_blog_archive_header(); ?>

		<?php if ( diako_blog_has_sidebar() ) : ?>
			<div class="diako-shop-drawer-backdrop" data-shop-drawer-backdrop hidden aria-hidden="true"></div>
		<?php endif; ?>

		<div class="diako-shop-shell">
			<?php diako_render_blog_sidebar(); ?>

			<div class="diako-shop-main" data-blog-main>
				<?php if ( diako_blog_has_sidebar() ) : ?>
					<div class="mb-6 lg:hidden">
						<?php diako_render_blog_sidebar_toggle(); ?>
					</div>
				<?php endif; ?>

				<?php diako_render_blog_posts_loop( $query ); ?>
			</div>
		</div>
	</div>
	<?php
	get_footer();

	if ( $query instanceof WP_Query ) {
		wp_reset_postdata();
	}

	unset( $GLOBALS['diako_is_mag_page'] );
}

define( 'DIAKO_BLOG_NEWSLETTER_OPTION', 'diako_blog_newsletter_emails' );

/**
 * Whether the current view is a single blog post.
 *
 * @return bool
 */
function diako_is_single_post_page(): bool {
	return is_singular( 'post' );
}

/**
 * Create a unique heading anchor ID.
 *
 * @param string        $text Heading text.
 * @param array<string> $used Already used IDs.
 * @return string
 */
function diako_get_post_heading_id( string $text, array &$used ): string {
	$base = sanitize_title( wp_strip_all_tags( $text ) );

	if ( '' === $base ) {
		$base = 'section';
	}

	$id  = $base;
	$idx = 2;

	while ( in_array( $id, $used, true ) ) {
		$id = $base . '-' . $idx;
		++$idx;
	}

	$used[] = $id;

	return $id;
}

/**
 * Parse headings from HTML content.
 *
 * @param string $content HTML content.
 * @return array<int, array{id: string, level: int, text: string}>
 */
function diako_parse_post_headings_from_html( string $content ): array {
	if ( '' === trim( $content ) ) {
		return array();
	}

	if ( ! preg_match_all( '/<h([2-3])([^>]*)>(.*?)<\/h\1>/is', $content, $matches, PREG_SET_ORDER ) ) {
		return array();
	}

	$items = array();
	$used  = array();

	foreach ( $matches as $match ) {
		$text = trim( wp_strip_all_tags( $match[3] ) );

		if ( '' === $text ) {
			continue;
		}

		$items[] = array(
			'id'    => diako_get_post_heading_id( $text, $used ),
			'level' => (int) $match[1],
			'text'  => $text,
		);
	}

	return $items;
}

/**
 * Prepare heading anchors for a single post before rendering.
 *
 * @param WP_Post|int|null $post Post object or ID.
 * @return array<int, array{id: string, level: int, text: string}>
 */
function diako_prepare_single_post_headings( $post = null ): array {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return array();
	}

	if ( isset( $GLOBALS['diako_post_toc_items'][ $post->ID ] ) ) {
		return $GLOBALS['diako_post_toc_items'][ $post->ID ];
	}

	$content = (string) $post->post_content;

	if ( function_exists( 'do_blocks' ) ) {
		$content = do_blocks( $content );
	}

	$items = diako_parse_post_headings_from_html( $content );

	$GLOBALS['diako_post_toc_items'][ $post->ID ]   = $items;
	$GLOBALS['diako_post_heading_queue'][ $post->ID ] = $items;

	return $items;
}

/**
 * Parse post headings for the table of contents.
 *
 * @param WP_Post|int|null $post Post object or ID.
 * @return array<int, array{id: string, level: int, text: string}>
 */
function diako_get_post_toc_items( $post = null ): array {
	return diako_prepare_single_post_headings( $post );
}

/**
 * Add stable IDs to h2/h3 headings in single post content.
 *
 * @param string $content Post content HTML.
 * @return string
 */
function diako_add_post_heading_ids( string $content ): string {
	if ( ! is_singular( 'post' ) || is_admin() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$post_id = get_the_ID();
	$queue   = diako_prepare_single_post_headings( $post_id );
	$index   = 0;

	if ( empty( $queue ) ) {
		return $content;
	}

	return (string) preg_replace_callback(
		'/<h([2-3])([^>]*)>(.*?)<\/h\1>/is',
		static function ( array $match ) use ( $queue, &$index ): string {
			$level = $match[1];
			$attrs = $match[2];
			$text  = trim( wp_strip_all_tags( $match[3] ) );
			$inner = $match[3];

			if ( '' === $text || preg_match( '/\sid=(["\']).*?\1/i', $attrs ) ) {
				return $match[0];
			}

			if ( ! isset( $queue[ $index ] ) ) {
				return $match[0];
			}

			$item = $queue[ $index ];
			++$index;

			if ( $text !== $item['text'] ) {
				return $match[0];
			}

			return sprintf(
				'<h%1$s id="%2$s"%3$s>%4$s</h%1$s>',
				$level,
				esc_attr( $item['id'] ),
				$attrs,
				$inner
			);
		},
		$content
	);
}
add_filter( 'the_content', 'diako_add_post_heading_ids', 5 );

/**
 * Related posts for the single post sidebar.
 *
 * @param WP_Post|int|null $post  Post object or ID.
 * @param int              $limit Number of posts.
 * @return array<int, WP_Post>
 */
function diako_get_related_posts( $post = null, int $limit = 3 ): array {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return array();
	}

	$categories = wp_get_post_categories( $post->ID );

	$query_args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => max( 1, $limit ),
		'post__not_in'        => array( (int) $post->ID ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	if ( ! empty( $categories ) ) {
		$query_args['category__in'] = $categories;
	}

	$query = new WP_Query( $query_args );

	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		unset( $query_args['category__in'] );
		$query = new WP_Query( $query_args );
	}

	$posts = $query->have_posts() ? $query->posts : array();

	wp_reset_postdata();

	return is_array( $posts ) ? $posts : array();
}

/**
 * Render share actions for a single post.
 *
 * @param WP_Post|int|null $post Post object or ID.
 * @return void
 */
function diako_render_post_share_buttons( $post = null ): void {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$url   = get_permalink( $post );
	$title = get_the_title( $post );
	?>
	<div class="diako-post-share" data-post-share>
		<span class="diako-post-share__label"><?php esc_html_e( 'اشتراک‌گذاری', 'diako' ); ?></span>
		<div class="diako-post-share__actions">
			<a
				class="diako-post-share__button"
				href="<?php echo esc_url( 'https://t.me/share/url?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( $title ) ); ?>"
				target="_blank"
				rel="noopener noreferrer"
				aria-label="<?php esc_attr_e( 'اشتراک در تلگرام', 'diako' ); ?>"
			>
				<?php echo diako_lucide_icon_svg( 'telegram', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
			<a
				class="diako-post-share__button"
				href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( $title . ' ' . $url ) ); ?>"
				target="_blank"
				rel="noopener noreferrer"
				aria-label="<?php esc_attr_e( 'اشتراک در واتساپ', 'diako' ); ?>"
			>
				<?php echo diako_lucide_icon_svg( 'whatsapp', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
			<button
				type="button"
				class="diako-post-share__button"
				data-share-copy
				data-share-url="<?php echo esc_url( $url ); ?>"
				aria-label="<?php esc_attr_e( 'کپی لینک', 'diako' ); ?>"
				title="<?php esc_attr_e( 'کپی لینک', 'diako' ); ?>"
			>
				<?php echo diako_lucide_icon_svg( 'copy', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>
	</div>
	<?php
}

/**
 * Render the mobile sidebar toggle on single posts.
 *
 * @return void
 */
function diako_render_single_post_sidebar_toggle(): void {
	?>
	<button
		type="button"
		class="<?php echo esc_attr( diako_button_classes( 'outline', 'sm', 'lg:hidden' ) ); ?>"
		data-shop-drawer-toggle
		aria-controls="diako-post-drawer"
		aria-expanded="false"
	>
		<?php echo diako_lucide_icon_svg( 'book-open', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php esc_html_e( 'فهرست و ابزارها', 'diako' ); ?>
	</button>
	<?php
}

/**
 * Render the single post sidebar.
 *
 * @param WP_Post|int|null $post Post object or ID.
 * @return void
 */
function diako_render_single_post_sidebar( $post = null ): void {
	get_template_part(
		'template-parts/blog/single-sidebar',
		null,
		array(
			'post' => get_post( $post ),
		)
	);
}

/**
 * Render the full single post page shell.
 *
 * @return void
 */
function diako_render_single_post_page(): void {
	if ( ! have_posts() ) {
		get_header();
		echo '<div class="container py-16 md:py-20"><p class="text-muted-foreground">' . esc_html__( 'مقاله‌ای یافت نشد.', 'diako' ) . '</p></div>';
		get_footer();
		return;
	}

	get_header();

	while ( have_posts() ) :
		the_post();
		?>
		<div class="diako-page diako-blog-page diako-post-single-page">
			<div class="diako-shop-drawer-backdrop" data-shop-drawer-backdrop hidden aria-hidden="true"></div>

			<div class="container pb-16 md:pb-20">
				<div class="diako-shop-shell">
					<?php diako_render_single_post_sidebar(); ?>

					<div class="diako-shop-main" data-blog-main>
						<div class="mb-6 lg:hidden">
							<?php diako_render_single_post_sidebar_toggle(); ?>
						</div>

						<?php get_template_part( 'template-parts/blog/single-content' ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	endwhile;

	get_footer();
}

/**
 * AJAX: subscribe to blog newsletter.
 */
function diako_ajax_blog_newsletter_subscribe(): void {
	check_ajax_referer( 'diako_blog_newsletter', 'nonce' );
	diako_require_recaptcha_for_ajax( 'newsletter_subscribe' );

	if ( diako_rate_limit_block( 'blog_newsletter', 3, 900 ) ) {
		diako_rate_limit_json_error();
	}

	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );

	if ( ! is_email( $email ) ) {
		wp_send_json_error(
			array( 'message' => __( 'لطفاً یک ایمیل معتبر وارد کنید.', 'diako' ) ),
			400
		);
	}

	$emails   = get_option( DIAKO_BLOG_NEWSLETTER_OPTION, array() );
	$emails   = is_array( $emails ) ? $emails : array();
	$existing = array_map(
		static function ( $row ) {
			return is_array( $row ) ? strtolower( (string) ( $row['email'] ?? '' ) ) : '';
		},
		$emails
	);

	if ( in_array( strtolower( $email ), $existing, true ) ) {
		wp_send_json_success(
			array( 'message' => __( 'ایمیل شما قبلاً ثبت شده است.', 'diako' ) )
		);
	}

	$added = diako_append_email_subscription( DIAKO_BLOG_NEWSLETTER_OPTION, $email );

	if ( is_wp_error( $added ) ) {
		wp_send_json_error(
			array( 'message' => $added->get_error_message() ),
			503
		);
	}

	wp_send_json_success(
		array( 'message' => __( 'عضویت شما با موفقیت ثبت شد.', 'diako' ) )
	);
}
add_action( 'wp_ajax_nopriv_diako_blog_newsletter_subscribe', 'diako_ajax_blog_newsletter_subscribe' );
add_action( 'wp_ajax_diako_blog_newsletter_subscribe', 'diako_ajax_blog_newsletter_subscribe' );

/**
 * Localize single post scripts.
 */
function diako_localize_single_post_script(): void {
	if ( ! diako_is_single_post_page() ) {
		return;
	}

	wp_localize_script(
		'diako-main',
		'diakoBlogSingle',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'diako_blog_newsletter' ),
			'i18n'    => array(
				'copied'       => __( 'لینک کپی شد.', 'diako' ),
				'copyFailed'   => __( 'کپی لینک انجام نشد.', 'diako' ),
				'submitError'  => __( 'خطا در ثبت ایمیل. دوباره تلاش کنید.', 'diako' ),
				'tocShowMore'  => __( 'نمایش %d مورد دیگر', 'diako' ),
				'tocShowLess'  => __( 'نمایش کمتر', 'diako' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'diako_localize_single_post_script', 120 );
