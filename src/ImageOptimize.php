<?php
/**
 * ImageOptimize plugin for Craft CMS
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize;

use Craft;
use craft\base\Field;
use craft\base\Model;
use craft\base\Plugin;
use craft\elements\Asset;
use craft\events\DefineAssetThumbUrlEvent;
use craft\events\DefineAssetUrlEvent;
use craft\events\ElementEvent;
use craft\events\FieldEvent;
use craft\events\ImageTransformerOperationEvent;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\ReplaceAssetEvent;
use craft\events\VolumeEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\UrlHelper;
use craft\imagetransforms\ImageTransformer;
use craft\models\FieldLayout;
use craft\services\Assets;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Plugins;
use craft\services\Utilities;
use craft\services\Volumes;
use craft\web\Controller;
use craft\web\TemplateResponseBehavior;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use nystudio107\imageoptimize\fields\OptimizedImages;
use nystudio107\imageoptimize\imagetransforms\CraftImageTransform;
use nystudio107\imageoptimize\imagetransforms\ImageTransformInterface;
use nystudio107\imageoptimize\models\Settings;
use nystudio107\imageoptimize\services\ServicesTrait;
use nystudio107\imageoptimize\utilities\ImageOptimizeUtility;
use nystudio107\imageoptimize\variables\ImageOptimizeVariable;
use yii\base\Event;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\Response;
use function function_exists;

/** @noinspection MissingPropertyAnnotationsInspection */

/**
 * Class ImageOptimize
 *
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 *
 * @property ImageTransformInterface $transformMethod
 */
class ImageOptimize extends Plugin
{
    // Traits
    // =========================================================================

    use ServicesTrait;

    // Static Properties
    // =========================================================================

    /**
     * @var ?ImageOptimize
     */
    public static ?ImageOptimize $plugin = null;

    /**
     * @var bool
     */
    public static bool $generatePlaceholders = true;

    // Public Properties
    // =========================================================================
    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public bool $hasCpSection = false;

    /**
     * @var bool
     */
    public bool $hasCpSettings = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;
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
    public function getSettingsResponse(): TemplateResponseBehavior|Response
    {
        $view = Craft::$app->getView();
        $namespace = $view->getNamespace();
        $view->setNamespace('settings');
        $settingsHtml = $this->settingsHtml();
        $view->setNamespace($namespace);
        /** @var Controller $controller */
        $controller = Craft::$app->controller;

        return $controller->renderTemplate('image-optimize/settings/index.twig', [
            'plugin' => $this,
            'settingsHtml' => $settingsHtml,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function settingsHtml(): ?string
    {
        // Get only the user-editable settings
        $settings = $this->getSettings();

        // Get the image transform types
        $allImageTransformTypes = self::$plugin->optimize->getAllImageTransformTypes();
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
                    'settings' => $settings,
                    'gdInstalled' => function_exists('imagecreatefromjpeg'),
                    'imageTransformTypeOptions' => $imageTransformTypeOptions,
                    'allImageTransformTypes' => $allImageTransformTypes,
                    'imageTransform' => self::$plugin->transformMethod,
                ]
            );
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
    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    /**
     * Set the transformMethod component
     */
    protected function setImageTransformComponent(): void
    {
        /* @var Settings $settings */
        $settings = $this->getSettings();
        if ($settings === null) {
            return;
        }
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
    protected function addComponents(): void
    {
        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('imageOptimize', [
                    'class' => ImageOptimizeVariable::class,
                    'viteService' => $this->vite,
                ]);
            }
        );

        // Register our Field
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            static function (RegisterComponentTypesEvent $event) {
                Craft::debug(
                    'Fields::EVENT_REGISTER_FIELD_TYPES',
                    __METHOD__
                );
                $event->types[] = OptimizedImages::class;
            }
        );

        // Register our Utility only if they are using the CraftImageTransform method
        if (self::$plugin->transformMethod instanceof CraftImageTransform) {
            Event::on(
                Utilities::class,
                Utilities::EVENT_REGISTER_UTILITY_TYPES,
                static function (RegisterComponentTypesEvent $event) {
                    $event->types[] = ImageOptimizeUtility::class;
                }
            );
        }
    }

    /**
     * Install our event handlers
     */
    protected function installEventHandlers(): void
    {
        $this->installAssetEventHandlers();
        $this->installElementEventHandlers();
        $this->installMiscEventHandlers();
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
    protected function installAssetEventHandlers(): void
    {
        // Use Asset::EVENT_BEFORE_DEFINE_URL if it's available
        // ref: https://github.com/craftcms/cms/issues/13018
        try {
            $ref = new \ReflectionClassConstant(Asset::class, 'EVENT_BEFORE_DEFINE_URL');
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (\ReflectionException) {
            $ref = null;
        }
        $eventName = $ref?->getDeclaringClass()->name === Asset::class
            ? Asset::EVENT_BEFORE_DEFINE_URL
            : Asset::EVENT_DEFINE_URL;
        // Handler: Assets::EVENT_DEFINE_URL
        Event::on(
            Asset::class,
            $eventName,
            static function (DefineAssetUrlEvent $event): void {
                Craft::debug(
                    'Asset::EVENT_DEFINE_URL',
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
            Assets::EVENT_DEFINE_THUMB_URL,
            static function (DefineAssetThumbUrlEvent $event): void {
                Craft::debug(
                    'Assets::EVENT_DEFINE_THUMB_URL',
                    __METHOD__
                );
                // Return the URL to the asset URL or null to let Craft handle it
                $event->url = ImageOptimize::$plugin->optimize->handleGetAssetThumbUrlEvent(
                    $event
                );
            }
        );

        // Handler: ImageTransformer::EVENT_TRANSFORM_IMAGE
        Event::on(
            ImageTransformer::class,
            ImageTransformer::EVENT_TRANSFORM_IMAGE,
            static function (ImageTransformerOperationEvent $event): void {
                Craft::debug(
                    'ImageTransformer::EVENT_TRANSFORM_IMAGE',
                    __METHOD__
                );
                // Return the path to the optimized image to _createTransformForAsset()
                $tempPath = ImageOptimize::$plugin->optimize->handleGenerateTransformEvent(
                    $event
                );
                if ($tempPath) {
                    // Remove the old Craft generated transform that's still sitting in the temp directory.
                    @unlink($event->tempPath);
                    $event->tempPath = $tempPath;
                }
            }
        );

        // Handler: ImageTransformer::EVENT_DELETE_TRANSFORMED_IMAGE
        Event::on(
            ImageTransformer::class,
            ImageTransformer::EVENT_DELETE_TRANSFORMED_IMAGE,
            static function (ImageTransformerOperationEvent $event): void {
                Craft::debug(
                    'ImageTransformer::EVENT_DELETE_TRANSFORMED_IMAGE',
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
            static function (ReplaceAssetEvent $event) {
                Craft::debug(
                    'Assets::EVENT_BEFORE_REPLACE_ASSET',
                    __METHOD__
                );
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
            static function (ReplaceAssetEvent $event) {
                Craft::debug(
                    'Assets::EVENT_AFTER_REPLACE_ASSET',
                    __METHOD__
                );
                $element = $event->asset;
                if ($element->id !== null) {
                    ImageOptimize::$plugin->optimizedImages->resaveAsset($element->id, true);
                }
            }
        );
    }

    /**
     * Install our Element event handlers
     */
    protected function installElementEventHandlers(): void
    {
        // Handler: Elements::EVENT_BEFORE_SAVE_ELEMENT
        Event::on(
            Assets::class,
            Elements::EVENT_BEFORE_SAVE_ELEMENT,
            static function (ElementEvent $event) {
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
            static function (ElementEvent $event) {
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
    protected function installMiscEventHandlers(): void
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
                /* @var Settings $settings */
                $settings = $this->getSettings();
                /** @var Field $field */
                if (($settings !== null) && !$event->isNew && $settings->automaticallyResaveImageVariants) {
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
                    /* @var Settings $settings */
                    $settings = $this->getSettings();
                    if (($settings !== null) && $settings->automaticallyResaveImageVariants) {
                        // After they have changed the settings, resave all the assets
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
                /* @var Settings $settings */
                $settings = $this->getSettings();
                // Only worry about this volume if it's not new
                if (($settings !== null) && !$event->isNew && $settings->automaticallyResaveImageVariants) {
                    $volume = $event->volume;
                    ImageOptimize::$plugin->optimizedImages->resaveVolumeAssets($volume);
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
     * Install site event listeners for site requests only
     */
    protected function installSiteEventListeners(): void
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
    protected function installCpEventListeners(): void
    {
        // Handler: Plugins::EVENT_AFTER_LOAD_PLUGINS
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_LOAD_PLUGINS,
            static function () {
                // Install these only after all other plugins have loaded
                Event::on(
                    View::class,
                    View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
                    static function (RegisterTemplateRootsEvent $e) {
                        // Register the root directodies
                        $allImageTransformTypes = ImageOptimize::$plugin->optimize->getAllImageTransformTypes();
                        /** @var ImageTransformInterface $imageTransformType */
                        foreach ($allImageTransformTypes as $imageTransformType) {
                            [$id, $baseDir] = $imageTransformType::getTemplatesRoot();
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
     */
    protected function checkForOptimizedImagesField(FieldEvent $event): void
    {
        $thisField = $event->field;
        if ($thisField instanceof OptimizedImages) {
            $volumes = Craft::$app->getVolumes()->getAllVolumes();
            foreach ($volumes as $volume) {
                $needToReSave = false;
                /** @var FieldLayout $fieldLayout */
                $fieldLayout = $volume->getFieldLayout();
                // Loop through the fields in the layout to see if it contains our field
                if ($fieldLayout) {
                    $fields = $fieldLayout->getCustomFields();
                    foreach ($fields as $field) {
                        /** @var Field $field */
                        if ($thisField->handle === $field->handle) {
                            $needToReSave = true;
                        }
                    }
                    if ($needToReSave) {
                        self::$plugin->optimizedImages->resaveVolumeAssets($volume, $thisField->id);
                    }
                }
            }
        }
    }
}
