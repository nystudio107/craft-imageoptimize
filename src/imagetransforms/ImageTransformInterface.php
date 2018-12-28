<?php
/**
 * ImageOptimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2018 nystudio107
 */

namespace nystudio107\imageoptimize\imagetransforms;

use craft\base\SavableComponentInterface;
use craft\elements\Asset;
use craft\models\AssetTransform;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.5.0
 */
interface ImageTransformInterface extends SavableComponentInterface
{
    // Static Methods
    // =========================================================================

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function getTemplatesRoot(): array;

    // Public Methods
    // =========================================================================

    /**
     * @param Asset               $asset
     * @param AssetTransform|null $transform
     * @param array               $params
     *
     * @return string|null
     */
    public function getTransformUrl(Asset $asset, $transform, array $params = []);

    /**
     * @param string              $url
     * @param Asset               $asset
     * @param AssetTransform|null $transform
     * @param array               $params
     *
     * @return string
     */
    public function getWebPUrl(string $url, Asset $asset, $transform, array $params = []): string;

    /**
     * @param Asset $asset
     * @param array $params
     *
     * @return mixed
     */
    public function getPurgeUrl(Asset $asset, array $params = []);

    /**
     * @param string $url
     * @param array  $params
     *
     * @return bool
     */
    public function purgeUrl(string $url, array $params = []): bool;

    /**
     * @param Asset $asset
     *
     * @return mixed
     */
    public function getAssetUri(Asset $asset);

    /**
     * @param string $url
     */
    public function prefetchRemoteFile($url);

    /**
     * @return array
     */
    public function getTransformParams(): array;
}
