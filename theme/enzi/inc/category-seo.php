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
	$brand = function_exists( 'diako_get_brand_name' ) ? diako_get_brand_name() : 'انزی‌شاپ';

	$generic = '
<h2>خرید از ' . esc_html( $brand ) . '</h2>
<p>در ' . esc_html( $brand ) . ' می‌توانید محصولات این دسته را با ضمانت اصالت، قیمت شفاف و ارسال سریع به سراسر کشور تهیه کنید. قبل از خرید، مشخصات، تصاویر و توضیحات هر محصول را با دقت بررسی کنید.</p>

<h3>چرا از ' . esc_html( $brand ) . ' بخریم؟</h3>
<ul>
<li>ضمانت اصالت کالا و اطلاعات شفاف در صفحه محصول.</li>
<li>ارسال سریع و بسته‌بندی ایمن.</li>
<li>پشتیبانی واقعی در تمام مراحل خرید.</li>
</ul>

<h2>سوالات متداول</h2>
<h3>آیا محصولات ' . esc_html( $brand ) . ' اصل هستند؟</h3>
<p>بله، تمامی محصولات با ضمانت اصالت عرضه می‌شوند مگر آنکه در صفحه محصول خلاف آن ذکر شده باشد.</p>
<h3>چگونه سفارش خود را پیگیری کنم؟</h3>
<p>پس از ثبت سفارش، از بخش پیگیری سفارش یا تماس با پشتیبانی می‌توانید وضعیت ارسال را بررسی کنید.</p>';

	$contents = array(
		'skincare'     => $generic,
		'makeup'       => $generic,
		'beauty-tools' => $generic,
		'hair-care'    => $generic,
		'body-care'    => $generic,
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
