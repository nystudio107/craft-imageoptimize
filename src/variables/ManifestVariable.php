<?php

namespace nystudio107\imageoptimize\variables;

use nystudio107\imageoptimize\ImageOptimize;

use craft\helpers\Template;

use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;

use Twig\Markup;

class ManifestVariable
{
    /**
     * Get the passed in JS modules from the manifest, and register them in the current Craft view
     *
     * @param array $modules
     *
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function registerJsModules(array $modules)
    {
        ImageOptimize::$plugin->manifest->registerJsModules($modules);
    }

    /**
     * Get the passed in CS modules from the manifest, and register them in the current Craft view
     *
     * @param array $modules
     *
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function registerCssModules(array $modules)
    {
        ImageOptimize::$plugin->manifest->registerCssModules($modules);
    }


    /**
     * Get the passed in JS module from the manifest, then output a `<script src="">` tag for it in the HTML
     *
     * @param string     $moduleName
     * @param bool       $async
     *
     * @return null|Markup
     * @throws NotFoundHttpException
     */
    public function includeJsModule(string $moduleName, bool $async = false)
    {
        return Template::raw(
            ImageOptimize::$plugin->manifest->includeJsModule($moduleName, $async) ?? ''
        );
    }

    /**
     * Get the passed in CS module from the manifest, then output a `<link>` tag for it in the HTML
     *
     * @param string     $moduleName
     * @param bool       $async
     *
     * @return Markup
     * @throws NotFoundHttpException
     */
    public function includeCssModule(string $moduleName, bool $async = false): Markup
    {
        return Template::raw(
            ImageOptimize::$plugin->manifest->includeCssModule($moduleName, $async) ?? ''
        );
    }
}
