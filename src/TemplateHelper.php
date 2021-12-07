<?php

namespace Rokka\Client;

use Rokka\Client\Core\SourceImage;
use Rokka\Client\Core\StackUri;
use Rokka\Client\LocalImage\AbstractLocalImage;
use Rokka\Client\LocalImage\FileInfo;
use Rokka\Client\LocalImage\RokkaHash;
use Rokka\Client\TemplateHelper\AbstractCallbacks;
use Rokka\Client\TemplateHelper\DefaultCallbacks;

/**
 * This class provides lots of helper functionality usually used in templates.
 *
 * It can also manage looking up hashes and uploading images to rokka, see the docs for details.
 *
 * @since 1.3.0
 */
class TemplateHelper
{
    /**
     * @var string
     */
    private $rokkaApiKey;

    /**
     * @var string
     */
    private $rokkaOrg;

    /**
     * @var string
     */
    private $rokkaDomain;

    /**
     * @var AbstractCallbacks
     */
    private $callbacks;

    /**
     * @var string|array
     */
    private $rokkaClientOptions;

    /**
     * @var \Rokka\Client\Image
     */
    private $imageClient;

    /**
     * @since 1.3.0
     *
     * @param string                 $organization      Organization name
     * @param string                 $apiKey            API key
     * @param AbstractCallbacks|null $callbacks         Optional callbacks for read and write of hashes
     * @param string|null            $publicRokkaDomain Optional public rokka URL, if different from the standard one (org.render.rokka.io)
     * @param string|array|null      $options           Optional options like api_base_url or proxy
     */
    public function __construct(
        $organization,
        $apiKey,
        AbstractCallbacks $callbacks = null,
        $publicRokkaDomain = null,
        $options = []
    ) {
        $this->rokkaApiKey = $apiKey;
        $this->rokkaOrg = $organization;

        if (null === $options) {
            $options = [];
        }
        $this->rokkaClientOptions = $options;

        if ($publicRokkaDomain) {
            $scheme = parse_url($publicRokkaDomain, \PHP_URL_SCHEME);
            if (null === $scheme) {
                $this->rokkaDomain = 'https://'.$publicRokkaDomain;
            } else {
                $this->rokkaDomain = $publicRokkaDomain;
            }
        } else {
            $this->rokkaDomain = 'https://'.$organization.'.rokka.io';
        }
        if (null === $callbacks) {
            $callbacks = new DefaultCallbacks();
        }
        $this->callbacks = $callbacks;
    }

    /**
     * Returns the hash of an image.
     * If we don't have an image stored locally, it uploads it to rokka.
     *
     * @since 1.3.0
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \RuntimeException
     *
     * @return string|null
     */
    public function getHashMaybeUpload(AbstractLocalImage $image)
    {
        if ($hash = $image->getRokkaHash()) {
            return $hash;
        }
        if (!$hash = $this->callbacks->getHash($image)) {
            if (!$this->isImage($image)) {
                return null;
            }
            $sourceImage = $this->imageUpload($image);
            if (null !== $sourceImage) {
                $hash = $this->callbacks->saveHash($image, $sourceImage);
            }
        }

        return $hash;
    }

    /**
     * Gets the rokka URL for an image
     * Uploads it, if we don't have a hash locally.
     *
     * @since 1.3.0
     *
     * @param AbstractLocalImage|string|\SplFileInfo $image       The image
     * @param string                                 $stack       The stack name
     * @param string|null                            $format      The image format of the image (jpg, png, webp, ...)
     * @param string|null                            $seo         if you want a different seo string than the default
     * @param string|null                            $seoLanguage Optional language to be used for slugifying (eg. 'de' slugifies 'ö' to 'oe')
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \RuntimeException
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
        $image = $this->getImageObject($image);

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
     * @since 1.3.0
     *
     * @param AbstractLocalImage|string|\SplFileInfo $image       The image to be resized
     * @param string|int                             $width       The width of the image
     * @param string|int|null                        $height      The height of the image
     * @param string                                 $format      The image format of the image (jpg, png, webp, ...)
     * @param string|null                            $seo
     * @param string                                 $seoLanguage
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getResizeUrl($image, $width, $height = null, $format = 'jpg', $seo = null, $seoLanguage = 'de')
    {
        $imageObject = $this->getImageObject($image);
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
     * @since 1.3.0
     *
     * @param AbstractLocalImage|string|\SplFileInfo $image       The image to be resized
     * @param string|int                             $width       The width of the image
     * @param string|int                             $height      The height of the image
     * @param string                                 $format      The image format of the image (jpg, png, webp, ...)
     * @param string|null                            $seo
     * @param string                                 $seoLanguage
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getResizeCropUrl($image, $width, $height, $format = 'jpg', $seo = null, $seoLanguage = '')
    {
        $imageObject = $this->getImageObject($image);

        $stack = "dynamic/resize-width-$width-height-$height-mode-fill--crop-width-$width-height-$height--options-autoformat-true-jpg.transparency.autoformat-true";

        return $this->getStackUrl($imageObject, $stack, $format, $seo, $seoLanguage);
    }

    /**
     * Return the rokka URL for getting the image in it's original size.
     *
     * @since 1.3.0
     *
     * @param AbstractLocalImage|string|\SplFileInfo $image       The image to be resized
     * @param string                                 $format      The image format of the image (jpg, png, webp, ...)
     * @param string|null                            $seo
     * @param string                                 $seoLanguage
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getOriginalSizeUrl($image, $format = 'jpg', $seo = null, $seoLanguage = '')
    {
        $imageObject = $this->getImageObject($image);

        $stack = 'dynamic/noop--options-autoformat-true-jpg.transparency.autoformat-true';

        return $this->getStackUrl($imageObject, $stack, $format, $seo, $seoLanguage);
    }

    /**
     * Returns a src and srcset attibrute (as one string) with the correct rokka render urls
     * for responsive images.
     * To be used directly in your HTML templates.
     *
     * @since 1.3.0
     *
     * @param string $url           The render URL of the "non-retina" image
     * @param array  $sizes         For which sizes srcset links should be generated, works with 'x' or 'w' style
     * @param bool   $setWidthInUrl If false, don't set the width as stack operation option, we provide it in $custom, usually as parameter
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public static function getSrcAttributes($url, $sizes = ['2x'], $setWidthInUrl = true)
    {
        $attrs = 'src="'.$url.'"';
        $srcSets = self::getSrcSets($url, $sizes, $setWidthInUrl);
        if (\count($srcSets) > 0) {
            $attrs .= ' srcset="'.implode(', ', ($srcSets)).'"';
        }

        return $attrs;
    }

    /**
     * Returns a srcset compatible url string with the correct rokka render urls
     * for responsive images.
     *
     * @since 1.11.0
     *
     * @param string $url           The render URL of the "non-retina" image
     * @param array  $sizes         For which sizes srcset links should be generated, works with 'x' or 'w' style
     * @param bool   $setWidthInUrl If false, don't set the width as stack operation option, we provide it in $custom, usually as parameter
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public static function getSrcSetUrl($url, $sizes = ['1x', '2x'], $setWidthInUrl = true)
    {
        $srcSets = self::getSrcSets($url, $sizes, $setWidthInUrl);

        return implode(', ', ($srcSets));
    }

    /**
     * Returns a background-image:url defintions (as one string) with the correct rokka render urls
     * for responsive images.
     * To be used directly in your CSS templates or HTML tags.
     *
     * @since 1.3.0
     *
     * @param string $url   The render URL of the "non-retina" image
     * @param array  $sizes For which sizes srcset links should be generated, works with 'x' or 'w' style
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public static function getBackgroundImageStyle($url, array $sizes = ['2x'])
    {
        $style = "background-image:url('$url');";

        $srcSets = [];
        foreach ($sizes as $size => $custom) {
            if (\is_int($size)) {
                $size = $custom;
                $custom = null;
            }
            $urlx2 = UriHelper::getSrcSetUrlString($url, $size, $custom);
            if ($urlx2 != $url) {
                $srcSets[] = "url('${urlx2}') ${size}";
            }
        }
        if (\count($srcSets) > 0) {
            $style .= " background-image: -webkit-image-set(url('$url') 1x, ".implode(', ', $srcSets).');';
        }

        return $style;
    }

    /**
     * Returns the filename of the image without extension.
     *
     * @since 1.3.0
     *
     * @return string
     */
    public function getImagename(AbstractLocalImage $image = null)
    {
        if (null === $image) {
            return '';
        }
        if (null === $image->getFilename()) {
            return '';
        }

        return pathinfo($image->getFilename(), \PATHINFO_FILENAME);
    }

    /**
     * Gets the rokka URL for an image hash and stack with optional seo filename in the URL.
     * Doesn't upload it, if we don't have a local hash for it. Use getStackUrl for that.
     *
     * @since 1.3.0
     * @see TemplateHelper::getStackUrl()
     *
     * @param string          $hash        The rokka hash
     * @param string|StackUri $stack       The stack name or a StackUrl object
     * @param string|null     $format      The image format of the image (jpg, png, webp, ...)
     * @param string|null     $seo         If you want to use a seo string in the URL
     * @param string|null     $seoLanguage Optional language to be used for slugifying (eg. 'de' slugifies 'ö' to 'oe')
     *
     * @throws \RuntimeException
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
     * Gets the rokka image client used by this class.
     *
     * @since 1.3.0
     *
     * @throws \RuntimeException
     *
     * @return \Rokka\Client\Image
     */
    public function getRokkaClient()
    {
        if (null === $this->imageClient) {
            $this->imageClient = Factory::getImageClient($this->rokkaOrg, $this->rokkaApiKey, $this->rokkaClientOptions);
        }

        return $this->imageClient;
    }

    /**
     * Create a URL-safe text from $text.
     *
     * @since 1.3.0
     *
     * @param string $text     Text to slugify
     * @param string $language Optional language to be used for slugifying (eg. 'de' slugifies 'ö' to 'oe')
     *
     * @throws \RuntimeException
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
        $slug = \URLify::filter($text, 60, $language, true, false);
        $slug = str_replace(['_'], '-', $slug);
        $slug = preg_replace('/[^0-9a-z-]/', '', $slug);

        if (null === $slug) {
            throw new \RuntimeException('An error eccored when generating the slug for '.$text);
        }

        return $slug;
    }

    /**
     * Returns a LocalImage object depending on the input.
     *
     * If input is
     * - LocalImageAbstract: returns that, sets $identidier and $context, if set
     * - SplFileInfo: returns \Rokka\Client\LocalImage\FileInfo
     * - string with hash pattern (/^[0-9a-f]{6,40}$/): returns \Rokka\Client\LocalImage\RokkaHash
     * - other strings: returns \Rokka\Client\LocalImage\FileInfo with $input as the path to the image
     *
     * @since 1.3.1
     *
     * @param AbstractLocalImage|string|\SplFileInfo $input
     * @param string|null                            $identifier
     * @param mixed                                  $context
     *
     * @throws \RuntimeException
     *
     * @return AbstractLocalImage
     */
    public function getImageObject($input, $identifier = null, $context = null)
    {
        if ($input instanceof AbstractLocalImage) {
            if (null !== $identifier) {
                $input->setIdentifier($identifier);
            }
            if (null !== $context) {
                $input->setContext($context);
            }

            return $input;
        }
        if ($input instanceof \SplFileInfo) {
            return new FileInfo($input, $identifier, $context);
        }
        if (\is_string($input)) {
            if (preg_match('/^[0-9a-f]{6,40}$/', $input)) {
                return new RokkaHash($input, $identifier, $context, $this);
            }

            return new FileInfo(new \SplFileInfo($input), $identifier, $context);
        }

        // we can't trust callers to only provide $input in one of the supported types
        // @phpstan-ignore-next-line
        $inputType = \is_object($input) ? \get_class($input) : \gettype($input);

        throw new \RuntimeException('Can not create a source image from input of type '.$inputType);
    }

    /**
     * @param string $url           The render URL of the "non-retina" image
     * @param array  $sizes         For which sizes srcset links should be generated, works with 'x' or 'w' style
     * @param bool   $setWidthInUrl If false, don't set the width as stack operation option, we provide it in $custom, usually as parameter
     *
     * @throws \RuntimeException
     */
    private static function getSrcSets($url, $sizes, $setWidthInUrl): array
    {
        $srcSets = [];
        foreach ($sizes as $size => $custom) {
            if (\is_int($size)) {
                if (\is_int($custom)) {
                    $size = $custom.'x';
                } else {
                    $size = $custom;
                }

                $custom = null;
            }
            $urlx2 = UriHelper::getSrcSetUrlString($url, $size, $custom, $setWidthInUrl);
            if ($urlx2 != $url) {
                $srcSets[] = "${urlx2} ${size}";
            }
        }

        return $srcSets;
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
     * @param AbstractLocalImage $image       The image
     * @param string|null        $seoLanguage Optional language to be used for slugifying (eg. 'de' slugifies 'ö' to 'oe')
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    private function generateRokkaUrlWithImage(
        $hash,
        $stack,
        $format = 'jpg',
        AbstractLocalImage $image = null,
        $seoLanguage = 'de'
    ) {
        return $this->generateRokkaUrl($hash, $stack, $format, $this->getImagename($image), $seoLanguage);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \RuntimeException
     *
     * @return SourceImage|null
     */
    private function imageUpload(AbstractLocalImage $image)
    {
        $imageClient = $this->getRokkaClient();
        $metadata = $this->callbacks->getMetadata($image);
        if (0 === \count($metadata)) {
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
            if (\count($sourceImages) > 0) {
                return $sourceImages[0];
            }
        }

        return null;
    }

    private function getMimeType(AbstractLocalImage $image): string
    {
        $mimeType = 'application/not-supported';
        $realpath = $image->getRealpath();
        if (\is_string($realpath)) {
            $resource = finfo_open(\FILEINFO_MIME_TYPE);
            \assert(\is_resource($resource));
            $mimeType = finfo_file($resource, $realpath);
        } else {
            $content = $image->getContent();
            if (null !== $content) {
                $resource = finfo_open(\FILEINFO_MIME_TYPE);
                \assert(\is_resource($resource));
                $mimeType = finfo_buffer($resource, $content);
            }
        }
        \assert(\is_string($mimeType));

        if ('text/html' == $mimeType || 'text/plain' == $mimeType) {
            if ($this->isSvg($image)) {
                $mimeType = 'image/svg+xml';
            }
        }

        return $mimeType;
    }

    private function isImage(AbstractLocalImage $image): bool
    {
        $mimeType = $this->getMimeType($image);
        if ('image/' == substr($mimeType, 0, 6)) {
            return true;
        }

        if ('application/pdf' == $mimeType) {
            return true;
        }

        if ('video/mp4' == $mimeType) {
            return true;
        }

        return false;
    }

    /**
     * Checks, if a file is svg (needed when xml declaration is missing).
     */
    private function isSvg(AbstractLocalImage $image): bool
    {
        $dom = new \DOMDocument();
        $content = $image->getContent();
        if (null === $content) {
            return false;
        }
        if ($dom->loadXML($content)) {
            $root = $dom->childNodes->item(0);
            if (null === $root) {
                return false;
            }
            if ('svg' == $root->localName && 'http://www.w3.org/2000/svg' == $root->namespaceURI) {
                return true;
            }
        }

        return false;
    }
}
