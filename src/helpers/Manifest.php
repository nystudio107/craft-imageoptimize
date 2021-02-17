<?php
/**
 * Twigpack plugin for Craft CMS 3.x
 *
 * Twigpack is the conduit between Twig and webpack, with manifest.json &
 * webpack-dev-server HMR support
 *
 * @link      https://nystudio107.com/
 * @copyright Copyright (c) 2018 nystudio107
 */

namespace nystudio107\imageoptimize\helpers;

use nystudio107\imageoptimize\assetbundles\imageoptimize\ImageOptimizeAsset;

use Craft;
use craft\helpers\Json as JsonHelper;
use craft\helpers\UrlHelper;

use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\caching\TagDependency;
use yii\web\NotFoundHttpException;

/**
 * @author    nystudio107
 * @package   Twigpack
 * @since     1.0.0
 */
class Manifest
{
    // Constants
    // =========================================================================

    const ASSET_CLASS = ImageOptimizeAsset::class;

    const CACHE_KEY = 'twigpack-' . self::ASSET_CLASS;
    const CACHE_TAG = 'twigpack-' . self::ASSET_CLASS;

    const DEVMODE_CACHE_DURATION = 1;

    const SUPPRESS_ERRORS_FOR_MODULES = [
        'styles.js',
        'commons.js',
        'vendors.js',
        'vendors.css',
        'styles.css',
    ];

    // Protected Static Properties
    // =========================================================================

    protected static $config = [
        // If `devMode` is on, use webpack-dev-server to all for HMR (hot module reloading)
        'useDevServer' => false,
        // Manifest names
        'manifest' => [
            'legacy' => 'manifest.json',
            'modern' => 'manifest.json',
        ],
        // Public server config
        'server' => [
            'manifestPath' => '/',
            'publicPath' => '/',
        ],
        // webpack-dev-server config
        'devServer' => [
            'manifestPath' => 'http://127.0.0.1:8080',
            'publicPath' => '/',
        ],
    ];

    /**
     * @var array
     */
    protected static $files;

    /**
     * @var bool
     */
    protected static $isHot = false;

    // Public Static Methods
    // =========================================================================

    /**
     * Simulate a static constructor
     *
     * ManifestVariable constructor.
     * @noinspection MagicMethodsValidityInspection
     */
    public static function __constructStatic()
    {
        self::invalidateCaches();
        $assetClass = self::ASSET_CLASS;
        $bundle = new $assetClass;
        $baseAssetsUrl = Craft::$app->assetManager->getPublishedUrl(
            $bundle->sourcePath,
            true
        );
        self::$config['server']['manifestPath'] = Craft::getAlias($bundle->sourcePath);
        self::$config['server']['publicPath'] = $baseAssetsUrl;
        $useDevServer = getenv('NYS_PLUGIN_DEVSERVER');
        if ($useDevServer !== false) {
            self::$config['useDevServer'] = (bool)$useDevServer;
        }
    }

    /**
     * Get the passed in JS modules from the manifest, and register them in the current Craft view
     *
     * @param array $modules
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    public static function registerJsModules(array $modules)
    {
        $view = Craft::$app->getView();
        foreach($modules as $module) {
            $jsModule = self::getModule(self::$config, $module, 'modern');
            if ($jsModule) {
                $view->registerJsFile($jsModule, [
                    'depends' => self::ASSET_CLASS
                ]);
            }
        }
    }

    /**
     * Get the passed in CS modules from the manifest, and register them in the current Craft view
     *
     * @param array $modules
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    public static function registerCssModules(array $modules)
    {
        $view = Craft::$app->getView();
        foreach($modules as $module) {
            $cssModule = self::getModule(self::$config, $module, 'legacy');
            if ($cssModule) {
                $view->registerCssFile($cssModule, [
                    'depends' => self::ASSET_CLASS
                ]);
            }
        }
    }

    // Protected Static Methods
    // =========================================================================

    /**
     * Return the URI to a module
     *
     * @param array  $config
     * @param string $moduleName
     * @param string $type
     * @param bool   $soft
     *
     * @return null|string
     * @throws NotFoundHttpException
     */
    protected static function getModule(array $config, string $moduleName, string $type = 'modern', bool $soft = false)
    {
        // Get the module entry
        $module = self::getModuleEntry($config, $moduleName, $type, $soft);
        if ($module !== null) {
            $prefix = self::$isHot
                ? $config['devServer']['publicPath']
                : $config['server']['publicPath'];
            // If the module isn't a full URL, prefix it
            if (!UrlHelper::isAbsoluteUrl($module)) {
                $module = self::combinePaths($prefix, $module);
            }
            // Resolve any aliases
            $alias = Craft::getAlias($module, false);
            if ($alias) {
                $module = $alias;
            }
            // Make sure it's a full URL
            if (!UrlHelper::isAbsoluteUrl($module) && !is_file($module)) {
                try {
                    $module = UrlHelper::siteUrl($module);
                } catch (Exception $e) {
                    Craft::error($e->getMessage(), __METHOD__);
                }
            }
        }

        return $module;
    }

    /**
     * Return a module's raw entry from the manifest
     *
     * @param array  $config
     * @param string $moduleName
     * @param string $type
     * @param bool   $soft
     *
     * @return null|string
     * @throws NotFoundHttpException
     */
    protected static function getModuleEntry(array $config, string $moduleName, string $type = 'modern', bool $soft = false)
    {
        $module = null;
        // Get the manifest file
        $manifest = self::getManifestFile($config, $type);
        if ($manifest !== null) {
            // Make sure it exists in the manifest
            if (empty($manifest[$moduleName])) {
                // Don't report errors for any files in SUPPRESS_ERRORS_FOR_MODULES
                if (!in_array($moduleName, self::SUPPRESS_ERRORS_FOR_MODULES)) {
                    self::reportError(Craft::t(
                        'instant-analytics',
                        'Module does not exist in the manifest: {moduleName}',
                        ['moduleName' => $moduleName]
                    ), $soft);
                }

                return null;
            }
            $module = $manifest[$moduleName];
        }

        return $module;
    }

    /**
     * Return a JSON-decoded manifest file
     *
     * @param array  $config
     * @param string $type
     *
     * @return null|array
     * @throws NotFoundHttpException
     */
    protected static function getManifestFile(array $config, string $type = 'modern')
    {
        $manifest = null;
        // Determine whether we should use the devServer for HMR or not
        $devMode = Craft::$app->getConfig()->getGeneral()->devMode;
        self::$isHot = ($devMode && $config['useDevServer']);
        // Try to get the manifest
        while ($manifest === null) {
            $manifestPath = self::$isHot
                ? $config['devServer']['manifestPath']
                : $config['server']['manifestPath'];
            // Normalize the path
            $path = self::combinePaths($manifestPath, $config['manifest'][$type]);
            $manifest = self::getJsonFile($path);
            // If the manifest isn't found, and it was hot, fall back on non-hot
            if ($manifest === null) {
                // We couldn't find a manifest; throw an error
                self::reportError(Craft::t(
                    'instant-analytics',
                    'Manifest file not found at: {manifestPath}',
                    ['manifestPath' => $manifestPath]
                ), true);
                if (self::$isHot) {
                    // Try again, but not with home module replacement
                    self::$isHot = false;
                } else {
                    // Give up and return null
                    return null;
                }
            }
        }

        return $manifest;
    }

    /**
     * Return the contents of a JSON file from a URI path
     *
     * @param string $path
     *
     * @return null|array
     */
    protected static function getJsonFile(string $path)
    {
        return self::getFileFromUri($path, [JsonHelper::class, 'decodeIfJson']);
    }

    /**
     * Invalidate all of the manifest caches
     */
    public static function invalidateCaches()
    {
        $cache = Craft::$app->getCache();
        TagDependency::invalidate($cache, self::CACHE_TAG);
        Craft::info('All manifest caches cleared', __METHOD__);
    }

    /**
     * Return the contents of a file from a URI path
     *
     * @param string        $path
     * @param callable|null $callback
     *
     * @return null|mixed
     */
    protected static function getFileFromUri(string $path, callable $callback = null)
    {
        // Resolve any aliases
        $alias = Craft::getAlias($path, false);
        if ($alias) {
            $path = (string)$alias;
        }
        // Make sure it's a full URL
        if (!UrlHelper::isAbsoluteUrl($path) && !is_file($path)) {
            try {
                $path = UrlHelper::siteUrl($path);
            } catch (Exception $e) {
                Craft::error($e->getMessage(), __METHOD__);
            }
        }

        return self::getFileContents($path, $callback);
    }

    /**
     * Return the contents of a file from the passed in path
     *
     * @param string   $path
     * @param callable $callback
     *
     * @return null|mixed
     */
    protected static function getFileContents(string $path, callable $callback = null)
    {
        // Return the memoized manifest if it exists
        if (!empty(self::$files[$path])) {
            return self::$files[$path];
        }
        // Create the dependency tags
        $dependency = new TagDependency([
            'tags' => [
                self::CACHE_TAG,
                self::CACHE_TAG.$path,
            ],
        ]);
        // Set the cache duration based on devMode
        $cacheDuration = Craft::$app->getConfig()->getGeneral()->devMode
            ? self::DEVMODE_CACHE_DURATION
            : null;
        // Get the result from the cache, or parse the file
        $cache = Craft::$app->getCache();
        $file = $cache->getOrSet(
            self::CACHE_KEY.$path,
            function () use ($path, $callback) {
                $result = null;
                $contents = @file_get_contents($path);
                if ($contents) {
                    $result = $contents;
                    if ($callback) {
                        $result = $callback($result);
                    }
                }

                return $result;
            },
            $cacheDuration,
            $dependency
        );
        self::$files[$path] = $file;

        return $file;
    }

    /**
     * Combined the passed in paths, whether file system or URL
     *
     * @param string ...$paths
     *
     * @return string
     */
    protected static function combinePaths(string ...$paths): string
    {
        $last_key = count($paths) - 1;
        array_walk($paths, function (&$val, $key) use ($last_key) {
            switch ($key) {
                case 0:
                    $val = rtrim($val, '/ ');
                    break;
                case $last_key:
                    $val = ltrim($val, '/ ');
                    break;
                default:
                    $val = trim($val, '/ ');
                    break;
            }
        });

        $first = array_shift($paths);
        $last = array_pop($paths);
        $paths = array_filter($paths);
        array_unshift($paths, $first);
        $paths[] = $last;

        return implode('/', $paths);
    }

    /**
     * @param string $error
     * @param bool   $soft
     *
     * @throws NotFoundHttpException
     */
    protected static function reportError(string $error, $soft = false)
    {
        $devMode = Craft::$app->getConfig()->getGeneral()->devMode;
        if ($devMode && !$soft) {
            throw new NotFoundHttpException($error);
        }
        Craft::error($error, __METHOD__);
    }
}

// Simulate a static constructor
Manifest::__constructStatic();
