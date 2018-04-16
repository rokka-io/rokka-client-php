# Change Log

All notable changes to this project will be documented in this file.

This project tries to follow [Semantic Versioning](http://semver.org/) since the beginning.

This document mainly describes API changes important to users of this library.

## 1.3.0 - unreleased

* Added `\Rokka\Client\Image::restoreSourceImage($hash, $organization)` for restoring deleted images.
* Added `\Rokka\Client\Image::copySourceImage($hash, $destination, $overwrite, $organization)` for copying an image to another organization.
* Added [`\Rokka\Client\TemplateHelper`](https://rokka.io/client-php-api/master/Rokka/Client/TemplateHelper.html) class with many methods for making life easier with template and integration into frameworks and CMS. 
* Renamed `\Rokka\Client\Core\StackAbstract` to `\Rokka\Client\Core\AbstractStack`. Deprecated `\Rokka\Client\Core\StackAbstract` (still here for BC reasons) 
* Added `\Rokka\Client\Core\DynamicMetadata\MultiAreas` and `\Rokka\Client\Core\DynamicMetadata\CropArea`. See [documentation about dynamic metadata](https://rokka.io/documentation/references/dynamic-metadata.html) for details.

## 1.2.0 - 2018-02-19

* Remove 3rd parameter $apiSecret from `\Rokka\Client\Factory::getImageClient()`. 3rd parameter is now the optional $baseUrl. Backwards compatibility is kept, but you're advised to adjust your clients.
* Add `Stack::getDynamicUriString()`
* Add the `StackUri` and '`UriComponents` classes.
* Add `UriHelper::composeUri(array|UriComponents $components): UriInterface` and `UriHelper::decomposeUri(UriInterface $uri): UriComponents` 
* Add `UriHelper::getSrcSetUrl(UriInterface $uri, string $size, null|string $custom = null)` and `UriHelper::getSrcSetUrlString(string $uri, string $size, null|string $custom = null)`
* Implement `Iterator` interface for `OperationCollection`, `SourceImageCollection` and `StackCollection`.
* Rokka PHP Client API docs are automatically generated and published at https://rokka.io/client-php-api/master/

## 1.1.0 - 2017-11-13

* Add support for the new `short_hash` property on SourceImage.
* Client supports the new rokka Stack Expression in the `Stack` Object.
* Add `Rokka\Client\Image::saveStack(Stack $stack, array $requestConfig)`. Supersedes `Rokka\Client\Image::createStack()`,
  which is marked as deprecated for now. See docs for more info.
* Add static method `Stack::createFromConfig(string $stackName, array $config, string $organization = null): Stack`.
* Add setters and getters to the Stack class. 
* Officially deprecated `Rokka\Client\Image::listSourceImages`, use `Rokka\Client\Image::searchSourceImages` instead
* For your info: Deprecated methods will work fine until the next major release (2.0), when they may be removed.

## 1.0.0 - 2017-11-03

* No more beta.
* DateTime object is correctly converted in metadata uploading.
* Supports static metadata from the rokka API and face detection.

## 0.10 - 2017-08-24

* Add $options to `\Rokka\Client\Image::uploadSourceImage($contents, $fileName, $organization = '', $options = null)` for directly adding meta data while uploading

## 0.9 - 2017-08-16

* Add `\Rokka\Client\UriHelper::addOptionsToUriString(string $url, $options)` and `\Rokka\Client\UriHelper::addOptionsToUri(UriInterface $url, $options)` for easily adding stack options to an existing URL.
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

