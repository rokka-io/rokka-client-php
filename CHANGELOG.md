# Change Log

All notable changes to this project will be documented in this file.

This project tries to follow [Semantic Versioning](http://semver.org/) since the beginning,
but small API changes may happen between MINOR versions.

This document mainly describes API changes important to users of this library.

## 0.9 - 2017-08-16

* Add `\Rokka\Client\UrlHelper::addOptionsToUriString(string $url, $options)` and `\Rokka\Client\UrlHelper::addOptionsToUri(UriInterface $url, $options)` for easily adding stack options to an existing URL.
* Drop support for PHP 5.5.  

## 0.8.0 - 2017-06-15

* Add overwrite parameter to `Rokka\Client\Image::createStack($stackName, $stackOperations, $organization = '',  $stackOptions = [], $overwrite = false)`. If set to true, a stack will be overwritten, if it already exists.

## 0.7.0 - 2017-05-17

* BC break! Change `Rokka\Client\Image::getSourceImage($hash, $binaryHash = false, $organization = '')`
  to `Rokka\Client\Image::getSourceImage($hash, $organization = '')`.
* Add `Rokka\Client\Image::deleteSourceImagesWithBinaryHash($binaryHash, $organization = '')`.
* Add `Rokka\Client\Image::getSourceImagesWithBinaryHash($binaryHash, $organization = '')`.
* Add options parameter to `Rokka\Client\Image::setDynamicMetadata(DynamicMetadataInterface $dynamicMetadata, $hash, $organization = '', $options = [])`.
  Only option right now is `['deletePrevious' => true]`, defaults to `false`.
* Add options parameter to `Rokka\Client\Image::deleteDynamicMetadata(DynamicMetadataInterface $dynamicMetadata, $hash, $organization = '', $options = [])`.
  Only option right now is `['deletePrevious' => true]`, defaults to `false`.

