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

use craft\elements\Asset;
use craft\helpers\ArrayHelper;
use craft\models\AssetTransform;

use Imgix\UrlBuilder;

use Craft;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
class ImgixImageTransform extends ImageTransform implements ImageTransformInterface
{
    // Constants
    // =========================================================================

    const TRANSFORM_ATTRIBUTES_MAP = [
        'width'   => 'w',
        'height'  => 'h',
        'quality' => 'q',
        'format'  => 'fm',
    ];

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

        $domain = isset($params['domain'])
            ? $params['domain']
            : 'demos.imgix.net';
        $builder = new UrlBuilder($domain);
        if ($asset && $builder) {
            $builder->setUseHttps(true);
            if ($transform) {
                // Map the transform properties
                foreach (self::TRANSFORM_ATTRIBUTES_MAP as $key => $value) {
                    if (!empty($transform[$key])) {
                        $params[$value] = $transform[$key];
                    }
                }
                // Remove any 'AUTO' settings
                ArrayHelper::removeValue($params, 'AUTO');
                // Handle the Imgix auto setting for compression/format
                $autoParams = [];
                if (empty($params['q'])) {
                    $autoParams = 'compress';
                }
                if (empty($params['fm'])) {
                    $autoParams = 'format';
                }
                if (!empty($autoParams)) {
                    $params['auto'] = implode(',', $autoParams);
                }
                // Handle interlaced images
                if (property_exists($transform, 'interlace')) {
                    if (($transform->interlace != 'none')
                        && (!empty($params['fm']))
                        && ($params['fm'] == 'jpg')
                    ) {
                        $params['fm'] = 'pjpg';
                    }
                }
                // Handle the mode
                switch ($transform->mode) {
                    case 'fit':
                        $params['fit'] = 'clamp';
                        break;

                    case 'stretch':
                        $params['fit'] = 'scale';
                        break;

                    default:
                        // Fit mode
                        $params['fit'] = 'crop';
                        $cropParams = [];
                        // Handle the focal point
                        $focalPoint = $asset->getFocalPoint();
                        if (!empty($focalPoint)) {
                            $params['fp-x'] = $focalPoint['x'];
                            $params['fp-y'] = $focalPoint['y'];
                            $cropParams[] = 'focalpoint';
                        } elseif (preg_match('/(top|center|bottom)-(left|center|right)/', $transform->position)) {
                            // Imgix defaults to 'center' if no param is present
                            $filteredCropParams = explode('-', $transform->position);
                            $filteredCropParams = array_diff($filteredCropParams, ['center']);
                            $cropParams[] = $filteredCropParams;
                        }
                        if (!empty($cropParams)) {
                            $params['crop'] = implode(',', $cropParams);
                        }
                        break;
                }
            } else {
                // No transform was passed in; so just auto all the things
                $params['auto'] = 'format,compress';
            }
            // Finally, create the Imgix URL for this transformed image
            $assetUri = self::getAssetUri($asset);
            $url = $builder->createURL($assetUri, $params);
            Craft::trace(
                'Imgix transform created for: ' . print_r($assetUri, true),
                __METHOD__
            );
            // Prime the pump by downloading the image
            self::prefetchRemoteFile($url);
        }

        return $url;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public static function getWebPUrl(string $url): string
    {
        $url = preg_replace('/fm=[^&]*/', 'fmt=webp', $url);
        // Prime the pump by downloading the image
        self::prefetchRemoteFile($url);

        return $url;
    }

    /**
     * @return array
     */
    public static function getTransformParams(): array
    {
        $settings = ImageOptimize::$plugin->getSettings();
        $params = [
            'domain' => $settings->imgixDomain,
        ];

        return $params;
    }
}
