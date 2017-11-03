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
use craft\helpers\UrlHelper;
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

        $domain = isset($params['domain'])
            ? $params['domain']
            : 'demos.imgix.net';
        $builder = new UrlBuilder($domain);
        if ($asset && $builder) {
            $builder->setUseHttps(true);
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
            $url = $builder->createURL($asset->filename, $params);
            // Prime the pump by downloading the image
            self::downloadRemoteFile($url);
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
        self::downloadRemoteFile($url);

        return $url;
    }

    /**
     * @param $url
     */
    protected static function downloadRemoteFile($url)
    {
        // Make this a full
        if (!UrlHelper::isAbsoluteUrl($url)) {
            $url = UrlHelper::siteUrl($url);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_NOBODY         => 1,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
