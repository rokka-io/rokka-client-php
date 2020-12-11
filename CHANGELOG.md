# Change Log

All notable changes to this project will be documented in this file.

This project tries to follow [Semantic Versioning](http://semver.org/) since the beginning.

This document mainly describes API changes important to users of this library.

## 1.14.0  - 2020-12-11

* Drop PHP 7.0 support. It's ancient.
* Allow deletedDate as search field (some special case here):
* Enable MP4 video uploads in TemplateHelper

## 1.13.1 - 2020-11-09

* Fix dependency on rokka/utils and make it use "^1.0" 

## 1.13.0 - 2020-11-08

* Added `\Rokka\Client\UriHelper::signUrl($url, $key, $until, $roundDateUpTo)` for easy signing of
  private images. See [Protected Images and Stacks](https://rokka.io/documentation/references/protected-images-and-stacks.html) for details.
* Added `\Rokka\Client\User::setOrganizationOption()`
* Added `\Rokka\Client\Core\Organization::getOptions()` and `\Rokka\Client\Core\Organization::getSigningKeys()`
* Added support for making SourceImages protected (via `\Rokka\Client\Image::uploadSourceImage()` or `\Rokka\Client\Image::setProtected()`)
* Added support for adding variables with non-url-supported characters. URLs then contain a `v` query parameter. 
* Upgraded `guzzlehttp/psr7` minimum requirement to 1.7

## 1.12.1 - 2020-10-12

* Added support for Guzzle 7 and Symfony Var_Dumper 5

## 1.12.0 - 2020-09-15

* Added percentage parameter to subject area (thanks to @mms-uret)
* Fixed some phpstan warnings (thanks to @Tobion)
* Replaced Sami by Doctum (thanks to @williamdes )

## 1.11.1 - 2020-05-13

* Fixed validation of search fields for dynamic and static fields

## 1.11.0 - 2020-01-16

* Added `\Rokka\Client\TemplateHelper::getSrcSetUrl` for getting a srcset compatible string with responsive urls
* Remove support for PHP 5.6 in composer.json

## 1.10.0 - 2019-03-11

* removed HHVM support on travis tests, therefore HHVM isn't officially supported anymore.
* Added stack variables and expressions support. See [the documentation](https://rokka.io/documentation/references/stacks.html#expressions) for more details.
* Added optional 3rd boolean parameter to `\Rokka\Client\UriHelper::addOptionsToUriString` and 
  related methods to return short versions for `options` (`o`) and `variables` (`v`)
* Added optional 3rd boolean parameter to `\Rokka\Client\TemplateHelper::getSrcAttributes` to set
  the stack operation option `width` for `resize`. If false, you should add that via the `$sizes`
  parameter, eg `getSrcAttributes(['2x' => 'v-w-500]` (if you want to set the width via a stack variable)

## 1.9.0 - 2018-12-13

* added public `\Rokka\Client\Image::copySourceImages($hashes, $destinationOrg, $overwrite = true, $sourceOrg = '')`
  to copy multiple images at once (max 100). Improves performance a lot.

## 1.8.1 - 2018-12-06

* Fixed search for integer user metadata. https://github.com/rokka-io/rokka-client-php/pull/62 thanks to @pascalvb

## 1.8.0 - 2018-11-05

* Added an object for the new dynamic metadata "version". 
  See https://rokka.io//documentation/references/dynamic-metadata.html#version for details 
* $option in `\Rokka\Client\Image::uploadSourceImage` and `\Rokka\Client\Image::setDynamicMetadata` now takes either
  a single DynamicMetaData Object or an array with the needed fields. Or an array thereof for multiple new sets.
  
## 1.7.0 - 2018-10-25

* Added new membership methods to `\Rokka\Client\User`. It's not totally backwards compatible, but the methods changed were not
  working as documented.
  See [documentation about users and memberships](https://rokka.io/documentation/references/users-and-memberships.html) for details.
  * `\Rokka\Client\Factory::getUserClient` takes an organisation and apikey as 1st and 2nd parameter now. 
  * `\Rokka\Client\Core\Membership` returns now an array with `roles` instead of a string with `role`, since a Membership can now multiple roles.
  * Added `\Rokka\Client\User::getCurrentUserId()`. Returns the user_id for the logged in user.
  * Added `\Rokka\Client\User::createUserAndMembership()`.
  * Added `\Rokka\Client\User::listMemberships()`.
  * Changed parameter order of `\Rokka\Client\User::createMembership()`. Wasn't working before at all.
  * Changed parameter order of `\Rokka\Client\User::getMembership()`. Wasn't working before at all.
  

## 1.6.0 - 2018-09-20

* Added possibility to add a proxy and other guzzle options also to TemplateHelper. 
  See [README.md](README.md#options-for-clients) for details.

## 1.5.0 - 2018-09-20

* Added possibility to add a proxy and other guzzle options to a client. 
  See [README.md](README.md#options-for-clients) for details.

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

