<?php

namespace Rokka\Client\Core;

use Rokka\Client\UriHelper;

class StackUrl extends Stack
{
    /**
     * @var string|null
     */
    private $baseUrl;

    public function __construct($name = null, array $stackOperations = [], array $stackOptions = [], $baseUrl = null)
    {
        $this->baseUrl = $baseUrl;
        parent::__construct(null, $name, $stackOperations, $stackOptions);
    }

    /**
     * Returns the stack url part as it should be with "addOptionsToUrl" calls.
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
