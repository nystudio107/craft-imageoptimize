<?php

namespace nystudio107\imageoptimize\variables;

use nystudio107\imageoptimize\services\Manifest as ManifestService;

use craft\base\Component;
use craft\helpers\Template;

use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;

use Twig\Markup;

class ManifestVariable extends Component
{

    // Public Properties
    // =========================================================================

    /**
     * @var ManifestService the manifest service
     */
    public $manifestService;

    // Public Methods
    // =========================================================================

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
        $this->manifestService->registerJsModules($modules);
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
        $this->manifestService->registerCssModules($modules);
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
            $this->manifestService->includeJsModule($moduleName, $async) ?? ''
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
            $this->manifestService->includeCssModule($moduleName, $async) ?? ''
        );
    }
}
