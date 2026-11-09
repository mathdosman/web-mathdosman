#!/bin/bash
set -e
cd /www/wwwroot/mathdosman.my.id

echo "Generating PNG icons from assets/img/icon.svg..."

convert -background none -density 300 -resize 192x192 assets/img/icon.svg assets/img/icon-192.png
convert -background none -density 300 -resize 512x512 assets/img/icon.svg assets/img/icon-512.png
convert -background none -density 300 -resize 512x512 assets/img/icon.svg assets/img/logo_mathdosman.png
convert -background none -density 300 -resize 1024x1024 assets/img/icon.svg assets/img/logo_mathdosman_1024.png
convert -background none -density 300 -resize 2048x2048 assets/img/icon.svg assets/img/logo_mathdosman_2048.png

echo "Done! Generated:"
ls -lh assets/img/icon-192.png assets/img/icon-512.png assets/img/logo_mathdosman.png assets/img/logo_mathdosman_1024.png assets/img/logo_mathdosman_2048.png
