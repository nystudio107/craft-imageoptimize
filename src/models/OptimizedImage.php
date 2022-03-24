<?php
/**
 * Image Optimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\models;

use Craft;
use craft\base\Model;
use craft\helpers\Html;
use craft\helpers\Template;
use craft\validators\ArrayValidator;
use nystudio107\imageoptimize\helpers\Color as ColorHelper;
use nystudio107\imageoptimize\helpers\UrlHelper;
use nystudio107\imageoptimize\ImageOptimize;
use function strlen;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.2.0
 */
class OptimizedImage extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var array An array of optimized image variant URLs
     */
    public array $optimizedImageUrls = [];

    /**
     * @var array An array of optimized .webp image variant URLs
     */
    public array $optimizedWebPImageUrls = [];

    /**
     * @var array An array of the widths of the optimized image variants
     */
    public array $variantSourceWidths = [];

    /**
     * @var array An array of the heights of the optimized image variants
     */
    public array $variantHeights = [];

    /**
     * @var array An array of the x,y image focal point coords, ranging from 0.0 to 1.0
     */
    public array $focalPoint;

    /**
     * @var int The width of the original source image
     */
    public int $originalImageWidth = 0;

    /**
     * @var int The height of the original source image
     */
    public int $originalImageHeight = 0;

    /**
     * @var string The base64 encoded placeholder LQIP image
     */
    public string $placeholder = '';

    /**
     * @var string The base64 encoded placeholder LQIP SVG image
     */
    public string $placeholderSvg = '';

    /**
     * @var array An array the 5 most dominant colors in the image
     */
    public array $colorPalette = [];

    /**
     * @var int The overall lightness of the image, from 0..100
     */
    public int $lightness;

    /**
     * @var int The width of the placeholder image
     */
    public int $placeholderWidth;

    /**
     * @var int The height of the placeholder image
     */
    public int $placeholderHeight;

    /**
     * @var array An array of errors logged when generating the image transforms
     */
    public array $stickyErrors = [];

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            ['optimizedImageUrls', ArrayValidator::class],
            ['optimizedWebPImageUrls', ArrayValidator::class],
            ['variantSourceWidths', ArrayValidator::class],
            ['variantHeights', ArrayValidator::class],
            ['focalPoint', 'safe'],
            ['originalImageWidth', 'integer'],
            ['originalImageHeight', 'integer'],
            ['placeholder', 'string'],
            ['placeholderSvg', 'string'],
            ['colorPalette', ArrayValidator::class],
            ['placeholderWidth', 'integer'],
            ['placeholderHeight', 'integer'],
            ['stickyErrors', ArrayValidator::class],
        ];
    }

    /**
     * Return the first image variant URL or the specific one passed in via
     * $width
     *
     * @param int $width
     *
     * @return string
     */
    public function src(int $width = 0): string
    {
        if (empty($width)) {
            return Template::raw(reset($this->optimizedImageUrls));
        }

        return Template::raw($this->optimizedImageUrls[$width] ?? '');
    }

    /**
     * Getter for CraftQL
     *
     * @param int $width
     *
     * @return string
     */
    public function getSrc(int $width = 0): string
    {
        return $this->src($width);
    }

    /**
     * Return a string of image URLs and their sizes
     *
     * @param bool $dpr Whether to generate 1x, 2x srcsets vs the normal XXXw
     *                  srcsets
     *
     * @return string
     */
    public function srcset(bool $dpr = false): string
    {
        return Template::raw($this->getSrcsetFromArray($this->optimizedImageUrls, $dpr));
    }

    /**
     * Getter for CraftQL
     *
     * @param bool $dpr
     *
     * @return string
     */
    public function getSrcset(bool $dpr = false): string
    {
        return $this->srcset($dpr);
    }

    /**
     * Return a string of image URLs and their sizes that match $width
     *
     * @param int $width
     * @param bool $dpr Whether to generate 1x, 2x srcsets vs the normal XXXw
     *                  srcsets
     *
     * @return string
     */
    public function srcsetWidth(int $width, bool $dpr = false): string
    {
        $subset = $this->getSrcsetSubsetArray($this->optimizedImageUrls, $width, 'width');

        return Template::raw($this->getSrcsetFromArray($subset, $dpr));
    }

    /**
     * Return a string of image URLs and their sizes that are at least $width
     * or larger
     *
     * @param int $width
     * @param bool $dpr Whether to generate 1x, 2x srcsets vs the normal XXXw
     *                  srcsets
     *
     * @return string
     */
    public function srcsetMinWidth(int $width, bool $dpr = false): string
    {
        $subset = $this->getSrcsetSubsetArray($this->optimizedImageUrls, $width, 'minwidth');

        return Template::raw($this->getSrcsetFromArray($subset, $dpr));
    }

    /**
     * Return a string of image URLs and their sizes that are $width or smaller
     *
     * @param int $width
     * @param bool $dpr Whether to generate 1x, 2x srcsets vs the normal XXXw
     *                  srcsets
     *
     * @return string
     */
    public function srcsetMaxWidth(int $width, bool $dpr = false): string
    {
        $subset = $this->getSrcsetSubsetArray($this->optimizedImageUrls, $width, 'maxwidth');

        return Template::raw($this->getSrcsetFromArray($subset, $dpr));
    }

    /**
     * Return the first webp image variant URL or the specific one passed in
     * via $width
     *
     * @param int $width
     *
     * @return string
     */
    public function srcWebp(int $width = 0): string
    {
        if (empty($width)) {
            return Template::raw(reset($this->optimizedWebPImageUrls));
        }

        return Template::raw($this->optimizedWebPImageUrls[$width] ?? '');
    }

    /**
     * Getter for CraftQL
     *
     * @param int $width
     *
     * @return string
     */
    public function getSrcWebp(int $width = 0): string
    {
        return $this->srcWebp($width);
    }

    /**
     * Return a string of webp image URLs and their sizes
     *
     * @param bool $dpr Whether to generate 1x, 2x srcsets vs the normal XXXw
     *                  srcsets
     *
     * @return string
     */
    public function srcsetWebp(bool $dpr = false): string
    {
        return Template::raw($this->getSrcsetFromArray($this->optimizedWebPImageUrls, $dpr));
    }

    /**
     * Getter for CraftQL
     *
     * @param bool $dpr
     *
     * @return string
     */
    public function getSrcsetWebp(bool $dpr = false): string
    {
        return $this->srcsetWebp($dpr);
    }

    /**
     * Return a string of webp image URLs and their sizes that match $width
     *
     * @param int $width
     * @param bool $dpr Whether to generate 1x, 2x srcsets vs the normal XXXw
     *                  srcsets
     *
     * @return string
     */
    public function srcsetWidthWebp(int $width, bool $dpr = false): string
    {
        $subset = $this->getSrcsetSubsetArray($this->optimizedWebPImageUrls, $width, 'width');

        return Template::raw($this->getSrcsetFromArray($subset, $dpr));
    }

    /**
     * Return a string of webp image URLs and their sizes that are at least
     * $width or larger
     *
     * @param int $width
     * @param bool $dpr Whether to generate 1x, 2x srcsets vs the normal XXXw
     *                  srcsets
     *
     * @return string
     */
    public function srcsetMinWidthWebp(int $width, bool $dpr = false): string
    {
        $subset = $this->getSrcsetSubsetArray($this->optimizedWebPImageUrls, $width, 'minwidth');

        return Template::raw($this->getSrcsetFromArray($subset, $dpr));
    }

    /**
     * Return a string of webp image URLs and their sizes that are $width or
     * smaller
     *
     * @param int $width
     * @param bool $dpr Whether to generate 1x, 2x srcsets vs the normal XXXw
     *                  srcsets
     *
     * @return string
     */
    public function srcsetMaxWidthWebp(int $width, bool $dpr = false): string
    {
        $subset = $this->getSrcsetSubsetArray($this->optimizedWebPImageUrls, $width, 'maxwidth');

        return Template::raw($this->getSrcsetFromArray($subset, $dpr));
    }

    /**
     * Work around issues with `<img srcset>` returning sizes larger than are
     * available as per:
     * https://medium.com/@MRWwebDesign/responsive-images-the-sizes-attribute-and-unexpected-image-sizes-882a2eadb6db
     *
     * @return int
     */
    public function maxSrcsetWidth(): int
    {
        $result = 0;
        if (!empty($this->optimizedImageUrls)) {
            $tempArray = $this->optimizedImageUrls;
            ksort($tempArray, SORT_NUMERIC);

            $keys = array_keys($tempArray);
            $result = end($keys);
        }

        return $result;
    }

    /**
     * Getter for CraftQL
     *
     * @return int
     */
    public function getMaxSrcsetWidth(): int
    {
        return $this->maxSrcsetWidth();
    }

    /**
     * Return the colors as an array of RGB colors
     */
    public function colorPaletteRgb(): array
    {
        $colors = [];

        foreach ($this->colorPalette as $color) {
            if (!empty($color)) {
                $colors[] = ColorHelper::HTMLToRGB($color);
            }
        }

        return $colors;
    }

    /**
     * Generate a complete <link rel="preload"> tag for this OptimizedImages model
     * ref: https://web.dev/preload-responsive-images/#imagesrcset-and-imagesizes
     *
     * @param array $linkAttrs
     *
     * @return string
     */
    public function linkPreloadTag(array $linkAttrs = []): string
    {
        // Any web browser that supports link rel="preload" as="image" also supports webp, so prefer that
        $srcset = $this->optimizedImageUrls;
        if (!empty($this->optimizedWebPImageUrls)) {
            $srcset = $this->optimizedWebPImageUrls;
        }
        // Merge the passed in options with the tag attributes
        $attrs = array_merge([
            'rel' => 'preload',
            'as' => 'image',
            'href' => reset($srcset),
            'imagesrcset' => $this->getSrcsetFromArray($srcset),
            'imagesizes' => '100vw',
        ],
            $linkAttrs
        );
        // Remove any empty attributes
        $attrs = array_filter($attrs);
        // Render the tag
        $tag = Html::tag('link', '', $attrs);

        return Template::raw($tag);
    }

    /**
     * Generate a complete <img> tag for this OptimizedImages model
     *
     * @param string $loading 'eager', 'lazy', 'lazySizes', 'lazySizesFallback'
     * @param string $placeHolder 'box', 'color', 'image', 'silhouette'
     * @param array $imgAttrs
     *
     * @return string
     */
    public function imgTag(string $loading = 'eager', string $placeHolder = 'box', array $imgAttrs = []): string
    {
        // Merge the passed in options with the tag attributes
        $attrs = array_merge([
            'class' => '',
            'style' => '',
            'width' => $this->placeholderWidth,
            'height' => $this->placeholderHeight,
            'src' => reset($this->optimizedImageUrls),
            'srcset' => $this->getSrcsetFromArray($this->optimizedImageUrls),
            'sizes' => '100vw',
            'loading' => '',
        ],
            $imgAttrs
        );
        // Handle lazy loading
        if ($loading !== 'eager') {
            $attrs = $this->swapLazyLoadAttrs($loading, $placeHolder, $attrs);
        }
        // Remove any empty attributes
        $attrs = array_filter($attrs);
        // Render the tag
        $tag = Html::tag('img', '', $attrs);

        return Template::raw($tag);
    }

    /**
     * Generate a complete <picture> tag for this OptimizedImages model
     *
     * @param string $loading 'eager', 'lazy', 'lazySizes', 'lazySizesFallback'
     * @param string $placeHolder 'box', 'color', 'image', 'silhouette'
     * @param array $pictureAttrs
     * @param array $srcsetAttrs
     * @param array $imgAttrs
     *
     * @return string
     */
    public function pictureTag(string $loading = 'eager', string $placeHolder = 'box', array $pictureAttrs = [], array $srcsetAttrs = [], array $imgAttrs = []): string
    {
        $content = '';
        // Handle the webp srcset
        if (!empty($this->optimizedWebPImageUrls)) {
            // Merge the passed in options with the tag attributes
            $attrs = array_merge([
                'srcset' => $this->getSrcsetFromArray($this->optimizedWebPImageUrls),
                'type' => 'image/webp',
                'sizes' => '100vw',
            ],
                $srcsetAttrs
            );
            // Handle lazy loading
            if ($loading !== 'eager') {
                $attrs = $this->swapLazyLoadAttrs($loading, $placeHolder, $attrs);
            }
            // Remove any empty attributes
            $attrs = array_filter($attrs);
            // Render the tag
            $content .= Html::tag('source', '', $attrs);
        }
        // Handle the regular srcset
        if (!empty($this->optimizedImageUrls)) {
            // Merge the passed in options with the tag attributes
            $attrs = array_merge([
                'srcset' => $this->getSrcsetFromArray($this->optimizedImageUrls),
                'sizes' => '100vw',
            ],
                $srcsetAttrs
            );
            // Handle lazy loading
            if ($loading !== 'eager') {
                $attrs = $this->swapLazyLoadAttrs($loading, $placeHolder, $attrs);
            }
            // Remove any empty attributes
            $attrs = array_filter($attrs);
            // Render the tag
            $content .= Html::tag('source', '', $attrs);
        }
        // Handle the img tag
        /** @noinspection SuspiciousAssignmentsInspection */
        $attrs = array_merge([
            'class' => '',
            'style' => '',
            'width' => $this->placeholderWidth,
            'height' => $this->placeholderHeight,
            'src' => reset($this->optimizedImageUrls),
            'loading' => '',
        ],
            $imgAttrs
        );
        // Handle lazy loading
        if ($loading !== 'eager') {
            $attrs = $this->swapLazyLoadAttrs($loading, $placeHolder, $attrs);
        }
        // Remove any empty attributes
        $attrs = array_filter($attrs);
        // Render the tag
        $content .= Html::tag('img', '', $attrs);
        // Merge the passed in options with the tag attributes
        $attrs = array_merge([
        ],
            $pictureAttrs
        );
        // Remove any empty attributes
        $attrs = array_filter($attrs);
        // Render the tag
        $tag = Html::tag('picture', $content, $attrs);

        return Template::raw($tag);
    }

    /**
     * Return a base64-encoded placeholder image
     *
     * @return string
     */
    public function placeholderImage(): string
    {
        $header = 'data:image/jpeg;base64,';
        if (!empty($this->placeholder)) {
            $content = $this->placeholder;
        } else {
            // At least return something
            return $this->defaultPlaceholderImage();
        }

        return Template::raw($header . rawurlencode($content));
    }

    /**
     * Getter for CraftQL
     *
     * @return string
     */
    public function getPlaceholderImage(): string
    {
        return $this->placeholderImage();
    }

    /**
     * @return string
     */
    public function placeholderImageSize(): string
    {
        $placeholder = $this->placeholderImage();
        $contentLength = !empty(strlen($placeholder)) ? strlen($placeholder) : 0;

        return ImageOptimize::$plugin->optimize->humanFileSize($contentLength, 1);
    }

    /**
     * Return an SVG box as a placeholder image
     *
     * @param string|null $color
     *
     * @return string
     */
    public function placeholderBox(?string $color = null): string
    {
        $width = $this->placeholderWidth ?? 1;
        $height = $this->placeholderHeight ?? 1;
        $color = $color ?? $this->colorPalette[0] ?? '#CCC';

        return Template::raw(ImageOptimize::$plugin->placeholder->generatePlaceholderBox($width, $height, $color));
    }

    /**
     * @param string|null $color
     *
     * @return string
     */
    public function getPlaceholderBox(string $color = null): string
    {
        return $this->placeholderBox($color);
    }

    /**
     * Getter for CraftQL
     *
     * @return string
     */
    public function placeholderBoxSize(): string
    {
        $placeholder = $this->placeholderBox();
        $contentLength = !empty(strlen($placeholder)) ? strlen($placeholder) : 0;

        return ImageOptimize::$plugin->optimize->humanFileSize($contentLength, 1);
    }

    /**
     * Return a silhouette of the image as an SVG placeholder
     *
     * @return string
     */
    public function placeholderSilhouette(): string
    {
        $header = 'data:image/svg+xml,';
        if (!empty($this->placeholderSvg)) {
            $content = $this->placeholderSvg;
        } else {
            // At least return something
            return $this->defaultPlaceholderImage();
        }

        return Template::raw($header . $content);
    }

    /**
     * Getter for CraftQL
     *
     * @return string
     */
    public function getPlaceholderSilhouette(): string
    {
        return $this->placeholderSilhouette();
    }

    /**
     * @return string
     */
    public function placeholderSilhouetteSize(): string
    {
        $placeholder = $this->placeholderSilhouette();
        $contentLength = !empty(strlen($placeholder)) ? strlen($placeholder) : 0;

        return ImageOptimize::$plugin->optimize->humanFileSize($contentLength, 1);
    }

    /**
     *  Get the file size of any remote resource (using curl),
     *  either in bytes or - default - as human-readable formatted string.
     *
     * @param string $url Takes the remote object's URL.
     * @param bool $formatSize Whether to return size in bytes or
     *                              formatted.
     * @param bool $useHead Whether to use HEAD requests. If false,
     *                              uses GET.
     *
     * @return  mixed    Returns human-readable formatted size
     *                              or size in bytes (default: formatted).
     * @noinspection PhpComposerExtensionStubsInspection*@author  Stephan Schmitz <eyecatchup@gmail.com>
     * @license MIT <http://eyecatchup.mit-license.org/>
     * @url     <https://gist.github.com/eyecatchup/f26300ffd7e50a92bc4d>
     *
     * @noinspection PhpComposerExtensionStubsInspection
     */
    public function getRemoteFileSize(string $url, bool $formatSize = true, bool $useHead = true): mixed
    {
        // Get an absolute URL with protocol that curl will be happy with
        $url = UrlHelper::absoluteUrlWithProtocol($url);
        $ch = curl_init($url);
        /** @noinspection CurlSslServerSpoofingInspection */
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);
        if ($useHead) {
            curl_setopt($ch, CURLOPT_NOBODY, 1);
        }
        curl_exec($ch);
        // content-length of download (in bytes), read from Content-Length: field
        $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        $error = curl_error($ch);
        if (!empty($error)) {
            Craft::error($error, __METHOD__);
        }
        curl_close($ch);
        // cannot retrieve file size, return "-1"
        if (!$contentLength) {
            return -1;
        }
        // return size in bytes
        if (!$formatSize) {
            return $contentLength;
        }

        return ImageOptimize::$plugin->optimize->humanFileSize($contentLength, 1);
    }

    // Protected Methods
    // =========================================================================

    protected function getSrcsetSubsetArray(array $set, int $width, string $comparison): array
    {
        $subset = [];
        $index = 0;
        if (empty($this->variantSourceWidths)) {
            return $subset;
        }
        foreach ($this->variantSourceWidths as $variantSourceWidth) {
            $match = false;
            switch ($comparison) {
                case 'width':
                    if ($variantSourceWidth === $width) {
                        $match = true;
                    }
                    break;

                case 'minwidth':
                    if ($variantSourceWidth >= $width) {
                        $match = true;
                    }
                    break;

                case 'maxwidth':
                    if ($variantSourceWidth <= $width) {
                        $match = true;
                    }
                    break;
            }
            if ($match) {
                $subset += array_slice($set, $index, 1, true);
            }
            $index++;
        }

        return $subset;
    }

    /**
     * @param array $array
     * @param bool $dpr
     *
     * @return string
     */
    protected function getSrcsetFromArray(array $array, bool $dpr = false): string
    {
        $srcset = '';
        foreach ($array as $key => $value) {
            if ($dpr) {
                $descriptor = '1x';
                if (!empty($array[(int)$key / 2])) {
                    $descriptor = '2x';
                }
                if (!empty($array[(int)$key / 3])) {
                    $descriptor = '3x';
                }
            } else {
                $descriptor = $key . 'w';
            }
            $srcset .= $value . ' ' . $descriptor . ', ';
        }

        return rtrim($srcset, ', ');
    }

    /**
     * Return a default placeholder image
     *
     * @return string
     */
    protected function defaultPlaceholderImage(): string
    {
        $width = 1;
        $height = 1;
        $color = '#CCC';

        return Template::raw(ImageOptimize::$plugin->placeholder->generatePlaceholderBox($width, $height, $color));
    }

    /**
     * Swap the tag attributes to work with lazy loading
     * ref: https://web.dev/native-lazy-loading/#how-do-i-handle-browsers-that-don't-yet-support-native-lazy-loading
     *
     * @param string $loading 'eager', 'lazy', 'lazySizes', 'lazySizesFallback'
     * @param string $placeHolder 'box', 'color', 'image', 'silhouette'
     * @param array $attrs
     *
     * @return array
     */
    protected function swapLazyLoadAttrs(string $loading, string $placeHolder, array $attrs): array
    {
        // Set the class and loading attributes
        if (isset($attrs['class'])) {
            $attrs['class'] = trim($attrs['class'] . ' lazyload');
        }
        // Set the style on this element to be the placeholder image as the background-image
        if (isset($attrs['style']) && !empty($attrs['src'])) {
            $attrs['style'] = trim(
                $attrs['style'] .
                'background-image:url(' . $this->getLazyLoadSrc($placeHolder) . '); background-size: cover;'
            );
        }
        // Handle attributes that lazy  and lazySizesFallback have in common
        switch ($loading) {
            case 'lazy':
            case 'lazySizesFallback':
                if (isset($attrs['loading'])) {
                    $attrs['loading'] = 'lazy';
                }
                break;
            default:
                break;
        }
        // Handle attributes that lazySizes and lazySizesFallback have in common
        switch ($loading) {
            case 'lazySizes':
            case 'lazySizesFallback':
                // Only swap to data- attributes if they want the LazySizes fallback
                if (!empty($attrs['sizes'])) {
                    $attrs['data-sizes'] = $attrs['sizes'];
                    $attrs['sizes'] = '';
                }
                if (!empty($attrs['srcset'])) {
                    $attrs['data-srcset'] = $attrs['srcset'];
                    $attrs['srcset'] = '';
                }
                if (!empty($attrs['src'])) {
                    $attrs['data-src'] = $attrs['src'];
                    $attrs['src'] = $this->getLazyLoadSrc($placeHolder);
                }
                break;
            default:
                break;
        }

        return $attrs;
    }

    /**
     * Return a lazy loading placeholder image based on the passed in $lazyload setting
     *
     * @param string $lazyLoad
     *
     * @return string
     */
    protected function getLazyLoadSrc(string $lazyLoad): string
    {
        $lazyLoad = strtolower($lazyLoad);
        return match ($lazyLoad) {
            'image' => $this->getPlaceholderImage(),
            'silhouette' => $this->getPlaceholderSilhouette(),
            'color' => $this->getPlaceholderBox($this->colorPalette[0] ?? null),
            default => $this->getPlaceholderBox('#CCC'),
        };
    }
}
