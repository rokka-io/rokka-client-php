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
     * @param int $width  Width
     * @param int $height Height
     * @param int $x      X-point
     * @param int $y      Y-point
     */
    public function __construct($x, $y, $width = 1, $height = 1)
    {
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Create a SubjectArea from the JSON data.
     *
     * @param string|array $data    JSON data
     * @param bool         $isArray If the data provided is already an array
     *
     * @return SubjectArea
     */
    public static function createFromJsonResponse($data, $isArray = false)
    {
        if (!$isArray) {
            $data = json_decode($data, true);
        }

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
}
