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
    public function afterElementSave(ElementInterface $asset, bool $isNew)
    {
        parent::afterElementSave($asset, $isNew);
        // Update our OptimizedImages Field data now that the Asset has been saved
        if ($asset instanceof Asset) {
            if ($isNew) {
                /**
                 * If this is a newly uploaded/created Asset, we can save the variants
                 * via a queue job to prevent it from blocking
                 */
                ImageOptimize::$plugin->optimizedImages->resaveAsset($asset->id);
            } else {
                /**
                 * If it's not a newly uploaded/created Asset, they may have edited
                 * the image with the ImageEditor, so we need to update the variants
                 * immediately, so the AssetSelectorHud displays the new images
                 */
                ImageOptimize::$plugin->optimizedImages->updateOptimizedImageFieldData($this, $asset);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $asset = null)
    {
        // If we're passed in a string, assume it's JSON-encoded, and decode it
        if (is_string($value) && !empty($value)) {
            $value = Json::decodeIfJson($value);
        }
        // If we're passed in an array, make a model from it
        if (is_array($value)) {
            // Create a new OptimizedImage model and populate it
            $model = new OptimizedImage($value);
        } else {
            if ($value instanceof OptimizedImage) {
                $model = $value;
            } else {
                // Just create a new empty model
                $model = new OptimizedImage(null);
            }
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
                $aspectRatio['break'] = true;
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
        if (!empty($element) && $element instanceof Asset) {
            /** @var Asset $element */
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
                    'elementId'   => $element->id,
                    'format'      => $element->getExtension(),
                    'id'          => $id,
                    'nameSpaceId' => $nameSpaceId,
                ]
            );
        } else {
            // Render an error template, since the field only works when attached to an Asset
            return Craft::$app->getView()->renderTemplate(
                'image-optimize/_components/fields/OptimizedImages_error',
                [
                ]
            );
        }
    }
}
