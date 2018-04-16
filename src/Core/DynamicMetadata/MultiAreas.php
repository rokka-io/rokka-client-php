<?php

namespace Rokka\Client\Core\DynamicMetadata;

use Rokka\Client\DynamicMetadataHelper;

class MultiAreas implements DynamicMetadataInterface
{
    /**
     * @var array
     */
    private $areas = [];

    /**
     * @param array $areas
     */
    public function __construct($areas)
    {
        $this->areas = $areas;
    }

    /**
     * @return string The name of the metadata
     */
    public static function getName()
    {
        return 'multi_areas';
    }

    /**
     * Get all Areas.
     *
     * @return array
     */
    public function getAreas()
    {
        return $this->areas;
    }

    /**
     * Gets an array of Areas with a specific name.
     *
     * @param string $name
     *
     * @return DynamicMetadataInterface[]|null
     */
    public function getArea($name)
    {
        if (!isset($this->areas[$name])) {
            return null;
        }

        return $this->areas[$name];
    }

    /**
     * Gets the first Area with a specific name (all others have no meaning currently).
     *
     * @param string $name
     *
     * @return DynamicMetadataInterface|null
     */
    public function getFirstArea($name)
    {
        if (!isset($this->areas[$name])) {
            return null;
        }
        if (!isset($this->areas[$name][0])) {
            return null;
        }

        return $this->areas[$name][0];
    }

    /**
     * Create a DynamicMetadata from the JSON data.
     *
     * @param string|array $data    JSON data
     * @param bool         $isArray If the data provided is already an array
     *
     * @return DynamicMetadataInterface
     */
    public static function createFromJsonResponse($data, $isArray = false)
    {
        if (!$isArray) {
            $data = json_decode($data, true);
        }
        $areas = [];
        foreach ($data as $name => $area) {
            $areas[$name] = [];
            foreach ($area as $class => $data) {
                $metaClass = DynamicMetadataHelper::getDynamicMetadataClassName($class);
                /* @var DynamicMetadataInterface $metaClass */
                $areas[$name][] = $metaClass::createFromJsonResponse($data, true);
            }
        }

        return new self($areas);
    }

    public function getForJson()
    {
        $areas = [];
        foreach ($this->areas as $name => $area) {
            $areas[$name] = [];
            foreach ($area as $index => $object) {
                /* @var DynamicMetadataInterface $object */
                $areas[$name][$object->getName()] = $object;
            }
        }

        return $areas;
    }
}
