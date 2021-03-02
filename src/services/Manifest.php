<?php
/**
 * Twigpack plugin for Craft CMS 3.x
 *
 * Twigpack is the conduit between Twig and webpack, with manifest.json &
 * webpack-dev-server HMR support
 *
 * @link      https://nystudio107.com/
 * @copyright Copyright (c) 2021 nystudio107
 */

namespace nystudio107\imageoptimize\services;

use Craft;
use craft\base\Component;
use craft\helpers\Json as JsonHelper;
use craft\helpers\UrlHelper;

use craft\web\AssetBundle;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\caching\TagDependency;
use yii\web\NotFoundHttpException;

/**
 * @author    nystudio107
 * @package   Twigpack
 * @since     1.0.0
 */
class Manifest extends Component
{
    // Constants
    // =========================================================================

    const CACHE_KEY = 'twigpack-';
    const CACHE_TAG = 'twigpack-';

    const DEVMODE_CACHE_DURATION = 1;

    const SUPPRESS_ERRORS_FOR_MODULES = [
        'styles.js',
        'commons.js',
        'vendors.js',
        'vendors.css',
        'styles.css',
    ];

    // Public Properties
    // =========================================================================

    /**
     * @var AssetBundle Asset bundle to get the published URLs from
     */
    public $assetClass;

    /**
     * @var bool Whether the devServer should be used, set based on `NYS_PLUGIN_DEVSERVER` env var
     */
    public $useDevServer = false;

    /**
     * @var string Name of the legacy manifest file
     */
    public $manifestLegacy = 'manifest.json';

    /**
     * @var string Name of the modern manifest file
     */
    public $manifestModern = 'manifest.json';

    /**
     * @var string The normal server manifest path
     */
    public $serverManifestPath = '/';

    /**
     * @var string The normal server public path
     */
    public $serverPublicPath = '/';

    /**
     * @var string The dev server manifest path
     */
    public $devServerManifestPath = '';

    /**
     * @var string The dev server public path
     */
    public $devServerPublicPath = '';

    // Protected Properties
    // =========================================================================

    /**
     * @var array
     */
    protected $files;

    /**
     * @var bool
     */
    protected $isHot = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->invalidateCaches();
        $bundle = new $this->assetClass;
        $baseAssetsUrl = Craft::$app->assetManager->getPublishedUrl(
            $bundle->sourcePath,
            true
        );
        $this->serverManifestPath = Craft::getAlias($bundle->sourcePath);
        $this->serverPublicPath = $baseAssetsUrl;
        $useDevServer = getenv('NYS_PLUGIN_DEVSERVER');
        if ($useDevServer !== false) {
            $this->useDevServer = (bool)$useDevServer;
        }
    }

    /**
     * Get the passed in JS modules from the manifest, and register them in the current Craft view
     *
     * @param array $modules
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    public function registerJsModules(array $modules)
    {
        $view = Craft::$app->getView();
        foreach($modules as $module) {
            $jsModule = $this->getModule($module, 'modern');
            if ($jsModule) {
                $view->registerJsFile($jsModule, [
                    'depends' => $this->assetClass,
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
    public function registerCssModules(array $modules)
    {
        $view = Craft::$app->getView();
        foreach($modules as $module) {
            $cssModule = $this->getModule($module, 'legacy');
            if ($cssModule) {
                $view->registerCssFile($cssModule, [
                    'depends' => $this->assetClass,
                ]);
            }
        }
    }

    /**
     * Get the passed in JS module from the manifest, then output a `<script src="">` tag for it in the HTML
     *
     * @param string $moduleName
     * @param bool   $async
     *
     * @return null|string
     * @throws NotFoundHttpException
     */
    public function includeJsModule(string $moduleName, bool $async)
    {
        $legacyModule = $this->getModule($moduleName, 'legacy');
        if ($legacyModule === null) {
            return '';
        }
        if ($async) {
            $modernModule = $this->getModule($moduleName, 'modern');
            if ($modernModule === null) {
                return '';
            }
        }
        $lines = [];
        if ($async) {
            $lines[] = "<script type=\"module\" src=\"{$modernModule}\"></script>";
            $lines[] = "<script nomodule src=\"{$legacyModule}\"></script>";
        } else {
            $lines[] = "<script src=\"{$legacyModule}\"></script>";
        }

        return implode("\r\n", $lines);
    }

    /**
     * Get the passed in CS module from the manifest, then output a `<link>` tag for it in the HTML
     *
     * @param string $moduleName
     * @param bool   $async
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function includeCssModule(string $moduleName, bool $async): string
    {
        $legacyModule = $this->getModule($moduleName, 'legacy', true);
        if ($legacyModule === null) {
            return '';
        }
        $lines = [];
        if ($async) {
            $lines[] = "<link rel=\"preload\" href=\"{$legacyModule}\" as=\"style\" onload=\"this.onload=null;this.rel='stylesheet'\" />";
            $lines[] = "<noscript><link rel=\"stylesheet\" href=\"{$legacyModule}\"></noscript>";
        } else {
            $lines[] = "<link rel=\"stylesheet\" href=\"{$legacyModule}\" />";
        }

        return implode("\r\n", $lines);
    }

    // Protected Static Methods
    // =========================================================================

    /**
     * Return the URI to a module
     *
     * @param string $moduleName
     * @param string $type
     * @param bool   $soft
     *
     * @return null|string
     * @throws NotFoundHttpException
     */
    protected function getModule(string $moduleName, string $type = 'modern', bool $soft = false)
    {
        // Get the module entry
        $module = $this->getModuleEntry($moduleName, $type, $soft);
        if ($module !== null) {
            $prefix = $this->isHot
                ? $this->devServerPublicPath
                : $this->serverPublicPath;
            // If the module isn't a full URL, prefix it
            if (!UrlHelper::isAbsoluteUrl($module)) {
                $module = $this->combinePaths($prefix, $module);
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
     * @param string $moduleName
     * @param string $type
     * @param bool   $soft
     *
     * @return null|string
     * @throws NotFoundHttpException
     */
    protected function getModuleEntry(string $moduleName, string $type = 'modern', bool $soft = false)
    {
        $module = null;
        // Get the manifest file
        $manifest = $this->getManifestFile($type);
        if ($manifest !== null) {
            // Make sure it exists in the manifest
            if (empty($manifest[$moduleName])) {
                // Don't report errors for any files in SUPPRESS_ERRORS_FOR_MODULES
                if (!in_array($moduleName, self::SUPPRESS_ERRORS_FOR_MODULES)) {
                    $this->reportError(Craft::t(
                        'image-optimize',
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
     * @param string $type
     *
     * @return null|array
     * @throws NotFoundHttpException
     */
    protected function getManifestFile(string $type = 'modern')
    {
        $manifest = null;
        // Determine whether we should use the devServer for HMR or not
        $devMode = Craft::$app->getConfig()->getGeneral()->devMode;
        $this->isHot = ($devMode && $this->useDevServer);
        // Try to get the manifest
        while ($manifest === null) {
            $manifestPath = $this->isHot
                ? $this->devServerManifestPath
                : $this->serverManifestPath;
            // Normalize the path
            $manifestType = 'manifest' . ucfirst($type);
            $path = $this->combinePaths($manifestPath, $this->$manifestType);
            $manifest = $this->getJsonFile($path);
            // If the manifest isn't found, and it was hot, fall back on non-hot
            if ($manifest === null) {
                // We couldn't find a manifest; throw an error
                $this->reportError(Craft::t(
                    'image-optimize',
                    'Manifest file not found at: {manifestPath}',
                    ['manifestPath' => $manifestPath]
                ), true);
                if ($this->isHot) {
                    // Try again, but not with home module replacement
                    $this->isHot = false;
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
    protected function getJsonFile(string $path)
    {
        return $this->getFileFromUri($path, [JsonHelper::class, 'decodeIfJson']);
    }

    /**
     * Invalidate all of the manifest caches
     */
    public function invalidateCaches()
    {
        $cache = Craft::$app->getCache();
        TagDependency::invalidate($cache, self::CACHE_TAG . $this->assetClass);
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
    protected function getFileFromUri(string $path, callable $callback = null)
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

        return $this->getFileContents($path, $callback);
    }

    /**
     * Return the contents of a file from the passed in path
     *
     * @param string   $path
     * @param callable $callback
     *
     * @return null|mixed
     */
    protected function getFileContents(string $path, callable $callback = null)
    {
        // Return the memoized manifest if it exists
        if (!empty($this->files[$path])) {
            return $this->files[$path];
        }
        // Create the dependency tags
        $dependency = new TagDependency([
            'tags' => [
                self::CACHE_TAG . $this->assetClass,
                self::CACHE_TAG . $this->assetClass . $path,
            ],
        ]);
        // Set the cache duration based on devMode
        $cacheDuration = Craft::$app->getConfig()->getGeneral()->devMode
            ? self::DEVMODE_CACHE_DURATION
            : null;
        // Get the result from the cache, or parse the file
        $cache = Craft::$app->getCache();
        $file = $cache->getOrSet(
            self::CACHE_KEY . $this->assetClass . $path,
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
        $this->files[$path] = $file;

        return $file;
    }

    /**
     * Combined the passed in paths, whether file system or URL
     *
     * @param string ...$paths
     *
     * @return string
     */
    protected function combinePaths(string ...$paths): string
    {
        $lastKey = count($paths) - 1;
        array_walk($paths, static function (&$val, $key) use ($lastKey) {
            switch ($key) {
                case 0:
                    $val = rtrim($val, '/ ');
                    break;
                case $lastKey:
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
    protected function reportError(string $error, $soft = false)
    {
        $devMode = Craft::$app->getConfig()->getGeneral()->devMode;
        if ($devMode && !$soft) {
            throw new NotFoundHttpException($error);
        }
        Craft::error($error, __METHOD__);
    }
}
