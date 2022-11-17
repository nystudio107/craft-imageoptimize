<?php
/**
 * ImageOptimize plugin for Craft CMS
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2018 nystudio107
 */

namespace nystudio107\imageoptimize\imagetransforms;

use Craft;
use craft\base\SavableComponent;
use craft\elements\Asset;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use craft\models\ImageTransform as CraftImageTransformModel;
use nystudio107\imageoptimize\helpers\UrlHelper;
use ReflectionClass;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.6.0
 */
abstract class ImageTransform extends SavableComponent implements ImageTransformInterface
{
    // Traits
    // =========================================================================

    use ImageTransformTrait;

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('image-optimize', 'Generic Transform');
    }

    /**
     * @inheritdoc
     */
    public static function getTemplatesRoot(): array
    {
        $reflect = new ReflectionClass(static::class);
        $classPath = FileHelper::normalizePath(
                dirname($reflect->getFileName())
                . '/../templates'
            )
            . DIRECTORY_SEPARATOR;
        $id = StringHelper::toKebabCase($reflect->getShortName());

        return [$id, $classPath];
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getTransformUrl(Asset $asset, CraftImageTransformModel|string|array|null $transform): ?string
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getWebPUrl(string $url, Asset $asset, CraftImageTransformModel|string|array|null $transform): ?string
    {
        return $url;
    }

    /**
     * @inheritdoc
     */
    public function getPurgeUrl(Asset $asset): ?string
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function purgeUrl(string $url): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getAssetUri(Asset $asset): ?string
    {
        $volume = $asset->getVolume();
        $assetPath = $asset->getPath();

        // Account for volume types with a subfolder setting
        // e.g. craftcms/aws-s3, craftcms/google-cloud
        if ($volume->getFs()->subfolder ?? null) {
            $subfolder = $volume->getFs()->subfolder;
            $subfolder = Craft::parseEnv($subfolder);
            return rtrim($subfolder, '/') . '/' . $assetPath;
        }

        return $assetPath;
    }

    /**
     * @param string $url
     * @noinspection PhpComposerExtensionStubsInspection
     */
    public function prefetchRemoteFile(string $url): void
    {
        // Get an absolute URL with protocol that curl will be happy with
        $url = UrlHelper::absoluteUrlWithProtocol($url);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_NOBODY => 1,
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
    public function appendExtension($pathOrUrl, $extension): string
    {
        $path = $this->decomposeUrl($pathOrUrl);
        $path_parts = pathinfo($path['path']);
        $new_path = ($path_parts['filename'] ?? '') . '.' . ($path_parts['extension'] ?? '') . $extension;
        if (!empty($path_parts['dirname']) && $path_parts['dirname'] !== '.') {
            $dirname = $path_parts['dirname'];
            $dirname = $dirname === '/' ? '' : $dirname;
            $new_path = $dirname . DIRECTORY_SEPARATOR . $new_path;
            $new_path = preg_replace('/([^:])(\/{2,})/', '$1/', $new_path);
        }

        return $path['prefix'] . $new_path . $path['suffix'];
    }

    // Protected Methods
    // =========================================================================

    /**
     * Decompose a URL into a prefix, path, and suffix
     *
     * @param $pathOrUrl
     *
     * @return array
     */
    protected function decomposeUrl($pathOrUrl): array
    {
        $result = array();

        if (filter_var($pathOrUrl, FILTER_VALIDATE_URL)) {
            $url_parts = parse_url($pathOrUrl);
            $result['prefix'] = $url_parts['scheme'] . '://' . $url_parts['host'];
            if (!empty($url_parts['port'])) {
                $result['prefix'] .= ':' . $url_parts['port'];
            }
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
