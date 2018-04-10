<?php

namespace Rokka\Client;

use Rokka\Client\Core\SourceImage;
use Rokka\Client\LocalImage\LocalImageAbstract;

/**
 * The default implementation for \Rokka\Client\TemplateHelperCallbacksAbstract.
 *
 * It stores the hash to an image on the filesystem as json. Either next to the image, if it's on the filesystem,
 * otherwise in the sys_get_temp_dir().
 *
 *
 * @since 1.3.0
 */
class TemplateHelperDefaultCallbacks extends TemplateHelperCallbacksAbstract
{
    public static $fileExtension = '.rokka.txt';

    public function getHash(LocalImageAbstract $image)
    {
        $hashFile = $this->getHashFileName($image);
        if (file_exists($hashFile)) {
            $data = json_decode(file_get_contents($hashFile), true);

            return $data['hash'];
        }

        return null;
    }

    public function saveHash(LocalImageAbstract $image, SourceImage $sourceImage)
    {
        file_put_contents($this->getHashFileName($image), json_encode(['hash' => $sourceImage->shortHash]));

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

        return sys_get_temp_dir().'/'.str_replace('/', '__', $image->getIdentifier()).self::$fileExtension;
    }
}
