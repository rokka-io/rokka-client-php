<?php

namespace Rokka\Client\Core\DynamicMetadata;

class DetectionFace implements DynamicMetadataInterface
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
     * DetectionFace constructor.
     */
    public function __construct()
    {
    }

    /**
     * Create a SubjectArea from the JSON data.
     *
     * @param string|array $data    JSON data
     * @param bool         $isArray If the data provided is already an array
     *
     * @return self
     */
    public static function createFromJsonResponse($data, $isArray = false)
    {
        if (!$isArray) {
            $data = json_decode($data, true);
        }

        // Make sure to build the SubjectArea with correct defaults in case of missing attributes.
        $data = array_merge(['x' => 0, 'y' => 0, 'width' => 1, 'height' => 1], $data);

        $object = new self();
        $object->x = $data['x'];
        $object->y = $data['y'];
        $object->width = $data['width'];
        $object->height = $data['height'];

        return $object;
    }

    /**
     * @return string The name of the metadata
     */
    public static function getName()
    {
        return 'detection_face';
    }

    public function getForJson()
    {
        return $this;
    }
}
