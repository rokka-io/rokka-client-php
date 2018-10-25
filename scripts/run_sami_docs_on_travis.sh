#!/usr/bin/env bash


git stash
git config remote.origin.fetch "+refs/heads/*:refs/remotes/origin/*"
git remote update

curl -s -o /tmp/sami.phar https://s3.eu-central-1.amazonaws.com/rokka-support-files/sami.phar

mkdir -p ./sami-output/build/client-php-api/
cp scripts/redirect_sami.html ./sami-output/build/client-php-api/index.html
cp -r scripts/sami_highlight /tmp/
export _SAMI_TEMPLATE_DIR=/tmp/
php /tmp/sami.phar -V
php /tmp/sami.phar update ./scripts/sami-config.php

exit 0
