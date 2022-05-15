#!/usr/bin/env bash


git stash
git config remote.origin.fetch "+refs/heads/*:refs/remotes/origin/*"
git remote update

cd /tmp/

if [ ! -f /tmp/doctum.phar ]; then
    # Download the latest 5.x release
    curl -s -o /tmp/doctum.phar https://doctum.long-term.support/releases/5/doctum.phar
    rm -f /tmp/doctum.phar.sha256
    curl -s -o /tmp/doctum.phar.sha256 https://doctum.long-term.support/releases/5/doctum.phar.sha256

    sha256sum --strict --check /tmp/doctum.phar.sha256
    rm -f /tmp/doctum.phar.sha256
    # You can fetch the latest version code here:
    # https://doctum.long-term.support/releases/5/VERSION
fi

mkdir -p ./doctum-output/build/client-php-api/
cp scripts/redirect_doctum.html ./doctum-output/build/client-php-api/index.html
cp -r scripts/doctum_highlight /tmp/
export _DOCTUM_TEMPLATE_DIR=/tmp/
php /tmp/doctum.phar --version
php /tmp/doctum.phar update ./scripts/doctum-config.php

exit 0
