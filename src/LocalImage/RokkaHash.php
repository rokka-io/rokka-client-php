<?php

namespace Rokka\Client\LocalImage;

class RokkaHash extends LocalImageAbstract
{
    private $content = null;

    private $hash = null;

    /**
     * RokkaHash constructor.
     * @param string $hash
     * @param string|null $identifier
     * @param mixed null $context
     */
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
        if (null === $this->content) {
            $this->content = file_get_contents($this->image->getPathname());
        }

        return $this->content;
    }
}
