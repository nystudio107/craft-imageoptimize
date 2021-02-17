<?php

namespace nystudio107\imageoptimize\variables;

use nystudio107\imageoptimize\helpers\Manifest as ManifestHelper;

use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;

class ManifestVariable
{
    /**
     * Get the passed in JS modules from the manifest, and register them in the current Craft view
     *
     * @param array $modules
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function registerJsModules(array $modules)
    {
        ManifestHelper::registerJsModules($modules);
    }

    /**
     * Get the passed in CS modules from the manifest, and register them in the current Craft view
     *
     * @param array $modules
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function registerCssModules(array $modules)
    {
        ManifestHelper::registerCssModules($modules);
    }

}
