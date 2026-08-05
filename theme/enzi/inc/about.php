<?php
/**
 * About page.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * About page ID.
 *
 * @return int
 */
function diako_get_about_page_id(): int {
	static $cached = null;

	if ( null !== $cached ) {
		return (int) $cached;
	}

	$page_id = (int) get_option( 'diako_about_page_id', 0 );

	if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
		$cached = $page_id;
		return (int) $cached;
	}

	$pages = get_pages(
		array(
			'meta_key'   => '_wp_page_template',
			'meta_value' => 'page-about.php',
			'number'     => 1,
		)
	);

	$cached = $pages ? (int) $pages[0]->ID : 0;

	if ( $cached ) {
		update_option( 'diako_about_page_id', $cached );
	}

	return (int) $cached;
}

/**
 * About page URL.
 *
 * @return string
 */
function diako_get_about_url(): string {
	$page_id = diako_get_about_page_id();

	if ( $page_id ) {
		return get_permalink( $page_id );
	}

	return home_url( '/about/' );
}

/**
 * Whether the current page is the about page.
 *
 * @return bool
 */
function diako_is_about_page(): bool {
	return is_page() && is_page_template( 'page-about.php' );
}

/**
 * Format a numeric stat for display.
 *
 * @param int|string $value Stat value.
 * @param string     $suffix Optional suffix (e.g. +).
 * @return string
 */
function diako_format_about_stat( $value, string $suffix = '' ): string {
	$formatted = function_exists( 'diako_number' ) ? diako_number( (string) $value ) : (string) $value;

	return $formatted . $suffix;
}

/**
 * About page stats.
 *
 * @return array<int, array{value: string, label: string}>
 */
function diako_get_about_stats(): array {
	return apply_filters(
		'diako_about_stats',
		array(
			array(
				'value' => diako_format_about_stat( 8, '+' ),
				'label' => __( 'سال تجربه در فروش آنلاین', 'diako' ),
			),
			array(
				'value' => diako_format_about_stat( 2000, '+' ),
				'label' => __( 'محصول فعال در فروشگاه', 'diako' ),
			),
			array(
				'value' => diako_format_about_stat( 12000, '+' ),
				'label' => __( 'سفارش موفق ثبت‌شده', 'diako' ),
			),
			array(
				'value' => diako_format_about_stat( 98, '%' ),
				'label' => __( 'رضایت مشتریان', 'diako' ),
			),
		)
	);
}

/**
 * Mission, vision, and values cards.
 *
 * @return array<int, array{icon: string, title: string, text: string}>
 */
function diako_get_about_pillars(): array {
	return apply_filters(
		'diako_about_pillars',
		array(
			array(
				'icon'  => 'target',
				'title' => __( 'ماموریت ما', 'diako' ),
				'text'  => __( 'فراهم کردن تجربه‌ای مطمئن و لذت‌بخش برای خرید آنلاین — با اصالت کالا، قیمت شفاف و پشتیبانی در دسترس.', 'diako' ),
			),
			array(
				'icon'  => 'sparkles',
				'title' => __( 'چشم‌انداز', 'diako' ),
				'text'  => __( 'تبدیل شدن به فروشگاه آنلاین قابل‌اعتماد در ایران؛ جایی که کیفیت محصول، شفافیت و رضایت مشتری در اولویت مطلق قرار دارد.', 'diako' ),
			),
			array(
				'icon'  => 'heart',
				'title' => __( 'ارزش‌های ما', 'diako' ),
				'text'  => sprintf(
					/* translators: %s: store brand name */
					__( 'صداقت در معرفی محصول، احترام به مشتری، پاسخگویی مسئولانه و ایجاد جامعه‌ای از خریداران وفادار که به %s اعتماد دارند.', 'diako' ),
					diako_get_brand_name()
				),
			),
		)
	);
}

/**
 * Why choose us items.
 *
 * @return array<int, array{icon: string, title: string, text: string}>
 */
function diako_get_about_highlights(): array {
	return apply_filters(
		'diako_about_highlights',
		array(
			array(
				'icon'  => 'shield-check',
				'title' => __( 'اصالت کالا', 'diako' ),
				'text'  => __( 'محصولات اصل و باکیفیت با ضمانت اصالت و اطلاعات شفاف در صفحه هر کالا.', 'diako' ),
			),
			array(
				'icon'  => 'package',
				'title' => __( 'ارسال سریع و ایمن', 'diako' ),
				'text'  => __( 'بسته‌بندی استاندارد و ارسال به سراسر کشور با امکان پیگیری سفارش.', 'diako' ),
			),
			array(
				'icon'  => 'sparkles',
				'title' => __( 'پشتیبانی واقعی', 'diako' ),
				'text'  => __( 'تیمی که در انتخاب محصول، ثبت سفارش و پیگیری تحویل همراه شماست.', 'diako' ),
			),
			array(
				'icon'  => 'credit-card',
				'title' => __( 'پرداخت امن', 'diako' ),
				'text'  => __( 'درگاه‌های معتبر پرداخت آنلاین و فرآیند خرید شفاف از سبد تا تحویل.', 'diako' ),
			),
			array(
				'icon'  => 'refresh-cw',
				'title' => __( 'تنوع و به‌روز بودن', 'diako' ),
				'text'  => sprintf(
					/* translators: %s: store brand name */
					__( 'طیف گسترده‌ای از محصولات با موجودی به‌روز در %s.', 'diako' ),
					diako_get_brand_name()
				),
			),
			array(
				'icon'  => 'users',
				'title' => __( 'جامعه مشتریان', 'diako' ),
				'text'  => sprintf(
					/* translators: %s: store brand name */
					__( 'مجله %s، راهنمای خرید و ارتباط نزدیک با مشتریان وفادار.', 'diako' ),
					diako_get_brand_name()
				),
			),
		)
	);
}

/**
 * Product category cards for the about page.
 *
 * @return array<int, array{icon: string, title: string, text: string, url: string}>
 */
function diako_get_about_categories(): array {
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$discount = home_url( '/discount/' );

	return apply_filters(
		'diako_about_categories',
		array(
			array(
				'icon'  => 'sparkles',
				'title' => __( 'محصولات جدید', 'diako' ),
				'text'  => __( 'تازه‌ترین کالاهای اضافه‌شده به فروشگاه.', 'diako' ),
				'url'   => $shop_url,
			),
			array(
				'icon'  => 'heart',
				'title' => __( 'پیشنهاد ویژه', 'diako' ),
				'text'  => __( 'تخفیف‌ها و فرصت‌های خرید امروز.', 'diako' ),
				'url'   => $discount,
			),
			array(
				'icon'  => 'package',
				'title' => __( 'همه محصولات', 'diako' ),
				'text'  => sprintf(
					/* translators: %s: store brand name */
					__( 'مرور کامل %s در یک جا.', 'diako' ),
					diako_get_brand_name()
				),
				'url'   => $shop_url,
			),
		)
	);
}

/**
 * Company timeline milestones.
 *
 * @return array<int, array{year: string, title: string, text: string}>
 */
function diako_get_about_timeline(): array {
	return apply_filters(
		'diako_about_timeline',
		array(
			array(
				'year'  => diako_format_about_stat( 1396 ),
				'title' => __( 'شروع فعالیت', 'diako' ),
				'text'  => sprintf(
					/* translators: %s: store brand name */
					__( 'آغاز فعالیت %s با تمرکز بر فروش آنلاین محصولات متنوع.', 'diako' ),
					diako_get_brand_name()
				),
			),
			array(
				'year'  => diako_format_about_stat( 1399 ),
				'title' => __( 'گسترش سبد محصولات', 'diako' ),
				'text'  => __( 'افزایش تنوع کالا و برندها متناسب با نیاز بازار ایران.', 'diako' ),
			),
			array(
				'year'  => diako_format_about_stat( 1401 ),
				'title' => __( 'ورود به فروش آنلاین', 'diako' ),
				'text'  => __( 'راه‌اندازی فروشگاه اینترنتی و ارسال به سراسر ایران.', 'diako' ),
			),
			array(
				'year'  => diako_format_about_stat( 1404 ),
				'title' => sprintf(
					/* translators: %s: store brand name */
					__( 'امروز %s', 'diako' ),
					diako_get_brand_name()
				),
				'text'  => sprintf(
					/* translators: %s: store brand name */
					__( 'ترکیب فروشگاه آنلاین، مجله %s و پشتیبانی واقعی برای تجربه خرید مطمئن.', 'diako' ),
					diako_get_brand_name()
				),
			),
		)
	);
}

/**
 * Ensure the about page exists.
 *
 * @return int
 */
function diako_ensure_about_page(): int {
	$page_id = diako_get_about_page_id();

	if ( $page_id ) {
		if ( 'page-about.php' !== get_page_template_slug( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', 'page-about.php' );
		}

		return $page_id;
	}

	$page = get_page_by_path( 'about' );

	if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
		$page_id = (int) $page->ID;
		update_post_meta( $page_id, '_wp_page_template', 'page-about.php' );
	} else {
		$page_id = wp_insert_post(
			array(
				'post_title'   => __( 'درباره ما', 'diako' ),
				'post_name'    => 'about',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			),
			true
		);

		if ( is_wp_error( $page_id ) || ! $page_id ) {
			return 0;
		}

		$page_id = (int) $page_id;
		update_post_meta( $page_id, '_wp_page_template', 'page-about.php' );
	}

	update_option( 'diako_about_page_id', $page_id );

	return $page_id;
}

add_action(
	'after_setup_theme',
	function () {
		if ( get_option( 'diako_about_page_bootstrapped' ) ) {
			return;
		}

		diako_ensure_about_page();
		update_option( 'diako_about_page_bootstrapped', 1 );
	},
	100
);

/**
 * Render the about page.
 */
function diako_render_about_page(): void {
	$shop_name   = diako_get_brand_name();
	$stats       = diako_get_about_stats();
	$pillars     = diako_get_about_pillars();
	$highlights  = diako_get_about_highlights();
	$categories  = diako_get_about_categories();
	$timeline    = diako_get_about_timeline();
	$shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$contact_url = function_exists( 'diako_get_contact_url' ) ? diako_get_contact_url() : home_url( '/contact/' );
	$mag_url     = function_exists( 'diako_get_mag_url' ) ? diako_get_mag_url() : home_url( '/mag/' );
	$contact     = diako_get_company_contact_details();
	?>
	<div class="diako-page diako-about-page">
		<header class="diako-about-page__hero">
			<div class="diako-about-page__hero-main">
				<div class="diako-about-page__hero-badge" aria-hidden="true">
					<?php echo diako_lucide_icon_svg( 'sparkles', 'h-7 w-7' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="diako-about-page__hero-content">
					<p class="diako-about-page__eyebrow"><?php esc_html_e( 'داستان ما', 'diako' ); ?></p>
					<h1 class="diako-page-title"><?php echo esc_html( sprintf( __( 'درباره %s', 'diako' ), $shop_name ) ); ?></h1>
					<p class="diako-page-desc diako-about-page__intro">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: shop name */
								__( '%s فروشگاه آنلاین محصولات متنوع است که با تمرکز بر اصالت کالا و پشتیبانی واقعی، تجربه خرید مطمئن را برای شما فراهم می‌کند.', 'diako' ),
								$shop_name
							)
						);
						?>
					</p>
				</div>
			</div>
			<div class="diako-about-page__hero-actions">
				<?php
				diako_button(
					array(
						'href'       => $shop_url,
						'label'      => __( 'مشاهده فروشگاه', 'diako' ),
						'variant'    => 'default',
						'icon'       => 'shopping-bag',
						'icon_class' => 'h-4 w-4',
					)
				);
				diako_button(
					array(
						'href'       => $contact_url,
						'label'      => __( 'تماس با ما', 'diako' ),
						'variant'    => 'outline',
						'icon'       => 'mail',
						'icon_class' => 'h-4 w-4',
					)
				);
				?>
			</div>
		</header>

		<section class="diako-about-page__stats" aria-label="<?php esc_attr_e( 'آمار و دستاوردها', 'diako' ); ?>">
			<div class="diako-about-stats-grid">
				<?php foreach ( $stats as $stat ) : ?>
					<div class="<?php echo esc_attr( diako_card_classes( 'diako-about-stat' ) ); ?>">
						<p class="diako-about-stat__value"><?php echo esc_html( $stat['value'] ); ?></p>
						<p class="diako-about-stat__label"><?php echo esc_html( $stat['label'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="diako-about-page__story">
			<div class="diako-about-story-grid">
				<div class="diako-about-story__content">
					<h2 class="diako-about-section-title"><?php esc_html_e( 'ما کی هستیم؟', 'diako' ); ?></h2>
					<div class="diako-about-story__text">
						<p>
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: shop name */
									__( '«%s» با هدف ساده‌سازی خرید آنلاین شکل گرفت. از همان روز اول، می‌خواستیم فضایی بسازیم که در آن بتوانید با خیال راحت خرید کنید، سوال بپرسید و به پشتیبانی واقعی تکیه کنید.', 'diako' ),
									$shop_name
								)
							);
							?>
						</p>
						<p>
							<?php esc_html_e( 'امروز از طریق فروشگاه آنلاین، طیف گسترده‌ای از محصولات را با ضمانت اصالت در اختیار شما قرار می‌دهیم. تیم ما در هر مرحله از خرید — از انتخاب تا تحویل — همراهتان است.', 'diako' ); ?>
						</p>
					</div>
					<ul class="diako-about-story__points">
						<li>
							<span class="diako-about-story__point-icon" aria-hidden="true"><?php echo diako_lucide_icon_svg( 'check-circle', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php esc_html_e( 'محصولات اورجینال با تاریخ مصرف معتبر', 'diako' ); ?></span>
						</li>
						<li>
							<span class="diako-about-story__point-icon" aria-hidden="true"><?php echo diako_lucide_icon_svg( 'check-circle', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php esc_html_e( 'ارسال سریع به سراسر ایران و پیگیری آنلاین سفارش', 'diako' ); ?></span>
						</li>
						<li>
							<span class="diako-about-story__point-icon" aria-hidden="true"><?php echo diako_lucide_icon_svg( 'check-circle', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php echo esc_html( sprintf( __( 'مجله %s برای راهنمای خرید و مقالات', 'diako' ), diako_get_brand_name() ) ); ?></span>
						</li>
					</ul>
				</div>

				<div class="<?php echo esc_attr( diako_card_classes( 'diako-about-story__card' ) ); ?>">
					<div class="diako-about-story__card-head">
						<span class="diako-about-story__card-icon" aria-hidden="true">
							<?php echo diako_lucide_icon_svg( 'map-pin', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<h3 class="diako-about-story__card-title"><?php esc_html_e( 'دسترسی حضوری', 'diako' ); ?></h3>
					</div>
					<p class="diako-about-story__card-text"><?php echo esc_html( $contact['address'] ); ?></p>
					<div class="diako-about-story__card-meta">
						<div>
							<p class="diako-about-story__card-label"><?php esc_html_e( 'تلفن', 'diako' ); ?></p>
							<?php diako_render_company_phone_link( 'diako-about-story__card-value' ); ?>
						</div>
						<div>
							<p class="diako-about-story__card-label"><?php esc_html_e( 'ایمیل', 'diako' ); ?></p>
							<a class="diako-about-story__card-value" href="mailto:<?php echo esc_attr( $contact['email'] ); ?>" dir="ltr"><?php echo esc_html( $contact['email'] ); ?></a>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="diako-about-page__pillars" aria-label="<?php esc_attr_e( 'ماموریت، چشم‌انداز و ارزش‌ها', 'diako' ); ?>">
			<div class="diako-about-pillars-grid">
				<?php foreach ( $pillars as $pillar ) : ?>
					<div class="<?php echo esc_attr( diako_card_classes( 'diako-about-pillar' ) ); ?>">
						<span class="diako-about-pillar__icon" aria-hidden="true">
							<?php echo diako_lucide_icon_svg( $pillar['icon'], 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<h3 class="diako-about-pillar__title"><?php echo esc_html( $pillar['title'] ); ?></h3>
						<p class="diako-about-pillar__text"><?php echo esc_html( $pillar['text'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="diako-about-page__highlights">
			<div class="diako-about-page__section-head">
				<h2 class="diako-about-section-title"><?php echo esc_html( sprintf( __( 'چرا %s؟', 'diako' ), $shop_name ) ); ?></h2>
				<p class="diako-about-section-desc"><?php echo esc_html( sprintf( __( 'دلایلی که مشتریان بارها به ما اعتماد می‌کنند و خرید بعدی‌شان را هم از %s انجام می‌دهند.', 'diako' ), $shop_name ) ); ?></p>
			</div>
			<div class="diako-about-highlights-grid">
				<?php foreach ( $highlights as $item ) : ?>
					<div class="<?php echo esc_attr( diako_card_classes( 'diako-about-highlight' ) ); ?>">
						<span class="diako-about-highlight__icon" aria-hidden="true">
							<?php echo diako_lucide_icon_svg( $item['icon'], 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<h3 class="diako-about-highlight__title"><?php echo esc_html( $item['title'] ); ?></h3>
						<p class="diako-about-highlight__text"><?php echo esc_html( $item['text'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="diako-about-page__categories" aria-label="<?php esc_attr_e( 'دسته‌بندی محصولات', 'diako' ); ?>">
			<div class="diako-about-page__section-head">
				<h2 class="diako-about-section-title"><?php esc_html_e( 'چه چیزهایی پیدا می‌کنید؟', 'diako' ); ?></h2>
				<p class="diako-about-section-desc"><?php esc_html_e( 'محصولات متنوع در یک فروشگاه آنلاین.', 'diako' ); ?></p>
			</div>
			<div class="diako-about-categories-grid">
				<?php foreach ( $categories as $category ) : ?>
					<a class="<?php echo esc_attr( diako_card_classes( 'diako-about-category' ) ); ?>" href="<?php echo esc_url( $category['url'] ); ?>">
						<span class="diako-about-category__icon" aria-hidden="true">
							<?php echo diako_lucide_icon_svg( $category['icon'], 'h-6 w-6' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<h3 class="diako-about-category__title"><?php echo esc_html( $category['title'] ); ?></h3>
						<p class="diako-about-category__text"><?php echo esc_html( $category['text'] ); ?></p>
						<span class="diako-about-category__link">
							<?php esc_html_e( 'مشاهده محصولات', 'diako' ); ?>
							<?php echo diako_lucide_icon_svg( 'chevron-left', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="diako-about-page__timeline" aria-label="<?php echo esc_attr( sprintf( __( 'مسیر رشد %s', 'diako' ), $shop_name ) ); ?>">
			<div class="diako-about-page__section-head">
				<h2 class="diako-about-section-title"><?php esc_html_e( 'مسیر ما', 'diako' ); ?></h2>
				<p class="diako-about-section-desc"><?php esc_html_e( 'از یک فروشگاه کوچک تا تجربه‌ای کامل خرید آنلاین.', 'diako' ); ?></p>
			</div>
			<ol class="diako-about-timeline">
				<?php foreach ( $timeline as $item ) : ?>
					<li class="diako-about-timeline__item">
						<div class="diako-about-timeline__marker" aria-hidden="true"></div>
						<div class="<?php echo esc_attr( diako_card_classes( 'diako-about-timeline__card' ) ); ?>">
							<p class="diako-about-timeline__year"><?php echo esc_html( $item['year'] ); ?></p>
							<h3 class="diako-about-timeline__title"><?php echo esc_html( $item['title'] ); ?></h3>
							<p class="diako-about-timeline__text"><?php echo esc_html( $item['text'] ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>

		<section class="diako-about-page__cta">
			<div class="diako-about-cta">
				<div class="diako-about-cta__content">
					<h2 class="diako-about-cta__title"><?php echo esc_html( sprintf( __( 'آماده خرید از %s هستید؟', 'diako' ), $shop_name ) ); ?></h2>
					<p class="diako-about-cta__desc"><?php echo esc_html( sprintf( __( 'همین حالا فروشگاه را ببینید، از مجله %s مطالعه کنید یا با تیم ما در تماس باشید.', 'diako' ), $shop_name ) ); ?></p>
				</div>
				<div class="diako-about-cta__actions">
					<?php
					diako_button(
						array(
							'href'       => $shop_url,
							'label'      => __( 'رفتن به فروشگاه', 'diako' ),
							'variant'    => 'default',
							'icon'       => 'shopping-bag',
							'icon_class' => 'h-4 w-4',
						)
					);
					diako_button(
						array(
							'href'       => $mag_url,
							'label'      => sprintf( __( 'مجله %s', 'diako' ), $shop_name ),
							'variant'    => 'outline',
							'icon'       => 'book-open',
							'icon_class' => 'h-4 w-4',
						)
					);
					diako_button(
						array(
							'href'       => $contact_url,
							'label'      => __( 'ارتباط با پشتیبانی', 'diako' ),
							'variant'    => 'outline',
							'icon'       => 'headphones',
							'icon_class' => 'h-4 w-4',
						)
					);
					?>
				</div>
			</div>
		</section>
	</div>
	<?php
}
