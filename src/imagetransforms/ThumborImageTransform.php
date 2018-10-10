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
        $baseUrl = $params['baseUrl'];
        $securityKey = $params['securityKey'] ?: null;
        $builder = \Thumbor\Url\Builder::construct($baseUrl, $securityKey, $assetUri);
        $settings = ImageOptimize::$plugin->getSettings();

        if ($transform->mode === 'fit') {

            // https://thumbor.readthedocs.io/en/latest/usage.html#fit-in
            $builder->fitIn($transform->width, $transform->height);
        } elseif ($transform->mode === 'stretch') {

            // AFAIK, this isn't possible with Thumbor…throw exception?
            // https://github.com/thumbor/thumbor/issues/1123
            $builder
                ->resize($transform->width, $transform->height)
                ->addFilter('upscale');
        } else {

            https://thumbor.readthedocs.io/en/latest/usage.html#image-size
            $builder->resize($transform->width, $transform->height);

            if ($focalPoint = self::getFocalPoint($asset)) {

                https://thumbor.readthedocs.io/en/latest/focal.html
                $builder->addFilter('focal', $focalPoint);
            } elseif (preg_match('/(top|center|bottom)-(left|center|right)/', $transform->position, $matches)) {
                $v = str_replace('center', 'middle', $matches[1]);
                $h = $matches[2];

                https://thumbor.readthedocs.io/en/latest/usage.html#horizontal-align
                $builder->valign($v)->halign($h);
            }
        }

        // https://thumbor.readthedocs.io/en/latest/format.html
        if ($format = self::getFormat($transform)) {
            $builder->addFilter('format', $format);
        }

        // https://thumbor.readthedocs.io/en/latest/quality.html
        if ($transform->quality) {
            $builder->addFilter('quality', $transform->quality);
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
     * @return string|null
     */
    protected static function getFocalPoint(Asset $asset)
    {
        $focalPoint = $asset->getFocalPoint();

        if (!$focalPoint) {
            return null;
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

    /**
     * @param AssetTransform|null $transform
     *
     * @return string|null
     */
    protected static function getFormat($transform)
    {
        $format = str_replace(
            ['Auto', 'jpg'],
            ['', 'jpeg'],
            $transform->format
        );

        return $format ?: null;
    }
}
