<?php

namespace nystudio107\imageoptimize\variables;

use nystudio107\imageoptimize\helpers\Manifest as ManifestHelper;

class ManifestVariable
{
    /**
     * Get the passed in JS modules from the manifest, and register them in the current Craft view
     *
     * @param array $modules
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     */
    public function registerJsModules(array $modules)
    {
        ManifestHelper::registerJsModules($modules);
    }

    /**
     * Get the passed in CS modules from the manifest, and register them in the current Craft view
     *
     * @param array $modules
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     */
    public function registerCssModules(array $modules)
    {
        ManifestHelper::registerCssModules($modules);
    }

}
