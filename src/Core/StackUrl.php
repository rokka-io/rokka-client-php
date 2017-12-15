<?php

namespace Rokka\Client\Core;

use Rokka\Client\UriHelper;

class StackUrl extends Stack
{
    /**
     * @var string|null
     */
    private $baseUrl;

    public function __construct($baseUrl = null, $name = null, array $stackOperations = [], array $stackOptions = [])
    {
        $this->baseUrl = $baseUrl;
        parent::__construct(null, $name, $stackOperations, $stackOptions);
    }


    /**
     * Returns the stack url part as it should be with "addOptionsToUrl" calls
     *
     * @since 1.2.0
     *
     * @return string
     */
    public function getStackUrl()
    {
        return trim(UriHelper::composeUri(['stack' => $this])->getPath(), '/');
    }
}
