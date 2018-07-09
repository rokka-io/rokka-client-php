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
     * Create a DynamicMetadata from the decoded JSON data.
     *
     * @param array $data Decoded JSON data
     *
     * @return DynamicMetadataInterface
     */
    public static function createFromDecodedJsonResponse($data);

    /**
     * Get the data, which should be json-fied later.
     *
     * @return mixed
     */
    public function getForJson();
}
