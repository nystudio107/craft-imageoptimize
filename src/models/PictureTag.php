<?php
/**
 * Image Optimize plugin for Craft CMS
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) nystudio107
 */

namespace nystudio107\imageoptimize\models;

use craft\helpers\Html;
use craft\helpers\Template;
use Twig\Markup;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     5.0.0-beta.1
 */
class PictureTag extends BaseImageTag
{
    /**
     * @var string The loading scheme to use: 'eager', 'lazy', 'lazySizes', 'lazySizesFallback'
     */
    public $loading = 'eager';

    /**
     * @var string The type of placeholder image to use: 'box', 'color', 'image', 'silhouette'
     */
    public $placeholder = 'box';

    /**
     * @var array array of tag attributes for the <picture> tag
     */
    public $pictureAttrs = [];

    /**
     * @var array array of tag attributes for the <source> tags
     */
    public $sourceAttrs = [];

    /**
     * @var array array of tag attributes for the <img> tag
     */
    public $imgAttrs = [];

    /**
     * @var OptimizedImage[] array OptimizedImage models to add as art direction
     */
    public $artDirection = [];

    /**
     * @param $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        // Populate the $imageAttrs
        $this->imgAttrs = [
            'class' => '',
            'style' => '',
            'width' => $this->optimizedImage->placeholderWidth,
            'height' => $this->optimizedImage->placeholderHeight,
            'src' => reset($this->optimizedImage->optimizedImageUrls),
            'loading' => '',
        ];
        // Populate the $sourceAttrs
        $this->populateSourceAttrs($this->optimizedImage, []);
        // Populate the $pictureAttrs
        $this->pictureAttrs = [];
    }

    /**
     * Set the $loading property
     *
     * @param string $value
     * @return $this
     */
    public function loading(string $value): PictureTag
    {
        $this->loading = $value;

        return $this;
    }

    /**
     * Set the $placeholder property
     *
     * @param string $value
     * @return $this
     */
    public function placeholder(string $value): PictureTag
    {
        $this->placeholder = $value;

        return $this;
    }

    /**
     * Merge the passed array of tag attributes into $pictureAttrs
     *
     * @param array $value
     * @return $this
     */
    public function pictureAttrs(array $value): PictureTag
    {
        $this->pictureAttrs = array_merge($this->pictureAttrs, $value);

        return $this;
    }

    /**
     * Merge the passed array of tag attributes into $sourceAttrs
     *
     * @param array $value
     * @return $this
     */
    public function sourceAttrs(array $value): PictureTag
    {
        foreach ($this->sourceAttrs as &$attrs) {
            $attrs = array_merge($attrs, $value);
        }
        unset($attrs);

        return $this;
    }

    /**
     * Merge the passed array of tag attributes into $imgAttrs
     *
     * @param array $value
     * @return $this
     */
    public function imgAttrs(array $value): PictureTag
    {
        $this->imgAttrs = array_merge($this->imgAttrs, $value);

        return $this;
    }

    /**
     * Add art direction sources to the $sourceAttrs
     *
     * @param OptimizedImage $optimizedImage
     * @param array $sourceAttrs
     * @return PictureTag
     */
    public function artDirection(OptimizedImage $optimizedImage, array $sourceAttrs = []): PictureTag
    {
        $this->populateSourceAttrs($optimizedImage, $sourceAttrs);

        return $this;
    }

    /**
     * Generate a complete <img> tag for the $optimizedImage OptimizedImage model
     *
     * @return Markup
     */
    public function render(): Markup
    {
        $content = '';
        // Handle the <source> tag(s)
        foreach ($this->sourceAttrs as $attrs) {
            // Handle lazy loading
            if ($this->loading !== 'eager') {
                $attrs = $this->swapLazyLoadAttrs($this->loading, $this->placeholder, $attrs);
            }
            // Remove any empty attributes
            $attrs = array_filter($attrs);
            // Render the tag
            $content .= Html::tag('source', '', $attrs);
        }
        // Handle the <img> tag
        $attrs = $this->imgAttrs;
        // Handle lazy loading
        if ($this->loading !== 'eager') {
            $attrs = $this->swapLazyLoadAttrs($this->loading, $this->placeholder, $attrs);
        }
        // Remove any empty attributes
        $attrs = array_filter($attrs);
        // Render the tag
        $content .= Html::tag('img', '', $attrs);
        // Handle the <picture> tag
        $attrs = $this->pictureAttrs;
        // Remove any empty attributes
        $attrs = array_filter($attrs);
        // Render the tag
        $tag = Html::tag('picture', $content, $attrs);

        return Template::raw($tag);
    }

    /**
     * Populate the $sourceAttrs from the passed in $optimizedImage and $sizes
     *
     * @param OptimizedImage $optimizedImage
     * @param array $sourceAttrs attributes to add to the $sourceAttrs array
     * @return void
     */
    protected function populateSourceAttrs(OptimizedImage $optimizedImage, array $sourceAttrs): void
    {
        if (!empty($optimizedImage->optimizedWebPImageUrls)) {
            $this->sourceAttrs[] = array_merge([
                'media' => '',
                'srcset' => $optimizedImage->getSrcsetFromArray($optimizedImage->optimizedWebPImageUrls),
                'type' => 'image/webp',
                'sizes' => '100vw',
                'width' => $optimizedImage->placeholderWidth,
                'height' => $optimizedImage->placeholderHeight,
            ], $sourceAttrs);
        }
        $this->sourceAttrs[] = array_merge([
            'media' => '',
            'srcset' => $optimizedImage->getSrcsetFromArray($optimizedImage->optimizedImageUrls),
            'sizes' => '100vw',
            'width' => $optimizedImage->placeholderWidth,
            'height' => $optimizedImage->placeholderHeight,
        ], $sourceAttrs);
    }
}
