#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DIST_DIR="$ROOT_DIR/dist"
PACKAGE_NAME="facebook-course-site-v4"
OUT="$DIST_DIR/${PACKAGE_NAME}.zip"

mkdir -p "$DIST_DIR"
rm -f "$OUT"

cd "$ROOT_DIR"
zip -r "$OUT" \
  README.md \
  index.php checkout.php mock_payment.php webhook.php success.php \
  lib admin scripts \
  -x "*.git*" "dist/*" "data/orders.sqlite" "*.DS_Store" >/dev/null

echo "Package created: $OUT"
