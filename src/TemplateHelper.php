<?php

namespace Rokka\Client;

use Rokka\Client\Base as BaseClient;
use Rokka\Client\Core\SourceImage;
use Rokka\Client\Core\StackUri;
use Rokka\Client\LocalImage\FileInfo;
use Rokka\Client\LocalImage\LocalImageAbstract;
use Rokka\Client\LocalImage\RokkaHash;

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
     * @var string
     */
    private $rokkaApiHost;

    public function __construct(
        $organization,
        $apiKey,
        TemplateHelperCallbacksAbstract $callbacks = null,
        $publicRokkaDomain = null,
        $rokkaApiHost = BaseClient::DEFAULT_API_BASE_URL
    ) {
        $this->rokkaApiKey = $apiKey;
        $this->rokkaOrg = $organization;
        $this->rokkaApiHost = $rokkaApiHost;
        if ($publicRokkaDomain) {
            $scheme = parse_url($publicRokkaDomain, PHP_URL_SCHEME);
            if (is_null($scheme)) {
                $this->rokkaDomain = 'https://'.$publicRokkaDomain;
            } else {
                $this->rokkaDomain = $publicRokkaDomain;
            }
        } else {
            $this->rokkaDomain = 'https://'.$organization.'.rokka.io';
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
     * @return string|null
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getHashMaybeUpload(LocalImageAbstract $image)
    {
        if ($hash = $image->getRokkaHash()) {
            return $hash;
        }
        if (!$hash = $this->callbacks->getHash($image)) {
            if (!$this->isImage($image)) {
                return null;
            }
            $sourceImage = $this->imageUpload($image);
            if (!is_null($sourceImage)) {
                $hash = $this->callbacks->saveHash($image, $sourceImage);
            }
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
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @param LocalImageAbstract|string|\SplFileInfo $image       The image to be resized
     * @param string|int                             $width       The width of the image
     * @param string|int|null                        $height      The height of the image
     * @param string                                 $format      The image format of the image (jpg, png, webp, ...)
     * @param string|null                            $seo
     * @param string                                 $seoLanguage
     *
     * @return string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * Return the rokka URL for getting a resized and cropped image.
     *
     *
     * @param LocalImageAbstract|string|\SplFileInfo $image       The image to be resized
     * @param string|int                             $width       The width of the image
     * @param string|int                             $height      The height of the image
     * @param string                                 $format      The image format of the image (jpg, png, webp, ...)
     * @param string|null                            $seo
     * @param string                                 $seoLanguage
     *
     * @return string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getResizeCropUrl($image, $width, $height, $format = 'jpg', $seo = null, $seoLanguage = '')
    {
        $imageObject = self::getImageObject($image);

        $stack = "dynamic/resize-width-$width-height-$height-mode-fill--crop-width-$width-height-$height--options-autoformat-true-jpg.transparency.autoformat-true";

        return $this->getStackUrl($imageObject, $stack, $format, $seo, $seoLanguage);
    }

    /**
     * Return the rokka URL for getting the image in it's original size.
     *
     *
     * @param LocalImageAbstract|string|\SplFileInfo $image       The image to be resized
     * @param string                                 $format      The image format of the image (jpg, png, webp, ...)
     * @param string|null                            $seo
     * @param string                                 $seoLanguage
     *
     * @return string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getOriginalSizeUrl($image, $format = 'jpg', $seo = null, $seoLanguage = '')
    {
        $imageObject = self::getImageObject($image);

        $stack = 'dynamic/noop--options-autoformat-true-jpg.transparency.autoformat-true';

        return $this->getStackUrl($imageObject, $stack, $format, $seo, $seoLanguage);
    }

    /**
     * @param string $url
     * @param array  $sizes
     *
     * @return string
     */
    public function getSrcAttributes($url, $sizes = ['2x'])
    {
        $attrs = 'src="'.$url.'"';
        $srcSets = [];
        foreach ($sizes as $size => $custom) {
            if (is_int($size)) {
                if (is_int($custom)) {
                    $size = $custom.'x';
                } else {
                    $size = $custom;
                }

                $custom = null;
            }
            $urlx2 = UriHelper::getSrcSetUrlString($url, $size, $custom);
            if ($urlx2 != $url) {
                $srcSets[] = "${urlx2} ${size}";
            }
        }
        if (count($srcSets) > 0) {
            $attrs .= ' srcset="'.implode(', ', ($srcSets)).'"';
        }

        return $attrs;
    }

    /**
     * @param string $url
     * @param array  $sizes
     *
     * @return string
     */
    public function getBackgroundImageStyle($url, array $sizes = ['2x'])
    {
        $style = "background-image:url('$url');";

        $srcSets = [];
        foreach ($sizes as $size => $custom) {
            if (is_int($size)) {
                $size = $custom;
                $custom = null;
            }
            $urlx2 = UriHelper::getSrcSetUrlString($url, $size, $custom);
            if ($urlx2 != $url) {
                $srcSets[] = "url('${urlx2}') ${size}";
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
     * @param LocalImageAbstract|null $image
     *
     * @return string
     */
    public function getImagename(LocalImageAbstract $image = null)
    {
        if (null === $image) {
            return '';
        }

        return (string) pathinfo($image->getFilename(), PATHINFO_FILENAME);
    }

    /**
     * Gets the rokka URL for an image hash and stack with optional seo filename in the URL.
     * Doesn't upload it, if we don't have a local hash for it. Use getStackUrl for that.
     *
     * @see TemplateHelper::getStackUrl()
     *
     * @param string          $hash        The rokka hash
     * @param string|StackUri $stack       The stack name or a StackUrl object
     * @param string|null     $format      The image format of the image (jpg, png, webp, ...)
     * @param string|null     $seo         If you want to use a seo string in the URL
     * @param string|null     $seoLanguage Optional language to be used for slugifying (eg. 'de' slugifies 'ö' to 'oe')
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
        $slug = null;
        if (!empty($seo) && null !== $seo) {
            if (null === $seoLanguage) {
                $seoLanguage = 'de';
            }
            $slug = self::slugify($seo, $seoLanguage);
        }
        $path = UriHelper::composeUri(['stack' => $stack, 'hash' => $hash, 'format' => $format, 'filename' => $slug]);

        return $this->rokkaDomain.$path->getPath();
    }

    /**
     * @return \Rokka\Client\Image
     */
    public function getRokkaClient()
    {
        $imageClient = Factory::getImageClient($this->rokkaOrg, $this->rokkaApiKey, '', $this->rokkaApiHost);

        return $imageClient;
    }

    /**
     * @param LocalImageAbstract|string|\SplFileInfo $file
     * @param string|null                            $identifier
     * @param mixed                                  $context
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
            if (preg_match('/^[0-9a-f]{6,40}$/', $file)) {
                return new RokkaHash($file);
            }
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
     * Doesn't upload it, if we don't have a local hash for it. Use getStackUrl() for that.
     * If $image is set, uses the filename for seo-ing the URL.
     *
     * @see TemplateHelper::getStackUrl()
     *
     * @param string             $hash        The rokka hash
     * @param string             $stack       The stack name
     * @param string|null        $format      The image format of the image (jpg, png, webp, ...)
     * @param LocalImageAbstract $image       The image
     * @param string|null        $seoLanguage Optional language to be used for slugifying (eg. 'de' slugifies 'ö' to 'oe')
     *
     * @return string
     */
    private function generateRokkaUrlWithImage(
        $hash,
        $stack,
        $format = 'jpg',
        LocalImageAbstract $image = null,
        $seoLanguage = 'de'
    ) {
        return $this->generateRokkaUrl($hash, $stack, $format, $this->getImagename($image), $seoLanguage);
    }

    /**
     * @param LocalImageAbstract $image
     *
     * @return null|SourceImage
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function imageUpload(LocalImageAbstract $image)
    {
        $imageClient = $this->getRokkaClient();
        $metadata = $this->callbacks->getMetadata($image);
        if (0 === count($metadata)) {
            $metadata = null;
        }
        $content = $image->getContent();
        if (null !== $content) {
            $filename = $image->getFilename();
            if (null === $filename) {
                $filename = 'unknown';
            }
            $answer = $imageClient->uploadSourceImage(
                $content,
                $filename,
                '',
                $metadata
            );
            $sourceImages = $answer->getSourceImages();
            if (count($sourceImages) > 0) {
                return $sourceImages[0];
            }
        }

        return null;
    }

    /**
     * @param LocalImageAbstract $image
     *
     * @return string
     */
    private function getMimeType(LocalImageAbstract $image)
    {
        if ($realpath = $image->getRealpath()) {
            $mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $realpath);
        } else {
            $mimeType = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $image->getContent());
        }

        if ('text/html' == $mimeType || 'text/plain' == $mimeType) {
            if ($this->isSvg($image)) {
                $mimeType = 'image/svg+xml';
            }
        }

        return $mimeType;
    }

    private function isImage(LocalImageAbstract $image)
    {
        $mimeType = $this->getMimeType($image);
        if ('image/' == substr($mimeType, 0, 6)) {
            return true;
        }

        if ('application/pdf' == $mimeType) {
            return true;
        }

        return false;
    }

    /**
     * Checks, if a file is svg (needed when xml declaration is missing).
     *
     * @param LocalImageAbstract $image
     *
     * @return bool
     */
    private function isSvg(LocalImageAbstract $image)
    {
        $dom = new \DOMDocument();
        if (@$dom->loadXML($image->getContent())) {
            $root = $dom->childNodes->item(0);
            if ('svg' == $root->localName && 'http://www.w3.org/2000/svg' == $root->namespaceURI) {
                return true;
            }
        }

        return false;
    }
}
