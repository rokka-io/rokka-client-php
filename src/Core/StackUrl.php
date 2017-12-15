<?php

namespace Rokka\Client\Core;

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
}
