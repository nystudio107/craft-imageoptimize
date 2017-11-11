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

use Cloudinary;
use Cloudinary\Uploader;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
class CloudinaryImageTransform extends ImageTransform implements ImageTransformInterface
{
    // Constants
    // =========================================================================

    const TRANSFORM_ATTRIBUTES_MAP = [
        'width' => 'wodth',
        'height' => 'height',
        'quality' => 'q',
        'format' => 'format',
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

        // Get the config parameters
        $cloud_name = isset($params['cloud_name'])
            ? $params['cloud_name']
            : 'sample';
        $api_key = isset($params['api_key'])
            ? $params['api_key']
            : 'a676b67565c6767a6767d6767f676fe1';
        $api_secret = isset($params['api_secret'])
            ? $params['api_secret']
            : 'a676b67565c6767a6767d6767f676fe1';
        Cloudinary::config([
            "cloud_name" => $cloud_name,
            "api_key" => $api_key,
            "api_secret" => $api_secret
        ]);

        // Map the transform properties
        foreach (self::TRANSFORM_ATTRIBUTES_MAP as $key => $value) {
            if (!empty($transform[$key])) {
                $params[$value] = $transform[$key];
            }
        }
        // Crop mode
        $params['fit'] = 'crop';
        // Handle the focal point
        $focalPoint = $asset->getFocalPoint();
        if (!empty($focalPoint)) {
            $params['fp-x'] = $focalPoint['x'];
            $params['fp-y'] = $focalPoint['y'];
            $params['crop'] = 'focalpoint';
        }

        $result = Uploader::upload(
            $asset->getUrl(),
            [
                'eager' => $params
            ]
        );

        if (!empty($result)) {
            if (!empty($result['url'])) {
                $url = $result['url'];
                // Prime the pump by downloading the image
                self::prefetchRemoteFile($url);
            }
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
        ];

        return $params;
    }
}
