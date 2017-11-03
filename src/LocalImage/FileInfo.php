<?php

namespace Rokka\Client\LocalImage;

class FileInfo extends LocalImageAbstract
{
    /**
     * @var \SplFileInfo
     */
    private $image;

    private $content = null;

    private $filename = null;

    /**
     * FileInfo constructor.
     * @param \SplFileInfo $image
     * @param string|null $identifier
     * @param mixed $context
     */
    public function __construct(\SplFileInfo $image, $identifier = null, $context = null)
    {
        $this->identifier = $identifier;
        $this->image = $image;
        $this->context = $context;
    }

    /**
     * @return string|null
     */
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
            $this->identifier = md5($this->getContent());
        }

        return $this->identifier;
    }

    /**
     * @return string|bool
     */
    public function getRealpath()
    {
        return $this->image->getRealPath();
    }

    public function getContent()
    {
        if (null === $this->content) {
            $this->content = file_get_contents($this->image->getPathname());
        }

        return $this->content;
    }
}
