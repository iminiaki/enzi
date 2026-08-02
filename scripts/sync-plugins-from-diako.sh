#!/usr/bin/env bash
# Copy the Diako plugin set into Enzi and activate it.
# Usage: ./scripts/sync-plugins-from-diako.sh
set -euo pipefail

ENZI_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DIAKO_DIR="$(cd "$ENZI_DIR/../diako" && pwd)"
TMP="$ENZI_DIR/.tmp-plugins-sync"
PLUGINS=(
  persian-woocommerce
  persian-woocommerce-shipping
  persian-woocommerce-sms
  voorodak
  wordpress-seo
  wordpress-seo-premium
)

if [ ! -d "$DIAKO_DIR" ]; then
  echo "Diako project not found at $DIAKO_DIR" >&2
  exit 1
fi

rm -rf "$TMP"
mkdir -p "$TMP"

echo "Exporting plugins from Diako..."
(
  cd "$DIAKO_DIR"
  docker compose exec -T wordpress tar -C /var/www/html/wp-content/plugins -czf /tmp/diako-plugins.tar.gz "${PLUGINS[@]}"
  docker compose cp wordpress:/tmp/diako-plugins.tar.gz "$TMP/plugins.tar.gz"
  docker compose exec -T wordpress rm -f /tmp/diako-plugins.tar.gz
)

echo "Importing plugins into Enzi..."
(
  cd "$ENZI_DIR"
  docker compose cp "$TMP/plugins.tar.gz" wordpress:/tmp/plugins.tar.gz
  docker compose exec -T wordpress bash -lc '
    set -e
    tar -C /var/www/html/wp-content/plugins -xzf /tmp/plugins.tar.gz
    rm -f /tmp/plugins.tar.gz
    chown -R www-data:www-data /var/www/html/wp-content/plugins || true
  '
  docker compose --profile tools run --rm wpcli plugin activate \
    persian-woocommerce \
    persian-woocommerce-shipping \
    persian-woocommerce-sms \
    voorodak \
    wordpress-seo \
    wordpress-seo-premium \
    woocommerce \
    updraftplus
)

rm -rf "$TMP"
echo "Done. Enzi plugins now match Diako."
