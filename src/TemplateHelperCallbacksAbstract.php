<?php

namespace Rokka\Client;

use Rokka\Client\Core\SourceImage;
use Rokka\Client\LocalImage\LocalImageAbstract;

abstract class TemplateHelperCallbacksAbstract
{
    /**
     * @param LocalImageAbstract $file
     *
     * @return null|string
     */
    abstract public function getHash(LocalImageAbstract $file);

    /**
     * @param LocalImageAbstract $file
     * @param SourceImage $sourceImage
     * @return string
     */
    abstract public function saveHash(LocalImageAbstract $file, SourceImage $sourceImage);

    /**
     * @param LocalImageAbstract $file
     *
     * @return array
     */
    public function getMetadata(LocalImageAbstract $file)
    {
        return [];
    }
}
