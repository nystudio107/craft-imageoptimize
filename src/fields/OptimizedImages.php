<?php
/**
 * Image Optimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\fields;

use nystudio107\imageoptimize\assetbundles\optimizedimagesfield\OptimizedImagesFieldAsset;
use nystudio107\imageoptimize\ImageOptimize;
use nystudio107\imageoptimize\models\OptimizedImage;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\elements\Asset;
use craft\helpers\Json;
use craft\validators\ArrayValidator;

use yii\db\Schema;

/** @noinspection MissingPropertyAnnotationsInspection */

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.2.0
 */
class OptimizedImages extends Field
{
    // Constants
    // =========================================================================

    const DEFAULT_ASPECT_RATIOS = [
        ['x' => 16, 'y' => 9],
    ];
    const DEFAULT_IMAGE_VARIANTS = [
        [
            'width'          => 1200,
            'useAspectRatio' => true,
            'aspectRatioX'   => 16.0,
            'aspectRatioY'   => 9.0,
            'retinaSizes'    => ['1'],
            'quality'        => 82,
            'format'         => 'jpg',
        ],
    ];

    // Public Properties
    // =========================================================================

    /**
     * @var array
     */
    public $variants = [];

    // Private Properties
    // =========================================================================

    /**
     * @var array
     */
    private $aspectRatios = [];

    /**
     * @var Asset
     */
    private $currentAsset = null;

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
        // Unset any deprecated properties
        unset($config['transformMethod']);
        unset($config['imgixDomain']);
        parent::__construct($config);
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'OptimizedImages';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Handle cases where the plugin has been uninstalled
        if (!empty(ImageOptimize::$plugin)) {
            $settings = ImageOptimize::$plugin->getSettings();
            if ($settings) {
                if (empty($this->variants)) {
                    $this->variants = $settings->defaultVariants;
                }
                $this->aspectRatios = $settings->defaultAspectRatios;
            }
        }
        // If the user has deleted all default aspect ratios, provide a fallback
        if (empty($this->aspectRatios)) {
            $this->aspectRatios = self::DEFAULT_ASPECT_RATIOS;
        }
        // If the user has deleted all default variants, provide a fallback
        if (empty($this->variants)) {
            $this->variants = self::DEFAULT_IMAGE_VARIANTS;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules = array_merge($rules, [
            ['variants', ArrayValidator::class],
        ]);

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function beforeElementSave(ElementInterface $element, bool $isNew): bool
    {
        $this->currentAsset = null;
        // Only stash the currentAsset if this is not a new element
        if (!$isNew) {
            /** @var Asset $element */
            if ($element instanceof Asset) {
                $this->currentAsset = $element;
            }
        }

        return parent::beforeElementSave($element, $isNew);
    }

    /**
     * @inheritdoc
     */
    public function afterElementSave(ElementInterface $element, bool $isNew)
    {
        parent::afterElementSave($element, $isNew);

        /** @var Asset $element */
        if ($element instanceof Asset) {
            // If this is a new element, resave it so that it as an id for our asset transforms
            if ($isNew) {
                ImageOptimize::$plugin->optimize->resaveAsset($element->id);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        // If we're passed in a string, assume it's JSON-encoded, and decode it
        if (is_string($value) && !empty($value)) {
            $value = Json::decodeIfJson($value);
        }
        // If it's not an array, default it to null
        if (!is_array($value)) {
            $value = null;
        }
        // Create a new OptimizedImage model and populate it
        $model = new OptimizedImage($value);
        if (!empty($this->currentAsset)) {
            ImageOptimize::$plugin->optimizedImages->populateOptimizedImageModel(
                $this->currentAsset,
                $this->variants,
                $model
            );
        }

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        return parent::serializeValue($value, $element);
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $reflect = new \ReflectionClass($this);
        $thisId = $reflect->getShortName();
        $id = Craft::$app->getView()->formatInputId($thisId);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);
        $namespacePrefix = Craft::$app->getView()->namespaceInputName($thisId);
        Craft::$app->getView()->registerJs('new Craft.OptimizedImagesInput('.
            '"'.$namespacedId.'", '.
            '"'.$namespacePrefix.'"'.
            ');');

        // Prep our aspect ratios
        $aspectRatios = [];
        $index = 1;
        foreach ($this->aspectRatios as $aspectRatio) {
            if ($index % 6 === 0) {
                $aspectRatios[] = array('break' => true);
            }
            $aspectRatios[] = $aspectRatio;
            $index++;
        }
        $aspectRatio = ['x' => 2, 'y' => 2, 'custom' => true];
        $aspectRatios[] = $aspectRatio;

        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'image-optimize/_components/fields/OptimizedImages_settings',
            [
                'field'        => $this,
                'aspectRatios' => $aspectRatios,
                'id'           => $id,
                'name'         => $this->handle,
                'namespace'    => $namespacedId,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        // Register our asset bundle
        Craft::$app->getView()->registerAssetBundle(OptimizedImagesFieldAsset::class);

        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $nameSpaceId = Craft::$app->getView()->namespaceInputId($id);

        // Variables to pass down to our field JavaScript to let it namespace properly
        $jsonVars = [
            'id'        => $id,
            'name'      => $this->handle,
            'namespace' => $nameSpaceId,
            'prefix'    => Craft::$app->getView()->namespaceInputId(''),
        ];
        $jsonVars = Json::encode($jsonVars);
        $view = Craft::$app->getView();
        $view->registerJs("$('#{$nameSpaceId}-field').ImageOptimizeOptimizedImages(".$jsonVars.");");

        $settings = ImageOptimize::$plugin->getSettings();

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'image-optimize/_components/fields/OptimizedImages_input',
            [
                'name'        => $this->handle,
                'value'       => $value,
                'variants'    => $this->variants,
                'field'       => $this,
                'settings'    => $settings,
                'id'          => $id,
                'nameSpaceId' => $nameSpaceId,
            ]
        );
    }
}
