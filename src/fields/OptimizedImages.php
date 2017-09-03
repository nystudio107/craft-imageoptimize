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

use nystudio107\imageoptimize\ImageOptimize;
use nystudio107\imageoptimize\assetbundles\optimizedimagesfield\OptimizedImagesFieldAsset;
use nystudio107\imageoptimize\models\OptimizedImage;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\elements\Asset;
use craft\helpers\Json;
use craft\models\AssetTransform;
use craft\services\Elements;

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
     * @var string
     */
    public $someAttribute = 'Some Default';

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
            ['someAttribute', 'string'],
            ['someAttribute', 'default', 'value' => 'Some Default'],
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
            /** @var Asset $value */
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
                    print_r('OptimizedImage afterElementSave() - $element - ' . $success . ' ' . print_r($element, true), true),
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
        /** @var Asset $element */
        Craft::info(
            print_r('OptimizedImage normalizeValue() - $value - ' . print_r($value, true), true),
            __METHOD__
        );

        if (is_string($value) && !empty($value)) {
            $value = Json::decodeIfJson($value);
        }

        if (is_array($value)) {
            $model = new OptimizedImage($value);
        } else {
            $model = new OptimizedImage();
        }

        if (!empty($this->currentAsset)) {
            $model->optimizedImageUrls = [];
            $model->optimizedWebPImageUrls = [];
            
            Craft::info(
                print_r('OptimizedImage creating transoforms', true),
                __METHOD__
            );

            $transform = new AssetTransform();

            $widths = [1170, 970, 750, 320];

            foreach ($widths as $width) {
                $aspectRatio = 4.0 / 3.0;

                $transform->width = $width;
                $transform->height = intval($transform->width / $aspectRatio);

                $url = $element->getUrl($transform);
                $model->optimizedImageUrls[] = $url;
                $model->optimizedWebPImageUrls[] = $url . '.webp';
            }
        }

        Craft::info(
            print_r('OptimizedImage normalizeValue()' . print_r($model, true), true),
            __METHOD__
        );

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        Craft::info(
            print_r('OptimizedImage serializeValue()' . print_r($value, true), true),
            __METHOD__
        );
        return parent::serializeValue($value, $element);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'image-optimize/_components/fields/OptimizedImages_settings',
            [
                'field' => $this,
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
            'id' => $id,
            'name' => $this->handle,
            'namespace' => $namespacedId,
            'prefix' => Craft::$app->getView()->namespaceInputId(''),
            ];
        $jsonVars = Json::encode($jsonVars);
        Craft::$app->getView()->registerJs("$('#{$namespacedId}-field').ImageOptimizeOptimizedImages(" . $jsonVars . ");");

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'image-optimize/_components/fields/OptimizedImages_input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
            ]
        );
    }
}
