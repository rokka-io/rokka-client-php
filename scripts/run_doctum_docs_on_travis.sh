#!/usr/bin/env bash


git stash
git config remote.origin.fetch "+refs/heads/*:refs/remotes/origin/*"
git remote update

curl -s -o /tmp/sami.phar https://s3.eu-central-1.amazonaws.com/rokka-support-files/sami.phar

mkdir -p ./doctum-output/build/client-php-api/
cp scripts/redirect_doctum.html ./doctum-output/build/client-php-api/index.html
cp -r scripts/doctum_highlight /tmp/
export _DOCTUM_TEMPLATE_DIR=/tmp/
php /tmp/doctum.phar --version
php /tmp/doctum.phar update ./scripts/doctum-config.php

exit 0
