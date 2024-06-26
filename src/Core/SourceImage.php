<?php

namespace Rokka\Client\Core;

use Rokka\Client\Core\DynamicMetadata\DynamicMetadataInterface;
use Rokka\Client\DynamicMetadataHelper;

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
    public $shortHash;

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
     * @var string Original mimetype when it was uploaded
     */
    public $mimetype;

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
     * @var array Static metadata
     */
    public $staticMetadata;

    /**
     * @var \DateTime When this image was first created
     */
    public $created;

    /**
     * @var string
     */
    public $link;

    /**
     * @var bool
     */
    public $protected;

    /**
     * @var bool
     */
    public $locked;

    /**
     * @param string    $organization
     * @param string    $binaryHash
     * @param string    $hash
     * @param string    $name         Original name
     * @param string    $format
     * @param string    $mimetype
     * @param int       $size         File size in bytes
     * @param int       $width        Width in pixels
     * @param int       $height       Height in pixels
     * @param \DateTime $created      Created at date
     * @param string    $link         Link to the image
     * @param string    $shortHash
     * @param bool      $protected
     * @param bool      $locked
     */
    public function __construct(
        $organization,
        $binaryHash,
        $hash,
        $name,
        $format,
        $mimetype,
        $size,
        $width,
        $height,
        array $userMetadata,
        array $dynamicMetadata,
        array $staticMetadata,
        \DateTime $created,
        $link,
        $shortHash = null,
        $protected = false,
        $locked = false
    ) {
        $this->organization = $organization;
        $this->binaryHash = $binaryHash;
        $this->hash = $hash;
        $this->name = $name;
        $this->format = $format;
        $this->mimetype = $mimetype;
        $this->size = $size;
        $this->width = $width;
        $this->height = $height;
        $this->userMetadata = $userMetadata;
        $this->dynamicMetadata = $dynamicMetadata;
        $this->staticMetadata = $staticMetadata;
        $this->created = $created;
        $this->link = $link;
        if (null === $shortHash) {
            $shortHash = $hash;
        }
        $this->shortHash = $shortHash;
        $this->protected = $protected;
        $this->locked = $locked;
    }

    /**
     * Create a source image from the decoded  JSON data.
     */
    public static function createFromDecodedJsonResponse(array $data): self
    {
        if (!isset($data['user_metadata'])) {
            $data['user_metadata'] = [];
        } else {
            foreach ($data['user_metadata'] as $key => $value) {
                if (str_starts_with($key, 'date:')) {
                    $data['user_metadata'][$key] = new \DateTime($value);
                }
            }
        }
        if (!isset($data['static_metadata'])) {
            $data['static_metadata'] = [];
        }

        $dynamic_metadata = [];

        // Rebuild the DynamicMetadata associated to the current SourceImage
        if (isset($data['dynamic_metadata'])) {
            foreach ($data['dynamic_metadata'] as $name => $metadata) {
                $metadata = DynamicMetadataHelper::buildDynamicMetadata($name, $metadata);
                if ($metadata) {
                    $dynamic_metadata[$name] = $metadata;
                }
            }
        }

        return new self(
            $data['organization'],
            $data['binary_hash'],
            $data['hash'],
            $data['name'],
            $data['format'],
            $data['mimetype'],
            $data['size'],
            $data['width'],
            $data['height'],
            $data['user_metadata'],
            $dynamic_metadata,
            $data['static_metadata'],
            new \DateTime($data['created']),
            $data['link'],
            $data['short_hash'],
            $data['protected'],
            $data['locked']
        );
    }

    /**
     * Create a source image from the JSON data.
     */
    public static function createFromJsonResponse(string $data): self
    {
        return self::createFromDecodedJsonResponse(json_decode($data, true));
    }
}
