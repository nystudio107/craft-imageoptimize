<?php
/**
 * Image Optimize plugin for Craft CMS
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\variables;

use nystudio107\imageoptimize\ImageOptimize;
use nystudio107\imageoptimize\imagetransforms\ImageTransformInterface;
use nystudio107\imageoptimize\models\OptimizedImage;

use nystudio107\pluginvite\variables\ViteVariableInterface;
use nystudio107\pluginvite\variables\ViteVariableTrait;

use craft\elements\Asset;
use craft\helpers\Template;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.4.0
 */
class ImageOptimizeVariable implements ViteVariableInterface
{
    use ViteVariableTrait;

    // Public Methods
    // =========================================================================

    /**
     * Render the LazySizes fallback JS
     *
     * @param array $scriptAttrs
     * @param array $variables
     * @return string
     */
    public function renderLazySizesFallbackJs($scriptAttrs = [], $variables = [])
    {
        return Template::raw(ImageOptimize::$plugin->optimize->renderLazySizesFallbackJs($scriptAttrs, $variables));
    }

    /**
     * Render the LazySizes JS
     *
     * @param array $scriptAttrs
     * @param array $variables
     * @return string
     */
    public function renderLazySizesJs($scriptAttrs = [], $variables = [])
    {
        return Template::raw(ImageOptimize::$plugin->optimize->renderLazySizesJs($scriptAttrs, $variables));
    }

    /**
     * Return an SVG box as a placeholder image
     *
     * @param             $width
     * @param             $height
     * @param string|null $color
     *
     * @return \Twig_Markup|null
     */
    public function placeholderBox($width, $height, $color = null)
    {
        return Template::raw(ImageOptimize::$plugin->placeholder->generatePlaceholderBox($width, $height, $color));
    }

    /**
     * @param Asset $asset
     * @param array $variants
     * @param bool $generatePlaceholders
     *
     * @return OptimizedImage|null
     */
    public function createOptimizedImages(
        Asset $asset,
              $variants = null,
              $generatePlaceholders = false
    )
    {
        // Override our settings for lengthy operations, since we're doing this via Twig
        ImageOptimize::$generatePlaceholders = $generatePlaceholders;

        return ImageOptimize::$plugin->optimizedImages->createOptimizedImages($asset, $variants);
    }

    /**
     * Returns whether `.webp` is a format supported by the server
     *
     * @return bool
     */
    public function serverSupportsWebP(): bool
    {
        return ImageOptimize::$plugin->optimize->serverSupportsWebP();
    }

    /**
     * Creates an Image Transform with a given config.
     *
     * @param mixed $config The Image Transform’s class name, or its config,
     *                      with a `type` value and optionally a `settings` value
     *
     * @return null|ImageTransformInterface The Image Transform
     */
    public function createImageTransformType($config): ImageTransformInterface
    {
        return ImageOptimize::$plugin->optimize->createImageTransformType($config);
    }

    /**
     * Return whether we are running Craft 3.1 or later
     *
     * @return bool
     */
    public function craft31(): bool
    {
        return ImageOptimize::$craft31;
    }
}
