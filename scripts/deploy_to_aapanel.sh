#!/usr/bin/env bash
set -euo pipefail
# Lightweight deploy helper for aaPanel hosting
# Usage:
#   # local copy to hosting path (run on same machine):
#   ./scripts/deploy_to_aapanel.sh local /www/wwwroot/mathdosman.my.id
#
#   # remote copy via SSH:
#   ./scripts/deploy_to_aapanel.sh ssh user@host:/www/wwwroot/mathdosman.my.id
#
# Notes:
# - This uses rsync and respects .gitignore patterns. It also explicitly
#   excludes config/config.php so you don't accidentally overwrite server config.
# - Run as a user that can write to the destination (or use sudo when needed).

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"

if ! command -v rsync >/dev/null 2>&1; then
    echo "rsync not found — please install rsync and try again" >&2
    exit 2
fi

if [ "$#" -lt 2 ]; then
    echo "Usage: $0 <local|ssh> <dest>" >&2
    echo "Examples:" >&2
    echo "  $0 local /www/wwwroot/mathdosman.my.id" >&2
    echo "  $0 ssh user@host:/www/wwwroot/mathdosman.my.id" >&2
    exit 2
fi

MODE="$1"
DEST="$2"

RSYNC_OPTS=(--archive --verbose --compress --delete --delay-updates --checksum)

# Exclude patterns: use .gitignore where present, but always exclude
# config/config.php (to preserve server-specific config) and logs/uploads.
EXCLUDE_FILE="${REPO_ROOT}/.gitignore"
EXCLUDES=(--exclude 'config/config.php' --exclude '.git/' --exclude 'logs/' --exclude 'gambar/' --exclude 'siswa/uploads/' --exclude 'vendor/')

RSYNC_CMD=(rsync "${RSYNC_OPTS[@]}" "${EXCLUDES[@]}" )

if [ -f "$EXCLUDE_FILE" ]; then
    RSYNC_CMD+=(--exclude-from="$EXCLUDE_FILE")
fi

if [ "$MODE" = "local" ]; then
    echo "Deploying repository -> $DEST (local)"
    "${RSYNC_CMD[@]}" "$REPO_ROOT/" "$DEST/"
else
    # assume MODE == ssh and DEST contains user@host:/path
    echo "Deploying repository -> $DEST (via SSH)"
    "${RSYNC_CMD[@]}" -e ssh "$REPO_ROOT/" "$DEST/"
fi

echo "Deploy finished."
echo "Remember to set ownership and permissions on destination, e.g.:"
echo "  sudo chown -R www:www $DEST && sudo find $DEST -type d -exec chmod 775 {} \\; && sudo find $DEST -type f -exec chmod 664 {} \\;"

exit 0
