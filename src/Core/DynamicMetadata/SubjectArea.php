<?php

namespace Rokka\Client\Core\DynamicMetadata;

class SubjectArea implements DynamicMetadataInterface
{
    /**
     * @var int
     */
    public $width;

    /**
     * @var int
     */
    public $height;

    /**
     * @var int
     */
    public $x;

    /**
     * @var int
     */
    public $y;

    /**
     * SubjectArea constructor.
     *
     * The SubjectArea can also be defined as a point, by setting both "width" and "height" to 1.
     *
     * @param int $x      X-point of the subject area, 0-based
     * @param int $y      Y-point of the subject area, 0-based
     * @param int $width  The width of the subject area box, default to 1px
     * @param int $height The height of the subject area box, default to 1px
     *
     * @throws \InvalidArgumentException if the provided values are not valid
     */
    public function __construct($x, $y, $width = 1, $height = 1)
    {
        if ($x < 0 || $y < 0) {
            throw new \InvalidArgumentException('Invalid position, "x" and "y" values must be positive integers');
        }

        $this->x = $x;
        $this->y = $y;

        if ($width < 1 || $height < 1) {
            throw new \InvalidArgumentException('Invalid dimensions, "width" and "height" values must be greater than zero');
        }
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Create a SubjectArea from the decoded JSON data.
     *
     * @param array $data Decoded JSON data
     *
     * @return SubjectArea
     */
    public static function createFromDecodedJsonResponse($data)
    {
        // Make sure to build the SubjectArea with correct defaults in case of missing attributes.
        $data = array_merge(['x' => 0, 'y' => 0, 'width' => 1, 'height' => 1], $data);

        return new self(
            max(0, $data['x']),
            max(0, $data['y']),
            max(1, $data['width']),
            max(1, $data['height'])
        );
    }

    /**
     * @return string The name of the metadata
     */
    public static function getName()
    {
        return 'subject_area';
    }

    public function getForJson()
    {
        return $this;
    }
}
