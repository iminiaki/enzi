<?php
/**
 * Result count (Persian digits).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Diako
 * @version 10.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $total, $per_page, $current, $orderedby ) ) {
	return;
}
?>
<p class="woocommerce-result-count" role="status" aria-relevant="all" <?php echo ( empty( $orderedby ) || 1 === intval( $total ) ) ? '' : 'data-is-sorted-by="true"'; ?>>
	<?php
	if ( 1 === intval( $total ) ) {
		echo esc_html( diako_filter_persian_digits_string( __( 'Showing the single result', 'woocommerce' ) ) );
	} elseif ( $total <= $per_page || -1 === $per_page ) {
		$orderedby_placeholder = empty( $orderedby ) ? '%2$s' : '<span class="screen-reader-text">%2$s</span>';
		/* translators: 1: total results 2: sorted by */
		$text = sprintf(
			_n( 'Showing all %1$d result', 'Showing all %1$d results', $total, 'woocommerce' ) . $orderedby_placeholder,
			absint( $total ),
			esc_html( $orderedby )
		);
		$text = diako_filter_persian_digits_string( $text );

		if ( empty( $orderedby ) ) {
			echo esc_html( $text );
		} else {
			echo $text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	} else {
		$first                 = ( $per_page * $current ) - $per_page + 1;
		$last                  = min( $total, $per_page * $current );
		$orderedby_placeholder = empty( $orderedby ) ? '%4$s' : '<span class="screen-reader-text">%4$s</span>';
		/* translators: 1: first result 2: last result 3: total results 4: sorted by */
		$text = sprintf(
			_nx(
				'Showing %1$d–%2$d of %3$d result',
				'Showing %1$d–%2$d of %3$d results',
				$total,
				'with first and last result',
				'woocommerce'
			) . $orderedby_placeholder,
			absint( $first ),
			absint( $last ),
			absint( $total ),
			esc_html( $orderedby )
		);
		$text = diako_filter_persian_digits_string( $text );

		if ( empty( $orderedby ) ) {
			echo esc_html( $text );
		} else {
			echo $text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
	?>
</p>
