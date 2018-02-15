<?php

namespace Rokka\Client\LocalImage;

abstract class LocalImageAbstract
{
    protected $context = null;

    protected $rokkaHash = null;

    /**
     * @var string|null
     */
    protected $identifier = null;

    public function getRealpath()
    {
        return false;
    }

    /**
     * @return string|null
     */
    abstract public function getFilename();

    /**
     * @return string|null
     */
    abstract public function getContent();

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param null|string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @param null $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * Returns the rokkaHash, in case the object knows it already.
     *
     * Happens especially with the RokkaHash LocalImage class, but others may use it too
     *
     * @return null|string
     */
    public function getRokkaHash()
    {
        return $this->rokkaHash;
    }
}
