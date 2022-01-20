# Rokka PHP Client

[![Build Status](https://github.com/rokka-io/rokka-client-php/actions/workflows/ci.yml/badge.svg)](https://github.com/rokka-io/rokka-client-php/actions/workflows/ci.yml)
[![Static analysis](https://github.com/rokka-io/rokka-client-php/actions/workflows/static.yml/badge.svg)](https://github.com/rokka-io/rokka-client-php/actions/workflows/static.yml)
[![Latest Stable Version](https://poser.pugx.org/rokka/client/version.png)](https://packagist.org/packages/rokka/client)

A [PHP](https://www.php.net/) library to access the API of the [Rokka](https://rokka.io/) image service.

If you are using the Symfony framework, have a look at the [Rokka Symfony Bundle](https://github.com/rokka-io/rokka-client-bundle) which integrates this library into Symfony.

## About

[rokka](https://rokka.io) is digital image processing done right. Store, render and deliver images. Easy and blazingly fast. This library allows to upload and manage your image files to rokka and deliver them in the right format, as light and as fast as possible. And you only pay what you use, no upfront and fixed costs.

Free account plans are available. Just install the plugin, register and use it.

## Installation

Require the library using composer:

`composer require rokka/client`

## Bootstrapping

You will need to register for a Rokka.io account and use the API key you receive.
The recommended way to do so is by using the [rokka-cli](https://github.com/rokka-io/rokka-client-php-cli/releases).

The `Rokka\Client\Factory` is the entry point for creating the API client.

You then need to set the credentials you created with the cli command.

### User Client

The user client is used for user and organization management.

```php
use Rokka\Client\Factory;

$apiKey = 'myKey';

$userClient = Factory::getUserClient($organization = null, $apiKey =null, $options = []);
$userClient->setCredentials($apiKey);
```

There is an optional parameter to specify the base URL of the Rokka API. This usually does not need to be adjusted.

### Image Client

The image client is used to upload images into an organization and manage rendering stacks.

```php
use Rokka\Client\Factory;

$organization = 'testorganization';
$apiKey = 'myKey';

$imageClient = Factory::getImageClient($organization, $apiKey);
```

There is an optional parameter to specify the base URL of the Rokka API.
This usually does not need to be adjusted.

### Options for clients

You can add an options array as last parameter to Factory::getUserClient` or `Factory::getImageClient`.
It takes the following format:

```php
[ 
   Factory::API_BASE_URL => 'https://some-other-api.rokka.io',
   Factory::RENDER_BASE_URL => 'https://myimages.example.com', // you you want/have another render base url
   Factory::PROXY => 'http://proxy:8888', // if you need to use a proxy
   Factory::GUZZLE_OPTIONS => ['verify' => false] // any guzzle option you need/want
]
```

## Usage

Read the [Getting Started](https://rokka.io/documentation/guides/get-started.html) guide of the [rokka.io documentation](https://rokka.io/documentation) to learn about the basic concepts of rokka.

The image and user clients provide the operations described in the API Reference section of the rokka documentation.

See als the [API Docs](https://rokka.io/client-php-api/master/) for further information.

## Running tests

Run `vendor/bin/phpunit` in the project root.

## Running PHP-CS-Fixer

```
composer run lint:fix
```

## Running phpstan

```
composer run phpstan
```
