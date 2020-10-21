# Rokka PHP Client

[![Build Status](https://travis-ci.org/rokka-io/rokka-client-php.svg?branch=master)](https://travis-ci.org/rokka-io/rokka-client-php)
[![StyleCI](https://styleci.io/repos/54187640/shield)](https://styleci.io/repos/54187640)
[![Latest Stable Version](https://poser.pugx.org/rokka/client/version.png)](https://packagist.org/packages/rokka/client)

A [PHP](http://php.net/) library to access the API of the [Rokka](https://rokka.io/) image service.

If you are using the Symfony framework, have a look at the [Rokka Symfony Bundle](https://github.com/rokka-io/rokka-client-bundle) which integrates this library into Symfony.

## About

[rokka](https://rokka.io) is digital image processing done right. Store, render and deliver images. Easy and blazingly fast. This library allows to upload and manage your image files to rokka and deliver them in the right format, as light and as fast as possible. And you only pay what you use, no upfront and fixed costs.

Free account plans are available. Just install the plugin, register and use it.

## Installation

Require the library using composer:

`composer require rokka/client`

## Bootstrapping

The `Rokka\Client\Factory` is the entry point for creating the API client.

You will need to register for a Rokka account and use the api key you receive.

### User Client

The user client is used for user and organization management.

```
use Rokka\Client\Factory;

$apiKey = 'myKey';

$userClient = Factory::getUserClient();
$userClient->setCredentials($apiKey);
```

There is an optional parameter to specify the base URL of the Rokka API. This usually does not need to be adjusted.

### Image Client

The image client is used to upload images into an organization and manage output stacks.

```
use Rokka\Client\Factory;

$organization = 'testorganization';
$apiKey = 'myKey';

$imageClient = Factory::getImageClient($organization, $apiKey);
```

There is an optional fourth parameter to specify the base URL of the Rokka API. This usually does not need to be adjusted.

### Options for clients

You can add an options array as 1st or 3rd parameter to Factory::getUserClient` or `Factory::getImageClient`.
It takes the following format:

```
[ 
   Factory::API_BASE_URL => 'https://some-other-api.rokka.io',
   Factory::PROXY => 'http://proxy:8888', // if you need to use a proxy
   Factory::GUZZLE_OPTIONS => ['verify' => false] // any guzzle option you need/want
]

```

## Usage

See the [official documentation](https://rokka.io/documentation) on how to use the Rokka API.

## API Docs

See als the [API Docs](https://rokka.io/client-php-api/master/) for further information.

## Running tests

Run `vendor/bin/phpunit` in the project root.

## Running PHP-CS-Fixer

```
curl https://cs.symfony.com/download/php-cs-fixer-v2.phar > /tmp/php-cs-fixer.phar
php /tmp/php-cs-fixer.phar  fix -v --diff --using-cache=yes src/
```

## Running phpstan

```
./vendor/bin/phpstan.phar analyze -c phpstan.neon -l 8 src/
```