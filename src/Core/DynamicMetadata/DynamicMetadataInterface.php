<?php

namespace Rokka\Client\Core\DynamicMetadata;

/**
 * Marker interface to mark classes as dynamic metadata.
 */
interface DynamicMetadataInterface
{
    /**
     * @return string The name of the metadata
     */
    public static function getName();

    /**
     * Create a DynamicMetadata from the JSON data.
     *
     * @param string|array $data    JSON data
     * @param bool         $isArray If the data provided is already an array
     *
     * @return DynamicMetadataInterface
     */
    public static function createFromJsonResponse($data, $isArray = false);
}
