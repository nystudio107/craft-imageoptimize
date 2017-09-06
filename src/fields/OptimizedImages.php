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

use craft\models\AssetTransform;
use nystudio107\imageoptimize\ImageOptimize;
use nystudio107\imageoptimize\assetbundles\optimizedimagesfield\OptimizedImagesFieldAsset;
use nystudio107\imageoptimize\models\OptimizedImage;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\elements\Asset;
use craft\helpers\Json;
use craft\validators\ArrayValidator;

use yii\db\Schema;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.2.0
 */
class OptimizedImages extends Field
{
    // Public Properties
    // =========================================================================

    /**
     * @var array
     */
    public $variants = [
        [
            'width' => 1170,
            'aspectRatioX' => 16.0,
            'aspectRatioY' => 9.0,
            'quality' => 0,
            'format' => null,
        ],
        [
            'width' => 970,
            'aspectRatioX' => 16.0,
            'aspectRatioY' => 9.0,
            'quality' => 0,
            'format' => null,
        ],
        [
            'width' => 750,
            'aspectRatioX' => 4.0,
            'aspectRatioY' => 3.0,
            'quality' => 0,
            'format' => null,
        ],
        [
            'width' => 320,
            'aspectRatioX' => 4.0,
            'aspectRatioY' => 3.0,
            'quality' => 0,
            'format' => null,
        ],
    ];

    // Private Properties
    // =========================================================================

    /**
     * @var Asset
     */
    private $currentAsset = null;

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('image-optimize', 'OptimizedImages');
    }

    // Public Methods
    // =========================================================================

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
        // If this is a new element, resave it so that it as an id for our asset transforms
        if ($isNew) {
            /** @var Asset $element */
            if ($element instanceof Asset) {
                // Initialize our field with defaults
                $this->currentAsset = $element;
                $defaultData = $this->normalizeValue(null, $element);
                $defaultSerializedData = $this->serializeValue($defaultData, $element);
                $element->setFieldValues([
                    $this->handle => $defaultSerializedData,
                ]);

                $success = Craft::$app->getElements()->saveElement($element, false);
                Craft::info(
                    print_r('Re-saved new asset ' . $success, true),
                    __METHOD__
                );
            }
        }

        parent::afterElementSave($element, $isNew);
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if (is_string($value) && !empty($value)) {
            $value = Json::decodeIfJson($value);
        }

        // Create a new OptimizedImage model and populate it
        $model = new OptimizedImage($value);
        if (!empty($this->currentAsset)) {
            $this->populateOptimizedImageModel($this->currentAsset, $model);
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
    public function getSettingsHtml()
    {
        $reflect = new \ReflectionClass($this);
        $thisId = $reflect->getShortName();
        $id = Craft::$app->getView()->formatInputId($thisId);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);
        $namespacePrefix = Craft::$app->getView()->namespaceInputName($thisId);
        Craft::$app->getView()->registerJs('new Craft.OptimizedImagesInput('.
            '"'. $namespacedId .'", '.
            '"'. $namespacePrefix .'"'.
            ');');

        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'image-optimize/_components/fields/OptimizedImages_settings',
            [
                'field'     => $this,
                'id'        => $id,
                'name'      => $this->handle,
                'namespace' => $namespacedId,
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
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        // Variables to pass down to our field JavaScript to let it namespace properly
        $jsonVars = [
            'id'        => $id,
            'name'      => $this->handle,
            'namespace' => $namespacedId,
            'prefix'    => Craft::$app->getView()->namespaceInputId(''),
        ];
        $jsonVars = Json::encode($jsonVars);
        Craft::$app->getView()->registerJs("$('#{$namespacedId}-field').ImageOptimizeOptimizedImages(" . $jsonVars . ");");

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'image-optimize/_components/fields/OptimizedImages_input',
            [
                'name'         => $this->handle,
                'value'        => $value,
                'variants'     => $this->variants,
                'field'        => $this,
                'id'           => $id,
                'namespacedId' => $namespacedId,
            ]
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @param Asset          $element
     * @param OptimizedImage $model
     */
    protected function populateOptimizedImageModel(Asset $element, OptimizedImage $model)
    {
        // Empty our the optimized image URLs
        $model->optimizedImageUrls = [];
        $model->optimizedWebPImageUrls = [];

        /** @var AssetTransform $transform */
        $transform = new AssetTransform();

        foreach ($this->variants as $variant) {
            // Create the transform based on the variant
            $aspectRatio = $variant['aspectRatioX'] / $variant['aspectRatioY'];
            $width = $variant['width'];
            $transform->width = $width;
            $transform->height = intval($width / $aspectRatio);
            $transform->quality = $variant['quality'];
            $transform->format = $variant['format'];

            // Generate the URLs to the optimized images
            $url = $element->getUrl($transform);
            $model->optimizedImageUrls[$width] = $url;
            $model->optimizedWebPImageUrls[$width] = $url . '.webp';

            Craft::info(
                'Created transforms for variant: ' . print_r($variant, true),
                __METHOD__
            );
        }
    }

}
