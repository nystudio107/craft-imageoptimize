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

        if ($asset->volume instanceof craft\awss3\Volume) {
            $assetUri = $asset->volume->subfolder.'/'.$asset->path;
        }

        $baseUrl = $params['baseUrl'];
        $securityKey = $params['securityKey'] ?: null;
        $builder = \Thumbor\Url\Builder::construct($baseUrl, $securityKey, $assetUri);

        // exit(var_dump($transform));

        if ($transform->mode === 'fit') {
            $builder->fitIn($transform->width, $transform->height);
        } elseif ($transform->mode === 'stretch') {
            // TODO: not sure?
            // $builder->resize($transform->width, $transform->height);
        } else {
            $builder->resize($transform->width, $transform->height);
            $builder->addFilter('focal', self::getFocalPoint($asset));
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
        $x = $focalPoint['x'] * $asset->width;
        $y = $focalPoint['y'] * $asset->height;

        return implode('', [
            $x - 1,
            'x',
            $y - 1,
            ':',
            $x + 1,
            'x',
            $y + 1,
        ]);
    }
}
