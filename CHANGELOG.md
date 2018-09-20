# Change Log

All notable changes to this project will be documented in this file.

This project tries to follow [Semantic Versioning](http://semver.org/) since the beginning.

This document mainly describes API changes important to users of this library.

## 1.5.0 - 2018-09-20

* Added possibility to add a proxy and other guzzle options to a client. See [README.md](README.md#options-for-clients) for details.

## 1.4.0 - 2018-08-15

* Added `\Rokka\Client\Image::uploadSourceImageByUrl($url, $organization = '', $options = null)` for using an URL instead of the image content for adding new images into rokka.
* Added `optimize_source` support for uploading images. See [documentation about source images](https://rokka.io/documentation/references/source-images.html#optimizing-source-images-before-saving) for details.
* Refactored `createFromJsonResponse($data, $isArray = false)` to `createFromJsonResponse($data)` and `createFromDecodedJsonResponse($data)`

## 1.3.2 - 2018-06-16

* Fix PHP 5.6 compatibility for `\Rokka\Client\Core\Stack::createFromConfig`

## 1.3.1 - 2018-04-16

* Make `\Rokka\Client\TemplateHelper::getImageObject` public.

## 1.3.0 - 2018-04-16

* Added `\Rokka\Client\Image::restoreSourceImage($hash, $organization)` for restoring deleted images. See [documentation about source images](https://rokka.io/documentation/references/source-images.html#restore-a-source-image) for details.
* Added `\Rokka\Client\Image::copySourceImage($hash, $destination, $overwrite, $organization)` for copying an image to another organization. See [documentation about source images](https://rokka.io/documentation/references/source-images.html#copy-a-source-image-to-another-organization) for details.
* Added [`\Rokka\Client\TemplateHelper`](https://rokka.io/client-php-api/master/Rokka/Client/TemplateHelper.html) class with many methods for making life easier with template and integration into frameworks and CMS. 
* Added `\Rokka\Client\Core\DynamicMetadata\MultiAreas` and `\Rokka\Client\Core\DynamicMetadata\CropArea`. See [documentation about dynamic metadata](https://rokka.io/documentation/references/dynamic-metadata.html) for details.
* Renamed `\Rokka\Client\Core\StackAbstract` to `\Rokka\Client\Core\AbstractStack`. Deprecated `\Rokka\Client\Core\StackAbstract` (still here for BC reasons) 

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

