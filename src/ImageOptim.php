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

use nystudio107\imageoptim\services\Optimize as OptimizeService;

use Craft;
use craft\base\Plugin;
use craft\services\AssetTransforms;
use craft\events\GenerateTransformEvent;

use yii\base\Event;

/**
 * Class ImageOptim
 *
 * @author    nystudio107
 * @package   ImageOptim
 * @since     1.0.0
 *
 * @property OptimizeService optimize
 */
class ImageOptim extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var ImageOptim
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
                // Return the path to the optimized image to _createTransformForAsset()
                $event->tempPath = ImageOptim::$plugin->optimize->handleGenerateTransformEvent(
                    $event
                );
            }
        );

        Craft::info(
            Craft::t(
                'imageoptim',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new \nystudio107\imageoptim\models\Settings();
    }
}
