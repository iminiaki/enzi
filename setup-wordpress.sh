#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

if [ -f .env ]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
fi

echo "Starting WordPress and MySQL..."
docker compose up -d --build

echo "Waiting for WordPress to be ready..."
for i in $(seq 1 60); do
  if docker compose exec -T wordpress curl -sf http://localhost/wp-admin/install.php >/dev/null 2>&1; then
    break
  fi
  sleep 2
done

echo "Installing WordPress..."
docker compose run --rm wpcli \
  core install \
  --url="${WP_URL}" \
  --title="${WP_TITLE}" \
  --admin_user="${WP_ADMIN_USER}" \
  --admin_password="${WP_ADMIN_PASSWORD}" \
  --admin_email="${WP_ADMIN_EMAIL}" \
  --skip-email \
  2>/dev/null || echo "WordPress may already be installed."

echo "Applying wp-config security constants..."
docker compose run --rm wpcli config set DISALLOW_FILE_EDIT true --raw 2>/dev/null || true
docker compose run --rm wpcli config set WP_DEBUG false --raw 2>/dev/null || true
docker compose run --rm wpcli config set WP_DEBUG_DISPLAY false --raw 2>/dev/null || true
docker compose run --rm wpcli config set WP_POST_REVISIONS 5 --raw 2>/dev/null || true
docker compose run --rm wpcli config set EMPTY_TRASH_DAYS 7 --raw 2>/dev/null || true

echo "Installing WooCommerce..."
if ! docker compose run --rm wpcli plugin is-installed woocommerce >/dev/null 2>&1; then
  docker compose run --rm wpcli plugin install woocommerce --activate
else
  docker compose run --rm wpcli plugin activate woocommerce
fi

echo "Installing UpdraftPlus Backup & Restore..."
if ! docker compose run --rm wpcli plugin is-installed updraftplus >/dev/null 2>&1; then
  curl -fsSL -o updraftplus.zip "https://downloads.wordpress.org/plugin/updraftplus.latest-stable.zip"
  docker compose cp updraftplus.zip wordpress:/var/www/html/updraftplus.zip
  docker compose run --rm wpcli plugin install /var/www/html/updraftplus.zip --activate
  docker compose exec -T wordpress rm -f /var/www/html/updraftplus.zip
  rm -f updraftplus.zip
else
  docker compose run --rm wpcli plugin activate updraftplus
fi

echo "Syncing Diako plugin set (persian-woocommerce*, voorodak, Yoast SEO)..."
if [ -x ./scripts/sync-plugins-from-diako.sh ] && [ -d ../diako ]; then
  ./scripts/sync-plugins-from-diako.sh || echo "Plugin sync skipped/failed — run scripts/sync-plugins-from-diako.sh manually."
else
  echo "Diako project or sync script missing; install premium plugins manually."
fi

echo "Applying Persian locale / store settings..."
docker compose run --rm wpcli language core install fa_IR --activate 2>/dev/null || true
docker compose run --rm wpcli language plugin install woocommerce fa_IR 2>/dev/null || true
docker compose run --rm wpcli option update timezone_string 'Asia/Tehran' || true
docker compose run --rm wpcli option update date_format 'j F Y' || true
docker compose run --rm wpcli option update start_of_week 6 || true
docker compose run --rm wpcli option update woocommerce_currency IRT || true
docker compose run --rm wpcli option update woocommerce_currency_pos right_space || true
docker compose run --rm wpcli option update woocommerce_coming_soon no || true
docker compose run --rm wpcli option update woocommerce_store_pages_only no || true
docker compose run --rm wpcli rewrite structure '/%postname%/' --hard 2>/dev/null || true

echo "Using classic WooCommerce cart/checkout shortcodes (theme templates)..."
docker compose run --rm wpcli eval '
$pages = array(
  "cart"     => array( "title" => "سبد خرید", "content" => "[woocommerce_cart]" ),
  "checkout" => array( "title" => "تسویه حساب", "content" => "[woocommerce_checkout]" ),
);
foreach ( $pages as $key => $data ) {
  $id = (int) wc_get_page_id( $key );
  if ( $id <= 0 ) { continue; }
  wp_update_post( array(
    "ID"           => $id,
    "post_title"   => $data["title"],
    "post_status"  => "publish",
    "post_content" => $data["content"],
  ) );
}
' 2>/dev/null || true
echo "Activating Enzi theme..."
docker compose run --rm wpcli theme activate enzi
docker compose run --rm wpcli eval-file /scripts/activate-enzi-theme.php || true
docker compose run --rm wpcli eval-file /scripts/bootstrap-beauty-categories.php || true
docker compose run --rm wpcli option update blogdescription 'فروشگاه تخصصی مراقبت پوست و زیبایی' || true

# Remove default Hello World / Sample Page so the site starts empty of demo content
echo "Removing default WordPress demo content..."
docker compose run --rm wpcli post delete 1 --force 2>/dev/null || true
docker compose run --rm wpcli post delete 2 --force 2>/dev/null || true
docker compose run --rm wpcli post delete $(docker compose run --rm wpcli post list --post_type=page --name=sample-page --field=ID 2>/dev/null) --force 2>/dev/null || true
docker compose run --rm wpcli plugin delete akismet hello 2>/dev/null || true

echo ""
echo "Enzi WordPress is ready at ${WP_URL}"
echo "Admin login: ${WP_ADMIN_USER} / ${WP_ADMIN_PASSWORD}"
echo "Theme: enzi"
echo "Plugins: WooCommerce, UpdraftPlus, Persian WooCommerce, Shipping, SMS, Voorodak, Yoast SEO (+ Premium)"
echo "No Diako posts, categories, or products were copied."
