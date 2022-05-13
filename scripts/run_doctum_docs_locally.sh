#!/usr/bin/env bash

# this script stores your current working dir in a branch called _current
# and doctum will generate docs for that too.
# at the end, it deletes that again.
if [ ! -f /tmp/doctum.phar ]; then
    # Download the latest 5.x release
    curl -s -o /tmp/doctum.phar https://doctum.long-term.support/releases/5.5.1/doctum.phar
    rm -f /tmp/doctum.phar.sha256
    curl -s -o /tmp/doctum.phar.sha256 https://doctum.long-term.support/releases/5.5.1/doctum.phar.sha256
    sha256sum --strict --check /tmp/doctum.phar.sha256
    rm -f /tmp/doctum.phar.sha256
    # You can fetch the latest (5.1.x) version code here:
    # https://doctum.long-term.support/releases/5.1/VERSION
fi
mkdir -p ./doctum-output/build/client-php-api/
cp scripts/redirect_doctum.html ./doctum-output/build/client-php-api/index.html

OLDBRANCH=$(git symbolic-ref --short HEAD)
set -e
finish () {
    git checkout _current
    git reset $OLDBRANCH
    git checkout $OLDBRANCH
    git branch -D _current
    echo "Finished, if everything went well, open doctum-output/build/client-php-api/_current/index.html"
    exit $1
}
trap finish EXIT SIGHUP SIGINT SIGTERM
git checkout -b _current HEAD
set +e
git commit -am "temp"
set -e
export _DOCTUM_BRANCH=_current
php /tmp/doctum.phar update ./scripts/doctum-config.php

