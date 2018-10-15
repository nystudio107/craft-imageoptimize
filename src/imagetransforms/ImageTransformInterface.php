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

    const IMAGE_TRANSFORM_MAP = [
        'craft' => CraftImageTransform::class,
        'imgix' => ImgixImageTransform::class,
        'thumbor' => ThumborImageTransform::class,
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
    public static function getTransformUrl(Asset $asset, $transform, array $params = []);

    /**
     * @param string              $url
     * @param Asset               $asset
     * @param AssetTransform|null $transform
     * @param array               $params
     *
     * @return string
     */
    public static function getWebPUrl(string $url, Asset $asset, $transform, array $params = []): string;

    /**
     * @param Asset $asset
     * @param array $params
     *
     * @return mixed
     */
    public static function getPurgeUrl(Asset $asset, array $params = []);

    /**
     * @param string $url
     * @param array  $params
     *
     * @return bool
     */
    public static function purgeUrl(string $url, array $params = []): bool;


    /**
     * @param Asset $asset
     *
     * @return mixed
     */
    public static function getAssetUri(Asset $asset);

    /**
     * @param string $url
     */
    public static function prefetchRemoteFile($url);

    /**
     * @return array
     */
    public static function getTransformParams(): array;
}
