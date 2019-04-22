<?php

namespace nystudio107\imageoptimize\variables;

use nystudio107\imageoptimize\helpers\Manifest as ManifestHelper;
use nystudio107\imageoptimize\assetbundles\imageoptimize\ImageOptimizeAsset;

use Craft;
use craft\helpers\Template;

class ManifestVariable
{
    // Protected Static Properties
    // =========================================================================

    protected static $config = [
        // If `devMode` is on, use webpack-dev-server to all for HMR (hot module reloading)
        'useDevServer' => false,
        // Manifest names
        'manifest'     => [
            'legacy' => 'manifest.json',
            'modern' => 'manifest.json',
        ],
        // Public server config
        'server'       => [
            'manifestPath' => '/',
            'publicPath' => '/',
        ],
        // webpack-dev-server config
        'devServer'    => [
            'manifestPath' => 'http://127.0.0.1:8080',
            'publicPath' => '/',
        ],
    ];

    // Public Methods
    // =========================================================================

    /**
     * ManifestVariable constructor.
     */
    public function __construct()
    {
        ManifestHelper::invalidateCaches();
        $bundle = new ImageOptimizeAsset();
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
     * @param string     $moduleName
     * @param bool       $async
     * @param null|array $config
     *
     * @return \Twig\Markup
     * @throws \yii\web\NotFoundHttpException
     */
    public function includeCssModule(string $moduleName, bool $async = false, $config = null): \Twig\Markup
    {
        return Template::raw(
            ManifestHelper::getCssModuleTags(self::$config, $moduleName, $async)
        );
    }

    /**
     * Returns the CSS file in $path wrapped in <style></style> tags
     *
     * @param string $path
     *
     * @return \Twig\Markup
     */
    public function includeInlineCssTags(string $path): \Twig\Markup
    {
        return Template::raw(
            ManifestHelper::getCssInlineTags($path)
        );
    }

    /**
     * Returns the uglified loadCSS rel=preload Polyfill as per:
     * https://github.com/filamentgroup/loadCSS#how-to-use-loadcss-recommended-example
     *
     * @return \Twig\Markup
     */
    public static function includeCssRelPreloadPolyfill(): \Twig\Markup
    {
        return Template::raw(
            ManifestHelper::getCssRelPreloadPolyfill()
        );
    }

    /**
     * @param string     $moduleName
     * @param bool       $async
     * @param null|array $config
     *
     * @return null|\Twig\Markup
     * @throws \yii\web\NotFoundHttpException
     */
    public function includeJsModule(string $moduleName, bool $async = false, $config = null)
    {
        return Template::raw(
            ManifestHelper::getJsModuleTags(self::$config, $moduleName, $async)
        );
    }

    /**
     * Return the URI to a module
     *
     * @param string $moduleName
     * @param string $type
     * @param null   $config
     *
     * @return null|\Twig\Markup
     * @throws \yii\web\NotFoundHttpException
     */
    public function getModuleUri(string $moduleName, string $type = 'modern', $config = null)
    {
        return Template::raw(
            ManifestHelper::getModule(self::$config, $moduleName, $type)
        );
    }

    /**
     * Include the Safari 10.1 nomodule fix JavaScript
     *
     * @return \Twig\Markup
     */
    public function includeSafariNomoduleFix(): \Twig\Markup
    {
        return Template::raw(
            ManifestHelper::getSafariNomoduleFix()
        );
    }

    /**
     * Returns the contents of a file from a URI path
     *
     * @param string $path
     *
     * @return \Twig\Markup
     */
    public function includeFile(string $path): \Twig\Markup
    {
        return Template::raw(
            ManifestHelper::getFile($path)
        );
    }

    /**
     * Returns the contents of a file from the $fileName in the manifest
     *
     * @param string $fileName
     * @param string $type
     * @param null   $config
     *
     * @return \Twig\Markup
     */
    public function includeFileFromManifest(string $fileName, string $type = 'legacy', $config = null): \Twig\Markup
    {
        return Template::raw(
            ManifestHelper::getFileFromManifest($config, $fileName, $type)
        );
    }
}
