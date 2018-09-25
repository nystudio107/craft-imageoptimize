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
        'useDevServer' => true,
        // Manifest names
        'manifest'     => [
            'legacy' => 'manifest-legacy.json',
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
        self::$config['server']['manifestPath'] = Craft::getAlias($bundle->sourcePath);
    }

    /**
     * @param string     $moduleName
     * @param bool       $async
     * @param null|array $config
     *
     * @return null|\Twig_Markup
     * @throws \yii\web\NotFoundHttpException
     */
    public function includeCssModule(string $moduleName, bool $async = false, $config = null)
    {
        return Template::raw(
            ManifestHelper::getCssModuleTags(self::$config, $moduleName, $async)
        );
    }

    /**
     * @param string     $moduleName
     * @param bool       $async
     * @param null|array $config
     *
     * @return null|\Twig_Markup
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
     * @return null|\Twig_Markup
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
     * @return null|\Twig_Markup
     */
    public function includeSafariNomoduleFix()
    {
        return Template::raw(
            ManifestHelper::getSafariNomoduleFix()
        );
    }
}
