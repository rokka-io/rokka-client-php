<?php

namespace Rokka\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Rokka\Client\Core\DynamicMetadata\DynamicMetadataInterface;
use Rokka\Client\Core\OperationCollection;
use Rokka\Client\Core\SourceImage;
use Rokka\Client\Core\SourceImageCollection;
use Rokka\Client\Core\Stack;
use Rokka\Client\Core\StackCollection;
use Rokka\Client\Core\StackUri;

/**
 * Image client for the rokka.io service.
 *
 * All code examples assume you already have a Rokka Image Client in `$client` with:
 *
 * ```language-php
 * $client = \Rokka\Client\Factory::getImageClient('testorganization', 'apiKey');
 * ```
 */
class Image extends Base
{
    const SOURCEIMAGE_RESOURCE = 'sourceimages';

    const DYNAMIC_META_RESOURCE = 'meta/dynamic';

    const USER_META_RESOURCE = 'meta/user';

    const STACK_RESOURCE = 'stacks';

    const OPERATIONS_RESOURCE = 'operations';

    /**
     * Constructor.
     *
     * @param ClientInterface $client              Client instance
     * @param string          $defaultOrganization Default organization
     * @param string          $apiKey              API key
     */
    public function __construct(ClientInterface $client, $defaultOrganization, $apiKey)
    {
        parent::__construct($client, $defaultOrganization);

        $this->setCredentials($apiKey);
    }

    /**
     * Upload a source image.
     *
     * @param string     $contents     Image contents
     * @param string     $fileName     Image file name
     * @param string     $organization Optional organization
     * @param array|null $options      Options for creating the image (like meta_user and meta_dynamic)
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return SourceImageCollection If no image contents are provided to be uploaded
     */
    public function uploadSourceImage($contents, $fileName, $organization = '', $options = null)
    {
        if (empty($contents)) {
            throw new \LogicException('You need to provide an image content to be uploaded');
        }
        $requestOptions = [[
            'name' => 'filedata',
            'contents' => $contents,
            'filename' => $fileName,
        ]];

        return $this->uploadSourceImageInternal($organization, $options, $requestOptions);
    }

    /**
     * Upload a source image.
     *
     * @param string     $url          url to a remote image
     * @param string     $organization Optional organization
     * @param array|null $options      Options for creating the image (like meta_user and meta_dynamic)
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return SourceImageCollection If no image contents are provided to be uploaded
     */
    public function uploadSourceImageByUrl($url, $organization = '', $options = null)
    {
        if (empty($url)) {
            throw new \LogicException('You need to provide an url to an image');
        }
        $requestOptions = [[
            'name' => 'url[0]',
            'contents' => $url,
        ]];

        return $this->uploadSourceImageInternal($organization, $options, $requestOptions);
    }

    /**
     * Delete a source image.
     *
     * @param string $hash         Hash of the image
     * @param string $organization Optional organization name
     *
     * @throws GuzzleException If the request fails for a different reason than image not found
     * @throws \Exception
     *
     * @return bool True if successful, false if image not found
     */
    public function deleteSourceImage($hash, $organization = '')
    {
        try {
            $response = $this->call('DELETE', implode('/', [self::SOURCEIMAGE_RESOURCE, $this->getOrganizationName($organization), $hash]));
        } catch (GuzzleException $e) {
            if (404 == $e->getCode()) {
                return false;
            }

            throw $e;
        }

        return '204' == $response->getStatusCode();
    }

    /**
     * Restore a source image.
     *
     * @param string $hash         Hash of the image
     * @param string $organization Optional organization name
     *
     * @throws GuzzleException If the request fails for a different reason than image not found
     *
     * @return bool True if successful, false if image not found
     */
    public function restoreSourceImage($hash, $organization = '')
    {
        try {
            $response = $this->call('POST', implode('/', [self::SOURCEIMAGE_RESOURCE, $this->getOrganizationName($organization), $hash, 'restore']));
        } catch (GuzzleException $e) {
            if (404 == $e->getCode()) {
                return false;
            }

            throw $e;
        }

        return '200' == $response->getStatusCode();
    }

    /**
     * Copy a source image to another org.
     *
     * Needs read permissions on the source organization and write permissions on the write organization.
     *
     * @param string $hash           Hash of the image
     * @param string $destinationOrg The destination organization
     * @param bool   $overwrite      If an existing image should be overwritten
     * @param string $sourceOrg      Optional source organization name
     *
     * @throws GuzzleException If the request fails for a different reason than image not found
     *
     * @return bool True if successful, false if source image not found
     */
    public function copySourceImage($hash, $destinationOrg, $overwrite = true, $sourceOrg = '')
    {
        try {
            $headers = ['Destination' => $destinationOrg];
            if (false === $overwrite) {
                $headers['Overwrite'] = 'F';
            }
            $response = $this->call('COPY',
                implode('/', [self::SOURCEIMAGE_RESOURCE, $this->getOrganizationName($sourceOrg), $hash]),
                ['headers' => $headers]
            );
        } catch (GuzzleException $e) {
            if (404 == $e->getCode()) {
                return false;
            }

            throw $e;
        }
        $statusCode = $response->getStatusCode();

        return  $statusCode >= 200 && $statusCode < 300;
    }

    /**
     * Delete source images by binaryhash.
     *
     * Since the same binaryhash can have different images in rokka, this may delete more than one picture.
     *
     * @param string $binaryHash   Hash of the image
     * @param string $organization Optional organization name
     *
     * @throws GuzzleException If the request fails for a different reason than image not found
     * @throws \Exception
     *
     * @return bool True if successful, false if image not found
     */
    public function deleteSourceImagesWithBinaryHash($binaryHash, $organization = '')
    {
        try {
            $response = $this->call('DELETE', implode('/', [self::SOURCEIMAGE_RESOURCE, $this->getOrganizationName($organization)]), ['query' => ['binaryHash' => $binaryHash]]);
        } catch (GuzzleException $e) {
            if (404 == $e->getCode()) {
                return false;
            }

            throw $e;
        }

        return '204' == $response->getStatusCode();
    }

    /**
     * Search and list source images.
     *
     * Sort direction can either be: "asc", "desc" (or the boolean TRUE value, treated as "asc")
     *
     * @param array           $search       The search query, as an associative array "field => value"
     * @param array           $sorts        The sorting parameters, as an associative array "field => sort-direction"
     * @param int|null        $limit        Optional limit
     * @param int|string|null $offset       Optional offset, either integer or the "Cursor" value
     * @param string          $organization Optional organization name
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return SourceImageCollection
     */
    public function searchSourceImages($search = [], $sorts = [], $limit = null, $offset = null, $organization = '')
    {
        $options = ['query' => []];

        $sort = SearchHelper::buildSearchSortParameter($sorts);
        if (!empty($sort)) {
            $options['query']['sort'] = $sort;
        }

        if (\is_array($search) && !empty($search)) {
            foreach ($search as $field => $value) {
                if (!SearchHelper::validateFieldName($field)) {
                    throw new \LogicException(sprintf('Invalid field name "%s" as search field', $field));
                }

                $options['query'][$field] = $value;
            }
        }

        if (isset($limit)) {
            $options['query']['limit'] = $limit;
        }
        if (isset($offset)) {
            $options['query']['offset'] = $offset;
        }

        $contents = $this
          ->call('GET', self::SOURCEIMAGE_RESOURCE.'/'.$this->getOrganizationName($organization), $options)
          ->getBody()
          ->getContents();

        return SourceImageCollection::createFromJsonResponse($contents);
    }

    /**
     * List source images.
     *
     * @deprecated 2.0.0 Use Image::searchSourceImages()
     * @see Image::searchSourceImages()
     *
     * @param null|int        $limit        Optional limit
     * @param null|int|string $offset       Optional offset, either integer or the "Cursor" value
     * @param string          $organization Optional organization name
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return SourceImageCollection
     */
    public function listSourceImages($limit = null, $offset = null, $organization = '')
    {
        return $this->searchSourceImages([], [], $limit, $offset, $organization);
    }

    /**
     * Load a source image's metadata from Rokka.
     *
     * @param string $hash         Hash of the image
     * @param string $organization Optional organization name
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return SourceImage
     */
    public function getSourceImage($hash, $organization = '')
    {
        $path = self::SOURCEIMAGE_RESOURCE.'/'.$this->getOrganizationName($organization);

        $path .= '/'.$hash;

        $contents = $this
            ->call('GET', $path)
            ->getBody()
            ->getContents();

        return SourceImage::createFromJsonResponse($contents);
    }

    /**
     * Loads source images metadata from Rokka by binaryhash.
     *
     * Since the same binaryhash can have different images in rokka, this may return more than one picture.
     *
     * @param string $binaryHash   Hash of the image
     * @param string $organization Optional organization name
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return SourceImageCollection
     */
    public function getSourceImagesWithBinaryHash($binaryHash, $organization = '')
    {
        $path = self::SOURCEIMAGE_RESOURCE.'/'.$this->getOrganizationName($organization);

        $options['query'] = ['binaryHash' => $binaryHash];
        $contents = $this
            ->call('GET', $path, $options)
            ->getBody()
            ->getContents();

        return SourceImageCollection::createFromJsonResponse($contents);
    }

    /**
     * Get a source image's binary contents from Rokka.
     *
     * @param string $hash         Hash of the image
     * @param string $organization Optional organization name
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getSourceImageContents($hash, $organization = '')
    {
        $path = implode('/', [
            self::SOURCEIMAGE_RESOURCE,
            $this->getOrganizationName($organization),
            $hash,
            'download', ]
        );

        return $this
            ->call('GET', $path)
            ->getBody()
            ->getContents();
    }

    /**
     * List operations.
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return OperationCollection
     */
    public function listOperations()
    {
        $contents = $this
            ->call('GET', self::OPERATIONS_RESOURCE)
            ->getBody()
            ->getContents();

        return OperationCollection::createFromJsonResponse($contents);
    }

    /**
     * Create a stack.
     *
     * @deprecated 2.0.0 Use Image::saveStack() instead
     * @see Image::saveStack()
     *
     * @param string $stackName       Name of the stack
     * @param array  $stackOperations Stack operations
     * @param string $organization    Optional organization name
     * @param array  $stackOptions    Stack options
     * @param bool   $overwrite       If an existing stack should be overwritten
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return Stack
     */
    public function createStack(
        $stackName,
        array $stackOperations,
        $organization = '',
        array $stackOptions = [],
        $overwrite = false
    ) {
        $stackData = [
            'operations' => $stackOperations,
            'options' => $stackOptions,
        ];
        $stack = Stack::createFromConfig($stackName, $stackData, $organization);

        return $this->saveStack($stack, ['overwrite' => $overwrite]);
    }

    /**
     * Save a stack on rokka.
     *
     * Example:
     * ```language-php
        $stack = new Stack(null, 'teststack');
        $stack->addStackOperation(new StackOperation('resize', ['width' => 200, 'height' => 200]));
        $stack->addStackOperation(new StackOperation('rotate', ['angle' => 45]));
        $stack->setStackOptions(['jpg.quality' => 80]);
        $requestConfig = ['overwrite' => true];
        $stack = $client->saveStack($stack, $requestConfig);
        echo 'Created stack ' . $stack->getName() . PHP_EOL;
     * ```
     * The only requestConfig option currently can be
     * ['overwrite' => true|false] (false is the default)
     *
     * @since 1.1.0
     *
     * @param Stack $stack         the Stack object to be saved
     * @param array $requestConfig options for the request
     *
     * @throws GuzzleException
     * @throws \LogicException   when stack name is not set
     * @throws \RuntimeException
     *
     * @return Stack
     */
    public function saveStack(Stack $stack, array $requestConfig = [])
    {
        if (empty($stack->getName())) {
            throw new \LogicException('Stack has no name, please set one.');
        }
        if (empty($stack->getOrganization())) {
            $stack->setOrganization($this->defaultOrganization);
        }

        $queryString = [];
        if (isset($requestConfig['overwrite']) && true === $requestConfig['overwrite']) {
            $queryString['overwrite'] = 'true';
        }
        $contents = $this
            ->call(
                'PUT',
                implode('/', [self::STACK_RESOURCE, $stack->getOrganization(), $stack->getName()]),
                ['json' => $stack->getConfig(), 'query' => $queryString]
            )
            ->getBody()
            ->getContents();

        return Stack::createFromJsonResponse($contents);
    }

    /**
     * List stacks.
     *
     * ```language-php
     * use Rokka\Client\Core\Stack;
     * $client = \Rokka\Client\Factory::getImageClient('testorganization', 'apiKey');
     * $stacks = $client->listStacks();
     * foreach ($stacks as $stack) {
     *   echo 'Stack ' . $stack->getName() . PHP_EOL;
     * }
     * ```
     *
     * @param null|int $limit        Optional limit
     * @param null|int $offset       Optional offset
     * @param string   $organization Optional organization name
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return StackCollection
     */
    public function listStacks($limit = null, $offset = null, $organization = '')
    {
        $options = [];

        if ($limit || $offset) {
            $options = ['query' => ['limit' => $limit, 'offset' => $offset]];
        }

        $contents = $this
            ->call('GET', self::STACK_RESOURCE.'/'.$this->getOrganizationName($organization), $options)
            ->getBody()
            ->getContents();

        return StackCollection::createFromJsonResponse($contents);
    }

    /**
     * Return a stack.
     *
     * @param string $stackName    Stack name
     * @param string $organization Optional organization name
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return Stack
     */
    public function getStack($stackName, $organization = '')
    {
        $contents = $this
            ->call('GET', implode('/', [self::STACK_RESOURCE, $this->getOrganizationName($organization), $stackName]))
            ->getBody()
            ->getContents();

        return Stack::createFromJsonResponse($contents);
    }

    /**
     * Delete a stack.
     *
     * @param string $stackName    Delete the stack
     * @param string $organization Optional organization name
     *
     * @throws GuzzleException
     *
     * @return bool True if successful
     */
    public function deleteStack($stackName, $organization = '')
    {
        $response = $this->call('DELETE', implode('/', [self::STACK_RESOURCE, $this->getOrganizationName($organization), $stackName]));

        return '204' == $response->getStatusCode();
    }

    /**
     * Add the given DynamicMetadata to a SourceImage.
     * Returns the new Hash for the SourceImage, it could be the same as the input one if the operation
     * did not change it.
     *
     * The only option currently can be
     * ['deletePrevious' => true]
     *
     * which deletes the previous image from rokka (but not the binary, since that's still used)
     * If not set, the original image is kept in rokka.
     *
     * @param DynamicMetadataInterface|array $dynamicMetadata A Dynamic Metadata object, an array with all the needed info.
     *                                                        Or an array with more than one of those.
     * @param string                         $hash            The Image hash
     * @param string                         $organization    Optional organization name
     * @param array                          $options         Optional options
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return string|false
     */
    public function setDynamicMetadata($dynamicMetadata, $hash, $organization = '', $options = [])
    {
        if (!\is_array($dynamicMetadata)) {
            $dynamicMetadata = [$dynamicMetadata];
        }

        $count = 0;
        $response = null;
        foreach ($dynamicMetadata as $value => $data) {
            $callOptions = [];
            if ($data instanceof DynamicMetadataInterface) {
                $name = $data::getName();
                $callOptions['json'] = $data->getForJson();
            } else {
                $name = $value;
                $callOptions['json'] = $data;
            }

            $path = implode('/', [
                self::SOURCEIMAGE_RESOURCE,
                $this->getOrganizationName($organization),
                $hash,
                self::DYNAMIC_META_RESOURCE,
                $name,
            ]);

            // delete the previous, if we're not on the first one anymore, or if we want to delete it.
            if ($count > 0 || (0 === $count && isset($options['deletePrevious']) && $options['deletePrevious'])) {
                $callOptions['query'] = ['deletePrevious' => 'true'];
            }

            ++$count;
            $response = $this->call('PUT', $path, $callOptions);
            if (!($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
                throw new \LogicException($response->getBody()->getContents(), $response->getStatusCode());
            }
            $hash = $this->extractHashFromLocationHeader($response->getHeader('Location'));
        }
        if ($response instanceof ResponseInterface) {
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                return $hash;
            }
            // Throw an exception to be handled by the caller.
            throw new \LogicException($response->getBody()->getContents(), $response->getStatusCode());
        }

        throw new \LogicException('Something went wrong with the call/response to the rokka API', 0);
    }

    /**
     * Delete the given DynamicMetadata from a SourceImage.
     * Returns the new Hash for the SourceImage, it could be the same as the input one if the operation
     * did not change it.
     *
     * The only option currently can be
     * ['deletePrevious' => true]
     * which deletes the previous image from rokka (but not the binary, since that's still used)
     * If not set, the original image is kept in rokka.
     *
     * @param string $dynamicMetadataName The DynamicMetadata name
     * @param string $hash                The Image hash
     * @param string $organization        Optional organization name
     * @param array  $options             Optional options
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return string|false
     */
    public function deleteDynamicMetadata($dynamicMetadataName, $hash, $organization = '', $options = [])
    {
        if (empty($hash)) {
            throw new \LogicException('Missing image Hash.');
        }

        if (empty($dynamicMetadataName)) {
            throw new \LogicException('Missing DynamicMetadata name.');
        }

        $path = implode('/', [
            self::SOURCEIMAGE_RESOURCE,
            $this->getOrganizationName($organization),
            $hash,
            self::DYNAMIC_META_RESOURCE,
            $dynamicMetadataName,
        ]);

        $callOptions = [];
        if (isset($options['deletePrevious']) && $options['deletePrevious']) {
            $callOptions['query'] = ['deletePrevious' => 'true'];
        }

        $response = $this->call('DELETE', $path, $callOptions);

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            return $this->extractHashFromLocationHeader($response->getHeader('Location'));
        }

        // Throw an exception to be handled by the caller.
        throw new \LogicException($response->getBody()->getContents(), $response->getStatusCode());
    }

    /**
     * Add (or update) the given user-metadata field to the image.
     *
     * @param string $field        The field name
     * @param string $value        The field value
     * @param string $hash         The image hash
     * @param string $organization The organization name
     *
     * @throws GuzzleException
     *
     * @return bool
     */
    public function setUserMetadataField($field, $value, $hash, $organization = '')
    {
        return $this->doUserMetadataRequest([$field => $value], $hash, 'PATCH', $organization);
    }

    /**
     * Add the given fields to the user-metadata of the image.
     *
     * @param array  $fields       An associative array of "field-name => value"
     * @param string $hash         The image hash
     * @param string $organization The organization name
     *
     * @throws GuzzleException
     *
     * @return bool
     */
    public function addUserMetadata($fields, $hash, $organization = '')
    {
        return $this->doUserMetadataRequest($fields, $hash, 'PATCH', $organization);
    }

    /**
     * Set the given fields as the user-metadata of the image.
     *
     * @param array  $fields       An associative array of "field-name => value"
     * @param string $hash         The image hash
     * @param string $organization The organization name
     *
     * @throws GuzzleException
     *
     * @return bool
     */
    public function setUserMetadata($fields, $hash, $organization = '')
    {
        return $this->doUserMetadataRequest($fields, $hash, 'PUT', $organization);
    }

    /**
     * Delete the user-metadata from the given image.
     *
     * @param string $hash         The image hash
     * @param string $organization The organization name
     *
     * @throws GuzzleException
     *
     * @return bool
     */
    public function deleteUserMetadata($hash, $organization = '')
    {
        return $this->doUserMetadataRequest(null, $hash, 'DELETE', $organization);
    }

    /**
     * Delete the given field from the user-metadata of the image.
     *
     * @param string $field        The field name
     * @param string $hash         The image hash
     * @param string $organization The organization name
     *
     * @throws GuzzleException
     *
     * @return bool
     */
    public function deleteUserMetadataField($field, $hash, $organization = '')
    {
        return $this->doUserMetadataRequest([$field => null], $hash, 'PATCH', $organization);
    }

    /**
     * Delete the given fields from the user-metadata of the image.
     *
     * @param array  $fields       The fields name
     * @param string $hash         The image hash
     * @param string $organization The organization name
     *
     * @throws GuzzleException
     *
     * @return bool
     */
    public function deleteUserMetadataFields($fields, $hash, $organization = '')
    {
        $data = [];
        foreach ($fields as $value) {
            $data[$value] = null;
        }

        return $this->doUserMetadataRequest($data, $hash, 'PATCH', $organization);
    }

    /**
     * Returns url for accessing the image.
     *
     * @param string          $hash         Identifier Hash
     * @param string|StackUri $stack        Stack to apply (name or StackUri object)
     * @param string          $format       Image format for output [jpg|png|gif]
     * @param string          $name         Optional image name for SEO purposes
     * @param string          $organization Optional organization name (if different from default in client)
     *
     * @throws \RuntimeException
     *
     * @return UriInterface
     */
    public function getSourceImageUri($hash, $stack, $format = 'jpg', $name = null, $organization = null)
    {
        $apiUri = new Uri($this->client->getConfig('base_uri'));
        $format = strtolower($format);

        // Removing the 'api.' part (third domain level)
        $parts = explode('.', $apiUri->getHost(), 2);
        $baseHost = array_pop($parts);

        $path = UriHelper::composeUri(['stack' => $stack, 'hash' => $hash, 'format' => $format, 'filename' => $name]);
        // Building the URI as "{scheme}://{organization}.{baseHost}[:{port}]/{stackName}/{hash}[/{name}].{format}"
        $parts = [
            'scheme' => $apiUri->getScheme(),
            'port' => $apiUri->getPort(),
            'host' => $this->getOrganizationName($organization).'.'.$baseHost,
            'path' => $path->getPath(),
        ];

        return Uri::fromParts($parts);
    }

    /**
     * Helper function to extract from a Location header the image hash, only the first Location is used.
     *
     * @param array $headers The collection of Location headers
     *
     * @return string|false
     */
    protected function extractHashFromLocationHeader(array $headers)
    {
        $location = reset($headers);

        // Check if we got a Location header, otherwise something went wrong here.
        if (empty($location)) {
            return false;
        }

        $uri = new Uri($location);
        $parts = explode('/', $uri->getPath());

        // Returning just the HASH part for "api.rokka.io/organization/sourceimages/{HASH}"
        $return = array_pop($parts);
        if (null === $return) {
            return false;
        }

        return $return;
    }

    /**
     * @param array|null $fields
     * @param string     $hash
     * @param string     $method
     * @param string     $organization
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return bool
     */
    private function doUserMetadataRequest($fields, $hash, $method, $organization = '')
    {
        $path = implode('/', [
            self::SOURCEIMAGE_RESOURCE,
            $this->getOrganizationName($organization),
            $hash,
            self::USER_META_RESOURCE,
        ]);
        $data = [];
        if ($fields) {
            $fields = $this->applyValueTransformationsToUserMeta($fields);
            $data = ['json' => $fields];
        }
        $response = $this->call($method, $path, $data);

        return true;
    }

    private function applyValueTransformationsToUserMeta(array $fields)
    {
        foreach ($fields as $key => $value) {
            if ($value instanceof \DateTime) {
                $fields[$key] = $value->setTimezone(new \DateTimeZone('UTC'))->format("Y-m-d\TH:i:s.v\Z");
            }
        }

        return $fields;
    }

    /**
     * @param string     $organization   Optional organization
     * @param array|null $options        Options for creating the image (like meta_user and meta_dynamic)
     * @param array      $requestOptions multipart options for the guzzle client
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return SourceImageCollection
     */
    private function uploadSourceImageInternal($organization, $options, $requestOptions)
    {
        if (isset($options['meta_user'])) {
            $options['meta_user'] = $this->applyValueTransformationsToUserMeta($options['meta_user']);
            $requestOptions[] = [
                'name' => 'meta_user[0]',
                'contents' => json_encode($options['meta_user']),
            ];
        }

        if (isset($options['meta_dynamic'])) {
            foreach ($options['meta_dynamic'] as $key => $value) {
                if ($value instanceof  DynamicMetadataInterface) {
                    $key = $value::getName();
                    $value = $value->getForJson();
                }
                $requestOptions[] = [
                    'name' => 'meta_dynamic[0]['.$key.']',
                    'contents' => json_encode($value),
                ];
            }
        }

        if (isset($options['optimize_source']) && true === $options['optimize_source']) {
            $requestOptions[] = [
                'name' => 'optimize_source',
                'contents' => 'true',
            ];
        }

        $contents = $this
            ->call('POST', self::SOURCEIMAGE_RESOURCE.'/'.$this->getOrganizationName($organization), ['multipart' => $requestOptions])
            ->getBody()
            ->getContents();

        return SourceImageCollection::createFromJsonResponse($contents);
    }
}
