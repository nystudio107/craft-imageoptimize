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

use Thumbor\Url\Builder;
use Thumbor\Url\BuilderFactory;
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
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public static function getWebPUrl(string $url): string
    {
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
    }
}
