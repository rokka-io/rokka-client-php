#!/usr/bin/env bash

git stash
git config remote.origin.fetch "+refs/heads/*:refs/remotes/origin/*"
git remote update
mkdir -p ./doctum-output/build/client-php-api/
cp scripts/redirect_doctum.html ./doctum-output/build/client-php-api/index.html
mkdir ./template/
cp -r scripts/doctum_highlight ./template/

exit 0
