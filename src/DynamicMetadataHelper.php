<?php

namespace Rokka\Client;

use Rokka\Client\Core\DynamicMetadata\DynamicMetadataInterface;

class DynamicMetadataHelper
{
    /**
     * @param string $name     The Metadata name
     * @param array  $metadata The Metadata contents, as an array
     *
     * @return DynamicMetadataInterface
     */
    public static function buildDynamicMetadata($name, array $metadata)
    {
        $metaClass = self::getDynamicMetadataClassName($name);
        if (class_exists($metaClass)) {
            /* @var DynamicMetadataInterface $metaClass */
            return  $metaClass::createFromJsonResponse($metadata, true);
        }
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
