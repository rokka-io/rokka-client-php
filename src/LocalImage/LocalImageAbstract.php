<?php

namespace Rokka\Client\LocalImage;

/**
 * FIXME: Add some short description.
 *
 * @since 1.3.0
 */
abstract class LocalImageAbstract
{
    /**
     * Can be anything and accessed in callbacks and such.
     *
     * @var mixed|null
     */
    protected $context = null;

    /**
     * The rokka hash from the rokka API.
     *
     * @var string|null
     */
    protected $rokkaHash = null;

    /**
     * A unique identifier for this image, can be any string.
     *
     * This will be used for example for saving the rokka hash somewhere
     *
     * @var string|null
     */
    protected $identifier = null;

    /**
     * @since 1.3.0
     *
     * @param string|null $identifier A unique custom identifier for this image
     */
    public function __construct($identifier = null)
    {
        $this->identifier = $identifier;
    }

    /**
     * Returns the realPath of an image, if there is one.
     * If the file isn't on the local file system (or a stream, php understands), return false.
     *
     * @since 1.3.0
     *
     * @return string|bool
     */
    public function getRealpath()
    {
        return false;
    }

    /**
     * Returns the filename of an image.
     *
     * @since 1.3.0
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
     * Returns the actual content of an image.
     *
     * @since 1.3.0
     *
     * @return string|null
     */
    abstract public function getContent();

    /**
     * Returns the unique custom identifier.
     *
     * @since 1.3.0
     *
     * @return null|string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Sets the unique custom identifier.
     *
     * @since 1.3.0
     *
     * @param string|null $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Returns the custom context.
     *
     * @since 1.3.0
     *
     * @return mixed|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Sets the custom context.
     *
     * @since 1.3.0
     *
     * @param mixed|null $context
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
     * @since 1.3.0
     *
     * @return null|string
     */
    public function getRokkaHash()
    {
        return $this->rokkaHash;
    }
}
