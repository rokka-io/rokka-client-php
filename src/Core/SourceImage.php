<?php

namespace Rokka\Client\Core;

use Rokka\Client\Core\DynamicMetadata\DynamicMetadataInterface;

/**
 * Represents the metadata of an image.
 */
class SourceImage
{
    /**
     * @var string
     */
    public $organization;

    /**
     * @var string
     */
    public $binaryHash;

    /**
     * @var string
     */
    public $hash;

    /**
     * @var string Original filename that was used when added to service
     */
    public $name;

    /**
     * @var string Original format when it was uploaded (3 letter ending of file)
     */
    public $format;

    /**
     * @var int Size of image in bytes
     */
    public $size;

    /**
     * @var int Width of image in pixels
     */
    public $width;

    /**
     * @var int Height of image in pixels
     */
    public $height;

    /**
     * @var array User metadata
     */
    public $userMetadata;

    /**
     * @var DynamicMetadataInterface[] Dynamic metadata
     */
    public $dynamicMetadata;

    /**
     * @var \DateTime When this image was first created
     */
    public $created;

    /**
     * @var string
     */
    public $link;

    /**
     * Constructor.
     *
     * @param string    $organization    Organization
     * @param string    $binaryHash      Binary hash
     * @param string    $hash            Hash
     * @param string    $name            Original name
     * @param string    $format          Format
     * @param int       $size            File size in bytes
     * @param int       $width           Width in pixels
     * @param int       $height          Height in pixels
     * @param array     $userMetadata    User metadata
     * @param array     $dynamicMetadata Dynamic metadata
     * @param \DateTime $created         Created at date
     * @param string    $link            Link to the image
     */
    public function __construct(
        $organization,
        $binaryHash,
        $hash,
        $name,
        $format,
        $size,
        $width,
        $height,
        array $userMetadata,
        array $dynamicMetadata,
        \DateTime $created,
        $link
    ) {
        $this->organization = $organization;
        $this->binaryHash = $binaryHash;
        $this->hash = $hash;
        $this->name = $name;
        $this->format = $format;
        $this->size = $size;
        $this->width = $width;
        $this->height = $height;
        $this->userMetadata = $userMetadata;
        $this->dynamicMetadata = $dynamicMetadata;
        $this->created = $created;
        $this->link = $link;
    }

    /**
     * Create a source image from the JSON data.
     *
     * @param string|array $data    JSON data
     * @param bool         $isArray If the data provided is already an array
     *
     * @return SourceImage
     */
    public static function createFromJsonResponse($data, $isArray = false)
    {
        if (!$isArray) {
            $data = json_decode($data, true);
        }

        if (!isset($data['user_metadata'])) {
            $data['user_metadata'] = [];
        } else {
            foreach ($data['user_metadata'] as $key => $value) {
                if (strpos($key, 'date:') === 0) {
                    $data['user_metadata'][$key] = new \DateTime($value);
                }
            }
        }

        $dynamic_metadata = [];

        // Rebuild the DynamicMetadata associated to the current SourceImage
        if (isset($data['dynamic_metadata'])) {
            foreach ($data['dynamic_metadata'] as $name => $metadata) {
                $metaClass = self::getDynamicMetadataClassName($name);
                if (class_exists($metaClass)) {
                    /** @var DynamicMetadataInterface $metaClass */
                    $meta = $metaClass::createFromJsonResponse($metadata, true);
                    $dynamic_metadata[$name] = $meta;
                }
            }
        }

        return new self(
            $data['organization'],
            $data['binary_hash'],
            $data['hash'],
            $data['name'],
            $data['format'],
            $data['size'],
            $data['width'],
            $data['height'],
            $data['user_metadata'],
            $dynamic_metadata,
            new \DateTime($data['created']),
            $data['link']
        );
    }

    /**
     * Returns the Dynamic Metadata class name from the API name.
     *
     * @param string $name The Metadata name from the API
     *
     * @return string The DynamicMetadata class name, as fully qualified class name
     */
    public static function getDynamicMetadataClassName($name)
    {
        // Convert to a CamelCase class name.
        // See Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter::denormalize()
        $camelCasedName = preg_replace_callback('/(^|_|\.)+(.)/', function ($match) {
            return ('.' === $match[1] ? '_' : '').strtoupper($match[2]);
        }, $name);

        return 'Rokka\Client\Core\DynamicMetadata\\'.$camelCasedName;
    }
}
