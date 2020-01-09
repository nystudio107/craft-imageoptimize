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

use nystudio107\imageoptimize\ImageOptimize;
use nystudio107\imageoptimize\helpers\UrlHelper;
use nystudio107\imageoptimize\helpers\Color as ColorHelper;

use craft\helpers\Template;
use craft\base\Model;
use craft\validators\ArrayValidator;

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
     * @var string[] An array of optimized image variant URLs
     */
    public $optimizedImageUrls = [];

    /**
     * @var string[] An array of optimized .webp image variant URLs
     */
    public $optimizedWebPImageUrls = [];

    /**
     * @var int[] An array of the widths of the optimized image variants
     */
    public $variantSourceWidths = [];

    /**
     * @var int[] An array of the heights of the optimized image variants
     */
    public $variantHeights = [];

    /**
     * @var float[] An array of the x,y image focal point coords, ranging from 0.0 to 1.0
     */
    public $focalPoint;

    /**
     * @var int The width of the original source image
     */
    public $originalImageWidth;

    /**
     * @var int The height of the original source image
     */
    public $originalImageHeight;

    /**
     * @var string The base64 encoded placeholder LQIP image
     */
    public $placeholder = '';

    /**
     * @var string The base64 encoded placeholder LQIP SVG image
     */
    public $placeholderSvg = '';

    /**
     * @var string[] An array the 5 most dominant colors in the image
     */
    public $colorPalette = [];

    /**
     * @var int The overall lightness of the image, from 0..100
     */
    public $lightness;

    /**
     * @var int The width of the placeholder image
     */
    public $placeholderWidth;

    /**
     * @var int The height of the placeholder image
     */
    public $placeholderHeight;

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
        ];
    }

    /**
     * Return the first image variant URL or the specific one passed in via
     * $width
     *
     * @param int $width
     *
     * @return \Twig\Markup|null
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
     * @return null|string|\Twig\Markup
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
     * @return \Twig\Markup|null
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
     * @param int  $width
     * @param bool $dpr Whether to generate 1x, 2x srcsets vs the normal XXXw
     *                  srcsets
     *
     * @return \Twig\Markup|null
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
     * @param int  $width
     * @param bool $dpr Whether to generate 1x, 2x srcsets vs the normal XXXw
     *                  srcsets
     *
     * @return \Twig\Markup|null
     */
    public function srcsetMinWidth(int $width, bool $dpr = false): string
    {
        $subset = $this->getSrcsetSubsetArray($this->optimizedImageUrls, $width, 'minwidth');

        return Template::raw($this->getSrcsetFromArray($subset, $dpr));
    }

    /**
     * Return a string of image URLs and their sizes that are $width or smaller
     *
     * @param int  $width
     * @param bool $dpr Whether to generate 1x, 2x srcsets vs the normal XXXw
     *                  srcsets
     *
     * @return \Twig\Markup|null
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
     * @return \Twig\Markup|null
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
     * @return \Twig\Markup|null
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
     * @param int  $width
     * @param bool $dpr Whether to generate 1x, 2x srcsets vs the normal XXXw
     *                  srcsets
     *
     * @return \Twig\Markup|null
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
     * @param int  $width
     * @param bool $dpr Whether to generate 1x, 2x srcsets vs the normal XXXw
     *                  srcsets
     *
     * @return \Twig\Markup|null
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
     * @param int  $width
     * @param bool $dpr Whether to generate 1x, 2x srcsets vs the normal XXXw
     *                  srcsets
     *
     * @return \Twig\Markup|null
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
     * Return a base64-encoded placeholder image
     *
     * @return \Twig\Markup|null
     */
    public function placeholderImage()
    {
        $header = 'data:image/jpeg;base64,';
        if (!empty($this->placeholder)) {
            $content = $this->placeholder;
        } else {
            // At least return something
            return $this->defaultPlaceholderImage();
        }

        return Template::raw($header.rawurlencode($content));
    }

    /**
     * Getter for CraftQL
     *
     * @return string
     */
    public function getPlaceholderImage(): string
    {
        return (string)$this->placeholderImage();
    }

    /**
     * @return string
     */
    public function placeholderImageSize(): string
    {
        $placeholder = $this->placeholderImage();
        $contentLength = !empty(\strlen($placeholder)) ? \strlen($placeholder) : 0;

        return ImageOptimize::$plugin->optimize->humanFileSize($contentLength, 1);
    }

    /**
     * Return an SVG box as a placeholder image
     *
     * @param string|null $color
     *
     * @return \Twig\Markup|null
     */
    public function placeholderBox(string $color = null)
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
        return (string)$this->placeholderBox($color);
    }

    /**
     * Getter for CraftQL
     *
     * @return string
     */
    public function placeholderBoxSize(): string
    {
        $placeholder = $this->placeholderBox();
        $contentLength = !empty(\strlen($placeholder)) ? \strlen($placeholder) : 0;

        return ImageOptimize::$plugin->optimize->humanFileSize($contentLength, 1);
    }

    /**
     * Return a silhouette of the image as an SVG placeholder
     *
     * @return \Twig\Markup|null
     */
    public function placeholderSilhouette()
    {
        $header = 'data:image/svg+xml,';
        if (!empty($this->placeholderSvg)) {
            $content = $this->placeholderSvg;
        } else {
            // At least return something
            return $this->defaultPlaceholderImage();
        }

        return Template::raw($header.$content);
    }

    /**
     * Getter for CraftQL
     *
     * @return string
     */
    public function getPlaceholderSilhouette(): string
    {
        return (string)$this->placeholderSilhouette();
    }

    /**
     * @return string
     */
    public function placeholderSilhouetteSize(): string
    {
        $placeholder = $this->placeholderSilhouette();
        $contentLength = !empty(\strlen($placeholder)) ? \strlen($placeholder) : 0;

        return ImageOptimize::$plugin->optimize->humanFileSize($contentLength, 1);
    }

    /**
     *  Get the file size of any remote resource (using curl),
     *  either in bytes or - default - as human-readable formatted string.
     *
     * @author  Stephan Schmitz <eyecatchup@gmail.com>
     * @license MIT <http://eyecatchup.mit-license.org/>
     * @url     <https://gist.github.com/eyecatchup/f26300ffd7e50a92bc4d>
     *
     * @param   string  $url        Takes the remote object's URL.
     * @param   boolean $formatSize Whether to return size in bytes or
     *                              formatted.
     * @param   boolean $useHead    Whether to use HEAD requests. If false,
     *                              uses GET.
     *
     * @return  int|mixed|string    Returns human-readable formatted size
     *                              or size in bytes (default: formatted).
     */
    public function getRemoteFileSize($url, $formatSize = true, $useHead = true)
    {
        // Get an absolute URL with protocol that curl will be happy with
        $url = UrlHelper::absoluteUrlWithProtocol($url);
        $ch = curl_init($url);
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
                    if ($variantSourceWidth == $width) {
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
     * @param bool  $dpr
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
                $descriptor = $key.'w';
            }
            $srcset .= $value.' '.$descriptor.', ';
        }
        $srcset = rtrim($srcset, ', ');

        return $srcset;
    }

    /**
     * Return a default placeholder image
     *
     * @return \Twig\Markup
     */
    protected function defaultPlaceholderImage(): \Twig\Markup
    {
        $width = 1;
        $height = 1;
        $color = '#CCC';

        return Template::raw(ImageOptimize::$plugin->placeholder->generatePlaceholderBox($width, $height, $color));
    }
}
