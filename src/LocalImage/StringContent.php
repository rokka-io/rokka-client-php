<?php

namespace Rokka\Client\LocalImage;

/**
 * Creates a LocalImage object with the content of an image as input.
 *
 * For images on a accessible file system, better use \Rokka\Client\LocalImage\FileInfo
 *
 * Example:
 *
 * ```language-php
 * $image = new StringContent($content, $identifier, $context);
 * ```
 *
 * @see FileInfo
 * @since 1.3.0
 */
class StringContent extends AbstractLocalImage
{
    /**
     * @var string|null
     */
    private $content;

    /**
     * @param string|null $image
     * @param string|null $identifier
     * @param mixed|null  $context
     */
    public function __construct($image, $identifier = null, $context = null)
    {
        parent::__construct($identifier);
        $this->content = $image;
        $this->context = $context;
    }

    public function getIdentifier()
    {
        if (null !== $this->identifier) {
            return $this->identifier;
        }
        $this->identifier = md5((string) $this->getContent());

        return $this->identifier;
    }

    public function getContent()
    {
        return $this->content;
    }
}
