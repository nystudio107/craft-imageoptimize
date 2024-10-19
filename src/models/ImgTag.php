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
class ImgTag extends BaseImageTag
{
    /**
     * @var string The loading scheme to use: 'eager', 'lazy', 'lazySizes', 'lazySizesFallback'
     */
    public string $loadingStrategy = 'eager';

    /**
     * @var string The type of placeholder image to use: 'box', 'color', 'image', 'silhouette'
     */
    public string $placeholder = 'box';

    /**
     * @var array array of tag attributes for the <img> tag
     */
    public array $imgAttrs = [];

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
            'srcset' => $this->optimizedImage->getSrcsetFromArray($this->optimizedImage->optimizedImageUrls),
            'sizes' => '100vw',
            'loading' => '',
        ];
    }

    /**
     * Set the $loading property
     *
     * @param string $value
     * @return $this
     */
    public function loadingStrategy(string $value): ImgTag
    {
        $this->loadingStrategy = $value;

        return $this;
    }

    /**
     * Set the $placeholder property
     *
     * @param string $value
     * @return $this
     */
    public function placeholder(string $value): ImgTag
    {
        $this->placeholder = $value;

        return $this;
    }

    /**
     * Merge the passed array of tag attributes into $imgAttrs
     *
     * @param array $value
     * @return $this
     */
    public function imgAttrs(array $value): ImgTag
    {
        $this->imgAttrs = array_merge($this->imgAttrs, $value);

        return $this;
    }

    /**
     * Generate a complete <img> tag for the $optimizedImage OptimizedImage model
     *
     * @return Markup
     */
    public function render(): Markup
    {
        $attrs = $this->imgAttrs;
        // Handle lazy loading
        if ($this->loadingStrategy !== 'eager') {
            $attrs = $this->swapLazyLoadAttrs($this->loadingStrategy, $this->placeholder, $attrs);
        }
        // Remove any empty attributes
        $attrs = $this->filterEmptyAttributes($attrs);
        // Render the tag
        $tag = Html::tag('img', '', $attrs);

        return Template::raw($tag);
    }
}
