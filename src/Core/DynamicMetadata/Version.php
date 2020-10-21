<?php

namespace Rokka\Client\Core\DynamicMetadata;

class Version implements DynamicMetadataInterface
{
    /**
     * @var string
     */
    public $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * @return string The name of the metadata
     */
    public static function getName()
    {
        return 'version';
    }

    /**
     * Create a CropArea from the decoded JSON data.
     *
     * @param array $data Decoded JSON data
     *
     * @return Version
     */
    public static function createFromDecodedJsonResponse($data)
    {
        $object = new self($data['text']);

        return $object;
    }

    public function getForJson()
    {
        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return void
     */
    public function setText(string $text)
    {
        $this->text = $text;
    }
}
