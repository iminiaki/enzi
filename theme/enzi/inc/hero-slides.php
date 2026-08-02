<?php
/**
 * Homepage hero slider slides.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	function () {
		register_post_type(
			'diako_hero_slide',
			array(
				'labels'              => array(
					'name'               => __( 'اسلایدر صفحه اصلی', 'diako' ),
					'singular_name'      => __( 'اسلاید', 'diako' ),
					'add_new'            => __( 'افزودن اسلاید', 'diako' ),
					'add_new_item'       => __( 'افزودن اسلاید جدید', 'diako' ),
					'edit_item'          => __( 'ویرایش اسلاید', 'diako' ),
					'new_item'           => __( 'اسلاید جدید', 'diako' ),
					'view_item'          => __( 'مشاهده اسلاید', 'diako' ),
					'search_items'       => __( 'جستجوی اسلاید', 'diako' ),
					'not_found'          => __( 'اسلایدی یافت نشد.', 'diako' ),
					'not_found_in_trash' => __( 'اسلایدی در زباله‌دان یافت نشد.', 'diako' ),
					'menu_name'          => __( 'اسلایدر صفحه اصلی', 'diako' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'menu_position'       => 25,
				'menu_icon'           => 'dashicons-images-alt2',
				'capability_type'     => 'page',
				'map_meta_cap'        => true,
				'hierarchical'        => false,
				'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'exclude_from_search' => true,
			)
		);
	}
);

add_action(
	'add_meta_boxes',
	function () {
		add_meta_box(
			'diako-hero-slide-link',
			__( 'تنظیمات اسلاید', 'diako' ),
			'diako_render_hero_slide_meta_box',
			'diako_hero_slide',
			'side',
			'default'
		);
	}
);

/**
 * Render hero slide meta box.
 *
 * @param WP_Post $post Post object.
 * @return void
 */
function diako_render_hero_slide_meta_box( WP_Post $post ) {
	wp_nonce_field( 'diako_save_hero_slide_meta', 'diako_hero_slide_nonce' );

	$url = (string) get_post_meta( $post->ID, '_diako_hero_slide_url', true );
	$alt = (string) get_post_meta( $post->ID, '_diako_hero_slide_alt', true );
	?>
	<p>
		<label class="screen-reader-text" for="diako_hero_slide_url"><?php esc_html_e( 'لینک اسلاید', 'diako' ); ?></label>
		<strong><?php esc_html_e( 'لینک (اختیاری)', 'diako' ); ?></strong>
		<input
			id="diako_hero_slide_url"
			class="widefat"
			type="url"
			name="diako_hero_slide_url"
			value="<?php echo esc_attr( $url ); ?>"
			placeholder="https://"
		>
	</p>
	<p>
		<label class="screen-reader-text" for="diako_hero_slide_alt"><?php esc_html_e( 'متن جایگزین تصویر', 'diako' ); ?></label>
		<strong><?php esc_html_e( 'متن جایگزین تصویر', 'diako' ); ?></strong>
		<input
			id="diako_hero_slide_alt"
			class="widefat"
			type="text"
			name="diako_hero_slide_alt"
			value="<?php echo esc_attr( $alt ); ?>"
			placeholder="<?php esc_attr_e( 'در صورت خالی بودن از عنوان استفاده می‌شود', 'diako' ); ?>"
		>
	</p>
	<p class="description">
		<?php esc_html_e( 'تصویر شاخص را از بخش «تصویر شاخص» تنظیم کنید. ترتیب نمایش با فیلد «ترتیب» کنترل می‌شود.', 'diako' ); ?>
	</p>
	<?php
}

add_action(
	'save_post_diako_hero_slide',
	function ( $post_id ) {
		if ( ! isset( $_POST['diako_hero_slide_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['diako_hero_slide_nonce'] ) ), 'diako_save_hero_slide_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$url = isset( $_POST['diako_hero_slide_url'] ) ? esc_url_raw( wp_unslash( $_POST['diako_hero_slide_url'] ) ) : '';
		$alt = isset( $_POST['diako_hero_slide_alt'] ) ? sanitize_text_field( wp_unslash( $_POST['diako_hero_slide_alt'] ) ) : '';

		update_post_meta( $post_id, '_diako_hero_slide_url', $url );
		update_post_meta( $post_id, '_diako_hero_slide_alt', $alt );
	}
);

/**
 * Default hero slides bundled with the theme.
 *
 * @return array<int, array{image: string, alt: string, url: string, attachment_id: int}>
 */
function diako_get_default_hero_slides() {
	$defaults = array(
		array(
			'file'  => 'enzi-skincare.png',
			'title' => 'مراقبت پوست',
		),
		array(
			'file'  => 'enzi-makeup.png',
			'title' => 'آرایش',
		),
		array(
			'file'  => 'enzi-haircare.png',
			'title' => 'مراقبت مو',
		),
	);

	$slides = array();

	foreach ( $defaults as $item ) {
		$path = DIAKO_DIR . '/assets/images/hero/' . $item['file'];

		if ( ! is_readable( $path ) ) {
			continue;
		}

		$slide = array(
			'image'         => DIAKO_URI . '/assets/images/hero/' . $item['file'],
			'alt'           => $item['title'],
			'url'           => '',
			'attachment_id' => 0,
			'srcset'        => '',
			'sizes'         => DIAKO_HERO_IMAGE_SIZES,
			'width'         => 1024,
			'height'        => 383,
		);

		$dimensions = @getimagesize( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		if ( is_array( $dimensions ) ) {
			$slide['width']  = (int) $dimensions[0];
			$slide['height'] = (int) $dimensions[1];
		}

		$slides[] = $slide;
	}

	return $slides;
}

/**
 * Import a theme asset into the media library.
 *
 * @param string $file_path Absolute file path.
 * @param string $title     Attachment title.
 * @return int Attachment ID or 0 on failure.
 */
function diako_import_hero_slide_attachment( $file_path, $title ) {
	if ( ! is_readable( $file_path ) ) {
		return 0;
	}

	$filename = basename( $file_path );
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'post_status'    => 'inherit',
			'meta_query'     => array(
				array(
					'key'   => '_diako_hero_source_file',
					'value' => $filename,
				),
			),
			'fields'         => 'ids',
		)
	);

	if ( ! empty( $existing ) ) {
		return (int) $existing[0];
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp_file = wp_tempnam( $filename );

	if ( ! $tmp_file || ! copy( $file_path, $tmp_file ) ) {
		if ( $tmp_file ) {
			@unlink( $tmp_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}
		return 0;
	}

	$file_array = array(
		'name'     => $filename,
		'tmp_name' => $tmp_file,
	);

	$attachment_id = media_handle_sideload( $file_array, 0, $title );

	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $tmp_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return 0;
	}

	update_post_meta( $attachment_id, '_diako_hero_source_file', $filename );

	return (int) $attachment_id;
}

/**
 * Create default hero slide posts from bundled theme images.
 *
 * @return void
 */
function diako_bootstrap_hero_slides() {
	if ( get_option( 'diako_hero_slides_bootstrapped' ) ) {
		return;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'diako_hero_slide',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	if ( ! empty( $existing ) ) {
		update_option( 'diako_hero_slides_bootstrapped', 1 );
		return;
	}

	$defaults = array(
		array(
			'file'  => 'enzi-skincare.png',
			'title' => 'مراقبت پوست',
			'order' => 1,
		),
		array(
			'file'  => 'enzi-makeup.png',
			'title' => 'آرایش',
			'order' => 2,
		),
		array(
			'file'  => 'enzi-haircare.png',
			'title' => 'مراقبت مو',
			'order' => 3,
		),
	);

	foreach ( $defaults as $item ) {
		$file_path = DIAKO_DIR . '/assets/images/hero/' . $item['file'];

		if ( ! is_readable( $file_path ) ) {
			continue;
		}

		$attachment_id = diako_import_hero_slide_attachment( $file_path, $item['title'] );

		if ( ! $attachment_id ) {
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'diako_hero_slide',
				'post_title'   => $item['title'],
				'post_status'  => 'publish',
				'menu_order'   => (int) $item['order'],
			),
			true
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		set_post_thumbnail( (int) $post_id, $attachment_id );
		update_post_meta( (int) $post_id, '_diako_hero_slide_alt', $item['title'] );
	}

	update_option( 'diako_hero_slides_bootstrapped', 1 );
}

add_action( 'after_setup_theme', 'diako_bootstrap_hero_slides', 100 );

/**
 * Redirect legacy hero slide list screen to Lastify settings.
 *
 * @return void
 */
function diako_redirect_hero_slides_list_screen() {
	$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : 'post';

	if ( 'diako_hero_slide' !== $post_type ) {
		return;
	}

	wp_safe_redirect( admin_url( 'admin.php?page=lastify&tab=slides' ) );
	exit;
}
add_action( 'load-edit.php', 'diako_redirect_hero_slides_list_screen' );

/**
 * Redirect legacy hero slide edit screens to Lastify settings.
 *
 * @return void
 */
function diako_redirect_hero_slides_edit_screen() {
	$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';

	if ( 'diako_hero_slide' === $post_type ) {
		wp_safe_redirect( admin_url( 'admin.php?page=lastify&tab=slides' ) );
		exit;
	}

	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

	if ( $post_id > 0 && 'diako_hero_slide' === get_post_type( $post_id ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=lastify&tab=slides' ) );
		exit;
	}
}
add_action( 'load-post.php', 'diako_redirect_hero_slides_edit_screen' );
add_action( 'load-post-new.php', 'diako_redirect_hero_slides_edit_screen' );

/**
 * Hero slides for the settings tab editor.
 *
 * @return array<int, array<string, mixed>>
 */
function diako_get_hero_slides_admin_items() {
	$query = new WP_Query(
		array(
			'post_type'              => 'diako_hero_slide',
			'post_status'            => array( 'publish', 'draft', 'pending' ),
			'posts_per_page'         => 50,
			'orderby'                => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		)
	);

	$items = array();

	foreach ( $query->posts as $post ) {
		$image_id = (int) get_post_thumbnail_id( $post );

		$items[] = array(
			'id'        => (int) $post->ID,
			'title'     => get_the_title( $post ),
			'image_id'  => $image_id,
			'image_url' => $image_id ? (string) wp_get_attachment_image_url( $image_id, 'medium' ) : '',
			'url'       => (string) get_post_meta( $post->ID, '_diako_hero_slide_url', true ),
			'alt'       => (string) get_post_meta( $post->ID, '_diako_hero_slide_alt', true ),
			'order'     => (int) $post->menu_order,
		);
	}

	wp_reset_postdata();

	return $items;
}

/**
 * Save hero slides submitted from the Lastify settings tab.
 *
 * @param array<int, array<string, mixed>> $rows Submitted slide rows.
 * @return void
 */
function diako_save_hero_slides_settings_tab( array $rows ) {
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$id       = absint( $row['id'] ?? 0 );
		$delete   = ! empty( $row['delete'] );
		$image_id = absint( $row['image_id'] ?? 0 );

		if ( $delete && $id > 0 ) {
			if ( 'diako_hero_slide' !== get_post_type( $id ) ) {
				continue;
			}

			wp_delete_post( $id, true );
			continue;
		}

		if ( $image_id <= 0 ) {
			if ( $id > 0 ) {
				if ( 'diako_hero_slide' !== get_post_type( $id ) ) {
					continue;
				}

				wp_delete_post( $id, true );
			}
			continue;
		}

		if ( $id > 0 && 'diako_hero_slide' !== get_post_type( $id ) ) {
			continue;
		}

		$title = sanitize_text_field( $row['title'] ?? '' );

		if ( '' === $title ) {
			$title = __( 'اسلاید', 'diako' );
		}

		$post_data = array(
			'post_type'   => 'diako_hero_slide',
			'post_title'  => $title,
			'post_status' => 'publish',
			'menu_order'  => absint( $row['order'] ?? 0 ),
		);

		if ( $id > 0 ) {
			$post_data['ID'] = $id;
			$post_id         = wp_update_post( $post_data, true );
		} else {
			$post_id = wp_insert_post( $post_data, true );
		}

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		set_post_thumbnail( (int) $post_id, $image_id );
		update_post_meta( (int) $post_id, '_diako_hero_slide_url', esc_url_raw( $row['url'] ?? '' ) );
		update_post_meta( (int) $post_id, '_diako_hero_slide_alt', sanitize_text_field( $row['alt'] ?? '' ) );
	}
}

/**
 * Render one hero slide row in the settings tab.
 *
 * @param int                  $index Row index.
 * @param array<string, mixed> $slide Slide data.
 * @return void
 */
function diako_render_hero_slide_settings_row( $index, array $slide = array() ) {
	$defaults = array(
		'id'        => 0,
		'title'     => '',
		'image_id'  => 0,
		'image_url' => '',
		'url'       => '',
		'alt'       => '',
		'order'     => 0,
	);

	$slide         = wp_parse_args( $slide, $defaults );
	$index         = (int) $index;
	$prefix        = "lastify_hero_slides[{$index}]";
	$preview       = $slide['image_url'];
	$slide_heading = sprintf(
		/* translators: %d: slide number */
		__( 'اسلاید %d', 'diako' ),
		$index + 1
	);
	?>
	<div class="lastify-settings__card lastify-hero-slide-row" data-lastify-hero-slide-row>
		<div class="lastify-hero-slide-row__header">
			<h3 data-lastify-slide-title><?php echo esc_html( $slide_heading ); ?></h3>
			<label class="lastify-hero-slide-row__delete">
				<input type="checkbox" name="<?php echo esc_attr( $prefix ); ?>[delete]" value="1">
				<?php esc_html_e( 'حذف', 'diako' ); ?>
			</label>
		</div>

		<input type="hidden" name="<?php echo esc_attr( $prefix ); ?>[id]" value="<?php echo esc_attr( (string) $slide['id'] ); ?>">

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'تصویر', 'diako' ); ?></th>
				<td>
					<div class="lastify-image-field" data-lastify-image-field>
						<input type="hidden" name="<?php echo esc_attr( $prefix ); ?>[image_id]" value="<?php echo esc_attr( (string) $slide['image_id'] ); ?>" data-lastify-image-id>
						<input class="regular-text" type="hidden" name="<?php echo esc_attr( $prefix ); ?>[image_url]" value="<?php echo esc_attr( $slide['image_url'] ); ?>" data-lastify-image-url>
						<p>
							<button type="button" class="button" data-lastify-select-image><?php esc_html_e( 'انتخاب تصویر', 'diako' ); ?></button>
							<button type="button" class="button" data-lastify-clear-image><?php esc_html_e( 'حذف تصویر', 'diako' ); ?></button>
						</p>
						<div class="lastify-image-field__preview lastify-hero-slide-row__preview" data-lastify-image-preview>
							<?php if ( $preview ) : ?>
								<img src="<?php echo esc_url( $preview ); ?>" alt="">
							<?php endif; ?>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $prefix ); ?>_title"><?php esc_html_e( 'عنوان', 'diako' ); ?></label></th>
				<td>
					<input id="<?php echo esc_attr( $prefix ); ?>_title" class="regular-text" type="text" name="<?php echo esc_attr( $prefix ); ?>[title]" value="<?php echo esc_attr( $slide['title'] ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $prefix ); ?>_url"><?php esc_html_e( 'لینک (اختیاری)', 'diako' ); ?></label></th>
				<td>
					<input id="<?php echo esc_attr( $prefix ); ?>_url" class="regular-text" type="url" name="<?php echo esc_attr( $prefix ); ?>[url]" value="<?php echo esc_attr( $slide['url'] ); ?>" placeholder="https://">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $prefix ); ?>_alt"><?php esc_html_e( 'متن جایگزین تصویر', 'diako' ); ?></label></th>
				<td>
					<input id="<?php echo esc_attr( $prefix ); ?>_alt" class="regular-text" type="text" name="<?php echo esc_attr( $prefix ); ?>[alt]" value="<?php echo esc_attr( $slide['alt'] ); ?>" placeholder="<?php esc_attr_e( 'در صورت خالی بودن از عنوان استفاده می‌شود', 'diako' ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $prefix ); ?>_order"><?php esc_html_e( 'ترتیب', 'diako' ); ?></label></th>
				<td>
					<input id="<?php echo esc_attr( $prefix ); ?>_order" class="small-text" type="number" min="0" name="<?php echo esc_attr( $prefix ); ?>[order]" value="<?php echo esc_attr( (string) $slide['order'] ); ?>">
				</td>
			</tr>
		</table>
	</div>
	<?php
}

/**
 * Render hero slides tab in Lastify settings.
 *
 * @return void
 */
function diako_render_hero_slides_settings_tab() {
	$slides = diako_get_hero_slides_admin_items();
	?>
	<p class="description">
		<?php esc_html_e( 'تصاویر اسلایدر بالای صفحه اصلی را اینجا مدیریت کنید. ترتیب نمایش با فیلد «ترتیب» کنترل می‌شود.', 'diako' ); ?>
	</p>

	<div id="lastify-hero-slides-list" class="lastify-hero-slides-list">
		<?php
		if ( empty( $slides ) ) {
			diako_render_hero_slide_settings_row( 0 );
		} else {
			foreach ( $slides as $index => $slide ) {
				diako_render_hero_slide_settings_row( $index, $slide );
			}
		}
		?>
	</div>

	<p>
		<button type="button" class="button" id="lastify-add-hero-slide">
			<?php esc_html_e( 'افزودن اسلاید', 'diako' ); ?>
		</button>
	</p>
	<?php
}

/**
 * Published hero slides for the homepage carousel.
 *
 * @return array<int, array{image: string, alt: string, url: string, attachment_id: int, srcset: string, sizes: string}>
 */
function diako_get_hero_slides() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'diako_hero_slide',
			'post_status'            => 'publish',
			'posts_per_page'         => 20,
			'orderby'                => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		)
	);

	$slides = array();

	foreach ( $query->posts as $post ) {
		$attachment_id = (int) get_post_thumbnail_id( $post );

		if ( ! $attachment_id ) {
			continue;
		}

		$src = wp_get_attachment_image_src( $attachment_id, 'diako-hero-md' );

		if ( ! $src ) {
			$src = wp_get_attachment_image_src( $attachment_id, 'diako-hero' );
		}

		if ( ! $src ) {
			$src = wp_get_attachment_image_src( $attachment_id, 'full' );
		}

		if ( ! $src ) {
			continue;
		}

		$image   = $src[0];
		$width   = (int) $src[1];
		$height  = (int) $src[2];
		$srcset  = (string) wp_get_attachment_image_srcset( $attachment_id, 'diako-hero' );

		if ( '' === $srcset ) {
			$srcset = (string) wp_get_attachment_image_srcset( $attachment_id, 'full' );
		}

		$alt = (string) get_post_meta( $post->ID, '_diako_hero_slide_alt', true );

		if ( '' === $alt ) {
			$alt = get_the_title( $post );
		}

		$slides[] = array(
			'image'         => $image,
			'alt'           => $alt,
			'url'           => (string) get_post_meta( $post->ID, '_diako_hero_slide_url', true ),
			'attachment_id' => $attachment_id,
			'srcset'        => $srcset,
			'sizes'         => DIAKO_HERO_IMAGE_SIZES,
			'width'         => $width,
			'height'        => $height,
		);
	}

	wp_reset_postdata();

	if ( empty( $slides ) ) {
		$cached = diako_get_default_hero_slides();
		return $cached;
	}

	$cached = $slides;

	return $cached;
}

/**
 * Render homepage hero carousel slides.
 *
 * @return void
 */
function diako_render_hero_slides() {
	$slides = diako_get_hero_slides();

	if ( empty( $slides ) ) {
		return;
	}

	foreach ( $slides as $index => $slide ) {
		$is_active = 0 === $index;
		$loading   = $is_active ? 'eager' : 'lazy';
		$fetch     = $is_active ? 'high' : 'auto';
		?>
		<div
			class="diako-carousel__slide<?php echo $is_active ? ' is-active' : ''; ?>"
			data-carousel-slide
			role="group"
			aria-roledescription="slide"
			aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number */ __( 'اسلاید %d', 'diako' ), $index + 1 ) ); ?>"
		>
			<?php if ( ! empty( $slide['url'] ) ) : ?>
				<a class="block h-full w-full" href="<?php echo esc_url( $slide['url'] ); ?>">
			<?php endif; ?>

			<img
				src="<?php echo esc_url( $slide['image'] ); ?>"
				<?php if ( ! empty( $slide['srcset'] ) ) : ?>
					srcset="<?php echo esc_attr( $slide['srcset'] ); ?>"
					sizes="<?php echo esc_attr( $slide['sizes'] ?? DIAKO_HERO_IMAGE_SIZES ); ?>"
				<?php endif; ?>
				<?php if ( ! empty( $slide['width'] ) && ! empty( $slide['height'] ) ) : ?>
					width="<?php echo esc_attr( (string) $slide['width'] ); ?>"
					height="<?php echo esc_attr( (string) $slide['height'] ); ?>"
				<?php endif; ?>
				alt="<?php echo esc_attr( $slide['alt'] ); ?>"
				class="h-full w-full object-cover"
				loading="<?php echo esc_attr( $loading ); ?>"
				decoding="async"
				fetchpriority="<?php echo esc_attr( $fetch ); ?>"
			>

			<?php if ( ! empty( $slide['url'] ) ) : ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
	}
}
