<?php

namespace Rokka\Client\LocalImage;

use Rokka\Client\Image;

/**
 * FIXME: Add some short description.
 *
 * @since 1.3.0
 */
class RokkaHash extends LocalImageAbstract
{
    /**
     * @var null|string
     */
    private $content = null;

    /**
     * @var Image|null
     */
    private $imageClient;

    /**
     * RokkaHash constructor.
     *
     * @param string      $hash
     * @param string|null $identifier
     * @param mixed|null  $context
     * @param Image|null  $imageClient
     */
    public function __construct($hash, $identifier = null, $context = null, $imageClient = null)
    {
        parent::__construct($identifier);
        $this->rokkaHash = $hash;
        $this->context = $context;
        $this->imageClient = $imageClient;
    }

    public function getIdentifier()
    {
        if (null !== $this->identifier) {
            return $this->identifier;
        }
        $this->identifier = $this->getRokkaHash();

        return $this->identifier;
    }

    /**
     * Returns the actual content of an image.
     *
     * @since 1.3.0
     *
     * @return null|string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getContent()
    {
        if (null === $this->imageClient) {
            throw new \RuntimeException('Rokka Image Client is not set for downloading images');
        }
        if (null === $this->content) {
            $rokkaHash = $this->getRokkaHash();
            if (null !== $rokkaHash) {
                $this->content = $this->imageClient->getSourceImageContents($rokkaHash);
            }
        }

        return $this->content;
    }
}
