<?php
/**
 * Single Product tabs
 *
 * @package Diako
 * @see     https://woocommerce.com/document/template-structure/
 * @version 9.8.0
 */

defined( 'ABSPATH' ) || exit;

$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( empty( $product_tabs ) ) {
	return;
}

$tab_index = 0;
?>
<div class="diako-product-tabs">
	<div class="diako-product-tabs__list" role="tablist" aria-label="<?php esc_attr_e( 'اطلاعات محصول', 'diako' ); ?>">
		<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
			<?php
			$is_active = 0 === $tab_index;
			++$tab_index;
			?>
			<div class="diako-product-tabs__item<?php echo $is_active ? ' active' : ''; ?>" role="presentation">
				<a
					id="tab-title-<?php echo esc_attr( $key ); ?>"
					class="diako-product-tabs__trigger"
					href="#tab-<?php echo esc_attr( $key ); ?>"
					data-tab-trigger="<?php echo esc_attr( $key ); ?>"
					role="tab"
					aria-controls="tab-<?php echo esc_attr( $key ); ?>"
					aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
					tabindex="<?php echo $is_active ? '0' : '-1'; ?>"
				>
					<?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?>
				</a>
			</div>
		<?php endforeach; ?>
	</div>

	<?php
	$tab_index = 0;
	foreach ( $product_tabs as $key => $product_tab ) :
		$is_active = 0 === $tab_index;
		++$tab_index;
		?>
		<div
			class="diako-product-tabs__panel woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $key ); ?> entry-content<?php echo $is_active ? ' is-active' : ''; ?>"
			id="tab-<?php echo esc_attr( $key ); ?>"
			data-tab-panel="<?php echo esc_attr( $key ); ?>"
			role="tabpanel"
			aria-labelledby="tab-title-<?php echo esc_attr( $key ); ?>"
			<?php echo $is_active ? '' : ' hidden'; ?>
		>
			<?php
			if ( isset( $product_tab['callback'] ) ) {
				call_user_func( $product_tab['callback'], $key, $product_tab );
			}
			?>
		</div>
	<?php endforeach; ?>

	<?php do_action( 'woocommerce_product_after_tabs' ); ?>
</div>
