<?php
/**
 * Enzi shop theme functions.
 *
 * @package Enzi
 */

defined( 'ABSPATH' ) || exit;

define( 'DIAKO_VERSION', '1.2.0' );
define( 'DIAKO_DIR', get_template_directory() );
define( 'DIAKO_URI', get_template_directory_uri() );

require_once DIAKO_DIR . '/inc/setup.php';
require_once DIAKO_DIR . '/inc/security.php';
require_once DIAKO_DIR . '/inc/login.php';
require_once DIAKO_DIR . '/inc/performance.php';
require_once DIAKO_DIR . '/inc/components.php';
require_once DIAKO_DIR . '/inc/localization.php';
require_once DIAKO_DIR . '/inc/theme-mode.php';
require_once DIAKO_DIR . '/inc/menu.php';
require_once DIAKO_DIR . '/inc/breadcrumbs.php';
require_once DIAKO_DIR . '/inc/enqueue.php';
require_once DIAKO_DIR . '/inc/schema.php';
require_once DIAKO_DIR . '/inc/template-tags.php';
require_once DIAKO_DIR . '/inc/blog.php';
require_once DIAKO_DIR . '/inc/hero-slides.php';
require_once DIAKO_DIR . '/inc/theme-settings.php';
require_once DIAKO_DIR . '/inc/coming-soon-page.php';
require_once DIAKO_DIR . '/inc/branding.php';
require_once DIAKO_DIR . '/inc/order-tracking.php';
require_once DIAKO_DIR . '/inc/legal.php';
require_once DIAKO_DIR . '/inc/contact.php';
require_once DIAKO_DIR . '/inc/about.php';
require_once DIAKO_DIR . '/inc/search.php';
require_once DIAKO_DIR . '/inc/stock-notify.php';
require_once DIAKO_DIR . '/inc/favorites.php';
require_once DIAKO_DIR . '/inc/compare.php';
require_once DIAKO_DIR . '/inc/woocommerce.php';
require_once DIAKO_DIR . '/inc/attribute-color.php';
require_once DIAKO_DIR . '/inc/product-variations.php';
require_once DIAKO_DIR . '/inc/product-card-cart.php';
require_once DIAKO_DIR . '/inc/used-categories.php';
require_once DIAKO_DIR . '/inc/site-health.php';
require_once DIAKO_DIR . '/inc/category-seo.php';
require_once DIAKO_DIR . '/inc/attribute-filters.php';
require_once DIAKO_DIR . '/inc/shop-filters.php';
require_once DIAKO_DIR . '/inc/checkout.php';
require_once DIAKO_DIR . '/inc/woocommerce-i18n.php';
