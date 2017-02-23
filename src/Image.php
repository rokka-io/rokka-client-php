<?php

namespace Rokka\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use Rokka\Client\Core\DynamicMetadata\DynamicMetadataInterface;
use Rokka\Client\Core\OperationCollection;
use Rokka\Client\Core\SourceImage;
use Rokka\Client\Core\SourceImageCollection;
use Rokka\Client\Core\Stack;
use Rokka\Client\Core\StackCollection;

/**
 * Image client for the rokka.io service
 */
class Image extends Base
{
    const SOURCEIMAGE_RESOURCE = 'sourceimages';
    const DYNAMIC_META_RESOURCE = 'meta/dynamic';
    const USER_META_RESOURCE = 'meta/user';

    const STACK_RESOURCE = 'stacks';
    const OPERATIONS_RESOURCE = 'operations';

    /**
     * Default organisation
     *
     * @var string
     */
    private $defaultOrganization;

    /**
     * Constructor.
     *
     * @param ClientInterface $client              Client instance
     * @param string          $defaultOrganization Default organization
     * @param string          $apiKey              API key
     * @param string          $apiSecret           API secret
     */
    public function __construct(ClientInterface $client, $defaultOrganization, $apiKey, $apiSecret)
    {
        parent::__construct($client);

        $this->defaultOrganization = $defaultOrganization;
        $this->setCredentials($apiKey, $apiSecret);
    }

    /**
     * Upload a source image.
     *
     * @param string $contents     Image contents
     * @param string $fileName     Image file name
     * @param string $organization Optional organization
     *
     * @throws \LogicException     If no image contents are provided to be uploaded
     * @return SourceImageCollection
     */
    public function uploadSourceImage($contents, $fileName, $organization = '')
    {
        if (empty($contents)) {
            throw new \LogicException('You need to provide an image content to be uploaded');
        }

        $contents = $this
            ->call('POST', self::SOURCEIMAGE_RESOURCE . '/' . $this->getOrganization($organization), ['multipart' => [
                [
                    'name'     => 'filedata',
                    'contents' => $contents,
                    'filename' => $fileName
                ]
            ]])
            ->getBody()
            ->getContents();

        return SourceImageCollection::createFromJsonResponse($contents);
    }

    /**
     * Delete a source image.
     *
     * @param string $hash         Hash of the image
     * @param string $organization Optional organization name
     *
     * @return boolean True if succesful
     */
    public function deleteSourceImage($hash, $organization = '')
    {
        $response = $this->call('DELETE', implode('/', [self::SOURCEIMAGE_RESOURCE, $this->getOrganization($organization), $hash]));

        return '204' == $response->getStatusCode();
    }

    /**
     * List source images.
     *
     * @param null|integer $limit        Optional limit
     * @param null|integer $offset       Optional offset
     * @param string       $organization Optional organization name
     *
     * @return SourceImageCollection
     */
    public function listSourceImages($limit = null, $offset = null, $organization = '')
    {
        $options = [];

        if ($limit || $offset) {
            $options = ['query' => ['limit' => $limit, 'offset' => $offset]];
        }

        $contents = $this
            ->call('GET', self::SOURCEIMAGE_RESOURCE . '/' . $this->getOrganization($organization), $options)
            ->getBody()
            ->getContents();

        return SourceImageCollection::createFromJsonResponse($contents);
    }

    /**
     * Load a source image's metadata from Rokka.
     *
     * @param string  $hash         Hash of the image
     * @param boolean $binaryHash   Use the binary hash to load the image metadata, rather than the normal hash.
     * @param string  $organization Optional organization name
     *
     * @return SourceImage
     */
    public function getSourceImage($hash, $binaryHash = false, $organization = '')
    {
        $options = [];
        $path = self::SOURCEIMAGE_RESOURCE . '/' . $this->getOrganization($organization);

        if ($binaryHash) {
            $options['query'] = ['binaryHash' => $hash];
        } else {
            $path .= '/' . $hash;
        }

        $contents = $this
            ->call('GET', $path, $options)
            ->getBody()
            ->getContents();

        return SourceImage::createFromJsonResponse($contents);
    }

    /**
     * Get a source image's binary contents from Rokka.
     *
     * @param string  $hash         Hash of the image
     * @param string  $organization Optional organization name
     *
     * @return string
     */
    public function getSourceImageContents($hash, $organization = '') {
        $path = implode('/', [
            self::SOURCEIMAGE_RESOURCE,
            $this->getOrganization($organization),
            $hash,
            'download']
        );

        return $this
            ->call('GET', $path)
            ->getBody()
            ->getContents();
    }

    /**
     * List operations
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
     * Create a stack
     *
     * @param string $stackName       Name of the stack
     * @param array  $stackOperations Stack operations
     * @param string $organization    Optional organization name
     * @param array  $stackOptions    Stack options
     *
     * @return Stack
     */
    public function createStack($stackName, array $stackOperations, $organization = '', array $stackOptions = [])
    {
        $stackData = [
            'operations' => $stackOperations,
            'options' => $stackOptions,
        ];

        $contents = $this
            ->call(
                'PUT',
                implode('/', [self::STACK_RESOURCE, $this->getOrganization($organization), $stackName]),
                ['json' => $stackData]
            )
            ->getBody()
            ->getContents();

        return Stack::createFromJsonResponse($contents);
    }

    /**
     * List stacks
     *
     * @param null|integer $limit        Optional limit
     * @param null|integer $offset       Optional offset
     * @param string       $organization Optional organization name
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
            ->call('GET', self::STACK_RESOURCE . '/' . $this->getOrganization($organization), $options)
            ->getBody()
            ->getContents();

        return StackCollection::createFromJsonResponse($contents);
    }

    /**
     * Return a stack
     *
     * @param string $stackName    Stack name
     * @param string $organization Optional organization name
     *
     * @return Stack
     */
    public function getStack($stackName, $organization = '')
    {
        $contents = $this
            ->call('GET', implode('/', [self::STACK_RESOURCE, $this->getOrganization($organization), $stackName]))
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
     * @return boolean True if successful
     */
    public function deleteStack($stackName, $organization = '')
    {
        $response = $this->call('DELETE', implode('/', [self::STACK_RESOURCE, $this->getOrganization($organization), $stackName]));

        return '204' == $response->getStatusCode();
    }

    /**
     * Add the given DynamicMetadata to a SourceImage.
     * Returns the new Hash for the SourceImage, it could be the same as the input one if the operation
     * did not change it.
     *
     * @param DynamicMetadataInterface $dynamicMetadata The Dynamic Metadata
     * @param string                   $hash            The Image hash
     * @param string                   $organization    Optional organization name
     *
     * @return string|false
     */
    public function setDynamicMetadata(DynamicMetadataInterface $dynamicMetadata, $hash, $organization = '') {

        $path = implode('/', [
            self::SOURCEIMAGE_RESOURCE,
            $this->getOrganization($organization),
            $hash,
            self::DYNAMIC_META_RESOURCE,
            $dynamicMetadata->getName()
        ]);

        $response = $this->call('PUT', $path, ['json' => $dynamicMetadata]);
        if ('204' == $response->getStatusCode()) {
            return $hash;
        }
        if ('201' == $response->getStatusCode()) {
            return $this->extractHashFromLocationHeader($response->getHeader('Location'));
        }

        // Throw an exception to be handled by the caller.
        throw new \LogicException($response->getBody()->getContents(), $response->getStatusCode());
    }

    /**
     * Delete the given DynamicMetadata from a SourceImage.
     * Returns the new Hash for the SourceImage, it could be the same as the input one if the operation
     * did not change it.
     *
     * @param string $dynamicMetadataName The DynamicMetadata name
     * @param string $hash                The Image hash
     * @param string $organization        Optional organization name
     *
     * @return string|false
     */
    public function deleteDynamicMetadata($dynamicMetadataName, $hash, $organization = '') {

        if (empty($hash)) {
            throw new \LogicException('Missing image Hash.');
        }

        if (empty($dynamicMetadataName)) {
            throw new \LogicException('Missing DynamicMetadata name.');
        }

        $path = implode('/', [
            self::SOURCEIMAGE_RESOURCE,
            $this->getOrganization($organization),
            $hash,
            self::DYNAMIC_META_RESOURCE,
            $dynamicMetadataName
        ]);

        $response = $this->call('DELETE', $path);

        if ('204' == $response->getStatusCode()) {
            return $hash;
        }
        if ('201' == $response->getStatusCode()) {
            return $this->extractHashFromLocationHeader($response->getHeader('Location'));
        }

        // Throw an exception to be handled by the caller.
        throw new \LogicException($response->getBody()->getContents(), $response->getStatusCode());
    }

    public function setUserMetadataField($field, $value, $hash, $organization = '') {
        return $this->doUserMetadataRequest([$field => $value], $hash, "PATCH", $organization);
    }

    public function addUserMetadata($fields, $hash, $organization = '') {
        return $this->doUserMetadataRequest($fields, $hash, "PATCH", $organization);
    }

    public function setUserMetadata($fields, $hash, $organization = '') {
        return $this->doUserMetadataRequest($fields, $hash, "PUT", $organization);
    }

    public function deleteUserMetadata($hash, $organization = '') {
        return $this->doUserMetadataRequest(null,  $hash, "DELETE", $organization);
    }

    public function deleteUserMetadataField($field, $hash, $organization = '') {
        return $this->doUserMetadataRequest([$field => null],  $hash, "PATCH", $organization);
    }

    public function deleteUserMetadataFields($fields, $hash, $organization = '') {
        $data = [];
        foreach ($fields as $value) {
            $data[$value] = null;
        }
        return $this->doUserMetadataRequest($data,  $hash, "PATCH", $organization);
    }

    private function doUserMetadataRequest($fields, $hash, $method, $organization = '') {
        $path = implode('/', [
            self::SOURCEIMAGE_RESOURCE,
            $this->getOrganization($organization),
            $hash,
            self::USER_META_RESOURCE
        ]);
        $data = [];
        if ($fields) {
            foreach ($fields as $key => $value) {
                if ($value instanceof \DateTime) {
                    $fields[$key] = $value->setTimezone(new \DateTimeZone("UTC"))->format("Y-m-d\TH:i:s.v\Z");
                }
            }
            $data = ['json' => $fields];
        }
        $response = $this->call($method, $path, $data);
        return true;
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
        return array_pop($parts);
    }

    /**
     * Returns url for accessing the image
     *
     * @param string $hash         Identifier Hash
     * @param string $stack        Stack to apply
     * @param string $format       Image format for output [jpg|png|gif]
     * @param string $name         Optional image name for SEO purposes
     * @param string $organization Optional organization name (if different from default in client)
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

        // Building path
        $path = '/' . $stack . '/' . $hash;

        if (null !== $name) {
            $path .= '/' . $name;
        }

        $path .= '.' . $format;

        // Building the URI as "{scheme}://{organization}.{baseHost}[:{port}]/{stackName}/{hash}[/{name}].{format}"
        $parts = [
            'scheme' => $apiUri->getScheme(),
            'port'   => $apiUri->getPort(),
            'host'   => $this->getOrganization($organization) . '.' . $baseHost,
            'path'   => $path,
        ];

        return Uri::fromParts($parts);
    }

    /**
     * Return the organization or the default if empty.
     *
     * @param string $organization Organization
     *
     * @return string
     */
    private function getOrganization($organization)
    {
        return empty($organization) ? $this->defaultOrganization : $organization;
    }
}
