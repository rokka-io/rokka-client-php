<?php

namespace Rokka\Client;

use Rokka\Client\LocalImage\FileInfo;
use Rokka\Client\LocalImage\LocalImageAbstract;
use Rokka\Client\LocalImage\StringContent;

/**
 * Class TemplateHelper.
 */
class TemplateHelper
{
    private $rokkaApiKey = null;
    private $rokkaOrg = null;
    private $rokkaDomain = null;
    /**
     * @var TemplateHelperCallbacksAbstract
     */
    private $callbacks = null;

    /**
     * TemplateHelper constructor.
     * @param string $org
     * @param string $key
     * @param TemplateHelperCallbacksAbstract|null $callbacks
     * @param string|null $publicRokkaDomain
     */
    public function __construct($org, $key, TemplateHelperCallbacksAbstract $callbacks = null, $publicRokkaDomain = null)
    {
        $this->rokkaApiKey = $key;
        $this->rokkaOrg = $org;
        if ($publicRokkaDomain) {
            $scheme = parse_url($publicRokkaDomain, PHP_URL_SCHEME);
            if (is_null($scheme)) {
                $this->rokkaDomain = 'https://'.$publicRokkaDomain;
            } else {
                $this->rokkaDomain = $publicRokkaDomain;
            }
        } else {
            $this->rokkaDomain = 'https://'.$org.'.rokka.io';
        }
        if (null === $callbacks) {
            $callbacks = new TemplateHelperDefaultCallbacks();
        }
        $this->callbacks = $callbacks;
    }

    /**
     * Returns the hash of an image.
     * If we don't have an image stored locally, it uploads it to rokka.
     *
     * @param LocalImageAbstract $image
     *
     * @return string
     */
    public function getHashMaybeUpload(LocalImageAbstract $image)
    {
        if ($hash = $image->getRokkaHash()) {
            return $hash;
        }
        if (!$hash = $this->callbacks->getHash($image)) {
            $hash = $this->imageUpload($image);
            $this->callbacks->saveHash($image, $hash);
        }

        return $hash;
    }

    /**
     * Gets the rokka URL for an image
     * Uploads it, if we don't have a hash locally.
     *
     * @param LocalImageAbstract|string|\SplFileInfo $image       The image
     * @param string                                 $stack       The stack name
     * @param string|null                            $format      The image format of the image (jpg, png, webp, ...)
     * @param string|null                            $seo         if you want a different seo string than the default
     * @param string|null                            $seoLanguage Optional language to be used for slugifying (eg. 'de' slugifies 'ö' to 'oe')
     *
     * @return string
     */
    public function getStackUrl(
      $image,
      $stack,
      $format = 'jpg',
      $seo = null,
      $seoLanguage = 'de'
    ) {
        if (null == $image) {
            return '';
        }
        $image = self::getImageObject($image);

        if (!$hash = self::getHashMaybeUpload($image)) {
            return '';
        }
        if (null === $seo) {
            return $this->generateRokkaUrlWithImage($hash, $stack, $format, $image, $seoLanguage);
        }

        return $this->generateRokkaUrl($hash, $stack, $format, $seo, $seoLanguage);
    }

    /**
     * Return the rokka URL for getting a resized image.
     *
     * FIXME: make height optional
     *
     * @param LocalImageAbstract|string|\SplFileInfo $image The image to be resized
     * @param string|int $width The width of the image
     * @param string|int|null $height The height of the image
     * @param string $format The image format of the image (jpg, png, webp, ...)
     *
     * @param string|null $seo
     * @param string $seoLanguage
     * @return string
     */
    public function getResizeUrl($image, $width, $height = null, $format = 'jpg', $seo = null, $seoLanguage = 'de')
    {
        $imageObject = self::getImageObject($image);
        if (null !== $height) {
            $heightString = "-height-$height";
        } else {
            $heightString = '';
        }
        $stack = "dynamic/resize-width-$width$heightString--options-autoformat-true-jpg.transparency.autoformat-true";

        return $this->getStackUrl($imageObject, $stack, $format, $seo, $seoLanguage);
    }

    /**
     * Return the rokka URL for getting a resized image.
     *
     *
     * @param LocalImageAbstract|string|\SplFileInfo $image The image to be resized
     * @param string|int $width The width of the image
     * @param string|int $height The height of the image
     * @param string $format The image format of the image (jpg, png, webp, ...)
     *
     * @param string|null $seo
     * @param string $seoLanguage
     * @return string
     */
    public function getResizeCropUrl($image, $width, $height, $format = 'jpg', $seo = null, $seoLanguage = '')
    {
        $imageObject = self::getImageObject($image);

        $stack = "dynamic/resize-width-$width-height-$height-mode-fill--crop-width-$width-height-$height--options-autoformat-true-jpg.transparency.autoformat-true";

        return $this->getStackUrl($imageObject, $stack, $format, $seo, $seoLanguage);
    }

    public function getSrcAttributes($url, $multipliers = [2])
    {
        $attrs = 'src="'.$url.'"';
        $srcSet = '';
        foreach ($multipliers as $multiply) {
            $urlx2 = UriHelper::addOptionsToUriString($url, 'options-dpr-'.$multiply);
            if ($urlx2 != $url) {
                $srcSet .= $urlx2.' '.$multiply.'x ';
            }
        }
        if ('' != $srcSet) {
            $attrs .= ' srcset="'.trim($srcSet).'"';
        }

        return $attrs;
    }

    public function getBackgroundImageStyle($url, $multipliers = [2])
    {
        $style = "background-image:url('$url');";

        $srcSets = [];
        foreach ($multipliers as $multiply) {
            $urlx2 = UriHelper::addOptionsToUriString($url, 'options-dpr-'.$multiply);
            if ($urlx2 != $url) {
                $srcSets[] = "url('${urlx2}') ${multiply}x";
            }
        }
        if (count($srcSets) > 0) {
            $style .= " background-image: -webkit-image-set(url('$url') 1x, ".implode(', ', $srcSets).');';
        }

        return $style;
    }

    /**
     * Returns the filename of the image without extension.
     *
     * @param LocalImageAbstract $image
     *
     * @return string
     */
    public function getImagename(LocalImageAbstract $image)
    {
        if (null === $image) {
            return '';
        }

        return (string) pathinfo($image->getFilename(), PATHINFO_FILENAME);
    }

    /**
     * Gets the rokka URL for an image hash and stack with optional seo filename in the URL.
     * Doesn't upload it, if we don't have a local hash for it. Use getImageUrl for that.
     *
     * @param string      $hash        The rokka hash
     * @param string      $stack       The stack name
     * @param string      $format      The image format of the image (jpg, png, webp, ...)
     * @param string|null $seo         If you want to use a seo string in the URL
     * @param string      $seoLanguage Optional language to be used for slugifying (eg. 'de' slugifies 'ö' to 'oe')
     *
     * @return string
     */
    public function generateRokkaUrl(
      $hash,
      $stack,
      $format = 'jpg',
      $seo = null,
      $seoLanguage = 'de'
    ) {
        if (null === $format) {
            $format = 'jpg';
        }
        if (!empty($seo)) {
            $slug = self::slugify($seo, $seoLanguage);
            if (!empty($slug)) {
                return $this->rokkaDomain."/$stack/$hash/$slug.$format";
            }
        }

        return $this->rokkaDomain."/$stack/$hash.$format";
    }

    /**
     * @return \Rokka\Client\Image
     */
    public function getRokkaClient()
    {
        $imageClient = Factory::getImageClient($this->rokkaOrg, $this->rokkaApiKey, '');

        return $imageClient;
    }

    /**
     * @param LocalImageAbstract|string|\SplFileInfo $file
     * @param string|null                                   $identifier
     * @param mixed                                   $context
     *
     * @return LocalImageAbstract
     */
    public static function getImageObject($file, $identifier = null, $context = null)
    {
        if ($file instanceof LocalImageAbstract) {
            if (null !== $identifier) {
                $file->setIdentifier($identifier);
            }
            if (null !== $context) {
                $file->setContext($context);
            }

            return $file;
        }
        if ($file instanceof \SplFileInfo) {
            return new FileInfo($file, $identifier, $context);
        } elseif (is_string($file)) {
            return new FileInfo(new \SplFileInfo($file), $identifier, $context);
        }
        // FIXME: return what, if nothing matches? Exception maybe
    }

    /**
     * Create a URL-safe text from $text.
     *
     * @param string $text     Text to slugify
     * @param string $language Optional language to be used for slugifying (eg. 'de' slugifies 'ö' to 'oe')
     *
     * @return string A string that should work in urls. Empty string is only allowed if $emptyText is ''
     */
    public static function slugify($text, $language = 'de')
    {
        \URLify::$maps['specials'] = [
            '.' => '-',
            ',' => '-',
            '@' => '-',
        ];
        $slug = \URLify::filter($text, 60, $language);
        $slug = str_replace(['_'], '-', $slug);
        $slug = preg_replace('/[^0-9a-z-]/', '', $slug);

        return $slug;
    }

    /**
     * Gets the rokka URL for an image hash and stack and uses the $image info for an seo filename in the URL.
     * Doesn't upload it, if we don't have a local hash for it. Use getImageUrl for that.
     * If $image is set, uses the filename for seo-ing the URL.
     *
     * @param string             $hash        The rokka hash
     * @param string             $stack       The stack name
     * @param string             $format      The image format of the image (jpg, png, webp, ...)
     * @param LocalImageAbstract $image       The image
     * @param string             $seoLanguage Optional language to be used for slugifying (eg. 'de' slugifies 'ö' to 'oe')
     *
     * @return string
     */
    protected function generateRokkaUrlWithImage(
        $hash,
        $stack,
        $format = 'jpg',
        LocalImageAbstract $image = null,
        $seoLanguage = 'de'
    ) {
        return $this->generateRokkaUrl($hash, $stack, $format, $this->getImagename($image), $seoLanguage);
    }

    protected function imageUpload(LocalImageAbstract $image)
    {
        //FIXME:: CHeck if an image type we support, otherwise return null
        $imageClient = $this->getRokkaClient();
        $metadata = $this->callbacks->getMetadata($image);
        if (0 === count($metadata)) {
            $metadata = null;
        }
        $answer = $imageClient->uploadSourceImage(
          $image->getContent(),
          $image->getFilename(),
          '',
          $metadata
        );
        $hash = $answer->getSourceImages()[0]->hash;

        return $hash;
    }
}
