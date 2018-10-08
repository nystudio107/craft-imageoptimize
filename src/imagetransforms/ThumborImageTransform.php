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
use craft\helpers\UrlHelper;
use craft\models\AssetTransform;

use Psr\Http\Message\ResponseInterface;

use Craft;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
class ThumborImageTransform extends ImageTransform implements ImageTransformInterface
{
    // Constants
    // =========================================================================

    // Static Methods
    // =========================================================================

    /**
     * @param Asset               $asset
     * @param AssetTransform|null $transform
     * @param array               $params
     *
     * @return string|null
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public static function getTransformUrl(Asset $asset, $transform, array $params = [])
    {
        $assetUri = self::getAssetUri($asset);

        // TODO: should this be in getAssetUri
        if ($asset->volume instanceof craft\awss3\Volume) {
            $assetUri = implode('/', array_filter([$asset->volume->subfolder, $asset->path]));
        }

        $baseUrl = $params['baseUrl'];
        $securityKey = $params['securityKey'] ?: null;
        $builder = \Thumbor\Url\Builder::construct($baseUrl, $securityKey, $assetUri);

        if ($transform->mode === 'fit') {
            $builder->fitIn($transform->width, $transform->height);
        } elseif ($transform->mode === 'stretch') {
            // TODO: not sure?
            // $builder->resize($transform->width, $transform->height);
        } else {
            $builder->resize($transform->width, $transform->height);

            $focalPoint = self::getFocalPoint($asset);

            if ($focalPoint) {
                $builder->addFilter('focal', $focalPoint);
            } elseif (preg_match('/(top|center|bottom)-(left|center|right)/', $transform->position)) {
                // code...
            }
        }

        return (string) $builder;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public static function getWebPUrl(string $url): string
    {
        return $url;
    }

    /**
     * @param Asset $asset
     * @param array $params
     *
     * @return null|string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getPurgeUrl(Asset $asset, array $params = [])
    {
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @return bool
     */
    public static function purgeUrl(string $url, array $params = []): bool
    {
    }

    /**
     * @return array
     */
    public static function getTransformParams(): array
    {
        $settings = ImageOptimize::$plugin->getSettings();
        $params = [
            'baseUrl' => $settings->thumborBaseUrl,
            'securityKey' => $settings->thumborSecurityKey,
        ];

        return $params;
    }

    /**
     * @return string
     */
    protected static function getFocalPoint(Asset $asset)
    {
        $focalPoint = $asset->getFocalPoint();

        if (!$focalPoint) {
            return;
        }

        $box = array_map('intval', [
            'top' => $focalPoint['y'] * $asset->height - 1,
            'left' => $focalPoint['x'] * $asset->width - 1,
            'bottom' => $focalPoint['y'] * $asset->height + 1,
            'right' => $focalPoint['x'] * $asset->width + 1,
        ]);

        return implode('', [
            $box['top'],
            'x',
            $box['left'],
            ':',
            $box['bottom'],
            'x',
            $box['right'],
        ]);
    }
}
