<?php
/**
 * ImageOptimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\imagetransforms;

use nystudio107\imageoptimize\ImageOptimize;

use Craft;
use craft\elements\Asset;
use craft\models\AssetTransform;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
class CraftImageTransform extends ImageTransform
{
    // Static Methods
    // =========================================================================

    /**
     * @param Asset               $asset
     * @param AssetTransform|null $transform
     * @param array               $params
     *
     * @return string|null
     */
    public static function getTransformUrl(Asset $asset, $transform, array $params = [])
    {
        $generateTransformsBeforePageLoad = $params['generateTransformsBeforePageLoad'] ?? true;
        // Generate the URLs to the optimized images
        $assets = Craft::$app->getAssets();
        $url = $assets->getAssetUrl($asset, $transform, $generateTransformsBeforePageLoad);

        return $url;
    }

    /**
     * @param string              $url
     * @param Asset               $asset
     * @param AssetTransform|null $transform
     * @param array               $params
     *
     * @return string
     */
    public static function getWebPUrl(string $url, Asset $asset, $transform, array $params = []): string
    {
        $url = self::appendExtension($url, '.webp');

        return $url;
    }

    /**
     * @return array
     */
    public static function getTransformParams(): array
    {
        $settings = ImageOptimize::$plugin->getSettings();
        // Get our $generateTransformsBeforePageLoad setting
        $generateTransformsBeforePageLoad = $settings->generateTransformsBeforePageLoad ?? true;
        $params = [
            'generateTransformsBeforePageLoad' => $generateTransformsBeforePageLoad,
        ];

        return $params;
    }
}
