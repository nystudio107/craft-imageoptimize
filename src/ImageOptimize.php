<?php
/**
 * ImageOptimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize;

use nystudio107\imageoptimize\fields\ResponsiveAssets;
use nystudio107\imageoptimize\services\Optimize as OptimizeService;
use nystudio107\imageoptimize\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\AssetTransforms;
use craft\events\GenerateTransformEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;

use yii\base\Event;

/**
 * Class ImageOptimize
 *
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 *
 * @property OptimizeService optimize
 */
class ImageOptimize extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var ImageOptimize
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

        // Register our Field
        Event::on(
            Fields::className(),
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ResponsiveAssets::className();
            }
        );

        // Handler: AssetTransforms::EVENT_GENERATE_TRANSFORM
        Event::on(
            AssetTransforms::className(),
            AssetTransforms::EVENT_GENERATE_TRANSFORM,
            function (GenerateTransformEvent $event) {
                Craft::trace(
                    'AssetTransforms::EVENT_GENERATE_TRANSFORM',
                    'image-optimize'
                );
                // Return the path to the optimized image to _createTransformForAsset()
                $event->tempPath = ImageOptimize::$plugin->optimize->handleGenerateTransformEvent(
                    $event
                );
            }
        );

        Craft::info(
            Craft::t(
                'image-optimize',
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
        return new Settings();
    }
}
