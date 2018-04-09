<?php

namespace Rokka\Client\LocalImage;

use Rokka\Client\Image;
use Rokka\Client\TemplateHelper;

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
     * @var TemplateHelper|null
     */
    private $templateHelper;

    /**
     * @param string               $hash
     * @param string|null          $identifier
     * @param mixed|null           $context
     * @param TemplateHelper|null  $templateHelper
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
     * @return null|string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
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
