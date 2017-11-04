<?php

namespace Rokka\Client;

use Rokka\Client\LocalImage\LocalImageAbstract;

abstract class TemplateHelperCallbacksAbstract
{
    /**
     * @param LocalImageAbstract $file
     * @return null|string
     */
    abstract public function getHash(LocalImageAbstract $file);

    /**
     * @param LocalImageAbstract $file
     * @param string $hash
     * @return void
     */
    abstract public function saveHash(LocalImageAbstract $file, $hash);

    /**
     * @param LocalImageAbstract $file
     * @return array
     */
    public function getMetadata(LocalImageAbstract $file)
    {
        return [];
    }
}