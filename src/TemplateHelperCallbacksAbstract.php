<?php

namespace Rokka\Client;

use Rokka\Client\Core\SourceImage;
use Rokka\Client\LocalImage\LocalImageAbstract;

/**
 * Used for looking up and saving hashes related to a local image.
 *
 * Inject an inherited object of this into new TemplateHelper(), by default
 * \Rokka\Client\TemplateHelperDefaultCallbacks is used.
 *
 * @since 1.3.0
 */
abstract class TemplateHelperCallbacksAbstract
{
    /**
     * Callback when a "local" image needs its hash.
     *
     * Look up if the hash is stored in the right place (DB or similar) and return it.
     * If not stored, return null, so that the picture will be uploaded.
     *
     * @since 1.3.0
     *
     * @param LocalImageAbstract $image
     *
     * @return null|string
     */
    abstract public function getHash(LocalImageAbstract $image);

    /**
     * This method is called, when an image was saved/uploaded to the rokka server.
     * This is the place, where you would store the hash in the right place (DB or similar).
     * Has to return the hash or short hash.
     *
     * @since 1.3.0
     *
     * @param LocalImageAbstract $image       The "local" image
     * @param SourceImage        $sourceImage The SourceImage on the rokka server with all needed meta info
     *
     * @return string hash or shorthash
     */
    abstract public function saveHash(LocalImageAbstract $image, SourceImage $sourceImage);

    /**
     * Return an array of metadata to be sent to the rokka server.
     *
     * @since 1.3.0
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
