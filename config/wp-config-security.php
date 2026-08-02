<?php
/**
 * WordPress security constants for Enzi deployments.
 *
 * Usage:
 * 1. Docker: applied automatically via WORDPRESS_CONFIG_EXTRA in docker-compose.yml
 * 2. Manual: paste the defines below into wp-config.php above "That's all, stop editing!"
 * 3. Production HTTPS: uncomment the SSL-related defines at the bottom
 *
 * @package Enzi
 */

if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

if ( ! defined( 'WP_DEBUG_DISPLAY' ) ) {
	define( 'WP_DEBUG_DISPLAY', false );
}

if ( ! defined( 'WP_DEBUG_LOG' ) ) {
	define( 'WP_DEBUG_LOG', false );
}

if ( ! defined( 'WP_POST_REVISIONS' ) ) {
	define( 'WP_POST_REVISIONS', 5 );
}

if ( ! defined( 'EMPTY_TRASH_DAYS' ) ) {
	define( 'EMPTY_TRASH_DAYS', 7 );
}

if ( ! defined( 'AUTOMATIC_UPDATER_DISABLED' ) ) {
	define( 'AUTOMATIC_UPDATER_DISABLED', false );
}

// Optional: load reCAPTCHA keys from environment (do not commit real keys to git).
if ( ! defined( 'ENZI_RECAPTCHA_SITE_KEY' ) && getenv( 'ENZI_RECAPTCHA_SITE_KEY' ) ) {
	define( 'ENZI_RECAPTCHA_SITE_KEY', getenv( 'ENZI_RECAPTCHA_SITE_KEY' ) );
}

if ( ! defined( 'ENZI_RECAPTCHA_SECRET_KEY' ) && getenv( 'ENZI_RECAPTCHA_SECRET_KEY' ) ) {
	define( 'ENZI_RECAPTCHA_SECRET_KEY', getenv( 'ENZI_RECAPTCHA_SECRET_KEY' ) );
}

// Also accept DIAKO_* aliases so theme code that still reads those constants keeps working.
if ( ! defined( 'DIAKO_RECAPTCHA_SITE_KEY' ) && defined( 'ENZI_RECAPTCHA_SITE_KEY' ) ) {
	define( 'DIAKO_RECAPTCHA_SITE_KEY', ENZI_RECAPTCHA_SITE_KEY );
}

if ( ! defined( 'DIAKO_RECAPTCHA_SECRET_KEY' ) && defined( 'ENZI_RECAPTCHA_SECRET_KEY' ) ) {
	define( 'DIAKO_RECAPTCHA_SECRET_KEY', ENZI_RECAPTCHA_SECRET_KEY );
}

// Production HTTPS only — uncomment on live sites:
// define( 'FORCE_SSL_ADMIN', true );
// define( 'DISALLOW_FILE_MODS', true );
