<?php

namespace Rokka\Client\LocalImage;

use Rokka\Client\Image;
use Rokka\Client\TemplateHelper;

/**
 * Creates a LocalImage object with an existing rokka hash as input.
 *
 * Example:
 *
 * ```language-php
 * $image = new RokkaHash($hash, $identifier, $context);
 * ```
 *
 * If you want to get the source image content by getContent(), you need to inject a TemplateHelper object
 * into the constructor
 *
 * @since 1.3.0
 */
class RokkaHash extends AbstractLocalImage
{
    /**
     * @var string|null
     */
    private $content = null;

    /**
     * @var TemplateHelper|null
     */
    private $templateHelper;

    /**
     * @param string              $hash
     * @param string|null         $identifier
     * @param mixed|null          $context
     * @param TemplateHelper|null $templateHelper
     */
    public function __construct($hash, $identifier = null, $context = null, $templateHelper = null)
    {
        parent::__construct($identifier);
        $this->rokkaHash = $hash;
        $this->context = $context;
        $this->templateHelper = $templateHelper;
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return string|null
     */
    public function getContent()
    {
        if (null === $this->templateHelper) {
            throw new \RuntimeException('Rokka TemplateHelper is not set in RokkaHash. Needed for downloading images.');
        }
        if (null === $this->content) {
            $rokkaHash = $this->getRokkaHash();
            if (null !== $rokkaHash) {
                $this->content = $this->templateHelper->getRokkaClient()->getSourceImageContents($rokkaHash);
            }
        }

        return $this->content;
    }
}
