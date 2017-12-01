<?php

namespace Rokka\Client\Core;

/**
 * Represents a collection of source images.
 */
class SourceImageCollection implements \Countable
{
    /**
     * Array of source images.
     *
     * @var SourceImage[]
     */
    private $sourceImages = [];

    /**
     * The total amount of items from the collection, with pagination.
     *
     * @var int
     */
    private $total;

    /**
     * When more than 10k items are returned a cursor is also created.
     *
     * @var string|null
     */
    private $cursor;

    /**
     * Pagination and other links returned by the API.
     *
     * @var array
     */
    private $links = [];

    /**
     * Constructor.
     *
     * @param SourceImage[] $sourceImages Array of source images
     * @param int           $total        The total amount of results matched
     * @param array         $links        The navigation/browse links
     * @param null          $cursor       The navigation cursor
     */
    public function __construct(array $sourceImages, $total, $links = [], $cursor = null)
    {
        $this->sourceImages = $sourceImages;
        $this->total = $total;
        $this->links = $links;
        $this->cursor = $cursor;
    }

    /**
     * Return number in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->sourceImages);
    }

    /**
     * Returns the total amount of items available in the API for the current listing.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Returns the cursor value, if any.
     *
     * @return string|null
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    /**
     * Returns the pagination/navigation links.
     *
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Return the source images in the collection.
     *
     * @return SourceImage[]
     */
    public function getSourceImages()
    {
        return $this->sourceImages;
    }

    /**
     * Create a collection from the JSON data returned by the rokka.io API.
     *
     * @param string $jsonString JSON as a string
     *
     * @return SourceImageCollection
     */
    public static function createFromJsonResponse($jsonString)
    {
        $data = json_decode($jsonString, true);

        $sourceImages = array_map(function ($sourceImage) {
            return SourceImage::createFromJsonResponse($sourceImage, true);
        }, $data['items']);

        $total = isset($data['total']) ? $data['total'] : 0;
        $links = isset($data['links']) ? $data['links'] : [];
        $cursor = isset($data['cursor']) ? $data['cursor'] : null;

        return new self($sourceImages, $total, $links, $cursor);
    }
}
