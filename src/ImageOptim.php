<?php
/**
 * ImageOptim plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptim;


use Craft;
use craft\base\Plugin;
use craft\services\AssetTransforms;
use craft\events\GenerateTransformEvent;

use yii\base\Event;

/**
 * @author    nystudio107
 * @package   ImageOptim
 * @since     1.0.0
 */
class ImageOptim extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var static
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Listen for image transform events
        Event::on(
            AssetTransforms::className(),
            AssetTransforms::EVENT_GENERATE_TRANSFORM,
            function (GenerateTransformEvent $event) {
                // Save the transformed image to a temp file
                $tempPath = ImageOptim::$plugin->optimize->saveTransformToTempFile(
                    $event->transformIndex,
                    $event->image
                );
                // Optimize the image
                ImageOptim::$plugin->optimize->optimizeImage(
                    $event->transformIndex,
                    $tempPath
                );
                // Return the path to the optimized image to _createTransformForAsset()
                $event->tempPath = $tempPath;
            }
        );

        Craft::info('ImageOptim ' . Craft::t('imageoptim', 'plugin loaded'), __METHOD__);
    }

    // Protected Methods
    // =========================================================================

}
