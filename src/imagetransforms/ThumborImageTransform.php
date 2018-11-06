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
use craft\models\AssetTransform;
use Thumbor\Url\Builder as UrlBuilder;

use Craft;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
class ThumborImageTransform extends ImageTransform
{
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
        return (string)self::getUrlBuilderForTransform($asset, $transform, $params);
    }

    /**
     * @param string              $url
     * @param Asset               $asset
     * @param AssetTransform|null $transform
     * @param array               $params
     *
     * @return string
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public static function getWebPUrl(string $url, Asset $asset, $transform, array $params = []): string
    {
        $builder = self::getUrlBuilderForTransform($asset, $transform, $params)
            ->addFilter('format', 'webp');

        return (string)$builder;
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @return bool
     */
    public static function purgeUrl(string $url, array $params = []): bool
    {
        return false;
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
     * @param Asset               $asset
     * @param AssetTransform|null $transform
     * @param array               $params
     *
     * @return UrlBuilder
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    private static function getUrlBuilderForTransform(Asset $asset, $transform, array $params = []): UrlBuilder
    {
        $assetUri = self::getAssetUri($asset);
        $baseUrl = $params['baseUrl'];
        $securityKey = $params['securityKey'] ?: null;
        $builder = UrlBuilder::construct($baseUrl, $securityKey, $assetUri);
        $settings = ImageOptimize::$plugin->getSettings();

        if ($transform->mode === 'fit') {
            // https://thumbor.readthedocs.io/en/latest/usage.html#fit-in
            $builder->fitIn($transform->width, $transform->height);
        } elseif ($transform->mode === 'stretch') {
            $builder
                ->resize($transform->width, $transform->height)
                ->addFilter('upscale');

            // https://github.com/thumbor/thumbor/issues/1123
            Craft::warning('Thumbor has no equivalent to the "stretch" transform mode. The resulting image will be resized and cropped, but not stretched.', __METHOD__);
        } else {

            // https://thumbor.readthedocs.io/en/latest/usage.html#image-size
            $builder->resize($transform->width, $transform->height);

            if ($focalPoint = self::getFocalPoint($asset)) {
                // https://thumbor.readthedocs.io/en/latest/focal.html
                $builder->addFilter('focal', $focalPoint);
            } elseif (preg_match('/(top|center|bottom)-(left|center|right)/', $transform->position, $matches)) {
                $v = str_replace('center', 'middle', $matches[1]);
                $h = $matches[2];

                // https://thumbor.readthedocs.io/en/latest/usage.html#horizontal-align
                $builder->valign($v)->halign($h);
            }
        }

        // https://thumbor.readthedocs.io/en/latest/format.html
        if ($format = self::getFormat($transform)) {
            $builder->addFilter('format', $format);
        }

        // https://thumbor.readthedocs.io/en/latest/quality.html
        if ($quality = self::getQuality($transform)) {
            $builder->addFilter('quality', $quality);
        }

        if (property_exists($transform, 'interlace')) {
            Craft::warning('Thumbor enables progressive JPEGs on the server-level, not as a request option. See https://thumbor.readthedocs.io/en/latest/jpegtran.html', __METHOD__);
        }

        if ($settings->autoSharpenScaledImages) {
            // See if the image has been scaled >= 50%
            $widthScale = $asset->getWidth() / ($transform->width ?? $asset->getWidth());
            $heightScale = $asset->getHeight() / ($transform->height ?? $asset->getHeight());
            if (($widthScale >= 2.0) || ($heightScale >= 2.0)) {
                // https://thumbor.readthedocs.io/en/latest/sharpen.html
                $builder->addFilter('sharpen', .5, .5, 'true');
            }
        }

        return $builder;
    }

    /**
     * @return string|null
     */
    private static function getFocalPoint(Asset $asset)
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
            $box['left'],
            'x',
            $box['top'],
            ':',
            $box['right'],
            'x',
            $box['bottom'],
        ]);
    }

    /**
     * @param AssetTransform|null $transform
     *
     * @return string|null
     */
    private static function getFormat($transform)
    {
        $format = str_replace(
            ['Auto', 'jpg'],
            ['', 'jpeg'],
            $transform->format
        );

        return $format ?: null;
    }

    /**
     * @param AssetTransform|null $transform
     *
     * @return int
     */
    private static function getQuality($transform)
    {
        return $transform->quality ?? Craft::$app->getConfig()->getGeneral()->defaultImageQuality;
    }
}
