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
use nystudio107\imageoptimize\listeners\GetCraftQLSchema;
use nystudio107\imageoptimize\models\Settings;
use nystudio107\imageoptimize\services\Optimize as OptimizeService;
use nystudio107\imageoptimize\services\OptimizedImages as OptimizedImagesService;
use nystudio107\imageoptimize\services\Placeholder as PlaceholderService;
use nystudio107\imageoptimize\variables\ImageOptimizeVariable;

use Craft;
use craft\base\Field;
use craft\base\Plugin;
use craft\base\Volume;
use craft\elements\Asset;
use craft\events\AssetTransformImageEvent;
use craft\events\ElementEvent;
use craft\events\FieldEvent;
use craft\events\GetAssetThumbUrlEvent;
use craft\events\GetAssetUrlEvent;
use craft\events\GenerateTransformEvent;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\ReplaceAssetEvent;
use craft\events\VolumeEvent;
use craft\helpers\ArrayHelper;
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
use craft\web\UrlManager;
use craft\web\View;

use markhuot\CraftQL\CraftQL;

use yii\base\Event;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/** @noinspection MissingPropertyAnnotationsInspection */

/**
 * Class ImageOptimize
 *
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 *
 * @property OptimizeService         optimize
 * @property PlaceholderService      placeholder
 * @property OptimizedImagesService  optimizedImages
 * @property ImageTransformInterface transformMethod
 * @property Settings                $settings
 * @method   Settings                getSettings()
 */
class ImageOptimize extends Plugin
{

    // Constants
    // =========================================================================

    const CRAFTQL_PLUGIN_HANDLE = 'craftql';

    // Static Properties
    // =========================================================================

    /**
     * @var ImageOptimize
     */
    public static $plugin;

    /**
     * @var bool
     */
    public static $generatePlaceholders = true;

    /**
     * @var bool
     */
    public static $craft31 = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;
        self::$craft31 = version_compare(Craft::$app->getVersion(), '3.1', '>=');
        // Handle any console commands
        $request = Craft::$app->getRequest();
        if ($request->getIsConsoleRequest()) {
            $this->controllerNamespace = 'nystudio107\imageoptimize\console\controllers';
        }
        // Set the image transform component
        $this->setImageTransformComponent();
        // Add in our Craft components
        $this->addComponents();
        // Install our global event handlers
        $this->installEventHandlers();
        // Log that the plugin has loaded
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

        return $controller->renderTemplate('image-optimize/settings/index.twig', [
            'plugin'       => $this,
            'settingsHtml' => $settingsHtml,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function settingsHtml()
    {
        // Get only the user-editable settings
        $settings = $this->getSettings();

        // Get the image transform types
        $allImageTransformTypes = ImageOptimize::$plugin->optimize->getAllImageTransformTypes();
        $imageTransformTypeOptions = [];
        /** @var ImageTransformInterface $class */
        foreach ($allImageTransformTypes as $class) {
            if ($class::isSelectable()) {
                $imageTransformTypeOptions[] = [
                    'value' => $class,
                    'label' => $class::displayName(),
                ];
            }
        }
        // Sort them by name
        ArrayHelper::multisort($imageTransformTypeOptions, 'label');

        // Render the settings template
        try {
            return Craft::$app->getView()->renderTemplate(
                'image-optimize/settings/_settings.twig',
                [
                    'settings'        => $settings,
                    'gdInstalled'     => \function_exists('imagecreatefromjpeg'),
                    'imageTransformTypeOptions' => $imageTransformTypeOptions,
                    'allImageTransformTypes' => $allImageTransformTypes,
                    'imageTransform' => ImageOptimize::$plugin->transformMethod,
                ]
            );
        } catch (\Twig\Error\LoaderError $e) {
            Craft::error($e->getMessage(), __METHOD__);
        } catch (Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }

        return '';
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

    /**
     * Set the transformMethod component
     */
    protected function setImageTransformComponent()
    {
        $settings = $this->getSettings();
        $definition = array_merge(
            $settings->imageTransformTypeSettings[$settings->transformClass] ?? [],
            ['class' => $settings->transformClass]
        );
        try {
            $this->set('transformMethod', $definition);
        } catch (InvalidConfigException $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }
    }

    /**
     * Add in our Craft components
     */
    protected function addComponents()
    {
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
                Craft::debug(
                    'Fields::EVENT_REGISTER_FIELD_TYPES',
                    __METHOD__
                );
                $event->types[] = OptimizedImages::class;
            }
        );
    }

    /**
     * Install our event handlers
     */
    protected function installEventHandlers()
    {
        $this->installAssetEventHandlers();
        $this->installElementEventHandlers();
        $this->installMiscEventHandlers();
        $this->installCraftQLEventHandlers();
        $request = Craft::$app->getRequest();
        // Install only for non-console site requests
        if ($request->getIsSiteRequest() && !$request->getIsConsoleRequest()) {
            $this->installSiteEventListeners();
        }
        // Install only for non-console cp requests
        if ($request->getIsCpRequest() && !$request->getIsConsoleRequest()) {
            $this->installCpEventListeners();
        }
    }

    /**
     * Install our Asset event handlers
     */
    protected function installAssetEventHandlers()
    {
        // Handler: Assets::EVENT_GET_ASSET_URL
        Event::on(
            Assets::class,
            Assets::EVENT_GET_ASSET_URL,
            function (GetAssetUrlEvent $event) {
                Craft::debug(
                    'Assets::EVENT_GET_ASSET_URL',
                    __METHOD__
                );
                // Return the URL to the asset URL or null to let Craft handle it
                $event->url = ImageOptimize::$plugin->optimize->handleGetAssetUrlEvent(
                    $event
                );
            }
        );

        // Handler: Assets::EVENT_GET_ASSET_THUMB_URL
        Event::on(
            Assets::class,
            Assets::EVENT_GET_ASSET_THUMB_URL,
            function (GetAssetThumbUrlEvent $event) {
                Craft::debug(
                    'Assets::EVENT_GET_ASSET_THUMB_URL',
                    __METHOD__
                );
                // Return the URL to the asset URL or null to let Craft handle it
                $event->url = ImageOptimize::$plugin->optimize->handleGetAssetThumbUrlEvent(
                    $event
                );
            }
        );

        // Handler: AssetTransforms::EVENT_GENERATE_TRANSFORM
        Event::on(
            AssetTransforms::class,
            AssetTransforms::EVENT_GENERATE_TRANSFORM,
            function (GenerateTransformEvent $event) {
                Craft::debug(
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
                Craft::debug(
                    'AssetTransforms::EVENT_AFTER_DELETE_TRANSFORMS',
                    __METHOD__
                );
                // Clean up any stray variant files
                ImageOptimize::$plugin->optimize->handleAfterDeleteTransformsEvent(
                    $event
                );
            }
        );

        // Handler: Assets::EVENT_BEFORE_REPLACE_ASSET
        Event::on(
            Assets::class,
            Assets::EVENT_BEFORE_REPLACE_ASSET,
            function (ReplaceAssetEvent $event) {
                Craft::debug(
                    'Assets::EVENT_BEFORE_REPLACE_ASSET',
                    __METHOD__
                );
                /** @var Asset $element */
                $element = $event->asset;
                // Purge the URL
                $purgeUrl = ImageOptimize::$plugin->transformMethod->getPurgeUrl($element);
                if ($purgeUrl) {
                    ImageOptimize::$plugin->transformMethod->purgeUrl($purgeUrl);
                }
            }
        );

        // Handler: Assets::EVENT_AFTER_REPLACE_ASSET
        Event::on(
            Assets::class,
            Assets::EVENT_AFTER_REPLACE_ASSET,
            function (ReplaceAssetEvent $event) {
                Craft::debug(
                    'Assets::EVENT_AFTER_REPLACE_ASSET',
                    __METHOD__
                );
                /** @var Asset $element */
                $element = $event->asset;
                if ($element->id !== null) {
                    ImageOptimize::$plugin->optimizedImages->resaveAsset($element->id);
                }
            }
        );
    }

    /**
     * Install our Element event handlers
     */
    protected function installElementEventHandlers()
    {
        // Handler: Elements::EVENT_BEFORE_SAVE_ELEMENT
        Event::on(
            Assets::class,
            Elements::EVENT_BEFORE_SAVE_ELEMENT,
            function (ElementEvent $event) {
                Craft::debug(
                    'Elements::EVENT_BEFORE_SAVE_ELEMENT',
                    __METHOD__
                );
                /** @var Asset $asset */
                $asset = $event->element;
                if (!$event->isNew) {
                    // Purge the URL
                    $purgeUrl = ImageOptimize::$plugin->transformMethod->getPurgeUrl($asset);
                    if ($purgeUrl) {
                        ImageOptimize::$plugin->transformMethod->purgeUrl($purgeUrl);
                    }
                }
            }
        );

        // Handler: Elements::EVENT_BEFORE_DELETE_ELEMENT
        Event::on(
            Asset::class,
            Elements::EVENT_BEFORE_DELETE_ELEMENT,
            function (ElementEvent $event) {
                Craft::debug(
                    'Elements::EVENT_BEFORE_DELETE_ELEMENT',
                    __METHOD__
                );
                /** @var Asset $asset */
                $asset = $event->element;
                // Purge the URL
                $purgeUrl = ImageOptimize::$plugin->transformMethod->getPurgeUrl($asset);
                if ($purgeUrl) {
                    ImageOptimize::$plugin->transformMethod->purgeUrl($purgeUrl);
                }
            }
        );
    }

    /**
     * Install our miscellaneous event handlers
     */
    protected function installMiscEventHandlers()
    {
        // Handler: Fields::EVENT_AFTER_SAVE_FIELD
        Event::on(
            Fields::class,
            Fields::EVENT_AFTER_SAVE_FIELD,
            function (FieldEvent $event) {
                Craft::debug(
                    'Fields::EVENT_AFTER_SAVE_FIELD',
                    __METHOD__
                );
                $settings = $this->getSettings();
                /** @var Field $field */
                if (!$event->isNew && $settings->automaticallyResaveImageVariants) {
                    $this->checkForOptimizedImagesField($event);
                }
            }
        );

        // Handler: Plugins::EVENT_AFTER_SAVE_PLUGIN_SETTINGS
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_SAVE_PLUGIN_SETTINGS,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    Craft::debug(
                        'Plugins::EVENT_AFTER_SAVE_PLUGIN_SETTINGS',
                        __METHOD__
                    );
                    $settings = $this->getSettings();
                    if ($settings->automaticallyResaveImageVariants) {
                        // After they have changed the settings, resave all of the assets
                        ImageOptimize::$plugin->optimizedImages->resaveAllVolumesAssets();
                    }
                }
            }
        );

        // Handler: Volumes::EVENT_AFTER_SAVE_VOLUME
        Event::on(
            Volumes::class,
            Volumes::EVENT_AFTER_SAVE_VOLUME,
            function (VolumeEvent $event) {
                Craft::debug(
                    'Volumes::EVENT_AFTER_SAVE_VOLUME',
                    __METHOD__
                );
                $settings = $this->getSettings();
                // Only worry about this volume if it's not new
                if (!$event->isNew && $settings->automaticallyResaveImageVariants) {
                    /** @var Volume $volume */
                    $volume = $event->volume;
                    if ($volume !== null) {
                        ImageOptimize::$plugin->optimizedImages->resaveVolumeAssets($volume);
                    }
                }
            }
        );

        // Handler: Plugins::EVENT_AFTER_INSTALL_PLUGIN
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    $request = Craft::$app->getRequest();
                    if ($request->isCpRequest) {
                        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('image-optimize/welcome'))->send();
                    }
                }
            }
        );
    }

    /**
     * Install our CraftQL event handlers
     */
    protected function installCraftQLEventHandlers()
    {
        if (class_exists(CraftQL::class)) {
            Event::on(
                OptimizedImages::class,
                GetCraftQLSchema::EVENT_GET_FIELD_SCHEMA,
                [new GetCraftQLSchema, 'handle']
            );
        }
    }

    /**
     * Install site event listeners for site requests only
     */
    protected function installSiteEventListeners()
    {
        // Handler: UrlManager::EVENT_REGISTER_SITE_URL_RULES
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                Craft::debug(
                    'UrlManager::EVENT_REGISTER_SITE_URL_RULES',
                    __METHOD__
                );
                // Register our Control Panel routes
                $event->rules = array_merge(
                    $event->rules,
                    $this->customFrontendRoutes()
                );
            }
        );
    }

    /**
     * Install site event listeners for cp requests only
     */
    protected function installCpEventListeners()
    {
        // Handler: Plugins::EVENT_AFTER_LOAD_PLUGINS
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_LOAD_PLUGINS,
            function () {
                    // Install these only after all other plugins have loaded
                    Event::on(
                        View::class,
                        View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
                        function (RegisterTemplateRootsEvent $e) {
                            // Register the root directodies
                            $allImageTransformTypes = ImageOptimize::$plugin->optimize->getAllImageTransformTypes();
                            /** @var ImageTransformInterface $imageTransformType */
                            foreach ($allImageTransformTypes as $imageTransformType) {
                                list($id, $baseDir) = $imageTransformType::getTemplatesRoot();
                                if (is_dir($baseDir)) {
                                    $e->roots[$id] = $baseDir;
                                }
                            }
                        }
                    );
            }
        );
    }

    /**
     * Return the custom frontend routes
     *
     * @return array
     */
    protected function customFrontendRoutes(): array
    {
        return [
        ];
    }

    /**
     * If the Field being saved is an OptimizedImages field, re-save the
     * responsive image variants automatically
     *
     * @param FieldEvent $event
     *
     * @throws \yii\base\InvalidConfigException
     */
    protected function checkForOptimizedImagesField(FieldEvent $event)
    {
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
                        /** @var Field $field */
                        if ($thisField->handle === $field->handle) {
                            $needToReSave = true;
                        }
                    }
                    if ($needToReSave) {
                        ImageOptimize::$plugin->optimizedImages->resaveVolumeAssets($volume, $thisField->id);
                    }
                }
            }
        }
    }
}
