<?php

namespace Rokka\Client\LocalImage;

class StringContent extends LocalImageAbstract
{
    private $content = null;

    private $filename = null;

    /**
     * StringContent constructor.
     *
     * @param string      $image
     * @param string|null $identifier
     * @param mixed       $context
     */
    public function __construct($image, $identifier = null, $context = null)
    {
        $this->identifier = $identifier;
        $this->content = $image;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        if (null !== $this->identifier) {
            return $this->identifier;
        }
        $this->identifier = md5($this->getContent());

        return $this->identifier;
    }

    public function getFilename()
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }
}
