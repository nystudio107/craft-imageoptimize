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
use nystudio107\imageoptimize\imagetransforms\ImageTransformInterface;
use nystudio107\imageoptimize\models\OptimizedImage;
use nystudio107\imageoptimize\models\Settings;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\elements\Asset;
use craft\helpers\Image;
use craft\helpers\Json;
use craft\models\AssetTransform;
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

    const IMAGE_TRANSFORM_MAP = [
        'craft' => 'nystudio107\imageoptimize\imagetransforms\CraftImageTransform'
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

    public function init()
    {
        parent::init();

        // Handle cases where the plugin has been uninstalled
        if (!empty(ImageOptimize::$plugin)) {
            /** @var Settings $settings */
            $settings = ImageOptimize::$plugin->getSettings();
            if ($settings) {
                if (empty($this->variants)) {
                    $this->variants = $settings->defaultVariants;
                }
            }
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
        /** @var Asset $element */
        if ($element instanceof Asset) {
            // If this is a new element, resave it so that it as an id for our asset transforms
            if ($isNew) {
                ImageOptimize::$plugin->optimize->resaveAsset($element->id);
            } else {
                // Otherwise do nothing, we've already been saved via the call to `normalizeValue`
            }
        }

        parent::afterElementSave($element, $isNew);
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
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
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
            '"'.$namespacedId.'", '.
            '"'.$namespacePrefix.'"'.
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
        $nameSpaceId = Craft::$app->getView()->namespaceInputId($id);

        // Variables to pass down to our field JavaScript to let it namespace properly
        $jsonVars = [
            'id'        => $id,
            'name'      => $this->handle,
            'namespace' => $nameSpaceId,
            'prefix'    => Craft::$app->getView()->namespaceInputId(''),
        ];
        $jsonVars = Json::encode($jsonVars);
        Craft::$app->getView()->registerJs("$('#{$nameSpaceId}-field').ImageOptimizeOptimizedImages(".$jsonVars.");");

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'image-optimize/_components/fields/OptimizedImages_input',
            [
                'name'        => $this->handle,
                'value'       => $value,
                'variants'    => $this->variants,
                'field'       => $this,
                'id'          => $id,
                'nameSpaceId' => $nameSpaceId,
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
        /** @var ImageTransformInterface $transformClass */
        $transformClass = self::IMAGE_TRANSFORM_MAP['craft'];

        // Empty our the optimized image URLs
        $model->optimizedImageUrls = [];
        $model->optimizedWebPImageUrls = [];

        /** @var AssetTransform $transform */
        $transform = new AssetTransform();
        $placeholderMade = false;
        // Get our $generateTransformsBeforePageLoad setting
        $settings = ImageOptimize::$plugin->getSettings();
        $generateTransformsBeforePageLoad = isset($settings['generateTransformsBeforePageLoad'])
            ? $settings['generateTransformsBeforePageLoad']
            : true ;
        foreach ($this->variants as $variant) {
            $retinaSizes = ['1'];
            if (!empty($variant['retinaSizes'])) {
                $retinaSizes = $variant['retinaSizes'];
            }
            foreach ($retinaSizes as $retinaSize) {
                $transform->format = $variant['format'];
                $finalFormat = $transform->format == null ? $element->getExtension() : $transform->format;
                // Only try the transform if it's possible
                if (Image::canManipulateAsImage($finalFormat)
                    && Image::canManipulateAsImage($element->getExtension())
                    && $element->height > 0) {
                    // Create the transform based on the variant
                    $useAspectRatio = isset($variant['useAspectRatio']) ? $variant['useAspectRatio'] : true;
                    if ($useAspectRatio) {
                        $aspectRatio = $variant['aspectRatioX'] / $variant['aspectRatioY'];
                    } else {
                        $aspectRatio = $element->width / $element->height;
                    }
                    $width = $variant['width'] * $retinaSize;
                    $transform->width = $width;
                    $transform->height = intval($width / $aspectRatio);
                    $transform->quality = $variant['quality'];
                    if (property_exists($transform, 'interlace')) {
                        $transform->interlace = 'line';
                    }
                    // Generate an image transform url
                    $url = $transformClass::getTransformUrl(
                        $element,
                        $transform,
                        [
                            'generateTransformsBeforePageLoad' => $generateTransformsBeforePageLoad,
                        ]
                    );
                    // Update the model
                    if (!empty($url)) {
                        $model->optimizedImageUrls[$width] = $url;
                        $model->optimizedWebPImageUrls[$width] = $url . '.webp';
                    }
                    $model->focalPoint = $element->focalPoint;
                    $model->originalImageWidth = $element->width;
                    $model->originalImageHeight = $element->height;
                    // Make our placeholder image once, from the first variant
                    if (!$placeholderMade) {
                        $model->placeholderWidth = $transform->width;
                        $model->placeholderHeight = $transform->height;
                        $this->generatePlaceholders($element, $model, $aspectRatio);
                        $placeholderMade = true;
                    }
                }

                Craft::info(
                    'Created transforms for variant: '.print_r($variant, true),
                    __METHOD__
                );
            }
        }
    }

    /**
     * @param Asset          $element
     * @param OptimizedImage $model
     * @param                $transform
     * @param                $aspectRatio
     */
    protected function generatePlaceholders(Asset $element, OptimizedImage $model, $aspectRatio)
    {
        $settings = ImageOptimize::$plugin->getSettings();
        $placeholder = ImageOptimize::$plugin->placeholder;
        // Generate our placeholder image
        $model->placeholder = $placeholder->generatePlaceholderImage($element, $aspectRatio);
        // Generate the color palette for the image
        if ($settings['createColorPalette']) {
            $model->colorPalette = $placeholder->generateColorPalette($element, $aspectRatio);
        }
        // Generate the Potrace SVG
        if ($settings['createPlaceholderSilhouettes']) {
            $model->placeholderSvg = $placeholder->generatePlaceholderSvg($element, $aspectRatio);
        }
    }

}
