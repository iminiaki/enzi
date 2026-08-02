<?php
/**
 * SEO-optimised long-form content for product category archives.
 *
 * The content is taken from the standard product category "description" (توضیح)
 * field, so it stays fully editable from the category screen in wp-admin and works
 * alongside Yoast SEO (which keeps handling the meta title/description/canonical and
 * analyses the term description). The description is rendered at the bottom of the
 * category archive, and any FAQ section inside it is exposed to search engines as
 * FAQPage JSON-LD structured data for rich results.
 *
 * @package Diako
 */

defined( 'ABSPATH' ) || exit;

/**
 * Long-form SEO body HTML for the current product category archive.
 *
 * Pulled from the category description. Only returned on the first page of a
 * category archive so the copy is not duplicated across paginated/filtered views.
 *
 * @return string
 */
function diako_get_product_category_seo_content() {
	if ( ! function_exists( 'is_product_category' ) || ! is_product_category() ) {
		return '';
	}

	$term = get_queried_object();

	if ( ! $term instanceof WP_Term ) {
		return '';
	}

	if ( (int) get_query_var( 'paged' ) > 1 ) {
		return '';
	}

	return trim( (string) $term->description );
}

/**
 * Render the long-form SEO content (category description) at the bottom of the page.
 *
 * @return void
 */
function diako_render_product_category_seo_content() {
	$content = diako_get_product_category_seo_content();

	if ( '' === $content ) {
		return;
	}

	$html = wp_kses_post( do_shortcode( wpautop( $content ) ) );
	?>
	<section class="diako-category-seo" aria-label="<?php esc_attr_e( 'درباره این دسته‌بندی', 'diako' ); ?>">
		<div class="diako-category-seo__inner">
			<?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</section>
	<?php
}

/**
 * Extract FAQ question/answer pairs from SEO body HTML.
 *
 * Convention: an <h2> whose text matches an FAQ keyword (سوال / پرسش / FAQ) opens
 * the FAQ region; each following <h3>/<h4> is a question and the content up to the
 * next heading is its answer.
 *
 * @param string $html SEO body HTML.
 * @return array<int, array{question: string, answer: string}>
 */
function diako_extract_category_seo_faqs( $html ) {
	$html = trim( (string) $html );

	if ( '' === $html || ! class_exists( 'DOMDocument' ) ) {
		return array();
	}

	$previous = libxml_use_internal_errors( true );

	$dom = new DOMDocument();
	$dom->loadHTML(
		'<?xml encoding="utf-8" ?><div class="diako-seo-root">' . $html . '</div>',
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
	);

	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	$root = $dom->documentElement;

	if ( ! $root instanceof DOMElement ) {
		return array();
	}

	$faqs       = array();
	$in_faq     = false;
	$question   = '';
	$answer     = '';
	$flush_item = static function () use ( &$faqs, &$question, &$answer ) {
		$q = trim( preg_replace( '/\s+/u', ' ', $question ) );
		$a = trim( preg_replace( '/\s+/u', ' ', $answer ) );

		if ( '' !== $q && '' !== $a ) {
			$faqs[] = array(
				'question' => $q,
				'answer'   => $a,
			);
		}

		$question = '';
		$answer   = '';
	};

	foreach ( $root->childNodes as $node ) {
		if ( XML_ELEMENT_NODE !== $node->nodeType ) {
			continue;
		}

		$tag  = strtolower( $node->nodeName );
		$text = (string) $node->textContent;

		if ( 'h2' === $tag ) {
			$flush_item();
			$in_faq = diako_text_is_faq_heading( $text );
			continue;
		}

		if ( ! $in_faq ) {
			continue;
		}

		if ( 'h3' === $tag || 'h4' === $tag ) {
			$flush_item();
			$question = $text;
			continue;
		}

		if ( '' !== $question ) {
			$answer .= ' ' . $text;
		}
	}

	$flush_item();

	return $faqs;
}

/**
 * Whether a heading label denotes an FAQ section.
 *
 * @param string $text Heading text.
 * @return bool
 */
function diako_text_is_faq_heading( $text ) {
	$text = mb_strtolower( trim( (string) $text ), 'UTF-8' );

	if ( '' === $text ) {
		return false;
	}

	$needles = array( 'سوال', 'سؤال', 'پرسش', 'faq', 'متداول' );

	foreach ( $needles as $needle ) {
		if ( false !== mb_strpos( $text, $needle, 0, 'UTF-8' ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Output FAQPage JSON-LD structured data for the current category archive.
 *
 * @return void
 */
function diako_output_category_faq_schema() {
	$content = diako_get_product_category_seo_content();

	if ( '' === $content ) {
		return;
	}

	$faqs = diako_extract_category_seo_faqs( $content );

	if ( empty( $faqs ) ) {
		return;
	}

	$entities = array();

	foreach ( $faqs as $faq ) {
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( $faq['question'] ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => wp_strip_all_tags( $faq['answer'] ),
			),
		);
	}

	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $entities,
	);

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}
add_action( 'wp_head', 'diako_output_category_faq_schema', 11 );

/**
 * Starter SEO content for the primary catalogue categories.
 *
 * Seeded once into the category description so the categories ship with real,
 * editable content (the admin can then rewrite/extend it freely from the توضیح
 * field — nothing is hard-coded into the template output).
 *
 * @return array<string, string>
 */
function diako_default_category_seo_contents() {
	$contents = array(
		'skincare'     => '
<h2>خرید محصولات مراقبت پوست اورجینال؛ راهنمای کامل روتین پوست</h2>
<p>دسته‌بندی «مراقبت پوست» در فروشگاه انزی، مرجعی برای خرید محصولات اورجینال پاک‌سازی، آبرسانی، ضدپیری و ضدآفتاب است. اگر به‌دنبال ساخت روتین روزانه متناسب با نوع پوست خود هستید، در این صفحه می‌توانید سرم، کرم، تونر و شوینده‌های معتبر را با تضمین اصالت پیدا کنید.</p>
<p>تنوع محصولات این دسته نیازهای پوست خشک، چرب، مختلط و حساس را پوشش می‌دهد؛ از کنترل چربی و جوش تا روشن‌کنندگی و محافظت در برابر آفتاب.</p>

<h3>انواع محصولات مراقبت پوست در انزی</h3>
<ul>
<li><strong>پاک‌کننده و شوینده:</strong> ژل، فوم و میسلار واتر برای پاک‌سازی ملایم بدون خشکی پوست.</li>
<li><strong>سرم و آمپول:</strong> محصولات هدفمند با ترکیباتی مثل هیالورونیک اسید، نیاسینامید و ویتامین C.</li>
<li><strong>مرطوب‌کننده:</strong> کرم و لوسیون برای حفظ سد دفاعی پوست در روز و شب.</li>
<li><strong>ضدآفتاب:</strong> محافظت روزانه با بافت سبک و مناسب استفاده زیر آرایش.</li>
</ul>

<h3>راهنمای انتخاب محصول مناسب پوست</h3>
<p>پیش از خرید، نوع پوست، نگرانی اصلی (خشکی، لک، جوش، حساسیت) و ترکیبات ناسازگار با پوست خود را مشخص کنید. در صفحه هر محصول، کاربرد و نکات استفاده درج شده است تا انتخاب آگاهانه‌تری داشته باشید.</p>

<h3>چرا مراقبت پوست را از انزی بخریم؟</h3>
<ul>
<li>ضمانت اصالت کالا و تاریخ مصرف معتبر.</li>
<li>ارسال سریع و بسته‌بندی ایمن به سراسر کشور.</li>
<li>مشاوره برای شروع روتین ساده و مؤثر.</li>
</ul>

<h2>سوالات متداول درباره مراقبت پوست</h2>
<h3>آیا محصولات انزی اورجینال هستند؟</h3>
<p>بله، تمامی محصولات مراقبت پوست با ضمانت اصالت عرضه می‌شوند.</p>
<h3>چطور روتین مناسب پوستم را شروع کنم؟</h3>
<p>از سه گام پایه شروع کنید: پاک‌کننده، مرطوب‌کننده و ضدآفتاب؛ سپس سرم متناسب با نیاز پوست را اضافه کنید.</p>
<h3>آیا امکان بازگشت محصول بازشده وجود دارد؟</h3>
<p>به دلایل بهداشتی، محصولات بازشده قابل بازگشت نیستند؛ جزئیات در شرایط و قوانین فروشگاه آمده است.</p>',
		'makeup'       => '
<h2>خرید محصولات آرایشی اورجینال</h2>
<p>دسته‌بندی «آرایش» انزی شامل محصولات آرایش صورت، چشم و لب از برندهای معتبر است. تمرکز ما روی کیفیت بافت، ماندگاری و اصالت کالا است تا آرایش روزانه یا مجلسی شما با خیال راحت کامل شود.</p>

<h3>انواع محصولات آرایشی</h3>
<ul>
<li><strong>زیرساز و کرم پودر:</strong> پوشش یکنواخت متناسب با تناژ پوست.</li>
<li><strong>آرایش چشم:</strong> ریمل، خط چشم و سایه با رنگ‌های روز.</li>
<li><strong>آرایش لب:</strong> رژلب، مداد لب و برق لب.</li>
<li><strong>گونه‌نما و کانتور:</strong> محصولات تکمیل‌کننده فرم صورت.</li>
</ul>

<h3>راهنمای انتخاب آرایش</h3>
<p>تناژ پوست، نوع پوشش مورد نیاز و حساسیت پوست اطراف چشم از مهم‌ترین معیارهای انتخاب هستند. پیش از خرید، توضیحات محصول و رنگ‌بندی را بررسی کنید.</p>

<h2>سوالات متداول درباره آرایش</h2>
<h3>چطور رنگ مناسب کرم پودر را انتخاب کنم؟</h3>
<p>تناژ پوست (سرد، گرم یا خنثی) و میزان پوشش مورد نیاز را در نظر بگیرید؛ در صورت تردید با پشتیبانی انزی مشورت کنید.</p>
<h3>آیا محصولات تستر هستند؟</h3>
<p>خیر، مگر آنکه در صفحه محصول به‌صورت شفاف ذکر شده باشد.</p>',
		'beauty-tools' => '
<h2>خرید ابزار زیبایی و مراقبت</h2>
<p>دسته‌بندی «ابزار زیبایی» مکمل روتین مراقبت پوست و آرایش شماست؛ از براش و اسفنج آرایشی تا ابزار پاک‌سازی و نگهداری محصولات.</p>

<h3>انواع ابزار زیبایی</h3>
<ul>
<li><strong>براش و اسفنج:</strong> برای پخش یکنواخت کرم پودر، پودر و سایه.</li>
<li><strong>ابزار پاک‌سازی:</strong> کمک به شست‌وشوی بهتر پوست و آرایش.</li>
<li><strong>لوازم نگهداری:</strong> نظم‌دهی و بهداشت بهتر محصولات آرایشی.</li>
</ul>

<h3>چرا ابزار را از انزی بخریم؟</h3>
<ul>
<li>انتخاب بر اساس کاربرد واقعی در روتین روزانه.</li>
<li>تضمین کیفیت و اصالت کالا.</li>
<li>ارسال سریع و پشتیبانی پس از فروش.</li>
</ul>

<h2>سوالات متداول درباره ابزار زیبایی</h2>
<h3>هر چند وقت یک‌بار باید براش‌ها را بشویم؟</h3>
<p>برای بهداشت پوست، شست‌وشوی منظم براش و اسفنج توصیه می‌شود؛ به‌ویژه پس از استفاده از محصولات کرمی.</p>
<h3>آیا ابزارها گارانتی دارند؟</h3>
<p>در صورت وجود گارانتی، جزئیات آن در صفحه همان محصول درج می‌شود.</p>',
		'hair-care'    => '
<h2>مراقبت مو</h2>
<p>محصولات مراقبت مو در انزی برای تغذیه، ترمیم و استایل مو انتخاب شده‌اند؛ از شامپو و نرم‌کننده تا سرم و ماسک مو.</p>
<h2>سوالات متداول</h2>
<h3>چطور محصول مناسب موهایم را انتخاب کنم؟</h3>
<p>نوع مو (خشک، چرب، آسیب‌دیده، رنگ‌شده) را مشخص کنید و توضیحات محصول را پیش از خرید بخوانید.</p>',
		'body-care'    => '
<h2>مراقبت بدن</h2>
<p>لوسیون، اسکراب و محصولات تخصصی مراقبت بدن برای نرمی و طراوت پوست در این دسته قرار دارند.</p>
<h2>سوالات متداول</h2>
<h3>آیا این محصولات برای پوست حساس مناسب‌اند؟</h3>
<p>ترکیبات و هشدارهای حساسیت را در صفحه محصول بررسی کنید؛ در صورت سابقه حساسیت، تست پچ انجام دهید.</p>',
	);

	return (array) apply_filters( 'diako_default_category_seo_contents', $contents );
}

/**
 * Seed the starter SEO content into the category description once (only where empty).
 *
 * Writes directly to the term_taxonomy table to preserve the rich HTML (the term
 * description kses filters would otherwise strip tables/headings/images during a
 * non-admin request). Admins editing the توضیح field later keep full HTML support.
 *
 * @return void
 */
function diako_seed_category_seo_content() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return;
	}

	if ( get_option( 'diako_category_seo_desc_seeded' ) ) {
		return;
	}

	global $wpdb;

	foreach ( diako_default_category_seo_contents() as $slug => $html ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );

		if ( ! $term instanceof WP_Term ) {
			continue;
		}

		if ( '' !== trim( (string) $term->description ) ) {
			continue;
		}

		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->term_taxonomy,
			array( 'description' => wp_kses_post( trim( $html ) ) ),
			array( 'term_taxonomy_id' => (int) $term->term_taxonomy_id ),
			array( '%s' ),
			array( '%d' )
		);

		clean_term_cache( (int) $term->term_id, 'product_cat' );
	}

	update_option( 'diako_category_seo_desc_seeded', 1 );
}
add_action( 'init', 'diako_seed_category_seo_content', 25 );
