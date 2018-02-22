<?php

namespace Rokka\Client;

use Rokka\Client\Core\SourceImage;
use Rokka\Client\LocalImage\LocalImageAbstract;

abstract class TemplateHelperCallbacksAbstract
{
    /**
     * Callback when a "local" image needs its hash
     *
     * Look up if the hash is stored in the right place (DB or similar) and return it.
     * If not stored, return null, so that the picture will be uploaded.
     *
     * @param LocalImageAbstract $file
     *
     * @return null|string
     */
    abstract public function getHash(LocalImageAbstract $file);

    /**
     * Callback when an image was saved/uploaded on the rokka server.
     *
     * Should return the hash or short hash
     *
     * @param LocalImageAbstract $file          The "local" image
     * @param SourceImage        $sourceImage   The SourceImage on the rokka server with all needed meta info
     *
     * @return string hash or shorthash
     */
    abstract public function saveHash(LocalImageAbstract $file, SourceImage $sourceImage);

    /**
     * Return an array of metadata to be sent to the rokka server.
     *
     * If you want to send special metadata to the rokka server for later searching, you can return them here.
     * Will be called, before an image is uploaded to rokka.
     *
     * @param LocalImageAbstract $file
     *
     * @return array
     */
    public function getMetadata(LocalImageAbstract $file)
    {
        return [];
    }
}
