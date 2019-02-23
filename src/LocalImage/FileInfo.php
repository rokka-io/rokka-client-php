<?php

namespace Rokka\Client\LocalImage;

/**
 * Creates a LocalImage object with a \SplFileInfo object as input.
 *
 * Ideally used for images on a file system, but can be used for anything SplFileInfo supports (php streams and such)
 *
 * Example:
 *
 * With a path to a file:
 *
 * ```language-php
 * $image = new FileInfo(new \SplFileInfo($filepath), $identifier, $context);
 * ```
 *
 * With the image in a variable, please use the \Rokka\Client\LocalImage\StringContent class
 *
 * ```language-php
 * $image = new StringContent($content, $identifier, $context);
 * ```
 *
 * @see \Rokka\Client\LocalImage\StringContent
 * @since 1.3.0
 */
class FileInfo extends AbstractLocalImage
{
    /**
     * @var \SplFileInfo
     */
    private $image;

    /**
     * @var null|string
     */
    private $content = null;

    /**
     * @var null|string
     */
    private $filename = null;

    public function __construct(\SplFileInfo $image, $identifier = null, $context = null)
    {
        parent::__construct($identifier);
        $this->image = $image;
        $this->context = $context;
    }

    public function getFilename()
    {
        if (null !== $this->filename) {
            return $this->filename;
        }
        if (false !== $this->getRealpath()) {
            $this->filename = $this->image->getFilename();
        }

        return $this->filename;
    }

    public function getIdentifier()
    {
        if (null !== $this->identifier) {
            return $this->identifier;
        }
        if (false !== $this->getRealpath()) {
            $this->identifier = $this->image->getFilename();
        } else {
            $this->identifier = md5((string) $this->getContent());
        }

        return $this->identifier;
    }

    public function getRealpath()
    {
        return $this->image->getRealPath();
    }

    /**
     * @return string|null
     */
    public function getContent()
    {
        if (null === $this->content) {
            $content = file_get_contents($this->image->getPathname());
            if (false === $content) {
                $this->content = null;
            } else {
                $this->content = $content;
            }
        }

        return $this->content;
    }
}
