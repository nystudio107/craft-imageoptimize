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

use Imgix\UrlBuilder;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
class ImgixImageTransform implements ImageTransformInterface
{
    // Constants
    // =========================================================================

    const TRANSFORM_ATTRIBUTES_MAP = [
        'width' => 'w',
        'height' => 'h',
        'quality' => 'q',
        'format' => 'fm',
    ];

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
        $url = '';
        $params = [];

        $builder = new UrlBuilder($domain);
        if ($asset && $builder) {
            $builder->setUseHttps(true);
            // Map the transform properties
            foreach (self::TRANSFORM_ATTRIBUTES_MAP as $key => $value) {
                if (!empty($transform[$key])) {
                    $params[$value] = $transform[$key];
                }
            }
            // Handle the focal point
            $focalPoint = $asset->getFocalPoint();
            if (!empty($focalPoint)) {
                $params['fp-x'] = $focalPoint['x'];
                $params['fp-y'] = $focalPoint['y'];
            }
            $url = $builder->createURL($asset->filename, $params);
        }

        return $url;
    }
}
