#!/usr/bin/env bash
# ==============================================================================
# Script Auto-Backup Database & Web MATHDOSMAN
# Jalankan via Cron Job di Server Proxmox / aaPanel:
# Contoh cron (setiap hari jam 02:00 pagi):
# 0 2 * * * /bin/bash /www/wwwroot/mathdosman.my.id/scripts/auto_backup.sh >> /var/log/mathdosman_backup.log 2>&1
# ==============================================================================

set -euo pipefail

BACKUP_DIR="/root/backups/mathdosman"
DATE=$(date +"%Y%m%d_%H%M%S")
DB_NAME="web-mathdosman"
DB_USER="root"
DB_PASS="007007" # Sesuaikan password DB Anda jika berbeda
KEEP_DAYS=7

mkdir -p "$BACKUP_DIR"

echo "[$DATE] Memulai backup database $DB_NAME..."

# 1. Dump database ke file kompresi .sql.gz
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_${DATE}.sql.gz"
if command -v mysqldump >/dev/null 2>&1; then
    mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_FILE"
    echo "[$DATE] Backup berhasil disimpan di: $BACKUP_FILE"
else
    echo "[$DATE] Error: mysqldump tidak ditemukan!" >&2
    exit 1
fi

# 2. Hapus backup lama yang berusia lebih dari 7 hari
echo "[$DATE] Membersihkan backup lama (> $KEEP_DAYS hari)..."
find "$BACKUP_DIR" -type f -name "${DB_NAME}_*.sql.gz" -mtime +$KEEP_DAYS -delete

echo "[$DATE] Selesai."

