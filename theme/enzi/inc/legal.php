<?php
/**
 * Legal pages (terms & conditions).
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Terms page ID.
 *
 * @return int
 */
function diako_get_terms_page_id() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$page_id = (int) get_option( 'diako_terms_page_id', 0 );

	if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
		$cached = $page_id;
		return $cached;
	}

	$pages = get_pages(
		array(
			'meta_key'   => '_wp_page_template',
			'meta_value' => 'page-terms.php',
			'number'     => 1,
		)
	);

	$cached = $pages ? (int) $pages[0]->ID : 0;

	if ( $cached ) {
		update_option( 'diako_terms_page_id', $cached );
	}

	return $cached;
}

/**
 * Terms page URL.
 *
 * @return string
 */
function diako_get_terms_url() {
	$page_id = diako_get_terms_page_id();

	if ( $page_id ) {
		return get_permalink( $page_id );
	}

	return home_url( '/terms/' );
}

/**
 * Structured terms sections.
 *
 * @return array<int, array{id: string, title: string, content: string, highlight?: bool}>
 */
function diako_get_terms_sections() {
	$contact = diako_get_company_contact_details();
	$shop    = get_bloginfo( 'name' );

	return array(
		array(
			'id'      => 'intro',
			'title'   => __( '۱. مقدمه', 'diako' ),
			'content' => sprintf(
				/* translators: %s: shop name */
				__( 'به %s خوش آمدید. این «شرایط و قوانین» شامل قوانین استفاده از وب‌سایت، ثبت سفارش و خرید از فروشگاه آنلاین ما است. با بازدید از سایت، ثبت‌نام یا ثبت سفارش، شما تأیید می‌کنید که این شرایط را مطالعه کرده و بدون قید و شرط می‌پذیرید.', 'diako' ),
				esc_html( $shop )
			),
		),
		array(
			'id'      => 'definitions',
			'title'   => __( '۲. تعاریف', 'diako' ),
			'content' => '<ul class="diako-legal-list">'
				. '<li><strong>' . esc_html__( 'فروشگاه:', 'diako' ) . '</strong> ' . esc_html( $shop ) . ' ' . esc_html__( 'و اپراتور قانونی آن.', 'diako' ) . '</li>'
				. '<li><strong>' . esc_html__( 'مشتری:', 'diako' ) . '</strong> ' . esc_html__( 'هر شخص حقیقی یا حقوقی که از وب‌سایت بازدید یا خرید می‌کند.', 'diako' ) . '</li>'
				. '<li><strong>' . esc_html__( 'محصول:', 'diako' ) . '</strong> ' . esc_html__( 'کالاهای فیزیکی عرضه‌شده در فروشگاه (و در صورت ارائه، کارت هدیه دیجیتال).', 'diako' ) . '</li>'
				. '<li><strong>' . esc_html__( 'سفارش:', 'diako' ) . '</strong> ' . esc_html__( 'درخواست خرید ثبت‌شده توسط مشتری پس از تأیید پرداخت.', 'diako' ) . '</li>'
				. '</ul>',
		),
		array(
			'id'      => 'orders',
			'title'   => __( '۳. ثبت سفارش و پرداخت', 'diako' ),
			'content' => '<ul class="diako-legal-list">'
				. '<li>' . esc_html__( 'اطلاعات واردشده هنگام ثبت سفارش باید صحیح و کامل باشد. مسئولیت هرگونه مغایرت ناشی از اطلاعات نادرست بر عهده مشتری است.', 'diako' ) . '</li>'
				. '<li>' . esc_html__( 'ثبت سفارش به منزله پیشنهاد خرید است و تن پس از تأیید پرداخت و صدور پیامک یا ایمیل تأیید سفارش، قطعی می‌شود.', 'diako' ) . '</li>'
				. '<li>' . esc_html__( 'فروشگاه حق دارد در صورت خطای قیمت‌گذاری، اتمام موجودی، مشکل فنی یا تردید در صحت سفارش، سفارش را لغو و مبلغ پرداختی را (در صورت وصول) استرداد کند.', 'diako' ) . '</li>'
				. '<li>' . esc_html__( 'قیمت‌ها به تومان/ریال (طبق نمایش سایت) محاسبه می‌شوند و مالیات یا هزینه ارسال — در صورت وجود — جداگانه در صفحه تسویه‌حساب نمایش داده می‌شود.', 'diako' ) . '</li>'
				. '</ul>',
		),
		array(
			'id'      => 'shipping',
			'title'   => __( '۴. ارسال و تحویل', 'diako' ),
			'content' => '<ul class="diako-legal-list">'
				. '<li>' . esc_html__( 'زمان تقریبی آماده‌سازی و ارسال در صفحه محصول یا هنگام ثبت سفارش اعلام می‌شود و ممکن است در شرایط خاص (تعطیلات، محدودیت‌های حمل‌ونقل و …) تغییر کند.', 'diako' ) . '</li>'
				. '<li>' . esc_html__( 'مسئولیت تحویل سالم کالا تا زمان تحویل به مشتری یا نماینده وی بر عهده فروشگاه است؛ پس از تحویل، مشتری موظف است بسته‌بندی و ظاهر کالا را بررسی کند.', 'diako' ) . '</li>'
				. '<li>' . esc_html__( 'در صورت مشاهده آسیب فیزیکی در بسته‌بندی، حداکثر تا ۲۴ ساعت پس از دریافت، همراه با مستندات (عکس/فیلم) به پشتیبانی اطلاع دهید.', 'diako' ) . '</li>'
				. '</ul>',
		),
		array(
			'id'        => 'no-refund',
			'title'     => __( '۵. سیاست بازگشت، تعویض و استرداد وجه', 'diako' ),
			'highlight' => true,
			'content'   => '<p class="diako-legal-emphasis">' . esc_html(
				sprintf(
					/* translators: %s: store brand name */
					__( 'فروشگاه %s تحت هیچ شرایطی استرداد وجه (Refund) انجام نمی‌دهد. تمامی فروش‌ها نهایی (All Sales Are Final) محسوب می‌شوند.', 'diako' ),
					function_exists( 'diako_get_brand_name' ) ? diako_get_brand_name() : get_bloginfo( 'name' )
				)
			) . '</p>'
				. '<ul class="diako-legal-list">'
				. '<li>' . esc_html__( 'به دلایل بهداشتی یا ایمنی، برخی کالاها پس از باز شدن پلمپ یا استفاده، قابل بازگشت نیستند.', 'diako' ) . '</li>'
				. '<li>' . esc_html__( 'بازگشت کالا به دلیل «انصراف از خرید»، «پشیمانی» یا «تغییر نظر» پذیرفته نمی‌شود.', 'diako' ) . '</li>'
				. '<li>' . esc_html__( 'پس از تحویل کالا، هیچ‌گونه استرداد وجه، اعتبار خرید یا جایگزینی صرفاً به درخواست مشتری انجام نخواهد شد.', 'diako' ) . '</li>'
				. '<li>' . esc_html__( 'در صورت ارسال کالای اشتباه یا آسیب‌دیده در حین حمل (قبل از تحویل و استفاده)، موضوع ظرف ۲۴ ساعت با ارائه مدارک بررسی می‌شود؛ راه‌حل ممکن تعویض همان کالا (در صورت موجودی) خواهد بود و شامل استرداد وجه نمی‌شود.', 'diako' ) . '</li>'
				. '<li>' . esc_html__( 'تخفیف‌ها، پیشنهادهای ویژه و فروش‌های حراجی مشمول این سیاست بدون استثنا هستند.', 'diako' ) . '</li>'
				. '</ul>',
		),
		array(
			'id'      => 'product-care',
			'title'   => __( '۶. نکات مهم خرید و استفاده', 'diako' ),
			'content' => '<ul class="diako-legal-list">'
				. '<li>' . esc_html__( 'مشتری مسئول است پیش از خرید، مشخصات و توضیحات محصول را بررسی کند.', 'diako' ) . '</li>'
				. '<li>' . esc_html__( 'در صورت وجود دستورالعمل استفاده یا نگهداری، آن را مطالعه و رعایت کنید.', 'diako' ) . '</li>'
				. '<li>' . esc_html__( 'تاریخ انقضا و شرایط نگهداری — در صورت وجود — روی بسته‌بندی یا صفحه محصول درج می‌شود؛ پس از تحویل، نگهداری صحیح بر عهده مشتری است.', 'diako' ) . '</li>'
				. '</ul>',
		),
		array(
			'id'      => 'warranty',
			'title'   => __( '۷. اصالت کالا', 'diako' ),
			'content' => '<ul class="diako-legal-list">'
				. '<li>' . esc_html__( 'فروشگاه متعهد به عرضه کالای اصل و نو (مگر آنکه در توضیحات محصول خلاف آن ذکر شده باشد) است.', 'diako' ) . '</li>'
				. '<li>' . esc_html__( 'گارانتی در صورت وجود، طبق ضوابط برند یا نمایندگی معرفی‌شده در صفحه محصول اعمال می‌شود و شامل استرداد وجه از سوی فروشگاه نمی‌شود.', 'diako' ) . '</li>'
				. '<li>' . esc_html__( 'تصاویر و مشخصات محصولات جهت راهنمایی است و ممکن است بسته‌بندی برند در به‌روزرسانی‌های بعدی تغییر کند.', 'diako' ) . '</li>'
				. '</ul>',
		),
		array(
			'id'      => 'liability',
			'title'   => __( '۸. محدودیت مسئولیت', 'diako' ),
			'content' => '<ul class="diako-legal-list">'
				. '<li>' . esc_html__( 'فروشگاه در حدود قوانین جاری، مسئول خسارات غیرمستقیم یا مشکلات ناشی از استفاده نادرست از کالا پس از تحویل نیست.', 'diako' ) . '</li>'
				. '<li>' . esc_html__( 'اطلاعات آموزشی مجله و صفحات محصول جایگزین مشاوره تخصصی نیستند.', 'diako' ) . '</li>'
				. '</ul>',
		),
		array(
			'id'      => 'privacy',
			'title'   => __( '۹. حریم خصوصی', 'diako' ),
			'content' => '<p>' . esc_html__( 'اطلاعات شخصی شما (نام، تماس، آدرس و …) صرفاً برای پردازش سفارش، ارسال، پشتیبانی و ارتباطات مرتبط با خرید استفاده می‌شود و بدون رضایت شما — مگر به موجب قانون — در اختیار اشخاص ثالث قرار نمی‌گیرد.', 'diako' ) . '</p>',
		),
		array(
			'id'      => 'changes',
			'title'   => __( '۱۰. تغییر شرایط', 'diako' ),
			'content' => '<p>' . esc_html__( 'فروشگاه حق دارد این شرایط را در هر زمان به‌روزرسانی کند. نسخه جاری همواره در همین صفحه منتشر می‌شود و تاریخ آخرین به‌روزرسانی در بالای صفحه درج می‌گردد. ادامه استفاده از سایت یا ثبت سفارش پس از تغییرات، به منزله پذیرش نسخه جدید است.', 'diako' ) . '</p>',
		),
		array(
			'id'      => 'law',
			'title'   => __( '۱۱. قانون حاکم', 'diako' ),
			'content' => '<p>' . esc_html__( 'این شرایط تابع قوانین جمهوری اسلامی ایران است. هرگونه اختلاف، در مرحله اول از طریق مذاکره و در صورت عدم توافق، در مراجع قضایی صالح پیگیری خواهد شد.', 'diako' ) . '</p>',
		),
		array(
			'id'      => 'contact',
			'title'   => __( '۱۲. تماس با ما', 'diako' ),
			'content' => '<p>' . esc_html__( 'برای پرسش درباره سفارش یا این شرایط، با ما تماس بگیرید:', 'diako' ) . '</p>'
				. '<ul class="diako-legal-list">'
				. '<li><strong>' . esc_html__( 'تلفن:', 'diako' ) . '</strong> <a href="tel:' . esc_attr( $contact['phone_tel'] ) . '"><span ' . diako_phone_ltr_attr() . '>' . esc_html( diako_to_persian_digits( $contact['phone_display'] ) ) . '</span></a></li>'
				. '<li><strong>' . esc_html__( 'ایمیل:', 'diako' ) . '</strong> <a href="mailto:' . esc_attr( $contact['email'] ) . '">' . esc_html( $contact['email'] ) . '</a></li>'
				. '<li><strong>' . esc_html__( 'آدرس:', 'diako' ) . '</strong> ' . esc_html( $contact['address'] ) . '</li>'
				. '</ul>',
		),
	);
}

/**
 * Create or repair the terms page.
 *
 * @return int Page ID or 0 on failure.
 */
function diako_ensure_terms_page() {
	$page_id = diako_get_terms_page_id();

	if ( $page_id ) {
		if ( 'page-terms.php' !== get_page_template_slug( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', 'page-terms.php' );
		}
	} else {
		$page = get_page_by_path( 'terms' );

		if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
			$page_id = (int) $page->ID;
			update_post_meta( $page_id, '_wp_page_template', 'page-terms.php' );
		} else {
			$page_id = wp_insert_post(
				array(
					'post_title'   => __( 'شرایط و قوانین', 'diako' ),
					'post_name'    => 'terms',
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_content' => '',
				),
				true
			);

			if ( is_wp_error( $page_id ) || ! $page_id ) {
				return 0;
			}

			update_post_meta( $page_id, '_wp_page_template', 'page-terms.php' );
		}
	}

	update_option( 'diako_terms_page_id', (int) $page_id );
	update_option( 'woocommerce_terms_page_id', (int) $page_id );

	return (int) $page_id;
}

add_action(
	'after_setup_theme',
	function () {
		if ( get_option( 'diako_terms_page_bootstrapped' ) ) {
			return;
		}

		diako_ensure_terms_page();
		update_option( 'diako_terms_page_bootstrapped', 1 );
	},
	100
);

/**
 * Render the terms & conditions page.
 *
 * @return void
 */
function diako_render_terms_page() {
	$sections     = diako_get_terms_sections();
	$updated      = get_the_modified_date( 'j F Y' );
	$shop_url     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	?>
	<div class="diako-page diako-legal-page">
		<header class="diako-legal-page__hero">
			<div class="diako-legal-page__hero-icon" aria-hidden="true">
				<?php echo diako_lucide_icon_svg( 'scale', 'h-7 w-7' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<div class="diako-legal-page__hero-content">
				<h1 class="diako-page-title"><?php esc_html_e( 'شرایط و قوانین', 'diako' ); ?></h1>
				<p class="diako-page-desc">
					<?php esc_html_e( 'لطفاً پیش از ثبت سفارش، این شرایط را با دقت مطالعه کنید.', 'diako' ); ?>
				</p>
				<?php if ( $updated ) : ?>
					<p class="diako-legal-page__updated">
						<?php
						printf(
							/* translators: %s: last updated date */
							esc_html__( 'آخرین به‌روزرسانی: %s', 'diako' ),
							esc_html( $updated )
						);
						?>
					</p>
				<?php endif; ?>
			</div>
		</header>

		<div class="diako-legal-page__notice">
			<?php echo diako_lucide_icon_svg( 'alert-circle', 'h-5 w-5 shrink-0' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: store brand name */
						__( 'فروشگاه %s تحت هیچ شرایطی استرداد وجه انجام نمی‌دهد. با تکمیل خرید، این سیاست را می‌پذیرید.', 'diako' ),
						function_exists( 'diako_get_brand_name' ) ? diako_get_brand_name() : get_bloginfo( 'name' )
					)
				);
				?>
			</p>
		</div>

		<div class="diako-legal-page__layout">
			<aside class="diako-legal-page__toc" aria-label="<?php esc_attr_e( 'فهرست مطالب', 'diako' ); ?>">
				<p class="diako-legal-page__toc-title"><?php esc_html_e( 'فهرست', 'diako' ); ?></p>
				<ol class="diako-legal-page__toc-list">
					<?php foreach ( $sections as $section ) : ?>
						<li>
							<a href="#<?php echo esc_attr( $section['id'] ); ?>">
								<?php echo esc_html( wp_strip_all_tags( $section['title'] ) ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ol>
			</aside>

			<div class="diako-legal-page__sections">
				<?php foreach ( $sections as $section ) : ?>
					<section
						id="<?php echo esc_attr( $section['id'] ); ?>"
						class="<?php echo esc_attr( diako_card_classes( 'diako-legal-section' . ( ! empty( $section['highlight'] ) ? ' diako-legal-section--highlight' : '' ) ) ); ?>"
					>
						<h2 class="diako-legal-section__title"><?php echo esc_html( wp_strip_all_tags( $section['title'] ) ); ?></h2>
						<div class="diako-legal-section__content prose prose-neutral dark:prose-invert max-w-none">
							<?php echo wp_kses_post( $section['content'] ); ?>
						</div>
					</section>
				<?php endforeach; ?>
			</div>
		</div>

		<footer class="diako-legal-page__footer">
			<p><?php esc_html_e( 'سؤالی دارید؟ قبل از خرید با پشتیبانی تماس بگیرید.', 'diako' ); ?></p>
			<div class="diako-legal-page__footer-actions">
				<?php
				diako_button(
					array(
						'href'       => 'tel:' . diako_get_company_contact_details()['phone_tel'],
						'label'      => __( 'تماس با پشتیبانی', 'diako' ),
						'variant'    => 'default',
						'icon'       => 'phone',
						'icon_class' => 'h-4 w-4',
					)
				);
				diako_button(
					array(
						'href'       => $shop_url,
						'label'      => __( 'بازگشت به فروشگاه', 'diako' ),
						'variant'    => 'outline',
						'icon'       => 'shopping-bag',
						'icon_class' => 'h-4 w-4',
					)
				);
				?>
			</div>
		</footer>
	</div>
	<?php
}
