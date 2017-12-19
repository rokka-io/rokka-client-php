#!/usr/bin/env bash


git stash
git config remote.origin.fetch "+refs/heads/*:refs/remotes/origin/*"
git remote update

if [[ ! -f  /tmp/sami.phar ]]; then
    curl -s -o /tmp/sami.phar http://get.sensiolabs.org/sami.phar
fi
mkdir -p ./sami-output/build/client-php-api/
cp scripts/redirect_sami.html ./sami-output/build/client-php-api/index.html
cp -r scripts/sami_highlight /tmp/
export _SAMI_TEMPLATE_DIR=/tmp/
set -e
php /tmp/sami.phar update ./scripts/sami-config.php

