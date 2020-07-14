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
use craft\queue\QueueInterface;

use yii\console\Controller;
use yii\queue\redis\Queue as RedisQueue;

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
        $this->runCraftQueue();
    }

    /**
     * Create a single OptimizedImage for the passed in Asset ID
     *
     * @param int|null $id
     */
    public function actionCreateAsset($id = null)
    {
        echo 'Creating optimized image variants'.PHP_EOL;

        if ($id === null) {
            echo 'No Asset ID specified'.PHP_EOL;
        } else {
            // Re-save a single Asset ID
            ImageOptimize::$plugin->optimizedImages->resaveAsset($id);
        }
        $this->runCraftQueue();
    }

    /**
     *
     */
    private function runCraftQueue()
    {
        // This might take a while
        App::maxPowerCaptain();
        $queue = Craft::$app->getQueue();
        if ($queue instanceof QueueInterface) {
            $queue->run();
        } elseif ($queue instanceof RedisQueue) {
            $queue->run(false);
        }
    }
}
