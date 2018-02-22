<?php

namespace Rokka\Client\LocalImage;

abstract class LocalImageAbstract
{
    /**
     * Can be anything and accessed in callbacks and such
     *
     * @var mixed|null
     */
    protected $context = null;

    /**
     * The rokka hash fromt the rokka API
     *
     * @var string|null
     */
    protected $rokkaHash = null;

    /**
     * A unique identifier for this image
     *
     * This will be used for example for saving the the rokka hash somewhere
     *
     * @var string|null
     */
    protected $identifier = null;

    public function __construct($identifier = null)
    {
        $this->identifier = $identifier;
    }

    /**
     * Returns the realPath of an image, if there is one.
     * If the file isn't on the local file system (or a stream, php understands), return false
     *
     * @return string|bool
     */
    public function getRealpath()
    {
        return false;
    }

    /**
     * Returns the filename of an image
     *
     * This is used for nicer looking generated URLs, but optional
     *
     * @return string|null
     */
    public function getFilename()
    {
        return null;
    }

    /**
     * Returns the actual content of an image
     *
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

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

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
