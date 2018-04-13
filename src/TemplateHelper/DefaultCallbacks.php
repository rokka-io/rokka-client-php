<?php

namespace Rokka\Client\TemplateHelper;

use Rokka\Client\Core\SourceImage;
use Rokka\Client\LocalImage\AbstractLocalImage;

/**
 * The default implementation for \Rokka\Client\TemplateHelper\AbstractCallbacks.
 *
 * It stores the hash to an image on the filesystem as json. Either next to the image, if it's on the filesystem,
 * otherwise in the sys_get_temp_dir().
 *
 *
 * @since 1.3.0
 */
class DefaultCallbacks extends AbstractCallbacks
{
    public static $fileExtension = '.rokka.txt';

    public function getHash(AbstractLocalImage $image)
    {
        $hashFile = $this->getHashFilePath($image);
        if (file_exists($hashFile)) {
            $data = json_decode(file_get_contents($hashFile), true);

            return $data['hash'];
        }

        return null;
    }

    public function saveHash(AbstractLocalImage $image, SourceImage $sourceImage)
    {
        //save the metadata file as json, so that in the future we may add more info, if needed
        file_put_contents($this->getHashFilePath($image), json_encode(['hash' => $sourceImage->shortHash]));

        return $sourceImage->shortHash;
    }

    /**
     * @param AbstractLocalImage $image
     *
     * @return string
     */
    private function getHashFilePath(AbstractLocalImage $image)
    {
        $path = $image->getRealpath();
        if (false !== $path) {
            return $path.self::$fileExtension;
        }
        // put it in the system tmp dir, if file doesn't have a real path.
        return sys_get_temp_dir().'/'.str_replace('/', '__', $image->getIdentifier()).self::$fileExtension;
    }
}
