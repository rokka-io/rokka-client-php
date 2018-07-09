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
     * Create a CropArea from the decoded JSON data.
     *
     * @param array $data Decoded JSON data
     *
     * @return CropArea
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
}
