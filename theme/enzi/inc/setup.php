<?php
/**
 * Theme setup.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'diako', DIAKO_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

		register_nav_menus(
			array(
				'primary' => __( 'منوی اصلی', 'diako' ),
				'footer'  => __( 'منوی فوتر', 'diako' ),
			)
		);

		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
	}
);

add_action(
	'widgets_init',
	function () {
		register_sidebar(
			array(
				'name'          => __( 'سایدبار فروشگاه', 'diako' ),
				'id'            => 'shop-sidebar',
				'description'   => __( 'ویجت‌های فیلتر محصول (قیمت، ویژگی، برچسب و …) در صفحات دسته‌بندی و فروشگاه نمایش داده می‌شوند.', 'diako' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			)
		);

		register_sidebar(
			array(
				'name'          => __( 'سایدبار مجله', 'diako' ),
				'id'            => 'blog-sidebar',
				'description'   => __( 'ویجت‌های کناری صفحات مجله و آرشیو مقالات.', 'diako' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			)
		);
	}
);
