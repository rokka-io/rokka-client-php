# Rokka PHP Client

A [PHP](http://php.net/) library to access the API of the [Rokka](https://rokka.io/) image service.

If you are using the Symfony framework, have a look at the [Rokka Symfony Bundle](https://raw.githubusercontent.com/rokka-io/rokka-client-bundle) which integrates this library into Symfony.

## Installation

Require the library using composer:

`composer require rokka/client`

## Bootstrapping

The `Rokka\Client\Factory` is the entry point for creating the API client.

You will need to create an account on Rokka to get your api key and secret and create an organization.

### User Client

The user client is used for user and organization management.

```
use Rokka\Client\Factory;

$apiKey = 'myKey';
$apiSecret = 'mySecret';

$userClient = Factory::getUserClient();
$userClient->setCredentials($apiKey, $apiSecret);
```

There is an optional parameter to specify the base URL of the Rokka API. This usually does not need to be adjusted.

### Image Client

The image client is used to upload images into an organization and manage output stacks.

```
use Rokka\Client\Factory;

$organization = 'testorganization';
$apiKey = 'myKey';
$apiSecret = 'mySecret';

$imageClient = Factory::getImageClient($organization, $apiKey, $apiSecret);
```

There is an optional fourth parameter to specify the base URL of the Rokka API. This usually does not need to be adjusted.

## Usage

See the [official documentation](https://rokka.io/documentation) on how to use the Rokka API.

## Running tests

Run `vendor/bin/phpunit` in the project root.
