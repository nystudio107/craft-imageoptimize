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
class LinkPreloadTag extends BaseTag
{
    /**
     * @var array array of tag attributes for the <link rel="preload"> tag
     */
    public $linkAttrs = [];

    /**
     * @param $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        // Any web browser that supports link rel="preload" as="image" also supports webp, so prefer that
        $srcset = $this->optimizedImage->optimizedImageUrls;
        if (!empty($this->optimizedImage->optimizedWebPImageUrls)) {
            $srcset = $this->optimizedImage->optimizedWebPImageUrls;
        }
        // Populate the $imageAttrs
        $this->linkAttrs = [
            'rel' => 'preload',
            'as' => 'image',
            'href' => reset($srcset),
            'imagesrcset' => $this->optimizedImage->getSrcsetFromArray($srcset),
            'imagesizes' => '100vw',
        ];
    }

    /**
     * Merge the passed array of tag attributes into $linkAttrs
     *
     * @param array $value
     * @return $this
     */
    public function linkAttrs(array $value): LinkPreloadTag
    {
        $this->linkAttrs = array_merge($this->linkAttrs, $value);

        return $this;
    }

    /**
     * Generate a complete <link rel="preload"> tag for the $optimizedImage OptimizedImage model
     * ref: https://web.dev/preload-responsive-images/#imagesrcset-and-imagesizes
     *
     * @return Markup
     */
    public function render(): Markup
    {
        $attrs = $this->linkAttrs;
        // Remove any empty attributes
        $attrs = $this->filterEmptyAttributes($attrs);
        // Render the tag
        $tag = Html::tag('link', '', $attrs);

        return Template::raw($tag);
    }
}
