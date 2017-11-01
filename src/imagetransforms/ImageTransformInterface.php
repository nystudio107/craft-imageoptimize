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

use craft\elements\Asset;
use craft\models\AssetTransform;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
interface ImageTransformInterface
{

    // Constants
    // =========================================================================

    const TRANSFORM_TYPE = 'Generic';

    // Static Methods
    // =========================================================================

    /**
     * @param Asset          $asset
     * @param AssetTransform $transform
     * @param array          $params
     *
     * @return string
     */
    public static function getTransformUrl(Asset $asset, AssetTransform $transform, array $params = []): string;

}
