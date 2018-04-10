<?php

namespace Rokka\Client\Core\DynamicMetadata;

class CropArea extends SubjectArea
{
    /**
     * @return string The name of the metadata
     */
    public static function getName()
    {
        return 'crop_area';
    }

    /**
     * Create a CropArea from the JSON data.
     *
     * @param string|array $data    JSON data
     * @param bool         $isArray If the data provided is already an array
     *
     * @return CropArea
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
}
