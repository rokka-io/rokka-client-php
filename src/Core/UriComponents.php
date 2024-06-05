<?php

namespace Rokka\Client\Core;

/**
 * Abstracts the components away of a rokka uri (hash, format, filename, stack, etc).
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
     * @param array $config array shape array{stack: string, hash?: ?string, format?: ?string, filename?: ?string} (not yet supported by doctum)
     *
     * @since 1.2.0
     */
    public static function createFromArray($config): self
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
     *
     * @return void
     */
    public function setStack($stack)
    {
        if ($stack instanceof StackUri) {
            $this->stack = $stack;
        } elseif (\is_string($stack)) {
            $this->stack = new StackUri($stack);
        } else {
            throw new \RuntimeException('Stack needs to be StackUri or string.');
        }
    }

    /**
     * @since 1.2.0
     *
     * @return string|null
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @since 1.2.0
     *
     * @param string|null $hash
     *
     * @return void
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @since 1.2.0
     *
     * @return string|null
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @since 1.2.0
     *
     * @param string|null $format
     *
     * @return void
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @since 1.2.0
     *
     * @return string|null
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @since 1.2.0
     *
     * @param string|null $filename
     *
     * @return void
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @param string $offset
     */
    public function offsetExists($offset): bool
    {
        return property_exists($this, $offset);
    }

    /**
     * @param string $offset
     *
     * @return StackUri|string|null
     */
    #[\ReturnTypeWillChange] // PHP 8 complains other and with < 8.0 there's no mixed return type
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * @param string          $offset
     * @param StackUri|string $value
     */
    #[\ReturnTypeWillChange] // PHP 8 complains other and with < 8.0 there's no mixed return type
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

    /**
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        $this->$offset = null;
    }
}
