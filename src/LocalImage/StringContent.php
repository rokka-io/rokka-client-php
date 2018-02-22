<?php

namespace Rokka\Client\LocalImage;

class StringContent extends LocalImageAbstract
{
    /**
     * @var null|string
     */
    private $content = null;

    /**
     * StringContent constructor.
     *
     * @param string      $image
     * @param string|null $identifier
     * @param mixed       $context
     */
    public function __construct($image, $identifier = null, $context = null)
    {
        parent::__construct($identifier);
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

    /**
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }
}
