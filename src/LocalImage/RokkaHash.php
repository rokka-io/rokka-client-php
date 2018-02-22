<?php

namespace Rokka\Client\LocalImage;

class RokkaHash extends LocalImageAbstract
{
    /**
     * @var null|string
     */
    private $content = null;

    public function __construct($hash, $identifier = null, $context = null)
    {
        parent::__construct($identifier);
        $this->rokkaHash = $hash;
        $this->context = $context;
    }

    public function getIdentifier()
    {
        if (null !== $this->identifier) {
            return $this->identifier;
        }
        $this->identifier = $this->rokkaHash;

        return $this->identifier;
    }

    public function getContent()
    {
        //FIXME: get it from rokka
        return null;
    }
}
