<?php

namespace Rokka\Client\Core;

/**
 * Class UriComponents.
 *
 * Abstracts the components away of a rokka uri (hash, format, filename, stack, etc)
 *
 * @since 1.2.0
 */
class UriComponents implements \ArrayAccess
{
    /**
     * @var StackUri
     */
    private $stack;

    /**
     * @var string|null
     */
    private $hash;

    /**
     * @var string|null
     */
    private $format;

    /**
     * @var string|null
     */
    private $filename;

    /**
     * StackUriComponents constructor.
     *
     * @since 1.2.0
     *
     * @param StackUri|string $stack
     * @param string|null     $hash
     * @param string|null     $format
     * @param string|null     $filename
     */
    public function __construct($stack, $hash = null, $format = null, $filename = null)
    {
        $this->setStack($stack);
        $this->hash = $hash;
        $this->format = $format;
        $this->filename = $filename;
    }

    /**
     * Creates a UriComponent object from an array with 'stack', 'hash', 'format', 'filename' and 'stack' as keys.
     *
     * @since 1.2.0
     */
    public static function createFromArray($config)
    {
        if (!isset($config['stack'])) {
            throw new \RuntimeException('Stack has to be set');
        }
        $hash = isset($config['hash']) ? $config['hash'] : null;
        $format = isset($config['format']) ? $config['format'] : null;
        $filename = isset($config['filename']) ? $config['filename'] : null;

        return new self($config['stack'], $hash, $format, $filename);
    }

    /**
     * @since 1.2.0
     *
     * @return StackUri
     */
    public function getStack()
    {
        return $this->stack;
    }

    /**
     * @since 1.2.0
     *
     * @param string|StackUri $stack
     *
     * @throws \RuntimeException
     */
    public function setStack($stack)
    {
        if ($stack instanceof StackUri) {
            $this->stack = $stack;
        } elseif (is_string($stack)) {
            $this->stack = new StackUri($stack);
        } else {
            if ('object' == gettype($stack)) {
                $given = get_class($stack);
            } else {
                $given = gettype($stack);
            }

            throw new \RuntimeException('Stack needs to be StackUri or string. '.$given.' given.');
        }
    }

    /**
     * @since 1.2.0
     *
     * @return null|string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @since 1.2.0
     *
     * @param null|string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @since 1.2.0
     *
     * @return null|string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @since 1.2.0
     *
     * @param null|string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @since 1.2.0
     *
     * @return null|string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @since 1.2.0
     *
     * @param null|string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        if (!property_exists($this, $offset)) {
            throw new \RuntimeException("Property $offset can't be set.");
        }
        if ('stack' === $offset) {
            $this->setStack($value);
        } else {
            $this->$offset = $value;
        }
    }

    public function offsetUnset($offset)
    {
        $this->$offset = null;
    }
}
