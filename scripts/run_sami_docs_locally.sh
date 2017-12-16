#!/usr/bin/env bash

# this script stores your current working dir in a branch called _current
# and sami will generate docs for that too.
# at the end, it deletes that again.
if [[ ! -f  /tmp/sami.phar ]]; then
curl -s -o /tmp/sami.phar http://get.sensiolabs.org/sami.phar
fi
mkdir -p ./sami-output/build/client-php-api/
cp scripts/redirect_sami.html ./sami-output/build/client-php-api/index.html

OLDBRANCH=$(git symbolic-ref --short HEAD)
set -e
finish () {
    git checkout _current
    git reset $OLDBRANCH
    git checkout $OLDBRANCH
    git branch -D _current
    echo "Finished, if everything went well, open sami-output/build/client-php-api/_current/index.html"
    exit $1
}
trap finish EXIT SIGHUP SIGINT SIGTERM
git checkout -b _current HEAD
git commit -am "temp"
export _SAMI_BRANCH=_current
php /tmp/sami.phar update ./scripts/sami-config.php

