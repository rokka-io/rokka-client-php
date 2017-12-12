<?php

namespace Rokka\Client;

use Rokka\Client\Core\SourceImage;
use Rokka\Client\LocalImage\LocalImageAbstract;

class TemplateHelperDefaultCallbacks extends TemplateHelperCallbacksAbstract
{
    public static $fileExtension = '.rokka.txt';

    public static $hashesFolder = '/tmp/';

    /**
     * @param LocalImageAbstract $file
     *
     * @return null|string
     */
    public function getHash(LocalImageAbstract $file)
    {
        $hashFile = $this->getHashFileName($file);
        if (file_exists($hashFile)) {
            $data = json_decode(file_get_contents($hashFile), true);

            return $data['hash'];
        }

        return null;
    }

    /**
     * @param LocalImageAbstract $file
     * @param SourceImage        $sourceImage
     *
     * @return string
     */
    public function saveHash(LocalImageAbstract $file, SourceImage $sourceImage)
    {
        file_put_contents($this->getHashFileName($file), json_encode(['hash' => $sourceImage->shortHash]));

        return $sourceImage->shortHash;
    }

    /**
     * @param LocalImageAbstract $image
     *
     * @return string
     */
    private function getHashFileName(LocalImageAbstract $image)
    {
        $path = $image->getRealpath();
        if (false !== $path) {
            return $path.self::$fileExtension;
        }

        return self::$hashesFolder.'/'.$image->getIdentifier().self::$fileExtension;
    }
}
