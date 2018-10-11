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

use nystudio107\imageoptimize\helpers\UrlHelper;

use craft\elements\Asset;
use craft\helpers\Assets as AssetsHelper;
use craft\models\AssetTransform;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
abstract class ImageTransform implements ImageTransformInterface
{
    // Public Static Methods
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

        return $url;
    }

    /**
     * @param string              $url
     * @param Asset               $asset
     * @param AssetTransform|null $transform
     * @param array               $params
     *
     * @return string
     */
    public static function getWebPUrl(string $url, Asset $asset, $transform, array $params = []): string
    {
        return $url;
    }

    /**
     * @param Asset $asset
     * @param array $params
     *
     * @return null|string
     */
    public static function getPurgeUrl(Asset $asset, array $params = [])
    {
        $url = null;

        return $url;
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @return bool
     */
    public static function purgeUrl(string $url, array $params = []): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public static function getTransformParams(): array
    {
        $params = [
        ];

        return $params;
    }

    /**
     * @param Asset $asset
     *
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public static function getAssetUri(Asset $asset)
    {
        $volume = $asset->getVolume();
        $assetPath = $asset->getPath();

        // Account for volume types with a subfolder setting
        // e.g. craftcms/aws-s3, craftcms/google-cloud
        if ($volume->subfolder ?? null) {
            return rtrim($volume->subfolder, '/').'/'.$assetPath;
        }

        return $assetPath;
    }

    /**
     * @param string $url
     */
    public static function prefetchRemoteFile($url)
    {
        // Get an absolute URL with protocol that curl will be happy with
        $url = UrlHelper::absoluteUrlWithProtocol($url);
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

    /**
     * Append an extension a passed url or path
     *
     * @param $pathOrUrl
     * @param $extension
     *
     * @return string
     */
    public static function appendExtension($pathOrUrl, $extension): string
    {
        $path = self::decomposeUrl($pathOrUrl);
        $path_parts = pathinfo($path['path']);
        $new_path = $path_parts['filename'] . '.' . $path_parts['extension'] . $extension;
        if (!empty($path_parts['dirname']) && $path_parts['dirname'] !== '.') {
            $new_path = $path_parts['dirname'] . DIRECTORY_SEPARATOR . $new_path;
            $new_path = preg_replace('/([^:])(\/{2,})/', '$1/', $new_path);
        }
        $output = $path['prefix'] . $new_path . $path['suffix'];

        return $output;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Decompose a url into a prefix, path, and suffix
     *
     * @param $pathOrUrl
     *
     * @return array
     */
    protected static function decomposeUrl($pathOrUrl): array
    {
        $result = array();

        if (filter_var($pathOrUrl, FILTER_VALIDATE_URL)) {
            $url_parts = parse_url($pathOrUrl);
            $result['prefix'] = $url_parts['scheme'] . '://' . $url_parts['host'];
            $result['path'] = $url_parts['path'];
            $result['suffix'] = '';
            $result['suffix'] .= empty($url_parts['query']) ? '' : '?' . $url_parts['query'];
            $result['suffix'] .= empty($url_parts['fragment']) ? '' : '#' . $url_parts['fragment'];
        } else {
            $result['prefix'] = '';
            $result['path'] = $pathOrUrl;
            $result['suffix'] = '';
        }

        return $result;
    }
}
