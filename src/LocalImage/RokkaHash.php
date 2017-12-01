<?php

namespace Rokka\Client\LocalImage;

class RokkaHash extends LocalImageAbstract
{
    private $content = null;

    private $hash = null;

    public function __construct($hash, $identifier = null, $context = null)
    {
        $this->identifier = $identifier;
        $this->rokkaHash = $hash;
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
        $this->identifier = $this->hash;

        return $this->identifier;
    }

    public function getFilename()
    {
        return null;
    }

    public function getContent()
    {
        //FIXME: get it from rokka
        return null;
    }
}
