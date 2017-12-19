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
use nystudio107\imageoptimize\imagetransforms\ImageTransformInterface;
use nystudio107\imageoptimize\models\Settings;
use nystudio107\imageoptimize\services\Optimize as OptimizeService;
use nystudio107\imageoptimize\services\OptimizedImages as OptimizedImagesService;
use nystudio107\imageoptimize\services\Placeholder as PlaceholderService;
use nystudio107\imageoptimize\variables\ImageOptimizeVariable;

use Craft;
use craft\base\Element;
use craft\base\Field;
use craft\base\Plugin;
use craft\base\Volume;
use craft\elements\Asset;
use craft\events\AssetTransformImageEvent;
use craft\events\ElementEvent;
use craft\events\FieldEvent;
use craft\events\GetAssetUrlEvent;
use craft\events\GenerateTransformEvent;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\ReplaceAssetEvent;
use craft\events\VolumeEvent;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use craft\services\Assets;
use craft\services\AssetTransforms;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Plugins;
use craft\services\Volumes;
use craft\web\twig\variables\CraftVariable;
use craft\web\Controller;

use yii\base\Event;

/** @noinspection MissingPropertyAnnotationsInspection */

/**
 * Class ImageOptimize
 *
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 *
 * @property OptimizeService        optimize
 * @property PlaceholderService     placeholder
 * @property OptimizedImagesService optimizedImages
 * @property Settings               $settings
 * @method   Settings               getSettings()
 */
class ImageOptimize extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var ImageOptimize
     */
    public static $plugin;

    /**
     * @var ImageTransformInterface
     */
    public static $transformClass;

    /**
     * @var array
     */
    public static $transformParams;

    /**
     * @var string
     */
    public static $previousTransformMethod = null;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Cache some settings
        $settings = $this->getSettings();
        self::$previousTransformMethod = $settings->transformMethod;
        self::$transformClass = ImageTransformInterface::IMAGE_TRANSFORM_MAP[$settings->transformMethod];
        self::$transformParams = self::$transformClass::getTransformParams();

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('imageOptimize', ImageOptimizeVariable::class);
            }
        );

        // Register our Field
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                Craft::trace(
                    'Fields::EVENT_REGISTER_FIELD_TYPES',
                    __METHOD__
                );
                $event->types[] = OptimizedImages::class;
            }
        );

        // Handler: Fields::EVENT_AFTER_SAVE_FIELD
        Event::on(
            Fields::class,
            Fields::EVENT_AFTER_SAVE_FIELD,
            function (FieldEvent $event) {
                Craft::trace(
                    'Fields::EVENT_AFTER_SAVE_FIELD',
                    'image-optimize'
                );
                /** @var Field $field */
                if (!$event->isNew) {
                    $thisField = $event->field;
                    if ($thisField instanceof OptimizedImages) {
                        $volumes = Craft::$app->getVolumes()->getAllVolumes();
                        foreach ($volumes as $volume) {
                            $needToReSave = false;
                            /** @var FieldLayout $fieldLayout */
                            /** @var Volume $volume */
                            $fieldLayout = $volume->getFieldLayout();
                            // Loop through the fields in the layout to see if it contains our field
                            if ($fieldLayout) {
                                $fields = $fieldLayout->getFields();
                                foreach ($fields as $field) {
                                    if ($thisField->handle == $field->handle) {
                                        $needToReSave = true;
                                    }
                                }
                                if ($needToReSave) {
                                    ImageOptimize::$plugin->optimize->resaveVolumeAssets($volume);
                                }
                            }
                        }
                    }
                }
            }
        );

        // Handler: Plugins::EVENT_AFTER_SAVE_PLUGIN_SETTINGS
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_SAVE_PLUGIN_SETTINGS,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    Craft::trace(
                        'Plugins::EVENT_AFTER_SAVE_PLUGIN_SETTINGS',
                        __METHOD__
                    );
                    $settings = self::getSettings();
                    // If they changed the global transform method, we need to resave all Asset Volumes
                    if (self::$previousTransformMethod != $settings->transformMethod) {
                        self::$previousTransformMethod = $settings->transformMethod;
                        self::$transformClass = ImageTransformInterface::IMAGE_TRANSFORM_MAP[$settings->transformMethod];
                        self::$transformParams = self::$transformClass::getTransformParams();
                        ImageOptimize::$plugin->optimize->resaveAllVolumesAssets();
                    }
                }
            }
        );

        // Handler: Volumes::EVENT_AFTER_SAVE_VOLUME
        Event::on(
            Volumes::class,
            Volumes::EVENT_AFTER_SAVE_VOLUME,
            function (VolumeEvent $event) {
                Craft::trace(
                    'Volumes::EVENT_AFTER_SAVE_VOLUME',
                    __METHOD__
                );
                // Only worry about this volume if it's not new
                if (!$event->isNew) {
                    /** @var Volume $volume */
                    $volume = $event->volume;
                    if (is_subclass_of($volume, Volume::class)) {
                        ImageOptimize::$plugin->optimize->resaveVolumeAssets($volume);
                    }
                }
            }
        );

        // Handler: Assets::EVENT_GET_ASSET_URL
        Event::on(
            Assets::class,
            Assets::EVENT_GET_ASSET_URL,
            function (GetAssetUrlEvent $event) {
                Craft::trace(
                    'Assets::EVENT_GET_ASSET_URL',
                    __METHOD__
                );
                // Return the URL to the asset URL or null to let Craft handle it
                $event->url = ImageOptimize::$plugin->optimize->handleGetAssetUrlEvent(
                    $event
                );
            }
        );

        // Handler: AssetTransforms::EVENT_GENERATE_TRANSFORM
        Event::on(
            AssetTransforms::class,
            AssetTransforms::EVENT_GENERATE_TRANSFORM,
            function (GenerateTransformEvent $event) {
                Craft::trace(
                    'AssetTransforms::EVENT_GENERATE_TRANSFORM',
                    __METHOD__
                );
                // Return the path to the optimized image to _createTransformForAsset()
                $event->tempPath = ImageOptimize::$plugin->optimize->handleGenerateTransformEvent(
                    $event
                );
            }
        );

        // Handler: AssetTransforms::EVENT_AFTER_DELETE_TRANSFORMS
        Event::on(
            AssetTransforms::class,
            AssetTransforms::EVENT_AFTER_DELETE_TRANSFORMS,
            function (AssetTransformImageEvent $event) {
                Craft::trace(
                    'AssetTransforms::EVENT_AFTER_DELETE_TRANSFORMS',
                    __METHOD__
                );
                // Clean up any stray variant files
                ImageOptimize::$plugin->optimize->handleAfterDeleteTransformsEvent(
                    $event
                );
            }
        );

        // Handler: Elements::EVENT_BEFORE_SAVE_ELEMENT
        Event::on(
            Elements::class,
            Elements::EVENT_BEFORE_SAVE_ELEMENT,
            function (ElementEvent $event) {
                Craft::trace(
                    'Elements::EVENT_BEFORE_SAVE_ELEMENT',
                    __METHOD__
                );
                /** @var Element $element */
                $element = $event->element;
                $isNewElement = $event->isNew;
                if (($element instanceof Asset) && (!$isNewElement)) {
                    // Purge the URL
                    $purgeUrl = ImageOptimize::$transformClass::getPurgeUrl(
                        $element,
                        ImageOptimize::$transformParams
                    );
                    if ($purgeUrl) {
                        ImageOptimize::$transformClass::purgeUrl($purgeUrl, ImageOptimize::$transformParams);
                    }
                }
            }
        );

        // Handler: Elements::EVENT_BEFORE_DELETE_ELEMENT
        Event::on(
            Elements::class,
            Elements::EVENT_BEFORE_DELETE_ELEMENT,
            function (ElementEvent $event) {
                Craft::trace(
                    'Elements::EVENT_BEFORE_DELETE_ELEMENT',
                    __METHOD__
                );
                /** @var Element $element */
                $element = $event->element;
                if ($element instanceof Asset) {
                    // Purge the URL
                    $purgeUrl = ImageOptimize::$transformClass::getPurgeUrl(
                        $element,
                        ImageOptimize::$transformParams
                    );
                    if ($purgeUrl) {
                        ImageOptimize::$transformClass::purgeUrl($purgeUrl, ImageOptimize::$transformParams);
                    }
                }
            }
        );

        // Handler: Assets::EVENT_BEFORE_REPLACE_ASSET
        Event::on(
            Assets::class,
            Assets::EVENT_BEFORE_REPLACE_ASSET,
            function (ReplaceAssetEvent $event) {
                Craft::trace(
                    'Assets::EVENT_BEFORE_REPLACE_ASSET',
                    __METHOD__
                );
                /** @var Asset $element */
                $element = $event->asset;
                // Purge the URL
                $purgeUrl = ImageOptimize::$transformClass::getPurgeUrl(
                    $element,
                    ImageOptimize::$transformParams
                );
                if ($purgeUrl) {
                    ImageOptimize::$transformClass::purgeUrl($purgeUrl, ImageOptimize::$transformParams);
                }
            }
        );

        // Handler: Elements::EVENT_AFTER_REPLACE_ASSET
        Event::on(
            Assets::class,
            Assets::EVENT_AFTER_REPLACE_ASSET,
            function (ReplaceAssetEvent $event) {
                Craft::trace(
                    'Assets::EVENT_AFTER_REPLACE_ASSET',
                    __METHOD__
                );
                /** @var Asset $element */
                $element = $event->asset;
                ImageOptimize::$plugin->optimize->resaveAsset($element->id);
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    $request = Craft::$app->getRequest();
                    if (($request->isCpRequest) && (!$request->isConsoleRequest)) {
                        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('image-optimize/welcome'))->send();
                    }
                }
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

    /**
     * @inheritdoc
     */
    public function getSettingsResponse()
    {
        $view = Craft::$app->getView();
        $namespace = $view->getNamespace();
        $view->setNamespace('settings');
        $settingsHtml = $this->settingsHtml();
        $view->setNamespace($namespace);

        /** @var Controller $controller */
        $controller = Craft::$app->controller;

        return $controller->renderTemplate('image-optimize/_settings', [
            'plugin'       => $this,
            'settingsHtml' => $settingsHtml,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function settingsHtml()
    {
        $imageProcessors = ImageOptimize::$plugin->optimize->getActiveImageProcessors();
        $variantCreators = ImageOptimize::$plugin->optimize->getActiveVariantCreators();

        // Get only the user-editable settings
        $settings = $this->getSettings();

        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'image-optimize/settings',
            [
                'settings'        => $settings,
                'imageProcessors' => $imageProcessors,
                'variantCreators' => $variantCreators,
            ]
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
