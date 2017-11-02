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

use Craft;
use craft\elements\Asset;
use craft\errors\AssetLogicException;
use craft\models\AssetTransform;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
class CraftImageTransform implements ImageTransformInterface
{
    // Static Methods
    // =========================================================================

    /**
     * @param Asset          $asset
     * @param AssetTransform $transform
     * @param array          $params
     *
     * @return string
     */
    public static function getTransformUrl(Asset $asset, AssetTransform $transform, array $params = []): string
    {
        $generateTransformsBeforePageLoad = isset($params['generateTransformsBeforePageLoad'])
            ? $params['generateTransformsBeforePageLoad']
            : true;
        // Force generateTransformsBeforePageLoad = true to generate the images now
        $generalConfig = Craft::$app->getConfig()->getGeneral();
        $oldSetting = $generalConfig->generateTransformsBeforePageLoad;
        $generalConfig->generateTransformsBeforePageLoad = $generateTransformsBeforePageLoad;
        $url = '';
        try {
            // Generate the URLs to the optimized images
            $url = $asset->getUrl($transform);
        } catch (AssetLogicException $e) {
            // This isn't an image or an image format that can be transformed
        }
        $generalConfig->generateTransformsBeforePageLoad = $oldSetting;

        return $url;
    }
}
