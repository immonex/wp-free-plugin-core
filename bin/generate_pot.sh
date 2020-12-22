#!/usr/bin/env bash

MAKEPOT_CMD="php vendor/inverisoss/wp-i18n-tools/makepot.php generic"

for folder in ./src/*/ ; do
  if [[ -d "$folder" && ! -L "$folder" && -d "$folder/languages" ]]; then
    CMD="$MAKEPOT_CMD src ${folder}languages/immonex-wp-free-plugin-core.pot"
    echo $CMD
    $($CMD)
  fi
done