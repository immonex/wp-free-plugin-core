#!/usr/bin/env bash

MAKEPOT_CMD="wp i18n make-pot --domain=immonex-wp-free-plugin-core --exclude=vendor,lib,assets,bin,data"

for folder in ./src/*/ ; do
  if [[ -d "$folder" && ! -L "$folder" && -d "$folder/languages" ]]; then
    CMD="$MAKEPOT_CMD ${folder} ${folder}languages/immonex-wp-free-plugin-core.pot"
    echo $CMD
    $CMD
  fi
done