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

use Craft;
use craft\helpers\Json as JsonHelper;
use craft\helpers\UrlHelper;

use yii\base\Exception;
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

    const CACHE_KEY = 'twigpack-image-optimize';
    const CACHE_TAG = 'twigpack-image-optimize';

    const DEVMODE_CACHE_DURATION = 1;

    // Protected Static Properties
    // =========================================================================

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
     * @param array  $config
     * @param string $moduleName
     * @param bool   $async
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public static function getCssModuleTags(array $config, string $moduleName, bool $async): string
    {
        $legacyModule = self::getModule($config, $moduleName, 'legacy', true);
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

    /**
     * @param string $path
     *
     * @return string
     */
    public static function getCssInlineTags(string $path): string
    {
        $result = self::getFile($path);
        if ($result) {
            $result = "<style>\r\n".$result."</style>\r\n";
            return $result;
        }

        return '';
    }

    /**
     * Returns the uglified loadCSS rel=preload Polyfill as per:
     * https://github.com/filamentgroup/loadCSS#how-to-use-loadcss-recommended-example
     *
     * @return string
     */
    public static function getCssRelPreloadPolyfill(): string
    {
        return <<<EOT
<script>
/*! loadCSS. [c]2017 Filament Group, Inc. MIT License */
!function(t){"use strict";t.loadCSS||(t.loadCSS=function(){});var e=loadCSS.relpreload={};if(e.support=function(){var e;try{e=t.document.createElement("link").relList.supports("preload")}catch(t){e=!1}return function(){return e}}(),e.bindMediaToggle=function(t){var e=t.media||"all";function a(){t.media=e}t.addEventListener?t.addEventListener("load",a):t.attachEvent&&t.attachEvent("onload",a),setTimeout(function(){t.rel="stylesheet",t.media="only x"}),setTimeout(a,3e3)},e.poly=function(){if(!e.support())for(var a=t.document.getElementsByTagName("link"),n=0;n<a.length;n++){var o=a[n];"preload"!==o.rel||"style"!==o.getAttribute("as")||o.getAttribute("data-loadcss")||(o.setAttribute("data-loadcss",!0),e.bindMediaToggle(o))}},!e.support()){e.poly();var a=t.setInterval(e.poly,500);t.addEventListener?t.addEventListener("load",function(){e.poly(),t.clearInterval(a)}):t.attachEvent&&t.attachEvent("onload",function(){e.poly(),t.clearInterval(a)})}"undefined"!=typeof exports?exports.loadCSS=loadCSS:t.loadCSS=loadCSS}("undefined"!=typeof global?global:this);
</script>
EOT;
    }

    /**
     * @param array  $config
     * @param string $moduleName
     * @param bool   $async
     *
     * @return null|string
     * @throws NotFoundHttpException
     */
    public static function getJsModuleTags(array $config, string $moduleName, bool $async)
    {
        $legacyModule = self::getModule($config, $moduleName, 'legacy');
        if ($legacyModule === null) {
            return '';
        }
        if ($async) {
            $modernModule = self::getModule($config, $moduleName, 'modern');
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
     * Safari 10.1 supports modules, but does not support the `nomodule`
     * attribute - it will load <script nomodule> anyway. This snippet solve
     * this problem, but only for script tags that load external code, e.g.:
     * <script nomodule src="nomodule.js"></script>
     *
     * Again: this will **not* # prevent inline script, e.g.:
     * <script nomodule>alert('no modules');</script>.
     *
     * This workaround is possible because Safari supports the non-standard
     * 'beforeload' event. This allows us to trap the module and nomodule load.
     *
     * Note also that `nomodule` is supported in later versions of Safari -
     * it's just 10.1 that omits this attribute.
     *
     * c.f.: https://gist.github.com/samthor/64b114e4a4f539915a95b91ffd340acc
     *
     * @return string
     */
    public static function getSafariNomoduleFix(): string
    {
        return <<<EOT
<script>
!function(){var e=document,t=e.createElement("script");if(!("noModule"in t)&&"onbeforeload"in t){var n=!1;e.addEventListener("beforeload",function(e){if(e.target===t)n=!0;else if(!e.target.hasAttribute("nomodule")||!n)return;e.preventDefault()},!0),t.type="module",t.src=".",e.head.appendChild(t),t.remove()}}();
</script>
EOT;
    }

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
    public static function getModule(array $config, string $moduleName, string $type = 'modern', bool $soft = false)
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
    public static function getModuleEntry(array $config, string $moduleName, string $type = 'modern', bool $soft = false)
    {
        $module = null;
        // Get the manifest file
        $manifest = self::getManifestFile($config, $type);
        if ($manifest !== null) {
            // Make sure it exists in the manifest
            if (empty($manifest[$moduleName])) {
                self::reportError(Craft::t(
                    'image-optimize',
                    'Module does not exist in the manifest: {moduleName}',
                    ['moduleName' => $moduleName]
                ), $soft);

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
    public static function getManifestFile(array $config, string $type = 'modern')
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
                    'image-optimize',
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
     * Returns the contents of a file from a URI path
     *
     * @param string $path
     *
     * @return string
     */
    public static function getFile(string $path): string
    {
        return self::getFileFromUri($path, null) ?? '';
    }

    /**
     * @param array  $config
     * @param string $fileName
     * @param string $type
     *
     * @return string
     */
    public static function getFileFromManifest(array $config, string $fileName, string $type = 'legacy'): string
    {
        try {
            $path = self::getModuleEntry($config, $fileName, $type, true);
        } catch (NotFoundHttpException $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }
        if ($path !== null) {
            $path = self::combinePaths(
                $config['localFiles']['basePath'],
                $path
            );

            return self::getFileFromUri($path, null) ?? '';
        }

        return '';
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
        return self::getFileFromUri($path, [self::class, 'jsonFileDecode']);
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

    // Protected Static Methods
    // =========================================================================

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
            $path = $alias;
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
        $last_key = \count($paths) - 1;
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

    // Private Static Methods
    // =========================================================================

    /**
     * @param $string
     *
     * @return mixed
     */
    private static function jsonFileDecode($string)
    {
        return JsonHelper::decodeIfJson($string);
    }
}
