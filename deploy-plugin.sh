#!/bin/bash 
# ==============================================================================
# Script tự động đồng bộ & cập nhật Plugin iLynk SePay VietQR lên Hosting
# Phát triển bởi iLynk Solution
# ==============================================================================

```bash deploy-plugin.sh ```

set -e

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="$DIR/plugins/ilynk-sepay-vietqr"
PLUGIN_FILE="$PLUGIN_DIR/ilynk-sepay-vietqr.php"
ZIP_FILE="$DIR/plugins/ilynk-sepay-vietqr.zip"

echo "🔍 1. Kiểm tra cú pháp PHP của Plugin..."
php -l "$PLUGIN_FILE"

echo "📦 2. Đóng gói lại file zip cài đặt..."
cd "$DIR/plugins"
rm -f "$ZIP_FILE"
zip -r "$ZIP_FILE" ilynk-sepay-vietqr/ -q

echo "🚀 3. Đẩy code cập nhật lên máy chủ site.thientam68.com qua FTP..."
curl -s -T "$PLUGIN_FILE" --ftp-create-dirs -u 'admin_thientam:xafpTiTNSRrkctGP' 'ftp://site.thientam68.com/wp-content/plugins/ilynk-sepay-vietqr/ilynk-sepay-vietqr.php'
curl -s -T "$PLUGIN_DIR/readme.txt" -u 'admin_thientam:xafpTiTNSRrkctGP' 'ftp://site.thientam68.com/wp-content/plugins/ilynk-sepay-vietqr/readme.txt'

echo "✅ CẬP NHẬT THÀNH CÔNG!"
echo "   - Mã nguồn plugin trên hosting đã được đồng bộ bản mới nhất."
echo "   - File zip cập nhật sẵn tại: plugins/ilynk-sepay-vietqr.zip"
