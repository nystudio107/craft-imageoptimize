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
use nystudio107\imageoptimize\models\OptimizedImage;

use ColorThief\ColorThief;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\elements\Asset;
use craft\errors\AssetLogicException;
use craft\helpers\Image;
use craft\helpers\Json;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use craft\image\Raster;
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

    const PLACEHOLDER_WIDTH = 16;
    const PLACEHOLDER_QUALITY = 50;

    const COLOR_PALETTE_WIDTH = 200;
    const COLOR_PALETTE_QUALITY = 75;

    // Public Properties
    // =========================================================================

    /**
     * @var array
     */
    public $variants = [
        [
            'width'          => 1170,
            'useAspectRatio' => true,
            'aspectRatioX'   => 16.0,
            'aspectRatioY'   => 9.0,
            'retinaSizes'    => ['1'],
            'quality'        => 82,
            'format'         => 'jpg',
        ],
        [
            'width'          => 970,
            'useAspectRatio' => true,
            'aspectRatioX'   => 16.0,
            'aspectRatioY'   => 9.0,
            'retinaSizes'    => ['1'],
            'quality'        => 82,
            'format'         => 'jpg',
        ],
        [
            'width'          => 750,
            'useAspectRatio' => true,
            'aspectRatioX'   => 4.0,
            'aspectRatioY'   => 3.0,
            'retinaSizes'    => ['1'],
            'quality'        => 60,
            'format'         => 'jpg',
        ],
        [
            'width'          => 320,
            'useAspectRatio' => true,
            'aspectRatioX'   => 4.0,
            'aspectRatioY'   => 3.0,
            'retinaSizes'    => ['1'],
            'quality'        => 60,
            'format'         => 'jpg',
            'useAspectRatio' => true,],
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
        /** @var Asset $element */
        if ($element instanceof Asset) {
            // If this is a new element, resave it so that it as an id for our asset transforms
            if ($isNew) {
                // Initialize our field with defaults
                $this->currentAsset = $element;
                $defaultData = $this->normalizeValue(null, $element);
                $defaultSerializedData = $this->serializeValue($defaultData, $element);
                $element->setFieldValues([
                    $this->handle => $defaultSerializedData,
                ]);

                $success = Craft::$app->getElements()->saveElement($element, false);
                Craft::info(
                    print_r('Re-saved new asset '.$success, true),
                    __METHOD__
                );
            } else {
                // Otherwise create a dummy model, and populate it, to recreate any asset transforms
                $model = new OptimizedImage();
                $this->populateOptimizedImageModel($element, $model);
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
        $placeholderMade = false;
        foreach ($this->variants as $variant) {
            $retinaSizes = ['1'];
            if (!empty($variant['retinaSizes'])) {
                $retinaSizes = $variant['retinaSizes'];
            }
            foreach ($retinaSizes as $retinaSize) {
                // Create the transform based on the variant
                $useAspectRatio = isset($variant['useAspectRatio']) ? $variant['useAspectRatio'] : true;
                if ($useAspectRatio) {
                    $aspectRatio = $variant['aspectRatioX'] / $variant['aspectRatioY'];
                } else {
                    if ($element->height != 0) {
                        $aspectRatio = $element->width / $element->height;
                    } else {
                        $aspectRatio = 1.0;
                    }
                }
                $width = $variant['width'] * $retinaSize;
                $transform->width = $width;
                $transform->height = intval($width / $aspectRatio);
                $transform->quality = $variant['quality'];
                $transform->format = $variant['format'];

                $finalFormat = $transform->format == null ? $element->getExtension() : $transform->format;

                // Only try the transform if it's possible
                if (Image::canManipulateAsImage($finalFormat) && Image::canManipulateAsImage($element->getExtension())) {
                    // Force generateTransformsBeforePageLoad = true to generate the images now
                    $generalConfig = Craft::$app->getConfig()->getGeneral();
                    $oldSetting = $generalConfig->generateTransformsBeforePageLoad;
                    $generalConfig->generateTransformsBeforePageLoad = true;
                    $url = '';
                    try {
                        // Generate the URLs to the optimized images
                        $url = $element->getUrl($transform);
                    } catch (AssetLogicException $e) {
                        // This isn't an image or an image format that can be transformed
                    }
                    $generalConfig->generateTransformsBeforePageLoad = $oldSetting;

                    // Update the model
                    if (!empty($url)) {
                        $model->optimizedImageUrls[$width] = $url;
                        $model->optimizedWebPImageUrls[$width] = $url.'.webp';
                    }
                    $model->focalPoint = $element->focalPoint;
                    $model->originalImageWidth = $element->width;
                    $model->originalImageHeight = $element->height;
                    // Make our placeholder image once, from the first variant
                    if (!$placeholderMade) {
                        $model->placeholderWidth = $transform->width;
                        $model->placeholderHeight = $transform->height;
                        // Generate our placeholder image
                        $model->placeholder = $this->generatePlaceholderImage($element, $aspectRatio);
                        // Generate the color palette for the image
                        $model->colorPalette = $this->generateColorPalette($element, $aspectRatio);
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
     * Generate a base64-encoded placeholder image
     *
     * @param Asset $asset
     * @param float $aspectRatio
     *
     * @return string
     */
    protected function generatePlaceholderImage(Asset $asset, float $aspectRatio): string
    {
        $result = '';
        $width = self::PLACEHOLDER_WIDTH;
        $height = intval($width / $aspectRatio);
        $tempPath = $this->createImageFromAsset($asset, $width, $height, self::PLACEHOLDER_QUALITY);
        if (!empty($tempPath)) {
            $result = base64_encode(file_get_contents($tempPath));
            unlink($tempPath);
        }

        return $result;
    }

    /**
     * Generate a color palette from the image
     *
     * @param Asset $asset
     * @param float $aspectRatio
     *
     * @return array
     */
    protected function generateColorPalette(Asset $asset, float $aspectRatio): array
    {
        $colorPalette = [];
        $width = self::COLOR_PALETTE_WIDTH;
        $height = intval($width / $aspectRatio);
        $tempPath = $this->createImageFromAsset($asset, $width, $height, self::COLOR_PALETTE_QUALITY);
        if (!empty($tempPath)) {
            // Extract the color palette
            $palette = ColorThief::getPalette($tempPath, 5);
            // Convert RGB to hex color
            foreach ($palette as $colors) {
                $colorPalette[] = sprintf("#%02x%02x%02x", $colors[0], $colors[1], $colors[2]);
            }
            unlink($tempPath);
        }

        return $colorPalette;
    }

    /**
     * @param Asset $asset
     * @param int   $width
     * @param int   $height
     * @param int   $quality
     *
     * @return string
     */
    protected function createImageFromAsset(Asset $asset, int $width, int $height, int $quality)
    {
        $tempPath = '';
        if (!empty($asset) && Image::canManipulateAsImage($asset->getExtension())) {
            $images = Craft::$app->getImages();
            $imageSource = $asset->getTransformSource();
            /** @var Image $image */
            if (StringHelper::toLowerCase($asset->getExtension()) === 'svg') {
                $image = $images->loadImage($imageSource, true, $width);
            } else {
                $image = $images->loadImage($imageSource);
            }

            if ($image instanceof Raster) {
                $image->setQuality($quality);
            }

            // Scale and crop the placeholder image
            if ($asset->focalPoint) {
                $position = $asset->getFocalPoint();
            } else {
                $position = 'center-center';
            }
            $image->scaleAndCrop($width, $height, true, $position);

            // Save the image out to a temp file, then return its contents
            $tempFilename = uniqid(pathinfo($asset->filename, PATHINFO_FILENAME), true).'.'.'jpg';
            $tempPath = Craft::$app->getPath()->getTempPath().DIRECTORY_SEPARATOR.$tempFilename;
            clearstatcache(true, $tempPath);
            $image->saveAs($tempPath);
        }

        return $tempPath;
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

}
