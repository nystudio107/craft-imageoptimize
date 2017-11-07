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
use craft\errors\AssetLogicException;
use craft\models\AssetTransform;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
class CraftImageTransform extends ImageTransform implements ImageTransformInterface
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
        $url = null;

        $generateTransformsBeforePageLoad = isset($params['generateTransformsBeforePageLoad'])
            ? $params['generateTransformsBeforePageLoad']
            : true;
        // Force generateTransformsBeforePageLoad = true to generate the images now
        $generalConfig = Craft::$app->getConfig()->getGeneral();
        $oldSetting = $generalConfig->generateTransformsBeforePageLoad;
        $generalConfig->generateTransformsBeforePageLoad = $generateTransformsBeforePageLoad;
        try {
            // Generate the URLs to the optimized images
            $url = $asset->getUrl($transform);
        } catch (AssetLogicException $e) {
            // This isn't an image or an image format that can be transformed
        }
        $generalConfig->generateTransformsBeforePageLoad = $oldSetting;

        return $url;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public static function getWebPUrl(string $url): string
    {
        $url = $url.".webp";

        return $url;
    }

    /**
     * @return array
     */
    public static function getTransformParams(): array
    {
        $settings = ImageOptimize::$plugin->getSettings();
        // Get our $generateTransformsBeforePageLoad setting
        $generateTransformsBeforePageLoad = isset($settings->generateTransformsBeforePageLoad)
            ? $settings->generateTransformsBeforePageLoad
            : true;
        $params = [
            'generateTransformsBeforePageLoad' => $generateTransformsBeforePageLoad,
        ];

        return $params;
    }

}
