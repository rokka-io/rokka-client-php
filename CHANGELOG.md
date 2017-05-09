# Change Log

All notable changes to this project will be documented in this file.

This project tries to follow [Semantic Versioning](http://semver.org/) since the beginning,
but small API changes may happen between MINOR versions.

This document mainly describes API changes important to users of this library.

## 0.7.0 - unreleased

* BC break! Change `Rokka\Client\Image::getSourceImage($hash, $binaryHash = false, $organization = '')`
  to `Rokka\Client\Image::getSourceImage($hash, $organization = '')`.
* Add `Rokka\Client\Image::deleteSourceImagesWithBinaryHash($binaryHash, $organization = '')`.
* Add `Rokka\Client\Image::getSourceImagesWithBinaryHash($binaryHash, $organization = '')`.
* Add options parameter to `Rokka\Client\Image::setDynamicMetadata(DynamicMetadataInterface $dynamicMetadata, $hash, $organization = '', $options = [])`.
  Only option right now is `['deletePrevious' => true]`, defaults to `false`.

