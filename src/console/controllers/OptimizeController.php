<?php
/**
 * Image Optimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2018 nystudio107
 */

namespace nystudio107\imageoptimize\console\controllers;

use nystudio107\imageoptimize\ImageOptimize;

use Craft;
use craft\base\Volume;
use craft\helpers\App;
use craft\helpers\FileHelper;
use craft\utilities\ClearCaches;

use yii\console\Controller;

/**
 * Optimize Command
 *
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.2.0
 */
class OptimizeController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Create all of the OptimizedImages Field variants by creating all of the
     * responsive image variant transforms
     *
     * @param string|null $volumeHandle
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate($volumeHandle = null)
    {
        echo 'Creating optimized image variants'.PHP_EOL;

        if ($volumeHandle === null) {
            // Re-save all of the optimized image variants in all volumes
            ImageOptimize::$plugin->optimizedImages->resaveAllVolumesAssets();
        } else {
            // Re-save all of the optimized image variants in a specific volume
            $volumes = Craft::$app->getVolumes();
            $volume = $volumes->getVolumeByHandle($volumeHandle);
            if ($volume) {
                /** @var Volume $volume */
                ImageOptimize::$plugin->optimizedImages->resaveVolumeAssets($volume);
            } else {
                echo 'Unknown Asset Volume handle: '.$volumeHandle.PHP_EOL;
            }
        }
        // This might take a while
        App::maxPowerCaptain();
        Craft::$app->getQueue()->run();
    }

    /**
     * Clear the Asset transform index cache tables, to force the re-creation of transformed images
     */
    public function actionClear()
    {
        foreach (ClearCaches::cacheOptions() as $cacheOption) {
            if ($cacheOption['key'] !== 'transform-indexes') {
                continue;
            }

            $action = $cacheOption['action'];

            if (\is_string($action)) {
                try {
                    FileHelper::clearDirectory($action);
                } catch (\Throwable $e) {
                    Craft::warning("Could not clear the directory {$action}: ".$e->getMessage(), __METHOD__);
                }
            } elseif (isset($cacheOption['params'])) {
                \call_user_func_array($action, $cacheOption['params']);
            } else {
                $action();
            }
        }
    }
}
