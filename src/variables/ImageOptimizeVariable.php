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

use craft\elements\Asset;
use craft\helpers\Template;
use nystudio107\imageoptimize\ImageOptimize;
use nystudio107\imageoptimize\imagetransforms\ImageTransformInterface;
use nystudio107\imageoptimize\models\OptimizedImage;
use nystudio107\pluginvite\variables\ViteVariableInterface;
use nystudio107\pluginvite\variables\ViteVariableTrait;
use Twig\Markup;
use yii\base\InvalidConfigException;

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
     * @return Markup
     */
    public function renderLazySizesFallbackJs(array $scriptAttrs = [], array $variables = []): Markup
    {
        return Template::raw(ImageOptimize::$plugin->optimize->renderLazySizesFallbackJs($scriptAttrs, $variables));
    }

    /**
     * Render the LazySizes JS
     *
     * @param array $scriptAttrs
     * @param array $variables
     * @return Markup
     */
    public function renderLazySizesJs(array $scriptAttrs = [], array $variables = []): Markup
    {
        return Template::raw(ImageOptimize::$plugin->optimize->renderLazySizesJs($scriptAttrs, $variables));
    }

    /**
     * Return an SVG box as a placeholder image
     *
     * @param             $width
     * @param             $height
     * @param ?string $color
     *
     * @return Markup
     */
    public function placeholderBox($width, $height, ?string $color = null): Markup
    {
        return Template::raw(ImageOptimize::$plugin->placeholder->generatePlaceholderBox($width, $height, $color));
    }

    /**
     * @param Asset $asset
     * @param ?array $variants
     * @param bool $generatePlaceholders
     *
     * @return ?OptimizedImage
     * @throws InvalidConfigException
     */
    public function createOptimizedImages(
        Asset  $asset,
        ?array $variants = null,
        bool   $generatePlaceholders = false
    ): ?OptimizedImage
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
     * @return ?ImageTransformInterface The Image Transform
     */
    public function createImageTransformType($config): ?ImageTransformInterface
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
        return true;
    }
}
