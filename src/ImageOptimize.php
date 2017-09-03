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

use nystudio107\imageoptimize\fields\OptimizedImages;
use nystudio107\imageoptimize\models\Settings;
use nystudio107\imageoptimize\services\Optimize as OptimizeService;

use Craft;
use craft\base\Plugin;
use craft\base\Volume;
use craft\events\GenerateTransformEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\VolumeEvent;
use craft\models\FieldLayout;
use craft\services\AssetTransforms;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Volumes;

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
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = OptimizedImages::class;
            }
        );

        // Handler: Volumes::EVENT_AFTER_SAVE_VOLUME
        Event::on(
            Volumes::class,
            Volumes::EVENT_AFTER_SAVE_VOLUME,
            function (VolumeEvent $event) {
                Craft::trace(
                    'Volumes::EVENT_AFTER_SAVE_VOLUME',
                    'image-optimize'
                );
                // Only worry about this volume if it's not new
                if (!$event->isNew) {
                    $needToReSave = false;
                    /** @var Volume $volume */
                    $volume = $event->volume;
                    /** @var FieldLayout $fieldLayout */
                    $fieldLayout = $volume->getFieldLayout();
                    // Loop through the fields in the layout to see if there is an OptimizedImages field
                    if ($fieldLayout) {
                        $fields = $fieldLayout->getFields();
                        foreach ($fields as $field) {
                            if ($field instanceof OptimizedImages) {
                                $needToReSave = true;
                            }
                        }
                    }
                    if ($needToReSave) {
                        ImageOptimize::$plugin->optimize->resaveVolumeAssets($volume);
                    }
                }
            }
        );

        // Handler: AssetTransforms::EVENT_GENERATE_TRANSFORM
        Event::on(
            AssetTransforms::class,
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
