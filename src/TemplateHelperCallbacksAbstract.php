<?php

namespace Rokka\Client;

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
     * @param string             $hash
     * @param string             $shortHash
     *
     * @return string
     */
    abstract public function saveHash(LocalImageAbstract $file, $hash, $shortHash);

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
