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
     * Return an array that contains the template root and corresponding file
     * system directory for the Image Transform's templates
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function getTemplatesRoot(): array;

    // Public Methods
    // =========================================================================

    /**
     * Return a URL to a transformed images
     *
     * @param Asset               $asset
     * @param AssetTransform|null $transform
     * @param array               $params
     *
     * @return string|null
     */
    public function getTransformUrl(Asset $asset, $transform, array $params = []);

    /**
     * Return a URL to the webp version of the transformed image
     *
     * @param string              $url
     * @param Asset               $asset
     * @param AssetTransform|null $transform
     * @param array               $params
     *
     * @return string
     */
    public function getWebPUrl(string $url, Asset $asset, $transform, array $params = []): string;

    /**
     * Return the URL that should be used to purge the Asset
     *
     * @param Asset $asset
     * @param array $params
     *
     * @return mixed
     */
    public function getPurgeUrl(Asset $asset, array $params = []);

    /**
     * Purge the URL from the service's cache
     *
     * @param string $url
     * @param array  $params
     *
     * @return bool
     */
    public function purgeUrl(string $url, array $params = []): bool;

    /**
     * Return the URI to the asset
     *
     * @param Asset $asset
     *
     * @return mixed
     */
    public function getAssetUri(Asset $asset);

    /**
     * Prefetch the remote file to prime the cache
     *
     * @param string $url
     */
    public function prefetchRemoteFile($url);

    /**
     * Get the parameters needed for this transform
     *
     * @return array
     */
    public function getTransformParams(): array;
}
